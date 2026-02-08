<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tiny_steps_cobros');

// Conexión a la base de datos (lanza excepción en lugar de die para evitar pantalla en blanco)
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception('Conexión: ' . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}
?>



