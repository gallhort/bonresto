<?php
require __DIR__ . '/../classes/DatabasePDO.php';

// Test with SQLite in-memory
try {
    $db = new DatabasePDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo = $db->getPdo();

    $pdo->exec("CREATE TABLE vendor (id INTEGER PRIMARY KEY, nom TEXT, gps TEXT);");
    $stmt = $pdo->prepare("INSERT INTO vendor (nom, gps) VALUES (:n, :g)");
    $stmt->execute([':n' => 'TestResto', ':g' => '12.34,56.78']);

    $rows = $db->fetchAll("SELECT * FROM vendor WHERE nom = ?", ['TestResto']);

    if (count($rows) === 1 && $rows[0]['gps'] === '12.34,56.78') {
        echo "OK: DatabasePDO basic test passed\n";
        exit(0);
    } else {
        echo "FAIL: Unexpected query result:\n";
        print_r($rows);
        exit(2);
    }
} catch (Exception $e) {
    echo "FAIL: Exception: " . $e->getMessage() . "\n";
    exit(1);
}
