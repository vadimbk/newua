<?php
/**
 * Clean up existing Grand Instrument cards (upc GI-), see save/gi_20261120/PLAN.md step 2.
 *
 *  - create manufacturers that the supplier uses but the site lacks (Powerlift, Delkol, Spin);
 *  - set the manufacturer shown on grandinstrument.ua for cards stored as 'Без бренда', empty, look-alike names;
 *  - disable cards that are absent from grandinstrument.ua, absent from the supplier CSV and have quantity 0;
 *  - fix the upc of cards whose article was typed with Cyrillic look-alike letters (GI-FSEB12З0 -> GI-FSEB1230),
 *    so the cron (5_toptul.csv) finds them again.
 *
 * Commands (run with /usr/local/lsws/lsphp74/bin/php):
 *
 *   build    --prod-config=<config.php> --offbrand=<gi_offbrand_final.csv> --csv=<5_toptul.csv> --out=<data.json>
 *            Reads prod read-only and writes the change list with the expected current values.
 *
 *   apply    --config=<config.php> --data=<data.json> --journal=<json> [--no-quantity-guard] [--dry-run]
 *            One transaction. Every change checks upc and the current value first; any mismatch aborts.
 *            --no-quantity-guard: do not require quantity 0 for disabling (staging stock is stale).
 *
 *   rollback --config=<config.php> --journal=<json> [--dry-run]
 */

error_reporting(E_ALL);
ini_set('memory_limit', '1024M');
mb_internal_encoding('UTF-8');

// brand on grandinstrument.ua -> manufacturer name on the site
$BRAND_MANUFACTURER = array(
    'Україна' => 'Україна', 'Powerlift' => 'Powerlift', 'ХЗСО' => 'ХЗСО', 'Aomai' => 'Aomai', 'Delkol' => 'Delkol',
    'Toptul' => 'Toptul', 'Standart' => 'Стандарт', 'Spin' => 'Spin',
);
// manufacturers to create when missing: name => seo keyword (same keyword for both languages, as most manufacturers have)
$NEW_MANUFACTURERS = array('Powerlift' => 'powerlift', 'Delkol' => 'delkol', 'Spin' => 'spin');

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
function norm_code($s) {
    $s = mb_strtoupper($s);
    $s = strtr($s, array('А' => 'A', 'В' => 'B', 'С' => 'C', 'Е' => 'E', 'Н' => 'H', 'К' => 'K', 'М' => 'M', 'О' => 'O', 'Р' => 'P',
        'Т' => 'T', 'Х' => 'X', 'З' => '3', 'І' => 'I', 'У' => 'Y'));
    return preg_replace('/[^A-Z0-9\x{0400}-\x{04FF}]/u', '', $s);
}
function manufacturer_ids($m) {
    $out = array();
    foreach (rows($m, "SELECT manufacturer_id, name FROM oc_manufacturer") as $r) $out[$r['name']][] = (int)$r['manufacturer_id'];
    return $out;
}

// ---------------------------------------------------------------- build

