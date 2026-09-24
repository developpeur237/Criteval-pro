<?php
declare(strict_types=1);

class TrainingSession
{
    public static function all(bool $activeOnly = false): array
    {
        $where = $activeOnly ? ' WHERE is_active = 1' : '';
        return db()->query(
            'SELECT ts.*, p.organization, p.title AS project_title
             FROM training_sessions ts LEFT JOIN projects p ON p.id = ts.project_id' . $where .
            ' ORDER BY ts.session_date ASC, ts.id ASC'
        )->fetchAll();
    }

    public static function calendarEvents(): array
    {
        $sessions = self::all(true);
        $events = [];
        foreach ($sessions as $session) {
            $start = trim((string) ($session['session_date'] ?? ''));
            $end = trim((string) ($session['end_date'] ?? $start));
            if ($start === '') continue;
            $rangeEnd = $end !== '' ? $end : $start;
            try {
                $endDate = new DateTimeImmutable($rangeEnd);
                $endDate = $endDate->modify('+1 day');
            } catch (Throwable $exception) {
                continue;
            }
            $events[] = [
                'type' => 'training',
                'title' => trim((string) ($session['name'] ?? 'Session de formation')),
                'start' => $start,
                'end' => $endDate->format('Y-m-d'),
                'allDay' => true,
                'color' => '#3498db',
            ];
        }
        return $events;
    }

    public static function create(array $data): int
    {
        $sessionDate = trim((string) ($data['session_date'] ?? ''));
        $endDate = trim((string) ($data['end_date'] ?? ''));
        if ($endDate === '') {
            $endDate = $sessionDate;
        }
        $stmt = db()->prepare(
            'INSERT INTO training_sessions (name, objective, session_date, end_date, project_id, capacity, format, evaluation_weight, criteria_json, public_slug, is_active, created_by)
             VALUES (:name, :objective, :session_date, :end_date, :project_id, :capacity, :format, :evaluation_weight, :criteria_json, :public_slug, :is_active, :created_by)'
        );
        $stmt->execute([
            'name' => $data['name'],
            'objective' => $data['objective'],
            'session_date' => $sessionDate,
            'end_date' => $endDate,
            'project_id' => $data['project_id'] ?? null,
            'capacity' => max(1, (int) ($data['capacity'] ?? 50)),
            'format' => $data['format'] ?? 'hybride',
            'evaluation_weight' => max(0, (float) ($data['evaluation_weight'] ?? 0)),
            'criteria_json' => json_encode(array_values(array_unique(array_filter(array_map('intval', (array) ($data['criteria_ids'] ?? [])), static fn (int $id): bool => $id > 0))), JSON_THROW_ON_ERROR),
            'public_slug' => $data['public_slug'],
            'is_active' => $data['is_active'] ?? 1,
            'created_by' => $data['created_by'] ?? null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function criteria(int $sessionId): array
    {
        $sessionStatement = db()->prepare('SELECT project_id, criteria_json FROM training_sessions WHERE id = :id LIMIT 1');
        $sessionStatement->execute(['id' => $sessionId]);
        $session = $sessionStatement->fetch();
        if (!is_array($session)) return [];

        $selected = json_decode((string) ($session['criteria_json'] ?? ''), true);
        $ids = is_array($selected) ? array_values(array_unique(array_filter(array_map('intval', $selected), static fn (int $id): bool => $id > 0))) : [];
        if ($ids === []) {
            $statement = db()->prepare('SELECT c.* FROM criteria c WHERE c.project_id = :project_id ORDER BY c.order_index ASC, c.id ASC');
            $statement->execute(['project_id' => (int) ($session['project_id'] ?? 0)]);
            return $statement->fetchAll() ?: [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $statement = db()->prepare('SELECT c.* FROM criteria c WHERE c.project_id = ? AND c.id IN (' . $placeholders . ') ORDER BY c.order_index ASC, c.id ASC');
        $statement->execute(array_merge([(int) ($session['project_id'] ?? 0)], $ids));
        return $statement->fetchAll() ?: [];
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = db()->prepare('SELECT * FROM training_sessions WHERE public_slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public static function dashboardData(int $sessionId): array
    {
        $sessionStmt = db()->prepare(
            'SELECT ts.*, p.organization, p.title AS project_title
             FROM training_sessions ts LEFT JOIN projects p ON p.id = ts.project_id WHERE ts.id = :id'
        );
        $sessionStmt->execute(['id' => $sessionId]);
        $session = $sessionStmt->fetch() ?: null;
        if (!$session) {
            return ['session' => null, 'participants' => [], 'stats' => []];
        }

        $participantStmt = db()->prepare(
            'SELECT s.id AS submission_id, s.candidate_name, s.candidate_email, s.country_code, s.status,
                    COALESCE(ta.attendance_status, "registered") AS attendance_status,
                    ta.grade, ta.comment, COALESCE(ta.included_in_evaluation, 0) AS included_in_evaluation
             FROM submissions s
             JOIN forms f ON f.id = s.form_id AND f.project_id = :project_id
             LEFT JOIN training_attendance ta ON ta.submission_id = s.id AND ta.session_id = :session_id
             ORDER BY s.submitted_at DESC, s.id DESC'
        );
        $participantStmt->execute(['project_id' => (int) $session['project_id'], 'session_id' => $sessionId]);
        $participants = $participantStmt->fetchAll();
        $registered = count($participants);
        $present = count(array_filter($participants, static fn (array $row): bool => $row['attendance_status'] === 'present'));
        $graded = array_values(array_filter(array_column($participants, 'grade'), static fn ($grade): bool => $grade !== null && $grade !== ''));
        $included = count(array_filter($participants, static fn (array $row): bool => (int) $row['included_in_evaluation'] === 1));
        return [
            'session' => $session,
            'participants' => $participants,
            'stats' => [
                'registered' => $registered,
                'present' => $present,
                'attendance_rate' => $registered > 0 ? round(($present / $registered) * 100) : 0,
                'graded' => count($graded),
                'average' => $graded ? round(array_sum(array_map('floatval', $graded)) / count($graded), 1) : 0,
                'included' => $included,
            ],
        ];
    }

    public static function saveParticipant(int $sessionId, int $submissionId, array $data): void
    {
        db()->prepare(
            'INSERT INTO training_attendance (session_id, submission_id, attendance_status, grade, comment, included_in_evaluation, updated_at)
             VALUES (:session_id, :submission_id, :attendance_status, :grade, :comment, :included, CURRENT_TIMESTAMP)
             ON CONFLICT(session_id, submission_id) DO UPDATE SET attendance_status = excluded.attendance_status,
                grade = excluded.grade, comment = excluded.comment, included_in_evaluation = excluded.included_in_evaluation,
                updated_at = CURRENT_TIMESTAMP'
        )->execute([
            'session_id' => $sessionId,
            'submission_id' => $submissionId,
            'attendance_status' => in_array($data['attendance_status'] ?? '', ['registered', 'present', 'absent', 'certified'], true) ? $data['attendance_status'] : 'registered',
            'grade' => $data['grade'] === '' || $data['grade'] === null ? null : max(0, min(20, (float) $data['grade'])),
            'comment' => $data['comment'] ?? null,
            'included' => !empty($data['included']) ? 1 : 0,
        ]);
    }
}
