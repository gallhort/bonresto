<?php
/**
 * searchCity.php
 * Recherche les villes dans la table algeria_cities
 */

header('Content-Type: application/json; charset=utf-8');

$servername = 'localhost';
$username = 'sam';
$password = '123';
$db = 'lebonresto';

$conn = new mysqli($servername, $username, $password, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion']);
    exit;
}

$conn->set_charset("utf8");

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$query = mysqli_real_escape_string($conn, $query);

// CORRECTION : Utiliser algeria_cities au lieu de city
$sql = "SELECT 
            commune_name_ascii, 
            daira_name_ascii, 
            wilaya_name_ascii,
            gps
        FROM algeria_cities 
        WHERE commune_name_ascii LIKE '%{$query}%' 
           OR daira_name_ascii LIKE '%{$query}%'
           OR wilaya_name_ascii LIKE '%{$query}%'
        ORDER BY commune_name_ascii ASC
        LIMIT 10";

$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur SQL',
        'message' => mysqli_error($conn),
        'sql' => $sql
    ]);
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