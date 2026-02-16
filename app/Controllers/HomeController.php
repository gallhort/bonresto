<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Restaurant;
 use App\Models\Review;
use App\Services\CacheService;
/**
 * Home Controller - VERSION CORRIGÉE
 * Gère la page d'accueil complète
 */
class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     */
    public function index(Request $request): void
{
    $restaurantModel = new Restaurant();
    $reviewModel = new Review();
    $cache = new CacheService();
    
    // 1. Total restaurants (cache 30 min)
    $totalRestaurants = $cache->remember('home_total', function() use ($restaurantModel) {
        return $restaurantModel->getTotalValidated();
    }, 1800);
    
    // 2. Restaurants mis en avant (cache 15 min)
    $featuredRestaurants = $cache->remember('home_featured', function() use ($restaurantModel) {
        return $restaurantModel->getFeatured(6);
    }, 900);

    // 2b. Top restaurants par région (cache 1h)
    $topByRegion = $cache->remember('home_top_by_region', function() use ($restaurantModel) {
        return $restaurantModel->getTopByRegion(4, 6);
    }, 3600);

    // 3. Derniers restaurants validés (cache 10 min)
    $latestRestaurants = $cache->remember('home_latest', function() use ($restaurantModel) {
        return $restaurantModel->getLatest(8);
    }, 600);
    
    // 4. Villes populaires (cache 1h)
    $popularCities = $cache->remember('home_cities', function() use ($restaurantModel) {
        return $restaurantModel->getPopularCitiesAlgeria(6);
    }, 3600);

    // 4b. Total villes distinctes (cache 1h)
    $totalCities = $cache->remember('home_total_cities', function() use ($restaurantModel) {
        return $restaurantModel->getTotalCities();
    }, 3600);
    
    // 5. Types de cuisine populaires (cache 1h)
    $popularCuisines = $cache->remember('home_cuisines', function() use ($restaurantModel) {
        return $restaurantModel->getPopularCuisineTypesMapped(8);
    }, 3600);
    
    // 6. Travelers' Choice (cache 30 min)
    $travelersChoice = $cache->remember('home_travelers', function() use ($restaurantModel) {
        return $restaurantModel->getTravelersChoice(10);
    }, 1800);
    
    // 7. Restaurants familles (cache 1h)
    $familyFriendly = $cache->remember('home_family', function() use ($restaurantModel) {
        return $restaurantModel->getFamilyFriendly(6);
    }, 3600);
    
    // 8. Avis récents (cache 5 min)
    $recentReviews = $cache->remember('home_reviews', function() use ($reviewModel) {
        return $reviewModel->getRecent(6);
    }, 300);
    
    // 9. Types de cuisine pour formulaire (cache 1h)
    $cuisineTypes = $cache->remember('home_cuisine_types', function() use ($restaurantModel) {
        return $restaurantModel->getCuisineTypes();
    }, 3600);

    // 10. Total avis approuvés (cache 30 min)
    $totalReviews = $cache->remember('home_total_reviews', function() use ($reviewModel) {
        return $reviewModel->getTotalApproved();
    }, 1800);

    // 11. Popular activities (cache 30 min)
    $popularActivities = $cache->remember('home_activities', function() {
        try {
            $stmt = \App\Core\Database::getInstance()->getPdo()->query("
                SELECT a.*,
                       (SELECT path FROM activity_photos ap WHERE ap.activity_id = a.id AND ap.type = 'main' LIMIT 1) as main_photo
                FROM activities a
                WHERE a.status = 'active'
                ORDER BY a.featured DESC, a.nb_avis DESC, a.note_moyenne DESC
                LIMIT 6
            ");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }, 1800);

    // 12. Activity stats (cache 1h)
    $activityStats = $cache->remember('home_activity_stats', function() {
        try {
            $pdo = \App\Core\Database::getInstance()->getPdo();
            return [
                'total' => (int)$pdo->query("SELECT COUNT(*) FROM activities WHERE status = 'active'")->fetchColumn(),
                'categories' => (int)$pdo->query("SELECT COUNT(DISTINCT category) FROM activities WHERE status = 'active'")->fetchColumn(),
                'villes' => (int)$pdo->query("SELECT COUNT(DISTINCT ville) FROM activities WHERE status = 'active'")->fetchColumn(),
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'categories' => 0, 'villes' => 0];
        }
    }, 3600);
    $totalActivities = $activityStats['total'];

        $this->view->renderPartial('home.index', [
            'title' => 'Le Bon Resto - Découvrez les meilleurs restaurants',
            'totalRestaurants' => $totalRestaurants,
            'totalReviews' => $totalReviews,
            'featuredRestaurants' => $featuredRestaurants,
            'topByRegion' => $topByRegion,
            'latestRestaurants' => $latestRestaurants,
            'popularCities' => $popularCities,
            'totalCities' => $totalCities,
            'popularCuisines' => $popularCuisines,
            'travelersChoice' => $travelersChoice,
            'familyFriendly' => $familyFriendly,
            'recentReviews' => $recentReviews,
            'cuisineTypes' => $cuisineTypes,
            'popularActivities' => $popularActivities,
            'totalActivities' => $totalActivities,
            'activityStats' => $activityStats,
            'pageCSS' => ['home-modern'],
            'pageJS' => ['home-modern']
        ]);
    }
}