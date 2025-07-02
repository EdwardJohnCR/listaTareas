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
    if (empty($data->description) || empty($data->machine)) {
        throw new Exception('La máquina y la descripción son obligatorias.', 400);
    }
    
    // --- VALIDACIÓN ANTI-DUPLICADOS (TU SOLUCIÓN IMPLEMENTADA) ---
    // Prepara una consulta para ver si ya existe una tarea idéntica (misma máquina y descripción)
    $checkQuery = "SELECT id FROM tasks WHERE machine = ? AND description = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ss", $data->machine, $data->description);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    // Si la consulta devuelve al menos una fila, la tarea ya existe.
    if ($result->num_rows > 0) {
        http_response_code(409); // 409 Conflict: indica un duplicado
        exit(json_encode(["message" => "Esta tarea ya existe y no se guardará de nuevo."]));
    }
    $checkStmt->close();
    // --- FIN DE LA VALIDACIÓN ---


    // Si no hay duplicados, procede a insertar la nueva tarea
    $query = "INSERT INTO tasks (machine, description, priority, department, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $status = 'Pendiente';
    $stmt->bind_param("sssss", $data->machine, $data->description, $data->priority, $data->department, $status);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "Tarea guardada con éxito.", "id" => $conn->insert_id]);
    } else {
        throw new Exception("No se pudo guardar la tarea: " . $stmt->error);
    }

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode(["message" => $e->getMessage()]);
}
?>