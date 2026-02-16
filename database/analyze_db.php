<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lebonresto;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== ÉTAPE 1 : ANALYSE COMPLÈTE DE LA BDD ===\n\n";

// 1. Structure de la table
echo "--- 1. STRUCTURE TABLE RESTAURANTS ---\n";
$cols = $pdo->query("DESCRIBE restaurants")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "  {$c['Field']} ({$c['Type']}) " . ($c['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}

// 2. google_place_id existe ?
$hasPlaceId = false;
foreach ($cols as $c) {
    if ($c['Field'] === 'google_place_id') $hasPlaceId = true;
}
echo "\n  google_place_id présent : " . ($hasPlaceId ? 'OUI' : 'NON') . "\n";

// 3. Stats globales
echo "\n--- 2. STATS GLOBALES ---\n";
$total = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$validated = $pdo->query("SELECT COUNT(*) FROM restaurants WHERE status='validated'")->fetchColumn();
echo "  Total restaurants: $total\n";
echo "  Validés: $validated\n";

// 4. Distribution par ville
echo "\n--- 3. DISTRIBUTION PAR VILLE ---\n";
$cities = $pdo->query("
    SELECT ville, COUNT(*) as cnt,
           SUM(CASE WHEN gps_latitude IS NOT NULL AND gps_latitude != 0 THEN 1 ELSE 0 END) as with_gps,
           SUM(CASE WHEN phone IS NOT NULL AND phone != '' AND phone != 'N/A' THEN 1 ELSE 0 END) as with_phone,
           SUM(CASE WHEN website IS NOT NULL AND website != '' AND website != 'N/A' THEN 1 ELSE 0 END) as with_website
    FROM restaurants
    WHERE status='validated'
    GROUP BY ville
    ORDER BY cnt DESC
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cities as $c) {
    echo "  {$c['ville']}: {$c['cnt']} restos (GPS: {$c['with_gps']}, Tel: {$c['with_phone']}, Web: {$c['with_website']})\n";
}

// 5. Distribution par type de cuisine
echo "\n--- 4. TYPES DE CUISINE ---\n";
$types = $pdo->query("
    SELECT type_cuisine, COUNT(*) as cnt
    FROM restaurants WHERE status='validated' AND type_cuisine IS NOT NULL AND type_cuisine != ''
    GROUP BY type_cuisine ORDER BY cnt DESC LIMIT 20
")->fetchAll(PDO::FETCH_ASSOC);
foreach ($types as $t) {
    echo "  {$t['type_cuisine']}: {$t['cnt']}\n";
}

// 6. Analyse GPS pour Alger - quartiers couverts
echo "\n--- 5. COUVERTURE GPS ALGER ---\n";
$algerGps = $pdo->query("
    SELECT nom, adresse, gps_latitude, gps_longitude
    FROM restaurants
    WHERE ville='Alger' AND status='validated' AND gps_latitude IS NOT NULL AND gps_latitude != 0
    ORDER BY gps_latitude
")->fetchAll(PDO::FETCH_ASSOC);
echo "  Restaurants avec GPS à Alger: " . count($algerGps) . "\n";
if (count($algerGps) > 0) {
    $lats = array_column($algerGps, 'gps_latitude');
    $lngs = array_column($algerGps, 'gps_longitude');
    echo "  Lat range: " . min($lats) . " → " . max($lats) . "\n";
    echo "  Lng range: " . min($lngs) . " → " . max($lngs) . "\n";
    echo "  Centre: " . round(array_sum($lats)/count($lats), 6) . ", " . round(array_sum($lngs)/count($lngs), 6) . "\n";
}

// Quartiers d'Alger (extraits des adresses)
echo "\n  Quartiers mentionnés dans les adresses:\n";
$algerAddrs = $pdo->query("
    SELECT adresse FROM restaurants WHERE ville='Alger' AND status='validated' AND adresse IS NOT NULL AND adresse != ''
")->fetchAll(PDO::FETCH_COLUMN);
$quartiers = [];
$knownQuartiers = ['Hydra','El Biar','Bab El Oued','Kouba','Hussein Dey','Bir Mourad Raïs','Sidi M\'Hamed',
    'Alger Centre','Belouizdad','El Madania','Dely Ibrahim','Ben Aknoun','Bouzareah','Cheraga',
    'Bordj El Kiffan','Dar El Beida','Rouiba','Bab Ezzouar','El Harrach','Mohammadia',
    'Ain Benian','Staoueli','Draria','Birtouta','Ouled Fayet','El Achour','Said Hamdine'];
foreach ($algerAddrs as $addr) {
    foreach ($knownQuartiers as $q) {
        if (stripos($addr, $q) !== false) {
            $quartiers[$q] = ($quartiers[$q] ?? 0) + 1;
        }
    }
}
arsort($quartiers);
foreach ($quartiers as $q => $cnt) {
    echo "    $q: $cnt\n";
}

// 7. Analyse GPS pour Oran
echo "\n--- 6. COUVERTURE GPS ORAN ---\n";
$oranGps = $pdo->query("
    SELECT nom, adresse, gps_latitude, gps_longitude
    FROM restaurants
    WHERE ville='Oran' AND status='validated' AND gps_latitude IS NOT NULL AND gps_latitude != 0
    ORDER BY gps_latitude
")->fetchAll(PDO::FETCH_ASSOC);
echo "  Restaurants avec GPS à Oran: " . count($oranGps) . "\n";
if (count($oranGps) > 0) {
    $lats = array_column($oranGps, 'gps_latitude');
    $lngs = array_column($oranGps, 'gps_longitude');
    echo "  Lat range: " . min($lats) . " → " . max($lats) . "\n";
    echo "  Lng range: " . min($lngs) . " → " . max($lngs) . "\n";
    echo "  Centre: " . round(array_sum($lats)/count($lats), 6) . ", " . round(array_sum($lngs)/count($lngs), 6) . "\n";
}

// 8. Champs vides / qualité données
echo "\n--- 7. QUALITÉ DES DONNÉES ---\n";
$quality = $pdo->query("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN gps_latitude IS NULL OR gps_latitude = 0 THEN 1 ELSE 0 END) as no_gps,
        SUM(CASE WHEN phone IS NULL OR phone = '' OR phone = 'N/A' THEN 1 ELSE 0 END) as no_phone,
        SUM(CASE WHEN website IS NULL OR website = '' OR website = 'N/A' THEN 1 ELSE 0 END) as no_website,
        SUM(CASE WHEN adresse IS NULL OR adresse = '' THEN 1 ELSE 0 END) as no_address,
        SUM(CASE WHEN descriptif IS NULL OR descriptif = '' OR descriptif LIKE 'Restaurant à%' THEN 1 ELSE 0 END) as generic_desc,
        SUM(CASE WHEN type_cuisine IS NULL OR type_cuisine = '' THEN 1 ELSE 0 END) as no_cuisine
    FROM restaurants WHERE status='validated'
")->fetch(PDO::FETCH_ASSOC);
echo "  Total validés: {$quality['total']}\n";
echo "  Sans GPS: {$quality['no_gps']} (" . round($quality['no_gps']/$quality['total']*100) . "%)\n";
echo "  Sans téléphone: {$quality['no_phone']} (" . round($quality['no_phone']/$quality['total']*100) . "%)\n";
echo "  Sans website: {$quality['no_website']} (" . round($quality['no_website']/$quality['total']*100) . "%)\n";
echo "  Sans adresse: {$quality['no_address']} (" . round($quality['no_address']/$quality['total']*100) . "%)\n";
echo "  Description générique: {$quality['generic_desc']} (" . round($quality['generic_desc']/$quality['total']*100) . "%)\n";
echo "  Sans type cuisine: {$quality['no_cuisine']} (" . round($quality['no_cuisine']/$quality['total']*100) . "%)\n";

// 9. Photos existantes
echo "\n--- 8. PHOTOS RESTAURANTS ---\n";
try {
    $photoCount = $pdo->query("SELECT COUNT(DISTINCT restaurant_id) FROM restaurant_photos")->fetchColumn();
    $totalPhotos = $pdo->query("SELECT COUNT(*) FROM restaurant_photos")->fetchColumn();
    echo "  Restaurants avec photos: $photoCount\n";
    echo "  Total photos: $totalPhotos\n";
} catch (Exception $e) {
    echo "  Table restaurant_photos non trouvée\n";
    // Check if photos are stored differently
    $withPhotos = $pdo->query("
        SELECT COUNT(*) FROM restaurants
        WHERE status='validated' AND id IN (
            SELECT DISTINCT restaurant_id FROM review_photos
        )
    ")->fetchColumn();
    echo "  Restaurants ayant des photos d'avis: $withPhotos\n";
}

// 10. Horaires existants
echo "\n--- 9. HORAIRES ---\n";
try {
    $withHours = $pdo->query("SELECT COUNT(DISTINCT restaurant_id) FROM horaires")->fetchColumn();
    echo "  Restaurants avec horaires: $withHours\n";
} catch (Exception $e) {
    echo "  Table horaires: " . $e->getMessage() . "\n";
}

echo "\n=== FIN ANALYSE ===\n";
