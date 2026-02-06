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
    } elseif (isset($_POST['eliminar_nino'])) {
        $id = $_POST['id'] ?? 0;
        
        if ($id > 0) {
            // Verificar si el niño tiene pagos asociados
            $stmt_check = $conn->prepare("SELECT COUNT(*) as total FROM pagos WHERE nino_id = ?");
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            $pagos_result = $stmt_check->get_result();
            $pagos_count = $pagos_result->fetch_assoc()['total'];
            $stmt_check->close();
            
            if ($pagos_count > 0) {
                $error = "No se puede eliminar el niño porque tiene $pagos_count pago(s) asociado(s). Primero elimine estos pagos.";
            } else {
                // Iniciar transacción para asegurar integridad
                $conn->begin_transaction();
                
                try {
                    // Eliminar el niño
                    $stmt = $conn->prepare("DELETE FROM ninos WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Error al eliminar niño: ' . $conn->error);
                    }
                    $stmt->close();
                    
                    // Reordenar los IDs de los niños restantes
                    // Desactivar temporalmente las claves foráneas
                    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                    
                    // Obtener todos los niños ordenados por fecha de registro
                    $ninos_restantes = $conn->query("SELECT id FROM ninos ORDER BY fecha_registro ASC");
                    $nuevo_id = 1;
                    $ids_a_actualizar = [];
                    
                    // Identificar qué IDs necesitan actualizarse
                    while ($nino = $ninos_restantes->fetch_assoc()) {
                        $old_id = $nino['id'];
                        if ($old_id != $nuevo_id) {
                            $ids_a_actualizar[$old_id] = $nuevo_id;
                        }
                        $nuevo_id++;
                    }
                    
                    // Ordenar por ID descendente para evitar conflictos al actualizar
                    krsort($ids_a_actualizar);
                    
                    // Actualizar los IDs que necesitan cambio (de mayor a menor para evitar conflictos)
                    foreach ($ids_a_actualizar as $old_id => $new_id) {
                        // Usar un ID temporal alto para evitar conflictos
                        $temp_id = 999999 + $old_id;
                        
                        // Primero mover a ID temporal
                        $stmt_temp = $conn->prepare("UPDATE ninos SET id = ? WHERE id = ?");
                        $stmt_temp->bind_param("ii", $temp_id, $old_id);
                        $stmt_temp->execute();
                        $stmt_temp->close();
                        
                        // Actualizar referencias en pagos
                        $stmt_update_pagos = $conn->prepare("UPDATE pagos SET nino_id = ? WHERE nino_id = ?");
                        $stmt_update_pagos->bind_param("ii", $temp_id, $old_id);
                        $stmt_update_pagos->execute();
                        $stmt_update_pagos->close();
                    }
                    
                    // Ahora actualizar de ID temporal al ID final
                    foreach ($ids_a_actualizar as $old_id => $new_id) {
                        $temp_id = 999999 + $old_id;
                        
                        // Actualizar referencias en pagos al ID final
                        $stmt_update_pagos = $conn->prepare("UPDATE pagos SET nino_id = ? WHERE nino_id = ?");
                        $stmt_update_pagos->bind_param("ii", $new_id, $temp_id);
                        $stmt_update_pagos->execute();
                        $stmt_update_pagos->close();
                        
                        // Actualizar el ID del niño al ID final
                        $stmt_update_nino = $conn->prepare("UPDATE ninos SET id = ? WHERE id = ?");
                        $stmt_update_nino->bind_param("ii", $new_id, $temp_id);
                        $stmt_update_nino->execute();
                        $stmt_update_nino->close();
                    }
                    
                    // Reactivar las claves foráneas
                    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                    
                    // Actualizar el AUTO_INCREMENT para que el próximo ID sea correcto
                    $max_id_result = $conn->query("SELECT COALESCE(MAX(id), 0) as max_id FROM ninos");
                    $max_id = $max_id_result->fetch_assoc()['max_id'];
                    $conn->query("ALTER TABLE ninos AUTO_INCREMENT = " . ($max_id + 1));
                    
                    // Confirmar transacción
                    $conn->commit();
                    
                    $mensaje = '<div class="alert alert-success">Niño eliminado exitosamente. Los IDs han sido reordenados automáticamente.</div>';
                    
                } catch (Exception $e) {
                    // Revertir transacción en caso de error
                    $conn->rollback();
                    $conn->query("SET FOREIGN_KEY_CHECKS = 1"); // Asegurar que se reactive
                    $error = 'Error al eliminar y reordenar: ' . $e->getMessage();
                }
            }
        } else {
            $error = 'ID inválido';
        }
    }
}

