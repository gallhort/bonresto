<?php

namespace App\Services;

/**
 * Détecteur de spam pour avis - Version PHP pure (gratuit)
 * Analyse 10 critères et calcule un score 0-100
 */
class SpamDetector
{
    /**
     * Mots-clés spam courants
     */
    /**
     * Mots-clés spam (pas de mots légitimes comme "prix" ou "réduction")
     */
    private const SPAM_KEYWORDS = [
        'cliquez', 'click', 'visitez', 'www.', 'http', 'https', '.com', '.fr',
        'viagra', 'casino', 'poker', 'bitcoi', 'crypto',
        'argent facile', 'offre limitée', 'urgent', 'félicitation',
        'cashback', 'gagnez de l\'argent', 'prix incroyable',
        'telegram', 'whatsapp groupe',
    ];
    
    /**
     * Analyse un avis et retourne score + détails
     */
    public function analyze(string $message, float $rating, string $authorName = '', array $context = []): array
    {
        $penalties = [];
        $totalPenalty = 0;
        
        // 1. LONGUEUR DU TEXTE (poids: 20%)
        $length = mb_strlen($message);
        if ($length < 10) {
            $penalty = 25;
            $penalties[] = [
                'rule' => 'Texte trop court',
                'penalty' => $penalty,
                'detail' => "Message de {$length} caractères (minimum recommandé: 20)"
            ];
            $totalPenalty += $penalty;
        } elseif ($length < 20) {
            $penalty = 10;
            $penalties[] = [
                'rule' => 'Texte court',
                'penalty' => $penalty,
                'detail' => "Message de {$length} caractères (peu informatif)"
            ];
            $totalPenalty += $penalty;
        }
        
        // 2. MOTS-CLÉS SPAM (poids: 30%)
        $spamCount = 0;
        $foundKeywords = [];
        $messageLower = mb_strtolower($message);
        
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (stripos($messageLower, $keyword) !== false) {
                $spamCount++;
                $foundKeywords[] = $keyword;
            }
        }
        
        if ($spamCount > 0) {
            $penalty = min(35, $spamCount * 12);
            $penalties[] = [
                'rule' => 'Mots-clés suspects',
                'penalty' => $penalty,
                'detail' => "Mots détectés: " . implode(', ', array_slice($foundKeywords, 0, 5))
            ];
            $totalPenalty += $penalty;
        }
        
        // 3. MAJUSCULES EXCESSIVES (poids: 15%)
        $totalLetters = preg_match_all('/\p{L}/u', $message);
        $upperCount = preg_match_all('/\p{Lu}/u', $message);
        
        if ($totalLetters > 0) {
            $upperRatio = ($upperCount / $totalLetters) * 100;
            if ($upperRatio > 60) {
                $penalty = 20;
                $penalties[] = [
                    'rule' => 'Trop de MAJUSCULES',
                    'penalty' => $penalty,
                    'detail' => round($upperRatio) . "% de majuscules (limite: 60%)"
                ];
                $totalPenalty += $penalty;
            } elseif ($upperRatio > 40) {
                $penalty = 10;
                $penalties[] = [
                    'rule' => 'Beaucoup de MAJUSCULES',
                    'penalty' => $penalty,
                    'detail' => round($upperRatio) . "% de majuscules"
                ];
                $totalPenalty += $penalty;
            }
        }
        
        // 4. RÉPÉTITIONS (poids: 10%)
        $words = preg_split('/\s+/', $messageLower);
        $wordCount = array_count_values($words);
        foreach ($wordCount as $word => $count) {
            if (mb_strlen($word) > 3 && $count >= 4) {
                $penalty = min(15, $count * 3);
                $penalties[] = [
                    'rule' => 'Répétitions excessives',
                    'penalty' => $penalty,
                    'detail' => "Mot '{$word}' répété {$count} fois"
                ];
                $totalPenalty += $penalty;
                break;
            }
        }
        
