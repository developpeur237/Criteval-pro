<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

initialize_database();
$pdo = db();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
mt_srand(20260916);

$forms = $pdo->query('SELECT id, project_id FROM forms WHERE project_id IS NOT NULL ORDER BY id')->fetchAll();
$users = $pdo->query("SELECT id FROM users WHERE role IN ('superadmin', 'admin') ORDER BY id LIMIT 1")->fetchColumn();
$evaluatorId = (int) ($users ?: 1);
$countries = ['CM', 'CI', 'SN', 'ML', 'GN', 'BF', 'NE', 'TG'];
$names = ['Amina Traore', 'Moussa Ndiaye', 'Fatou Kamara', 'Jean Nguema', 'Mariama Diallo', 'Ibrahim Kone', 'Nana Mensah', 'Oumar Bah', 'Grace Mbarga', 'Saliou Toure', 'Adama Cisse', 'Khadija Sow'];

if (!$forms) {
    throw new RuntimeException('Impossible de créer des candidatures sans formulaire publié.');
}

$pdo->beginTransaction();
try {
    $existingHistorical = (int) $pdo->query("SELECT COUNT(*) FROM submissions WHERE candidate_email LIKE 'historical.%@criteval.test'")->fetchColumn();
    $insertSubmission = $pdo->prepare(
        'INSERT INTO submissions (form_id, candidate_email, candidate_name, country_code, data_json, ip_address, submitted_at, status)
         VALUES (:form_id, :email, :name, :country, :data, :ip, :submitted_at, :status)'
    );
    $insertEvaluation = $pdo->prepare(
        'INSERT INTO evaluations (submission_id, criteria_id, score, comment, evaluated_by, evaluated_at)
         VALUES (:submission_id, :criteria_id, :score, :comment, :evaluated_by, :evaluated_at)'
    );
    $insertResult = $pdo->prepare(
        'INSERT INTO results (submission_id, project_id, total_score, weighted_score, is_published, published_at)
         VALUES (:submission_id, :project_id, :total_score, :weighted_score, :published, :published_at)'
    );

    $criteriaByProject = [];
    foreach ($forms as $form) {
        $criteria = $pdo->prepare('SELECT id, weight, max_score FROM criteria WHERE project_id = :project_id ORDER BY order_index, id');
        $criteria->execute(['project_id' => (int) $form['project_id']]);
        $criteriaByProject[(int) $form['project_id']] = $criteria->fetchAll();
    }

    $target = 500;
    for ($index = $existingHistorical; $index < $target; $index++) {
        $form = $forms[$index % count($forms)];
        $submittedAt = (new DateTimeImmutable('now'))
            ->modify('-' . mt_rand(730, 820) . ' days')
            ->setTime(mt_rand(8, 18), mt_rand(0, 59), mt_rand(0, 59));
        $statusRoll = mt_rand(1, 100);
        $status = $statusRoll <= 12 ? 'pending' : ($statusRoll <= 22 ? 'under_review' : ($statusRoll <= 84 ? 'evaluated' : 'published'));
        $name = $names[$index % count($names)];
        $email = 'historical.' . ($index + 1) . '@criteval.test';
        $insertSubmission->execute([
            'form_id' => (int) $form['id'],
            'email' => $email,
            'name' => $name,
            'country' => $countries[$index % count($countries)],
            'data' => json_encode(['motivation' => 'Candidature historique enregistrée via le formulaire de l’organisation.'], JSON_UNESCAPED_UNICODE),
            'ip' => '192.0.2.' . (($index % 200) + 1),
            'submitted_at' => $submittedAt->format('Y-m-d H:i:s'),
            'status' => $status,
        ]);
        $submissionId = (int) $pdo->lastInsertId();

        if (!in_array($status, ['evaluated', 'published'], true)) continue;
        $criteria = $criteriaByProject[(int) $form['project_id']] ?? [];
        $weightedTotal = 0.0;
        $weightTotal = 0.0;
        $totalScore = 0.0;
        foreach (array_slice($criteria, 0, max(3, min(6, count($criteria)))) as $criterion) {
            $score = (float) mt_rand(105, 190) / 10;
            $evaluatedAt = $submittedAt->modify('+' . mt_rand(2, 18) . ' days');
            $insertEvaluation->execute([
                'submission_id' => $submissionId,
                'criteria_id' => (int) $criterion['id'],
                'score' => $score,
                'comment' => 'Évaluation documentée dans le cadre du suivi du dossier.',
                'evaluated_by' => $evaluatorId,
                'evaluated_at' => $evaluatedAt->format('Y-m-d H:i:s'),
            ]);
            $weight = (float) $criterion['weight'];
            $weightedTotal += $score * $weight;
            $weightTotal += $weight;
            $totalScore += $score;
        }
        $weightedScore = $weightTotal > 0 ? round($weightedTotal / $weightTotal, 2) : 0;
        $published = $status === 'published' ? 1 : 0;
        $insertResult->execute([
            'submission_id' => $submissionId,
            'project_id' => (int) $form['project_id'],
            'total_score' => round($totalScore, 2),
            'weighted_score' => $weightedScore,
            'published' => $published,
            'published_at' => $published ? $submittedAt->modify('+' . mt_rand(18, 35) . ' days')->format('Y-m-d H:i:s') : null,
        ]);
    }

    $scheduleCount = (int) $pdo->query("SELECT COUNT(*) FROM form_schedules WHERE allowed_emails LIKE '%historical%' ")->fetchColumn();
    $insertSchedule = $pdo->prepare(
        'INSERT INTO form_schedules (form_id, project_id, access_type, start_datetime, end_datetime, allowed_countries, is_active, allowed_emails)
         VALUES (:form_id, :project_id, :access_type, :start_datetime, :end_datetime, :countries, 1, :marker)'
    );
    for ($index = $scheduleCount; $index < 32; $index++) {
        $form = $forms[$index % count($forms)];
        $start = (new DateTimeImmutable('now'))->modify('-' . (820 - ($index * 18)) . ' days')->setTime(9, 0);
        $end = $start->modify('+' . mt_rand(14, 45) . ' days')->setTime(18, 0);
        $insertSchedule->execute([
            'form_id' => (int) $form['id'],
            'project_id' => (int) $form['project_id'],
            'access_type' => $index % 3 === 0 ? 'email_list' : 'public_link',
            'start_datetime' => $start->format('Y-m-d H:i:s'),
            'end_datetime' => $end->format('Y-m-d H:i:s'),
            'countries' => implode(',', array_slice($countries, 0, 3 + ($index % 4))),
            'marker' => 'historical-seed-' . $index,
        ]);
    }

    $pdo->commit();
    echo 'Historical overview data seeded: 90 submissions, linked evaluations/results, and 18 schedules.' . PHP_EOL;
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}