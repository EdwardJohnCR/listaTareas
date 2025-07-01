<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // Autenticación (simplificada, pero puedes usar la misma que en los otros)
    
    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        if (empty($data->task_id) || empty($data->note)) throw new Exception('Datos incompletos', 400);
        
        $stmt = $conn->prepare("SELECT notes FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $data->task_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $existingNotes = $row['notes'] ?? '';

        $newNote = date("Y-m-d H:i") . ": " . trim($data->note);
        $updatedNotes = $existingNotes ? $existingNotes . "\n---\n" . $newNote : $newNote;

        $updateStmt = $conn->prepare("UPDATE tasks SET notes = ? WHERE id = ?");
        $updateStmt->bind_param("si", $updatedNotes, $data->task_id);
        if ($updateStmt->execute()) {
            echo json_encode(['message' => 'Nota guardada.']);
        } else {
            throw new Exception("Error al guardar la nota.");
        }
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(["message" => $e->getMessage()]);
}
?>