<?php
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../config/validaciones.php';
requirePadre();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$mensaje = '';

// Obtener niños del usuario
$ninos = $conn->query("SELECT * FROM ninos WHERE usuario_id = $user_id AND activo = 1 ORDER BY nombre");

// Procesar subida de pago
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subir_pago'])) {
    $nino_id = intval($_POST['nino_id'] ?? 0);
    $monto = $_POST['monto'] ?? '';
    $mes_pago = sanitizarInput($_POST['mes_pago'] ?? '');
    $anio_pago = intval($_POST['anio_pago'] ?? 0);
    $numero_cuenta = sanitizarInput($_POST['numero_cuenta'] ?? '');
    $es_deposito = isset($_POST['es_deposito']) ? 1 : 0;
    $numero_comprobante = sanitizarInput($_POST['numero_comprobante'] ?? '');
    $fecha_transaccion = $_POST['fecha_transaccion'] ?? '';
    $motivo_pago = sanitizarInput($_POST['motivo_pago'] ?? '');
    $descripcion = sanitizarInput($_POST['descripcion'] ?? '');
    
    // Validaciones
    $errores = [];
    
    if (empty($nino_id) || $nino_id <= 0) {
        $errores[] = 'Debe seleccionar un niño';
    }
    
    $validacion_monto = validarMonto($monto);
    if (!$validacion_monto['valido']) {
        $errores[] = $validacion_monto['mensaje'];
    }
    
    if (empty($numero_cuenta)) {
        $errores[] = 'Debe seleccionar una cuenta bancaria';
    }
    
    if (empty($motivo_pago)) {
        $errores[] = 'Debe seleccionar un motivo de pago';
    } elseif (!in_array($motivo_pago, ['mensualidad', 'atrasos', 'horas_adicionales', 'otro'])) {
        $errores[] = 'El motivo de pago seleccionado no es válido';
    }
    
    // Si el motivo es "otro", la descripción es requerida
    if ($motivo_pago === 'otro' && empty($descripcion)) {
        $errores[] = 'Debe proporcionar una descripción cuando selecciona "Otro" como motivo';
    }
    
    if (empty($fecha_transaccion)) {
        $errores[] = 'La fecha de transacción es requerida';
    } else {
        $validacion_fecha = validarFecha($fecha_transaccion, 'fecha de transacción');
        if (!$validacion_fecha['valido']) {
            $errores[] = $validacion_fecha['mensaje'];
        }
    }
    
    $validacion_descripcion = validarTexto($descripcion, 'descripción', 1000, false);
    if (!$validacion_descripcion['valido']) {
        $errores[] = $validacion_descripcion['mensaje'];
    }
    
    if (!empty($numero_comprobante)) {
        $validacion_comprobante = validarTexto($numero_comprobante, 'número de comprobante', 100, false);
        if (!$validacion_comprobante['valido']) {
            $errores[] = $validacion_comprobante['mensaje'];
        }
    }
    
    // Validar archivo si se subió
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $validacion_archivo = validarArchivo($_FILES['comprobante'], ['jpg', 'jpeg', 'png'], 100);
        if (!$validacion_archivo['valido']) {
            $errores[] = $validacion_archivo['mensaje'];
        }
    }
    
    // Determinar cuenta_bancaria basado en el número de cuenta seleccionado
    $cuenta_bancaria = null;
    if ($numero_cuenta) {
        if (strpos($numero_cuenta, 'BOLIVARIANO') !== false || strpos($numero_cuenta, 'Bolivariano') !== false) {
            $cuenta_bancaria = 'Bolivariano';
        }
    }
    
    // Verificar que el niño pertenece al usuario
    $stmt = $conn->prepare("SELECT id FROM ninos WHERE id = ? AND usuario_id = ? AND activo = 1");
    $stmt->bind_param("ii", $nino_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $errores[] = 'El niño seleccionado no es válido';
    }
    $stmt->close();
    
    if (empty($errores)) {
        // Procesar archivo
        $comprobante_path = null;
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/comprobantes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Verificar tamaño máximo (100MB)
            $max_size = 100 * 1024 * 1024; // 100MB en bytes
            if ($_FILES['comprobante']['size'] > $max_size) {
                $mensaje = '<div class="alert alert-error">El archivo es demasiado grande. Tamaño máximo permitido: 100MB</div>';
            } else {
                $file_extension = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                $file_name = 'pago_' . $user_id . '_' . time() . '_' . uniqid() . '.' . $file_extension;
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $file_path)) {
                    $comprobante_path = 'uploads/comprobantes/' . $file_name;
                } else {
                    $mensaje = '<div class="alert alert-error">Error al subir el archivo</div>';
                }
                } else {
                    $mensaje = '<div class="alert alert-error">Formato de archivo no permitido. Use JPG o PNG</div>';
                }
            }
        }
        
        if (!$mensaje) {
            // Verificar qué campos existen en la tabla
            $campos_disponibles = [];
            $result_campos = $conn->query("SHOW COLUMNS FROM pagos");
            while ($campo = $result_campos->fetch_assoc()) {
                $campos_disponibles[] = $campo['Field'];
            }
            
            // Construir la consulta dinámicamente según los campos disponibles
            $campos = ['nino_id', 'usuario_id', 'monto', 'mes_pago', 'anio_pago'];
            $valores = [$nino_id, $user_id, $monto, $mes_pago, $anio_pago];
            $tipos = "iidss";
            
            if (in_array('cuenta_bancaria', $campos_disponibles)) {
                $campos[] = 'cuenta_bancaria';
                $valores[] = $cuenta_bancaria;
                $tipos .= 's';
            }
            
            if (in_array('numero_cuenta', $campos_disponibles)) {
                $campos[] = 'numero_cuenta';
                $valores[] = $numero_cuenta;
                $tipos .= 's';
            }
            
            if (in_array('es_deposito', $campos_disponibles)) {
                $campos[] = 'es_deposito';
                $valores[] = $es_deposito;
                $tipos .= 'i';
            }
            
            if (in_array('numero_comprobante', $campos_disponibles)) {
                $campos[] = 'numero_comprobante';
                $valores[] = $numero_comprobante;
                $tipos .= 's';
            }
            
            if (in_array('fecha_transaccion', $campos_disponibles)) {
                $campos[] = 'fecha_transaccion';
                $valores[] = $fecha_transaccion ?: null;
                $tipos .= 's';
            }
            
            if (in_array('motivo_pago', $campos_disponibles)) {
                $campos[] = 'motivo_pago';
                $valores[] = $motivo_pago ?: null;
                $tipos .= 's';
            }
            
            if (in_array('descripcion', $campos_disponibles)) {
                $campos[] = 'descripcion';
                $valores[] = $descripcion;
                $tipos .= 's';
            }
            
            if (in_array('comprobante_path', $campos_disponibles)) {
                $campos[] = 'comprobante_path';
                $valores[] = $comprobante_path;
                $tipos .= 's';
            }
            
            // Construir la consulta SQL
            $placeholders = str_repeat('?,', count($campos) - 1) . '?';
            $sql = "INSERT INTO pagos (" . implode(', ', $campos) . ") VALUES ($placeholders)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($tipos, ...$valores);
            
            if ($stmt->execute()) {
                $pago_id = $conn->insert_id;
                $mensaje = '<div class="alert alert-success">
                    <strong>¡Pago subido exitosamente!</strong> El administrador lo revisará pronto.
                </div>';
                // Limpiar formulario
                $_POST = array();
            } else {
                $mensaje = '<div class="alert alert-error">Error al registrar el pago: ' . $conn->error . '</div>';
            }
            $stmt->close();
        } else {
            $mensaje = '<div class="alert alert-error"><strong>Errores de validación:</strong><ul style="margin-top: 10px; padding-left: 20px;">';
            foreach ($errores as $error) {
                $mensaje .= '<li>' . htmlspecialchars($error) . '</li>';
            }
            $mensaje .= '</ul></div>';
        }
    }
}

