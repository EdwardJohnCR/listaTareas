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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'ID inválido']);
    exit;
}

try {
    $query = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['message' => 'Tarea eliminada exitosamente']);
    } else {
        http_response_code(404);
        echo json_encode(['message' => 'Tarea no encontrada']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Error al eliminar tarea',
        'error' => $e->getMessage()
    ]);
}
?>