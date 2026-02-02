<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Obtener todos los pagos
$query = "SELECT p.*, n.nombre as nino_nombre, u.nombre as usuario_nombre, u.email as usuario_email 
          FROM pagos p 
          JOIN ninos n ON p.nino_id = n.id 
          JOIN usuarios u ON p.usuario_id = u.id 
          ORDER BY p.fecha_subida DESC";
$pagos = $conn->query($query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos - Tiny Steps</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Gestión de Pagos</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Todos los Pagos</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha Subida</th>
                                <th>Niño</th>
                                <th>Padre</th>
                                <th>Monto</th>
                                <th>Mes/Año</th>
                                <th>Cuenta Bancaria</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pagos->num_rows > 0): ?>
                                <?php while ($pago = $pagos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_subida'])); ?></td>
                                        <td><?php echo htmlspecialchars($pago['nino_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($pago['usuario_nombre']); ?></td>
                                        <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($pago['mes_pago'] . '/' . $pago['anio_pago']); ?></td>
                                        <td>
                                            <?php if ($pago['cuenta_bancaria']): ?>
                                                <span style="padding: 4px 8px; background: #E3F2FD; color: #1976D2; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                    <?php echo htmlspecialchars($pago['cuenta_bancaria'] == 'Pichincha' ? 'Pichincha' : 'Bolivariano'); ?>
                                                </span>
                                            <?php else: ?>
                                                <span>-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $pago['estado']; ?>">
                                                <?php echo ucfirst($pago['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($pago['comprobante_path']): ?>
                                                <a href="../<?php echo htmlspecialchars($pago['comprobante_path']); ?>" target="_blank" class="btn btn-sm btn-secondary">Ver</a>
                                            <?php else: ?>
                                                <span>-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="ver_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-primary">Verificar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="empty-state">No hay pagos registrados</td>
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


