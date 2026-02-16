<?php

namespace App\Models;

use App\Core\Model;

/**
 * Restaurant Model
 * Gère les données des restaurants (nouvelle structure)
 */
class Restaurant extends Model
{
    protected string $table = 'restaurants';
    protected string $primaryKey = 'id';
    
    /**
     * Récupère les restaurants mis en avant (AVEC PHOTOS)
     */
    public function getFeatured(int $limit = 6): array
    {
        $sql = "SELECT 
                    r.*,
                    rp.path as main_photo,
                    rp.filename as photo_filename
                FROM {$this->table} r
                LEFT JOIN restaurant_photos rp ON (
                    rp.restaurant_id = r.id 
                    AND rp.type = 'main'
                    AND rp.ordre = 0
                )
                WHERE r.status = 'validated' 
                AND r.featured = 1 
                ORDER BY r.id DESC 
                LIMIT :limit";
        
        return $this->query($sql, ['limit' => $limit]);
    }
    
    /**
     * Récupère les derniers restaurants validés (AVEC PHOTOS)
     */
    public function getLatest(int $limit = 8): array
    {
        $sql = "SELECT 
                    r.*,
                    rp.path as main_photo,
                    rp.filename as photo_filename
                FROM {$this->table} r
                LEFT JOIN restaurant_photos rp ON (
                    rp.restaurant_id = r.id 
                    AND rp.type = 'main'
                    AND rp.ordre = 0
                )
                WHERE r.status = 'validated' 
                ORDER BY r.id DESC 
                LIMIT :limit";
        
        return $this->query($sql, ['limit' => $limit]);
    }
    
    /**
     * Récupère tous les types de cuisine
     */
    public function getCuisineTypes(): array
    {
        $sql = "SELECT DISTINCT type_cuisine FROM {$this->table} 
                WHERE status = 'validated' 
                AND type_cuisine IS NOT NULL 
                AND type_cuisine != '' 
                ORDER BY type_cuisine ASC";
        
        $results = $this->query($sql);
        
        // Retourner uniquement les valeurs
        return array_column($results, 'type_cuisine');
    }
    
    /**
     * Récupère un restaurant avec toutes ses relations
     */
    public function getWithRelations(int $id): ?array
    {
        $sql = "SELECT r.*, 
                       rp.path as main_photo,
                       rp.filename as photo_filename,
                       GROUP_CONCAT(DISTINCT rp2.filename) as all_photos,
                       AVG(rev.note_globale) as avg_rating,
                       COUNT(DISTINCT rev.id) as review_count
                FROM {$this->table} r
                LEFT JOIN restaurant_photos rp ON (
                    rp.restaurant_id = r.id 
                    AND rp.type = 'main'
                    AND rp.ordre = 0
                )
                LEFT JOIN restaurant_photos rp2 ON rp2.restaurant_id = r.id
                LEFT JOIN reviews rev ON rev.restaurant_id = r.id
                WHERE r.id = :id
                GROUP BY r.id";
        
        $results = $this->query($sql, ['id' => $id]);
        
        return $results[0] ?? null;
    }
    
    /**
     * Recherche de restaurants par proximité (AVEC PHOTOS)
     */
    public function searchNearby(float $lat, float $lon, int $radius, string $type = null, array $filters = []): array
    {
        // Formule de distance Haversine
        $distanceFormula = $this->getDistanceFormula($lat, $lon);
        
        $sql = "SELECT 
                    r.*, 
                    {$distanceFormula} as distance,
                    rp.path as main_photo,
                    rp.filename as photo_filename
                FROM {$this->table} r
                LEFT JOIN restaurant_photos rp ON (
                    rp.restaurant_id = r.id 
                    AND rp.type = 'main'
                    AND rp.ordre = 0
                )
                WHERE r.status = 'validated'";
        
        $params = [];
        
        // Filtre par type de cuisine
        if ($type && $type !== 'all') {
            $sql .= " AND r.type_cuisine = :type";
            $params['type'] = $type;
        }
        
        // Filtre par prix
        if (isset($filters['price']) && $filters['price'] !== 'all') {
            $sql .= " AND r.price_range = :price";
            $params['price'] = $filters['price'];
        }
        
        $sql .= " HAVING distance <= :radius 
                  ORDER BY distance ASC";
        
        $params['radius'] = $radius;
        
        return $this->query($sql, $params);
    }
    
