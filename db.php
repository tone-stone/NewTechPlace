<?php
/**
 * Clase de Base de Datos
 * Proporciona métodos CRUD y consultas preparadas
 */

require_once 'config.php';

class Database {
    public $conexion;
    private $prepare;

    public function __construct() {
        try {
            $this->conexion = new mysqli(
                DB_HOST,
                DB_USER,
                DB_PASS,
                DB_NAME,
                DB_PORT
            );

            if ($this->conexion->connect_error) {
                throw new Exception('Error en conexión: ' . $this->conexion->connect_error);
            }

            // Establecer charset
            $this->conexion->set_charset('utf8mb4');
        } catch (Exception $e) {
            die(json_encode(['error' => $e->getMessage()]));
        }
    }

    /**
     * Obtener todos los usuarios
     * @param array $filtros - Filtros opcionales
     * @return array
     */
    public function obtenerUsuarios($filtros = []) {
        $query = "SELECT id, nombre, email, telefono, estado, fecha_creacion FROM usuarios WHERE 1=1";
        
        if (!empty($filtros['estado'])) {
            $query .= " AND estado = ?";
        }

        if (!empty($filtros['busqueda'])) {
            $query .= " AND (nombre LIKE ? OR email LIKE ? OR telefono LIKE ?)";
        }

        $query .= " ORDER BY fecha_creacion DESC";

        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        if (!empty($filtros['estado']) && !empty($filtros['busqueda'])) {
            $estado = $filtros['estado'];
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $stmt->bind_param('ssss', $estado, $busqueda, $busqueda, $busqueda);
        } elseif (!empty($filtros['estado'])) {
            $estado = $filtros['estado'];
            $stmt->bind_param('s', $estado);
        } elseif (!empty($filtros['busqueda'])) {
            $busqueda = '%' . $filtros['busqueda'] . '%';
            $stmt->bind_param('sss', $busqueda, $busqueda, $busqueda);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuarios = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $usuarios;
    }

    /**
     * Obtener usuario por ID
     * @param int $id
     * @return array|null
     */
    public function obtenerUsuarioPorId($id) {
        $query = "SELECT id, nombre, email, telefono, estado, fecha_creacion FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        return $usuario;
    }

    /**
     * Crear nuevo usuario
     * @param array $datos
     * @return bool|int - ID del nuevo usuario o false
     */
    public function crearUsuario($datos) {
        $query = "INSERT INTO usuarios (nombre, email, telefono, password, estado, fecha_creacion) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        $password_hash = password_hash($datos['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
        $estado = $datos['estado'] ?? 'activo';

        $stmt->bind_param(
            'sssss',
            $datos['nombre'],
            $datos['email'],
            $datos['telefono'],
            $password_hash,
            $estado
        );

        if ($stmt->execute()) {
            $id = $this->conexion->insert_id;
            $stmt->close();
            return $id;
        } else {
            $stmt->close();
            return false;
        }
    }

    /**
     * Actualizar usuario
     * @param int $id
     * @param array $datos
     * @return bool
     */
    public function actualizarUsuario($id, $datos) {
        $campos = [];
        $tipos = '';
        $valores = [];

        if (isset($datos['nombre'])) {
            $campos[] = 'nombre = ?';
            $tipos .= 's';
            $valores[] = $datos['nombre'];
        }

        if (isset($datos['email'])) {
            $campos[] = 'email = ?';
            $tipos .= 's';
            $valores[] = $datos['email'];
        }

        if (isset($datos['telefono'])) {
            $campos[] = 'telefono = ?';
            $tipos .= 's';
            $valores[] = $datos['telefono'];
        }

        if (isset($datos['estado'])) {
            $campos[] = 'estado = ?';
            $tipos .= 's';
            $valores[] = $datos['estado'];
        }

        if (empty($campos)) {
            return false;
        }

        $query = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        $tipos .= 'i';
        $valores[] = $id;

        $stmt->bind_param($tipos, ...$valores);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Eliminar usuario
     * @param int $id
     * @return bool
     */
    public function eliminarUsuario($id) {
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        $stmt->bind_param('i', $id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtener estadísticas
     * @return array
     */
    public function obtenerEstadisticas() {
        $stats = [
            'total' => 0,
            'activos' => 0,
            'inactivos' => 0,
            'este_mes' => 0
        ];

        // Total
        $result = $this->conexion->query("SELECT COUNT(*) as total FROM usuarios");
        $row = $result->fetch_assoc();
        $stats['total'] = $row['total'];

        // Activos
        $result = $this->conexion->query("SELECT COUNT(*) as activos FROM usuarios WHERE estado = 'activo'");
        $row = $result->fetch_assoc();
        $stats['activos'] = $row['activos'];

        // Inactivos
        $result = $this->conexion->query("SELECT COUNT(*) as inactivos FROM usuarios WHERE estado = 'inactivo'");
        $row = $result->fetch_assoc();
        $stats['inactivos'] = $row['inactivos'];

        // Este mes
        $result = $this->conexion->query(
            "SELECT COUNT(*) as este_mes FROM usuarios 
             WHERE MONTH(fecha_creacion) = MONTH(NOW()) 
             AND YEAR(fecha_creacion) = YEAR(NOW())"
        );
        $row = $result->fetch_assoc();
        $stats['este_mes'] = $row['este_mes'];

        return $stats;
    }

    /**
     * Verificar si el email existe
     * @param string $email
     * @return bool
     */
    public function emailExiste($email) {
        $query = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    /**
     * Cerrar conexión
     */
    public function cerrar() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->cerrar();
    }
}
?>
