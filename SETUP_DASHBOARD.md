# 📚 Configuración Completa del Dashboard con Base de Datos

## 🚀 Paso 1: Crear la Base de Datos

### Opción A: Usar phpMyAdmin (Recomendado para MAMP)

1. **Abre phpMyAdmin:**
   - Inicia MAMP
   - Ve a: http://localhost/phpmyadmin
   - Inicia sesión (usuario: root, sin contraseña)

2. **Crear base de datos:**
   - Click en "Nueva"
   - Nombre: `techplace_db`
   - Intercalación: `utf8mb4_unicode_ci`
   - Click en "Crear"

3. **Ejecutar script SQL:**
   - Abre la base de datos `techplace_db`
   - Ve a la pestaña "SQL"
   - Copia todo el contenido de `setup_db.sql`
   - Pega en el editor
   - Click en "Continuar"

### Opción B: Usar Terminal MySQL

```bash
# Abre terminal y conecta a MySQL
mysql -u root -p

# Si no hay contraseña, solo presiona Enter
# Luego ejecuta:

CREATE DATABASE IF NOT EXISTS techplace_db;
USE techplace_db;

# Luego copia y pega el contenido de setup_db.sql
```

---

## 🔧 Paso 2: Verificar Archivos Creados

Asegúrate de que existan estos archivos en `C:\MAMP\htdocs\NewTechPlace\`:

✅ **config.php** - Configuración de BD
✅ **db.php** - Clase de Base de Datos
✅ **api_dashboard.php** - API REST para el dashboard
✅ **dashboard.php** - Página del dashboard (dinámica)
✅ **setup_db.sql** - Script de BD

---

## 🌐 Paso 3: Acceder al Dashboard

### Desde el Login:
```
1. Ve a: http://localhost/NewTechPlace/login.html
2. Email: admin@techplace.com
3. Contraseña: 123456
4. Se redirigirá a dashboard.php
```

### Datos de Prueba Incluidos:

| Email | Contraseña | Estado |
|-------|-----------|--------|
| admin@techplace.com | 123456 | Activo |
| juan@techplace.com | 123456 | Activo |
| maria@techplace.com | 123456 | Activo |
| carlos@techplace.com | 123456 | Inactivo |
| ana@techplace.com | 123456 | Activo |
| jorge@techplace.com | 123456 | Activo |
| laura@techplace.com | 123456 | Inactivo |

---

## 📊 Características del Dashboard

### ✨ Funciones Implementadas:
- ✅ Mostrar lista de usuarios desde BD
- ✅ Estadísticas en tiempo real (Total, Activos, Inactivos, Este mes)
- ✅ Búsqueda en tiempo real
- ✅ Filtros por estado
- ✅ Ordenamiento (Nombre, Fecha)
- ✅ Eliminar usuarios (con confirmación)
- ✅ Mostrar Nombre, Email, Teléfono
- ✅ Indicador visual de estado (Activo/Inactivo)
- ✅ Interfaz responsive (Desktop, Tablet, Móvil)
- ✅ Diseño moderno con gradientes púrpura
- ✅ Animaciones suaves
- ✅ Sidebar colapsable

### 🔄 Funciones Pendientes (Fase 2):
- ⏳ Agregar nuevo usuario (formulario)
- ⏳ Editar usuario existente (modal)
- ⏳ Cambiar contraseña

---

## 🛠️ Estructura de la Base de Datos

### Tabla: `usuarios`

```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  telefono VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  estado ENUM('activo', 'inactivo') DEFAULT 'activo',
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 🔗 Estructura de la API

### Endpoints disponibles:

#### 1. Obtener Usuarios
```
GET: api_dashboard.php?accion=obtener_usuarios
Parámetros opcionales:
  - estado: activo|inactivo
  - busqueda: término de búsqueda

Respuesta:
{
  "success": true,
  "data": [...usuarios],
  "total": 7
}
```

#### 2. Obtener Estadísticas
```
GET: api_dashboard.php?accion=obtener_estadisticas

Respuesta:
{
  "success": true,
  "data": {
    "total": 7,
    "activos": 5,
    "inactivos": 2,
    "este_mes": 3
  }
}
```

#### 3. Eliminar Usuario
```
DELETE: api_dashboard.php?accion=eliminar_usuario&id=1

Respuesta:
{
  "success": true,
  "message": "Usuario eliminado exitosamente"
}
```

---

## 🐛 Solución de Problemas

### Problema: "Error de conexión a base de datos"
**Solución:**
1. Verifica que MAMP esté corriendo
2. Comprueba que MySQL esté activo (puerto 3306)
3. Revisa que `config.php` tenga los datos correctos
4. Asegúrate de que `techplace_db` exista en phpMyAdmin

### Problema: "Error 404 al cargar dashboard.php"
**Solución:**
1. Verifica que el archivo exista en la ruta correcta
2. Asegúrate de estar accediendo desde el login (no directamente)
3. Comprueba que PHP esté habilitado en MAMP

### Problema: "Los datos no se cargan"
**Solución:**
1. Abre la consola (F12 en navegador)
2. Revisa si hay errores de JavaScript
3. Verifica en Network si la API responde
4. Comprueba que la BD tenga usuarios

---

## 📱 Versiones Disponibles

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| dashboard.html | Static | Versión sin BD (datos de ejemplo) |
| dashboard.php | Dynamic | Versión con BD (datos reales) |

**Importante:** Usa `dashboard.php` en producción para datos reales de la BD.

---

## 🔐 Seguridad Implementada

✅ Contraseñas hasheadas con bcrypt
✅ Consultas preparadas contra SQL injection
✅ Validación de input
✅ Sesiones PHP
✅ CORS configurado
✅ Headers de seguridad

---

## 📝 Próximos Pasos

1. **Implementar CRUD completo:**
   - Agregar nuevo usuario (formulario modal)
   - Editar usuario existente
   - Cambiar estado (activo/inactivo)

2. **Mejorar validaciones:**
   - Validar email único
   - Validar formato de teléfono
   - Validar contraseña fuerte

3. **Agregar características:**
   - Paginación
   - Exportar a CSV/Excel
   - Historial de cambios
   - Roles y permisos

---

## 💡 Notas Importantes

- El dashboard es **completamente responsive**
- Todos los datos se cargan vía **AJAX** (sin recargar página)
- Las **animaciones** son suaves y modernas
- El **diseño** es consistente con index.html
- Los **colores** temáticos son púrpura (#c084fc)

¡El dashboard está listo para usar! 🎉
