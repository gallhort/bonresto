<?php

namespace App\Services;

use PDO;

/**
 * ExternalDataService — Infrastructure Phase 2 pour données externes
 *
 * Stocke et gère les données externes (Google Places, Facebook, Yassir, etc.)
 * dans la table `restaurant_external_data`.
 *
 * Les méthodes fetch* sont des stubs en attente d'API keys/implémentation.
 * Chaque source est indépendante et peut être déclenchée via cron :
 *   GET /api/cron/fetch-external-data?token=xxx&source=google_places&limit=50
 *
 * Architecture :
 * - Chaque fetch retourne le nombre de records insérés/mis à jour
 * - `storeData()` utilise UPSERT (ON DUPLICATE KEY UPDATE)
 * - `cleanupExpired()` supprime les données périmées
 * - Les scores dans ScoringService peuvent lire `restaurant_external_data`
 *   via sous-requêtes quand les données sont disponibles
 */
class ExternalDataService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Route dispatcher — appelé par le cron controller
     * @return array ['source' => string, 'processed' => int, 'errors' => int]
     */
    public function fetchBySource(string $source, int $limit = 50): array
    {
        return match ($source) {
            'google_places' => $this->fetchGooglePlaces($limit),
            'facebook'      => $this->fetchFacebook($limit),
            'yassir'        => $this->scrapeYassir($limit),
            'jumia'         => $this->scrapeJumia($limit),
            'instagram'     => $this->fetchInstagram($limit),
            'website'       => $this->scrapeWebsite($limit),
            default         => ['source' => $source, 'processed' => 0, 'errors' => 0, 'message' => 'Source inconnue'],
        };
    }

    /**
     * Google Places API — utilise google_place_id (854 restos)
     * Budget: ~$24 pour 1405 restos (Place Details Basic + Atmosphere)
     *
     * Données récupérables :
     * - google_rating (note Google)
     * - google_reviews_count
     * - google_price_level (0-4)
     * - google_opening_hours
     * - google_photos_count
     * - google_types (restaurant, cafe, etc.)
     *
     * @stub En attente API key (mars 2026)
     */
    private function fetchGooglePlaces(int $limit): array
    {
        // TODO: Implémenter quand l'API key sera disponible
        // $apiKey = getenv('GOOGLE_PLACES_API_KEY');
        // if (empty($apiKey)) return [...];
        //
        // $stmt = $this->db->prepare("
        //     SELECT r.id, r.google_place_id
        //     FROM restaurants r
        //     LEFT JOIN restaurant_external_data red
        //         ON red.restaurant_id = r.id AND red.source = 'google_places' AND red.data_key = 'google_rating'
        //     WHERE r.status = 'validated'
        //       AND r.google_place_id IS NOT NULL AND r.google_place_id != ''
        //       AND (red.id IS NULL OR red.expires_at < NOW())
        //     ORDER BY r.popularity_score DESC
        //     LIMIT :lim
        // ");
        // foreach ($restaurants as $r) {
        //     $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$r['google_place_id']}&fields=rating,user_ratings_total,price_level,opening_hours,photos,types&key={$apiKey}";
        //     // curl fetch, parse, storeData()
        // }

        return ['source' => 'google_places', 'processed' => 0, 'errors' => 0, 'message' => 'Stub — en attente API key'];
    }

    /**
     * Facebook Graph API — pages restaurants (gratuit)
     *
     * Données récupérables :
     * - fb_rating (note Facebook)
     * - fb_likes (nombre de likes page)
     * - fb_checkins
     * - fb_hours
     *
     * @stub En attente implémentation
     */
    private function fetchFacebook(int $limit): array
    {
        return ['source' => 'facebook', 'processed' => 0, 'errors' => 0, 'message' => 'Stub — en attente implémentation'];
    }

    /**
     * Yassir Food — scraping menus et prix (gratuit)
     *
     * Données récupérables :
     * - yassir_available (bool)
     * - yassir_avg_price
     * - yassir_menu_items_count
     * - yassir_delivery_time
     *
     * @stub En attente implémentation
     */
    private function scrapeYassir(int $limit): array
    {
        return ['source' => 'yassir', 'processed' => 0, 'errors' => 0, 'message' => 'Stub — en attente implémentation'];
    }

    /**
     * Jumia Food — scraping menus et prix (gratuit)
     *
     * Données récupérables :
     * - jumia_available (bool)
     * - jumia_avg_price
     * - jumia_rating
     * - jumia_delivery_fee
     *
     * @stub En attente implémentation
     */
    private function scrapeJumia(int $limit): array
    {
        return ['source' => 'jumia', 'processed' => 0, 'errors' => 0, 'message' => 'Stub — en attente implémentation'];
    }

    /**
     * Instagram — hashtag counts via public web (gratuit)
     *
     * Données récupérables :
     * - ig_hashtag_count (nombre de posts avec le hashtag du restaurant)
     * - ig_profile_exists (bool)
     *
     * @stub En attente implémentation
     */
    private function fetchInstagram(int $limit): array
    {
        return ['source' => 'instagram', 'processed' => 0, 'errors' => 0, 'message' => 'Stub — en attente implémentation'];
    }

    /**
     * Website scraping — extraction NLP depuis les sites des restaurants
     *
     * Données récupérables :
     * - website_has_menu (bool)
     * - website_has_reservation (bool)
     * - website_has_delivery (bool)
     * - website_keywords (JSON array)
     * - website_language (fr, ar, en)
     *
     * @stub En attente implémentation
     */
    private function scrapeWebsite(int $limit): array
    {
        return ['source' => 'website', 'processed' => 0, 'errors' => 0, 'message' => 'Stub — en attente implémentation'];
    }

    /**
     * Stockage unifié — UPSERT dans restaurant_external_data
     */
    public function storeData(
        int $restaurantId,
        string $source,
        string $key,
        ?string $value = null,
        ?float $numeric = null,
        float $confidence = 1.0,
        ?string $expiresAt = null,
        ?string $rawJson = null
    ): bool {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO restaurant_external_data
                    (restaurant_id, source, data_key, data_value, data_numeric, confidence, fetched_at, expires_at, raw_json)
                VALUES
                    (:rid, :src, :key, :val, :num, :conf, NOW(), :exp, :raw)
                ON DUPLICATE KEY UPDATE
                    data_value = VALUES(data_value),
                    data_numeric = VALUES(data_numeric),
                    confidence = VALUES(confidence),
                    fetched_at = NOW(),
                    expires_at = VALUES(expires_at),
                    raw_json = VALUES(raw_json)
            ");
            $stmt->execute([
                ':rid' => $restaurantId,
                ':src' => $source,
                ':key' => $key,
                ':val' => $value,
                ':num' => $numeric,
                ':conf' => $confidence,
                ':exp' => $expiresAt,
                ':raw' => $rawJson,
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("[ExternalData] Store error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Nettoyage des données expirées
     * @return int Nombre de lignes supprimées
     */
    public function cleanupExpired(): int
    {
        try {
            return $this->db->exec("
                DELETE FROM restaurant_external_data
                WHERE expires_at IS NOT NULL AND expires_at < NOW()
            ");
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Stats des données externes par source
     */
    public function getStats(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT source,
                       COUNT(*) as total,
                       COUNT(DISTINCT restaurant_id) as restaurants,
                       SUM(CASE WHEN expires_at IS NOT NULL AND expires_at < NOW() THEN 1 ELSE 0 END) as expired,
                       MIN(fetched_at) as oldest,
                       MAX(fetched_at) as newest
                FROM restaurant_external_data
                GROUP BY source
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
