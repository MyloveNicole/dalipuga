<?php
/**
 * Public Entry Point - Main Dashboard
 * Determines if user should go to resident or admin dashboard
 */

// Load bootstrap
require_once dirname(__DIR__) . '/config/bootstrap.php';

// Set security headers
SecurityHeadersMiddleware::setHeaders();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: /jadiz/public/auth/resident_login.php");
    exit;
}

// Redirect to appropriate dashboard
if ($_SESSION['role'] === 'resident') {
    header("Location: /jadiz/public/resident_dashboard.php");
} elseif ($_SESSION['role'] === 'admin') {
    header("Location: /jadiz/public/admin_dashboard.php");
} else {
    session_destroy();
    header("Location: /jadiz/public/auth/resident_login.php");
}
exit;
?>
