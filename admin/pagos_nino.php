<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Obtener ID del niño
$nino_id = $_GET['id'] ?? 0;

// Obtener información del niño
$stmt = $conn->prepare("SELECT n.*, u.nombre as usuario_nombre, u.email as usuario_email 
                        FROM ninos n 
                        JOIN usuarios u ON n.usuario_id = u.id 
                        WHERE n.id = ?");
$stmt->bind_param("i", $nino_id);
$stmt->execute();
$result = $stmt->get_result();
$nino = $result->fetch_assoc();

if (!$nino) {
    header('Location: ninos.php');
    exit();
}

// Obtener pagos del niño
$pagos = $conn->query("SELECT p.*, u.nombre as usuario_nombre 
                      FROM pagos p 
                      JOIN usuarios u ON p.usuario_id = u.id 
                      WHERE p.nino_id = $nino_id 
                      ORDER BY p.fecha_subida DESC");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos del Niño - Tiny Steps</title>
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
                <h1>Pagos de <?php echo htmlspecialchars($nino['nombre']); ?></h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2>Información del Niño</h2>
                </div>
                <div style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <strong>Nombre:</strong> <?php echo htmlspecialchars($nino['nombre']); ?>
                    </div>
                    <div>
                        <strong>Fecha de Nacimiento:</strong> <?php echo $nino['fecha_nacimiento'] ? date('d/m/Y', strtotime($nino['fecha_nacimiento'])) : '-'; ?>
                    </div>
                    <div>
                        <strong>Padre/Madre:</strong> <?php echo htmlspecialchars($nino['usuario_nombre']); ?>
                    </div>
                    <div>
                        <strong>Email:</strong> <?php echo htmlspecialchars($nino['usuario_email']); ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Historial de Pagos</h2>
                </div>
                <div class="table-container">
                    <?php if ($pagos->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha Subida</th>
                                    <th>Monto</th>
                                    <th>Mes/Año</th>
                                    <th>Cuenta Bancaria</th>
                                    <th>Estado</th>
                                    <th>Comprobante</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($pago = $pagos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_subida'])); ?></td>
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
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state" style="padding: 40px;">
                            <p>No hay pagos registrados para este niño</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <a href="ninos.php" class="btn btn-secondary">Volver a Niños</a>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
