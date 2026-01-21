<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: auth/login.php");
    exit;
}
?>
