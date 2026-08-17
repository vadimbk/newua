<?php
/**
 * Follow-up to migrate_3dprint_categories.php.
 *
 * The homepage side menu (module megamenuvhsheme, table oc_megamenuvh_sheme) is a
 * hand-curated list, not generated from oc_category. It still points at the deleted
 * category 189. This script:
 *   - retargets the old "computer accessories" entry to the new peripherals
 *     category (844) and re-enables it, dropping 423 from its child list;
 *   - inserts a new top-level entry for "3D print" (423) right above it, whose
 *     child list is the new filaments category (845).
 * Both menu schemes are updated so no scheme keeps a link to a dead slug.
 *
 * Run with: lsphp74 scripts/migrate_3dprint_vhmenu.php [--apply]
 */

$apply = in_array('--apply', $argv, true);

require_once __DIR__ . '/../html/config.php';

$db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
if ($db->connect_error) {
    fwrite(STDERR, 'DB connect failed: ' . $db->connect_error . PHP_EOL);
    exit(1);
}
$db->set_charset('utf8mb4');

// scheme id => menu entry id of the obsolete "computer accessories" item
$targets = [1 => 17, 2 => 37];

$peripherals_name = ['2' => 'Компьютерная периферия', '3' => "Комп'ютерна периферія"];
$peripherals_link = ['2' => 'kompyuternaya-periferiya', '3' => 'uk/komp-yuterna-periferiya'];
$print_name       = ['2' => '3D печать', '3' => '3D друк'];
$print_link       = ['2' => '3d-pechat', '3' => 'uk/3d-druk'];

$db->begin_transaction();
try {
    foreach ($targets as $scheme_id => $entry_id) {
        $row = $db->query('SELECT * FROM oc_megamenuvh_sheme
                           WHERE megamenu_id = ' . (int)$entry_id . ' AND mm_sheme_id = ' . (int)$scheme_id)->fetch_assoc();
        if (!$row) {
            echo 'scheme ' . $scheme_id . ': entry ' . $entry_id . ' not found, skipped' . PHP_EOL;
            continue;
        }

        $sort = (int)$row['sort_menu'];

        // free the slot the new "3D print" entry will take
        $db->query('UPDATE oc_megamenuvh_sheme SET sort_menu = sort_menu + 1
                    WHERE mm_sheme_id = ' . (int)$scheme_id . ' AND sort_menu >= ' . $sort);

        // retarget and re-enable the peripherals entry
        $cat = json_decode($row['category_setting'], true);
        if (isset($cat['category_list']) && is_array($cat['category_list'])) {
            $cat['category_list'] = array_values(array_diff($cat['category_list'], ['423']));
        }
        $stmt = $db->prepare('UPDATE oc_megamenuvh_sheme
                              SET namemenu = ?, link = ?, category_setting = ?, status = 1
                              WHERE megamenu_id = ? AND mm_sheme_id = ?');
        $nm = json_encode($peripherals_name, JSON_UNESCAPED_SLASHES);
        $lk = json_encode($peripherals_link);
        $cs = json_encode($cat);
        $stmt->bind_param('sssii', $nm, $lk, $cs, $entry_id, $scheme_id);
        $stmt->execute();
        $stmt->close();
        echo 'scheme ' . $scheme_id . ': entry ' . $entry_id . ' -> peripherals (844), enabled' . PHP_EOL;

        // insert the new "3D print" entry in the freed slot
        $print_cat = [
            'variant_category'    => 'simple',
            'show_sub_category'   => '1',
            'category_img_width'  => '50',
            'category_img_height' => '50',
            'category_list'       => ['845'],
        ];
        $stmt = $db->prepare('INSERT INTO oc_megamenuvh_sheme
              (mm_sheme_id, namemenu, link, menu_type, status, sticker_parent, sticker_parent_bg, spctext,
               sort_menu, image, image_hover, informations_list, manufacturers_setting, products_setting,
               link_setting, category_setting, html_setting, freelinks_setting, use_add_html, add_html)
            VALUES (?, ?, ?, "category", 1, ?, "FFFFFF", "FFFFFF", ?, "", "", "", "", "", 0, ?, "", "", 0, ?)');
        $pn  = json_encode($print_name, JSON_UNESCAPED_SLASHES);
        $pl  = json_encode($print_link);
        $sp  = json_encode(['2' => '', '3' => '']);
        $pcs = json_encode($print_cat);
        $ah  = json_encode(['2' => '', '3' => '']);
        $stmt->bind_param('isssiss', $scheme_id, $pn, $pl, $sp, $sort, $pcs, $ah);
        $stmt->execute();
        $new_id = $db->insert_id;
        $stmt->close();
        echo 'scheme ' . $scheme_id . ': inserted "3D print" entry ' . $new_id . ' at sort ' . $sort . PHP_EOL;
    }

    if ($apply) {
        $db->commit();
        echo 'COMMITTED' . PHP_EOL;
    } else {
        $db->rollback();
        echo 'DRY RUN — rolled back. Re-run with --apply.' . PHP_EOL;
    }
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, 'ROLLED BACK: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$db->close();
