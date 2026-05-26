<?php
ini_set('display_errors', 1);
//error_reporting( E_ALL ^ E_NOTICE );
error_reporting( E_ALL & ~E_NOTICE );

header("Content-Type: text/html; charset=UTF-8");
require_once ( "ParserCatalog.php" );
setlocale(LC_ALL, 'ru_RU.UTF-8');
ini_set('pcre.backtrack_limit', '5000000');
ini_set('memory_limit', '1000M');
$start_time = microtime(true);

$obj  = new ParserCatalog();
$obj->mainParse();