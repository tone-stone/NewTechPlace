<?php
/**
 * Configuración de Base de Datos
 * TechPlace - Sistema de Gestión de Usuarios
 */

// === PARÁMETROS DE CONEXIÓN ===
define('DB_HOST', '192.185.131.135');
define('DB_USER', 'techpla1_db');
define('DB_PASS', 'techplacetj');
define('DB_NAME', 'techpla1_db');
define('DB_PORT', 3306);

// === CONFIGURACIÓN DE APLICACIÓN ===
define('APP_URL', 'http://192.185.131.135');
define('APP_NAME', 'TechPlace');
define('APP_VERSION', '1.0.0');

// === CONFIGURACIÓN DE SESIÓN ===
define('SESSION_TIMEOUT', 3600); // 1 hora en segundos

// === CONFIGURACIÓN DE SEGURIDAD ===
define('BCRYPT_COST', 10); // Costo de hashing bcrypt

// === MANEJO DE ERRORES ===
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en producción
ini_set('log_errors', 1);

// === ZONA HORARIA ===
date_default_timezone_set('America/Mexico_City');
?>
