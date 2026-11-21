<?php
/**
 * Import new Grand Instrument products (upc GI-<article>) from the supplier's Prom feed.
 *
 * Which products: an explicit list (vendorCode,gi_brand) verified against grandinstrument.ua,
 * see save/gi_20261120/PLAN.md. Content (names, descriptions, pictures, params, categories) comes
 * from the Prom feed toptul.online, price/stock/purchase from the parser CSV 5_toptul.csv that the
 * prod cron (token toptul-23062026) uses, so the cron keeps the new cards updated afterwards.
 *
 * Conventions copied from the existing GI cards (2024 batch): name = feed name, model empty,
 * stock_status 5, subtract 0, minimum 1, store 0, layout 0, one category, photos in
 * catalog/product/Toptul/<prom file name>, attributes in group 8, no special price.
 * Decided for the new cards (PLAN.md): Intertool meta_description template, description images
 * localised into catalog/desc/GRANDINSTRUMENT/, UK attribute text = RU text when it has no Cyrillic.
 *
 * Commands (run with /usr/local/lsws/lsphp74/bin/php):
 *
 *   build      --feed=<prom xml> --csv=<5_toptul.csv> --list=<csv vendorCode,gi_brand>
 *              --prod-config=<config.php> --dev-config=<config.php> --root=<site root for images> --out=<dir>
 *              [--categories=<json {feed_category_id: site_category_id}>] [--id-start=7813001] [--no-images]
 *              Reads both DBs read-only, downloads images into --root, writes payload.json, files.json,
 *              preview.csv, skipped.csv, category_todo.csv, warnings.txt. No DB writes.
 *
 *   sync-files --manifest=<files.json> --from=<root> --to=<root> --created-log=<file> [--dry-run]
 *              Copies images to another site root; never overwrites a different file; logs what it created.
 *
 *   remove-files --created-log=<file> --root=<root> [--dry-run]
 *              Deletes exactly the files a sync-files run created (used if apply fails or is rolled back).
 *
 *   apply      --config=<config.php> --payload=<json> --journal=<json> [--assign-sku] [--dry-run]
 *              Inserts the products in one transaction.
 *
 *   rollback   --config=<config.php> --journal=<json> [--dry-run]
 *              Deletes exactly what apply inserted.
 */

error_reporting(E_ALL);
ini_set('memory_limit', '3072M');
mb_internal_encoding('UTF-8');

define('UPC_PREFIX', 'GI-');
define('RATE', 45.0);
define('ATTR_GROUP', 8);
define('LANG_UK', 3);
define('LANG_RU', 2);
define('STOCK_STATUS_ID', 5);
define('IMG_PRODUCT_DIR', 'catalog/product/Toptul/');
define('IMG_DESC_DIR', 'catalog/desc/GRANDINSTRUMENT/');

// brand as shown on grandinstrument.ua -> manufacturer name on the site (resolved to an id per DB)
$BRAND_MANUFACTURER = array(
    'Toptul' => 'Toptul', 'Airkraft' => 'Airkraft', 'Standart' => 'Стандарт', 'Torin' => 'TORIN', 'Protester' => 'PROTESTER',
    'G.I.Kraft' => 'G.I.Kraft', 'Launch' => 'Launch', 'Україна' => 'Україна', 'Bright' => 'BRIGHT', 'Wintact' => 'wintact',
    'Benetech' => 'Benetech', 'Auarita' => 'Auarita', 'Aeropro' => 'Aeropro', 'Trisco' => 'Trisco', 'ХЗСО' => 'ХЗСО',
    'Fiac' => 'Fiac', 'Italco' => 'Italco', 'Powerlift' => 'Powerlift', 'Spin' => 'Spin',
);

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

// ---------------------------------------------------------------- inputs

function parse_feed($file) {
    $r = new XMLReader();
    if (!$r->open($file, null, LIBXML_NONET | LIBXML_PARSEHUGE)) fail("cannot open $file");
    $out = array(); $cats = array();
    while ($r->read()) {
        if ($r->nodeType != XMLReader::ELEMENT) continue;
        if ($r->name == 'category') {
            $x = simplexml_load_string($r->readOuterXml());
            $cats[(string)$x['id']] = array('name' => trim((string)$x), 'parent' => (string)$x['parentId']);
            continue;
        }
        if ($r->name != 'offer') continue;
        $x = simplexml_load_string($r->readOuterXml(), null, LIBXML_NONET | LIBXML_PARSEHUGE | LIBXML_NOCDATA);
        $code = trim((string)$x->vendorCode);
        if ($code === '' || isset($out[$code])) continue;
        $params = array();
        foreach ($x->param as $p) $params[] = array('name' => trim((string)$p['name']), 'unit' => trim((string)$p['unit']), 'value' => trim((string)$p));
        $pics = array();
        foreach ($x->picture as $p) $pics[] = trim((string)$p);
        $out[$code] = array(
            'code' => $code, 'category' => (string)$x->categoryId, 'vendor' => trim((string)$x->vendor),
            'name_ru' => trim(preg_replace('/\s+/u', ' ', (string)$x->name)),
            'name_uk' => trim(preg_replace('/\s+/u', ' ', (string)$x->name_ua)),
            'description_ru' => (string)$x->description, 'description_uk' => (string)$x->description_ua,
            'params' => $params, 'pics' => $pics,
        );
    }
    return array($out, $cats);
}
function parse_csv($file) {
    $f = fopen($file, 'r');
    if (!$f) fail("cannot open $file");
    $h = fgetcsv($f, 0, ';');
    if ($h !== array('sku', 'price', 'stock', 'purchase', 'vat')) fail('unexpected CSV header: ' . implode(';', (array)$h));
    $out = array();
    while ($r = fgetcsv($f, 0, ';')) if (count($r) >= 4) $out[(string)$r[0]] = array('price' => (float)$r[1], 'stock' => (int)$r[2], 'purchase' => (float)$r[3]);
    return $out;
}
function parse_list($file) {
    $f = fopen($file, 'r');
    if (!$f) fail("cannot open $file");
    $h = fgetcsv($f);
    if ($h !== array('vendorCode', 'gi_brand')) fail('unexpected list header');
    $out = array();
    while ($r = fgetcsv($f)) $out[(string)$r[0]] = $r[1];
    return $out;
}