// Función para reordenar todos los IDs de niños
function reordenarIdsNinos($conn) {
    $conn->begin_transaction();
    
    try {
        // Desactivar temporalmente las claves foráneas
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Obtener todos los niños ordenados por fecha de registro
        $ninos_restantes = $conn->query("SELECT id FROM ninos ORDER BY fecha_registro ASC");
        $nuevo_id = 1;
        $ids_a_actualizar = [];
        
        // Identificar qué IDs necesitan actualizarse
        while ($nino = $ninos_restantes->fetch_assoc()) {
            $old_id = $nino['id'];
            if ($old_id != $nuevo_id) {
                $ids_a_actualizar[$old_id] = $nuevo_id;
            }
            $nuevo_id++;
        }
        
        // Si hay IDs que actualizar
        if (count($ids_a_actualizar) > 0) {
            // Ordenar por ID descendente para evitar conflictos al actualizar
            krsort($ids_a_actualizar);
            
            // Actualizar los IDs que necesitan cambio (de mayor a menor para evitar conflictos)
            foreach ($ids_a_actualizar as $old_id => $new_id) {
                // Usar un ID temporal alto para evitar conflictos
                $temp_id = 999999 + $old_id;
                
                // Primero mover a ID temporal
                $stmt_temp = $conn->prepare("UPDATE ninos SET id = ? WHERE id = ?");
                $stmt_temp->bind_param("ii", $temp_id, $old_id);
                $stmt_temp->execute();
                $stmt_temp->close();
                
                // Actualizar referencias en pagos
                $stmt_update_pagos = $conn->prepare("UPDATE pagos SET nino_id = ? WHERE nino_id = ?");
                $stmt_update_pagos->bind_param("ii", $temp_id, $old_id);
                $stmt_update_pagos->execute();
                $stmt_update_pagos->close();
            }
            
            // Ahora actualizar de ID temporal al ID final
            foreach ($ids_a_actualizar as $old_id => $new_id) {
                $temp_id = 999999 + $old_id;
                
                // Actualizar referencias en pagos al ID final
                $stmt_update_pagos = $conn->prepare("UPDATE pagos SET nino_id = ? WHERE nino_id = ?");
                $stmt_update_pagos->bind_param("ii", $new_id, $temp_id);
                $stmt_update_pagos->execute();
                $stmt_update_pagos->close();
                
                // Actualizar el ID del niño al ID final
                $stmt_update_nino = $conn->prepare("UPDATE ninos SET id = ? WHERE id = ?");
                $stmt_update_nino->bind_param("ii", $new_id, $temp_id);
                $stmt_update_nino->execute();
                $stmt_update_nino->close();
            }
        }
        
        // Reactivar las claves foráneas
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        // Actualizar el AUTO_INCREMENT para que el próximo ID sea correcto
        $max_id_result = $conn->query("SELECT COALESCE(MAX(id), 0) as max_id FROM ninos");
        $max_id = $max_id_result->fetch_assoc()['max_id'];
        $conn->query("ALTER TABLE ninos AUTO_INCREMENT = " . ($max_id + 1));
        
        // Confirmar transacción
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        return false;
    }
}

// Verificar si los IDs están desordenados y reordenarlos automáticamente
$primer_id_check = $conn->query("SELECT MIN(id) as min_id FROM ninos");
$primer_id_check_result = $primer_id_check->fetch_assoc();
$min_id_actual = $primer_id_check_result['min_id'] ?? 0;

// Si el primer ID no es 1 y hay niños, reordenar automáticamente
if ($min_id_actual > 1 && $min_id_actual > 0) {
    if (reordenarIdsNinos($conn)) {
        $mensaje = '<div class="alert alert-success">IDs reordenados automáticamente. Ahora empiezan desde el número 1.</div>';
    } else {
        $error = 'Error al reordenar los IDs automáticamente.';
    }
}

// También permitir reordenamiento manual
if (isset($_GET['reordenar_ids']) && $_GET['reordenar_ids'] == '1') {
    if (reordenarIdsNinos($conn)) {
        $mensaje = '<div class="alert alert-success">IDs reordenados exitosamente. Ahora empiezan desde el número 1.</div>';
    } else {
        $error = 'Error al reordenar los IDs.';
    }
}

// Obtener todos los niños con información del usuario
$ninos = $conn->query("SELECT n.*, u.nombre as usuario_nombre, u.email as usuario_email 
                      FROM ninos n 
                      JOIN usuarios u ON n.usuario_id = u.id 
                      ORDER BY n.fecha_registro DESC");

// Obtener el total de niños activos
$total_ninos_query = $conn->query("SELECT COUNT(*) as total FROM ninos WHERE activo = 1");
$total_ninos = $total_ninos_query->fetch_assoc()['total'];

// Verificar si los IDs están desordenados (si el primer ID no es 1)
$primer_id_query = $conn->query("SELECT MIN(id) as min_id FROM ninos");
$primer_id = $primer_id_query->fetch_assoc()['min_id'];
$ids_desordenados = ($primer_id > 1);

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
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <h2>Lista de Niños</h2>
                    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 1.1em;">
                            Total: <?php echo $total_ninos; ?> niño(s) activo(s)
                        </div>
                        <?php if ($ids_desordenados): ?>
                            <a href="?reordenar_ids=1" class="btn btn-primary" onclick="return confirm('¿Deseas reordenar todos los IDs para que empiecen desde el número 1? Esto actualizará todas las referencias.');" style="padding: 8px 16px; font-size: 0.9em;">
                                🔄 Reordenar IDs (Empezar desde 1)
                            </a>
                        <?php endif; ?>
                    </div>
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
                                            <form method="POST" style="display: inline-block; margin-left: 5px;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este niño? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="id" value="<?php echo $nino['id']; ?>">
                                                <button type="submit" name="eliminar_nino" class="btn btn-sm btn-danger">Eliminar</button>
                                            </form>
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
