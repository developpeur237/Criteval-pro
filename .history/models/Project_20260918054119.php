<?php
declare(strict_types=1);

class Project
{
    public static function all(): array
    {
        return db()->query('SELECT id, title, status FROM projects ORDER BY title ASC')->fetchAll();
    }

    public static function manageAll(): array
    {
        return db()->query(
            'SELECT id, title, organization, domains, target_audiences, legal_status, country_code, project_count,
                    intervention_zone, description, contact_name, contact_phone, contact_email, website,
                    budget_requested, duration_months, logo_path, status, created_at
             FROM projects ORDER BY created_at DESC, id DESC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM projects WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $project = $stmt->fetch();
        return $project ?: null;
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

    public static function dashboardProjects(?int $limit = null): array
    {
        $sql = 'SELECT id, title, organization, domains, target_audiences, legal_status, country_code, project_count,
                    intervention_zone, description, contact_name, contact_phone, contact_email, website,
                    budget_requested, duration_months, logo_path, status, created_at
             FROM projects
             ORDER BY created_at DESC, id DESC';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . max(0, $limit);
        }

        $stmt = db()->query($sql);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
                'INSERT INTO projects (title, organization, domains, target_audiences, legal_status, country_code, project_count,
                    intervention_zone, description, contact_name, contact_phone, contact_email, website, budget_requested,
                    duration_months, logo_path, status, created_by)
                 VALUES (:title, :organization, :domains, :target_audiences, :legal_status, :country_code, :project_count,
                    :intervention_zone, :description, :contact_name, :contact_phone, :contact_email, :website, :budget_requested,
                    :duration_months, :logo_path, :status, :created_by)'
        );
        $stmt->execute(self::parameters($data));
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $parameters = self::parameters($data);
        unset($parameters['created_by']);
        $parameters['id'] = $id;
        db()->prepare(
            'UPDATE projects SET title = :title, organization = :organization, domains = :domains,
                target_audiences = :target_audiences, legal_status = :legal_status, country_code = :country_code,
                project_count = :project_count, intervention_zone = :intervention_zone, description = :description,
                contact_name = :contact_name, contact_phone = :contact_phone, contact_email = :contact_email,
                website = :website, budget_requested = :budget_requested, duration_months = :duration_months,
                logo_path = :logo_path, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        )->execute($parameters);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM projects WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private static function parameters(array $data): array
    {
        return [
            'title' => $data['title'],
            'organization' => $data['organization'] ?? null,
            'domains' => $data['domains'] ?? null,
            'target_audiences' => $data['target_audiences'] ?? null,
            'legal_status' => $data['legal_status'] ?? 'non_legal',
            'country_code' => $data['country_code'] ?? '',
            'project_count' => $data['project_count'] ?? null,
            'intervention_zone' => $data['intervention_zone'] ?? null,
            'description' => $data['description'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'website' => $data['website'] ?? null,
            'budget_requested' => $data['budget_requested'] ?? null,
            'duration_months' => $data['duration_months'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'status' => $data['status'],
            'created_by' => $data['created_by'] ?? null,
        ];
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
