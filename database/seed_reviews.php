<?php
$pdo = new PDO('mysql:host=localhost;dbname=lebonresto;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$newUsers = $pdo->query("SELECT id FROM users WHERE email LIKE '%@test.com'")->fetchAll(PDO::FETCH_COLUMN);
$restos = $pdo->query("SELECT id FROM restaurants WHERE status='validated' ORDER BY RAND() LIMIT 30")->fetchAll(PDO::FETCH_COLUMN);

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
    "Endroit parfait pour un dejeuner d'affaires. Cuisine raffinee et service discret.",
    "La terrasse est magnifique avec vue sur la mer. Plats frais et bien prepares.",
    "On y retourne chaque semaine tellement c'est bon. Le personnel nous connait maintenant!",
    "Premiere visite et deja conquis. Le tajine etait exceptionnel.",
    "Ambiance familiale tres agreable. Les enfants ont adore le menu qui leur etait dedie.",
];

$titles = [
    "Excellente experience!", "Tres bon!", "A recommander", "Superbe decouverte",
    "On y reviendra", "Coup de coeur", "Tres satisfait", "Belle surprise",
    "Restaurant de qualite", "Un sans-faute", "Agreable moment", "Delicieux!",
];

echo "Adding reviews from " . count($newUsers) . " test users...\n";
$reviewCount = 0;
foreach ($newUsers as $uid) {
    $n = rand(3, 6);
    shuffle($restos);
    for ($i = 0; $i < $n && $i < count($restos); $i++) {
        $rid = $restos[$i];
        $note = rand(3, 5);
        $msg = $messages[array_rand($messages)];
        $title = $titles[array_rand($titles)];
        $days = rand(1, 45);
        try {
            $pdo->prepare("INSERT INTO reviews (user_id, restaurant_id, note_globale, note_nourriture, note_service, note_ambiance, note_prix, title, message, status, source, created_at) VALUES (:uid, :rid, :note, :nn, :ns, :na, :np, :title, :msg, 'approved', 'site', NOW() - INTERVAL :d DAY)")
                ->execute([
                    ':uid' => $uid, ':rid' => $rid, ':note' => $note,
                    ':nn' => rand(3, 5), ':ns' => rand(3, 5), ':na' => rand(3, 5), ':np' => rand(3, 5),
                    ':title' => $title, ':msg' => $msg, ':d' => $days,
                ]);
            $reviewId = (int)$pdo->lastInsertId();
            $reviewCount++;

            // Activity feed
            $pdo->prepare("INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata, created_at) VALUES (:uid, 'review', 'restaurant', :rid, :meta, NOW() - INTERVAL :d DAY)")
                ->execute([':uid' => $uid, ':rid' => $rid, ':meta' => json_encode(['rating' => $note]), ':d' => $days]);

            // Add tags to some reviews
            if (rand(0, 2) > 0) {
                $tagTypes = ['romantique','familial','business','terrasse','vue','calme','anime','bon_rapport','grandes_portions','service_rapide','halal_certifie','livraison'];
                shuffle($tagTypes);
                $tags = array_slice($tagTypes, 0, rand(1, 3));
                foreach ($tags as $tag) {
                    try {
                        $pdo->prepare("INSERT IGNORE INTO review_tags (review_id, tag) VALUES (:rid, :tag)")
                            ->execute([':rid' => $reviewId, ':tag' => $tag]);
                        $pdo->prepare("INSERT INTO restaurant_context_tags (restaurant_id, tag, vote_count) VALUES (:rid, :tag, 1) ON DUPLICATE KEY UPDATE vote_count = vote_count + 1")
                            ->execute([':rid' => $rid, ':tag' => $tag]);
                    } catch (PDOException $e) {}
                }
            }

            echo ".";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false) {
                echo "\nERR: " . $e->getMessage() . "\n";
            }
        }
    }
}
echo "\nReviews added: $reviewCount\n";

// Update nb_avis and note_moyenne for affected restaurants
echo "Updating restaurant stats...\n";
$pdo->exec("UPDATE restaurants r SET
    nb_avis = (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved'),
    note_moyenne = (SELECT ROUND(AVG(rv.note_globale), 2) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved')
");

echo "\n=== DONE ===\n";
echo "  reviews: " . $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn() . "\n";
echo "  activity_feed: " . $pdo->query("SELECT COUNT(*) FROM activity_feed")->fetchColumn() . "\n";
echo "  review_tags: " . $pdo->query("SELECT COUNT(*) FROM review_tags")->fetchColumn() . "\n";
