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
            $requiredStatement = $pdo->prepare('SELECT id FROM criteria WHERE project_id = :project_id AND is_required = 1');
            $requiredStatement->execute(['project_id' => (int) $submission['project_id']]);
            $requiredCriteria = array_map('intval', $requiredStatement->fetchAll(PDO::FETCH_COLUMN));
            $submittedCriteria = array_map('intval', array_keys($scores));
            $missing = array_diff($requiredCriteria, $submittedCriteria);
            if ($missing !== []) {
                throw new InvalidArgumentException('Toutes les notes des critères obligatoires sont requises.');
            }
            $evidenceStatement = $pdo->prepare(
                'SELECT c.id FROM criteria c
                 LEFT JOIN organization_evidence oe ON oe.criteria_id = c.id AND oe.submission_id = :submission_id
                 WHERE c.project_id = :project_id AND c.is_required = 1
                 GROUP BY c.id HAVING COUNT(oe.id) = 0'
            );
            $evidenceStatement->execute(['submission_id' => $submissionId, 'project_id' => (int) $submission['project_id']]);
            if ($evidenceStatement->fetchColumn() !== false) {
                throw new InvalidArgumentException('Chaque critère obligatoire doit disposer d’une preuve avant évaluation.');
            }
            $statement = $pdo->prepare(
                'INSERT INTO evaluations (submission_id, criteria_id, score, comment, evaluated_by)
                 VALUES (:submission_id, :criteria_id, :score, :comment, :evaluated_by)'
            );
            $deleteExisting = $pdo->prepare('DELETE FROM evaluations WHERE submission_id = :submission_id AND criteria_id = :criteria_id');
            $criteriaIds = [];
            foreach ($scores as $criteriaId => $score) {
                $criteria = Criteria::find((int) $criteriaId);
                if (!$criteria || (int) $criteria['project_id'] !== (int) $submission['project_id']) {
                    throw new InvalidArgumentException('Un critere ne correspond pas a cette organisation.');
                }
                if (!is_numeric($score) || (float) $score < 0 || (float) $score > (float) $criteria['max_score']) {
                    throw new InvalidArgumentException('Chaque note doit etre comprise entre 0 et le score maximal du critere.');
                }
                $criteriaIds[] = (int) $criteriaId;
                $safeScore = (float) $score;
                $deleteExisting->execute(['submission_id' => $submissionId, 'criteria_id' => (int) $criteriaId]);
                $statement->execute([
                    'submission_id' => $submissionId,
                    'criteria_id' => (int) $criteriaId,
                    'score' => $safeScore,
                    'comment' => null,
                    'evaluated_by' => $userId,
                ]);
            }
            if (count($criteriaIds) !== count(array_unique($criteriaIds))) {
                throw new InvalidArgumentException('Un critere ne peut etre note qu une seule fois.');
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
            self::refreshRanks($pdo, (int) $submission['project_id']);
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private static function refreshRanks(PDO $pdo, int $projectId): void
    {
        $rows = $pdo->prepare(
            'SELECT r.submission_id FROM results r
             JOIN submissions s ON s.id = r.submission_id
             WHERE r.project_id = :project_id AND s.status IN ("evaluated", "published")
             ORDER BY COALESCE(r.weighted_score, 0) DESC, s.submitted_at ASC, s.id ASC'
        );
        $rows->execute(['project_id' => $projectId]);
        $update = $pdo->prepare('UPDATE results SET rank_position = :rank WHERE submission_id = :submission_id');
        $rank = 1;
        foreach ($rows->fetchAll(PDO::FETCH_COLUMN) as $submissionId) {
            $update->execute(['rank' => $rank++, 'submission_id' => (int) $submissionId]);
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
