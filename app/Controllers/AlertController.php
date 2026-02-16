<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\NotificationService;
use App\Services\RateLimiter;
use App\Services\Logger;
use PDO;

/**
 * F18 - AlertController
 * Alertes de disponibilite pour les restaurants (notification quand une table se libere)
 */
class AlertController extends Controller
{
    /**
     * Nombre maximum d'alertes actives par utilisateur
     */
    private const MAX_ACTIVE_ALERTS = 5;

    /**
     * Seuil de reservations acceptees en dessous duquel on considere qu'il y a de la disponibilite
     */
    private const AVAILABILITY_THRESHOLD = 10;

    /**
     * API - Creer une alerte de disponibilite
     * POST /api/availability-alerts
     */
    public function create(Request $request): void
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

        // Rate limit: 10 alert creations per hour
        if (!RateLimiter::attempt("alert_create_{$userId}", 10, 3600)) {
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

        $restaurantId = (int)($input['restaurant_id'] ?? 0);
        $desiredDate = trim($input['desired_date'] ?? '');
        $desiredTime = trim($input['desired_time'] ?? '');
        $partySize = max(1, (int)($input['party_size'] ?? 2));

        // Validate restaurant exists
        $restStmt = $this->db->prepare("
            SELECT id, nom, slug FROM restaurants WHERE id = :rid AND status = 'validated'
        ");
        $restStmt->execute([':rid' => $restaurantId]);
        $restaurant = $restStmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouve']);
            return;
        }

        // Validate date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desiredDate)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Date invalide']);
            return;
        }