        // 5. PONCTUATION EXCESSIVE (poids: 10%)
        $exclamCount = substr_count($message, '!');
        $questionCount = substr_count($message, '?');
        
        if ($exclamCount > 3 || $questionCount > 3) {
            $penalty = min(15, ($exclamCount + $questionCount) * 2);
            $penalties[] = [
                'rule' => 'Ponctuation excessive',
                'penalty' => $penalty,
                'detail' => "{$exclamCount} points d'exclamation, {$questionCount} points d'interrogation"
            ];
            $totalPenalty += $penalty;
        }
        
        // 6. CARACTÈRES SPÉCIAUX SUSPECTS (poids: 5%)
        if (preg_match('/[₿฿€$£¥₽]/', $message)) {
            $penalty = 10;
            $penalties[] = [
                'rule' => 'Symboles monétaires',
                'penalty' => $penalty,
                'detail' => "Symboles de devises détectés"
            ];
            $totalPenalty += $penalty;
        }
        
        // 7. INCOHÉRENCE NOTE/TEXTE (poids: 5%)
        $positiveWords = ['excellent', 'super', 'génial', 'parfait', 'top', 'recommande', 'meilleur'];
        $negativeWords = ['nul', 'mauvais', 'horrible', 'déçu', 'décevant', 'éviter', 'catastrophe'];
        
        $hasPositive = false;
        $hasNegative = false;
        
        foreach ($positiveWords as $word) {
            if (stripos($messageLower, $word) !== false) {
                $hasPositive = true;
                break;
            }
        }
        
        foreach ($negativeWords as $word) {
            if (stripos($messageLower, $word) !== false) {
                $hasNegative = true;
                break;
            }
        }
        
        // Note basse (1-2) + texte positif = suspect
        if ($rating <= 2 && $hasPositive && !$hasNegative) {
            $penalty = 12;
            $penalties[] = [
                'rule' => 'Incohérence note/commentaire',
                'penalty' => $penalty,
                'detail' => "Note {$rating}/5 mais texte positif (suspect)"
            ];
            $totalPenalty += $penalty;
        }
        
        // Note haute (4-5) + texte négatif = suspect
        if ($rating >= 4 && $hasNegative && !$hasPositive) {
            $penalty = 12;
            $penalties[] = [
                'rule' => 'Incohérence note/commentaire',
                'penalty' => $penalty,
                'detail' => "Note {$rating}/5 mais texte négatif (suspect)"
            ];
            $totalPenalty += $penalty;
        }
        
        // 8. NOM AUTEUR SUSPECT (poids: 5%)
        if ($authorName) {
            $suspectPatterns = [
                '/^(admin|test|bot|spam|promo|guest)/i',
                '/\d{3,}/', // 3 chiffres ou plus
                '/^[a-z]{1,3}$/i', // 1-3 lettres seulement
            ];
            
            foreach ($suspectPatterns as $pattern) {
                if (preg_match($pattern, $authorName)) {
                    $penalty = 8;
                    $penalties[] = [
                        'rule' => 'Nom auteur suspect',
                        'penalty' => $penalty,
                        'detail' => "Nom '{$authorName}' ressemble à un bot"
                    ];
                    $totalPenalty += $penalty;
                    break;
                }
            }
        }
        
        // 9. EMOJIS EXCESSIFS (poids: 5%)
        $emojiCount = preg_match_all('/[\x{1F300}-\x{1F9FF}]/u', $message);
        if ($emojiCount > 5) {
            $penalty = 8;
            $penalties[] = [
                'rule' => 'Trop d\'emojis',
                'penalty' => $penalty,
                'detail' => "{$emojiCount} emojis détectés (limite: 5)"
            ];
            $totalPenalty += $penalty;
        }
        
