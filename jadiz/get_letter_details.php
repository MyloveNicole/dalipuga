<?php
include("db_connect.php");

if (isset($_GET['id'])) {
    $letter_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT id, resident_name, resident_email, resident_location, resident_contact, subject, message, date_sent, status FROM letters WHERE id = ?");
    $stmt->bind_param("i", $letter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($row);
    }
    $stmt->close();
}
?>
