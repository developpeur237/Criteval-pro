<?php
declare(strict_types=1);

class TrainingSession
{
    public static function all(bool $activeOnly = false): array
    {
        $where = $activeOnly ? ' WHERE is_active = 1' : '';
        return db()->query('SELECT * FROM training_sessions' . $where . ' ORDER BY session_date ASC, id ASC')->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO training_sessions (name, objective, session_date, public_slug, is_active, created_by)
             VALUES (:name, :objective, :session_date, :public_slug, :is_active, :created_by)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'objective' => $data['objective'],
            'session_date' => $data['session_date'],
            'public_slug' => $data['public_slug'],
            'is_active' => $data['is_active'] ?? 1,
            'created_by' => $data['created_by'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM training_sessions WHERE public_slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }
}
