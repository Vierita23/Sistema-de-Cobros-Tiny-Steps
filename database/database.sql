-- Base de datos para Sistema de Cobros Tiny Steps
CREATE DATABASE IF NOT EXISTS tiny_steps_cobros;
USE tiny_steps_cobros;

-- Tabla de usuarios (admin y padres)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'padre') NOT NULL DEFAULT 'padre',
    telefono VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
);

-- Tabla de niños
CREATE TABLE IF NOT EXISTS ninos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    usuario_id INT NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de pagos
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nino_id INT NOT NULL,
    usuario_id INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    mes_pago VARCHAR(20) NOT NULL,
    anio_pago INT NOT NULL,
    cuenta_bancaria ENUM('Pichincha', 'Bolivariano') NULL,
    descripcion TEXT NULL,
    comprobante_path VARCHAR(255),
    estado ENUM('pendiente', 'aceptado', 'rechazado') DEFAULT 'pendiente',
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_verificacion DATETIME NULL,
    observaciones TEXT,
    FOREIGN KEY (nino_id) REFERENCES ninos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Insertar usuario admin por defecto (password: admin123)
INSERT INTO usuarios (nombre, email, password, tipo) VALUES 
('Administrador', 'admin@tinysteps.com', '$2y$10$fZpGhbl1GnoEc.h0c5kmXOMQasDsc3rz8GuSjkK4kQZcORfRFymB6', 'admin');

