<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Obtener estadísticas
$stats = [
    'total_usuarios' => 0,
    'total_ninos' => 0,
    'pagos_pendientes' => 0,
    'pagos_aceptados' => 0
];

$result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE tipo = 'padre' AND activo = 1");
$stats['total_usuarios'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM ninos WHERE activo = 1");
$stats['total_ninos'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM pagos WHERE estado = 'pendiente'");
$stats['pagos_pendientes'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM pagos WHERE estado = 'aceptado'");
$stats['pagos_aceptados'] = $result->fetch_assoc()['total'];

// Obtener últimos pagos
$query = "SELECT p.*, n.nombre as nino_nombre, u.nombre as usuario_nombre 
          FROM pagos p 
          JOIN ninos n ON p.nino_id = n.id 
          JOIN usuarios u ON p.usuario_id = u.id 
          ORDER BY p.fecha_subida DESC 
          LIMIT 10";
$ultimos_pagos = $conn->query($query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración | Tiny Steps</title>
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
                <h1>Panel de Administración</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; margin-bottom: 35px;">
                <div class="stat-card" style="animation-delay: 0.1s;">
                    <h3>Total Usuarios</h3>
                    <div class="stat-number"><?php echo $stats['total_usuarios']; ?></div>
                </div>
                <div class="stat-card" style="animation-delay: 0.2s;">
                    <h3>Total Niños</h3>
                    <div class="stat-number"><?php echo $stats['total_ninos']; ?></div>
                </div>
                <div class="stat-card" style="animation-delay: 0.3s;">
                    <h3>Pagos Pendientes</h3>
                    <div class="stat-number" style="background: linear-gradient(135deg, #FCE38A 0%, #F9D371 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?php echo $stats['pagos_pendientes']; ?></div>
                </div>
                <div class="stat-card" style="animation-delay: 0.4s;">
                    <h3>Pagos Aceptados</h3>
                    <div class="stat-number" style="background: linear-gradient(135deg, #95E1D3 0%, #7dd3c0 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?php echo $stats['pagos_aceptados']; ?></div>
                </div>
            </div>
            
            <!-- Últimos Pagos -->
            <div class="card">
                <div class="card-header">
                    <h2>Últimos Pagos</h2>
                    <a href="pagos.php" class="btn btn-primary btn-sm">Ver Todos</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Niño</th>
                                <th>Padre</th>
                                <th>Monto</th>
                                <th>Mes/Año</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($ultimos_pagos->num_rows > 0): ?>
                                <?php while ($pago = $ultimos_pagos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($pago['fecha_subida'])); ?></td>
                                        <td><?php echo htmlspecialchars($pago['nino_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($pago['usuario_nombre']); ?></td>
                                        <td>$<?php echo number_format($pago['monto'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($pago['mes_pago'] . '/' . $pago['anio_pago']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $pago['estado']; ?>">
                                                <?php echo ucfirst($pago['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="ver_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-secondary">Ver</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="empty-state">No hay pagos registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
</body>
</html>


