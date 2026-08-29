<?php
$sql = file_get_contents(__DIR__ . '/../storage/init_db.sql');
$sql = preg_replace('/(?:--|#).*$/m', '', $sql);
$stmts = preg_split('/;\s*(?=(\r?\n|$))/', $sql);
echo "Total statements: " . count($stmts) . "\n";
foreach ($stmts as $i => $st) {
    $s = trim($st);
    if ($s === '') {
        continue;
    }
    echo "--- Statement " . ($i + 1) . " (len=" . strlen($s) . ")\n";
    echo substr($s, 0, 300) . "\n";
}
