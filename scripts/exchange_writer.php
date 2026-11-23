<?php
/**
 * Exchange writer: live site -> Odoo exchange DB (schema `exchange`, CT 170).
 * Run by cron every minute. Read-only on MariaDB: no OpenCart table, code or event
 * is changed. Rows are built by scripts/exchange_lib.php, the same code as the
 * backfill, so live rows and the initial load look the same.
 *
 * One run, in one PostgreSQL transaction:
 *   1. products: version 1 for every product id that exists on the site and not in
 *      exchange.products yet (new products only; change detection comes later);
 *   2. orders: an order is written once, at its first status (snapshot), if the
 *      first status is not 0/8/16/19 and is not older than `orders_from`;
 *   3. order_status: every oc_order_history row since the cursor, for orders that
 *      are in exchange.orders. All history of an order written in this run.
 * Inserts use ON CONFLICT DO NOTHING, so re-reading rows is harmless.
 *
 * Cursor = max(order_status.status_date) minus CURSOR_MARGIN. Both sides come
 * from the MariaDB clock, so no time zone mixing. After a truncate + backfill the
 * writer goes on from the end of the backfill. --since overrides the cursor.
 *
 * Config: /var/www/.exchange/writer.ini (mode 600, outside git and web roots):
 *   host, port, dbname, user, password  - PostgreSQL, role exchange_writer
 *   site_db     - the only OpenCart DB allowed to commit (radioshop); any other DB
 *                 (staging `ls`) can run only with --dry-run
 *   orders_from - oldest first-status date of an order to write (YYYY-MM-DD)
 *
 * Run: lsphp74 scripts/exchange_writer.php CONFIG [--dry-run] [--since='YYYY-MM-DD HH:MM:SS'] [-v]
 *      CONFIG = OpenCart config.php with the DB credentials.
 *      --dry-run does everything inside the transaction and rolls it back.
 *      -v prints the run summary.
 * Exit code 0 = ok or another run holds the lock; 1 = error (also in
 * exchange_log and in /var/www/.exchange/writer.log).
 */

const INI_FILE      = '/var/www/.exchange/writer.ini';
const LOCK_FILE     = '/var/www/.exchange/writer.lock';
const LOG_FILE      = '/var/www/.exchange/writer.log';
const CURSOR_MARGIN = 10800; // seconds, 3 h

$args = array_slice($argv, 1);
$dry = in_array('--dry-run', $args);
$since = null;
$config = null;
foreach ($args as $a) {
    if (strpos($a, '--since=') === 0) $since = substr($a, 8);
    elseif ($a[0] !== '-') $config = $a;
}
if (!$config || !is_file($config)) {
    fwrite(STDERR, "usage: exchange_writer.php CONFIG [--dry-run] [--since='YYYY-MM-DD HH:MM:SS']\n");
    exit(1);
}
if ($since !== null && !preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $since)) {
    fwrite(STDERR, "--since must be 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'\n");
    exit(1);
}

$lock = fopen(LOCK_FILE, 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) exit(0);

require $config;
require __DIR__ . '/exchange_lib.php';

function wlog($msg) {
    file_put_contents(LOG_FILE, gmdate('Y-m-d H:i:s') . ' UTC ' . DB_DATABASE . ' ' . $msg . "\n", FILE_APPEND);
}

