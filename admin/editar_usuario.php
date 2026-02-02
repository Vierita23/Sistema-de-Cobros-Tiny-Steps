<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();
$mensaje = '';
$error = '';

// Obtener ID del usuario
$usuario_id = $_GET['id'] ?? 0;

if (!$usuario_id) {
    header('Location: usuarios.php');
    exit();
}

// Obtener información del usuario
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header('Location: usuarios.php');
    exit();
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $tipo = $_POST['tipo'] ?? 'padre';
    $telefono = $_POST['telefono'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Si se proporciona una nueva contraseña
    $password = $_POST['password'] ?? '';
    
    if (!empty($nombre) && !empty($email)) {
        if (!empty($password)) {
            // Actualizar con nueva contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, tipo = ?, telefono = ?, activo = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssisi", $nombre, $email, $tipo, $telefono, $activo, $password_hash, $usuario_id);
        } else {
            // Actualizar sin cambiar contraseña
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, tipo = ?, telefono = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $nombre, $email, $tipo, $telefono, $activo, $usuario_id);
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
            $error = 'Error al actualizar usuario: ' . $conn->error;
        }
        $stmt->close();
    } else {
        $error = 'Por favor completa todos los campos obligatorios';
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Tiny Steps</title>
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
            
            <?php if ($mensaje): ?>
                <?php echo $mensaje; ?>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Información del Usuario</h2>
                </div>
                
                <form method="POST" style="padding: 35px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" required 
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo">Tipo de Usuario *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="padre" <?php echo $usuario['tipo'] == 'padre' ? 'selected' : ''; ?>>Padre/Madre</option>
                                <option value="admin" <?php echo $usuario['tipo'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" 
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                        <input type="password" id="password" name="password" 
                               placeholder="Dejar en blanco para mantener la contraseña actual">
                        <small>Si deseas cambiar la contraseña, ingresa una nueva. De lo contrario, déjala en blanco.</small>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="activo" value="1" 
                                   <?php echo $usuario['activo'] ? 'checked' : ''; ?> 
                                   style="width: auto; cursor: pointer;">
                            <span>Usuario Activo</span>
                        </label>
                    </div>
                    
                    <div style="background: var(--light-gray); padding: 20px; border-radius: var(--border-radius); margin: 25px 0;">
                        <strong>Información Adicional:</strong>
                        <div style="margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <strong>ID:</strong> <?php echo $usuario['id']; ?>
                            </div>
                            <div>
                                <strong>Fecha de Registro:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_registro'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="actualizar_usuario" class="btn btn-primary">Actualizar Usuario</button>
                        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
