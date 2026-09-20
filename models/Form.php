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
                'span' => max(1, min(12, (int) ($item['span'] ?? 6))),
                'rowSpan' => max(1, min(6, (int) ($item['rowSpan'] ?? 1))),
                'scale' => max(75, min(125, (int) ($item['scale'] ?? 100))),
                'background' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($item['background'] ?? '')) ? $item['background'] : '#ffffff',
                'color' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($item['color'] ?? '')) ? $item['color'] : '#1e293b',
                'radius' => max(0, min(24, (int) ($item['radius'] ?? 10))),
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
