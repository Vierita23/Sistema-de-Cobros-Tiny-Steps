<?php
/**
 * Archivo de ejemplo para configuración de base de datos
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo y renómbralo a: database.php
 * 2. Completa los datos de tu base de datos del hosting
 * 3. NO subas este archivo al hosting con datos reales en el repositorio
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost'); // Cambiar si el hosting usa otra IP/host
define('DB_USER', 'tu_usuario_db'); // Usuario de la base de datos del hosting
define('DB_PASS', 'tu_contraseña_db'); // Contraseña de la base de datos
define('DB_NAME', 'tu_nombre_db'); // Nombre de la base de datos

// Conexión a la base de datos
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8");
        return $conn;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
