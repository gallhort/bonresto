<?php
/**
 * searchCity.php
 * Recherche les villes dans la table algeria_cities
 */

header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/connect.php';
// $conn is provided by connect.php

// $conn provided by connect.php
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion']);
    exit;
}

$conn->set_charset("utf8mb4");

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Utiliser une requête préparée pour le LIKE
$sql = "SELECT commune_name_ascii, daira_name_ascii, wilaya_name_ascii, gps
        FROM algeria_cities
        WHERE commune_name_ascii LIKE ?
           OR daira_name_ascii LIKE ?
           OR wilaya_name_ascii LIKE ?
        ORDER BY commune_name_ascii ASC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$param = "%" . $query . "%";
$stmt->bind_param('sss', $param, $param, $param);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur SQL']);
    mysqli_close($conn);
    exit;
}

$cities = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Parser le champ GPS "latitude,longitude"
    $gpsArray = explode(',', $row['gps']);
    
    $cities[] = [
        'commune_name_ascii' => $row['commune_name_ascii'],
        'daira_name_ascii' => $row['daira_name_ascii'],
        'wilaya_name_ascii' => $row['wilaya_name_ascii'],
        'gps' => $row['gps'],
        'latitude' => isset($gpsArray[0]) ? trim($gpsArray[0]) : '',
        'longitude' => isset($gpsArray[1]) ? trim($gpsArray[1]) : ''
    ];
}

mysqli_close($conn);

echo json_encode($cities, JSON_UNESCAPED_UNICODE);
?>