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

    // Lógica para eliminar...
    $data = json_decode(file_get_contents("php://input"));
    if (empty($data->id)) throw new Exception('ID de tarea requerido', 400);

    $query = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $data->id);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Tarea eliminada"]);
    } else {
        throw new Exception("Error al eliminar: " . $stmt->error);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(["message" => $e->getMessage()]);
}
?>