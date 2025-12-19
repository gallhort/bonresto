<?php

// ===== CONNEXION PDO (existante) =====
// Utiliser les variables d'environnement si disponibles, sinon basculer sur des valeurs de secours
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'lebonresto';
$dbUser = getenv('DB_USER') ?: 'sam';
$dbPass = getenv('DB_PASS') ?: '123';
$dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

try {
    $dbh = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbCharset} COLLATE {$dbCharset}_unicode_ci"
    ]);
    $dbh->exec("SET CHARACTER SET {$dbCharset}");

} catch (PDOException $e) {
    // Message d'erreur minimal pour ne pas divulguer d'infos sensibles
    error_log('DB PDO connection failed: ' . $e->getMessage());
    echo 'Échec lors de la connexion BDD.';
}

// ===== CONNEXION MySQLi (pour le panel admin) =====
$mysqliHost = $dbHost;
$mysqliUser = $dbUser;
$mysqliPass = $dbPass;
$mysqliDb   = $dbName;

$conn = new mysqli($mysqliHost, $mysqliUser, $mysqliPass, $mysqliDb);

if ($conn->connect_error) {
    die("Erreur de connexion MySQLi : " . $conn->connect_error);
}

// Définir l'encodage UTF-8 (une seule fois suffit)
$conn->set_charset($dbCharset);

?>