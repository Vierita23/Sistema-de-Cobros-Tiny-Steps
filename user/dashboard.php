<?php
require_once '../config/database.php';
require_once '../config/session.php';
requirePadre();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Obtener niños del usuario
$ninos = $conn->query("SELECT * FROM ninos WHERE usuario_id = $user_id AND activo = 1 ORDER BY nombre");

// Obtener pagos del usuario
$pagos = $conn->query("SELECT p.*, n.nombre as nino_nombre 
                       FROM pagos p 
                       JOIN ninos n ON p.nino_id = n.id 
                       WHERE p.usuario_id = $user_id 
                       ORDER BY p.fecha_subida DESC 
                       LIMIT 10");

// Estadísticas del usuario
$stats = [
    'total_ninos' => $ninos->num_rows,
    'pagos_pendientes' => 0,
    'pagos_aceptados' => 0,
    'pagos_rechazados' => 0
];

$result = $conn->query("SELECT estado, COUNT(*) as total FROM pagos WHERE usuario_id = $user_id GROUP BY estado");
while ($row = $result->fetch_assoc()) {
    $stats['pagos_' . $row['estado']] = $row['total'];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel | Tiny Steps</title>
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
                <h1>Mi Panel</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px; margin-bottom: 35px;">
                <div class="stat-card" style="animation-delay: 0.1s;">
                    <h3>Mis Niños</h3>
                    <div class="stat-number"><?php echo $stats['total_ninos']; ?></div>
                </div>
                <div class="stat-card" style="animation-delay: 0.2s;">
                    <h3>Pagos Pendientes</h3>
                    <div class="stat-number" style="background: linear-gradient(135deg, #FCE38A 0%, #F9D371 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?php echo $stats['pagos_pendientes']; ?></div>
                </div>
                <div class="stat-card" style="animation-delay: 0.3s;">
                    <h3>Pagos Aceptados</h3>
                    <div class="stat-number" style="background: linear-gradient(135deg, #95E1D3 0%, #7dd3c0 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?php echo $stats['pagos_aceptados']; ?></div>
                </div>
                <div class="stat-card" style="animation-delay: 0.4s;">
                    <h3>Pagos Rechazados</h3>
                    <div class="stat-number" style="background: linear-gradient(135deg, #F38181 0%, #e85d75 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?php echo $stats['pagos_rechazados']; ?></div>
                </div>
            </div>
            
            <!-- Mis Niños -->
            <div class="card">
                <div class="card-header">
                    <h2>Mis Niños</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px;">
                    <?php if ($ninos->num_rows > 0): ?>
                        <?php while ($nino = $ninos->fetch_assoc()): ?>
                            <div class="nino-card">
                                <h3><?php echo htmlspecialchars($nino['nombre']); ?></h3>
                                <?php if ($nino['fecha_nacimiento']): ?>
                                    <p>
                                        Nacimiento: <?php echo date('d/m/Y', strtotime($nino['fecha_nacimiento'])); ?>
                                    </p>
                                <?php endif; ?>
                                <a href="nino.php?id=<?php echo $nino['id']; ?>" class="btn">Ver Detalles</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No tienes niños registrados. Contacta al administrador.</p>
                        </div>
                    <?php endif; ?>
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
                                <th>Monto</th>
                                <th>Mes/Año</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pagos->num_rows > 0): ?>
                                <?php while ($pago = $pagos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($pago['fecha_subida'])); ?></td>
                                        <td><?php echo htmlspecialchars($pago['nino_nombre']); ?></td>
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
                                    <td colspan="6" class="empty-state">No hay pagos registrados</td>
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


