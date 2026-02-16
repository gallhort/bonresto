<?php
/**
 * Seed test data for LeBonResto - Phases 12 & 13 features
 */
$pdo = new PDO('mysql:host=localhost;dbname=lebonresto;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$success = 0;
$errors = 0;

function run($pdo, $sql, $params, &$success, &$errors) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $success++;
        return true;
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        if (strpos($msg, 'Duplicate') !== false) {
            echo "SKIP dup\n";
        } else {
            echo "ERR: $msg\n";
            $errors++;
        }
        return false;
    }
}

// =====================================================================
// 1. Create test users
// =====================================================================
echo "=== Users ===\n";
$testUsers = [
    ['Amina', 'Benali', 'amina.b@test.com', 'aminab', 'AMINA001'],
    ['Karim', 'Hadj', 'karim.h@test.com', 'karimh', 'KARIM001'],
    ['Sofia', 'Mebarki', 'sofia.m@test.com', 'sofiam', 'SOFIA001'],
    ['Yacine', 'Boudjema', 'yacine.b@test.com', 'yacineb', 'YACINE01'],
    ['Lina', 'Rahmani', 'lina.r@test.com', 'linar', 'LINA0001'],
    ['Omar', 'Ait-Said', 'omar.a@test.com', 'omara', 'OMAR0001'],
    ['Nadia', 'Ferhat', 'nadia.f@test.com', 'nadiaf', 'NADIA001'],
    ['Mehdi', 'Kaci', 'mehdi.k@test.com', 'mehdik', 'MEHDI001'],
];

$hash = password_hash('TestPass123!', PASSWORD_BCRYPT);
$newUserIds = [];
foreach ($testUsers as $u) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $u[2]]);
    $existing = $stmt->fetchColumn();
    if ($existing) {
        $newUserIds[] = (int)$existing;
        echo "EXISTS: {$u[0]} id=$existing\n";
    } else {
        $pts = rand(50, 800);
        $badge = ['Explorateur','Gourmet','Connaisseur'][rand(0,2)];
        $days = rand(5, 60);
        run($pdo, "INSERT INTO users (prenom, nom, email, username, password_hash, role, points, badge, referral_code, created_at) VALUES (:prenom, :nom, :email, :user, :pass, 'user', :pts, :badge, :ref, NOW() - INTERVAL :days DAY)", [
            ':prenom' => $u[0], ':nom' => $u[1], ':email' => $u[2], ':user' => $u[3],
            ':pass' => $hash, ':pts' => $pts, ':badge' => $badge, ':ref' => $u[4], ':days' => $days,
        ], $success, $errors);
        $newUserIds[] = (int)$pdo->lastInsertId();
        echo "OK: {$u[0]} id=" . end($newUserIds) . "\n";
    }
}

// Set referral codes on existing users
for ($i = 1; $i <= 5; $i++) {
    $code = 'DJIDA' . str_pad($i, 3, '0', STR_PAD_LEFT);
    $pdo->prepare("UPDATE users SET referral_code = :code WHERE id = :id AND (referral_code IS NULL OR referral_code = '')")
        ->execute([':code' => $code, ':id' => $i]);
}

$allUserIds = array_merge([1,2,3,4,5], $newUserIds);
// Filter out 0s
$allUserIds = array_values(array_filter($allUserIds, fn($id) => $id > 0));
echo "Working with " . count($allUserIds) . " users: " . implode(',', $allUserIds) . "\n";

