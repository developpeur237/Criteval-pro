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

    public static function availability(int $formId, ?string $country = null, ?DateTimeImmutable $now = null): array
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
