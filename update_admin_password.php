<?php
require_once 'config/database.php';

// Nueva contraseña
$nueva_password = 'tinyvicentina789';
$email_admin = 'admin@tinysteps.com';

// Generar hash de la contraseña
$password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);

try {
    $conn = getDBConnection();
    
    // Actualizar la contraseña del administrador
    $stmt = $conn->prepare("UPDATE usuarios SET password = ? WHERE email = ? AND tipo = 'admin'");
    $stmt->bind_param("ss", $password_hash, $email_admin);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Contraseña Actualizada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .success {
            color: #10B981;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .info {
            background: #F3F4F6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #6366F1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='success'>✓ Contraseña actualizada exitosamente</div>
        <div class='info'>
            <strong>Email:</strong> admin@tinysteps.com<br>
            <strong>Nueva Contraseña:</strong> tinyvicentina789
        </div>
        <p>Ahora puedes iniciar sesión con la nueva contraseña.</p>
        <a href='index.php' class='btn'>Ir al Login</a>
    </div>
</body>
</html>";
        } else {
            echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .error {
            color: #EF4444;
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='error'>⚠ No se encontró el usuario administrador</div>
        <p>Verifica que el email 'admin@tinysteps.com' exista en la base de datos.</p>
    </div>
</body>
</html>";
        }
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $conn->error);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
        }
        .error {
            color: #EF4444;
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='error'>✗ Error</div>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
    </div>
</body>
</html>";
}
?>
