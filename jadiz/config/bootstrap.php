<?php
/**
 * Application Bootstrap
 * Initializes all necessary components
 */

// Define base path
define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', BASE_PATH . '/src');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', BASE_PATH . '/views');
define('CONFIG_PATH', BASE_PATH . '/config');
define('LOGS_PATH', BASE_PATH . '/logs');
define('DATABASE_PATH', BASE_PATH . '/database');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/error.log');

// Load configuration
require_once CONFIG_PATH . '/Config.php';
$config = Config::getInstance();

// Set timezone
date_default_timezone_set('UTC');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => (int)$config->get('SESSION_TIMEOUT', 3600),
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_name($config->get('SESSION_NAME', 'jadiz_session'));
    session_start();
}

// Load all utility classes
$classFiles = glob(APP_PATH . '/utils/*.php');
foreach ($classFiles as $file) {
    require_once $file;
}

// Load all classes
$classFiles = glob(APP_PATH . '/classes/*.php');
foreach ($classFiles as $file) {
    require_once $file;
}

// Load all middleware
$middlewareFiles = glob(APP_PATH . '/middleware/*.php');
foreach ($middlewareFiles as $file) {
    require_once $file;
}

// Setup logging
$logger = Logger::getInstance();

// Set error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logger) {
    $logger->error("PHP Error [$errno]: $errstr in $errfile on line $errline");
});

// Set exception handler
set_exception_handler(function ($exception) use ($logger) {
    $logger->error("Exception: " . $exception->getMessage());
    if ($config->get('APP_ENV') === 'production') {
        die('An error occurred. Please try again later.');
    } else {
        die('Error: ' . $exception->getMessage());
    }
});

// Check logs directory is writable
if (!is_writable(LOGS_PATH)) {
    chmod(LOGS_PATH, 0755);
}

// Database connection with error handling
try {
    $database = Database::getInstance($config->get('DB_HOST'), $config->get('DB_USER'), $config->get('DB_PASS'), $config->get('DB_NAME'));
    $conn = $database->getConnection();
} catch (Exception $e) {
    $logger->error("Database connection failed: " . $e->getMessage());
    die('Database connection failed. Please check your configuration.');
}

// Initialize session security checks
SessionSecurityMiddleware::initialize();
?>
