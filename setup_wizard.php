<?php
/**
 * Script de Configuración de Base de Datos
 * Ejecuta el setup_db.sql automáticamente
 */

// Desactivar límite de tiempo para ejecución
set_time_limit(0);

// === CONEXIÓN A MYSQL (sin seleccionar BD) ===
// Intentar diferentes configuraciones
$configuraciones = [
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'port' => 3306],
    ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'port' => 3306],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => 'root', 'port' => 3306],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root', 'port' => 3306],
];

$conexion = null;
$config_usada = null;

foreach ($configuraciones as $config) {
    @$conexion = new mysqli(
        $config['host'],
        $config['user'],
        $config['pass'],
        '',
        $config['port']
    );
    
    if (!$conexion->connect_error) {
        $config_usada = $config;
        break;
    }
    $conexion = null;
}

if (!$conexion) {
    echo "<html><head><meta charset='UTF-8'></head><body style='font-family: Arial; background: #09090f; color: #fff; padding: 2rem;'>";
    echo "<div style='max-width: 800px; margin: 0 auto; background: #1a1a2e; padding: 2rem; border-radius: 10px; border-left: 4px solid #ef4444;'>";
    echo "<h2 style='color: #ef4444;'>❌ Error de Conexión a MySQL</h2>";
    echo "<p><strong>Estado:</strong> No se pudo conectar a MySQL en ninguna configuración</p>";
    echo "<h3>🔍 Solución Manual:</h3>";
    echo "<ol>";
    echo "<li>Abre <strong>phpMyAdmin</strong>:<br>";
    echo "   → Inicia MAMP<br>";
    echo "   → Ve a: <a href='http://localhost:8888/MAMP/index.php?page=phpmyadmin' style='color: #c084fc;'>MAMP Dashboard → phpMyAdmin</a></li>";
    echo "<li>Inicia sesión (usuario: root, sin contraseña)</li>";
    echo "<li>Click en 'Nueva' base de datos</li>";
    echo "<li>Nombre: <code>techplace_db</code></li>";
    echo "<li>Intercalación: <code>utf8mb4_unicode_ci</code></li>";
    echo "<li>Click en 'Crear'</li>";
    echo "<li>Abre la pestaña SQL y copia todo el contenido de <code>setup_db.sql</code></li>";
    echo "<li>Pega en el editor y ejecuta</li>";
    echo "</ol>";
    echo "</div>";
    echo "</body></html>";
    die();
}

// Establecer charset
$conexion->set_charset('utf8mb4');

