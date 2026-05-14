<?php
/**
 * API del Dashboard
 * Maneja todas las peticiones del dashboard
 */

require_once 'config.php';
require_once 'db.php';

// Detectar método de petición
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

try {
    $db = new Database();

    switch ($accion) {
        // === OBTENER DATOS ===
        case 'obtener_usuarios':
            obtenerUsuarios($db);
            break;

        case 'obtener_estadisticas':
            obtenerEstadisticas($db);
            break;

        case 'obtener_usuario':
            obtenerUsuario($db);
            break;

        // === ACCIONES CRUD ===
        case 'crear_usuario':
            if ($metodo === 'POST') {
                crearUsuario($db);
            } else {
                respuesta(['error' => 'Método no permitido'], 405);
            }
            break;

        case 'actualizar_usuario':
            if ($metodo === 'POST' || $metodo === 'PUT') {
                actualizarUsuario($db);
            } else {
                respuesta(['error' => 'Método no permitido'], 405);
            }
            break;

        case 'eliminar_usuario':
            if ($metodo === 'POST' || $metodo === 'DELETE') {
                eliminarUsuario($db);
            } else {
                respuesta(['error' => 'Método no permitido'], 405);
            }
            break;

        default:
            respuesta(['error' => 'Acción no reconocida'], 400);
    }

} catch (Exception $e) {
    respuesta(['error' => $e->getMessage()], 500);
}

// =====================================================
// FUNCIONES
// =====================================================

/**
 * Obtener lista de usuarios
 */
function obtenerUsuarios($db) {
    $filtros = [];

    if (!empty($_GET['estado'])) {
        $filtros['estado'] = $_GET['estado'];
    }

    if (!empty($_GET['busqueda'])) {
        $filtros['busqueda'] = $_GET['busqueda'];
    }

    $usuarios = $db->obtenerUsuarios($filtros);

    respuesta([
        'success' => true,
        'data' => $usuarios,
        'total' => count($usuarios)
    ]);
}

/**
 * Obtener un usuario por ID
 */
function obtenerUsuario($db) {
    if (empty($_GET['id'])) {
        respuesta(['error' => 'ID no proporcionado'], 400);
        return;
    }

    $id = (int)$_GET['id'];
    $usuario = $db->obtenerUsuarioPorId($id);

    if (!$usuario) {
        respuesta(['error' => 'Usuario no encontrado'], 404);
        return;
    }

    respuesta([
        'success' => true,
        'data' => $usuario
    ]);
}

/**
 * Obtener estadísticas
 */
function obtenerEstadisticas($db) {
    $stats = $db->obtenerEstadisticas();

    respuesta([
        'success' => true,
        'data' => $stats
    ]);
}

/**
 * Crear nuevo usuario
 */
function crearUsuario($db) {
    $datos = json_decode(file_get_contents('php://input'), true);

    // Validar datos requeridos
    if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
        respuesta(['error' => 'Campos requeridos: nombre, email, password'], 400);
        return;
    }

    // Validar email
    if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        respuesta(['error' => 'Email no válido'], 400);
        return;
    }

    // Verificar si el email ya existe
    if ($db->emailExiste($datos['email'])) {
        respuesta(['error' => 'El email ya está registrado'], 409);
        return;
    }

    // Crear usuario
    $id = $db->crearUsuario($datos);

    if ($id) {
        $usuario = $db->obtenerUsuarioPorId($id);
        respuesta([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => $usuario
        ], 201);
    } else {
        respuesta(['error' => 'Error al crear usuario'], 500);
    }
}

/**
 * Actualizar usuario
 */
function actualizarUsuario($db) {
    if (empty($_GET['id'])) {
        respuesta(['error' => 'ID no proporcionado'], 400);
        return;
    }

    $id = (int)$_GET['id'];
    $datos = json_decode(file_get_contents('php://input'), true);

    // Verificar que el usuario existe
    if (!$db->obtenerUsuarioPorId($id)) {
        respuesta(['error' => 'Usuario no encontrado'], 404);
        return;
    }

    // Si se proporciona un email nuevo, verificar que no exista
    if (!empty($datos['email']) && isset($datos['email'])) {
        $usuarioActual = $db->obtenerUsuarioPorId($id);
        if ($datos['email'] !== $usuarioActual['email'] && $db->emailExiste($datos['email'])) {
            respuesta(['error' => 'El email ya está registrado'], 409);
            return;
        }
    }

    // Actualizar usuario
    if ($db->actualizarUsuario($id, $datos)) {
        $usuario = $db->obtenerUsuarioPorId($id);
        respuesta([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'data' => $usuario
        ]);
    } else {
        respuesta(['error' => 'Error al actualizar usuario'], 500);
    }
}

/**
 * Eliminar usuario
 */
function eliminarUsuario($db) {
    if (empty($_GET['id'])) {
        respuesta(['error' => 'ID no proporcionado'], 400);
        return;
    }

    $id = (int)$_GET['id'];

    // Verificar que el usuario existe
    if (!$db->obtenerUsuarioPorId($id)) {
        respuesta(['error' => 'Usuario no encontrado'], 404);
        return;
    }

    // Eliminar usuario
    if ($db->eliminarUsuario($id)) {
        respuesta([
            'success' => true,
            'message' => 'Usuario eliminado exitosamente'
        ]);
    } else {
        respuesta(['error' => 'Error al eliminar usuario'], 500);
    }
}

/**
 * Función auxiliar para respuestas JSON
 */
function respuesta($datos, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($datos);
    exit;
}
?>
