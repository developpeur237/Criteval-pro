<?php
require 'C:/laragon/www/criteval-pro/config/config.php';
require 'C:/laragon/www/criteval-pro/config/database.php';
$pdo = db();
$sql = "WITH event_stream AS (
    SELECT submitted_at AS event_date, CASE WHEN status IN ('evaluated', 'published') THEN 1 ELSE 0 END AS is_evaluated
    FROM submissions
    UNION ALL
    SELECT start_datetime AS event_date, 0 AS is_evaluated
    FROM form_schedules
    WHERE start_datetime IS NOT NULL
    UNION ALL
    SELECT end_datetime AS event_date, 0 AS is_evaluated
    FROM form_schedules
    WHERE end_datetime IS NOT NULL
    UNION ALL
    SELECT session_date AS event_date, 0 AS is_evaluated
    FROM training_sessions
    WHERE session_date IS NOT NULL
    UNION ALL
    SELECT published_at AS event_date, 1 AS is_evaluated
    FROM results
    WHERE published_at IS NOT NULL
)
SELECT date(event_date) AS label, COUNT(*) AS total, SUM(is_evaluated) AS evaluated
FROM event_stream
WHERE event_date >= datetime('now', '-730 days')
GROUP BY date(event_date)
ORDER BY label ASC
LIMIT 12";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "event_rows=" . count($rows) . PHP_EOL;
foreach ($rows as $row) {
    echo $row['label'] . ':' . $row['total'] . ':' . ($row['evaluated'] ?? 0) . PHP_EOL;
}
