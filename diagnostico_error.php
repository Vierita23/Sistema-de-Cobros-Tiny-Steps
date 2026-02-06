<?php
/**
 * Script de Diagnóstico de Error 500
 * Ejecuta este archivo para identificar el problema
 */

// Habilitar mostrar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Error - Tiny Steps</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #EF4444;
            border-bottom: 3px solid #EF4444;
            padding-bottom: 10px;
        }
        .error {
            background: #FEE2E2;
            border-left: 4px solid #EF4444;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .exito {
            background: #D1FAE5;
            border-left: 4px solid #10B981;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .advertencia {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .codigo {
            background: #1F2937;
            color: #10B981;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Error 500</h1>
        
        <?php
        $errores = [];
        $exitos = [];
        
        // Verificar PHP
        $exitos[] = "PHP " . phpversion() . " - Funcionando";
        
        // Verificar archivos principales
        $archivos_requeridos = [
            'index.php' => 'Archivo principal',
            'config/database.php' => 'Configuración de base de datos',
            'config/session.php' => 'Configuración de sesión',
            'assets/css/style.css' => 'Estilos CSS'
        ];
        
        foreach ($archivos_requeridos as $archivo => $nombre) {
            if (file_exists($archivo)) {
                $exitos[] = "$nombre - Existe";
            } else {
                $errores[] = "$nombre - NO encontrado ($archivo)";
            }
        }
        
        // Verificar sintaxis de archivos PHP principales
        $archivos_php = [
            'index.php',
            'config/database.php',
            'config/session.php'
        ];
        
        foreach ($archivos_php as $archivo) {
            if (file_exists($archivo)) {
                $output = [];
                $return_var = 0;
                exec("php -l \"$archivo\" 2>&1", $output, $return_var);
                if ($return_var === 0) {
                    $exitos[] = "Sintaxis de $archivo - Correcta";
                } else {
                    $errores[] = "Error de sintaxis en $archivo: " . implode("\n", $output);
                }
            }
        }
        
        // Verificar conexión a base de datos
        if (file_exists('config/database.php')) {
            try {
                require_once 'config/database.php';
                $conn = getDBConnection();
                $exitos[] = "Conexión a base de datos - Exitosa";
                $conn->close();
            } catch (Exception $e) {
                $errores[] = "Error de conexión a base de datos: " . $e->getMessage();
            } catch (Error $e) {
                $errores[] = "Error de conexión a base de datos: " . $e->getMessage();
            }
        }
        
        // Verificar .htaccess
        if (file_exists('.htaccess')) {
            $exitos[] = "Archivo .htaccess - Presente";
            $advertencia_htaccess = true;
        } else {
            $advertencia_htaccess = false;
        }
        
        // Verificar permisos
        $carpetas = ['uploads/comprobantes', 'logs'];
        foreach ($carpetas as $carpeta) {
            if (is_dir($carpeta)) {
                if (is_writable($carpeta)) {
                    $exitos[] = "Permisos de $carpeta - OK";
                } else {
                    $errores[] = "Sin permisos de escritura en $carpeta";
                }
            }
        }
        
        // Mostrar resultados
        if (!empty($exitos)) {
            echo "<h2 style='color: #10B981;'>✓ Verificaciones Exitosas</h2>";
            foreach ($exitos as $exito) {
                echo "<div class='exito'>" . htmlspecialchars($exito) . "</div>";
            }
        }
        
        if (!empty($errores)) {
            echo "<h2 style='color: #EF4444;'>✗ Errores Encontrados</h2>";
            foreach ($errores as $error) {
                echo "<div class='error'>" . nl2br(htmlspecialchars($error)) . "</div>";
            }
        }
        
        if ($advertencia_htaccess) {
            echo "<div class='advertencia'>";
            echo "<strong>⚠️ Posible problema con .htaccess:</strong><br>";
            echo "El archivo .htaccess puede estar causando el error. Prueba renombrarlo temporalmente a .htaccess.bak";
            echo "</div>";
        }
        
        // Intentar cargar index.php
        echo "<h2 style='color: #3B82F6;'>🔧 Prueba de Carga</h2>";
        echo "<div class='advertencia'>";
        echo "Intentando cargar index.php...<br><br>";
        
        ob_start();
        try {
            if (file_exists('index.php')) {
                // Capturar cualquier error
                $error_handler = function($errno, $errstr, $errfile, $errline) {
                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
                };
                set_error_handler($error_handler);
                
                // Intentar incluir
                include 'index.php';
                restore_error_handler();
                echo "<div class='exito'>index.php se cargó correctamente</div>";
            } else {
                echo "<div class='error'>index.php no encontrado</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>Error al cargar index.php:<br>" . htmlspecialchars($e->getMessage()) . "</div>";
        } catch (Error $e) {
            echo "<div class='error'>Error fatal al cargar index.php:<br>" . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ob_end_clean();
        echo "</div>";
        ?>
        
        <h2 style="color: #6366F1;">💡 Soluciones Rápidas</h2>
        
        <div class="advertencia">
            <strong>1. Verificar .htaccess:</strong><br>
            Renombra temporalmente .htaccess a .htaccess.bak y recarga la página
        </div>
        
        <div class="advertencia">
            <strong>2. Verificar logs de error:</strong><br>
            Revisa el archivo de error de Apache en XAMPP:<br>
            <div class="codigo">C:\xampp\apache\logs\error.log</div>
        </div>
        
        <div class="advertencia">
            <strong>3. Verificar permisos:</strong><br>
            Asegúrate de que las carpetas uploads/ y logs/ tengan permisos de escritura
        </div>
        
        <div class="advertencia">
            <strong>4. Verificar configuración de base de datos:</strong><br>
            Revisa que config/database.php tenga las credenciales correctas
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" style="display: inline-block; padding: 12px 24px; background: #6366F1; color: white; text-decoration: none; border-radius: 8px;">
                Intentar Cargar Sistema
            </a>
        </div>
    </div>
</body>
</html>
