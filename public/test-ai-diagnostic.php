<?php
/**
 * SCRIPT DIAGNOSTIC - Tester l'IA manuellement
 * 
 * Utilisation :
 * 1. Place ce fichier dans /public/test-ai.php
 * 2. Va sur http://tonsite.com/test-ai.php
 * 3. Lis les erreurs affichées
 */

// Bootstrap minimal
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Services/SpamDetector.php';
require_once __DIR__ . '/../app/Helpers/ReviewModerationHelper.php';

use App\Core\Database;
use App\Services\SpamDetector;
use App\Helpers\ReviewModerationHelper;

echo "<h1>🔍 Diagnostic Modération IA</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;}pre{background:#f5f5f5;padding:15px;border-radius:8px;}</style>";

// Test 1 : Classes existent ?
echo "<h2>✅ Test 1 : Chargement des classes</h2>";

if (class_exists('App\Services\SpamDetector')) {
    echo "✅ SpamDetector trouvé<br>";
} else {
    echo "❌ SpamDetector INTROUVABLE<br>";
}

if (class_exists('App\Helpers\ReviewModerationHelper')) {
    echo "✅ ReviewModerationHelper trouvé<br>";
} else {
    echo "❌ ReviewModerationHelper INTROUVABLE<br>";
}

// Test 2 : Analyse spam
echo "<h2>✅ Test 2 : Analyse d'un avis</h2>";

try {
    $detector = new SpamDetector();
    
    // Avis de test
    $testMessage = "SUPER PROMO !!! Visitez www.spam.com CLIQUEZ ICI !!!!!!";
    $testRating = 5;
    $testAuthor = "test123";
    
    echo "<strong>Message testé :</strong> " . htmlspecialchars($testMessage) . "<br><br>";
    
    $analysis = $detector->analyze($testMessage, $testRating, $testAuthor);
    
    echo "<strong>Résultat :</strong><br>";
    echo "<pre>" . print_r($analysis, true) . "</pre>";
    
    echo "<h3>Score : " . $analysis['score'] . "/100</h3>";
    echo "<h3>Action : " . $analysis['action'] . "</h3>";
    
    if (!empty($analysis['penalties'])) {
        echo "<h4>Pénalités détectées :</h4><ul>";
        foreach ($analysis['penalties'] as $p) {
            echo "<li><strong>" . $p['rule'] . "</strong>: " . $p['detail'] . " (-" . $p['penalty'] . " pts)</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>ERREUR lors de l'analyse :</strong><br>";
    echo "<pre style='color:red;'>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
}

// Test 3 : Modération complète
echo "<h2>✅ Test 3 : Modération complète</h2>";

try {
    $db = Database::getInstance()->getPdo(); // CORRECTION ICI
    
    $reviewData = [
        'restaurant_id' => 1,
        'user_id' => 1,
        'message' => "SUPER PROMO !!! www.spam.com",
        'note_globale' => 5,
        'author_name' => 'bot123',
        'status' => 'pending'
    ];
    
    echo "<strong>Données avant modération :</strong><br>";
    echo "<pre>" . print_r($reviewData, true) . "</pre>";
    
    $moderated = ReviewModerationHelper::autoModerate($reviewData, $db);
    
    echo "<strong>Données APRÈS modération :</strong><br>";
    echo "<pre>" . print_r($moderated, true) . "</pre>";
    
    echo "<h3>Statut final : " . $moderated['status'] . "</h3>";
    echo "<h3>Score : " . $moderated['spam_score'] . "/100</h3>";
    
} catch (Exception $e) {
    echo "❌ <strong>ERREUR lors de la modération :</strong><br>";
    echo "<pre style='color:red;'>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p><em>Si tout est ✅, l'IA fonctionne correctement !</em></p>";