<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lebonresto;charset=utf8mb4', 'root', '');

// Check all import tables
$tables = $pdo->query("SHOW TABLES LIKE 'restaurants_%'")->fetchAll(PDO::FETCH_COLUMN);
echo "=== Import tables ===\n";
foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo "$t: $count rows\n";
}

// Check existing cities distribution
echo "\n=== Current restaurants by city ===\n";
$cities = $pdo->query("SELECT ville, COUNT(*) as cnt FROM restaurants WHERE status='validated' GROUP BY ville ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cities as $c) {
    echo $c['ville'] . ': ' . $c['cnt'] . "\n";
}

// Check import table sample
echo "\n=== Import table columns ===\n";
$sample = $pdo->query("SELECT * FROM `restaurants_alger_2025_12_02_23_39_16` LIMIT 3 OFFSET 1")->fetchAll(PDO::FETCH_ASSOC);
print_r($sample[0]);

// Count distinct restaurants in import table (excluding header)
echo "\n=== Import distinct names ===\n";
$count = $pdo->query("SELECT COUNT(DISTINCT TRIM(`COL 1`)) FROM `restaurants_alger_2025_12_02_23_39_16` WHERE `COL 1` != 'Nom'")->fetchColumn();
echo "Distinct names in import: $count\n";
