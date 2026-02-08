# 📊 Cómo Ver tu Base de Datos - Sistema Tiny Steps

## 🔗 Información de la Base de Datos

### Configuración Actual:
- **Host:** `localhost`
- **Usuario:** `root`
- **Contraseña:** (vacía - sin contraseña)
- **Nombre de Base de Datos:** `tiny_steps_cobros`
- **Puerto:** 3306 (por defecto)

### Archivo de Configuración:
La configuración está en: `config/database.php`

---

## 🌐 Acceder a phpMyAdmin (Interfaz Web)

### Opción 1: Usar el Script Automático (Recomendado)
1. **Doble clic en:** `abrir_phpmyadmin.bat`
2. Se abrirá automáticamente phpMyAdmin en tu navegador

### Opción 2: Acceso Manual
1. Asegúrate de que **Apache y MySQL** estén corriendo en XAMPP
2. Abre tu navegador
3. Ve a: **`http://localhost/phpmyadmin/`**

### Credenciales de Acceso:
- **Usuario:** `root`
- **Contraseña:** (déjalo vacío - no escribas nada)

---

## 📋 Pasos para Ver tu Base de Datos

### 1. Abrir phpMyAdmin
- Ejecuta `abrir_phpmyadmin.bat` o ve a `http://localhost/phpmyadmin/`

### 2. Iniciar Sesión
- Usuario: `root`
- Contraseña: (vacía)
- Haz clic en "Iniciar sesión"

### 3. Seleccionar la Base de Datos
- En el menú izquierdo, busca: **`tiny_steps_cobros`**
- Haz clic en el nombre de la base de datos

### 4. Ver las Tablas
Verás las siguientes tablas:
- **`usuarios`** - Usuarios del sistema (admin y padres)
- **`ninos`** - Niños registrados
- **`pagos`** - Pagos registrados

### 5. Ver Contenido de una Tabla
- Haz clic en el nombre de la tabla (ej: `usuarios`)
- Verás todos los registros
- Puedes editar, eliminar o agregar registros desde aquí

---

## 🔍 Ver Información de la Base de Datos

### Ver Todas las Bases de Datos:
1. En phpMyAdmin, en el menú izquierdo verás todas las bases de datos
2. Busca **`tiny_steps_cobros`**

### Ver Estructura de las Tablas:
1. Selecciona la base de datos `tiny_steps_cobros`
2. Haz clic en una tabla
3. Ve a la pestaña **"Estructura"** para ver las columnas y tipos de datos

### Ejecutar Consultas SQL:
1. Selecciona la base de datos
2. Haz clic en la pestaña **"SQL"**
3. Escribe tu consulta SQL
4. Haz clic en "Continuar"

---

## 📝 Ejemplos de Consultas Útiles

### Ver todos los usuarios:
```sql
SELECT * FROM usuarios;
```

### Ver todos los pagos aceptados:
```sql
SELECT * FROM pagos WHERE estado = 'aceptado';
```

### Ver total recaudado:
```sql
SELECT SUM(monto) as total FROM pagos WHERE estado = 'aceptado';
```

### Ver niños activos:
```sql
SELECT * FROM ninos WHERE activo = 1;
```

---

## 🛠️ Herramientas Disponibles

### Scripts en el Proyecto:
- **`abrir_phpmyadmin.bat`** - Abre phpMyAdmin automáticamente
- **`abrir_phpmyadmin.php`** - Versión PHP del script

### Panel de Control de XAMPP:
- Ubicación: `C:\xampp\xampp-control.exe`
- Desde ahí puedes iniciar/detener MySQL y Apache

---

## ⚠️ Notas Importantes

1. **Siempre haz respaldo** antes de modificar datos directamente en phpMyAdmin
2. **No elimines tablas** sin saber qué estás haciendo
3. **La contraseña por defecto está vacía** - considera cambiarla en producción
4. **MySQL debe estar corriendo** para acceder a phpMyAdmin

---

## 🔗 Enlaces Rápidos

- **phpMyAdmin:** `http://localhost/phpmyadmin/`
- **Panel XAMPP:** `C:\xampp\xampp-control.exe`
- **Configuración DB:** `config/database.php`

---

## 📞 Si No Puedes Acceder

1. Verifica que **MySQL esté corriendo** en XAMPP
2. Verifica que **Apache esté corriendo** en XAMPP
3. Ejecuta `verificar_xampp.bat` para verificar servicios
4. Revisa los logs de MySQL si hay errores

---

**Última actualización:** 2024
