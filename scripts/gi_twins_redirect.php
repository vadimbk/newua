<?php
/**
 * One-off (2026-11-21, both DBs applied, journals in save/gi_20261120/twins_journal_*.json).
 * Retires the old look-alike GI duplicates: status 0, their seo keywords removed from oc_seo_url
 * and re-added as 301 redirects (SEO URL Generator table) to the live twin card.
 * Usage: php scripts/gi_twins_redirect.php apply <config.php> <journal.json> [--dry-run]
 *        php scripts/gi_twins_redirect.php rollback <config.php> <journal.json>
 */
$PAIRS = array( // duplicate product_id => array(expected duplicate upc, twin upc)
    7792728 => array('GI-GAAE0З01', 'GI-GAAE0301'),
    7792912 => array('GI-NS-H-З00З-1.З', 'GI-NS-H-3003-1.3'),
    7792941 => array('GI-NGAW090З', 'GI-NGAW0903'),
    7793748 => array('GI-NS-H-827B-1.З', 'GI-NS-H-827B-1.3'),
    7797031 => array('GI-EHF0З00', 'GI-EHF0300'),
);
if ($argc < 4) { fwrite(STDERR, "usage: apply|rollback config journal [--dry-run]\n"); exit(2); }
list(, $cmd, $config, $jfile) = $argv;
$dry = in_array('--dry-run', $argv, true);
$cfg = file_get_contents($config);
$get = function ($k) use ($cfg) { if (!preg_match("/define\('$k',\s*'([^']*)'\)/", $cfg, $m)) exit("no $k\n"); return $m[1]; };
$db = new mysqli($get('DB_HOSTNAME'), $get('DB_USERNAME'), $get('DB_PASSWORD'), $get('DB_DATABASE'));
$db->set_charset('utf8mb4');
$db->query('SET SESSION sql_mode=""');
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
            $t = $rows("SELECT product_id, status FROM oc_product WHERE BINARY upc=" . $e($twinUpc));
            if (count($t) != 1 || $t[0]['status'] != 1) throw new RuntimeException("$pid: twin $twinUpc missing, ambiguous or disabled");
            $tid = (int)$t[0]['product_id'];
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
            $rows("UPDATE oc_product SET status=0 WHERE product_id=$pid");
            $J['items'][] = array('product_id' => $pid, 'twin_id' => $tid, 'old_status' => (int)$d[0]['status'], 'seo_rows' => $seo, 'redirect_ids' => $redir);
            echo "  $pid -> $tid: disabled (was {$d[0]['status']}), " . count($seo) . " keywords -> 301\n";
        }
        if ($dry) { $db->rollback(); echo "dry run: rolled back\n"; exit(0); }
        file_put_contents($jfile, json_encode($J, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $db->commit(); echo "committed; journal $jfile\n";
    } elseif ($cmd === 'rollback') {
        $J = json_decode(file_get_contents($jfile), true);
        if ($J['db'] !== $get('DB_DATABASE')) throw new RuntimeException('journal is for ' . $J['db']);
        foreach (array_reverse($J['items']) as $it) {
            foreach ($it['redirect_ids'] as $rid) $rows("DELETE FROM oc_seo_url_generator_redirects WHERE seo_url_id=" . (int)$rid);
            foreach ($it['seo_rows'] as $s) $rows("INSERT INTO oc_seo_url SET seo_url_id=" . (int)$s['seo_url_id'] . ", store_id=" . (int)$s['store_id'] . ", language_id=" . (int)$s['language_id'] . ", query=" . $e($s['query']) . ", keyword=" . $e($s['keyword']));
            $rows("UPDATE oc_product SET status=" . (int)$it['old_status'] . " WHERE product_id=" . (int)$it['product_id']);
        }
        $db->commit(); echo "rolled back by journal\n";
    } else throw new RuntimeException("unknown command $cmd");
} catch (Exception $ex) { $db->rollback(); fwrite(STDERR, 'ERROR: ' . $ex->getMessage() . "\n"); exit(1); }
