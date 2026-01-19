<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/validaciones.php';
requireAdmin();

$conn = getDBConnection();
$mensaje = '';

// Eliminar niño
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_nino'])) {
    $nino_id = intval($_POST['nino_id'] ?? 0);
    
    if ($nino_id > 0) {
        // Marcar como inactivo (soft delete)
        $stmt = $conn->prepare("UPDATE ninos SET activo = 0 WHERE id = ?");
        $stmt->bind_param("i", $nino_id);
        
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Niño eliminado exitosamente</div>';
        } else {
            $mensaje = '<div class="alert alert-error">Error al eliminar niño: ' . $conn->error . '</div>';
        }
        $stmt->close();
    }
}

// Crear nuevo niño
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_nino'])) {
    $nombre = sanitizarInput($_POST['nombre'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $usuario_id = intval($_POST['usuario_id'] ?? 0);
    
    // Validaciones
    $errores = [];
    
    $validacion_nombre = validarNombre($nombre);
    if (!$validacion_nombre['valido']) {
        $errores[] = $validacion_nombre['mensaje'];
    }
    
    $validacion_fecha = validarFecha($fecha_nacimiento, 'fecha de nacimiento');
    if (!$validacion_fecha['valido']) {
        $errores[] = $validacion_fecha['mensaje'];
    }
    
    if (empty($usuario_id) || $usuario_id <= 0) {
        $errores[] = 'Debe seleccionar un padre/madre';
    }
    
    if (empty($errores)) {
        $stmt = $conn->prepare("INSERT INTO ninos (nombre, fecha_nacimiento, usuario_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nombre, $fecha_nacimiento, $usuario_id);
        
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Niño registrado exitosamente</div>';
            // Limpiar formulario
            $_POST = array();
        } else {
            $mensaje = '<div class="alert alert-error">Error al registrar niño: ' . $conn->error . '</div>';
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

// Obtener usuarios padres
$usuarios = $conn->query("SELECT id, nombre, email FROM usuarios WHERE tipo = 'padre' AND activo = 1 ORDER BY nombre");

// Obtener niños
$ninos = $conn->query("SELECT n.*, u.nombre as padre_nombre, u.email as padre_email 
                        FROM ninos n 
                        JOIN usuarios u ON n.usuario_id = u.id 
                        WHERE n.activo = 1
                        ORDER BY n.fecha_registro DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Niños | Tiny Steps</title>
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
            
            <?php echo $mensaje; ?>
            
            <!-- Formulario para crear niño -->
            <div class="card">
                <div class="card-header">
                    <h2>Registrar Nuevo Niño</h2>
                </div>
                <form method="POST" id="formCrearNino" onsubmit="return validarFormularioCrearNino()">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre del Niño</label>
                            <input type="text" id="nombre" name="nombre" required
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,100}"
                                   title="Solo letras y espacios, mínimo 2 caracteres"
                                   onblur="validarNombreInput(this)">
                            <small class="error-message" id="error_nombre"></small>
                        </div>
                        <div class="form-group">
                            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                                   max="<?php echo date('Y-m-d'); ?>">
                            <small class="error-message" id="error_fecha_nacimiento"></small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_id">Padre/Madre</label>
                            <select id="usuario_id" name="usuario_id" required>
                                <option value="">Seleccionar...</option>
                                <?php 
                                $usuarios->data_seek(0);
                                while ($usuario = $usuarios->fetch_assoc()): ?>
                                    <option value="<?php echo $usuario['id']; ?>">
                                        <?php echo htmlspecialchars($usuario['nombre'] . ' (' . $usuario['email'] . ')'); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                            <small class="error-message" id="error_usuario_id"></small>
                        </div>
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
                                <th>Email del Padre</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $usuarios->data_seek(0); // Reset pointer
                            if ($ninos->num_rows > 0): ?>
                                <?php while ($nino = $ninos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $nino['id']; ?></td>
                                        <td><?php echo htmlspecialchars($nino['nombre']); ?></td>
                                        <td><?php echo $nino['fecha_nacimiento'] ? date('d/m/Y', strtotime($nino['fecha_nacimiento'])) : '-'; ?></td>
                                        <td><?php echo htmlspecialchars($nino['padre_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($nino['padre_email']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($nino['fecha_registro'])); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                <a href="pagos_nino.php?id=<?php echo $nino['id']; ?>" class="btn btn-sm btn-primary">Ver Pagos</a>
                                                <form method="POST" style="display: inline;" onsubmit="return confirmarEliminacionNino('<?php echo htmlspecialchars($nino['nombre'], ENT_QUOTES); ?>')">
                                                    <input type="hidden" name="nino_id" value="<?php echo $nino['id']; ?>">
                                                    <button type="submit" name="eliminar_nino" class="btn btn-sm btn-danger">
                                                        🗑️ Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No hay niños registrados</td>
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
        function validarFormularioCrearNino() {
            let valido = true;
            
            valido = validarNombreInput(document.getElementById('nombre')) && valido;
            
            const fechaInput = document.getElementById('fecha_nacimiento');
            if (!fechaInput || fechaInput.value === '') {
                mostrarError('fecha_nacimiento', 'La fecha de nacimiento es requerida');
                valido = false;
            } else {
                limpiarError('fecha_nacimiento');
            }
            
            const usuarioSelect = document.getElementById('usuario_id');
            if (!usuarioSelect || usuarioSelect.value === '') {
                mostrarError('usuario_id', 'Debe seleccionar un padre/madre');
                valido = false;
            } else {
                limpiarError('usuario_id');
            }
            
            return valido;
        }
        
        function confirmarEliminacionNino(nombre) {
            return confirm('¿Estás seguro de que deseas eliminar al niño "' + nombre + '"?\n\nEsta acción marcará al niño como inactivo.');
        }
    </script>
</body>
</html>


