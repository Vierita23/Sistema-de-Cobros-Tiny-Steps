# Guía Rápida para Localhost - Sistema Tiny Steps

## 📋 Requisitos Previos

1. **XAMPP instalado** en tu sistema
2. **Apache y MySQL** deben estar corriendo

## 🚀 Pasos para Configurar Localhost

### Paso 1: Iniciar XAMPP

1. Abre el **Panel de Control de XAMPP**
2. Inicia los siguientes servicios:
   - ✅ **Apache** (debe estar en verde)
   - ✅ **MySQL** (debe estar en verde)

### Paso 2: Verificar Servicios

Ejecuta el archivo `verificar_xampp.bat` para verificar que todo esté funcionando correctamente.

O verifica manualmente:
- Apache debe estar escuchando en el puerto **80**
- MySQL debe estar escuchando en el puerto **3306**

### Paso 3: Instalar la Base de Datos

1. Abre tu navegador
2. Ve a: `http://localhost/Sistema%20de%20Cobros%20Tiny%20Steps/install.php`
   - O simplemente: `http://localhost/Sistema de Cobros Tiny Steps/install.php`
3. Haz clic en el botón **"Instalar Base de Datos"**
4. Espera a que se complete la instalación

### Paso 4: Acceder al Sistema

1. Ve a: `http://localhost/Sistema%20de%20Cobros%20Tiny%20Steps/`
2. O directamente: `http://localhost/Sistema de Cobros Tiny Steps/index.php`

### Paso 5: Iniciar Sesión

**Credenciales por defecto del administrador:**
- **Email:** `admin@tinysteps.com`
- **Contraseña:** `admin123`

## 🔧 Solución de Problemas

### Error: "Apache no inicia"
- Verifica que el puerto 80 no esté siendo usado por otro programa
- Puedes cambiar el puerto de Apache en el archivo `httpd.conf` de XAMPP

### Error: "MySQL no inicia"
- Verifica que el puerto 3306 no esté siendo usado
- Revisa los logs de MySQL en XAMPP

### Error: "No se puede conectar a la base de datos"
- Verifica que MySQL esté corriendo
- Verifica la configuración en `config/database.php`:
  - Host: `localhost`
  - Usuario: `root`
  - Contraseña: (vacía por defecto en XAMPP)
  - Base de datos: `tiny_steps_cobros`

### Error: "404 Not Found"
- Verifica que la carpeta esté en: `C:\xampp\htdocs\Sistema de Cobros Tiny Steps\`
- Verifica que Apache esté corriendo
- Intenta acceder con la ruta completa: `http://localhost/Sistema%20de%20Cobros%20Tiny%20Steps/`

## 📝 Notas Importantes

- El proyecto debe estar en: `C:\xampp\htdocs\Sistema de Cobros Tiny Steps\`
- Si cambias la ubicación, ajusta la URL en el navegador
- Los espacios en la ruta se pueden manejar con `%20` o simplemente escribiéndolos normalmente

## 🔗 URLs Útiles

- **Instalación:** `http://localhost/Sistema de Cobros Tiny Steps/install.php`
- **Login:** `http://localhost/Sistema de Cobros Tiny Steps/index.php`
- **phpMyAdmin:** `http://localhost/phpmyadmin/` (para gestionar la base de datos)
