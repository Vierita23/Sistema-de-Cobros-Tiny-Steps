# Sistema de Cobros Tiny Steps

Sistema de gestión de pagos para guardería que permite a los padres subir comprobantes de pago y a los administradores verificar y aprobar/rechazar los pagos.

## Características

- **Panel de Administrador:**
  - Crear usuarios (padres)
  - Registrar niños asociados a cada usuario
  - Ver todos los pagos
  - Verificar pagos (aceptar/rechazar)
  - Ver comprobantes de pago

- **Panel de Usuario (Padres):**
  - Ver sus niños registrados
  - Subir comprobantes de pago
  - Ver estado de sus pagos (pendiente/aceptado/rechazado)
  - Ver observaciones del administrador

## Instalación

1. **Base de Datos:**
   - Importa el archivo `database.sql` en tu base de datos MySQL
   - Ajusta las credenciales en `config/database.php` si es necesario

2. **Configuración:**
   - Asegúrate de que la carpeta `uploads/comprobantes/` tenga permisos de escritura
   - El sistema está configurado para XAMPP por defecto

3. **Credenciales por Defecto:**
   - **Admin:** admin@tinysteps.com / admin123

## Estructura de Archivos

```
/
├── admin/              # Panel de administrador
│   ├── dashboard.php
│   ├── usuarios.php
│   ├── ninos.php
│   ├── pagos.php
│   └── ver_pago.php
├── user/               # Panel de usuario
│   ├── dashboard.php
│   ├── pagos.php
│   ├── subir_pago.php
│   ├── ver_pago.php
│   └── nino.php
├── config/             # Configuración
│   ├── database.php
│   └── session.php
├── assets/             # Recursos
│   └── css/
│       └── style.css
├── uploads/            # Comprobantes subidos
│   └── comprobantes/
├── index.php          # Login
├── logout.php
└── database.sql        # Script de base de datos
```

## Uso

1. Inicia sesión como administrador
2. Crea usuarios (padres) desde el panel de administración
3. Registra niños asociados a cada usuario
4. Los padres pueden iniciar sesión y subir pagos
5. Los administradores verifican los pagos y los marcan como aceptados o rechazados
6. Los padres pueden ver el estado de sus pagos en tiempo real

## Tecnologías

- PHP 7.4+
- MySQL
- HTML5/CSS3/JavaScript
- Diseño responsivo







