<?php
require_once __DIR__ . '/View.php';

class Controller {
    protected function startSession(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    protected function view(string $view, array $data = []): void {
        View::render($view, $data);
    }

    protected function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    protected function jsonResponse(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
