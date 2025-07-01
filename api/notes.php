<?php
 header("Content-Type: application/json; charset=UTF-8");
 require_once 'config.php';
 

 $method = $_SERVER['REQUEST_METHOD'];
 

 try {
     // Autenticación (simplificada, debería ser la misma que en otros archivos)
     $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
     if (!$authHeader) throw new Exception('No autorizado', 401);
     list($type, $credentials) = explode(' ', $authHeader, 2);
     $decodedCredentials = base64_decode($credentials);
     list($user, $pass) = explode(':', $decodedCredentials, 2);
     if ($user !== DB_USER || $pass !== DB_PASS) throw new Exception('Credenciales incorrectas', 401);
 

     if ($method === 'POST') {
         $data = json_decode(file_get_contents("php://input"));
         if (empty($data->task_id) || empty($data->note)) throw new Exception('Datos incompletos', 400);
 

         $stmt = $conn->prepare("SELECT notes FROM tasks WHERE id = ?");
         $stmt->bind_param("i", $data->task_id);
         $stmt->execute();
         $row = $stmt->get_result()->fetch_assoc();
         $existingNotes = $row['notes'] ?? '';
 

         $newNote = date("Y-m-d H:i") . ": " . trim($data->note);
         $updatedNotes = $existingNotes ? $existingNotes . "\n---\n" . $newNote : $newNote;
 

         $updateStmt = $conn->prepare("UPDATE tasks SET notes = ? WHERE id = ?");
         $updateStmt->bind_param("si", $updatedNotes, $data->task_id);
         if ($updateStmt->execute()) {
             echo json_encode(['message' => 'Nota guardada.']);
         } else {
             throw new Exception("Error al guardar la nota.");
         }
     } elseif ($method === 'GET' && isset($_GET['task_id'])) {
         $taskId = intval($_GET['task_id']);
         $stmt = $conn->prepare("SELECT notes FROM tasks WHERE id = ?");
         $stmt->bind_param("i", $taskId);
         $stmt->execute();
         $result = $stmt->get_result();
         $row = $result->fetch_assoc();
         echo json_encode(['notes' => $row['notes'] ?? '']);
     } else {
         http_response_code(400);
         echo json_encode(['message' => 'Solicitud incorrecta.']);
     }
 

 } catch (Exception $e) {
     $code = $e->getCode() ?: 500;
     http_response_code($code);
     echo json_encode(["message" => $e->getMessage()]);
 }