<?php
require_once '../config/database.php';
require_once '../config/session.php';
requirePadre();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Obtener mes y año seleccionados (por defecto mes actual)
$mes_seleccionado = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
$anio_seleccionado = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

// Nombres de los meses
$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Obtener estadísticas generales del usuario (totales de todos los tiempos)
$stats_query = $conn->query("SELECT 
    COUNT(*) as total_pagos,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'aceptado' THEN 1 ELSE 0 END) as aceptados,
    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados,
    SUM(CASE WHEN estado = 'aceptado' THEN monto ELSE 0 END) as total_pagado
    FROM pagos WHERE usuario_id = $user_id");
$stats = $stats_query->fetch_assoc();

// Obtener estadísticas del mes seleccionado
$stats_mes_query = $conn->query("SELECT 
    COUNT(*) as total_pagos_mes,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes_mes,
    SUM(CASE WHEN estado = 'aceptado' THEN 1 ELSE 0 END) as aceptados_mes,
    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados_mes,
    SUM(CASE WHEN estado = 'aceptado' THEN monto ELSE 0 END) as total_pagado_mes
    FROM pagos 
    WHERE usuario_id = $user_id 
    AND mes_pago = '" . $mes_seleccionado . "' 
    AND anio_pago = " . $anio_seleccionado);
$stats_mes = $stats_mes_query->fetch_assoc();

// Obtener años disponibles (años en los que el usuario tiene pagos)
$anios_result = $conn->query("SELECT DISTINCT anio_pago FROM pagos WHERE usuario_id = $user_id ORDER BY anio_pago DESC");
$anios_array = [];
while ($row = $anios_result->fetch_assoc()) {
    $anios_array[] = (int)$row['anio_pago'];
}
// Obtener año actual
$anio_actual = (int)date('Y');
$anio_maximo = 2040;
// Agregar año actual y años futuros hasta 2040 si no están en la lista
for ($anio = $anio_actual; $anio <= $anio_maximo; $anio++) {
    if (!in_array($anio, $anios_array)) {
        $anios_array[] = $anio;
    }
}
// Ordenar de forma descendente (más reciente primero)
rsort($anios_array);

// Obtener niños del usuario
$ninos_query = $conn->query("SELECT id, nombre FROM ninos WHERE usuario_id = $user_id AND activo = 1");
$total_ninos = $ninos_query->num_rows;

// Obtener últimos pagos
$ultimos_pagos = $conn->query("SELECT p.*, n.nombre as nino_nombre 
                               FROM pagos p 
                               JOIN ninos n ON p.nino_id = n.id 
                               WHERE p.usuario_id = $user_id 
                               ORDER BY p.fecha_subida DESC 
                               LIMIT 5");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Dashboard - Tiny Steps</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="alternate icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--border-radius-lg);
            padding: 50px 40px;
            margin-bottom: 40px;
            color: var(--white);
            position: relative;
            overflow: hidden;
            box-shadow: var(--box-shadow-lg);
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .hero-title {
            font-size: 3em;
            font-weight: 800;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }
        
        .hero-subtitle {
            font-size: 1.3em;
            opacity: 0.95;
            position: relative;
            z-index: 1;
        }
        
        .stat-card-premium {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 35px;
            box-shadow: var(--box-shadow-md);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            border-top: 5px solid;
        }
        
        .stat-card-premium:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--box-shadow-xl);
        }
        
        .stat-card-premium.card-primary { border-top-color: #667eea; }
        .stat-card-premium.card-warning { border-top-color: #F59E0B; }
        .stat-card-premium.card-success { border-top-color: #10B981; }
        .stat-card-premium.card-info { border-top-color: #3B82F6; }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .stat-icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            position: relative;
        }
        
        .stat-icon-wrapper.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon-wrapper.warning { background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); }
        .stat-icon-wrapper.success { background: linear-gradient(135deg, #10B981 0%, #059669 100%); }
        .stat-icon-wrapper.info { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); }
        
        .stat-value {
            font-size: 3.5em;
            font-weight: 900;
            margin: 15px 0;
            background: var(--stat-text-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            font-size: 1.1em;
            font-weight: 600;
            color: var(--gray-dark);
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        .action-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 40px 30px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            border: 2px solid var(--light-gray);
            position: relative;
            overflow: hidden;
        }
        
        .action-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .action-card:hover::after {
            left: 100%;
        }
        
        .action-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: var(--box-shadow-lg);
        }
        
        .action-icon {
            font-size: 4em;
            margin-bottom: 20px;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.15));
            transition: transform 0.3s;
        }
        
        .action-card:hover .action-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .action-title {
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .action-desc {
            color: var(--gray);
            font-size: 0.95em;
            line-height: 1.6;
        }
        
        .recent-payments-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--box-shadow-md);
            overflow: hidden;
        }
        
        .payment-item {
            padding: 25px 30px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: var(--transition-fast);
        }
        
        .payment-item:hover {
            background: var(--light-gray);
            transform: translateX(5px);
        }
        
        .payment-item:last-child {
            border-bottom: none;
        }
        
        .payment-info {
            flex: 1;
        }
        
        .payment-nino {
            font-weight: 700;
            font-size: 1.1em;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .payment-details {
            color: var(--gray);
            font-size: 0.9em;
        }
        
        .payment-amount {
            font-size: 1.8em;
            font-weight: 800;
            margin-right: 20px;
        }
        
        .empty-state-premium {
            text-align: center;
            padding: 80px 40px;
            color: var(--gray);
        }
        
        .empty-icon {
            font-size: 5em;
            margin-bottom: 20px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <!-- Hero Section -->
            <div class="hero-section">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <div class="hero-title">¡Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</div>
                        <div class="hero-subtitle">Bienvenido a tu panel de control. Aquí puedes gestionar tus pagos y ver el estado de tus transacciones.</div>
                    </div>
                    <div style="text-align: right; background: rgba(255,255,255,0.12); padding: 12px 20px; border-radius: 10px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                        <div id="fecha-actual" style="font-size: 0.95em; font-weight: 500; opacity: 0.95; margin-bottom: 4px;">
                            <?php 
                            setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
                            $fecha_completa = strftime('%A, %d de %B de %Y', time());
                            echo ucfirst($fecha_completa);
                            ?>
                        </div>
                        <div id="hora-actual" style="font-size: 1.1em; font-weight: 600; opacity: 1;">
                            <?php echo date('H:i:s'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtro de Mes/Año para Pagos -->
            <div class="card" style="margin-bottom: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div style="padding: 25px;">
                    <h2 style="margin: 0 0 20px 0; color: white; font-size: 1.5em;">📅 Ver Mis Pagos por Mes</h2>
                    <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; opacity: 0.9;">Mes:</label>
                            <select name="mes" style="width: 100%; padding: 12px; border-radius: 8px; border: none; font-size: 1em; background: white; color: #333;" onchange="this.form.submit()">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $mes_seleccionado == $i ? 'selected' : ''; ?>>
                                        <?php echo $meses[$i]; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div style="flex: 1; min-width: 150px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; opacity: 0.9;">Año:</label>
                            <select name="anio" style="width: 100%; padding: 12px; border-radius: 8px; border: none; font-size: 1em; background: white; color: #333;" onchange="this.form.submit()">
                                <?php foreach ($anios_array as $anio): ?>
                                    <option value="<?php echo $anio; ?>" <?php echo $anio_seleccionado == $anio ? 'selected' : ''; ?>>
                                        <?php echo $anio; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: flex; align-items: flex-end; gap: 10px;">
                            <button type="submit" class="btn" style="background: white; color: #667eea; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                                🔍 Filtrar
                            </button>
                            <a href="dashboard.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; padding: 12px 24px; border: 2px solid white; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block;">
                                📊 Ver Todo
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 25px; margin-bottom: 40px;">
                <div class="stat-card-premium card-primary" style="--stat-text-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper primary">📊</div>
                    </div>
                    <div class="stat-value"><?php echo $stats_mes['total_pagos_mes'] ?? 0; ?></div>
                    <div class="stat-label">Pagos (<?php echo $meses[$mes_seleccionado] . ' ' . $anio_seleccionado; ?>)</div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid rgba(0,0,0,0.1); font-size: 0.9em; opacity: 0.8;">
                        <strong>Total:</strong> <?php echo $stats['total_pagos'] ?? 0; ?> pagos
                    </div>
                </div>
                
                <div class="stat-card-premium card-warning" style="--stat-text-gradient: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper warning">⏳</div>
                    </div>
                    <div class="stat-value"><?php echo $stats_mes['pendientes_mes'] ?? 0; ?></div>
                    <div class="stat-label">Pendientes (<?php echo $meses[$mes_seleccionado] . ' ' . $anio_seleccionado; ?>)</div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid rgba(0,0,0,0.1); font-size: 0.9em; opacity: 0.8;">
                        <strong>Total:</strong> <?php echo $stats['pendientes'] ?? 0; ?> pendientes
                    </div>
                </div>
                
                <div class="stat-card-premium card-success" style="--stat-text-gradient: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper success">✓</div>
                    </div>
                    <div class="stat-value"><?php echo $stats_mes['aceptados_mes'] ?? 0; ?></div>
                    <div class="stat-label">Aceptados (<?php echo $meses[$mes_seleccionado] . ' ' . $anio_seleccionado; ?>)</div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid rgba(0,0,0,0.1); font-size: 0.9em; opacity: 0.8;">
                        <strong>Total:</strong> <?php echo $stats['aceptados'] ?? 0; ?> aceptados
                    </div>
                </div>
                
                <div class="stat-card-premium card-info" style="--stat-text-gradient: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                    <div class="stat-header">
                        <div class="stat-icon-wrapper info">💰</div>
                    </div>
                    <div class="stat-value" style="font-size: 2.5em;">$<?php echo number_format($stats_mes['total_pagado_mes'] ?? 0, 0); ?></div>
                    <div class="stat-label">Total Pagado (<?php echo $meses[$mes_seleccionado] . ' ' . $anio_seleccionado; ?>)</div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid rgba(0,0,0,0.1); font-size: 0.9em; opacity: 0.8;">
                        <strong>Total General:</strong> $<?php echo number_format($stats['total_pagado'] ?? 0, 2); ?>
                    </div>
                </div>
            </div>
            
            <!-- Acciones Rápidas -->
            <div class="card" style="margin-bottom: 40px;">
                <div class="card-header">
                    <h2>🚀 Acciones Rápidas</h2>
                </div>
                <div style="padding: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                    <a href="subir_pago.php" class="action-card">
                        <span class="action-icon">📤</span>
                        <div class="action-title">Subir Nuevo Pago</div>
                        <div class="action-desc">Registra un nuevo pago con su comprobante</div>
                    </a>
                    <a href="pagos.php" class="action-card">
                        <span class="action-icon">💰</span>
                        <div class="action-title">Ver Mis Pagos</div>
                        <div class="action-desc">Consulta el historial completo de tus pagos</div>
                    </a>
                    <a href="nino.php" class="action-card">
                        <span class="action-icon">👶</span>
                        <div class="action-title">Mis Niños</div>
                        <div class="action-desc">Gestiona la información de tus niños</div>
                    </a>
                </div>
            </div>
            
            <!-- Últimos Pagos -->
            <div class="recent-payments-card">
                <div class="card-header">
                    <h2>📋 Últimos Pagos</h2>
                </div>
                <?php if ($ultimos_pagos->num_rows > 0): ?>
                    <div>
                        <?php while ($pago = $ultimos_pagos->fetch_assoc()): ?>
                            <div class="payment-item">
                                <div class="payment-info">
                                    <div class="payment-nino"><?php echo htmlspecialchars($pago['nino_nombre']); ?></div>
                                    <div class="payment-details">
                                        <?php echo htmlspecialchars($pago['mes_pago'] . '/' . $pago['anio_pago']); ?> • 
                                        <?php echo date('d/m/Y', strtotime($pago['fecha_subida'])); ?>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 20px;">
                                    <div class="payment-amount" style="color: var(--success);">
                                        $<?php echo number_format($pago['monto'], 2); ?>
                                    </div>
                                    <div>
                                        <span class="status-badge status-<?php echo $pago['estado']; ?>">
                                            <?php echo ucfirst($pago['estado']); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <a href="ver_pago.php?id=<?php echo $pago['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div style="padding: 25px; text-align: center; background: var(--light-gray);">
                        <a href="pagos.php" class="btn btn-secondary">Ver Todos los Pagos</a>
                    </div>
                <?php else: ?>
                    <div class="empty-state-premium">
                        <div class="empty-icon">📭</div>
                        <h3 style="margin-bottom: 15px; color: var(--dark);">No tienes pagos registrados aún</h3>
                        <p style="margin-bottom: 25px; color: var(--gray);">Comienza registrando tu primer pago</p>
                        <a href="subir_pago.php" class="btn btn-primary">Subir Primer Pago</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        // Función para formatear fecha en español
        function formatearFecha(fecha) {
            const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 
                          'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            
            const diaSemana = dias[fecha.getDay()];
            const dia = fecha.getDate();
            const mes = meses[fecha.getMonth()];
            const año = fecha.getFullYear();
            
            return diaSemana + ', ' + dia + ' de ' + mes + ' de ' + año;
        }
        
        // Función para formatear hora
        function formatearHora(fecha) {
            const horas = String(fecha.getHours()).padStart(2, '0');
            const minutos = String(fecha.getMinutes()).padStart(2, '0');
            const segundos = String(fecha.getSeconds()).padStart(2, '0');
            return horas + ':' + minutos + ':' + segundos;
        }
        
        // Actualizar fecha y hora automáticamente
        function actualizarFechaHora() {
            const ahora = new Date();
            const fechaElement = document.getElementById('fecha-actual');
            const horaElement = document.getElementById('hora-actual');
            
            if (fechaElement) {
                fechaElement.textContent = formatearFecha(ahora);
            }
            if (horaElement) {
                horaElement.textContent = formatearHora(ahora);
            }
        }
        
        // Actualizar cada segundo
        setInterval(actualizarFechaHora, 1000);
        
        // Actualizar inmediatamente al cargar
        actualizarFechaHora();
        
        // Animación de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card-premium, .action-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Animación de números
            const statValues = document.querySelectorAll('.stat-value');
            statValues.forEach(stat => {
                const finalValue = stat.textContent;
                if (!isNaN(finalValue) && finalValue !== '') {
                    let current = 0;
                    const increment = finalValue / 30;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= finalValue) {
                            stat.textContent = finalValue;
                            clearInterval(timer);
                        } else {
                            stat.textContent = Math.floor(current);
                        }
                    }, 50);
                }
            });
        });
    </script>
</body>
</html>
