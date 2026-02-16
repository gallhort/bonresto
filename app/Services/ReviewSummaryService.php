<?php

namespace App\Services;

use PDO;

/**
 * Service de synthese des avis par mots-cles pour un restaurant
 * Genere un resume type Yelp/Google Maps a partir de l'analyse de frequence
 */
class ReviewSummaryService
{
    private PDO $db;

    /**
     * Mots-cles positifs francais a detecter dans les avis
     */
    private const POSITIVE_WORDS = [
        'excellent', 'super', 'génial', 'parfait', 'top',
        'délicieux', 'frais', 'copieux', 'accueillant', 'rapide',
        'propre', 'terrasse', 'vue', 'ambiance', 'souriant',
        'attentionné', 'savoureux', 'succulent', 'bravo', 'merci',
        'recommande', 'incroyable', 'magnifique', 'chaleureux',
    ];

    /**
     * Mots-cles negatifs francais a detecter dans les avis
     */
    private const NEGATIVE_WORDS = [
        'nul', 'mauvais', 'horrible', 'déçu', 'lent',
        'froid', 'sale', 'cher', 'petit', 'bruyant',
        'désagréable', 'attente', 'long', 'moyen', 'fade',
        'insipide', 'impoli', 'décevant', 'surgelé', 'minuscule',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Calcule et enregistre le resume des avis pour un restaurant
     *
     * @param int $restaurantId
     * @return array Le resume genere (keywords, scores, texte)
     */
    public function computeSummary(int $restaurantId): array
    {
        // 1. Recuperer tous les avis approuves
        $stmt = $this->db->prepare("
            SELECT r.message, r.note_globale, r.note_nourriture,
                   r.note_service, r.note_ambiance, r.note_prix
            FROM reviews r
            WHERE r.restaurant_id = :restaurant_id
              AND r.status = 'approved'
        ");
        $stmt->execute([':restaurant_id' => $restaurantId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si aucun avis, retourner un resume vide et le sauvegarder
        if (empty($reviews)) {
            $emptySummary = [
                'restaurant_id' => $restaurantId,
                'positive_keywords' => [],
                'negative_keywords' => [],
                'avg_cuisine' => null,
                'avg_service' => null,
                'avg_ambiance' => null,
                'avg_prix' => null,
                'avg_globale' => null,
                'review_count' => 0,
                'summary_text' => '',
            ];
            $this->saveSummary($emptySummary);
            return $emptySummary;
        }

        // 2. Analyse de frequence des mots-cles
        $positiveFreq = array_fill_keys(self::POSITIVE_WORDS, 0);
        $negativeFreq = array_fill_keys(self::NEGATIVE_WORDS, 0);

        foreach ($reviews as $review) {
            $text = mb_strtolower($review['message'] ?? '', 'UTF-8');
            // Normaliser les accents pour la comparaison
            // On garde le texte original en minuscule et on cherche chaque mot-cle dedans
            foreach (self::POSITIVE_WORDS as $word) {
                if (mb_strpos($text, $word) !== false) {
                    $positiveFreq[$word]++;
                }
            }
            foreach (self::NEGATIVE_WORDS as $word) {
                if (mb_strpos($text, $word) !== false) {
                    $negativeFreq[$word]++;
                }
            }
        }

        // Filtrer les mots-cles qui apparaissent au moins 1 fois, trier par frequence
        $positiveKeywords = array_filter($positiveFreq, fn($count) => $count > 0);
        arsort($positiveKeywords);

        $negativeKeywords = array_filter($negativeFreq, fn($count) => $count > 0);
        arsort($negativeKeywords);

        // Limiter aux top 5 de chaque
        $topPositive = array_slice($positiveKeywords, 0, 5, true);
        $topNegative = array_slice($negativeKeywords, 0, 5, true);

        // 3. Calcul des moyennes par categorie
        $reviewCount = count($reviews);

        $sumCuisine = 0;
        $countCuisine = 0;
        $sumService = 0;
        $countService = 0;
        $sumAmbiance = 0;
        $countAmbiance = 0;
        $sumPrix = 0;
        $countPrix = 0;
        $sumGlobale = 0;
        $countGlobale = 0;

        foreach ($reviews as $review) {
            if (!empty($review['note_nourriture']) && $review['note_nourriture'] > 0) {
                $sumCuisine += (float) $review['note_nourriture'];
                $countCuisine++;
            }
            if (!empty($review['note_service']) && $review['note_service'] > 0) {
                $sumService += (float) $review['note_service'];
                $countService++;
            }
            if (!empty($review['note_ambiance']) && $review['note_ambiance'] > 0) {
                $sumAmbiance += (float) $review['note_ambiance'];
                $countAmbiance++;
            }
            if (!empty($review['note_prix']) && $review['note_prix'] > 0) {
                $sumPrix += (float) $review['note_prix'];
                $countPrix++;
            }
            if (!empty($review['note_globale']) && $review['note_globale'] > 0) {
                $sumGlobale += (float) $review['note_globale'];
                $countGlobale++;
            }
        }

        $avgCuisine = $countCuisine > 0 ? round($sumCuisine / $countCuisine, 2) : null;
        $avgService = $countService > 0 ? round($sumService / $countService, 2) : null;
        $avgAmbiance = $countAmbiance > 0 ? round($sumAmbiance / $countAmbiance, 2) : null;
        $avgPrix = $countPrix > 0 ? round($sumPrix / $countPrix, 2) : null;
        $avgGlobale = $countGlobale > 0 ? round($sumGlobale / $countGlobale, 2) : null;

        // 4. Generer le texte de synthese
        $summaryText = $this->buildSummaryText($topPositive, $topNegative);

        // 5. Assembler le resultat
        $summary = [
            'restaurant_id' => $restaurantId,
            'positive_keywords' => $topPositive,
            'negative_keywords' => $topNegative,
            'avg_cuisine' => $avgCuisine,
            'avg_service' => $avgService,
            'avg_ambiance' => $avgAmbiance,
            'avg_prix' => $avgPrix,
            'avg_globale' => $avgGlobale,
            'review_count' => $reviewCount,
            'summary_text' => $summaryText,
        ];

        // 6. Sauvegarder (UPSERT)
        $this->saveSummary($summary);

        return $summary;
    }

    /**
     * Recupere le resume en cache depuis la base de donnees
     *
     * @param int $restaurantId
     * @return array|null Le resume ou null si inexistant
     */
    public function getSummary(int $restaurantId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT restaurant_id, positive_keywords, negative_keywords,
                   avg_cuisine, avg_service, avg_ambiance, avg_prix, avg_globale,
                   review_count, summary_text, updated_at
            FROM restaurant_review_summaries
            WHERE restaurant_id = :restaurant_id
        ");
        $stmt->execute([':restaurant_id' => $restaurantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Decoder les champs JSON
        $row['positive_keywords'] = json_decode($row['positive_keywords'], true) ?: [];
        $row['negative_keywords'] = json_decode($row['negative_keywords'], true) ?: [];

        // Caster les numeriques
        $row['avg_cuisine'] = $row['avg_cuisine'] !== null ? (float) $row['avg_cuisine'] : null;
        $row['avg_service'] = $row['avg_service'] !== null ? (float) $row['avg_service'] : null;
        $row['avg_ambiance'] = $row['avg_ambiance'] !== null ? (float) $row['avg_ambiance'] : null;
        $row['avg_prix'] = $row['avg_prix'] !== null ? (float) $row['avg_prix'] : null;
        $row['avg_globale'] = $row['avg_globale'] !== null ? (float) $row['avg_globale'] : null;
        $row['review_count'] = (int) $row['review_count'];

        return $row;
    }

    /**
     * Construit le texte de synthese a partir des mots-cles
     *
     * @param array $positiveKeywords Mots positifs tries par frequence [mot => count]
     * @param array $negativeKeywords Mots negatifs tries par frequence [mot => count]
     * @return string Le texte de synthese
     */
    private function buildSummaryText(array $positiveKeywords, array $negativeKeywords): string
    {
        $parts = [];

        if (!empty($positiveKeywords)) {
            $topPositiveWords = array_keys(array_slice($positiveKeywords, 0, 3, true));
            $parts[] = 'Les clients adorent: ' . implode(', ', $topPositiveWords);
        }

        if (!empty($negativeKeywords)) {
            $topNegativeWords = array_keys(array_slice($negativeKeywords, 0, 3, true));
            $parts[] = 'Points à améliorer: ' . implode(', ', $topNegativeWords);
        }

        if (empty($parts)) {
            return '';
        }

        return implode('. ', $parts);
    }

    /**
     * Sauvegarde le resume en base (INSERT ... ON DUPLICATE KEY UPDATE)
     *
     * @param array $summary Les donnees du resume
     */
    private function saveSummary(array $summary): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO restaurant_review_summaries
                (restaurant_id, positive_keywords, negative_keywords,
                 avg_cuisine, avg_service, avg_ambiance, avg_prix, avg_globale,
                 review_count, summary_text, updated_at)
            VALUES
                (:restaurant_id, :positive_keywords, :negative_keywords,
                 :avg_cuisine, :avg_service, :avg_ambiance, :avg_prix, :avg_globale,
                 :review_count, :summary_text, NOW())
            ON DUPLICATE KEY UPDATE
                positive_keywords = :upd_positive_keywords,
                negative_keywords = :upd_negative_keywords,
                avg_cuisine = :upd_avg_cuisine,
                avg_service = :upd_avg_service,
                avg_ambiance = :upd_avg_ambiance,
                avg_prix = :upd_avg_prix,
                avg_globale = :upd_avg_globale,
                review_count = :upd_review_count,
                summary_text = :upd_summary_text,
                updated_at = NOW()
        ");

        $positiveJson = json_encode($summary['positive_keywords'], JSON_UNESCAPED_UNICODE);
        $negativeJson = json_encode($summary['negative_keywords'], JSON_UNESCAPED_UNICODE);

        $stmt->execute([
            ':restaurant_id' => $summary['restaurant_id'],
            ':positive_keywords' => $positiveJson,
            ':negative_keywords' => $negativeJson,
            ':avg_cuisine' => $summary['avg_cuisine'],
            ':avg_service' => $summary['avg_service'],
            ':avg_ambiance' => $summary['avg_ambiance'],
            ':avg_prix' => $summary['avg_prix'],
            ':avg_globale' => $summary['avg_globale'],
            ':review_count' => $summary['review_count'],
            ':summary_text' => $summary['summary_text'],
            ':upd_positive_keywords' => $positiveJson,
            ':upd_negative_keywords' => $negativeJson,
            ':upd_avg_cuisine' => $summary['avg_cuisine'],
            ':upd_avg_service' => $summary['avg_service'],
            ':upd_avg_ambiance' => $summary['avg_ambiance'],
            ':upd_avg_prix' => $summary['avg_prix'],
            ':upd_avg_globale' => $summary['avg_globale'],
            ':upd_review_count' => $summary['review_count'],
            ':upd_summary_text' => $summary['summary_text'],
        ]);
    }
}
