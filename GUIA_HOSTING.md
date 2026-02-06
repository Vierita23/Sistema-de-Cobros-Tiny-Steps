# Guía Completa: Subir Sistema a Hosting

## 🌐 Opciones de Hosting Recomendadas

### Opción 1: Hosting Gratuito (Para empezar)

#### **000webhost** (Recomendado - Gratis)
- **URL:** https://www.000webhost.com/
- **Ventajas:**
  - ✅ Completamente gratis
  - ✅ Sin publicidad forzada
  - ✅ Base de datos MySQL incluida
  - ✅ Panel de control fácil (cPanel)
  - ✅ 300 MB de espacio
- **Desventajas:**
  - ⚠️ Puede ser lento a veces
  - ⚠️ Subdominio tipo: `tusistema.000webhostapp.com`

#### **InfinityFree** (Gratis)
- **URL:** https://www.infinityfree.net/
- **Ventajas:**
  - ✅ Ilimitado (espacio y ancho de banda)
  - ✅ Base de datos MySQL
  - ✅ Panel de control
- **Desventajas:**
  - ⚠️ Puede tener publicidad
  - ⚠️ Subdominio tipo: `tusistema.rf.gd`

#### **Freehostia** (Gratis)
- **URL:** https://www.freehostia.com/
- **Ventajas:**
  - ✅ 250 MB de espacio
  - ✅ Base de datos MySQL
  - ✅ Panel de control

### Opción 2: Hosting de Pago (Profesional)

#### **Hostinger** (Recomendado - Económico)
- **URL:** https://www.hostinger.com/
- **Precio:** Desde $2.99/mes
- **Ventajas:**
  - ✅ Muy rápido y estable
  - ✅ Dominio gratis el primer año
  - ✅ Soporte 24/7
  - ✅ SSL gratuito
  - ✅ Panel de control moderno

#### **Namecheap** (Económico)
- **URL:** https://www.namecheap.com/
- **Precio:** Desde $1.88/mes
- **Ventajas:**
  - ✅ Buen precio
  - ✅ Dominio incluido
  - ✅ SSL gratuito

## 📋 Pasos para Subir el Sistema

### PASO 1: Crear Backup de Base de Datos
1. Ejecuta: `http://localhost/Sistema de Cobros Tiny Steps/backup_database.php`
2. Descarga el archivo `.sql` generado

### PASO 2: Crear Paquete para Subir
1. Ejecuta: `http://localhost/Sistema de Cobros Tiny Steps/crear_paquete_migracion.php`
2. Descarga el archivo ZIP generado

### PASO 3: Registrarse en el Hosting
1. Elige uno de los hostings de arriba
2. Regístrate (gratis o de pago)
3. Crea tu cuenta

### PASO 4: Crear Base de Datos en el Hosting
1. Accede al panel de control (cPanel o similar)
2. Busca "MySQL Databases" o "Bases de Datos"
3. Crea una nueva base de datos
4. Crea un usuario para la base de datos
5. Asigna el usuario a la base de datos
6. **Anota:** nombre de BD, usuario, contraseña, host (normalmente `localhost`)

### PASO 5: Importar Base de Datos
1. Busca "phpMyAdmin" en el panel
2. Selecciona tu base de datos
3. Ve a la pestaña "Importar"
4. Sube el archivo `.sql` que descargaste
5. Haz clic en "Continuar"

### PASO 6: Subir Archivos
1. Busca "File Manager" o "Administrador de Archivos"
2. Ve a la carpeta `public_html` o `htdocs`
3. Sube el ZIP que descargaste
4. Extrae el ZIP en esa carpeta
5. O sube los archivos directamente

### PASO 7: Configurar database.php
1. En el File Manager, ve a `config/database.php`
2. Edítalo con las credenciales del hosting:
   ```php
   define('DB_HOST', 'localhost'); // O la IP que te dé el hosting
   define('DB_USER', 'usuario_del_hosting');
   define('DB_PASS', 'contraseña_del_hosting');
   define('DB_NAME', 'nombre_bd_del_hosting');
   ```

### PASO 8: Configurar Permisos
1. En File Manager, ve a las carpetas:
   - `uploads/comprobantes/` → Permisos 755 o 777
   - `logs/` → Permisos 755 o 777

### PASO 9: Probar el Sistema
1. Accede a tu dominio/subdominio
2. Prueba iniciar sesión:
   - Email: `admin@tinysteps.com`
   - Contraseña: `tinyvicentina789`

## ✅ Checklist de Migración

- [ ] Backup de base de datos creado
- [ ] Paquete ZIP creado
- [ ] Cuenta de hosting creada
- [ ] Base de datos MySQL creada en el hosting
- [ ] Backup importado en phpMyAdmin
- [ ] Archivos subidos al hosting
- [ ] `config/database.php` actualizado con credenciales del hosting
- [ ] Permisos de carpetas configurados
- [ ] Sistema probado y funcionando

## 🆘 Si Tienes Problemas

1. **Error 500:** Verifica `config/database.php` y permisos
2. **No conecta a BD:** Verifica credenciales y host
3. **Página en blanco:** Revisa logs de error del hosting
4. **Archivos no se suben:** Verifica permisos de `uploads/`
