<?php
declare(strict_types=1);

class Submission
{
    public static function forEvaluation(int $limit = 50): array
    {
        $stmt = db()->prepare(
            'SELECT s.*, COALESCE(p.organization, p.title) AS organization, p.id AS project_id
             FROM submissions s
             JOIN forms f ON f.id = s.form_id
             LEFT JOIN projects p ON p.id = f.project_id
             WHERE s.status IN ("pending", "under_review")
             ORDER BY s.submitted_at ASC, s.id ASC LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO submissions (form_id, candidate_email, candidate_name, country_code, data_json, ip_address, status)
             VALUES (:form_id, :candidate_email, :candidate_name, :country_code, :data_json, :ip_address, :status)'
        );
        $stmt->execute([
            'form_id' => (int) $data['form_id'],
            'candidate_email' => $data['candidate_email'],
            'candidate_name' => $data['candidate_name'] ?? null,
            'country_code' => $data['country_code'] ?? null,
            'data_json' => $data['data_json'] ?? '{}',
            'ip_address' => $data['ip_address'] ?? null,
            'status' => $data['status'] ?? 'pending',
        ]);
        return (int) db()->lastInsertId();
    }

    public static function count(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM submissions')->fetchColumn();
    }

    public static function pendingCount(): int
    {
        return (int) db()->query("SELECT COUNT(*) FROM submissions WHERE status = 'pending'")->fetchColumn();
    }

    public static function evaluatedCount(): int
    {
        return (int) db()->query("SELECT COUNT(*) FROM submissions WHERE status IN ('evaluated', 'published')")->fetchColumn();
    }

    public static function latestForDashboard(int $limit = 5): array
    {
        $stmt = db()->prepare(
            'SELECT s.id, s.candidate_name, s.candidate_email, s.country_code, s.status, s.submitted_at,
                    COALESCE(r.weighted_score, r.total_score) AS score,
                    p.title AS project_title
             FROM submissions s
             LEFT JOIN forms f ON f.id = s.form_id
             LEFT JOIN projects p ON p.id = f.project_id
             LEFT JOIN results r ON r.submission_id = s.id
             ORDER BY s.submitted_at DESC, s.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function monthlyCounts(int $months = 24): array
    {
        $stmt = db()->prepare(
            "SELECT strftime('%m/%Y', submitted_at) AS label, COUNT(*) AS total
             FROM submissions
             WHERE submitted_at >= datetime('now', :interval)
             GROUP BY strftime('%Y-%m', submitted_at)
             ORDER BY MIN(submitted_at) ASC"
        );
        $stmt->bindValue(':interval', '-' . max(6, $months) . ' months', PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function chartCounts(?int $days = null): array
    {
        $where = '';
        $params = [];
        if ($days !== null) {
            $where = "WHERE event_date >= datetime('now', :interval)";
            $params[':interval'] = '-' . max(365, $days) . ' days';
        }

        $stmt = db()->prepare(
            "WITH event_stream AS (
                SELECT submitted_at AS event_date,
                       CASE WHEN status IN ('evaluated', 'published') THEN 1 ELSE 0 END AS is_evaluated
                FROM submissions
                UNION ALL
                SELECT start_datetime AS event_date, 0 AS is_evaluated
                FROM form_schedules
                WHERE start_datetime IS NOT NULL
                UNION ALL
                SELECT end_datetime AS event_date, 0 AS is_evaluated
                FROM form_schedules
                WHERE end_datetime IS NOT NULL
                UNION ALL
                SELECT session_date AS event_date, 0 AS is_evaluated
                FROM training_sessions
                WHERE session_date IS NOT NULL
                UNION ALL
                SELECT published_at AS event_date, 1 AS is_evaluated
                FROM results
                WHERE published_at IS NOT NULL
            )
            SELECT date(event_date) AS label,
                   COUNT(*) AS total,
                   SUM(is_evaluated) AS evaluated
            FROM event_stream
            {$where}
            GROUP BY date(event_date)
            ORDER BY label ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countryCounts(int $limit = 5): array
    {
        $stmt = db()->prepare(
            'SELECT COALESCE(country_code, "N/A") AS country_code, COUNT(*) AS total
             FROM submissions
             GROUP BY COALESCE(country_code, "N/A")
             ORDER BY total DESC, country_code ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
