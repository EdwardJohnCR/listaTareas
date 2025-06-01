<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    saveAllTasks();
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Método no permitido"));
}

function saveAllTasks() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"));
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Eliminar todas las tareas existentes
        $conn->query("DELETE FROM tasks");
        
        // Insertar las nuevas tareas
        $query = "INSERT INTO tasks (machine, description, priority, department, status, notes, completed, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        foreach ($data as $task) {
            $created_at = isset($task->created_at) ? $task->created_at : date('Y-m-d H:i:s');
            $stmt->bind_param(
                "ssssssis",
                $task->machine,
                $task->description,
                $task->priority,
                $task->department,
                $task->status,
                $task->notes,
                $task->completed,
                $created_at
            );
            $stmt->execute();
        }
        
        // Confirmar transacción
        $conn->commit();
        echo json_encode(array("message" => "Todas las tareas fueron guardadas exitosamente"));
    } catch (Exception $e) {
        // Revertir en caso de error
        $conn->rollback();
        http_response_code(500);
        echo json_encode(array("message" => "Error al guardar tareas: " . $e->getMessage()));
    }
}
?>