echo "<html><head><meta charset='UTF-8'><style>
body { font-family: 'Segoe UI', Arial; background: linear-gradient(135deg, #09090f 0%, #1a1a2e 100%); color: #fff; padding: 2rem; }
pre { font-family: monospace; background: #1a1a2e; padding: 1.5rem; border-radius: 5px; border-left: 3px solid #c084fc; overflow-x: auto; }
.success { color: #10b981; }
.error { color: #ef4444; }
.info { color: #60a5fa; }
strong { color: #c084fc; }
</style></head><body>";

echo "<pre style='font-family: monospace;'>";
echo "🔧 <strong>Iniciando configuración de Base de Datos...</strong>\n";
echo "   Conexión establecida con: " . $config_usada['host'] . "@" . $config_usada['port'] . "\n\n";

// === CREAR BASE DE DATOS ===
$query = "CREATE DATABASE IF NOT EXISTS `techplace_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

if ($conexion->query($query)) {
    echo "✅ Base de datos 'techplace_db' creada/verificada\n";
} else {
    die("❌ Error al crear BD: " . $conexion->error);
}

// === SELECCIONAR BASE DE DATOS ===
if (!$conexion->select_db('techplace_db')) {
    die("❌ Error al seleccionar BD: " . $conexion->error);
}

echo "✅ Base de datos seleccionada\n\n";

$password_prueba_hash = password_hash('123456', PASSWORD_BCRYPT, ['cost' => 10]);

// === CREAR TABLA USUARIOS ===
echo "📊 Creando tabla de usuarios...\n";

// Primero dropear la tabla si existe (para asegurar que tenga todos los campos)
$conexion->query("DROP TABLE IF EXISTS usuarios;");

$query_tabla = "CREATE TABLE IF NOT EXISTS `usuarios` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conexion->query($query_tabla)) {
    echo "✅ Tabla 'usuarios' creada/verificada\n\n";
} else {
    die("❌ Error al crear tabla: " . $conexion->error);
}

// === LIMPIAR DATOS ANTERIORES (OPCIONAL) ===
// Comentar si quieres preservar datos existentes
$conexion->query("TRUNCATE TABLE usuarios;");

// === INSERTAR USUARIOS DE EJEMPLO ===
echo "👥 Insertando usuarios de ejemplo...\n";

$usuarios = [
    [
        'nombre' => 'Admin TechPlace',
        'email' => 'admin@techplace.com',
        'telefono' => '+52 664 111 1111',
        'password' => $password_prueba_hash, // 123456
        'estado' => 'activo'
    ],
    [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@techplace.com',
        'telefono' => '+52 664 123 4567',
        'password' => $password_prueba_hash,
        'estado' => 'activo'
    ],
    [
        'nombre' => 'María García',
        'email' => 'maria@techplace.com',
        'telefono' => '+52 664 234 5678',
        'password' => $password_prueba_hash,
        'estado' => 'activo'
    ],
    [
        'nombre' => 'Carlos López',
        'email' => 'carlos@techplace.com',
        'telefono' => '+52 664 345 6789',
        'password' => $password_prueba_hash,
        'estado' => 'inactivo'
    ],
    [
        'nombre' => 'Ana Rodríguez',
        'email' => 'ana@techplace.com',
        'telefono' => '+52 664 456 7890',
        'password' => $password_prueba_hash,
        'estado' => 'activo'
    ],
    [
        'nombre' => 'Jorge Martínez',
        'email' => 'jorge@techplace.com',
        'telefono' => '+52 664 567 8901',
        'password' => $password_prueba_hash,
        'estado' => 'activo'
    ],
    [
        'nombre' => 'Laura Sánchez',
        'email' => 'laura@techplace.com',
        'telefono' => '+52 664 678 9012',
        'password' => $password_prueba_hash,
        'estado' => 'inactivo'
    ]
];

$contador = 0;
foreach ($usuarios as $usuario) {
    $query_insert = "INSERT INTO usuarios (nombre, email, telefono, password, estado) 
                    VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($query_insert);
    
    if (!$stmt) {
        echo "❌ Error al preparar sentencia: " . $conexion->error . "\n";
        continue;
    }
    
    $stmt->bind_param(
        'sssss',
        $usuario['nombre'],
        $usuario['email'],
        $usuario['telefono'],
        $usuario['password'],
        $usuario['estado']
    );
    
    if ($stmt->execute()) {
        echo "  ✅ " . $usuario['nombre'] . " (" . $usuario['email'] . ")\n";
        $contador++;
    } else {
        echo "  ❌ Error al insertar " . $usuario['email'] . ": " . $stmt->error . "\n";
    }
    
    $stmt->close();
}

echo "\n✅ Se insertaron $contador usuarios de ejemplo\n\n";

// === VERIFICAR DATOS ===
echo "📋 <strong>Datos en la base de datos:</strong>\n";
$result = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
$row = $result->fetch_assoc();
echo "Total de usuarios: " . $row['total'] . "\n\n";

// === LISTAR USUARIOS ===
echo "👥 <strong>Lista de usuarios:</strong>\n";
echo "─────────────────────────────────────────────────────────────────\n";

$result = $conexion->query("SELECT id, nombre, email, telefono, estado FROM usuarios ORDER BY id ASC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo sprintf(
            "ID: %d | Nombre: %-20s | Email: %-25s | Teléfono: %-15s | Estado: %s\n",
            $row['id'],
            $row['nombre'],
            $row['email'],
            $row['telefono'] ?: 'N/A',
            $row['estado']
        );
    }
}

echo "─────────────────────────────────────────────────────────────────\n\n";

// === ESTADÍSTICAS ===
echo "📊 <strong>Estadísticas:</strong>\n";
$stats = $conexion->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado='activo' THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN estado='inactivo' THEN 1 ELSE 0 END) as inactivos
    FROM usuarios
");

if ($stats) {
    $row = $stats->fetch_assoc();
    echo "Total de usuarios: " . $row['total'] . "\n";
    echo "Usuarios activos: " . $row['activos'] . "\n";
    echo "Usuarios inactivos: " . $row['inactivos'] . "\n";
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║              ✅ CONFIGURACIÓN COMPLETADA                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "🔗 <strong>Próximos pasos:</strong>\n";
echo "1. Ve a: <a href='login.html' style='color: #0066cc;'>http://localhost/NewTechPlace/login.html</a>\n";
echo "2. Email: admin@techplace.com\n";
echo "3. Contraseña: 123456\n";
echo "4. ¡Ingresa al dashboard!\n\n";

echo "📝 <strong>Notas de seguridad:</strong>\n";
echo "• Este script es solo para desarrollo\n";
echo "• Las contraseñas son de prueba (123456)\n";
echo "• Cámbilas antes de producción\n";

echo "</pre>";

// === CERRAR CONEXIÓN ===
$conexion->close();

echo "
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #09090f 0%, #1a1a2e 100%);
        color: #fff;
        padding: 2rem;
    }
    a {
        color: #c084fc !important;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
</style>
";
?>
