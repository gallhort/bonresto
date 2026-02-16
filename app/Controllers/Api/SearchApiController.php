<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use PDO;

/**
 * API Controller pour l'autocomplete global
 * Recherche dans les villes ET les restaurants
 */
class SearchApiController extends Controller
{
    // Centre géographique de l'Algérie (pour "Voir tous")
    private const ALGERIA_CENTER_LAT = 28.0339;
    private const ALGERIA_CENTER_LNG = 1.6596;
    private const ALGERIA_RADIUS = 2000; // km

    /**
     * Autocomplete global : villes + restaurants
     * GET /api/search/autocomplete?q=xxx
     */
    public function autocomplete(): void
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');

        $query = trim($_GET['q'] ?? '');

        // Minimum 2 caractères
        if (strlen($query) < 2) {
            echo json_encode([
                'success' => true,
                'query' => $query,
                'villes' => [],
                'restaurants' => [],
                'voir_tous' => null
            ]);
            return;
        }

        try {
            // 1. Rechercher les villes (max 3)
            $villes = $this->searchVilles($query, 3);

            // 2. Rechercher les restaurants (max 3)
            $restaurants = $this->searchRestaurants($query, 3);

            // 3. Déterminer le "Voir tous"
            $voirTous = $this->buildVoirTous($query, $villes);

            echo json_encode([
                'success' => true,
                'query' => $query,
                'villes' => $villes,
                'restaurants' => $restaurants,
                'voir_tous' => $voirTous
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Recherche les villes/communes
     */
    private function searchVilles(string $query, int $limit): array
    {
        $queryLike = '%' . $query . '%';
        
        $sql = "SELECT DISTINCT
                    commune_name_ascii as commune,
                    wilaya_name_ascii as wilaya,
                    gps
                FROM algeria_cities 
                WHERE commune_name_ascii LIKE ?
                   OR wilaya_name_ascii LIKE ?
                LIMIT 3";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$queryLike, $queryLike]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parser le GPS et formater
        $villes = [];
        foreach ($results as $ville) {
            if (empty($ville['gps']) || strpos($ville['gps'], ',') === false) {
                continue;
            }
            $gps = explode(',', $ville['gps']);
            $villes[] = [
                'commune' => $ville['commune'],
                'wilaya' => $ville['wilaya'],
                'lat' => trim($gps[0]),
                'lng' => trim($gps[1]),
                'nb_restaurants' => 0
            ];
        }
        return $villes;
    }
    
    /**
     * Compte les restaurants dans une ville (désactivé temporairement)
     */
    private function countRestaurantsInVille(string $commune, string $wilaya): int
    {
        return 0; // Temporaire pour debug
    }

    /**
     * Recherche les restaurants
     */
    private function searchRestaurants(string $query, int $limit): array
    {
        $queryLike = '%' . $query . '%';
        
        // Requête avec LEFT JOIN pour récupérer la photo principale
        $sql = "SELECT 
                    r.id,
                    r.nom,
                    r.slug,
                    r.type_cuisine,
                    r.ville,
                    r.wilaya,
                    COALESCE(r.note_moyenne, 0) as note_moyenne,
                    COALESCE(r.nb_avis, 0) as nb_avis,
                    rp.path as photo_path
                FROM restaurants r
                LEFT JOIN restaurant_photos rp ON rp.restaurant_id = r.id AND rp.type = 'main'
                WHERE r.status = 'validated'
                  AND r.nom LIKE ?
                GROUP BY r.id
                ORDER BY r.note_moyenne DESC
                LIMIT 3";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$queryLike]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $restaurants = [];
        foreach ($results as $resto) {
            // Formater le chemin de la photo (ajouter / si nécessaire)
            $photo = null;
            if (!empty($resto['photo_path'])) {
                $photo = $resto['photo_path'];
                // Ajouter / au début si pas présent
                if (strpos($photo, '/') !== 0) {
                    $photo = '/' . $photo;
                }
            }
            
            $restaurants[] = [
                'id' => (int) $resto['id'],
                'nom' => $resto['nom'],
                'slug' => $resto['slug'],
                'type_cuisine' => $resto['type_cuisine'],
                'ville' => $resto['ville'],
                'wilaya' => $resto['wilaya'],
                'note_moyenne' => round((float) $resto['note_moyenne'], 1),
                'nb_avis' => (int) $resto['nb_avis'],
                'photo' => $photo
            ];
        }
        return $restaurants;
    }

    /**
     * Construit le lien "Voir tous les restaurants"
     * Si une seule ville correspond exactement → on utilise son GPS
     * Sinon → on centre sur toute l'Algérie
     */
    private function buildVoirTous(string $query, array $villes): array
    {
        // Vérifier si une seule ville correspond EXACTEMENT
        $villeUnique = $this->getVilleUnique($query);

        if ($villeUnique) {
            // Une seule ville → GPS précis
            return [
                'label' => 'Voir tous les restaurants à ' . $villeUnique['commune'],
                'url' => '/search?ville=' . urlencode($villeUnique['commune']) 
                       . '&lat=' . $villeUnique['lat'] 
                       . '&lng=' . $villeUnique['lng'],
                'ville_unique' => $villeUnique['commune'],
                'lat' => $villeUnique['lat'],
                'lng' => $villeUnique['lng']
            ];
        } else {
            // Plusieurs villes ou recherche ambiguë → Centre Algérie
            return [
                'label' => 'Voir tous les restaurants',
                'url' => '/search?q=' . urlencode($query) 
                       . '&lat=' . self::ALGERIA_CENTER_LAT 
                       . '&lng=' . self::ALGERIA_CENTER_LNG 
                       . '&radius=' . self::ALGERIA_RADIUS,
                'ville_unique' => null,
                'lat' => self::ALGERIA_CENTER_LAT,
                'lng' => self::ALGERIA_CENTER_LNG
            ];
        }
    }

    /**
     * Vérifie si la recherche correspond à UNE SEULE ville exactement
     */
    private function getVilleUnique(string $query): ?array
    {
        $queryLike = '%' . $query . '%';
        
        // Compter combien de villes correspondent (commune OU wilaya)
        $sqlCount = "SELECT COUNT(DISTINCT commune_name_ascii) as total
                     FROM algeria_cities 
                     WHERE commune_name_ascii LIKE ?
                        OR wilaya_name_ascii LIKE ?";

        $stmt = $this->db->prepare($sqlCount);
        $stmt->execute([$queryLike, $queryLike]);
        $count = (int) $stmt->fetchColumn();

        // Si plus d'une ville → pas unique
        if ($count !== 1) {
            return null;
        }

        // Récupérer cette ville unique
        $sql = "SELECT commune_name_ascii as commune, wilaya_name_ascii as wilaya, gps
                FROM algeria_cities 
                WHERE commune_name_ascii LIKE ?
                   OR wilaya_name_ascii LIKE ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$queryLike, $queryLike]);
        $ville = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ville || empty($ville['gps'])) {
            return null;
        }

        $gps = explode(',', $ville['gps']);
        if (count($gps) < 2) {
            return null;
        }
        
        return [
            'commune' => $ville['commune'],
            'wilaya' => $ville['wilaya'],
            'lat' => trim($gps[0]),
            'lng' => trim($gps[1])
        ];
    }
}