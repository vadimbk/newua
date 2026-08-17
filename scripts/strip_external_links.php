<?php
/**
 * Remove links that lead away from the shop out of product descriptions.
 *
 * Every <a> pointing at an external host is dropped, except hosts named by
 * --keep (YouTube by default: those are product video reviews, not competitor
 * or supplier traffic). Links to our own host and relative links are left
 * alone.
 *
 * How an element is dropped depends on what it holds:
 *
 *   - ordinary content (model codes, phrases, images) -> the <a> wrapper is
 *     removed and the content stays, so sentences and tables survive intact
 *   - empty content, or content that is just the URL itself -> the whole
 *     element goes, otherwise a bare external address would remain as text
 *
 * Descriptions exist in two storage forms, plain and HTML-escaped, and both
 * are handled. Tag boundaries are matched with cheap lazy patterns: a
 * per-character negative lookahead exhausts the PCRE JIT stack on long rows
 * and makes preg_replace_callback return null. Every result is checked for
 * null before it can reach the database.
 *
 * Dry run by default; --apply also requires --backup=<snapshot table>.
 *
 * Usage:
 *   /usr/local/lsws/lsphp74/bin/php scripts/strip_external_links.php \
 *       --root=/var/www/ls.radio-shop.com.ua [--keep=youtube.com] \
 *       [--apply --backup=<table>]
 */

$opts = getopt('', array('root:', 'backup:', 'apply', 'keep::', 'samples::'));

if (empty($opts['root'])) {
    fwrite(STDERR, "ERROR: --root is required\n");
    exit(1);
}

$root       = rtrim($opts['root'], '/');
$apply      = array_key_exists('apply', $opts);
$backup     = isset($opts['backup']) ? $opts['backup'] : '';
$keep_raw   = isset($opts['keep']) && $opts['keep'] !== false ? $opts['keep'] : 'youtube.com';
$max_sample = isset($opts['samples']) ? (int)$opts['samples'] : 6;
$config     = $root . '/html/config.php';

if (!is_file($config)) {
    fwrite(STDERR, "ERROR: config not found: $config\n");
    exit(1);
}

require $config;

$keep_hosts = array_filter(array_map('trim', explode(',', strtolower($keep_raw))));
$own_hosts  = array('radio-shop.com.ua', 'www.radio-shop.com.ua', 'ls.radio-shop.com.ua');

echo 'keeping links to        : ' . implode(', ', $keep_hosts) . "\n";

/**
 * True when this href must lose its <a> wrapper.
 */
function is_external_target($href, array $keep_hosts, array $own_hosts)
{
    $href = html_entity_decode(trim($href), ENT_QUOTES, 'UTF-8');

    if (!preg_match('#^https?://#i', $href)) {
        return false;
    }

    $host = strtolower((string)parse_url($href, PHP_URL_HOST));

    if ($host === '' || in_array($host, $own_hosts, true)) {
        return false;
    }

    foreach ($keep_hosts as $keep) {
        if ($host === $keep || substr($host, -strlen('.' . $keep)) === '.' . $keep) {
            return false;
        }
    }

    return true;
}

/**
 * Decide the replacement for one matched <a> element.
 *
 * $inner is the element content exactly as stored; $esc marks the escaped
 * form. Returns the text that takes the element's place.
 */
function replacement_for($inner, $esc, &$unwrapped, &$dropped)
{
    $text = $inner;

    if ($esc) {
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }

    $text = trim(html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8'));

    // Nothing worth keeping, or the visible text is the address itself.
    if ($text === '' || preg_match('#^https?://#i', $text)) {
        $dropped++;

        return '';
    }

    $unwrapped++;

    return $inner;
}

/**
 * Tidy the artefacts an removal leaves behind: a stranded space before
 * punctuation, or a run of spaces where a phrase used to be. HTML collapses
 * neither of those when rendering, so they would be visible.
 */
function tidy($html)
{
    $html = preg_replace('/[ \t]{2,}/u', ' ', $html);
    $html = preg_replace('/ +([.,;:!?])/u', '$1', $html);
    $html = preg_replace('/\(\s*\)/u', '', $html);

    return $html;
}

// ---------------------------------------------------------------- connect ---

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_errno) {
    fwrite(STDERR, 'ERROR: db connect: ' . $db->connect_error . "\n");
    exit(1);
}

$db->set_charset('utf8mb4');
echo 'database                : ' . DB_DATABASE . ' @ ' . DB_HOSTNAME . "\n";

