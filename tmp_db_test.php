<?php
try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/storage/criteval_pro.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "connected\n";
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' ORDER BY name");
    foreach ($stmt as $row) {
        echo $row[0] . "\n";
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
