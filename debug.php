<?php
header('Content-Type: application/json; charset=utf-8');

include_once __DIR__ . '/connect.php';
// debug uses $conn from connect.php

// $conn provided by connect.php
if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion BDD']);
    exit;
}
$conn->set_charset("utf8mb4");

// Afficher tous les restaurants avec leurs coordonnées GPS
$query = "SELECT Nom, gps, note, Type FROM vendeur LIMIT 20";
$result = $conn->query($query);

$restaurants = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $gps = $row['gps'];
        $parts = explode(',', $gps);
        
        $restaurants[] = [
            'nom' => $row['Nom'],
            'gps_original' => $gps,
            'gps_parsed' => [
                'lat' => isset($parts[0]) ? trim($parts[0]) : 'ERREUR',
                'lon' => isset($parts[1]) ? trim($parts[1]) : 'ERREUR'
            ],
            'note' => $row['note'],
            'type' => $row['Type']
        ];
    }
}

// Tester la distance pour un restaurant
$lat_user = 48.8566;
$lon_user = 2.3522;

if (!empty($restaurants)) {
    $first = $restaurants[0];
    $gps_parts = explode(',', $first['gps_original']);
    $lat_resto = trim($gps_parts[0]);
    $lon_resto = trim($gps_parts[1]);
    
    // Test formule distance
    $testQuery = "SELECT 
        ( 6371 * acos( cos( radians($lat_user) ) * cos( radians( $lat_resto ) ) * cos( radians( $lon_resto ) - radians($lon_user) ) + sin( radians($lat_user) ) * sin( radians( $lat_resto ) ) ) ) AS distance";
    
    $testResult = $conn->query($testQuery);
    $distanceTest = null;
    if ($testResult) {
        $row = $testResult->fetch_assoc();
        $distanceTest = $row['distance'];
    }
}

echo json_encode([
    'total_restaurants' => count($restaurants),
    'restaurants' => $restaurants,
    'test_distance' => [
        'user_gps' => "$lat_user,$lon_user",
        'first_resto_gps' => isset($first['gps_original']) ? $first['gps_original'] : null,
        'distance_calculee' => $distanceTest,
        'rayon_test' => 30
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conn->close();
?>