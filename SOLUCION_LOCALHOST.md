# 🔧 Solución de Problemas de Localhost - Sistema Tiny Steps

## 🚀 Inicio Rápido

### Opción 1: Script Automático (Recomendado)
1. **Doble clic en:** `iniciar_localhost.bat`
   - Este script verifica e inicia automáticamente Apache y MySQL
   - Abre el navegador con el sistema

### Opción 2: Ayuda Detallada
1. **Doble clic en:** `ayuda_localhost.bat`
   - Diagnóstico completo del sistema
   - Opciones para iniciar servicios manualmente
   - Enlaces directos a herramientas útiles

### Opción 3: Verificación Web
1. Abre tu navegador
2. Ve a: `http://localhost/Sistema de Cobros Tiny Steps/verificar_localhost.php`
   - Muestra el estado de todos los componentes
   - Indica qué falta configurar

---

## ✅ Verificación Paso a Paso

### 1. Verificar XAMPP
- **Ubicación esperada:** `C:\xampp\`
- **Verificar:** Ejecuta `verificar_xampp.bat`

### 2. Verificar Apache
- **Puerto:** 80
- **Verificar:** Debe aparecer en verde en el Panel de Control de XAMPP
- **Problema común:** Puerto 80 ocupado por otro programa
  - **Solución:** Cierra Skype, IIS u otros programas que usen el puerto 80
  - **Alternativa:** Cambia el puerto de Apache a 8080 en `httpd.conf`

### 3. Verificar MySQL
- **Puerto:** 3306
- **Verificar:** Debe aparecer en verde en el Panel de Control de XAMPP
- **Problema común:** Puerto 3306 ocupado
  - **Solución:** Cierra otros servicios MySQL que puedan estar corriendo

### 4. Verificar Base de Datos
- **Nombre:** `tiny_steps_cobros`
- **Usuario:** `root`
- **Contraseña:** (vacía por defecto)
- **Instalación:** Ve a `http://localhost/Sistema de Cobros Tiny Steps/install.php`

---

## 🔍 Problemas Comunes y Soluciones

### ❌ Error: "Apache no inicia"

**Causas posibles:**
1. Puerto 80 ocupado
2. Error en la configuración
3. Permisos insuficientes

**Soluciones:**
```batch
# Verificar qué usa el puerto 80
netstat -ano | findstr :80

# Iniciar Apache manualmente
C:\xampp\apache_start.bat

# O desde el Panel de Control de XAMPP
```

**Si el puerto 80 está ocupado:**
1. Abre `C:\xampp\apache\conf\httpd.conf`
2. Busca `Listen 80`
3. Cámbialo a `Listen 8080`
4. Guarda y reinicia Apache
5. Accede con: `http://localhost:8080/Sistema de Cobros Tiny Steps/`

---

### ❌ Error: "MySQL no inicia"

**Causas posibles:**
1. Puerto 3306 ocupado
2. Archivos corruptos
3. Proceso anterior no cerrado

**Soluciones:**
```batch
# Verificar qué usa el puerto 3306
netstat -ano | findstr :3306

# Iniciar MySQL manualmente
C:\xampp\mysql_start.bat

# O desde el Panel de Control de XAMPP
```

**Si MySQL no inicia:**
1. Cierra todos los procesos de MySQL
2. Reinicia el Panel de Control de XAMPP
3. Intenta iniciar MySQL nuevamente
4. Si persiste, revisa los logs en `C:\xampp\mysql\data\`

---

### ❌ Error: "No se puede conectar a la base de datos"

**Causas posibles:**
1. MySQL no está corriendo
2. Base de datos no existe
3. Credenciales incorrectas

**Soluciones:**
1. Verifica que MySQL esté corriendo
2. Ve a: `http://localhost/Sistema de Cobros Tiny Steps/install.php`
3. Haz clic en "Instalar Base de Datos"
4. Verifica la configuración en `config/database.php`:
   ```php
   DB_HOST = 'localhost'
   DB_USER = 'root'
   DB_PASS = ''  // Vacía por defecto en XAMPP
   DB_NAME = 'tiny_steps_cobros'
   ```

---

### ❌ Error: "404 Not Found"

**Causas posibles:**
1. Carpeta en ubicación incorrecta
2. Apache no está corriendo
3. Ruta incorrecta en el navegador

**Soluciones:**
1. Verifica que la carpeta esté en: `C:\xampp\htdocs\Sistema de Cobros Tiny Steps\`
2. Verifica que Apache esté corriendo
3. Usa la URL correcta:
   - `http://localhost/Sistema de Cobros Tiny Steps/`
   - O con codificación: `http://localhost/Sistema%20de%20Cobros%20Tiny%20Steps/`

---

### ❌ Error: "Access Denied" o "Forbidden"

**Causas posibles:**
1. Permisos de archivos
2. Configuración de Apache

**Soluciones:**
1. Verifica permisos de la carpeta
2. Asegúrate de que los archivos `.htaccess` estén presentes
3. Revisa la configuración de Apache en `httpd.conf`

---

## 📋 Checklist de Verificación

Antes de reportar un problema, verifica:

- [ ] XAMPP está instalado en `C:\xampp\`
- [ ] Apache está corriendo (puerto 80 o 8080)
- [ ] MySQL está corriendo (puerto 3306)
- [ ] La carpeta del proyecto está en `C:\xampp\htdocs\Sistema de Cobros Tiny Steps\`
- [ ] La base de datos `tiny_steps_cobros` existe
- [ ] El archivo `config/database.php` tiene la configuración correcta
- [ ] Los archivos tienen permisos de lectura

---

## 🛠️ Herramientas Útiles

### Scripts Disponibles:
- **`iniciar_localhost.bat`** - Inicia servicios y abre el navegador
- **`ayuda_localhost.bat`** - Diagnóstico completo y ayuda
- **`verificar_xampp.bat`** - Verifica servicios de XAMPP
- **`verificar_localhost.php`** - Verificación web completa

### URLs Importantes:
- **Login:** `http://localhost/Sistema de Cobros Tiny Steps/`
- **Instalación:** `http://localhost/Sistema de Cobros Tiny Steps/install.php`
- **Verificación:** `http://localhost/Sistema de Cobros Tiny Steps/verificar_localhost.php`
- **phpMyAdmin:** `http://localhost/phpmyadmin/`

---

## 💡 Consejos

1. **Siempre inicia Apache antes que MySQL** (aunque no es crítico)
2. **Cierra el Panel de Control de XAMPP** si no lo necesitas (puede causar conflictos)
3. **Usa `ayuda_localhost.bat`** si tienes problemas persistentes
4. **Revisa los logs** en `C:\xampp\apache\logs\` y `C:\xampp\mysql\data\` si hay errores
5. **Reinicia los servicios** si algo no funciona correctamente

---

## 📞 Si Nada Funciona

1. Cierra completamente XAMPP
2. Reinicia tu computadora
3. Abre el Panel de Control de XAMPP
4. Inicia Apache y MySQL manualmente
5. Ejecuta `ayuda_localhost.bat`
6. Sigue las instrucciones que aparezcan

---

## 🔗 Enlaces Rápidos

- **Panel de Control XAMPP:** `C:\xampp\xampp-control.exe`
- **phpMyAdmin:** `http://localhost/phpmyadmin/`
- **Verificación Web:** `http://localhost/Sistema de Cobros Tiny Steps/verificar_localhost.php`

---

**Última actualización:** 2024
