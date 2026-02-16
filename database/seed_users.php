<?php
$pdo = new PDO('mysql:host=localhost;dbname=lebonresto;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$hash = password_hash('TestPass123!', PASSWORD_BCRYPT);
$testUsers = [
    ['Amina', 'Benali', 'amina.b@test.com', 'aminab', 'AMINA001', 350, 'Connaisseur'],
    ['Karim', 'Hadj', 'karim.h@test.com', 'karimh', 'KARIM001', 520, 'Connaisseur'],
    ['Sofia', 'Mebarki', 'sofia.m@test.com', 'sofiam', 'SOFIA001', 180, 'Gourmet'],
    ['Yacine', 'Boudjema', 'yacine.b@test.com', 'yacineb', 'YACINE01', 90, 'Explorateur'],
    ['Lina', 'Rahmani', 'lina.r@test.com', 'linar', 'LINA0001', 720, 'Expert'],
    ['Omar', 'Ait-Said', 'omar.a@test.com', 'omara', 'OMAR0001', 260, 'Gourmet'],
    ['Nadia', 'Ferhat', 'nadia.f@test.com', 'nadiaf', 'NADIA001', 450, 'Connaisseur'],
    ['Mehdi', 'Kaci', 'mehdi.k@test.com', 'mehdik', 'MEHDI001', 140, 'Gourmet'],
];

foreach ($testUsers as $u) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $u[2]]);
    if ($stmt->fetchColumn()) {
        echo "SKIP: {$u[0]} exists\n";
        continue;
    }
    $days = rand(5, 60);
    $pdo->prepare("INSERT INTO users (prenom, nom, email, username, password_hash, is_admin, points, badge, referral_code, created_at) VALUES (:prenom, :nom, :email, :user, :pass, 0, :pts, :badge, :ref, NOW() - INTERVAL :days DAY)")
        ->execute([
            ':prenom' => $u[0], ':nom' => $u[1], ':email' => $u[2], ':user' => $u[3],
            ':pass' => $hash, ':pts' => $u[5], ':badge' => $u[6], ':ref' => $u[4], ':days' => $days,
        ]);
    echo "OK: {$u[0]} {$u[1]} id=" . $pdo->lastInsertId() . " ({$u[5]}pts, {$u[6]})\n";
}

