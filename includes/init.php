<?php
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path
define('ROOT_PATH', dirname(dirname(__FILE__)));

// Load required files
require_once ROOT_PATH . '/classes/Database.php';
require_once ROOT_PATH . '/classes/Layout.php';
require_once ROOT_PATH . '/includes/helpers.php';

// Initialize database connection and make it global
$GLOBALS['db'] = Database::getInstance()->getConnection();

// Debug database connection
if (!$GLOBALS['db']) {
    die("Failed to initialize database connection in init.php");
}

// Include config file
require_once __DIR__ . '/../config/config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// Load configuration
require_once __DIR__ . '/../config/config.php';


// Set charset
$conn->set_charset("utf8");
// Load database class
require_once __DIR__ . '/../classes/Database.php';
// Load required classes

// Load core classes
require_once __DIR__ . '/../classes/Transaksi.php';
require_once __DIR__ . '/../classes/Layout.php';  
require_once __DIR__ . '/../classes/Report.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Barang.php';
require_once __DIR__ . '/../classes/Supplier.php';
require_once __DIR__ . '/../classes/Retur.php';
require_once __DIR__ . '/../classes/Pembeli.php';

// Load helpers
require_once __DIR__ . '/helpers.php'; 