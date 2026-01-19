<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Obtener todos los pagos agrupados por niño
$query = "SELECT p.*, n.nombre as nino_nombre, n.id as nino_id, u.nombre as usuario_nombre, u.email as usuario_email 
          FROM pagos p 
          JOIN ninos n ON p.nino_id = n.id 
          JOIN usuarios u ON p.usuario_id = u.id 
          ORDER BY n.nombre ASC, p.anio_pago DESC, 
                   FIELD(p.mes_pago, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                         'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre') DESC,
                   p.fecha_subida DESC";
$result = $conn->query($query);

// Agrupar pagos por niño y luego por mes/año
$pagos_por_nino = [];
while ($pago = $result->fetch_assoc()) {
    $nino_id = $pago['nino_id'];
    $nino_nombre = $pago['nino_nombre'];
    $mes_anio = $pago['mes_pago'] . ' ' . $pago['anio_pago'];
    
    if (!isset($pagos_por_nino[$nino_id])) {
        $pagos_por_nino[$nino_id] = [
            'nombre' => $nino_nombre,
            'padre' => $pago['usuario_nombre'],
            'email' => $pago['usuario_email'],
            'meses' => []
        ];
    }
    
    if (!isset($pagos_por_nino[$nino_id]['meses'][$mes_anio])) {
        $pagos_por_nino[$nino_id]['meses'][$mes_anio] = [];
    }
    
    $pagos_por_nino[$nino_id]['meses'][$mes_anio][] = $pago;
}

// Calcular totales por niño y por mes
$totales_por_nino = [];
$totales_por_mes_nino = [];
foreach ($pagos_por_nino as $nino_id => $info) {
    $total_nino = 0;
    $totales_por_mes_nino[$nino_id] = [];
    
    foreach ($info['meses'] as $mes_anio => $pagos) {
        $total_mes = 0;
        foreach ($pagos as $pago) {
            $total_mes += $pago['monto'];
            $total_nino += $pago['monto'];
        }
        $totales_por_mes_nino[$nino_id][$mes_anio] = $total_mes;
    }
    
    $totales_por_nino[$nino_id] = $total_nino;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos | Tiny Steps</title>
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
                <h1>Gestión de Pagos</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <?php if (empty($pagos_por_nino)): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Gestión de Pagos</h2>
                    </div>
                    <div class="empty-state" style="padding: 40px; text-align: center;">
                        <p>No hay pagos registrados</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($pagos_por_nino as $nino_id => $info_nino): ?>
                    <div class="card" style="margin-bottom: 30px; border-left: 5px solid var(--primary);">
                        <div class="card-header" style="background: linear-gradient(135deg, rgba(255, 0, 255, 0.1) 0%, rgba(0, 191, 255, 0.1) 100%);">
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                                <div>
                                    <h2 style="margin: 0; background: linear-gradient(135deg, var(--primary) 0%, var(--cyan) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                        👶 <?php echo htmlspecialchars($info_nino['nombre']); ?>
                                    </h2>
                                    <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;">
                                        Padre/Madre: <strong><?php echo htmlspecialchars($info_nino['padre']); ?></strong> | 
                                        Email: <?php echo htmlspecialchars($info_nino['email']); ?>
                                    </p>
                                </div>
                                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                    <span style="font-size: 1.2em; font-weight: 700; color: var(--dark);">
                                        Total General: <span style="color: var(--primary); font-size: 1.3em;">$<?php echo number_format($totales_por_nino[$nino_id], 2); ?></span>
                                    </span>
                                    <a href="pagos_nino.php?id=<?php echo $nino_id; ?>" class="btn btn-primary">Ver Todos los Pagos</a>
                                </div>
                            </div>
                        </div>
                        
                        <?php foreach ($info_nino['meses'] as $mes_anio => $pagos): ?>
                            <div style="margin: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px; border-left: 4px solid var(--cyan);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h3 style="margin: 0; color: var(--dark); font-size: 1.1em;">
                                        📅 <?php echo htmlspecialchars($mes_anio); ?>
                                    </h3>
                                    <span style="font-weight: 700; color: var(--primary);">
                                        Total del Mes: $<?php echo number_format($totales_por_mes_nino[$nino_id][$mes_anio], 2); ?>
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
                                                        <a href="ver_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-primary">Verificar</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
</body>
</html>


