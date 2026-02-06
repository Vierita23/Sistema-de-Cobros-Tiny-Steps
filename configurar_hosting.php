<?php
/**
 * Script de Configuración para Hosting
 * Ejecuta este archivo DESPUÉS de subir al hosting
 * para verificar que todo esté configurado correctamente
 */

// Verificar si estamos en localhost o en hosting
$es_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                 strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

if ($es_localhost) {
    die("Este script solo debe ejecutarse en el hosting, no en localhost.");
}

$errores = [];
$exitos = [];
$advertencias = [];

// Verificar archivos
$archivos_requeridos = [
    'index.php' => 'Archivo principal',
    'config/database.php' => 'Configuración de base de datos',
    'config/session.php' => 'Configuración de sesión',
    'admin/dashboard.php' => 'Dashboard de administrador',
    'user/dashboard.php' => 'Dashboard de usuario'
];

foreach ($archivos_requeridos as $archivo => $nombre) {
    if (file_exists($archivo)) {
        $exitos[] = "$nombre - Existe";
    } else {
        $errores[] = "$nombre - NO encontrado ($archivo)";
    }
}

// Verificar conexión a base de datos
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        $conn = getDBConnection();
        $exitos[] = "Conexión a base de datos - Exitosa";
        
        // Verificar tablas
        $tablas_requeridas = ['usuarios', 'ninos', 'pagos'];
        $result = $conn->query("SHOW TABLES");
        $tablas_existentes = [];
        while ($row = $result->fetch_array()) {
            $tablas_existentes[] = $row[0];
        }
        
        foreach ($tablas_requeridas as $tabla) {
            if (in_array($tabla, $tablas_existentes)) {
                $exitos[] = "Tabla '$tabla' - Existe";
            } else {
                $errores[] = "Tabla '$tabla' - NO existe (necesitas importar el backup)";
            }
        }
        
        $conn->close();
    } catch (Exception $e) {
        $errores[] = "Error de conexión: " . $e->getMessage();
    }
} else {
    $errores[] = "config/database.php no existe - Necesitas crearlo";
}

// Verificar permisos
$carpetas = ['uploads/comprobantes', 'logs'];
foreach ($carpetas as $carpeta) {
    if (is_dir($carpeta)) {
        if (is_writable($carpeta)) {
            $exitos[] = "Permisos de $carpeta - OK";
        } else {
            $errores[] = "Sin permisos de escritura en $carpeta (configura permisos 755 o 777)";
        }
    } else {
        $advertencias[] = "$carpeta - No existe (se creará automáticamente)";
    }
}

// Verificar PHP
$php_version = phpversion();
if (version_compare($php_version, '7.4.0', '>=')) {
    $exitos[] = "PHP $php_version - Versión correcta";
} else {
    $advertencias[] = "PHP $php_version - Se recomienda PHP 7.4 o superior";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Hosting - Tiny Steps</title>
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
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .exito {
            background: #D1FAE5;
            border-left: 4px solid #10B981;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .advertencia {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .error {
            background: #FEE2E2;
            border-left: 4px solid #EF4444;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .resumen {
            margin-top: 30px;
            padding: 20px;
            background: #F3F4F6;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #6366F1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Verificación de Configuración del Hosting</h1>
        
        <?php if (!empty($exitos)): ?>
            <h2 style="color: #10B981;">✓ Verificaciones Exitosas (<?php echo count($exitos); ?>)</h2>
            <?php foreach ($exitos as $exito): ?>
                <div class="exito"><?php echo htmlspecialchars($exito); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($advertencias)): ?>
            <h2 style="color: #F59E0B;">⚠ Advertencias (<?php echo count($advertencias); ?>)</h2>
            <?php foreach ($advertencias as $advertencia): ?>
                <div class="advertencia"><?php echo htmlspecialchars($advertencia); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($errores)): ?>
            <h2 style="color: #EF4444;">✗ Errores que Deben Corregirse (<?php echo count($errores); ?>)</h2>
            <?php foreach ($errores as $error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="resumen">
            <h3>Resumen</h3>
            <p><strong>Total de verificaciones:</strong> <?php echo count($exitos) + count($advertencias) + count($errores); ?></p>
            <p><strong>Éxitos:</strong> <?php echo count($exitos); ?></p>
            <p><strong>Advertencias:</strong> <?php echo count($advertencias); ?></p>
            <p><strong>Errores:</strong> <?php echo count($errores); ?></p>
            
            <?php if (empty($errores)): ?>
                <p style="color: #10B981; font-weight: bold; margin-top: 15px;">
                    ✅ El sistema está configurado correctamente. Puedes eliminar este archivo por seguridad.
                </p>
            <?php else: ?>
                <p style="color: #EF4444; font-weight: bold; margin-top: 15px;">
                    ❌ Hay errores que deben corregirse antes de usar el sistema.
                </p>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" class="btn">Ir al Sistema</a>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #FEF3C7; border-radius: 5px;">
            <strong>⚠️ IMPORTANTE:</strong> Elimina este archivo (configurar_hosting.php) después de verificar por seguridad.
        </div>
    </div>
</body>
</html>
