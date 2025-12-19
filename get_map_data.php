<?php
header('Content-Type: application/json');

include_once __DIR__ . '/connect.php';
require_once __DIR__ . '/classes/DatabasePDO.php';

// Initialiser le wrapper PDO (en mode fail-safe)
try {
    $dbw = new DatabasePDO();
} catch (Exception $e) {
    error_log('get_map_data: DB init failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur interne BDD']);
    exit;
}

// Récupération des paramètres
$currentgps = $_GET['currentgps'] ?? '';
$radius = (int)($_GET['radius'] ?? 10);
$type = $_GET['type'] ?? 'Tous';
$start = (int)($_GET['start'] ?? 0);
$nb = (int)($_GET['nb'] ?? 1000);
$options = null;
if (!empty($_GET['options'])) {
    $decoded = base64_decode($_GET['options'], true);
    if ($decoded !== false) {
        $un = @unserialize($decoded);
        if (is_array($un)) {
            $options = array_filter($un, function($v){ return preg_match('/^[A-Za-z0-9_]+$/', $v); });
        }
    }
}

// Parser les coordonnées GPS
$gpsArray = explode(',', $currentgps);
if (count($gpsArray) != 2) {
    echo json_encode(['success' => false, 'error' => 'GPS invalide']);
    exit;
}

$lat = (float) trim($gpsArray[0]);
$lon = (float) trim($gpsArray[1]);
// Clamp radius and nb
$radius = max(0, min(200, (int)$radius));
$start = max(0, (int)$start);
$nb = max(1, min(1000, (int)$nb));

// Formule de distance
$distanceFormula = "(((acos(sin((" . $lat . "*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((" . $lat . "*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((" . $lon . "- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344)";

// Construire la requête
if ($type == 'Tous') {
    $requete = "SELECT 
        {$distanceFormula} AS distance,
        v.gps, v.nom, v.type, v.adresse, v.ville
        FROM vendeur v
        LEFT JOIN options o ON v.Nom = o.Nom
        WHERE {$distanceFormula} <= " . $radius;
} else {
    $type_escaped = mysqli_real_escape_string($conn, $type);
    $requete = "SELECT 
        {$distanceFormula} AS distance,
        v.gps, v.nom, v.type, v.adresse, v.ville
        FROM vendeur v
        LEFT JOIN options o ON v.Nom = o.Nom
        WHERE {$distanceFormula} <= " . $radius . " 
        AND v.Type = '{$type_escaped}'";
}

// Ajouter les options (validation stricte)
if (!empty($options) && is_array($options)) {
    foreach ($options as $val) {
        if (preg_match('/^[A-Za-z0-9_]+$/', $val)) {
            $requete .= " AND o.`" . $val . "` = '1'";
        }
    }
}

$sql = $requete . " ORDER BY distance ASC LIMIT {$start}, {$nb}";

try {
    $rows = $dbw->fetchAll($sql, $params ?? []);
    $restaurants = [];
    foreach ($rows as $ligne) {
        $restaurants[] = [
            'nom' => $ligne['nom'],
            'type' => $ligne['type'],
            'adresse' => $ligne['adresse'],
            'ville' => $ligne['ville'],
            'gps' => $ligne['gps'],
            'distance' => round($ligne['distance'], 2)
        ];
    }

    echo json_encode(['success' => true, 'restaurants' => $restaurants]);
} catch (Exception $e) {
    error_log('get_map_data exception: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Erreur interne BDD']);
}
?>