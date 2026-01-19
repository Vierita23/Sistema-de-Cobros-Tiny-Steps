<?php
require_once '../config/database.php';
require_once '../config/session.php';
requirePadre();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$nino_id = $_GET['id'] ?? 0;

// Verificar que el niño pertenece al usuario
$stmt = $conn->prepare("SELECT * FROM ninos WHERE id = ? AND usuario_id = ? AND activo = 1");
$stmt->bind_param("ii", $nino_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$nino = $result->fetch_assoc();

if (!$nino) {
    header('Location: dashboard.php');
    exit();
}

// Obtener pagos del niño agrupados por mes/año
$pagos_query = "SELECT * FROM pagos WHERE nino_id = ? 
                ORDER BY anio_pago DESC, 
                         FIELD(mes_pago, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre') DESC,
                         fecha_subida DESC";
$stmt = $conn->prepare($pagos_query);
$stmt->bind_param("i", $nino_id);
$stmt->execute();
$result = $stmt->get_result();

// Agrupar pagos por mes/año
$pagos_por_mes = [];
while ($pago = $result->fetch_assoc()) {
    $mes_anio = $pago['mes_pago'] . ' ' . $pago['anio_pago'];
    if (!isset($pagos_por_mes[$mes_anio])) {
        $pagos_por_mes[$mes_anio] = [];
    }
    $pagos_por_mes[$mes_anio][] = $pago;
}

// Calcular totales por mes
$totales_por_mes = [];
foreach ($pagos_por_mes as $mes_anio => $pagos) {
    $total = 0;
    foreach ($pagos as $pago) {
        $total += $pago['monto'];
    }
    $totales_por_mes[$mes_anio] = $total;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nino['nombre']); ?> | Tiny Steps</title>
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
                <h1><?php echo htmlspecialchars($nino['nombre']); ?></h1>
                <div class="user-info">
                    <a href="dashboard.php" class="btn btn-secondary">Volver</a>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Información del Niño</h2>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <strong>Nombre:</strong> <?php echo htmlspecialchars($nino['nombre']); ?>
                    </div>
                    <?php if ($nino['fecha_nacimiento']): ?>
                    <div>
                        <strong>Fecha de Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($nino['fecha_nacimiento'])); ?>
                    </div>
                    <?php endif; ?>
                    <div>
                        <strong>Fecha de Registro:</strong> <?php echo date('d/m/Y', strtotime($nino['fecha_registro'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2>Pagos de <?php echo htmlspecialchars($nino['nombre']); ?></h2>
                    <a href="subir_pago.php?nino_id=<?php echo $nino['id']; ?>" class="btn btn-primary btn-sm">Subir Nuevo Pago</a>
                </div>
                
                <?php if (empty($pagos_por_mes)): ?>
                    <div class="empty-state" style="padding: 40px; text-align: center;">
                        <p>No hay pagos registrados para este niño</p>
                        <a href="subir_pago.php?nino_id=<?php echo $nino['id']; ?>" class="btn btn-primary" style="margin-top: 20px;">Subir Primer Pago</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($pagos_por_mes as $mes_anio => $pagos): ?>
                        <div style="margin: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px; border-left: 4px solid var(--cyan);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <h3 style="margin: 0; color: var(--dark); font-size: 1.1em;">
                                    📅 <?php echo htmlspecialchars($mes_anio); ?>
                                </h3>
                                <span style="font-weight: 700; color: var(--primary);">
                                    Total del Mes: $<?php echo number_format($totales_por_mes[$mes_anio], 2); ?>
                                </span>
                            </div>
                            
                            <div class="table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Fecha Subida</th>
                                            <th>Monto</th>
                                            <th>Motivo</th>
                                            <th>Cuenta Bancaria</th>
                                            <th>Estado</th>
                                            <th>Comprobante</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pagos as $pago): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_subida'])); ?></td>
                                                <td><strong>$<?php echo number_format($pago['monto'], 2); ?></strong></td>
                                                <td>
                                                    <?php 
                                                    if (isset($pago['motivo_pago']) && $pago['motivo_pago']):
                                                        $motivos = [
                                                            'mensualidad' => 'Mensualidad',
                                                            'atrasos' => 'Atrasos',
                                                            'horas_adicionales' => 'Horas Adicionales',
                                                            'otro' => 'Otro'
                                                        ];
                                                        $motivo_texto = $motivos[$pago['motivo_pago']] ?? ucfirst($pago['motivo_pago']);
                                                    ?>
                                                        <span style="padding: 4px 8px; background: linear-gradient(135deg, rgba(255, 0, 255, 0.1) 0%, rgba(0, 191, 255, 0.1) 100%); color: var(--primary); border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                            <?php echo htmlspecialchars($motivo_texto); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span>-</span>
                                                    <?php endif; ?>
                                                </td>
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
                                                    <a href="ver_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-primary">Ver Detalles</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
</body>
</html>


