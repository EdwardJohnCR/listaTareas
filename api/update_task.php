<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php';

try {
    // Autenticación...
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$authHeader) throw new Exception('No autorizado', 401);
    list($type, $credentials) = explode(' ', $authHeader, 2);
    $decodedCredentials = base64_decode($credentials);
    list($user, $pass) = explode(':', $decodedCredentials, 2);
    if ($user !== DB_USER || $pass !== DB_PASS) throw new Exception('Credenciales incorrectas', 401);

    $data = json_decode(file_get_contents("php://input"));
    if (empty($data->id)) throw new Exception('ID de tarea requerido', 400);

    // La consulta correcta
    $query = "UPDATE tasks SET status = ?, department = ?, priority = ?, completed = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    // Convertir el booleano 'completed' a un entero (1 o 0)
    $completed = $data->completed ? 1 : 0;
    
    // El 'bind_param' corregido
    $stmt->bind_param("sssii", $data->status, $data->department, $data->priority, $completed, $data->id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Tarea actualizada"]);
    } else {
        throw new Exception("Error al actualizar: " . $stmt->error);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(["message" => $e->getMessage()]);
}
?>