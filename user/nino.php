<?php
require_once '../config/database.php';
require_once '../config/session.php';
requirePadre();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Obtener niños del usuario
$ninos = $conn->query("SELECT * FROM ninos WHERE usuario_id = $user_id ORDER BY nombre");

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Niños - Tiny Steps</title>
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
                <h1>Mis Niños</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2>Lista de Niños</h2>
                </div>
                <div class="table-container">
                    <?php if ($ninos->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Fecha de Nacimiento</th>
                                    <th>Fecha de Registro</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($nino = $ninos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($nino['nombre']); ?></td>
                                        <td><?php echo $nino['fecha_nacimiento'] ? date('d/m/Y', strtotime($nino['fecha_nacimiento'])) : '-'; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($nino['fecha_registro'])); ?></td>
                                        <td>
                                            <span style="padding: 4px 8px; background: <?php echo $nino['activo'] ? '#C8E6C9' : '#FFCDD2'; ?>; color: <?php echo $nino['activo'] ? '#2E7D32' : '#C62828'; ?>; border-radius: 5px; font-size: 0.85em; font-weight: 600;">
                                                <?php echo $nino['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="pagos.php?nino_id=<?php echo $nino['id']; ?>" class="btn btn-sm btn-primary">Ver Pagos</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state" style="padding: 40px;">
                            <p style="margin-bottom: 20px;">No tienes niños registrados</p>
                            <p style="color: var(--gray);">Por favor contacta al administrador para registrar un niño.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
