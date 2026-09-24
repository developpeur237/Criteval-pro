<?php
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app_settings.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/otp.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/upload.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Project.php';
require_once __DIR__ . '/models/Criteria.php';
require_once __DIR__ . '/models/Form.php';
require_once __DIR__ . '/models/Submission.php';
require_once __DIR__ . '/models/TrainingSession.php';
require_once __DIR__ . '/models/Ranking.php';
require_once __DIR__ . '/models/Evaluation.php';
require_once __DIR__ . '/models/Dashboard.php';
require_once __DIR__ . '/models/Notification.php';
require_once __DIR__ . '/models/FormField.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
if (APP_ENV === 'production' && ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$base = trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

if ($base !== '' && str_starts_with($path, $base)) {
    $path = trim(substr($path, strlen($base)), '/');
}

$route = $path === '' ? 'home' : $path;
$route = trim($route, '/');

if ($route !== 'install' && !database_is_ready()) {
    header('Location: ' . BASE_URL . '/install');
    exit;
}

if ($route === 'install') {
    $settings = db_settings();
    $error = null;
    $success = null;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        try {
            initialize_database();
            $success = 'La base SQLite locale est prête.';
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            exit('Jeton CSRF invalide.');
        }
        $settings = [
            'host' => sanitize_text($_POST['host'] ?? '127.0.0.1'),
            'port' => (int) ($_POST['port'] ?? 3306),
            'username' => sanitize_text($_POST['username'] ?? ''),
            'password' => (string) ($_POST['password'] ?? ''),
            'database' => sanitize_text($_POST['database'] ?? ''),
            'socket' => sanitize_text($_POST['socket'] ?? ''),
            'charset' => sanitize_text($_POST['charset'] ?? 'utf8mb4'),
            'collation' => sanitize_text($_POST['collation'] ?? 'utf8mb4_unicode_ci'),
            'use_ssl' => isset($_POST['use_ssl']) && $_POST['use_ssl'] === '1',
        ];

        if (!validate_database_connection($settings, $error)) {
            render_view('modules/Public/views/install.php', [
                'error' => $error,
                'settings' => $settings,
            ]);
            exit;
        }

        if (!save_database_settings($settings)) {
            $error = 'Impossible de sauvegarder la configuration de la base de données.';
            render_view('modules/Public/views/install.php', [
                'error' => $error,
                'settings' => $settings,
            ]);
            exit;
        }

        if (!ensure_init_db_sql($error)) {
            render_view('modules/Public/views/install.php', [
                'error' => $error,
                'settings' => $settings,
            ]);
            exit;
        }

        try {
            $pdo = create_pdo_connection($settings, false);
            if (!run_sql_file($pdo, DATABASE_SCHEMA_PATH, $error)) {
                render_view('modules/Public/views/install.php', [
                    'error' => $error,
                    'settings' => $settings,
                ]);
                exit;
            }
        } catch (PDOException $ex) {
            $error = $ex->getMessage();
            render_view('modules/Public/views/install.php', [
                'error' => $error,
                'settings' => $settings,
            ]);
            exit;
        }

        if (!mark_database_installed()) {
            $error = 'La base de données a été initialisée, mais impossible de créer le fichier de verrouillage.';
            render_view('modules/Public/views/install.php', [
                'error' => $error,
                'settings' => $settings,
            ]);
            exit;
        }

        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    render_view('modules/Public/views/install.php', [
        'settings' => $settings,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'login') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        render_view('modules/Auth/views/login.php', ['error' => 'Session de connexion expiree. Rechargez la page.']);
        exit;
    }
    $credential = sanitize_text($_POST['credential'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $user = User::authenticate($credential, $password);

    if ($user) {
        login_user($user);
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    }

    http_response_code(401);
    render_view('modules/Auth/views/login.php', [
        'error' => 'Identifiant ou mot de passe incorrect.',
        'credential' => $credential,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/projects') {
    require_permission('projets', (int) ($_POST['id'] ?? 0) > 0 ? 'update' : 'create');

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo 'Jeton CSRF invalide.';
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $title = sanitize_text($_POST['title'] ?? '');
    $organization = sanitize_text($_POST['organization'] ?? '');
    $intervention_zone = sanitize_text($_POST['intervention_zone'] ?? '');
    $description = sanitize_text($_POST['description'] ?? '');
    $domains = sanitize_text($_POST['domains'] ?? '');
    $targetAudiences = sanitize_text($_POST['target_audiences'] ?? '');
    $legalStatus = in_array($_POST['legal_status'] ?? '', ['legal', 'non_legal'], true) ? $_POST['legal_status'] : 'non_legal';
    $countryCode = sanitize_text($_POST['country_code'] ?? '');
    if (preg_match('/^.{0,20}$/u', $countryCode) !== 1) {
        http_response_code(422);
        exit('Le nom du pays ne doit pas dépasser 20 caractères.');
    }
    $projectCount = ($_POST['project_count'] ?? '') === '' ? null : max(0, (int) $_POST['project_count']);
    $budgetRequested = ($_POST['budget_requested'] ?? '') === '' ? null : max(0, (float) $_POST['budget_requested']);
    $durationMonths = ($_POST['duration_months'] ?? '') === '' ? null : max(1, (int) $_POST['duration_months']);
    $contactEmail = filter_var(trim((string) ($_POST['contact_email'] ?? '')), FILTER_VALIDATE_EMAIL) ?: null;
    $status = in_array($_POST['status'] ?? '', ['draft', 'active', 'closed', 'archived'], true) ? $_POST['status'] : 'draft';
    $createdBy = current_user()['id'] ?? null;
    $criteriaConfigured = isset($_POST['criteria_configured']);
    $criteriaTemplateIds = array_key_exists('criteria_template_ids', $_POST)
        ? array_map('intval', (array) $_POST['criteria_template_ids'])
        : (isset($_POST['criteria_selection_submitted']) ? [] : array_map('intval', array_column(Criteria::templates(), 'id')));
    $customCriteria = [];
    foreach ((array) ($_POST['custom_criteria'] ?? []) as $custom) {
        if (!is_array($custom)) {
            continue;
        }
        $customCriteria[] = [
            'id' => (int) ($custom['id'] ?? 0),
            'label' => sanitize_text($custom['label'] ?? ''),
            'description' => sanitize_text($custom['description'] ?? ''),
            'weight' => (float) ($custom['weight'] ?? 1),
            'max_score' => (float) ($custom['max_score'] ?? 20),
            'is_required' => isset($custom['is_required']) ? 1 : 0,
        ];
    }

    if ($title !== '') {
        $existing = $id > 0 ? Project::find($id) : null;
        $logoPath = $existing['logo_path'] ?? null;
        $logo = $_FILES['logo'] ?? null;
        if ($logo && (int) ($logo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ((int) $logo['error'] !== UPLOAD_ERR_OK || (int) $logo['size'] > 2 * 1024 * 1024 || !is_uploaded_file((string) $logo['tmp_name'])) {
                http_response_code(422);
                exit('Le logo doit être une image valide de 2 Mo maximum.');
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file((string) $logo['tmp_name']);
            $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            if (!isset($extensions[$mime])) {
                http_response_code(422);
                exit('Format de logo non pris en charge.');
            }
            $directory = APP_ROOT . '/uploads/organizations';
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                http_response_code(500);
                exit('Impossible de créer le dossier des logos.');
            }
            $filename = 'organization-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime];
            if (!move_uploaded_file((string) $logo['tmp_name'], $directory . '/' . $filename)) {
                http_response_code(500);
                exit('Impossible d’enregistrer le logo.');
            }
            $logoPath = 'uploads/organizations/' . $filename;
        }
        $data = [
            'title' => $title,
            'organization' => $organization,
            'domains' => $domains,
            'target_audiences' => $targetAudiences,
            'legal_status' => $legalStatus,
            'country_code' => $countryCode,
            'project_count' => $projectCount,
            'intervention_zone' => $intervention_zone,
            'description' => $description,
            'contact_name' => sanitize_text($_POST['contact_name'] ?? ''),
            'contact_phone' => sanitize_text($_POST['contact_phone'] ?? ''),
            'contact_email' => $contactEmail,
            'website' => (($website = filter_var(trim(sanitize_text_max($_POST['website'] ?? '', 500)), FILTER_VALIDATE_URL)) && preg_match('/^https?:\/\//i', $website)) ? $website : '',
            'budget_requested' => $budgetRequested,
            'duration_months' => $durationMonths,
            'logo_path' => $logoPath,
            'status' => $status,
            'created_by' => $createdBy,
        ];
        if ($id > 0 && $existing) {
            Project::update($id, $data);
            if ($criteriaConfigured) {
                Criteria::configureProject($id, $criteriaTemplateIds, $customCriteria);
            }
        } else {
            $projectId = Project::create($data);
            Criteria::configureProject($projectId, $criteriaTemplateIds, $customCriteria);
        }
    }

    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/projects/delete') {
    require_permission('projets', 'delete');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Jeton CSRF invalide.');
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0 || !Project::find($id)) {
        http_response_code(404);
        exit('Organisation introuvable.');
    }

    try {
        Project::delete($id);
    } catch (Throwable $exception) {
        http_response_code(422);
        exit('Cette organisation ne peut pas être supprimée car elle possède encore des éléments associés.');
    }

    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/sessions') {
    require_permission('formation', 'create');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Jeton CSRF invalide.');
    }
    $name = sanitize_text($_POST['name'] ?? '');
    $objective = sanitize_text($_POST['objective'] ?? '');
    $date = sanitize_text($_POST['session_date'] ?? '');
    $projectId = (int) ($_POST['project_id'] ?? 0);
    if ($name === '' || $objective === '' || $projectId <= 0 || !Project::find($projectId) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(422);
        exit('Nom, objectif et date de session requis.');
    }
    $slug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)), '-') . '-' . bin2hex(random_bytes(3));
    $sessionId = TrainingSession::create([
        'name' => $name,
        'objective' => $objective,
        'session_date' => $date,
        'project_id' => $projectId,
        'capacity' => (int) ($_POST['capacity'] ?? 50),
        'format' => in_array($_POST['format'] ?? '', ['hybride', 'presentiel', 'en_ligne'], true) ? $_POST['format'] : 'hybride',
        'evaluation_weight' => (float) ($_POST['evaluation_weight'] ?? 0),
        'criteria_ids' => (array) ($_POST['criteria_ids'] ?? []),
        'is_active' => (int) ($_POST['is_active'] ?? 1),
        'public_slug' => $slug,
        'created_by' => current_user()['id'] ?? null,
    ]);
    header('Location: ' . BASE_URL . '/dashboard?training_id=' . $sessionId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/sessions/participant') {
    require_permission('formation', 'update');
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(403); echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']); exit; }
    $sessionId = (int) ($_POST['session_id'] ?? 0);
    $submissionId = (int) ($_POST['submission_id'] ?? 0);
    if ($sessionId <= 0 || $submissionId <= 0) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Session ou participant invalide.']); exit; }
    TrainingSession::saveParticipant($sessionId, $submissionId, [
        'attendance_status' => sanitize_text($_POST['attendance_status'] ?? 'registered'),
        'grade' => $_POST['grade'] ?? null,
        'comment' => sanitize_text($_POST['comment'] ?? ''),
        'included' => isset($_POST['included']),
    ]);
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/evaluations/score') {
    require_permission('evaluations', 'update');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Jeton CSRF invalide.'); }
    $submissionId = (int) ($_POST['submission_id'] ?? 0);
    $scores = is_array($_POST['scores'] ?? null) ? $_POST['scores'] : [];
    if ($submissionId <= 0 || !$scores) { http_response_code(422); exit('Soumission et notes requises.'); }
    try {
        Evaluation::saveScores($submissionId, $scores, (int) (current_user()['id'] ?? 0));
    } catch (InvalidArgumentException $exception) {
        http_response_code(422);
        exit($exception->getMessage());
    }
    header('Location: ' . BASE_URL . '/admin/evaluations/score?saved=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/forms') {
    require_permission('formulaires', (int) ($_POST['id'] ?? 0) > 0 ? 'update' : 'create');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Jeton CSRF invalide.'); }
    $title = sanitize_text($_POST['title'] ?? '');
    $layout = (string) ($_POST['layout_json'] ?? '[]');
    try {
        $layout = Form::normalizeLayout($layout);
    } catch (Throwable $exception) {
        http_response_code(422);
        exit('Titre ou structure de formulaire invalide.');
    }
    if ($title === '') { http_response_code(422); exit('Titre de formulaire requis.'); }
    $id = Form::save([
        'id' => (int) ($_POST['id'] ?? 0), 'title' => $title,
        'description' => sanitize_text($_POST['description'] ?? ''), 'project_id' => (int) ($_POST['project_id'] ?? 0),
        'layout_json' => $layout, 'status' => in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft',
        'created_by' => current_user()['id'] ?? 0,
    ]);
    header('Location: ' . BASE_URL . '/admin/forms/builder?id=' . $id . '&saved=1'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/forms/schedules') {
    require_permission('calendrier', 'create'); header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(403); echo json_encode(['success' => false]); exit; }
    $formId = (int) ($_POST['form_id'] ?? 0);
    if (!$formId || !Form::find($formId)) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Formulaire invalide.']); exit; }
    $startDate = sanitize_text($_POST['start_datetime'] ?? '');
    $endDate = sanitize_text($_POST['end_datetime'] ?? '');
    if ($startDate === '' || $endDate === '' || strtotime($startDate) === false || strtotime($endDate) === false || strtotime($endDate) <= strtotime($startDate)) {
        http_response_code(422); echo json_encode(['success' => false, 'message' => 'Les dates d’ouverture et de clôture sont obligatoires et cohérentes.']); exit;
    }
    $form = Form::find($formId);
    $projectId = (int) ($_POST['project_id'] ?? 0);
    if ($projectId > 0 && !empty($form['project_id']) && $projectId !== (int) $form['project_id']) {
        http_response_code(422); echo json_encode(['success' => false, 'message' => 'L’organisation choisie doit correspondre à celle du formulaire.']); exit;
    }
    $projectId = $projectId ?: (int) ($form['project_id'] ?? 0);
    $allowedCountries = Form::countryList(implode(',', (array) ($_POST['allowed_countries'] ?? [])));
    Form::saveSchedule(['form_id' => $formId, 'project_id' => $projectId ?: null,
        'access_type' => in_array($_POST['access_type'] ?? '', ['public_link', 'email_list', 'admin_only'], true) ? $_POST['access_type'] : 'public_link',
        'start_datetime' => $startDate, 'end_datetime' => $endDate,
        'allowed_emails' => sanitize_text($_POST['allowed_emails'] ?? ''), 'allowed_countries' => implode(',', $allowedCountries),
        'is_active' => isset($_POST['is_active']) ? 1 : 0]);
    echo json_encode(['success' => true]); exit;
}

if ($route === 'admin/forms/schedules' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_admin(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(Form::schedules()); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/criteria') {
    require_permission('criteres', 'create');
    header('Content-Type: application/json; charset=utf-8');

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    $projectId = (int) ($_POST['project_id'] ?? 0);
    $label = sanitize_text($_POST['label'] ?? '');
    $description = sanitize_text($_POST['description'] ?? '');
    $weight = (float) ($_POST['weight'] ?? 1.0);
    $maxScore = (float) ($_POST['max_score'] ?? 20.0);
    $isRequired = isset($_POST['is_required']) ? 1 : 0;

    if ($projectId <= 0 || $label === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Organisation et libellé du critère sont requis.']);
        exit;
    }

    $criteriaId = Criteria::create([
        'project_id' => $projectId,
        'label' => $label,
        'description' => $description,
        'weight' => $weight,
        'max_score' => $maxScore,
        'is_required' => $isRequired,
    ]);

    $created = Criteria::find($criteriaId);
    echo json_encode(['success' => true, 'id' => $criteriaId, 'item' => $created]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/criteria/update') {
    require_permission('criteres', 'update');
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    $criteriaId = (int) ($_POST['id'] ?? 0);
    $label = sanitize_text($_POST['label'] ?? '');
    if ($criteriaId <= 0 || $label === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Critère et libellé requis.']);
        exit;
    }

    $updated = Criteria::update($criteriaId, [
        'label' => $label,
        'description' => sanitize_text($_POST['description'] ?? ''),
        'weight' => max(0.01, (float) ($_POST['weight'] ?? 1)),
        'max_score' => max(0.01, (float) ($_POST['max_score'] ?? 20)),
        'is_required' => isset($_POST['is_required']) ? 1 : 0,
    ]);
    echo json_encode(['success' => $updated, 'message' => $updated ? 'Critère modifié.' : 'Critère introuvable ou inchangé.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/criteria/delete') {
    require_permission('criteres', 'delete');
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    $criteriaId = (int) ($_POST['id'] ?? 0);
    if ($criteriaId <= 0 || !Criteria::find($criteriaId)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Critère introuvable.']);
        exit;
    }

    $usage = db()->prepare('SELECT COUNT(*) FROM evaluations WHERE criteria_id = :criteria_id');
    $usage->execute(['criteria_id' => $criteriaId]);
    if ((int) $usage->fetchColumn() > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Ce critère est déjà utilisé dans une évaluation et ne peut pas être supprimé.']);
        exit;
    }

    try {
        echo json_encode(['success' => Criteria::delete($criteriaId), 'message' => 'Critère supprimé.']);
    } catch (Throwable $exception) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer ce critère car il est utilisé par des données existantes.']);
    }
    exit;
}

if ($route === 'admin/criteria' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_permission('criteres', 'view');
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'items' => Criteria::all()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/settings') {
    require_permission('parametres', 'update');

    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    $currentSettings = app_settings();
    $appSettings = $currentSettings;
    $appSettings['application_name'] = sanitize_text($_POST['application_name'] ?? $appSettings['application_name']);
    $appSettings['organization'] = sanitize_text($_POST['organization'] ?? $appSettings['organization']);
    $appSettings['contact_email'] = sanitize_text($_POST['contact_email'] ?? $appSettings['contact_email']);
    $appSettings['website'] = (($website = filter_var(trim(sanitize_text_max($_POST['website'] ?? $appSettings['website'], 500)), FILTER_VALIDATE_URL)) && preg_match('/^https?:\/\//i', $website)) ? $website : '';

    $appSettings['modules'] = [
        'apercu' => isset($_POST['modules']['apercu']) && $_POST['modules']['apercu'] === '1',
        'projets' => isset($_POST['modules']['projets']) && $_POST['modules']['projets'] === '1',
        'criteres' => isset($_POST['modules']['criteres']) && $_POST['modules']['criteres'] === '1',
        'formulaires' => isset($_POST['modules']['formulaires']) && $_POST['modules']['formulaires'] === '1',
        'evaluations' => isset($_POST['modules']['evaluations']) && $_POST['modules']['evaluations'] === '1',
    ];

    $submittedSmtp = is_array($_POST['smtp'] ?? null) ? $_POST['smtp'] : [];
    $currentSmtp = $currentSettings['smtp'] ?? [];
    $smtpProfile = in_array($submittedSmtp['profile'] ?? '', ['hostinger', 'localhost', 'custom'], true)
        ? (string) $submittedSmtp['profile']
        : (string) ($currentSmtp['profile'] ?? 'hostinger');
    $smtpHost = sanitize_text($submittedSmtp['host'] ?? ($currentSmtp['host'] ?? ''));
    $smtpPort = max(1, min(65535, (int) ($submittedSmtp['port'] ?? ($currentSmtp['port'] ?? 587))));
    $smtpUsername = sanitize_text($submittedSmtp['username'] ?? ($currentSmtp['username'] ?? ''));
    $smtpSecurity = in_array($submittedSmtp['security'] ?? '', ['tls', 'ssl', 'none'], true)
        ? (string) $submittedSmtp['security']
        : (string) ($currentSmtp['security'] ?? 'tls');

    if ($smtpProfile === 'hostinger') {
        $smtpHost = 'smtp.hostinger.com';
        $smtpPort = 587;
        $smtpSecurity = 'tls';
    } elseif ($smtpProfile === 'localhost') {
        $smtpHost = '127.0.0.1';
        $smtpPort = 25;
        $smtpUsername = '';
        $smtpSecurity = 'none';
    }

    $appSettings['smtp'] = [
        'profile' => $smtpProfile,
        'transport' => in_array($submittedSmtp['transport'] ?? '', ['auto', 'smtp', 'php'], true) ? (string) $submittedSmtp['transport'] : ($currentSmtp['transport'] ?? 'auto'),
        'host' => $smtpHost,
        'port' => $smtpPort,
        'username' => $smtpUsername,
        'password' => (string) ($submittedSmtp['password'] ?? '') !== '' ? (string) $submittedSmtp['password'] : (string) ($currentSmtp['password'] ?? ''),
        'security' => $smtpSecurity,
        'from' => sanitize_text($submittedSmtp['from'] ?? ($currentSmtp['from'] ?? $appSettings['contact_email'])),
        'sender' => sanitize_text($submittedSmtp['sender'] ?? ($currentSmtp['sender'] ?? 'Criteval Pro')),
        'timeout' => max(5, min(60, (int) ($submittedSmtp['timeout'] ?? ($currentSmtp['timeout'] ?? 15)))),
    ];

    $appSettings['security'] = [
        'session_timeout' => max(1, (int) ($_POST['security']['session_timeout'] ?? $appSettings['security']['session_timeout'])),
        'otp_attempts' => max(1, (int) ($_POST['security']['otp_attempts'] ?? $appSettings['security']['otp_attempts'])),
        'logs_enabled' => isset($_POST['security']['logs_enabled']) && $_POST['security']['logs_enabled'] === '1',
        'two_factor_enabled' => isset($_POST['security']['two_factor_enabled']) && $_POST['security']['two_factor_enabled'] === '1',
        'csrf_protection' => isset($_POST['security']['csrf_protection']) && $_POST['security']['csrf_protection'] === '1',
    ];
    $submittedNotifications = is_array($_POST['notifications'] ?? null) ? $_POST['notifications'] : [];
    $appSettings['notifications'] = [
        'enabled' => isset($submittedNotifications['enabled']) && $submittedNotifications['enabled'] === '1',
        'schedule_alerts' => isset($submittedNotifications['schedule_alerts']) && $submittedNotifications['schedule_alerts'] === '1',
        'browser_alerts' => isset($submittedNotifications['browser_alerts']) && $submittedNotifications['browser_alerts'] === '1',
        'sound_alerts' => isset($submittedNotifications['sound_alerts']) && $submittedNotifications['sound_alerts'] === '1',
        'lead_minutes' => max(5, min(10080, (int) ($submittedNotifications['lead_minutes'] ?? 1440))),
        'training_lead_days' => max(1, min(30, (int) ($submittedNotifications['training_lead_days'] ?? 7))),
    ];

    if (!save_app_settings($appSettings)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder les paramètres.']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($route === 'admin/notifications' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_permission('parametres', 'view');
    header('Content-Type: application/json; charset=utf-8');
    $settings = app_settings();
    $enabled = !empty($settings['notifications']['enabled']);
    $items = $enabled ? Notification::current($settings) : [];
    echo json_encode([
        'success' => true,
        'enabled' => $enabled,
        'items' => $items,
        'available_count' => count($items),
        'pending_count' => count(array_filter($items, static fn (array $item): bool => ($item['state'] ?? 'pending') === 'pending')),
        'browser_alerts' => !empty($settings['notifications']['browser_alerts']),
        'sound_alerts' => !empty($settings['notifications']['sound_alerts']),
        'server_time' => date(DateTimeInterface::ATOM),
    ]);
    exit;
}

if ($route === 'admin/notifications/create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('parametres', 'create');
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }
    $title = sanitize_text($_POST['title'] ?? '');
    $message = sanitize_text($_POST['message'] ?? '');
    $dueAt = sanitize_text($_POST['due_at'] ?? '');
    $severity = in_array($_POST['severity'] ?? '', ['info', 'warning', 'urgent', 'danger'], true) ? $_POST['severity'] : 'info';
    $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $dueAt);
    if ($title === '' || $message === '' || !$date) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Titre, message et date d’échéance requis.']);
        exit;
    }
    $settings = app_settings();
    $settings['notifications']['custom'][] = [
        'id' => bin2hex(random_bytes(8)),
        'title' => $title,
        'message' => $message,
        'due_at' => $date->format('Y-m-d H:i:s'),
        'severity' => $severity,
        'created_at' => date('Y-m-d H:i:s'),
    ];
    if (!save_app_settings($settings)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible de créer la notification.']);
        exit;
    }
    echo json_encode(['success' => true, 'message' => 'Notification créée.']);
    exit;
}

if ($route === 'otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null) || !security_rate_limit('otp_request', 5, 600)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Trop de demandes. Réessayez plus tard.']);
        exit;
    }
    $email = sanitize_text($_POST['email'] ?? '');
    $formId = max(0, (int) ($_POST['form_id'] ?? 0));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }

    $form = $formId > 0 ? Form::find($formId) : null;
    $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));
    $domainStatement = db()->query('SELECT id, organization, title, domains, contact_email FROM projects WHERE status <> "archived"');
    $organization = null;
    foreach ($domainStatement->fetchAll() as $project) {
        $configuredDomains = preg_split('/[,;\s]+/', strtolower((string) ($project['domains'] ?? ''))) ?: [];
        if (filter_var((string) ($project['contact_email'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            $configuredDomains[] = strtolower((string) substr(strrchr((string) $project['contact_email'], '@') ?: '', 1));
        }
        foreach ($configuredDomains as $configuredDomain) {
            $configuredDomain = ltrim(trim($configuredDomain), '@');
            if ($configuredDomain !== '' && $configuredDomain === $domain) {
                $organization = $project;
                break 2;
            }
        }
    }
    if (!$organization) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Veuillez saisir une adresse email professionnelle valide appartenant à une organisation enregistrée.']);
        exit;
    }
    $availability = $formId > 0 ? Form::availability($formId, null, $email) : ['available' => true, 'message' => null];
    if (!$availability['available']) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => $availability['message']]);
        exit;
    }
    $otp = generate_otp();
    // Store expiry in SQLite UTC so CLI, Apache and PHP-FPM timezone settings cannot disagree.
    $stmt = db()->prepare('INSERT INTO otp_tokens (email, token, form_id, expires_at, is_used) VALUES (:email, :token, :form_id, datetime("now", "+10 minutes"), 0)');
    $stmt->execute([
        'email' => strtolower($email),
        'token' => password_hash($otp, PASSWORD_DEFAULT),
        'form_id' => $formId > 0 ? $formId : null,
    ]);

    $result = send_otp_email($email, $otp, $form);
    if (!$result['success']) {
        http_response_code(422);
    }
    $_SESSION['candidate_country'] = strtoupper(sanitize_text($_POST['country_code'] ?? ''));
    $_SESSION['candidate_organization_id'] = (int) $organization['id'];
    echo json_encode($result + ['message' => $result['success'] ? 'Code envoyé par email.' : $result['message']]);
    exit;
}

