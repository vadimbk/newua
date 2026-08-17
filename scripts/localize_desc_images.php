<?php
/**
 * Download externally hotlinked <img> sources referenced from product
 * descriptions and store them locally under a supplier-named directory.
 *
 * Layout: image/catalog/desc/<SUPPLIER>/<file name>
 * No host name appears in the path or, later, in the description markup.
 *
 * Several supplier hosts mirror the same file (intertool.ua and
 * s3.intertool.ua share 134 file names). Identical files are collapsed onto
 * one local copy; genuinely different files that happen to share a name get a
 * content-hash suffix so nothing is ever overwritten.
 *
 * This script is READ-ONLY with respect to the database: SELECT only. The
 * description rewrite is a separate step driven by the CSV map produced here.
 *
 * Safe to re-run: the CSV map from a previous run is used to skip finished
 * downloads without touching the network. The map is flushed periodically so
 * an interrupted run does not lose its progress record.
 *
 * Usage:
 *   /usr/local/lsws/lsphp74/bin/php scripts/localize_desc_images.php \
 *       --root=/var/www/ls.radio-shop.com.ua [--limit=N] [--dry-run]
 */

$opts = getopt('', array('root:', 'limit::', 'dry-run'));

if (empty($opts['root'])) {
    fwrite(STDERR, "ERROR: --root is required, e.g. /var/www/ls.radio-shop.com.ua\n");
    exit(1);
}

$root    = rtrim($opts['root'], '/');
$limit   = isset($opts['limit']) ? (int)$opts['limit'] : 0;
$dry_run = array_key_exists('dry-run', $opts);
$config  = $root . '/html/config.php';

if (!is_file($config)) {
    fwrite(STDERR, "ERROR: config not found: $config\n");
    exit(1);
}

// Credentials come from the host's own gitignored config.php, so this script
// stays free of secrets and works unchanged on either webroot.
require $config;

$map_file = $root . '/localize_desc_images_map.csv';
$log_file = $root . '/localize_desc_images_failed.log';

// Hosts that are ours: those files are already local, so they are never
// downloaded. Removing the host from them is a job for the rewrite step.
$own_hosts = array('radio-shop.com.ua', 'www.radio-shop.com.ua', 'ls.radio-shop.com.ua');

/**
 * Map an origin host onto the supplier directory name.
 */
function supplier_for($host)
{
    $host = strtolower($host);

    if (strpos($host, 'intertool.ua') !== false) {
        return 'INTERTOOL';
    }

    if (strpos($host, 'eopt.ua') !== false) {
        return 'HOEGERT';
    }

    if (strpos($host, 'grandinstrument.ua') !== false) {
        return 'GRANDINSTRUMENT';
    }

    if (strpos($host, 'proskit') !== false || strpos($host, 'prokits') !== false) {
        return 'PROSKIT';
    }

    if (strpos($host, 'masteram') !== false) {
        return 'MASTERAM';
    }

    if (strpos($host, 'toya24') !== false || strpos($host, 'yato24') !== false) {
        return 'YATO';
    }

    return 'MISC';
}

/**
 * Insert a suffix before the file extension.
 */
function suffix_name($name, $suffix)
{
    $dot = strrpos($name, '.');

    return ($dot === false)
        ? $name . $suffix
        : substr($name, 0, $dot) . $suffix . substr($name, $dot);
}

/**
 * Build the repo-relative target path for a URL. Returns null if unusable.
 */
function target_for($url)
{
    $host = parse_url($url, PHP_URL_HOST);
    $path = parse_url($url, PHP_URL_PATH);

    if (!$host || !$path) {
        return null;
    }

    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', rawurldecode(basename($path)));

    if ($name === '' || $name === '.' || $name === '..') {
        return null;
    }

    // A few URLs differ only by query string; fold it into the name so they
    // cannot collapse onto one file.
    $query = parse_url($url, PHP_URL_QUERY);

    if ($query !== null && $query !== '') {
        $name = suffix_name($name, '-q' . substr(md5($query), 0, 8));
    }

    return 'image/catalog/desc/' . supplier_for($host) . '/' . $name;
}

/**
 * Write the URL to local path map. Called periodically so that an interrupted
 * run still leaves a usable resume record behind.
 */
