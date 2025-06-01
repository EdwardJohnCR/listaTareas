<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener todas las tareas
        getTasks();
        break;
    case 'POST':
        // Crear nueva tarea
        createTask();
        break;
    case 'PUT':
        // Actualizar tarea existente
        updateTask();
        break;
    case 'DELETE':
        // Eliminar tarea
        deleteTask();
        break;
    default:
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido"));
        break;
}

function getTasks() {
    global $conn;
    
    $query = "SELECT * FROM tasks ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    $tasks = array();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    echo json_encode($tasks);
}

function createTask() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"));
    
    $machine = $data->machine;
    $description = $data->description;
    $priority = $data->priority;
    $department = $data->department;
    $status = 'Pendiente'; // Estado por defecto
    
    $query = "INSERT INTO tasks (machine, description, priority, department, status) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $machine, $description, $priority, $department, $status);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        echo json_encode(array(
            "id" => $id,
            "message" => "Tarea creada exitosamente"
        ));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Error al crear tarea"));
    }
}

function updateTask() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"));
    
    $id = $data->id;
    $fields = array();
    $params = array();
    $types = "";
    
    if (isset($data->description)) {
        $fields[] = "description = ?";
        $params[] = $data->description;
        $types .= "s";
    }
    
    if (isset($data->priority)) {
        $fields[] = "priority = ?";
        $params[] = $data->priority;
        $types .= "s";
    }
    
    if (isset($data->department)) {
        $fields[] = "department = ?";
        $params[] = $data->department;
        $types .= "s";
    }
    
    if (isset($data->status)) {
        $fields[] = "status = ?";
        $params[] = $data->status;
        $types .= "s";
    }
    
    if (isset($data->completed)) {
        $fields[] = "completed = ?";
        $params[] = $data->completed;
        $types .= "i";
    }
    
    if (isset($data->notes)) {
        $fields[] = "notes = ?";
        $params[] = $data->notes;
        $types .= "s";
    }
    
    if (count($fields) == 0) {
        http_response_code(400);
        echo json_encode(array("message" => "No hay campos para actualizar"));
        return;
    }
    
    $query = "UPDATE tasks SET " . implode(", ", $fields) . " WHERE id = ?";
    $params[] = $id;
    $types .= "i";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(array("message" => "Tarea actualizada exitosamente"));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Error al actualizar tarea"));
    }
}

function deleteTask() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"));
    $id = $data->id;
    
    $query = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(array("message" => "Tarea eliminada exitosamente"));
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Error al eliminar tarea"));
    }
}
?>  