        // 10. DÉTECTION DE DOUBLONS (si DB fournie dans le contexte)
        if (!empty($context['db']) && !empty($context['restaurant_id'])) {
            try {
                $db = $context['db'];
                $messageHash = md5(mb_strtolower(trim($message)));
                $stmt = $db->prepare("
                    SELECT message FROM reviews
                    WHERE restaurant_id = :rid AND status IN ('approved', 'pending')
                    ORDER BY created_at DESC LIMIT 20
                ");
                $stmt->execute([':rid' => $context['restaurant_id']]);
                $existingReviews = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                foreach ($existingReviews as $existing) {
                    $existingHash = md5(mb_strtolower(trim($existing)));
                    if ($messageHash === $existingHash) {
                        $penalty = 40;
                        $penalties[] = [
                            'rule' => 'Message identique existant',
                            'penalty' => $penalty,
                            'detail' => 'Ce message est identique à un avis déjà posté'
                        ];
                        $totalPenalty += $penalty;
                        break;
                    }
                    // Similarité Levenshtein normalisée
                    $maxLen = max(mb_strlen($message), mb_strlen($existing));
                    if ($maxLen > 0 && $maxLen < 500) {
                        $distance = levenshtein(
                            mb_substr(mb_strtolower($message), 0, 255),
                            mb_substr(mb_strtolower($existing), 0, 255)
                        );
                        $similarity = 1 - ($distance / max(mb_strlen(mb_substr($message, 0, 255)), mb_strlen(mb_substr($existing, 0, 255)), 1));
                        if ($similarity > 0.8) {
                            $penalty = 30;
                            $penalties[] = [
                                'rule' => 'Message très similaire existant',
                                'penalty' => $penalty,
                                'detail' => 'Similarité de ' . round($similarity * 100) . '% avec un avis existant'
                            ];
                            $totalPenalty += $penalty;
                            break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silently fail on duplicate detection
            }
        }

        // 11. DÉTECTION AVIS FAKE (compte neuf + 5/5 + texte court)
        if (!empty($context['account_age_hours'])) {
            $isNewAccount = $context['account_age_hours'] < 24;
            $isPerfectScore = $rating >= 4.5;
            $isShortText = mb_strlen($message) < 50;

            if ($isNewAccount && $isPerfectScore && $isShortText) {
                $penalty = 20;
                $penalties[] = [
                    'rule' => 'Profil suspect',
                    'penalty' => $penalty,
                    'detail' => 'Compte récent (<24h) + note parfaite + texte court'
                ];
                $totalPenalty += $penalty;
            }
        }

        // 12. TOUTES LES NOTES À 5/5 (suspect)
        if (!empty($context['all_notes_max'])) {
            $penalty = 10;
            $penalties[] = [
                'rule' => 'Notes toutes maximales',
                'penalty' => $penalty,
                'detail' => 'Nourriture, service, ambiance et prix tous à 5/5'
            ];
            $totalPenalty += $penalty;
        }

        // CALCUL SCORE FINAL
        $score = max(0, 100 - $totalPenalty);
        
        // DÉTERMINER ACTION
        if ($score >= 80) {
            $action = 'approve';
            $actionLabel = 'Auto-approuvé';
            $confidence = 'high';
        } elseif ($score >= 50) {
            $action = 'review';
            $actionLabel = 'À vérifier manuellement';
            $confidence = 'medium';
        } else {
            $action = 'reject';
            $actionLabel = 'Auto-rejeté (spam probable)';
            $confidence = 'high';
        }
        
        return [
            'score' => $score,
            'action' => $action,
            'action_label' => $actionLabel,
            'confidence' => $confidence,
            'penalties' => $penalties,
            'total_penalty' => $totalPenalty,
            'analyzed_at' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Génère un résumé textuel des raisons
     */
    public function getSummary(array $analysis): string
    {
        if (empty($analysis['penalties'])) {
            return "Aucun problème détecté. Avis de qualité.";
        }
        
        $summary = [];
        foreach ($analysis['penalties'] as $penalty) {
            $summary[] = "• " . $penalty['rule'] . ": " . $penalty['detail'];
        }
        
        return implode("\n", $summary);
    }
}
