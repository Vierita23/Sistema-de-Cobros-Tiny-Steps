<?php
// Script de migración para agregar campos de cuenta bancaria y descripción
require_once 'config/database.php';

$conn = getDBConnection();
$mensajes = [];

// Verificar y agregar campo cuenta_bancaria
$result = $conn->query("SHOW COLUMNS FROM pagos LIKE 'cuenta_bancaria'");
if ($result->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE pagos ADD COLUMN cuenta_bancaria ENUM('Pichincha', 'Bolivariano') NULL AFTER anio_pago");
        $mensajes[] = "✅ Campo 'cuenta_bancaria' agregado exitosamente.";
    } catch (Exception $e) {
        $mensajes[] = "❌ Error al agregar campo 'cuenta_bancaria': " . $e->getMessage();
    }
} else {
    $mensajes[] = "ℹ️ El campo 'cuenta_bancaria' ya existe.";
}

// Verificar y agregar campo descripcion
$result = $conn->query("SHOW COLUMNS FROM pagos LIKE 'descripcion'");
if ($result->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE pagos ADD COLUMN descripcion TEXT NULL AFTER cuenta_bancaria");
        $mensajes[] = "✅ Campo 'descripcion' agregado exitosamente.";
    } catch (Exception $e) {
        $mensajes[] = "❌ Error al agregar campo 'descripcion': " . $e->getMessage();
    }
} else {
    $mensajes[] = "ℹ️ El campo 'descripcion' ya existe.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración de Base de Datos | Tiny Steps</title>
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
                <p class="logo-subtitle">Migración de Base de Datos</p>
            </div>
            
            <div style="margin-top: 30px;">
                <?php foreach ($mensajes as $mensaje): ?>
                    <div class="alert <?php echo strpos($mensaje, '✅') !== false ? 'alert-success' : (strpos($mensaje, '❌') !== false ? 'alert-error' : 'alert-info'); ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 30px; text-align: center;">
                    <a href="index.php" class="btn btn-primary btn-block">Volver al Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

