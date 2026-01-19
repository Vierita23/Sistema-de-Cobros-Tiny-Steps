<?php
// Funciones de validación reutilizables

function validarEmail($email) {
    if (empty($email)) {
        return ['valido' => false, 'mensaje' => 'El email es requerido'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valido' => false, 'mensaje' => 'El formato del email no es válido'];
    }
    
    if (strlen($email) > 100) {
        return ['valido' => false, 'mensaje' => 'El email no puede tener más de 100 caracteres'];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function validarNombre($nombre) {
    if (empty($nombre)) {
        return ['valido' => false, 'mensaje' => 'El nombre es requerido'];
    }
    
    if (strlen($nombre) < 2) {
        return ['valido' => false, 'mensaje' => 'El nombre debe tener al menos 2 caracteres'];
    }
    
    if (strlen($nombre) > 100) {
        return ['valido' => false, 'mensaje' => 'El nombre no puede tener más de 100 caracteres'];
    }
    
    if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u", $nombre)) {
        return ['valido' => false, 'mensaje' => 'El nombre solo puede contener letras y espacios'];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function validarPassword($password, $requerido = true) {
    if (empty($password)) {
        if ($requerido) {
            return ['valido' => false, 'mensaje' => 'La contraseña es requerida'];
        } else {
            return ['valido' => true, 'mensaje' => '']; // Opcional
        }
    }
    
    if (strlen($password) < 6) {
        return ['valido' => false, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres'];
    }
    
    if (strlen($password) > 50) {
        return ['valido' => false, 'mensaje' => 'La contraseña no puede tener más de 50 caracteres'];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function validarTelefono($telefono) {
    if (empty($telefono)) {
        return ['valido' => true, 'mensaje' => '']; // Opcional
    }
    
    // Eliminar espacios, guiones y paréntesis
    $telefono_limpio = preg_replace('/[\s\-\(\)]/', '', $telefono);
    
    if (!preg_match('/^[0-9]{7,15}$/', $telefono_limpio)) {
        return ['valido' => false, 'mensaje' => 'El teléfono debe contener entre 7 y 15 dígitos'];
    }
    
    if (strlen($telefono) > 20) {
        return ['valido' => false, 'mensaje' => 'El teléfono no puede tener más de 20 caracteres'];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function validarMonto($monto) {
    if (empty($monto) && $monto !== '0') {
        return ['valido' => false, 'mensaje' => 'El monto es requerido'];
    }
    
    if (!is_numeric($monto)) {
        return ['valido' => false, 'mensaje' => 'El monto debe ser un número válido'];
    }
    
    $monto_float = floatval($monto);
    
    if ($monto_float < 0) {
        return ['valido' => false, 'mensaje' => 'El monto no puede ser negativo'];
    }
    
    if ($monto_float > 999999.99) {
        return ['valido' => false, 'mensaje' => 'El monto no puede ser mayor a 999,999.99'];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function validarFecha($fecha, $campo = 'fecha') {
    if (empty($fecha)) {
        return ['valido' => false, 'mensaje' => "La {$campo} es requerida"];
    }
    
    $fecha_array = explode('-', $fecha);
    if (count($fecha_array) !== 3 || !checkdate($fecha_array[1], $fecha_array[2], $fecha_array[0])) {
        return ['valido' => false, 'mensaje' => "La {$campo} no es válida"];
    }
    
    // Validar que la fecha no sea futura (para fecha de nacimiento)
    if ($campo === 'fecha de nacimiento') {
        $fecha_obj = new DateTime($fecha);
        $hoy = new DateTime();
        if ($fecha_obj > $hoy) {
            return ['valido' => false, 'mensaje' => 'La fecha de nacimiento no puede ser futura'];
        }
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function validarArchivo($archivo, $tipos_permitidos = ['jpg', 'jpeg', 'png'], $tamaño_max_mb = 100) {
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['valido' => true, 'mensaje' => '']; // Opcional
    }
    
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['valido' => false, 'mensaje' => 'Error al subir el archivo'];
    }
    
    $tamaño_max = $tamaño_max_mb * 1024 * 1024; // Convertir MB a bytes
    if ($archivo['size'] > $tamaño_max) {
        return ['valido' => false, 'mensaje' => "El archivo es demasiado grande. Tamaño máximo: {$tamaño_max_mb}MB"];
    }
    
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $tipos_permitidos)) {
        $tipos_str = implode(', ', $tipos_permitidos);
        return ['valido' => false, 'mensaje' => "Formato no permitido. Formatos permitidos: {$tipos_str}"];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function validarTexto($texto, $campo = 'texto', $max_longitud = 1000, $requerido = false) {
    if (empty($texto)) {
        if ($requerido) {
            return ['valido' => false, 'mensaje' => "El {$campo} es requerido"];
        } else {
            return ['valido' => true, 'mensaje' => '']; // Opcional
        }
    }
    
    if (strlen($texto) > $max_longitud) {
        return ['valido' => false, 'mensaje' => "El {$campo} no puede tener más de {$max_longitud} caracteres"];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

function sanitizarInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validarEmailUnico($conn, $email, $usuario_id_excluir = null) {
    if ($usuario_id_excluir) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $usuario_id_excluir);
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result->num_rows > 0) {
        return ['valido' => false, 'mensaje' => 'Este email ya está registrado'];
    }
    
    return ['valido' => true, 'mensaje' => ''];
}
?>









