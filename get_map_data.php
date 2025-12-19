<?php
header('Content-Type: application/json');

// Configuration BDD
$servername = 'localhost';
$username = 'sam';
$password = '123';
$db = 'lebonresto';

// Connexion
$conn = new mysqli($servername, $username, $password, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connexion échouée']);
    exit;
}

// Récupération des paramètres
$currentgps = $_GET['currentgps'] ?? '';
$radius = (int)($_GET['radius'] ?? 10);
$type = $_GET['type'] ?? 'Tous';
$start = (int)($_GET['start'] ?? 0);
$nb = (int)($_GET['nb'] ?? 1000);
$options = isset($_GET['options']) ? unserialize(base64_decode($_GET['options'])) : null;

// Parser les coordonnées GPS
$gpsArray = explode(',', $currentgps);
if (count($gpsArray) != 2) {
    echo json_encode(['success' => false, 'error' => 'GPS invalide']);
    exit;
}

$geoc = [
    'lat' => trim($gpsArray[0]),
    'lon' => trim($gpsArray[1])
];

// Formule de distance
$distanceFormula = "(((acos(sin((" . $geoc['lat'] . "*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((" . $geoc['lat'] . "*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((" . $geoc['lon'] . "- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344)";

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

// Ajouter les options
if (isset($options) && is_array($options)) {
    foreach ($options as $val) {
        $val = mysqli_real_escape_string($conn, $val);
        $requete .= " AND o.{$val} = '1'";
    }
}

$requete .= " ORDER BY distance ASC LIMIT {$start}, {$nb}";

// Exécuter la requête
$resultat = mysqli_query($conn, $requete);

if (!$resultat) {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
    exit;
}

$restaurants = [];
while ($ligne = mysqli_fetch_assoc($resultat)) {
    $restaurants[] = [
        'nom' => $ligne['nom'],
        'type' => $ligne['type'],
        'adresse' => $ligne['adresse'],
        'ville' => $ligne['ville'],
        'gps' => $ligne['gps'],
        'distance' => round($ligne['distance'], 2)
    ];
}

mysqli_close($conn);

echo json_encode(['success' => true, 'restaurants' => $restaurants]);
?>