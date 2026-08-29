<?php
declare(strict_types=1);

/**
 * MVP persistence uses a local SQLite file.  Application code talks only to
 * PDO, so a future MySQL adapter can replace this connection without changing
 * models or controllers.
 */
const DATABASE_DRIVER = 'sqlite';
const DATABASE_PATH = APP_ROOT . '/storage/criteval_pro.sqlite';
const DATABASE_SCHEMA_PATH = APP_ROOT . '/database/criteval_pro.sqlite.sql';

function db_settings(): array
{
    return [
        'driver' => DATABASE_DRIVER,
        'path' => DATABASE_PATH,
    ];
}

/** Return safe, non-secret connection diagnostics for the superadmin panel. */
function database_status_info(): array
{
    $settings = db_settings();
    $driver = (string) ($settings['driver'] ?? DATABASE_DRIVER);
    $path = (string) ($settings['path'] ?? DATABASE_PATH);
    $info = [
        'driver' => $driver, 'type' => strtoupper($driver),
        'name' => $driver === 'sqlite' ? basename($path) : (string) ($settings['database'] ?? '—'),
        'host' => $driver === 'sqlite' ? 'Local filesystem' : (string) ($settings['host'] ?? '—'),
        'port' => $driver === 'sqlite' ? '—' : (string) ($settings['port'] ?? '—'),
        'path' => $driver === 'sqlite' ? $path : '—', 'size' => '—', 'modified' => '—',
        'status' => 'Unavailable', 'tables' => '—', 'server' => '—', 'error' => null,
    ];
    if ($driver === 'sqlite' && is_file($path)) {
        $info['size'] = number_format((int) filesize($path), 0, ',', ' ') . ' bytes';
        $info['modified'] = date('Y-m-d H:i:s', (int) filemtime($path));
    }
    try {
        $pdo = db();
        $info['status'] = 'Connected';
        $info['tables'] = (string) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")->fetchColumn();
        $info['server'] = (string) $pdo->query('SELECT sqlite_version()')->fetchColumn();
    } catch (Throwable $exception) {
        $info['error'] = $exception->getMessage();
        $info['status'] = 'Connection failed';
    }
    return $info;
}

function create_pdo_connection(?array $settings = null, bool $initialize = true): PDO
{
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('L’extension PHP pdo_sqlite est requise pour utiliser Criteval Pro.');
    }

    $settings ??= db_settings();
    $path = $settings['path'] ?? DATABASE_PATH;
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Impossible de créer le répertoire de données SQLite.');
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');

    if ($initialize) {
        initialize_database($pdo);
    }

    return $pdo;
}

function db(): PDO
{
    static $pdo = null;
    return $pdo ??= create_pdo_connection();
}

function database_is_ready(): bool
{
    try {
        $stmt = create_pdo_connection(null, false)->query("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'users'");
        return $stmt->fetchColumn() !== false;
    } catch (Throwable $exception) {
        return false;
    }
}

function initialize_database(?PDO $pdo = null): void
{
    $pdo ??= create_pdo_connection(null, false);
    $exists = $pdo->query("SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = 'users'")->fetchColumn();
    if ($exists !== false) {
        return;
    }

    if (!is_file(DATABASE_SCHEMA_PATH)) {
        throw new RuntimeException('Le schéma SQLite est introuvable.');
    }

    $pdo->exec((string) file_get_contents(DATABASE_SCHEMA_PATH));
}

// Compatibility helpers used by the legacy installer. SQLite needs no server
// credentials or dump export; initialization is handled directly above.
function validate_database_connection(array $settings, ?string &$error = null): bool
{
    try {
        create_pdo_connection(null, false);
        return true;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        return false;
    }
}

function save_database_settings(array $settings): bool
{
    return true;
}

function ensure_init_db_sql(?string &$error = null): bool
{
    return is_file(DATABASE_SCHEMA_PATH);
}

function run_sql_file(PDO $pdo, string $path, ?string &$error = null): bool
{
    try {
        initialize_database($pdo);
        return true;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        return false;
    }
}

function mark_database_installed(): bool
{
    return true;
}
