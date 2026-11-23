<?php
/**
 * Exchange backfill: one-off export of a date window from the site DB into the
 * Odoo exchange DB format (schema `exchange`, see exchange_schema_draft.sql).
 *
 * Read-only on MariaDB. Writes a PostgreSQL SQL file that replaces the content
 * of orders, order_status, products, suppliers, supplier_feeds, leads
 * (TRUNCATE + INSERT in one transaction). order_status_in and exchange_log are
 * truncated too: they belong to Odoo and must be empty for a test load.
 *
 * Rules (as agreed with the integrator, answers of 2026-10-29):
 *   - an order is written once, at its first real status; the payload is the
 *     snapshot at that moment (status_id = first status, history = first row);
 *   - orders whose first status is 0 or a cancellation (8, 16, 19) are skipped;
 *   - every oc_order_history row of an exported order inside the window goes
 *     to order_status, later changes reach Odoo only that way;
 *   - products: the whole catalog (active and inactive), version 1;
 *   - supplier_feeds: live feeds from 10.100.0.124, purchase 0 -> NULL. In a
 *     feed with a stock column an empty stock means "not available" -> 0; a
 *     feed without that column gives NULL ("available, quantity unknown");
 *   - payer.company and payer.edrpou only for type 2: for type 1 a value left
 *     in the hidden form field means nothing;
 *   - UAH amounts are rounded like the storefront (oc_currency.decimal_place);
 *   - leads: the two sample payloads from lead_examples.json (forms are not
 *     stored in the site DB), marked "_example": true.
 * Site DB timestamps are written as they are (UTC).
 *
 * Shared row builders: scripts/exchange_lib.php (also used by exchange_writer.php).
 *
 * Run: lsphp74 scripts/exchange_backfill.php FROM TO OUT.sql [CONFIG]
 *      TO is exclusive; CONFIG = OpenCart config.php with the DB credentials
 *      (default: staging). The MariaDB session is READ ONLY.
 */

require isset($argv[4]) ? $argv[4] : '/var/www/ls.radio-shop.com.ua/html/config.php';
require __DIR__ . '/exchange_lib.php';

if ($argc < 4) {
    fwrite(STDERR, "usage: exchange_backfill.php FROM TO OUT.sql [CONFIG]\n");
    exit(1);
}
list(, $from, $to, $out) = $argv;
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    fwrite(STDERR, "FROM and TO must be YYYY-MM-DD\n");
    exit(1);
}

ex_connect();

// PostgreSQL literal; standard_conforming_strings is on, so only quotes are doubled.
function pg($v) {
    if ($v === null) return 'NULL';
    if ($v === true) return 'true';
    if ($v === false) return 'false';
    if (is_int($v) || is_float($v)) return (string)$v;
    return "'" . str_replace(array("\0", "'"), array('', "''"), $v) . "'";
}

function js($a) {
    return pg(ex_json($a)) . '::jsonb';
}

// Row (column => PHP value) -> SQL literals in the order of $cols; payload as jsonb.
function lit($row, $cols) {
    $o = array();
    foreach ($cols as $c) $o[] = $c === 'payload' ? js($row[$c]) : pg($row[$c]);
    return $o;
}

$sql = fopen($out, 'w');
function w($s) {
    global $sql;
    fwrite($sql, $s . "\n");
}

function insert($table, $cols, $rows) {
    foreach (array_chunk($rows, CHUNK) as $chunk) {
        w("INSERT INTO $table (" . implode(', ', $cols) . ") VALUES");
        w(implode(",\n", array_map(function ($r) { return '(' . implode(', ', $r) . ')'; }, $chunk)) . ';');
    }
}

$stats = array('database' => DB_DATABASE, 'window' => "$from .. $to");

