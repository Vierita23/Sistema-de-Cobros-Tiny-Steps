<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();
$mensaje = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_usuario'])) {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $tipo = $_POST['tipo'] ?? 'padre';
        $telefono = $_POST['telefono'] ?? '';
        
        if (!empty($nombre) && !empty($email) && !empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, tipo, telefono) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $email, $password_hash, $tipo, $telefono);
            
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success">Usuario creado exitosamente</div>';
            } else {
                $error = 'Error al crear usuario: ' . $conn->error;
            }
            $stmt->close();
        } else {
            $error = 'Por favor completa todos los campos obligatorios';
        }
    } elseif (isset($_POST['actualizar_usuario'])) {
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $tipo = $_POST['tipo'] ?? 'padre';
        $telefono = $_POST['telefono'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if (!empty($nombre) && !empty($email)) {
            $stmt = $conn->prepare("UPDATE usuarios SET nombre = ?, email = ?, tipo = ?, telefono = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $nombre, $email, $tipo, $telefono, $activo, $id);
            
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success">Usuario actualizado exitosamente</div>';
            } else {
                $error = 'Error al actualizar usuario: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Obtener todos los usuarios
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Tiny Steps</title>
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
            
            <?php if ($mensaje): ?>
                <?php echo $mensaje; ?>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Formulario para crear usuario -->
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2>Crear Nuevo Usuario</h2>
                </div>
                <form method="POST" style="padding: 30px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre *</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password">Contraseña *</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo de Usuario *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="padre">Padre/Madre</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="text" id="telefono" name="telefono">
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
                                <th>Tipo</th>
                                <th>Teléfono</th>
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
                                        <td>
                                            <span style="padding: 4px 8px; background: <?php echo $usuario['tipo'] == 'admin' ? '#FFE0B2' : '#E1BEE7'; ?>; color: <?php echo $usuario['tipo'] == 'admin' ? '#E65100' : '#4A148C'; ?>; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                <?php echo ucfirst($usuario['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($usuario['telefono'] ?? '-'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                        <td>
                                            <span style="padding: 4px 8px; background: <?php echo $usuario['activo'] ? '#C8E6C9' : '#FFCDD2'; ?>; color: <?php echo $usuario['activo'] ? '#2E7D32' : '#C62828'; ?>; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
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
</body>
</html>
