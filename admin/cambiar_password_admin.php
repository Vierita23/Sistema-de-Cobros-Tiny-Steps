<?php
// Script para cambiar la contraseña del administrador
require_once 'config/database.php';

$mensaje = '';
$nueva_password = '';
$password_cambiada = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $nueva_password = $_POST['nueva_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    if (empty($nueva_password) || empty($confirmar_password)) {
        $mensaje = '<div class="alert alert-error">Por favor, completa ambos campos</div>';
    } elseif ($nueva_password !== $confirmar_password) {
        $mensaje = '<div class="alert alert-error">Las contraseñas no coinciden</div>';
    } elseif (strlen($nueva_password) < 8) {
        $mensaje = '<div class="alert alert-error">La contraseña debe tener al menos 8 caracteres</div>';
    } else {
        $conn = getDBConnection();
        
        // Generar hash de la nueva contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Actualizar la contraseña del admin
        $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = 'admin@tinysteps.com' AND tipo = 'admin'");
        $stmt->bind_param("s", $password_hash);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $mensaje = '<div class="alert alert-success">✅ Contraseña del administrador actualizada exitosamente</div>';
                $password_cambiada = true;
                $nueva_password = ''; // Limpiar para no mostrarla
            } else {
                $mensaje = '<div class="alert alert-error">No se encontró el usuario administrador</div>';
            }
        } else {
            $mensaje = '<div class="alert alert-error">Error al actualizar la contraseña: ' . $conn->error . '</div>';
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Generar una contraseña segura sugerida
function generarPasswordSegura() {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*';
    $password = '';
    for ($i = 0; $i < 12; $i++) {
        $password .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $password;
}

$password_sugerida = generarPasswordSegura();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña Admin | Tiny Steps</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="alternate icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box" style="max-width: 500px;">
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
                <p class="logo-subtitle">Cambiar Contraseña del Administrador</p>
            </div>
            
            <?php echo $mensaje; ?>
            
            <?php if (!$password_cambiada): ?>
                <form method="POST" class="login-form">
                    <div class="form-group">
                        <label for="nueva_password">Nueva Contraseña</label>
                        <input type="password" id="nueva_password" name="nueva_password" required minlength="8" autocomplete="new-password">
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Mínimo 8 caracteres (recomendado: letras, números y símbolos)
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmar_password">Confirmar Contraseña</label>
                        <input type="password" id="confirmar_password" name="confirmar_password" required minlength="8" autocomplete="new-password">
                    </div>
                    
                    <div style="background: #f0f0f0; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--primary);">
                        <strong style="display: block; margin-bottom: 8px; color: var(--dark);">💡 Contraseña Sugerida:</strong>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <code id="password_sugerida" style="flex: 1; padding: 8px; background: white; border-radius: 5px; font-size: 0.9em; font-weight: 600; color: var(--primary);">
                                <?php echo htmlspecialchars($password_sugerida); ?>
                            </code>
                            <button type="button" onclick="copiarPassword()" class="btn btn-secondary" style="padding: 8px 15px; font-size: 0.9em;">
                                Copiar
                            </button>
                            <button type="button" onclick="usarPasswordSugerida()" class="btn btn-primary" style="padding: 8px 15px; font-size: 0.9em;">
                                Usar
                            </button>
                        </div>
                        <small style="color: #666; display: block; margin-top: 8px;">
                            Puedes usar esta contraseña o crear una propia
                        </small>
                    </div>
                    
                    <button type="submit" name="cambiar_password" class="btn btn-primary btn-block">Cambiar Contraseña</button>
                </form>
            <?php else: ?>
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 3em; margin-bottom: 15px;">✅</div>
                    <p style="margin-bottom: 20px; color: var(--dark);">La contraseña ha sido actualizada exitosamente.</p>
                    <a href="index.php" class="btn btn-primary">Volver al Login</a>
                </div>
            <?php endif; ?>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="index.php" style="color: #666; text-decoration: none; font-size: 0.9em;">← Volver al Login</a>
            </div>
        </div>
    </div>
    
    <script>
        function copiarPassword() {
            const password = document.getElementById('password_sugerida').textContent;
            navigator.clipboard.writeText(password).then(function() {
                alert('Contraseña copiada al portapapeles');
            });
        }
        
        function usarPasswordSugerida() {
            const password = document.getElementById('password_sugerida').textContent;
            document.getElementById('nueva_password').value = password;
            document.getElementById('confirmar_password').value = password;
        }
    </script>
</body>
</html>









