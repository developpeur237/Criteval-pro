<?php
declare(strict_types=1);

function render_view(string $relativePath, array $data = []): void
{
    $path = APP_ROOT . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    if (!is_file($path)) {
        http_response_code(500);
        echo 'View not found.';
        return;
    }

    extract($data, EXTR_SKIP);
    require $path;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
