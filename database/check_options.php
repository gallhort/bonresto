<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=lebonresto;charset=utf8mb4', 'root', '');

echo "=== STRUCTURE restaurant_options ===\n";
$cols = $pdo->query("DESCRIBE restaurant_options")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";

echo "\n=== TYPES D'OPTIONS EXISTANTS ===\n";
$types = $pdo->query("SELECT option_type, COUNT(*) as cnt FROM restaurant_options GROUP BY option_type ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($types as $t) echo "  {$t['option_type']}: {$t['cnt']}\n";

echo "\n=== COLONNES restaurants liées aux services ===\n";
$cols2 = $pdo->query("DESCRIBE restaurants")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols2 as $c) {
    if (in_array($c['Field'], ['reservations_enabled','menu_enabled','verified_halal','google_place_id'])) {
        echo "  {$c['Field']} ({$c['Type']})\n";
    }
}
