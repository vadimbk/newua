<?php
/**
 * Import INTERTOOL products that exist in the supplier feed but on neither site.
 *
 * Conventions are copied from the August 2026 import (product_id 7810100..7810349),
 * verified against those rows: names, meta templates, tags, seo keywords
 * (seo_url_generator translit), images, localised description images,
 * attributes in group 8, -10% special for customer groups 1 and 2.
 *
 * Commands (run with /usr/local/lsws/lsphp74/bin/php):
 *
 *   build      --ua=<xml> --ru=<xml> --prod-config=<config.php> --dev-config=<config.php>
 *              --root=<site root that receives the images> --out=<dir>
 *              [--overrides=<json>] [--id-start=7812001]
 *              Reads both DBs (read-only), downloads images into --root, writes
 *              <out>/payload.json, <out>/files.json and <out>/preview.csv. No DB writes.
 *
 *   sync-files --manifest=<out>/files.json --from=<root> --to=<root> [--dry-run]
 *              Copies the downloaded images to another site root. Never overwrites a
 *              different file; aborts on any content conflict before copying anything.
 *
 *   apply      --config=<config.php> --payload=<json> --journal=<json>
 *              [--assign-sku] [--dry-run]
 *              Inserts the products in one transaction. --assign-sku allocates the
 *              internal sku from this DB at apply time (use on prod) and writes
 *              <payload>.applied.json with the final values.
 *
 *   rollback   --config=<config.php> --journal=<json> [--dry-run]
 *              Deletes exactly what apply inserted, using the journal.
 *
 *   restore-names --config=<config.php> --data=<json> --journal=<json> [--dry-run]
 *   unhook        --config=<config.php> --product-id=<id> --expect-upc=<upc> --journal=<json> [--dry-run]
 *              Small guarded fixes on existing cards (see the functions below).
 */

error_reporting(E_ALL);
ini_set('memory_limit', '2048M');
mb_internal_encoding('UTF-8');

define('UPC_PREFIX', 'INT-');
define('RATE', 45.0);
define('ATTR_GROUP', 8);
define('LANG_UK', 3);
define('LANG_RU', 2);
define('MANUFACTURER_ID', 92);
define('STOCK_STATUS_ID', 5);
define('IN_STOCK_QTY', 100);
define('SPECIAL_RATIO', 0.9);
define('SPECIAL_GROUP_ID', 18);
define('SPECIAL_CUSTOMER_GROUPS', '1,2');
define('IMG_PRODUCT_DIR', 'catalog/product/intertool/');
define('IMG_DESC_DIR', 'catalog/desc/INTERTOOL/');
define('SUPPLIER_HOST', 's3.intertool.ua');

$TRANSLIT_DIR = dirname(__DIR__) . '/html/system/library/seo_url_generator/translit/';
require $TRANSLIT_DIR . 'ukr.php';
require $TRANSLIT_DIR . 'rus.php';

function fail($msg) { fwrite(STDERR, "ERROR: $msg\n"); exit(1); }
function say($msg) { echo $msg, "\n"; }

function opts() {
    global $argv;
    $o = array('_cmd' => isset($argv[1]) ? $argv[1] : '');
    foreach (array_slice($argv, 2) as $a) {
        if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m)) $o[$m[1]] = isset($m[2]) ? $m[2] : true;
        else fail("bad argument: $a");
    }
    return $o;
}
function need($o, $k) { if (!isset($o[$k]) || $o[$k] === true || $o[$k] === '') fail("--$k is required"); return $o[$k]; }

/** Connect using an OpenCart config.php without defining its constants globally. */
function db_connect($config, $read_only) {
    $src = file_get_contents($config);
    $c = array();
    foreach (array('DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_PORT', 'DB_PREFIX') as $k) {
        if (!preg_match("/define\\(\\s*'$k'\\s*,\\s*'?([^')]*)'?\\s*\\)/", $src, $m)) fail("$k not found in $config");
        $c[$k] = $m[1];
    }
    if ($c['DB_PREFIX'] !== 'oc_') fail('unexpected DB_PREFIX');
    $m = new mysqli($c['DB_HOSTNAME'], $c['DB_USERNAME'], $c['DB_PASSWORD'], $c['DB_DATABASE'], (int)$c['DB_PORT']);
    if ($m->connect_errno) fail('DB connect: ' . $m->connect_error);
    $m->set_charset('utf8mb4');
    if ($read_only) q($m, 'SET SESSION TRANSACTION READ ONLY');
    $m->db_name = $c['DB_DATABASE'];
    return $m;
}
function q($m, $sql) {
    $r = $m->query($sql);
    if ($r === false) throw new RuntimeException($m->error . ' | ' . mb_substr($sql, 0, 300));
    return $r;
}
function rows($m, $sql) { return q($m, $sql)->fetch_all(MYSQLI_ASSOC); }
function e($m, $v) { return "'" . $m->real_escape_string((string)$v) . "'"; }

// ---------------------------------------------------------------- feed

