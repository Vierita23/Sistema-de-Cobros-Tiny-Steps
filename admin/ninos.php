<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();
$mensaje = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_nino'])) {
        $nombre = $_POST['nombre'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
        $usuario_id = $_POST['usuario_id'] ?? 0;
        
        if (!empty($nombre) && !empty($usuario_id)) {
            $stmt = $conn->prepare("INSERT INTO ninos (nombre, fecha_nacimiento, usuario_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $nombre, $fecha_nacimiento, $usuario_id);
            
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success">Niño registrado exitosamente</div>';
            } else {
                $error = 'Error al registrar niño: ' . $conn->error;
            }
            $stmt->close();
        } else {
            $error = 'Por favor completa todos los campos obligatorios';
        }
    } elseif (isset($_POST['actualizar_nino'])) {
        $id = $_POST['id'] ?? 0;
        $nombre = $_POST['nombre'] ?? '';
        $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
        $usuario_id = $_POST['usuario_id'] ?? 0;
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        if (!empty($nombre) && !empty($usuario_id)) {
            $stmt = $conn->prepare("UPDATE ninos SET nombre = ?, fecha_nacimiento = ?, usuario_id = ?, activo = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $nombre, $fecha_nacimiento, $usuario_id, $activo, $id);
            
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success">Niño actualizado exitosamente</div>';
            } else {
                $error = 'Error al actualizar niño: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

// Obtener todos los niños con información del usuario
$ninos = $conn->query("SELECT n.*, u.nombre as usuario_nombre, u.email as usuario_email 
                      FROM ninos n 
                      JOIN usuarios u ON n.usuario_id = u.id 
                      ORDER BY n.fecha_registro DESC");

// Obtener usuarios para el select
$usuarios = $conn->query("SELECT id, nombre, email FROM usuarios WHERE tipo = 'padre' AND activo = 1 ORDER BY nombre");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Niños - Tiny Steps</title>
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
                <h1>Gestión de Niños</h1>
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
            
            <!-- Formulario para crear niño -->
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2>Registrar Nuevo Niño</h2>
                </div>
                <form method="POST" style="padding: 30px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Niño *</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="usuario_id">Padre/Madre *</label>
                        <select id="usuario_id" name="usuario_id" required>
                            <option value="">Seleccione un padre/madre</option>
                            <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                                <option value="<?php echo $usuario['id']; ?>">
                                    <?php echo htmlspecialchars($usuario['nombre'] . ' (' . $usuario['email'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="crear_nino" class="btn btn-primary">Registrar Niño</button>
                    </div>
                </form>
            </div>
            
            <!-- Lista de niños -->
            <div class="card">
                <div class="card-header">
                    <h2>Lista de Niños</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Padre/Madre</th>
                                <th>Email</th>
                                <th>Fecha Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Resetear el resultado para poder iterarlo de nuevo
                            $ninos->data_seek(0);
                            if ($ninos->num_rows > 0): ?>
                                <?php while ($nino = $ninos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $nino['id']; ?></td>
                                        <td><?php echo htmlspecialchars($nino['nombre']); ?></td>
                                        <td><?php echo $nino['fecha_nacimiento'] ? date('d/m/Y', strtotime($nino['fecha_nacimiento'])) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($nino['usuario_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($nino['usuario_email']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($nino['fecha_registro'])); ?></td>
                                        <td>
                                            <span style="padding: 4px 8px; background: <?php echo $nino['activo'] ? '#C8E6C9' : '#FFCDD2'; ?>; color: <?php echo $nino['activo'] ? '#2E7D32' : '#C62828'; ?>; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                <?php echo $nino['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="pagos_nino.php?id=<?php echo $nino['id']; ?>" class="btn btn-sm btn-secondary">Ver Pagos</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="empty-state">No hay niños registrados</td>
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
