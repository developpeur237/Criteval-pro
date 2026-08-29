<?php
declare(strict_types=1);

class Criteria
{
    public static function all(): array
    {
        $stmt = db()->query(
            'SELECT c.*, p.title AS project_title
             FROM criteria c
             LEFT JOIN projects p ON p.id = c.project_id
             ORDER BY c.project_id ASC, c.order_index ASC, c.id ASC'
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public static function byProject(int $projectId): array
    {
        $stmt = db()->prepare(
            'SELECT c.*, p.title AS project_title
             FROM criteria c
             LEFT JOIN projects p ON p.id = c.project_id
             WHERE c.project_id = :project_id
             ORDER BY c.order_index ASC, c.id ASC'
        );
        $stmt->execute(['project_id' => $projectId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM criteria WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO criteria (project_id, label, description, weight, max_score, is_required, order_index)
             VALUES (:project_id, :label, :description, :weight, :max_score, :is_required, :order_index)'
        );
        $stmt->execute([
            'project_id' => (int) ($data['project_id'] ?? 0),
            'label' => (string) ($data['label'] ?? ''),
            'description' => $data['description'] ?? null,
            'weight' => isset($data['weight']) ? (float) $data['weight'] : 1.0,
            'max_score' => isset($data['max_score']) ? (float) $data['max_score'] : 20.0,
            'is_required' => isset($data['is_required']) ? (int) $data['is_required'] : 1,
            'order_index' => isset($data['order_index']) ? (int) $data['order_index'] : 0,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['project_id'])) { $fields[] = 'project_id = :project_id'; $params['project_id'] = (int) $data['project_id']; }
        if (isset($data['label'])) { $fields[] = 'label = :label'; $params['label'] = (string) $data['label']; }
        if (array_key_exists('description', $data)) { $fields[] = 'description = :description'; $params['description'] = $data['description']; }
        if (isset($data['weight'])) { $fields[] = 'weight = :weight'; $params['weight'] = (float) $data['weight']; }
        if (isset($data['max_score'])) { $fields[] = 'max_score = :max_score'; $params['max_score'] = (float) $data['max_score']; }
        if (isset($data['is_required'])) { $fields[] = 'is_required = :is_required'; $params['is_required'] = (int) $data['is_required']; }
        if (isset($data['order_index'])) { $fields[] = 'order_index = :order_index'; $params['order_index'] = (int) $data['order_index']; }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE criteria SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = db()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare('DELETE FROM criteria WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
