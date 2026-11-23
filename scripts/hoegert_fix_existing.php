<?php
/**
 * Fix the supplier link of existing HOEGERT cards (upc BOC-<EAN>), see save/hoegert_20261121/PLAN.md step 1.
 *
 *  - re-hook: the card's name carries an article (last bracket), the eopt feed gives that article exactly one EAN,
 *    and the card holds a different EAN (work clothing with rotated sizes). upc becomes BOC-<EAN of the named article>;
 *    name, URL, photos stay. quantity and price are set from the parser CSV the way the cron writes them
 *    (in CSV: quantity = stock, price = CSV UAH / 45; not in CSV: quantity 0, price kept);
 *  - ean_changed: the card's EAN is gone from the feed, its article is in the feed under exactly one other EAN that
 *    no card holds (supplier re-coded the article): upc becomes BOC-<new EAN>, quantity/price as above;
 *  - hook: HOEGERT cards whose upc is a bare feed article (HT2C318) get BOC-<EAN>, quantity/price as above,
 *    unless another card already holds that EAN (then it is a duplicate card: reported, not changed);
 *  - unhook: the listed cards get the shop's '1' prefix (1BOC-<EAN>), so no cron touches them; quantity kept.
 *
 * Commands (run with /usr/local/lsws/lsphp74/bin/php):
 *
 *   build    --config=<config.php of the target DB> --feed=<eopt xml> --csv=<6_hoegert.csv> --out=<data.json>
 *            [--unhook=<product_id,product_id,...>]
 *            Reads the target DB read-only and writes the change list with the expected current values.
 *
 *   apply    --config=<config.php> --data=<data.json> --journal=<json> [--dry-run]
 *            One transaction. Every change checks the current upc first; any mismatch aborts.
 *
 *   rollback --config=<config.php> --journal=<json> [--dry-run]
 *            Restores upc, quantity, price; aborts when a card changed its upc since apply.
 */

error_reporting(E_ALL);
ini_set('memory_limit', '1024M');
mb_internal_encoding('UTF-8');

define('UPC_PREFIX', 'BOC-');
define('RATE', 45.0);
define('MANUFACTURER_ID', 832);
define('LANG_UK', 3);

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

