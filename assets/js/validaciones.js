// Validaciones del lado del cliente (JavaScript)

function mostrarError(campo, mensaje) {
    const errorElement = document.getElementById('error_' + campo);
    if (errorElement) {
        errorElement.textContent = mensaje;
        errorElement.style.color = '#dc3545';
        errorElement.style.display = 'block';
        errorElement.style.fontSize = '0.85em';
        errorElement.style.marginTop = '5px';
    }
    
    const inputElement = document.getElementById(campo);
    if (inputElement) {
        inputElement.style.borderColor = '#dc3545';
    }
}

function limpiarError(campo) {
    const errorElement = document.getElementById('error_' + campo);
    if (errorElement) {
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }
    
    const inputElement = document.getElementById(campo);
    if (inputElement) {
        inputElement.style.borderColor = '';
    }
}

function validarNombreInput(input) {
    const nombre = input.value.trim();
    limpiarError('nombre');
    
    if (nombre.length < 2) {
        mostrarError('nombre', 'El nombre debe tener al menos 2 caracteres');
        return false;
    }
    
    if (nombre.length > 100) {
        mostrarError('nombre', 'El nombre no puede tener más de 100 caracteres');
        return false;
    }
    
    if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre)) {
        mostrarError('nombre', 'El nombre solo puede contener letras y espacios');
        return false;
    }
    
    return true;
}

function validarEmailInput(input) {
    const email = input.value.trim();
    limpiarError('email');
    
    if (email.length === 0) {
        mostrarError('email', 'El email es requerido');
        return false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        mostrarError('email', 'El formato del email no es válido');
        return false;
    }
    
    if (email.length > 100) {
        mostrarError('email', 'El email no puede tener más de 100 caracteres');
        return false;
    }
    
    return true;
}

function validarPasswordInput(input, requerido = true) {
    const password = input.value;
    limpiarError('password');
    
    if (password.length === 0) {
        if (requerido) {
            mostrarError('password', 'La contraseña es requerida');
            return false;
        } else {
            return true; // Opcional
        }
    }
    
    if (password.length < 6) {
        mostrarError('password', 'La contraseña debe tener al menos 6 caracteres');
        return false;
    }
    
    if (password.length > 50) {
        mostrarError('password', 'La contraseña no puede tener más de 50 caracteres');
        return false;
    }
    
    return true;
}

function validarTelefonoInput(input) {
    const telefono = input.value.trim();
    limpiarError('telefono');
    
    if (telefono.length === 0) {
        return true; // Opcional
    }
    
    const telefonoLimpio = telefono.replace(/[\s\-\(\)]/g, '');
    if (!/^[0-9]{7,15}$/.test(telefonoLimpio)) {
        mostrarError('telefono', 'El teléfono debe contener entre 7 y 15 dígitos');
        return false;
    }
    
    if (telefono.length > 20) {
        mostrarError('telefono', 'El teléfono no puede tener más de 20 caracteres');
        return false;
    }
    
    return true;
}

function validarMontoInput(input) {
    const monto = input.value.trim();
    limpiarError('monto');
    
    if (monto.length === 0) {
        mostrarError('monto', 'El monto es requerido');
        return false;
    }
    
    const montoNum = parseFloat(monto);
    if (isNaN(montoNum)) {
        mostrarError('monto', 'El monto debe ser un número válido');
        return false;
    }
    
    if (montoNum < 0) {
        mostrarError('monto', 'El monto no puede ser negativo');
        return false;
    }
    
    if (montoNum > 999999.99) {
        mostrarError('monto', 'El monto no puede ser mayor a 999,999.99');
        return false;
    }
    
    return true;
}

function validarFormularioCrearUsuario() {
    let valido = true;
    
    valido = validarNombreInput(document.getElementById('nombre')) && valido;
    valido = validarEmailInput(document.getElementById('email')) && valido;
    valido = validarPasswordInput(document.getElementById('password'), true) && valido;
    valido = validarTelefonoInput(document.getElementById('telefono')) && valido;
    
    return valido;
}

function validarFormularioEditarUsuario() {
    let valido = true;
    
    valido = validarNombreInput(document.getElementById('nombre')) && valido;
    valido = validarEmailInput(document.getElementById('email')) && valido;
    
    const passwordInput = document.getElementById('password');
    if (passwordInput && passwordInput.value.length > 0) {
        valido = validarPasswordInput(passwordInput, true) && valido;
    }
    
    valido = validarTelefonoInput(document.getElementById('telefono')) && valido;
    
    return valido;
}

function validarFormularioSubirPago() {
    let valido = true;
    
    const ninoSelect = document.getElementById('nino_id');
    if (!ninoSelect || ninoSelect.value === '') {
        alert('Debe seleccionar un niño');
        valido = false;
    }
    
    const montoInput = document.getElementById('monto');
    if (montoInput) {
        valido = validarMontoInput(montoInput) && valido;
    }
    
    const cuentaSelect = document.getElementById('numero_cuenta');
    if (!cuentaSelect || cuentaSelect.value === '') {
        alert('Debe seleccionar una cuenta bancaria');
        valido = false;
    }
    
    const fechaInput = document.getElementById('fecha_transaccion');
    if (!fechaInput || fechaInput.value === '') {
        alert('La fecha de transacción es requerida');
        valido = false;
    }
    
    const archivoInput = document.getElementById('comprobante');
    if (archivoInput && archivoInput.files.length > 0) {
        const archivo = archivoInput.files[0];
        const maxSize = 100 * 1024 * 1024; // 100MB
        
        if (archivo.size > maxSize) {
            alert('El archivo es demasiado grande. Tamaño máximo: 100MB');
            valido = false;
        }
        
        const extension = archivo.name.split('.').pop().toLowerCase();
        const extensionesPermitidas = ['jpg', 'jpeg', 'png'];
        if (!extensionesPermitidas.includes(extension)) {
            alert('Formato no permitido. Formatos permitidos: JPG, PNG');
            valido = false;
        }
    }
    
    return valido;
}

// Agregar validación al formulario de subir pago si existe
document.addEventListener('DOMContentLoaded', function() {
    const formSubirPago = document.querySelector('form[method="POST"][enctype="multipart/form-data"]');
    if (formSubirPago && document.getElementById('monto')) {
        formSubirPago.addEventListener('submit', function(e) {
            if (!validarFormularioSubirPago()) {
                e.preventDefault();
                return false;
            }
        });
    }
});









