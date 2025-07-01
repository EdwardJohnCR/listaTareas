<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'config.php'; // Incluye la conexión y las constantes

try {
    // Autenticación...
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if (!$authHeader) throw new Exception('No se encontró el encabezado de autorización.', 401);
    list($type, $credentials) = explode(' ', $authHeader, 2);
    $decodedCredentials = base64_decode($credentials);
    list($user, $pass) = explode(':', $decodedCredentials, 2);
    if ($user !== DB_USER || $pass !== DB_PASS) throw new Exception('Credenciales incorrectas.', 401);

    // Lógica para guardar...
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->description) || empty($data->machine)) {
        throw new Exception('La máquina y la descripción son obligatorias.', 400);
    }
    
    // La consulta SQL ahora coincide con la nueva tabla
    $query = "INSERT INTO tasks (machine, description, priority, department, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    $status = 'Pendiente'; // Status inicial
    $stmt->bind_param(
        "sssss",
        $data->machine,
        $data->description,
        $data->priority,
        $data->department,
        $status
    );

    if ($stmt->execute()) {
        http_response_code(201); // Creado
        echo json_encode(["message" => "Tarea guardada con éxito."]);
    } else {
        throw new Exception("No se pudo guardar la tarea: " . $stmt->error);
    }

} catch (Exception $e) {
    // Si cualquier cosa falla, envía el código de error correcto
    $errorCode = $e->getCode() > 0 ? $e->getCode() : 500;
    http_response_code($errorCode);
    echo json_encode(["message" => "Ocurrió un error en el servidor.", "error" => $e->getMessage()]);
}
?>