$restos = $pdo->query("SELECT id, nom FROM restaurants WHERE status='validated' ORDER BY RAND() LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
$restoIds = array_column($restos, 'id');
echo "Using " . count($restoIds) . " restaurants\n\n";

// =====================================================================
// 2. Follows
// =====================================================================
echo "=== Follows ===\n";
$followCount = 0;
foreach ($allUserIds as $follower) {
    $others = array_values(array_diff($allUserIds, [$follower]));
    shuffle($others);
    $toFollow = array_slice($others, 0, rand(2, min(4, count($others))));
    foreach ($toFollow as $followed) {
        if (run($pdo, "INSERT IGNORE INTO user_follows (follower_id, followed_id, created_at) VALUES (:f, :t, NOW() - INTERVAL :d DAY)", [
            ':f' => $follower, ':t' => $followed, ':d' => rand(1, 30),
        ], $success, $errors)) {
            $followCount++;
        }
    }
}
echo "Follows: $followCount\n\n";

// =====================================================================
// 3. Collections
// =====================================================================
echo "=== Collections ===\n";
$collections = [
    [$allUserIds[0], 'Mes restos italiens preferes', 'Les meilleurs restaurants italiens que j\'ai testes'],
    [$allUserIds[1], 'Sorties entre amis', 'Endroits parfaits pour un diner entre potes'],
    [$allUserIds[2] ?? 1, 'Top burgers Alger', 'Les meilleurs burgers de la capitale'],
    [$allUserIds[3] ?? 1, 'Romantique', 'Restaurants parfaits pour un diner en amoureux'],
    [$allUserIds[4] ?? 2, 'Budget friendly', 'Bien manger sans se ruiner'],
    [$allUserIds[0], 'Fruits de mer Oran', 'Les meilleurs poissons et fruits de mer oranais'],
    [$allUserIds[5] ?? 3, 'Cuisine du monde', 'Voyage culinaire a travers Alger'],
];

$collectionIds = [];
foreach ($collections as $c) {
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $c[1]));
    if (run($pdo, "INSERT INTO collections (user_id, title, description, slug, is_public, created_at) VALUES (:uid, :title, :desc, :slug, 1, NOW() - INTERVAL :d DAY)", [
        ':uid' => $c[0], ':title' => $c[1], ':desc' => $c[2], ':slug' => $slug, ':d' => rand(5, 45),
    ], $success, $errors)) {
        $collectionIds[] = (int)$pdo->lastInsertId();
    }
}

foreach ($collectionIds as $cid) {
    if ($cid <= 0) continue;
    shuffle($restoIds);
    $picked = array_slice($restoIds, 0, rand(3, 5));
    $pos = 1;
    foreach ($picked as $rid) {
        run($pdo, "INSERT INTO collection_restaurants (collection_id, restaurant_id, position, added_at) VALUES (:cid, :rid, :pos, NOW() - INTERVAL :d DAY)", [
            ':cid' => $cid, ':rid' => $rid, ':pos' => $pos++, ':d' => rand(1, 30),
        ], $success, $errors);
    }
}
echo "Collections: " . count($collectionIds) . "\n\n";

// =====================================================================
// 4. Tips
// =====================================================================
echo "=== Tips ===\n";
$tips = [
    "Demandez la table pres de la fenetre, vue magnifique!",
    "Le tiramisu maison est incroyable, a ne pas rater",
    "Reservez le weekend, ca se remplit vite",
    "Le menu du midi est un excellent rapport qualite-prix",
    "Parking gratuit derriere le restaurant",
    "Le chef prepare des plats hors menu sur demande",
    "Ambiance parfaite pour un anniversaire",
    "Les portions sont genereuses, un plat suffit largement",
    "Terrasse agreable en ete, pensez a reserver",
    "Essayez le cafe special de la maison en dessert",
    "Les enfants adorent, ils ont un menu kids",
    "Le pain maison est offert et delicieux",
    "Demandez le plat du jour, toujours une bonne surprise",
    "Les salades sont fraiches et copieuses",
    "Le couscous du vendredi est legendaire",
    "Ils font des doggy bags sans probleme",
];

$tipCount = 0;
foreach ($restoIds as $rid) {
    $n = rand(1, 3);
    for ($t = 0; $t < $n; $t++) {
        $uid = $allUserIds[array_rand($allUserIds)];
        $tip = $tips[array_rand($tips)];
        if (run($pdo, "INSERT INTO restaurant_tips (user_id, restaurant_id, message, status, votes, created_at) VALUES (:uid, :rid, :msg, 'approved', :v, NOW() - INTERVAL :d DAY)", [
            ':uid' => $uid, ':rid' => $rid, ':msg' => $tip, ':v' => rand(0, 15), ':d' => rand(1, 20),
        ], $success, $errors)) {
            $tipCount++;
        }
    }
}
echo "Tips: $tipCount\n\n";