if ($route === 'otp/verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null) || !security_rate_limit('otp_verify', 10, 600)) {
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Trop de tentatives. Réessayez plus tard.']);
        exit;
    }
    $email = strtolower(sanitize_text($_POST['email'] ?? ''));
    $otp = trim((string) ($_POST['otp'] ?? ''));
    $formId = max(0, (int) ($_POST['form_id'] ?? 0));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $otp)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Code ou email invalide.']);
        exit;
    }

    $stmt = db()->prepare('SELECT * FROM otp_tokens WHERE email = :email AND is_used = 0 AND expires_at >= datetime("now") ORDER BY created_at DESC, id DESC LIMIT 5');
    $stmt->execute(['email' => $email]);
    $matched = null;
    foreach ($stmt->fetchAll() as $row) {
        if (($formId === 0 || (int) ($row['form_id'] ?? 0) === $formId) && password_verify($otp, (string) $row['token'])) {
            $matched = $row;
            break;
        }
    }
    if (!$matched) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Code invalide ou expire.']);
        exit;
    }

    db()->prepare('UPDATE otp_tokens SET is_used = 1 WHERE id = :id')->execute(['id' => (int) $matched['id']]);
    $_SESSION['candidate_email'] = $email;
    $_SESSION['candidate_form_id'] = $formId;
    echo json_encode(['success' => true, 'redirect' => BASE_URL . '/candidate']);
    exit;
}

