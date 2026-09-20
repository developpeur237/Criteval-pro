<?php
declare(strict_types=1);

class Evaluation
{
    public static function saveScores(int $submissionId, array $scores, int $userId): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $submissionStatement = $pdo->prepare(
                'SELECT p.id AS project_id FROM submissions s JOIN forms f ON f.id = s.form_id
                 JOIN projects p ON p.id = f.project_id WHERE s.id = :id LIMIT 1'
            );
            $submissionStatement->execute(['id' => $submissionId]);
            $submission = $submissionStatement->fetch();
            if (!is_array($submission)) {
                throw new InvalidArgumentException('Soumission introuvable.');
            }
            $statement = $pdo->prepare(
                'INSERT INTO evaluations (submission_id, criteria_id, score, comment, evaluated_by)
                 VALUES (:submission_id, :criteria_id, :score, :comment, :evaluated_by)'
            );
            foreach ($scores as $criteriaId => $score) {
                $criteria = Criteria::find((int) $criteriaId);
                if (!$criteria) continue;
                $safeScore = max(0, min((float) $criteria['max_score'], (float) $score));
                $statement->execute([
                    'submission_id' => $submissionId,
                    'criteria_id' => (int) $criteriaId,
                    'score' => $safeScore,
                    'comment' => null,
                    'evaluated_by' => $userId,
                ]);
            }
            $pdo->prepare("UPDATE submissions SET status = 'evaluated' WHERE id = :id")->execute(['id' => $submissionId]);
            $scoreStatement = $pdo->prepare(
                'SELECT COALESCE(SUM(e.score), 0) AS total_score,
                        COALESCE(SUM(e.score * c.weight) / NULLIF(SUM(c.weight), 0), 0) AS weighted_score
                 FROM evaluations e JOIN criteria c ON c.id = e.criteria_id
                 WHERE e.submission_id = :submission_id'
            );
            $scoreStatement->execute(['submission_id' => $submissionId]);
            $score = $scoreStatement->fetch();
            $resultStatement = $pdo->prepare(
                'INSERT INTO results (submission_id, project_id, total_score, weighted_score)
                 VALUES (:submission_id, :project_id, :total_score, :weighted_score)
                 ON CONFLICT(submission_id) DO UPDATE SET total_score = excluded.total_score,
                 weighted_score = excluded.weighted_score, project_id = excluded.project_id'
            );
            $resultStatement->execute([
                'submission_id' => $submissionId,
                'project_id' => (int) $submission['project_id'],
                'total_score' => (float) ($score['total_score'] ?? 0),
                'weighted_score' => (float) ($score['weighted_score'] ?? 0),
            ]);
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public static function count(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM evaluations')->fetchColumn();
    }

    public static function averageScore(): float
    {
        $value = db()->query('SELECT COALESCE(AVG(score), 0) FROM evaluations')->fetchColumn();
        return (float) $value;
    }
}
