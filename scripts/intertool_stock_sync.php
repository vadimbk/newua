<?php
/**
 * Intertool stock sync.
 *
 * The Intertool price feed lists ONLY products that are in stock and does NOT
 * carry quantities. So availability is derived from feed membership:
 *   - upc IN  feed  -> quantity = IN_STOCK_QTY  (back in stock)
 *   - upc NOT in feed -> quantity = 0           (out of stock)
 * Scope is strictly the Intertool group: upc LIKE 'INT-%'.
 *
 * Safe + idempotent: only rows whose quantity actually differs are updated,
 * and a sanity guard aborts the whole run if the feed looks broken/short so a
 * bad fetch can never zero the whole catalogue.
 *
 * Run from CLI / cron:  php /var/www/radio-shop.com.ua/scripts/intertool_stock_sync.php
 */

require '/var/www/radio-shop.com.ua/html/config.php';

const FEED_URL     = 'http://10.100.0.124:8001/feeds/2_intertool.csv';
const PREFIX       = 'INT-';
const IN_STOCK_QTY = 100;          // standard "available" quantity for Intertool
const MIN_FEED_ROWS = 2000;        // guard: feed normally has ~3000 rows
const LOG_FILE     = '/var/www/radio-shop.com.ua/scripts/intertool_stock_sync.log';

function logline($msg) {
    $line = date('Y-m-d H:i:s') . ' ' . $msg . "\n";
    file_put_contents(LOG_FILE, $line, FILE_APPEND);
    echo $line;
}

// --- 1. Fetch feed ---------------------------------------------------------
$ch = curl_init(FEED_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$body = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($body === false || $http != 200) {
    logline("ABORT: feed fetch failed (http=$http). No changes made.");
    exit(1);
}

// --- 2. Parse feed sku -> set of group upc ('INT-' . sku) -------------------
$feed_upc = array();
$lines = preg_split('/\r\n|\n|\r/', $body);
array_shift($lines); // header
foreach ($lines as $l) {
    if ($l === '') continue;
    $sku = trim(strtok($l, ';'));
    if ($sku !== '') $feed_upc[PREFIX . $sku] = true;
}

if (count($feed_upc) < MIN_FEED_ROWS) {
    logline('ABORT: feed has only ' . count($feed_upc) . ' rows (< ' . MIN_FEED_ROWS . '). Looks broken. No changes made.');
    exit(1);
}

// --- 3. Compare against catalogue group ------------------------------------
$mysqli = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
if ($mysqli->connect_errno) {
    logline('ABORT: DB connect failed: ' . $mysqli->connect_error);
    exit(1);
}

$res = $mysqli->query("SELECT product_id, upc, quantity FROM " . DB_PREFIX . "product WHERE upc LIKE '" . PREFIX . "%'");
$to_zero = array();   // not in feed, currently qty != 0
$to_avail = array();  // in feed, currently qty != IN_STOCK_QTY
$group_total = 0;
while ($row = $res->fetch_assoc()) {
    $group_total++;
    $q = (int)$row['quantity'];
    if (isset($feed_upc[$row['upc']])) {
        if ($q !== IN_STOCK_QTY) $to_avail[] = (int)$row['product_id'];
    } else {
        if ($q !== 0) $to_zero[] = (int)$row['product_id'];
    }
}

// --- 4. Apply (only changed rows) ------------------------------------------
function bulk_update($mysqli, $ids, $qty) {
    $changed = 0;
    foreach (array_chunk($ids, 1000) as $chunk) {
        $in = implode(',', $chunk);
        $mysqli->query("UPDATE " . DB_PREFIX . "product SET quantity = $qty, date_modified = NOW() WHERE product_id IN ($in)");
        $changed += $mysqli->affected_rows;
    }
    return $changed;
}

$zeroed = bulk_update($mysqli, $to_zero, 0);
$restored = bulk_update($mysqli, $to_avail, IN_STOCK_QTY);

logline("OK: group(INT-)=$group_total feed=" . count($feed_upc) . " -> set 0: $zeroed, set " . IN_STOCK_QTY . ": $restored");
$mysqli->close();
