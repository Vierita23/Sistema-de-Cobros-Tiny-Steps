<?php
/**
 * Script para solucionar el Error 500
 */

// Renombrar .htaccess si existe
if (file_exists('.htaccess')) {
    if (rename('.htaccess', '.htaccess.backup')) {
        $mensaje = "✅ Archivo .htaccess renombrado a .htaccess.backup";
        $accion = "renombrado";
    } else {
        $mensaje = "❌ No se pudo renombrar .htaccess. Hazlo manualmente.";
        $accion = "error";
    }
} else {
    $mensaje = "ℹ️ No se encontró archivo .htaccess";
    $accion = "no_existe";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solución Error 500 - Tiny Steps</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 600px;
        }
        .exito {
            background: #D1FAE5;
            border-left: 4px solid #10B981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        .error {
            background: #FEE2E2;
            border-left: 4px solid #EF4444;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        .info {
            background: #DBEAFE;
            border-left: 4px solid #3B82F6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #6366F1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
        }
        .btn:hover {
            background: #4F46E5;
        }
        .codigo {
            background: #1F2937;
            color: #10B981;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Solución de Error 500</h1>
        
        <?php if ($accion === "renombrado"): ?>
            <div class="exito">
                <strong><?php echo $mensaje; ?></strong><br><br>
                El archivo .htaccess puede estar causando el error. Ahora intenta acceder al sistema nuevamente.
            </div>
        <?php elseif ($accion === "error"): ?>
            <div class="error">
                <strong><?php echo $mensaje; ?></strong><br><br>
                <strong>Hazlo manualmente:</strong><br>
                1. Ve a la carpeta del proyecto<br>
                2. Renombra .htaccess a .htaccess.backup<br>
                3. Recarga la página
            </div>
        <?php else: ?>
            <div class="info">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <strong>Pasos siguientes:</strong>
            <ol style="text-align: left;">
                <li>Intenta acceder al sistema ahora</li>
                <li>Si aún hay error, revisa el log de Apache:
                    <div class="codigo">C:\xampp\apache\logs\error.log</div>
                </li>
                <li>Verifica que XAMPP esté corriendo correctamente</li>
            </ol>
        </div>
        
        <div style="margin-top: 30px;">
            <a href="index.php" class="btn">Intentar Acceder al Sistema</a>
            <a href="diagnostico_error.php" class="btn">Ver Diagnóstico Completo</a>
        </div>
        
        <div class="info" style="margin-top: 30px;">
            <strong>💡 Si el problema persiste:</strong><br>
            <ul style="text-align: left;">
                <li>Reinicia Apache en XAMPP</li>
                <li>Verifica que PHP esté funcionando: <a href="phpinfo.php" target="_blank">phpinfo.php</a></li>
                <li>Revisa los logs de error de Apache</li>
            </ul>
        </div>
    </div>
</body>
</html>
