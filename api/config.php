<?php
$servername = "localhost"; // O el que te haya dado Hostinger
$username = "u310879082_lisTa_User"; // El usuario que creaste
$password = "D14cF]!Ft]"; // La contraseña que creaste
$dbname = "u310879082_lisTa_DB"; // El nombre de la base de datos que creaste

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
