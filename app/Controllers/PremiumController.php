<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CacheService;
use App\Services\RateLimiter;
use App\Services\NotificationService;
use PDO;

/**
 * F35 - Premium Subscriptions Controller
 * Gestion des abonnements premium pour les proprietaires de restaurants
 * Plans: Essentiel, Pro, Elite avec periode d'essai de 14 jours
 */
class PremiumController extends Controller
{
    /**
     * Page publique des plans premium
     * GET /premium
     */
    public function plans(): void
    {
        $plans = [
            [
                'id' => 'essentiel',
                'name' => 'Essentiel',
                'price_monthly' => 2900,
                'price_yearly' => 29000,
                'features' => [
                    'Badge Premium sur votre fiche',
                    'Statistiques de base',
                    'Reponse aux avis',
                    'Notifications en temps reel',
                    'Support par email',
                ],
                'highlight' => false,
            ],
            [
                'id' => 'pro',
                'name' => 'Pro',
                'price_monthly' => 5900,
                'price_yearly' => 59000,
                'features' => [
                    'Tout Essentiel +',
                    'Mise en avant dans la recherche',
                    'Statistiques avancees (analytics)',
                    'Gestion des offres promotionnelles',
                    'Commandes en ligne',
                    'Menu digital complet',
                    'Support prioritaire',
                ],
                'highlight' => true,
            ],
            [
                'id' => 'elite',
                'name' => 'Elite',
                'price_monthly' => 9900,
                'price_yearly' => 99000,
                'features' => [
                    'Tout Pro +',
                    'Position premium en tete de liste',
                    'Campagnes publicitaires integrees',
                    'Concierge IA personnalise',
                    'Widget site web',
                    'API acces complet',
                    'Account manager dedie',
                    'Rapports mensuels personnalises',
                ],
                'highlight' => false,
            ],
        ];

        $this->render('premium.index', [
            'title' => 'Plans Premium - LeBonResto',
            'plans' => $plans,
        ]);
    }

