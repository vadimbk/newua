<?php
/**
 * Sets the Ukrainian name of the attributes created by a gi_import_new.php apply run.
 * Only touches attributes listed in the journal whose UK name still equals the RU name.
 * Usage: php scripts/gi_attr_uk.php <config.php> <import journal.json> scripts/gi_attr_uk.json [--dry-run]
 */
if ($argc < 4) { fwrite(STDERR, "usage: config journal map [--dry-run]\n"); exit(2); }
$cfg = file_get_contents($argv[1]);
$get = function ($k) use ($cfg) { if (!preg_match("/define\('$k',\s*'([^']*)'\)/", $cfg, $m)) { fwrite(STDERR, "no $k\n"); exit(2); } return $m[1]; };
$db = new mysqli($get('DB_HOSTNAME'), $get('DB_USERNAME'), $get('DB_PASSWORD'), $get('DB_DATABASE'));
$db->set_charset('utf8mb4');
$journal = json_decode(file_get_contents($argv[2]), true);
$map = json_decode(file_get_contents($argv[3]), true);
$dry = in_array('--dry-run', $argv, true);
if (!$journal || !is_array($map)) { fwrite(STDERR, "bad journal or map\n"); exit(2); }
$db->begin_transaction();
$done = 0; $skip = array();
foreach ($journal['created_attributes'] as $a) {
    $id = (int)$a['attribute_id'];
    $ru = $a['name_ru'];
    if (!isset($map[$ru])) { $skip[] = "$id no translation: $ru"; continue; }
    $r = $db->query("SELECT language_id, name FROM oc_attribute_description WHERE attribute_id=$id")->fetch_all(MYSQLI_ASSOC);
    $names = array_column($r, 'name', 'language_id');
    if (!isset($names[2], $names[3]) || $names[2] !== $ru) { $skip[] = "$id not as journaled"; continue; }
    if ($names[3] !== $ru) { $skip[] = "$id UK already set: {$names[3]}"; continue; }
    $db->query("UPDATE oc_attribute_description SET name='" . $db->real_escape_string($map[$ru]) . "' WHERE attribute_id=$id AND language_id=3");
    $done++;
}
echo 'DB=' . $get('DB_DATABASE') . " translated=$done skipped=" . count($skip) . "\n";
foreach ($skip as $s) echo "  skip: $s\n";
if ($dry) { $db->rollback(); echo "dry run: rolled back\n"; } else { $db->commit(); echo "committed\n"; }
