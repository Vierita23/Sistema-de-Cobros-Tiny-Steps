<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Si ya está logueado, redirigir según su tipo
if (isLoggedIn()) {
    if (isAdmin()) {
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
    
    if (!empty($email) && !empty($password)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, nombre, email, password, tipo FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['tipo'];
                
                if ($user['tipo'] === 'admin') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: user/dashboard.php');
                }
                exit();
            } else {
                $error = 'Credenciales incorrectas';
            }
        } else {
            $error = 'Credenciales incorrectas';
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $error = 'Por favor completa todos los campos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Tiny Steps - Sistema de Gestión</title>
    <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
    <link rel="alternate icon" href="assets/favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="logo-section">
                <h1 class="logo-title">
                    <span style="color: #FF00FF;">T</span>
                    <span style="color: #00FF00;">i</span>
                    <span style="color: #0000FF;">n</span>
                    <span style="color: #FF0000;">y</span>
                    <span style="color: #FFFF00;">S</span>
                    <span style="color: #00FF00;">t</span>
                    <span style="color: #FF0000;">e</span>
                    <span style="color: #00BFFF;">p</span>
                    <span style="color: #FF00FF;">s</span>
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
        </div>
    </div>
</body>
</html>





