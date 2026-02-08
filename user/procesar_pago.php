<?php
/**
 * Procesa el formulario de registro de pago y redirige a subir_pago.php.
 * No se envía ninguna salida antes del redirect para evitar pantalla en blanco.
 */
error_reporting(E_ALL);
ini_set('display_errors', 0); // No imprimir nada; siempre redirigir
ini_set('log_errors', 1);

ob_start(); // Capturar cualquier warning/notice para que no rompa el header()

// Si hay error fatal, mostrar página con enlace (nunca pantalla en blanco)
register_shutdown_function(function () {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) ob_end_clean();
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $_SESSION['error_pago'] = 'Error: ' . $e['message'] . ' (línea ' . $e['line'] . ')';
        header('Location: subir_pago.php');
        exit;
    }
});

function redirigir_pago($mensaje_ok = null, $mensaje_error = null) {
    while (ob_get_level()) ob_end_clean();
    if ($mensaje_ok !== null) $_SESSION['mensaje_exito'] = $mensaje_ok;
    if ($mensaje_error !== null) $_SESSION['error_pago'] = $mensaje_error;
    header('Location: subir_pago.php');
    exit;
}

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/session.php';
    requirePadre();
} catch (Throwable $e) {
    if (session_status() === PHP_SESSION_NONE) @session_start();
    $_SESSION['error_pago'] = 'Error al cargar: ' . $e->getMessage();
    header('Location: subir_pago.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['subir_pago'])) {
    redirigir_pago(null, 'Solicitud no válida.');
}

$user_id = $_SESSION['user_id'];
$nino_id = $_POST['nino_id'] ?? '';
$monto = $_POST['monto'] ?? '';
$anio_pago = $_POST['anio_pago'] ?? '';
$cuenta_destino = $_POST['cuenta_destino'] ?? '';
$es_deposito = isset($_POST['es_deposito']) ? 1 : 0;
$numero_comprobante = $_POST['numero_comprobante'] ?? '';
$fecha_transaccion = $_POST['fecha_transaccion'] ?? '';
$motivo_pago = $_POST['motivo_pago'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$mes_pago = $meses[(int) date('n')];

if (empty($nino_id) || empty($monto) || empty($anio_pago) || empty($cuenta_destino)) {
    redirigir_pago(null, 'Por favor completa todos los campos obligatorios.');
}
if (!is_numeric($monto) || (float) $monto <= 0) {
    redirigir_pago(null, 'El monto debe ser un número mayor a 0.');
}
if (!is_numeric($anio_pago) || $anio_pago < 2020 || $anio_pago > 2100) {
    redirigir_pago(null, 'El año no es válido.');
}

$comprobante_path = null;
if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/comprobantes/';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0777, true);
    }
    $ext = strtolower(pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
        redirigir_pago(null, 'Formato no permitido. Use JPG, PNG o PDF.');
    }
    $file_name = 'pago_' . $user_id . '_' . time() . '_' . uniqid() . '.' . $ext;
    $file_path = $upload_dir . $file_name;
    if (!@move_uploaded_file($_FILES['comprobante']['tmp_name'], $file_path)) {
        redirigir_pago(null, 'Error al subir el comprobante. Comprueba permisos de la carpeta uploads.');
    }
    $comprobante_path = 'uploads/comprobantes/' . $file_name;
}

try {
    $conn = getDBConnection();
} catch (Throwable $e) {
    redirigir_pago(null, 'Error de conexión: ' . $e->getMessage());
}

$cuenta_bancaria = $cuenta_destino;
$descripcion_val = (string) $descripcion;
$comprobante_path_val = $comprobante_path !== null ? $comprobante_path : '';
$anio_pago_int = (int) $anio_pago;

// Primero intentar INSERT mínimo (funciona con la tabla base)
$stmt = @$conn->prepare("INSERT INTO pagos (nino_id, usuario_id, monto, mes_pago, anio_pago, cuenta_bancaria, descripcion, comprobante_path, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
if ($stmt) {
    $stmt->bind_param("iidsisss", $nino_id, $user_id, $monto, $mes_pago, $anio_pago_int, $cuenta_bancaria, $descripcion_val, $comprobante_path_val);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        redirigir_pago('Pago registrado correctamente. Será revisado por el administrador.', null);
    }
    $stmt->close();
}

// Si falla, intentar INSERT con columnas extra (migraciones)
$stmt = @$conn->prepare("INSERT INTO pagos (nino_id, usuario_id, monto, mes_pago, anio_pago, cuenta_bancaria, es_deposito, numero_comprobante, fecha_transaccion, motivo_pago, descripcion, comprobante_path, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
if ($stmt) {
    $fecha_trans = !empty($fecha_transaccion) ? $fecha_transaccion : null;
    $num_comp = (string) $numero_comprobante;
    $motivo = (string) $motivo_pago;
    $stmt->bind_param("iidsisssssss", $nino_id, $user_id, $monto, $mes_pago, $anio_pago_int, $cuenta_bancaria, $es_deposito, $num_comp, $fecha_trans, $motivo, $descripcion_val, $comprobante_path_val);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        redirigir_pago('Pago registrado correctamente. Será revisado por el administrador.', null);
    }
    $stmt->close();
}

$err = $conn->error;
$conn->close();
redirigir_pago(null, 'No se pudo guardar el pago. ' . ($err ?: 'Revisa que la base de datos tenga la tabla pagos.'));
