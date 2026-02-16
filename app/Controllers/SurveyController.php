<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\LoyaltyService;
use App\Services\CacheService;
use App\Services\RateLimiter;
use App\Services\Logger;
use PDO;

/**
 * F17 - SurveyController
 * Questionnaire de satisfaction post-visite lie aux reservations
 */
class SurveyController extends Controller
{
    /**
     * Page formulaire de sondage post-visite
     * GET /survey/{reservationId}
     */
    public function show(Request $request): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            return;
        }

        $reservationId = (int)$request->param('reservationId');
        $userId = (int)$_SESSION['user']['id'];

        // Fetch reservation - must belong to user, be accepted, and date in the past
        $stmt = $this->db->prepare("
            SELECT res.id, res.user_id, res.restaurant_id, res.date_souhaitee, res.heure, res.status,
                   r.nom AS restaurant_nom, r.slug AS restaurant_slug
            FROM reservations res
            JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.id = :rid AND res.user_id = :uid
        ");
        $stmt->execute([':rid' => $reservationId, ':uid' => $userId]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            $this->notFound('Reservation non trouvee');
            return;
        }

        // Must be accepted
        if ($reservation['status'] !== 'accepted') {
            $this->render('survey.form', [
                'title' => 'Sondage - Erreur',
                'error' => 'Cette reservation n\'a pas ete acceptee.',
                'reservation' => $reservation,
            ]);
            return;
        }

        // Must be in the past
        if ($reservation['date_souhaitee'] >= date('Y-m-d')) {
            $this->render('survey.form', [
                'title' => 'Sondage - Erreur',
                'error' => 'Vous pourrez remplir le sondage apres votre visite.',
                'reservation' => $reservation,
            ]);
            return;
        }

        // Check no existing survey
        $existStmt = $this->db->prepare("
            SELECT id FROM surveys WHERE reservation_id = :rid
        ");
        $existStmt->execute([':rid' => $reservationId]);
        if ($existStmt->fetch()) {
            $this->render('survey.form', [
                'title' => 'Sondage - Deja rempli',
                'error' => 'Vous avez deja rempli le sondage pour cette reservation.',
                'reservation' => $reservation,
            ]);
            return;
        }

        $this->render('survey.form', [
            'title' => 'Sondage de satisfaction - ' . htmlspecialchars($reservation['restaurant_nom']),
            'reservation' => $reservation,
            'error' => null,
        ]);
    }

    /**
     * API - Soumettre un sondage
     * POST /api/surveys
     */
    public function submit(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        // Rate limit: 10 surveys per hour
        if (!RateLimiter::attempt("survey_submit_{$userId}", 10, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de requetes. Reessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Donnees invalides']);
            return;
        }

        $reservationId = (int)($input['reservation_id'] ?? 0);
        $foodRating = (int)($input['food_rating'] ?? 0);
        $serviceRating = (int)($input['service_rating'] ?? 0);
        $ambianceRating = (int)($input['ambiance_rating'] ?? 0);
        $valueRating = (int)($input['value_rating'] ?? 0);
        $wouldRecommend = (int)($input['would_recommend'] ?? 0);
        $feedback = trim($input['feedback'] ?? '');

        // Validate reservation ownership
        $resStmt = $this->db->prepare("
            SELECT res.id, res.user_id, res.restaurant_id, res.date_souhaitee, res.status,
                   r.nom AS restaurant_nom
            FROM reservations res
            JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.id = :rid AND res.user_id = :uid
        ");
        $resStmt->execute([':rid' => $reservationId, ':uid' => $userId]);
        $reservation = $resStmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Reservation non trouvee']);
            return;
        }

        if ($reservation['status'] !== 'accepted') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cette reservation n\'a pas ete acceptee']);
            return;
        }

        if ($reservation['date_souhaitee'] >= date('Y-m-d')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez remplir le sondage qu\'apres votre visite']);
            return;
        }

        // Check no existing survey
        $existStmt = $this->db->prepare("SELECT id FROM surveys WHERE reservation_id = :rid");
        $existStmt->execute([':rid' => $reservationId]);
        if ($existStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vous avez deja rempli ce sondage']);
            return;
        }

        // Validate ratings
        $errors = [];
        foreach (['food_rating' => $foodRating, 'service_rating' => $serviceRating, 'ambiance_rating' => $ambianceRating, 'value_rating' => $valueRating] as $field => $val) {
            if ($val < 1 || $val > 5) {
                $label = str_replace('_', ' ', $field);
                $errors[] = ucfirst($label) . ' doit etre entre 1 et 5';
            }
        }

        if (!in_array($wouldRecommend, [0, 1])) {
            $errors[] = 'Recommandation doit etre 0 ou 1';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Sanitize feedback
        $feedback = mb_substr($feedback, 0, 2000);

        try {
            $insertStmt = $this->db->prepare("
                INSERT INTO surveys (reservation_id, user_id, restaurant_id, food_rating, service_rating,
                                     ambiance_rating, value_rating, would_recommend, feedback, created_at)
                VALUES (:res_id, :uid, :rest_id, :food, :service, :ambiance, :value, :recommend, :feedback, NOW())
            ");
            $insertStmt->execute([
                ':res_id' => $reservationId,
                ':uid' => $userId,
                ':rest_id' => (int)$reservation['restaurant_id'],
                ':food' => $foodRating,
                ':service' => $serviceRating,
                ':ambiance' => $ambianceRating,
                ':value' => $valueRating,
                ':recommend' => $wouldRecommend,
                ':feedback' => $feedback ?: null,
            ]);

            // Award 5 loyalty points
            try {
                $loyaltyService = new LoyaltyService($this->db);
                $loyaltyService->addPoints($userId, 'survey', $reservationId, 'survey');
            } catch (\Exception $e) {
                Logger::error('SurveyController: loyalty points error: ' . $e->getMessage());
            }

            // Invalidate cache for this restaurant's survey results
            $cache = new CacheService();
            $cache->delete('survey_results_' . $reservation['restaurant_id']);

            echo json_encode([
                'success' => true,
                'message' => 'Merci pour votre retour ! +5 points de fidelite',
            ]);
        } catch (\Exception $e) {
            Logger::error('SurveyController::submit error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du sondage']);
        }
    }

    /**
     * API - Resultats agreges des sondages pour un restaurant
     * GET /api/restaurants/{id}/survey-results
     */
    public function results(Request $request): void
    {
        header('Content-Type: application/json');

        $restaurantId = (int)$request->param('id');

        // Check restaurant exists
        $restStmt = $this->db->prepare("SELECT id, nom FROM restaurants WHERE id = :rid AND status = 'validated'");
        $restStmt->execute([':rid' => $restaurantId]);
        $restaurant = $restStmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouve']);
            return;
        }

        // Cache for 1 hour
        $cache = new CacheService();
        $cacheKey = 'survey_results_' . $restaurantId;

        $results = $cache->remember($cacheKey, function () use ($restaurantId) {
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*) AS total_surveys,
                    ROUND(AVG(food_rating), 2) AS avg_food,
                    ROUND(AVG(service_rating), 2) AS avg_service,
                    ROUND(AVG(ambiance_rating), 2) AS avg_ambiance,
                    ROUND(AVG(value_rating), 2) AS avg_value,
                    ROUND(AVG((food_rating + service_rating + ambiance_rating + value_rating) / 4), 2) AS avg_overall,
                    SUM(would_recommend) AS recommend_count
                FROM surveys
                WHERE restaurant_id = :rid
            ");
            $stmt->execute([':rid' => $restaurantId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $total = (int)($row['total_surveys'] ?? 0);

            return [
                'total_surveys' => $total,
                'avg_food' => $total > 0 ? (float)$row['avg_food'] : null,
                'avg_service' => $total > 0 ? (float)$row['avg_service'] : null,
                'avg_ambiance' => $total > 0 ? (float)$row['avg_ambiance'] : null,
                'avg_value' => $total > 0 ? (float)$row['avg_value'] : null,
                'avg_overall' => $total > 0 ? (float)$row['avg_overall'] : null,
                'recommendation_rate' => $total > 0 ? round((int)$row['recommend_count'] / $total * 100, 1) : null,
            ];
        }, 3600);

        echo json_encode([
            'success' => true,
            'restaurant_id' => $restaurantId,
            'restaurant_nom' => $restaurant['nom'],
            'results' => $results,
        ]);
    }
}
