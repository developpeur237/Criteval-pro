<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Criteria.php';
require_once __DIR__ . '/../models/Evaluation.php';

initialize_database();
$pdo = db();

$submissions = $pdo->query("SELECT s.id, f.project_id FROM submissions s JOIN forms f ON f.id = s.form_id WHERE s.candidate_email LIKE '%@example.test' ORDER BY s.id ASC LIMIT 6")->fetchAll();
foreach ($submissions as $index => $submission) {
    $criteria = Criteria::byProject((int) $submission['project_id']);
    if (!$criteria || (int) $pdo->query('SELECT COUNT(*) FROM evaluations WHERE submission_id = ' . (int) $submission['id'])->fetchColumn() > 0) {
        continue;
    }
    $scores = [15.5, 13.5, 17.0];
    foreach (array_slice($criteria, 0, 3) as $scoreIndex => $criterion) {
        $statement = $pdo->prepare(
            'INSERT INTO evaluations (submission_id, criteria_id, score, comment, evaluated_by)
             VALUES (:submission_id, :criteria_id, :score, :comment, 1)'
        );
        $statement->execute([
            'submission_id' => (int) $submission['id'],
            'criteria_id' => (int) $criterion['id'],
            'score' => $scores[$scoreIndex] ?? 15,
            'comment' => 'Évaluation de démonstration issue du dossier organisation.',
        ]);
    }
    $scoreStatement = $pdo->prepare(
        'SELECT COALESCE(SUM(e.score), 0) AS total_score,
                COALESCE(SUM(e.score * c.weight) / NULLIF(SUM(c.weight), 0), 0) AS weighted_score
         FROM evaluations e JOIN criteria c ON c.id = e.criteria_id WHERE e.submission_id = :submission_id'
    );
    $scoreStatement->execute(['submission_id' => (int) $submission['id']]);
    $score = $scoreStatement->fetch();
    $pdo->prepare("UPDATE submissions SET status = 'evaluated' WHERE id = :id")->execute(['id' => (int) $submission['id']]);
    $pdo->prepare(
        'INSERT INTO results (submission_id, project_id, total_score, weighted_score, is_published)
         VALUES (:submission_id, :project_id, :total_score, :weighted_score, :published)
         ON CONFLICT(submission_id) DO UPDATE SET total_score = excluded.total_score,
         weighted_score = excluded.weighted_score, project_id = excluded.project_id'
    )->execute([
        'submission_id' => (int) $submission['id'],
        'project_id' => (int) $submission['project_id'],
        'total_score' => (float) $score['total_score'],
        'weighted_score' => (float) $score['weighted_score'],
        'published' => $index === 0 ? 1 : 0,
    ]);
}

$forms = $pdo->query('SELECT f.id, f.project_id FROM forms f JOIN projects p ON p.id = f.project_id WHERE f.status = "published" ORDER BY f.id ASC LIMIT 5')->fetchAll();
foreach ($forms as $index => $form) {
    $exists = $pdo->prepare('SELECT COUNT(*) FROM form_schedules WHERE form_id = :form_id');
    $exists->execute(['form_id' => (int) $form['id']]);
    if ((int) $exists->fetchColumn() > 0) continue;
    $start = date('Y-m-d H:i:s', strtotime('+' . ($index + 1) . ' days'));
    $end = date('Y-m-d H:i:s', strtotime('+' . ($index + 10) . ' days'));
    $pdo->prepare(
        'INSERT INTO form_schedules (form_id, project_id, access_type, start_datetime, end_datetime, allowed_countries, is_active)
         VALUES (:form_id, :project_id, "public_link", :start_datetime, :end_datetime, :allowed_countries, 1)'
    )->execute([
        'form_id' => (int) $form['id'],
        'project_id' => (int) $form['project_id'],
        'start_datetime' => $start,
        'end_datetime' => $end,
        'allowed_countries' => 'ML,CM,SN',
    ]);
}

echo 'Overview seed terminé : évaluations, résultats et échéances cohérents ajoutés.' . PHP_EOL;