function cmd_build($o) {
    global $BRAND_MANUFACTURER, $NEW_MANUFACTURERS;
    $m = db_connect(need($o, 'prod-config'), true);
    $csv = array();
    $f = fopen(need($o, 'csv'), 'r');
    fgetcsv($f, 0, ';');
    while ($r = fgetcsv($f, 0, ';')) $csv[(string)$r[0]] = (int)$r[2];
    $mi = manufacturer_ids($m);
    $cur = array();
    foreach (rows($m, "SELECT p.product_id, p.upc, p.status, p.quantity, p.manufacturer_id, COALESCE(m.name,'') mname
                       FROM oc_product p LEFT JOIN oc_manufacturer m ON m.manufacturer_id=p.manufacturer_id WHERE p.upc LIKE 'GI-%'") as $r)
        $cur[(int)$r['product_id']] = $r;

    $changes = array(); $report = array();

    // 1. look-alike upc: the card's code is not in the CSV, but exactly one CSV code matches after folding, and no card uses it yet
    $byNorm = array();
    foreach ($csv as $code => $_) $byNorm[norm_code($code)][] = $code;
    // MariaDB compares upc case-insensitively and ignores trailing spaces, so the cron already matches such cards
    $csvCi = array();
    foreach ($csv as $code => $_) $csvCi[strtoupper(rtrim($code))] = true;
    $used = array();
    foreach ($cur as $r) $used[substr($r['upc'], 3)] = true;
    $upcFixed = array();
    foreach ($cur as $pid => $r) {
        $code = substr($r['upc'], 3);
        if (isset($csv[$code]) || isset($csvCi[strtoupper(rtrim($code))]) || strpos($r['upc'], ',') !== false) continue;
        $n = norm_code($code);
        if (empty($byNorm[$n]) || count($byNorm[$n]) != 1) continue;
        $new = $byNorm[$n][0];
        if ($new === $code || isset($used[$new])) { $report[] = "upc $pid {$r['upc']}: GI-$new already used by another card, skipped"; continue; }
        $changes[$pid] = array('product_id' => $pid, 'expect_upc' => $r['upc'], 'set_upc' => 'GI-' . $new);
        $upcFixed[$pid] = true;
    }

    // 2. manufacturer and disable, from the per-card check against grandinstrument.ua
    $f = fopen(need($o, 'offbrand'), 'r');
    $h = fgetcsv($f);
    while ($r = fgetcsv($f)) {
        $r = array_combine($h, $r);
        $pid = (int)$r['product_id'];
        if (!isset($cur[$pid])) fail("product $pid from the offbrand list is not a GI card on prod");
        $c = $cur[$pid];
        if ($c['upc'] !== $r['upc']) fail("product $pid: upc on prod is {$c['upc']}, list says {$r['upc']}");
        $brand = $r['gi_brand'];
        if ($brand !== 'NOT FOUND') {
            if (!isset($BRAND_MANUFACTURER[$brand])) fail("product $pid: no manufacturer mapping for brand '$brand'");
            $target = $BRAND_MANUFACTURER[$brand];
            if ($c['mname'] === $target) continue;
            if (!isset($changes[$pid])) $changes[$pid] = array('product_id' => $pid, 'expect_upc' => $c['upc']);
            $changes[$pid]['expect_manufacturer_id'] = (int)$c['manufacturer_id'];
            $changes[$pid]['set_manufacturer'] = $target;
        } else {
            $code = substr($c['upc'], 3);
            if (isset($upcFixed[$pid])) { $report[] = "disable $pid {$c['upc']}: skipped, its upc is being fixed (the supplier sells it)"; continue; }
            if (isset($csv[$code])) { $report[] = "disable $pid {$c['upc']}: skipped, in the supplier CSV"; continue; }
            if ((int)$c['quantity'] !== 0) { $report[] = "disable $pid {$c['upc']}: skipped, quantity {$c['quantity']}"; continue; }
            if ((int)$c['status'] !== 1) continue;
            $changes[$pid] = array('product_id' => $pid, 'expect_upc' => $c['upc'], 'disable' => true);
        }
    }
    $new = array();
    foreach ($NEW_MANUFACTURERS as $name => $kw) if (empty($mi[$name])) $new[] = array('name' => $name, 'keyword' => $kw);
    $changes = array_values($changes);
    $data = array('built' => date('c'), 'source_db' => $m->db_name, 'manufacturers' => $new, 'changes' => $changes);
    file_put_contents(need($o, 'out'), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $cnt = array('set_upc' => 0, 'set_manufacturer' => 0, 'disable' => 0);
    $per = array();
    foreach ($changes as $ch) foreach ($cnt as $k => $_) if (isset($ch[$k])) { $cnt[$k]++; if ($k == 'set_manufacturer') @$per[$ch[$k]]++; }
    say('new manufacturers: ' . implode(', ', array_column($new, 'name')));
    say("changes: upc={$cnt['set_upc']} manufacturer={$cnt['set_manufacturer']} disable={$cnt['disable']}");
    foreach ($per as $k => $v) say("  manufacturer -> $k: $v");
    foreach ($report as $line) say("  note: $line");
}

// ---------------------------------------------------------------- apply / rollback

function cmd_apply($o) {
    $D = json_decode(file_get_contents(need($o, 'data')), true);
    if (!$D || !isset($D['changes'])) fail('bad data');
    $journal_file = need($o, 'journal');
    $dry = isset($o['dry-run']);
    $qguard = !isset($o['no-quantity-guard']);
    if (!$dry && file_exists($journal_file)) fail("journal $journal_file already exists");
    $m = db_connect(need($o, 'config'), false);
    say('DB=' . $m->db_name . ($dry ? ' (dry run)' : ''));
    $J = array('db' => $m->db_name, 'at' => date('c'), 'created_manufacturers' => array(), 'before' => array());
    q($m, 'START TRANSACTION');
    try {
        $mi = manufacturer_ids($m);
        foreach ($D['manufacturers'] as $nm) {
            if (!empty($mi[$nm['name']])) { say("manufacturer {$nm['name']} exists already (#{$mi[$nm['name']][0]})"); continue; }
            if (rows($m, 'SELECT 1 FROM oc_seo_url WHERE keyword=' . e($m, $nm['keyword']))) throw new RuntimeException("seo keyword {$nm['keyword']} taken");
            q($m, "INSERT INTO oc_manufacturer SET name=" . e($m, $nm['name']) . ", image='', sort_order=0");
            $id = (int)$m->insert_id;
            q($m, "INSERT INTO oc_manufacturer_to_store SET manufacturer_id=$id, store_id=0");
            foreach (array(2, 3) as $lang) {
                q($m, "INSERT INTO oc_manufacturer_description SET manufacturer_id=$id, language_id=$lang, description='', meta_description=' ', meta_keyword=' ', custom_title=''");
                q($m, "INSERT INTO oc_seo_url SET store_id=0, language_id=$lang, query='manufacturer_id=$id', keyword=" . e($m, $nm['keyword']));
            }
            $mi[$nm['name']] = array($id);
            $J['created_manufacturers'][] = $id;
            say("created manufacturer {$nm['name']} #$id");
        }
        $n = array('upc' => 0, 'manufacturer' => 0, 'disable' => 0);
        foreach ($D['changes'] as $ch) {
            $pid = (int)$ch['product_id'];
            $c = rows($m, "SELECT upc, status, quantity, manufacturer_id FROM oc_product WHERE product_id=$pid FOR UPDATE");
            if (!$c) throw new RuntimeException("product $pid not found");
            $c = $c[0];
            if ($c['upc'] !== $ch['expect_upc']) throw new RuntimeException("product $pid: upc is '{$c['upc']}', expected '{$ch['expect_upc']}'");
            $set = array();
            if (isset($ch['set_upc'])) {
                if (rows($m, 'SELECT product_id FROM oc_product WHERE product_id<>' . $pid . ' AND upc=' . e($m, $ch['set_upc']))) throw new RuntimeException("product $pid: upc {$ch['set_upc']} already used");
                $set[] = 'upc=' . e($m, $ch['set_upc']); $n['upc']++;
            }
            if (isset($ch['set_manufacturer'])) {
                if (empty($mi[$ch['set_manufacturer']]) || count($mi[$ch['set_manufacturer']]) != 1) throw new RuntimeException("manufacturer '{$ch['set_manufacturer']}' missing or not unique");
                if ((int)$c['manufacturer_id'] !== (int)$ch['expect_manufacturer_id']) throw new RuntimeException("product $pid: manufacturer_id is {$c['manufacturer_id']}, expected {$ch['expect_manufacturer_id']}");
                $set[] = 'manufacturer_id=' . (int)$mi[$ch['set_manufacturer']][0]; $n['manufacturer']++;
            }
            if (!empty($ch['disable'])) {
                if ((int)$c['status'] !== 1) throw new RuntimeException("product $pid: already disabled");
                if ($qguard && (int)$c['quantity'] !== 0) throw new RuntimeException("product $pid: quantity {$c['quantity']}, expected 0");
                $set[] = 'status=0'; $n['disable']++;
            }
            if (!$set) continue;
            q($m, 'UPDATE oc_product SET ' . implode(', ', $set) . ", date_modified=NOW() WHERE product_id=$pid");
            $J['before'][] = array('product_id' => $pid, 'upc' => $c['upc'], 'status' => (int)$c['status'], 'manufacturer_id' => (int)$c['manufacturer_id'], 'after' => $set);
        }
        say("updated: upc={$n['upc']} manufacturer={$n['manufacturer']} disabled={$n['disable']}");
        if ($dry) { q($m, 'ROLLBACK'); say('dry run: rolled back'); return; }
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
    q($m, 'START TRANSACTION');
    try {
        foreach (array_reverse($J['before']) as $b) {
            $pid = (int)$b['product_id'];
            q($m, "UPDATE oc_product SET upc=" . e($m, $b['upc']) . ", status=" . (int)$b['status'] . ", manufacturer_id=" . (int)$b['manufacturer_id'] . ", date_modified=NOW() WHERE product_id=$pid");
        }
        foreach ($J['created_manufacturers'] as $id) {
            $id = (int)$id;
            if (rows($m, "SELECT 1 FROM oc_product WHERE manufacturer_id=$id LIMIT 1")) { say("manufacturer #$id still used, kept"); continue; }
            q($m, "DELETE FROM oc_seo_url WHERE query='manufacturer_id=$id'");
            q($m, "DELETE FROM oc_manufacturer_description WHERE manufacturer_id=$id");
            q($m, "DELETE FROM oc_manufacturer_to_store WHERE manufacturer_id=$id");
            q($m, "DELETE FROM oc_manufacturer WHERE manufacturer_id=$id");
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
