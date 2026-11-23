<?php
/**
 * One-off (2026-11-23, prod only: the hand-made cards do not exist on ls). See save/hoegert_20261121/PLAN.md.
 * Retires the hand-made HOEGERT duplicates whose upc is a bare article (HT2C318/312/313, quantity 100 that never
 * updates) in favour of the BOC-<EAN> twins the cron keeps up to date:
 *  - duplicate: status 0, seo keywords removed from oc_seo_url and re-added as 301 redirects
 *    (SEO URL Generator table) to the twin's keyword of the same store/language;
 *  - twin: gets the duplicate's categories it lacks (category 100 "Паяльна станція") and its related-product links.
 * Usage: php scripts/hoegert_twins_redirect.php apply <config.php> <journal.json> [--dry-run]
 *        php scripts/hoegert_twins_redirect.php rollback <config.php> <journal.json>
 */
$PAIRS = array( // duplicate product_id => array(expected duplicate upc, twin upc)
    7808817 => array('HT2C318', 'BOC-5902801607776'),
    7809078 => array('HT2C312', 'BOC-5902801607943'),
    7809079 => array('HT2C313', 'BOC-5902801607967'),
);
if ($argc < 4) { fwrite(STDERR, "usage: apply|rollback config journal [--dry-run]\n"); exit(2); }
list(, $cmd, $config, $jfile) = $argv;
$dry = in_array('--dry-run', $argv, true);
$cfg = file_get_contents($config);
$get = function ($k) use ($cfg) { if (!preg_match("/define\('$k',\s*'([^']*)'\)/", $cfg, $m)) exit("no $k\n"); return $m[1]; };
$db = new mysqli($get('DB_HOSTNAME'), $get('DB_USERNAME'), $get('DB_PASSWORD'), $get('DB_DATABASE'));
$db->set_charset('utf8mb4');
$e = function ($s) use ($db) { return "'" . $db->real_escape_string($s) . "'"; };
$rows = function ($sql) use ($db) { $r = $db->query($sql); if (!$r) throw new RuntimeException($db->error . " in $sql"); return $r === true ? array() : $r->fetch_all(MYSQLI_ASSOC); };
echo 'DB=' . $get('DB_DATABASE') . ($dry ? ' (dry run)' : '') . "\n";
$db->begin_transaction();
try {
    if ($cmd === 'apply') {
        if (is_file($jfile)) throw new RuntimeException("journal $jfile exists");
        $J = array('db' => $get('DB_DATABASE'), 'applied' => date('c'), 'items' => array());
        foreach ($PAIRS as $pid => $pp) {
            list($dupUpc, $twinUpc) = $pp;
            $d = $rows("SELECT product_id, upc, status FROM oc_product WHERE product_id=$pid FOR UPDATE");
            if (!$d || $d[0]['upc'] !== $dupUpc) throw new RuntimeException("$pid: upc is not $dupUpc");
            $t = $rows("SELECT product_id, status FROM oc_product WHERE upc=" . $e($twinUpc));
            if (count($t) != 1 || $t[0]['status'] != 1) throw new RuntimeException("$pid: twin $twinUpc missing, ambiguous or disabled");
            $tid = (int)$t[0]['product_id'];
            // seo keywords -> 301 to the twin
            $seo = $rows("SELECT * FROM oc_seo_url WHERE query='product_id=$pid'");
            $tseo = array();
            foreach ($rows("SELECT store_id, language_id, keyword FROM oc_seo_url WHERE query='product_id=$tid'") as $r) $tseo[$r['store_id']][$r['language_id']] = $r['keyword'];
            $redir = array();
            foreach ($seo as $s) {
                if (!isset($tseo[$s['store_id']][$s['language_id']])) throw new RuntimeException("$pid: twin $tid has no keyword for store {$s['store_id']} lang {$s['language_id']}");
                if ($rows("SELECT 1 FROM oc_seo_url_generator_redirects WHERE seo_url_old=" . $e($s['keyword']) . " AND store_id=" . (int)$s['store_id']))
                    throw new RuntimeException("$pid: redirect for {$s['keyword']} already exists");
                $rows("INSERT INTO oc_seo_url_generator_redirects SET store_id=" . (int)$s['store_id'] . ", language_id=" . (int)$s['language_id']
                    . ", seo_url_old=" . $e($s['keyword']) . ", seo_url_actual=" . $e($tseo[$s['store_id']][$s['language_id']]) . ", query='product_id=$tid'");
                $redir[] = (int)$db->insert_id;
            }
            $rows("DELETE FROM oc_seo_url WHERE query='product_id=$pid'");
            $rows("UPDATE oc_product SET status=0, date_modified=NOW() WHERE product_id=$pid");
            // categories the twin lacks
            $cats = array();
            foreach ($rows("SELECT category_id FROM oc_product_to_category WHERE product_id=$pid AND category_id NOT IN (SELECT category_id FROM oc_product_to_category WHERE product_id=$tid)") as $r) {
                $rows("INSERT INTO oc_product_to_category SET product_id=$tid, category_id=" . (int)$r['category_id']);
                $cats[] = (int)$r['category_id'];
            }
            // related links, both directions, without self links and existing ones
            $rel = array();
            foreach ($rows("SELECT product_id, related_id FROM oc_product_related WHERE product_id=$pid OR related_id=$pid") as $r) {
                $a = (int)$r['product_id'] == $pid ? $tid : (int)$r['product_id'];
                $b = (int)$r['related_id'] == $pid ? $tid : (int)$r['related_id'];
                if ($a == $b || $rows("SELECT 1 FROM oc_product_related WHERE product_id=$a AND related_id=$b")) continue;
                $rows("INSERT INTO oc_product_related SET product_id=$a, related_id=$b");
                $rel[] = array($a, $b);
            }
            $J['items'][] = array('product_id' => $pid, 'twin_id' => $tid, 'old_status' => (int)$d[0]['status'], 'seo_rows' => $seo,
                'redirect_ids' => $redir, 'added_categories' => $cats, 'added_related' => $rel);
            echo "  $pid -> $tid: disabled (was {$d[0]['status']}), " . count($seo) . " keywords -> 301, categories added: "
                . (implode(',', $cats) ?: '-') . ", related links added: " . count($rel) . "\n";
        }
        if ($dry) { $db->rollback(); echo "dry run: rolled back\n"; exit(0); }
        if (file_put_contents($jfile, json_encode($J, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) throw new RuntimeException('cannot write journal');
        $db->commit(); echo "committed; journal $jfile\n";
    } elseif ($cmd === 'rollback') {
        $J = json_decode(file_get_contents($jfile), true);
        if ($J['db'] !== $get('DB_DATABASE')) throw new RuntimeException('journal is for ' . $J['db']);
        foreach (array_reverse($J['items']) as $it) {
            $tid = (int)$it['twin_id'];
            foreach ($it['added_related'] as $ab) $rows("DELETE FROM oc_product_related WHERE product_id=" . (int)$ab[0] . " AND related_id=" . (int)$ab[1]);
            foreach ($it['added_categories'] as $c) $rows("DELETE FROM oc_product_to_category WHERE product_id=$tid AND category_id=" . (int)$c);
            foreach ($it['redirect_ids'] as $rid) $rows("DELETE FROM oc_seo_url_generator_redirects WHERE seo_url_id=" . (int)$rid);
            foreach ($it['seo_rows'] as $s) $rows("INSERT INTO oc_seo_url SET seo_url_id=" . (int)$s['seo_url_id'] . ", store_id=" . (int)$s['store_id'] . ", language_id=" . (int)$s['language_id'] . ", query=" . $e($s['query']) . ", keyword=" . $e($s['keyword']));
            $rows("UPDATE oc_product SET status=" . (int)$it['old_status'] . " WHERE product_id=" . (int)$it['product_id']);
        }
        if ($dry) { $db->rollback(); echo "dry run: rolled back\n"; exit(0); }
        $db->commit(); echo "rolled back by journal\n";
    } else throw new RuntimeException("unknown command $cmd");
} catch (Exception $ex) { $db->rollback(); fwrite(STDERR, 'ERROR: ' . $ex->getMessage() . "\n"); exit(1); }
