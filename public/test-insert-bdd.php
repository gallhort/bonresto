<?php
/**
 * TEST DIRECT BDD - Vérifier si l'insertion fonctionne
 * 
 * Place dans /public/test-insert.php
 * Visite : http://tonsite.com/test-insert.php
 */

require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

echo "<h1>🧪 Test Insertion BDD</h1>";
echo "<style>body{font-family:sans-serif;padding:20px;}pre{background:#f5f5f5;padding:15px;}</style>";

try {
    $db = Database::getInstance()->getPdo();
    
    echo "<h2>✅ Connexion BDD OK</h2>";
    
    // Test 1 : Vérifier colonnes
    echo "<h3>Test 1 : Vérifier colonnes reviews</h3>";
    
    $columns = $db->query("SHOW COLUMNS FROM reviews")->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredCols = ['spam_score', 'spam_details', 'moderated_by', 'moderated_at', 'ai_rejected'];
    $missingCols = [];
    
    $existingCols = array_column($columns, 'Field');
    
    foreach ($requiredCols as $col) {
        if (!in_array($col, $existingCols)) {
            $missingCols[] = $col;
        }
    }
    
    if (empty($missingCols)) {
        echo "✅ Toutes les colonnes IA sont présentes<br>";
        echo "<pre>" . implode(", ", $requiredCols) . "</pre>";
    } else {
        echo "❌ <strong>COLONNES MANQUANTES :</strong><br>";
        echo "<pre style='color:red;'>" . implode(", ", $missingCols) . "</pre>";
        echo "<br><strong>Exécute cette migration :</strong><br>";
        echo "<textarea style='width:100%;height:150px;'>";
        echo "ALTER TABLE reviews ADD COLUMN spam_score INT DEFAULT 100;\n";
        echo "ALTER TABLE reviews ADD COLUMN spam_details TEXT;\n";
        echo "ALTER TABLE reviews ADD COLUMN moderated_by ENUM('manual', 'ai') DEFAULT 'manual';\n";
        echo "ALTER TABLE reviews ADD COLUMN moderated_at TIMESTAMP NULL;\n";
        echo "ALTER TABLE reviews ADD COLUMN ai_rejected TINYINT(1) DEFAULT 0;";
        echo "</textarea>";
        die();
    }
    
    // Test 2 : Insertion minimale
    echo "<h3>Test 2 : Insertion minimale (sans IA)</h3>";
    
    $testData = [
        'restaurant_id' => 1,
        'user_id' => 1,
        'author_name' => 'Test User',
        'title' => 'Test',
        'message' => 'Message de test pour vérifier insertion',
        'note_globale' => 5.0,
        'note_nourriture' => null,
        'note_service' => null,
        'note_ambiance' => null,
        'note_prix' => null,
        'status' => 'pending',
        'source' => 'site', // Avis du site
        'spam_score' => 100,
        'spam_details' => null,
        'moderated_by' => 'manual',
        'moderated_at' => null,
        'ai_rejected' => 0
    ];
    
    $sql = "INSERT INTO reviews 
            (restaurant_id, user_id, author_name, title, message, 
             note_globale, note_nourriture, note_service, note_ambiance, note_prix,
             status, source,
             spam_score, spam_details, moderated_by, moderated_at, ai_rejected,
             created_at)
            VALUES 
            (:restaurant_id, :user_id, :author_name, :title, :message,
             :note_globale, :note_nourriture, :note_service, :note_ambiance, :note_prix,
             :status, :source,
             :spam_score, :spam_details, :moderated_by, :moderated_at, :ai_rejected,
             NOW())";
    
    echo "<strong>Requête SQL :</strong><br>";
    echo "<pre style='font-size:11px;'>" . htmlspecialchars($sql) . "</pre>";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute($testData);
    
    if ($result) {
        $insertId = $db->lastInsertId();
        echo "✅ <strong>Insertion réussie ! ID = {$insertId}</strong><br><br>";
        
        // Vérifier données insérées
        $check = $db->query("SELECT * FROM reviews WHERE id = {$insertId}")->fetch(PDO::FETCH_ASSOC);
        echo "<strong>Données insérées :</strong><br>";
        echo "<pre>" . print_r($check, true) . "</pre>";
        
        // Nettoyer
        echo "<br><strong>Nettoyage (suppression test) :</strong><br>";
        $db->exec("DELETE FROM reviews WHERE id = {$insertId}");
        echo "✅ Test supprimé<br>";
        
    } else {
        echo "❌ <strong>Erreur insertion</strong><br>";
        print_r($stmt->errorInfo());
    }
    
    // Test 3 : Insertion AVEC données IA
    echo "<h3>Test 3 : Insertion AVEC données IA complètes</h3>";
    
    $testDataAI = [
        'restaurant_id' => 1,
        'user_id' => 1,
        'author_name' => 'Bot Spam',
        'title' => 'PROMO',
        'message' => 'SUPER PROMO !!! www.spam.com',
        'note_globale' => 5.0,
        'note_nourriture' => null,
        'note_service' => null,
        'note_ambiance' => null,
        'note_prix' => null,
        'status' => 'rejected',
        'source' => 'site', // Avis du site
        'spam_score' => 32,
        'spam_details' => json_encode([
            'penalties' => [
                ['rule' => 'Mots spam', 'penalty' => 35, 'detail' => 'promo, www']
            ],
            'total_penalty' => 68
        ]),
        'moderated_by' => 'ai',
        'moderated_at' => date('Y-m-d H:i:s'),
        'ai_rejected' => 1
    ];
    
    $stmt2 = $db->prepare($sql);
    $result2 = $stmt2->execute($testDataAI);
    
    if ($result2) {
        $insertId2 = $db->lastInsertId();
        echo "✅ <strong>Insertion IA réussie ! ID = {$insertId2}</strong><br><br>";
        
        $check2 = $db->query("SELECT id, message, status, spam_score, ai_rejected, moderated_by FROM reviews WHERE id = {$insertId2}")->fetch(PDO::FETCH_ASSOC);
        echo "<strong>Données insérées :</strong><br>";
        echo "<pre>" . print_r($check2, true) . "</pre>";
        
        echo "<br><strong>Nettoyage :</strong><br>";
        $db->exec("DELETE FROM reviews WHERE id = {$insertId2}");
        echo "✅ Test supprimé<br>";
        
    } else {
        echo "❌ <strong>Erreur insertion IA</strong><br>";
        echo "<pre style='color:red;'>" . print_r($stmt2->errorInfo(), true) . "</pre>";
    }
    
    echo "<hr>";
    echo "<h2>🎉 RÉSUMÉ</h2>";
    echo "<p>Si TOUS les tests sont ✅, la BDD est OK.</p>";
    echo "<p>Le problème vient du ReviewController ou du ReviewModel.</p>";
    
} catch (Exception $e) {
    echo "❌ <strong>ERREUR FATALE :</strong><br>";
    echo "<pre style='color:red;'>" . $e->getMessage() . "\n\n" . $e->getTraceAsString() . "</pre>";
}