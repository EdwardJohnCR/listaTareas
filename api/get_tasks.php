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

try {
    $query = "SELECT * FROM tasks ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    $tasks = array();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    echo json_encode($tasks);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Error al obtener tareas',
        'error' => $e->getMessage()
    ]);
}
?>