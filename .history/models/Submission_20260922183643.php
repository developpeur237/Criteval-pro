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

    public static function evidenceForSubmission(int $submissionId): array
    {
        $statement = db()->prepare(
            'SELECT oe.id, oe.criteria_id, oe.original_name, oe.mime_type, oe.file_size, c.label
             FROM organization_evidence oe JOIN criteria c ON c.id = oe.criteria_id
             WHERE oe.submission_id = :submission_id ORDER BY c.order_index, oe.id'
        );
        $statement->execute(['submission_id' => $submissionId]);
        return $statement->fetchAll();
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
            $where = "WHERE submitted_at >= datetime('now', :interval)
                      AND submitted_at <= datetime('now')";
            $params[':interval'] = '-' . max(1, $days) . ' days';
        } else {
            $where = "WHERE submitted_at <= datetime('now')";
        }

        $stmt = db()->prepare(
            "SELECT date(submitted_at) AS label,
                   COUNT(*) AS total,
                   SUM(CASE WHEN status IN ('evaluated', 'published') THEN 1 ELSE 0 END) AS evaluated
            FROM submissions
            {$where}
            GROUP BY date(submitted_at)
            ORDER BY label ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function allTimeMonthlyChartCounts(): array
    {
        $stmt = db()->query(
            "WITH RECURSIVE months(month_start) AS (
                SELECT date(strftime('%Y-%m-01', MIN(submitted_at)))
                FROM submissions
                WHERE submitted_at <= datetime('now')
                UNION ALL
                SELECT date(month_start, '+1 month')
                FROM months
                WHERE month_start < date('now', 'start of month')
            ), monthly_counts AS (
                SELECT strftime('%Y-%m-01', submitted_at) AS month_start,
                       COUNT(*) AS total,
                       SUM(CASE WHEN status IN ('evaluated', 'published') THEN 1 ELSE 0 END) AS evaluated
                FROM submissions
                WHERE submitted_at <= datetime('now')
                GROUP BY strftime('%Y-%m-01', submitted_at)
            )
            SELECT strftime('%m/%Y', months.month_start) AS label,
                   COALESCE(monthly_counts.total, 0) AS total,
                   COALESCE(monthly_counts.evaluated, 0) AS evaluated
            FROM months
            LEFT JOIN monthly_counts ON monthly_counts.month_start = months.month_start
            ORDER BY months.month_start ASC"
        );

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
