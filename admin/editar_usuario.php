<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/validaciones.php';
requireAdmin();

$conn = getDBConnection();
$mensaje = '';

// Obtener ID del usuario
$usuario_id = $_GET['id'] ?? 0;

// Obtener información del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    header('Location: usuarios.php');
    exit();
}

// Procesar actualización del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    $nombre = sanitizarInput($_POST['nombre'] ?? '');
    $email = sanitizarInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $telefono = sanitizarInput($_POST['telefono'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    $errores = [];
    
    $validacion_nombre = validarNombre($nombre);
    if (!$validacion_nombre['valido']) {
        $errores[] = $validacion_nombre['mensaje'];
    }
    
    $validacion_email = validarEmail($email);
    if (!$validacion_email['valido']) {
        $errores[] = $validacion_email['mensaje'];
    } else {
        $validacion_email_unico = validarEmailUnico($conn, $email, $usuario_id);
        if (!$validacion_email_unico['valido']) {
            $errores[] = $validacion_email_unico['mensaje'];
        }
    }
    
    if (!empty($password)) {
        $validacion_password = validarPassword($password, true);
        if (!$validacion_password['valido']) {
            $errores[] = $validacion_password['mensaje'];
        }
    }
    
    $validacion_telefono = validarTelefono($telefono);
    if (!$validacion_telefono['valido']) {
        $errores[] = $validacion_telefono['mensaje'];
    }
    
    if (empty($errores)) {
        // Si se proporcionó una nueva contraseña, actualizarla
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ?, telefono = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $nombre, $email, $password_hash, $telefono, $activo, $usuario_id);
        } else {
            // No actualizar la contraseña si está vacía
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, telefono = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("sssii", $nombre, $email, $telefono, $activo, $usuario_id);
        }
        
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Usuario actualizado exitosamente</div>';
            // Recargar datos
            $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
        } else {
            $mensaje = '<div class="alert alert-error">Error al actualizar usuario: ' . $conn->error . '</div>';
        }
        $stmt->close();
    } else {
        $mensaje = '<div class="alert alert-error"><strong>Errores de validación:</strong><ul style="margin-top: 10px; padding-left: 20px;">';
        foreach ($errores as $error) {
            $mensaje .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $mensaje .= '</ul></div>';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Tiny Steps</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="alternate icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Editar Usuario</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <?php echo $mensaje; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Información del Usuario</h2>
                </div>
                
                <form method="POST" id="formEditarUsuario" onsubmit="return validarFormularioEditarUsuario()">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,100}"
                                   title="Solo letras y espacios, mínimo 2 caracteres"
                                   onblur="validarNombreInput(this)">
                            <small class="error-message" id="error_nombre"></small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required
                                   maxlength="100"
                                   onblur="validarEmailInput(this)">
                            <small class="error-message" id="error_email"></small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Nueva Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="Dejar vacío para mantener la contraseña actual"
                                   minlength="6" maxlength="50"
                                   onblur="validarPasswordInput(this, false)">
                            <small class="error-message" id="error_password"></small>
                            <small style="color: #666; display: block; margin-top: 5px;">Solo completa este campo si deseas cambiar la contraseña (mínimo 6 caracteres)</small>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>"
                                   pattern="[0-9\s\-\(\)]{7,20}"
                                   title="Entre 7 y 15 dígitos"
                                   onblur="validarTelefonoInput(this)">
                            <small class="error-message" id="error_telefono"></small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="activo" value="1" <?php echo $usuario['activo'] ? 'checked' : ''; ?> style="width: auto; margin: 0;">
                            <span>Usuario Activo</span>
                        </label>
                    </div>
                    
                    <div style="background: #E3F2FD; padding: 15px; border-radius: 10px; margin-bottom: 20px; border-left: 4px solid #2196F3;">
                        <strong>Información:</strong>
                        <ul style="margin-top: 10px; padding-left: 20px; line-height: 1.8;">
                            <li><strong>ID:</strong> <?php echo $usuario['id']; ?></li>
                            <li><strong>Tipo:</strong> <?php echo ucfirst($usuario['tipo']); ?></li>
                            <li><strong>Fecha de Registro:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?></li>
                        </ul>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="actualizar_usuario" class="btn btn-primary">Guardar Cambios</button>
                        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/validaciones.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
</body>
</html>

