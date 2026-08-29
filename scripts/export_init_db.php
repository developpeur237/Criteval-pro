<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!is_file(DATABASE_SCHEMA_PATH)) {
    fwrite(STDERR, "SQLite schema not found: " . DATABASE_SCHEMA_PATH . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "SQLite MVP schema: " . DATABASE_SCHEMA_PATH . PHP_EOL);
