<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();
$mensaje = '';

// Obtener ID del pago
$pago_id = $_GET['id'] ?? 0;

// Obtener información del pago
$stmt = $conn->prepare("SELECT p.*, n.nombre as nino_nombre, u.nombre as usuario_nombre, u.email as usuario_email 
                        FROM pagos p 
                        JOIN ninos n ON p.nino_id = n.id 
                        JOIN usuarios u ON p.usuario_id = u.id 
                        WHERE p.id = ?");
$stmt->bind_param("i", $pago_id);
$stmt->execute();
$result = $stmt->get_result();
$pago = $result->fetch_assoc();

if (!$pago) {
    header('Location: pagos.php');
    exit();
}

// Procesar verificación del pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verificar_pago'])) {
    $estado = $_POST['estado'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    $stmt = $conn->prepare("UPDATE pagos SET estado = ?, fecha_verificacion = NOW(), observaciones = ? WHERE id = ?");
    $stmt->bind_param("ssi", $estado, $observaciones, $pago_id);
    
    if ($stmt->execute()) {
        $mensaje = '<div class="alert alert-success">Pago actualizado exitosamente</div>';
        // Recargar datos
        $stmt = $conn->prepare("SELECT p.*, n.nombre as nino_nombre, u.nombre as usuario_nombre, u.email as usuario_email 
                                FROM pagos p 
                                JOIN ninos n ON p.nino_id = n.id 
                                JOIN usuarios u ON p.usuario_id = u.id 
                                WHERE p.id = ?");
        $stmt->bind_param("i", $pago_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pago = $result->fetch_assoc();
    } else {
        $mensaje = '<div class="alert alert-error">Error al actualizar pago: ' . $conn->error . '</div>';
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Pago | Tiny Steps</title>
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
                <h1>Verificar Pago</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <?php echo $mensaje; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Información del Pago</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div>
                        <strong>Niño:</strong> <?php echo htmlspecialchars($pago['nino_nombre']); ?>
                    </div>
                    <div>
                        <strong>Padre/Madre:</strong> <?php echo htmlspecialchars($pago['usuario_nombre']); ?>
                    </div>
                    <div>
                        <strong>Email:</strong> <?php echo htmlspecialchars($pago['usuario_email']); ?>
                    </div>
                    <div>
                        <strong>Monto:</strong> $<?php echo number_format($pago['monto'], 2); ?>
                    </div>
                    <div>
                        <strong>Mes/Año:</strong> <?php echo htmlspecialchars($pago['mes_pago'] . '/' . $pago['anio_pago']); ?>
                    </div>
                    <?php if ($pago['cuenta_bancaria']): ?>
                    <div>
                        <strong>Cuenta Bancaria:</strong> 
                        <span style="padding: 4px 12px; background: #E3F2FD; color: #1976D2; border-radius: 5px; font-weight: 600;">
                            <?php echo htmlspecialchars($pago['cuenta_bancaria'] == 'Pichincha' ? 'Banco del Pichincha' : 'Banco Bolivariano'); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <strong>Estado Actual:</strong> 
                        <span class="status-badge status-<?php echo $pago['estado']; ?>">
                            <?php echo ucfirst($pago['estado']); ?>
                        </span>
                    </div>
                    <div>
                        <strong>Fecha de Subida:</strong> <?php echo date('d/m/Y H:i', strtotime($pago['fecha_subida'])); ?>
                    </div>
                    <?php if ($pago['fecha_verificacion']): ?>
                    <div>
                        <strong>Fecha de Verificación:</strong> <?php echo date('d/m/Y H:i', strtotime($pago['fecha_verificacion'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                    <?php if (isset($pago['motivo_pago']) && $pago['motivo_pago']): ?>
                    <div>
                        <strong>Motivo del Pago:</strong> 
                        <span style="padding: 4px 12px; background: linear-gradient(135deg, rgba(255, 0, 255, 0.1) 0%, rgba(0, 191, 255, 0.1) 100%); color: var(--primary); border-radius: 5px; font-weight: 600;">
                            <?php 
                            $motivos = [
                                'mensualidad' => 'Mensualidad',
                                'atrasos' => 'Atrasos',
                                'horas_adicionales' => 'Horas Adicionales',
                                'otro' => 'Otro'
                            ];
                            echo htmlspecialchars($motivos[$pago['motivo_pago']] ?? ucfirst($pago['motivo_pago'])); 
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($pago['descripcion']): ?>
                <div style="margin-bottom: 30px;">
                    <strong>Descripción:</strong>
                    <div style="padding: 15px; background: var(--light); border-radius: 10px; margin-top: 10px; border-left: 4px solid var(--secondary);">
                        <?php echo nl2br(htmlspecialchars($pago['descripcion'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($pago['comprobante_path']): ?>
                <div style="margin-bottom: 30px;">
                    <strong>Comprobante de Pago:</strong>
                    <div style="margin-top: 15px;">
                        <img src="../<?php echo htmlspecialchars($pago['comprobante_path']); ?>" 
                             alt="Comprobante" 
                             style="max-width: 100%; max-height: 500px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    </div>
                    <div style="margin-top: 15px;">
                        <a href="../<?php echo htmlspecialchars($pago['comprobante_path']); ?>" 
                           target="_blank" 
                           class="btn btn-secondary">Abrir en nueva ventana</a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($pago['observaciones']): ?>
                <div style="margin-bottom: 30px;">
                    <strong>Observaciones:</strong>
                    <p style="padding: 15px; background: var(--light); border-radius: 10px; margin-top: 10px;">
                        <?php echo nl2br(htmlspecialchars($pago['observaciones'])); ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="estado">Cambiar Estado</label>
                            <select id="estado" name="estado" required>
                                <option value="pendiente" <?php echo $pago['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="aceptado" <?php echo $pago['estado'] == 'aceptado' ? 'selected' : ''; ?>>Aceptado</option>
                                <option value="rechazado" <?php echo $pago['estado'] == 'rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observaciones">Observaciones</label>
                        <textarea id="observaciones" name="observaciones" rows="4" placeholder="Agregar observaciones sobre el pago..."><?php echo htmlspecialchars($pago['observaciones'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="verificar_pago" class="btn btn-primary">Actualizar Estado</button>
                        <a href="pagos.php" class="btn btn-secondary">Volver a Pagos</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
</body>
</html>