// ---------------------------------------------------------------- text helpers

function cut($s, $n) { return mb_substr($s, 0, $n); }
function slug($name, $fn) {
    $s = $fn(html_entity_decode($name, ENT_QUOTES, 'UTF-8'));
    $s = preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($s));
    return trim($s, '-');
}
/** Existing GI template for meta_title (no defect there). */
function meta_title($name, $lang) {
    return cut($lang == LANG_UK ? "$name - купити за найнижчими цінами в Україні" : "$name - купить по самым низким ценам в Украине", 255);
}
/** Intertool template (decision 1 in PLAN.md). */
function meta_description($name, $category, $lang) {
    return cut($lang == LANG_UK
        ? "$name купити за найкращою ціною. $category. ✈ Доставка по Києву та Україні. Гарантія"
        : "$name купить по лучшей цене. $category. ✈ Доставка по Киеву и Украине. Гарантия", 255);
}
/** Article normalised for duplicate search: upper case, Cyrillic look-alikes folded to Latin, only A-Z0-9. */
function norm_code($s) {
    $s = mb_strtoupper(html_entity_decode($s, ENT_QUOTES, 'UTF-8'));
    $s = strtr($s, array('А' => 'A', 'В' => 'B', 'С' => 'C', 'Е' => 'E', 'Н' => 'H', 'К' => 'K', 'М' => 'M', 'О' => 'O', 'Р' => 'P',
        'Т' => 'T', 'Х' => 'X', 'З' => '3', 'І' => 'I', 'У' => 'Y'));
    return preg_replace('/[^A-Z0-9\x{0400}-\x{04FF}]/u', '', $s);
}
function has_cyrillic($s) { return (bool)preg_match('/\p{Cyrillic}/u', $s); }

// ---------------------------------------------------------------- images

function http_get($url) {
    // allegroimg.com accepts the TLS handshake from this server but never answers (blocks data-centre traffic);
    // the same file comes through the DuckDuckGo image proxy unchanged. Verified 2026-11-21.
    if (preg_match('#^https?://[a-z0-9]+\.allegroimg\.com/#i', $url)) {
        usleep(300000);
        $url = 'https://external-content.duckduckgo.com/iu/?u=' . rawurlencode($url) . '&f=1';
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 120, CURLOPT_USERAGENT => 'Mozilla/5.0'));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($body !== false && $code == 200 && strlen($body) > 0) ? $body : null;
}
function is_image_blob($blob) { return @getimagesizefromstring($blob) !== false; }
/** OpenCart 3 thumbnails only work for JPEG, PNG and GIF (catalog/model/tool/image.php). */
function is_oc_image_file($abs) { $i = @getimagesize($abs); return $i !== false && in_array($i[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF)); }
/**
 * Prom serves part of its pictures as WebP under a .jpg name; OpenCart then shows no thumbnail.
 * Re-encode WebP to JPEG on a white background; returns null when the blob cannot be decoded.
 */