    /**
     * Formule de calcul de distance (Haversine) - nouvelle structure
     */
    private function getDistanceFormula(float $lat, float $lon): string
    {
        return "(
            6371 * acos(
                cos(radians({$lat})) * 
                cos(radians(r.gps_latitude)) * 
                cos(radians(r.gps_longitude) - radians({$lon})) + 
                sin(radians({$lat})) * 
                sin(radians(r.gps_latitude))
            )
        )";
    }
    
    /**
     * Récupère les restaurants en attente de validation
     */
    public function getPending(): array
    {
        return $this->where('status', 'pending');
    }
    
    /**
     * Récupère les restaurants validés (AVEC PHOTOS)
     */
    public function getValidated(): array
    {
        $sql = "SELECT 
                    r.*,
                    rp.path as main_photo,
                    rp.filename as photo_filename
                FROM {$this->table} r
                LEFT JOIN restaurant_photos rp ON (
                    rp.restaurant_id = r.id 
                    AND rp.type = 'main'
                    AND rp.ordre = 0
                )
                WHERE r.status = 'validated'
                ORDER BY r.featured DESC, r.note_moyenne DESC, r.nom ASC";
        
        return $this->rawQuery($sql, []);
    }
    
    /**
     * Valide un restaurant
     */
    public function validate(int $id): bool
    {
        return $this->update($id, ['status' => 'validated']);
    }
    
    /**
     * Rejette un restaurant
     */
    public function reject(int $id, string $reason = null): bool
    {
        $data = ['status' => 'rejected'];
        
        if ($reason) {
            $data['rejection_reason'] = $reason;
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Toggle le statut "Mis en avant"
     */
    public function toggleFeatured(int $id): bool
    {
        $restaurant = $this->find($id);
        
        if (!$restaurant) {
            return false;
        }
        
        $newStatus = $restaurant['featured'] == 1 ? 0 : 1;
        
        return $this->update($id, ['featured' => $newStatus]);
    }
    
    /**
     * Récupère les photos d'un restaurant
     */
    public function getPhotos(int $restaurantId): array
    {
        $sql = "SELECT * FROM restaurant_photos 
                WHERE restaurant_id = :restaurant_id 
                ORDER BY 
                    CASE type
                        WHEN 'main' THEN 1
                        WHEN 'slide' THEN 2
                        ELSE 3
                    END,
                    ordre ASC, 
                    id ASC";
        
        return $this->query($sql, ['restaurant_id' => $restaurantId]);
    }
    
    /**
     * Récupère les options d'un restaurant
     */
    public function getOptions(int $restaurantId): ?array
    {
        $sql = "SELECT * FROM restaurant_options 
                WHERE restaurant_id = :restaurant_id";
        
        $results = $this->query($sql, ['restaurant_id' => $restaurantId]);
        
        return $results[0] ?? null;
    }
    
    /**
     * Récupère les horaires d'un restaurant
     */
    public function getHoraires(int $restaurantId): array
    {
        $sql = "SELECT * FROM restaurant_horaires 
                WHERE restaurant_id = :restaurant_id 
                ORDER BY jour_semaine ASC";
        
        return $this->query($sql, ['restaurant_id' => $restaurantId]);
    }
    
    /**
     * Récupère les avis d'un restaurant
     */
    public function getReviews(int $restaurantId, int $limit = null): array
    {
        $sql = "SELECT r.*, u.prenom, u.nom, u.photo_profil
                FROM reviews r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.restaurant_id = :restaurant_id 
                AND r.status = 'approved'
                ORDER BY r.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
            return $this->query($sql, ['restaurant_id' => $restaurantId, 'limit' => $limit]);
        }
        
        return $this->query($sql, ['restaurant_id' => $restaurantId]);
    }
    
    /**
     * Recherche personnalisée (AVEC PHOTOS)
     */
    public function search(array $criteria): array
    {
        $sql = "SELECT 
                    r.*,
                    rp.path as main_photo,
                    rp.filename as photo_filename
                FROM {$this->table} r
                LEFT JOIN restaurant_photos rp ON (
                    rp.restaurant_id = r.id 
                    AND rp.type = 'main'
                    AND rp.ordre = 0
                )
                WHERE r.status = 'validated'";
        
        $params = [];
        
        if (!empty($criteria['ville']) && $criteria['ville'] !== 'all') {
            $sql .= " AND r.ville = :ville";
            $params['ville'] = $criteria['ville'];
        }
        
        if (!empty($criteria['wilaya']) && $criteria['wilaya'] !== 'all') {
            $sql .= " AND r.wilaya = :wilaya";
            $params['wilaya'] = $criteria['wilaya'];
        }
        
        if (!empty($criteria['type']) && $criteria['type'] !== 'all') {
            $sql .= " AND r.type_cuisine = :type";
            $params['type'] = $criteria['type'];
        }
        
        $sql .= " ORDER BY r.featured DESC, r.note_moyenne DESC, r.nom ASC";
        
        return $this->rawQuery($sql, $params);
    }
    /**
     * Récupère les villes les plus populaires avec nombre de restaurants
     */
    public function getPopularCities(int $limit = 6): array
    {
        $sql = "
            SELECT 
                ville,
                wilaya,
                pays,
                COUNT(*) as nb_restaurants,
                AVG(note_moyenne) as note_moyenne,
                (SELECT rp.path FROM restaurant_photos rp 
                 INNER JOIN restaurants r2 ON rp.restaurant_id = r2.id
                 WHERE r2.ville = r.ville AND rp.type = 'main' 
                 LIMIT 1) as photo
            FROM {$this->table} r
            WHERE status = 'validated' 
            AND ville IS NOT NULL
            AND ville != ''
            GROUP BY ville, wilaya, pays
            ORDER BY nb_restaurants DESC, note_moyenne DESC
            LIMIT :limit
        ";
        
        return $this->query($sql, ['limit' => $limit]);
    }

    /**
     * Récupère les types de cuisine les plus populaires
     */
    public function getPopularCuisineTypes(int $limit = 8): array
    {
        $sql = "
            SELECT 
                type_cuisine,
                COUNT(*) as nb_restaurants,
                AVG(note_moyenne) as note_moyenne
            FROM {$this->table}
            WHERE status = 'validated' 
            AND type_cuisine IS NOT NULL
            AND type_cuisine != ''
            GROUP BY type_cuisine
            ORDER BY nb_restaurants DESC
            LIMIT :limit
        ";
        
        return $this->query($sql, ['limit' => $limit]);
    }

    /**
     * Récupère les restaurants "Travelers' Choice" (top du mois)
     * Critères: note_moyenne >= 8.0 et minimum 5 avis
     */
    public function getTravelersChoice(int $limit = 10): array
    {
        $sql = "
            SELECT 
                r.*,
                rp.path as main_photo,
                rp.filename as photo_filename
            FROM {$this->table} r
            LEFT JOIN restaurant_photos rp ON (
                rp.restaurant_id = r.id 
                AND rp.type = 'main'
                AND rp.ordre = 0
            )
            WHERE r.status = 'validated'
            AND r.note_moyenne >= 8.0
            AND r.nb_avis >= 5
            ORDER BY r.note_moyenne DESC, r.nb_avis DESC
            LIMIT :limit
        ";
        
        return $this->query($sql, ['limit' => $limit]);
    }

    /**
     * Récupère les restaurants parfaits pour les familles
     */
    public function getFamilyFriendly(int $limit = 6): array
    {
        $sql = "
            SELECT 
                r.*,
                rp.path as main_photo,
                rp.filename as photo_filename
            FROM {$this->table} r
            LEFT JOIN restaurant_photos rp ON (
                rp.restaurant_id = r.id 
                AND rp.type = 'main'
                AND rp.ordre = 0
            )
            INNER JOIN restaurant_options ro ON r.id = ro.restaurant_id
            WHERE r.status = 'validated'
            AND (ro.baby_chair = 1 OR ro.game_zone = 1)
            ORDER BY r.note_moyenne DESC
            LIMIT :limit
        ";
        
        return $this->query($sql, ['limit' => $limit]);
    }
    // À ajouter dans la classe Restaurant (App\Models\Restaurant)

/**
 * Exécute une requête personnalisée pour filtrer les restaurants
 * 
 * @param string $sql La requête SQL
 * @param array $params Les paramètres de la requête
 * @return array Les résultats
 */
public function getFilteredRestaurants(string $sql, array $params = []): array
{
    return $this->query($sql, $params);
}
/**
     * Recherche avancée de restaurants
     */
    public function advancedSearch(array $params, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT 
                    r.*,
                    COALESCE(r.note_moyenne, 0) as note_moyenne,
                    COUNT(DISTINCT rev.id) as nb_avis
                FROM {$this->table} r
                LEFT JOIN reviews rev ON rev.restaurant_id = r.id AND rev.status = 'approved'
                WHERE r.status = 'validated'";
        
        $queryParams = [];
        
        // Filtre recherche générale
        if (!empty($params['query'])) {
            $sql .= " AND (r.nom LIKE :query OR r.description LIKE :query OR r.ville LIKE :query)";
            $queryParams['query'] = '%' . $params['query'] . '%';
        }
        
        // Filtre ville
        if (!empty($params['ville'])) {
            $sql .= " AND r.ville = :ville";
            $queryParams['ville'] = $params['ville'];
        }
        
        // Filtre type de cuisine
        if (!empty($params['type']) && $params['type'] !== 'all') {
            $sql .= " AND r.type_cuisine = :type";
            $queryParams['type'] = $params['type'];
        }
        
        // Filtre gamme de prix
        if (!empty($params['price'])) {
            $sql .= " AND r.price_range = :price";
            $queryParams['price'] = $params['price'];
        }
        
        $sql .= " GROUP BY r.id";
        
        // Filtre note minimum
        if (!empty($params['minRating'])) {
            $sql .= " HAVING note_moyenne >= :minRating";
            $queryParams['minRating'] = (float)$params['minRating'];
        }
        
        // Tri
        $sortBy = $params['sort'] ?? 'relevance';
        switch ($sortBy) {
            case 'rating':
                $sql .= " ORDER BY note_moyenne DESC, nb_avis DESC";
                break;
            case 'reviews':
                $sql .= " ORDER BY nb_avis DESC, note_moyenne DESC";
                break;
            case 'price_low':
                $sql .= " ORDER BY FIELD(r.price_range, '€', '€€', '€€€', '€€€€'), note_moyenne DESC";
                break;
            case 'price_high':
                $sql .= " ORDER BY FIELD(r.price_range, '€€€€', '€€€', '€€', '€'), note_moyenne DESC";
                break;
            case 'name':
                $sql .= " ORDER BY r.nom ASC";
                break;
            default: // relevance
                if (!empty($params['query'])) {
                    $sql .= " ORDER BY (r.nom LIKE :query_exact) DESC, note_moyenne DESC, nb_avis DESC";
                    $queryParams['query_exact'] = '%' . $params['query'] . '%';
                } else {
                    $sql .= " ORDER BY note_moyenne DESC, nb_avis DESC";
                }
        }
        
        // Pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        $queryParams['limit'] = $limit;
        $queryParams['offset'] = $offset;
        
        return $this->query($sql, $queryParams);
    }
    
    /**
     * Compte les résultats de recherche
     */
    public function countSearch(array $params): int
    {
        $sql = "SELECT COUNT(DISTINCT r.id) as total 
                FROM {$this->table} r
                LEFT JOIN reviews rev ON rev.restaurant_id = r.id AND rev.status = 'approved'
                WHERE r.status = 'validated'";
        
        $queryParams = [];
        
        if (!empty($params['query'])) {
            $sql .= " AND (r.nom LIKE :query OR r.description LIKE :query OR r.ville LIKE :query)";
            $queryParams['query'] = '%' . $params['query'] . '%';
        }
        
        if (!empty($params['ville'])) {
            $sql .= " AND r.ville = :ville";
            $queryParams['ville'] = $params['ville'];
        }
        
        if (!empty($params['type']) && $params['type'] !== 'all') {
            $sql .= " AND r.type_cuisine = :type";
            $queryParams['type'] = $params['type'];
        }
        
        if (!empty($params['price'])) {
            $sql .= " AND r.price_range = :price";
            $queryParams['price'] = $params['price'];
        }
        
        $result = $this->query($sql, $queryParams);
        return $result[0]['total'] ?? 0;
    }
    
    /**
     * Récupère toutes les villes disponibles
     */
    public function getAvailableCities(): array
    {
        $sql = "SELECT DISTINCT ville 
                FROM {$this->table} 
                WHERE status = 'validated' 
                ORDER BY ville";
        
        return $this->query($sql);
    }
    
    /**
     * Récupère tous les types de cuisine disponibles
     */
    public function getAvailableCuisineTypes(): array
    {
        $sql = "SELECT DISTINCT type_cuisine 
                FROM {$this->table} 
                WHERE status = 'validated' 
                AND type_cuisine IS NOT NULL 
                ORDER BY type_cuisine";
        
        return $this->query($sql);
    }
    /**
     * Compte le nombre total de restaurants validés
     */
    public function getTotalValidated(): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE status = 'validated'";
        
        $result = $this->query($sql);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Compte le nombre de villes distinctes (restaurants validés)
     */
    public function getTotalCities(): int
    {
        $sql = "SELECT COUNT(DISTINCT ville) as total
                FROM {$this->table}
                WHERE status = 'validated' AND ville IS NOT NULL AND ville != ''";
        $result = $this->query($sql);
        return (int) ($result[0]['total'] ?? 0);
    }

    /**
     * Récupère les villes populaires (ALGÉRIE SEULEMENT)
     */
    public function getPopularCitiesAlgeria(int $limit = 6): array
    {
        $sql = "
            SELECT 
                ville,
                wilaya,
                COUNT(*) as count,
                AVG(note_moyenne) as note_moyenne,
                (SELECT rp.path FROM restaurant_photos rp 
                 INNER JOIN restaurants r2 ON rp.restaurant_id = r2.id
                 WHERE r2.ville = r.ville AND rp.type = 'main' 
                 LIMIT 1) as photo
            FROM {$this->table} r
            WHERE status = 'validated' 
            AND pays = 'Algérie'
            AND ville IS NOT NULL
            AND ville != ''
            GROUP BY ville, wilaya
            ORDER BY count DESC, note_moyenne DESC
            LIMIT :limit
        ";
        
        return $this->query($sql, ['limit' => $limit]);
    }

    /**
     * Récupère les meilleurs restaurants groupés par grandes régions
     * Retourne un tableau [['ville' => 'Alger', 'restaurants' => [...]], ...]
     */
    public function getTopByRegion(int $maxRegions = 4, int $perRegion = 6): array
    {
        // 1. Top régions par nombre de restaurants
        $sqlRegions = "
            SELECT wilaya, COUNT(*) as cnt
            FROM {$this->table}
            WHERE status = 'validated' AND wilaya IS NOT NULL AND wilaya != ''
            GROUP BY wilaya
            ORDER BY cnt DESC
            LIMIT :maxRegions
        ";
        $regions = $this->query($sqlRegions, ['maxRegions' => $maxRegions]);
        if (empty($regions)) return [];

        $result = [];
        foreach ($regions as $region) {
            $sqlRestos = "
                SELECT r.*, rp.path as main_photo, COUNT(rev.id) as total_avis
                FROM {$this->table} r
                LEFT JOIN restaurant_photos rp ON rp.restaurant_id = r.id AND rp.type = 'main' AND rp.ordre = 0
                LEFT JOIN reviews rev ON rev.restaurant_id = r.id AND rev.status = 'approved'
                WHERE r.status = 'validated' AND r.wilaya = :wilaya
                GROUP BY r.id, rp.path
                ORDER BY r.note_moyenne DESC, total_avis DESC
                LIMIT :perRegion
            ";
            $restos = $this->query($sqlRestos, ['wilaya' => $region['wilaya'], 'perRegion' => $perRegion]);
            if (!empty($restos)) {
                $result[] = [
                    'wilaya' => $region['wilaya'],
                    'count' => $region['cnt'],
                    'restaurants' => $restos
                ];
            }
        }
        return $result;
    }

    /**
     * Récupère les types de cuisine avec mapping pour la vue
     */
    public function getPopularCuisineTypesMapped(int $limit = 8): array
    {
        $sql = "
            SELECT 
                type_cuisine as type,
                COUNT(*) as count,
                AVG(note_moyenne) as note_moyenne
            FROM {$this->table}
            WHERE status = 'validated' 
            AND type_cuisine IS NOT NULL
            AND type_cuisine != ''
            GROUP BY type_cuisine
            ORDER BY count DESC
            LIMIT :limit
        ";
        
        return $this->query($sql, ['limit' => $limit]);
    }
}