<?php
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['id'];

if (!empty($taskId)) {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la tarea.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID de tarea no proporcionado.']);
}

$conn->close();
?>