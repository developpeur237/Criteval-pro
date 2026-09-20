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
        upgrade_domain_schema($pdo);
        return;
    }

    if (!is_file(DATABASE_SCHEMA_PATH)) {
        throw new RuntimeException('Le schéma SQLite est introuvable.');
    }

    $pdo->exec((string) file_get_contents(DATABASE_SCHEMA_PATH));
    upgrade_domain_schema($pdo);
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

function upgrade_domain_schema(PDO $pdo): void
{
    $columns = $pdo->query("PRAGMA table_info(projects)")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'name');
        $projectColumns = [
            'domains' => 'TEXT',
            'target_audiences' => 'TEXT',
            'legal_status' => "TEXT NOT NULL DEFAULT 'non_legal'",
            'country_code' => "TEXT NOT NULL DEFAULT ''",
            'project_count' => 'INTEGER',
        ];
        foreach ($projectColumns as $name => $definition) {
            if (!in_array($name, $existingColumns, true)) {
                $pdo->exec('ALTER TABLE projects ADD COLUMN ' . $name . ' ' . $definition);
            }
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS organization_evidence (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                submission_id INTEGER NOT NULL,
                criteria_id INTEGER NOT NULL,
                file_path TEXT NOT NULL,
                original_name TEXT NOT NULL,
                mime_type TEXT,
                file_size INTEGER NOT NULL DEFAULT 0,
                uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
                FOREIGN KEY (criteria_id) REFERENCES criteria(id) ON DELETE CASCADE
            )'
        );
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_evidence_submission_criteria ON organization_evidence(submission_id, criteria_id)');

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS training_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                objective TEXT NOT NULL,
                session_date DATE NOT NULL,
                public_slug TEXT NOT NULL UNIQUE,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_by INTEGER,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )'
        );

        $criteria = [
            ['Participation régulière aux réunions et activités CS4ME (minimum 75 %) au cours de l’année', 'Présence documentée aux réunions et activités CS4ME.', 1],
            ['Réponse aux consultations, enquêtes et appels à contribution dans les délais demandés', 'Réponses transmises dans les délais convenus.', 2],
            ['Au moins X relais par trimestre sur les réseaux sociaux ou canaux de communication de l’organisation', 'Relais et publications vérifiables sur les canaux de communication.', 3],
            ['Contribution financière aux activités communes', 'Cofinancement d’événements ou d’actions collectives.', 4],
            ['Partage régulier de données, rapports et résultats d’activité par trimestre', 'Données et rapports d’activité partagés chaque trimestre.', 5],
            ['Participation au recrutement de nouveaux membres CS4ME au niveau pays', 'Contribution attestée au recrutement de nouveaux membres.', 6],
            ['Mise en œuvre d’activités de terrain documentées et rapportées', 'Activités de terrain accompagnées de comptes rendus.', 7],
            ['Actions de plaidoyer menées au nom ou en lien avec CS4ME', 'Actions de plaidoyer documentées et reliées à CS4ME.', 8],
            ['Au moins une activité de renforcement des capacités par an', 'Activité organisée par CS4ME ou ses partenaires.', 9],
            ['Formation des pairs, partage d’expertise et mentorat', 'Transmission d’expertise à des organisations moins expérimentées.', 10],
        ];
        $projects = $pdo->query('SELECT id FROM projects ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        $insert = $pdo->prepare(
            'INSERT INTO criteria (project_id, label, description, weight, max_score, is_required, order_index)
             VALUES (:project_id, :label, :description, 1.00, 20.00, 1, :order_index)'
        );
        foreach ($projects as $projectId) {
            foreach ($criteria as [$label, $description, $order]) {
                $check = $pdo->prepare('SELECT 1 FROM criteria WHERE project_id = :project_id AND order_index = :order_index LIMIT 1');
                $check->execute(['project_id' => (int) $projectId, 'order_index' => $order]);
                if ($check->fetchColumn() === false) {
                    $insert->execute([
                        'project_id' => (int) $projectId,
                        'label' => $label,
                        'description' => $description,
                        'order_index' => $order,
                    ]);
                }
            }
        }
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
