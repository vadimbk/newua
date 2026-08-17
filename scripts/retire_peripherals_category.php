<?php
/**
 * Retire the "Computer peripherals" category tree (2026-08-17, director's call).
 *
 * Deletes category 844 and every descendant, disables the products that lived
 * only there, and cleans up the places that reference them: seo urls, ocfilter
 * bindings, the storefront category grid module and the hand-curated homepage
 * menus.
 *
 * The category tree is discovered from oc_category_path, so the same script is
 * correct on prod (844 alone) and on staging (844 plus six subcategories).
 *
 * Products that also belong to a category outside the tree are only unlinked,
 * never disabled. Snapshot tables are written first so the whole thing can be
 * undone.
 *
 * Run with: lsphp74 scripts/retire_peripherals_category.php [--apply]
 * Without --apply it reports and rolls back.
 */

$apply = in_array('--apply', $argv, true);
$stamp = '20260817';

require_once __DIR__ . '/../html/config.php';

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
if ($db->connect_error) {
    fwrite(STDERR, 'DB connect failed: ' . $db->connect_error . PHP_EOL);
    exit(1);
}
$db->set_charset('utf8mb4');

const ROOT      = 844;
const MODULE_ID = 32;
const MENU_SLUG = 'kompyuternaya-periferiya';

function q(mysqli $db, $sql) {
    $res = $db->query($sql);
    if ($res === false) {
        throw new RuntimeException('SQL failed: ' . $db->error . "\n" . $sql);
    }
    return $res;
}

function one(mysqli $db, $sql) {
    $row = q($db, $sql)->fetch_row();
    return $row ? $row[0] : null;
}

function col(mysqli $db, $sql) {
    $out = [];
    $res = q($db, $sql);
    while ($row = $res->fetch_row()) {
        $out[] = $row[0];
    }
    return $out;
}

function step($msg) { echo $msg . PHP_EOL; }

// ---------------------------------------------------------------- discovery
if (one($db, 'SELECT COUNT(*) FROM oc_category WHERE category_id = ' . ROOT) != 1) {
    fwrite(STDERR, 'Category ' . ROOT . ' not found - nothing to do.' . PHP_EOL);
    exit(1);
}

// mysqli hands back strings; keep these as ints so the strict comparisons
// against the module whitelist below actually match
$cats = array_map('intval', col($db, 'SELECT DISTINCT category_id FROM oc_category_path WHERE path_id = ' . ROOT));
if (!in_array(ROOT, $cats, true)) {
    $cats[] = ROOT;
}
sort($cats);
$cat_list = implode(',', array_map('intval', $cats));

$all_products = col($db, 'SELECT DISTINCT product_id FROM oc_product_to_category WHERE category_id IN (' . $cat_list . ')');

