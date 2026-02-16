<?php

namespace App\Services;

use PDO;

/**
 * DataEnrichmentService — Pipeline d'enrichissement de données restaurants
 *
 * Exploite les données internes + sources externes gratuites pour enrichir
 * la table `restaurant_external_data` et améliorer les 22 scores du concierge.
 *
 * Sources internes :
 * - Reviews (4789) : prix moyens, plats populaires, ambiances, services mentionnés
 * - Descriptions (descriptif 92% + description 7%) : spécialités, ambiance, services
 * - Horaires croisés : patterns brunch weekend, nocturne, service continu
 *
 * Sources externes gratuites :
 * - Overpass API (OSM) : données restaurant, cuisine, horaires OSM
 * - Nominatim : géocodage/vérification d'adresses
 * - Sites web : menu en ligne, réservation, livraison, réseaux sociaux
 *
 * Cron : GET /api/cron/enrich-data?token=xxx&source=reviews&limit=200
 */
class DataEnrichmentService
{
    private PDO $db;
    private ExternalDataService $externalData;

    /** Patterns pour extraire les prix depuis le texte des reviews */
    private const PRICE_PATTERNS = [
        // "3000 DA", "3000DA", "3 000 DA", "3.000 DA"
        '/(\d[\d\s.,]*\d)\s*(?:DA|DZD|dinars?)/iu',
        // "3000 دج", "3000 دينار"
        '/(\d[\d\s.,]*\d)\s*(?:دج|دينار)/u',
    ];

    /** Patterns pour détecter les plats mentionnés */
    private const DISH_PATTERNS = [
        'couscous'       => '/\bcouscous\b/iu',
        'tajine'         => '/\btajine?\b/iu',
        'chorba'         => '/\bchorba\b/iu',
        'pizza'          => '/\bpizzas?\b/iu',
        'burger'         => '/\bburgers?\b/iu',
        'grillades'      => '/\bgrillade?s?\b/iu',
        'brochettes'     => '/\bbrochettes?\b/iu',
        'chawarma'       => '/\b(chawarma|shawarma|chaouarma)\b/iu',
        'escalope'       => '/\bescalopes?\b/iu',
        'salade'         => '/\bsalades?\b/iu',
        'poisson'        => '/\bpoissons?\b/iu',
        'fruits_de_mer'  => '/\b(fruits?\s+de\s+mer|crevettes?|calamars?|moules?)\b/iu',
        'pates'          => '/\b(pâtes?|p[aâ]tes?|spaghetti|tagliatelle|lasagne)\b/iu',
        'steak'          => '/\b(steak|entrecôte|filet|viande)\b/iu',
        'sushi'          => '/\b(sushi|maki|sashimi)\b/iu',
        'sandwich'       => '/\b(sandwich|panini|wrap)\b/iu',
        'crepe'          => '/\b(crêpes?|crepes?|gaufres?)\b/iu',
        'patisserie'     => '/\b(pâtisserie|gâteau|tarte|tiramisu|fondant|dessert)\b/iu',
        'cafe'           => '/\b(café|cappuccino|expresso|latte)\b/iu',
        'the'            => '/\b(thé\s+(à la menthe|vert)?|th[eé]\b)/iu',
        'bourek'         => '/\b(bourek|boureks?|brik|bricks?)\b/iu',
        'rechta'         => '/\b(rechta|chakhchoukha|berkoukes?|trida|tlitli|rfiss)\b/iu',
        'garantita'      => '/\b(garantita|karantika|garentita)\b/iu',
        'makroud'        => '/\b(makroud|zlabia|kalb\s+el\s+louz|baklava)\b/iu',
        'mhadjeb'        => '/\b(m\'?hadjeb|m\'?hajeb|msemen)\b/iu',
    ];

