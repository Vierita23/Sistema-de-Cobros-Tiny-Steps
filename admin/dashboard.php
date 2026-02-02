<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$conn = getDBConnection();

// Obtener estadísticas generales
$stats_query = $conn->query("SELECT 
    (SELECT COUNT(*) FROM usuarios WHERE activo = 1) as total_usuarios,
    (SELECT COUNT(*) FROM ninos WHERE activo = 1) as total_ninos,
    (SELECT COUNT(*) FROM pagos) as total_pagos,
    (SELECT COUNT(*) FROM pagos WHERE estado = 'pendiente') as pagos_pendientes,
    (SELECT COUNT(*) FROM pagos WHERE estado = 'aceptado') as pagos_aceptados,
    (SELECT COUNT(*) FROM pagos WHERE estado = 'rechazado') as pagos_rechazados,
    (SELECT SUM(monto) FROM pagos WHERE estado = 'aceptado') as total_recaudado");
$stats = $stats_query->fetch_assoc();

// Obtener últimos pagos
$ultimos_pagos = $conn->query("SELECT p.*, n.nombre as nino_nombre, u.nombre as usuario_nombre 
                               FROM pagos p 
                               JOIN ninos n ON p.nino_id = n.id 
                               JOIN usuarios u ON p.usuario_id = u.id 
                               ORDER BY p.fecha_subida DESC 
                               LIMIT 10");

// Calcular porcentajes para gráficos
$total_pagos_val = $stats['total_pagos'] ?? 1;
$porcentaje_aceptados = $total_pagos_val > 0 ? round(($stats['pagos_aceptados'] / $total_pagos_val) * 100) : 0;
$porcentaje_pendientes = $total_pagos_val > 0 ? round(($stats['pagos_pendientes'] / $total_pagos_val) * 100) : 0;
$porcentaje_rechazados = $total_pagos_val > 0 ? round(($stats['pagos_rechazados'] / $total_pagos_val) * 100) : 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo - Tiny Steps</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="alternate icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stat-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            box-shadow: var(--box-shadow-md);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--stat-gradient);
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--box-shadow-xl);
        }
        
        .stat-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: var(--stat-icon-bg);
            color: var(--stat-icon-color);
            font-size: 28px;
        }
        
        .stat-card-value {
            font-size: 3em;
            font-weight: 800;
            margin: 15px 0 10px 0;
            background: var(--stat-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-card-label {
            color: var(--gray-dark);
            font-size: 0.95em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card-trend {
            margin-top: 15px;
            font-size: 0.85em;
            color: var(--success);
            font-weight: 600;
        }
        
        .chart-container {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            box-shadow: var(--box-shadow-md);
            margin-bottom: 30px;
        }
        
        .progress-bar {
            height: 12px;
            background: var(--light-gray);
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 1s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .quick-action-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 35px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            border: 2px solid transparent;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }
        
        .quick-action-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.6s;
        }
        
        .quick-action-card:hover::before {
            transform: scale(1);
        }
        
        .quick-action-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: var(--primary);
            box-shadow: var(--box-shadow-lg);
        }
        
        .quick-action-icon {
            font-size: 3.5em;
            margin-bottom: 20px;
            display: block;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }
        
        .quick-action-title {
            font-size: 1.3em;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .quick-action-desc {
            color: var(--gray);
            font-size: 0.9em;
        }
        
        .welcome-banner {
            background: var(--primary-gradient);
            border-radius: var(--border-radius-lg);
            padding: 40px;
            margin-bottom: 40px;
            color: var(--white);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .welcome-title {
            font-size: 2.5em;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            font-size: 1.2em;
            opacity: 0.95;
        }
    </style>
</head>
<body>
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <!-- Banner de Bienvenida -->
            <div class="welcome-banner">
                <div class="welcome-title">👋 Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="welcome-subtitle">Panel de Control Administrativo - Gestión Completa del Sistema</div>
            </div>
            
            <!-- Estadísticas Principales -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; margin-bottom: 30px;">
                <div class="stat-card" style="--stat-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); --stat-icon-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%); --stat-icon-color: white;">
                    <div class="stat-card-icon">👥</div>
                    <div class="stat-card-label">Total Usuarios</div>
                    <div class="stat-card-value"><?php echo $stats['total_usuarios'] ?? 0; ?></div>
                    <div class="stat-card-trend">✓ Activos en el sistema</div>
                </div>
                
                <div class="stat-card" style="--stat-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); --stat-icon-bg: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); --stat-icon-color: white;">
                    <div class="stat-card-icon">👶</div>
                    <div class="stat-card-label">Total Niños</div>
                    <div class="stat-card-value"><?php echo $stats['total_ninos'] ?? 0; ?></div>
                    <div class="stat-card-trend">✓ Registrados</div>
                </div>
                
                <div class="stat-card" style="--stat-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); --stat-icon-bg: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); --stat-icon-color: white;">
                    <div class="stat-card-icon">⏳</div>
                    <div class="stat-card-label">Pagos Pendientes</div>
                    <div class="stat-card-value"><?php echo $stats['pagos_pendientes'] ?? 0; ?></div>
                    <div class="stat-card-trend">⚠ Requieren revisión</div>
                </div>
                
                <div class="stat-card" style="--stat-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); --stat-icon-bg: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); --stat-icon-color: white;">
                    <div class="stat-card-icon">💰</div>
                    <div class="stat-card-label">Total Recaudado</div>
                    <div class="stat-card-value" style="font-size: 2.2em;">$<?php echo number_format($stats['total_recaudado'] ?? 0, 2); ?></div>
                    <div class="stat-card-trend">✓ Pagos aceptados</div>
                </div>
            </div>
            
            <!-- Gráfico de Estado de Pagos -->
            <div class="chart-container">
                <h2 style="margin: 0 0 25px 0; font-size: 1.8em; font-weight: 700; color: var(--dark);">📊 Distribución de Pagos</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 25px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-weight: 600; color: var(--success);">✓ Aceptados</span>
                            <span style="font-weight: 700; color: var(--dark);"><?php echo $porcentaje_aceptados; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $porcentaje_aceptados; ?>%; background: linear-gradient(135deg, #10B981 0%, #059669 100%);"></div>
                        </div>
                        <div style="margin-top: 5px; font-size: 0.9em; color: var(--gray);"><?php echo $stats['pagos_aceptados'] ?? 0; ?> pagos</div>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-weight: 600; color: var(--warning);">⏳ Pendientes</span>
                            <span style="font-weight: 700; color: var(--dark);"><?php echo $porcentaje_pendientes; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $porcentaje_pendientes; ?>%; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);"></div>
                        </div>
                        <div style="margin-top: 5px; font-size: 0.9em; color: var(--gray);"><?php echo $stats['pagos_pendientes'] ?? 0; ?> pagos</div>
                    </div>
                    
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-weight: 600; color: var(--error);">✗ Rechazados</span>
                            <span style="font-weight: 700; color: var(--dark);"><?php echo $porcentaje_rechazados; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $porcentaje_rechazados; ?>%; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);"></div>
                        </div>
                        <div style="margin-top: 5px; font-size: 0.9em; color: var(--gray);"><?php echo $stats['pagos_rechazados'] ?? 0; ?> pagos</div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2>⚡ Acciones Rápidas</h2>
                </div>
                <div style="padding: 35px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px;">
                    <a href="pagos.php" class="quick-action-card">
                        <span class="quick-action-icon">💰</span>
                        <div class="quick-action-title">Gestionar Pagos</div>
                        <div class="quick-action-desc">Revisar y verificar todos los pagos del sistema</div>
                    </a>
                    <a href="usuarios.php" class="quick-action-card">
                        <span class="quick-action-icon">👥</span>
                        <div class="quick-action-title">Gestionar Usuarios</div>
                        <div class="quick-action-desc">Administrar usuarios y permisos del sistema</div>
                    </a>
                    <a href="ninos.php" class="quick-action-card">
                        <span class="quick-action-icon">👶</span>
                        <div class="quick-action-title">Gestionar Niños</div>
                        <div class="quick-action-desc">Administrar información de los niños registrados</div>
                    </a>
                </div>
            </div>
            
            <!-- Últimos Pagos -->
            <div class="card">
                <div class="card-header">
                    <h2>📋 Últimos Pagos Registrados</h2>
                </div>
                <div class="table-container">
                    <?php if ($ultimos_pagos->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Niño</th>
                                    <th>Padre/Madre</th>
                                    <th>Monto</th>
                                    <th>Mes/Año</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($pago = $ultimos_pagos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($pago['fecha_subida'])); ?></td>
                                        <td><strong><?php echo htmlspecialchars($pago['nino_nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($pago['usuario_nombre']); ?></td>
                                        <td style="font-weight: 700; color: var(--success);">$<?php echo number_format($pago['monto'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($pago['mes_pago'] . '/' . $pago['anio_pago']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $pago['estado']; ?>">
                                                <?php echo ucfirst($pago['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="ver_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-primary">Verificar</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div style="padding: 25px; text-align: center; background: var(--light-gray);">
                            <a href="pagos.php" class="btn btn-secondary">Ver Todos los Pagos</a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="padding: 60px;">
                            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
                            <p style="margin-bottom: 20px; font-size: 1.2em;">No hay pagos registrados aún</p>
                            <p style="color: var(--gray);">Los pagos aparecerán aquí cuando los usuarios los registren</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        // Animación de entrada para las cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .quick-action-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
