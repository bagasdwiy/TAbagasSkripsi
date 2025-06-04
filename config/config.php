<?php
// Database Configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'toko_kelontong');
// Application Configuration
define('SITE_URL', 'http://localhost/skripsi/toko2');
define('APP_NAME', 'Sistem Kasir');
define('TIMEZONE', 'Asia/Jakarta');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
