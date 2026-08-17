<?php
/**
 * Rewrite product descriptions so that every <img> points at a local file
 * instead of a supplier host.
 *
 * Two kinds of replacement are performed:
 *
 *   1. Supplier URLs listed in the CSV map produced by localize_desc_images.php
 *      become the root-relative path of the downloaded copy.
 *   2. Absolute URLs pointing at our own storefront lose scheme and host, so
 *      no host name survives anywhere in the markup.
 *
 * URLs whose download failed are deliberately left untouched: rewriting them
 * would replace a working hotlink with a dead local path.
 *
 * Dry run by default. Nothing is written without --apply, and --apply refuses
 * to start unless the backup snapshot table is present.
 *
 * Usage:
 *   /usr/local/lsws/lsphp74/bin/php scripts/rewrite_desc_images.php \
 *       --root=/var/www/ls.radio-shop.com.ua --backup=<table> [--apply] [--limit=N]
 */

$opts = getopt('', array('root:', 'backup:', 'apply', 'limit::', 'strip-own-host'));

if (empty($opts['root'])) {
    fwrite(STDERR, "ERROR: --root is required\n");
    exit(1);
}

$root   = rtrim($opts['root'], '/');
$apply  = array_key_exists('apply', $opts);
$limit  = isset($opts['limit']) ? (int)$opts['limit'] : 0;
$backup = isset($opts['backup']) ? $opts['backup'] : '';
$config = $root . '/html/config.php';

// Off by default. Stripping our own host touches internal <a href> links only:
// it shares no row with the image rewrite, and some of those links sit inside
// escaped CKEditor widget payloads. It is a separate change with its own risk,
// so it must be requested explicitly.
$strip_own = array_key_exists('strip-own-host', $opts);

if (!is_file($config)) {
    fwrite(STDERR, "ERROR: config not found: $config\n");
    exit(1);
}

require $config;

$map_file = $root . '/localize_desc_images_map.csv';

if (!is_file($map_file)) {
    fwrite(STDERR, "ERROR: map not found: $map_file\n");
    exit(1);
}

// Our own storefront hosts. Absolute links to these are made root-relative.
$own_host_pattern = '#https?://(?:www\.)?(?:radio-shop\.com\.ua|ls\.radio-shop\.com\.ua)/#i';

// ------------------------------------------------------------- load map -----

$search  = array();
$replace = array();
$skipped_urls = array();
$hosts   = array();

$fh = fopen($map_file, 'r');
fgetcsv($fh);

while (($row = fgetcsv($fh)) !== false) {
    if (count($row) < 3) {
        continue;
    }

    list($url, $local, $status) = $row;

    $host = strtolower((string)parse_url($url, PHP_URL_HOST));

    if ($host !== '') {
        $hosts[$host] = true;
    }

    // Only URLs backed by a file on disk may be rewritten.
    if ($local === '' || !in_array($status, array('downloaded', 'reused', 'exists'), true)) {
        $skipped_urls[$url] = $status;
        continue;
    }

    if (!is_file($root . '/html/' . $local)) {
        $skipped_urls[$url] = 'file-missing';
        continue;
    }

    $search[]  = $url;
    $replace[] = '/' . $local;

    // URLs are collected from decoded markup, but the replacement runs against
    // raw storage. When a URL carries an ampersand the stored form has it
    // entity-encoded, so the decoded string would never match. Register that
    // variant as well.
    if (strpos($url, '&') !== false) {
        $search[]  = str_replace('&', '&amp;', $url);
        $replace[] = '/' . $local;
    }
}

fclose($fh);

// Longest first: one URL can be a prefix of another, and str_replace works
// sequentially, so a short match would otherwise corrupt the longer one.
// The lengths must live in a variable because array_multisort takes it by
// reference.
$lengths = array_map('strlen', $search);
array_multisort($lengths, SORT_DESC, $search, $replace);

echo 'rewritable URLs : ' . count($search) . "\n";
echo 'skipped URLs    : ' . count($skipped_urls) . "\n";

// ---------------------------------------------------------------- connect ---

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_errno) {
    fwrite(STDERR, 'ERROR: db connect: ' . $db->connect_error . "\n");
    exit(1);
}

$db->set_charset('utf8mb4');

echo 'database        : ' . DB_DATABASE . ' @ ' . DB_HOSTNAME . "\n";

