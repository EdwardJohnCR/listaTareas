<?php
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$taskId = $data['id'];
$isCompleted = $data['is_completed'];

if (isset($taskId) && isset($isCompleted)) {
    $stmt = $conn->prepare("UPDATE tasks SET is_completed = ? WHERE id = ?");
    // Convertimos el booleano a entero (0 o 1) para la base de datos
    $completedValue = $isCompleted ? 1 : 0;
    $stmt->bind_param("ii", $completedValue, $taskId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la tarea.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
}

$conn->close();
?>
