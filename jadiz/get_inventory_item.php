<?php
include("db_connect.php");

if (isset($_GET['id'])) {
    $item_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT id, item_name, category, quantity, unit, status FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $item_id);
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
