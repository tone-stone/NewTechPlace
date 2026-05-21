<?php
require_once __DIR__ . '/Database.php';

class User {
    private Database $database;

    public function __construct() {
        $this->database = new Database();
    }

    public function obtenerUsuarios(array $filtros = []): array {
        return $this->database->obtenerUsuarios($filtros);
    }

    public function obtenerUsuarioPorId(int $id): ?array {
        return $this->database->obtenerUsuarioPorId($id);
    }

    public function obtenerUsuarioPorEmail(string $email): ?array {
        return $this->database->obtenerUsuarioPorEmail($email);
    }

    public function crearUsuario(array $datos) {
        return $this->database->crearUsuario($datos);
    }

    public function actualizarUsuario(int $id, array $datos): bool {
        return $this->database->actualizarUsuario($id, $datos);
    }

    public function actualizarPassword(int $id, string $passwordHash): bool {
        return $this->database->actualizarPassword($id, $passwordHash);
    }

    public function eliminarUsuario(int $id): bool {
        return $this->database->eliminarUsuario($id);
    }

    public function obtenerEstadisticas(): array {
        return $this->database->obtenerEstadisticas();
    }

    public function emailExiste(string $email): bool {
        return $this->database->emailExiste($email);
    }

    public function cerrar(): void {
        $this->database->cerrar();
    }
}
