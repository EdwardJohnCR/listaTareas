<?php
require 'config.php';

$sql = "SELECT id, task_name, is_completed FROM tasks ORDER BY created_at DESC";
$result = $conn->query($sql);

$tasks = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Asegurarnos que is_completed sea un booleano para JavaScript
        $row['is_completed'] = (bool)$row['is_completed'];
        $tasks[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($tasks);
?>