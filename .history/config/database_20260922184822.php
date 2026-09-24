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

function reset_database_to_initial_state(): string
{
    if (!is_file(DATABASE_SCHEMA_PATH)) {
        throw new RuntimeException('Le schéma SQLite est introuvable.');
    }

    $backupPath = DATABASE_PATH . '.backup-' . date('Ymd-His') . '.sqlite';
    if (is_file(DATABASE_PATH) && !copy(DATABASE_PATH, $backupPath)) {
        throw new RuntimeException('Impossible de créer la sauvegarde de sécurité.');
    }

    $pdo = db();
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
    try {
        $pdo->exec('PRAGMA foreign_keys = OFF');
        $pdo->beginTransaction();
        foreach ($tables as $table) {
            $quotedTable = '"' . str_replace('"', '""', (string) $table) . '"';
            $pdo->exec('DROP TABLE IF EXISTS ' . $quotedTable);
        }
        $pdo->exec((string) file_get_contents(DATABASE_SCHEMA_PATH));
        upgrade_domain_schema($pdo);
        $pdo->commit();
        $pdo->exec('PRAGMA foreign_keys = ON');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $pdo->exec('PRAGMA foreign_keys = ON');
        throw $exception;
    }

    return $backupPath;
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
            'contact_name' => 'TEXT',
            'contact_phone' => 'TEXT',
            'contact_email' => 'TEXT',
            'website' => 'TEXT',
            'budget_requested' => 'REAL',
            'duration_months' => 'INTEGER',
            'logo_path' => 'TEXT',
        ];
        foreach ($projectColumns as $name => $definition) {
            if (!in_array($name, $existingColumns, true)) {
                $pdo->exec('ALTER TABLE projects ADD COLUMN ' . $name . ' ' . $definition);
            }
        }

        $criteriaColumns = $pdo->query('PRAGMA table_info(criteria)')->fetchAll(PDO::FETCH_ASSOC);
        if (!in_array('source_template_id', array_column($criteriaColumns, 'name'), true)) {
            $pdo->exec('ALTER TABLE criteria ADD COLUMN source_template_id INTEGER');
        }
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS criteria_templates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                label TEXT NOT NULL,
                description TEXT,
                weight REAL NOT NULL DEFAULT 1.0,
                max_score REAL NOT NULL DEFAULT 20.0,
                is_required INTEGER NOT NULL DEFAULT 1,
                order_index INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS project_criteria (
                project_id INTEGER NOT NULL,
                criteria_id INTEGER NOT NULL,
                order_index INTEGER NOT NULL DEFAULT 0,
                PRIMARY KEY (project_id, criteria_id),
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
                FOREIGN KEY (criteria_id) REFERENCES criteria(id) ON DELETE CASCADE
            )'
        );
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_criteria_source_template ON criteria(source_template_id)');

        $templateCount = (int) $pdo->query('SELECT COUNT(*) FROM criteria_templates')->fetchColumn();
        if ($templateCount === 0) {
            $templates = [
                ['Participation régulière aux réunions et activités CS4ME (minimum 75 %) au cours de l’année', 'Présence documentée aux réunions et activités CS4ME.'],
                ['Réponse aux consultations, enquêtes et appels à contribution dans les délais demandés', 'Réponses transmises dans les délais convenus.'],
                ['Au moins X relais par trimestre sur les réseaux sociaux ou canaux de communication de l’organisation', 'Relais et publications vérifiables sur les canaux de communication.'],
                ['Contribution financière aux activités communes', 'Cofinancement d’événements ou d’actions collectives.'],
                ['Partage régulier de données, rapports et résultats d’activité par trimestre', 'Données et rapports d’activité partagés chaque trimestre.'],
                ['Participation au recrutement de nouveaux membres CS4ME au niveau pays', 'Contribution attestée au recrutement de nouveaux membres.'],
                ['Mise en œuvre d’activités de terrain documentées et rapportées', 'Activités de terrain accompagnées de comptes rendus.'],
                ['Actions de plaidoyer menées au nom ou en lien avec CS4ME', 'Actions de plaidoyer documentées et reliées à CS4ME.'],
                ['Au moins une activité de renforcement des capacités par an', 'Activité organisée par CS4ME ou ses partenaires.'],
                ['Formation des pairs, partage d’expertise et mentorat', 'Transmission d’expertise à des organisations moins expérimentées.'],
            ];
            $insertTemplate = $pdo->prepare('INSERT INTO criteria_templates (label, description, order_index) VALUES (:label, :description, :order_index)');
            foreach ($templates as $index => [$label, $description]) {
                $insertTemplate->execute(['label' => $label, 'description' => $description, 'order_index' => $index + 1]);
            }
        }
        $pdo->exec(
            'UPDATE criteria SET source_template_id = (
                SELECT t.id FROM criteria_templates t WHERE t.label = criteria.label LIMIT 1
             ) WHERE source_template_id IS NULL AND EXISTS (
                SELECT 1 FROM criteria_templates t WHERE t.label = criteria.label
             )'
        );

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
                project_id INTEGER,
                capacity INTEGER NOT NULL DEFAULT 50,
                format TEXT NOT NULL DEFAULT "hybride",
                evaluation_weight REAL NOT NULL DEFAULT 0,
                public_slug TEXT NOT NULL UNIQUE,
                is_active INTEGER NOT NULL DEFAULT 1,
                created_by INTEGER,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
                FOREIGN KEY (created_by) REFERENCES users(id)
            )'
        );
        $trainingColumns = array_column($pdo->query('PRAGMA table_info(training_sessions)')->fetchAll(PDO::FETCH_ASSOC), 'name');
        foreach (['project_id' => 'INTEGER', 'capacity' => 'INTEGER NOT NULL DEFAULT 50', 'format' => 'TEXT NOT NULL DEFAULT "hybride"', 'evaluation_weight' => 'REAL NOT NULL DEFAULT 0', 'criteria_json' => 'TEXT'] as $name => $definition) {
            if (!in_array($name, $trainingColumns, true)) {
                $pdo->exec('ALTER TABLE training_sessions ADD COLUMN ' . $name . ' ' . $definition);
            }
        }
        $pdo->exec('UPDATE training_sessions SET project_id = (SELECT id FROM projects ORDER BY id LIMIT 1) WHERE project_id IS NULL');
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS training_attendance (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                submission_id INTEGER NOT NULL,
                attendance_status TEXT NOT NULL DEFAULT "registered",
                grade REAL,
                comment TEXT,
                included_in_evaluation INTEGER NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(session_id, submission_id),
                FOREIGN KEY (session_id) REFERENCES training_sessions(id) ON DELETE CASCADE,
                FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
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
        if (!$projects) {
            $pdo->exec(
                "INSERT INTO projects (title, organization, intervention_zone, description, status, created_by)
                 VALUES ('Organisation pilote CS4ME', 'Organisation pilote CS4ME', 'Afrique de l’Ouest', 'Fiche d’organisation CS4ME à compléter.', 'active', NULL)"
            );
            $projects = [$pdo->lastInsertId()];
        }
        $insert = $pdo->prepare(
            'INSERT INTO criteria (project_id, label, description, weight, max_score, is_required, order_index)
             VALUES (:project_id, :label, :description, 1.00, 20.00, 1, :order_index)'
        );
        foreach ($projects as $projectId) {
            $criteriaCount = $pdo->prepare('SELECT COUNT(*) FROM criteria WHERE project_id = :project_id');
            $criteriaCount->execute(['project_id' => (int) $projectId]);
            if ((int) $criteriaCount->fetchColumn() > 0) {
                continue;
            }
            foreach ($criteria as [$label, $description, $order]) {
                $insert->execute([
                    'project_id' => (int) $projectId,
                    'label' => $label,
                    'description' => $description,
                    'order_index' => $order,
                ]);
            }
        }

        $formExists = (int) $pdo->query('SELECT COUNT(*) FROM forms')->fetchColumn();
        if ($formExists === 0) {
            $form = $pdo->prepare(
                'INSERT INTO forms (title, description, project_id, layout_json, status, created_by)
                 VALUES (:title, :description, :project_id, :layout_json, :status, NULL)'
            );
            $form->execute([
                'title' => 'Évaluation d’organisation CS4ME',
                'description' => 'Formulaire de présentation de l’organisation et de dépôt des preuves par critère.',
                'project_id' => (int) $projects[0],
                'layout_json' => json_encode(['fields' => [
                    ['type' => 'text', 'label' => 'Nom de l’organisation'],
                    ['type' => 'text', 'label' => 'Pays d’appartenance'],
                    ['type' => 'textarea', 'label' => 'Présentation de l’organisation'],
                ]], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
            ]);
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
