<?php

namespace App\Helpers;

use App\Services\SpamDetector;

/**
 * Helper pour la modération automatique des avis
 */
class ReviewModerationHelper
{
    /**
     * Analyse et modère automatiquement un avis
     * Retourne les données enrichies avec score et statut
     */
    public static function autoModerate(array $reviewData, \PDO $db): array
    {
        $detector = new SpamDetector();
        
        // Analyse spam
        $analysis = $detector->analyze(
            $reviewData['message'] ?? '',
            $reviewData['note_globale'] ?? 3,
            $reviewData['author_name'] ?? ''
        );
        
        // Vérifier doublons (message identique dans les 7 derniers jours)
        $duplicateCheck = $db->prepare("
            SELECT id FROM reviews 
            WHERE message = ? 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            LIMIT 1
        ");
        $duplicateCheck->execute([$reviewData['message']]);
        
        if ($duplicateCheck->fetch()) {
            $analysis['penalties'][] = [
                'rule' => 'Message dupliqué',
                'penalty' => 30,
                'detail' => 'Message identique trouvé dans les 7 derniers jours'
            ];
            $analysis['score'] = max(0, $analysis['score'] - 30);
            $analysis['action'] = 'reject';
        }
        
        // Déterminer statut et métadonnées
        switch ($analysis['action']) {
            case 'approve':
                $reviewData['status'] = 'approved';
                $reviewData['moderated_by'] = 'ai';
                $reviewData['ai_rejected'] = 0;
                break;
                
            case 'reject':
                $reviewData['status'] = 'rejected';
                $reviewData['moderated_by'] = 'ai';
                $reviewData['ai_rejected'] = 1;
                break;
                
            case 'review':
            default:
                $reviewData['status'] = 'pending';
                $reviewData['moderated_by'] = 'manual';
                $reviewData['ai_rejected'] = 0;
                break;
        }
        
        // Ajouter données IA
        $reviewData['spam_score'] = $analysis['score'];
        $reviewData['spam_details'] = json_encode([
            'penalties' => $analysis['penalties'],
            'total_penalty' => $analysis['total_penalty'],
            'analyzed_at' => $analysis['analyzed_at'],
            'confidence' => $analysis['confidence']
        ], JSON_UNESCAPED_UNICODE);
        $reviewData['moderated_at'] = date('Y-m-d H:i:s');
        
        return $reviewData;
    }
    
    /**
     * Formate les détails spam pour affichage
     */
    public static function formatSpamDetails(string $jsonDetails): string
    {
        $details = json_decode($jsonDetails, true);
        
        if (empty($details['penalties'])) {
            return "✅ Aucun problème détecté";
        }
        
        $html = "<ul style='margin: 0; padding-left: 20px;'>";
        foreach ($details['penalties'] as $penalty) {
            $icon = $penalty['penalty'] > 15 ? '🔴' : '⚠️';
            $html .= "<li>{$icon} <strong>{$penalty['rule']}</strong>: {$penalty['detail']} <em>(-{$penalty['penalty']} pts)</em></li>";
        }
        $html .= "</ul>";
        
        return $html;
    }
}
