<?php

// ===== CONNEXION PDO (existante) =====
$dsn = 'mysql:host=localhost;dbname=lebonresto;charset=utf8mb4';  // ← AJOUT DE charset=utf8mb4
$user = 'sam';
$password = '123';

try {
    $dbh = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"  // ← AJOUT
    ]);
        $dbh->exec("SET CHARACTER SET utf8mb4");

} catch (PDOException $e) {
    echo 'Échec lors de la connexion : ' . $e->getMessage();
}

// ===== CONNEXION MySQLi (pour le panel admin) =====
$conn = new mysqli('localhost', 'sam', '123', 'lebonresto');

if ($conn->connect_error) {
    die("Erreur de connexion MySQLi : " . $conn->connect_error);
}

// Définir l'encodage UTF-8 (une seule fois suffit)
$conn->set_charset("utf8mb4");

?>