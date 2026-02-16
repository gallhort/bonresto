<?php

namespace App\Services;

use PDO;

/**
 * Service de suggestions personnalisées
 * Recommande des restaurants basés sur les préférences utilisateur
 */
class RecommendationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Obtenir des suggestions personnalisées pour un utilisateur
     */
    public function getPersonalizedSuggestions(int $userId, int $limit = 6): array
    {
        // 1. Récupérer les cuisines préférées (restos notés 4-5 étoiles)
        $cuisineStmt = $this->db->prepare("
            SELECT r.type_cuisine, COUNT(*) as cnt, AVG(rev.note_globale) as avg_note
            FROM reviews rev
            INNER JOIN restaurants r ON r.id = rev.restaurant_id
            WHERE rev.user_id = :uid AND rev.note_globale >= 4 AND r.type_cuisine IS NOT NULL
            GROUP BY r.type_cuisine
            ORDER BY cnt DESC, avg_note DESC
            LIMIT 5
        ");
        $cuisineStmt->execute([':uid' => $userId]);
        $favCuisines = $cuisineStmt->fetchAll(PDO::FETCH_COLUMN);

        // 2. Récupérer les villes fréquentées
        $villeStmt = $this->db->prepare("
            SELECT r.ville, COUNT(*) as cnt
            FROM reviews rev
            INNER JOIN restaurants r ON r.id = rev.restaurant_id
            WHERE rev.user_id = :uid AND r.ville IS NOT NULL
            GROUP BY r.ville
            ORDER BY cnt DESC
            LIMIT 3
        ");
        $villeStmt->execute([':uid' => $userId]);
        $favVilles = $villeStmt->fetchAll(PDO::FETCH_COLUMN);

        // 3. Récupérer les IDs de restaurants déjà visités
        $visitedStmt = $this->db->prepare("
            SELECT DISTINCT restaurant_id FROM reviews WHERE user_id = :uid
            UNION
            SELECT DISTINCT restaurant_id FROM wishlist WHERE user_id = :uid2
        ");
        $visitedStmt->execute([':uid' => $userId, ':uid2' => $userId]);
        $visitedIds = $visitedStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($favCuisines) && empty($favVilles)) {
            // Pas d'historique → retourner les mieux notés
            return $this->getTopRated($limit, $visitedIds);
        }

        // 4. Construire la requête de recommandation
        $params = [];
        $scoreParts = [];

        // Score cuisine: +3 points si cuisine favorite
        if (!empty($favCuisines)) {
            $cuisinePlaceholders = [];
            foreach ($favCuisines as $i => $cuisine) {
                $key = ':cuisine_' . $i;
                $params[$key] = $cuisine;
                $cuisinePlaceholders[] = $key;
            }
            $cuisineIn = implode(',', $cuisinePlaceholders);
            $scoreParts[] = "CASE WHEN r.type_cuisine IN ($cuisineIn) THEN 3 ELSE 0 END";
        }

        // Score ville: +2 points si ville fréquentée
        if (!empty($favVilles)) {
            $villePlaceholders = [];
            foreach ($favVilles as $i => $ville) {
                $key = ':ville_' . $i;
                $params[$key] = $ville;
                $villePlaceholders[] = $key;
            }
            $villeIn = implode(',', $villePlaceholders);
            $scoreParts[] = "CASE WHEN r.ville IN ($villeIn) THEN 2 ELSE 0 END";
        }

        // Score note: normaliser sur 1 point
        $scoreParts[] = "COALESCE(r.note_moyenne / 5, 0)";

        // Score popularité: normaliser
        $scoreParts[] = "LEAST(COALESCE(r.nb_avis, 0) / 50, 1)";

        $scoreExpr = implode(' + ', $scoreParts);

        // Exclure les restaurants déjà visités
        $excludeClause = '';
        if (!empty($visitedIds)) {
            $excludePlaceholders = [];
            foreach ($visitedIds as $i => $id) {
                $key = ':excl_' . $i;
                $params[$key] = (int)$id;
                $excludePlaceholders[] = $key;
            }
            $excludeClause = 'AND r.id NOT IN (' . implode(',', $excludePlaceholders) . ')';
        }

        $sql = "
            SELECT r.id, r.nom, r.slug, r.type_cuisine, r.ville, r.adresse,
                   r.price_range, COALESCE(r.note_moyenne, 0) as note_moyenne,
                   COALESCE(r.nb_avis, 0) as nb_avis,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as main_photo,
                   ($scoreExpr) as relevance_score
            FROM restaurants r
            WHERE r.status = 'validated' $excludeClause
            ORDER BY relevance_score DESC, r.note_moyenne DESC
            LIMIT " . (int)$limit . "
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Top restaurants pour les utilisateurs sans historique
     */
    private function getTopRated(int $limit, array $excludeIds = []): array
    {
        $params = [];
        $excludeClause = '';

        if (!empty($excludeIds)) {
            $placeholders = [];
            foreach ($excludeIds as $i => $id) {
                $key = ':excl_' . $i;
                $params[$key] = (int)$id;
                $placeholders[] = $key;
            }
            $excludeClause = 'AND r.id NOT IN (' . implode(',', $placeholders) . ')';
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.type_cuisine, r.ville, r.adresse,
                   r.price_range, COALESCE(r.note_moyenne, 0) as note_moyenne,
                   COALESCE(r.nb_avis, 0) as nb_avis,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as main_photo
            FROM restaurants r
            WHERE r.status = 'validated' AND r.nb_avis >= 3 $excludeClause
            ORDER BY r.note_moyenne DESC, r.nb_avis DESC
            LIMIT " . (int)$limit . "
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Restaurants similaires à un restaurant donné
     */
    public function getSimilarRestaurants(int $restaurantId, int $limit = 4): array
    {
        $limit = (int)$limit;
        $stmt = $this->db->prepare("
            SELECT r2.id, r2.nom, r2.slug, r2.type_cuisine, r2.ville,
                   r2.price_range, COALESCE(r2.note_moyenne, 0) as note_moyenne,
                   COALESCE(r2.nb_avis, 0) as nb_avis,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r2.id AND rp.type = 'main' LIMIT 1) as main_photo
            FROM restaurants r1
            INNER JOIN restaurants r2 ON r2.id != r1.id
                AND r2.status = 'validated'
                AND (r2.type_cuisine = r1.type_cuisine OR r2.ville = r1.ville)
            WHERE r1.id = :rid
            ORDER BY
                (r2.type_cuisine = r1.type_cuisine AND r2.ville = r1.ville) DESC,
                (r2.type_cuisine = r1.type_cuisine) DESC,
                r2.note_moyenne DESC
            LIMIT {$limit}
        ");
        $stmt->execute([':rid' => $restaurantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
