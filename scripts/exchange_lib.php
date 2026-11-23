<?php
/**
 * Shared code of the Odoo exchange scripts (exchange_backfill.php, exchange_writer.php).
 *
 * Builds order, order history and product rows in the format of schema `exchange`
 * (see exchange_schema_draft.sql) from the OpenCart DB. Rows are returned as PHP
 * values keyed by column name; the caller turns them into SQL text (backfill) or
 * binds them to prepared statements (writer).
 *
 * Usage: require the OpenCart config.php first (DB_* constants), then this file,
 * then call ex_connect().
 */

const FEEDS_URL = 'http://10.100.0.124:8001/feeds/';
const SITE_URL  = 'https://radio-shop.com.ua/';
const LANG_UK   = 3;
const CHUNK     = 500;
// An order whose first status is one of these is not written to the exchange.
const SKIP_FIRST_STATUS = array(0, 8, 16, 19);

// Supplier directory (materials BR-INT-014). priority is set by Radio Shop later.
$SUPPLIERS = array(
    // prefix => name, vat, feed file, refresh, exact_qty, active
    'V'    => array('Virok', true, '3_virok', '3 на добу', true, true),
    'INT'  => array('Intertool', true, '2_intertool', '7 на добу', false, true),
    'BOC'  => array('Hoegert', true, '6_hoegert', '4 на добу', true, true),
    'GI'   => array('Toptul', true, '5_toptul', '4 на добу', true, true),
    'EMT'  => array('Technocom', false, '15_technocom', 'імпорт на сайт вимкнений', false, true),
    'MC'   => array('Masterclub', false, '4_masterclub', '1 на добу', true, true),
    'RCS'  => array('RCS Components', false, '8_rcs', '4 на добу', true, true),
    'ST'   => array('Stanley', true, '16_stanley', '4 на добу', true, true),
    'MAST' => array('Masteram', true, '13_masteram', '4 на добу', true, true),
    'ALLS' => array('All-Spares', true, '14_all-spares', '4 на добу', true, true),
    'B'    => array('Bosch', false, '7_bosch', '4 на добу', false, true),
    'BIA'  => array('Biakom', null, null, 'профіль неактивний', false, false),
    'DLS'  => array('уточнюється', null, null, null, false, false),
    'DVS'  => array('уточнюється', null, null, null, false, false),
    'PW'   => array('уточнюється', null, null, null, false, false),
    'M'    => array('уточнюється', null, null, null, false, false),
    'LP'   => array('уточнюється', null, null, null, false, false),
    'MIE'  => array('уточнюється', null, null, null, false, false),
    'GL'   => array('уточнюється', null, null, null, false, false),
    'А'    => array('уточнюється', null, null, null, false, false), // Cyrillic A
);

$db = null;
$uah_rate = null;
$uah_decimals = null;
$status_names = array();

// Opens a READ ONLY MariaDB session and loads the UAH rate and status names.
function ex_connect() {
    global $db, $uah_rate, $uah_decimals, $status_names;
    $db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $currency = q("SELECT value, decimal_place FROM oc_currency WHERE code = 'UAH'");
    $uah_rate = (float)$currency[0]['value'];
    $uah_decimals = (int)$currency[0]['decimal_place'];
    $status_names = array();
    foreach (q("SELECT order_status_id, name FROM oc_order_status WHERE language_id = " . LANG_UK) as $r) {
        $status_names[(int)$r['order_status_id']] = $r['name'];
    }
}

function q($sql) {
    global $db;
    $r = $db->query($sql);
    if (!$r) {
        throw new RuntimeException("SQL error: " . $db->error . "\n$sql");
    }
    return $r->fetch_all(MYSQLI_ASSOC);
}

function dec($s) {
    return $s === null ? null : html_entity_decode($s, ENT_QUOTES, 'UTF-8');
}

function ids($a) {
    return implode(',', array_map('intval', $a));
}

// "1EMT-14-0425, RCS167458" -> [{prefix, sku, unhooked}]; unknown prefix -> prefix null.
function parse_upc($upc) {
    global $SUPPLIERS;
    static $re = null;
    if ($re === null) {
        $p = array_keys($SUPPLIERS);
        usort($p, function ($a, $b) { return mb_strlen($b) - mb_strlen($a); });
        $re = '/^(1?)(' . implode('|', array_map('preg_quote', $p)) . ')(-|(?=\d)|$)(.*)$/iu';
    }
    $codes = array();
    foreach (array_filter(array_map('trim', explode(',', (string)$upc)), 'strlen') as $c) {
        if (preg_match($re, $c, $m)) {
            // A bare prefix ("DVS", "GL-") names the supplier without a code.
            $codes[] = array('prefix' => mb_strtoupper($m[2]), 'sku' => $m[4] === '' ? null : $m[4], 'unhooked' => $m[1] === '1');
        } else {
            $codes[] = array('prefix' => null, 'sku' => $c, 'unhooked' => false);
        }
    }
    return $codes;
}

