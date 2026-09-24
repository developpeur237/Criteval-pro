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

function safe_return_url(?string $candidate, string $fallback): string
{
    $candidate = trim((string) $candidate);
    if ($candidate === '' || str_starts_with($candidate, '//')) return $fallback;
    $parts = parse_url($candidate);
    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) return $fallback;
    $path = (string) ($parts['path'] ?? '');
    $base = rtrim((string) (defined('BASE_URL') ? BASE_URL : ''), '/');
    if ($path === '' || ($base !== '' && $path !== $base && !str_starts_with($path, $base . '/'))) return $fallback;
    return $path . (isset($parts['query']) ? '?' . $parts['query'] : '');
}

function flash_redirect_url(string $url, string $message, string $type = 'success'): string
{
    $separator = str_contains($url, '?') ? '&' : '?';
    return $url . $separator . http_build_query([
        'flash_message' => $message,
        'flash_type' => in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info',
    ]);
}
