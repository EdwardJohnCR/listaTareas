<?php
// Devolver siempre contenido de tipo JSON
header('Content-Type: application/json');
require_once 'config.php';

$sql = "SELECT id, task_name, is_completed FROM tasks ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    // Si hay un error en la consulta, devuélvelo para poder depurar
    echo json_encode(['success' => false, 'message' => 'Error en la consulta SQL: ' . $conn->error]);
    exit;
}

$tasks = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Correcto: Convertir el valor de la BD (0 o 1) a un booleano (false o true)
        $row['is_completed'] = (bool)$row['is_completed']; 
        $tasks[] = $row;
    }
}

$conn->close();

// Devuelve directamente el arreglo de tareas
echo json_encode($tasks); 
?>