function phone($t) {
    $d = preg_replace('/\D/', '', (string)$t);
    if (strlen($d) == 12 && substr($d, 0, 3) == '380') return '+' . $d;
    if (strlen($d) == 10 && $d[0] == '0') return '+38' . $d;
    if (strlen($d) == 9) return '+380' . $d;
    return $t === '' ? null : $t;
}

function uah($usd, $rate) {
    global $uah_decimals;
    return round((float)$usd * $rate, $uah_decimals);
}

function ex_json($a) {
    return json_encode($a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
}

/**
 * Full history of the given orders: order_id => rows ordered by time, then id.
 * $until (exclusive, 'Y-m-d H:i:s' or null) cuts the history like the backfill window.
 */
function ex_history($order_ids, $until = null) {
    $hist = array();
    foreach (array_chunk($order_ids, CHUNK) as $chunk) {
        foreach (q("SELECT order_id, order_status_id, comment, date_added FROM oc_order_history
                    WHERE order_id IN (" . ids($chunk) . ")" . ($until ? " AND date_added < '$until'" : "") . "
                    ORDER BY order_id, date_added, order_history_id") as $r) {
            $hist[(int)$r['order_id']][] = $r;
        }
    }
    return $hist;
}

/**
 * Rows for `orders` (snapshot at the first status) and `order_status` (every history row).
 * $hist = order_id => history rows (first row = first status), as from ex_history().
 * Returns array(orders, statuses, product ids used by the order lines).
 * statuses is keyed "order_id|status_id|date" (the unique key of order_status).
 */
function ex_build_orders($order_ids, $hist) {
    global $uah_rate, $status_names;
    $order_rows = array();
    $status_rows = array();
    $order_products = array();
    foreach (array_chunk($order_ids, CHUNK) as $chunk) {
        $in = ids($chunk);
        $orders = q("SELECT * FROM oc_order WHERE order_id IN ($in)");
        $lines = array();
        foreach (q("SELECT op.order_id, op.order_product_id, op.product_id, p.sku, p.upc, op.name, op.quantity, op.price, op.total
                    FROM oc_order_product op LEFT JOIN oc_product p USING(product_id)
                    WHERE op.order_id IN ($in) ORDER BY op.order_product_id") as $r) {
            $lines[(int)$r['order_id']][] = $r;
        }
        $totals = array();
        foreach (q("SELECT order_id, code, title, value FROM oc_order_total WHERE order_id IN ($in) ORDER BY sort_order") as $r) {
            $totals[(int)$r['order_id']][] = $r;
        }
        $simple = array();
        foreach (q("SELECT * FROM oc_order_simple_fields WHERE order_id IN ($in)") as $r) {
            $simple[(int)$r['order_id']] = $r;
        }

        foreach ($orders as $o) {
            $oid = (int)$o['order_id'];
            $rate = (float)$o['currency_value'] ?: $uah_rate;
            $first = $hist[$oid][0];
            $fs = (int)$first['order_status_id'];
            $sf = isset($simple[$oid]) ? array_map('dec', $simple[$oid]) : array();
            $g = function ($k) use ($sf) { return isset($sf[$k]) && $sf[$k] !== '' ? $sf[$k] : null; };
            $email = dec($o['email']);
            if ($email === '' || strpos($email, 'localhost') !== false) $email = null;

            $addr = function ($p) use ($o) {
                return array(
                    'firstname' => dec($o[$p . '_firstname']), 'lastname' => dec($o[$p . '_lastname']),
                    'company' => dec($o[$p . '_company']), 'country' => dec($o[$p . '_country']),
                    'zone' => dec($o[$p . '_zone']), 'city' => dec($o[$p . '_city']),
                    'address_1' => dec($o[$p . '_address_1']), 'address_2' => dec($o[$p . '_address_2']),
                    'postcode' => dec($o[$p . '_postcode']),
                );
            };

            $pl = array();
            foreach (isset($lines[$oid]) ? $lines[$oid] : array() as $l) {
                $order_products[(int)$l['product_id']] = true;
                $pl[] = array(
                    'order_product_id' => (int)$l['order_product_id'], 'product_id' => (int)$l['product_id'],
                    'sku' => $l['sku'], 'upc_raw' => $l['upc'], 'upc' => parse_upc($l['upc']),
                    'name' => dec($l['name']), 'qty' => (int)$l['quantity'],
                    'price_usd' => round((float)$l['price'], 4), 'total_usd' => round((float)$l['total'], 4),
                    'price_uah' => uah($l['price'], $rate), 'total_uah' => uah($l['total'], $rate),
                );
            }

            $t = array('sub_total' => 0.0, 'discount' => 0.0, 'coupon' => 0.0, 'shipping' => null, 'total' => (float)$o['total']);
            $discount_title = null;
            $other = array();
            foreach (isset($totals[$oid]) ? $totals[$oid] : array() as $r) {
                $v = (float)$r['value'];
                switch ($r['code']) {
                    case 'sub_total': $t['sub_total'] = $v; break;
                    case 'total': $t['total'] = $v; break;
                    case 'shoputils_cumulative_discounts': $t['discount'] += $v; $discount_title = dec($r['title']); break;
                    case 'coupon': case 'voucher': $t['coupon'] += $v; break;
                    case 'shipping': $t['shipping'] = $v; break;
                    default: $other[] = array('code' => $r['code'], 'title' => dec($r['title']), 'value_usd' => $v);
                }
            }
            $totals_out = array(
                'sub_total_usd' => round($t['sub_total'], 4), 'discount_usd' => round($t['discount'], 4),
                'discount_title' => $discount_title, 'coupon_usd' => round($t['coupon'], 4),
                'shipping_usd' => $t['shipping'], 'total_usd' => round($t['total'], 4),
                'sub_total_uah' => uah($t['sub_total'], $rate), 'discount_uah' => uah($t['discount'], $rate),
                'coupon_uah' => uah($t['coupon'], $rate),
                'total_uah' => uah($t['total'], $rate),
            );
            if ($other) $totals_out['other'] = $other;

            $comment = dec($o['comment']);
            $payload = array(
                'source' => 'radio-shop', 'order_id' => $oid, 'version' => 1,
                'date_added' => $o['date_added'],
                'status_id' => $fs, 'status_name' => isset($status_names[$fs]) ? $status_names[$fs] : null,
                'is_paid' => $fs == 18,
                'is_preorder' => strpos((string)$comment, 'ПІД ЗАМОВЛЕННЯ') !== false,
                'customer' => array(
                    'customer_id' => (int)$o['customer_id'] ?: null, 'customer_group_id' => (int)$o['customer_group_id'],
                    'firstname' => dec($o['firstname']), 'lastname' => dec($o['lastname']),
                    'phone' => phone($o['telephone']), 'email' => $email,
                ),
                'payer' => array(
                    'type' => $g('payer_type'),
                    'company' => $g('payer_type') === '2' ? $g('field20') : null,
                    'edrpou' => $g('payer_type') === '2' ? $g('payer_edrpou') : null,
                ),
                'payment' => array('code' => $o['payment_code'], 'method' => dec($o['payment_method']), 'address' => $addr('payment')),
                'shipping' => array(
                    'code' => $o['shipping_code'], 'method' => dec($o['shipping_method']), 'address' => $addr('shipping'),
                    'simple_fields' => array(
                        'call_back' => $g('zvon') === null ? null : (int)$g('zvon'),
                        'street' => $g('shipping_field21'), 'house' => $g('shipping_field22'), 'flat' => $g('shipping_field23'),
                        'warehouse_no' => $g('shipping_field24'), 'warehouse_address' => $g('shipping_field25'),
                    ),
                    'recipient' => $g('recipient_other') === '1' ? array(
                        'lastname' => $g('recipient_lastname'), 'firstname' => $g('recipient_firstname'),
                        'phone' => phone($g('recipient_phone')),
                    ) : null,
                    'novaposhta_ttn' => null,
                ),
                'currency' => array('code' => $o['currency_code'], 'value' => $rate),
                'lines' => $pl,
                'totals' => $totals_out,
                'comment' => $comment,
                'marketing' => array(
                    'referer' => $o['referer'], 'utm_source' => $o['utm_source'], 'utm_medium' => $o['utm_medium'],
                    'utm_campaign' => $o['utm_campaign'], 'utm_content' => $o['utm_content'], 'utm_term' => $o['utm_term'],
                ),
                'history' => array(array(
                    'status_id' => $fs, 'status_name' => isset($status_names[$fs]) ? $status_names[$fs] : null,
                    'date' => $first['date_added'],
                )),
            );

            $order_rows[] = array(
                'order_id' => $oid, 'version' => 1, 'status_id' => $fs, 'is_paid' => $fs == 18,
                'is_preorder' => $payload['is_preorder'], 'phone' => $payload['customer']['phone'],
                'customer_id' => $payload['customer']['customer_id'],
                'total_usd' => $totals_out['total_usd'], 'total_uah' => $totals_out['total_uah'],
                'date_added' => $o['date_added'], 'payload' => $payload,
                'created_at' => $first['date_added'] . '+00',
            );

            foreach ($hist[$oid] as $h) {
                $status_rows[$oid . '|' . (int)$h['order_status_id'] . '|' . $h['date_added']] = ex_status_row($oid, $h);
            }
        }
    }
    return array($order_rows, $status_rows, array_keys($order_products));
}

// One `order_status` row from an oc_order_history row.
function ex_status_row($oid, $h) {
    global $status_names;
    $sid = (int)$h['order_status_id'];
    $c = dec($h['comment']);
    return array(
        'order_id' => (int)$oid, 'status_id' => $sid,
        'status_name' => isset($status_names[$sid]) ? $status_names[$sid] : null,
        'comment' => $c === '' ? null : $c,
        'status_date' => $h['date_added'], 'created_at' => $h['date_added'] . '+00',
    );
}

/**
 * Rows for `products`, version 1, for the given product ids.
 * Returns array(rows, number of ids not found in oc_product).
 */
function ex_build_products($pids) {
    global $uah_rate;
    $product_rows = array();
    $missing = 0;
    foreach (array_chunk($pids, CHUNK) as $chunk) {
        $in = ids($chunk);
        $names = array();
        foreach (q("SELECT d.product_id, l.code, d.name FROM oc_product_description d JOIN oc_language l USING(language_id) WHERE d.product_id IN ($in)") as $r) {
            $names[(int)$r['product_id']][substr($r['code'], 0, 2)] = dec($r['name']);
        }
        $cats = array();
        foreach (q("SELECT pc.product_id, pc.category_id, cd.name FROM oc_product_to_category pc
                    JOIN oc_category_description cd ON cd.category_id = pc.category_id AND cd.language_id = " . LANG_UK . "
                    WHERE pc.product_id IN ($in)") as $r) {
            $cats[(int)$r['product_id']][] = array('category_id' => (int)$r['category_id'], 'name' => dec($r['name']));
        }
        $urls = array();
        foreach (q("SELECT query, keyword FROM oc_seo_url WHERE language_id = " . LANG_UK . " AND query IN ('product_id=" . implode("','product_id=", array_map('intval', $chunk)) . "')") as $r) {
            $urls[(int)substr($r['query'], 11)] = SITE_URL . $r['keyword'];
        }
        $imgs = array();
        foreach (q("SELECT product_id, image FROM oc_product_image WHERE product_id IN ($in) ORDER BY product_id, sort_order") as $r) {
            if ($r['image'] !== '') $imgs[(int)$r['product_id']][] = SITE_URL . 'image/' . $r['image'];
        }
        $found = array();
        foreach (q("SELECT p.*, m.name manufacturer, ss.name stock_status FROM oc_product p
                    LEFT JOIN oc_manufacturer m USING(manufacturer_id)
                    LEFT JOIN oc_stock_status ss ON ss.stock_status_id = p.stock_status_id AND ss.language_id = " . LANG_UK . "
                    WHERE p.product_id IN ($in)") as $p) {
            $pid = (int)$p['product_id'];
            $found[$pid] = true;
            $codes = parse_upc($p['upc']);
            $unh = false;
            foreach ($codes as $c) $unh = $unh || $c['unhooked'];
            $images = $p['image'] !== '' ? array(SITE_URL . 'image/' . $p['image']) : array();
            if (isset($imgs[$pid])) $images = array_merge($images, $imgs[$pid]);
            $payload = array(
                'source' => 'radio-shop', 'product_id' => $pid, 'sku' => $p['sku'], 'model' => $p['model'],
                'status' => (bool)$p['status'], 'deleted' => false,
                'upc_raw' => $p['upc'], 'upc' => $codes, 'multi_supplier' => count($codes) > 1, 'unhooked' => $unh,
                'name' => isset($names[$pid]) ? $names[$pid] : (object)array(),
                'manufacturer' => dec($p['manufacturer']),
                'categories' => isset($cats[$pid]) ? $cats[$pid] : array(),
                'price_usd' => round((float)$p['price'], 4), 'price_uah' => uah($p['price'], $uah_rate),
                'cost_price_uah' => (float)$p['cost_price'] ?: null,
                'quantity' => (int)$p['quantity'], 'stock_status' => $p['stock_status'],
                'weight_kg' => (float)$p['weight'],
                'url' => isset($urls[$pid]) ? $urls[$pid] : null,
                'images' => $images,
                'date_modified' => $p['date_modified'],
            );
            $product_rows[] = array(
                'product_id' => $pid, 'sku' => (string)$p['sku'], 'version' => 1, 'status' => (bool)$p['status'],
                'multi_supplier' => count($codes) > 1, 'unhooked' => $unh, 'deleted' => false,
                'updated_at' => $p['date_modified'], 'payload' => $payload,
                'created_at' => $p['date_modified'] . '+00',
            );
        }
        $missing += count($chunk) - count($found);
    }
    return array($product_rows, $missing);
}
