# Guía Paso a Paso: Subir Proyecto a Hosting

## 📦 PASO 1: Preparar Archivos (Hacer AHORA)

### 1.1 Crear Backup de Base de Datos
1. Abre en tu navegador:
   ```
   http://localhost/Sistema de Cobros Tiny Steps/backup_database.php
   ```
2. Descarga el archivo `.sql` que se genera
3. Guárdalo en un lugar seguro

### 1.2 Crear Paquete ZIP
1. Abre en tu navegador:
   ```
   http://localhost/Sistema de Cobros Tiny Steps/crear_paquete_migracion.php
   ```
2. Descarga el archivo ZIP generado
3. Guárdalo en un lugar fácil de encontrar

---

## 🌐 PASO 2: Registrarse en Hostinger

### 2.1 Crear Cuenta
1. Ve a: https://www.hostinger.com/
2. Haz clic en "Get Started" o "Empezar"
3. Elige un plan (recomendado: **Premium $1.99/mes**)
4. Completa el registro con tu email
5. Confirma tu email

### 2.2 Acceder al Panel
1. Inicia sesión en Hostinger
2. Accede al panel de control (hPanel)

---

## 🗄️ PASO 3: Crear Base de Datos MySQL

### 3.1 En el Panel de Hostinger
1. Busca la sección **"Bases de Datos"** o **"MySQL Databases"**
2. Haz clic en **"Crear Base de Datos"** o **"Create Database"**

### 3.2 Configurar Base de Datos
1. **Nombre de la base de datos:** `tinysteps_cobros` (o el que prefieras)
2. **Usuario:** Se crea automáticamente o puedes crear uno
3. **Contraseña:** Crea una contraseña segura
4. Haz clic en **"Crear"**

### 3.3 Anotar Credenciales
**IMPORTANTE:** Anota estos datos (los necesitarás después):
- **Host:** Normalmente `localhost` (o te lo dirá el hosting)
- **Nombre de BD:** `tinysteps_cobros` (o el que creaste)
- **Usuario:** El usuario que creaste
- **Contraseña:** La contraseña que pusiste

---

## 📥 PASO 4: Importar Base de Datos

### 4.1 Acceder a phpMyAdmin
1. En el panel de Hostinger, busca **"phpMyAdmin"**
2. Haz clic para abrirlo

### 4.2 Importar el Backup
1. En phpMyAdmin, selecciona tu base de datos (izquierda)
2. Ve a la pestaña **"Importar"** o **"Import"**
3. Haz clic en **"Elegir archivo"** o **"Choose File"**
4. Selecciona el archivo `.sql` que descargaste
5. Haz clic en **"Continuar"** o **"Go"**
6. Espera a que termine la importación

---

## 📤 PASO 5: Subir Archivos del Sistema

### 5.1 Acceder al File Manager
1. En el panel de Hostinger, busca **"File Manager"** o **"Administrador de Archivos"**
2. Haz clic para abrirlo

### 5.2 Navegar a la Carpeta Correcta
1. Ve a la carpeta **`public_html`** (esta es la carpeta raíz de tu sitio)
2. Si no existe, créala

### 5.3 Subir el ZIP
**Opción A: Subir ZIP y Extraer**
1. Haz clic en **"Subir"** o **"Upload"**
2. Selecciona el archivo ZIP que descargaste
3. Espera a que termine la subida
4. Haz clic derecho en el ZIP → **"Extraer"** o **"Extract"**
5. Extrae en `public_html`

**Opción B: Subir Archivos Directamente**
1. Descomprime el ZIP en tu computadora
2. Sube todos los archivos y carpetas a `public_html`
3. Mantén la estructura de carpetas (admin/, user/, config/, etc.)

---

## ⚙️ PASO 6: Configurar database.php

### 6.1 Editar el Archivo
1. En File Manager, ve a: `public_html/config/database.php`
2. Haz clic derecho → **"Editar"** o **"Edit"**

### 6.2 Actualizar Credenciales
Reemplaza con las credenciales de tu hosting:

```php
<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost'); // O la IP que te dé Hostinger
define('DB_USER', 'usuario_que_creaste'); // Usuario de la BD
define('DB_PASS', 'contraseña_que_creaste'); // Contraseña de la BD
define('DB_NAME', 'tinysteps_cobros'); // Nombre de la BD

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

### 6.3 Guardar
1. Haz clic en **"Guardar"** o **"Save"**

---

## 🔐 PASO 7: Configurar Permisos de Carpetas

### 7.1 Configurar Permisos
1. En File Manager, ve a la carpeta `public_html/uploads/comprobantes/`
2. Haz clic derecho → **"Cambiar Permisos"** o **"Change Permissions"**
3. Establece: **755** o **777**
4. Haz clic en **"Aplicar"**

5. Repite para la carpeta `public_html/logs/` (si existe)

---

## ✅ PASO 8: Probar el Sistema

### 8.1 Acceder al Sistema
1. Ve a tu dominio (ej: `tudominio.com` o el subdominio que te dieron)
2. O usa: `https://tudominio.hosting.com/`

### 8.2 Probar Login
- **Email:** `admin@tinysteps.com`
- **Contraseña:** `tinyvicentina789`

### 8.3 Verificar Funcionalidades
- ✅ Login funciona
- ✅ Dashboard carga
- ✅ Puedes subir pagos
- ✅ Las imágenes se suben correctamente

---

## 🆘 Solución de Problemas

### Error 500
- Verifica `config/database.php` (credenciales correctas)
- Verifica permisos de carpetas (755 o 777)
- Revisa logs de error del hosting

### No conecta a Base de Datos
- Verifica que el host sea correcto (puede ser `localhost` o una IP)
- Verifica usuario y contraseña
- Verifica que la base de datos exista

### Página en Blanco
- Revisa logs de error del hosting
- Verifica que todos los archivos se subieron
- Verifica permisos de archivos

### Archivos no se Suben
- Verifica permisos de `uploads/comprobantes/` (debe ser 755 o 777)
- Verifica que la carpeta existe

---

## 📋 Checklist Final

- [ ] Backup de BD creado y descargado
- [ ] Paquete ZIP creado y descargado
- [ ] Cuenta en Hostinger creada
- [ ] Base de datos MySQL creada
- [ ] Backup importado en phpMyAdmin
- [ ] Archivos subidos a public_html
- [ ] `config/database.php` actualizado
- [ ] Permisos de carpetas configurados
- [ ] Sistema probado y funcionando

---

## 🎉 ¡Listo!

Una vez completados estos pasos, tu sistema estará en línea y los padres de familia podrán acceder desde cualquier lugar con el link permanente.