if ($apply) {
    if ($backup === '') {
        fwrite(STDERR, "ERROR: --apply requires --backup=<snapshot table>\n");
        exit(1);
    }

    $check = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($backup) . "'");

    if (!$check || !$check->num_rows) {
        fwrite(STDERR, "ERROR: backup table '$backup' not found; refusing\n");
        exit(1);
    }

    $live = (int)$db->query('SELECT COUNT(*) c FROM oc_product_description')->fetch_assoc()['c'];
    $snap = (int)$db->query("SELECT COUNT(*) c FROM `" . $backup . "`")->fetch_assoc()['c'];

    if ($live !== $snap) {
        fwrite(STDERR, "ERROR: backup has $snap rows, live has $live; refusing\n");
        exit(1);
    }

    echo "backup verified         : $backup ($snap rows)\n";
}

// ----------------------------------------------------------------- work -----

$result = $db->query("SELECT product_id, language_id, description FROM oc_product_description
                      WHERE description LIKE '%<a %' OR description LIKE '%&lt;a %'");

if (!$result) {
    fwrite(STDERR, 'ERROR: select failed: ' . $db->error . "\n");
    exit(1);
}

$write     = $db->prepare('UPDATE oc_product_description SET description = ? WHERE product_id = ? AND language_id = ?');
$pending   = array();
$unwrapped = 0;
$dropped   = 0;
$failures  = array();
$samples   = array();

while ($row = $result->fetch_assoc()) {
    $before = $row['description'];

    $plain_cb = function ($m) use ($keep_hosts, $own_hosts, &$unwrapped, &$dropped) {
        if (!is_external_target($m[1], $keep_hosts, $own_hosts)) {
            return $m[0];
        }

        return replacement_for($m[2], false, $unwrapped, $dropped);
    };

    $esc_cb = function ($m) use ($keep_hosts, $own_hosts, &$unwrapped, &$dropped) {
        if (!is_external_target($m[1], $keep_hosts, $own_hosts)) {
            return $m[0];
        }

        return replacement_for($m[2], true, $unwrapped, $dropped);
    };

    $after = preg_replace_callback(
        '/<a\b[^>]*?href\s*=\s*["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/is',
        $plain_cb,
        $before
    );

    if ($after !== null) {
        $after = preg_replace_callback(
            '/&lt;a\b.*?href\s*=\s*&quot;(.*?)&quot;.*?&gt;(.*?)&lt;\/a&gt;/is',
            $esc_cb,
            $after
        );
    }

    if ($after === null) {
        $failures[] = $row['product_id'] . '/' . $row['language_id'] . ' (pcre ' . preg_last_error() . ')';
        continue;
    }

    if ($after === $before) {
        continue;
    }

    $after = tidy($after);

    if ($after === null) {
        $failures[] = $row['product_id'] . '/' . $row['language_id'] . ' (pcre in tidy)';
        continue;
    }

    $pending[] = array((int)$row['product_id'], (int)$row['language_id'], $after);

    if (count($samples) < $max_sample) {
        $samples[] = array($row['product_id'], $row['language_id'], $before, $after);
    }
}

$result->free();

echo "\nrows to change          : " . count($pending) . "\n";
echo "links unwrapped         : $unwrapped\n";
echo "elements dropped whole  : $dropped\n";
echo 'rows skipped, pcre fail : ' . count($failures) . "\n";

foreach ($failures as $f) {
    echo "   SKIPPED: $f\n";
}

// Show what a reader will actually see, not raw markup.
foreach ($samples as $s) {
    list($pid, $lid, $b, $a) = $s;

    $bt = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($b, ENT_QUOTES, 'UTF-8'))));
    $at = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode($a, ENT_QUOTES, 'UTF-8'))));

    // Print the neighbourhood of the first difference.
    $i = 0;
    $max = min(mb_strlen($bt), mb_strlen($at));

    while ($i < $max && mb_substr($bt, $i, 1) === mb_substr($at, $i, 1)) {
        $i++;
    }

    $from = max(0, $i - 60);

    echo "\n-- product_id=$pid language_id=$lid\n";
    echo '   было : ...' . mb_substr($bt, $from, 170) . "...\n";
    echo '   стало: ...' . mb_substr($at, $from, 170) . "...\n";
}

if (!$apply) {
    echo "\nDRY RUN: nothing written. Add --apply --backup=<table> to write.\n";
    $db->close();
    exit(0);
}

$db->begin_transaction();
$done = 0;

foreach ($pending as $p) {
    list($product_id, $language_id, $after) = $p;
    $write->bind_param('sii', $after, $product_id, $language_id);

    if (!$write->execute()) {
        $db->rollback();
        fwrite(STDERR, "ERROR: update failed on $product_id/$language_id: " . $write->error . "\n");
        exit(1);
    }

    if ((++$done % 500) === 0) {
        $db->commit();
        $db->begin_transaction();
    }
}

$db->commit();
echo "\nrows written            : $done\n";
$db->close();
