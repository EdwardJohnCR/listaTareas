<?php
echo "<h1>Prueba de Conexión a la Base de Datos</h1>";

// Incluir el archivo de configuración
// require 'config.php' intentará conectarse usando los datos que pusiste.
require 'config.php';

// El archivo 'config.php' ya tiene una verificación de conexión.
// Si el script llega hasta aquí sin mostrar un error, significa que la conexión fue exitosa.

echo "<p style='color: green; font-weight: bold;'>¡Conexión a la base de datos exitosa!</p>";
echo "<p>El script pudo incluir 'config.php' y establecer una conexión sin problemas.</p>";

// No olvides cerrar la conexión
$conn->close();
?><?php
// Desactivar temporalmente los errores en pantalla para dar un mensaje personalizado
ini_set('display_errors', 0);
error_reporting(0);

echo "<h1>Diagnóstico de Conexión a Base de Datos</h1>";
$configFile = 'config.php';

if (!file_exists($configFile)) {
    die("<p style='color: red; font-weight: bold;'>ERROR: No se encuentra el archivo '{$configFile}'. Asegúrate de que esté en la misma carpeta.</p>");
}

require_once $configFile;

echo "<p>Intentando conectar con los siguientes datos:</p>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
echo "<li><strong>Usuario:</strong> " . DB_USER . "</li>";
echo "<li><strong>Base de Datos:</strong> " . DB_NAME . "</li>";
echo "<li><strong>Contraseña:</strong> " . (defined('DB_PASS') && DB_PASS ? '****** (definida)' : 'NO definida') . "</li>";
echo "</ul>";

// Intentar la conexión y capturar errores específicos
try {
    // La conexión ya se intenta en config.php, aquí la verificamos
    if ($conn && $conn->connect_error) {
        // Lanzar una excepción para ser capturada abajo
        throw new Exception($conn->connect_error, $conn->connect_errno);
    }

    if (!$conn) {
        throw new Exception("El objeto de conexión no es válido. Revisa el código en config.php.");
    }
    
    // Si llegamos aquí, la conexión fue exitosa
    echo "<p style='font-size: 1.2em; color: green; font-weight: bold;'>✅ ¡Conexión Exitosa!</p>";
    echo "<p>La base de datos '" . DB_NAME . "' está respondiendo correctamente.</p>";

} catch (Exception $e) {
    // Si hay un error, mostrar un mensaje claro
    echo "<p style='font-size: 1.2em; color: red; font-weight: bold;'>❌ ERROR DE CONEXIÓN</p>";
    echo "<p><strong>Mensaje del error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Código del error:</strong> " . htmlspecialchars($e->getCode()) . "</p>";
    
    echo "<h3>Posibles Soluciones:</h3>";
    echo "<ul>";
    if ($e->getCode() == 1045) { // Access Denied
        echo "<li>Verifica que el <strong>usuario</strong> y la <strong>contraseña</strong> en `config.php` sean 100% correctos. Distingue mayúsculas de minúsculas.</li>";
        echo "<li>Si copiaste y pegaste la contraseña, asegúrate de que no haya espacios en blanco al inicio o al final.</li>";
    } elseif ($e->getCode() == 1044) { // Access denied for user to database
         echo "<li>El usuario no tiene permisos para esa base de datos. En Hostinger, asegúrate de haber asociado el usuario a la base de datos correctamente.</li>";
    } elseif ($e->getCode() == 1049) { // Unknown Database
        echo "<li>El <strong>nombre de la base de datos</strong> (`DB_NAME`) en `config.php` es incorrecto.</li>";
    } elseif ($e->getCode() == 2002) { // Can't connect to server
        echo "<li>El <strong>Host</strong> (`DB_HOST`) es incorrecto. Confírmalo en tu panel de Hostinger. Probablemente deba ser `localhost`.</li>";
    } else {
        echo "<li>Revisa todos los datos en tu archivo `config.php` (Host, Usuario, Contraseña, Nombre de la DB).</li>";
    }
    echo "</ul>";
}

// Cerrar la conexión si existe
if ($conn) {
    $conn->close();
}
?>