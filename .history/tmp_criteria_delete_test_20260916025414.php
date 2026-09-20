<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/models/Criteria.php';
initialize_database();
$pdo = db();
$projectId = (int) $pdo->query('SELECT id FROM projects ORDER BY id LIMIT 1')->fetchColumn();
$id = Criteria::create(['project_id' => $projectId, 'label' => 'Temporary deletion persistence test']);
$deleted = Criteria::delete($id);
initialize_database();
$remaining = (int) $pdo->query('SELECT COUNT(*) FROM criteria WHERE id = ' . $id)->fetchColumn();
echo 'created=' . $id . ';deleted=' . ($deleted ? 'true' : 'false') . ';remaining_after_upgrade=' . $remaining . PHP_EOL;
