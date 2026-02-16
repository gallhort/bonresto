<?php
/**
 * Seed review_photos: copy some restaurant photos as fake review photos
 * to test the photo-centric feed
 */
$pdo = new PDO('mysql:host=localhost;dbname=lebonresto;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

// Get some approved reviews that don't already have photos
$reviews = $pdo->query("
    SELECT rev.id as review_id, rev.restaurant_id, rev.user_id
    FROM reviews rev
    WHERE rev.status = 'approved'
    AND rev.id NOT IN (SELECT review_id FROM review_photos)
    ORDER BY RAND()
    LIMIT 25
")->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($reviews) . " reviews without photos\n";

// Get all restaurant photos we can use as source
$restoPhotos = $pdo->query("
    SELECT restaurant_id, path FROM restaurant_photos
    WHERE path IS NOT NULL AND path != ''
    ORDER BY RAND()
")->fetchAll(PDO::FETCH_ASSOC);

// Index by restaurant_id
$photosByResto = [];
foreach ($restoPhotos as $rp) {
    $photosByResto[$rp['restaurant_id']][] = $rp['path'];
}

// Also get review photos that physically exist on disk
$diskPhotos = glob(__DIR__ . '/../public/uploads/reviews/*/*/*/*.*');
$diskPhotos2 = glob(__DIR__ . '/../public/uploads/reviews/*/*/*.*');
$allDiskPhotos = array_merge($diskPhotos, $diskPhotos2);
$diskPaths = [];
foreach ($allDiskPhotos as $fp) {
    // Convert to relative path from public/
    $rel = str_replace('\\', '/', $fp);
    $pos = strpos($rel, 'uploads/reviews/');
    if ($pos !== false) {
        $diskPaths[] = substr($rel, $pos);
    }
}
echo "Found " . count($diskPaths) . " photo files on disk\n";

$inserted = 0;
$stmt = $pdo->prepare("
    INSERT INTO review_photos (review_id, photo_path, category, display_order, created_at)
    VALUES (:rid, :path, :cat, :order, NOW())
");

$categories = ['food', 'drink', 'interior', 'exterior', 'menu', 'other'];

foreach ($reviews as $rev) {
    // Pick 1-3 photos for this review
    $numPhotos = rand(1, 3);
    $availablePhotos = [];

    // Try restaurant's own photos first
    if (!empty($photosByResto[$rev['restaurant_id']])) {
        $availablePhotos = $photosByResto[$rev['restaurant_id']];
    }

    // Also add random disk photos
    if (!empty($diskPaths)) {
        $shuffled = $diskPaths;
        shuffle($shuffled);
        $availablePhotos = array_merge($availablePhotos, array_slice($shuffled, 0, 3));
    }

    // Also add photos from other restaurants
    $otherRestoPhotos = array_column($restoPhotos, 'path');
    shuffle($otherRestoPhotos);
    $availablePhotos = array_merge($availablePhotos, array_slice($otherRestoPhotos, 0, 3));

    if (empty($availablePhotos)) continue;

    $availablePhotos = array_unique($availablePhotos);
    shuffle($availablePhotos);

    for ($i = 0; $i < $numPhotos && $i < count($availablePhotos); $i++) {
        try {
            $stmt->execute([
                ':rid' => $rev['review_id'],
                ':path' => $availablePhotos[$i],
                ':cat' => $categories[array_rand($categories)],
                ':order' => $i + 1,
            ]);
            $inserted++;
            echo ".";
        } catch (PDOException $e) {
            echo "x";
        }
    }
}

echo "\n\nInserted $inserted review photos for " . count($reviews) . " reviews\n";
echo "Total review_photos: " . $pdo->query("SELECT COUNT(*) FROM review_photos")->fetchColumn() . "\n";