function write_map($map_file, array $map)
{
    $tmp = $map_file . '.tmp';
    $csv = fopen($tmp, 'w');

    if (!$csv) {
        return;
    }

    fputcsv($csv, array('url', 'local_path', 'status'));

    foreach ($map as $url => $entry) {
        fputcsv($csv, array($url, $entry[0], $entry[1]));
    }

    fclose($csv);
    rename($tmp, $map_file);
}

// ---------------------------------------------------------------- collect ---

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

if ($db->connect_errno) {
    fwrite(STDERR, 'ERROR: db connect: ' . $db->connect_error . "\n");
    exit(1);
}

$db->set_charset('utf8mb4');

$result = $db->query("SELECT description FROM oc_product_description
                      WHERE description LIKE '%<img%' OR description LIKE '%&lt;img%'");

if (!$result) {
    fwrite(STDERR, 'ERROR: select failed: ' . $db->error . "\n");
    exit(1);
}

$urls = array();

while ($row = $result->fetch_assoc()) {
    $found = array();

    // Descriptions are stored in two forms in this catalogue: as plain markup,
    // and HTML-escaped. Decoding first, exactly as the theme does before
    // rendering, normalises both into ordinary markup, so a single scan sees
    // every image regardless of how the row happens to be stored. Matching the
    // escaped form directly is unreliable: attribute values carry entities of
    // their own and the tag boundaries stop being obvious.
    $decoded = html_entity_decode($row['description'], ENT_QUOTES, 'UTF-8');

    if (preg_match_all('/<img[^>]+src\s*=\s*["\']([^"\']+)["\']/i', $decoded, $matches)) {
        $found = $matches[1];
    }

    foreach ($found as $url) {
        $url = trim($url);

        if (!preg_match('#^https?://#i', $url)) {
            continue;
        }

        $host = strtolower((string)parse_url($url, PHP_URL_HOST));

        if ($host === '' || in_array($host, $own_hosts, true)) {
            continue;
        }

        $urls[$url] = true;
    }
}

$result->free();
$db->close();

$urls = array_keys($urls);
sort($urls);

echo 'distinct external URLs: ' . count($urls) . "\n";

// ------------------------------------------------------- resume from CSV ----

$done_before = array();

if (is_file($map_file) && ($fh = fopen($map_file, 'r'))) {
    fgetcsv($fh);

    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) >= 3 && $row[1] !== ''
            && in_array($row[2], array('downloaded', 'reused', 'exists'), true)) {
            $done_before[$row[0]] = $row[1];
        }
    }

    fclose($fh);

    echo 'resume records: ' . count($done_before) . "\n";
}

if ($dry_run) {
    $by_supplier = array();

    foreach ($urls as $url) {
        $target = target_for($url);
        $key    = ($target === null) ? '(unparsable)' : explode('/', $target)[3];

        $by_supplier[$key] = isset($by_supplier[$key]) ? $by_supplier[$key] + 1 : 1;
    }

    ksort($by_supplier);

    echo "\nwould download, by supplier directory:\n";

    foreach ($by_supplier as $supplier => $count) {
        echo '  ' . str_pad($supplier, 18) . $count . "\n";
    }

    echo "\nsample mapping:\n";

    foreach (array_slice($urls, 0, 8) as $url) {
        echo '  ' . $url . "\n    -> " . target_for($url) . "\n";
    }

    echo "\ndry-run: nothing was downloaded and nothing was written\n";
    exit(0);
}

// --------------------------------------------------------------- download ---

$map        = array();
$hash_cache = array();
$downloaded = 0;
$reused     = 0;
$skipped    = 0;
$failed     = 0;
$bytes      = 0;
$attempts   = 0;

$log = fopen($log_file, 'a');