    /** Patterns pour extraire des signaux d'ambiance/service depuis descriptions */
    private const DESC_SIGNAL_PATTERNS = [
        // Vue
        'vue_mer'           => '/\b(vue\s+(sur\s+)?(la\s+)?mer|front\s+de\s+mer|bord\s+de\s+mer|face\s+[àa]\s+la\s+mer)\b/iu',
        'vue_panoramique'   => '/\b(panoram|vue\s+(magnifique|superbe|imprenable|splendide)|rooftop|toit[- ]terrasse)\b/iu',
        // Ambiance
        'calme'             => '/\b(calme|paisible|tranquille|serein|zen|cosy|cozy|feutr[eé])\b/iu',
        'festif'            => '/\b(ambiance|musique|animation|soir[eé]e|festif|convivial)\b/iu',
        'romantique'        => '/\b(romantique|intime|tamisé|chandelle|bougie|[eé]l[eé]gant)\b/iu',
        // Services
        'livraison'         => '/\b(livraison|delivery|[àa]\s+domicile|emporter|takeaway)\b/iu',
        'reservation'       => '/\b(r[eé]serv(er|ation)|sur\s+r[eé]servation)\b/iu',
        'parking_desc'      => '/\b(parking|stationnement|garage)\b/iu',
        'wifi_desc'         => '/\b(wifi|wi[- ]fi|internet)\b/iu',
        'terrasse_desc'     => '/\b(terrasse|ext[eé]rieur|plein\s+air|jardin)\b/iu',
        'climatise'         => '/\b(climatis[eé]|air\s+conditionn[eé])\b/iu',
        // Spécialités
        'traditionnel'      => '/\b(tradition[n]?el|authentique|typique|artisanal|fait\s+maison|recette\s+de\s+grand[- ]m[eè]re)\b/iu',
        'gastronomique'     => '/\b(gastronomique|gastro|haut\s+de\s+gamme|fine\s+dining|raffiné)\b/iu',
        'brunch_desc'       => '/\b(brunch|petit[- ]d[eé]jeuner|breakfast|ftour)\b/iu',
        'healthy_desc'      => '/\b(healthy|sant[eé]|bio|di[eé]t[eé]tique|v[eé]g[eé]tarien|vegan)\b/iu',
        'enfants_desc'      => '/\b(enfants?|famille|aire\s+de\s+jeu|espace\s+enfant|menu\s+enfant)\b/iu',
        'groupe_desc'       => '/\b(salle\s+priv[eé]e?|salon\s+priv[eé]|privatiser|banquet|grand\s+groupe|s[eé]minaire)\b/iu',
    ];

    /** Normalisation type_cuisine : corrections de doublons */
    private const CUISINE_NORMALIZATION = [
        // Algerien
        'Algerien'              => 'Algerien traditionnel',
        'Algérien'              => 'Algerien traditionnel',
        'algerien'              => 'Algerien traditionnel',
        'Algerien Traditionnel' => 'Algerien traditionnel',
        'Algérien Traditionnel' => 'Algerien traditionnel',
        'Traditionnel'          => 'Algerien traditionnel',
        'traditionnel'          => 'Algerien traditionnel',
        'Cuisine Algerienne'    => 'Algerien traditionnel',
        'Cuisine algérienne'    => 'Algerien traditionnel',
        // Poissons/Fruits de mer
        'Poissons et fruits de mer' => 'Poissons/Fruits de mer',
        'Poissons/fruits de mer'    => 'Poissons/Fruits de mer',
        'Fruits de mer'             => 'Poissons/Fruits de mer',
        'fruits de mer'             => 'Poissons/Fruits de mer',
        // Fast food
        'Fast food restaurant'  => 'Fast food',
        'Restauration rapide'   => 'Fast food',
        'fast food'             => 'Fast food',
        // Classique
        'Classique'             => 'classique',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->externalData = new ExternalDataService($db);
    }

    /**
     * Route dispatcher — appelé par le cron controller
     */
    public function enrichBySource(string $source, int $limit = 200): array
    {
        return match ($source) {
            'reviews'       => $this->mineReviews($limit),
            'descriptions'  => $this->mineDescriptions($limit),
            'horaires'      => $this->analyzeHoraires(),
            'normalize'     => $this->normalizeCuisineTypes(),
            'osm'           => $this->fetchOverpassOSM($limit),
            'websites'      => $this->scrapeWebsites($limit),
            'all'           => $this->enrichAll($limit),
            default         => ['source' => $source, 'processed' => 0, 'errors' => 0, 'message' => 'Source inconnue'],
        };
    }

    /**
     * Run all enrichment sources in sequence
     */
    public function enrichAll(int $limit = 200): array
    {
        $results = [];
        $results['normalize'] = $this->normalizeCuisineTypes();
        $results['reviews'] = $this->mineReviews($limit);
        $results['descriptions'] = $this->mineDescriptions($limit);
        $results['horaires'] = $this->analyzeHoraires();
        $results['osm'] = $this->fetchOverpassOSM($limit);
        $results['websites'] = $this->scrapeWebsites($limit);

        $totalProcessed = 0;
        $totalErrors = 0;
        foreach ($results as $r) {
            $totalProcessed += $r['processed'] ?? 0;
            $totalErrors += $r['errors'] ?? 0;
        }

        return [
            'source' => 'all',
            'processed' => $totalProcessed,
            'errors' => $totalErrors,
            'details' => $results,
        ];
    }

    // ═══════════════════════════════════════════════════════
    // PHASE A1 — Mining des reviews
    // ═══════════════════════════════════════════════════════

