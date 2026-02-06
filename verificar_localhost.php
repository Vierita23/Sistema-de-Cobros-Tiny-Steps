<?php
/**
 * Script de verificación del estado de localhost
 * Accede a: http://localhost/Sistema de Cobros Tiny Steps/verificar_localhost.php
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Localhost | Tiny Steps</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .check-item.ok {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }
        .status {
            font-weight: bold;
            margin-right: 10px;
        }
        .status.ok { color: #28a745; }
        .status.error { color: #dc3545; }
        .status.warning { color: #ffc107; }
        .actions {
            margin-top: 30px;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 5px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            border-left: 4px solid #2196F3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Localhost</h1>
        
        <?php
        $checks = [];
        
        // Verificar PHP
        $phpVersion = phpversion();
        $checks[] = [
            'name' => 'PHP',
            'status' => 'ok',
            'message' => "Versión: $phpVersion"
        ];
        
        // Verificar Apache
        $apacheRunning = function_exists('apache_get_version');
        if ($apacheRunning) {
            $checks[] = [
                'name' => 'Apache',
                'status' => 'ok',
                'message' => 'Apache está corriendo correctamente'
            ];
        } else {
            $checks[] = [
                'name' => 'Apache',
                'status' => 'warning',
                'message' => 'No se puede verificar Apache desde PHP (esto es normal)'
            ];
        }
        
        // Verificar MySQL
        $mysqlOk = false;
        $mysqlError = '';
        try {
            $conn = @new mysqli('localhost', 'root', '');
            if ($conn->connect_error) {
                $mysqlError = $conn->connect_error;
            } else {
                $mysqlOk = true;
                $conn->close();
            }
        } catch (Exception $e) {
            $mysqlError = $e->getMessage();
        }
        
        if ($mysqlOk) {
            $checks[] = [
                'name' => 'MySQL',
                'status' => 'ok',
                'message' => 'MySQL está corriendo y accesible'
            ];
        } else {
            $checks[] = [
                'name' => 'MySQL',
                'status' => 'error',
                'message' => "MySQL no está disponible: " . ($mysqlError ?: 'Error de conexión')
            ];
        }
        
        // Verificar archivo de configuración
        if (file_exists(__DIR__ . '/config/database.php')) {
            $checks[] = [
                'name' => 'Archivo de Configuración',
                'status' => 'ok',
                'message' => 'config/database.php existe'
            ];
        } else {
            $checks[] = [
                'name' => 'Archivo de Configuración',
                'status' => 'error',
                'message' => 'config/database.php no encontrado'
            ];
        }
        
        // Verificar base de datos
        $dbExists = false;
        if ($mysqlOk) {
            try {
                require_once __DIR__ . '/config/database.php';
                $conn = getDBConnection();
                $dbExists = true;
                $conn->close();
                $checks[] = [
                    'name' => 'Base de Datos',
                    'status' => 'ok',
                    'message' => 'Base de datos "tiny_steps_cobros" existe y es accesible'
                ];
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Base de Datos',
                    'status' => 'error',
                    'message' => 'No se puede conectar a la base de datos. Ejecuta install.php primero.'
                ];
            }
        } else {
            $checks[] = [
                'name' => 'Base de Datos',
                'status' => 'error',
                'message' => 'No se puede verificar (MySQL no está disponible)'
            ];
        }
        
        // Verificar archivo SQL
        if (file_exists(__DIR__ . '/database/database.sql')) {
            $checks[] = [
                'name' => 'Archivo SQL',
                'status' => 'ok',
                'message' => 'database/database.sql existe'
            ];
        } else {
            $checks[] = [
                'name' => 'Archivo SQL',
                'status' => 'error',
                'message' => 'database/database.sql no encontrado'
            ];
        }
        
        // Mostrar resultados
        foreach ($checks as $check) {
            $statusClass = $check['status'];
            $statusIcon = $check['status'] === 'ok' ? '✅' : ($check['status'] === 'error' ? '❌' : '⚠️');
            echo "<div class='check-item $statusClass'>";
            echo "<span class='status $statusClass'>$statusIcon</span>";
            echo "<strong>{$check['name']}:</strong> {$check['message']}";
            echo "</div>";
        }
        
        // Resumen
        $allOk = array_reduce($checks, function($carry, $item) {
            return $carry && ($item['status'] === 'ok' || $item['status'] === 'warning');
        }, true);
        
        echo "<div class='info'>";
        if ($allOk && $dbExists) {
            echo "<h3>✅ Sistema listo para usar</h3>";
            echo "<p>Todo está configurado correctamente. Puedes acceder al sistema.</p>";
        } else if ($allOk && !$dbExists) {
            echo "<h3>⚠️ Instalación necesaria</h3>";
            echo "<p>Los servicios están funcionando, pero necesitas instalar la base de datos.</p>";
        } else {
            echo "<h3>❌ Problemas detectados</h3>";
            echo "<p>Hay algunos problemas que necesitas resolver antes de usar el sistema.</p>";
        }
        echo "</div>";
        ?>
        
        <div class="actions">
            <?php if (!$dbExists): ?>
                <a href="install.php" class="btn">📦 Instalar Base de Datos</a>
            <?php endif; ?>
            <a href="index.php" class="btn">🚪 Ir al Login</a>
            <a href="verificar_localhost.php" class="btn">🔄 Verificar de Nuevo</a>
        </div>
    </div>
</body>
</html>
