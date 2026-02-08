<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// POST aquí = formulario enviado a esta URL por error. Redirigir sin tocar sesión (evita 500)
if (!empty($_POST['subir_pago'])) {
    header('Location: subir_pago.php');
    exit;
}

// Ante error fatal, mostrar algo en lugar de pantalla en blanco
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (ob_get_level()) ob_end_clean();
        echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title></head><body style="font-family:sans-serif;padding:20px;background:#fff3f3;">';
        echo '<h1>Error</h1><p>' . htmlspecialchars($e['message']) . '</p><p><a href="dashboard.php">Volver al inicio</a></p></body></html>';
    }
});

$mensaje = '';
$error = '';
$conn_cerrada = false;
$conn = null;
$user_id = 0;
$ninos = [];
$cuentas_bancarias = [
    'Pichincha' => ['nombre' => 'Mutualista Pichincha', 'tipo' => 'Ahorros', 'numero' => '20622558', 'color' => '#E65100', 'gradient' => 'linear-gradient(135deg, #FF6F00 0%, #E65100 100%)'],
    'Bolivariano' => ['nombre' => 'Banco Bolivariano', 'tipo' => 'Ahorros', 'numero' => '5001700105', 'color' => '#1565C0', 'gradient' => 'linear-gradient(135deg, #1976D2 0%, #1565C0 100%)']
];

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/session.php';
    requirePadre();

    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];

    // Mensajes desde procesar_pago.php
    if (isset($_SESSION['mensaje_exito'])) {
        $mensaje = '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensaje_exito']) . '</div>';
        unset($_SESSION['mensaje_exito']);
    }
    if (isset($_SESSION['error_pago'])) {
        $error = $_SESSION['error_pago'];
        unset($_SESSION['error_pago']);
    }
} catch (Throwable $e) {
    $error = 'Error: ' . $e->getMessage();
    $ninos = [];
    if (isset($_SESSION['user_id'])) $user_id = $_SESSION['user_id'];
}

// Obtener niños del usuario (solo si hay conexión)
if ($conn) {
    try {
        $ninos_query = $conn->query("SELECT id, nombre FROM ninos WHERE usuario_id = " . (int)$user_id . " AND activo = 1 ORDER BY nombre");
        if ($ninos_query) {
            while ($row = $ninos_query->fetch_assoc()) {
                $ninos[] = $row;
            }
        } else {
            if (empty($error)) $error = 'Error al cargar la lista de niños.';
        }
    } catch (Throwable $e) {
        if (empty($error)) $error = $e->getMessage();
    }
}

