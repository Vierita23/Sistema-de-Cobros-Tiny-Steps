<?php
// Versión de prueba sin base de datos
session_start();

// Si ya está logueado, redirigir
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Credenciales de prueba (sin base de datos)
    if ($email === 'admin@tinysteps.com' && $password === 'tinyvicentina789') {
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = 'Administrador';
        $_SESSION['user_email'] = 'admin@tinysteps.com';
        $_SESSION['user_type'] = 'admin';
        header('Location: admin/dashboard.php');
        exit();
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Tiny Steps</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo-section">
                <h1 class="logo-title">
                    <span>T</span><span>i</span><span>n</span><span>y</span>
                    <span>S</span><span>t</span><span>e</span><span>p</span><span>s</span>
                </h1>
                <p class="logo-subtitle">Centro de Desarrollo Infantil</p>
            </div>
            
            <form method="POST" action="" class="login-form">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #FEF3C7; border-radius: 8px; text-align: center;">
                <strong>Modo de Prueba (sin base de datos)</strong><br>
                Email: admin@tinysteps.com<br>
                Contraseña: tinyvicentina789
            </div>
        </div>
    </div>
</body>
</html>
