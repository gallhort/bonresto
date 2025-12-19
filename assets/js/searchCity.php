<?php
/* ----------  conf ---------- */
$dsn  = 'mysql:host=localhost;dbname=lebonresto;charset=utf8mb4';
$user = 'sam';
$pass = '123';

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    http_response_code(500);
    exit('DB error');
}

/* ----------  récupère le terme ---------- */
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

/* ----------  recherche ---------- */
$stmt = $pdo->prepare(
    "SELECT id,
            commune_name_ascii,
            daira_name_ascii,
            wilaya_name_ascii,
            gps
     FROM   algeria_cities
     WHERE  commune_name_ascii LIKE :kw
        OR  daira_name_ascii   LIKE :kw
        OR  wilaya_name_ascii  LIKE :kw
     LIMIT  20");
$stmt->execute([':kw' => '%'.$q.'%']);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ----------  sortie JSON ---------- */
header('Content-Type: application/json');
echo json_encode($data);
?>