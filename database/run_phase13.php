<?php
$pdo = new PDO('mysql:host=localhost;dbname=lebonresto;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$sql = file_get_contents(__DIR__ . '/phase13_features.sql');
// Remove SQL comment lines before splitting
$sql = preg_replace('/^--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));
$success = 0;
$errors = 0;
foreach ($statements as $stmt) {
    if (empty($stmt)) continue;
    try {
        $pdo->exec($stmt);
        $success++;
        echo "OK: " . substr(str_replace("\n", " ", $stmt), 0, 70) . "...\n";
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Duplicate column') !== false || strpos($msg, 'already exists') !== false || strpos($msg, 'Duplicate key name') !== false) {
            echo "SKIP: " . substr(str_replace("\n", " ", $stmt), 0, 60) . "...\n";
        } else {
            echo "ERROR: $msg\n";
            $errors++;
        }
    }
}
echo "\nDone: $success OK, $errors errors\n";
