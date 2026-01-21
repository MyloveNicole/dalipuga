<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "barangay_system";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("<div class='alert alert-danger'>Connection failed: " . $conn->connect_error . "</div>");
}
?>
