<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function sanitize_text(?string $value): string
{
    return trim(strip_tags((string) $value));
}
