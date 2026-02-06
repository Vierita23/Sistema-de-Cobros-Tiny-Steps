<?php
/**
 * Script para crear un paquete ZIP listo para migración
 * Excluye archivos sensibles y de desarrollo
 */

$archivos_excluir = [
    'backup_database.php',
    'update_admin_password.php',
    'verificar_hosting.php',
    'crear_paquete_migracion.php',
    '.git',
    '.gitignore',
    'MIGRACION.md',
    'README.md',
    'docs/',
    'logs/*.txt',
    'logs/*.log',
    'backup_*.sql',
    '*.sql',
    'config/database.php', // No incluir con credenciales locales
];

function crearZip($directorio, $zip, $excluir = []) {
    $archivos = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directorio),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($archivos as $archivo) {
        if (!$archivo->isDir()) {
            $rutaArchivo = $archivo->getRealPath();
            $rutaRelativa = str_replace($directorio . DIRECTORY_SEPARATOR, '', $rutaArchivo);
            
            // Verificar si debe excluirse
            $excluir_archivo = false;
            foreach ($excluir as $patron) {
                if (fnmatch($patron, $rutaRelativa) || strpos($rutaRelativa, $patron) === 0) {
                    $excluir_archivo = true;
                    break;
                }
            }
            
            if (!$excluir_archivo) {
                $zip->addFile($rutaArchivo, $rutaRelativa);
            }
        }
    }
}

$nombre_paquete = 'tiny_steps_cobros_migracion_' . date('Y-m-d_H-i-s') . '.zip';
$directorio_actual = __DIR__;

$zip = new ZipArchive();
if ($zip->open($nombre_paquete, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    crearZip($directorio_actual, $zip, $archivos_excluir);
    $zip->close();
    
    echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Paquete Creado</title>
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
            text-align: left;
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
        .lista {
            text-align: left;
            margin: 15px 0;
        }
        .lista li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='success'>✓ Paquete creado exitosamente</div>
        <div class='info'>
            <strong>Archivo generado:</strong><br>
            $nombre_paquete
        </div>
        <div class='lista'>
            <strong>Este paquete incluye:</strong>
            <ul>
                <li>✓ Todos los archivos PHP del sistema</li>
                <li>✓ Carpetas assets/ (CSS, JS, imágenes)</li>
                <li>✓ Carpeta config/ (sin database.php)</li>
                <li>✓ Archivo .htaccess</li>
                <li>✓ Carpeta uploads/ (estructura)</li>
            </ul>
            <strong>NO incluye:</strong>
            <ul>
                <li>✗ Archivos de backup (.sql)</li>
                <li>✗ Scripts de desarrollo</li>
                <li>✗ config/database.php (debes crearlo en el hosting)</li>
                <li>✗ Logs</li>
            </ul>
        </div>
        <p><strong>Próximos pasos:</strong></p>
        <ol style='text-align: left; display: inline-block;'>
            <li>Descarga este ZIP</li>
            <li>Descomprímelo en tu computadora</li>
            <li>Crea config/database.php con las credenciales del hosting</li>
            <li>Sube todo al hosting</li>
            <li>Importa el backup de la base de datos</li>
        </ol>
        <a href='$nombre_paquete' class='btn' download>Descargar Paquete ZIP</a>
    </div>
</body>
</html>";
} else {
    echo "Error al crear el archivo ZIP. Asegúrate de que la extensión ZipArchive esté habilitada en PHP.";
}
?>
