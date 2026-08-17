<?php
/**
 * Clean up product description markup left over after image localisation.
 *
 * Descriptions are stored in two forms: plain markup and HTML-escaped markup
 * (the theme runs html_entity_decode before rendering). Rather than guessing
 * at substrings, each <img> element is isolated by its own tag boundaries in
 * whichever form it appears, and the decision is taken on the isolated tag.
 * Doubly escaped payloads (&amp;quot;) belong to CKEditor widget data; tag
 * boundaries do not apply there and they are left alone.
 *
 * Operations, each selected explicitly:
 *
 *   --remove-dead-images  drop <img> elements whose source never answered
 *   --fix-own-images      turn absolute URLs on our own host into root
 *                         relative paths, repairing the few that name a file
 *                         which no longer exists under that exact name
 *
 * Dry run by default; --apply also requires --backup=<snapshot table>.
 *
 * Usage:
 *   /usr/local/lsws/lsphp74/bin/php scripts/cleanup_desc_markup.php \
 *       --root=/var/www/ls.radio-shop.com.ua --remove-dead-images \
 *       --fix-own-images [--apply --backup=<table>]
 */

$opts = getopt('', array('root:', 'backup:', 'apply', 'remove-dead-images', 'fix-own-images'));

if (empty($opts['root'])) {
    fwrite(STDERR, "ERROR: --root is required\n");
    exit(1);
}

$root    = rtrim($opts['root'], '/');
$apply   = array_key_exists('apply', $opts);
$backup  = isset($opts['backup']) ? $opts['backup'] : '';
$do_dead = array_key_exists('remove-dead-images', $opts);
$do_own  = array_key_exists('fix-own-images', $opts);
$config  = $root . '/html/config.php';

if (!$do_dead && !$do_own) {
    fwrite(STDERR, "ERROR: pick at least one operation\n");
    exit(1);
}

if (!is_file($config)) {
    fwrite(STDERR, "ERROR: config not found: $config\n");
    exit(1);
}

require $config;

// Six references carry a " (1)" duplicate suffix whose file was removed while
// the original survives; one points into image/data where the file actually
// lives under image/catalog/product. Each was confirmed on disk individually.
$repairs = array(
    '/image/data/insruments/pliers/8PK-906 (1).jpg'      => '/image/data/insruments/pliers/8PK-906.jpg',
    '/image/data/insruments/pliers/1PK-055S (1).jpg'     => '/image/data/insruments/pliers/1PK-055S.jpg',
    '/image/data/insruments/pliers/1PK-705 (1).jpg'      => '/image/data/insruments/pliers/1PK-705.jpg',
    '/image/data/insruments/pliers/1PK-5101-CE (1).jpg'  => '/image/data/insruments/pliers/1PK-5101-CE.jpg',
    '/image/data/insruments/pliers/1PK-396A (1).jpg'     => '/image/data/insruments/pliers/1PK-396A.jpg',
    '/image/data/insruments/pliers/1PK-705Y (1).jpg'     => '/image/data/insruments/pliers/1PK-705Y.jpg',
    '/image/data/insruments/stripping cable/SR-330.jpg'  => '/image/catalog/product/insruments/stripping cable/SR-330.jpg',
);

$own_host_re = '#^https?://(?:www\.)?(?:ls\.)?radio-shop\.com\.ua(/.*)$#i';

// ------------------------------------------------------- dead URL list ------

$dead = array();

if ($do_dead) {
    $map_file = $root . '/localize_desc_images_map.csv';

    if (!is_file($map_file)) {
        fwrite(STDERR, "ERROR: map not found: $map_file\n");
        exit(1);
    }

    $fh = fopen($map_file, 'r');
    fgetcsv($fh);

    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) >= 3 && !in_array($row[2], array('downloaded', 'reused', 'exists'), true)) {
            $dead[$row[0]] = true;
        }
    }

    fclose($fh);
    echo 'dead image URLs          : ' . count($dead) . "\n";
}

// ---------------------------------------------------------------- connect ---

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_errno) {
    fwrite(STDERR, 'ERROR: db connect: ' . $db->connect_error . "\n");
    exit(1);
}

$db->set_charset('utf8mb4');
echo 'database                 : ' . DB_DATABASE . ' @ ' . DB_HOSTNAME . "\n";

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

    echo "backup verified          : $backup ($snap rows)\n";
}

// ------------------------------------------------------------- counters -----

