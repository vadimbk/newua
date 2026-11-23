<?php
/**
 * Import new HOEGERT products (upc BOC-<EAN>) from the eopt.ua feed, see save/hoegert_20261121/PLAN.md step 2.
 *
 * Which products: feed offers whose EAN is linked on neither DB (BOC-, 1BOC-, 1-BOC-), available="true", with a price,
 * and whose article/EAN is not found in any product (name, upc, model, mpn) on either DB.
 * Price/stock: parser CSV 6_hoegert.csv when the EAN is there (what the prod cron writes), otherwise the feed.
 *
 * Content:
 *  - uk: the feed (name, description, params);
 *  - ru: the eopt /ru/ product page (h1, description tab, features tab aligned with the feed params by position);
 *    what is still missing comes from overrides.json (filled by the translate command, Gemini, marked);
 *  - work clothing (article HT5K...-<size>): category, and photos/description/attributes when the feed has none,
 *    come from a sibling card of the same family (same article stem); only the size differs;
 *  - category: overrides > clothing sibling > vote of existing HOEGERT cards sharing the longest article prefix (>= 70 %).
 *
 * Conventions (Aug 2026 batch + GI run): name = feed name, model empty, manufacturer 832, stock_status 5, subtract 0,
 * minimum 1, store 0, layout 0, one category, photos catalog/product/Toptul/files/products/<file>-800x800.jpg,
 * description photos catalog/desc/HOEGERT/, attributes in group 8, no special price,
 * status 0 when the product has no photo or no description (Vadym, 2026-11-23).
 *
 * Commands (run with /usr/local/lsws/lsphp74/bin/php):
 *
 *   build      --feed=<eopt xml> --csv=<6_hoegert.csv> --prod-config=<config.php> --dev-config=<config.php>
 *              --root=<site root for images> --out=<dir> [--overrides=<json>] [--id-start=7816001] [--no-images]
 *              Reads both DBs read-only, fetches eopt ru pages (cached in <out>/pages), downloads images into --root,
 *              writes payload.json, files.json, preview.csv, skipped.csv, translate_todo.json, warnings.txt. No DB writes.
 *
 *   translate  --todo=<translate_todo.json> --env=<.env with VERTEX_*> --overrides=<json>
 *              Asks gemini-3.1-pro-preview (location global) for the missing ru texts and merges them into overrides.json
 *              (existing keys are kept). Rebuild afterwards.
 *
 *   sync-files --manifest=<files.json> --from=<root> --to=<root> --created-log=<file> [--dry-run]
 *   remove-files --created-log=<file> --root=<root> [--dry-run]
 *   apply      --config=<config.php> --payload=<json> --journal=<json> [--assign-sku] [--dry-run]
 *   rollback   --config=<config.php> --journal=<json> [--dry-run]
 */

error_reporting(E_ALL);
ini_set('memory_limit', '3072M');
mb_internal_encoding('UTF-8');

define('UPC_PREFIX', 'BOC-');
define('RATE', 45.0);
define('ATTR_GROUP', 8);
define('LANG_UK', 3);
define('LANG_RU', 2);
define('STOCK_STATUS_ID', 5);
define('MANUFACTURER_ID', 832);
define('IMG_PRODUCT_DIR', 'catalog/product/Toptul/files/products/');
define('IMG_DESC_DIR', 'catalog/desc/HOEGERT/');
define('GEMINI_MODEL', 'gemini-3.1-pro-preview');

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

// ---------------------------------------------------------------- text helpers

function ws($s) { return trim(preg_replace('/\s+/u', ' ', (string)$s)); }
function dec($s) { for ($i = 0; $i < 3; $i++) $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'); return ws($s); }
/** eopt names: double spaces, sometimes '((CODE)'. */
function clean_name($s) { return cut(preg_replace('/\(\(+/u', '(', ws($s)), 255); }
function name_article($name) { return preg_match('/\(+([^()]+)\)\s*$/u', dec($name), $m) ? mb_strtoupper(trim($m[1])) : ''; }
function cut($s, $n) { return mb_substr($s, 0, $n); }
function slug($name, $fn) {
    $s = $fn(html_entity_decode($name, ENT_QUOTES, 'UTF-8'));
    $s = preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($s));
    return trim($s, '-');
}
function meta_title($name, $lang) {
    return cut($lang == LANG_UK ? "$name - купити за найнижчими цінами в Україні" : "$name - купить по самым низким ценам в Украине", 255);
}
function meta_description($name, $category, $lang) {
    return cut($lang == LANG_UK
        ? "$name купити за найкращою ціною. $category. ✈ Доставка по Києву та Україні. Гарантія"
        : "$name купить по лучшей цене. $category. ✈ Доставка по Киеву и Украине. Гарантия", 255);
}
function norm_code($s) {
    $s = mb_strtoupper(html_entity_decode($s, ENT_QUOTES, 'UTF-8'));
    $s = strtr($s, array('А' => 'A', 'В' => 'B', 'С' => 'C', 'Е' => 'E', 'Н' => 'H', 'К' => 'K', 'М' => 'M', 'О' => 'O', 'Р' => 'P',
        'Т' => 'T', 'Х' => 'X', 'З' => '3', 'І' => 'I', 'У' => 'Y'));
    return preg_replace('/[^A-Z0-9\x{0400}-\x{04FF}]/u', '', $s);
}
function has_cyrillic($s) { return (bool)preg_match('/\p{Cyrillic}/u', $s); }
function plain_len($html) { return mb_strlen(trim(strip_tags(html_entity_decode((string)$html, ENT_QUOTES, 'UTF-8')))); }
/** Work clothing family: HT5K279-2-XL -> stem HT5K279-2, size XL. */
function clothing_family($code) {
    return preg_match('/^(HT5K\d+(?:-\d+)?)-([^-]+)$/i', $code, $m) ? array(mb_strtoupper($m[1]), $m[2]) : null;
}

