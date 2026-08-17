<?php
/**
 * Category restructure requested 2026-08-17:
 *   1. new top-level category "Computer peripherals" (id 844) takes over the
 *      children and products of "Computer accessories" (189), then 189 is deleted;
 *   2. "3D print" (423) is promoted to top level and enabled;
 *   3. new subcategory "Filaments" (id 845) under 423 receives every product
 *      whose ru name starts with the ru word for filament (removed from 423 itself).
 *
 * Run with: lsphp74 scripts/migrate_3dprint_categories.php [--apply]
 * Without --apply it only reports what it would do.
 */

$apply = in_array('--apply', $argv, true);

require_once __DIR__ . '/../html/config.php';

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
if ($db->connect_error) {
    fwrite(STDERR, 'DB connect failed: ' . $db->connect_error . PHP_EOL);
    exit(1);
}
$db->set_charset('utf8mb4');

const OLD_ACC   = 189;   // computer accessories (to be deleted)
const NEW_ACC   = 844;   // computer peripherals
const PRINT_3D  = 423;   // 3D print
const FILAMENTS = 845;   // filaments
const ACC_KIDS  = [190, 191, 200, 201, 202, 211];
const MODULE_ID = 32;    // oct_category_wall (storefront category grid)

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

$log = [];
function step(&$log, $msg) { $log[] = $msg; echo $msg . PHP_EOL; }

// ---------------------------------------------------------------- sanity checks
$taken = one($db, 'SELECT COUNT(*) FROM oc_category WHERE category_id IN (' . NEW_ACC . ',' . FILAMENTS . ')');
if ($taken > 0) {
    fwrite(STDERR, 'Target ids ' . NEW_ACC . '/' . FILAMENTS . ' already exist — aborting.' . PHP_EOL);
    exit(1);
}

$filament_ids = [];
$res = q($db, "SELECT product_id FROM oc_product_description WHERE language_id = 2 AND name LIKE 'Филамент%'");
while ($row = $res->fetch_row()) {
    $filament_ids[] = (int)$row[0];
}
if (!$filament_ids) {
    fwrite(STDERR, 'No products matching the filament name prefix - aborting.' . PHP_EOL);
    exit(1);
}
$filament_list = implode(',', $filament_ids);

step($log, 'Products matching the filament name prefix: ' . count($filament_ids));
step($log, 'Products directly in ' . OLD_ACC . ': ' . one($db, 'SELECT COUNT(*) FROM oc_product_to_category WHERE category_id = ' . OLD_ACC));
step($log, 'Products in ' . PRINT_3D . ' before move: ' . one($db, 'SELECT COUNT(*) FROM oc_product_to_category WHERE category_id = ' . PRINT_3D));

if (!$apply) {
    step($log, 'Dry run — nothing written. Re-run with --apply.');
    exit(0);
}