function parse_feed($file) {
    $r = new XMLReader();
    if (!$r->open($file, null, LIBXML_NONET | LIBXML_PARSEHUGE)) fail("cannot open $file");
    $out = array();
    while ($r->read()) {
        if ($r->nodeType != XMLReader::ELEMENT || $r->name != 'offer') continue;
        $x = simplexml_load_string($r->readOuterXml(), null, LIBXML_NONET | LIBXML_PARSEHUGE);
        $sku = trim((string)$x->vendorCode);
        if ($sku === '') continue;
        if (isset($out[$sku])) { $out[$sku]['cats'][] = (string)$x->categoryId; continue; }
        $params = array();
        foreach ($x->param as $p) $params[] = array('name' => trim((string)$p['name']), 'unit' => trim((string)$p['unit']), 'value' => trim((string)$p));
        $pics = array();
        foreach ($x->picture as $p) $pics[] = trim((string)$p);
        $out[$sku] = array(
            'sku' => $sku, 'avail' => (string)$x['available'] === 'true', 'price' => (float)$x->price,
            'brand' => trim((string)$x->vendor), 'name' => trim(preg_replace('/\s+/u', ' ', (string)$x->name)),
            'description' => (string)$x->description, 'params' => $params, 'pics' => $pics,
            'cats' => array((string)$x->categoryId),
        );
    }
    return $out;
}

// ---------------------------------------------------------------- text helpers

function slug($name, $fn) {
    $s = $fn(html_entity_decode($name, ENT_QUOTES, 'UTF-8'));
    $s = preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($s));
    return trim($s, '-');
}
function make_tags($name, $category) {
    $out = array();
    foreach (preg_split('/[\s,]+/u', mb_strtolower(html_entity_decode($name, ENT_QUOTES, 'UTF-8'))) as $w) {
        $w = trim($w, '()');
        if (mb_strlen($w) < 3 || in_array($w, $out, true)) continue;
        $out[] = $w;
    }
    $c = mb_strtolower($category);
    if (!in_array($c, $out, true)) $out[] = $c;
    return implode(', ', $out);
}
function cut($s, $n) { return mb_substr($s, 0, $n); }
function full_name($feed_name, $sku, $brand) {
    $name = trim(preg_replace('/\s+/u', ' ', $feed_name));
    return $brand === 'NONAME (INTERTOOL)' ? "$name $sku" : "$name INTERTOOL $sku";
}
function meta_title($name, $sku, $lang) {
    return cut($lang == LANG_UK
        ? "$name - купити електротехніку у Києві, доставка по Україні, продаж в інтернет магазині Radio-Shop | $sku"
        : "$name - купить электротехнику в Киеве, доставка по Украине, продажа в интернет магазине Radio-Shop | $sku", 255);
}
function meta_description($name, $category, $lang) {
    return cut($lang == LANG_UK
        ? "$name купити за найкращою ціною. $category. ✈ Доставка по Києву та Україні. Гарантія"
        : "$name купить по лучшей цене. $category. ✈ Доставка по Киеву и Украине. Гарантия", 255);
}

// ---------------------------------------------------------------- images

function http_get($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 120, CURLOPT_USERAGENT => 'Mozilla/5.0'));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($body !== false && $code == 200 && strlen($body) > 0) ? $body : null;
}
function is_image_blob($blob) { return @getimagesizefromstring($blob) !== false; }
function supplier_path($url) {
    $p = parse_url($url);
    if (!$p || !isset($p['host']) || strcasecmp($p['host'], SUPPLIER_HOST) !== 0 || empty($p['path'])) return null;
    return ltrim(rawurldecode($p['path']), '/');
}
/** Product photo: mirror the supplier path, '+' becomes '-' (August convention). */
function product_image_rel($url) {
    $path = supplier_path($url);
    if ($path === null) return null;
    return IMG_PRODUCT_DIR . str_replace('+', '-', $path);
}
function suffix_name($name, $suffix) {
    $dot = strrpos($name, '.');
    return $dot === false ? $name . $suffix : substr($name, 0, $dot) . $suffix . substr($name, $dot);
}
function write_file($abs, $blob) {
    $dir = dirname($abs);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) fail("mkdir $dir");
    $tmp = "$abs.tmp" . getmypid();
    if (file_put_contents($tmp, $blob) !== strlen($blob)) fail("write $tmp");
    rename($tmp, $abs);
}
/**
 * Store a photo at its mirrored path. An existing file is kept as is (same path
 * means the same supplier file). Returns the relative path or null on failure.
 */
function fetch_product_image($url, $root, &$files, &$warn) {
    $rel = product_image_rel($url);
    if ($rel === null) { $warn[] = "not a supplier image url: $url"; return null; }
    $abs = "$root/image/$rel";
    if (!is_file($abs)) {
        $blob = http_get($url);
        if ($blob === null || !is_image_blob($blob)) { $warn[] = "download failed: $url"; return null; }
        write_file($abs, $blob);
    }
    $files[$rel] = md5_file($abs);
    return $rel;
}
/**
 * Description image: image/catalog/desc/INTERTOOL/<basename>. A different file with
 * the same name gets a content-hash suffix, nothing is overwritten (same rule as
 * scripts/localize_desc_images.php).
 */
function fetch_desc_image($url, $root, &$files, &$warn, &$cache) {
    if (isset($cache[$url])) return $cache[$url];
    $path = supplier_path($url);
    if ($path === null) return $cache[$url] = null;
    $blob = http_get($url);
    if ($blob === null || !is_image_blob($blob)) { $warn[] = "description image download failed: $url"; return $cache[$url] = null; }
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($path));
    $rel = IMG_DESC_DIR . $name;
    $abs = "$root/image/$rel";
    $hash = md5($blob);
    if (is_file($abs) && md5_file($abs) !== $hash) {
        $rel = IMG_DESC_DIR . suffix_name($name, '-' . substr($hash, 0, 8));
        $abs = "$root/image/$rel";
    }
    if (!is_file($abs)) write_file($abs, $blob);
    $files[$rel] = $hash;
    return $cache[$url] = $rel;
}