// Add some reviews from new users to populate the feed
$newUsers = $pdo->query("SELECT id FROM users WHERE email LIKE '%@test.com'")->fetchAll(PDO::FETCH_COLUMN);
$restos = $pdo->query("SELECT id FROM restaurants WHERE status='validated' ORDER BY RAND() LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);
echo "\nAdding reviews from test users...\n";
$reviewCount = 0;
foreach ($newUsers as $uid) {
    $n = rand(2, 5);
    shuffle($restos);
    for ($i = 0; $i < $n; $i++) {
        $rid = $restos[$i];
        $note = rand(3, 5);
        $messages = [
            "Tres bon restaurant, je recommande vivement! Le service etait impeccable.",
            "Cuisine excellente, cadre agreable. Un peu bruyant le weekend mais ca vaut le detour.",
            "J'ai adore! Les plats sont bien presentes et delicieux. Prix un peu eleves mais justifies.",
            "Bonne experience globale. Le personnel est accueillant et les portions genereuses.",
            "Restaurant sympa pour une sortie entre amis. La carte est variee et les prix raisonnables.",
            "Cuisine authentique et savoureuse. L'ambiance est chaleureuse, on s'y sent bien.",
            "Excellent rapport qualite-prix. Je reviendrai sans hesiter!",
            "Un vrai coup de coeur. Le chef maitrise son art, chaque plat est une decouverte.",
            "Tres satisfait de ma visite. Service rapide, plats copieux et bien assaisonnes.",
            "Belle decouverte! Le menu change regulierement, toujours des surprises agreables.",
        ];
        $msg = $messages[array_rand($messages)];
        $days = rand(1, 45);
        try {
            $pdo->prepare("INSERT INTO reviews (user_id, restaurant_id, note_globale, note_cuisine, note_service, note_ambiance, note_rapport_qualite_prix, message, status, created_at) VALUES (:uid, :rid, :note, :nc, :ns, :na, :nq, :msg, 'approved', NOW() - INTERVAL :d DAY)")
                ->execute([
                    ':uid' => $uid, ':rid' => $rid, ':note' => $note,
                    ':nc' => rand(3, 5), ':ns' => rand(3, 5), ':na' => rand(3, 5), ':nq' => rand(3, 5),
                    ':msg' => $msg, ':d' => $days,
                ]);
            $reviewCount++;

            // Add activity feed entry
            $pdo->prepare("INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'review', 'restaurant', :rid, :meta, NOW() - INTERVAL :d DAY)")
                ->execute([':uid' => $uid, ':rid' => $rid, ':meta' => json_encode(['rating' => $note]), ':d' => $days]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "ERR review: " . $e->getMessage() . "\n";
            }
        }
    }
}
echo "Reviews added: $reviewCount\n";

// Referrals (user 1 referred Amina and Karim)
echo "\nReferrals...\n";
$aminaId = $pdo->query("SELECT id FROM users WHERE email='amina.b@test.com'")->fetchColumn();
$karimId = $pdo->query("SELECT id FROM users WHERE email='karim.h@test.com'")->fetchColumn();
$linaId = $pdo->query("SELECT id FROM users WHERE email='lina.r@test.com'")->fetchColumn();
if ($aminaId) {
    try {
        $pdo->prepare("INSERT INTO referrals (referrer_id, referred_id, status, points_awarded, created_at, completed_at) VALUES (:ref, :red, 'completed', 500, NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 18 DAY)")
            ->execute([':ref' => 1, ':red' => $aminaId]);
        echo "OK: user 1 -> Amina (completed)\n";
    } catch (PDOException $e) { echo "SKIP ref\n"; }
}
if ($karimId) {
    try {
        $pdo->prepare("INSERT INTO referrals (referrer_id, referred_id, status, points_awarded, created_at, completed_at) VALUES (:ref, :red, 'completed', 500, NOW() - INTERVAL 15 DAY, NOW() - INTERVAL 10 DAY)")
            ->execute([':ref' => 1, ':red' => $karimId]);
        echo "OK: user 1 -> Karim (completed)\n";
    } catch (PDOException $e) { echo "SKIP ref\n"; }
}
if ($linaId) {
    try {
        $pdo->prepare("INSERT INTO referrals (referrer_id, referred_id, status, points_awarded, created_at) VALUES (:ref, :red, 'pending', 0, NOW() - INTERVAL 5 DAY)")
            ->execute([':ref' => 5, ':red' => $linaId]);
        echo "OK: user 5 -> Lina (pending)\n";
    } catch (PDOException $e) { echo "SKIP ref\n"; }
}

// More follows from new users
echo "\nAdding follows from new users...\n";
$allIds = array_merge([1,2,3,4,5], $newUsers);
foreach ($newUsers as $uid) {
    $others = array_values(array_diff($allIds, [$uid]));
    shuffle($others);
    foreach (array_slice($others, 0, rand(3, 6)) as $fid) {
        try {
            $pdo->prepare("INSERT IGNORE INTO user_follows (follower_id, followed_id, created_at) VALUES (:f, :t, NOW() - INTERVAL :d DAY)")
                ->execute([':f' => $uid, ':t' => $fid, ':d' => rand(1, 20)]);
        } catch (PDOException $e) {}
    }
}

echo "\n=== DONE ===\n";
$tables = ['users','activity_feed','restaurant_tips','user_follows','collections','collection_restaurants','checkins','referrals','review_tags','restaurant_context_tags','reviews'];
foreach ($tables as $t) {
    echo "  $t: " . $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn() . "\n";
}