    /**
     * Souscrire a un plan premium (proprietaire)
     * POST /api/premium/subscribe
     */
    public function subscribe(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        // Rate limit: 5 subscribe attempts per hour
        if (!RateLimiter::attempt("premium_subscribe_$userId", 5, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de tentatives. Reessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $planId = trim($input['plan'] ?? '');
        $billing = trim($input['billing'] ?? 'monthly');

        // Validate plan
        $validPlans = ['essentiel', 'pro', 'elite'];
        if (!in_array($planId, $validPlans)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Plan invalide']);
            return;
        }

        // Validate billing cycle
        if (!in_array($billing, ['monthly', 'yearly'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cycle de facturation invalide']);
            return;
        }

        // Check user owns a restaurant
        $stmt = $this->db->prepare("
            SELECT id, nom FROM restaurants WHERE owner_id = :uid AND status = 'validated' LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Vous devez etre proprietaire d\'un restaurant valide']);
            return;
        }

        $restaurantId = (int)$restaurant['id'];

        // Check for existing active/trial subscription
        $existingStmt = $this->db->prepare("
            SELECT id, plan_id, status FROM premium_subscriptions
            WHERE restaurant_id = :rid AND status IN ('active', 'trial')
            LIMIT 1
        ");
        $existingStmt->execute([':rid' => $restaurantId]);
        $existing = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'Vous avez deja un abonnement actif (plan: ' . $existing['plan_id'] . ')',
            ]);
            return;
        }

        // Price mapping (in centimes DZD)
        $prices = [
            'essentiel' => ['monthly' => 2900, 'yearly' => 29000],
            'pro' => ['monthly' => 5900, 'yearly' => 59000],
            'elite' => ['monthly' => 9900, 'yearly' => 99000],
        ];

        $price = $prices[$planId][$billing];
        $trialEndsAt = date('Y-m-d H:i:s', strtotime('+14 days'));

        try {
            $this->db->beginTransaction();

            // Create subscription with trial
            $insertStmt = $this->db->prepare("
                INSERT INTO premium_subscriptions
                    (restaurant_id, user_id, plan_id, billing_cycle, price, status,
                     trial_ends_at, created_at, updated_at)
                VALUES
                    (:rid, :uid, :plan, :billing, :price, 'trial',
                     :trial_ends, NOW(), NOW())
            ");
            $insertStmt->execute([
                ':rid' => $restaurantId,
                ':uid' => $userId,
                ':plan' => $planId,
                ':billing' => $billing,
                ':price' => $price,
                ':trial_ends' => $trialEndsAt,
            ]);
            $subscriptionId = (int)$this->db->lastInsertId();

            // Mark restaurant as premium
            $updateStmt = $this->db->prepare("
                UPDATE restaurants SET is_premium = 1 WHERE id = :rid
            ");
            $updateStmt->execute([':rid' => $restaurantId]);

            $this->db->commit();

            // Send notification
            NotificationService::create(
                $userId,
                'premium',
                'Abonnement Premium active !',
                'Votre essai gratuit de 14 jours pour le plan ' . ucfirst($planId) . ' est actif. Profitez de toutes les fonctionnalites !',
                ['subscription_id' => $subscriptionId, 'plan' => $planId]
            );

            echo json_encode([
                'success' => true,
                'subscription_id' => $subscriptionId,
                'plan' => $planId,
                'status' => 'trial',
                'trial_ends_at' => $trialEndsAt,
                'message' => 'Essai gratuit de 14 jours active pour le plan ' . ucfirst($planId) . ' !',
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la souscription']);
        }
    }

    /**
     * Annuler un abonnement premium
     * POST /api/premium/cancel
     */
    public function cancel(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        // Find active subscription for this user
        $stmt = $this->db->prepare("
            SELECT ps.id, ps.restaurant_id, ps.plan_id, ps.status
            FROM premium_subscriptions ps
            WHERE ps.user_id = :uid AND ps.status IN ('active', 'trial')
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subscription) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Aucun abonnement actif trouve']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Cancel subscription
            $cancelStmt = $this->db->prepare("
                UPDATE premium_subscriptions
                SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
                WHERE id = :sid
            ");
            $cancelStmt->execute([':sid' => $subscription['id']]);

            // Remove premium from restaurant
            $updateStmt = $this->db->prepare("
                UPDATE restaurants SET is_premium = 0 WHERE id = :rid
            ");
            $updateStmt->execute([':rid' => $subscription['restaurant_id']]);

            $this->db->commit();

            // Notify user
            NotificationService::create(
                $userId,
                'premium',
                'Abonnement Premium annule',
                'Votre abonnement ' . ucfirst($subscription['plan_id']) . ' a ete annule. Vous pouvez vous reabonner a tout moment.',
                ['subscription_id' => $subscription['id']]
            );

            echo json_encode([
                'success' => true,
                'message' => 'Abonnement annule avec succes',
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'annulation']);
        }
    }

    /**
     * Recuperer les details de l'abonnement courant
     * GET /api/premium/my-subscription
     */
    public function mySubscription(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT ps.id, ps.restaurant_id, ps.plan_id, ps.billing_cycle, ps.price,
                   ps.status, ps.trial_ends_at, ps.cancelled_at, ps.created_at, ps.updated_at,
                   r.nom AS restaurant_name
            FROM premium_subscriptions ps
            JOIN restaurants r ON r.id = ps.restaurant_id
            WHERE ps.user_id = :uid
            ORDER BY ps.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$subscription) {
            echo json_encode([
                'success' => true,
                'subscription' => null,
                'message' => 'Aucun abonnement trouve',
            ]);
            return;
        }

        // Check if trial has expired
        if ($subscription['status'] === 'trial' && $subscription['trial_ends_at']) {
            $trialEnd = strtotime($subscription['trial_ends_at']);
            if ($trialEnd < time()) {
                // Auto-expire trial
                $expireStmt = $this->db->prepare("
                    UPDATE premium_subscriptions
                    SET status = 'expired', updated_at = NOW()
                    WHERE id = :sid
                ");
                $expireStmt->execute([':sid' => $subscription['id']]);

                $removePremiumStmt = $this->db->prepare("
                    UPDATE restaurants SET is_premium = 0 WHERE id = :rid
                ");
                $removePremiumStmt->execute([':rid' => $subscription['restaurant_id']]);

                $subscription['status'] = 'expired';
            }
        }

        // Calculate days remaining for trial
        $daysRemaining = null;
        if ($subscription['status'] === 'trial' && $subscription['trial_ends_at']) {
            $diff = strtotime($subscription['trial_ends_at']) - time();
            $daysRemaining = max(0, (int)ceil($diff / 86400));
        }

        echo json_encode([
            'success' => true,
            'subscription' => [
                'id' => (int)$subscription['id'],
                'restaurant_id' => (int)$subscription['restaurant_id'],
                'restaurant_name' => $subscription['restaurant_name'],
                'plan' => $subscription['plan_id'],
                'billing_cycle' => $subscription['billing_cycle'],
                'price' => (int)$subscription['price'],
                'status' => $subscription['status'],
                'trial_ends_at' => $subscription['trial_ends_at'],
                'trial_days_remaining' => $daysRemaining,
                'cancelled_at' => $subscription['cancelled_at'],
                'created_at' => $subscription['created_at'],
            ],
        ]);
    }
}
