<?php
// Script de instalación del sistema
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'tiny_steps_cobros';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conectar sin seleccionar base de datos
        $conn = new mysqli($db_host, $db_user, $db_pass);
        
        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }
        
        // Crear base de datos si no existe
        $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->select_db($db_name);
        
        // Leer y ejecutar el archivo SQL
        $sql_file = __DIR__ . '/database.sql';
        if (!file_exists($sql_file)) {
            throw new Exception("No se encontró el archivo database.sql");
        }
        
        $sql = file_get_contents($sql_file);
        
        // Dividir el SQL en sentencias individuales
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                // Remover comentarios de línea
                $statement = preg_replace('/--.*$/m', '', $statement);
                if (!empty(trim($statement))) {
                    if (!$conn->query($statement)) {
                        throw new Exception("Error ejecutando SQL: " . $conn->error . "<br>SQL: " . substr($statement, 0, 100));
                    }
                }
            }
        }
        
        $conn->close();
        $success = true;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación del Sistema | Tiny Steps</title>
        <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="alternate icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo-section">
                <h1 class="logo-title">
                    <span style="color: #FF00FF;">T</span>
                    <span style="color: #00FF00;">i</span>
                    <span style="color: #0000FF;">n</span>
                    <span style="color: #FF0000;">y</span>
                    <span style="color: #FFFF00;">S</span>
                    <span style="color: #00FF00;">t</span>
                    <span style="color: #FF0000;">e</span>
                    <span style="color: #00BFFF;">p</span>
                    <span style="color: #FF00FF;">s</span>
                </h1>
                <p class="logo-subtitle">Instalación del Sistema</p>
            </div>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <h3>¡Instalación completada exitosamente!</h3>
                    <p>La base de datos ha sido creada y configurada correctamente.</p>
                    <p style="margin-top: 20px;">
                        <strong>Credenciales por defecto:</strong><br>
                        Email: admin@tinysteps.com<br>
                        Contraseña: admin123
                    </p>
                    <div style="margin-top: 30px;">
                        <a href="index.php" class="btn btn-primary btn-block">Ir al Login</a>
                    </div>
                </div>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <p style="margin-bottom: 20px; color: #666;">
                        Este script creará la base de datos y todas las tablas necesarias para el sistema.
                    </p>
                    <p style="margin-bottom: 20px; color: #666; font-size: 0.9em;">
                        <strong>Configuración:</strong><br>
                        Host: <?php echo htmlspecialchars($db_host); ?><br>
                        Usuario: <?php echo htmlspecialchars($db_user); ?><br>
                        Base de datos: <?php echo htmlspecialchars($db_name); ?>
                    </p>
                    <button type="submit" class="btn btn-primary btn-block">Instalar Base de Datos</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>


