<?php
header('Content-Type: application/json');
require_once 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$taskId = isset($data['id']) ? (int)$data['id'] : 0;
// Verifica si 'is_completed' existe en los datos
$isCompleted = isset($data['is_completed']) ? (bool)$data['is_completed'] : false;

if ($taskId > 0) {
    $stmt = $conn->prepare("UPDATE tasks SET is_completed = ? WHERE id = ?");
    $completedValue = $isCompleted ? 1 : 0; // Convertir booleano a 1 o 0 para la BD
    $stmt->bind_param("ii", $completedValue, $taskId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}

$conn->close();
?>