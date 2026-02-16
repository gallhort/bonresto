<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lebonresto;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== STRUCTURE TABLE REVIEWS ===\n";
$cols = $pdo->query("DESCRIBE reviews")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  {$c['Field']} ({$c['Type']}) " . ($c['Null'] === 'YES' ? 'NULL' : 'NOT NULL')
         . ($c['Key'] ? " [{$c['Key']}]" : '') . " Default: {$c['Default']}\n";
}

echo "\n=== INDEXES ===\n";
$idx = $pdo->query("SHOW INDEX FROM reviews")->fetchAll(PDO::FETCH_ASSOC);
foreach ($idx as $i) {
    echo "  {$i['Key_name']}: {$i['Column_name']}\n";
}

echo "\n=== SAMPLE REVIEW ===\n";
$sample = $pdo->query("SELECT * FROM reviews LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($sample) print_r($sample);
else echo "  (aucun avis)\n";

echo "\n=== USERS TABLE - check for google user ===\n";
$cols2 = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $c) {
    echo "  {$c['Field']} ({$c['Type']})\n";
}