// products that survive elsewhere must keep their status
$keep = $all_products
    ? col($db, 'SELECT DISTINCT product_id FROM oc_product_to_category
                WHERE product_id IN (' . implode(',', array_map('intval', $all_products)) . ')
                  AND category_id NOT IN (' . $cat_list . ')')
    : [];
$to_disable = array_values(array_diff($all_products, $keep));

step('Categories to delete: ' . $cat_list . ' (' . count($cats) . ')');
step('Products in the tree:  ' . count($all_products));
step('  -> disable (nowhere else): ' . count($to_disable));
step('  -> only unlink (also elsewhere): ' . count($keep));

if (!$all_products) {
    step('No products linked - only the categories will go.');
}

// Snapshots first and outside the transaction: CREATE/DROP TABLE force an
// implicit commit in MySQL, which would silently break the rollback below.
if ($apply) {
    q($db, 'DROP TABLE IF EXISTS bak_periferiya_p2c_' . $stamp);
    q($db, 'CREATE TABLE bak_periferiya_p2c_' . $stamp . '
            SELECT product_id, category_id FROM oc_product_to_category
            WHERE category_id IN (' . $cat_list . ')');

    q($db, 'DROP TABLE IF EXISTS bak_periferiya_cat_' . $stamp);
    q($db, 'CREATE TABLE bak_periferiya_cat_' . $stamp . '
            SELECT * FROM oc_category WHERE category_id IN (' . $cat_list . ')');

    q($db, 'DROP TABLE IF EXISTS bak_periferiya_catdesc_' . $stamp);
    q($db, 'CREATE TABLE bak_periferiya_catdesc_' . $stamp . '
            SELECT * FROM oc_category_description WHERE category_id IN (' . $cat_list . ')');

    q($db, 'DROP TABLE IF EXISTS bak_periferiya_status_' . $stamp);
    if ($to_disable) {
        q($db, 'CREATE TABLE bak_periferiya_status_' . $stamp . '
                SELECT product_id, status, date_modified FROM oc_product
                WHERE product_id IN (' . implode(',', array_map('intval', $to_disable)) . ')');
    } else {
        q($db, 'CREATE TABLE bak_periferiya_status_' . $stamp . '
                SELECT product_id, status, date_modified FROM oc_product WHERE 1 = 0');
    }
    step('Snapshot tables written: bak_periferiya_{p2c,cat,catdesc,status}_' . $stamp);
} else {
    step('Dry run: snapshot tables not created.');
}

$db->begin_transaction();
try {
    // ------------------------------------------------- disable the products
    if ($to_disable) {
        q($db, 'UPDATE oc_product SET status = 0, date_modified = NOW()
                WHERE product_id IN (' . implode(',', array_map('intval', $to_disable)) . ')');
        step('Disabled ' . count($to_disable) . ' products');
    }

    // ------------------------------------------------- drop the categories
    q($db, 'DELETE FROM oc_product_to_category WHERE category_id IN (' . $cat_list . ')');
    q($db, 'DELETE FROM oc_ocfilter_option_to_category WHERE category_id IN (' . $cat_list . ')');

    foreach (['oc_category', 'oc_category_description', 'oc_category_description_seo',
              'oc_category_to_store', 'oc_category_to_layout', 'oc_category_filter'] as $table) {
        q($db, 'DELETE FROM ' . $table . ' WHERE category_id IN (' . $cat_list . ')');
    }
    q($db, 'DELETE FROM oc_category_path WHERE category_id IN (' . $cat_list . ') OR path_id IN (' . $cat_list . ')');

    $seo_queries = [];
    foreach ($cats as $cid) {
        $seo_queries[] = "'category_id=" . (int)$cid . "'";
    }
    q($db, 'DELETE FROM oc_seo_url WHERE query IN (' . implode(',', $seo_queries) . ')');
    step('Deleted ' . count($cats) . ' categories and their seo urls');

    // ------------------------------------------------- hand-curated menus
    $menu = q($db, "SELECT megamenu_id, mm_sheme_id FROM oc_megamenuvh_sheme
                    WHERE link LIKE '%" . MENU_SLUG . "%'");
    $n = 0;
    while ($row = $menu->fetch_assoc()) {
        q($db, 'UPDATE oc_megamenuvh_sheme SET status = 0
                WHERE megamenu_id = ' . (int)$row['megamenu_id'] . '
                  AND mm_sheme_id = ' . (int)$row['mm_sheme_id']);
        $n++;
    }
    step('Disabled ' . $n . ' hand-curated menu entries');

    // ------------------------------------------------- category grid module
    $stmt = $db->prepare('SELECT setting FROM oc_module WHERE module_id = ?');
    $mid  = MODULE_ID;
    $stmt->bind_param('i', $mid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    $stmt->close();

    if ($row) {
        $data = json_decode($row[0], true);
        if (isset($data['module_categories']) && is_array($data['module_categories'])) {
            $before = count($data['module_categories']);
            $data['module_categories'] = array_values(array_filter(
                $data['module_categories'],
                function ($cid) use ($cats) { return !in_array((int)$cid, $cats, true); }
            ));
            $json = json_encode($data, JSON_UNESCAPED_SLASHES);

            $stmt = $db->prepare('UPDATE oc_module SET setting = ? WHERE module_id = ?');
            $stmt->bind_param('si', $json, $mid);
            $stmt->execute();
            $stmt->close();
            step('Category grid module: ' . $before . ' -> ' . count($data['module_categories']) . ' entries');
        }
    }

    // ---------------------------------------------------------------- verify
    $checks = [
        'categories left'   => one($db, 'SELECT COUNT(*) FROM oc_category WHERE category_id IN (' . $cat_list . ')'),
        'p2c rows left'     => one($db, 'SELECT COUNT(*) FROM oc_product_to_category WHERE category_id IN (' . $cat_list . ')'),
        'paths left'        => one($db, 'SELECT COUNT(*) FROM oc_category_path WHERE category_id IN (' . $cat_list . ') OR path_id IN (' . $cat_list . ')'),
        'seo urls left'     => one($db, 'SELECT COUNT(*) FROM oc_seo_url WHERE query IN (' . implode(',', $seo_queries) . ')'),
        'still enabled'     => $to_disable
            ? one($db, 'SELECT COUNT(*) FROM oc_product WHERE status = 1 AND product_id IN (' . implode(',', array_map('intval', $to_disable)) . ')')
            : 0,
        'broken paths'      => one($db, 'SELECT COUNT(*) FROM oc_category_path cp
                                         LEFT JOIN oc_category c ON c.category_id = cp.path_id
                                         WHERE c.category_id IS NULL'),
    ];
    echo PHP_EOL . '--- verification ---' . PHP_EOL;
    foreach ($checks as $label => $value) {
        printf("%-18s %s\n", $label, $value);
    }

    foreach (['categories left', 'p2c rows left', 'paths left', 'seo urls left', 'still enabled'] as $k) {
        if ($checks[$k] != 0) {
            throw new RuntimeException('leftover detected: ' . $k);
        }
    }

    if ($apply) {
        $db->commit();
        step(PHP_EOL . 'COMMITTED');
    } else {
        $db->rollback();
        step(PHP_EOL . 'DRY RUN - rolled back. Re-run with --apply.');
    }
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, 'ROLLED BACK: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$db->close();
