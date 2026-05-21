<?php
class View {
    public static function render(string $view, array $data = []) {
        extract($data, EXTR_SKIP);
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($viewFile)) {
            throw new Exception('View not found: ' . $viewFile);
        }

        require $viewFile;
    }
}