    /**
     * Extract prices, popular dishes, and service signals from review texts
     * Stores results in restaurant_external_data with source='review_mining'
     */
    public function mineReviews(int $limit = 200): array
    {
        $processed = 0;
        $errors = 0;

        try {
            // Get restaurants that haven't been mined yet (or expired)
            $stmt = $this->db->prepare("
                SELECT r.id
                FROM restaurants r
                WHERE r.status = 'validated'
                  AND r.id NOT IN (
                      SELECT DISTINCT red.restaurant_id
                      FROM restaurant_external_data red
                      WHERE red.source = 'review_mining'
                        AND red.data_key = 'avg_price_mentioned'
                        AND (red.expires_at IS NULL OR red.expires_at > NOW())
                  )
                ORDER BY r.nb_avis DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $restaurantIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($restaurantIds as $restaurantId) {
                try {
                    $this->mineRestaurantReviews((int)$restaurantId);
                    $processed++;
                } catch (\Exception $e) {
                    error_log("[DataEnrichment] Review mining error for resto {$restaurantId}: " . $e->getMessage());
                    $errors++;
                }
            }
        } catch (\Exception $e) {
            error_log("[DataEnrichment] Review mining global error: " . $e->getMessage());
            $errors++;
        }

        return ['source' => 'review_mining', 'processed' => $processed, 'errors' => $errors];
    }

    /**
     * Mine all reviews for a single restaurant
     */
    private function mineRestaurantReviews(int $restaurantId): void
    {
        $stmt = $this->db->prepare("
            SELECT message FROM reviews
            WHERE restaurant_id = :rid AND status = 'approved'
              AND message IS NOT NULL AND LENGTH(message) >= 20
        ");
        $stmt->execute([':rid' => $restaurantId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($reviews)) return;

        $allPrices = [];
        $dishCounts = [];
        $serviceSignals = [];
        $ambianceSignals = [];

        foreach ($reviews as $text) {
            // Extract prices
            foreach (self::PRICE_PATTERNS as $pattern) {
                if (preg_match_all($pattern, $text, $matches)) {
                    foreach ($matches[1] as $priceStr) {
                        $price = (float)preg_replace('/[\s.,]/', '', $priceStr);
                        // Sanity check: 50 DA to 50,000 DA
                        if ($price >= 50 && $price <= 50000) {
                            $allPrices[] = $price;
                        }
                    }
                }
            }

            // Count dish mentions
            foreach (self::DISH_PATTERNS as $dish => $pattern) {
                if (preg_match($pattern, $text)) {
                    $dishCounts[$dish] = ($dishCounts[$dish] ?? 0) + 1;
                }
            }

            // Service/ambiance signals from review text (broader than ReviewAnalyzer keywords)
            if (preg_match('/\b(livr|delivery|domicile|emporter)/iu', $text)) {
                $serviceSignals['delivery_mentioned'] = ($serviceSignals['delivery_mentioned'] ?? 0) + 1;
            }
            if (preg_match('/\b(parking|stationn|garag)/iu', $text)) {
                $serviceSignals['parking_mentioned'] = ($serviceSignals['parking_mentioned'] ?? 0) + 1;
            }
            if (preg_match('/\b(wifi|wi-fi|internet)/iu', $text)) {
                $serviceSignals['wifi_mentioned'] = ($serviceSignals['wifi_mentioned'] ?? 0) + 1;
            }
            if (preg_match('/\b(terrasse|ext[eé]rieur|dehors|jardin)/iu', $text)) {
                $serviceSignals['terrace_mentioned'] = ($serviceSignals['terrace_mentioned'] ?? 0) + 1;
            }
            if (preg_match('/\b(calme|paisible|tranquille|serein|cosy)/iu', $text)) {
                $ambianceSignals['calme'] = ($ambianceSignals['calme'] ?? 0) + 1;
            }
            if (preg_match('/\b(bruyant|bruit|musique\s+fort)/iu', $text)) {
                $ambianceSignals['bruyant'] = ($ambianceSignals['bruyant'] ?? 0) + 1;
            }
            if (preg_match('/\b(vue\s+(mer|magnifique|superbe|imprenable|panoram)|rooftop|toit)/iu', $text)) {
                $ambianceSignals['vue'] = ($ambianceSignals['vue'] ?? 0) + 1;
            }
            if (preg_match('/\b(belle?\s+d[eé]co|design|magnifique\s+cadre|joli\s+cadre|beau\s+cadre)/iu', $text)) {
                $ambianceSignals['belle_deco'] = ($ambianceSignals['belle_deco'] ?? 0) + 1;
            }
        }

        $reviewCount = count($reviews);
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Store average price mentioned
        if (!empty($allPrices)) {
            $avgPrice = array_sum($allPrices) / count($allPrices);
            $medianPrice = $this->median($allPrices);
            $this->externalData->storeData($restaurantId, 'review_mining', 'avg_price_mentioned', null, round($avgPrice), 0.8, $expires);
            $this->externalData->storeData($restaurantId, 'review_mining', 'median_price_mentioned', null, round($medianPrice), 0.8, $expires);
            $this->externalData->storeData($restaurantId, 'review_mining', 'price_mentions_count', null, (float)count($allPrices), 1.0, $expires);
        } else {
            $this->externalData->storeData($restaurantId, 'review_mining', 'avg_price_mentioned', null, null, 0.5, $expires);
        }

        // Store popular dishes (top 5)
        arsort($dishCounts);
        $topDishes = array_slice($dishCounts, 0, 5, true);
        if (!empty($topDishes)) {
            $this->externalData->storeData(
                $restaurantId, 'review_mining', 'popular_dishes',
                json_encode($topDishes, JSON_UNESCAPED_UNICODE), null,
                0.9, $expires, json_encode(['total_reviews' => $reviewCount, 'all_dishes' => $dishCounts])
            );
        }

        // Store service signals
        foreach ($serviceSignals as $signal => $count) {
            $confidence = min(1.0, $count / max(1, $reviewCount) * 5); // 20% mention rate = 1.0 confidence
            $this->externalData->storeData($restaurantId, 'review_mining', $signal, null, (float)$count, $confidence, $expires);
        }

        // Store ambiance signals
        foreach ($ambianceSignals as $signal => $count) {
            $confidence = min(1.0, $count / max(1, $reviewCount) * 5);
            $this->externalData->storeData($restaurantId, 'review_mining', 'ambiance_' . $signal, null, (float)$count, $confidence, $expires);
        }

        // Store review count used for mining
        $this->externalData->storeData($restaurantId, 'review_mining', 'reviews_mined', null, (float)$reviewCount, 1.0, $expires);
    }

    // ═══════════════════════════════════════════════════════
    // PHASE A2 — Mining des descriptions
    // ═══════════════════════════════════════════════════════

    /**
     * Extract signals from restaurant descriptions (descriptif + description)
     */
    public function mineDescriptions(int $limit = 200): array
    {
        $processed = 0;
        $errors = 0;

        try {
            $stmt = $this->db->prepare("
                SELECT r.id, r.description, r.descriptif
                FROM restaurants r
                WHERE r.status = 'validated'
                  AND (r.description IS NOT NULL AND r.description != ''
                       OR r.descriptif IS NOT NULL AND r.descriptif != '')
                  AND r.id NOT IN (
                      SELECT DISTINCT red.restaurant_id
                      FROM restaurant_external_data red
                      WHERE red.source = 'description_mining'
                        AND (red.expires_at IS NULL OR red.expires_at > NOW())
                  )
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $expires = date('Y-m-d H:i:s', strtotime('+90 days'));

            foreach ($restaurants as $r) {
                try {
                    $text = trim(($r['description'] ?? '') . ' ' . ($r['descriptif'] ?? ''));
                    if (mb_strlen($text) < 10) continue;

                    $signals = [];
                    foreach (self::DESC_SIGNAL_PATTERNS as $signal => $pattern) {
                        if (preg_match($pattern, $text)) {
                            $signals[] = $signal;
                        }
                    }

                    // Store signals as JSON array
                    $this->externalData->storeData(
                        (int)$r['id'], 'description_mining', 'desc_signals',
                        json_encode($signals, JSON_UNESCAPED_UNICODE),
                        (float)count($signals),
                        0.85, $expires
                    );

                    // Also extract dishes from descriptions
                    $dishes = [];
                    foreach (self::DISH_PATTERNS as $dish => $pattern) {
                        if (preg_match($pattern, $text)) {
                            $dishes[] = $dish;
                        }
                    }
                    if (!empty($dishes)) {
                        $this->externalData->storeData(
                            (int)$r['id'], 'description_mining', 'desc_dishes',
                            json_encode($dishes, JSON_UNESCAPED_UNICODE),
                            (float)count($dishes),
                            0.9, $expires
                        );
                    }

                    $processed++;
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        } catch (\Exception $e) {
            $errors++;
        }

        return ['source' => 'description_mining', 'processed' => $processed, 'errors' => $errors];
    }

    // ═══════════════════════════════════════════════════════
    // PHASE A3 — Normalisation type_cuisine
    // ═══════════════════════════════════════════════════════

    /**
     * Normalize duplicate/inconsistent cuisine types
     */
    public function normalizeCuisineTypes(): array
    {
        $updated = 0;

        try {
            $stmt = $this->db->prepare("UPDATE restaurants SET type_cuisine = :new WHERE type_cuisine = :old AND status = 'validated'");

            foreach (self::CUISINE_NORMALIZATION as $old => $new) {
                $stmt->execute([':new' => $new, ':old' => $old]);
                $updated += $stmt->rowCount();
            }

            // Trim whitespace
            $updated += $this->db->exec("UPDATE restaurants SET type_cuisine = TRIM(type_cuisine) WHERE type_cuisine != TRIM(type_cuisine) AND status = 'validated'");

        } catch (\Exception $e) {
            error_log("[DataEnrichment] Normalize error: " . $e->getMessage());
        }

        return ['source' => 'normalize', 'processed' => $updated, 'errors' => 0];
    }

    // ═══════════════════════════════════════════════════════
    // PHASE A4 — Patterns horaires avancés
    // ═══════════════════════════════════════════════════════

    /**
     * Analyze opening hours patterns: brunch weekend, nocturne, service continu
     */
    public function analyzeHoraires(): array
    {
        $processed = 0;

        try {
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

            // Get all restaurants with horaires
            $stmt = $this->db->query("
                SELECT h.restaurant_id,
                       h.jour_semaine,
                       h.ouverture_matin, h.fermeture_matin,
                       h.ouverture_soir, h.fermeture_soir,
                       h.ferme, h.service_continu
                FROM restaurant_horaires h
                INNER JOIN restaurants r ON r.id = h.restaurant_id AND r.status = 'validated'
                ORDER BY h.restaurant_id, h.jour_semaine
            ");
            $all = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by restaurant
            $grouped = [];
            foreach ($all as $row) {
                $grouped[$row['restaurant_id']][] = $row;
            }

            foreach ($grouped as $restaurantId => $horaires) {
                $patterns = $this->detectHorairePatterns($horaires);

                foreach ($patterns as $key => $value) {
                    $this->externalData->storeData(
                        (int)$restaurantId, 'horaire_analysis', $key,
                        is_bool($value) ? ($value ? '1' : '0') : (string)$value,
                        is_numeric($value) ? (float)$value : ($value ? 1.0 : 0.0),
                        1.0, $expires
                    );
                }

                $processed++;
            }
        } catch (\Exception $e) {
            error_log("[DataEnrichment] Horaires error: " . $e->getMessage());
        }

        return ['source' => 'horaire_analysis', 'processed' => $processed, 'errors' => 0];
    }

    /**
     * Detect patterns from a restaurant's weekly schedule
     */
    private function detectHorairePatterns(array $horaires): array
    {
        $patterns = [
            'opens_early' => false,      // Ouverture <= 08:00
            'brunch_weekend' => false,    // Ouvert le matin vendredi/samedi
            'late_night' => false,        // Fermeture >= 23:00
            'after_midnight' => false,    // Fermeture 00:00-05:00
            'service_continu' => false,   // Au moins un jour service continu
            'open_7_days' => true,        // Ouvert 7/7
            'days_open' => 0,             // Nombre de jours ouverts
            'earliest_open' => null,      // Heure d'ouverture la plus tôt
            'latest_close' => null,       // Heure de fermeture la plus tard
        ];

        foreach ($horaires as $h) {
            if ($h['ferme']) {
                $patterns['open_7_days'] = false;
                continue;
            }

            $patterns['days_open']++;

            // Earliest opening
            $openTime = $h['ouverture_matin'] ?? $h['ouverture_soir'];
            if ($openTime && ($patterns['earliest_open'] === null || $openTime < $patterns['earliest_open'])) {
                $patterns['earliest_open'] = $openTime;
            }

            // Opens early (for brunch)
            if ($h['ouverture_matin'] && $h['ouverture_matin'] <= '08:00:00') {
                $patterns['opens_early'] = true;
            }

            // Brunch weekend: ouvert le matin vendredi (5) ou samedi (6)
            // jour: 0=lundi ... 6=dimanche (or 1-7 depends on DB)
            $jour = (int)$h['jour_semaine'];
            if (in_array($jour, [5, 6, 0, 7]) && $h['ouverture_matin'] && $h['ouverture_matin'] <= '10:00:00') {
                $patterns['brunch_weekend'] = true;
            }

            // Late night
            $closeTime = $h['fermeture_soir'];
            if ($closeTime) {
                if ($closeTime >= '23:00:00' && $closeTime <= '23:59:59') {
                    $patterns['late_night'] = true;
                }
                if ($closeTime >= '00:00:00' && $closeTime <= '05:00:00') {
                    $patterns['after_midnight'] = true;
                    $patterns['late_night'] = true;
                }
                if ($patterns['latest_close'] === null || $closeTime > $patterns['latest_close']) {
                    $patterns['latest_close'] = $closeTime;
                }
            }

            // Service continu
            if ($h['service_continu']) {
                $patterns['service_continu'] = true;
            }
        }

        return $patterns;
    }

    // ═══════════════════════════════════════════════════════
    // PHASE B1 — Overpass API (OpenStreetMap)
    // ═══════════════════════════════════════════════════════

    /**
     * Fetch restaurant data from OSM via Overpass API
     * Matches our restaurants by name+proximity (100% automatic, confidence threshold)
     */
    public function fetchOverpassOSM(int $limit = 200): array
    {
        $processed = 0;
        $errors = 0;

        try {
            // Get restaurants not yet enriched from OSM
            $stmt = $this->db->prepare("
                SELECT r.id, r.nom, r.ville, r.gps_latitude, r.gps_longitude
                FROM restaurants r
                WHERE r.status = 'validated'
                  AND r.gps_latitude IS NOT NULL AND r.gps_latitude != 0
                  AND r.id NOT IN (
                      SELECT DISTINCT red.restaurant_id
                      FROM restaurant_external_data red
                      WHERE red.source = 'osm'
                        AND (red.expires_at IS NULL OR red.expires_at > NOW())
                  )
                ORDER BY r.nb_avis DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group by city for batch Overpass queries (max ~50 per query)
            $byCity = [];
            foreach ($restaurants as $r) {
                $city = $r['ville'] ?? 'unknown';
                $byCity[$city][] = $r;
            }

            $expires = date('Y-m-d H:i:s', strtotime('+90 days'));

            foreach ($byCity as $city => $cityRestos) {
                // Compute bbox from restaurant GPS coords (+ 0.02° padding ≈ 2km)
                $lats = array_column($cityRestos, 'gps_latitude');
                $lngs = array_column($cityRestos, 'gps_longitude');
                $bbox = [
                    'south' => min($lats) - 0.02,
                    'west'  => min($lngs) - 0.02,
                    'north' => max($lats) + 0.02,
                    'east'  => max($lngs) + 0.02,
                ];

                $osmData = $this->queryOverpassByBbox($bbox);

                if ($osmData === null) {
                    $errors++;
                    error_log("[DataEnrichment] Overpass failed for city: {$city}");
                    continue;
                }

                foreach ($cityRestos as $resto) {
                    $match = $this->matchOSMRestaurant($resto, $osmData);

                    if ($match !== null) {
                        // Store matched OSM data
                        $this->externalData->storeData(
                            (int)$resto['id'], 'osm', 'osm_id',
                            (string)$match['osm_id'], null,
                            $match['confidence'], $expires
                        );

                        if (!empty($match['cuisine'])) {
                            $this->externalData->storeData(
                                (int)$resto['id'], 'osm', 'osm_cuisine',
                                $match['cuisine'], null,
                                $match['confidence'], $expires
                            );
                        }

                        if (!empty($match['opening_hours'])) {
                            $this->externalData->storeData(
                                (int)$resto['id'], 'osm', 'osm_opening_hours',
                                $match['opening_hours'], null,
                                $match['confidence'] * 0.8, $expires
                            );
                        }

                        if (!empty($match['phone'])) {
                            $this->externalData->storeData(
                                (int)$resto['id'], 'osm', 'osm_phone',
                                $match['phone'], null,
                                $match['confidence'], $expires
                            );
                        }

                        if (!empty($match['website'])) {
                            $this->externalData->storeData(
                                (int)$resto['id'], 'osm', 'osm_website',
                                $match['website'], null,
                                $match['confidence'], $expires
                            );
                        }

                        if (!empty($match['addr_street'])) {
                            $this->externalData->storeData(
                                (int)$resto['id'], 'osm', 'osm_address',
                                $match['addr_street'], null,
                                $match['confidence'], $expires
                            );
                        }

                        $processed++;
                    } else {
                        // No match — store that we tried
                        $this->externalData->storeData(
                            (int)$resto['id'], 'osm', 'osm_id',
                            'no_match', null, 0.0, $expires
                        );
                    }
                }

                // Rate limit: 1 request per 5 seconds for Overpass (avoid 429)
                sleep(5);
            }
        } catch (\Exception $e) {
            error_log("[DataEnrichment] OSM error: " . $e->getMessage());
            $errors++;
        }

        return ['source' => 'osm', 'processed' => $processed, 'errors' => $errors];
    }

    /**
     * Query Overpass API for restaurants within a bounding box
     * @param array $bbox [south, west, north, east]
     * @return array|null Array of OSM elements, or null on error
     */
    private function queryOverpassByBbox(array $bbox): ?array
    {
        $b = sprintf('%.4f,%.4f,%.4f,%.4f', $bbox['south'], $bbox['west'], $bbox['north'], $bbox['east']);

        $query = "[out:json][timeout:30];
(
  node[\"amenity\"=\"restaurant\"]({$b});
  way[\"amenity\"=\"restaurant\"]({$b});
  node[\"amenity\"=\"fast_food\"]({$b});
  way[\"amenity\"=\"fast_food\"]({$b});
  node[\"amenity\"=\"cafe\"]({$b});
  way[\"amenity\"=\"cafe\"]({$b});
);
out center tags;";

        $url = 'https://overpass-api.de/api/interpreter';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'data=' . urlencode($query),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 35,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'LeBonResto/1.0 (restaurant-enrichment)',
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            error_log("[DataEnrichment] Overpass HTTP {$httpCode} bbox {$b}: {$curlError}");
            return null;
        }

        $data = json_decode($response, true);
        if (!is_array($data) || empty($data['elements'])) {
            return [];
        }

        return $data['elements'];
    }

    /**
     * Match a restaurant to OSM data using name similarity + distance
     * @return array|null Match with confidence, or null if no match
     */
    private function matchOSMRestaurant(array $resto, array $osmElements): ?array
    {
        $bestMatch = null;
        $bestScore = 0;

        $restoName = mb_strtolower(trim($resto['nom']));
        $restoLat = (float)$resto['gps_latitude'];
        $restoLng = (float)$resto['gps_longitude'];

        foreach ($osmElements as $el) {
            $tags = $el['tags'] ?? [];
            $osmName = mb_strtolower(trim($tags['name'] ?? $tags['name:fr'] ?? $tags['name:ar'] ?? ''));

            if (empty($osmName)) continue;

            // Calculate name similarity (Levenshtein-based)
            $nameSimilarity = $this->nameSimilarity($restoName, $osmName);

            // Calculate distance
            $osmLat = (float)($el['lat'] ?? $el['center']['lat'] ?? 0);
            $osmLng = (float)($el['lon'] ?? $el['center']['lon'] ?? 0);

            if ($osmLat == 0 || $osmLng == 0) continue;

            $distance = $this->haversineDistance($restoLat, $restoLng, $osmLat, $osmLng);

            // Combined score: name similarity (70%) + proximity (30%)
            // Distance bonus: <100m = 1.0, <500m = 0.8, <1km = 0.5, >2km = 0
            $proxScore = $distance < 0.1 ? 1.0 : ($distance < 0.5 ? 0.8 : ($distance < 1.0 ? 0.5 : ($distance < 2.0 ? 0.2 : 0)));

            $combinedScore = ($nameSimilarity * 0.7) + ($proxScore * 0.3);

            if ($combinedScore > $bestScore && $combinedScore >= 0.55) {
                $bestScore = $combinedScore;
                $bestMatch = [
                    'osm_id' => $el['id'],
                    'osm_type' => $el['type'],
                    'cuisine' => $tags['cuisine'] ?? null,
                    'opening_hours' => $tags['opening_hours'] ?? null,
                    'phone' => $tags['phone'] ?? $tags['contact:phone'] ?? null,
                    'website' => $tags['website'] ?? $tags['contact:website'] ?? null,
                    'addr_street' => trim(($tags['addr:housenumber'] ?? '') . ' ' . ($tags['addr:street'] ?? '')),
                    'confidence' => round($bestScore, 2),
                    'distance_km' => round($distance, 3),
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Name similarity score (0-1) using normalized Levenshtein
     * Handles common restaurant name variations (Le/La/El/Restaurant prefix)
     */
    private function nameSimilarity(string $name1, string $name2): float
    {
        // Normalize: remove common prefixes, articles, punctuation
        $normalize = function (string $s): string {
            $s = mb_strtolower($s);
            $s = preg_replace('/^(le |la |l\'|el |al |restaurant |resto |chez |pizzeria |café |cafe )/u', '', $s);
            $s = preg_replace('/[^a-zà-ÿ0-9\s]/u', '', $s);
            return trim(preg_replace('/\s+/', ' ', $s));
        };

        $n1 = $normalize($name1);
        $n2 = $normalize($name2);

        if ($n1 === $n2) return 1.0;
        if (empty($n1) || empty($n2)) return 0.0;

        // Levenshtein similarity (normalized)
        $maxLen = max(mb_strlen($n1), mb_strlen($n2));
        $lev = levenshtein($n1, $n2);
        $levSim = 1.0 - ($lev / $maxLen);

        // Contains check (one name contains the other)
        $containsBonus = 0;
        if (str_contains($n1, $n2) || str_contains($n2, $n1)) {
            $containsBonus = 0.2;
        }

        return min(1.0, $levSim + $containsBonus);
    }

    // ═══════════════════════════════════════════════════════
    // PHASE B2 — Website scraping
    // ═══════════════════════════════════════════════════════

    /**
     * Check restaurant websites for menu, reservation, delivery, social links
     */
    public function scrapeWebsites(int $limit = 200): array
    {
        $processed = 0;
        $errors = 0;

        try {
            $stmt = $this->db->prepare("
                SELECT r.id, r.website
                FROM restaurants r
                WHERE r.status = 'validated'
                  AND r.website IS NOT NULL AND r.website != ''
                  AND r.website NOT IN ('N/A', 'n/a', '-', 'Aucun', 'aucun')
                  AND r.website LIKE 'http%'
                  AND r.id NOT IN (
                      SELECT DISTINCT red.restaurant_id
                      FROM restaurant_external_data red
                      WHERE red.source = 'website'
                        AND (red.expires_at IS NULL OR red.expires_at > NOW())
                  )
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $expires = date('Y-m-d H:i:s', strtotime('+60 days'));

            foreach ($restaurants as $r) {
                try {
                    $result = $this->analyzeWebsite($r['website']);

                    foreach ($result as $key => $value) {
                        $this->externalData->storeData(
                            (int)$r['id'], 'website', $key,
                            is_bool($value) ? ($value ? '1' : '0') : (is_array($value) ? json_encode($value) : (string)$value),
                            is_numeric($value) ? (float)$value : ($value ? 1.0 : 0.0),
                            0.9, $expires
                        );
                    }

                    $processed++;
                    // Rate limit: 500ms between requests
                    usleep(500000);
                } catch (\Exception $e) {
                    $errors++;
                }
            }
        } catch (\Exception $e) {
            $errors++;
        }

        return ['source' => 'website', 'processed' => $processed, 'errors' => $errors];
    }

    /**
     * Analyze a single website URL
     */
    private function analyzeWebsite(string $url): array
    {
        // Ensure URL has protocol
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }

        $result = [
            'website_active' => false,
            'website_has_menu' => false,
            'website_has_reservation' => false,
            'website_has_delivery' => false,
            'website_language' => null,
            'website_facebook' => null,
            'website_instagram' => null,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; LeBonResto/1.0)',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 400 || $html === false) {
            return $result;
        }

        $result['website_active'] = true;
        $htmlLower = mb_strtolower($html);

        // Check for menu
        if (preg_match('/\b(menu|carte|nos\s+plats|our\s+menu|قائمة)/iu', $htmlLower)) {
            $result['website_has_menu'] = true;
        }

        // Check for reservation
        if (preg_match('/\b(r[eé]serv|book\s+(a\s+)?table|booking|حجز)/iu', $htmlLower)) {
            $result['website_has_reservation'] = true;
        }

        // Check for delivery
        if (preg_match('/\b(livraison|delivery|commander|order\s+online|توصيل)/iu', $htmlLower)) {
            $result['website_has_delivery'] = true;
        }

        // Detect language
        if (preg_match('/<html[^>]+lang=["\']?(ar|fr|en)/i', $html, $m)) {
            $result['website_language'] = $m[1];
        } elseif (preg_match('/[\x{0600}-\x{06FF}]{10,}/u', $html)) {
            $result['website_language'] = 'ar';
        } elseif (preg_match('/\b(restaurant|bienvenue|accueil|notre)\b/i', $htmlLower)) {
            $result['website_language'] = 'fr';
        }

        // Extract social media links
        if (preg_match('/facebook\.com\/([a-zA-Z0-9._-]+)/i', $html, $m)) {
            $result['website_facebook'] = $m[1];
        }
        if (preg_match('/instagram\.com\/([a-zA-Z0-9._-]+)/i', $html, $m)) {
            $result['website_instagram'] = $m[1];
        }

        return $result;
    }

    // ═══════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════

    /**
     * Calculate median of an array of numbers
     */
    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $mid = (int)floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$mid - 1] + $values[$mid]) / 2;
        }
        return $values[$mid];
    }

    /**
     * Haversine distance in km
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Get enrichment stats summary
     */
    public function getStats(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT source,
                       COUNT(*) as total_records,
                       COUNT(DISTINCT restaurant_id) as restaurants,
                       SUM(CASE WHEN expires_at IS NOT NULL AND expires_at < NOW() THEN 1 ELSE 0 END) as expired,
                       MIN(fetched_at) as oldest,
                       MAX(fetched_at) as newest
                FROM restaurant_external_data
                GROUP BY source
                ORDER BY source
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
