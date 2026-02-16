<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Restaurant;

/**
 * API Controller pour les restaurants
 * Fournit des données JSON pour la carte et autres besoins
 */
class RestaurantController extends Controller
{
    private Restaurant $restaurantModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->restaurantModel = new Restaurant();
    }
    
    /**
     * Retourne tous les restaurants en JSON (pour carte Leaflet)
     */
    public function index(Request $request): void
    {
        // Récupérer les filtres
        $ville = $request->get('ville');
        $type = $request->get('type');
        $search = $request->get('search');
        
        // Construire la requête
        $sql = "SELECT 
                    r.id,
                    r.nom,
                    r.slug,
                    r.ville,
                    r.type_cuisine,
                    r.gps_latitude as latitude,
                    r.gps_longitude as longitude,
                    r.adresse,
                    r.price_range,
                    COALESCE(r.note_moyenne, 0) as note_moyenne,
                    COUNT(DISTINCT rev.id) as nb_avis
                FROM restaurants r
                LEFT JOIN reviews rev ON rev.restaurant_id = r.id AND rev.status = 'approved'
                WHERE r.status = 'validated'
                AND r.gps_latitude IS NOT NULL
                AND r.gps_longitude IS NOT NULL";
        
        $params = [];
        
        // Filtrer par ville
        if ($ville) {
            $sql .= " AND r.ville = :ville";
            $params['ville'] = $ville;
        }
        
        // Filtrer par type de cuisine
        if ($type && $type !== 'all') {
            $sql .= " AND r.type_cuisine = :type";
            $params['type'] = $type;
        }
        
        // Recherche
        if ($search) {
            $sql .= " AND (r.nom LIKE :search OR r.description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $sql .= " GROUP BY r.id ORDER BY r.note_moyenne DESC, r.nb_avis DESC";
        
        // CORRECTION: Appeler la méthode publique du modèle
        $restaurants = $this->restaurantModel->getFilteredRestaurants($sql, $params);
        
        // Formatter pour Leaflet
        $markers = array_map(function($resto) {
            return [
                'id' => $resto['id'],
                'name' => $resto['nom'],
                'slug' => $resto['slug'],
                'lat' => (float)$resto['latitude'],
                'lng' => (float)$resto['longitude'],
                'ville' => $resto['ville'],
                'type' => $resto['type_cuisine'],
                'address' => $resto['adresse'],
                'rating' => (float)$resto['note_moyenne'],
                'reviews_count' => (int)$resto['nb_avis'],
                'price_range' => $resto['price_range'] ?? '€€',
                'url' => '/restaurant/' . $resto['id']
            ];
        }, $restaurants);
        
        $this->json([
            'success' => true,
            'count' => count($markers),
            'restaurants' => $markers
        ]);
    }
    
    /**
     * Retourne un restaurant spécifique en JSON
     */
    public function show(Request $request): void
    {
        $id = $request->param('id');
        
        $restaurant = $this->restaurantModel->find((int)$id);
        
        if (!$restaurant) {
            $this->json(['success' => false, 'message' => 'Restaurant non trouvé'], 404);
            return;
        }
        
        $this->json([
            'success' => true,
            'restaurant' => $restaurant
        ]);
    }
}