if ($apply) {
    if ($backup === '') {
        fwrite(STDERR, "ERROR: --apply requires --backup=<snapshot table>\n");
        exit(1);
    }

    $check = $db->query("SHOW TABLES LIKE '" . $db->real_escape_string($backup) . "'");

    if (!$check || !$check->num_rows) {
        fwrite(STDERR, "ERROR: backup table '$backup' not found; refusing to modify data\n");
        exit(1);
    }

    $live = $db->query('SELECT COUNT(*) c FROM oc_product_description')->fetch_assoc()['c'];
    $snap = $db->query("SELECT COUNT(*) c FROM `" . $backup . "`")->fetch_assoc()['c'];

    if ((int)$live !== (int)$snap) {
        fwrite(STDERR, "ERROR: backup row count $snap does not match live $live; refusing\n");
        exit(1);
    }

    echo "backup verified : $backup ($snap rows)\n";
}

// ------------------------------------------------------------ select rows ---

$where = array();

foreach (array_keys($hosts) as $host) {
    $where[] = "description LIKE '%" . $db->real_escape_string($host) . "%'";
}

if ($strip_own) {
    $where[] = "description LIKE '%radio-shop.com.ua%'";
}

$sql = 'SELECT product_id, language_id FROM oc_product_description WHERE (' . implode(' OR ', $where) . ')';

$result = $db->query($sql);

if (!$result) {
    fwrite(STDERR, 'ERROR: select failed: ' . $db->error . "\n");
    exit(1);
}

$keys = array();

while ($row = $result->fetch_assoc()) {
    $keys[] = array((int)$row['product_id'], (int)$row['language_id']);
}

$result->free();

echo 'candidate rows  : ' . count($keys) . "\n\n";

// --------------------------------------------------------------- rewrite ----

$read   = $db->prepare('SELECT description FROM oc_product_description WHERE product_id = ? AND language_id = ?');
$write  = $db->prepare('UPDATE oc_product_description SET description = ? WHERE product_id = ? AND language_id = ?');

$changed   = 0;
$unchanged = 0;
$processed = 0;
$samples   = array();

if ($apply) {
    $db->begin_transaction();
}

foreach ($keys as $key) {
    if ($limit > 0 && $processed >= $limit) {
        break;
    }

    $processed++;

    list($product_id, $language_id) = $key;

    $read->bind_param('ii', $product_id, $language_id);
    $read->execute();
    $res = $read->get_result();
    $row = $res->fetch_assoc();
    $res->free();

    if (!$row) {
        continue;
    }

    $before = $row['description'];
    $after  = str_replace($search, $replace, $before);

    if ($strip_own) {
        $after = preg_replace($own_host_pattern, '/', $after);
    }

    if ($after === $before) {
        $unchanged++;
        continue;
    }

    $changed++;

    if (count($samples) < 3) {
        $samples[] = array($product_id, $language_id, $before, $after);
    }

    if ($apply) {
        $write->bind_param('sii', $after, $product_id, $language_id);

        if (!$write->execute()) {
            $db->rollback();
            fwrite(STDERR, "ERROR: update failed on $product_id/$language_id: " . $db->error . "\n");
            exit(1);
        }

        if (($changed % 500) === 0) {
            $db->commit();
            $db->begin_transaction();
            echo "committed $changed rows\n";
        }
    }
}

if ($apply) {
    $db->commit();
}

echo 'rows processed  : ' . $processed . "\n";
echo 'rows changed    : ' . $changed . "\n";
echo 'rows unchanged  : ' . $unchanged . "\n";

// ----------------------------------------------------------- verification ---

foreach ($samples as $sample) {
    list($product_id, $language_id, $before, $after) = $sample;

    echo "\n-- sample product_id=$product_id language_id=$language_id\n";

    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $before, $b);
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $after, $a);

    foreach (array_slice($b[1], 0, 3) as $i => $src) {
        echo '   before: ' . $src . "\n";
        echo '   after : ' . (isset($a[1][$i]) ? $a[1][$i] : '?') . "\n";
    }
}

if ($apply) {
    echo "\n-- post-apply verification\n";

    foreach (array_keys($hosts) as $host) {
        $q = $db->query("SELECT COUNT(*) c FROM oc_product_description WHERE description LIKE '%"
            . $db->real_escape_string($host) . "%'");
        $c = (int)$q->fetch_assoc()['c'];

        echo '   rows still mentioning ' . str_pad($host, 26) . $c . "\n";
    }

    if ($strip_own) {
        $q = $db->query("SELECT COUNT(*) c FROM oc_product_description WHERE description LIKE '%radio-shop.com.ua%'");
        echo '   rows still mentioning ' . str_pad('radio-shop.com.ua', 26) . (int)$q->fetch_assoc()['c'] . "\n";
    }

    echo "\n   any remainder should be accounted for by the "
        . count($skipped_urls) . " skipped URLs left hotlinked on purpose\n";
} else {
    echo "\nDRY RUN: no rows were modified. Re-run with --apply --backup=<table> to write.\n";
}

$db->close();
