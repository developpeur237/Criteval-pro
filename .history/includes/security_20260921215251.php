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

function sanitize_text($value): string
{
    if ($value === null || is_array($value) || is_object($value)) {
        return '';
    }
    return trim(strip_tags((string) $value));
}

function sanitize_text_max($value, int $maxLength = 10000): string
{
    $value = sanitize_text(is_scalar($value) ? (string) $value : null);
    return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
}

function sanitize_nested_scalars($value, int $maxDepth = 4, int $maxLength = 10000)
{
    if ($maxDepth < 0) return null;
    if (is_array($value)) {
        $result = [];
        $count = 0;
        foreach ($value as $key => $item) {
            if (++$count > 200) break;
            $safeKey = sanitize_text_max(is_scalar($key) ? (string) $key : '', 120);
            if ($safeKey !== '') $result[$safeKey] = sanitize_nested_scalars($item, $maxDepth - 1, $maxLength);
        }
        return $result;
    }
    return is_scalar($value) ? sanitize_text_max($value, $maxLength) : null;
}

function security_rate_limit(string $key, int $maxAttempts, int $windowSeconds): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $fileKey = hash('sha256', $key . '|' . $ip);
    $directory = APP_ROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'rate_limits';
    if (!is_dir($directory)) @mkdir($directory, 0700, true);
    $path = $directory . DIRECTORY_SEPARATOR . $fileKey . '.json';
    $handle = @fopen($path, 'c+');
    if (is_resource($handle)) {
        $allowed = true;
        if (flock($handle, LOCK_EX)) {
            rewind($handle);
            $raw = stream_get_contents($handle);
            $bucket = json_decode((string) $raw, true);
            $now = time();
            if (!is_array($bucket) || ($now - (int) ($bucket['started'] ?? 0)) >= $windowSeconds) {
                $bucket = ['started' => $now, 'count' => 0];
            }
            $bucket['count'] = (int) ($bucket['count'] ?? 0) + 1;
            $allowed = $bucket['count'] <= $maxAttempts;
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($bucket));
            fflush($handle);
            flock($handle, LOCK_UN);
            fclose($handle);
            if (!$allowed) return false;
            return true;
        } else {
            fclose($handle);
        }
    }

    // Keep a session fallback if protected storage is temporarily unavailable.
    $now = time();
    $bucket = $_SESSION['_rate_limits'][$key] ?? ['started' => $now, 'count' => 0];
    if (!is_array($bucket) || ($now - (int) ($bucket['started'] ?? 0)) >= $windowSeconds) {
        $bucket = ['started' => $now, 'count' => 0];
    }
    $bucket['count'] = (int) ($bucket['count'] ?? 0) + 1;
    $_SESSION['_rate_limits'][$key] = $bucket;
    return $bucket['count'] <= $maxAttempts;
}