/** Strip the supplier stylesheet, localise images, unwrap links to other sites. */
function clean_description($html, $root, &$files, &$warn, &$cache, &$stat) {
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html);
    $html = preg_replace_callback('#(<img\b[^>]*?\bsrc=")([^"]+)(")#i', function ($m) use ($root, &$files, &$warn, &$cache, &$stat) {
        $rel = fetch_desc_image($m[2], $root, $files, $warn, $cache);
        if ($rel === null) { $stat['external_img']++; return $m[0]; }
        $stat['local_img']++;
        return $m[1] . '/image/' . $rel . $m[3];
    }, $html);
    if ($html === null) fail('preg_replace_callback failed on a description');
    $html = preg_replace_callback('#<a\b[^>]*\bhref="https?://[^"]*"[^>]*>(.*?)</a>#is', function ($m) use (&$stat) {
        $stat['unwrapped_links']++;
        return $m[1];
    }, $html);
    $html = preg_replace("/\n[ \t]*(\n[ \t]*)+/", "\n", $html);
    return trim($html);
}

// ---------------------------------------------------------------- build

function linked_skus($m) {
    $have = array();
    foreach (rows($m, "SELECT upc, model, sku, mpn FROM oc_product WHERE upc LIKE '%INT-%' OR model <> '' OR mpn <> ''") as $r) {
        foreach (explode(',', $r['upc']) as $t) if (preg_match('/^(?:1-?)?INT-(.+)$/i', trim($t), $mm)) $have[trim($mm[1])] = true;
        foreach (array('model', 'mpn') as $f) if ($r[$f] !== '') $have[trim($r[$f])] = true;
    }
    return $have;
}
function category_votes($m, $feed) {
    $vote = array();
    foreach (rows($m, "SELECT p.upc, pc.category_id FROM oc_product p JOIN oc_product_to_category pc ON pc.product_id=p.product_id
                       JOIN oc_category c ON c.category_id=pc.category_id AND c.status=1 WHERE p.upc LIKE 'INT-%'") as $r) {
        $sku = substr($r['upc'], strlen(UPC_PREFIX));
        if (!isset($feed[$sku])) continue;
        foreach (array_unique($feed[$sku]['cats']) as $fc) @$vote[$fc][$r['category_id']]++;
    }
    foreach ($vote as $fc => $v) arsort($vote[$fc]);
    return $vote;
}
function category_names($m) {
    $n = array();
    foreach (rows($m, "SELECT d.category_id, d.language_id, d.name, c.status FROM oc_category_description d JOIN oc_category c ON c.category_id=d.category_id") as $r)
        $n[$r['category_id']][$r['language_id']] = array('name' => $r['name'], 'status' => (int)$r['status']);
    return $n;
}
function taken_keywords($m) {
    $k = array();
    foreach (rows($m, "SELECT keyword FROM oc_seo_url") as $r) $k[mb_strtolower($r['keyword'])] = true;
    return $k;
}
function numeric_sku_max($m) {
    $r = rows($m, "SELECT MAX(CAST(sku AS UNSIGNED)) mx FROM oc_product WHERE sku REGEXP '^[0-9]+$' AND CAST(sku AS UNSIGNED) < 100000");
    return (int)$r[0]['mx'];
}
function intertool_special_end($m) {
    $r = rows($m, "SELECT ps.date_end, COUNT(*) c FROM oc_product_special ps JOIN oc_product p ON p.product_id=ps.product_id
                   WHERE p.upc LIKE 'INT-%' AND ps.product_special_group_id=" . SPECIAL_GROUP_ID . " GROUP BY ps.date_end ORDER BY c DESC LIMIT 1");
    return $r ? $r[0]['date_end'] : null;
}
function to_float($s) { return (float)str_replace(array(' ', ','), array('', '.'), $s); }

function cmd_build($o) {
    $ua = parse_feed(need($o, 'ua'));
    $ru = parse_feed(need($o, 'ru'));
    $root = rtrim(need($o, 'root'), '/');
    $out = rtrim(need($o, 'out'), '/');
    if (!is_dir("$root/image")) fail("$root/image not found");
    if (!is_dir($out) && !mkdir($out, 0755, true)) fail("mkdir $out");
    $ov = isset($o['overrides']) ? json_decode(file_get_contents($o['overrides']), true) : array();
    if (isset($o['overrides']) && !is_array($ov)) fail('overrides: invalid JSON');
    $id = isset($o['id-start']) ? (int)$o['id-start'] : 7812001;

    $prod = db_connect(need($o, 'prod-config'), true);
    $dev = db_connect(need($o, 'dev-config'), true);
    $have_p = linked_skus($prod);
    $have_d = linked_skus($dev);
    $missing = array();
    foreach ($ua as $sku => $f) if (!isset($have_p[$sku]) && !isset($have_d[$sku])) $missing[] = $sku;
    sort($missing, SORT_STRING);
    say('feed=' . count($ua) . ' missing on both sites=' . count($missing));

    $vote = category_votes($prod, $ua);
    $cn_p = category_names($prod);
    $cn_d = category_names($dev);
    $kw = taken_keywords($prod) + taken_keywords($dev);
    $sku_next = numeric_sku_max($prod) + 1;
    $special_end = intertool_special_end($prod);
    if (!$special_end) fail('cannot determine special date_end on prod');

    $products = array(); $files = array(); $warn = array(); $cache = array();
    $stat = array('local_img' => 0, 'external_img' => 0, 'unwrapped_links' => 0);
    foreach ($missing as $sku) {
        $f = $ua[$sku];
        $r = isset($ru[$sku]) ? $ru[$sku] : null;
        if (!$r) { $warn[] = "$sku: missing in ru feed, skipped"; continue; }
        $x = isset($ov[$sku]) ? $ov[$sku] : array();
        $flags = array();

        // category
        $cat = null; $cat_src = '';
        if (!empty($x['category_id'])) { $cat = (int)$x['category_id']; $cat_src = 'override'; }
        else foreach (array_unique($f['cats']) as $fc) if (!empty($vote[$fc])) {
            $cat = (int)key($vote[$fc]);
            $share = reset($vote[$fc]) / array_sum($vote[$fc]);
            $cat_src = sprintf('vote %d/%d', reset($vote[$fc]), array_sum($vote[$fc]));
            if ($share < 0.7) $flags[] = 'category: weak vote';
            break;
        }
        if (!$cat) { $warn[] = "$sku: no category, skipped"; continue; }
        foreach (array('prod' => $cn_p, 'dev' => $cn_d) as $env => $cn)
            if (empty($cn[$cat][LANG_UK]) || !$cn[$cat][LANG_UK]['status']) fail("$sku: category $cat missing or disabled on $env");

        // names
        $base_uk = isset($x['name_uk']) ? $x['name_uk'] : $f['name'];
        $base_ru = isset($x['name_ru']) ? $x['name_ru'] : $r['name'];
        if (isset($x['name_uk'])) $flags[] = 'name uk: corrected';
        if (isset($x['name_ru'])) $flags[] = 'name ru: corrected';
        $name_uk = cut(full_name($base_uk, $sku, $f['brand']), 255);
        $name_ru = cut(full_name($base_ru, $sku, $f['brand']), 255);

        // descriptions
        // an override description is a translation of the supplier text; it goes through the same cleaning
        if (isset($x['description_uk'])) $flags[] = 'description uk: translated';
        if (isset($x['description_ru'])) $flags[] = 'description ru: translated';
        $desc_uk = clean_description(isset($x['description_uk']) ? $x['description_uk'] : $f['description'], $root, $files, $warn, $cache, $stat);
        $desc_ru = clean_description(isset($x['description_ru']) ? $x['description_ru'] : $r['description'], $root, $files, $warn, $cache, $stat);
        if (trim(strip_tags($desc_uk)) === '' && strpos($desc_uk, '<img') === false) $flags[] = 'description uk: EMPTY';
        if (trim(strip_tags($desc_ru)) === '' && strpos($desc_ru, '<img') === false) $flags[] = 'description ru: EMPTY';

        // images: first picture is the main image, the rest go to the gallery
        $imgs = array();
        foreach ($f['pics'] as $u) { $rel = fetch_product_image($u, $root, $files, $warn); if ($rel !== null) $imgs[] = $rel; }
        $imgs = array_values(array_unique($imgs));
        if (!$imgs) $flags[] = 'images: NONE';

        // attributes: ua/ru params line up by position
        $attrs = array(); $weight = 0.0;
        if (count($f['params']) != count($r['params'])) $flags[] = 'attributes: ua/ru count differs, ru skipped';
        foreach ($f['params'] as $i => $p) {
            if ($p['value'] === '') continue;
            $pr = (count($f['params']) == count($r['params'])) ? $r['params'][$i] : $p;
            $nu = cut($p['name'] . ($p['unit'] !== '' ? ', ' . mb_strtolower($p['unit']) : ''), 64);
            $nr = cut($pr['name'] . ($pr['unit'] !== '' ? ', ' . mb_strtolower($pr['unit']) : ''), 64);
            $attrs[] = array('name_uk' => $nu, 'name_ru' => $nr, 'text_uk' => $p['value'], 'text_ru' => $pr['value'] !== '' ? $pr['value'] : $p['value']);
            if ($p['name'] === 'Вага') $weight = to_float($p['value']);
        }

        // seo keywords, unique across both DBs and this batch; the uk and ru keyword of the
        // same product may be identical (1709 prod products already do that)
        $keys = array();
        foreach (array(LANG_UK => array($name_uk, 'sug_translit_ukr'), LANG_RU => array($name_ru, 'sug_translit_rus')) as $lang => $a) {
            $k = cut(slug($a[0], $a[1]), 250); $base = $k; $n = 2;
            if ($lang == LANG_RU && $k === $keys[LANG_UK]) { $keys[$lang] = $k; continue; }
            while (isset($kw[$k])) { $k = "$base-$n"; $n++; }
            if ($k !== $base) $flags[] = "seo keyword ($lang): suffixed";
            $kw[$k] = true; $keys[$lang] = $k;
        }

        $price = round($f['price'] / RATE, 4);
        $products[] = array(
            'product_id' => $id++, 'sku' => (string)$sku_next++, 'upc' => UPC_PREFIX . $sku, 'supplier_sku' => $sku,
            'quantity' => $f['avail'] ? IN_STOCK_QTY : 0, 'price' => $price, 'price_uah' => $f['price'],
            'special' => round($price * SPECIAL_RATIO, 2), 'special_end' => $special_end,
            'weight' => $weight, 'image' => $imgs ? $imgs[0] : '', 'gallery' => array_slice($imgs, 1),
            'category_id' => $cat, 'category_src' => $cat_src,
            'category_uk' => $cn_p[$cat][LANG_UK]['name'], 'category_ru' => isset($cn_p[$cat][LANG_RU]) ? $cn_p[$cat][LANG_RU]['name'] : $cn_p[$cat][LANG_UK]['name'],
            'name_uk' => $name_uk, 'name_ru' => $name_ru, 'description_uk' => $desc_uk, 'description_ru' => $desc_ru,
            'keyword_uk' => $keys[LANG_UK], 'keyword_ru' => $keys[LANG_RU], 'attributes' => $attrs, 'flags' => $flags,
        );
    }

    file_put_contents("$out/payload.json", json_encode(array('built' => date('c'), 'products' => $products), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    ksort($files);
    file_put_contents("$out/files.json", json_encode($files, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $fh = fopen("$out/preview.csv", 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, array('supplier_sku', 'product_id', 'sku', 'name_uk', 'name_ru', 'category_id', 'category', 'category_src', 'price_uah', 'special_uah',
        'quantity', 'images', 'attributes', 'desc_uk_chars', 'keyword_uk', 'keyword_ru', 'flags'), ';');
    foreach ($products as $p) fputcsv($fh, array($p['supplier_sku'], $p['product_id'], $p['sku'], $p['name_uk'], $p['name_ru'], $p['category_id'], $p['category_uk'],
        $p['category_src'], $p['price_uah'], round($p['special'] * RATE), $p['quantity'], ($p['image'] ? 1 : 0) + count($p['gallery']), count($p['attributes']),
        mb_strlen(trim(strip_tags($p['description_uk']))), $p['keyword_uk'], $p['keyword_ru'], implode('; ', $p['flags'])), ';');
    fclose($fh);
    file_put_contents("$out/warnings.txt", implode("\n", $warn) . "\n");
    say('products=' . count($products) . ' files=' . count($files) . ' warnings=' . count($warn));
    say('description images: local=' . $stat['local_img'] . ' external(kept)=' . $stat['external_img'] . ' unwrapped links=' . $stat['unwrapped_links']);
    say("special date_end=$special_end  provisional sku from " . ($sku_next - count($products)));
}

// ---------------------------------------------------------------- sync-files

function cmd_sync_files($o) {
    $files = json_decode(file_get_contents(need($o, 'manifest')), true);
    $from = rtrim(need($o, 'from'), '/');
    $to = rtrim(need($o, 'to'), '/');
    $dry = isset($o['dry-run']);
    $copy = array(); $same = 0; $conflict = array();
    foreach ($files as $rel => $hash) {
        $src = "$from/image/$rel"; $dst = "$to/image/$rel";
        if (!is_file($src) || md5_file($src) !== $hash) fail("source missing or changed: $src");
        if (is_file($dst)) { if (md5_file($dst) === $hash) $same++; else $conflict[] = $rel; }
        else $copy[] = $rel;
    }
    if ($conflict) fail("different files already exist at the target, nothing copied:\n  " . implode("\n  ", $conflict));
    say('to copy=' . count($copy) . ' identical already=' . $same . ($dry ? ' (dry run)' : ''));
    if ($dry) return;
    foreach ($copy as $rel) {
        $dst = "$to/image/$rel";
        if (!is_dir(dirname($dst)) && !mkdir(dirname($dst), 0755, true)) fail('mkdir ' . dirname($dst));
        if (!copy("$from/image/$rel", $dst)) fail("copy $rel");
    }
    say('copied ' . count($copy));
}

// ---------------------------------------------------------------- apply / rollback

function resolve_attribute($m, $a, &$created, $dry) {
    static $cache = array();
    $key = $m->db_name . '|' . $a['name_uk'];
    if (isset($cache[$key])) return $cache[$key];
    $r = rows($m, "SELECT a.attribute_id FROM oc_attribute a JOIN oc_attribute_description d ON d.attribute_id=a.attribute_id AND d.language_id=" . LANG_UK . "
                   WHERE a.attribute_group_id=" . ATTR_GROUP . " AND d.name=" . e($m, $a['name_uk']) . " ORDER BY a.attribute_id LIMIT 1");
    if ($r) return $cache[$key] = (int)$r[0]['attribute_id'];
    if ($dry) { $created[] = array('attribute_id' => 0, 'name_uk' => $a['name_uk']); return $cache[$key] = 0; }
    q($m, "INSERT INTO oc_attribute SET attribute_group_id=" . ATTR_GROUP . ", sort_order=0");
    $aid = (int)$m->insert_id;
    q($m, "INSERT INTO oc_attribute_description (attribute_id, language_id, name) VALUES ($aid, " . LANG_UK . ", " . e($m, $a['name_uk']) . "), ($aid, " . LANG_RU . ", " . e($m, $a['name_ru']) . ")");
    $created[] = array('attribute_id' => $aid, 'name_uk' => $a['name_uk']);
    return $cache[$key] = $aid;
}

function cmd_apply($o) {
    $payload_file = need($o, 'payload');
    $journal_file = need($o, 'journal');
    $dry = isset($o['dry-run']);
    if (!$dry && file_exists($journal_file)) fail("journal $journal_file already exists");
    $P = json_decode(file_get_contents($payload_file), true);
    if (!$P || empty($P['products'])) fail('empty payload');
    $m = db_connect(need($o, 'config'), false);
    say('DB=' . $m->db_name . ($dry ? ' (dry run, rolled back at the end)' : ''));

    $ids = array_column($P['products'], 'product_id');
    $upcs = array_column($P['products'], 'upc');
    $created = array();
    q($m, 'START TRANSACTION');
    try {
        // guards
        $busy = rows($m, 'SELECT product_id FROM oc_product WHERE product_id IN (' . implode(',', array_map('intval', $ids)) . ')');
        if ($busy) throw new RuntimeException('product_id already used: ' . implode(',', array_column($busy, 'product_id')));
        foreach ($P['products'] as $p) {
            $s = $p['supplier_sku'];
            $hit = rows($m, "SELECT product_id, upc FROM oc_product WHERE upc IN (" . e($m, "INT-$s") . ',' . e($m, "1INT-$s") . ',' . e($m, "1-INT-$s") . ") OR FIND_IN_SET(" . e($m, "INT-$s") . ", REPLACE(upc, ' ', ''))");
            if ($hit) throw new RuntimeException("$s already on this site as product_id " . $hit[0]['product_id']);
            foreach (array_unique(array($p['keyword_uk'], $p['keyword_ru'])) as $k)
                if (rows($m, 'SELECT seo_url_id FROM oc_seo_url WHERE keyword=' . e($m, $k))) throw new RuntimeException("seo keyword taken: $k");
            if (!rows($m, 'SELECT category_id FROM oc_category WHERE status=1 AND category_id=' . (int)$p['category_id'])) throw new RuntimeException("category {$p['category_id']} missing/disabled");
        }
        if (isset($o['assign-sku'])) {
            q($m, 'LOCK TABLES oc_product WRITE'); // not transactional; released right after reading
            $next = numeric_sku_max($m) + 1;
            q($m, 'UNLOCK TABLES');
            q($m, 'START TRANSACTION');
            foreach ($P['products'] as $i => $p) $P['products'][$i]['sku'] = (string)$next++;
            say('sku assigned from ' . $P['products'][0]['sku'] . ' to ' . end($P['products'])['sku']);
        }
        foreach ($P['products'] as $p) {
            $pid = (int)$p['product_id'];
            q($m, "INSERT INTO oc_product SET product_id=$pid, model='', sku=" . e($m, $p['sku']) . ", upc=" . e($m, $p['upc']) . ", ean='', jan='', isbn='', mpn='', location='',
                quantity=" . (int)$p['quantity'] . ", stock_status_id=" . STOCK_STATUS_ID . ", image=" . e($m, $p['image']) . ", manufacturer_id=" . MANUFACTURER_ID . ",
                shipping=1, price=" . sprintf('%.4F', $p['price']) . ", points=0, tax_class_id=0, date_available=CURDATE(), weight=" . sprintf('%.8F', $p['weight']) . ",
                weight_class_id=1, length=0, width=0, height=0, length_class_id=0, subtract=0, minimum=1, sort_order=0, status=1,
                date_added=NOW(), date_modified=NOW(), unixml_feed='', unixml_link='', viewed=0, oct_stickers=''");
            foreach (array(LANG_UK => 'uk', LANG_RU => 'ru') as $lang => $l) {
                $name = $p["name_$l"]; $cat = $p["category_$l"];
                q($m, "INSERT INTO oc_product_description SET product_id=$pid, language_id=$lang, name=" . e($m, $name) . ", featured='', fimage='', video1='',
                    html_slick_listing='', html_product_right='', html_product_tab='', tab_title='', description=" . e($m, $p["description_$l"]) . ",
                    tag=" . e($m, make_tags($name, $cat)) . ", meta_title=" . e($m, meta_title($name, $p['sku'], $lang)) . ",
                    meta_description=" . e($m, meta_description($name, $cat, $lang)) . ", meta_keyword='', custom_alt='', custom_h1='', custom_h2='',
                    custom_imgtitle='', custom_title='', default_meta=0");
                q($m, "INSERT INTO oc_seo_url SET store_id=0, language_id=$lang, query='product_id=$pid', keyword=" . e($m, $p["keyword_$l"]));
            }
            q($m, "INSERT INTO oc_product_to_category SET product_id=$pid, category_id=" . (int)$p['category_id']);
            q($m, "INSERT INTO oc_product_to_store SET product_id=$pid, store_id=0");
            q($m, "INSERT INTO oc_product_to_layout SET product_id=$pid, store_id=0, layout_id=0");
            foreach ($p['gallery'] as $i => $img) q($m, "INSERT INTO oc_product_image SET product_id=$pid, image=" . e($m, $img) . ", sort_order=$i");
            foreach (explode(',', SPECIAL_CUSTOMER_GROUPS) as $cg)
                q($m, "INSERT INTO oc_product_special SET product_id=$pid, customer_group_id=" . (int)$cg . ", priority=0, price=" . sprintf('%.4F', $p['special']) . ",
                    date_start='0000-00-00', date_end=" . e($m, $p['special_end']) . ", timer=0, product_special_group_id=" . SPECIAL_GROUP_ID);
            $seen = array();
            foreach ($p['attributes'] as $a) {
                $aid = resolve_attribute($m, $a, $created, $dry);
                if ($aid && isset($seen[$aid])) continue; // same attribute twice in one card: keep the first
                $seen[$aid] = true;
                if (!$aid) continue;
                q($m, "INSERT INTO oc_product_attribute (product_id, attribute_id, language_id, text) VALUES
                    ($pid, $aid, " . LANG_UK . ", " . e($m, $a['text_uk']) . "), ($pid, $aid, " . LANG_RU . ", " . e($m, $a['text_ru']) . ")");
            }
        }
        // verification inside the transaction
        $in = implode(',', array_map('intval', $ids));
        $chk = rows($m, "SELECT (SELECT COUNT(*) FROM oc_product WHERE product_id IN ($in)) p,
            (SELECT COUNT(*) FROM oc_product_description WHERE product_id IN ($in)) d,
            (SELECT COUNT(*) FROM oc_seo_url WHERE query IN ('product_id=" . implode("','product_id=", array_map('intval', $ids)) . "')) s,
            (SELECT COUNT(*) FROM oc_product_to_category WHERE product_id IN ($in)) c,
            (SELECT COUNT(*) FROM oc_product_special WHERE product_id IN ($in)) sp,
            (SELECT COUNT(*) FROM oc_product_image WHERE product_id IN ($in)) i,
            (SELECT COUNT(*) FROM oc_product_attribute WHERE product_id IN ($in)) a")[0];
        $n = count($ids);
        say("inserted: products={$chk['p']} descriptions={$chk['d']} seo={$chk['s']} categories={$chk['c']} specials={$chk['sp']} gallery={$chk['i']} attribute rows={$chk['a']} new attributes=" . count($created));
        if ($chk['p'] != $n || $chk['d'] != 2 * $n || $chk['s'] != 2 * $n || $chk['c'] != $n || $chk['sp'] != 2 * $n) throw new RuntimeException('verification counts do not match');
        if ($dry) { q($m, 'ROLLBACK'); say('dry run: rolled back'); return; }
        $journal = array('db' => $m->db_name, 'applied' => date('c'), 'product_ids' => $ids, 'created_attributes' => $created, 'payload' => $payload_file);
        if (file_put_contents($journal_file, json_encode($journal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) throw new RuntimeException('cannot write journal');
        q($m, 'COMMIT');
        say("committed; journal $journal_file");
        if (isset($o['assign-sku'])) {
            file_put_contents($payload_file . '.applied.json', json_encode($P, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            say("final payload $payload_file.applied.json");
        }
    } catch (Exception $ex) {
        q($m, 'ROLLBACK');
        @q($m, 'UNLOCK TABLES');
        fail('rolled back: ' . $ex->getMessage());
    }
}

function cmd_rollback($o) {
    $J = json_decode(file_get_contents(need($o, 'journal')), true);
    if (!$J || empty($J['product_ids'])) fail('bad journal');
    $m = db_connect(need($o, 'config'), false);
    if ($m->db_name !== $J['db']) fail("journal is for {$J['db']}, config points to {$m->db_name}");
    $dry = isset($o['dry-run']);
    $in = implode(',', array_map('intval', $J['product_ids']));
    $seo = "'product_id=" . implode("','product_id=", array_map('intval', $J['product_ids'])) . "'";
    q($m, 'START TRANSACTION');
    try {
        $n = array();
        foreach (array('oc_product_attribute', 'oc_product_special', 'oc_product_image', 'oc_product_to_layout', 'oc_product_to_store',
                       'oc_product_to_category', 'oc_product_description', 'oc_product') as $t) {
            q($m, "DELETE FROM $t WHERE product_id IN ($in)"); $n[$t] = $m->affected_rows;
        }
        q($m, "DELETE FROM oc_seo_url WHERE query IN ($seo)"); $n['oc_seo_url'] = $m->affected_rows;
        $kept = 0; $removed = 0;
        foreach ($J['created_attributes'] as $a) {
            $aid = (int)$a['attribute_id'];
            if (!$aid) continue;
            if (rows($m, "SELECT 1 FROM oc_product_attribute WHERE attribute_id=$aid LIMIT 1")) { $kept++; continue; }
            q($m, "DELETE FROM oc_attribute_description WHERE attribute_id=$aid");
            q($m, "DELETE FROM oc_attribute WHERE attribute_id=$aid");
            $removed++;
        }
        foreach ($n as $t => $c) echo "$t=$c ";
        say("attributes removed=$removed kept(in use)=$kept");
        if ($dry) { q($m, 'ROLLBACK'); say('dry run: rolled back'); return; }
        q($m, 'COMMIT');
        say('committed');
    } catch (Exception $ex) {
        q($m, 'ROLLBACK');
        fail('rolled back: ' . $ex->getMessage());
    }
}

// ---------------------------------------------------------------- restore-names / unhook

/**
 * restore-names --config=<config.php> --data=<json> --journal=<json> [--dry-run]
 * data: [{"product_id":1,"language_id":3,"expect_name":"...","name":"..."}, ...]
 * Updates name, meta_title, meta_description and tag. The seo keyword is never touched.
 * Each row is updated only if its current name equals expect_name.
 */
function cmd_restore_names($o) {
    $data = json_decode(file_get_contents(need($o, 'data')), true);
    if (!$data) fail('bad data');
    $journal_file = need($o, 'journal');
    $dry = isset($o['dry-run']);
    if (!$dry && file_exists($journal_file)) fail("journal $journal_file already exists");
    $m = db_connect(need($o, 'config'), false);
    say('DB=' . $m->db_name . ($dry ? ' (dry run)' : ''));
    $before = array();
    q($m, 'START TRANSACTION');
    try {
        foreach ($data as $d) {
            $pid = (int)$d['product_id']; $lang = (int)$d['language_id'];
            $cur = rows($m, "SELECT d.name, d.meta_title, d.meta_description, d.tag, TRIM(p.sku) sku,
                (SELECT cd.name FROM oc_product_to_category pc JOIN oc_category_description cd ON cd.category_id=pc.category_id AND cd.language_id=$lang
                 WHERE pc.product_id=p.product_id ORDER BY pc.category_id LIMIT 1) cat
                FROM oc_product p JOIN oc_product_description d ON d.product_id=p.product_id AND d.language_id=$lang WHERE p.product_id=$pid FOR UPDATE");
            if (!$cur) throw new RuntimeException("product $pid lang $lang not found");
            $c = $cur[0];
            if ($c['name'] !== $d['expect_name']) throw new RuntimeException("product $pid lang $lang: name is '{$c['name']}', expected '{$d['expect_name']}'");
            $sku = preg_replace('/\s+/', '', $c['sku']);
            $name = $d['name'];
            q($m, "UPDATE oc_product_description SET name=" . e($m, $name) . ", meta_title=" . e($m, meta_title($name, $sku, $lang)) . ",
                meta_description=" . e($m, meta_description($name, (string)$c['cat'], $lang)) . ", tag=" . e($m, make_tags($name, (string)$c['cat'])) . "
                WHERE product_id=$pid AND language_id=$lang");
            if ($m->affected_rows != 1) throw new RuntimeException("product $pid lang $lang: update affected {$m->affected_rows} rows");
            $before[] = array('product_id' => $pid, 'language_id' => $lang, 'name' => $c['name'], 'meta_title' => $c['meta_title'], 'meta_description' => $c['meta_description'], 'tag' => $c['tag']);
            say("  $pid/$lang: '{$c['name']}' -> '$name'");
        }
        if ($dry) { q($m, 'ROLLBACK'); say('dry run: rolled back'); return; }
        if (file_put_contents($journal_file, json_encode(array('db' => $m->db_name, 'at' => date('c'), 'before' => $before), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) throw new RuntimeException('cannot write journal');
        q($m, 'COMMIT');
        say('committed; journal ' . $journal_file);
    } catch (Exception $ex) { q($m, 'ROLLBACK'); fail('rolled back: ' . $ex->getMessage()); }
}

/**
 * unhook --config=<config.php> --product-id=<id> --expect-upc=INT-XXX --journal=<json> [--dry-run]
 * Marks a card as deliberately unhooked (upc '1' + upc) and sets quantity 0, because no
 * sync touches an unhooked card and it would otherwise stay "in stock" forever.
 */
function cmd_unhook($o) {
    $pid = (int)need($o, 'product-id');
    $upc = need($o, 'expect-upc');
    $journal_file = need($o, 'journal');
    $dry = isset($o['dry-run']);
    if (!$dry && file_exists($journal_file)) fail("journal $journal_file already exists");
    $m = db_connect(need($o, 'config'), false);
    q($m, 'START TRANSACTION');
    try {
        $cur = rows($m, "SELECT upc, quantity FROM oc_product WHERE product_id=$pid FOR UPDATE");
        if (!$cur) throw new RuntimeException("product $pid not found");
        if ($cur[0]['upc'] !== $upc) throw new RuntimeException("product $pid upc is '{$cur[0]['upc']}', expected '$upc'");
        q($m, "UPDATE oc_product SET upc=" . e($m, '1' . $upc) . ", quantity=0, date_modified=NOW() WHERE product_id=$pid");
        say("DB={$m->db_name} $pid: upc $upc -> 1$upc, quantity {$cur[0]['quantity']} -> 0" . ($dry ? ' (dry run)' : ''));
        if ($dry) { q($m, 'ROLLBACK'); return; }
        if (file_put_contents($journal_file, json_encode(array('db' => $m->db_name, 'at' => date('c'), 'product_id' => $pid, 'before' => $cur[0]), JSON_PRETTY_PRINT)) === false) throw new RuntimeException('cannot write journal');
        q($m, 'COMMIT');
        say('committed; journal ' . $journal_file);
    } catch (Exception $ex) { q($m, 'ROLLBACK'); fail('rolled back: ' . $ex->getMessage()); }
}

$o = opts();
switch ($o['_cmd']) {
    case 'build': cmd_build($o); break;
    case 'sync-files': cmd_sync_files($o); break;
    case 'apply': cmd_apply($o); break;
    case 'rollback': cmd_rollback($o); break;
    case 'restore-names': cmd_restore_names($o); break;
    case 'unhook': cmd_unhook($o); break;
    default: fail('command: build | sync-files | apply | rollback | restore-names | unhook');
}
