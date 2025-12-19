<?php

// ===== CONNEXION PDO (existante) =====
// Utiliser les variables d'environnement si disponibles, sinon basculer sur des valeurs de secours
// Chargement des paramètres depuis les variables d'environnement si disponibles
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'lebonresto';
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$dbCharset = getenv('DB_CHARSET') ?: 'utf8mb4';

// Simple loader pour un fichier .env local (format KEY=VALUE) si présent
$envFiles = [__DIR__ . '/.env', __DIR__ . '/../.env'];
foreach ($envFiles as $envFile) {
    if ((getenv('DB_USER') === false || getenv('DB_PASS') === false) && file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') === false) continue;
            list($k, $v) = array_map('trim', explode('=', $line, 2));
            $v = trim($v, "\"' ");
            if ($k === '') continue;
            if (getenv($k) === false) putenv("{$k}={$v}");
            // remplir les variables locales si elles sont vides
            if (($k === 'DB_HOST') && empty($dbHost)) $dbHost = $v;
            if (($k === 'DB_NAME') && (empty($dbName) || $dbName === '')) $dbName = $v;
            if (($k === 'DB_USER') && (empty($dbUser) || $dbUser === false)) $dbUser = $v;
            if (($k === 'DB_PASS') && (empty($dbPass) || $dbPass === false)) $dbPass = $v;
            if (($k === 'DB_CHARSET') && empty($dbCharset)) $dbCharset = $v;
        }
        break;
    }
}

// Si toujours absents, tenter un fallback de développement couramment utilisé (XAMPP: root avec mot de passe vide)
if (empty($dbUser) || $dbUser === false) {
    $candidates = [ ['root', ''] ];
    foreach ($candidates as $cand) {
        $tryUser = $cand[0]; $tryPass = $cand[1];
        $tmp = @new mysqli($dbHost, $tryUser, $tryPass, $dbName);
        if ($tmp && !$tmp->connect_error) {
            $dbUser = $tryUser;
            $dbPass = $tryPass;
            $tmp->close();
            break;
        }
    }
}

// A ce stade si on n'a toujours pas d'utilisateur, afficher un message clair et arrêter l'exécution
if (empty($dbUser) || $dbUser === false) {
    error_log('DB credentials not set. Please create a .env with DB_USER and DB_PASS or set environment variables.');
    echo 'Erreur interne : configuration BDD non définie. Veuillez créer un fichier .env (DB_HOST, DB_NAME, DB_USER, DB_PASS) ou définir les variables d\'environnement.';
    return;
}

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