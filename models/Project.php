<?php
declare(strict_types=1);

class Project
{
    public static function all(): array
    {
        return db()->query('SELECT id, title, status FROM projects ORDER BY title ASC')->fetchAll();
    }
    public static function countByStatus(string $status): int
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM projects WHERE status = :status');
        $stmt->execute(['status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    public static function activeCount(): int
    {
        return self::countByStatus('active');
    }

    public static function dashboardProjects(): array
    {
        $stmt = db()->query(
            'SELECT id, title, organization, intervention_zone, status, created_at
             FROM projects
             ORDER BY created_at DESC, id DESC
             LIMIT 4'
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO projects (title, organization, intervention_zone, description, status, created_by)
             VALUES (:title, :organization, :intervention_zone, :description, :status, :created_by)'
        );
        $stmt->execute([
            'title' => $data['title'],
            'organization' => $data['organization'],
            'intervention_zone' => $data['intervention_zone'],
            'description' => $data['description'],
            'status' => $data['status'],
            'created_by' => $data['created_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function submissionsByProject(): array
    {
        $stmt = db()->query(
            'SELECT p.title, COUNT(s.id) AS submission_count
             FROM projects p
             LEFT JOIN forms f ON f.project_id = p.id
             LEFT JOIN submissions s ON s.form_id = f.id
             GROUP BY p.id, p.title
             ORDER BY submission_count DESC, p.id DESC
             LIMIT 6'
        );
        return $stmt->fetchAll();
    }
}
