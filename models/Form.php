<?php
declare(strict_types=1);

class Form
{
    public static function all(): array
    {
        return db()->query(
            'SELECT f.id, f.title, f.description, f.status, f.layout_json, f.created_at, p.title AS project_title
             FROM forms f LEFT JOIN projects p ON p.id = f.project_id
             ORDER BY f.created_at DESC, f.id DESC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM forms WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $form = $stmt->fetch();
        return is_array($form) ? $form : null;
    }

    public static function save(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        $params = [
            'title' => $data['title'], 'description' => $data['description'],
            'project_id' => $data['project_id'] ?: null, 'layout_json' => $data['layout_json'],
            'status' => $data['status'], 'created_by' => $data['created_by'] ?: null,
        ];
        if ($id > 0) {
            $params['id'] = $id;
            db()->prepare('UPDATE forms SET title=:title, description=:description, project_id=:project_id, layout_json=:layout_json, status=:status WHERE id=:id')->execute($params);
            return $id;
        }
        db()->prepare('INSERT INTO forms (title, description, project_id, layout_json, status, created_by) VALUES (:title, :description, :project_id, :layout_json, :status, :created_by)')->execute($params);
        return (int) db()->lastInsertId();
    }

    public static function schedules(): array
    {
        return db()->query('SELECT s.*, f.title AS form_title, p.title AS project_title FROM form_schedules s JOIN forms f ON f.id=s.form_id LEFT JOIN projects p ON p.id=s.project_id ORDER BY s.start_datetime')->fetchAll();
    }

    public static function saveSchedule(array $data): int
    {
        $stmt = db()->prepare('INSERT INTO form_schedules (form_id, project_id, access_type, start_datetime, end_datetime, allowed_emails, allowed_countries, is_active) VALUES (:form_id, :project_id, :access_type, :start_datetime, :end_datetime, :allowed_emails, :allowed_countries, :is_active)');
        $stmt->execute($data);
        return (int) db()->lastInsertId();
    }
}
