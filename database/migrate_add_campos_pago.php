<?php
// Script de migración para agregar nuevos campos al formulario de pago
require_once 'config/database.php';

$conn = getDBConnection();
$mensajes = [];

// Verificar y agregar campo numero_cuenta (número de cuenta bancaria específica)
$result = $conn->query("SHOW COLUMNS FROM pagos LIKE 'numero_cuenta'");
if ($result->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE pagos ADD COLUMN numero_cuenta VARCHAR(50) NULL AFTER cuenta_bancaria");
        $mensajes[] = "✅ Campo 'numero_cuenta' agregado exitosamente.";
    } catch (Exception $e) {
        $mensajes[] = "❌ Error al agregar campo 'numero_cuenta': " . $e->getMessage();
    }
} else {
    $mensajes[] = "ℹ️ El campo 'numero_cuenta' ya existe.";
}

// Verificar y agregar campo es_deposito (si es depósito o transferencia)
$result = $conn->query("SHOW COLUMNS FROM pagos LIKE 'es_deposito'");
if ($result->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE pagos ADD COLUMN es_deposito TINYINT(1) DEFAULT 1 AFTER numero_cuenta");
        $mensajes[] = "✅ Campo 'es_deposito' agregado exitosamente.";
    } catch (Exception $e) {
        $mensajes[] = "❌ Error al agregar campo 'es_deposito': " . $e->getMessage();
    }
} else {
    $mensajes[] = "ℹ️ El campo 'es_deposito' ya existe.";
}

// Verificar y agregar campo numero_comprobante
$result = $conn->query("SHOW COLUMNS FROM pagos LIKE 'numero_comprobante'");
if ($result->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE pagos ADD COLUMN numero_comprobante VARCHAR(100) NULL AFTER es_deposito");
        $mensajes[] = "✅ Campo 'numero_comprobante' agregado exitosamente.";
    } catch (Exception $e) {
        $mensajes[] = "❌ Error al agregar campo 'numero_comprobante': " . $e->getMessage();
    }
} else {
    $mensajes[] = "ℹ️ El campo 'numero_comprobante' ya existe.";
}

// Verificar y agregar campo fecha_transaccion
$result = $conn->query("SHOW COLUMNS FROM pagos LIKE 'fecha_transaccion'");
if ($result->num_rows == 0) {
    try {
        $conn->query("ALTER TABLE pagos ADD COLUMN fecha_transaccion DATE NULL AFTER numero_comprobante");
        $mensajes[] = "✅ Campo 'fecha_transaccion' agregado exitosamente.";
    } catch (Exception $e) {
        $mensajes[] = "❌ Error al agregar campo 'fecha_transaccion': " . $e->getMessage();
    }
} else {
    $mensajes[] = "ℹ️ El campo 'fecha_transaccion' ya existe.";
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
                <h2 style="margin-bottom: 20px; color: var(--dark);">Agregar Campos al Formulario de Pago</h2>
                
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

