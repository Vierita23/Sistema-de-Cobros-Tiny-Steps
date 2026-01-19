<?php
require_once '../config/database.php';
require_once '../config/session.php';
requirePadre();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Obtener todos los pagos del usuario agrupados por mes/año
$pagos_query = "SELECT p.*, n.nombre as nino_nombre 
                FROM pagos p 
                JOIN ninos n ON p.nino_id = n.id 
                WHERE p.usuario_id = $user_id 
                ORDER BY p.anio_pago DESC, 
                         FIELD(p.mes_pago, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre') DESC,
                         p.fecha_subida DESC";
$result = $conn->query($pagos_query);

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

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pagos | Tiny Steps</title>
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
                <h1>Mis Pagos</h1>
                <div class="user-info">
                    <a href="subir_pago.php" class="btn btn-primary">Subir Nuevo Pago</a>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <?php if (empty($pagos_por_mes)): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Mis Pagos</h2>
                    </div>
                    <div class="empty-state" style="padding: 40px; text-align: center;">
                        <p>No hay pagos registrados</p>
                        <a href="subir_pago.php" class="btn btn-primary" style="margin-top: 20px;">Subir Primer Pago</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($pagos_por_mes as $mes_anio => $pagos): ?>
                    <div class="card" style="margin-bottom: 25px;">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, rgba(255, 0, 255, 0.1) 0%, rgba(0, 191, 255, 0.1) 100%);">
                            <h2 style="margin: 0; background: linear-gradient(135deg, var(--primary) 0%, var(--cyan) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                📅 <?php echo htmlspecialchars($mes_anio); ?>
                            </h2>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 1.1em; font-weight: 700; color: var(--dark);">
                                    Total: <span style="color: var(--primary);">$<?php echo number_format($totales_por_mes[$mes_anio], 2); ?></span>
                                </span>
                            </div>
                        </div>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Fecha Subida</th>
                                        <th>Niño</th>
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
                                            <td><?php echo htmlspecialchars($pago['nino_nombre']); ?></td>
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
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
</body>
</html>


