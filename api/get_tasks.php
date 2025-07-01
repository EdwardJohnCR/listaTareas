<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

try {
    // Autenticación (simplificada y robusta)
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$authHeader) throw new Exception('No autorizado', 401);
    list($type, $credentials) = explode(' ', $authHeader, 2);
    $decodedCredentials = base64_decode($credentials);
    list($user, $pass) = explode(':', $decodedCredentials, 2);
    if ($user !== DB_USER || $pass !== DB_PASS) throw new Exception('Credenciales incorrectas', 401);

    // Lógica para obtener tareas
    $tasks = [];
    $query = "SELECT id, machine, description, priority, department, status, notes, completed FROM tasks ORDER BY created_at DESC";
    $result = $conn->query($query);

    if ($result === false) {
        throw new Exception("Error en la consulta SQL: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        // Asegurarse que 'completed' sea un booleano para JS
        $row['completed'] = (bool)$row['completed'];
        $tasks[] = $row;
    }
    
    $conn->close();

    http_response_code(200);
    echo json_encode($tasks);

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(["message" => $e->getMessage()]);
}
?>