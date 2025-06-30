<?php
// Devolver siempre contenido de tipo JSON
header('Content-Type: application/json');
require_once 'config.php';

// Leer el JSON que envía el JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Obtener el nombre de la tarea del arreglo de datos
$taskName = isset($data['task_name']) ? trim($data['task_name']) : '';

if (!empty($taskName)) {
    $stmt = $conn->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $taskName);
    
    if ($stmt->execute()) {
        $last_id = $conn->insert_id;
        // Devolver una respuesta clara de éxito con el nuevo ID
        echo json_encode(['success' => true, 'id' => $last_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la tarea: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'El nombre de la tarea no puede estar vacío.']);
}

$conn->close();
?>