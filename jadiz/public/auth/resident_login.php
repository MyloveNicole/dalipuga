<?php
/**
 * Public Entry Point - Resident Login Page
 * Route: /public/auth/resident_login.php
 */

// Load bootstrap
require_once dirname(dirname(__DIR__)) . '/config/bootstrap.php';

// Set page title
$pageTitle = "Resident Login - Dalipuga Cleanup Management System";
$error = "";

// Handle authentication
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Verify CSRF token
    CsrfProtectionMiddleware::verify();

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        $auth = new Auth();
        $userData = $auth->authenticateResident($email, $password);
        $auth->createSession($userData);
        
        header("Location: ../resident_dashboard.php");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Display login form
include VIEWS_PATH . '/auth/resident_login.php';
?>
