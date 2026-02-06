<?php
/**
 * Script para obtener un link público temporal
 * Usa servicios de túnel gratuitos para exponer tu localhost
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obtener Link Público - Tiny Steps</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
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
        .opcion {
            background: #F3F4F6;
            padding: 25px;
            margin: 20px 0;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .opcion h2 {
            color: #667eea;
            margin-top: 0;
        }
        .pasos {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .pasos ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        .pasos li {
            margin: 8px 0;
            line-height: 1.6;
        }
        .codigo {
            background: #1F2937;
            color: #10B981;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
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
        .btn:hover {
            background: #4F46E5;
        }
        .advertencia {
            background: #FEF3C7;
            border-left: 4px solid #F59E0B;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .exito {
            background: #D1FAE5;
            border-left: 4px solid #10B981;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌐 Obtener Link Público para tu Sistema</h1>
        
        <div class="opcion">
            <h2>Opción 1: Link Local (Solo en tu computadora/red)</h2>
            <div class="pasos">
                <p><strong>URL Local:</strong></p>
                <div class="codigo">
                    http://localhost/Sistema%20de%20Cobros%20Tiny%20Steps/
                </div>
                <p><strong>O también:</strong></p>
                <div class="codigo">
                    http://127.0.0.1/Sistema%20de%20Cobros%20Tiny%20Steps/
                </div>
                <div class="advertencia">
                    ⚠️ Esta URL solo funciona en tu computadora. No es accesible desde internet.
                </div>
            </div>
        </div>
        
        <div class="opcion">
            <h2>Opción 2: LocalTunnel (Gratis, Sin Instalación)</h2>
            <div class="pasos">
                <p><strong>Pasos:</strong></p>
                <ol>
                    <li>Abre una terminal/consola en tu computadora</li>
                    <li>Instala Node.js si no lo tienes: <a href="https://nodejs.org/" target="_blank">Descargar Node.js</a></li>
                    <li>Instala localtunnel ejecutando:</li>
                </ol>
                <div class="codigo">
                    npm install -g localtunnel
                </div>
                <ol start="4">
                    <li>Inicia XAMPP (Apache debe estar corriendo)</li>
                    <li>Ejecuta este comando:</li>
                </ol>
                <div class="codigo">
                    lt --port 80
                </div>
                <p>O si XAMPP usa otro puerto:</p>
                <div class="codigo">
                    lt --port 8080
                </div>
                <ol start="6">
                    <li>Te dará un link como: <code>https://xxxxx.loca.lt</code></li>
                    <li>Ese es tu link público temporal</li>
                </ol>
                <div class="exito">
                    ✅ Ventaja: Gratis, rápido, sin registro
                </div>
            </div>
        </div>
        
        <div class="opcion">
            <h2>Opción 3: ngrok (Más Estable, Requiere Registro)</h2>
            <div class="pasos">
                <p><strong>Pasos:</strong></p>
                <ol>
                    <li>Regístrate en: <a href="https://ngrok.com/" target="_blank">ngrok.com</a></li>
                    <li>Descarga ngrok</li>
                    <li>Extrae el archivo ngrok.exe</li>
                    <li>Inicia XAMPP (Apache debe estar corriendo)</li>
                    <li>Abre una terminal en la carpeta de ngrok y ejecuta:</li>
                </ol>
                <div class="codigo">
                    ngrok http 80
                </div>
                <p>O si XAMPP usa otro puerto:</p>
                <div class="codigo">
                    ngrok http 8080
                </div>
                <ol start="6">
                    <li>Te dará un link como: <code>https://xxxxx.ngrok.io</code></li>
                    <li>Ese es tu link público</li>
                </ol>
                <div class="exito">
                    ✅ Ventaja: Más estable, puedes usar el mismo link
                </div>
            </div>
        </div>
        
        <div class="opcion">
            <h2>Opción 4: ServidorPHP (Muy Rápido, Sin Instalación)</h2>
            <div class="pasos">
                <p><strong>Pasos:</strong></p>
                <ol>
                    <li>Ve a: <a href="https://www.serveo.net/" target="_blank">serveo.net</a></li>
                    <li>O abre una terminal y ejecuta:</li>
                </ol>
                <div class="codigo">
                    ssh -R 80:localhost:80 serveo.net
                </div>
                <p>O si XAMPP usa otro puerto:</p>
                <div class="codigo">
                    ssh -R 80:localhost:8080 serveo.net
                </div>
                <ol start="3">
                    <li>Te dará un link público</li>
                </ol>
                <div class="exito">
                    ✅ Ventaja: No requiere instalación, funciona desde terminal
                </div>
            </div>
        </div>
        
        <div class="opcion">
            <h2>Opción 5: Subir a Hosting Gratuito (Permanente)</h2>
            <div class="pasos">
                <p><strong>Opciones de hosting gratuito:</strong></p>
                <ul>
                    <li><strong>000webhost:</strong> <a href="https://www.000webhost.com/" target="_blank">000webhost.com</a></li>
                    <li><strong>InfinityFree:</strong> <a href="https://www.infinityfree.net/" target="_blank">infinityfree.net</a></li>
                    <li><strong>Freehostia:</strong> <a href="https://www.freehostia.com/" target="_blank">freehostia.com</a></li>
                </ul>
                <p><strong>Pasos rápidos:</strong></p>
                <ol>
                    <li>Regístrate en uno de estos servicios</li>
                    <li>Crea la base de datos MySQL</li>
                    <li>Sube los archivos usando FileZilla o el administrador de archivos</li>
                    <li>Importa el backup de la base de datos</li>
                    <li>Configura config/database.php</li>
                </ol>
                <div class="advertencia">
                    ⚠️ Esto toma más tiempo (30-60 minutos) pero es permanente
                </div>
            </div>
        </div>
        
        <div class="advertencia" style="margin-top: 30px;">
            <strong>⚠️ Importante:</strong>
            <ul>
                <li>Asegúrate de que XAMPP/Apache esté corriendo antes de usar los túneles</li>
                <li>Los links de túneles son temporales (se renuevan cada vez que los inicias)</li>
                <li>Para un link permanente, usa un hosting</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">Ir al Sistema</a>
            <a href="backup_database.php" class="btn">Crear Backup</a>
        </div>
    </div>
</body>
</html>
