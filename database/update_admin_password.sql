-- Script para actualizar la contraseña del administrador
-- Nueva contraseña: tinyvicentina789

USE tiny_steps_cobros;

UPDATE usuarios 
SET password = '$2y$10$KaxOxSscagKtrC7W9XhK1.ToNaFvL3BGw6vtLAA6SztYQjX/W.Uge'
WHERE email = 'admin@tinysteps.com' AND tipo = 'admin';
