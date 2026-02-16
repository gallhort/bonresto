<?php

namespace App\Services;

use PDO;

/**
 * ScoringService — Moteur de scoring contextuel pour le Concierge v2
 *
 * Calcule un score composite pour chaque restaurant en fonction de :
 * - L'intent detecte (familial, romantique, business, rapide, etc.)
 * - Les poids adaptatifs (concierge_weights table)
 * - Les scores pre-indexes (score_familial, score_romantique, etc.)
 * - La note moyenne, popularite, prix, fraicheur
 *
 * Retourne un score + une explication en langage naturel
 */
class ScoringService
{
    private PDO $db;

    /** Cache des poids par intent */
    private static ?array $weightsCache = null;

    /** Map intent → colonne de score occasion */
    private const OCCASION_COLUMNS = [
        'occasion_familial'       => 'score_familial',
        'occasion_romantique'     => 'score_romantique',
        'occasion_business'       => 'score_business',
        'occasion_rapide'         => 'score_rapide',
        'occasion_festif'         => 'score_festif',
        'occasion_terrasse'       => 'score_terrasse',
        'occasion_budget'         => 'score_budget',
        'occasion_gastronomique'  => 'score_gastronomique',
        // ═══ 14 nouveaux scores v2 ═══
        'occasion_brunch'         => 'score_brunch',
        'occasion_livraison'      => 'score_livraison',
        'occasion_vue'            => 'score_vue',
        'occasion_healthy'        => 'score_healthy',
        'occasion_ouvert_tard'    => 'score_ouvert_tard',
        'occasion_instagrammable' => 'score_instagrammable',
        'occasion_calme'          => 'score_calme',
        'occasion_nouveau'        => 'score_nouveau',
        'occasion_parking'        => 'score_parking',
        'occasion_ramadan'        => 'score_ramadan',
        'occasion_groupe'         => 'score_groupe',
        'occasion_wifi_travail'   => 'score_wifi_travail',
        'occasion_enfants'        => 'score_enfants',
        'occasion_traditionnel'   => 'score_traditionnel',
        'amenity_search'          => 'score_terrasse', // fallback
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Calculer le score contextuel pour un restaurant
     *
     * @param array  $restaurant Row de la table restaurants (avec scores pre-indexes)
     * @param string $intent     Intent detecte
     * @param array  $context    Contexte de la requete: city, cuisine, budget, occasion, etc.
     * @return array ['score' => float, 'explanation' => string, 'factors' => array]
     */
    public function computeScore(array $restaurant, string $intent, array $context = []): array
    {
        $weights = $this->getWeights($intent);
        $factors = [];
        $score = 0.0;

        // ── Factor 1: Note moyenne (0-5 → 0-1) ──
        $noteNorm = min(1.0, ($restaurant['note_moyenne'] ?? 0) / 5.0);
        $noteContrib = $noteNorm * $weights['w_note'];
        $score += $noteContrib;
        if ($noteNorm >= 0.8) {
            $factors[] = 'Tres bien note (' . number_format($restaurant['note_moyenne'], 1) . '/5)';
        } elseif ($noteNorm >= 0.6) {
            $factors[] = 'Bien note (' . number_format($restaurant['note_moyenne'], 1) . '/5)';
        }

        // ── Factor 2: Popularite (normalize to 0-1, max 200 points) ──
        $popNorm = min(1.0, ($restaurant['popularity_score'] ?? 0) / 200.0);
        $popContrib = $popNorm * $weights['w_popularite'];
        $score += $popContrib;
        if ($popNorm >= 0.5) {
            $factors[] = 'Populaire (' . (int)($restaurant['nb_avis'] ?? 0) . ' avis)';
        }

        // ── Factor 3: Score occasion (pre-indexed) ──
        $occasionCol = $this->getOccasionColumn($intent, $context);
        $occasionScore = (float)($restaurant[$occasionCol] ?? 0);
        $occContrib = $occasionScore * $weights['w_occasion'];
        $score += $occContrib;
        if ($occasionScore >= 0.5) {
            $factors[] = $this->getOccasionLabel($occasionCol);
        }

        // ── Factor 4: Proximite (si coordonnees fournies) ──
        $proxContrib = 0;
        if (!empty($context['lat']) && !empty($context['lng']) && !empty($restaurant['gps_latitude'])) {
            $distance = $this->haversineDistance(
                (float)$context['lat'], (float)$context['lng'],
                (float)$restaurant['gps_latitude'], (float)$restaurant['gps_longitude']
            );
            // 0km = 1.0, 10km = 0.5, 20km+ = ~0
            $proxNorm = max(0, 1.0 - ($distance / 20.0));
            $proxContrib = $proxNorm * $weights['w_proximite'];
            $score += $proxContrib;
            if ($distance <= 3) {
                $factors[] = 'Tout pres (' . number_format($distance, 1) . ' km)';
            } elseif ($distance <= 8) {
                $factors[] = 'A ' . number_format($distance, 1) . ' km';
            }
        }

        // ── Factor 5: Prix (budget match) ──
        $priceLen = mb_strlen($restaurant['price_range'] ?? '');
        $priceNorm = 0.5; // default middle
        if (!empty($context['budget'])) {
            $targetBudget = $context['budget']; // 'low', 'medium', 'high'
            if ($targetBudget === 'low') {
                $priceNorm = $priceLen <= 2 ? 1.0 : ($priceLen === 3 ? 0.6 : 0.2);
            } elseif ($targetBudget === 'high') {
                $priceNorm = $priceLen >= 3 ? 1.0 : ($priceLen >= 2 ? 0.6 : 0.3);
            } else {
                $priceNorm = $priceLen === 3 || $priceLen === 4 ? 0.8 : 0.5;
            }
        } else {
            // No budget specified: slight preference for affordable
            $priceNorm = $priceLen <= 3 ? 0.7 : 0.4;
        }
        $priceContrib = $priceNorm * $weights['w_prix'];
        $score += $priceContrib;
        if ($priceNorm >= 0.7 && !empty($context['budget'])) {
            $labels = ['low' => 'Prix abordable', 'medium' => 'Prix moyen', 'high' => 'Gastronomique'];
            $factors[] = $labels[$context['budget']] ?? 'Bon rapport qualite-prix';
        }

        // ── Factor 6: Fraicheur (recent activity) ──
        $freshness = (float)($restaurant['recent_reviews'] ?? 0) / 5.0; // 5 recent reviews = max
        $freshness = min(1.0, $freshness);
        if ($restaurant['orders_enabled'] ?? false) {
            $freshness = min(1.0, $freshness + 0.2);
        }
        $freshContrib = $freshness * $weights['w_fraicheur'];
        $score += $freshContrib;
        if ($freshness >= 0.6) {
            $factors[] = 'Actif recemment';
        }

        // Clamp to 0-1
        $score = min(1.0, max(0, $score));

        // Build explanation sentence
        $explanation = $this->buildExplanation($restaurant, $factors, $score);

        return [
            'score' => round($score, 4),
            'explanation' => $explanation,
            'factors' => $factors,
            'debug' => [
                'note' => round($noteContrib, 3),
                'pop' => round($popContrib, 3),
                'occasion' => round($occContrib, 3),
                'prox' => round($proxContrib, 3),
                'prix' => round($priceContrib, 3),
                'fresh' => round($freshContrib, 3),
            ],
        ];
    }

    /**
     * Scorer et trier un ensemble de restaurants
     *
     * @param array  $restaurants Liste de restaurants (rows DB)
     * @param string $intent
     * @param array  $context
     * @param int    $limit       Nombre max de resultats
     * @return array [['restaurant' => ..., 'score' => ..., 'explanation' => ...], ...]
     */
    public function rankRestaurants(array $restaurants, string $intent, array $context = [], int $limit = 3): array
    {
        $scored = [];
        foreach ($restaurants as $r) {
            $result = $this->computeScore($r, $intent, $context);
            $scored[] = [
                'restaurant' => $r,
                'score' => $result['score'],
                'explanation' => $result['explanation'],
                'factors' => $result['factors'],
            ];
        }

        // Sort by score descending
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    /**
     * Charger les poids adaptatifs depuis la DB (avec cache)
     */
    private function getWeights(string $intent): array
    {
        if (self::$weightsCache === null) {
            self::$weightsCache = [];
            try {
                $stmt = $this->db->query("SELECT * FROM concierge_weights");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($rows as $row) {
                    self::$weightsCache[$row['intent']] = $row;
                }
            } catch (\Exception $e) {
                // Table may not exist yet
            }
        }

        // Return weights for this intent, or default
        return self::$weightsCache[$intent]
            ?? self::$weightsCache['recommendation']
            ?? [
                'w_note' => 0.25,
                'w_popularite' => 0.20,
                'w_occasion' => 0.20,
                'w_proximite' => 0.15,
                'w_prix' => 0.10,
                'w_fraicheur' => 0.10,
            ];
    }

    /**
     * Determiner la colonne occasion a utiliser
     */
    private function getOccasionColumn(string $intent, array $context): string
    {
        // If context specifies an occasion directly
        if (!empty($context['occasion'])) {
            $col = 'score_' . $context['occasion'];
            $validCols = [
                'score_familial', 'score_romantique', 'score_business', 'score_rapide',
                'score_festif', 'score_terrasse', 'score_budget', 'score_gastronomique',
                'score_brunch', 'score_livraison', 'score_vue', 'score_healthy',
                'score_ouvert_tard', 'score_instagrammable', 'score_calme', 'score_nouveau',
                'score_parking', 'score_ramadan', 'score_groupe', 'score_wifi_travail',
                'score_enfants', 'score_traditionnel',
            ];
            if (in_array($col, $validCols)) {
                return $col;
            }
        }

        // Map intent to occasion column
        if (isset(self::OCCASION_COLUMNS[$intent])) {
            return self::OCCASION_COLUMNS[$intent];
        }

        // Budget intents
        if ($intent === 'price') {
            return 'score_budget';
        }

        // Default: use a blend (popularity-like)
        return 'score_familial'; // most generic
    }

    /**
     * Label humain pour un score occasion
     */
    private function getOccasionLabel(string $column): string
    {
        return match ($column) {
            'score_familial' => 'Ideal en famille',
            'score_romantique' => 'Ambiance romantique',
            'score_business' => 'Parfait pour le business',
            'score_rapide' => 'Service rapide',
            'score_festif' => 'Ambiance festive',
            'score_terrasse' => 'Belle terrasse',
            'score_budget' => 'Bon rapport qualite-prix',
            'score_gastronomique' => 'Experience gastronomique',
            'score_brunch' => 'Ideal pour un brunch',
            'score_livraison' => 'Livraison disponible',
            'score_vue' => 'Vue exceptionnelle',
            'score_healthy' => 'Cuisine saine et legere',
            'score_ouvert_tard' => 'Ouvert tard le soir',
            'score_instagrammable' => 'Cadre photogenique',
            'score_calme' => 'Ambiance calme et paisible',
            'score_nouveau' => 'Nouveau et tendance',
            'score_parking' => 'Parking facile',
            'score_ramadan' => 'Ideal pour le Ramadan',
            'score_groupe' => 'Parfait pour les grands groupes',
            'score_wifi_travail' => 'Cafe wifi pour travailler',
            'score_enfants' => 'Espace enfants',
            'score_traditionnel' => 'Cuisine algerienne authentique',
            default => '',
        };
    }

    /**
     * Construire l'explication en langage naturel
     */
    private function buildExplanation(array $restaurant, array $factors, float $score): string
    {
        if (empty($factors)) {
            return 'Un bon choix dans votre zone.';
        }

        // Take top 2-3 factors
        $topFactors = array_slice($factors, 0, 3);
        $name = $restaurant['nom'] ?? 'Ce restaurant';

        if (count($topFactors) === 1) {
            return $topFactors[0] . '.';
        }

        $last = array_pop($topFactors);
        return implode(', ', $topFactors) . ' et ' . lcfirst($last) . '.';
    }

    /**
     * Distance Haversine en km
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371; // Rayon de la Terre en km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    /**
     * Enregistrer une recommendation (pour le feedback loop)
     *
     * @return int|null ID de la recommendation inseree
     */
    public function logRecommendation(
        string $sessionId,
        ?int $conversationId,
        ?int $userId,
        int $restaurantId,
        int $position,
        string $intent,
        string $queryText,
        float $contextScore,
        string $explanation
    ): ?int {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO concierge_recommendations
                    (session_id, conversation_id, user_id, restaurant_id, position, intent, query_text, context_score, explanation)
                VALUES
                    (:sid, :cid, :uid, :rid, :pos, :intent, :query, :score, :expl)
            ");
            $stmt->execute([
                ':sid' => $sessionId,
                ':cid' => $conversationId,
                ':uid' => $userId,
                ':rid' => $restaurantId,
                ':pos' => $position,
                ':intent' => $intent,
                ':query' => mb_substr($queryText, 0, 500),
                ':score' => $contextScore,
                ':expl' => mb_substr($explanation, 0, 255),
            ]);
            $recId = (int)$this->db->lastInsertId();

            // Increment impressions counter in concierge_weights
            if ($position === 1) { // Only once per recommendation set
                $this->db->prepare("
                    UPDATE concierge_weights SET impressions = impressions + 1 WHERE intent = :intent
                ")->execute([':intent' => $intent]);
            }

            return $recId;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Enregistrer un clic sur une recommendation + incrementer compteurs weights
     */
    public function trackClick(int $recommendationId): void
    {
        try {
            // Get the intent before updating
            $stmt = $this->db->prepare("
                SELECT intent FROM concierge_recommendations WHERE id = :id AND clicked = 0
            ");
            $stmt->execute([':id' => $recommendationId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return;

            $this->db->prepare("
                UPDATE concierge_recommendations
                SET clicked = 1, clicked_at = NOW()
                WHERE id = :id AND clicked = 0
            ")->execute([':id' => $recommendationId]);

            // Increment clicks counter in concierge_weights
            $this->db->prepare("
                UPDATE concierge_weights SET clicks = clicks + 1 WHERE intent = :intent
            ")->execute([':intent' => $row['intent']]);
        } catch (\Exception $e) {
            // silently fail
        }
    }

    /**
     * Enregistrer le dwell time (secondes passees sur la fiche)
     */
    public function trackDwell(int $recommendationId, int $seconds): void
    {
        try {
            $seconds = max(0, min(3600, $seconds)); // clamp 0-3600
            $this->db->prepare("
                UPDATE concierge_recommendations
                SET dwell_time = :seconds
                WHERE id = :id
            ")->execute([':seconds' => $seconds, ':id' => $recommendationId]);
        } catch (\Exception $e) {
            // silently fail
        }
    }

    /**
     * Enregistrer une conversion (reservation ou commande)
     */
    public function trackConversion(int $recommendationId, string $type): void
    {
        try {
            $field = $type === 'order' ? 'ordered' : 'booked';
            $timeField = $type === 'order' ? 'ordered_at' : 'booked_at';
            $this->db->prepare("
                UPDATE concierge_recommendations
                SET {$field} = 1, {$timeField} = NOW()
                WHERE id = :id
            ")->execute([':id' => $recommendationId]);
        } catch (\Exception $e) {
            // silently fail
        }
    }

    /**
     * Mise a jour des poids adaptatifs (a appeler periodiquement via cron)
     * Analyse les 30 derniers jours de feedback et ajuste les poids
     */
    public function updateAdaptiveWeights(): array
    {
        $stats = [];

        try {
            // Get CTR and conversion rate per intent
            $stmt = $this->db->query("
                SELECT
                    intent,
                    COUNT(*) as impressions,
                    SUM(clicked) as clicks,
                    SUM(booked) + SUM(ordered) as conversions,
                    SUM(clicked) / COUNT(*) as ctr,
                    (SUM(booked) + SUM(ordered)) / GREATEST(SUM(clicked), 1) as conversion_rate
                FROM concierge_recommendations
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY intent
                HAVING impressions >= 10
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $intent = $row['intent'];
                $ctr = (float)$row['ctr'];

                // Update stats in weights table
                $this->db->prepare("
                    UPDATE concierge_weights
                    SET impressions = :imp, clicks = :clk, conversions = :conv, ctr = :ctr
                    WHERE intent = :intent
                ")->execute([
                    ':imp' => $row['impressions'],
                    ':clk' => $row['clicks'],
                    ':conv' => $row['conversions'],
                    ':ctr' => $ctr,
                    ':intent' => $intent,
                ]);

                $stats[$intent] = [
                    'impressions' => (int)$row['impressions'],
                    'ctr' => round($ctr, 4),
                    'conversion_rate' => round((float)$row['conversion_rate'], 4),
                ];
            }
        } catch (\Exception $e) {
            // silently fail
        }

        return $stats;
    }

    /**
     * Recalculer les scores occasion pour tous les restaurants (batch)
     * Appele par le cron /api/cron/recompute-scores
     *
     * Architecture du scoring v3:
     * - Multiplicateur cuisine: 0.0 elimine, 1.0 boost max (pour romantique, gastro, business)
     * - Fiabilite bayesienne: restos avec peu d'avis penalises (m=10, C=3.8)
     * - note_ambiance des reviews: signal reel pour romantique/festif/gastro
     * - Amenites: signals forts quand rares (wifi=6, prive=5)
     */
    public function recomputeOccasionScores(): int
    {
        $count = 0;

        try {
            // Bayesian average constants: m=10 avis minimum, C=3.8 (prior global)
            // Formula: bayesian = (nb_avis * note + m * C) / (nb_avis + m)
            // Ensures: 5 avis + 5/5 = 4.26, 200 avis + 4.5/5 = 4.47 (fiable > rare)
            $M = 10;
            $C = 3.8;

            $queries = [
                // ═══ score_familial ═══
                // Mix de cuisines kid-friendly + prix abordable + baby_chair/jeux + fiabilite
                // PAS de multiplicateur: tous les types de cuisine peuvent etre familiaux
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_familial = LEAST(1.0,
                    -- Cuisine kid-friendly (bonus, pas exclusion)
                    CASE
                        WHEN r.type_cuisine LIKE '%pizza%' THEN 0.12
                        WHEN r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%hamburger%' THEN 0.10
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%rapide%' THEN 0.08
                        WHEN r.type_cuisine LIKE '%tradition%' OR r.type_cuisine LIKE '%lg%rien%' OR r.type_cuisine LIKE '%classique%' THEN 0.12
                        WHEN r.type_cuisine LIKE '%Grill%' THEN 0.10
                        ELSE 0.05
                    END +
                    -- Prix abordable (€ ou €€)
                    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) <= 1 THEN 0.15 WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 2 THEN 0.12 ELSE 0.04 END +
                    -- Equipements enfants (signaux forts)
                    COALESCE(ro.baby_chair, 0) * 0.15 +
                    COALESCE(ro.game_zone, 0) * 0.12 +
                    -- Note bayesienne (fiabilite: 5 avis 5/5 < 200 avis 4.5/5)
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.3 THEN 0.18
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.12
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.5 THEN 0.06
                        ELSE 0.02
                    END +
                    -- Popularite (nb avis = preuve sociale)
                    CASE WHEN r.nb_avis >= 50 THEN 0.12 WHEN r.nb_avis >= 20 THEN 0.08 WHEN r.nb_avis >= 5 THEN 0.05 ELSE 0.01 END +
                    -- Signal trip_type reel
                    LEAST(0.12, (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.trip_type = 'En famille' AND rv.status = 'approved') * 0.06) +
                    -- Signal review_insights familial (NLP/Groq enrichi)
                    COALESCE(
                        (SELECT LEAST(0.10, AVG(ri.occasion_familial) * 0.15) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.occasion_familial > 0.3),
                    0) +
                    -- Parking (pratique avec enfants)
                    COALESCE(ro.parking, 0) * 0.04
                ) WHERE r.status = 'validated'",

                // ═══ score_romantique ═══
                // Multiplicateur cuisine × (note_ambiance + note_bayesienne + prix + amenites)
                // note_ambiance des reviews = signal reel d'ambiance
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_romantique = LEAST(1.0,
                    -- Multiplicateur cuisine (0 = elimine)
                    CASE
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%hamburger%' OR r.type_cuisine LIKE '%rapide%' OR r.type_cuisine LIKE '%kebab%' OR r.type_cuisine LIKE '%am%ricain%' THEN 0.0
                        WHEN r.type_cuisine LIKE '%pizza%' THEN 0.05
                        WHEN r.type_cuisine LIKE '%gastro%' THEN 1.0
                        WHEN r.type_cuisine LIKE '%fran%' THEN 0.95
                        WHEN r.type_cuisine LIKE '%ital%' THEN 0.90
                        WHEN r.type_cuisine LIKE '%mer%' OR r.type_cuisine LIKE '%poisson%' OR r.type_cuisine LIKE '%fruit%' THEN 0.88
                        WHEN r.type_cuisine LIKE '%m%diterran%' OR r.type_cuisine LIKE '%orient%' THEN 0.78
                        WHEN r.type_cuisine LIKE '%asiat%' OR r.type_cuisine LIKE '%japon%' OR r.type_cuisine LIKE '%sushi%' OR r.type_cuisine LIKE '%cor%en%' THEN 0.72
                        WHEN r.type_cuisine LIKE '%tradition%' OR r.type_cuisine LIKE '%classique%' OR r.type_cuisine LIKE '%lg%rien%' THEN 0.55
                        WHEN r.type_cuisine LIKE '%Grill%' THEN 0.40
                        WHEN r.type_cuisine LIKE '%caf%' OR r.type_cuisine LIKE '%brunch%' THEN 0.35
                        ELSE 0.50
                    END * (
                        -- note_ambiance moyenne des reviews (signal reel 0-0.25)
                        COALESCE(
                            CASE
                                WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.5 THEN 0.25
                                WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.0 THEN 0.18
                                WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 3.5 THEN 0.10
                                ELSE 0.03
                            END,
                        0.03) +
                        -- Note bayesienne (fiabilite)
                        CASE
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.3 THEN 0.25
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.18
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.5 THEN 0.08
                            ELSE 0.02
                        END +
                        -- Prix (€€€ = ambiance gastronomique)
                        CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 3 THEN 0.20 WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 2 THEN 0.08 ELSE 0.0 END +
                        -- Amenites romantiques
                        COALESCE(ro.terrace, 0) * 0.08 +
                        COALESCE(ro.private_room, 0) * 0.10 +
                        -- Fiabilite: nb avis minimum pour confiance
                        CASE WHEN r.nb_avis >= 50 THEN 0.08 WHEN r.nb_avis >= 15 THEN 0.05 WHEN r.nb_avis >= 5 THEN 0.03 ELSE 0.0 END +
                        -- Signal trip_type reel (couple)
                        LEAST(0.08, (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.trip_type = 'En couple' AND rv.status = 'approved') * 0.04) +
                        -- Signal review_insights romantique (NLP/Groq enrichi)
                        COALESCE(
                            (SELECT LEAST(0.10, AVG(ri.occasion_romantique) * 0.15) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.occasion_romantique > 0.3),
                        0)
                    )
                ) WHERE r.status = 'validated'",

                // ═══ score_business ═══
                // Multiplicateur cuisine × (wifi + parking + prive + note + prix)
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_business = LEAST(1.0,
                    CASE
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%hamburger%' OR r.type_cuisine LIKE '%rapide%' OR r.type_cuisine LIKE '%kebab%' THEN 0.10
                        WHEN r.type_cuisine LIKE '%pizza%' THEN 0.25
                        WHEN r.type_cuisine LIKE '%gastro%' OR r.type_cuisine LIKE '%fran%' THEN 1.0
                        WHEN r.type_cuisine LIKE '%ital%' OR r.type_cuisine LIKE '%m%diterran%' THEN 0.85
                        WHEN r.type_cuisine LIKE '%tradition%' OR r.type_cuisine LIKE '%classique%' OR r.type_cuisine LIKE '%lg%rien%' THEN 0.65
                        ELSE 0.55
                    END * (
                        COALESCE(ro.wifi, 0) * 0.20 +
                        COALESCE(ro.parking, 0) * 0.12 +
                        COALESCE(ro.private_room, 0) * 0.18 +
                        CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 3 THEN 0.18 WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 2 THEN 0.10 ELSE 0.03 END +
                        CASE
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.2 THEN 0.18
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.8 THEN 0.10
                            ELSE 0.03
                        END +
                        CASE WHEN r.nb_avis >= 20 THEN 0.08 WHEN r.nb_avis >= 5 THEN 0.04 ELSE 0.01 END +
                        COALESCE(
                            CASE
                                WHEN (SELECT AVG(rv.note_service) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_service > 0) >= 4.3 THEN 0.08
                                WHEN (SELECT AVG(rv.note_service) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_service > 0) >= 4.0 THEN 0.04
                                ELSE 0.01
                            END,
                        0.01) +
                        -- Signal review_insights business (NLP/Groq enrichi)
                        COALESCE(
                            (SELECT LEAST(0.08, AVG(ri.occasion_business) * 0.12) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.occasion_business > 0.3),
                        0)
                    )
                ) WHERE r.status = 'validated'",

                // ═══ score_rapide ═══
                // Cuisine fast + pas cher + delivery/takeaway + commande en ligne
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_rapide = LEAST(1.0,
                    CASE
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%rapide%' THEN 0.28
                        WHEN r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%hamburger%' THEN 0.25
                        WHEN r.type_cuisine LIKE '%pizza%' OR r.type_cuisine LIKE '%kebab%' THEN 0.22
                        WHEN r.type_cuisine LIKE '%emporter%' THEN 0.22
                        ELSE 0.03
                    END +
                    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) <= 1 THEN 0.18 WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 2 THEN 0.12 ELSE 0.03 END +
                    COALESCE(ro.delivery, 0) * 0.14 +
                    COALESCE(ro.takeaway, 0) * 0.12 +
                    CASE WHEN r.orders_enabled = 1 THEN 0.10 ELSE 0.02 END +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.5 THEN 0.05
                        ELSE 0.02
                    END +
                    CASE WHEN r.nb_avis >= 10 THEN 0.06 ELSE 0.02 END
                ) WHERE r.status = 'validated'",

                // ═══ score_festif ═══
                // Terrasse + bonne ambiance + populaire + events
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_festif = LEAST(1.0,
                    COALESCE(ro.terrace, 0) * 0.18 +
                    -- note_ambiance reelle des reviews
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.5 THEN 0.20
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.0 THEN 0.12
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 3.5 THEN 0.06
                            ELSE 0.02
                        END,
                    0.02) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.3 THEN 0.15
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        ELSE 0.03
                    END +
                    CASE WHEN r.nb_avis >= 50 THEN 0.15 WHEN r.nb_avis >= 15 THEN 0.10 WHEN r.nb_avis >= 5 THEN 0.05 ELSE 0.01 END +
                    CASE WHEN r.events_enabled = 1 THEN 0.12 ELSE 0.02 END +
                    CASE
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%rapide%' THEN 0.02
                        ELSE 0.08
                    END +
                    CASE WHEN r.popularity_score >= 80 THEN 0.08 WHEN r.popularity_score >= 40 THEN 0.04 ELSE 0.01 END +
                    -- Signal review_insights festif (NLP/Groq enrichi)
                    COALESCE(
                        (SELECT LEAST(0.08, AVG(ri.occasion_festif) * 0.12) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.occasion_festif > 0.3),
                    0)
                ) WHERE r.status = 'validated'",

                // ═══ score_terrasse ═══
                // Terrasse flag dominant + bonne note + populaire
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_terrasse = LEAST(1.0,
                    COALESCE(ro.terrace, 0) * 0.45 +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.18
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.5 THEN 0.10
                        ELSE 0.04
                    END +
                    CASE WHEN r.nb_avis >= 10 THEN 0.12 WHEN r.nb_avis >= 3 THEN 0.06 ELSE 0.02 END +
                    CASE WHEN r.popularity_score >= 30 THEN 0.10 ELSE 0.03 END +
                    COALESCE(ro.parking, 0) * 0.08 +
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.0 THEN 0.07
                            ELSE 0.02
                        END,
                    0.02)
                ) WHERE r.status = 'validated'",

                // ═══ score_budget ═══
                // Prix dominant + bonne note + populaire
                "UPDATE restaurants r SET r.score_budget = LEAST(1.0,
                    CASE
                        WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) <= 1 THEN 0.40
                        WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 2 THEN 0.25
                        WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 3 THEN 0.08
                        ELSE 0.03
                    END +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.2 THEN 0.22
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.8 THEN 0.14
                        ELSE 0.05
                    END +
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(rv.note_prix) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_prix > 0) >= 4.0 THEN 0.12
                            WHEN (SELECT AVG(rv.note_prix) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_prix > 0) >= 3.5 THEN 0.06
                            ELSE 0.02
                        END,
                    0.02) +
                    CASE WHEN r.nb_avis >= 20 THEN 0.10 WHEN r.nb_avis >= 5 THEN 0.06 ELSE 0.02 END +
                    CASE WHEN r.popularity_score >= 30 THEN 0.08 ELSE 0.02 END +
                    0.04
                ) WHERE r.status = 'validated'",

                // ═══ score_gastronomique ═══
                // Multiplicateur cuisine × (prix_haut + note_bayesienne + ambiance + amenites)
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_gastronomique = LEAST(1.0,
                    CASE
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%hamburger%' OR r.type_cuisine LIKE '%rapide%' OR r.type_cuisine LIKE '%kebab%' OR r.type_cuisine LIKE '%pizza%' OR r.type_cuisine LIKE '%am%ricain%' THEN 0.0
                        WHEN r.type_cuisine LIKE '%gastro%' THEN 1.0
                        WHEN r.type_cuisine LIKE '%fran%' THEN 0.95
                        WHEN r.type_cuisine LIKE '%mer%' OR r.type_cuisine LIKE '%poisson%' OR r.type_cuisine LIKE '%fruit%' THEN 0.88
                        WHEN r.type_cuisine LIKE '%ital%' THEN 0.82
                        WHEN r.type_cuisine LIKE '%m%diterran%' OR r.type_cuisine LIKE '%orient%' THEN 0.72
                        WHEN r.type_cuisine LIKE '%asiat%' OR r.type_cuisine LIKE '%japon%' OR r.type_cuisine LIKE '%sushi%' THEN 0.72
                        WHEN r.type_cuisine LIKE '%tradition%' OR r.type_cuisine LIKE '%classique%' OR r.type_cuisine LIKE '%lg%rien%' THEN 0.50
                        ELSE 0.40
                    END * (
                        CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 3 THEN 0.25 WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 2 THEN 0.10 ELSE 0.0 END +
                        CASE
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.3 THEN 0.25
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.18
                            WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.5 THEN 0.08
                            ELSE 0.0
                        END +
                        COALESCE(
                            CASE
                                WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.5 THEN 0.18
                                WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.0 THEN 0.10
                                ELSE 0.02
                            END,
                        0.02) +
                        COALESCE(
                            CASE
                                WHEN (SELECT AVG(rv.note_nourriture) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_nourriture > 0) >= 4.5 THEN 0.12
                                WHEN (SELECT AVG(rv.note_nourriture) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_nourriture > 0) >= 4.0 THEN 0.06
                                ELSE 0.01
                            END,
                        0.01) +
                        COALESCE(ro.private_room, 0) * 0.08 +
                        COALESCE(ro.valet_service, 0) * 0.06 +
                        CASE WHEN r.reservations_enabled = 1 THEN 0.06 ELSE 0.01 END +
                        -- Signal review_insights sentiment food (NLP/Groq enrichi)
                        COALESCE(
                            CASE
                                WHEN (SELECT AVG(ri.sentiment_food) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.sentiment_food IS NOT NULL) >= 4.5 THEN 0.08
                                WHEN (SELECT AVG(ri.sentiment_food) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.sentiment_food IS NOT NULL) >= 4.0 THEN 0.04
                                ELSE 0.01
                            END,
                        0.01)
                    )
                ) WHERE r.status = 'validated'",

                // ═══════════════════════════════════════════════════════
                // 14 NOUVEAUX SCORES v2 — Scoring Enrichment
                // ═══════════════════════════════════════════════════════

                // ═══ score_brunch ═══
                // Cuisine café/brunch/pâtisserie + horaires matin + review keywords
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_brunch = LEAST(1.0,
                    CASE
                        WHEN r.type_cuisine LIKE '%caf%' OR r.type_cuisine LIKE '%brunch%' THEN 0.25
                        WHEN r.type_cuisine LIKE '%patisserie%' OR r.type_cuisine LIKE '%boulangerie%' THEN 0.20
                        WHEN r.type_cuisine LIKE '%salon de th%' THEN 0.18
                        ELSE 0.03
                    END +
                    -- Horaires matin (ouverture <= 09:00)
                    COALESCE((SELECT IF(MIN(h.ouverture_matin) <= '09:00:00', 0.20, 0.05) FROM restaurant_horaires h WHERE h.restaurant_id = r.id AND h.ferme = 0 AND h.ouverture_matin IS NOT NULL), 0.02) +
                    -- Review keywords brunch/petit_dejeuner/ftour
                    LEAST(0.18, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%brunch%' OR ri.keywords LIKE '%petit_dejeuner%' OR ri.keywords LIKE '%ftour%')) * 0.06) +
                    -- Context tags
                    LEAST(0.10, (SELECT COALESCE(SUM(ct.vote_count), 0) FROM restaurant_context_tags ct WHERE ct.restaurant_id = r.id AND ct.tag IN ('brunch', 'petit-dejeuner', 'breakfast')) * 0.05) +
                    -- Note bayesienne
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.12
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 3.5 THEN 0.06
                        ELSE 0.02
                    END +
                    CASE WHEN r.nb_avis >= 10 THEN 0.08 WHEN r.nb_avis >= 3 THEN 0.04 ELSE 0.01 END
                ) WHERE r.status = 'validated'",

                // ═══ score_livraison ═══
                // delivery flag dominant + orders_enabled + review keywords
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_livraison = LEAST(1.0,
                    COALESCE(ro.delivery, 0) * 0.30 +
                    CASE WHEN r.delivery_enabled = 1 THEN 0.15 ELSE 0.0 END +
                    CASE WHEN r.orders_enabled = 1 THEN 0.12 ELSE 0.0 END +
                    COALESCE(ro.takeaway, 0) * 0.08 +
                    LEAST(0.10, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%livraison%' OR ri.keywords LIKE '%emporter%')) * 0.05) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.12
                        ELSE 0.04
                    END +
                    CASE WHEN r.nb_avis >= 10 THEN 0.06 ELSE 0.02 END +
                    CASE WHEN r.popularity_score >= 30 THEN 0.05 ELSE 0.01 END
                ) WHERE r.status = 'validated'",

                // ═══ score_vue ═══
                // Review keywords dominant (vue_mer, vue_panoramique, rooftop) + context_tags + terrasse
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_vue = LEAST(1.0,
                    -- Review keywords (signal dominant)
                    LEAST(0.35, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%vue_mer%' OR ri.keywords LIKE '%vue_panoramique%' OR ri.keywords LIKE '%rooftop%')) * 0.08) +
                    -- Context tags vue
                    LEAST(0.15, (SELECT COALESCE(SUM(ct.vote_count), 0) FROM restaurant_context_tags ct WHERE ct.restaurant_id = r.id AND ct.tag IN ('vue', 'vue-mer', 'rooftop', 'panoramique')) * 0.05) +
                    -- Terrasse (souvent avec vue)
                    COALESCE(ro.terrace, 0) * 0.10 +
                    -- note_ambiance (vue = ambiance)
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.5 THEN 0.12
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.0 THEN 0.06
                            ELSE 0.02
                        END,
                    0.02) +
                    -- Photos count (lieux avec vue = plus de photos)
                    LEAST(0.08, (SELECT COUNT(*) FROM restaurant_photos rp WHERE rp.restaurant_id = r.id) * 0.02) +
                    -- Note bayesienne
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.2 THEN 0.10
                        ELSE 0.03
                    END +
                    CASE WHEN r.nb_avis >= 5 THEN 0.06 ELSE 0.01 END
                ) WHERE r.status = 'validated'",

                // ═══ score_healthy ═══
                // Cuisine végétarien/vegan/sushi + review keywords + description
                "UPDATE restaurants r
                SET r.score_healthy = LEAST(1.0,
                    CASE
                        WHEN r.type_cuisine LIKE '%v%g%tarien%' OR r.type_cuisine LIKE '%vegan%' THEN 0.30
                        WHEN r.type_cuisine LIKE '%sushi%' OR r.type_cuisine LIKE '%japon%' THEN 0.18
                        WHEN r.type_cuisine LIKE '%salade%' OR r.type_cuisine LIKE '%bio%' THEN 0.22
                        WHEN r.type_cuisine LIKE '%m%diterran%' THEN 0.10
                        ELSE 0.03
                    END +
                    LEAST(0.20, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%healthy%' OR ri.keywords LIKE '%salade%' OR ri.keywords LIKE '%vegetarien%')) * 0.06) +
                    LEAST(0.10, (SELECT COALESCE(SUM(ct.vote_count), 0) FROM restaurant_context_tags ct WHERE ct.restaurant_id = r.id AND ct.tag IN ('healthy', 'bio', 'vegetarien', 'salade')) * 0.05) +
                    CASE WHEN r.description LIKE '%healthy%' OR r.description LIKE '%bio%' OR r.description LIKE '%salade%' OR r.description LIKE '%diet%' THEN 0.08 ELSE 0.0 END +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.12
                        ELSE 0.04
                    END +
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(ri.sentiment_food) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.sentiment_food IS NOT NULL) >= 4.5 THEN 0.10
                            WHEN (SELECT AVG(ri.sentiment_food) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.sentiment_food IS NOT NULL) >= 4.0 THEN 0.05
                            ELSE 0.01
                        END,
                    0.01) +
                    CASE WHEN r.nb_avis >= 5 THEN 0.06 ELSE 0.02 END
                ) WHERE r.status = 'validated'",

                // ═══ score_ouvert_tard ═══
                // Horaires fermeture_soir >= 23:00 dominant + cuisine fast-food + review keywords
                "UPDATE restaurants r
                SET r.score_ouvert_tard = LEAST(1.0,
                    -- Horaires fermeture tard (signal dominant)
                    COALESCE((SELECT
                        CASE
                            WHEN MAX(h.fermeture_soir) >= '00:00:00' AND MAX(h.fermeture_soir) <= '05:00:00' THEN 0.40
                            WHEN MAX(h.fermeture_soir) >= '23:00:00' THEN 0.35
                            WHEN MAX(h.fermeture_soir) >= '22:00:00' THEN 0.15
                            ELSE 0.03
                        END
                        FROM restaurant_horaires h WHERE h.restaurant_id = r.id AND h.ferme = 0 AND h.fermeture_soir IS NOT NULL), 0.0) +
                    -- Cuisine fast-food (souvent tard)
                    CASE
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%rapide%' OR r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%pizza%' OR r.type_cuisine LIKE '%kebab%' THEN 0.12
                        ELSE 0.03
                    END +
                    LEAST(0.10, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.keywords LIKE '%tard%') * 0.05) +
                    -- Service continu = plus flexible
                    COALESCE((SELECT IF(SUM(h2.service_continu) > 0, 0.08, 0.02) FROM restaurant_horaires h2 WHERE h2.restaurant_id = r.id AND h2.ferme = 0), 0.01) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        ELSE 0.03
                    END +
                    CASE WHEN r.nb_avis >= 5 THEN 0.06 ELSE 0.02 END +
                    CASE WHEN r.popularity_score >= 30 THEN 0.06 ELSE 0.02 END
                ) WHERE r.status = 'validated'",

                // ═══ score_instagrammable ═══
                // Photos + review keywords (belle_deco) + note_ambiance + prix
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_instagrammable = LEAST(1.0,
                    -- Photos count
                    LEAST(0.15, (SELECT COUNT(*) FROM restaurant_photos rp WHERE rp.restaurant_id = r.id) * 0.03) +
                    -- Review keywords deco/photogenique
                    LEAST(0.20, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%belle_deco%' OR ri.keywords LIKE '%cadre agr%')) * 0.05) +
                    -- note_ambiance elevee
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.5 THEN 0.20
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.0 THEN 0.12
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 3.5 THEN 0.06
                            ELSE 0.02
                        END,
                    0.02) +
                    -- Prix haut = souvent joli cadre
                    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 3 THEN 0.10 WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 2 THEN 0.04 ELSE 0.0 END +
                    COALESCE(ro.terrace, 0) * 0.08 +
                    -- Website = soin de l'image
                    CASE WHEN r.website IS NOT NULL AND r.website != '' THEN 0.05 ELSE 0.0 END +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.2 THEN 0.10
                        ELSE 0.03
                    END +
                    CASE WHEN r.nb_avis >= 10 THEN 0.06 ELSE 0.02 END
                ) WHERE r.status = 'validated'",

                // ═══ score_calme ═══
                // Review keywords (calme, cosy) - bruyant + context_tags + cuisine café/gastro
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_calme = LEAST(1.0,
                    -- Review keywords calme/cosy positif
                    LEAST(0.25, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%calme%' OR ri.keywords LIKE '%cosy%' OR ri.keywords LIKE '%intime%')) * 0.06) +
                    -- Review keywords bruyant negatif
                    - LEAST(0.15, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.keywords LIKE '%bruyant%') * 0.08) +
                    -- Context tags calme
                    LEAST(0.12, (SELECT COALESCE(SUM(ct.vote_count), 0) FROM restaurant_context_tags ct WHERE ct.restaurant_id = r.id AND ct.tag IN ('calme', 'cosy', 'intime', 'paisible')) * 0.04) +
                    -- Cuisine calme (café, gastro, salon de thé)
                    CASE
                        WHEN r.type_cuisine LIKE '%caf%' OR r.type_cuisine LIKE '%salon%th%' THEN 0.12
                        WHEN r.type_cuisine LIKE '%gastro%' OR r.type_cuisine LIKE '%fran%' THEN 0.10
                        ELSE 0.03
                    END +
                    COALESCE(ro.private_room, 0) * 0.08 +
                    COALESCE(ro.air_conditioning, 0) * 0.04 +
                    -- note_ambiance elevee
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 4.3 THEN 0.12
                            WHEN (SELECT AVG(rv.note_ambiance) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved' AND rv.note_ambiance > 0) >= 3.8 THEN 0.06
                            ELSE 0.02
                        END,
                    0.02) +
                    CASE WHEN r.nb_avis >= 5 THEN 0.05 ELSE 0.01 END +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.08
                        ELSE 0.03
                    END
                ) WHERE r.status = 'validated'",

                // ═══ score_nouveau ═══
                // created_at recent dominant + review count recent + website + photos
                "UPDATE restaurants r
                SET r.score_nouveau = LEAST(1.0,
                    -- Recence de creation (signal dominant)
                    CASE
                        WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH) THEN 0.40
                        WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) THEN 0.25
                        WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH) THEN 0.12
                        ELSE 0.02
                    END +
                    -- Reviews recentes (buzz)
                    LEAST(0.15, (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND rv.status = 'approved') * 0.05) +
                    LEAST(0.08, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.keywords LIKE '%nouveau%') * 0.04) +
                    -- Website (signe de professionnalisme)
                    CASE WHEN r.website IS NOT NULL AND r.website != '' THEN 0.08 ELSE 0.0 END +
                    -- Photos (nouveau = bien presente)
                    LEAST(0.08, (SELECT COUNT(*) FROM restaurant_photos rp WHERE rp.restaurant_id = r.id) * 0.02) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        ELSE 0.04
                    END +
                    CASE WHEN r.popularity_score >= 20 THEN 0.06 ELSE 0.02 END
                ) WHERE r.status = 'validated'",

                // ═══ score_parking ═══
                // restaurant_options.parking dominant + valet + review keywords
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_parking = LEAST(1.0,
                    COALESCE(ro.parking, 0) * 0.40 +
                    COALESCE(ro.valet_service, 0) * 0.15 +
                    LEAST(0.12, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.keywords LIKE '%parking%') * 0.04) +
                    LEAST(0.08, (SELECT COALESCE(SUM(ct.vote_count), 0) FROM restaurant_context_tags ct WHERE ct.restaurant_id = r.id AND ct.tag = 'parking') * 0.04) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        ELSE 0.04
                    END +
                    CASE WHEN r.nb_avis >= 5 THEN 0.06 ELSE 0.02 END +
                    CASE WHEN r.popularity_score >= 20 THEN 0.05 ELSE 0.01 END
                ) WHERE r.status = 'validated'",

