<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Verificar si el usuario es admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

// Verificar si el usuario es padre
function isPadre() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'padre';
}

// Redirigir si no está autenticado
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../index.php');
        exit();
    }
}

// Redirigir si no es admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../user/dashboard.php');
        exit();
    }
}

// Redirigir si no es padre
function requirePadre() {
    requireLogin();
    if (!isPadre()) {
        header('Location: ../admin/dashboard.php');
        exit();
    }
}

