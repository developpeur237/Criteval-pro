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

        $monthly = Submission::monthlyCounts(24);
        $monthlyByLabel = [];
        foreach ($monthly as $row) {
            $monthlyByLabel[(string) $row['label']] = (int) $row['total'];
        }
        $monthlyLabels = [];
        $monthlyTotals = [];
        for ($offset = 23; $offset >= 0; $offset--) {
            $date = new DateTimeImmutable('first day of this month');
            $date = $date->modify('-' . $offset . ' months');
            $label = $date->format('m/Y');
            $monthlyLabels[] = $label;
            $monthlyTotals[] = $monthlyByLabel[$label] ?? 0;
        }

        $chartRanges = [];
        foreach (['1m' => 31, '3m' => 90, '6m' => 180, '1a' => 365, '18m' => 540, '2a' => 730] as $range => $days) {
            $chartRanges[$range] = self::buildChartRange(Submission::chartCounts($days), $days);
        }
        $chartRanges['tout'] = self::buildAllTimeChartRange(Submission::chartCounts());

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
            'chart_ranges' => $chartRanges,
            'countries' => $countries,
            'scores' => $scores,
            'upcoming' => $upcoming,
            'latest' => Submission::latestForDashboard(5),
            'ranking' => array_slice(Ranking::all(), 0, 5),
        ];
    }

    private static function buildChartRange(array $rows, int $days): array
    {
        $byDate = [];
        foreach ($rows as $row) {
            $byDate[(string) $row['label']] = [
                'total' => (int) $row['total'],
                'evaluated' => (int) ($row['evaluated'] ?? 0),
            ];
        }

        $labels = [];
        $totals = [];
        $evaluated = [];
        $start = new DateTimeImmutable('today');
        for ($offset = $days - 1; $offset >= 0; $offset--) {
            $date = $start->modify('-' . $offset . ' days');
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d/m');
            $totals[] = (int) ($byDate[$key]['total'] ?? 0);
            $evaluated[] = (int) ($byDate[$key]['evaluated'] ?? 0);
        }

        $smoothedTotals = self::smoothSeries($totals, 5);
        $smoothedEvaluated = self::smoothSeries($evaluated, 5);

        return ['labels' => $labels, 'totals' => $smoothedTotals, 'evaluated' => $smoothedEvaluated];
    }

    private static function smoothSeries(array $values, int $window): array
    {
        if ($values === []) {
            return [];
        }

        $size = max(1, min($window, count($values)));
        $half = intdiv($size, 2);
        $smoothed = [];

        foreach ($values as $index => $value) {
            $start = max(0, $index - $half);
            $end = min(count($values) - 1, $index + $half);
            $slice = array_slice($values, $start, $end - $start + 1);
            $smoothed[] = (int) round(array_sum($slice) / count($slice));
        }

        return $smoothed;
    }

    private static function buildAllTimeChartRange(array $rows): array
    {
        return [
            'labels' => array_map(static fn (array $row): string => (string) $row['label'], $rows),
            'totals' => array_map(static fn (array $row): int => (int) $row['total'], $rows),
            'evaluated' => array_map(static fn (array $row): int => (int) ($row['evaluated'] ?? 0), $rows),
        ];
    }
}