function dec($s) { for ($i = 0; $i < 3; $i++) $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8'); return trim(preg_replace('/\s+/u', ' ', $s)); }
/** Article in the last bracket of a name; eopt names sometimes carry '((CODE)'. */
function name_article($name) { return preg_match('/\(+([^()]+)\)\s*$/u', dec($name), $m) ? mb_strtoupper(trim($m[1])) : ''; }

function parse_feed($file) {
    $x = simplexml_load_file($file, null, LIBXML_NONET | LIBXML_PARSEHUGE | LIBXML_NOCDATA);
    if (!$x) fail("cannot parse $file");
    $by_ean = array(); $by_code = array();
    foreach ($x->shop->offers->offer as $o) {
        $ean = trim((string)$o->sku); $code = mb_strtoupper(trim((string)$o->code));
        if ($ean === '' || $code === '') continue;
        $by_ean[$ean][$code] = true;
        $by_code[$code][$ean] = true;
    }
    return array($by_ean, $by_code);
}
function parse_csv($file) {
    $f = fopen($file, 'r');
    if (!$f) fail("cannot open $file");
    $h = fgetcsv($f, 0, ';');
    if ($h !== array('sku', 'price', 'stock', 'purchase', 'vat')) fail('unexpected CSV header: ' . implode(';', (array)$h));
    $out = array();
    while ($r = fgetcsv($f, 0, ';')) if (count($r) >= 3) $out[trim($r[0])] = array('price' => (float)$r[1], 'stock' => (int)$r[2]);
    if (count($out) < 1000) fail('CSV has only ' . count($out) . ' rows');
    return $out;
}
/** What the cron would write for this EAN. */
function cron_values($csv, $ean, $card) {
    if (isset($csv[$ean])) return array('quantity' => $csv[$ean]['stock'], 'price' => round($csv[$ean]['price'] / RATE, 4));
    return array('quantity' => 0, 'price' => (float)$card['price']);
}

function cmd_build($o) {
    list($by_ean, $by_code) = parse_feed(need($o, 'feed'));
    $csv = parse_csv(need($o, 'csv'));
    $m = db_connect(need($o, 'config'), true);
    $unhook_ids = isset($o['unhook']) ? array_filter(array_map('intval', explode(',', $o['unhook']))) : array();

    $cards = rows($m, "SELECT p.product_id, p.upc, p.quantity, p.price, p.manufacturer_id, d.name FROM oc_product p
                       LEFT JOIN oc_product_description d ON d.product_id=p.product_id AND d.language_id=" . LANG_UK . "
                       WHERE p.upc LIKE '%BOC-%' OR p.manufacturer_id=" . MANUFACTURER_ID);
    $holder = array(); // EAN -> product_ids holding BOC-<EAN>
    foreach ($cards as $c) foreach (explode(',', $c['upc']) as $t) if (preg_match('/^BOC-(.+)$/i', trim($t), $mm)) $holder[trim($mm[1])][] = (int)$c['product_id'];
    $by_id = array(); foreach ($cards as $c) $by_id[(int)$c['product_id']] = $c;

    $changes = array(); $report = array();
    foreach ($cards as $c) {
        $pid = (int)$c['product_id'];
        if (in_array($pid, $unhook_ids)) continue;
        $art = name_article((string)$c['name']);
        if (preg_match('/^BOC-([^,]+)$/i', $c['upc'], $mm)) {
            $ean = trim($mm[1]);
            if ($art === '') continue;
            if (!isset($by_ean[$ean])) {
                // EAN gone from the feed: the supplier may have re-coded the same article (one free real EAN in the feed)
                if (!isset($by_code[$art]) || count($by_code[$art]) != 1) continue;
                $target = key($by_code[$art]);
                if (!preg_match('/^\d{8,14}$/', $target) || !empty($holder[$target])) continue;
                $changes[] = array('kind' => 'ean_changed', 'product_id' => $pid, 'article' => $art, 'expect_upc' => $c['upc'], 'upc' => UPC_PREFIX . $target)
                    + cron_values($csv, $target, $c) + array('name' => dec($c['name']));
                continue;
            }
            if (isset($by_ean[$ean][$art])) continue; // matches
            if (!isset($by_code[$art])) { $report[] = "$pid: card article $art not in feed, holds EAN of " . implode('/', array_keys($by_ean[$ean])) . ' (not changed)'; continue; }
            if (count($by_code[$art]) != 1) { $report[] = "$pid: card article $art has several EANs in feed (not changed)"; continue; }
            $target = key($by_code[$art]);
            $changes[] = array('kind' => 'rehook', 'product_id' => $pid, 'article' => $art, 'expect_upc' => $c['upc'], 'upc' => UPC_PREFIX . $target)
                + cron_values($csv, $target, $c) + array('name' => dec($c['name']));
        } elseif ((int)$c['manufacturer_id'] === MANUFACTURER_ID && strpos($c['upc'], 'BOC-') === false) {
            $code = mb_strtoupper(trim($c['upc']));
            if ($code === '' || !isset($by_code[$code])) { $report[] = "$pid: HOEGERT card with upc '{$c['upc']}' not linkable (not changed)"; continue; }
            if (count($by_code[$code]) != 1) { $report[] = "$pid: upc $code has several EANs in feed (not changed)"; continue; }
            $target = key($by_code[$code]);
            if (!empty($holder[$target])) { $report[] = "$pid: upc $code = EAN $target, which card #" . implode(',', $holder[$target]) . ' already holds: duplicate card (not changed)'; continue; }
            $changes[] = array('kind' => 'hook', 'product_id' => $pid, 'article' => $code, 'expect_upc' => $c['upc'], 'upc' => UPC_PREFIX . $target)
                + cron_values($csv, $target, $c) + array('name' => dec($c['name']));
        }
    }
    foreach ($unhook_ids as $pid) {
        if (!isset($by_id[$pid])) fail("unhook: product $pid not found or not a HOEGERT/BOC card");
        $c = $by_id[$pid];
        if (!preg_match('/^BOC-[^,]+$/i', $c['upc'])) fail("unhook: product $pid has upc '{$c['upc']}'");
        $changes[] = array('kind' => 'unhook', 'product_id' => $pid, 'article' => name_article((string)$c['name']), 'expect_upc' => $c['upc'],
            'upc' => '1' . $c['upc'], 'quantity' => (int)$c['quantity'], 'price' => (float)$c['price'], 'name' => dec($c['name']));
    }

    // final state must be consistent: every new BOC- EAN held by exactly one card
    $moving = array(); foreach ($changes as $ch) $moving[$ch['product_id']] = true;
    $final = array();
    foreach ($holder as $ean => $pids) foreach ($pids as $pid) if (!isset($moving[$pid])) $final[$ean][] = $pid;
    foreach ($changes as $ch) if (strpos($ch['upc'], UPC_PREFIX) === 0) $final[substr($ch['upc'], strlen(UPC_PREFIX))][] = $ch['product_id'];
    foreach ($changes as $ch) {
        if (strpos($ch['upc'], UPC_PREFIX) !== 0) continue;
        $ean = substr($ch['upc'], strlen(UPC_PREFIX));
        if (count($final[$ean]) > 1) fail("EAN $ean would be held by several cards: " . implode(',', $final[$ean]));
    }

    file_put_contents(need($o, 'out'), json_encode(array('db' => $m->db_name, 'built' => date('c'), 'changes' => $changes, 'report' => $report),
        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    $k = array(); foreach ($changes as $ch) @$k[$ch['kind']]++;
    say('DB=' . $m->db_name . ' changes: ' . json_encode($k));
    foreach ($changes as $ch) say(sprintf('  %-6s #%d %s -> %s qty=%d price=%.4f  %s', $ch['kind'], $ch['product_id'], $ch['expect_upc'], $ch['upc'], $ch['quantity'], $ch['price'], $ch['name']));
    foreach ($report as $r) say("  report: $r");
}

function cmd_apply($o) {
    $D = json_decode(file_get_contents(need($o, 'data')), true);
    if (!$D || !isset($D['changes'])) fail('bad data file');
    $journal_file = need($o, 'journal');
    $dry = isset($o['dry-run']);
    if (!$dry && file_exists($journal_file)) fail("journal $journal_file already exists");
    $m = db_connect(need($o, 'config'), false);
    if ($m->db_name !== $D['db']) fail("data is for {$D['db']}, config points to {$m->db_name}");
    say('DB=' . $m->db_name . ($dry ? ' (dry run, rolled back at the end)' : ''));
    $before = array(); $after = array();
    q($m, 'START TRANSACTION');
    try {
        foreach ($D['changes'] as $ch) {
            $pid = (int)$ch['product_id'];
            $cur = rows($m, "SELECT product_id, upc, quantity, price FROM oc_product WHERE product_id=$pid FOR UPDATE");
            if (!$cur) throw new RuntimeException("product $pid not found");
            $cur = $cur[0];
            if ($cur['upc'] !== $ch['expect_upc']) throw new RuntimeException("product $pid: upc is '{$cur['upc']}', expected '{$ch['expect_upc']}'");
            $before[] = $cur;
            q($m, "UPDATE oc_product SET upc=" . e($m, $ch['upc']) . ", quantity=" . (int)$ch['quantity'] . ", price=" . sprintf('%.4F', $ch['price']) . ",
                   date_modified=NOW() WHERE product_id=$pid");
            if ($m->affected_rows != 1) throw new RuntimeException("product $pid: update affected {$m->affected_rows} rows");
            $after[] = array('product_id' => $pid, 'upc' => $ch['upc'], 'quantity' => (int)$ch['quantity'], 'price' => sprintf('%.4F', $ch['price']));
        }
        // no BOC EAN on two cards after the change
        $dup = rows($m, "SELECT upc, GROUP_CONCAT(product_id) ids FROM oc_product WHERE upc LIKE 'BOC-%' GROUP BY upc HAVING COUNT(*) > 1");
        if ($dup) throw new RuntimeException('duplicate upc after change: ' . json_encode($dup));
        say('updated ' . count($after) . ' cards');
        if ($dry) { q($m, 'ROLLBACK'); say('dry run: rolled back'); return; }
        $J = array('db' => $m->db_name, 'applied' => date('c'), 'data' => need($o, 'data'), 'before' => $before, 'after' => $after);
        if (file_put_contents($journal_file, json_encode($J, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) throw new RuntimeException('cannot write journal');
        q($m, 'COMMIT');
        say("committed; journal $journal_file");
    } catch (Exception $ex) {
        q($m, 'ROLLBACK');
        fail('rolled back: ' . $ex->getMessage());
    }
}

function cmd_rollback($o) {
    $J = json_decode(file_get_contents(need($o, 'journal')), true);
    if (!$J || !isset($J['before'])) fail('bad journal');
    $m = db_connect(need($o, 'config'), false);
    if ($m->db_name !== $J['db']) fail("journal is for {$J['db']}, config points to {$m->db_name}");
    $dry = isset($o['dry-run']);
    $after = array(); foreach ($J['after'] as $a) $after[$a['product_id']] = $a;
    q($m, 'START TRANSACTION');
    try {
        foreach ($J['before'] as $b) {
            $pid = (int)$b['product_id'];
            $cur = rows($m, "SELECT upc FROM oc_product WHERE product_id=$pid FOR UPDATE");
            if (!$cur || $cur[0]['upc'] !== $after[$pid]['upc']) throw new RuntimeException("product $pid: upc changed since apply");
            q($m, "UPDATE oc_product SET upc=" . e($m, $b['upc']) . ", quantity=" . (int)$b['quantity'] . ", price=" . e($m, $b['price']) . ", date_modified=NOW() WHERE product_id=$pid");
        }
        say('restored ' . count($J['before']) . ' cards' . ($dry ? ' (dry run)' : ''));
        if ($dry) { q($m, 'ROLLBACK'); return; }
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
    case 'apply': cmd_apply($o); break;
    case 'rollback': cmd_rollback($o); break;
    default: fail('command: build | apply | rollback');
}