// --- Orders ------------------------------------------------------------------
$hist = array(); // order_id => history rows ordered
foreach (q("SELECT h.order_id, h.order_status_id, h.comment, h.date_added FROM oc_order_history h
            JOIN oc_order o USING(order_id)
            WHERE o.date_added >= DATE_SUB('$from', INTERVAL 60 DAY) AND h.date_added < '$to'
            ORDER BY h.order_id, h.date_added, h.order_history_id") as $r) {
    $hist[(int)$r['order_id']][] = $r;
}

$order_ids = array();
$skipped = array();
foreach ($hist as $oid => $rows) {
    $first = $rows[0];
    if ($first['date_added'] < $from) continue;
    $fs = (int)$first['order_status_id'];
    if (in_array($fs, SKIP_FIRST_STATUS)) {
        $skipped[$fs] = (isset($skipped[$fs]) ? $skipped[$fs] : 0) + 1;
        continue;
    }
    $order_ids[] = $oid;
    $stats['first_status'][$fs] = (isset($stats['first_status'][$fs]) ? $stats['first_status'][$fs] : 0) + 1;
}
$stats['orders_skipped_by_first_status'] = $skipped;

list($order_rows, $status_rows) = ex_build_orders($order_ids, $hist);
$stats['orders'] = count($order_rows);
$stats['order_status'] = count($status_rows);

// --- Products ----------------------------------------------------------------
$pids = array_map('intval', array_column(q("SELECT product_id FROM oc_product ORDER BY product_id"), 'product_id'));
list($product_rows, $missing) = ex_build_products($pids);
$stats['products'] = count($product_rows);
$stats['products_missing'] = $missing;

$ORDER_COLS = array('order_id', 'version', 'status_id', 'is_paid', 'is_preorder', 'phone', 'customer_id', 'total_usd', 'total_uah', 'date_added', 'payload', 'created_at');
$STATUS_COLS = array('order_id', 'status_id', 'status_name', 'comment', 'status_date', 'created_at');
$PRODUCT_COLS = array('product_id', 'sku', 'version', 'status', 'multi_supplier', 'unhooked', 'deleted', 'updated_at', 'payload', 'created_at');
$order_rows = array_map(function ($r) use ($ORDER_COLS) { return lit($r, $ORDER_COLS); }, $order_rows);
$status_rows = array_map(function ($r) use ($STATUS_COLS) { return lit($r, $STATUS_COLS); }, array_values($status_rows));
$product_rows = array_map(function ($r) use ($PRODUCT_COLS) { return lit($r, $PRODUCT_COLS); }, $product_rows);

// --- Suppliers and feeds -------------------------------------------------------
$supplier_rows = array();
$feed_rows = array();
$now = gmdate('Y-m-d H:i:s') . '+00';
foreach ($SUPPLIERS as $prefix => $s) {
    if (!$s[2]) {
        $supplier_rows[] = array(pg($prefix), pg($s[0]), pg($s[1]), 'NULL', pg($s[3]), 100, pg($s[4]), pg($s[5]), 'NULL', 'NULL', 'NULL');
        continue;
    }
    $body = @file_get_contents(FEEDS_URL . $s[2] . '.csv');
    if ($body === false) {
        fwrite(STDERR, "feed {$s[2]} fetch failed\n");
        exit(1);
    }
    $lines = preg_split('/\r\n|\n|\r/', trim($body));
    $head = str_getcsv(array_shift($lines), ';');
    $has_stock = in_array('stock', $head);
    $seen = array();
    foreach ($lines as $l) {
        $r = @array_combine($head, str_getcsv($l, ';'));
        if (!$r || trim($r['sku']) === '' || isset($seen[trim($r['sku'])])) continue;
        $sku = trim($r['sku']);
        $seen[$sku] = true;
        $purchase = isset($r['purchase']) && (float)$r['purchase'] > 0 ? round((float)$r['purchase'], 2) : null;
        $feed_rows[] = array(
            pg($prefix), pg($sku), pg(round((float)$r['price'], 2)), pg($purchase),
            $has_stock ? (int)$r['stock'] : 'NULL',
            pg(strtolower(trim($r['vat'])) === 'true'), pg($now),
        );
    }
    $stats['feeds'][$prefix] = count($seen);
    $supplier_rows[] = array(pg($prefix), pg($s[0]), pg($s[1]), pg($s[2] . '.csv'), pg($s[3]), 100, pg($s[4]), pg($s[5]), pg($now), pg($now), count($seen));
}

// --- Leads (samples) -------------------------------------------------------
$lead_rows = array();
$le = json_decode(file_get_contents('/var/www/ls.radio-shop.com.ua/lead_examples.json'), true);
foreach ($le['leads'] as $l) {
    $l['_example'] = true;
    $lead_rows[] = array(
        pg($l['form_code']), pg($l['name']), pg($l['company']), pg($l['phone']), pg($l['email']),
        pg($l['message']), pg($l['file_url']), js($l), pg($l['created_at']),
    );
}

// --- SQL ---------------------------------------------------------------------
w("-- Exchange backfill $from .. $to (exclusive), generated " . gmdate('Y-m-d H:i:s') . " UTC");
w("SET search_path TO exchange;");
w("BEGIN;");
w("TRUNCATE orders, order_status, products, supplier_feeds, suppliers, leads, order_status_in, exchange_log RESTART IDENTITY;");
insert('suppliers', array('prefix', 'name', 'vat', 'feed', 'refresh', 'priority', 'exact_qty', 'active', 'feed_loaded_at', 'feed_changed_at', 'feed_rows'), $supplier_rows);
insert('supplier_feeds', array('prefix', 'supplier_sku', 'price_uah', 'purchase_uah', 'stock', 'vat', 'updated_at'), $feed_rows);
insert('products', $PRODUCT_COLS, $product_rows);
insert('orders', $ORDER_COLS, $order_rows);
insert('order_status', $STATUS_COLS, $status_rows);
insert('leads', array('form_code', 'name', 'company', 'phone', 'email', 'message', 'file_url', 'payload', 'created_at'), $lead_rows);
w("COMMIT;");
fclose($sql);

$stats['leads'] = count($lead_rows);
echo json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "\n";