                // ═══ score_ramadan ═══
                // Review keywords (ramadan, ftour, shour) + cuisine traditionnelle + horaires soir
                "UPDATE restaurants r
                SET r.score_ramadan = LEAST(1.0,
                    LEAST(0.25, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%ramadan%' OR ri.keywords LIKE '%ftour%')) * 0.06) +
                    LEAST(0.10, (SELECT COALESCE(SUM(ct.vote_count), 0) FROM restaurant_context_tags ct WHERE ct.restaurant_id = r.id AND ct.tag IN ('ramadan', 'ftour', 'iftar')) * 0.05) +
                    -- Cuisine traditionnelle (ftour = cuisine algerienne)
                    CASE
                        WHEN r.type_cuisine LIKE '%tradition%' OR r.type_cuisine LIKE '%lg%rien%' OR r.type_cuisine LIKE '%classique%' THEN 0.18
                        WHEN r.type_cuisine LIKE '%orient%' OR r.type_cuisine LIKE '%Grill%' THEN 0.10
                        ELSE 0.03
                    END +
                    -- Description mentionne ramadan/ftour
                    CASE WHEN r.description LIKE '%ramadan%' OR r.description LIKE '%ftour%' OR r.description LIKE '%iftar%' THEN 0.10 ELSE 0.0 END +
                    -- Horaires soir (ftour = soir)
                    COALESCE((SELECT IF(MAX(h.fermeture_soir) >= '21:00:00', 0.08, 0.02) FROM restaurant_horaires h WHERE h.restaurant_id = r.id AND h.ferme = 0), 0.01) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        ELSE 0.04
                    END +
                    CASE WHEN r.nb_avis >= 5 THEN 0.06 ELSE 0.02 END +
                    CASE WHEN r.popularity_score >= 20 THEN 0.05 ELSE 0.01 END
                ) WHERE r.status = 'validated'",

                // ═══ score_groupe ═══
                // private_room + review keywords (grand_groupe, salle_privee) + events + parking
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_groupe = LEAST(1.0,
                    COALESCE(ro.private_room, 0) * 0.25 +
                    LEAST(0.15, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%grand_groupe%' OR ri.keywords LIKE '%salle_privee%')) * 0.06) +
                    CASE WHEN r.events_enabled = 1 THEN 0.12 ELSE 0.02 END +
                    CASE WHEN r.reservations_enabled = 1 THEN 0.08 ELSE 0.01 END +
                    COALESCE(ro.parking, 0) * 0.08 +
                    -- trip_type Entre amis (grands groupes)
                    LEAST(0.10, (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.trip_type = 'Entre amis' AND rv.status = 'approved') * 0.04) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        ELSE 0.04
                    END +
                    CASE WHEN r.nb_avis >= 10 THEN 0.06 ELSE 0.02 END
                ) WHERE r.status = 'validated'",

                // ═══ score_wifi_travail ═══
                // wifi flag ultra-fort (6 restos) + cuisine café + review keywords
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_wifi_travail = LEAST(1.0,
                    COALESCE(ro.wifi, 0) * 0.40 +
                    CASE
                        WHEN r.type_cuisine LIKE '%caf%' OR r.type_cuisine LIKE '%coffee%' THEN 0.15
                        WHEN r.type_cuisine LIKE '%salon%th%' THEN 0.12
                        ELSE 0.03
                    END +
                    LEAST(0.10, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%wifi%' OR ri.keywords LIKE '%travailler%')) * 0.05) +
                    -- Service continu = rester longtemps
                    COALESCE((SELECT IF(SUM(h.service_continu) > 0, 0.08, 0.02) FROM restaurant_horaires h WHERE h.restaurant_id = r.id AND h.ferme = 0), 0.01) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.10
                        ELSE 0.04
                    END +
                    CASE WHEN r.nb_avis >= 3 THEN 0.06 ELSE 0.02 END +
                    -- Calme = bon pour travailler
                    LEAST(0.06, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%calme%' OR ri.keywords LIKE '%cosy%')) * 0.03)
                ) WHERE r.status = 'validated'",

                // ═══ score_enfants ═══
                // game_zone + baby_chair + review keywords (jeux_enfants, menu_enfant) + trip_type famille
                "UPDATE restaurants r
                LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
                SET r.score_enfants = LEAST(1.0,
                    COALESCE(ro.game_zone, 0) * 0.25 +
                    COALESCE(ro.baby_chair, 0) * 0.15 +
                    LEAST(0.18, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%jeux_enfants%' OR ri.keywords LIKE '%menu_enfant%' OR ri.keywords LIKE '%espace enfant%')) * 0.06) +
                    -- trip_type En famille
                    LEAST(0.10, (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.trip_type = 'En famille' AND rv.status = 'approved') * 0.04) +
                    -- Prix abordable (famille = budget)
                    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) <= 1 THEN 0.08 WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 2 THEN 0.05 ELSE 0.02 END +
                    -- Cuisine kid-friendly
                    CASE
                        WHEN r.type_cuisine LIKE '%pizza%' OR r.type_cuisine LIKE '%burger%' THEN 0.08
                        WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%rapide%' THEN 0.06
                        ELSE 0.02
                    END +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.08
                        ELSE 0.03
                    END +
                    CASE WHEN r.nb_avis >= 5 THEN 0.04 ELSE 0.01 END
                ) WHERE r.status = 'validated'",

                // ═══ score_traditionnel ═══
                // type_cuisine dominant (traditionnel/algérien) + review keywords + description
                "UPDATE restaurants r
                SET r.score_traditionnel = LEAST(1.0,
                    CASE
                        WHEN r.type_cuisine LIKE '%tradition%' OR r.type_cuisine LIKE '%lg%rien%' THEN 0.30
                        WHEN r.type_cuisine LIKE '%classique%' OR r.type_cuisine LIKE '%kabyle%' THEN 0.25
                        WHEN r.type_cuisine LIKE '%Grill%' OR r.type_cuisine LIKE '%chawarma%' OR r.type_cuisine LIKE '%orient%' THEN 0.12
                        ELSE 0.02
                    END +
                    LEAST(0.18, (SELECT COUNT(*) FROM review_insights ri WHERE ri.restaurant_id = r.id AND (ri.keywords LIKE '%traditionnel%' OR ri.keywords LIKE '%plat_algerien%' OR ri.keywords LIKE '%fait maison%')) * 0.05) +
                    CASE WHEN r.description LIKE '%tradition%' OR r.description LIKE '%authentique%' OR r.description LIKE '%fait maison%' OR r.description LIKE '%familial%' THEN 0.08 ELSE 0.0 END +
                    -- note_nourriture elevee
                    COALESCE(
                        CASE
                            WHEN (SELECT AVG(ri.sentiment_food) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.sentiment_food IS NOT NULL) >= 4.5 THEN 0.10
                            WHEN (SELECT AVG(ri.sentiment_food) FROM review_insights ri WHERE ri.restaurant_id = r.id AND ri.sentiment_food IS NOT NULL) >= 4.0 THEN 0.05
                            ELSE 0.01
                        END,
                    0.01) +
                    CASE
                        WHEN (r.nb_avis * r.note_moyenne + {$M} * {$C}) / (r.nb_avis + {$M}) >= 4.0 THEN 0.12
                        ELSE 0.04
                    END +
                    CASE WHEN r.nb_avis >= 10 THEN 0.08 WHEN r.nb_avis >= 3 THEN 0.04 ELSE 0.01 END +
                    CASE WHEN r.popularity_score >= 30 THEN 0.06 ELSE 0.02 END
                ) WHERE r.status = 'validated'",
            ];

            foreach ($queries as $sql) {
                $count += $this->db->exec($sql);
            }

            // Update timestamp
            $this->db->exec("UPDATE restaurants SET score_updated_at = NOW() WHERE status = 'validated'");

            // Also recalculate popularity_score with temporal decay
            $this->db->exec("
                UPDATE restaurants r SET popularity_score = (
                    COALESCE(r.note_moyenne, 0) * 20 +
                    COALESCE(r.nb_avis, 0) * 5 +
                    COALESCE(r.vues_total, 0) * 0.01 +
                    (SELECT COUNT(*) FROM orders o WHERE o.restaurant_id = r.id AND o.status = 'delivered') * 10 +
                    (SELECT IF(COUNT(*) > 0, 15, 0) FROM restaurant_awards ra WHERE ra.restaurant_id = r.id) +
                    (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) * 8 +
                    (SELECT COUNT(*) FROM orders o2 WHERE o2.restaurant_id = r.id AND o2.status = 'delivered' AND o2.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) * 12
                ) WHERE r.status = 'validated'
            ");

        } catch (\Exception $e) {
            // Log but don't crash
        }

        return $count;
    }

    /**
     * Vider le cache des poids (apres mise a jour)
     */
    public static function clearCache(): void
    {
        self::$weightsCache = null;
    }
}
