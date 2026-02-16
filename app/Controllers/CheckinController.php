<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\LoyaltyService;
use App\Services\ActivityFeedService;
use App\Services\NotificationService;
use App\Services\RateLimiter;
use PDO;

/**
 * Controller pour le système de Check-in géographique
 */
class CheckinController extends Controller
{
    private const MAX_DISTANCE_METERS = 200;
    private const CHECKIN_COOLDOWN_HOURS = 4;
    private const POINTS_PER_CHECKIN = 20;

    /**
     * API - Effectuer un check-in
     * POST /api/restaurants/{id}/checkin
     * Body: { lat: float, lng: float }
     */
    public function store(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $restaurantId = (int)$request->param('id');

        // Rate limit: 10 check-ins par heure
        if (!RateLimiter::attempt("checkin_$userId", 10, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de check-ins. Reessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $userLat = (float)($input['lat'] ?? 0);
        $userLng = (float)($input['lng'] ?? 0);

        if (!$userLat || !$userLng) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Coordonnees GPS requises']);
            return;
        }

        // Vérifier que le restaurant existe et a des coordonnées
        $stmt = $this->db->prepare("
            SELECT id, nom, slug, gps_latitude, gps_longitude
            FROM restaurants
            WHERE id = :rid AND status = 'validated'
        ");
        $stmt->execute([':rid' => $restaurantId]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant || !$restaurant['gps_latitude'] || !$restaurant['gps_longitude']) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouve ou sans coordonnees GPS']);
            return;
        }

        // Vérifier le cooldown (pas de double check-in dans les X heures)
        $cooldownStmt = $this->db->prepare("
            SELECT id FROM checkins
            WHERE user_id = :uid AND restaurant_id = :rid
            AND created_at > DATE_SUB(NOW(), INTERVAL :hours HOUR)
        ");
        $cooldownStmt->execute([
            ':uid' => $userId,
            ':rid' => $restaurantId,
            ':hours' => self::CHECKIN_COOLDOWN_HOURS,
        ]);
        if ($cooldownStmt->fetch()) {
            echo json_encode([
                'success' => false,
                'error' => 'Vous avez deja fait un check-in ici recemment. Revenez dans quelques heures !'
            ]);
            return;
        }

        // Calculer la distance (formule Haversine)
        $distance = $this->haversineDistance(
            $userLat, $userLng,
            (float)$restaurant['gps_latitude'], (float)$restaurant['gps_longitude']
        );

        $distanceMeters = (int)round($distance * 1000);

        if ($distanceMeters > self::MAX_DISTANCE_METERS) {
            echo json_encode([
                'success' => false,
                'error' => "Vous etes trop loin du restaurant ({$distanceMeters}m). Rapprochez-vous a moins de " . self::MAX_DISTANCE_METERS . "m.",
                'distance' => $distanceMeters,
                'max_distance' => self::MAX_DISTANCE_METERS,
            ]);
            return;
        }

        // Tout est bon : enregistrer le check-in
        $this->db->beginTransaction();
        try {
            $insertStmt = $this->db->prepare("
                INSERT INTO checkins (user_id, restaurant_id, user_lat, user_lng, distance_m, points_earned)
                VALUES (:uid, :rid, :lat, :lng, :dist, :pts)
            ");
            $insertStmt->execute([
                ':uid' => $userId,
                ':rid' => $restaurantId,
                ':lat' => $userLat,
                ':lng' => $userLng,
                ':dist' => $distanceMeters,
                ':pts' => self::POINTS_PER_CHECKIN,
            ]);

            // Ajouter des points de fidélité
            $loyaltyService = new LoyaltyService($this->db);
            $loyaltyService->addPoints($userId, 'checkin', $restaurantId, 'restaurant');

            // Log dans le fil d'actualité
            $feedService = new ActivityFeedService($this->db);
            $feedService->log($userId, 'checkin', 'restaurant', $restaurantId, [
                'restaurant_name' => $restaurant['nom'],
                'distance' => $distanceMeters,
            ]);

            $this->db->commit();

            // Compter le total de check-ins
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM checkins WHERE user_id = :uid");
            $countStmt->execute([':uid' => $userId]);
            $totalCheckins = (int)$countStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'message' => "Check-in valide ! +{" . self::POINTS_PER_CHECKIN . "} points",
                'distance' => $distanceMeters,
                'points_earned' => self::POINTS_PER_CHECKIN,
                'total_checkins' => $totalCheckins,
                'restaurant_name' => $restaurant['nom'],
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * API - Historique des check-ins de l'utilisateur
     * GET /api/checkins
     */
    public function history(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));

        $stmt = $this->db->prepare("
            SELECT c.*, r.nom as restaurant_nom, r.slug as restaurant_slug, r.ville,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as restaurant_photo
            FROM checkins c
            INNER JOIN restaurants r ON r.id = c.restaurant_id
            WHERE c.user_id = :uid
            ORDER BY c.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    /**
     * API - Vérifier si un check-in a été fait pour ce resto (pour badge "Avis vérifié")
     * GET /api/restaurants/{id}/checkin-status
     */
    public function status(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => true, 'has_checkin' => false]);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $restaurantId = (int)$request->param('id');

        $stmt = $this->db->prepare("
            SELECT id, created_at FROM checkins
            WHERE user_id = :uid AND restaurant_id = :rid
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([':uid' => $userId, ':rid' => $restaurantId]);
        $checkin = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'has_checkin' => (bool)$checkin,
            'last_checkin' => $checkin['created_at'] ?? null,
        ]);
    }

    /**
     * Formule Haversine : distance entre 2 points GPS en km
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
