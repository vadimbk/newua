<?php
/**
 * Find LiteSpeed cache entries whose page body contains a string.
 *
 * LSCache entry files keep the URL key in a plain header, but many bodies are gzip-compressed,
 * so grep misses them. This scanner also decompresses the body from the gzip magic.
 * Prints: entry file, mtime, URL key. Delete single entry files to purge exactly those pages.
 *
 * Usage (lsphp74): php scripts/lscache_scan.php <cache root> <needle>
 *   e.g. php scripts/lscache_scan.php /var/www/radio-shop.com.ua/cache '/var/www/radio-shop.com.ua/html/image/'
 */
if ($argc < 3) { fwrite(STDERR, "usage: lscache_scan.php <cache root> <needle>\n"); exit(2); }
$root = $argv[1]; $needle = $argv[2]; $n = 0; $hit = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if (!$f->isFile()) continue;
    $n++;
    $d = file_get_contents($f->getPathname());
    if ($d === false) continue;
    $found = strpos($d, $needle) !== false;
    if (!$found && ($p = strpos($d, "\x1f\x8b\x08")) !== false) {
        $u = @gzdecode(substr($d, $p));
        if ($u !== false && strpos($u, $needle) !== false) $found = true;
    }
    if ($found) {
        $hit++;
        preg_match('#(/[^\x00-\x1f]{0,150}?)60aa#', substr($d, 0, 400), $m);
        echo $f->getPathname(), "\t", date('m-d H:i', $f->getMTime()), "\t", isset($m[1]) ? $m[1] : '?', "\n";
    }
}
fwrite(STDERR, "scanned=$n found=$hit\n");