foreach ($urls as $url) {
    $target_rel = target_for($url);

    if ($target_rel === null) {
        $map[$url] = array('', 'unparsable');
        $failed++;
        fwrite($log, "unparsable\t$url\n");
        continue;
    }

    // Finished in an earlier run and still on disk: skip without any network.
    if (isset($done_before[$url]) && is_file($root . '/html/' . $done_before[$url])) {
        $map[$url] = array($done_before[$url], 'exists');
        $skipped++;
        continue;
    }

    if ($limit > 0 && $attempts >= $limit) {
        break;
    }

    $attempts++;

    // Progress is reported at the top of the iteration because most branches
    // below leave the loop body early.
    if (($attempts % 100) === 0) {
        echo 'attempted ' . $attempts . ': downloaded ' . $downloaded . ', reused ' . $reused
            . ', failed ' . $failed . ', ' . round($bytes / 1048576) . " MB\n";
        write_map($map_file, $map + array_map(
            function ($p) {
                return array($p, 'exists');
            },
            array_diff_key($done_before, $map)
        ));
    }

    $target_abs = $root . '/html/' . $target_rel;
    $dir        = dirname($target_abs);

    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        $map[$url] = array('', 'mkdir-failed');
        $failed++;
        fwrite($log, "mkdir-failed\t$url\t$dir\n");
        continue;
    }

    $tmp    = $target_abs . '.' . getmypid() . '.part';
    $handle = fopen($tmp, 'wb');

    if (!$handle) {
        $map[$url] = array('', 'open-failed');
        $failed++;
        fwrite($log, "open-failed\t$url\t$tmp\n");
        continue;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FILE, $handle);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);
    curl_setopt($ch, CURLOPT_USERAGENT, 'radio-shop image localiser');
    curl_exec($ch);

    $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $type = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $err  = curl_error($ch);

    curl_close($ch);
    fclose($handle);

    $size = is_file($tmp) ? filesize($tmp) : 0;

    // Validate the payload itself rather than the Content-Type header: some
    // suppliers answer with a bare "image" type that no header test would
    // accept. Anything that is not a decodable image is discarded so that a
    // re-run retries it from a clean state.
    $valid = ($code === 200 && $size > 0 && @getimagesize($tmp) !== false);

    if (!$valid) {
        @unlink($tmp);
        $map[$url] = array('', 'http-' . $code);
        $failed++;
        fwrite($log, "http-$code\tsize=$size\ttype=$type\t$url\t$err\n");
        continue;
    }

    if (!is_file($target_abs)) {
        if (!rename($tmp, $target_abs)) {
            @unlink($tmp);
            $map[$url] = array('', 'rename-failed');
            $failed++;
            fwrite($log, "rename-failed\t$url\t$target_abs\n");
            continue;
        }

        $map[$url] = array($target_rel, 'downloaded');
        $downloaded++;
        $bytes += $size;
        continue;
    }

    // Name clash: keep one copy when the bytes match, otherwise store the
    // newcomer under a content-hash name. Nothing is ever overwritten.
    if (!isset($hash_cache[$target_abs])) {
        $hash_cache[$target_abs] = md5_file($target_abs);
    }

    $new_hash = md5_file($tmp);

    if ($new_hash === $hash_cache[$target_abs]) {
        @unlink($tmp);
        $map[$url] = array($target_rel, 'reused');
        $reused++;
        continue;
    }

    $alt_rel = suffix_name($target_rel, '-' . substr($new_hash, 0, 8));
    $alt_abs = $root . '/html/' . $alt_rel;

    if (is_file($alt_abs)) {
        @unlink($tmp);
        $map[$url] = array($alt_rel, 'reused');
        $reused++;
        fwrite($log, "name-clash-resolved\t$url\t$alt_rel\n");
        continue;
    }

    if (rename($tmp, $alt_abs)) {
        $map[$url] = array($alt_rel, 'downloaded');
        $downloaded++;
        $bytes += $size;
        fwrite($log, "name-clash-resolved\t$url\t$alt_rel\n");
    } else {
        @unlink($tmp);
        $map[$url] = array('', 'rename-failed');
        $failed++;
        fwrite($log, "rename-failed\t$url\t$alt_abs\n");
    }
}

fclose($log);

// Preserve entries from earlier runs that this run did not revisit.
foreach ($done_before as $url => $path) {
    if (!isset($map[$url])) {
        $map[$url] = array($path, 'exists');
    }
}

write_map($map_file, $map);

echo "\ndownloaded: $downloaded\nreused    : $reused\nskipped   : $skipped\nfailed    : $failed\n";
echo 'volume    : ' . round($bytes / 1048576) . " MB\n";
echo "map       : $map_file\nfailures  : $log_file\n";
