<?php
declare(strict_types=1);

require_once __DIR__ . '/FormField.php';

class Form
{
    public static function normalizeLayout(string $layout): string
    {
        $clip = static fn (string $value, int $length): string => function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
        $items = json_decode($layout, true);
        if (!is_array($items) || count($items) > 200) {
            throw new InvalidArgumentException('La structure du formulaire est invalide ou trop volumineuse.');
        }
        $allowed = FormField::TYPES;
        $clamp = static fn ($value, int $min, int $max, int $default): int => max($min, min($max, (int) ($value ?? $default)));
        $choice = static fn ($value, array $allowed, string $default): string => in_array((string) $value, $allowed, true) ? (string) $value : $default;
        $hex = static fn ($value, string $default): string => preg_match('/^#[0-9a-f]{6}$/i', (string) $value) ? (string) $value : $default;
        $cssText = static fn ($value, string $default, int $length = 180): string => preg_replace('/[^a-zA-Z0-9_#(),.%+\-\s]/', '', substr((string) ($value ?? $default), 0, $length)) ?: $default;
        $normalized = [];
        foreach ($items as $index => $item) {
            if (!is_array($item) || !in_array((string) ($item['type'] ?? ''), $allowed, true)) {
                throw new InvalidArgumentException('Un élément du formulaire est invalide.');
            }
            $type = (string) $item['type'];
            $name = preg_replace('/[^A-Za-z0-9_-]/', '_', (string) ($item['name'] ?? 'champ_' . $index));
            $name = trim((string) $name, '_') ?: 'champ_' . $index;
            $normalized[] = [
                'id' => substr((string) ($item['id'] ?? 'field-' . $index), 0, 80),
                'type' => $type,
                'label' => $clip(trim(strip_tags((string) ($item['label'] ?? 'Champ'))), 180),
                'name' => substr($name, 0, 80),
                'placeholder' => $clip(trim(strip_tags((string) ($item['placeholder'] ?? ''))), 500),
                'options' => $clip((string) ($item['options'] ?? ''), 2000),
                'required' => !empty($item['required']),
                'gridVersion' => 2,
                'span' => max(1, min(24, (int) ($item['span'] ?? 12) * ((int) ($item['gridVersion'] ?? 1) < 2 ? 2 : 1))),
                'rowSpan' => max(1, min(6, (int) ($item['rowSpan'] ?? 1))),
                'scale' => max(50, min(200, (int) ($item['scale'] ?? 100))),
                'background' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($item['background'] ?? '')) ? $item['background'] : '#ffffff',
                'color' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($item['color'] ?? '')) ? $item['color'] : '#1e293b',
                'radius' => max(0, min(24, (int) ($item['radius'] ?? 10))),
                'widthValue' => $clamp($item['widthValue'] ?? 100, 1, 2000, 100),
                'widthUnit' => $choice($item['widthUnit'] ?? '%', ['%', 'px', 'auto'], '%'),
                'heightValue' => $clamp($item['heightValue'] ?? 0, 0, 2000, 0),
                'heightUnit' => $choice($item['heightUnit'] ?? 'auto', ['auto', 'px', '%'], 'auto'),
                'marginTop' => $clamp($item['marginTop'] ?? 0, -200, 500, 0),
                'marginRight' => $clamp($item['marginRight'] ?? 0, -200, 500, 0),
                'marginBottom' => $clamp($item['marginBottom'] ?? 0, -200, 500, 0),
                'marginLeft' => $clamp($item['marginLeft'] ?? 0, -200, 500, 0),
                'paddingTop' => $clamp($item['paddingTop'] ?? 14, 0, 500, 14),
                'paddingRight' => $clamp($item['paddingRight'] ?? 14, 0, 500, 14),
                'paddingBottom' => $clamp($item['paddingBottom'] ?? 14, 0, 500, 14),
                'paddingLeft' => $clamp($item['paddingLeft'] ?? 14, 0, 500, 14),
                'position' => $choice($item['position'] ?? 'normal', ['normal', 'relative', 'absolute'], 'normal'),
                'offsetX' => $clamp($item['offsetX'] ?? 0, -2000, 2000, 0),
                'offsetY' => $clamp($item['offsetY'] ?? 0, -2000, 2000, 0),
                'zIndex' => $clamp($item['zIndex'] ?? 1, 0, 9999, 1),
                'borderWidth' => $clamp($item['borderWidth'] ?? 1, 0, 30, 1),
                'borderStyle' => $choice($item['borderStyle'] ?? 'solid', ['none', 'solid', 'dashed', 'dotted', 'double'], 'solid'),
                'borderColor' => $hex($item['borderColor'] ?? '#d5dce7', '#d5dce7'),
                'opacity' => $clamp($item['opacity'] ?? 100, 0, 100, 100),
                'shadow' => $choice($item['shadow'] ?? 'soft', ['none', 'soft', 'medium', 'strong'], 'soft'),
                'textAlign' => $choice($item['textAlign'] ?? 'left', ['left', 'center', 'right', 'justify'], 'left'),
                'fontSize' => $clamp($item['fontSize'] ?? 14, 8, 72, 14),
                'fontWeight' => $choice($item['fontWeight'] ?? '600', ['400', '500', '600', '700', '800'], '600'),
                'lineHeight' => $clamp($item['lineHeight'] ?? 140, 80, 240, 140),
                'letterSpacing' => $clamp($item['letterSpacing'] ?? 0, -10, 30, 0),
                'visibility' => $choice($item['visibility'] ?? 'visible', ['visible', 'hidden'], 'visible'),
                'overflow' => $choice($item['overflow'] ?? 'visible', ['visible', 'hidden', 'auto'], 'visible'),
                // Extended visual system: every element can be tuned independently.
                'minWidth' => $clamp($item['minWidth'] ?? 0, 0, 2000, 0),
                'maxWidth' => $clamp($item['maxWidth'] ?? 2000, 0, 3000, 2000),
                'minHeight' => $clamp($item['minHeight'] ?? 0, 0, 2000, 0),
                'maxHeight' => $clamp($item['maxHeight'] ?? 2000, 0, 3000, 2000),
                'gridColumnStart' => $clamp(($item['gridColumnStart'] ?? 0) * ((int) ($item['gridVersion'] ?? 1) < 2 ? 2 : 1), 0, 24, 0),
                'gridRowStart' => $clamp($item['gridRowStart'] ?? 0, 0, 100, 0),
                'alignSelf' => $choice($item['alignSelf'] ?? 'stretch', ['auto', 'start', 'center', 'end', 'stretch'], 'stretch'),
                'justifySelf' => $choice($item['justifySelf'] ?? 'stretch', ['auto', 'start', 'center', 'end', 'stretch'], 'stretch'),
                'rotate' => $clamp($item['rotate'] ?? 0, -180, 180, 0),
                'skewX' => $clamp($item['skewX'] ?? 0, -45, 45, 0),
                'skewY' => $clamp($item['skewY'] ?? 0, -45, 45, 0),
                'transformOrigin' => $choice($item['transformOrigin'] ?? 'center', ['top left', 'top center', 'top right', 'center left', 'center', 'center right', 'bottom left', 'bottom center', 'bottom right'], 'center'),
                'radiusTopLeft' => $clamp($item['radiusTopLeft'] ?? ($item['radius'] ?? 10), 0, 200, 10),
                'radiusTopRight' => $clamp($item['radiusTopRight'] ?? ($item['radius'] ?? 10), 0, 200, 10),
                'radiusBottomRight' => $clamp($item['radiusBottomRight'] ?? ($item['radius'] ?? 10), 0, 200, 10),
                'radiusBottomLeft' => $clamp($item['radiusBottomLeft'] ?? ($item['radius'] ?? 10), 0, 200, 10),
                'backgroundType' => $choice($item['backgroundType'] ?? 'solid', ['solid', 'gradient'], 'solid'),
                'backgroundGradient' => $cssText($item['backgroundGradient'] ?? 'linear-gradient(135deg,#ffffff,#f3f7fb)', 'linear-gradient(135deg,#ffffff,#f3f7fb)'),
                'inputBackground' => $hex($item['inputBackground'] ?? '#ffffff', '#ffffff'),
                'inputBorderColor' => $hex($item['inputBorderColor'] ?? '#d5dce7', '#d5dce7'),
                'inputRadius' => $clamp($item['inputRadius'] ?? 8, 0, 60, 8),
                'inputPadding' => $clamp($item['inputPadding'] ?? 12, 0, 60, 12),
                'accentColor' => $hex($item['accentColor'] ?? '#2eaf7d', '#2eaf7d'),
                'customClass' => preg_replace('/[^A-Za-z0-9_-]/', '', substr((string) ($item['customClass'] ?? ''), 0, 60)),
            ];
            if ($type === 'file' && count($normalized) > 100) {
                throw new InvalidArgumentException('Le formulaire contient trop de champs fichier.');
            }
        }
        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function all(): array
    {
        return db()->query(
            "SELECT f.id, f.title, f.description, f.status, f.criteria_mode, f.layout_json, f.created_at,
                    f.project_id, p.title AS project_title
             FROM forms f LEFT JOIN projects p ON p.id = f.project_id
             WHERE f.status <> 'archived'
             ORDER BY f.created_at DESC, f.id DESC"
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM forms WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $form = $stmt->fetch();
        return is_array($form) ? $form : null;
    }

    public static function criteriaForForm(int $formId): array
    {
        $statement = db()->prepare(
            'SELECT c.*, fc.order_index AS form_order_index
             FROM form_criteria fc
             JOIN criteria c ON c.id = fc.criteria_id
             WHERE fc.form_id = :form_id
             ORDER BY fc.order_index ASC, c.id ASC'
        );
        $statement->execute(['form_id' => $formId]);
        $rows = $statement->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public static function saveCriteriaSelection(int $formId, array $criteriaIds, bool $configured = false): void
    {
        $form = self::find($formId);
        if (!$form) throw new RuntimeException('Formulaire introuvable.');

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $validIds = [];
            $projectId = (int) ($form['project_id'] ?? 0);
            if ($configured && $projectId > 0) {
                $candidateIds = array_values(array_unique(array_filter(array_map('intval', $criteriaIds), static fn (int $id): bool => $id > 0)));
                if ($candidateIds !== []) {
                    $placeholders = implode(',', array_fill(0, count($candidateIds), '?'));
                    $statement = $pdo->prepare('SELECT id FROM criteria WHERE project_id = ? AND id IN (' . $placeholders . ')');
                    $statement->execute(array_merge([$projectId], $candidateIds));
                    $validIds = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
                    $validIds = array_values(array_intersect($candidateIds, $validIds));
                }
            }
            $pdo->prepare('DELETE FROM form_criteria WHERE form_id = :form_id')->execute(['form_id' => $formId]);
            if ($configured && $validIds !== []) {
                $insert = $pdo->prepare('INSERT INTO form_criteria (form_id, criteria_id, order_index) VALUES (:form_id, :criteria_id, :order_index)');
                foreach ($validIds as $order => $criteriaId) {
                    $insert->execute(['form_id' => $formId, 'criteria_id' => $criteriaId, 'order_index' => $order + 1]);
                }
            }
            $pdo->prepare("UPDATE forms SET criteria_mode = :criteria_mode WHERE id = :form_id")
                ->execute(['criteria_mode' => $configured ? 'selected' : 'inherit', 'form_id' => $formId]);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $exception;
        }
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
            $existing = self::find($id);
            if (!$existing) {
                throw new RuntimeException('Formulaire introuvable.');
            }
            db()->prepare('UPDATE forms SET title=:title, description=:description, project_id=:project_id, layout_json=:layout_json, status=:status WHERE id=:id')->execute([
                'title' => $params['title'],
                'description' => $params['description'],
                'project_id' => $params['project_id'],
                'layout_json' => $params['layout_json'],
                'status' => $params['status'],
                'id' => $id,
            ]);
            return $id;
        }
        db()->prepare('INSERT INTO forms (title, description, project_id, layout_json, status, created_by) VALUES (:title, :description, :project_id, :layout_json, :status, :created_by)')->execute($params);
        return (int) db()->lastInsertId();
    }

    /**
     * Retire un formulaire sans casser ses réponses, évaluations ou liens publics.
     * Les données historiques restent consultables et le formulaire n'est plus actif.
     */
    public static function delete(int $id): void
    {
        $statement = db()->prepare("UPDATE forms SET status = 'archived' WHERE id = :id AND status <> 'archived'");
        $statement->execute(['id' => $id]);
        if ($statement->rowCount() !== 1) {
            throw new RuntimeException('Formulaire introuvable ou déjà retiré.');
        }
    }

    public static function schedules(): array
    {
        return db()->query('SELECT s.*, f.title AS form_title, f.status AS form_status, p.title AS project_title, p.organization FROM form_schedules s JOIN forms f ON f.id=s.form_id LEFT JOIN projects p ON p.id=s.project_id ORDER BY s.start_datetime')->fetchAll();
    }

    public static function saveSchedule(array $data): int
    {
        $stmt = db()->prepare('INSERT INTO form_schedules (form_id, project_id, access_type, start_datetime, end_datetime, allowed_emails, allowed_countries, is_active) VALUES (:form_id, :project_id, :access_type, :start_datetime, :end_datetime, :allowed_emails, :allowed_countries, :is_active)');
        $stmt->execute($data);
        return (int) db()->lastInsertId();
    }

    public static function scheduleFor(int $formId): ?array
    {
        $stmt = db()->prepare('SELECT s.*, f.title AS form_title, f.status AS form_status, p.title AS project_title, p.organization
            FROM form_schedules s JOIN forms f ON f.id = s.form_id LEFT JOIN projects p ON p.id = s.project_id
            WHERE s.form_id = :form_id ORDER BY s.id DESC LIMIT 1');
        $stmt->execute(['form_id' => $formId]);
        $schedule = $stmt->fetch();
        return is_array($schedule) ? $schedule : null;
    }

    public static function availability(int $formId, ?string $country = null, ?string $email = null, ?DateTimeImmutable $now = null): array
    {
        $form = self::find($formId);
        if (!$form || ($form['status'] ?? '') !== 'published') {
            return ['available' => false, 'message' => 'Ce formulaire n’est pas publié.'];
        }
        $schedule = self::scheduleFor($formId);
        if (!$schedule) {
            return ['available' => true, 'schedule' => null, 'message' => null];
        }
        if ((int) ($schedule['is_active'] ?? 0) !== 1) {
            return ['available' => false, 'schedule' => $schedule, 'message' => 'Ce formulaire est désactivé.'];
        }
        $now ??= new DateTimeImmutable('now');
        $start = self::scheduleDate($schedule['start_datetime'] ?? null, '00:00:00');
        $end = self::scheduleDate($schedule['end_datetime'] ?? null, '23:59:59');
        if ($start && $now < $start) {
            return ['available' => false, 'schedule' => $schedule, 'message' => 'Ce formulaire ouvrira le ' . $start->format('d/m/Y à H:i') . '.'];
        }
        if ($end && $now > $end) {
            return ['available' => false, 'schedule' => $schedule, 'message' => 'La période de candidature est clôturée depuis le ' . $end->format('d/m/Y à H:i') . '.'];
        }
        if (in_array(($schedule['access_type'] ?? 'public_link'), ['admin_only'], true)) {
            return ['available' => false, 'schedule' => $schedule, 'message' => 'Ce formulaire est réservé aux administrateurs.'];
        }
        if (($schedule['access_type'] ?? '') === 'email_list') {
            $allowedEmails = array_values(array_filter(array_map(static fn (string $value): string => strtolower(trim($value)), preg_split('/[,;\r\n]+/', (string) ($schedule['allowed_emails'] ?? '')) ?: [])));
            if ($email === null || !in_array(strtolower(trim($email)), $allowedEmails, true)) {
                return ['available' => false, 'schedule' => $schedule, 'message' => 'Votre adresse email ne figure pas dans la liste autorisée.'];
            }
        }
        $allowedCountries = self::countryList($schedule['allowed_countries'] ?? '');
        if ($country !== null && $allowedCountries !== [] && !in_array(strtoupper(trim($country)), $allowedCountries, true)) {
            return ['available' => false, 'schedule' => $schedule, 'message' => 'Votre pays n’est pas éligible pour ce formulaire.'];
        }
        return ['available' => true, 'schedule' => $schedule, 'message' => null];
    }

    public static function countryList(?string $value): array
    {
        $countries = preg_split('/[,;\r\n]+/', (string) $value) ?: [];
        $countries = array_map(static fn (string $country): string => strtoupper(trim($country)), $countries);
        $countries = array_values(array_unique(array_filter($countries)));
        sort($countries, SORT_NATURAL | SORT_FLAG_CASE);
        return $countries;
    }

    private static function scheduleDate(?string $value, string $defaultTime): ?DateTimeImmutable
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) $value .= ' ' . $defaultTime;
        try { return new DateTimeImmutable($value); } catch (Throwable $exception) { return null; }
    }
}