function to_oc_blob($blob) {
    $i = @getimagesizefromstring($blob);
    if ($i === false) return null;
    if (in_array($i[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) return $blob;
    if ($i[2] !== IMAGETYPE_WEBP || !($src = @imagecreatefromstring($blob))) return null;
    $w = imagesx($src); $h = imagesy($src);
    $dst = imagecreatetruecolor($w, $h);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopy($dst, $src, 0, 0, 0, 0, $w, $h);
    ob_start(); imagejpeg($dst, null, 92); $out = ob_get_clean();
    return $out !== '' ? $out : null;
}
function suffix_name($name, $suffix) {
    $dot = strrpos($name, '.');
    return $dot === false ? $name . $suffix : substr($name, 0, $dot) . $suffix . substr($name, $dot);
}
function safe_basename($url) {
    $p = parse_url($url, PHP_URL_PATH);
    // Allegro: /<size>/<dir>/<picture id>/<optional SEO name>; the SEO name is not unique, the picture id is
    if (preg_match('#allegroimg\.com$#i', (string)parse_url($url, PHP_URL_HOST))
        && preg_match('#^/[^/]+/[^/]+/([0-9a-f]{16,})(?:/([^/]*))?$#i', (string)$p, $am)) {
        $b = 'allegro-' . $am[1] . (isset($am[2]) && $am[2] !== '' ? '-' . rawurldecode($am[2]) : '');
    } else {
        $b = $p ? rawurldecode(basename($p)) : '';
    }
    $b = preg_replace('/[^A-Za-z0-9._-]/', '_', $b);
    if (trim($b, '._') === '') return null;
    // keep names well below the 255-byte file system limit; the hash keeps a shortened name unique
    if (strlen($b) > 120) {
        $ext = preg_match('/\.(jpe?g|png|gif|webp)$/i', $b, $em) ? $em[0] : '';
        $b = rtrim(substr($b, 0, 100), '._-') . '-' . substr(md5($url), 0, 10) . $ext;
    }
    return $b;
}
function write_file($abs, $blob) {
    $dir = dirname($abs);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) fail("mkdir $dir");
    $tmp = "$abs.tmp" . getmypid();
    if (file_put_contents($tmp, $blob) !== strlen($blob)) fail("write $tmp");
    rename($tmp, $abs);
}
/**
 * Store a file under $dir/<basename>; a different file with the same name gets a content-hash suffix.
 * Prom file names start with a unique picture id, so the same name normally means the same picture.
 */
function fetch_image($url, $dir, $root, &$files, &$warn, &$cache) {
    if (isset($cache[$url])) return $cache[$url];
    if (!preg_match('#^https?://#i', $url)) { $warn[] = "not an absolute image url: $url"; return $cache[$url] = null; }
    $name = safe_basename($url);
    if ($name === null) { $warn[] = "no file name in url: $url"; return $cache[$url] = null; }
    if (!preg_match('/\.(jpe?g|png|gif|webp)$/i', $name)) $name .= '.jpg';
    if (!empty($GLOBALS['NO_IMAGES'])) return $cache[$url] = $dir . $name;
    // Prom and Allegro file names carry a unique picture id: an existing valid file with that name is the same picture
    $abs = "$root/image/$dir$name";
    if (is_file($abs) && is_oc_image_file($abs)) { $files[$dir . $name] = md5_file($abs); return $cache[$url] = $dir . $name; }
    $blob = http_get($url);
    if ($blob === null || !is_image_blob($blob)) { $warn[] = "download failed: $url"; return $cache[$url] = null; }
    $blob = to_oc_blob($blob);
    if ($blob === null) { $warn[] = "unsupported image format: $url"; return $cache[$url] = null; }
    $hash = md5($blob);
    $rel = $dir . $name;
    $abs = "$root/image/$rel";
    if (is_file($abs) && md5_file($abs) !== $hash) {
        $rel = $dir . suffix_name($name, '-' . substr($hash, 0, 8));
        $abs = "$root/image/$rel";
    }
    if (!is_file($abs)) write_file($abs, $blob);
    $files[$rel] = $hash;
    return $cache[$url] = $rel;
}

/** Localise images, unwrap links to other sites, drop editor attributes (data-start/data-end). */
function clean_description($html, $root, &$files, &$warn, &$cache, &$stat) {
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html);
    $html = preg_replace('#\s+data-(?:start|end)="[^"]*"#i', '', $html);
    // Prom editor leftovers: empty hidden spans carrying a background image, inside otherwise empty positioned divs
    $html = preg_replace('#<span\b[^>]*display\s*:\s*none[^>]*>\s*</span>#i', '', $html);
    $html = preg_replace('#<div\b[^>]*>\s*</div>#i', '', $html);
    $html = preg_replace_callback('#(<img\b[^>]*?\bsrc=")([^"]+)(")#i', function ($m) use ($root, &$files, &$warn, &$cache, &$stat) {
        $rel = fetch_image($m[2], IMG_DESC_DIR, $root, $files, $warn, $cache);
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

// ---------------------------------------------------------------- DB lookups (read-only)

/** Codes already linked on a site: GI-, 1GI-, 1-GI- in upc (comma lists included). */
function linked_codes($m) {
    $have = array();
    foreach (rows($m, "SELECT upc FROM oc_product WHERE upc LIKE '%GI-%'") as $r)
        foreach (explode(',', $r['upc']) as $t) if (preg_match('/^(?:1-?)?GI-(.+)$/i', trim($t), $mm)) $have[norm_code($mm[1])] = true;
    return $have;
}
/** Normalised GI codes of a site (linked and unhooked): code => product_id, for the 'code inside another GI code' check. */
function gi_codes($m) {
    $out = array();
    foreach (rows($m, "SELECT product_id, upc FROM oc_product WHERE upc LIKE '%GI-%'") as $r)
        foreach (explode(',', $r['upc']) as $t) if (preg_match('/^(?:1-?)?GI-(.+)$/i', trim($t), $mm)) $out[norm_code($mm[1])] = $r['product_id'] . ' ' . trim($t);
    return $out;
}
/** Index of normalised tokens from names, upc, model, sku and mpn of every product: token => product_id. */
function token_index($m) {
    $idx = array();
    $add = function ($s, $pid) use (&$idx) {
        foreach (preg_split('/[\s,;()"\/]+/u', html_entity_decode((string)$s, ENT_QUOTES, 'UTF-8')) as $t) {
            $n = norm_code($t);
            if (mb_strlen($n) >= 5 && !isset($idx[$n])) $idx[$n] = $pid;
        }
    };
    foreach (rows($m, "SELECT product_id, upc, model, mpn FROM oc_product") as $r) {
        foreach (explode(',', $r['upc']) as $t) $add(preg_replace('/^(?:1-?)?[A-Za-z]{1,5}-/', '', trim($t)), $r['product_id']);
        $add($r['upc'], $r['product_id']); $add($r['model'], $r['product_id']); $add($r['mpn'], $r['product_id']);
    }
    foreach (rows($m, "SELECT product_id, name FROM oc_product_description") as $r) $add($r['name'], $r['product_id']);
    return $idx;
}
function category_votes($m, $feed) {
    $vote = array();
    foreach (rows($m, "SELECT p.upc, pc.category_id FROM oc_product p JOIN oc_product_to_category pc ON pc.product_id=p.product_id
                       JOIN oc_category c ON c.category_id=pc.category_id AND c.status=1 WHERE p.upc LIKE 'GI-%'") as $r) {
        $code = substr($r['upc'], strlen(UPC_PREFIX));
        if (isset($feed[$code])) @$vote[$feed[$code]['category']][$r['category_id']]++;
    }
    foreach ($vote as $fc => $v) arsort($vote[$fc]);
    return $vote;
}
function category_names($m) {
    $n = array();
    foreach (rows($m, "SELECT d.category_id, d.language_id, d.name, c.status, c.parent_id FROM oc_category_description d JOIN oc_category c ON c.category_id=d.category_id") as $r)
        $n[$r['category_id']][$r['language_id']] = array('name' => $r['name'], 'status' => (int)$r['status'], 'parent' => (int)$r['parent_id']);
    return $n;
}
function category_path($cn, $id, $lang) {
    $p = array(); $guard = 0;
    while ($id && isset($cn[$id][$lang]) && $guard++ < 10) { array_unshift($p, $cn[$id][$lang]['name']); $id = $cn[$id][$lang]['parent']; }
    return implode(' > ', $p);
}
function taken_keywords($m) {
    $k = array();
    foreach (rows($m, "SELECT keyword FROM oc_seo_url") as $r) $k[mb_strtolower($r['keyword'])] = true;
    return $k;
}
function manufacturer_ids($m) {
    $out = array();
    foreach (rows($m, "SELECT manufacturer_id, name FROM oc_manufacturer") as $r) $out[$r['name']][] = (int)$r['manufacturer_id'];
    return $out;
}
function numeric_sku_max($m) {
    $r = rows($m, "SELECT MAX(CAST(sku AS UNSIGNED)) mx FROM oc_product WHERE sku REGEXP '^[0-9]+$' AND CAST(sku AS UNSIGNED) < 100000");
    return (int)$r[0]['mx'];
}

// ---------------------------------------------------------------- build

function cmd_build($o) {
    global $BRAND_MANUFACTURER;
    list($feed, $fcats) = parse_feed(need($o, 'feed'));
    $csv = parse_csv(need($o, 'csv'));
    $list = parse_list(need($o, 'list'));
    $root = rtrim(need($o, 'root'), '/');
    $out = rtrim(need($o, 'out'), '/');
    if (!is_dir("$root/image")) fail("$root/image not found");
    if (!is_dir($out) && !mkdir($out, 0755, true)) fail("mkdir $out");
    $map = array();
    if (isset($o['categories'])) {
        $map = json_decode(file_get_contents($o['categories']), true);
        if (!is_array($map)) fail('categories: invalid JSON');
    }
    $id = isset($o['id-start']) ? (int)$o['id-start'] : 7813001;
    $GLOBALS['NO_IMAGES'] = isset($o['no-images']);
    if ($GLOBALS['NO_IMAGES']) say('--no-images: nothing is downloaded, payload is NOT usable for apply');

    $prod = db_connect(need($o, 'prod-config'), true);
    $dev = db_connect(need($o, 'dev-config'), true);
    foreach (array('prod' => $prod, 'dev' => $dev) as $env => $m) {
        $mi = manufacturer_ids($m);
        foreach ($BRAND_MANUFACTURER as $b => $name)
            if (isset($mi[$name]) && count($mi[$name]) > 1) fail("$env: manufacturer name '$name' is not unique");
    }
    $linked = linked_codes($prod) + linked_codes($dev);
    say('indexing product tokens...');
    $tok_p = token_index($prod);
    $tok_d = token_index($dev);
    $gi_p = gi_codes($prod);
    $gi_d = gi_codes($dev);
    $vote = category_votes($prod, $feed);
    $cn_p = category_names($prod);
    $cn_d = category_names($dev);
    $kw = taken_keywords($prod) + taken_keywords($dev);
    $sku_next = numeric_sku_max($prod) + 1;
    say('list=' . count($list) . ' feed=' . count($feed) . ' csv=' . count($csv));

    $products = array(); $skipped = array(); $files = array(); $warn = array(); $cache = array(); $todo = array();
    $stat = array('local_img' => 0, 'external_img' => 0, 'unwrapped_links' => 0);
    $n = 0;
    foreach ($list as $code => $brand) {
        $code = (string)$code;
        if (++$n % 100 == 0) fwrite(STDERR, "$n\n");
        $skip = function ($why) use (&$skipped, $code, $brand, $feed) {
            $skipped[] = array($code, $brand, isset($feed[$code]) ? $feed[$code]['name_ru'] : '', $why);
        };
        if (!isset($feed[$code])) { $skip('not in the Prom feed'); continue; }
        if (!isset($csv[$code])) { $skip('not in today\'s CSV (no stock at the supplier)'); continue; }
        if (!isset($BRAND_MANUFACTURER[$brand])) { $skip("brand '$brand' has no manufacturer mapping"); continue; }
        $f = $feed[$code];
        $c = $csv[$code];
        $nc = norm_code($code);
        if (isset($linked[$nc])) { $skip('code already linked on a site (GI-/1GI-, look-alike letters folded)'); continue; }
        if (mb_strlen($nc) >= 5) {
            foreach (array('prod' => $tok_p, 'dev' => $tok_d) as $env => $tok)
                if (isset($tok[$nc])) { $skip("article found in an existing $env product #" . $tok[$nc] . ' (name/upc/model/mpn)'); continue 2; }
        }

        // the article inside another GI code (GI-FSEB1240G, GI-NKK15ST, GI-AAAT1717V4): a variant or a duplicate, needs a human look
        if (mb_strlen($nc) >= 4) {
            foreach (array('prod' => $gi_p, 'dev' => $gi_d) as $env => $gi)
                foreach ($gi as $gc => $info)
                    if ($gc !== $nc && strpos($gc, $nc) !== false) { $skip("possible variant/duplicate: code inside $env GI card #$info"); continue 3; }
        }

        // category: approved mapping first, then the vote of existing GI cards
        $flags = array(); $cat = null; $cat_src = '';
        $fc = $f['category'];
        if (isset($map[$fc]) && (int)$map[$fc] > 0) { $cat = (int)$map[$fc]; $cat_src = 'mapping'; }
        elseif (!empty($vote[$fc])) {
            $share = reset($vote[$fc]) / array_sum($vote[$fc]);
            if ($share >= 0.7) { $cat = (int)key($vote[$fc]); $cat_src = sprintf('vote %d/%d', reset($vote[$fc]), array_sum($vote[$fc])); }
        }
        if (!$cat) {
            if (!isset($todo[$fc])) $todo[$fc] = array('count' => 0, 'examples' => array(), 'vote' => isset($vote[$fc]) ? $vote[$fc] : array());
            $todo[$fc]['count']++;
            if (count($todo[$fc]['examples']) < 3) $todo[$fc]['examples'][] = $f['name_ru'];
            $skip('no approved category for feed category ' . $fc);
            continue;
        }
        foreach (array('prod' => $cn_p, 'dev' => $cn_d) as $env => $cn)
            if (empty($cn[$cat][LANG_UK]) || !$cn[$cat][LANG_UK]['status']) fail("$code: category $cat missing or disabled on $env");

        // names
        $name_ru = cut($f['name_ru'], 255);
        $name_uk = cut($f['name_uk'] !== '' ? $f['name_uk'] : $f['name_ru'], 255);
        if ($f['name_uk'] === '') $flags[] = 'name uk: EMPTY in feed, ru used';
        foreach (array('ru' => $name_ru, 'uk' => $name_uk) as $l => $nm)
            if (preg_match('/\p{Cyrillic}[A-Za-z]|[A-Za-z]\p{Cyrillic}/u', $nm)) $flags[] = "name $l: mixed Latin/Cyrillic letters";

        // descriptions
        $desc_ru = clean_description($f['description_ru'], $root, $files, $warn, $cache, $stat);
        $desc_uk = clean_description($f['description_uk'] !== '' ? $f['description_uk'] : $f['description_ru'], $root, $files, $warn, $cache, $stat);
        if ($f['description_uk'] === '') $flags[] = 'description uk: EMPTY in feed, ru used';
        if (trim(strip_tags($desc_ru)) === '' && strpos($desc_ru, '<img') === false) $flags[] = 'description ru: EMPTY';

        // images: first picture is the main image, the rest go to the gallery
        $imgs = array();
        foreach ($f['pics'] as $u) { $rel = fetch_image($u, IMG_PRODUCT_DIR, $root, $files, $warn, $cache); if ($rel !== null) $imgs[] = $rel; }
        $imgs = array_values(array_unique($imgs));
        if (!$imgs) $flags[] = 'images: NONE';

        // attributes: RU only in the feed; UK text = RU text when it has no Cyrillic
        $attrs = array(); $seen = array();
        foreach ($f['params'] as $p) {
            if ($p['value'] === '' || $p['name'] === '') continue;
            $nr = cut($p['name'] . ($p['unit'] !== '' ? ', ' . mb_strtolower($p['unit']) : ''), 64);
            if (isset($seen[mb_strtolower($nr)])) continue;
            $seen[mb_strtolower($nr)] = true;
            $attrs[] = array('name_ru' => $nr, 'text_ru' => $p['value'], 'text_uk' => has_cyrillic($p['value']) ? '' : $p['value']);
        }

        // seo keywords, unique across both DBs and this batch
        $keys = array();
        foreach (array(LANG_UK => array($name_uk, 'sug_translit_ukr'), LANG_RU => array($name_ru, 'sug_translit_rus')) as $lang => $a) {
            $k = cut(slug($a[0], $a[1]), 250); $base = $k; $i = 2;
            if ($k === '') { $k = $base = 'gi-' . mb_strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $code)); }
            if ($lang == LANG_RU && $k === $keys[LANG_UK]) { $keys[$lang] = $k; continue; }
            while (isset($kw[$k])) { $k = "$base-$i"; $i++; }
            if ($k !== $base) $flags[] = "seo keyword ($lang): suffixed";
            $kw[$k] = true; $keys[$lang] = $k;
        }

        $products[] = array(
            'product_id' => $id++, 'sku' => (string)$sku_next++, 'upc' => UPC_PREFIX . $code, 'supplier_code' => $code,
            'brand' => $brand, 'manufacturer' => $BRAND_MANUFACTURER[$brand],
            'quantity' => $c['stock'], 'price' => round($c['price'] / RATE, 4), 'price_uah' => $c['price'], 'cost_price' => $c['purchase'],
            'image' => $imgs ? $imgs[0] : '', 'gallery' => array_slice($imgs, 1),
            'category_id' => $cat, 'category_src' => $cat_src, 'feed_category' => $fc,
            'category_uk' => $cn_p[$cat][LANG_UK]['name'], 'category_ru' => isset($cn_p[$cat][LANG_RU]) ? $cn_p[$cat][LANG_RU]['name'] : $cn_p[$cat][LANG_UK]['name'],
            'name_uk' => $name_uk, 'name_ru' => $name_ru, 'description_uk' => $desc_uk, 'description_ru' => $desc_ru,
            'keyword_uk' => $keys[LANG_UK], 'keyword_ru' => $keys[LANG_RU], 'attributes' => $attrs, 'flags' => $flags,
        );
    }

    file_put_contents("$out/payload.json", json_encode(array('built' => date('c'), 'no_images' => $GLOBALS['NO_IMAGES'], 'products' => $products), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    ksort($files);
    file_put_contents("$out/files.json", json_encode($files, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $fh = fopen("$out/preview.csv", 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, array('supplier_code', 'brand', 'manufacturer', 'product_id', 'name_ru', 'name_uk', 'category_id', 'category_path', 'category_src',
        'feed_category', 'price_uah', 'quantity', 'cost_uah', 'images', 'attributes', 'desc_ru_chars', 'keyword_uk', 'keyword_ru', 'flags'), ';');
    foreach ($products as $p) fputcsv($fh, array($p['supplier_code'], $p['brand'], $p['manufacturer'], $p['product_id'], $p['name_ru'], $p['name_uk'],
        $p['category_id'], category_path($cn_p, $p['category_id'], LANG_RU), $p['category_src'],
        isset($fcats[$p['feed_category']]) ? $fcats[$p['feed_category']]['name'] : $p['feed_category'], $p['price_uah'], $p['quantity'], $p['cost_price'],
        ($p['image'] ? 1 : 0) + count($p['gallery']), count($p['attributes']), mb_strlen(trim(strip_tags($p['description_ru']))),
        $p['keyword_uk'], $p['keyword_ru'], implode('; ', $p['flags'])), ';');
    fclose($fh);

    $fh = fopen("$out/skipped.csv", 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, array('supplier_code', 'brand', 'name_ru', 'reason'), ';');
    foreach ($skipped as $s) fputcsv($fh, $s, ';');
    fclose($fh);

    // categories still to approve: feed category, its path, how many items, current weak vote, examples
    uasort($todo, function ($a, $b) { return $b['count'] - $a['count']; });
    $fh = fopen("$out/category_todo.csv", 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, array('feed_category_id', 'feed_category_path', 'items', 'weak_vote (site category: cards)', 'site_category_id (fill in)', 'examples'), ';');
    foreach ($todo as $fc => $t) {
        $path = array(); $id2 = $fc; $g = 0;
        while ($id2 !== '' && isset($fcats[$id2]) && $g++ < 10) { array_unshift($path, $fcats[$id2]['name']); $id2 = $fcats[$id2]['parent']; }
        $v = array(); foreach (array_slice($t['vote'], 0, 3, true) as $cid => $cnt) $v[] = "$cid " . category_path($cn_p, $cid, LANG_RU) . ": $cnt";
        fputcsv($fh, array($fc, implode(' > ', $path), $t['count'], implode(' | ', $v), '', implode(' | ', $t['examples'])), ';');
    }
    fclose($fh);

    file_put_contents("$out/warnings.txt", implode("\n", $warn) . "\n");
    $reasons = array();
    foreach ($skipped as $s) { $r = preg_replace('/#.*$|category \d+$/', '', $s[3]); @$reasons[$r]++; }
    say('products=' . count($products) . ' skipped=' . count($skipped) . ' files=' . count($files) . ' warnings=' . count($warn));
    foreach ($reasons as $r => $c) say("  skipped: $c  $r");
    say('feed categories still to approve: ' . count($todo));
    say('description images: local=' . $stat['local_img'] . ' external(kept)=' . $stat['external_img'] . ' unwrapped links=' . $stat['unwrapped_links']);
    say('provisional sku from ' . ($sku_next - count($products)) . ' (final sku is assigned at apply with --assign-sku)');
}

// ---------------------------------------------------------------- files

function cmd_sync_files($o) {
    $files = json_decode(file_get_contents(need($o, 'manifest')), true);
    $from = rtrim(need($o, 'from'), '/');
    $to = rtrim(need($o, 'to'), '/');
    $log = need($o, 'created-log');
    $dry = isset($o['dry-run']);
    if (!$dry && file_exists($log)) fail("created-log $log already exists");
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
    $fh = fopen($log, 'w');
    foreach ($copy as $rel) {
        $dst = "$to/image/$rel";
        if (!is_dir(dirname($dst)) && !mkdir(dirname($dst), 0755, true)) fail('mkdir ' . dirname($dst));
        if (!copy("$from/image/$rel", $dst)) fail("copy $rel");
        fwrite($fh, $rel . "\t" . $files[$rel] . "\n");
    }
    fclose($fh);
    say('copied ' . count($copy) . "; log $log");
}

function cmd_remove_files($o) {
    $log = need($o, 'created-log');
    $root = rtrim(need($o, 'root'), '/');
    $dry = isset($o['dry-run']);
    $del = 0; $changed = 0; $gone = 0;
    foreach (file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        list($rel, $hash) = explode("\t", $line);
        $abs = "$root/image/$rel";
        if (!is_file($abs)) { $gone++; continue; }
        if (md5_file($abs) !== $hash) { $changed++; say("kept (content changed since copy): $rel"); continue; }
        if (!$dry && !unlink($abs)) fail("unlink $abs");
        $del++;
    }
    say("removed=$del kept(changed)=$changed missing=$gone" . ($dry ? ' (dry run)' : ''));
}

// ---------------------------------------------------------------- apply / rollback

/** Attribute in group 8 by RU name; with several candidates the most used one wins. */
function resolve_attribute($m, $a, &$created, $dry) {
    static $cache = array();
    $key = $m->db_name . '|' . mb_strtolower($a['name_ru']);
    if (isset($cache[$key])) return $cache[$key];
    $r = rows($m, "SELECT a.attribute_id, (SELECT COUNT(*) FROM oc_product_attribute pa WHERE pa.attribute_id=a.attribute_id) used
                   FROM oc_attribute a JOIN oc_attribute_description d ON d.attribute_id=a.attribute_id AND d.language_id=" . LANG_RU . "
                   WHERE a.attribute_group_id=" . ATTR_GROUP . " AND d.name=" . e($m, $a['name_ru']) . " ORDER BY used DESC, a.attribute_id LIMIT 1");
    if ($r) return $cache[$key] = (int)$r[0]['attribute_id'];
    if ($dry) { $created[] = array('attribute_id' => 0, 'name_ru' => $a['name_ru']); return $cache[$key] = 0; }
    // no UK name in the feed and no machine translation: the UK name repeats the RU name (reported for manual translation)
    q($m, "INSERT INTO oc_attribute SET attribute_group_id=" . ATTR_GROUP . ", sort_order=0");
    $aid = (int)$m->insert_id;
    q($m, "INSERT INTO oc_attribute_description (attribute_id, language_id, name) VALUES ($aid, " . LANG_UK . ", " . e($m, $a['name_ru']) . "), ($aid, " . LANG_RU . ", " . e($m, $a['name_ru']) . ")");
    $created[] = array('attribute_id' => $aid, 'name_ru' => $a['name_ru']);
    return $cache[$key] = $aid;
}

function cmd_apply($o) {
    $payload_file = need($o, 'payload');
    $journal_file = need($o, 'journal');
    $dry = isset($o['dry-run']);
    if (!$dry && file_exists($journal_file)) fail("journal $journal_file already exists");
    $P = json_decode(file_get_contents($payload_file), true);
    if (!$P || empty($P['products'])) fail('empty payload');
    if (!empty($P['no_images'])) fail('payload was built with --no-images');
    $m = db_connect(need($o, 'config'), false);
    say('DB=' . $m->db_name . ($dry ? ' (dry run, rolled back at the end)' : ''));

    $ids = array_column($P['products'], 'product_id');
    $created = array();
    // manufacturers by name in this DB; each must exist exactly once
    $mi = manufacturer_ids($m);
    $mid = array();
    foreach (array_unique(array_column($P['products'], 'manufacturer')) as $name) {
        if (empty($mi[$name])) fail("manufacturer '$name' does not exist in {$m->db_name} (run gi_fix_existing.php first)");
        if (count($mi[$name]) > 1) fail("manufacturer '$name' is not unique in {$m->db_name}");
        $mid[$name] = $mi[$name][0];
    }
    q($m, 'START TRANSACTION');
    try {
        $busy = rows($m, 'SELECT product_id FROM oc_product WHERE product_id IN (' . implode(',', array_map('intval', $ids)) . ')');
        if ($busy) throw new RuntimeException('product_id already used: ' . implode(',', array_column($busy, 'product_id')));
        $linked = linked_codes($m);
        foreach ($P['products'] as $p) {
            if (isset($linked[norm_code($p['supplier_code'])])) throw new RuntimeException("{$p['supplier_code']} is already linked on this site");
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
                quantity=" . (int)$p['quantity'] . ", stock_status_id=" . STOCK_STATUS_ID . ", image=" . e($m, $p['image']) . ", manufacturer_id=" . (int)$mid[$p['manufacturer']] . ",
                shipping=1, price=" . sprintf('%.4F', $p['price']) . ", cost_price=" . sprintf('%.4F', $p['cost_price']) . ", points=0, tax_class_id=0, date_available=CURDATE(),
                weight=0, weight_class_id=1, length=0, width=0, height=0, length_class_id=1, subtract=0, minimum=1, sort_order=0, status=1,
                date_added=NOW(), date_modified=NOW(), unixml_feed='', unixml_link='', viewed=0, oct_stickers=''");
            foreach (array(LANG_UK => 'uk', LANG_RU => 'ru') as $lang => $l) {
                $name = $p["name_$l"];
                q($m, "INSERT INTO oc_product_description SET product_id=$pid, language_id=$lang, name=" . e($m, $name) . ", featured='', fimage='', video1='',
                    html_slick_listing='', html_product_right='', html_product_tab='', tab_title='', description=" . e($m, $p["description_$l"]) . ",
                    tag='', meta_title=" . e($m, meta_title($name, $lang)) . ", meta_description=" . e($m, meta_description($name, $p["category_$l"], $lang)) . ",
                    meta_keyword='', custom_alt='', custom_h1='', custom_h2='', custom_imgtitle='', custom_title='', default_meta=0");
                q($m, "INSERT INTO oc_seo_url SET store_id=0, language_id=$lang, query='product_id=$pid', keyword=" . e($m, $p["keyword_$l"]));
            }
            q($m, "INSERT INTO oc_product_to_category SET product_id=$pid, category_id=" . (int)$p['category_id']);
            q($m, "INSERT INTO oc_product_to_store SET product_id=$pid, store_id=0");
            q($m, "INSERT INTO oc_product_to_layout SET product_id=$pid, store_id=0, layout_id=0");
            foreach ($p['gallery'] as $i => $img) q($m, "INSERT INTO oc_product_image SET product_id=$pid, image=" . e($m, $img) . ", sort_order=$i");
            $seen = array();
            foreach ($p['attributes'] as $a) {
                $aid = resolve_attribute($m, $a, $created, $dry);
                if (!$aid || isset($seen[$aid])) continue; // same attribute twice in one card: keep the first
                $seen[$aid] = true;
                q($m, "INSERT INTO oc_product_attribute (product_id, attribute_id, language_id, text) VALUES
                    ($pid, $aid, " . LANG_UK . ", " . e($m, $a['text_uk']) . "), ($pid, $aid, " . LANG_RU . ", " . e($m, $a['text_ru']) . ")");
            }
        }
        $in = implode(',', array_map('intval', $ids));
        $chk = rows($m, "SELECT (SELECT COUNT(*) FROM oc_product WHERE product_id IN ($in)) p,
            (SELECT COUNT(*) FROM oc_product_description WHERE product_id IN ($in)) d,
            (SELECT COUNT(*) FROM oc_seo_url WHERE query IN ('product_id=" . implode("','product_id=", array_map('intval', $ids)) . "')) s,
            (SELECT COUNT(*) FROM oc_product_to_category WHERE product_id IN ($in)) c,
            (SELECT COUNT(*) FROM oc_product_to_store WHERE product_id IN ($in)) st,
            (SELECT COUNT(*) FROM oc_product_to_layout WHERE product_id IN ($in)) l,
            (SELECT COUNT(*) FROM oc_product_image WHERE product_id IN ($in)) i,
            (SELECT COUNT(*) FROM oc_product_attribute WHERE product_id IN ($in)) a")[0];
        $n = count($ids);
        say("inserted: products={$chk['p']} descriptions={$chk['d']} seo={$chk['s']} categories={$chk['c']} stores={$chk['st']} layouts={$chk['l']} gallery={$chk['i']} attribute rows={$chk['a']} new attributes=" . count($created));
        if ($chk['p'] != $n || $chk['d'] != 2 * $n || $chk['s'] != 2 * $n || $chk['c'] != $n || $chk['st'] != $n || $chk['l'] != $n)
            throw new RuntimeException('verification counts do not match');
        if ($created) say('new attributes (UK name = RU name, to translate): ' . implode(' | ', array_column($created, 'name_ru')));
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
        foreach (array('oc_product_attribute', 'oc_product_special', 'oc_product_discount', 'oc_product_image', 'oc_product_to_layout', 'oc_product_to_store',
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

$o = opts();
switch ($o['_cmd']) {
    case 'build': cmd_build($o); break;
    case 'sync-files': cmd_sync_files($o); break;
    case 'remove-files': cmd_remove_files($o); break;
    case 'apply': cmd_apply($o); break;
    case 'rollback': cmd_rollback($o); break;
    default: fail('command: build | sync-files | remove-files | apply | rollback');
}
