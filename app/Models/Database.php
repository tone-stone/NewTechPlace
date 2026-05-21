<?php
require_once __DIR__ . '/../../config.php';

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

            $this->conexion->set_charset('utf8mb4');
        } catch (Exception $e) {
            die(json_encode(['error' => $e->getMessage()]));
        }
    }

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

    public function obtenerUsuarioPorEmail($email) {
        $query = "SELECT id, nombre, email, telefono, password, estado, fecha_creacion FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        return $usuario;
    }

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

    public function actualizarPassword($id, $passwordHash) {
        $query = "UPDATE usuarios SET password = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->conexion->error);
        }

        $stmt->bind_param('si', $passwordHash, $id);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

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

    public function obtenerEstadisticas() {
        $stats = [
            'total' => 0,
            'activos' => 0,
            'inactivos' => 0,
            'este_mes' => 0
        ];

        $result = $this->conexion->query("SELECT COUNT(*) as total FROM usuarios");
        $row = $result->fetch_assoc();
        $stats['total'] = $row['total'];

        $result = $this->conexion->query("SELECT COUNT(*) as activos FROM usuarios WHERE estado = 'activo'");
        $row = $result->fetch_assoc();
        $stats['activos'] = $row['activos'];

        $result = $this->conexion->query("SELECT COUNT(*) as inactivos FROM usuarios WHERE estado = 'inactivo'");
        $row = $result->fetch_assoc();
        $stats['inactivos'] = $row['inactivos'];

        $result = $this->conexion->query(
            "SELECT COUNT(*) as este_mes FROM usuarios 
             WHERE MONTH(fecha_creacion) = MONTH(NOW()) 
             AND YEAR(fecha_creacion) = YEAR(NOW())"
        );
        $row = $result->fetch_assoc();
        $stats['este_mes'] = $row['este_mes'];

        return $stats;
    }

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

    public function cerrar() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }

    public function __destruct() {
        $this->cerrar();
    }
}