// Si hay nino_id en la URL, preseleccionarlo
$nino_seleccionado = $_GET['nino_id'] ?? '';

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pago | Tiny Steps</title>
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
                <h1>Subir Pago</h1>
                <div class="user-info">
                    <a href="pagos.php" class="btn btn-secondary">Volver a Pagos</a>
                    <a href="../logout.php" class="logout-btn">Cerrar Sesión</a>
                </div>
            </div>
            
            <?php echo $mensaje; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2>Adicionar</h2>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nino_id">Niño</label>
                        <select id="nino_id" name="nino_id" required>
                            <option value="">Seleccionar niño...</option>
                            <?php 
                            $ninos->data_seek(0);
                            while ($nino = $ninos->fetch_assoc()): ?>
                                <option value="<?php echo $nino['id']; ?>" 
                                        <?php echo $nino_seleccionado == $nino['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($nino['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="numero_cuenta">Cuenta</label>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <select id="numero_cuenta" name="numero_cuenta" required style="border: 2px solid #dc3545; flex: 1;">
                                <option value="">------</option>
                                <option value="1651106425 - BANCO BOLIVARIANO">1651106425 - Banco Bolivariano</option>
                            </select>
                            <img src="../assets/logo_bolivariano.png" alt="Banco Bolivariano" style="height: 50px; width: auto; object-fit: contain; max-width: 150px;" onerror="this.style.display='none'">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo_pago">Motivo del Pago</label>
                        <select id="motivo_pago" name="motivo_pago" required>
                            <option value="">Seleccionar motivo...</option>
                            <option value="mensualidad">Mensualidad</option>
                            <option value="atrasos">Atrasos</option>
                            <option value="horas_adicionales">Horas Adicionales</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="descripcion_group">
                        <label for="descripcion">Descripción</label>
                        <textarea id="descripcion" name="descripcion" rows="4" placeholder="Describe detalles adicionales sobre el pago (opcional)"></textarea>
                        <small style="color: #666; display: block; margin-top: 5px;">Opcional: Agrega información adicional sobre el pago</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Deposito</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button type="button" id="btnDeposito" onclick="toggleDeposito()" class="btn btn-secondary" style="background: #2196F3; border-color: #2196F3; color: white; padding: 8px 20px;">
                                Si
                            </button>
                            <input type="hidden" id="es_deposito" name="es_deposito" value="1">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="monto">Valor</label>
                        <input type="number" id="monto" name="monto" step="0.01" min="0" max="999999.99" required value="0.00" placeholder="0.00" onblur="validarMontoInput(this)">
                        <small class="error-message" id="error_monto"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="numero_comprobante">No. Comprobante</label>
                        <input type="text" id="numero_comprobante" name="numero_comprobante" placeholder="Ingrese el número de comprobante">
                    </div>
                    
                    <div class="form-group">
                        <label for="fecha_transaccion">Fecha de transacción</label>
                        <input type="date" id="fecha_transaccion" name="fecha_transaccion" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Imagen del deposito</label>
                        <div>
                            <button type="button" onclick="document.getElementById('comprobante').click()" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px; background: #2196F3; border-color: #2196F3;">
                                <span>📎</span> Seleccionar
                            </button>
                            <input type="file" id="comprobante" name="comprobante" accept=".jpg,.jpeg,.png" onchange="previewFile(this)" style="display: none;">
                            <div class="file-preview" id="filePreview" style="margin-top: 10px;"></div>
                            <p style="font-size: 0.85em; color: #28a745; margin-top: 8px; font-weight: 600;">
                                Tamaño máximo permitido 100Mb, en formato jpg, png
                            </p>
                        </div>
                    </div>
                    
                    <!-- Campos ocultos para compatibilidad -->
                    <input type="hidden" name="mes_pago" value="<?php echo date('F'); ?>">
                    <input type="hidden" name="anio_pago" value="<?php echo date('Y'); ?>">
                    <div class="form-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="submit" name="subir_pago" class="btn btn-primary" style="background: #28a745; border-color: #28a745;">Guardar</button>
                        <a href="pagos.php" class="btn btn-secondary" style="background: #dc3545; border-color: #dc3545; color: white;">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function toggleDeposito() {
            const btn = document.getElementById('btnDeposito');
            const input = document.getElementById('es_deposito');
            const isDeposito = input.value === '1';
            
            if (isDeposito) {
                input.value = '0';
                btn.textContent = 'No';
                btn.style.background = '#dc3545';
                btn.style.borderColor = '#dc3545';
            } else {
                input.value = '1';
                btn.textContent = 'Si';
                btn.style.background = '#2196F3';
                btn.style.borderColor = '#2196F3';
            }
        }
        
        function previewFile(input) {
            const preview = document.getElementById('filePreview');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const maxSize = 100 * 1024 * 1024; // 100MB
                
                if (file.size > maxSize) {
                    alert('El archivo es demasiado grande. Tamaño máximo: 100MB');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                
                if (file.type.startsWith('image/')) {
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '300px';
                        img.style.maxHeight = '300px';
                        img.style.borderRadius = '8px';
                        img.style.marginTop = '10px';
                        preview.appendChild(img);
                        preview.innerHTML += '<p style="margin-top: 10px; color: #666; font-size: 0.9em;">' + file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)</p>';
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
    <script src="../assets/js/validaciones.js"></script>
    <script src="../assets/js/dark-mode.js"></script>
    <script>
        // Mostrar/ocultar descripción según el motivo seleccionado
        document.addEventListener('DOMContentLoaded', function() {
            const motivoSelect = document.getElementById('motivo_pago');
            const descripcionGroup = document.getElementById('descripcion_group');
            const descripcionTextarea = document.getElementById('descripcion');
            
            if (motivoSelect && descripcionGroup) {
                motivoSelect.addEventListener('change', function() {
                    if (this.value === 'otro') {
                        descripcionTextarea.placeholder = 'Por favor, describe el motivo del pago';
                        descripcionTextarea.setAttribute('required', 'required');
                    } else {
                        descripcionTextarea.placeholder = 'Describe detalles adicionales sobre el pago (opcional)';
                        descripcionTextarea.removeAttribute('required');
                    }
                });
            }
        });
    </script>
</body>
</html>


