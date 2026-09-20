<?php
declare(strict_types=1);

class Notification
{
    public static function current(array $settings): array
    {
        $notifications = [];
        $now = new DateTimeImmutable('now');
        $leadMinutes = max(5, min(10080, (int) ($settings['notifications']['lead_minutes'] ?? 1440)));
        $leadSeconds = $leadMinutes * 60;
        $recentSeconds = 7 * 86400;

        if (!empty($settings['notifications']['schedule_alerts'])) {
            $schedules = db()->query(
                'SELECT s.id, s.start_datetime, s.end_datetime, s.is_active,
                        f.title AS form_title, p.title AS project_title
                 FROM form_schedules s
                 JOIN forms f ON f.id = s.form_id
                 LEFT JOIN projects p ON p.id = s.project_id
                 ORDER BY COALESCE(s.start_datetime, s.end_datetime) ASC'
            )->fetchAll();

            foreach ($schedules as $schedule) {
                $formTitle = (string) ($schedule['form_title'] ?: 'Formulaire');
                $projectTitle = (string) ($schedule['project_title'] ?: 'Organisation');
                $prefix = $formTitle . ' - ' . $projectTitle;
                $start = self::dateValue($schedule['start_datetime'] ?? null);
                $end = self::dateValue($schedule['end_datetime'] ?? null);

                if ($start !== null) {
                    $seconds = $start->getTimestamp() - $now->getTimestamp();
                    if ($seconds >= -$recentSeconds && $seconds <= $leadSeconds) {
                        $notifications[] = self::item(
                            'schedule-start-' . (int) $schedule['id'],
                            'schedule',
                            $seconds < 0 ? 'info' : ($seconds <= 3600 ? 'urgent' : 'warning'),
                            $seconds < 0 ? 'Formulaire ouvert maintenant' : 'Ouverture du formulaire imminente',
                            $seconds < 0 ? $prefix . ' est ouverte depuis ' . self::elapsedTime(abs($seconds)) . '.' : $prefix . ' ouvre ' . self::relativeTime($seconds) . '.',
                            $start,
                            'calendrier',
                            $seconds < 0 ? 'delivered' : 'pending'
                        );
                    }
                }

                if ($end !== null) {
                    $seconds = $end->getTimestamp() - $now->getTimestamp();
                    if ($seconds >= 0 && $seconds <= $leadSeconds) {
                        $notifications[] = self::item(
                            'schedule-end-' . (int) $schedule['id'],
                            'schedule',
                            $seconds <= 3600 ? 'urgent' : 'warning',
                            'Échéance du formulaire imminente',
                            $prefix . ' arrive à échéance ' . self::relativeTime($seconds) . '.',
                            $end,
                            'calendrier',
                            'pending'
                        );
                    } elseif ($seconds < 0 && $seconds >= -86400 && (int) $schedule['is_active'] === 1) {
                        $notifications[] = self::item(
                            'schedule-overdue-' . (int) $schedule['id'],
                            'schedule',
                            'danger',
                            'Échéance dépassée : action requise',
                            $prefix . ' a dépassé son échéance.',
                            $end,
                            'calendrier',
                            'delivered'
                        );
                    }
                }
            }
        }

        $trainingLeadDays = max(1, min(30, (int) ($settings['notifications']['training_lead_days'] ?? 7)));
        $sessions = db()->query(
            'SELECT t.id, t.name, t.session_date, p.title AS project_title
             FROM training_sessions t LEFT JOIN projects p ON p.id = t.project_id
             WHERE t.is_active = 1 ORDER BY t.session_date ASC'
        )->fetchAll();
        foreach ($sessions as $session) {
            $date = self::dateValue($session['session_date'] ?? null, '00:00:00');
            if ($date === null) {
                continue;
            }
            $seconds = $date->getTimestamp() - $now->getTimestamp();
            if ($seconds >= -$recentSeconds && $seconds <= $trainingLeadDays * 86400) {
                $notifications[] = self::item(
                    'training-' . (int) $session['id'],
                    'training',
                    $seconds <= 86400 ? 'urgent' : 'info',
                    $seconds < 0 ? 'Formation en cours' : 'Formation à venir',
                    $seconds < 0 ? (string) $session['name'] . ' a commencé il y a ' . self::elapsedTime(abs($seconds)) . '.' : (string) $session['name'] . ' est prévue ' . self::relativeTime($seconds) . '.',
                    $date,
                    'formation',
                    $seconds < 0 ? 'delivered' : 'pending'
                );
            }
        }

        foreach ((array) ($settings['notifications']['custom'] ?? []) as $custom) {
            if (!is_array($custom) || trim((string) ($custom['title'] ?? '')) === '' || trim((string) ($custom['message'] ?? '')) === '') {
                continue;
            }
            $dueAt = self::dateValue($custom['due_at'] ?? null);
            if ($dueAt === null) {
                continue;
            }
            $seconds = $dueAt->getTimestamp() - $now->getTimestamp();
            if ($seconds >= -$recentSeconds && $seconds <= $leadSeconds) {
                $notifications[] = self::item(
                    'custom-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($custom['id'] ?? sha1((string) $custom['title']))),
                    'custom',
                    in_array(($custom['severity'] ?? ''), ['urgent', 'danger', 'warning'], true) ? $custom['severity'] : 'info',
                    (string) $custom['title'],
                    (string) $custom['message'],
                    $dueAt,
                    'apercu',
                    $seconds < 0 ? 'delivered' : 'pending'
                );
            }
        }

        usort($notifications, static function (array $first, array $second): int {
            return strcmp((string) $first['due_at'], (string) $second['due_at']);
        });
        return array_slice($notifications, 0, 30);
    }

    private static function dateValue(?string $value, string $defaultTime = '00:00:00'): ?DateTimeImmutable
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
                $value .= ' ' . $defaultTime;
            }
            return new DateTimeImmutable($value);
        } catch (Throwable $exception) {
            return null;
        }
    }

    private static function item(string $id, string $type, string $severity, string $title, string $message, DateTimeImmutable $dueAt, string $module, string $state = 'pending'): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'due_at' => $dueAt->format(DateTimeInterface::ATOM),
            'display_date' => $dueAt->format('d/m/Y H:i'),
            'module' => $module,
            'state' => $state,
            'available' => true,
        ];
    }

    private static function relativeTime(int $seconds): string
    {
        if ($seconds < 3600) {
            $minutes = max(1, (int) ceil($seconds / 60));
            return 'dans ' . $minutes . ' min';
        }
        $hours = (int) ceil($seconds / 3600);
        if ($hours < 24) {
            return 'dans ' . $hours . ' h';
        }
        return 'dans ' . (int) ceil($seconds / 86400) . ' j';
    }

    private static function elapsedTime(int $seconds): string
    {
        if ($seconds < 3600) {
            return max(1, (int) floor($seconds / 60)) . ' min';
        }
        return ($seconds < 86400 ? (int) floor($seconds / 3600) . ' h' : (int) floor($seconds / 86400) . ' j');
    }
}
