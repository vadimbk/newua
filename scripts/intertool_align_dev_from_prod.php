<?php
/**
 * Copy the INTERTOOL catalogue state from prod (radioshop, read-only) to dev (ls).
 *
 * Dev runs no supplier crons, so its Intertool stock, prices and specials drift.
 * Prod is the source of truth. For every prod product whose upc contains 'INT-'
 * (hooked and deliberately unhooked alike) and that exists on dev with the same
 * product_id, this copies:
 *   oc_product: upc, sku, quantity, price, status, stock_status_id
 *   oc_product_special: all rows (dev rows replaced by prod rows)
 * Names, descriptions, seo urls and images are not touched.
 *
 * The direction is fixed: the source must be DB radioshop, the target DB ls.
 *
 * Usage (lsphp74):
 *   php scripts/intertool_align_dev_from_prod.php --source-config=/var/www/radio-shop.com.ua/html/config.php \
 *       --target-config=/var/www/ls.radio-shop.com.ua/html/config.php --journal=<json> [--dry-run]
 */

error_reporting(E_ALL);
ini_set('memory_limit', '1024M');

function fail($msg) { fwrite(STDERR, "ERROR: $msg\n"); exit(1); }
function say($msg) { echo $msg, "\n"; }
function db_connect($config, $read_only, $expect_db) {
    $src = file_get_contents($config);
    $c = array();
    foreach (array('DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE', 'DB_PORT') as $k) {
        if (!preg_match("/define\\(\\s*'$k'\\s*,\\s*'?([^')]*)'?\\s*\\)/", $src, $m)) fail("$k not found in $config");
        $c[$k] = $m[1];
    }
    if ($c['DB_DATABASE'] !== $expect_db) fail("$config points to {$c['DB_DATABASE']}, expected $expect_db");
    $m = new mysqli($c['DB_HOSTNAME'], $c['DB_USERNAME'], $c['DB_PASSWORD'], $c['DB_DATABASE'], (int)$c['DB_PORT']);
    if ($m->connect_errno) fail('DB connect: ' . $m->connect_error);
    $m->set_charset('utf8mb4');
    if ($read_only) q($m, 'SET SESSION TRANSACTION READ ONLY');
    return $m;
}
function q($m, $sql) {
    $r = $m->query($sql);
    if ($r === false) throw new RuntimeException($m->error . ' | ' . substr($sql, 0, 300));
    return $r;
}
function rows($m, $sql) { return q($m, $sql)->fetch_all(MYSQLI_ASSOC); }
function e($m, $v) { return "'" . $m->real_escape_string((string)$v) . "'"; }

$o = array();
foreach (array_slice($argv, 1) as $a) {
    if (!preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $mm)) fail("bad argument $a");
    $o[$mm[1]] = isset($mm[2]) ? $mm[2] : true;
}
foreach (array('source-config', 'target-config', 'journal') as $k) if (empty($o[$k]) || $o[$k] === true) fail("--$k is required");
$dry = isset($o['dry-run']);
if (!$dry && file_exists($o['journal'])) fail("journal {$o['journal']} already exists");

$src = db_connect($o['source-config'], true, 'radioshop');
$dst = db_connect($o['target-config'], false, 'ls');

$FIELDS = array('upc', 'sku', 'quantity', 'price', 'status', 'stock_status_id');
$prod = array();
foreach (rows($src, "SELECT product_id, " . implode(', ', $FIELDS) . " FROM oc_product WHERE upc LIKE '%INT-%'") as $r) $prod[(int)$r['product_id']] = $r;
if (count($prod) < 5000) fail('prod group has only ' . count($prod) . ' products, looks wrong');
$ids = array_keys($prod);
$in = implode(',', $ids);

