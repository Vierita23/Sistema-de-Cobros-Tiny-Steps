<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/validaciones.php';
requireAdmin();

$conn = getDBConnection();
$mensaje = '';

// Eliminar usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_usuario'])) {
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    
    if ($usuario_id > 0) {
        // Verificar que no sea el usuario actual
        if ($usuario_id == $_SESSION['user_id']) {
            $mensaje = '<div class="alert alert-error">No puedes eliminar tu propio usuario</div>';
        } else {
            // Verificar que no sea un admin
            $stmt = $conn->prepare("SELECT tipo FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
            $stmt->close();
            
            if ($usuario && $usuario['tipo'] === 'admin') {
                $mensaje = '<div class="alert alert-error">No se pueden eliminar usuarios administradores</div>';
            } else {
                // Marcar como inactivo (soft delete)
                $stmt = $conn->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
                $stmt->bind_param("i", $usuario_id);
                
                if ($stmt->execute()) {
                    $mensaje = '<div class="alert alert-success">Usuario eliminado exitosamente</div>';
                } else {
                    $mensaje = '<div class="alert alert-error">Error al eliminar usuario: ' . $conn->error . '</div>';
                }
                $stmt->close();
            }
        }
    }
}

// Crear nuevo usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $nombre = sanitizarInput($_POST['nombre'] ?? '');
    $email = sanitizarInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $telefono = sanitizarInput($_POST['telefono'] ?? '');
    
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
        $validacion_email_unico = validarEmailUnico($conn, $email);
        if (!$validacion_email_unico['valido']) {
            $errores[] = $validacion_email_unico['mensaje'];
        }
    }
    
    $validacion_password = validarPassword($password, true);
    if (!$validacion_password['valido']) {
        $errores[] = $validacion_password['mensaje'];
    }
    
    $validacion_telefono = validarTelefono($telefono);
    if (!$validacion_telefono['valido']) {
        $errores[] = $validacion_telefono['mensaje'];
    }
    
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, telefono, tipo) VALUES (?, ?, ?, ?, 'padre')");
        $stmt->bind_param("ssss", $nombre, $email, $password_hash, $telefono);
        
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Usuario creado exitosamente</div>';
            // Limpiar formulario
            $_POST = array();
        } else {
            $mensaje = '<div class="alert alert-error">Error al crear usuario: ' . $conn->error . '</div>';
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

// Obtener usuarios
$usuarios = $conn->query("SELECT u.*, COUNT(n.id) as total_ninos 
                          FROM usuarios u 
                          LEFT JOIN ninos n ON u.id = n.usuario_id AND n.activo = 1
                          WHERE u.tipo = 'padre' 
                          GROUP BY u.id 
                          ORDER BY u.fecha_registro DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Tiny Steps</title>
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
                <h1>Gestión de Usuarios</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <?php echo $mensaje; ?>
            
            <!-- Formulario para crear usuario -->
            <div class="card">
                <div class="card-header">
                    <h2>Crear Nuevo Usuario (Padre)</h2>
                </div>
                <form method="POST" id="formCrearUsuario" onsubmit="return validarFormularioCrearUsuario()">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo</label>
                            <input type="text" id="nombre" name="nombre" required 
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,100}" 
                                   title="Solo letras y espacios, mínimo 2 caracteres"
                                   onblur="validarNombreInput(this)">
                            <small class="error-message" id="error_nombre"></small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required 
                                   maxlength="100"
                                   onblur="validarEmailInput(this)">
                            <small class="error-message" id="error_email"></small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" required 
                                   minlength="6" maxlength="50"
                                   onblur="validarPasswordInput(this)">
                            <small class="error-message" id="error_password"></small>
                            <small style="color: #666; display: block; margin-top: 5px;">Mínimo 6 caracteres</small>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" 
                                   pattern="[0-9\s\-\(\)]{7,20}"
                                   title="Entre 7 y 15 dígitos"
                                   onblur="validarTelefonoInput(this)">
                            <small class="error-message" id="error_telefono"></small>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
            
            <!-- Lista de usuarios -->
            <div class="card">
                <div class="card-header">
                    <h2>Lista de Usuarios</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Niños Registrados</th>
                                <th>Fecha Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($usuarios->num_rows > 0): ?>
                                <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['telefono'] ?? '-'); ?></td>
                                        <td><?php echo $usuario['total_ninos']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $usuario['activo'] ? 'status-aceptado' : 'status-rechazado'; ?>">
                                                <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85em;">
                                                    ✏️ Editar
                                                </a>
                                                <?php if ($usuario['id'] != $_SESSION['user_id'] && $usuario['tipo'] != 'admin'): ?>
                                                <form method="POST" style="display: inline;" onsubmit="return confirmarEliminacion('<?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES); ?>')">
                                                    <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                    <button type="submit" name="eliminar_usuario" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85em;">
                                                        🗑️ Eliminar
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">No hay usuarios registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/validaciones.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
    <script>
        function confirmarEliminacion(nombre) {
            return confirm('¿Estás seguro de que deseas eliminar al usuario "' + nombre + '"?\n\nEsta acción marcará al usuario como inactivo y no podrá iniciar sesión.');
        }
    </script>
</body>
</html>


