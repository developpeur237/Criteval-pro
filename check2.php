<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
$pdo = db();
echo "form_schedules count: " . $pdo->query('SELECT COUNT(*) FROM form_schedules')->fetchColumn() . "\n";
echo "training_sessions count: " . $pdo->query('SELECT COUNT(*) FROM training_sessions')->fetchColumn() . "\n";
$rows = $pdo->query('SELECT * FROM form_schedules')->fetchAll();
foreach ($rows as $r) {
  echo json_encode(array_map(function($v){return $v === null ? null : (string)$v;}, $r), JSON_UNESCAPED_UNICODE) . "\n";
}
