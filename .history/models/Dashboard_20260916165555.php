<?php
declare(strict_types=1);

class Dashboard
{
    public static function overview(): array
    {
        $pdo = db();
        $activeProjects = (int) $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'active'")->fetchColumn();
        $submissions = (int) $pdo->query('SELECT COUNT(*) FROM submissions')->fetchColumn();
        $evaluated = (int) $pdo->query("SELECT COUNT(*) FROM submissions WHERE status IN ('evaluated', 'published')")->fetchColumn();
        $pending = (int) $pdo->query("SELECT COUNT(*) FROM submissions WHERE status IN ('pending', 'under_review')")->fetchColumn();
        $weightedAverage = (float) $pdo->query('SELECT COALESCE(AVG(weighted_score), 0) FROM results')->fetchColumn();

        $monthly = Submission::monthlyCounts(6);
        $monthlyByLabel = [];
        foreach ($monthly as $row) {
            $monthlyByLabel[(string) $row['label']] = (int) $row['total'];
        }
        $monthlyLabels = [];
        $monthlyTotals = [];
        for ($offset = 5; $offset >= 0; $offset--) {
            $date = new DateTimeImmutable('first day of this month');
            $date = $date->modify('-' . $offset . ' months');
            $label = $date->format('m/Y');
            $monthlyLabels[] = $label;
            $monthlyTotals[] = $monthlyByLabel[$label] ?? 0;
        }

        $countryRows = Submission::countryCounts(5);
        $countries = [];
        foreach ($countryRows as $row) {
            $countries[] = ['label' => (string) $row['country_code'], 'total' => (int) $row['total']];
        }

        $scoreRows = $pdo->query(
            'SELECT COALESCE(p.organization, p.title) AS label, ROUND(AVG(r.weighted_score), 1) AS score
             FROM results r JOIN submissions s ON s.id = r.submission_id
             JOIN forms f ON f.id = s.form_id JOIN projects p ON p.id = f.project_id
             GROUP BY p.id ORDER BY score DESC, label ASC LIMIT 6'
        )->fetchAll();
        $scores = array_map(static fn (array $row): array => ['label' => (string) $row['label'], 'score' => (float) $row['score']], $scoreRows);

        $upcomingRows = $pdo->query(
            "SELECT fs.start_datetime, fs.end_datetime, f.title AS form_title, COALESCE(p.organization, p.title) AS organization
             FROM form_schedules fs JOIN forms f ON f.id = fs.form_id LEFT JOIN projects p ON p.id = fs.project_id
             WHERE fs.is_active = 1 AND (fs.end_datetime IS NULL OR fs.end_datetime >= datetime('now'))
             ORDER BY COALESCE(fs.start_datetime, fs.end_datetime) ASC LIMIT 5"
        )->fetchAll();
        $upcoming = array_map(static function (array $row): array {
            return [
                'title' => (string) $row['form_title'],
                'organization' => (string) ($row['organization'] ?? 'Organisation'),
                'date' => $row['end_datetime'] ?: $row['start_datetime'],
            ];
        }, $upcomingRows);

        return [
            'kpis' => [
                'active_projects' => $activeProjects,
                'submissions' => $submissions,
                'evaluated' => $evaluated,
                'pending' => $pending,
                'weighted_average' => round($weightedAverage, 1),
            ],
            'monthly' => ['labels' => $monthlyLabels, 'totals' => $monthlyTotals],
            'countries' => $countries,
            'scores' => $scores,
            'upcoming' => $upcoming,
            'latest' => Submission::latestForDashboard(5),
            'ranking' => array_slice(Ranking::all(), 0, 5),
        ];
    }
}