// =====================================================================
// 5. Review tags + context tags
// =====================================================================
echo "=== Review tags ===\n";
$tagTypes = ['romantique','familial','business','terrasse','vue','calme','anime','bon_rapport','grandes_portions','service_rapide','halal_certifie','livraison'];
$reviews = $pdo->query("SELECT id, restaurant_id FROM reviews ORDER BY RAND() LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
$tagCount = 0;
foreach ($reviews as $rev) {
    $n = rand(1, 4);
    shuffle($tagTypes);
    $chosen = array_slice($tagTypes, 0, $n);
    foreach ($chosen as $tag) {
        run($pdo, "INSERT IGNORE INTO review_tags (review_id, tag) VALUES (:rid, :tag)", [
            ':rid' => $rev['id'], ':tag' => $tag,
        ], $success, $errors);
        run($pdo, "INSERT INTO restaurant_context_tags (restaurant_id, tag, vote_count) VALUES (:rid, :tag, 1) ON DUPLICATE KEY UPDATE vote_count = vote_count + 1", [
            ':rid' => $rev['restaurant_id'], ':tag' => $tag,
        ], $success, $errors);
        $tagCount++;
    }
}
echo "Tags: $tagCount\n\n";

// =====================================================================
// 6. Checkins
// =====================================================================
echo "=== Checkins ===\n";
$restoCoords = $pdo->query("SELECT id, gps_latitude, gps_longitude FROM restaurants WHERE status='validated' AND gps_latitude IS NOT NULL AND gps_latitude != 0 LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$checkinCount = 0;
foreach ($restoCoords as $rc) {
    $n = rand(1, 3);
    for ($c = 0; $c < $n; $c++) {
        $uid = $allUserIds[array_rand($allUserIds)];
        $lat = (float)$rc['gps_latitude'] + (rand(-50, 50) / 100000);
        $lng = (float)$rc['gps_longitude'] + (rand(-50, 50) / 100000);
        $dist = rand(5, 180);
        if (run($pdo, "INSERT INTO checkins (user_id, restaurant_id, user_lat, user_lng, distance_m, points_earned, created_at) VALUES (:uid, :rid, :lat, :lng, :dist, 20, NOW() - INTERVAL :d DAY)", [
            ':uid' => $uid, ':rid' => $rc['id'], ':lat' => $lat, ':lng' => $lng, ':dist' => $dist, ':d' => rand(0, 15),
        ], $success, $errors)) {
            $checkinCount++;
        }
    }
}
echo "Checkins: $checkinCount\n\n";

// =====================================================================
// 7. Referrals
// =====================================================================
echo "=== Referrals ===\n";
if (count($newUserIds) >= 3 && $newUserIds[0] > 0 && $newUserIds[1] > 0) {
    run($pdo, "INSERT IGNORE INTO referrals (referrer_id, referred_id, status, points_awarded, created_at, completed_at) VALUES (:ref, :red, 'completed', 500, NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 18 DAY)", [
        ':ref' => 1, ':red' => $newUserIds[0],
    ], $success, $errors);
    run($pdo, "INSERT IGNORE INTO referrals (referrer_id, referred_id, status, points_awarded, created_at, completed_at) VALUES (:ref, :red, 'completed', 500, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 10 DAY)", [
        ':ref' => 1, ':red' => $newUserIds[1],
    ], $success, $errors);
    if ($newUserIds[4] > 0) {
        run($pdo, "INSERT IGNORE INTO referrals (referrer_id, referred_id, status, points_awarded, created_at) VALUES (:ref, :red, 'pending', 0, NOW() - INTERVAL 5 DAY)", [
            ':ref' => 5, ':red' => $newUserIds[4],
        ], $success, $errors);
    }
}
echo "Referrals done\n\n";

// =====================================================================
// 8. ACTIVITY FEED
// =====================================================================
echo "=== Activity Feed ===\n";
$actCount = 0;

// From real reviews
$recentReviews = $pdo->query("SELECT user_id, restaurant_id, note_globale, created_at FROM reviews ORDER BY created_at DESC LIMIT 25")->fetchAll(PDO::FETCH_ASSOC);
foreach ($recentReviews as $rev) {
    if (run($pdo, "INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'review', 'restaurant', :tid, :meta, :cat)", [
        ':uid' => $rev['user_id'], ':tid' => $rev['restaurant_id'],
        ':meta' => json_encode(['rating' => (int)$rev['note_globale']]),
        ':cat' => $rev['created_at'],
    ], $success, $errors)) $actCount++;
}

// Photo activities
foreach ($allUserIds as $uid) {
    if (rand(0, 1)) {
        $rid = $restoIds[array_rand($restoIds)];
        $days = rand(1, 20);
        $hours = rand(0, 23);
        if (run($pdo, "INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'photo', 'restaurant', :tid, :meta, NOW() - INTERVAL :d DAY - INTERVAL :h HOUR)", [
            ':uid' => $uid, ':tid' => $rid, ':meta' => json_encode(['count' => rand(1, 5)]),
            ':d' => $days, ':h' => $hours,
        ], $success, $errors)) $actCount++;
    }
}

// Checkin activities
foreach ($allUserIds as $uid) {
    if (rand(0, 2) > 0) {
        $rid = $restoIds[array_rand($restoIds)];
        if (run($pdo, "INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'checkin', 'restaurant', :tid, :meta, NOW() - INTERVAL :d DAY - INTERVAL :h HOUR)", [
            ':uid' => $uid, ':tid' => $rid, ':meta' => json_encode([]),
            ':d' => rand(0, 10), ':h' => rand(0, 23),
        ], $success, $errors)) $actCount++;
    }
}

// Collection activities
foreach ($collectionIds as $i => $cid) {
    if ($cid <= 0 || !isset($collections[$i])) continue;
    $rid = $restoIds[array_rand($restoIds)];
    if (run($pdo, "INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'collection', 'restaurant', :tid, :meta, NOW() - INTERVAL :d DAY)", [
        ':uid' => $collections[$i][0], ':tid' => $rid,
        ':meta' => json_encode(['collection_name' => $collections[$i][1]]),
        ':d' => rand(1, 30),
    ], $success, $errors)) $actCount++;
}

// Badge activities
$badgeNames = ['Gourmet', 'Connaisseur', 'Expert', 'Ambassadeur'];
foreach (array_slice($allUserIds, 0, 5) as $uid) {
    if (run($pdo, "INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'badge', 'restaurant', 0, :meta, NOW() - INTERVAL :d DAY)", [
        ':uid' => $uid, ':meta' => json_encode(['badge_name' => $badgeNames[array_rand($badgeNames)]]),
        ':d' => rand(1, 40),
    ], $success, $errors)) $actCount++;
}

// Reservation activities
foreach (array_slice($allUserIds, 2, 4) as $uid) {
    $rid = $restoIds[array_rand($restoIds)];
    if (run($pdo, "INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'reservation', 'restaurant', :tid, :meta, NOW() - INTERVAL :d DAY - INTERVAL :h HOUR)", [
        ':uid' => $uid, ':tid' => $rid, ':meta' => json_encode([]),
        ':d' => rand(0, 7), ':h' => rand(0, 12),
    ], $success, $errors)) $actCount++;
}

echo "Activity items: $actCount\n\n";

// =====================================================================
echo "========================================\n";
echo "SEED COMPLETE: $success OK, $errors errors\n";
echo "========================================\n";

$tables = ['users','activity_feed','restaurant_tips','user_follows','collections','collection_restaurants','checkins','referrals','review_tags','restaurant_context_tags'];
echo "\nFinal counts:\n";
foreach ($tables as $t) {
    echo "  $t: " . $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn() . "\n";
}
