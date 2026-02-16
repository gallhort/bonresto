<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use App\Services\RateLimiter;
use PDO;

/**
 * F20 - Newsletter
 * Manages newsletter subscriptions with email, ville, and frequency preferences.
 * Supports subscribe, unsubscribe (via unique token), and preferences update.
 */
class NewsletterController extends Controller
{
    /**
     * POST /api/newsletter/subscribe
     * Subscribe an email to the newsletter.
     * Body (JSON or form): { email, ville? (optional), frequency? (optional: daily|weekly|monthly) }
     * Generates a unique unsubscribe token. Prevents duplicate subscriptions.
     */
    public function subscribe(): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        // Rate limit: 5 subscriptions per IP per hour
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt("newsletter_sub_{$ip}", 5, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de tentatives. Réessayez plus tard.']);
            return;
        }

        // Parse input (supports both JSON and form-encoded)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
        } else {
            $input = $_POST;
        }

        $email     = trim($input['email'] ?? '');
        $ville     = trim($input['ville'] ?? '');
        $frequency = trim($input['frequency'] ?? 'weekly');

        // Validate email
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Adresse email invalide']);
            return;
        }

        // Validate frequency
        $allowedFrequencies = ['weekly', 'monthly'];
        if (!in_array($frequency, $allowedFrequencies, true)) {
            $frequency = 'weekly';
        }

        // Sanitize ville (max 100 chars, strip tags)
        $ville = mb_substr(strip_tags($ville), 0, 100);

        try {
            // Check for existing subscription
            $checkStmt = $this->db->prepare("
                SELECT id, token, is_active FROM newsletter_subscriptions WHERE email = :email LIMIT 1
            ");
            $checkStmt->execute([':email' => $email]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ((int)$existing['is_active']) {
                    // Already subscribed and active
                    echo json_encode([
                        'success' => true,
                        'message' => 'Vous êtes déjà inscrit à la newsletter.',
                        'already_subscribed' => true,
                    ]);
                    return;
                }

                // Reactivate and update preferences
                $reactivateStmt = $this->db->prepare("
                    UPDATE newsletter_subscriptions
                    SET is_active = 1, ville = :ville, frequency = :freq, unsubscribed_at = NULL
                    WHERE id = :id
                ");
                $reactivateStmt->execute([
                    ':ville' => $ville ?: null,
                    ':freq'  => $frequency,
                    ':id'    => $existing['id'],
                ]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Votre inscription a été réactivée avec succès !',
                ]);
                return;
            }

            // New subscription - generate unique token
            $token = bin2hex(random_bytes(32));

            $insertStmt = $this->db->prepare("
                INSERT INTO newsletter_subscriptions (email, ville, frequency, token, is_active, created_at)
                VALUES (:email, :ville, :freq, :token, 1, NOW())
            ");
            $insertStmt->execute([
                ':email' => $email,
                ':ville' => $ville ?: null,
                ':freq'  => $frequency,
                ':token' => $token,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Inscription à la newsletter réussie ! Vous recevrez nos meilleures recommandations.',
            ]);

        } catch (\Exception $e) {
            Logger::error("NewsletterController::subscribe error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de l\'inscription']);
        }
    }

    /**
     * GET /newsletter/unsubscribe/{token}
     * Deactivates the subscription matching the token and renders a confirmation page.
     */
    public function unsubscribe(Request $request): void
    {
        $token = trim($request->param('token', ''));

        if (strlen($token) < 10) {
            $this->render('newsletter.unsubscribe', [
                'title'   => 'Désinscription',
                'success' => false,
                'message' => 'Lien de désinscription invalide.',
            ]);
            return;
        }

        try {
            // Find subscription
            $stmt = $this->db->prepare("
                SELECT id, email, is_active FROM newsletter_subscriptions WHERE token = :token LIMIT 1
            ");
            $stmt->execute([':token' => $token]);
            $sub = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sub) {
                $this->render('newsletter.unsubscribe', [
                    'title'   => 'Désinscription',
                    'success' => false,
                    'message' => 'Abonnement introuvable. Le lien est peut-être expiré.',
                ]);
                return;
            }

            if (!(int)$sub['is_active']) {
                $this->render('newsletter.unsubscribe', [
                    'title'   => 'Désinscription',
                    'success' => true,
                    'message' => 'Vous êtes déjà désinscrit de la newsletter.',
                ]);
                return;
            }

            // Deactivate
            $updateStmt = $this->db->prepare("
                UPDATE newsletter_subscriptions SET is_active = 0, unsubscribed_at = NOW() WHERE id = :id
            ");
            $updateStmt->execute([':id' => $sub['id']]);

            $this->render('newsletter.unsubscribe', [
                'title'   => 'Désinscription confirmée',
                'success' => true,
                'message' => 'Vous avez été désinscrit de la newsletter avec succès. Vous ne recevrez plus d\'emails de notre part.',
            ]);

        } catch (\Exception $e) {
            Logger::error("NewsletterController::unsubscribe error: " . $e->getMessage());
            $this->render('newsletter.unsubscribe', [
                'title'   => 'Désinscription',
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
            ]);
        }
    }

    /**
     * POST /api/newsletter/preferences
     * Update ville and/or frequency for an existing subscription (identified by token).
     * Body JSON: { token, ville?, frequency? }
     */
    public function preferences(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $token     = trim($input['token'] ?? '');
        $ville     = isset($input['ville']) ? mb_substr(strip_tags(trim($input['ville'])), 0, 100) : null;
        $frequency = trim($input['frequency'] ?? '');

        if (strlen($token) < 10) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Token invalide']);
            return;
        }

        // Validate frequency if provided
        $allowedFrequencies = ['weekly', 'monthly'];
        if ($frequency && !in_array($frequency, $allowedFrequencies, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Fréquence invalide (weekly, monthly)']);
            return;
        }

        try {
            // Find subscription by token
            $stmt = $this->db->prepare("
                SELECT id, is_active FROM newsletter_subscriptions WHERE token = :token LIMIT 1
            ");
            $stmt->execute([':token' => $token]);
            $sub = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$sub) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Abonnement introuvable']);
                return;
            }

            if (!(int)$sub['is_active']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Cet abonnement est désactivé. Réinscrivez-vous d\'abord.']);
                return;
            }

            // Build dynamic update
            $setClauses = [];
            $params     = [':id' => $sub['id']];

            if ($ville !== null) {
                $setClauses[] = 'ville = :ville';
                $params[':ville'] = $ville ?: null;
            }

            if ($frequency) {
                $setClauses[] = 'frequency = :freq';
                $params[':freq'] = $frequency;
            }

            $sql = "UPDATE newsletter_subscriptions SET " . implode(', ', $setClauses) . " WHERE id = :id";
            $updateStmt = $this->db->prepare($sql);
            $updateStmt->execute($params);

            echo json_encode([
                'success' => true,
                'message' => 'Préférences mises à jour avec succès.',
            ]);

        } catch (\Exception $e) {
            Logger::error("NewsletterController::preferences error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }
}
