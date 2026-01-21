<?php
include("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'add') {
        $item_name = trim($_POST['item_name']);
        $category = trim($_POST['category']);
        $quantity = intval($_POST['quantity']);
        $unit = trim($_POST['unit']);
        $status = trim($_POST['status']);

        if ($item_name && $category && $quantity && $unit && $status) {
            $stmt = $conn->prepare(
                "INSERT INTO inventory (item_name, category, quantity, unit, status) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssiss", $item_name, $category, $quantity, $unit, $status);

            if ($stmt->execute()) {
                header("Location: inventory.php?success=added");
            } else {
                header("Location: inventory.php?error=add_failed");
            }
            $stmt->close();
        } else {
            header("Location: inventory.php?error=missing_fields");
        }
    } 
    elseif ($action == 'edit') {
        $item_id = intval($_POST['item_id']);
        $item_name = trim($_POST['item_name']);
        $category = trim($_POST['category']);
        $quantity = intval($_POST['quantity']);
        $unit = trim($_POST['unit']);
        $status = trim($_POST['status']);

        if ($item_id && $item_name && $category && $quantity && $unit && $status) {
            $stmt = $conn->prepare(
                "UPDATE inventory SET item_name = ?, category = ?, quantity = ?, unit = ?, status = ? 
                 WHERE id = ?"
            );
            $stmt->bind_param("ssissi", $item_name, $category, $quantity, $unit, $status, $item_id);

            if ($stmt->execute()) {
                header("Location: inventory.php?success=updated");
            } else {
                header("Location: inventory.php?error=update_failed");
            }
            $stmt->close();
        }
    }
    elseif ($action == 'delete') {
        $item_id = intval($_POST['item_id']);

        if ($item_id) {
            $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
            $stmt->bind_param("i", $item_id);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false]);
            }
            $stmt->close();
        }
    }
}
?>