$db->begin_transaction();
try {
    // ------------------------------------------------- 1. new computer peripherals category
    q($db, 'INSERT INTO oc_category
              (category_id, image, parent_id, top, `column`, sort_order, video, status, date_added, date_modified)
            SELECT ' . NEW_ACC . ', image, 0, 1, 1, 18, video, 1, NOW(), NOW()
            FROM oc_category WHERE category_id = ' . OLD_ACC);

    q($db, "INSERT INTO oc_category_description
              (category_id, language_id, name, gallery, description, meta_title, meta_description, meta_keyword, custom_title, default_meta)
            SELECT " . NEW_ACC . ", language_id,
                   CASE language_id WHEN 3 THEN \"Комп'ютерна периферія\" ELSE 'Компьютерная периферия' END,
                   gallery, description,
                   CASE language_id WHEN 3 THEN \"Комп'ютерна периферія\" ELSE 'Компьютерная периферия' END,
                   CASE language_id WHEN 3 THEN \"Комп'ютерна периферія\" ELSE 'Компьютерная периферия' END,
                   CASE language_id WHEN 3 THEN \"комп'ютерна периферія\" ELSE 'компьютерная периферия' END,
                   custom_title, default_meta
            FROM oc_category_description WHERE category_id = " . OLD_ACC);

    q($db, 'INSERT INTO oc_category_description_seo (category_id, language_id, custom_imgtitle, custom_alt, custom_h1, custom_h2)
            SELECT ' . NEW_ACC . ', language_id, custom_imgtitle, custom_alt, custom_h1, custom_h2
            FROM oc_category_description_seo WHERE category_id = ' . OLD_ACC);

    q($db, 'INSERT INTO oc_category_to_store (category_id, store_id)
            SELECT ' . NEW_ACC . ', store_id FROM oc_category_to_store WHERE category_id = ' . OLD_ACC);

    q($db, 'INSERT INTO oc_category_to_layout (category_id, store_id, layout_id)
            SELECT ' . NEW_ACC . ', store_id, layout_id FROM oc_category_to_layout WHERE category_id = ' . OLD_ACC);

    q($db, 'INSERT INTO oc_category_path (category_id, path_id, level) VALUES (' . NEW_ACC . ', ' . NEW_ACC . ', 0)');

    q($db, "INSERT INTO oc_seo_url (store_id, language_id, query, keyword) VALUES
              (0, 2, 'category_id=" . NEW_ACC . "', 'kompyuternaya-periferiya'),
              (0, 3, 'category_id=" . NEW_ACC . "', 'komp-yuterna-periferiya')");
    step($log, 'Created category ' . NEW_ACC . ' "Компьютерная периферия" (top level, visible)');

    // ------------------------------------------------- 2. re-parent the six children
    $kids = implode(',', ACC_KIDS);
    q($db, 'UPDATE oc_category SET parent_id = ' . NEW_ACC . ', status = 1, date_modified = NOW() WHERE category_id IN (' . $kids . ')');
    q($db, 'DELETE FROM oc_category_path WHERE category_id IN (' . $kids . ')');
    foreach (ACC_KIDS as $kid) {
        q($db, 'INSERT INTO oc_category_path (category_id, path_id, level) VALUES
                  (' . $kid . ', ' . NEW_ACC . ', 0), (' . $kid . ', ' . $kid . ', 1)');
    }
    step($log, 'Re-parented and enabled subcategories: ' . $kids);

    // ------------------------------------------------- 3. move products / filters off 189
    q($db, 'UPDATE oc_product_to_category SET category_id = ' . NEW_ACC . ' WHERE category_id = ' . OLD_ACC);
    q($db, 'UPDATE oc_ocfilter_option_to_category SET category_id = ' . NEW_ACC . ' WHERE category_id = ' . OLD_ACC);
    step($log, 'Moved ' . OLD_ACC . ' product and ocfilter links to ' . NEW_ACC);

    // ------------------------------------------------- 4. delete 189
    foreach (['oc_category', 'oc_category_description', 'oc_category_description_seo',
              'oc_category_to_store', 'oc_category_to_layout', 'oc_category_filter'] as $table) {
        q($db, 'DELETE FROM ' . $table . ' WHERE category_id = ' . OLD_ACC);
    }
    q($db, 'DELETE FROM oc_category_path WHERE category_id = ' . OLD_ACC . ' OR path_id = ' . OLD_ACC);
    q($db, "DELETE FROM oc_seo_url WHERE query = 'category_id=" . OLD_ACC . "'");
    step($log, 'Deleted category ' . OLD_ACC);

    // ------------------------------------------------- 5. promote the 3D print category
    q($db, 'UPDATE oc_category SET parent_id = 0, top = 1, status = 1, sort_order = 17, date_modified = NOW()
            WHERE category_id = ' . PRINT_3D);
    q($db, 'DELETE FROM oc_category_path WHERE category_id = ' . PRINT_3D);
    q($db, 'INSERT INTO oc_category_path (category_id, path_id, level) VALUES (' . PRINT_3D . ', ' . PRINT_3D . ', 0)');
    step($log, 'Promoted ' . PRINT_3D . ' "3D печать" to top level, enabled, sort_order 17');

    // ------------------------------------------------- 6. new filaments category under 3D print
    q($db, 'INSERT INTO oc_category
              (category_id, image, parent_id, top, `column`, sort_order, video, status, date_added, date_modified)
            VALUES (' . FILAMENTS . ", '', " . PRINT_3D . ", 0, 1, 1, '', 1, NOW(), NOW())");

    q($db, "INSERT INTO oc_category_description
              (category_id, language_id, name, gallery, description, meta_title, meta_description, meta_keyword, custom_title, default_meta)
            VALUES
              (" . FILAMENTS . ", 2, 'Филаменты', '', '', 'Филаменты', 'Филаменты для 3D-принтеров', 'филаменты', '', 0),
              (" . FILAMENTS . ", 3, 'Філаменти', '', '', 'Філаменти', 'Філаменти для 3D-принтерів', 'філаменти', '', 0)");

    q($db, 'INSERT INTO oc_category_description_seo (category_id, language_id, custom_imgtitle, custom_alt, custom_h1, custom_h2)
            VALUES (' . FILAMENTS . ", 2, '', '', '', ''), (" . FILAMENTS . ", 3, '', '', '', '')");

    q($db, 'INSERT INTO oc_category_to_store (category_id, store_id) VALUES (' . FILAMENTS . ', 0)');
    q($db, 'INSERT INTO oc_category_to_layout (category_id, store_id, layout_id) VALUES (' . FILAMENTS . ', 0, 0)');
    q($db, 'INSERT INTO oc_category_path (category_id, path_id, level) VALUES
              (' . FILAMENTS . ', ' . PRINT_3D . ', 0), (' . FILAMENTS . ', ' . FILAMENTS . ', 1)');
    q($db, "INSERT INTO oc_seo_url (store_id, language_id, query, keyword) VALUES
              (0, 2, 'category_id=" . FILAMENTS . "', 'filamenty'),
              (0, 3, 'category_id=" . FILAMENTS . "', 'filamenti')");

    // inherit the ocfilter option bindings of the parent category
    q($db, 'INSERT IGNORE INTO oc_ocfilter_option_to_category (option_id, category_id)
            SELECT option_id, ' . FILAMENTS . ' FROM oc_ocfilter_option_to_category WHERE category_id = ' . PRINT_3D);
    step($log, 'Created category ' . FILAMENTS . ' "Филаменты" under ' . PRINT_3D);

    // ------------------------------------------------- 7. move the filaments
    q($db, 'INSERT IGNORE INTO oc_product_to_category (product_id, category_id)
            SELECT product_id, ' . FILAMENTS . ' FROM oc_product WHERE product_id IN (' . $filament_list . ')');
    q($db, 'DELETE FROM oc_product_to_category WHERE category_id = ' . PRINT_3D . ' AND product_id IN (' . $filament_list . ')');
    step($log, 'Moved ' . count($filament_ids) . ' filament products from ' . PRINT_3D . ' to ' . FILAMENTS);

    // ------------------------------------------------- 8. catalog wall module whitelist
    $stmt = $db->prepare('SELECT setting FROM oc_module WHERE module_id = ?');
    $stmt->bind_param('i', $mid);
    $mid = MODULE_ID;
    $stmt->execute();
    $setting = $stmt->get_result()->fetch_row()[0];
    $stmt->close();

    $data = json_decode($setting, true);
    if (isset($data['module_categories']) && is_array($data['module_categories'])) {
        $out = [];
        foreach ($data['module_categories'] as $cid) {
            if ((int)$cid === OLD_ACC) {
                $out[] = (string)NEW_ACC;
                continue;
            }
            $out[] = (string)$cid;
            if ((int)$cid === PRINT_3D) {
                $out[] = (string)FILAMENTS;
            }
        }
        $data['module_categories'] = $out;
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);

        $stmt = $db->prepare('UPDATE oc_module SET setting = ? WHERE module_id = ?');
        $stmt->bind_param('si', $json, $mid);
        $stmt->execute();
        $stmt->close();
        step($log, 'Updated oct_category_wall module whitelist (189->844, +845)');
    } else {
        step($log, 'WARNING: module ' . MODULE_ID . ' has no module_categories — skipped');
    }

    $db->commit();
    step($log, 'COMMITTED');
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, 'ROLLED BACK: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

// ---------------------------------------------------------------- verification
$checks = [
    'top-level 3D print'   => "SELECT COUNT(*) FROM oc_category WHERE category_id = " . PRINT_3D . " AND parent_id = 0 AND top = 1 AND status = 1",
    'products in 423'       => 'SELECT COUNT(*) FROM oc_product_to_category WHERE category_id = ' . PRINT_3D,
    'products in 845'       => 'SELECT COUNT(*) FROM oc_product_to_category WHERE category_id = ' . FILAMENTS,
    'products in 844'       => 'SELECT COUNT(*) FROM oc_product_to_category WHERE category_id = ' . NEW_ACC,
    '189 leftovers'         => 'SELECT COUNT(*) FROM oc_category WHERE category_id = ' . OLD_ACC . ' OR parent_id = ' . OLD_ACC,
    'orphan products'       => 'SELECT COUNT(*) FROM oc_product p WHERE p.status = 1 AND NOT EXISTS
                                  (SELECT 1 FROM oc_product_to_category x WHERE x.product_id = p.product_id)',
    'broken paths'          => 'SELECT COUNT(*) FROM oc_category_path cp LEFT JOIN oc_category c ON c.category_id = cp.path_id WHERE c.category_id IS NULL',
];
echo PHP_EOL . '--- verification ---' . PHP_EOL;
foreach ($checks as $label => $sql) {
    printf("%-22s %s\n", $label, one($db, $sql));
}

$db->close();
