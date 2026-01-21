<?php
include("db_connect.php");

if (isset($_GET['id'])) {
    $letter_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("UPDATE letters SET status = 'read', read_at = NOW() WHERE id = ? AND status = 'unread'");
    $stmt->bind_param("i", $letter_id);
    $stmt->execute();
    $stmt->close();
}
?>
