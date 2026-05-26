<?php
// Version
define('VERSION', '3.0.2.0');

// Configuration
if (is_file('config.php')) {
	require_once('config.php');
}

// Install
if (!defined('DIR_APPLICATION')) {
	header('Location: install/index.php');
	exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Start buffer
ob_start();

start('catalog');

//end buffer
$html = ob_get_clean();

if (is_file('seo-fix.php')) {
    require_once('seo-fix.php');

    /**
     * Fix not valid seo Tags
     */
    $html = fixHTag($html);
    $html = fixOtherTag($html);
    $html = fixOldLink($html);
}

echo $html;
