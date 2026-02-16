<?php
/**
 * Script de seed Google Places API
 * Recupere les vraies photos et avis pour chaque activite
 *
 * Usage: php scripts/seed_google_places.php
 *
 * Prerequis:
 *   1. Cle API Google Places dans .env : GOOGLE_PLACES_API_KEY=xxxxx
 *   2. Activer Places API (New) dans Google Cloud Console
 *   3. Les activites doivent deja etre en base (phase16_activities.sql)
 *
 * Limites Google Places API:
 *   - Text Search : $32 / 1000 requetes
 *   - Place Details : $17 / 1000 requetes
 *   - Place Photos : $7 / 1000 requetes
 *   - Free tier : $200/mois gratuit (~5000 recherches)
 *
 * Ce script fait ~110 Text Search + ~110 Details + ~200 Photos = ~420 requetes = ~$10
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Charger .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            putenv(trim($line));
        }
    }
}

$apiKey = getenv('GOOGLE_PLACES_API_KEY');
if (!$apiKey) {
    die("GOOGLE_PLACES_API_KEY manquant dans .env\n");
}

// Connexion DB
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'lebonresto';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erreur DB: " . $e->getMessage() . "\n");
}

// Recuperer toutes les activites
$activities = $pdo->query("SELECT id, nom, ville, slug, gps_latitude, gps_longitude FROM activities ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$total = count($activities);

echo "=== Seed Google Places API ===\n";
echo "Activites a traiter: $total\n";
echo "Cle API: " . substr($apiKey, 0, 8) . "...\n\n";

$stats = ['found' => 0, 'photos' => 0, 'reviews' => 0, 'skipped' => 0, 'errors' => 0];

foreach ($activities as $i => $activity) {
    $num = $i + 1;
    echo "[$num/$total] {$activity['nom']} ({$activity['ville']})... ";

    // Verifier si deja traite (a des photos non-unsplash)
    $existingPhotos = $pdo->prepare("SELECT COUNT(*) FROM activity_photos WHERE activity_id = :id AND path NOT LIKE '%unsplash%'");
    $existingPhotos->execute([':id' => $activity['id']]);
    if ((int)$existingPhotos->fetchColumn() > 0) {
        echo "SKIP (deja traite)\n";
        $stats['skipped']++;
        continue;
    }

    // 1. Text Search pour trouver le place_id
    $query = $activity['nom'] . ' ' . $activity['ville'] . ' Algerie';
    $searchUrl = 'https://maps.googleapis.com/maps/api/place/textsearch/json?' . http_build_query([
        'query' => $query,
        'key' => $apiKey,
        'language' => 'fr',
        'region' => 'dz',
    ]);

    // Si on a les coordonnees GPS, ajouter un biais de localisation
    if ($activity['gps_latitude'] && $activity['gps_longitude']) {
        $searchUrl .= '&location=' . $activity['gps_latitude'] . ',' . $activity['gps_longitude'] . '&radius=5000';
    }

    $searchResult = json_decode(file_get_contents($searchUrl), true);

    if (empty($searchResult['results'])) {
        echo "NOT FOUND\n";
        $stats['errors']++;
        continue;
    }

    $place = $searchResult['results'][0];
    $placeId = $place['place_id'];
    $stats['found']++;

    // 2. Place Details pour les avis
    $detailsUrl = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query([
        'place_id' => $placeId,
        'key' => $apiKey,
        'language' => 'fr',
        'fields' => 'reviews,photos,rating,user_ratings_total',
    ]);

    $detailsResult = json_decode(file_get_contents($detailsUrl), true);
    $details = $detailsResult['result'] ?? [];

    // 3. Supprimer les anciennes photos placeholder
    $pdo->prepare("DELETE FROM activity_photos WHERE activity_id = :id AND path LIKE '%unsplash%'")
        ->execute([':id' => $activity['id']]);

    // 4. Telecharger et sauvegarder les photos (max 3 par lieu)
    $photoCount = 0;
    $photos = $details['photos'] ?? $place['photos'] ?? [];
    $photosToFetch = array_slice($photos, 0, 3);

    foreach ($photosToFetch as $pi => $photo) {
        $photoRef = $photo['photo_reference'];
        $photoUrl = "https://maps.googleapis.com/maps/api/place/photo?" . http_build_query([
            'maxwidth' => 800,
            'photo_reference' => $photoRef,
            'key' => $apiKey,
        ]);

        // Telecharger la photo localement
        $photoDir = __DIR__ . '/../public/assets/images/activities/' . $activity['slug'];
        if (!is_dir($photoDir)) {
            mkdir($photoDir, 0755, true);
        }

        $photoContent = file_get_contents($photoUrl);
        if ($photoContent) {
            $photoFilename = ($pi + 1) . '.jpg';
            $photoPath = $photoDir . '/' . $photoFilename;
            file_put_contents($photoPath, $photoContent);

            $dbPath = '/assets/images/activities/' . $activity['slug'] . '/' . $photoFilename;
            $type = $pi === 0 ? 'main' : 'gallery';
            $caption = $photo['html_attributions'][0] ?? $activity['nom'];
            $caption = strip_tags($caption);

            $stmt = $pdo->prepare("INSERT INTO activity_photos (activity_id, path, type, caption) VALUES (:aid, :path, :type, :caption)");
            $stmt->execute([
                ':aid' => $activity['id'],
                ':path' => $dbPath,
                ':type' => $type,
                ':caption' => mb_substr($caption, 0, 255),
            ]);
            $photoCount++;
            $stats['photos']++;
        }
    }

    // 5. Importer les avis Google (max 5 par lieu)
    $reviewCount = 0;
    $reviews = $details['reviews'] ?? [];
    $reviewsToImport = array_slice($reviews, 0, 5);

    // Supprimer les anciens avis de test (user_id = 1)
    $pdo->prepare("DELETE FROM activity_reviews WHERE activity_id = :id AND user_id = 1")
        ->execute([':id' => $activity['id']]);

    foreach ($reviewsToImport as $review) {
        if (empty($review['text']) || mb_strlen($review['text']) < 10) continue;

        $stmt = $pdo->prepare("
            INSERT INTO activity_reviews (activity_id, user_id, note_globale, message, status, created_at)
            VALUES (:aid, 1, :note, :msg, 'approved', :date)
        ");
        $stmt->execute([
            ':aid' => $activity['id'],
            ':note' => min(5, max(1, $review['rating'])),
            ':msg' => mb_substr($review['text'], 0, 2000),
            ':date' => date('Y-m-d H:i:s', $review['time'] ?? time()),
        ]);
        $reviewCount++;
        $stats['reviews']++;
    }

    // 6. Mettre a jour les stats de l'activite
    $rating = $details['rating'] ?? null;
    if ($rating) {
        $pdo->prepare("UPDATE activities SET note_moyenne = :note WHERE id = :id")
            ->execute([':note' => min(5, $rating), ':id' => $activity['id']]);
    }

    echo "OK (place_id: " . substr($placeId, 0, 20) . "... | {$photoCount} photos, {$reviewCount} avis)\n";

    // Rate limiting: 100ms entre chaque requete
    usleep(100000);
}

// Mettre a jour les compteurs
$pdo->exec("
    UPDATE activities a SET
        nb_avis = (SELECT COUNT(*) FROM activity_reviews ar WHERE ar.activity_id = a.id AND ar.status = 'approved'),
        nb_photos = (SELECT COUNT(*) FROM activity_photos ap WHERE ap.activity_id = a.id),
        note_moyenne = COALESCE((SELECT AVG(ar.note_globale) FROM activity_reviews ar WHERE ar.activity_id = a.id AND ar.status = 'approved'), a.note_moyenne)
");

echo "\n=== Terminee ===\n";
echo "Trouves: {$stats['found']} | Photos: {$stats['photos']} | Avis: {$stats['reviews']} | Skip: {$stats['skipped']} | Erreurs: {$stats['errors']}\n";
