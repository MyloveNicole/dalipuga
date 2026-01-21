<?php
session_start();

// Check if resident is logged in
if (!isset($_SESSION['resident_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: auth/resident_login.php");
    exit;
}
?>
