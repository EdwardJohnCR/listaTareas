<?php
require_once 'config.php';

// Verificar autenticación básica
if (!isset($_SERVER['PHP_AUTH_USER']) || 
    !isset($_SERVER['PHP_AUTH_PW']) || 
    $_SERVER['PHP_AUTH_USER'] !== 'u310879082_lisTa_User' || 
    $_SERVER['PHP_AUTH_PW'] !== '|6+Ai1s&m?aQ') {
    header('WWW-Authenticate: Basic realm="Task Manager"');
    http_response_code(401);
    echo json_encode(['message' => 'Autenticación requerida']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['message' => 'Datos inválidos']);
    exit;
}

try {
    $query = "INSERT INTO tasks (id, machine, description, priority, department, status, notes, completed, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
              ON DUPLICATE KEY UPDATE 
              machine = VALUES(machine),
              description = VALUES(description),
              priority = VALUES(priority),
              department = VALUES(department),
              status = VALUES(status),
              notes = VALUES(notes),
              completed = VALUES(completed),
              updated_at = CURRENT_TIMESTAMP";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param(
        'issssssis',
        $data['id'],
        $data['machine'],
        $data['description'],
        $data['priority'],
        $data['department'],
        $data['status'],
        $data['notes'],
        $data['completed'],
        $data['createdAt']
    );
    
    $stmt->execute();
    
    echo json_encode([
        'message' => 'Tarea guardada exitosamente',
        'id' => $data['id']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Error al guardar tarea',
        'error' => $e->getMessage()
    ]);
}
?>