// ---------------------------------------------------------------- inputs

function parse_feed($file) {
    $x = simplexml_load_file($file, null, LIBXML_NONET | LIBXML_PARSEHUGE | LIBXML_NOCDATA);
    if (!$x) fail("cannot parse $file");
    $out = array();
    foreach ($x->shop->offers->offer as $o) {
        $ean = trim((string)$o->sku);
        if ($ean === '' || isset($out[$ean])) continue;
        $params = array();
        foreach ($o->param as $p) $params[] = array('name' => ws((string)$p['name']), 'value' => ws((string)$p));
        $out[$ean] = array(
            'ean' => $ean, 'code' => trim((string)$o->code), 'name' => clean_name((string)$o->name), 'url' => trim((string)$o->url),
            'price' => trim((string)$o->base_price), 'stock' => trim((string)$o->stock), 'available' => (string)$o['available'] === 'true',
            'image' => trim((string)$o->image), 'description' => trim((string)$o->description), 'params' => $params,
        );
    }
    return $out;
}
function parse_csv($file) {
    $f = fopen($file, 'r');
    if (!$f) fail("cannot open $file");
    $h = fgetcsv($f, 0, ';');
    if ($h !== array('sku', 'price', 'stock', 'purchase', 'vat')) fail('unexpected CSV header: ' . implode(';', (array)$h));
    $out = array();
    while ($r = fgetcsv($f, 0, ';')) if (count($r) >= 4) $out[trim($r[0])] = array('price' => (float)$r[1], 'stock' => (int)$r[2], 'purchase' => (float)$r[3]);
    return $out;
}

// ---------------------------------------------------------------- eopt ru page

function http_get($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 120, CURLOPT_USERAGENT => 'Mozilla/5.0', CURLOPT_ENCODING => 'gzip'));
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($body !== false && $code == 200 && strlen($body) > 0) ? $body : null;
}
function inner_html($node) { $s = ''; foreach ($node->childNodes as $c) $s .= $node->ownerDocument->saveHTML($c); return $s; }
/** name, description html, features [[name, value]], breadcrumb path of an eopt product page. */
function parse_eopt_page($html) {
    $d = new DOMDocument();
    libxml_use_internal_errors(true);
    $d->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    $x = new DOMXPath($d);
    $h1 = $x->query('//h1');
    $desc = $x->query('//div[@id="description"]//div[contains(@class,"tab_product_pad_top")]');
    $feat = array();
    foreach ($x->query('//div[@id="features"]//ul[contains(@class,"features_tab")]/li') as $li) {
        $n = $x->query('.//span[contains(@class,"features_name")]', $li);
        $v = $x->query('.//*[contains(@class,"features_value")]', $li);
        if ($n->length && $v->length) $feat[] = array(ws($n->item(0)->textContent), ws($v->item(0)->textContent));
    }
    $crumbs = array();
    foreach ($x->query('//*[@itemtype="https://schema.org/BreadcrumbList"]//*[@itemprop="name"]') as $c) { $t = ws($c->textContent); if ($t !== '') $crumbs[] = $t; }
    return array(
        'name' => $h1->length ? clean_name($h1->item(0)->textContent) : '',
        'description' => $desc->length ? trim(inner_html($desc->item(0))) : '',
        'features' => $feat,
        'crumbs' => array_slice($crumbs, 1, -1), // drop "E-opt" and the product itself
    );
}
function eopt_ru_page($url, $cache_dir) {
    if (!preg_match('#^https://eopt\.ua/products/[^/?\#]+$#', $url)) return null;
    $file = $cache_dir . '/' . md5($url) . '.html';
    if (is_file($file)) $html = file_get_contents($file);
    else {
        usleep(400000);
        $html = http_get(str_replace('https://eopt.ua/products/', 'https://eopt.ua/ru/products/', $url));
        if ($html === null) return null;
        file_put_contents($file, $html);
    }
    return parse_eopt_page($html);
}

// ---------------------------------------------------------------- images

function is_oc_image_file($abs) { $i = @getimagesize($abs); return $i !== false && in_array($i[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF)); }
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
/** eopt: 000764605_1.800x800.jpg -> 000764605_1-800x800.jpg (Aug 2026 convention). */
function safe_basename($url) {
    $p = parse_url($url, PHP_URL_PATH);
    $b = $p ? rawurldecode(basename($p)) : '';
    $b = preg_replace('/\.(\d+x\d+)\.(jpe?g|png|gif|webp)$/i', '-$1.$2', $b);
    $b = preg_replace('/[^A-Za-z0-9._-]/', '_', $b);
    if (trim($b, '._') === '') return null;
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
function fetch_image($url, $dir, $root, &$files, &$warn, &$cache) {
    if (isset($cache[$url])) return $cache[$url];
    if (strpos($url, '//') === 0) $url = 'https:' . $url;
    elseif (strpos($url, '/') === 0) $url = 'https://eopt.ua' . $url;
    if (!preg_match('#^https?://#i', $url)) { $warn[] = "not an absolute image url: $url"; return $cache[$url] = null; }
    $name = safe_basename($url);
    if ($name === null) { $warn[] = "no file name in url: $url"; return $cache[$url] = null; }
    if (!preg_match('/\.(jpe?g|png|gif|webp)$/i', $name)) $name .= '.jpg';
    if (!empty($GLOBALS['NO_IMAGES'])) return $cache[$url] = $dir . $name;
    // eopt file names carry a unique picture id: an existing valid file with that name is the same picture
    $abs = "$root/image/$dir$name";
    if (is_file($abs) && is_oc_image_file($abs)) { $files[$dir . $name] = md5_file($abs); return $cache[$url] = $dir . $name; }
    $blob = http_get($url);
    if ($blob === null || @getimagesizefromstring($blob) === false) { $warn[] = "download failed: $url"; return $cache[$url] = null; }
    $blob = to_oc_blob($blob);
    if ($blob === null) { $warn[] = "unsupported image format: $url"; return $cache[$url] = null; }
    $hash = md5($blob);
    $rel = $dir . $name;
    $abs = "$root/image/$rel";
    if (is_file($abs) && md5_file($abs) !== $hash) { $rel = $dir . suffix_name($name, '-' . substr($hash, 0, 8)); $abs = "$root/image/$rel"; }
    if (!is_file($abs)) write_file($abs, $blob);
    $files[$rel] = $hash;
    return $cache[$url] = $rel;
}
/** Localise images, unwrap links to other sites, drop styles and editor attributes. */
function clean_description($html, $root, &$files, &$warn, &$cache) {
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html);
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#\s+data-(?:start|end)="[^"]*"#i', '', $html);
    $html = preg_replace_callback('#(<img\b[^>]*?\bsrc=")([^"]+)(")#i', function ($m) use ($root, &$files, &$warn, &$cache) {
        $rel = fetch_image($m[2], IMG_DESC_DIR, $root, $files, $warn, $cache);
        return $rel === null ? $m[0] : $m[1] . '/image/' . $rel . $m[3];
    }, $html);
    if ($html === null) fail('preg_replace_callback failed on a description');
    $html = preg_replace_callback('#<a\b[^>]*\bhref="https?://[^"]*"[^>]*>(.*?)</a>#is', function ($m) { return $m[1]; }, $html);
    $html = preg_replace("/\n[ \t]*(\n[ \t]*)+/", "\n", $html);
    return trim($html);
}