if ($route === 'candidate/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null) || !security_rate_limit('candidate_submit', 5, 3600)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Requête invalide ou trop fréquente.']);
        exit;
    }
    $email = strtolower(sanitize_text($_POST['email'] ?? ($_SESSION['candidate_email'] ?? '')));
    $name = sanitize_text_max($_POST['candidate_name'] ?? '', 150);
    $formId = max(1, (int) ($_POST['form_id'] ?? ($_SESSION['candidate_form_id'] ?? 1)));
    $sessionId = max(0, (int) ($_POST['training_session_id'] ?? 0));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }
    if (empty($_SESSION['candidate_email']) || strtolower((string) $_SESSION['candidate_email']) !== $email) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Verification email requise avant soumission.']);
        exit;
    }

    $form = Form::find($formId);
    if (!$form) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Formulaire introuvable.']);
        exit;
    }
    $session = null;
    if ($sessionId > 0) {
        $sessionStatement = db()->prepare('SELECT * FROM training_sessions WHERE id = :id AND is_active = 1 LIMIT 1');
        $sessionStatement->execute(['id' => $sessionId]);
        $session = $sessionStatement->fetch();
        if (!is_array($session)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'La session sélectionnée est introuvable.']);
            exit;
        }
    }
    $formCriteria = !empty($form['project_id']) ? Criteria::byProject((int) $form['project_id']) : [];
    $allowedCriteria = [];
    $requiredCriteria = [];
    foreach ($formCriteria as $criterion) {
        $criterionId = (int) ($criterion['id'] ?? 0);
        if ($criterionId <= 0) continue;
        $allowedCriteria[$criterionId] = true;
        if (!empty($criterion['is_required'])) $requiredCriteria[$criterionId] = true;
    }
    $checkedCriteria = array_values(array_unique(array_map('intval', (array) ($_POST['criteria_checked'] ?? []))));
    $evidenceNames = is_array($_FILES['evidence']['name'] ?? null) ? $_FILES['evidence']['name'] : [];
    foreach ($requiredCriteria as $criterionId => $_required) {
        if (!in_array((int) $criterionId, $checkedCriteria, true)) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Veuillez confirmer chaque critère obligatoire avant de continuer.']);
            exit;
        }
    }
    foreach ($checkedCriteria as $criterionId) {
        if (!isset($allowedCriteria[$criterionId])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Un critère sélectionné est invalide.']);
            exit;
        }
        $details = trim((string) ($_POST['criteria'][$criterionId]['details'] ?? ''));
        $names = $evidenceNames[$criterionId] ?? [];
        if (is_string($names)) $names = [$names];
        if ($details === '' || empty(array_filter((array) $names))) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Chaque critère coché doit comporter des détails et au moins un justificatif.']);
            exit;
        }
    }
    foreach ($requiredCriteria as $criterionId => $_required) {
        $names = $evidenceNames[$criterionId] ?? [];
        if (is_string($names)) $names = [$names];
        if (empty(array_filter((array) $names))) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Chaque critère obligatoire doit avoir une preuve.']);
            exit;
        }
    }
    $rawCriteria = is_array($_POST['criteria'] ?? null) ? $_POST['criteria'] : [];
    $safeCriteria = [];
    foreach ($checkedCriteria as $criterionId) {
        $details = $rawCriteria[$criterionId]['details'] ?? '';
        $safeCriteria[(string) $criterionId] = ['details' => sanitize_text_max($details, 10000)];
    }
    $safeCustomFields = sanitize_nested_scalars($_POST['custom_fields'] ?? [], 2, 5000);
    if (!is_array($safeCustomFields)) $safeCustomFields = [];
    $submissionPayload = [
        'representator_name' => $name,
        'organisation_id' => (int) ($_SESSION['candidate_organization_id'] ?? 0),
        'form_id' => $formId,
        'training_session_id' => $sessionId ?: null,
        'country_code' => sanitize_text($_POST['country_code'] ?? ($_SESSION['candidate_country'] ?? '')),
        'organisation' => sanitize_text($_POST['organisation'] ?? ''),
        'criteria' => $safeCriteria,
        'criteria_checked' => $checkedCriteria,
        'custom_fields' => $safeCustomFields,
    ];
    $submissionId = Submission::create([
        'form_id' => $formId,
        'candidate_email' => $email,
        'candidate_name' => $name,
        'country_code' => sanitize_text($_POST['country_code'] ?? ''),
        'data_json' => json_encode($submissionPayload, JSON_UNESCAPED_UNICODE),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
    $evidenceFiles = $_FILES['evidence'] ?? [];
    if (is_array($evidenceFiles['name'] ?? null)) {
        $evidenceDirectory = APP_ROOT . '/storage/evidence';
        if (!is_dir($evidenceDirectory)) {
            mkdir($evidenceDirectory, 0775, true);
        }
        foreach ($evidenceFiles['name'] as $criteriaId => $originalNames) {
            if (!isset($allowedCriteria[(int) $criteriaId])) continue;
            $originalNames = is_array($originalNames) ? $originalNames : [$originalNames];
            foreach ($originalNames as $fileIndex => $originalName) {
                $error = (int) ($evidenceFiles['error'][$criteriaId][$fileIndex] ?? UPLOAD_ERR_NO_FILE);
                $temporaryPath = (string) ($evidenceFiles['tmp_name'][$criteriaId][$fileIndex] ?? '');
                $size = (int) ($evidenceFiles['size'][$criteriaId][$fileIndex] ?? 0);
                if ($error === UPLOAD_ERR_NO_FILE) continue;
                if ($error !== UPLOAD_ERR_OK || $size <= 0 || $size > 10 * 1024 * 1024 || !is_uploaded_file($temporaryPath)) continue;
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
                if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true)) continue;
                $safeName = bin2hex(random_bytes(16)) . '.' . ($mime === 'application/pdf' ? 'pdf' : ($mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg')));
                if (move_uploaded_file($temporaryPath, $evidenceDirectory . '/' . $safeName)) {
                $evidenceStatement = db()->prepare(
                    'INSERT INTO organization_evidence (submission_id, criteria_id, file_path, original_name, mime_type, file_size)
                     VALUES (:submission_id, :criteria_id, :file_path, :original_name, :mime_type, :file_size)'
                );
                $evidenceStatement->execute([
                    'submission_id' => $submissionId,
                    'criteria_id' => (int) $criteriaId,
                    'file_path' => 'storage/evidence/' . $safeName,
                    'original_name' => sanitize_text((string) $originalName),
                    'mime_type' => $mime,
                    'file_size' => $size,
                ]);
                }
            }
        }
    }
    $mail = send_submission_confirmation_email($email, $name, $form, $submissionId, $submissionPayload);
    echo json_encode([
        'success' => true,
        'id' => $submissionId,
        'mail' => $mail,
        'message' => !empty($mail['queued'])
            ? 'Candidature soumise. Confirmation email mise en file.'
            : ($mail['success'] ? 'Candidature soumise et confirmation envoyee.' : 'Candidature soumise. Email non envoye : ' . $mail['message']),
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $route === 'admin/sessions/data') {
    require_permission('formation', 'view');
    header('Content-Type: application/json; charset=utf-8');
    $sessionId = max(0, (int) ($_GET['training_id'] ?? 0));
    if ($sessionId <= 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Session invalide.']);
        exit;
    }
    $dashboard = TrainingSession::dashboardData($sessionId);
    if (empty($dashboard['session'])) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Session introuvable.']);
        exit;
    }
    echo json_encode([
        'success' => true,
        'session' => $dashboard['session'],
        'participants' => $dashboard['participants'],
        'stats' => $dashboard['stats'],
        'criteria' => TrainingSession::criteria($sessionId),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Evidence is deliberately served through an authorization-checked endpoint;
// the storage directory is not a public download area.
if (preg_match('#^admin/evidence/(\d+)$#', $route, $matches) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_permission('evaluations', 'view');
    $statement = db()->prepare(
        'SELECT oe.file_path, oe.original_name, oe.mime_type
         FROM organization_evidence oe
         JOIN submissions s ON s.id = oe.submission_id
         JOIN forms f ON f.id = s.form_id
         JOIN criteria c ON c.id = oe.criteria_id AND c.project_id = f.project_id
         WHERE oe.id = :id LIMIT 1'
    );
    $statement->execute(['id' => (int) $matches[1]]);
    $evidence = $statement->fetch();
    $relativePath = is_array($evidence) ? (string) ($evidence['file_path'] ?? '') : '';
    $fullPath = $relativePath !== '' ? APP_ROOT . '/' . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR) : '';
    if (!is_array($evidence) || !is_file($fullPath) || !str_starts_with(realpath($fullPath) ?: '', realpath(APP_ROOT . '/storage/evidence') ?: '')) {
        http_response_code(404);
        exit('Preuve introuvable.');
    }
    header('Content-Type: ' . ((string) ($evidence['mime_type'] ?? 'application/octet-stream')));
    $downloadName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename((string) ($evidence['original_name'] ?? 'preuve'))) ?: 'preuve';
    header('Content-Disposition: inline; filename="' . $downloadName . '"');
    header('Content-Length: ' . (string) filesize($fullPath));
    readfile($fullPath);
    exit;
}

if ($route === 'admin/email/test' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('parametres', 'update');
    header('Content-Type: application/json; charset=utf-8');
    if (!is_superadmin() || !verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé ou jeton CSRF invalide.']);
        exit;
    }
    $result = test_app_email((string) ($_POST['recipient'] ?? ''));
    if (!$result['success']) http_response_code(422);
    echo json_encode($result);
    exit;
}

if ($route === 'admin/email/status' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_permission('parametres', 'view');
    header('Content-Type: application/json; charset=utf-8');
    $flush = mailer_flush_queue(10);
    $status = mailer_connectivity(null, true);
    echo json_encode([
        'success' => true,
        'online' => $status['online'],
        'smtp_reachable' => $status['smtp_reachable'],
        'php_mail_available' => $status['php_mail_available'],
        'host' => $status['host'],
        'port' => $status['port'],
        'error' => $status['error'],
        'queue_count' => mailer_queue_count(),
        'flushed' => $flush,
    ]);
    exit;
}

if ($route === 'admin/email/send' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('parametres', 'create');
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    $subject = trim((string) ($_POST['subject'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));
    $to = $_POST['to'] ?? '';
    if ($subject === '' || !is_valid_mail_header($subject)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Sujet email invalide.']);
        exit;
    }

    $result = send_admin_custom_email($to, $subject, $message, isset($_POST['html']) && $_POST['html'] === '1');
    if (!$result['success']) {
        http_response_code(422);
    }
    echo json_encode($result);
    exit;
}

if ($route === 'admin/database/export' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_permission('parametres', 'view');
    if (!is_superadmin() || !is_file(DATABASE_PATH)) {
        http_response_code(403);
        exit('Accès refusé.');
    }
    header('Content-Type: application/vnd.sqlite3');
    header('Content-Disposition: attachment; filename="criteval_pro-' . date('Y-m-d-His') . '.sqlite"');
    header('Content-Length: ' . (string) filesize(DATABASE_PATH));
    readfile(DATABASE_PATH);
    exit;
}

if ($route === 'admin/database/import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_permission('parametres', 'update');
    header('Content-Type: application/json; charset=utf-8');
    if (!is_superadmin() || !verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé ou jeton CSRF invalide.']);
        exit;
    }
    $upload = $_FILES['database_file'] ?? null;
    if (!is_array($upload) || (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Sélectionnez un fichier SQLite valide.']);
        exit;
    }
    if ((int) ($upload['size'] ?? 0) < 100 || (int) ($upload['size'] ?? 0) > 100 * 1024 * 1024 || !is_uploaded_file((string) $upload['tmp_name'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Le fichier doit peser entre 100 octets et 100 Mo.']);
        exit;
    }
    $temporary = (string) $upload['tmp_name'];
    try {
        $candidate = new PDO('sqlite:' . $temporary, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $candidate->exec('PRAGMA query_only = ON');
        $hasUsers = $candidate->query("SELECT 1 FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn();
        if ($hasUsers === false) {
            throw new RuntimeException('Le fichier ne contient pas la table users requise.');
        }
        $backup = DATABASE_PATH . '.backup-' . date('Ymd-His') . '.sqlite';
        if (is_file(DATABASE_PATH) && !copy(DATABASE_PATH, $backup)) {
            throw new RuntimeException('Impossible de créer la sauvegarde de sécurité.');
        }
        if (!copy($temporary, DATABASE_PATH)) {
            throw new RuntimeException('Impossible de remplacer la base SQLite.');
        }
        echo json_encode(['success' => true, 'message' => 'Base importée. Sauvegarde créée : ' . basename($backup)]);
    } catch (Throwable $exception) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
    }
    exit;
}

// ══════════════════════════════════════
// ADMIN: Users management (list, create/update, delete)
// ══════════════════════════════════════
if ($route === 'admin/users' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_permission('utilisateurs', 'view');
    $projects = Project::dashboardProjects();
    $users = User::all();
    render_view('modules/Admin/views/dashboard.php', [
        'projects' => $projects,
        'users' => $users,
        'startModule' => 'utilisateurs',
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/users') {
    require_permission('utilisateurs', (int) ($_POST['id'] ?? 0) > 0 ? 'update' : 'create');
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Jeton CSRF invalide.']);
        exit;
    }

    if (!is_superadmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Seul un superadministrateur peut gérer les utilisateurs.']);
        exit;
    }

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $data = [];
    $data['username'] = sanitize_text($_POST['username'] ?? '');
    $data['name'] = sanitize_text($_POST['name'] ?? '');
    $data['email'] = sanitize_text($_POST['email'] ?? '');
    $data['role'] = in_array(($_POST['role'] ?? ''), available_roles(), true) ? $_POST['role'] : 'user';
    $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $data['permissions'] = normalize_permissions_structure($_POST['permissions'] ?? default_permissions_for_role($data['role']));

    if ($id <= 0) {
        $data['password'] = (string) ($_POST['password'] ?? '');
    } elseif (!empty($_POST['password'])) {
        $data['password'] = (string) $_POST['password'];
    }

    if ($data['name'] === '' || $data['username'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Nom, nom utilisateur et adresse email valides sont requis.']);
        exit;
    }

    if ($id <= 0 && (!isset($data['password']) || strlen($data['password']) < 8)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caract�res.']);
        exit;
    }

    if ($id > 0 && isset($data['password']) && strlen($data['password']) > 0 && strlen($data['password']) < 8) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caract�res.']);
        exit;
    }

    $existingByCredential = User::findByCredential($data['username']);
    if ($existingByCredential && (int) $existingByCredential['id'] !== $id) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Ce nom utilisateur est d�j� utilis�.']);
        exit;
    }
    $existingByEmail = User::findByCredential($data['email']);
    if ($existingByEmail && (int) $existingByEmail['id'] !== $id) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Cette adresse email est d�j� utilis�e.']);
        exit;
    }

    try {
        if ($id > 0) {
            $ok = User::update($id, $data);
            echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Utilisateur mis à jour.' : 'Aucune modification effectuée.']);
        } else {
            $newId = User::create($data);
            echo json_encode(['success' => $newId > 0, 'id' => $newId, 'message' => 'Utilisateur créé avec succès.']);
        }
    } catch (Throwable $exception) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible d’enregistrer cet utilisateur.']);
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/users/delete') {
    require_permission('utilisateurs', 'delete');
    header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission refusée.']);
        exit;
    }

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID invalide.']);
        exit;
    }

    if (!is_superadmin()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission refusée.']);
        exit;
    }

    $ok = User::delete($id);
    echo json_encode(['success' => (bool) $ok]);
    exit;
}
if ($route === 'logout') {
    logout_user();
    header('Location: ' . BASE_URL . '/login');
    exit;
}

$legacyRoutes = [
    'index.html' => 'home',
    'login.html' => 'login',
    'dashboard.html' => 'dashboard',
    'candidate.html' => 'candidate',
];

if (isset($legacyRoutes[$route])) {
    $route = $legacyRoutes[$route];
}

$moduleRouteAliases = [
    'dashboard' => 'apercu',
    'organisations' => 'projets',
    'criteres' => 'criteres',
    'formulaires' => 'formulaires',
    'formation' => 'formation',
    'evaluations' => 'evaluations',
    'classements' => 'classements',
    'planning' => 'calendrier',
    'parametres' => 'parametres',
];
$routeAlias = strtolower(rawurldecode($route));
$routeAlias = strtr($routeAlias, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ù' => 'u']);
$startModule = $moduleRouteAliases[$routeAlias] ?? null;
if ($startModule !== null) {
    $route = 'dashboard';
}

$routes = [
    'home' => 'modules/Public/views/home.php',
    'index' => 'modules/Public/views/home.php',
    'about' => 'modules/Public/views/about.php',
    'contact' => 'modules/Public/views/contact.php',
    'login' => 'modules/Auth/views/login.php',
    'otp' => 'modules/Auth/views/otp_verify.php',
    'forgot-password' => 'modules/Auth/views/forgot_password.php',
    'admin' => 'modules/Admin/views/dashboard.php',
    'dashboard' => 'modules/Admin/views/dashboard.php',
    'admin/projects' => 'modules/Admin/views/projects/index.php',
    'admin/criteria' => 'modules/Admin/views/criteria/index.php',
    'admin/sessions' => 'modules/Admin/views/training/index.php',
    'admin/forms' => 'modules/Admin/views/forms/gallery.php',
    'admin/forms/builder' => 'modules/Admin/views/forms/builder.php',
    'admin/forms/planning' => 'modules/Admin/views/forms/planning.php',
    'admin/evaluations/levels' => 'modules/Admin/views/evaluations/levels.php',
    'admin/evaluations/score' => 'modules/Admin/views/evaluations/score.php',
    'admin/evaluations/ranking' => 'modules/Admin/views/evaluations/ranking.php',
    'candidate' => 'modules/Candidate/views/form_fill.php',
    'formulaire' => 'modules/Candidate/views/form_access.php',
    'formulaire/remplir' => 'modules/Candidate/views/form_fill.php',
];

if (preg_match('#^session/([a-z0-9-]+)$#i', $route, $matches)) {
    render_view('modules/Public/views/session.php', ['session' => TrainingSession::findBySlug($matches[1])]);
    exit;
}

if (!isset($routes[$route])) {
    http_response_code(404);
    render_view('modules/Public/views/home.php');
    exit;
}

if ($route === 'dashboard' || str_starts_with($route, 'admin')) {
    $pagePermissions = [
        'dashboard' => ['apercu', 'view'],
        'admin' => ['apercu', 'view'],
        'admin/projects' => ['projets', 'view'],
        'admin/criteria' => ['criteres', 'view'],
        'admin/sessions' => ['formation', 'view'],
        'admin/forms' => ['formulaires', 'view'],
        'admin/forms/builder' => ['formulaires', 'view'],
        'admin/forms/planning' => ['calendrier', 'view'],
        'admin/evaluations/levels' => ['evaluations', 'view'],
        'admin/evaluations/score' => ['evaluations', 'view'],
        'admin/evaluations/ranking' => ['classements', 'view'],
    ];
    if (isset($pagePermissions[$route])) {
        require_permission($pagePermissions[$route][0], $pagePermissions[$route][1]);
    } else {
        require_admin();
    }
}

$projects = [];
$users = [];
$criteria = [];
$moduleCounts = [];
$overview = [];
if (in_array($route, ['admin', 'dashboard'], true)) {
    $projects = Project::manageAll();
    $forms = Form::all();
    $users = User::all();
    $criteria = Criteria::all();
    $overview = Dashboard::overview();
    $criteriaTemplates = Criteria::templates();
    foreach ($projects as &$project) {
        $project['criteria_configuration'] = Criteria::projectConfiguration((int) $project['id']);
    }
    unset($project);
    $trainingSessions = TrainingSession::all();
    $selectedTrainingId = (int) ($_GET['training_id'] ?? ($trainingSessions[0]['id'] ?? 0));
    $trainingDashboard = $selectedTrainingId > 0 ? TrainingSession::dashboardData($selectedTrainingId) : ['session' => null, 'participants' => [], 'stats' => []];
    $trainingCriteria = $selectedTrainingId > 0
        ? TrainingSession::criteria($selectedTrainingId)
        : [];
    $countNewRecords = static function (string $table, ?string $dateColumn): int {
        if ($dateColumn === null) {
            return 0;
        }

        try {
            $stmt = db()->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $dateColumn . ' >= :since');
            $stmt->execute(['since' => date('Y-m-d H:i:s', time() - 86400)]);
            return (int) $stmt->fetchColumn();
        } catch (Throwable $exception) {
            return 0;
        }
    };
    $moduleCounts = [
        'apercu' => $countNewRecords('submissions', 'submitted_at'),
        'projets' => $countNewRecords('projects', 'created_at'),
        'criteres' => $countNewRecords('criteria', null),
        'formulaires' => $countNewRecords('forms', 'created_at'),
        'formation' => 0,
        'evaluations' => $countNewRecords('evaluations', 'evaluated_at'),
        'classements' => $countNewRecords('results', 'published_at'),
        'calendrier' => $countNewRecords('form_schedules', null),
        'parametres' => $countNewRecords('module_settings', null),
        'utilisateurs' => $countNewRecords('users', 'created_at'),
    ];
}

if ($route === 'admin/projects') {
    $projects = Project::manageAll();
    $criteriaTemplates = Criteria::templates();
    foreach ($projects as &$project) {
        $project['criteria_configuration'] = Criteria::projectConfiguration((int) $project['id']);
    }
    unset($project);
    $editProject = isset($_GET['edit']) ? Project::find((int) $_GET['edit']) : null;
}
if ($route === 'admin/criteria') { $projects = Project::dashboardProjects(); $criteria = Criteria::all(); }
if ($route === 'admin/sessions') { $sessions = TrainingSession::all(); $projects = Project::all(); }
if ($route === 'admin/evaluations/ranking') { $rankings = Ranking::all(); }
if ($route === 'admin/evaluations/score') {
    $submissions = Submission::forEvaluation();
    foreach ($submissions as &$submission) {
        $submission['evidence'] = Submission::evidenceForSubmission((int) $submission['id']);
    }
    unset($submission);
    $criteria = Criteria::all();
}
if ($route === 'admin/forms') { $forms = Form::all(); }
if ($route === 'admin/forms/builder') { $form = Form::find((int) ($_GET['id'] ?? 0)); $projects = Project::all(); }
if ($route === 'admin/forms/planning') { $forms = Form::all(); $projects = Project::all(); }
if ($route === 'formulaire/remplir' || $route === 'candidate') {
    $candidateForm = Form::find((int) ($_SESSION['candidate_form_id'] ?? 1));
    $candidateCriteria = $candidateForm && !empty($candidateForm['project_id'])
        ? Criteria::byProject((int) $candidateForm['project_id'])
        : Criteria::all();
    $candidateForms = Form::all();
    $candidateSessions = array_map(static function (array $session): array {
        $session['criteria'] = TrainingSession::criteria((int) ($session['id'] ?? 0));
        return $session;
    }, TrainingSession::all());
    $candidateFormCatalog = [];
    foreach ($candidateForms as $formItem) {
        $formProjectId = (int) ($formItem['project_id'] ?? 0);
        $candidateFormCatalog[(int) $formItem['id']] = [
            'form' => $formItem,
            'criteria' => $formProjectId > 0 ? Criteria::byProject($formProjectId) : [],
            'sessions' => $candidateSessions,
        ];
    }
    $candidateOrganization = null;
    if (!empty($_SESSION['candidate_organization_id'])) {
        $organizationStatement = db()->prepare('SELECT * FROM projects WHERE id = :id LIMIT 1');
        $organizationStatement->execute(['id' => (int) $_SESSION['candidate_organization_id']]);
        $candidateOrganization = $organizationStatement->fetch() ?: null;
    }
}

render_view($routes[$route], [
    'projects' => $projects,
    'users' => $users,
    'criteria' => $criteria,
    'criteriaTemplates' => $criteriaTemplates ?? Criteria::templates(),
    'trainingSessions' => $trainingSessions ?? TrainingSession::all(),
    'trainingDashboard' => $trainingDashboard ?? ['session' => null, 'participants' => [], 'stats' => []],
    'trainingCriteria' => $trainingCriteria ?? [],
    'moduleCounts' => $moduleCounts,
    'startModule' => $startModule,
    'overview' => $overview,
    'forms' => $forms ?? [],
    'form' => $form ?? null,
    'editProject' => $editProject ?? null,
    'sessions' => $sessions ?? [],
    'candidateCriteria' => $candidateCriteria ?? [],
    'candidateForms' => $candidateForms ?? [],
    'candidateSessions' => $candidateSessions ?? [],
    'candidateFormCatalog' => $candidateFormCatalog ?? [],
    'candidateOrganization' => $candidateOrganization ?? null,
    'rankings' => $rankings ?? [],
    'submissions' => $submissions ?? [],
    'databaseInfo' => is_superadmin() ? database_status_info() : null,
]);

