<?php
include("db_connect.php");

header('Content-Type: application/json');

$sql = "SELECT id, resident_id, resident_name, subject, resident_location, resident_contact, date_sent, status FROM letters ORDER BY date_sent DESC";
$result = $conn->query($sql);

$letters = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $letters[] = [
            'id' => $row['id'],
            'resident_id' => $row['resident_id'],
            'resident_name' => $row['resident_name'],
            'subject' => $row['subject'],
            'resident_location' => $row['resident_location'],
            'resident_contact' => $row['resident_contact'],
            'date_sent' => $row['date_sent'],
            'status' => $row['status']
        ];
    }
}

echo json_encode($letters);
?>
