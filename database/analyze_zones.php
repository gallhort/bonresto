<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lebonresto;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Analyse par grille : diviser chaque ville en zones de ~2km et compter les restaurants par zone

function analyzeCity($pdo, $ville) {
    $rows = $pdo->prepare("
        SELECT nom, adresse, gps_latitude as lat, gps_longitude as lng
        FROM restaurants
        WHERE ville = ? AND status='validated' AND gps_latitude IS NOT NULL AND gps_latitude != 0
    ");
    $rows->execute([$ville]);
    $restos = $rows->fetchAll(PDO::FETCH_ASSOC);

    if (empty($restos)) return;

    $lats = array_column($restos, 'lat');
    $lngs = array_column($restos, 'lng');

    $minLat = min($lats); $maxLat = max($lats);
    $minLng = min($lngs); $maxLng = max($lngs);
    $centerLat = ($minLat + $maxLat) / 2;
    $centerLng = ($minLng + $maxLng) / 2;

    echo "\n=== $ville ({$rows->rowCount()} restaurants) ===\n";
    echo "  Bounding box: ($minLat,$minLng) -> ($maxLat,$maxLng)\n";
    echo "  Centre: ($centerLat, $centerLng)\n";

    // Grille de ~2km (0.018 deg lat, 0.022 deg lng en Algérie)
    $gridLat = 0.018;
    $gridLng = 0.022;

    $grid = [];
    foreach ($restos as $r) {
        $cellLat = floor(($r['lat'] - $minLat) / $gridLat);
        $cellLng = floor(($r['lng'] - $minLng) / $gridLng);
        $key = "$cellLat,$cellLng";
        if (!isset($grid[$key])) {
            $grid[$key] = ['count' => 0, 'lat' => 0, 'lng' => 0, 'names' => []];
        }
        $grid[$key]['count']++;
        $grid[$key]['lat'] += $r['lat'];
        $grid[$key]['lng'] += $r['lng'];
        $grid[$key]['names'][] = $r['nom'];
    }

    // Trier par densité
    uasort($grid, fn($a, $b) => $b['count'] - $a['count']);

    echo "\n  Zones denses (clustering):\n";
    foreach ($grid as $key => $cell) {
        $avgLat = round($cell['lat'] / $cell['count'], 6);
        $avgLng = round($cell['lng'] / $cell['count'], 6);
        $preview = implode(', ', array_slice($cell['names'], 0, 3));
        if (count($cell['names']) > 3) $preview .= '...';
        echo "    Zone $key: {$cell['count']} restos (centre: $avgLat, $avgLng) - $preview\n";
    }
}

// Analyse des communes connues d'Alger
echo "\n\n========== COMMUNES D'ALGER - COUVERTURE ==========\n";
$communesAlger = [
    // Centre
    ['Alger Centre', 36.7731, 3.0588],
    ['Sidi M\'Hamed', 36.7589, 3.0525],
    ['Belouizdad', 36.7531, 3.0658],
    ['El Madania', 36.7458, 3.0550],
    ['Bab El Oued', 36.7892, 3.0508],
    // Centre-Sud
    ['Hydra', 36.7397, 3.0267],
    ['El Biar', 36.7639, 3.0303],
    ['Bir Mourad Raïs', 36.7342, 3.0500],
    ['Kouba', 36.7264, 3.0608],
    ['Hussein Dey', 36.7450, 3.0933],
    // Ouest
    ['Bouzareah', 36.7833, 3.0167],
    ['Ben Aknoun', 36.7500, 3.0083],
    ['Dely Ibrahim', 36.7522, 2.9833],
    ['Cheraga', 36.7667, 2.9500],
    ['Ouled Fayet', 36.7333, 2.9333],
    ['El Achour', 36.7267, 2.9667],
    ['Draria', 36.7167, 2.9500],
    ['Ain Benian', 36.8025, 2.9200],
    ['Staoueli', 36.7583, 2.8833],
    ['Zeralda', 36.7117, 2.8533],
    // Est
    ['Bab Ezzouar', 36.7200, 3.1833],
    ['Bordj El Kiffan', 36.7467, 3.1933],
    ['Dar El Beida', 36.7133, 3.2133],
    ['Mohammadia', 36.7333, 3.1467],
    ['El Harrach', 36.7200, 3.1333],
    ['Oued Smar', 36.7067, 3.1667],
    ['Rouiba', 36.7333, 3.2833],
    ['Reghaia', 36.7367, 3.3417],
    // Sud
    ['Baraki', 36.6667, 3.0833],
    ['Birtouta', 36.6333, 3.0500],
    ['Eucalyptus', 36.6800, 3.1100],
    ['Sidi Moussa', 36.6167, 3.1000],
    ['Baba Hassen', 36.6917, 2.9750],
    ['Douera', 36.6667, 2.9333],
    ['Khraicia', 36.6750, 3.0000],
    ['Said Hamdine', 36.7350, 3.0400],
];

foreach ($communesAlger as [$commune, $lat, $lng]) {
    // Compter les restaurants dans un rayon de ~2km
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM restaurants
        WHERE ville = 'Alger' AND status = 'validated'
        AND gps_latitude BETWEEN ? AND ?
        AND gps_longitude BETWEEN ? AND ?
    ");
    $delta = 0.018; // ~2km
    $stmt->execute([$lat - $delta, $lat + $delta, $lng - $delta, $lng + $delta]);
    $count = $stmt->fetchColumn();
    $status = $count == 0 ? '❌ VIDE' : ($count < 3 ? '⚠️ FAIBLE' : '✅');
    echo "  $commune: $count restos $status (centre: $lat, $lng)\n";
}

// Analyse des communes/quartiers d'Oran
echo "\n\n========== COMMUNES D'ORAN - COUVERTURE ==========\n";
$communesOran = [
    ['Oran Centre', 35.6972, -0.6333],
    ['Sidi El Houari', 35.6972, -0.6489],
    ['Es-Senia', 35.6350, -0.6217],
    ['Bir El Djir', 35.7183, -0.5700],
    ['El Kerma', 35.6217, -0.5933],
    ['Ain El Turk', 35.7400, -0.7700],
    ['Mers El Kebir', 35.7267, -0.7100],
    ['Arzew', 35.8228, -0.3203],
    ['Bethioua', 35.7833, -0.2583],
    ['Gdyel', 35.7767, -0.4667],
    ['Hassi Bounif', 35.6583, -0.5250],
    ['Sidi Chahmi', 35.6867, -0.5617],
    ['Canastel', 35.7367, -0.5917],
    ['Hai Sabah / USTO', 35.7050, -0.5600],
    ['Hai Menzah / Belgaid', 35.7367, -0.5500],
    ['El Yasmine / El Bayada', 35.7083, -0.5417],
    ['Misserghine', 35.6333, -0.6833],
    ['Boutlelis', 35.6300, -0.7267],
];

foreach ($communesOran as [$commune, $lat, $lng]) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM restaurants
        WHERE ville = 'Oran' AND status = 'validated'
        AND gps_latitude BETWEEN ? AND ?
        AND gps_longitude BETWEEN ? AND ?
    ");
    $delta = 0.018;
    $stmt->execute([$lat - $delta, $lat + $delta, $lng - $delta, $lng + $delta]);
    $count = $stmt->fetchColumn();
    $status = $count == 0 ? '❌ VIDE' : ($count < 3 ? '⚠️ FAIBLE' : '✅');
    echo "  $commune: $count restos $status (centre: $lat, $lng)\n";
}

// Résumé : zones vides à cibler
echo "\n\n========== ZONES VIDES À CIBLER ==========\n";
echo "(Communes avec 0 restaurants dans un rayon de 2km)\n";
