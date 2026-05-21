<?php
require_once __DIR__ . '/../Core/Controller.php';
require_once __DIR__ . '/../Models/User.php';

class AuthController extends Controller {
    public function showLogin(string $error = ''): void {
        $this->startSession();
        $this->view('auth.login', ['error' => $error]);
    }

    public function login(): void {
        $this->startSession();

        if (!empty($_SESSION['usuario_id'])) {
            $this->redirect('dashboard.php');
        }

        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($email) || empty($password)) {
            $this->showLogin('Por favor ingresa email y contraseña');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showLogin('Email no válido');
            return;
        }

        try {
            $userModel = new User();
            $usuario = $userModel->obtenerUsuarioPorEmail($email);

            if (!$usuario) {
                $userModel->cerrar();
                $this->showLogin('Email o contraseña incorrectos');
                return;
            }

            $password_guardado = $usuario['password'];
            $login_valido = false;
            $rehash_necesario = false;

            if (password_verify($password, $password_guardado)) {
                $login_valido = true;
                $rehash_necesario = password_needs_rehash(
                    $password_guardado,
                    PASSWORD_BCRYPT,
                    ['cost' => BCRYPT_COST]
                );
            } elseif ($password === $password_guardado) {
                $login_valido = true;
                $rehash_necesario = true;
            }

            if ($login_valido) {
                if ($rehash_necesario) {
                    $nuevo_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
                    $userModel->actualizarPassword($usuario['id'], $nuevo_hash);
                }

                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['login_time'] = time();

                $userModel->cerrar();
                $this->redirect('dashboard.php');
                return;
            }

            $userModel->cerrar();
            $this->showLogin('Email o contraseña incorrectos');
        } catch (Exception $e) {
            $this->showLogin('Error al procesar login: ' . $e->getMessage());
        }
    }

    public function logout(): void {
        $this->startSession();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->redirect('login.php');
    }
}