$prod_sp = array();
foreach (rows($src, "SELECT product_id, customer_group_id, priority, price, date_start, date_end, timer, product_special_group_id
                     FROM oc_product_special WHERE product_id IN ($in) ORDER BY product_special_id") as $r) $prod_sp[(int)$r['product_id']][] = $r;

q($dst, 'START TRANSACTION');
try {
    $dev = array();
    foreach (rows($dst, "SELECT product_id, " . implode(', ', $FIELDS) . " FROM oc_product WHERE product_id IN ($in) FOR UPDATE") as $r) $dev[(int)$r['product_id']] = $r;
    $dev_sp = array();
    foreach (rows($dst, "SELECT * FROM oc_product_special WHERE product_id IN ($in) ORDER BY product_special_id") as $r) $dev_sp[(int)$r['product_id']][] = $r;

    $missing = array_diff($ids, array_keys($dev));
    $before = array(); $stat = array_fill_keys($FIELDS, 0); $changed = 0; $sp_changed = 0;
    foreach ($prod as $pid => $p) {
        if (!isset($dev[$pid])) continue;
        $d = $dev[$pid]; $set = array();
        foreach ($FIELDS as $f) {
            $same = ($f === 'price') ? abs((float)$d[$f] - (float)$p[$f]) < 0.00005 : ((string)$d[$f] === (string)$p[$f]);
            if (!$same) { $set[] = "$f=" . e($dst, $p[$f]); $stat[$f]++; }
        }
        if ($set) {
            q($dst, "UPDATE oc_product SET " . implode(', ', $set) . ", date_modified=NOW() WHERE product_id=$pid");
            $before[$pid]['product'] = $d; $changed++;
        }
        // specials: compare as normalised sets, replace when different
        $norm = function ($list) {
            $out = array();
            foreach ((array)$list as $s) $out[] = implode('|', array($s['customer_group_id'], $s['priority'], sprintf('%.4F', $s['price']), $s['date_start'], $s['date_end'], $s['timer'], $s['product_special_group_id']));
            sort($out);
            return $out;
        };
        $ps = isset($prod_sp[$pid]) ? $prod_sp[$pid] : array();
        $ds = isset($dev_sp[$pid]) ? $dev_sp[$pid] : array();
        if ($norm($ps) !== $norm($ds)) {
            q($dst, "DELETE FROM oc_product_special WHERE product_id=$pid");
            foreach ($ps as $s)
                q($dst, "INSERT INTO oc_product_special SET product_id=$pid, customer_group_id=" . (int)$s['customer_group_id'] . ", priority=" . (int)$s['priority'] . ",
                    price=" . e($dst, $s['price']) . ", date_start=" . e($dst, $s['date_start']) . ", date_end=" . e($dst, $s['date_end']) . ",
                    timer=" . (int)$s['timer'] . ", product_special_group_id=" . (int)$s['product_special_group_id']);
            $before[$pid]['specials'] = $ds; $sp_changed++;
        }
    }
    say('prod group=' . count($prod) . ' on dev=' . count($dev) . ' missing on dev=' . count($missing) . ($missing ? ' (' . implode(',', array_slice($missing, 0, 10)) . ')' : ''));
    say("products updated=$changed: " . json_encode($stat));
    say("products with specials replaced=$sp_changed");

    // verification inside the transaction: dev now equals prod for the group
    $bad = 0;
    foreach (rows($dst, "SELECT product_id, " . implode(', ', $FIELDS) . " FROM oc_product WHERE product_id IN ($in)") as $r) {
        $p = $prod[(int)$r['product_id']];
        foreach ($FIELDS as $f) if ($f === 'price' ? abs((float)$r[$f] - (float)$p[$f]) >= 0.00005 : (string)$r[$f] !== (string)$p[$f]) { $bad++; break; }
    }
    $sp_dev = (int)rows($dst, "SELECT COUNT(*) c FROM oc_product_special WHERE product_id IN ($in)")[0]['c'];
    $sp_prod = 0; foreach ($prod_sp as $pid => $l) if (isset($dev[$pid])) $sp_prod += count($l);
    say("verify: rows still different=$bad, specials dev=$sp_dev prod=$sp_prod");
    if ($bad || $sp_dev !== $sp_prod) throw new RuntimeException('verification failed');

    if ($dry) { q($dst, 'ROLLBACK'); say('dry run: rolled back'); exit(0); }
    if (file_put_contents($o['journal'], json_encode(array('at' => date('c'), 'before' => $before), JSON_UNESCAPED_UNICODE)) === false) throw new RuntimeException('cannot write journal');
    q($dst, 'COMMIT');
    say('committed; journal ' . $o['journal']);
} catch (Exception $ex) {
    q($dst, 'ROLLBACK');
    fail('rolled back: ' . $ex->getMessage());
}
