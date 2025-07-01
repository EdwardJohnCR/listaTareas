<?php
/**
 * Este archivo contiene la configuración de la base de datos
 * y establece la conexión.
 */

// Muestra todos los errores de PHP. Muy útil para depurar.
// Deberías desactivarlo en un sitio en producción por seguridad.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Definición de Constantes de la Base de Datos ---
// Usar define() es una buena práctica para valores que no cambian.
define('DB_HOST', 'localhost');
define('DB_USER', 'u310879082_lisTa_User');
define('DB_PASS', 'D14cF]!Ft]');
define('DB_NAME', 'u310879082_lisTa_DB');

// --- Crear Conexión ---
// Se usan las constantes definidas arriba.
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Verificar Conexión ---
// Si la conexión falla, el script se detiene aquí y envía un error en formato JSON.
if ($conn->connect_error) {
    http_response_code(503); // Service Unavailable
    // El 'die()' detiene la ejecución del script por completo.
    die(json_encode([
        "message" => "Error fatal: No se pudo conectar a la base de datos.",
        "error" => $conn->connect_error
    ]));
}

// Establece el charset a utf8mb4 para soportar todos los caracteres (tildes, emojis, etc.)
$conn->set_charset("utf8mb4");

?>