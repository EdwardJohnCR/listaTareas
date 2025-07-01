<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

// Este script podría tener su propia lógica de autenticación
// Por simplicidad, se omite aquí pero debería implementarse

$method = $_SERVER['REQUEST_METHOD'];
$taskId = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if ($method === 'GET' && $taskId > 0) {
    $stmt = $conn->prepare("SELECT notes FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    echo json_encode($row ? $row : ['notes' => '']);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (empty($data->task_id) || empty($data->note)) {
        http_response_code(400);
        echo json_encode(['message' => 'Datos incompletos.']);
        exit;
    }
    
    // Obtener notas existentes y añadir la nueva
    $stmt = $conn->prepare("SELECT notes FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $data->task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $existingNotes = $row ? $row['notes'] : '';

    $newNote = date("Y-m-d H:i") . ": " . $data->note;
    $updatedNotes = $existingNotes ? $existingNotes . "\n---\n" . $newNote : $newNote;

    $updateStmt = $conn->prepare("UPDATE tasks SET notes = ? WHERE id = ?");
    $updateStmt->bind_param("si", $updatedNotes, $data->task_id);
    
    if ($updateStmt->execute()) {
        echo json_encode(['message' => 'Nota guardada.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Error al guardar la nota.']);
    }
}
?>