// El formulario se envía a procesar_pago.php, que redirige aquí con mensaje en sesión.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Nuevo Pago - Tiny Steps</title>
    <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
    <link rel="alternate icon" href="../assets/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .payment-form-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .bank-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .bank-card {
            background: var(--white);
            border: 3px solid var(--gray-light);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        .bank-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: var(--bank-gradient);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .bank-card.selected {
            border-color: var(--bank-color);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }
        
        .bank-card.selected::before {
            transform: scaleX(1);
        }
        
        .bank-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-lg);
        }
        
        .bank-icon {
            width: 70px;
            height: 70px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin-bottom: 20px;
            background: var(--bank-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .bank-name {
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .bank-details {
            color: var(--gray-dark);
            font-size: 0.95em;
            line-height: 1.8;
        }
        
        .bank-account-number {
            font-size: 1.3em;
            font-weight: 800;
            color: var(--bank-color);
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        
        .selected-bank-info {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 2px solid var(--primary);
            border-radius: var(--border-radius-lg);
            padding: 25px;
            margin-bottom: 30px;
            display: none;
        }
        
        .selected-bank-info.active {
            display: block;
            animation: slideDown 0.4s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-section {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 35px;
            margin-bottom: 25px;
            box-shadow: var(--box-shadow-md);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            font-size: 1.4em;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .file-upload-area {
            border: 3px dashed var(--gray-light);
            border-radius: var(--border-radius-lg);
            padding: 40px;
            text-align: center;
            transition: var(--transition);
            background: var(--light-gray);
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }
        
        .file-upload-area.dragover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 4em;
            margin-bottom: 15px;
        }
        
        .upload-text {
            font-size: 1.1em;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .upload-hint {
            color: var(--gray);
            font-size: 0.9em;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .file-name-display {
            margin-top: 15px;
            padding: 12px;
            background: var(--success-light);
            border-radius: var(--border-radius);
            color: var(--success);
            font-weight: 600;
            display: none;
        }
        
        .file-name-display.active {
            display: block;
        }
    </style>
</head>
<body>
    <button class="menu-toggle" id="menuToggle">☰</button>
    <div class="menu-overlay" id="menuOverlay"></div>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>💳 Subir Nuevo Pago</h1>
                <div class="user-info">
                    <span>Bienvenido, <?php echo htmlspecialchars(isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''); ?></span>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <?php if ($mensaje): ?>
                <?php echo $mensaje; ?>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (empty($ninos)): ?>
                <div class="card">
                    <div class="alert alert-warning">
                        <strong>⚠️ No tienes niños registrados.</strong> Por favor contacta al administrador para registrar un niño.
                    </div>
                    <a href="dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
                </div>
            <?php else: ?>
                <div class="payment-form-container">
                    <!-- Selección de Banco Destino -->
                    <div class="form-section">
                        <div class="section-title">🏦 Selecciona la Cuenta de Destino *</div>
                        <div class="bank-selection">
                            <?php foreach ($cuentas_bancarias as $key => $cuenta): ?>
                                <div class="bank-card" 
                                     data-bank="<?php echo $key; ?>"
                                     style="--bank-color: <?php echo $cuenta['color']; ?>; --bank-gradient: <?php echo $cuenta['gradient']; ?>">
                                    <div class="bank-icon"><?php echo $key == 'Pichincha' ? '🏦' : '💼'; ?></div>
                                    <div class="bank-name"><?php echo htmlspecialchars($cuenta['nombre']); ?></div>
                                    <div class="bank-details">
                                        <div><strong>Tipo:</strong> <?php echo htmlspecialchars($cuenta['tipo']); ?></div>
                                        <div class="bank-account-number"><?php echo htmlspecialchars($cuenta['numero']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Información de la cuenta seleccionada -->
                        <div class="selected-bank-info" id="selectedBankInfo">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                <span style="font-size: 2em;" id="selectedBankIcon">🏦</span>
                                <div>
                                    <div style="font-size: 1.3em; font-weight: 700; color: var(--dark);" id="selectedBankName"></div>
                                    <div style="color: var(--gray); font-size: 0.9em;" id="selectedBankDetails"></div>
                                </div>
                            </div>
                            <div style="padding: 15px; background: rgba(255, 255, 255, 0.7); border-radius: var(--border-radius); margin-top: 15px;">
                                <strong>Número de Cuenta:</strong>
                                <div style="font-size: 1.5em; font-weight: 800; color: var(--primary); font-family: 'Courier New', monospace; letter-spacing: 2px; margin-top: 5px;" id="selectedBankNumber"></div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="cuenta_destino" id="cuenta_destino" value="<?php echo htmlspecialchars($_POST['cuenta_destino'] ?? ''); ?>" required>
                    </div>
                    
                    <form method="POST" action="procesar_pago.php" enctype="multipart/form-data" id="paymentForm" data-action="procesar_pago.php">
                        <input type="hidden" name="cuenta_destino" id="cuenta_destino_input" value="<?php echo htmlspecialchars($_POST['cuenta_destino'] ?? ''); ?>" required>
                        
                        <!-- Información del Pago -->
                        <div class="form-section">
                            <div class="section-title">📝 Información del Pago</div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nino_id">👶 Niño *</label>
                                    <select id="nino_id" name="nino_id" required>
                                        <option value="">Seleccione un niño</option>
                                        <?php foreach ($ninos as $nino): ?>
                                            <option value="<?php echo $nino['id']; ?>" <?php echo (isset($_POST['nino_id']) && $_POST['nino_id'] == $nino['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($nino['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="monto">💰 Monto ($) *</label>
                                    <input type="number" id="monto" name="monto" step="0.01" min="0.01" required 
                                           value="<?php echo htmlspecialchars($_POST['monto'] ?? ''); ?>" placeholder="0.00">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="anio_pago">📆 Año *</label>
                                    <input type="number" id="anio_pago" name="anio_pago" min="2020" max="2100" required 
                                           value="<?php echo htmlspecialchars($_POST['anio_pago'] ?? date('Y')); ?>" placeholder="<?php echo date('Y'); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="motivo_pago">🎯 Motivo del Pago</label>
                                    <select id="motivo_pago" name="motivo_pago">
                                        <option value="">Seleccione el motivo</option>
                                        <option value="mensualidad" <?php echo (isset($_POST['motivo_pago']) && $_POST['motivo_pago'] == 'mensualidad') ? 'selected' : ''; ?>>Mensualidad</option>
                                        <option value="atrasos" <?php echo (isset($_POST['motivo_pago']) && $_POST['motivo_pago'] == 'atrasos') ? 'selected' : ''; ?>>Atrasos</option>
                                        <option value="horas_adicionales" <?php echo (isset($_POST['motivo_pago']) && $_POST['motivo_pago'] == 'horas_adicionales') ? 'selected' : ''; ?>>Horas Adicionales</option>
                                        <option value="otro" <?php echo (isset($_POST['motivo_pago']) && $_POST['motivo_pago'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="numero_comprobante">📄 Número de Comprobante</label>
                                    <input type="text" id="numero_comprobante" name="numero_comprobante" 
                                           value="<?php echo htmlspecialchars($_POST['numero_comprobante'] ?? ''); ?>" 
                                           placeholder="Número de comprobante de transferencia">
                                </div>
                                
                                <div class="form-group">
                                    <label for="fecha_transaccion">📅 Fecha de Transacción</label>
                                    <input type="date" id="fecha_transaccion" name="fecha_transaccion" 
                                           value="<?php echo htmlspecialchars($_POST['fecha_transaccion'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label style="display: flex; align-items: center; gap: 10px;">
                                    <input type="checkbox" name="es_deposito" value="1" 
                                           <?php echo (isset($_POST['es_deposito']) && $_POST['es_deposito']) ? 'checked' : ''; ?>
                                           style="width: auto; cursor: pointer;">
                                    <span>Es depósito (si no está marcado, es transferencia)</span>
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label for="descripcion">📝 Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="4" 
                                          placeholder="Información adicional sobre el pago..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Comprobante -->
                        <div class="form-section">
                            <div class="section-title">📎 Comprobante de Pago *</div>
                            
                            <div class="file-upload-area" id="uploadArea">
                                <div class="upload-icon">📤</div>
                                <div class="upload-text">Arrastra tu comprobante aquí o haz clic para seleccionar</div>
                                <div class="upload-hint">Formatos: JPG, PNG, PDF (Máx. 5MB)</div>
                                <input type="file" id="comprobante" name="comprobante" accept="image/*,.pdf" required>
                            </div>
                            
                            <div class="file-name-display" id="fileNameDisplay"></div>
                        </div>
                        
                        <div class="form-actions" style="margin-top: 30px;">
                            <button type="submit" name="subir_pago" class="btn btn-primary" style="font-size: 1.1em; padding: 15px 40px;">
                                ✅ Registrar Pago
                            </button>
                            <a href="pagos.php" class="btn btn-secondary" style="font-size: 1.1em; padding: 15px 40px;">Cancelar</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php if (!$conn_cerrada && isset($conn) && $conn): $conn->close(); endif; ?>
    <script src="../assets/js/mobile-menu.js"></script>
    <script>
        const cuentasBancarias = <?php echo json_encode($cuentas_bancarias); ?>;
        const bankCards = document.querySelectorAll('.bank-card');
        const selectedBankInfo = document.getElementById('selectedBankInfo');
        const cuentaDestinoInput = document.getElementById('cuenta_destino_input');
        const paymentForm = document.getElementById('paymentForm');
        
        // Forzar que el envío vaya siempre a procesar_pago.php (evita 500 por caché)
        var formAction = paymentForm.getAttribute('data-action') || 'procesar_pago.php';
        paymentForm.setAttribute('action', formAction);
        paymentForm.addEventListener('submit', function() { paymentForm.action = formAction; }, true);
        
        // Selección de banco
        bankCards.forEach(card => {
            card.addEventListener('click', function() {
                const bankKey = this.dataset.bank;
                
                // Remover selección anterior
                bankCards.forEach(c => c.classList.remove('selected'));
                
                // Agregar selección actual
                this.classList.add('selected');
                
                // Actualizar input hidden
                cuentaDestinoInput.value = bankKey;
                
                // Mostrar información de la cuenta seleccionada
                const cuenta = cuentasBancarias[bankKey];
                document.getElementById('selectedBankIcon').textContent = bankKey === 'Pichincha' ? '🏦' : '💼';
                document.getElementById('selectedBankName').textContent = cuenta.nombre;
                document.getElementById('selectedBankDetails').textContent = `Tipo: ${cuenta.tipo}`;
                document.getElementById('selectedBankNumber').textContent = cuenta.numero;
                document.getElementById('selectedBankNumber').style.color = cuenta.color;
                
                selectedBankInfo.classList.add('active');
            });
        });
        
        // Si hay un banco preseleccionado (después de error)
        <?php if (isset($_POST['cuenta_destino']) && !empty($_POST['cuenta_destino'])): ?>
            const preselectedBank = document.querySelector(`[data-bank="<?php echo htmlspecialchars($_POST['cuenta_destino']); ?>"]`);
            if (preselectedBank) {
                preselectedBank.click();
            }
        <?php endif; ?>
        
        // Upload de archivo
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('comprobante');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        
        uploadArea.addEventListener('click', () => fileInput.click());
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                updateFileName();
            }
        });
        
        fileInput.addEventListener('change', updateFileName);
        
        function updateFileName() {
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileNameDisplay.textContent = `📎 Archivo seleccionado: ${file.name}`;
                fileNameDisplay.classList.add('active');
            }
        }
        
        // Validación del formulario
        paymentForm.addEventListener('submit', function(e) {
            paymentForm.action = formAction;
            if (!cuentaDestinoInput.value) {
                e.preventDefault();
                alert('Por favor selecciona la cuenta de destino');
                return false;
            }
        });
    </script>
</body>
</html>
