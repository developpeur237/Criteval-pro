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
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Project.php';
require_once __DIR__ . '/models/Criteria.php';
require_once __DIR__ . '/models/Form.php';
require_once __DIR__ . '/models/Submission.php';
require_once __DIR__ . '/models/TrainingSession.php';
require_once __DIR__ . '/models/Ranking.php';
require_once __DIR__ . '/models/Evaluation.php';

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
    require_admin();

    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        echo 'Jeton CSRF invalide.';
        exit;
    }

    $title = sanitize_text($_POST['title'] ?? '');
    $organization = sanitize_text($_POST['organization'] ?? '');
    $intervention_zone = sanitize_text($_POST['intervention_zone'] ?? '');
    $description = sanitize_text($_POST['description'] ?? '');
    $domains = sanitize_text($_POST['domains'] ?? '');
    $targetAudiences = sanitize_text($_POST['target_audiences'] ?? '');
    $legalStatus = in_array($_POST['legal_status'] ?? '', ['legal', 'non_legal'], true) ? $_POST['legal_status'] : 'non_legal';
    $countryCode = sanitize_text($_POST['country_code'] ?? '');
    $projectCount = ($_POST['project_count'] ?? '') === '' ? null : max(0, (int) $_POST['project_count']);
    $status = in_array($_POST['status'] ?? '', ['draft', 'active', 'closed', 'archived'], true) ? $_POST['status'] : 'draft';
    $createdBy = current_user()['id'] ?? null;

    if ($title !== '') {
        Project::create([
            'title' => $title,
            'organization' => $organization,
            'domains' => $domains,
            'target_audiences' => $targetAudiences,
            'legal_status' => $legalStatus,
            'country_code' => $countryCode,
            'project_count' => $projectCount,
            'intervention_zone' => $intervention_zone,
            'description' => $description,
            'status' => $status,
            'created_by' => $createdBy,
        ]);
    }

    header('Location: ' . BASE_URL . '/dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/sessions') {
    require_admin();
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Jeton CSRF invalide.');
    }
    $name = sanitize_text($_POST['name'] ?? '');
    $objective = sanitize_text($_POST['objective'] ?? '');
    $date = sanitize_text($_POST['session_date'] ?? '');
    if ($name === '' || $objective === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(422);
        exit('Nom, objectif et date de session requis.');
    }
    $slug = trim(preg_replace('/[^a-z0-9]+/i', '-', strtolower($name)), '-') . '-' . bin2hex(random_bytes(3));
    TrainingSession::create([
        'name' => $name,
        'objective' => $objective,
        'session_date' => $date,
        'public_slug' => $slug,
        'created_by' => current_user()['id'] ?? null,
    ]);
    header('Location: ' . BASE_URL . '/admin/sessions');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/evaluations/score') {
    require_admin();
    if (!verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Jeton CSRF invalide.'); }
    $submissionId = (int) ($_POST['submission_id'] ?? 0);
    $scores = is_array($_POST['scores'] ?? null) ? $_POST['scores'] : [];
    if ($submissionId <= 0 || !$scores) { http_response_code(422); exit('Soumission et notes requises.'); }
    Evaluation::saveScores($submissionId, $scores, (int) (current_user()['id'] ?? 0));
    header('Location: ' . BASE_URL . '/admin/evaluations/score?saved=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/forms') {
    require_admin();
    if (!verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(403); exit('Jeton CSRF invalide.'); }
    $title = sanitize_text($_POST['title'] ?? '');
    $layout = (string) ($_POST['layout_json'] ?? '[]');
    json_decode($layout, true);
    if ($title === '' || json_last_error() !== JSON_ERROR_NONE) { http_response_code(422); exit('Titre ou structure de formulaire invalide.'); }
    $id = Form::save([
        'id' => (int) ($_POST['id'] ?? 0), 'title' => $title,
        'description' => sanitize_text($_POST['description'] ?? ''), 'project_id' => (int) ($_POST['project_id'] ?? 0),
        'layout_json' => $layout, 'status' => in_array($_POST['status'] ?? '', ['draft', 'published', 'archived'], true) ? $_POST['status'] : 'draft',
        'created_by' => current_user()['id'] ?? 0,
    ]);
    header('Location: ' . BASE_URL . '/admin/forms/builder?id=' . $id . '&saved=1'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/forms/schedules') {
    require_admin(); header('Content-Type: application/json; charset=utf-8');
    if (!verify_csrf($_POST['csrf_token'] ?? null)) { http_response_code(403); echo json_encode(['success' => false]); exit; }
    $formId = (int) ($_POST['form_id'] ?? 0);
    if (!$formId || !Form::find($formId)) { http_response_code(422); echo json_encode(['success' => false, 'message' => 'Formulaire invalide.']); exit; }
    Form::saveSchedule(['form_id' => $formId, 'project_id' => (int) ($_POST['project_id'] ?? 0) ?: null,
        'access_type' => in_array($_POST['access_type'] ?? '', ['public_link', 'email_list', 'admin_only'], true) ? $_POST['access_type'] : 'public_link',
        'start_datetime' => (string) ($_POST['start_datetime'] ?? ''), 'end_datetime' => (string) ($_POST['end_datetime'] ?? ''),
        'allowed_emails' => sanitize_text($_POST['allowed_emails'] ?? ''), 'allowed_countries' => sanitize_text($_POST['allowed_countries'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0]);
    echo json_encode(['success' => true]); exit;
}

if ($route === 'admin/forms/schedules' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    require_admin(); header('Content-Type: application/json; charset=utf-8');
    echo json_encode(Form::schedules()); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/criteria') {
    require_admin();
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
        echo json_encode(['success' => false, 'message' => 'Projet et libellé du critère sont requis.']);
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
    require_admin();
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
    require_admin();
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
    require_admin();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'items' => Criteria::all()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $route === 'admin/settings') {
    require_admin();

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
    $appSettings['website'] = sanitize_text($_POST['website'] ?? $appSettings['website']);

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

    if (!save_app_settings($appSettings)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Impossible de sauvegarder les paramètres.']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($route === 'otp' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $email = sanitize_text($_POST['email'] ?? '');
    $formId = max(0, (int) ($_POST['form_id'] ?? 0));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
        exit;
    }

    $form = $formId > 0 ? Form::find($formId) : null;
    $otp = generate_otp();
    $stmt = db()->prepare('INSERT INTO otp_tokens (email, token, form_id, expires_at, is_used) VALUES (:email, :token, :form_id, :expires_at, 0)');
    $stmt->execute([
        'email' => strtolower($email),
        'token' => password_hash($otp, PASSWORD_DEFAULT),
        'form_id' => $formId > 0 ? $formId : null,
        'expires_at' => date('Y-m-d H:i:s', time() + 600),
    ]);

    $result = send_otp_email($email, $otp, $form);
    if (!$result['success']) {
        http_response_code(422);
    }
    echo json_encode($result + ['message' => $result['success'] ? 'Code envoye par email.' : $result['message']]);
    exit;
}

if ($route === 'otp/verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $email = strtolower(sanitize_text($_POST['email'] ?? ''));
    $otp = trim((string) ($_POST['otp'] ?? ''));
    $formId = max(0, (int) ($_POST['form_id'] ?? 0));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^\d{6}$/', $otp)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Code ou email invalide.']);
        exit;
    }

    $stmt = db()->prepare('SELECT * FROM otp_tokens WHERE email = :email AND is_used = 0 AND expires_at >= :now ORDER BY created_at DESC, id DESC LIMIT 5');
    $stmt->execute(['email' => $email, 'now' => date('Y-m-d H:i:s')]);
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
    echo json_encode(['success' => true, 'redirect' => BASE_URL . '/formulaire/remplir']);
    exit;
}

if ($route === 'candidate/submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $email = strtolower(sanitize_text($_POST['email'] ?? ($_SESSION['candidate_email'] ?? '')));
    $name = sanitize_text($_POST['candidate_name'] ?? '');
    $formId = max(1, (int) ($_POST['form_id'] ?? ($_SESSION['candidate_form_id'] ?? 1)));
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
    $submissionId = Submission::create([
        'form_id' => $formId,
        'candidate_email' => $email,
        'candidate_name' => $name,
        'country_code' => sanitize_text($_POST['country_code'] ?? ''),
        'data_json' => json_encode($_POST, JSON_UNESCAPED_UNICODE),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
    $evidenceFiles = $_FILES['evidence'] ?? [];
    if (is_array($evidenceFiles['name'] ?? null)) {
        $evidenceDirectory = APP_ROOT . '/storage/evidence';
        if (!is_dir($evidenceDirectory)) {
            mkdir($evidenceDirectory, 0775, true);
        }
        foreach ($evidenceFiles['name'] as $criteriaId => $originalName) {
            $error = (int) ($evidenceFiles['error'][$criteriaId] ?? UPLOAD_ERR_NO_FILE);
            $temporaryPath = (string) ($evidenceFiles['tmp_name'][$criteriaId] ?? '');
            $size = (int) ($evidenceFiles['size'][$criteriaId] ?? 0);
            if ($error === UPLOAD_ERR_NO_FILE) continue;
            if ($error !== UPLOAD_ERR_OK || $size <= 0 || $size > 10 * 1024 * 1024 || !is_uploaded_file($temporaryPath)) continue;
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
            if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) continue;
            $safeName = bin2hex(random_bytes(16)) . '.' . ($mime === 'application/pdf' ? 'pdf' : ($mime === 'image/png' ? 'png' : 'jpg'));
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
    $mail = send_submission_confirmation_email($email, $name, $form, $submissionId);
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

if ($route === 'admin/email/test' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
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
    require_admin();
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
    require_admin();
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
    require_admin();
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
    require_admin();
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
    require_admin();
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
    require_admin();
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
    require_admin();
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
    require_admin();
}

$projects = [];
$users = [];
$criteria = [];
$moduleCounts = [];
if (in_array($route, ['admin', 'dashboard'], true)) {
    $projects = Project::dashboardProjects();
    $users = User::all();
    $criteria = Criteria::all();
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

if ($route === 'admin/projects') { $projects = Project::dashboardProjects(); }
if ($route === 'admin/criteria') { $projects = Project::dashboardProjects(); $criteria = Criteria::all(); }
if ($route === 'admin/sessions') { $sessions = TrainingSession::all(); }
if ($route === 'admin/evaluations/ranking') { $rankings = Ranking::all(); }
if ($route === 'admin/evaluations/score') { $submissions = Submission::forEvaluation(); $criteria = Criteria::all(); }
if ($route === 'admin/forms') { $forms = Form::all(); }
if ($route === 'admin/forms/builder') { $form = Form::find((int) ($_GET['id'] ?? 0)); $projects = Project::all(); }
if ($route === 'admin/forms/planning') { $forms = Form::all(); $projects = Project::all(); }
if ($route === 'formulaire/remplir' || $route === 'candidate') {
    $candidateForm = Form::find((int) ($_SESSION['candidate_form_id'] ?? 1));
    $candidateCriteria = $candidateForm && !empty($candidateForm['project_id'])
        ? Criteria::byProject((int) $candidateForm['project_id'])
        : Criteria::all();
}

render_view($routes[$route], [
    'projects' => $projects,
    'users' => $users,
    'criteria' => $criteria,
    'moduleCounts' => $moduleCounts,
    'forms' => $forms ?? [],
    'form' => $form ?? null,
    'sessions' => $sessions ?? [],
    'candidateCriteria' => $candidateCriteria ?? [],
    'rankings' => $rankings ?? [],
    'submissions' => $submissions ?? [],
    'databaseInfo' => is_superadmin() ? database_status_info() : null,
]);

