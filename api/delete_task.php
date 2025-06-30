<?php
header('Content-Type: application/json');
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$taskId = isset($data['id']) ? (int)$data['id'] : 0;

if ($taskId > 0) {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID de tarea no válido.']);
}

$conn->close();
?>