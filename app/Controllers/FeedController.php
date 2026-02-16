<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ActivityFeedService;
use App\Services\RecommendationService;
use PDO;

/**
 * Controller pour le fil d'actualité social et les suggestions
 */
class FeedController extends Controller
{
    /**
     * Page - Fil d'actualité
     * GET /feed
     */
    public function index(Request $request): void
    {
        $feedService = new ActivityFeedService($this->db);
        $tab = $_GET['tab'] ?? 'all';

        if ($tab === 'following' && $this->isAuthenticated()) {
            $activities = $feedService->getFollowingFeed((int)$_SESSION['user']['id'], 30);
        } else {
            $tab = 'all';
            $activities = $feedService->getPublicFeed(30);
        }

        // Suggestions personnalisées si connecté
        $suggestions = [];
        if ($this->isAuthenticated()) {
            $recoService = new RecommendationService($this->db);
            $suggestions = $recoService->getPersonalizedSuggestions(
                (int)$_SESSION['user']['id'], 4
            );
        }

        $this->render('feed/index', [
            'title' => 'Fil d\'actualite',
            'activities' => $activities,
            'suggestions' => $suggestions,
            'currentTab' => $tab,
            'isAuthenticated' => $this->isAuthenticated(),
        ]);
    }

    /**
     * API - Charger plus d'activités (infinite scroll)
     * GET /api/feed?offset=X
     */
    public function apiFeed(Request $request): void
    {
        header('Content-Type: application/json');

        $offset = max(0, (int)($_GET['offset'] ?? 0));
        $limit = min(30, max(1, (int)($_GET['limit'] ?? 15)));

        $feedService = new ActivityFeedService($this->db);
        $tab = $_GET['tab'] ?? 'all';

        if ($tab === 'following' && $this->isAuthenticated()) {
            $activities = $feedService->getFollowingFeed((int)$_SESSION['user']['id'], $limit, $offset);
        } else {
            $activities = $feedService->getPublicFeed($limit, $offset);
        }

        // Formatter les activités
        $formatted = [];
        foreach ($activities as $act) {
            $reviewPhotos = !empty($act['review_photos']) ? explode('|||', $act['review_photos']) : [];
            $formatted[] = [
                'id' => $act['id'],
                'action_type' => $act['action_type'],
                'html' => ActivityFeedService::formatActivity($act),
                'icon' => ActivityFeedService::getActivityIcon($act['action_type']),
                'color' => ActivityFeedService::getActivityColor($act['action_type']),
                'time_ago' => $act['time_ago'],
                'user_photo' => $act['user_photo'] ?? null,
                'user_badge' => $act['user_badge'] ?? null,
                'user_name' => ($act['prenom'] ?? '') . ' ' . ($act['user_nom'] ?? ''),
                'restaurant_slug' => $act['restaurant_slug'] ?? null,
                'restaurant_name' => $act['restaurant_nom'] ?? null,
                'restaurant_city' => $act['restaurant_ville'] ?? null,
                'restaurant_photo' => $act['restaurant_photo'] ?? null,
                'review_message' => $act['review_message'] ?? null,
                'review_photos' => $reviewPhotos,
                'review_note' => $act['review_note'] ?? null,
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $formatted,
            'has_more' => count($formatted) === $limit,
        ]);
    }

    /**
     * API - Suggestions personnalisées
     * GET /api/suggestions
     */
    public function apiSuggestions(Request $request): void
    {
        header('Content-Type: application/json');

        $limit = min(10, max(1, (int)($_GET['limit'] ?? 6)));
        $recoService = new RecommendationService($this->db);

        if ($this->isAuthenticated()) {
            $suggestions = $recoService->getPersonalizedSuggestions(
                (int)$_SESSION['user']['id'], $limit
            );
        } else {
            $suggestions = $recoService->getPersonalizedSuggestions(0, $limit);
        }

        echo json_encode([
            'success' => true,
            'data' => $suggestions,
            'personalized' => $this->isAuthenticated(),
        ]);
    }
}