$ini = parse_ini_file(INI_FILE);
$pg = null;
try {
    if (!$ini || empty($ini['site_db']) || empty($ini['orders_from'])) throw new RuntimeException('bad ' . INI_FILE);
    if (!$dry && DB_DATABASE !== $ini['site_db']) {
        throw new RuntimeException('site DB ' . DB_DATABASE . ' may run only with --dry-run (site_db = ' . $ini['site_db'] . ')');
    }
    $pg = new PDO("pgsql:host={$ini['host']};port={$ini['port']};dbname={$ini['dbname']}", $ini['user'], $ini['password'],
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    $pg->exec('SET search_path TO exchange');
    ex_connect();

    $stats = array('site_db' => DB_DATABASE, 'dry_run' => $dry);
    $pg->beginTransaction();

    // Cursor.
    if ($since === null) {
        $max = $pg->query("SELECT max(status_date) FROM order_status WHERE source = 'radio-shop'")->fetchColumn();
        if (!$max) throw new RuntimeException('order_status is empty: run the backfill first or pass --since');
        // Parsed and printed as UTC, so the margin is plain arithmetic on the stored value.
        $since = gmdate('Y-m-d H:i:s', strtotime($max . ' UTC') - CURSOR_MARGIN);
    }
    $stats['since'] = $since;

    // 1. New products.
    $site_pids = array_map('intval', array_column(q("SELECT product_id FROM oc_product"), 'product_id'));
    $ex_pids = array_flip(array_map('intval', $pg->query("SELECT DISTINCT product_id FROM products WHERE source = 'radio-shop'")->fetchAll(PDO::FETCH_COLUMN)));
    $new_pids = array();
    foreach ($site_pids as $p) if (!isset($ex_pids[$p])) $new_pids[] = $p;
    list($product_rows) = ex_build_products($new_pids);
    $ins = $pg->prepare("INSERT INTO products (product_id, sku, version, status, multi_supplier, unhooked, deleted, updated_at, payload)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?::jsonb) ON CONFLICT (source, product_id, version) DO NOTHING");
    $n = 0;
    foreach ($product_rows as $r) {
        $ins->execute(array($r['product_id'], $r['sku'], $r['version'], b($r['status']), b($r['multi_supplier']),
            b($r['unhooked']), b($r['deleted']), $r['updated_at'], ex_json($r['payload'])));
        $n += $ins->rowCount();
    }
    $stats['products'] = $n;

    // 2. Orders touched since the cursor.
    $touched = array_map('intval', array_column(q("SELECT DISTINCT order_id FROM oc_order_history
                                                     WHERE date_added >= '" . $since . "'"), 'order_id'));
    $hist = ex_history($touched);
    $known = array();
    foreach (array_chunk($touched, CHUNK) as $chunk) {
        foreach ($pg->query("SELECT DISTINCT order_id FROM orders WHERE source = 'radio-shop' AND order_id IN (" . ids($chunk) . ")")
                 ->fetchAll(PDO::FETCH_COLUMN) as $oid) {
            $known[(int)$oid] = true;
        }
    }
    $new_orders = array();
    $skipped = array('first_status' => 0, 'older_than_orders_from' => 0);
    foreach ($touched as $oid) {
        if (isset($known[$oid]) || empty($hist[$oid])) continue;
        $first = $hist[$oid][0];
        if (in_array((int)$first['order_status_id'], SKIP_FIRST_STATUS)) { $skipped['first_status']++; continue; }
        if ($first['date_added'] < $ini['orders_from']) { $skipped['older_than_orders_from']++; continue; }
        $new_orders[] = $oid;
    }
    list($order_rows, $new_status_rows) = ex_build_orders($new_orders, $hist);
    $ins = $pg->prepare("INSERT INTO orders (order_id, version, status_id, is_paid, is_preorder, phone, customer_id, total_usd, total_uah, date_added, payload)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?::jsonb) ON CONFLICT (source, order_id, version) DO NOTHING");
    $n = 0;
    foreach ($order_rows as $r) {
        $ins->execute(array($r['order_id'], $r['version'], $r['status_id'], b($r['is_paid']), b($r['is_preorder']), $r['phone'],
            $r['customer_id'], $r['total_usd'], $r['total_uah'], $r['date_added'], ex_json($r['payload'])));
        $n += $ins->rowCount();
    }
    $stats['orders'] = $n;
    $stats['orders_skipped'] = $skipped;

    // 3. Status rows: all history of orders written now, rows since the cursor for known orders.
    $status_rows = $new_status_rows;
    foreach (array_keys($known) as $oid) {
        foreach ($hist[$oid] as $h) {
            if ($h['date_added'] >= $since) $status_rows[$oid . '|' . (int)$h['order_status_id'] . '|' . $h['date_added']] = ex_status_row($oid, $h);
        }
    }
    $ins = $pg->prepare("INSERT INTO order_status (order_id, status_id, status_name, comment, status_date)
                         VALUES (?, ?, ?, ?, ?) ON CONFLICT (source, order_id, status_id, status_date) DO NOTHING");
    $n = 0;
    foreach ($status_rows as $r) {
        $ins->execute(array($r['order_id'], $r['status_id'], $r['status_name'], $r['comment'], $r['status_date']));
        $n += $ins->rowCount();
    }
    $stats['order_status'] = $n;

    if ($stats['products'] || $stats['orders'] || $stats['order_status']) {
        $pg->prepare("INSERT INTO exchange_log (side, level, entity, message) VALUES ('site', 'info', 'writer', ?)")
           ->execute(array(ex_json($stats)));
        if (!$dry) wlog(ex_json($stats));
    }

    if ($dry) $pg->rollBack(); else $pg->commit();
    if ($dry || in_array('-v', $args)) echo ex_json($stats), "\n";
} catch (Exception $e) {
    $msg = $e->getMessage();
    if ($pg && $pg->inTransaction()) $pg->rollBack();
    wlog(($dry ? '[dry-run] ' : '') . 'ERROR ' . $msg);
    if ($pg && !$dry) {
        try {
            $pg->prepare("INSERT INTO exchange_log (side, level, entity, message) VALUES ('site', 'error', 'writer', ?)")
               ->execute(array(mb_substr($msg, 0, 2000)));
        } catch (Exception $e2) {
            wlog('ERROR exchange_log: ' . $e2->getMessage());
        }
    }
    fwrite(STDERR, $msg . "\n");
    exit(1);
}

function b($v) {
    return $v ? 't' : 'f';
}
