<?php
// HTTP
define('HTTP_SERVER', 'https://ls.radio-shop.com.ua/admin/');
define('HTTP_CATALOG', 'https://ls.radio-shop.com.ua/');

// HTTPS
define('HTTPS_SERVER', 'https://ls.radio-shop.com.ua/admin/');
define('HTTPS_CATALOG', 'https://ls.radio-shop.com.ua/');

// DIR
define('DIR_APPLICATION', '/var/www/ls.radio-shop.com.ua/html/admin/');
define('DIR_SYSTEM', '/var/www/ls.radio-shop.com.ua/html/system/');
define('DIR_IMAGE', '/var/www/ls.radio-shop.com.ua/html/image/');
define('DIR_STORAGE', '/var/www/ls.radio-shop.com.ua/storage/');
define('DIR_CATALOG', '/var/www/ls.radio-shop.com.ua/html/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', '10.100.0.103');
define('DB_USERNAME', 'qxi9802lkdas');
define('DB_PASSWORD', 'U9-S7z(mCXJfbo_b');
define('DB_DATABASE', 'ls');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');

//Redis settings
define('CACHE_HOSTNAME', '127.0.0.1');
define('CACHE_PORT', '6379');
define('CACHE_PREFIX', 'lsredis_');
