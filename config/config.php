<?php
declare(strict_types=1);

define('APP_NAME', 'Criteval Pro');
define('APP_ENV', 'development');
define('APP_ROOT', dirname(__DIR__));
define('BASE_URL', rtrim($_ENV['BASE_URL'] ?? dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'));
define('APP_TIMEZONE', 'Africa/Douala');

date_default_timezone_set(APP_TIMEZONE);

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
