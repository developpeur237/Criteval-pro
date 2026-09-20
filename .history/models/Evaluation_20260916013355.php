<?php
declare(strict_types=1);

class Evaluation
{
    public static function saveScores(int $submissionId, array $scores, int $userId): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
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
