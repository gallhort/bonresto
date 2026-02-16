<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use App\Services\LoyaltyService;
use PDO;

class TipController extends Controller
{
    /**
     * API - Quick Tips pour un restaurant
     * GET /api/restaurants/{id}/tips
     */
    public function list(Request $request): void
    {
        header('Content-Type: application/json');
        $restaurantId = (int)$request->param('id');

        $stmt = $this->db->prepare("
            SELECT t.*, u.prenom, u.nom as user_nom, u.photo_profil as user_photo
            FROM restaurant_tips t
            INNER JOIN users u ON u.id = t.user_id
            WHERE t.restaurant_id = :rid AND t.status = 'approved'
            ORDER BY t.votes DESC, t.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([':rid' => $restaurantId]);

        echo json_encode(['success' => true, 'tips' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    /**
     * API - Poster un Quick Tip
     * POST /api/restaurants/{id}/tip
     */
    public function store(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifie']);
            return;
        }

        if (!RateLimiter::attempt('tip_create', 5, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Limite atteinte (5 tips/heure)']);
            return;
        }

        $restaurantId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];
        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (empty($message) || mb_strlen($message) < 5 || mb_strlen($message) > 200) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le conseil doit faire entre 5 et 200 caracteres']);
            return;
        }

        // Check duplicate
        $check = $this->db->prepare("
            SELECT id FROM restaurant_tips WHERE user_id = :uid AND restaurant_id = :rid AND message = :msg
        ");
        $check->execute([':uid' => $userId, ':rid' => $restaurantId, ':msg' => $message]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Vous avez deja poste ce conseil']);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO restaurant_tips (user_id, restaurant_id, message, status, created_at)
            VALUES (:uid, :rid, :msg, 'approved', NOW())
        ");
        $stmt->execute([':uid' => $userId, ':rid' => $restaurantId, ':msg' => $message]);
        $tipId = (int)$this->db->lastInsertId();

        // Points loyalty
        try {
            $loyaltyService = new LoyaltyService($this->db);
            $loyaltyService->addPoints($userId, 'tip', $tipId, 'tip');
        } catch (\Exception $e) {}

        echo json_encode(['success' => true, 'tip_id' => $tipId, 'message' => 'Conseil publie !']);
    }

    /**
     * API - Voter pour un tip
     * POST /api/tips/{id}/vote
     */
    public function vote(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifie']);
            return;
        }

        $tipId = (int)$request->param('id');
        $this->db->prepare("UPDATE restaurant_tips SET votes = votes + 1 WHERE id = ?")->execute([$tipId]);
        $stmt = $this->db->prepare("SELECT votes FROM restaurant_tips WHERE id = ?");
        $stmt->execute([$tipId]);
        $votes = (int)$stmt->fetchColumn();

        echo json_encode(['success' => true, 'votes' => $votes]);
    }
}
