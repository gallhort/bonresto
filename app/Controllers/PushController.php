<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use App\Services\RateLimiter;
use PDO;

/**
 * F21 - Push Notifications
 * Manages Web Push subscriptions (endpoint, p256dh, auth_key) and serves the VAPID public key.
 * Actual push sending requires a server-side web-push library (e.g., minishlink/web-push).
 */
class PushController extends Controller
{
    /**
     * VAPID public key placeholder.
     * Replace with real VAPID key generated via: openssl ecparam -genkey -name prime256v1 -out private.pem
     * then use a web-push library to derive the public key.
     */
    private const VAPID_PUBLIC_KEY = 'BEl62iUYgUivxIkv69yViEuiBIa-Ib9-SkvMeAtA3LFgDzkGs-GDx5sCtBJKZLAiH6Io0-XC5bXp-Z5XB_wv6Yg';

    /**
     * POST /api/push/subscribe
     * Stores a push subscription for the authenticated user.
     * Body JSON: { endpoint, p256dh, auth_key }
     * Prevents duplicates by endpoint.
     */
    public function subscribe(): void
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

        // Rate limit: 10 subscription attempts per hour per user
        if (!RateLimiter::attempt("push_sub_{$userId}", 10, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de tentatives. Réessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $endpoint = trim($input['endpoint'] ?? '');
        $p256dh   = trim($input['p256dh'] ?? '');
        $authKey  = trim($input['auth_key'] ?? '');

        // Validate required fields
        if (!$endpoint || !filter_var($endpoint, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Endpoint invalide']);
            return;
        }

        if (!$p256dh || strlen($p256dh) < 10) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Clé p256dh invalide']);
            return;
        }

        if (!$authKey || strlen($authKey) < 5) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Clé auth invalide']);
            return;
        }

        // Limit endpoint length (URLs can be long but not infinite)
        if (strlen($endpoint) > 2000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Endpoint trop long']);
            return;
        }

        try {
            // Check for existing subscription with same endpoint
            $checkStmt = $this->db->prepare("
                SELECT id, user_id FROM push_subscriptions WHERE endpoint = :endpoint LIMIT 1
            ");
            $checkStmt->execute([':endpoint' => $endpoint]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update keys and user_id if endpoint already exists
                $updateStmt = $this->db->prepare("
                    UPDATE push_subscriptions
                    SET user_id = :uid, p256dh = :p256dh, auth_key = :auth, updated_at = NOW()
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':uid'    => $userId,
                    ':p256dh' => $p256dh,
                    ':auth'   => $authKey,
                    ':id'     => $existing['id'],
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Abonnement push mis à jour.',
                    'updated' => true,
                ]);
                return;
            }

            // Insert new subscription
            $insertStmt = $this->db->prepare("
                INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth_key, created_at, updated_at)
                VALUES (:uid, :endpoint, :p256dh, :auth, NOW(), NOW())
            ");
            $insertStmt->execute([
                ':uid'      => $userId,
                ':endpoint' => $endpoint,
                ':p256dh'   => $p256dh,
                ':auth'     => $authKey,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Notifications push activées avec succès !',
            ]);

        } catch (\Exception $e) {
            Logger::error("PushController::subscribe error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * POST /api/push/unsubscribe
     * Removes a push subscription by endpoint. Requires auth.
     * Body JSON: { endpoint }
     */
    public function unsubscribe(): void
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

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $endpoint = trim($input['endpoint'] ?? '');

        if (!$endpoint) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Endpoint requis']);
            return;
        }

        try {
            // Delete subscription matching endpoint AND user_id (security: only own subscriptions)
            $stmt = $this->db->prepare("
                DELETE FROM push_subscriptions WHERE endpoint = :endpoint AND user_id = :uid
            ");
            $stmt->execute([
                ':endpoint' => $endpoint,
                ':uid'      => $userId,
            ]);

            $deleted = $stmt->rowCount();

            if ($deleted > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notifications push désactivées.',
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Aucun abonnement trouvé pour cet endpoint.',
                ]);
            }

        } catch (\Exception $e) {
            Logger::error("PushController::unsubscribe error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * GET /api/push/vapid-key
     * Returns the VAPID public key needed by the browser to subscribe to push notifications.
     * This is a hardcoded placeholder; replace with your actual generated VAPID key.
     */
    public function getVapidKey(): void
    {
        header('Content-Type: application/json');

        echo json_encode([
            'success'    => true,
            'vapid_key'  => self::VAPID_PUBLIC_KEY,
        ]);
    }
}
