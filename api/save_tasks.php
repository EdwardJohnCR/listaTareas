<?php
require_once 'config.php';

header('Content-Type: application/json');

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

if (!$data || !isset($data['tasks'])) {
    http_response_code(400);
    echo json_encode(['message' => 'Datos inválidos']);
    exit;
}

try {
    $conn->begin_transaction();
    
    // Limpiar tabla existente
    $conn->query('TRUNCATE TABLE tasks');
    
    // Preparar declaración para insertar
    $stmt = $conn->prepare(
        'INSERT INTO tasks (machine, description, priority, department, status, notes, completed, created_at) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    
    foreach ($data['tasks'] as $task) {
        $createdAt = isset($task['createdAt']) ? $task['createdAt'] : date('Y-m-d H:i:s');
        $stmt->bind_param(
            'ssssssis',
            $task['machine'],
            $task['description'],
            $task['priority'],
            $task['department'],
            $task['status'],
            $task['notes'],
            $task['completed'],
            $createdAt
        );
        $stmt->execute();
    }
    
    $conn->commit();
    
    echo json_encode([
        'message' => 'Tareas guardadas exitosamente',
        'count' => count($data['tasks'])
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'message' => 'Error al guardar tareas',
        'error' => $e->getMessage()
    ]);
}
?>