<?php
// Script de migración para agregar campo de motivo de pago
require_once 'config/database.php';

$conn = getDBConnection();
$mensajes = [];

// Verificar y agregar campo motivo_pago
$result = $conn->query("SHOW COLUMNS FROM pagos LIKE 'motivo_pago'");
if ($result->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE pagos ADD COLUMN motivo_pago ENUM('mensualidad', 'atrasos', 'horas_adicionales', 'otro') NULL AFTER descripcion");
        $mensajes[] = "✅ Campo 'motivo_pago' agregado exitosamente.";
    } catch (Exception $e) {
        $mensajes[] = "❌ Error al agregar campo 'motivo_pago': " . $e->getMessage();
    }
} else {
    $mensajes[] = "ℹ️ El campo 'motivo_pago' ya existe.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración - Tiny Steps</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="alternate icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box" style="max-width: 600px;">
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
                <p class="logo-subtitle">Migración de Base de Datos</p>
            </div>
            
            <div style="margin-top: 30px;">
                <h2 style="margin-bottom: 20px; color: var(--dark);">Agregar Campo de Motivo de Pago</h2>
                
                <?php foreach ($mensajes as $mensaje): ?>
                    <div class="alert <?php echo strpos($mensaje, '✅') !== false ? 'alert-success' : (strpos($mensaje, '❌') !== false ? 'alert-error' : 'alert-info'); ?>" style="margin-bottom: 10px;">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 30px; text-align: center;">
                    <a href="index.php" class="btn btn-primary">Volver al Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>









