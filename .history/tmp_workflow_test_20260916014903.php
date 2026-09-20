<?php
require __DIR__ . '/config/config.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/models/Criteria.php';
require __DIR__ . '/models/Evaluation.php';
require __DIR__ . '/models/Ranking.php';
initialize_database();
$pdo = db();
$old = $pdo->query("SELECT id FROM submissions WHERE candidate_email = 'workflow@example.com'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($old as $oldId) {
    $pdo->prepare('DELETE FROM results WHERE submission_id = :id')->execute(['id' => (int) $oldId]);
    $pdo->prepare('DELETE FROM submissions WHERE id = :id')->execute(['id' => (int) $oldId]);
}
$pdo->exec("INSERT INTO submissions (form_id, candidate_email, candidate_name, country_code, data_json, status) VALUES (1, 'workflow@example.com', 'Workflow Test', 'CM', '{}', 'pending')");
$id = (int) $pdo->lastInsertId();
$scores = [];
foreach (Criteria::all() as $criterion) {
    $scores[(int) $criterion['id']] = 10;
}
Evaluation::saveScores($id, $scores, 1);
$result = $pdo->prepare('SELECT weighted_score FROM results WHERE submission_id = :id');
$result->execute(['id' => $id]);
echo 'weighted=' . $result->fetchColumn() . '; ranking=' . count(Ranking::all()) . PHP_EOL;
$pdo->prepare('DELETE FROM results WHERE submission_id = :id')->execute(['id' => $id]);
$pdo->prepare('DELETE FROM submissions WHERE id = :id')->execute(['id' => $id]);
