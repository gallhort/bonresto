<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Use central connection
include_once __DIR__ . '/connect.php';
// $conn and $dbh are available

// Récupérer les paramètres
$action = $_GET['action'] ?? 'search';
$addr = $_GET['adresse'] ?? '';
$type = $_GET['foodType'] ?? 'Tous';
$radius = (int)($_GET['searchRadius'] ?? 10);
$currentgps = $_GET['currentgps'] ?? '';
$tri = (int)($_GET['tri'] ?? 1);
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 12);
$offset = ($page - 1) * $limit;

// Parser les coordonnées GPS
$gpsArray = explode(',', $currentgps);
if (count($gpsArray) != 2 || empty(trim($gpsArray[0])) || empty(trim($gpsArray[1]))) {
    echo json_encode(['error' => 'Coordonnées GPS invalides']);
    exit;
}

$geoc = [
    'lat' => trim($gpsArray[0]),
    'lon' => trim($gpsArray[1])
];

// Initialize PDO wrapper and validate DB connection
require_once __DIR__ . '/classes/DatabasePDO.php';
try {
    $dbw = new DatabasePDO();
} catch (Exception $e) {
    error_log('api_restaurants: DB init failed: ' . $e->getMessage());
    echo json_encode(['error' => 'Erreur interne BDD']);
    exit;
}

// FORMULE DE DISTANCE (Haversine corrigée - retourne les km)
$lat1 = (float)$geoc['lat'];
$lon1 = (float)$geoc['lon'];
// Clamp radius to a reasonable maximum (e.g., 200 km) and ensure numeric
$radius = max(0, min(200, (float)$radius));
$distanceFormula = "( 6371 * acos( cos( radians(" . $lat1 . ") ) * cos( radians( SUBSTRING_INDEX(gps, ',', 1) ) ) * cos( radians( SUBSTRING_INDEX(gps, ',', -1) ) - radians(" . $lon1 . ") ) + sin( radians(" . $lat1 . ") ) * sin( radians( SUBSTRING_INDEX(gps, ',', 1) ) ) ) )";

// ============ FONCTIONS ============

function getRestaurants($dbw, $distanceFormula, $type, $radius, $tri, $offset, $limit) {
    $query = "SELECT 
        {$distanceFormula} AS distance,
        v.gps, v.note, v.Nom, v.Type, v.adresse, v.codePostal, 
        v.descriptif, v.ville, 
        COALESCE(p.main, 'default.jpg') as photo
        FROM vendeur v
        LEFT JOIN photos p ON v.Nom = p.Nom
        WHERE {$distanceFormula} <= :radius";

    $params = ['radius' => $radius];

    if ($type != 'Tous') {
        $query .= " AND v.Type = :type";
        $params['type'] = $type;
    }

    // Tri
    switch ($tri) {
        case 2:
            $query .= " ORDER BY distance ASC";
            break;
        case 3:
            $query .= " ORDER BY v.note DESC";
            break;
        case 4:
            $query .= " ORDER BY v.Nom ASC";
            break;
        default:
            $query .= " ORDER BY v.note DESC";
    }

    $offset = (int)$offset;
    $limit = (int)$limit;
    $query .= " LIMIT {$offset}, {$limit}";

    $rows = $dbw->fetchAll($query, $params);
    $restaurants = [];

    foreach ($rows as $row) {
        $image = !empty($row['photo']) && $row['photo'] != 'default.jpg' 
            ? 'images/vendeur/' . $row['photo'] 
            : 'assets/images/default.jpg';

        $restaurants[] = [
            'id' => uniqid(),
            'name' => $row['Nom'],
            'type' => $row['Type'],
            'rating' => (float)$row['note'],
            'reviews' => rand(50, 300),
            'description' => substr($row['descriptif'] ?? 'Cuisine de qualité', 0, 80),
            'price' => rand(15, 50),
            'distance' => round((float)$row['distance'], 1),
            'gps' => $row['gps'],
            'address' => $row['adresse'] . ', ' . $row['codePostal'] . ' ' . $row['ville'],
            'image' => $image
        ];
    }

    return $restaurants;
}

function countRestaurants($dbw, $distanceFormula, $type, $radius) {
    $query = "SELECT COUNT(DISTINCT v.Nom) as count FROM vendeur v 
              WHERE {$distanceFormula} <= :radius";
    $params = ['radius' => $radius];

    if ($type != 'Tous') {
        $query .= " AND v.Type = :type";
        $params['type'] = $type;
    }

    $row = $dbw->fetch($query, $params);
    return $row ? (int)$row['count'] : 0;
}

function getCategories($dbw) {
    $query = "SELECT DISTINCT Type, COUNT(Type) as count FROM vendeur 
              WHERE Type IS NOT NULL AND Type != '' 
              GROUP BY Type ORDER BY count DESC";
    $rows = $dbw->fetchAll($query);
    $categories = [];

    foreach ($rows as $row) {
        $categories[] = [
            'name' => $row['Type'],
            'count' => (int)$row['count']
        ];
    }

    return $categories;
}

function getRatings($dbw) {
    $ratings = [
        ['label' => '4.5 - 5.0 ⭐', 'min' => 4.5, 'max' => 5.0],
        ['label' => '4.0 - 4.5 ⭐', 'min' => 4.0, 'max' => 4.5],
        ['label' => '3.0 - 4.0 ⭐', 'min' => 3.0, 'max' => 4.0],
        ['label' => 'En dessous de 3.0', 'min' => 0, 'max' => 3.0]
    ];
    
    foreach ($ratings as &$rating) {
        $query = "SELECT COUNT(*) as count FROM vendeur 
                  WHERE note >= :min AND note < :max";
        $row = $dbw->fetch($query, ['min' => $rating['min'], 'max' => $rating['max']]);
        $rating['count'] = $row ? (int)$row['count'] : 0;
    }
    
    return $ratings;
}

function getOptions($dbw) {
    $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
              WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'options' 
              AND COLUMN_NAME NOT IN ('id', 'Nom')
              LIMIT 10";
    
    $rows = $dbw->fetchAll($query);
    $options = [];
    
    foreach ($rows as $row) {
        $colName = $row['COLUMN_NAME'];
        // Compter combien de vendeurs ont cette option activée
        $countQuery = "SELECT COUNT(DISTINCT Nom) as count FROM options WHERE `" . $colName . "` = '1'";
        $countRow = $dbw->fetch($countQuery);
        $count = $countRow ? (int)$countRow['count'] : 0;

        $options[] = [
            'name' => $colName,
            'count' => $count
        ];
    }
    
    return $options;
}

// ============ ACTION SEARCH ============

if ($action === 'search') {
    $total = countRestaurants($dbw, $distanceFormula, $type, $radius);
    $restaurants = getRestaurants($dbw, $distanceFormula, $type, $radius, $tri, $offset, $limit);
    $categories = getCategories($dbw);
    $ratings = getRatings($dbw);
    $options = getOptions($dbw);

    echo json_encode([
        'success' => true,
        'restaurants' => $restaurants,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'filters' => [
            'categories' => $categories,
            'ratings' => $ratings,
            'options' => $options
        ]
    ], JSON_UNESCAPED_UNICODE);
}

// Close legacy mysqli connection if present
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>