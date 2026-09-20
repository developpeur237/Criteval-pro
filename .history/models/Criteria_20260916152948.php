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

    public static function templates(): array
    {
        $stmt = db()->query(
            'SELECT id, label, description, weight, max_score, is_required, order_index
             FROM criteria_templates WHERE is_active = 1 ORDER BY order_index ASC, id ASC'
        );
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public static function projectConfiguration(int $projectId): array
    {
        $stmt = db()->prepare(
            'SELECT c.id, c.project_id, c.source_template_id, c.label, c.description, c.weight,
                    c.max_score, c.is_required, c.order_index
             FROM criteria c WHERE c.project_id = :project_id
             ORDER BY c.order_index ASC, c.id ASC'
        );
        $stmt->execute(['project_id' => $projectId]);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public static function configureProject(int $projectId, array $templateIds = [], array $customCriteria = []): void
    {
        if ($projectId <= 0) {
            return;
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $templateIds = array_values(array_unique(array_filter(array_map('intval', $templateIds), static fn (int $id): bool => $id > 0)));
            $templateQuery = $pdo->prepare('SELECT * FROM criteria_templates WHERE id = :id AND is_active = 1');
            $existingTemplate = $pdo->prepare('SELECT id FROM criteria WHERE project_id = :project_id AND source_template_id = :template_id LIMIT 1');
            $insertCriterion = $pdo->prepare(
                'INSERT INTO criteria (project_id, label, description, weight, max_score, is_required, order_index, source_template_id)
                 VALUES (:project_id, :label, :description, :weight, :max_score, :is_required, :order_index, :source_template_id)'
            );
            foreach ($templateIds as $order => $templateId) {
                $templateQuery->execute(['id' => $templateId]);
                $template = $templateQuery->fetch();
                if (!$template) {
                    continue;
                }
                $existingTemplate->execute(['project_id' => $projectId, 'template_id' => $templateId]);
                if (!$existingTemplate->fetchColumn()) {
                    $insertCriterion->execute([
                        'project_id' => $projectId,
                        'label' => $template['label'],
                        'description' => $template['description'],
                        'weight' => $template['weight'],
                        'max_score' => $template['max_score'],
                        'is_required' => $template['is_required'],
                        'order_index' => $order + 1,
                        'source_template_id' => $templateId,
                    ]);
                }
            }

            $selectedTemplateIds = $templateIds ?: [0];
            $placeholders = implode(',', array_fill(0, count($selectedTemplateIds), '?'));
            $deleteTemplates = $pdo->prepare(
                'DELETE FROM criteria WHERE project_id = ? AND source_template_id IS NOT NULL AND source_template_id NOT IN (' . $placeholders . ')
                 AND id NOT IN (SELECT criteria_id FROM evaluations)'
            );
            $deleteTemplates->execute(array_merge([$projectId], $selectedTemplateIds));

            $customIds = [];
            $customInsert = $pdo->prepare(
                'INSERT INTO criteria (project_id, label, description, weight, max_score, is_required, order_index, source_template_id)
                 VALUES (:project_id, :label, :description, :weight, :max_score, :is_required, :order_index, NULL)'
            );
            $customUpdate = $pdo->prepare(
                'UPDATE criteria SET label = :label, description = :description, weight = :weight,
                    max_score = :max_score, is_required = :is_required, order_index = :order_index
                 WHERE id = :id AND project_id = :project_id AND source_template_id IS NULL'
            );
            foreach (array_values($customCriteria) as $order => $custom) {
                if (!is_array($custom)) {
                    continue;
                }
                $label = trim((string) ($custom['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $id = (int) ($custom['id'] ?? 0);
                $values = [
                    'label' => $label,
                    'description' => trim((string) ($custom['description'] ?? '')),
                    'weight' => max(0.01, (float) ($custom['weight'] ?? 1)),
                    'max_score' => max(0.01, (float) ($custom['max_score'] ?? 20)),
                    'is_required' => !empty($custom['is_required']) ? 1 : 0,
                    'order_index' => count($templateIds) + $order + 1,
                ];
                if ($id > 0) {
                    $values['id'] = $id;
                    $values['project_id'] = $projectId;
                    $customUpdate->execute($values);
                    if ($customUpdate->rowCount() > 0 || self::belongsToProject($id, $projectId)) {
                        $customIds[] = $id;
                    }
                } else {
                    $customInsert->execute(array_merge(['project_id' => $projectId], $values));
                    $customIds[] = (int) $pdo->lastInsertId();
                }
            }

            $deleteCustom = 'DELETE FROM criteria WHERE project_id = ? AND source_template_id IS NULL';
            $deleteParams = [$projectId];
            if ($customIds !== []) {
                $customPlaceholders = implode(',', array_fill(0, count($customIds), '?'));
                $deleteCustom .= ' AND id NOT IN (' . $customPlaceholders . ')';
                $deleteParams = array_merge($deleteParams, $customIds);
            }
            $deleteCustom .= ' AND id NOT IN (SELECT criteria_id FROM evaluations)';
            $pdo->prepare($deleteCustom)->execute($deleteParams);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    private static function belongsToProject(int $criteriaId, int $projectId): bool
    {
        $stmt = db()->prepare('SELECT COUNT(*) FROM criteria WHERE id = :id AND project_id = :project_id AND source_template_id IS NULL');
        $stmt->execute(['id' => $criteriaId, 'project_id' => $projectId]);
        return (int) $stmt->fetchColumn() > 0;
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
