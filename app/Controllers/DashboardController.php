<?php
require_once __DIR__ . '/../Core/Controller.php';

class DashboardController extends Controller {
    public function index(): void {
        $this->startSession();

        if (empty($_SESSION['usuario_id']) || empty($_SESSION['usuario_nombre'])) {
            $this->redirect('login.php');
        }

        $usuario_actual = [
            'id' => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['usuario_nombre'],
            'email' => $_SESSION['usuario_email'] ?? 'usuario@techplace.com',
            'inicial' => strtoupper($_SESSION['usuario_nombre'][0] ?? 'U')
        ];

        $this->view('dashboard.index', ['usuario_actual' => $usuario_actual]);
    }
}