// ---------------------------------------------------------------- DB lookups (read-only)

/** EANs linked on a site: BOC-, 1BOC-, 1-BOC- in upc (comma lists included). */
function linked_eans($m) {
    $have = array();
    foreach (rows($m, "SELECT upc FROM oc_product WHERE upc LIKE '%BOC-%'") as $r)
        foreach (explode(',', $r['upc']) as $t) if (preg_match('/^(?:1-?)?BOC-(.+)$/i', trim($t), $mm)) $have[trim($mm[1])] = true;
    return $have;
}
/** Normalised tokens from names, upc, model, mpn of every product: token => product_id. */
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
/** HOEGERT cards of prod with article, category, images, texts, attributes (for siblings and votes). */
function hoegert_cards($m) {
    $cards = array();
    foreach (rows($m, "SELECT p.product_id, p.status, p.image, d.name name_uk, r.name name_ru, d.description desc_uk, r.description desc_ru
                       FROM oc_product p JOIN oc_product_description d ON d.product_id=p.product_id AND d.language_id=" . LANG_UK . "
                       JOIN oc_product_description r ON r.product_id=p.product_id AND r.language_id=" . LANG_RU . "
                       WHERE p.manufacturer_id=" . MANUFACTURER_ID) as $r) {
        $r['article'] = name_article($r['name_uk']);
        $r['category'] = 0;
        $cards[(int)$r['product_id']] = $r;
    }
    foreach (rows($m, "SELECT pc.product_id, pc.category_id FROM oc_product_to_category pc JOIN oc_category c ON c.category_id=pc.category_id AND c.status=1
                       JOIN oc_product p ON p.product_id=pc.product_id WHERE p.manufacturer_id=" . MANUFACTURER_ID . " ORDER BY pc.category_id") as $r)
        if (isset($cards[$r['product_id']]) && !$cards[$r['product_id']]['category']) $cards[$r['product_id']]['category'] = (int)$r['category_id'];
    return $cards;
}
function card_gallery($m, $pid) { return array_column(rows($m, "SELECT image FROM oc_product_image WHERE product_id=" . (int)$pid . " ORDER BY sort_order, product_image_id"), 'image'); }
function card_attributes($m, $pid) {
    $out = array();
    foreach (rows($m, "SELECT pa.attribute_id, pa.language_id, pa.text, d.name FROM oc_product_attribute pa
                       JOIN oc_attribute_description d ON d.attribute_id=pa.attribute_id AND d.language_id=" . LANG_UK . "
                       WHERE pa.product_id=" . (int)$pid . " ORDER BY pa.attribute_id") as $r) {
        $out[$r['attribute_id']]['name_uk'] = $r['name'];
        $out[$r['attribute_id']][$r['language_id'] == LANG_UK ? 'text_uk' : 'text_ru'] = $r['text'];
    }
    return $out;
}
/** Group 8 attributes by lower-case UK name (most used first): name => [id, ru name]. */
function attributes_by_uk($m) {
    $out = array();
    foreach (rows($m, "SELECT a.attribute_id, u.name uk, r.name ru, (SELECT COUNT(*) FROM oc_product_attribute pa WHERE pa.attribute_id=a.attribute_id) used
                       FROM oc_attribute a JOIN oc_attribute_description u ON u.attribute_id=a.attribute_id AND u.language_id=" . LANG_UK . "
                       LEFT JOIN oc_attribute_description r ON r.attribute_id=a.attribute_id AND r.language_id=" . LANG_RU . "
                       WHERE a.attribute_group_id=" . ATTR_GROUP . " ORDER BY used DESC, a.attribute_id") as $r) {
        $k = mb_strtolower(ws($r['uk']));
        if (!isset($out[$k])) $out[$k] = array('id' => (int)$r['attribute_id'], 'ru' => (string)$r['ru']);
    }
    return $out;
}
/** uk -> ru value dictionary per attribute name from existing products: lower(uk name) | uk text => ru text (most common). */
function value_dictionary($m) {
    $cnt = array();
    foreach (rows($m, "SELECT LOWER(d.name) n, u.text uk, r.text ru, COUNT(*) c FROM oc_product_attribute u
                       JOIN oc_product_attribute r ON r.product_id=u.product_id AND r.attribute_id=u.attribute_id AND r.language_id=" . LANG_RU . "
                       JOIN oc_attribute a ON a.attribute_id=u.attribute_id AND a.attribute_group_id=" . ATTR_GROUP . "
                       JOIN oc_attribute_description d ON d.attribute_id=u.attribute_id AND d.language_id=" . LANG_UK . "
                       WHERE u.language_id=" . LANG_UK . " AND u.text<>'' AND r.text<>'' GROUP BY 1,2,3 ORDER BY c DESC") as $r) {
        $k = ws($r['n']) . '|' . ws($r['uk']);
        if (!isset($cnt[$k])) $cnt[$k] = $r['ru'];
    }
    return $cnt;
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
function numeric_sku_max($m) {
    $r = rows($m, "SELECT MAX(CAST(sku AS UNSIGNED)) mx FROM oc_product WHERE sku REGEXP '^[0-9]+$' AND CAST(sku AS UNSIGNED) < 100000");
    return (int)$r[0]['mx'];
}
/** Category vote of HOEGERT cards sharing the longest article prefix (at least 5 characters) with $code. */
function prefix_vote($cards, $code) {
    $code = norm_code($code);
    for ($len = mb_strlen($code) - 1; $len >= 5; $len--) {
        $pre = mb_substr($code, 0, $len); $v = array();
        foreach ($cards as $c) if ($c['category'] && $c['status'] && strpos(norm_code($c['article']), $pre) === 0) @$v[$c['category']]++;
        if ($v) { arsort($v); return array('prefix' => $pre, 'votes' => $v); }
    }
    return null;
}

// ---------------------------------------------------------------- build

function cmd_build($o) {
    $feed = parse_feed(need($o, 'feed'));
    $csv = parse_csv(need($o, 'csv'));
    $root = rtrim(need($o, 'root'), '/');
    $out = rtrim(need($o, 'out'), '/');
    if (!is_dir("$root/image")) fail("$root/image not found");
    if (!is_dir("$out/pages") && !mkdir("$out/pages", 0755, true)) fail("mkdir $out/pages");
    $ov = array();
    if (isset($o['overrides']) && is_file($o['overrides'])) {
        $ov = json_decode(file_get_contents($o['overrides']), true);
        if (!is_array($ov)) fail('overrides: invalid JSON');
    }
    $id = isset($o['id-start']) ? (int)$o['id-start'] : 7816001;
    $GLOBALS['NO_IMAGES'] = isset($o['no-images']);
    if ($GLOBALS['NO_IMAGES']) say('--no-images: nothing is downloaded, payload is NOT usable for apply');

    $prod = db_connect(need($o, 'prod-config'), true);
    $dev = db_connect(need($o, 'dev-config'), true);
    $lp = linked_eans($prod); $ld = linked_eans($dev);
    $old_ean = array('prod' => array(), 'dev' => array()); // EAN => card that gives it up in a pending fix
    // hoegert_fix_existing.php change lists not yet applied on a DB: count their final upc state
    if (isset($o['fix-data'])) foreach (explode(',', $o['fix-data']) as $ff) {
        $F = json_decode(file_get_contents($ff), true);
        if (!$F || !isset($F['changes'])) fail("bad fix data $ff");
        if ($F['db'] === $prod->db_name) $L = &$lp; elseif ($F['db'] === $dev->db_name) $L = &$ld; else fail("fix data $ff is for {$F['db']}");
        $db = $F['db'] === $prod->db_name ? $prod : $dev;
        $pending = 0;
        foreach ($F['changes'] as $ch) {
            $cur = rows($db, 'SELECT upc FROM oc_product WHERE product_id=' . (int)$ch['product_id']);
            if (!$cur || $cur[0]['upc'] !== $ch['expect_upc']) continue; // applied already (or changed): the DB state counts
            if (preg_match('/^BOC-(.+)$/i', $ch['expect_upc'], $mm)) { unset($L[trim($mm[1])]); $old_ean[$F['db'] === $prod->db_name ? 'prod' : 'dev'][trim($mm[1])] = (int)$ch['product_id']; }
            $pending++;
        }
        foreach ($F['changes'] as $ch) if (preg_match('/^(?:1-?)?BOC-(.+)$/i', $ch['upc'], $mm)) $L[trim($mm[1])] = true;
        unset($L);
        say("fix data {$F['db']}: $pending changes pending, counted as applied");
    }
    $linked = $lp + $ld;
    say('indexing product tokens...');
    $tok = array('prod' => token_index($prod), 'dev' => token_index($dev));
    $cards = hoegert_cards($prod);
    $attr = attributes_by_uk($prod);
    $vdict = value_dictionary($prod);
    $cn_p = category_names($prod);
    $cn_d = category_names($dev);
    $kw = taken_keywords($prod) + taken_keywords($dev);
    $sku_next = numeric_sku_max($prod) + 1;
    say('feed=' . count($feed) . ' csv=' . count($csv) . ' hoegert cards=' . count($cards));

    $products = array(); $skipped = array(); $files = array(); $warn = array(); $cache = array(); $todo = array();
    foreach ($feed as $ean => $f) {
        $ean = (string)$ean;
        if (isset($linked[$ean])) continue;
        $skip = function ($why) use (&$skipped, $ean, $f) { $skipped[] = array($ean, $f['code'], $f['name'], $why); };
        if (!$f['available']) { $skip('not available in the feed'); continue; }
        $price_uah = isset($csv[$ean]) ? $csv[$ean]['price'] : (float)$f['price'];
        if ($price_uah <= 0) { $skip('no price'); continue; }
        foreach (array(norm_code($f['code']), norm_code($ean)) as $nc)
            if (mb_strlen($nc) >= 5) foreach ($tok as $env => $t)
                if (isset($t[$nc])) {
                    // the EAN is still in the upc of a card that gives it up in a pending fix: not a duplicate
                    if ($nc === norm_code($ean) && isset($old_ean[$env][$ean]) && $old_ean[$env][$ean] == $t[$nc]) continue;
                    $skip("article/EAN found in existing $env product #" . $t[$nc]); continue 3;
                }
        $o_ = isset($ov[$ean]) ? $ov[$ean] : array();
        $flags = array(); $src = array();

        // eopt ru page
        $page = eopt_ru_page($f['url'], "$out/pages");
        if ($page === null) $flags[] = 'ru page: not fetched';

        // clothing sibling
        $sib = null; $fam = clothing_family($f['code']);
        if ($fam) {
            $best = -1;
            foreach ($cards as $c) {
                $cf = clothing_family($c['article']);
                if (!$cf || $cf[0] !== $fam[0]) continue;
                similar_text(mb_strtolower(preg_replace('/\([^()]*\)$/u', '', dec($c['name_uk']))), mb_strtolower($f['name']), $pc);
                $score = $pc + ($c['status'] ? 100 : 0) + ($c['image'] ? 50 : 0);
                if ($score > $best) { $best = $score; $sib = $c + array('size' => $cf[1]); }
            }
            if ($sib) $src[] = "sibling #{$sib['product_id']} {$sib['article']}";
            else $flags[] = 'clothing: no sibling card';
        }

        // category
        $cat = 0; $cat_src = '';
        if (!empty($o_['category_id'])) { $cat = (int)$o_['category_id']; $cat_src = 'override'; }
        elseif ($sib && $sib['category']) { $cat = $sib['category']; $cat_src = 'sibling'; }
        else {
            $v = prefix_vote($cards, $f['code']);
            if ($v) {
                $share = reset($v['votes']) / array_sum($v['votes']);
                if ($share >= 0.7) { $cat = (int)key($v['votes']); $cat_src = sprintf('vote %s %d/%d', $v['prefix'], reset($v['votes']), array_sum($v['votes'])); }
            }
        }
        if (!$cat) {
            $v = prefix_vote($cards, $f['code']); $vs = array();
            if ($v) foreach (array_slice($v['votes'], 0, 3, true) as $cid => $n) $vs[] = "$cid " . category_path($cn_p, $cid, LANG_UK) . ": $n";
            $skip('no category; eopt: ' . ($page ? implode(' > ', $page['crumbs']) : '?') . '; prefix vote: ' . implode(' | ', $vs));
            continue;
        }
        foreach (array('prod' => $cn_p, 'dev' => $cn_d) as $env => $cn)
            if (empty($cn[$cat][LANG_UK]) || !$cn[$cat][LANG_UK]['status']) fail("$ean: category $cat missing or disabled on $env");

        // names
        $name_uk = $f['name'];
        $name_ru = '';
        if (!empty($o_['name_ru'])) { $name_ru = clean_name($o_['name_ru']); $src[] = 'name ru: override' . (!empty($o_['note']) ? " ({$o_['note']})" : ''); }
        elseif ($page && $page['name'] !== '' && $page['name'] !== $name_uk) { $name_ru = $page['name']; $src[] = 'name ru: eopt'; }
        else $todo[$ean]['name'] = $name_uk;

        // descriptions: feed uk + eopt ru; clothing without feed text takes the sibling's
        $desc_uk = clean_description($f['description'], $root, $files, $warn, $cache);
        $desc_ru = '';
        if (plain_len($desc_uk) == 0 && $sib && plain_len($sib['desc_uk']) > 0) {
            $desc_uk = clean_description($sib['desc_uk'], $root, $files, $warn, $cache);
            $desc_ru = clean_description($sib['desc_ru'], $root, $files, $warn, $cache);
            $src[] = 'description: sibling';
        } elseif (plain_len($desc_uk) > 0) {
            if (!empty($o_['description_ru'])) { $desc_ru = clean_description($o_['description_ru'], $root, $files, $warn, $cache); $src[] = 'description ru: override'; }
            elseif ($page && plain_len($page['description']) > 0 && dec(strip_tags($page['description'])) !== dec(strip_tags($desc_uk))) {
                $desc_ru = clean_description($page['description'], $root, $files, $warn, $cache); $src[] = 'description ru: eopt';
            } else $todo[$ean]['description'] = $desc_uk;
        }
        if (plain_len($desc_uk) == 0) $flags[] = 'description: NONE';

        // images: feed picture, else the eopt page has none either; clothing takes the sibling's photos
        $imgs = array();
        if ($f['image'] !== '') { $rel = fetch_image($f['image'], IMG_PRODUCT_DIR, $root, $files, $warn, $cache); if ($rel !== null) $imgs[] = $rel; }
        if (!$imgs && $sib && $sib['image']) {
            $imgs = array_merge(array($sib['image']), card_gallery($prod, $sib['product_id']));
            foreach ($imgs as $rel) { if (!is_file("$root/image/$rel")) $warn[] = "$ean: sibling image missing in $root: $rel"; else $files[$rel] = md5_file("$root/image/$rel"); }
            $src[] = 'images: sibling';
        }
        $imgs = array_values(array_unique($imgs));
        if (!$imgs) $flags[] = 'images: NONE';

        // attributes: feed params (uk); ru name from the DB attribute or the eopt feature at the same position; ru value from
        // the eopt feature, the DB dictionary, the value itself without Cyrillic, or the override
        $attrs = array(); $seen = array();
        // eopt ru features come in another order and may carry extra lines: match by ru attribute name, or by an equal value
        $feat_by_name = array(); $feat_by_value = array();
        if ($page) foreach ($page['features'] as $fe) {
            $feat_by_name[mb_strtolower(rtrim($fe[0], ':, '))] = $fe[1];
            if (!has_cyrillic($fe[1])) $feat_by_value[$fe[1]][] = $fe[0];
        }
        $params = $f['params'];
        if (!$params && $sib) {
            foreach (card_attributes($prod, $sib['product_id']) as $a) {
                $t_uk = isset($a['text_uk']) ? $a['text_uk'] : '';
                $t_ru = isset($a['text_ru']) ? $a['text_ru'] : '';
                if (mb_stripos($a['name_uk'], 'розмір') === 0) { $t_uk = $t_ru = $fam[1]; }
                $params[] = array('name' => $a['name_uk'], 'value' => $t_uk, 'ru' => $t_ru);
            }
            if ($params) $src[] = 'attributes: sibling';
        }
        foreach ($params as $i => $p) {
            if ($p['name'] === '' || $p['value'] === '') continue;
            $k = mb_strtolower($p['name']);
            if (isset($seen[$k])) continue;
            $seen[$k] = true;
            $a = array('name_uk' => cut($p['name'], 64), 'text_uk' => $p['value'], 'attribute_id' => isset($attr[$k]) ? $attr[$k]['id'] : 0);
            $vm = isset($feat_by_value[$p['value']]) && count($feat_by_value[$p['value']]) == 1 ? $feat_by_value[$p['value']][0] : '';
            if (isset($attr[$k]) && $attr[$k]['ru'] !== '') $a['name_ru'] = $attr[$k]['ru'];
            elseif (!empty($o_['attr_names'][$p['name']])) $a['name_ru'] = cut($o_['attr_names'][$p['name']], 64);
            elseif ($vm !== '') $a['name_ru'] = cut(rtrim($vm, ':, '), 64);
            else { $todo[$ean]['attr_names'][] = $p['name']; $a['name_ru'] = ''; }
            $fk = mb_strtolower(rtrim($a['name_ru'], ':, '));
            if (isset($p['ru']) && $p['ru'] !== '') $a['text_ru'] = $p['ru'];
            elseif (!empty($o_['attr_values'][$p['value']])) $a['text_ru'] = $o_['attr_values'][$p['value']];
            elseif (isset($vdict[$k . '|' . $p['value']])) $a['text_ru'] = $vdict[$k . '|' . $p['value']];
            elseif (!has_cyrillic($p['value'])) $a['text_ru'] = $p['value'];
            elseif ($fk !== '' && isset($feat_by_name[$fk])) $a['text_ru'] = $feat_by_name[$fk];
            else { $todo[$ean]['attr_values'][] = $p['value']; $a['text_ru'] = ''; }
            $attrs[] = $a;
        }
        if ($name_ru === '' || (plain_len($desc_uk) > 0 && $desc_ru === '')) $flags[] = 'ru text: MISSING (run translate)';
        foreach ($attrs as $a) if ($a['name_ru'] === '' || $a['text_ru'] === '') { $flags[] = 'ru attribute: MISSING (run translate)'; break; }

        // seo keywords unique across both DBs and this batch
        $keys = array();
        foreach (array(LANG_UK => array($name_uk, 'sug_translit_ukr'), LANG_RU => array($name_ru !== '' ? $name_ru : $name_uk, 'sug_translit_rus')) as $lang => $a) {
            $k = cut(slug($a[0], $a[1]), 250); $base = $k; $i = 2;
            if ($k === '') $k = $base = 'hoegert-' . mb_strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $f['code']));
            if ($lang == LANG_RU && $k === $keys[LANG_UK]) { $keys[$lang] = $k; continue; }
            while (isset($kw[$k])) { $k = "$base-$i"; $i++; }
            if ($k !== $base) $flags[] = "seo keyword ($lang): suffixed";
            $kw[$k] = true; $keys[$lang] = $k;
        }

        $status = ($imgs && plain_len($desc_uk) > 0) ? 1 : 0;
        $products[] = array(
            'product_id' => $id++, 'sku' => (string)$sku_next++, 'upc' => UPC_PREFIX . $ean, 'ean' => $ean, 'supplier_code' => $f['code'],
            'quantity' => isset($csv[$ean]) ? $csv[$ean]['stock'] : (int)$f['stock'], 'qty_src' => isset($csv[$ean]) ? 'csv' : 'feed',
            'price' => round($price_uah / RATE, 4), 'price_uah' => $price_uah, 'status' => $status,
            'image' => $imgs ? $imgs[0] : '', 'gallery' => array_slice($imgs, 1),
            'category_id' => $cat, 'category_src' => $cat_src,
            'category_uk' => $cn_p[$cat][LANG_UK]['name'], 'category_ru' => isset($cn_p[$cat][LANG_RU]) ? $cn_p[$cat][LANG_RU]['name'] : $cn_p[$cat][LANG_UK]['name'],
            'name_uk' => $name_uk, 'name_ru' => $name_ru, 'description_uk' => $desc_uk, 'description_ru' => $desc_ru,
            'keyword_uk' => $keys[LANG_UK], 'keyword_ru' => $keys[LANG_RU], 'attributes' => $attrs,
            'eopt_category' => $page ? implode(' > ', $page['crumbs']) : '', 'sources' => $src, 'flags' => $flags,
        );
        if (isset($todo[$ean])) $todo[$ean] += array('code' => $f['code'], 'name_uk' => $name_uk);
    }

    file_put_contents("$out/payload.json", json_encode(array('built' => date('c'), 'no_images' => $GLOBALS['NO_IMAGES'], 'products' => $products), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    ksort($files);
    file_put_contents("$out/files.json", json_encode($files, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    file_put_contents("$out/translate_todo.json", json_encode($todo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    $fh = fopen("$out/preview.csv", 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, array('ean', 'article', 'product_id', 'status', 'name_uk', 'name_ru', 'category_id', 'category_path', 'category_src', 'eopt_category',
        'price_uah', 'quantity', 'qty_src', 'images', 'attributes', 'desc_uk_chars', 'desc_ru_chars', 'keyword_uk', 'keyword_ru', 'sources', 'flags'), ';');
    foreach ($products as $p) fputcsv($fh, array($p['ean'], $p['supplier_code'], $p['product_id'], $p['status'], $p['name_uk'], $p['name_ru'],
        $p['category_id'], category_path($cn_p, $p['category_id'], LANG_UK), $p['category_src'], $p['eopt_category'], $p['price_uah'], $p['quantity'], $p['qty_src'],
        ($p['image'] ? 1 : 0) + count($p['gallery']), count($p['attributes']), plain_len($p['description_uk']), plain_len($p['description_ru']),
        $p['keyword_uk'], $p['keyword_ru'], implode('; ', $p['sources']), implode('; ', $p['flags'])), ';');
    fclose($fh);
    $fh = fopen("$out/skipped.csv", 'w');
    fwrite($fh, "\xEF\xBB\xBF");
    fputcsv($fh, array('ean', 'article', 'name', 'reason'), ';');
    foreach ($skipped as $s) fputcsv($fh, $s, ';');
    fclose($fh);
    file_put_contents("$out/warnings.txt", implode("\n", $warn) . "\n");

    $reasons = array();
    foreach ($skipped as $s) { $r = preg_replace('/#\d+$|;.*$/', '', $s[3]); @$reasons[$r]++; }
    $en = count(array_filter($products, function ($p) { return $p['status']; }));
    say('products=' . count($products) . " (enabled $en) skipped=" . count($skipped) . ' files=' . count($files) . ' warnings=' . count($warn) . ' to translate=' . count($todo));
    foreach ($reasons as $r => $c) say("  skipped: $c  $r");
    say('provisional sku from ' . ($sku_next - count($products)) . ' (final sku is assigned at apply with --assign-sku)');
}

// ---------------------------------------------------------------- translate (Gemini)

function vertex_token($sa) {
    $b64 = function ($s) { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); };
    $now = time();
    $h = $b64(json_encode(array('alg' => 'RS256', 'typ' => 'JWT')));
    $c = $b64(json_encode(array('iss' => $sa['client_email'], 'scope' => 'https://www.googleapis.com/auth/cloud-platform',
        'aud' => 'https://oauth2.googleapis.com/token', 'iat' => $now, 'exp' => $now + 3600)));
    if (!openssl_sign("$h.$c", $sig, $sa['private_key'], 'sha256WithRSAEncryption')) fail('openssl_sign failed');
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 30,
        CURLOPT_POSTFIELDS => http_build_query(array('grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => "$h.$c." . $b64($sig)))));
    $r = json_decode(curl_exec($ch), true);
    if (empty($r['access_token'])) fail('no Vertex access token');
    return $r['access_token'];
}
function cmd_translate($o) {
    $todo = json_decode(file_get_contents(need($o, 'todo')), true);
    if (!is_array($todo)) fail('bad todo file');
    if (!$todo) { say('nothing to translate'); return; }
    $ovf = need($o, 'overrides');
    $ov = is_file($ovf) ? json_decode(file_get_contents($ovf), true) : array();
    if (!is_array($ov)) fail('overrides: invalid JSON');
    $env = array();
    foreach (file(need($o, 'env'), FILE_IGNORE_NEW_LINES) as $l) if (preg_match('/^(VERTEX_[A-Z_]+)=(.*)$/', $l, $m)) $env[$m[1]] = trim($m[2], "\"'");
    $sa = isset($env['VERTEX_SERVICE_ACCOUNT_JSON']) ? $env['VERTEX_SERVICE_ACCOUNT_JSON'] : '';
    $sa = is_file($sa) ? json_decode(file_get_contents($sa), true) : json_decode($sa, true);
    if (!$sa || empty($env['VERTEX_PROJECT_ID'])) fail('VERTEX_* settings not found');

    $prompt = "Translate Ukrainian product texts of an online tool shop (HOEGERT brand) into Russian.\n"
        . "Rules: keep the brand, article codes in brackets, numbers, units and sizes unchanged; keep HTML tags exactly as they are, translate only text;\n"
        . "use normal Russian trade terms (e.g. 'Головка торцевая', 'Отвертка', 'Перчатки'); do not add or drop information.\n"
        . "Return JSON only, same keys as the input: for every EAN an object with name_ru (if 'name' given), description_ru (if 'description' given),\n"
        . "attr_names (object uk -> ru, if 'attr_names' given), attr_values (object uk -> ru, if 'attr_values' given).\n\nInput:\n"
        . json_encode($todo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $url = "https://aiplatform.googleapis.com/v1/projects/{$env['VERTEX_PROJECT_ID']}/locations/global/publishers/google/models/" . GEMINI_MODEL . ':generateContent';
    $ch = curl_init($url);
    $body = array(
        'contents' => array(array('role' => 'user', 'parts' => array(array('text' => $prompt)))),
        'generationConfig' => array('temperature' => 0.1, 'responseMimeType' => 'application/json'),
    );
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_TIMEOUT => 600,
        CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . vertex_token($sa), 'Content-Type: application/json'),
        CURLOPT_POSTFIELDS => json_encode($body)));
    $r = json_decode(curl_exec($ch), true);
    if (!isset($r['candidates'][0]['content']['parts'])) fail('Gemini: ' . mb_substr(json_encode($r), 0, 500));
    $txt = '';
    foreach ($r['candidates'][0]['content']['parts'] as $p) if (isset($p['text'])) $txt .= $p['text'];
    $res = json_decode($txt, true);
    if (!is_array($res)) fail('Gemini answer is not JSON: ' . mb_substr($txt, 0, 300));
    $model = isset($r['modelVersion']) ? $r['modelVersion'] : GEMINI_MODEL;
    $added = 0;
    foreach ($todo as $ean => $t) {
        if (!isset($res[$ean])) { say("no answer for $ean"); continue; }
        $a = $res[$ean];
        if (!isset($ov[$ean])) $ov[$ean] = array();
        foreach (array('name' => 'name_ru', 'description' => 'description_ru') as $in => $k)
            if (isset($t[$in]) && empty($ov[$ean][$k]) && !empty($a[$k])) { $ov[$ean][$k] = $a[$k]; $added++; }
        foreach (array('attr_names', 'attr_values') as $k)
            if (!empty($t[$k]) && !empty($a[$k]) && is_array($a[$k]))
                foreach ($t[$k] as $uk) if (empty($ov[$ean][$k][$uk]) && !empty($a[$k][$uk])) { $ov[$ean][$k][$uk] = $a[$k][$uk]; $added++; }
        $ov[$ean]['note'] = trim((isset($ov[$ean]['note']) ? $ov[$ean]['note'] . '; ' : '') . "ru: Gemini translation ($model, " . date('Y-m-d') . ')', '; ');
    }
    file_put_contents($ovf, json_encode($ov, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    say("added $added texts to $ovf (model $model)");
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

/** Attribute in group 8 by UK name; created with the given RU name when missing. */
function resolve_attribute($m, $a, &$created, $dry) {
    static $cache = array();
    $key = $m->db_name . '|' . mb_strtolower($a['name_uk']);
    if (isset($cache[$key])) return $cache[$key];
    $r = rows($m, "SELECT a.attribute_id, (SELECT COUNT(*) FROM oc_product_attribute pa WHERE pa.attribute_id=a.attribute_id) used
                   FROM oc_attribute a JOIN oc_attribute_description d ON d.attribute_id=a.attribute_id AND d.language_id=" . LANG_UK . "
                   WHERE a.attribute_group_id=" . ATTR_GROUP . " AND d.name=" . e($m, $a['name_uk']) . " ORDER BY used DESC, a.attribute_id LIMIT 1");
    if ($r) return $cache[$key] = (int)$r[0]['attribute_id'];
    // the existing UK name may carry a typo ("Масимальний діаметр ..."): fall back to the RU name
    if ($a['name_ru'] !== '') {
        $r = rows($m, "SELECT a.attribute_id, (SELECT COUNT(*) FROM oc_product_attribute pa WHERE pa.attribute_id=a.attribute_id) used
                       FROM oc_attribute a JOIN oc_attribute_description d ON d.attribute_id=a.attribute_id AND d.language_id=" . LANG_RU . "
                       WHERE a.attribute_group_id=" . ATTR_GROUP . " AND d.name=" . e($m, $a['name_ru']) . " ORDER BY used DESC, a.attribute_id LIMIT 1");
        if ($r) return $cache[$key] = (int)$r[0]['attribute_id'];
    }
    if ($dry) { $created[] = array('attribute_id' => 0, 'name_uk' => $a['name_uk']); return $cache[$key] = 0; }
    q($m, "INSERT INTO oc_attribute SET attribute_group_id=" . ATTR_GROUP . ", sort_order=0");
    $aid = (int)$m->insert_id;
    q($m, "INSERT INTO oc_attribute_description (attribute_id, language_id, name) VALUES ($aid, " . LANG_UK . ", " . e($m, $a['name_uk']) . "), ($aid, " . LANG_RU . ", " . e($m, $a['name_ru']) . ")");
    $created[] = array('attribute_id' => $aid, 'name_uk' => $a['name_uk'], 'name_ru' => $a['name_ru']);
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
    foreach ($P['products'] as $p) {
        if ($p['name_ru'] === '' || (plain_len($p['description_uk']) > 0 && $p['description_ru'] === '')) fail("{$p['ean']}: ru text missing, run translate and rebuild");
        foreach ($p['attributes'] as $a) if ($a['name_ru'] === '' || $a['text_ru'] === '') fail("{$p['ean']}: ru attribute missing, run translate and rebuild");
    }
    $m = db_connect(need($o, 'config'), false);
    say('DB=' . $m->db_name . ($dry ? ' (dry run, rolled back at the end)' : ''));
    if (!rows($m, 'SELECT 1 FROM oc_manufacturer WHERE manufacturer_id=' . MANUFACTURER_ID)) fail('manufacturer ' . MANUFACTURER_ID . ' missing');

    $ids = array_column($P['products'], 'product_id');
    $created = array();
    q($m, 'START TRANSACTION');
    try {
        $busy = rows($m, 'SELECT product_id FROM oc_product WHERE product_id IN (' . implode(',', array_map('intval', $ids)) . ')');
        if ($busy) throw new RuntimeException('product_id already used: ' . implode(',', array_column($busy, 'product_id')));
        $linked = linked_eans($m);
        foreach ($P['products'] as $p) {
            if (isset($linked[$p['ean']])) throw new RuntimeException("{$p['ean']} is already linked on this site");
            foreach (array_unique(array($p['keyword_uk'], $p['keyword_ru'])) as $k)
                if (rows($m, 'SELECT seo_url_id FROM oc_seo_url WHERE keyword=' . e($m, $k))) throw new RuntimeException("seo keyword taken: $k");
            if (!rows($m, 'SELECT category_id FROM oc_category WHERE status=1 AND category_id=' . (int)$p['category_id'])) throw new RuntimeException("category {$p['category_id']} missing/disabled");
        }
        if (isset($o['assign-sku'])) {
            q($m, 'LOCK TABLES oc_product WRITE');
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
                shipping=1, price=" . sprintf('%.4F', $p['price']) . ", cost_price=0, points=0, tax_class_id=0, date_available=CURDATE(),
                weight=0, weight_class_id=1, length=0, width=0, height=0, length_class_id=1, subtract=0, minimum=1, sort_order=0, status=" . (int)$p['status'] . ",
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
                if (!$aid || isset($seen[$aid])) continue;
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
        if ($created) say('new attributes: ' . implode(' | ', array_column($created, 'name_uk')));
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
    case 'translate': cmd_translate($o); break;
    case 'sync-files': cmd_sync_files($o); break;
    case 'remove-files': cmd_remove_files($o); break;
    case 'apply': cmd_apply($o); break;
    case 'rollback': cmd_rollback($o); break;
    default: fail('command: build | translate | sync-files | remove-files | apply | rollback');
}
