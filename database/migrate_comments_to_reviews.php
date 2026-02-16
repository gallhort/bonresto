<?php
/**
 * Migration: comments (legacy Google reviews) → reviews
 *
 * Migre les 934 avis Google de la table `comments` vers la table `reviews`.
 *
 * Mapping:
 *   comments.nom       → JOIN restaurants.nom → reviews.restaurant_id
 *   comments.user      → reviews.author_name
 *   comments.message   → reviews.message
 *   comments.title     → reviews.title
 *   comments.note      → reviews.note_globale (déjà /5)
 *   comments.food      → reviews.note_nourriture
 *   comments.service   → reviews.note_service
 *   comments.location  → reviews.note_ambiance
 *   comments.price     → reviews.note_prix
 *   comments.useful    → reviews.votes_utiles
 *   source             → 'google'
 *   status             → 'approved'
 *   created_at         → '2025-12-01 00:00:00' (date fixe, pas de timestamp dans comments)
 *
 * Usage: php database/migrate_comments_to_reviews.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv ?? []);

$pdo = new PDO('mysql:host=localhost;dbname=lebonresto;charset=utf8mb4', 'sam', '123');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

echo "=== MIGRATION comments → reviews ===" . PHP_EOL;
echo $dryRun ? "[MODE DRY-RUN — aucune modification]" . PHP_EOL : "[MODE REEL — les données seront insérées]" . PHP_EOL;
echo PHP_EOL;

// 1. Compter les avis Google (source='google' ou source='')
$totalGoogle = $pdo->query("SELECT COUNT(*) FROM comments WHERE source IN ('google', '')")->fetchColumn();
echo "Avis Google dans comments: {$totalGoogle}" . PHP_EOL;

// 2. Vérifier combien matchent un restaurant
$matchCount = $pdo->query("
    SELECT COUNT(*)
    FROM comments c
    INNER JOIN restaurants r ON r.nom COLLATE utf8mb4_unicode_ci = c.nom COLLATE utf8mb4_unicode_ci
    WHERE c.source IN ('google', '')
")->fetchColumn();
echo "Matchent un restaurant: {$matchCount}" . PHP_EOL;

$noMatchCount = $totalGoogle - $matchCount;
echo "Sans match (seront ignorés): {$noMatchCount}" . PHP_EOL;

// 3. Vérifier les doublons déjà migrés
$alreadyMigrated = $pdo->query("SELECT COUNT(*) FROM reviews WHERE source = 'google'")->fetchColumn();
echo "Avis Google déjà dans reviews: {$alreadyMigrated}" . PHP_EOL;

if ($alreadyMigrated > 0 && !$dryRun) {
    echo PHP_EOL . "ATTENTION: Il y a déjà {$alreadyMigrated} avis Google dans reviews." . PHP_EOL;
    echo "Voulez-vous continuer ? Les doublons (même restaurant + même auteur + même message) seront ignorés." . PHP_EOL;
    echo "Tapez 'oui' pour continuer: ";
    $confirm = trim(fgets(STDIN));
    if ($confirm !== 'oui') {
        echo "Migration annulée." . PHP_EOL;
        exit(0);
    }
}

// 4. Charger les comments Google avec leur restaurant_id
$stmt = $pdo->query("
    SELECT
        c.comment_id,
        c.message,
        c.user,
        c.nom as resto_nom,
        c.note,
        c.title,
        c.food,
        c.service,
        c.location,
        c.price,
        c.useful,
        r.id as restaurant_id
    FROM comments c
    INNER JOIN restaurants r ON r.nom COLLATE utf8mb4_unicode_ci = c.nom COLLATE utf8mb4_unicode_ci
    WHERE c.source IN ('google', '')
    ORDER BY c.comment_id
");

$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Comments à migrer: " . count($comments) . PHP_EOL . PHP_EOL;

if ($dryRun) {
    echo "--- Aperçu des 10 premiers ---" . PHP_EOL;
    foreach (array_slice($comments, 0, 10) as $c) {
        echo "  [{$c['comment_id']}] {$c['resto_nom']} (id={$c['restaurant_id']}) | {$c['user']} | {$c['note']}/5 | "
             . mb_substr($c['title'] ?? '', 0, 50) . PHP_EOL;
    }
    echo PHP_EOL;
}

// 5. Préparer l'insertion
$insertStmt = $pdo->prepare("
    INSERT INTO reviews (
        restaurant_id, user_id, author_name, title, message,
        note_globale, note_nourriture, note_service, note_ambiance, note_prix,
        status, source, votes_utiles, created_at, updated_at,
        spam_score, moderated_by
    ) VALUES (
        :restaurant_id, NULL, :author_name, :title, :message,
        :note_globale, :note_nourriture, :note_service, :note_ambiance, :note_prix,
        'approved', 'google', :votes_utiles, :created_at, :updated_at,
        0, 'manual'
    )
");

// 6. Préparer la détection de doublons (positional params car LEFT() avec named param pose problème en PDO natif)
$dupeCheck = $pdo->prepare("
    SELECT COUNT(*) FROM reviews
    WHERE restaurant_id = ?
    AND author_name = ?
    AND source = 'google'
    AND LEFT(message, 100) = ?
");

$migrated = 0;
$skippedDupes = 0;
$skippedEmpty = 0;
$errors = 0;

$pdo->beginTransaction();

try {
    foreach ($comments as $c) {
        // Skip si message vide ET title vide
        $msg = trim($c['message'] ?? '');
        $title = trim($c['title'] ?? '');
        if ($msg === '' && $title === '') {
            $skippedEmpty++;
            continue;
        }

        // Si message vide mais title existe, utiliser le title comme message
        if ($msg === '') {
            $msg = $title;
        }

        // Vérifier doublon
        $dupeCheck->execute([$c['restaurant_id'], $c['user'], mb_substr($msg, 0, 100)]);
        if ($dupeCheck->fetchColumn() > 0) {
            $skippedDupes++;
            continue;
        }

        // Clamp notes à [1, 5]
        $noteGlobale = max(1, min(5, (float)$c['note']));
        $noteFood = max(1, min(5, (float)$c['food']));
        $noteService = max(1, min(5, (float)$c['service']));
        $noteAmbiance = max(1, min(5, (float)$c['location']));
        $notePrix = max(1, min(5, (float)$c['price']));

        if (!$dryRun) {
            $insertStmt->execute([
                ':restaurant_id' => $c['restaurant_id'],
                ':author_name' => $c['user'],
                ':title' => mb_substr($title, 0, 200),
                ':message' => $msg,
                ':note_globale' => $noteGlobale,
                ':note_nourriture' => $noteFood,
                ':note_service' => $noteService,
                ':note_ambiance' => $noteAmbiance,
                ':note_prix' => $notePrix,
                ':votes_utiles' => max(0, (int)$c['useful']),
                ':created_at' => '2025-12-01 00:00:00',
                ':updated_at' => '2025-12-01 00:00:00'
            ]);
        }

        $migrated++;
    }

    if (!$dryRun) {
        $pdo->commit();
    } else {
        $pdo->rollBack();
    }

} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERREUR: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo "=== RESULTAT ===" . PHP_EOL;
echo "Migrés: {$migrated}" . PHP_EOL;
echo "Doublons ignorés: {$skippedDupes}" . PHP_EOL;
echo "Vides ignorés: {$skippedEmpty}" . PHP_EOL;
echo "Erreurs: {$errors}" . PHP_EOL;

if (!$dryRun) {
    // Stats post-migration
    echo PHP_EOL . "=== STATS POST-MIGRATION ===" . PHP_EOL;
    echo "Total reviews: " . $pdo->query("SELECT COUNT(*) FROM reviews")->fetchColumn() . PHP_EOL;
    echo "Reviews Google: " . $pdo->query("SELECT COUNT(*) FROM reviews WHERE source = 'google'")->fetchColumn() . PHP_EOL;
    echo "Reviews site: " . $pdo->query("SELECT COUNT(*) FROM reviews WHERE source = 'site'")->fetchColumn() . PHP_EOL;
    echo "Restos avec au moins 1 avis: " . $pdo->query("SELECT COUNT(DISTINCT restaurant_id) FROM reviews")->fetchColumn() . PHP_EOL;
    echo "Note moyenne globale: " . $pdo->query("SELECT ROUND(AVG(note_globale), 2) FROM reviews")->fetchColumn() . PHP_EOL;
} else {
    echo PHP_EOL . "[Dry-run terminé — rien n'a été modifié]" . PHP_EOL;
}
