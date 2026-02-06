<?php
/**
 * Script para iniciar LocalTunnel y obtener link público
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Túnel - Tiny Steps</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .instrucciones {
            background: #F3F4F6;
            padding: 25px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .codigo {
            background: #1F2937;
            color: #10B981;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #6366F1;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 10px 5px;
            font-weight: 600;
        }
        .exito {
            background: #D1FAE5;
            border-left: 4px solid #10B981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌐 Iniciar Túnel Público</h1>
        
        <div class="instrucciones">
            <h2>Pasos para obtener tu link público:</h2>
            
            <p><strong>1. Abre una nueva terminal/CMD</strong></p>
            
            <p><strong>2. Verifica qué puerto usa XAMPP:</strong></p>
            <ul>
                <li>Abre el Panel de Control de XAMPP</li>
                <li>Mira el puerto de Apache (normalmente 80 o 8080)</li>
            </ul>
            
            <p><strong>3. Ejecuta uno de estos comandos:</strong></p>
            
            <p>Si Apache usa el puerto <strong>80</strong>:</p>
            <div class="codigo">lt --port 80</div>
            
            <p>Si Apache usa el puerto <strong>8080</strong>:</p>
            <div class="codigo">lt --port 8080</div>
            
            <p><strong>4. Te dará un link como:</strong></p>
            <div class="codigo" style="background: #10B981; color: white; font-weight: bold;">
                https://xxxxx.loca.lt
            </div>
            
            <p><strong>5. Ese es tu link público. Compártelo con quien necesites.</strong></p>
        </div>
        
        <div class="exito">
            <strong>✅ LocalTunnel ya está instalado</strong><br>
            Solo necesitas ejecutar el comando en la terminal.
        </div>
        
        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" class="btn">Ir al Sistema</a>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #FEF3C7; border-radius: 8px;">
            <strong>💡 Nota:</strong><br>
            - El link se renueva cada vez que inicias el túnel<br>
            - Mantén la terminal abierta mientras uses el link<br>
            - Para un link permanente, sube el sistema a un hosting
        </div>
    </div>
</body>
</html>
