# Guía de Migración - Sistema de Cobros Tiny Steps

## 📋 Preparación para el Hosting

### Paso 1: Backup de la Base de Datos

1. Ejecuta el script de backup:
   ```
   http://localhost/Sistema de Cobros Tiny Steps/backup_database.php
   ```
   
2. Descarga el archivo `.sql` generado y guárdalo en un lugar seguro.

### Paso 2: Preparar Archivos

#### Archivos a subir:
- ✅ Todos los archivos PHP
- ✅ Carpeta `assets/` (CSS, JS, imágenes)
- ✅ Carpeta `config/` (pero NO subir `database.php` con credenciales locales)
- ✅ Carpeta `uploads/` (si tiene contenido)
- ✅ Archivo `.htaccess`

#### Archivos que NO debes subir:
- ❌ `backup_database.php` (solo para desarrollo)
- ❌ `update_admin_password.php` (solo para desarrollo)
- ❌ Archivos `.sql` de backup
- ❌ `install.php` (si ya está instalado)
- ❌ Archivos de log (`.txt` en carpeta `logs/`)

### Paso 3: Configurar Base de Datos en el Hosting

1. **Crear la base de datos en el hosting:**
   - Accede al panel de control (cPanel, Plesk, etc.)
   - Crea una nueva base de datos MySQL
   - Anota el nombre de la base de datos, usuario y contraseña

2. **Importar el backup:**
   - Usa phpMyAdmin o el importador del hosting
   - Importa el archivo `.sql` que descargaste

### Paso 4: Configurar `config/database.php`

Edita el archivo `config/database.php` con las credenciales del hosting:

```php
<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost'); // O la IP que te proporcione el hosting
define('DB_USER', 'usuario_del_hosting');
define('DB_PASS', 'contraseña_del_hosting');
define('DB_NAME', 'nombre_base_datos_hosting');

// Conexión a la base de datos
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8");
        return $conn;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
```

### Paso 5: Configurar Permisos de Carpetas

En el hosting, asegúrate de que estas carpetas tengan permisos de escritura (755 o 777):

```bash
uploads/comprobantes/  → 755 o 777
logs/                  → 755 o 777
```

### Paso 6: Verificar Configuración PHP

Asegúrate de que el hosting tenga:
- ✅ PHP 7.4 o superior
- ✅ Extensión MySQLi habilitada
- ✅ `upload_max_filesize` mínimo 5MB
- ✅ `post_max_size` mínimo 5MB

### Paso 7: Probar el Sistema

1. Accede a la URL del hosting
2. Prueba iniciar sesión con:
   - Email: `admin@tinysteps.com`
   - Contraseña: `tinyvicentina789`
3. Verifica que todas las funcionalidades trabajen correctamente

## 🔒 Seguridad Post-Migración

### Cambiar Contraseña del Administrador

Después de migrar, cambia la contraseña del administrador desde el panel.

### Verificar Archivos Sensibles

Asegúrate de que estos archivos NO sean accesibles públicamente:
- `config/database.php`
- `backup_*.sql`
- Archivos en `database/`

El archivo `.htaccess` ya incluye protecciones para esto.

## 📝 Checklist de Migración

- [ ] Backup de base de datos creado y descargado
- [ ] Archivos subidos al hosting (excepto los que no deben subirse)
- [ ] Base de datos creada en el hosting
- [ ] Backup importado en la base de datos del hosting
- [ ] `config/database.php` actualizado con credenciales del hosting
- [ ] Permisos de carpetas configurados (uploads, logs)
- [ ] `.htaccess` subido y funcionando
- [ ] Sistema probado y funcionando correctamente
- [ ] Contraseña de administrador cambiada

## 🆘 Solución de Problemas

### Error de conexión a la base de datos
- Verifica las credenciales en `config/database.php`
- Asegúrate de que el host sea correcto (puede ser `localhost` o una IP específica)

### Error 500
- Revisa los logs de error del hosting
- Verifica permisos de archivos y carpetas
- Asegúrate de que PHP tenga las extensiones necesarias

### Imágenes no se suben
- Verifica permisos de la carpeta `uploads/comprobantes/`
- Revisa `upload_max_filesize` en PHP

### Página en blanco
- Activa el display de errores temporalmente
- Revisa los logs de PHP del hosting

## 📞 Soporte

Si tienes problemas durante la migración, verifica:
1. Logs de error del hosting
2. Configuración de PHP
3. Permisos de archivos y carpetas
4. Credenciales de la base de datos
