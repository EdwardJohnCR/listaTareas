<?php
header('Content-Type: application/json');
require_once 'config.php';

// Lee el JSON que envía el JavaScript
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['task_name']) && !empty(trim($data['task_name']))) {
    $taskName = trim($data['task_name']);

    // Usar sentencias preparadas para máxima seguridad
    $stmt = $conn->prepare("INSERT INTO tasks (task_name) VALUES (?)");
    $stmt->bind_param("s", $taskName);
    
    if ($stmt->execute()) {
        http_response_code(201); // Código para "Creado"
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        http_response_code(500); // Error del servidor
        echo json_encode(['success' => false, 'message' => 'Error del servidor al guardar.']);
    }
    $stmt->close();
} else {
    http_response_code(400); // Petición incorrecta
    echo json_encode(['success' => false, 'message' => 'El nombre de la tarea es obligatorio.']);
}

$conn->close();
?>