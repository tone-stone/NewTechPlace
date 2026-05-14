-- =====================================================
-- TechPlace - Script de Setup de Base de Datos
-- =====================================================

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS `techplace_db`;
USE `techplace_db`;

-- =====================================================
-- Tabla de Usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_estado (estado),
  INDEX idx_fecha_creacion (fecha_creacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Insertar usuarios de ejemplo (OPCIONAL)
-- =====================================================
-- Contraseñas: 123456 (hash bcrypt)
INSERT INTO `usuarios` (`nombre`, `email`, `telefono`, `password`, `estado`, `fecha_creacion`) VALUES
('Admin TechPlace', 'admin@techplace.com', '+52 664 111 1111', '$2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa', 'activo', '2024-01-01 10:00:00'),
('Juan Pérez', 'juan@techplace.com', '+52 664 123 4567', '$2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa', 'activo', '2024-01-15 12:30:00'),
('María García', 'maria@techplace.com', '+52 664 234 5678', '$2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa', 'activo', '2024-02-20 14:15:00'),
('Carlos López', 'carlos@techplace.com', '+52 664 345 6789', '$2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa', 'inactivo', '2024-03-10 09:45:00'),
('Ana Rodríguez', 'ana@techplace.com', '+52 664 456 7890', '$2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa', 'activo', '2024-04-05 11:20:00'),
('Jorge Martínez', 'jorge@techplace.com', '+52 664 567 8901', '$2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa', 'activo', '2024-04-10 16:00:00'),
('Laura Sánchez', 'laura@techplace.com', '+52 664 678 9012', '$2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa', 'inactivo', '2024-04-12 13:30:00');

-- =====================================================
-- Nota sobre contraseñas:
-- =====================================================
-- Contraseña: 123456
-- Hash: $2y$10$n5UIk.B3WvrLD2CdFGsf7.RfrrV.3qUCXIC39cWY39IfTQ6YSJZTa
-- Para cambiar, usar: password_hash('nueva_contraseña', PASSWORD_BCRYPT, ['cost' => 10])
