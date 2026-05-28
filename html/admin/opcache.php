<?php
if (!isset($_GET['key']) || $_GET['key'] !== '070321') {
    http_response_code(403);
    exit('Forbidden');
}
opcache_reset();
echo 'OPcache cleared!';
