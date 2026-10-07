<?php
define('DB_HOST', 'db');
define('DB_USER', 'root');
define('DB_PASS', 'rootpass');
define('DB_NAME', 'pharmacy');

define('APP_NAME', 'NR1 Pharmacy');
define('APP_URL', 'http://localhost:8080');
define('CURRENCY', '$');

date_default_timezone_set('Asia/Phnom_Penh');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}