<?php
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$taskName = $data['task_name'];

if (!empty($taskName)) {
    $stmt = $conn->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    $stmt->bind_param("s", $taskName);
    
    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        echo json_encode(['success' => true, 'id' => $last_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la tarea.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'El nombre de la tarea no puede estar vacío.']);
}

$conn->close();
?>