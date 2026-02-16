<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lebonresto;charset=utf8mb4', 'root', '');
$cols = $pdo->query('DESCRIBE restaurants')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) {
    echo $c['Field'] . ' | ' . $c['Type'] . ' | ' . $c['Null'] . ' | ' . $c['Default'] . PHP_EOL;
}
echo "\n--- Count ---\n";
echo 'Total: ' . $pdo->query('SELECT COUNT(*) FROM restaurants')->fetchColumn() . PHP_EOL;
echo "\n--- Sample ---\n";
$sample = $pdo->query('SELECT * FROM restaurants LIMIT 1')->fetch(PDO::FETCH_ASSOC);
print_r($sample);