        if ($desiredDate < date('Y-m-d')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La date doit etre aujourd\'hui ou dans le futur']);
            return;
        }

        // Validate optional time
        if ($desiredTime !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $desiredTime)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Heure invalide']);
            return;
        }

        if ($partySize < 1 || $partySize > 50) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nombre de personnes invalide (1-50)']);
            return;
        }

        // Check max active alerts for user
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM availability_alerts
            WHERE user_id = :uid AND is_notified = 0 AND desired_date >= CURDATE()
        ");
        $countStmt->execute([':uid' => $userId]);
        $activeCount = (int)$countStmt->fetchColumn();

        if ($activeCount >= self::MAX_ACTIVE_ALERTS) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Vous avez atteint la limite de ' . self::MAX_ACTIVE_ALERTS . ' alertes actives. Supprimez-en une avant d\'en creer une nouvelle.',
            ]);
            return;
        }

        // Check for duplicate alert (same restaurant + same date)
        $dupeStmt = $this->db->prepare("
            SELECT id FROM availability_alerts
            WHERE user_id = :uid AND restaurant_id = :rid AND desired_date = :ddate AND is_notified = 0
        ");
        $dupeStmt->execute([':uid' => $userId, ':rid' => $restaurantId, ':ddate' => $desiredDate]);
        if ($dupeStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vous avez deja une alerte pour ce restaurant a cette date']);
            return;
        }

        try {
            $insertStmt = $this->db->prepare("
                INSERT INTO availability_alerts (user_id, restaurant_id, desired_date, desired_time,
                                                 party_size, is_notified, created_at)
                VALUES (:uid, :rid, :ddate, :dtime, :psize, 0, NOW())
            ");
            $insertStmt->execute([
                ':uid' => $userId,
                ':rid' => $restaurantId,
                ':ddate' => $desiredDate,
                ':dtime' => $desiredTime ?: null,
                ':psize' => $partySize,
            ]);

            $alertId = (int)$this->db->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => 'Alerte creee. Vous serez notifie si une disponibilite se libere.',
                'alert_id' => $alertId,
            ]);
        } catch (\Exception $e) {
            Logger::error('AlertController::create error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la creation de l\'alerte']);
        }
    }

    /**
     * API - Recuperer les alertes actives de l'utilisateur
     * GET /api/my-alerts
     */
    public function myAlerts(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT a.id, a.restaurant_id, a.desired_date, a.desired_time, a.party_size,
                   a.is_notified, a.notified_at, a.created_at,
                   r.nom AS restaurant_nom, r.slug AS restaurant_slug, r.ville
            FROM availability_alerts a
            JOIN restaurants r ON r.id = a.restaurant_id
            WHERE a.user_id = :uid AND a.desired_date >= CURDATE()
            ORDER BY a.desired_date ASC
        ");
        $stmt->execute([':uid' => $userId]);
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'alerts' => $alerts,
        ]);
    }

    /**
     * API - Supprimer une alerte
     * POST /api/availability-alerts/{id}/delete
     */
    public function delete(Request $request): void
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
        $alertId = (int)$request->param('id');

        // Verify ownership
        $stmt = $this->db->prepare("
            SELECT id FROM availability_alerts WHERE id = :aid AND user_id = :uid
        ");
        $stmt->execute([':aid' => $alertId, ':uid' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Alerte non trouvee']);
            return;
        }

        try {
            $delStmt = $this->db->prepare("DELETE FROM availability_alerts WHERE id = :aid AND user_id = :uid");
            $delStmt->execute([':aid' => $alertId, ':uid' => $userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Alerte supprimee',
            ]);
        } catch (\Exception $e) {
            Logger::error('AlertController::delete error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression']);
        }
    }

    /**
     * Cron - Verifier les disponibilites et notifier les utilisateurs
     * GET /api/cron/check-availability
     *
     * For each unnotified alert where desired_date is today or tomorrow:
     * check if the restaurant has fewer than AVAILABILITY_THRESHOLD accepted reservations
     * for that date. If so, notify the user and mark the alert as notified.
     */
    public function checkAndNotify(): void
    {
        header('Content-Type: application/json');

        // Rate limit cron: 1 call per 5 minutes
        if (!RateLimiter::attempt('cron_check_availability', 1, 300)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Cron deja en cours. Reessayez dans quelques minutes.']);
            return;
        }

        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        // Fetch all unnotified alerts for today or tomorrow
        $stmt = $this->db->prepare("
            SELECT a.id, a.user_id, a.restaurant_id, a.desired_date, a.desired_time, a.party_size,
                   r.nom AS restaurant_nom, r.slug AS restaurant_slug
            FROM availability_alerts a
            JOIN restaurants r ON r.id = a.restaurant_id
            WHERE a.is_notified = 0
              AND a.desired_date IN (:today, :tomorrow)
            ORDER BY a.desired_date ASC
        ");
        $stmt->execute([':today' => $today, ':tomorrow' => $tomorrow]);
        $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($alerts)) {
            echo json_encode([
                'success' => true,
                'message' => 'Aucune alerte a traiter',
                'processed' => 0,
                'notified' => 0,
            ]);
            return;
        }

        $notifService = new NotificationService($this->db);
        $processed = 0;
        $notified = 0;

        // Group by restaurant_id + desired_date to avoid repeated queries
        $availabilityCache = [];

        foreach ($alerts as $alert) {
            $processed++;
            $cacheKey = $alert['restaurant_id'] . '_' . $alert['desired_date'];

            if (!isset($availabilityCache[$cacheKey])) {
                // Count accepted reservations for this restaurant on this date
                $countStmt = $this->db->prepare("
                    SELECT COUNT(*) FROM reservations
                    WHERE restaurant_id = :rid AND date_souhaitee = :ddate AND status = 'accepted'
                ");
                $countStmt->execute([':rid' => $alert['restaurant_id'], ':ddate' => $alert['desired_date']]);
                $availabilityCache[$cacheKey] = (int)$countStmt->fetchColumn();
            }

            $acceptedCount = $availabilityCache[$cacheKey];

            // If below threshold, there is availability
            if ($acceptedCount < self::AVAILABILITY_THRESHOLD) {
                // Mark alert as notified
                $updStmt = $this->db->prepare("
                    UPDATE availability_alerts SET is_notified = 1, notified_at = NOW() WHERE id = :aid
                ");
                $updStmt->execute([':aid' => $alert['id']]);

                // Create notification for user
                $dateFormatted = date('d/m/Y', strtotime($alert['desired_date']));
                $notifService->create(
                    (int)$alert['user_id'],
                    'availability_alert',
                    'Disponibilite trouvee !',
                    'Le restaurant ' . $alert['restaurant_nom'] . ' a des disponibilites le ' . $dateFormatted . '. Reservez vite !',
                    ['link' => '/restaurant/' . $alert['restaurant_slug']]
                );

                $notified++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Traitement termine: $processed alertes traitees, $notified notifications envoyees",
            'processed' => $processed,
            'notified' => $notified,
        ]);
    }
}