$dead_removed  = 0;
$own_fixed     = 0;
$own_repaired  = 0;
$missing       = array();

/**
 * Decide what happens to one isolated <img> tag.
 *
 * $tag    the tag exactly as it appears in storage
 * $esc    true when the tag is stored HTML-escaped
 * Returns the replacement text for that tag ('' removes it).
 */
function handle_tag($tag, $esc, $dead, $do_dead, $do_own, $repairs, $own_host_re, $root,
                    &$dead_removed, &$own_fixed, &$own_repaired, &$missing)
{
    // Read the source out of the tag in whichever quoting the form uses.
    $pattern = $esc
        ? '/src\s*=\s*&quot;(.*?)&quot;/is'
        : '/src\s*=\s*(["\'])(.*?)\1/is';

    if (!preg_match($pattern, $tag, $m)) {
        return $tag;
    }

    $src = $esc ? $m[1] : $m[2];
    $raw = html_entity_decode($src, ENT_QUOTES, 'UTF-8');

    if ($do_dead && isset($dead[$raw])) {
        $dead_removed++;

        return '';
    }

    if ($do_own && preg_match($own_host_re, $raw, $mm)) {
        $path = $mm[1];

        if (isset($repairs[$path])) {
            $path = $repairs[$path];
            $own_repaired++;
        }

        if (!is_file($root . '/html' . rawurldecode($path))) {
            $missing[$path] = true;

            return $tag;
        }

        $own_fixed++;
        $new = $esc ? htmlspecialchars($path, ENT_QUOTES, 'UTF-8') : $path;

        return str_replace($src, $new, $tag);
    }

    return $tag;
}

// ----------------------------------------------------------------- work -----

$result = $db->query("SELECT product_id, language_id, description FROM oc_product_description
                      WHERE description LIKE '%<img%' OR description LIKE '%&lt;img%'");

if (!$result) {
    fwrite(STDERR, 'ERROR: select failed: ' . $db->error . "\n");
    exit(1);
}

$regex_failures = array();
$write   = $db->prepare('UPDATE oc_product_description SET description = ? WHERE product_id = ? AND language_id = ?');
$pending = array();

while ($row = $result->fetch_assoc()) {
    $before = $row['description'];

    $common = function ($tag, $esc) use (
        $dead, $do_dead, $do_own, $repairs, $own_host_re, $root,
        &$dead_removed, &$own_fixed, &$own_repaired, &$missing
    ) {
        return handle_tag($tag, $esc, $dead, $do_dead, $do_own, $repairs, $own_host_re, $root,
            $dead_removed, $own_fixed, $own_repaired, $missing);
    };

    // Plain form: a tag runs to the first '>'.
    $after = preg_replace_callback(
        '/<img\b[^>]*>/i',
        function ($m) use ($common) {
            return $common($m[0], false);
        },
        $before
    );

    // Escaped form: a tag runs to the first '&gt;'. A literal '>' inside an
    // attribute would itself be stored as '&amp;gt;', so the first '&gt;' is
    // always the real tag end. A plain lazy match is therefore both correct
    // and cheap; a per-character negative lookahead here exhausts the PCRE JIT
    // stack on long descriptions and makes preg_replace_callback return null.
    if ($after !== null) {
        $after = preg_replace_callback(
            '/&lt;img\b.*?&gt;/is',
            function ($m) use ($common) {
                return $common($m[0], true);
            },
            $after
        );
    }

    // A null means the regex engine gave up. Writing it would blank the
    // description, so the row is reported and left untouched instead.
    if ($after === null) {
        $regex_failures[] = $row['product_id'] . '/' . $row['language_id']
            . ' (pcre error ' . preg_last_error() . ')';
        continue;
    }

    if ($after !== $before) {
        $pending[] = array((int)$row['product_id'], (int)$row['language_id'], $after);
    }
}

$result->free();

echo "\nrows to change           : " . count($pending) . "\n";
echo "dead <img> removed       : $dead_removed\n";
echo "own URLs made relative   : $own_fixed\n";
echo "  of which path repaired : $own_repaired\n";
echo 'paths still missing      : ' . count($missing) . "\n";
echo 'rows skipped, regex fail : ' . count($regex_failures) . "\n";
foreach ($regex_failures as $rf) { echo "   SKIPPED: $rf\n"; }

foreach (array_keys($missing) as $p) {
    echo "   MISSING: $p\n";
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
echo "\nrows written             : $done\n";
$db->close();
