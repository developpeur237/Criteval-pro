<?php
require 'C:/laragon/www/criteval-pro/config/config.php';
require 'C:/laragon/www/criteval-pro/config/database.php';
$pdo = db();
$min = $pdo->query("SELECT MIN(submitted_at) FROM submissions")->fetchColumn();
$max = $pdo->query("SELECT MAX(submitted_at) FROM submissions")->fetchColumn();
$count = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
$rows = $pdo->query("SELECT date(submitted_at) AS d, COUNT(*) AS c FROM submissions WHERE submitted_at >= datetime('now','-730 days') GROUP BY date(submitted_at) ORDER BY d ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
echo "count={$count}\nmin={$min}\nmax={$max}\n";
foreach ($rows as $r) { echo $r['d'] . ':' . $r['c'] . "\n"; }
