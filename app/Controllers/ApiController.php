<?php
require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Models/User.php';

class ApiController extends Controller {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function handleRequest(): void {
        $accion = $_GET['accion'] ?? '';

        try {
            switch ($accion) {
                case 'obtener_usuarios':
                    $this->obtenerUsuarios();
                    break;

                case 'obtener_estadisticas':
                    $this->obtenerEstadisticas();
                    break;

                case 'obtener_usuario':
                    $this->obtenerUsuario();
                    break;

                case 'crear_usuario':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $this->crearUsuario();
                    } else {
                        $this->jsonResponse(['error' => 'Método no permitido'], 405);
                    }
                    break;

                case 'actualizar_usuario':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
                        $this->actualizarUsuario();
                    } else {
                        $this->jsonResponse(['error' => 'Método no permitido'], 405);
                    }
                    break;

                case 'eliminar_usuario':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
                        $this->eliminarUsuario();
                    } else {
                        $this->jsonResponse(['error' => 'Método no permitido'], 405);
                    }
                    break;

                default:
                    $this->jsonResponse(['error' => 'Acción no reconocida'], 400);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function obtenerUsuarios(): void {
        $filtros = [];

        if (!empty($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }

        if (!empty($_GET['busqueda'])) {
            $filtros['busqueda'] = $_GET['busqueda'];
        }

        $usuarios = $this->userModel->obtenerUsuarios($filtros);

        $this->jsonResponse([
            'success' => true,
            'data' => $usuarios,
            'total' => count($usuarios)
        ]);
    }

    private function obtenerUsuario(): void {
        if (empty($_GET['id'])) {
            $this->jsonResponse(['error' => 'ID no proporcionado'], 400);
        }

        $id = (int) $_GET['id'];
        $usuario = $this->userModel->obtenerUsuarioPorId($id);

        if (!$usuario) {
            $this->jsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        $this->jsonResponse([
            'success' => true,
            'data' => $usuario
        ]);
    }

    private function obtenerEstadisticas(): void {
        $stats = $this->userModel->obtenerEstadisticas();

        $this->jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
    }

    private function crearUsuario(): void {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
            $this->jsonResponse(['error' => 'Campos requeridos: nombre, email, password'], 400);
        }

        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['error' => 'Email no válido'], 400);
        }

        if ($this->userModel->emailExiste($datos['email'])) {
            $this->jsonResponse(['error' => 'El email ya está registrado'], 409);
        }

        $id = $this->userModel->crearUsuario($datos);

        if ($id) {
            $usuario = $this->userModel->obtenerUsuarioPorId($id);
            $this->jsonResponse([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $usuario
            ], 201);
        }

        $this->jsonResponse(['error' => 'Error al crear usuario'], 500);
    }

    private function actualizarUsuario(): void {
        if (empty($_GET['id'])) {
            $this->jsonResponse(['error' => 'ID no proporcionado'], 400);
        }

        $id = (int) $_GET['id'];
        $datos = json_decode(file_get_contents('php://input'), true);

        $usuarioActual = $this->userModel->obtenerUsuarioPorId($id);
        if (!$usuarioActual) {
            $this->jsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if (!empty($datos['email']) && $datos['email'] !== $usuarioActual['email']) {
            if ($this->userModel->emailExiste($datos['email'])) {
                $this->jsonResponse(['error' => 'El email ya está registrado'], 409);
            }
        }

        if ($this->userModel->actualizarUsuario($id, $datos)) {
            $usuario = $this->userModel->obtenerUsuarioPorId($id);
            $this->jsonResponse([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $usuario
            ]);
        }

        $this->jsonResponse(['error' => 'Error al actualizar usuario'], 500);
    }

    private function eliminarUsuario(): void {
        if (empty($_GET['id'])) {
            $this->jsonResponse(['error' => 'ID no proporcionado'], 400);
        }

        $id = (int) $_GET['id'];
        $usuarioActual = $this->userModel->obtenerUsuarioPorId($id);

        if (!$usuarioActual) {
            $this->jsonResponse(['error' => 'Usuario no encontrado'], 404);
        }

        if ($this->userModel->eliminarUsuario($id)) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        }

        $this->jsonResponse(['error' => 'Error al eliminar usuario'], 500);
    }
}
