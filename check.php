<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
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

$pdo = db();
echo "DB path: " . (defined('DATABASE_PATH') ? constant('DATABASE_PATH') : 'n/a') . "\n";

echo "\n=== training_sessions ===\n";
$rows = $pdo->query("SELECT id, name, session_date, end_date, project_id, is_active, public_slug, capacity, format, evaluation_weight FROM training_sessions ORDER BY id ASC")->fetchAll();
foreach ($rows as $r) {
  echo json_encode(array_map(function($v){return $v === null ? null : (string)$v;}, $r), JSON_UNESCAPED_UNICODE) . "\n";
}
echo "count: " . count($rows) . "\n";

echo "\n=== forms ===\n";
$forms = $pdo->query("SELECT id, title, project_id, is_active, is_published, status, schedule, access_type FROM forms ORDER BY id ASC")->fetchAll();
foreach ($forms as $f) {
  echo json_encode(array_map(function($v){return $v === null ? null : (string)$v;}, $f), JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== form_schedules ===\n";
$fs = $pdo->query("SELECT * FROM form_schedules ORDER BY id ASC")->fetchAll();
foreach ($fs as $f) {
  echo json_encode(array_map(function($v){return $v === null ? null : (string)$v;}, $f), JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== projects ===\n";
$pr = $pdo->query("SELECT id, organization, title, country_code, intervention_zone FROM projects ORDER BY id ASC")->fetchAll();
foreach ($pr as $p) {
  echo json_encode(array_map(function($v){return $v === null ? null : (string)$v;}, $p), JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== criteria ===\n";
$cr = $pdo->query("SELECT id, project_id, label, weight, max_score, is_required, order_index FROM criteria ORDER BY id ASC")->fetchAll();
foreach ($cr as $c) {
  echo json_encode(array_map(function($v){return $v === null ? null : (string)$v;}, $c), JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== TrainingSession::all(true) ===\n";
$all = TrainingSession::all(true);
foreach ($all as $a) {
  echo json_encode(array_map(function($v){return $v === null ? null : (string)$v;}, $a), JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\n=== calendarEvents ===\n";
$ev = TrainingSession::calendarEvents();
foreach ($ev as $e) {
  echo json_encode($e, JSON_UNESCAPED_UNICODE) . "\n";
}
