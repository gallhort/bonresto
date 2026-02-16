<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\NotificationService;
use App\Services\RateLimiter;
use App\Services\Logger;
use PDO;

/**
 * F29 - WaitlistController
 * Gestion de la liste d'attente pour les restaurants
 */
class WaitlistController extends Controller
{
    /**
     * Temps d'attente estime par position (minutes)
     */
    private const WAIT_PER_POSITION = 15;

    /**
     * API - Rejoindre la liste d'attente
     * POST /api/restaurants/{id}/waitlist
     */
    public function join(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $restaurantId = (int)$request->param('id');

        $input = json_decode(file_get_contents('php://input'), true);
        $guestName = trim($input['guest_name'] ?? '');
        $guestPhone = trim($input['guest_phone'] ?? '');
        $partySize = max(1, (int)($input['party_size'] ?? 1));
        $notes = trim($input['notes'] ?? '');

        // Determine user or guest
        $userId = null;
        if ($this->isAuthenticated()) {
            $userId = (int)$_SESSION['user']['id'];
        }

        // Rate limit based on IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::attempt("waitlist_join_{$ip}", 10, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de requetes. Reessayez plus tard.']);
            return;
        }

        // Validate guest info if not authenticated
        if (!$userId) {
            if (mb_strlen($guestName) < 2 || mb_strlen($guestName) > 100) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Nom requis (2-100 caracteres)']);
                return;
            }
            if (!preg_match('/^[0-9+\s\-]{8,20}$/', $guestPhone)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Numero de telephone invalide']);
                return;
            }
        }

        if ($partySize < 1 || $partySize > 50) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nombre de personnes invalide (1-50)']);
            return;
        }

        // Check restaurant exists and waitlist is enabled
        $restStmt = $this->db->prepare("
            SELECT r.id, r.nom, r.owner_id, r.waitlist_enabled
            FROM restaurants r
            WHERE r.id = :rid AND r.status = 'validated'
        ");
        $restStmt->execute([':rid' => $restaurantId]);
        $restaurant = $restStmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouve']);
            return;
        }

        if (!(int)$restaurant['waitlist_enabled']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La liste d\'attente n\'est pas activee pour ce restaurant']);
            return;
        }

        // Check user not already on active waitlist for this restaurant
        if ($userId) {
            $existStmt = $this->db->prepare("
                SELECT id FROM waitlist_entries
                WHERE restaurant_id = :rid AND user_id = :uid AND status IN ('waiting', 'notified')
            ");
            $existStmt->execute([':rid' => $restaurantId, ':uid' => $userId]);
            if ($existStmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Vous etes deja sur la liste d\'attente']);
                return;
            }
        }

        try {
            $this->db->beginTransaction();

            // Get next position
            $posStmt = $this->db->prepare("
                SELECT COALESCE(MAX(position), 0) + 1 AS next_pos
                FROM waitlist_entries
                WHERE restaurant_id = :rid AND status IN ('waiting', 'notified')
            ");
            $posStmt->execute([':rid' => $restaurantId]);
            $nextPosition = (int)$posStmt->fetchColumn();

            $estimatedWait = $nextPosition * self::WAIT_PER_POSITION;

            // Insert entry
            $insertStmt = $this->db->prepare("
                INSERT INTO waitlist_entries (restaurant_id, user_id, guest_name, guest_phone, party_size,
                                             position, estimated_wait_minutes, notes, status, created_at)
                VALUES (:rid, :uid, :gname, :gphone, :psize, :pos, :wait, :notes, 'waiting', NOW())
            ");
            $insertStmt->execute([
                ':rid' => $restaurantId,
                ':uid' => $userId,
                ':gname' => $userId ? null : $guestName,
                ':gphone' => $userId ? null : $guestPhone,
                ':psize' => $partySize,
                ':pos' => $nextPosition,
                ':wait' => $estimatedWait,
                ':notes' => mb_substr($notes, 0, 500) ?: null,
            ]);

            $entryId = (int)$this->db->lastInsertId();

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Vous avez rejoint la liste d\'attente',
                'entry_id' => $entryId,
                'position' => $nextPosition,
                'estimated_wait_minutes' => $estimatedWait,
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('WaitlistController::join error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'ajout a la liste d\'attente']);
        }
    }

    /**
     * API - Verifier la position sur la liste d'attente
     * GET /api/waitlist/{id}/status
     */
    public function status(Request $request): void
    {
        header('Content-Type: application/json');

        $entryId = (int)$request->param('id');

        $stmt = $this->db->prepare("
            SELECT we.id, we.restaurant_id, we.user_id, we.guest_name, we.party_size,
                   we.position, we.estimated_wait_minutes, we.status, we.created_at,
                   r.nom AS restaurant_nom
            FROM waitlist_entries we
            JOIN restaurants r ON r.id = we.restaurant_id
            WHERE we.id = :id
        ");
        $stmt->execute([':id' => $entryId]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Entree non trouvee']);
            return;
        }

        // Recalculate current position (count how many people are ahead)
        $currentPosition = 0;
        if ($entry['status'] === 'waiting') {
            $posStmt = $this->db->prepare("
                SELECT COUNT(*) FROM waitlist_entries
                WHERE restaurant_id = :rid AND status = 'waiting' AND position < :pos
            ");
            $posStmt->execute([':rid' => $entry['restaurant_id'], ':pos' => $entry['position']]);
            $currentPosition = (int)$posStmt->fetchColumn() + 1;
        }

        $estimatedWait = $currentPosition * self::WAIT_PER_POSITION;

        echo json_encode([
            'success' => true,
            'entry' => [
                'id' => (int)$entry['id'],
                'restaurant_nom' => $entry['restaurant_nom'],
                'party_size' => (int)$entry['party_size'],
                'position' => $currentPosition,
                'estimated_wait_minutes' => $estimatedWait,
                'status' => $entry['status'],
                'created_at' => $entry['created_at'],
            ],
        ]);
    }

    /**
     * API - Proprietaire notifie que la table est prete
     * POST /api/waitlist/{id}/notify
     */
    public function notify(Request $request): void
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
        $entryId = (int)$request->param('id');

        // Fetch entry + ownership check
        $stmt = $this->db->prepare("
            SELECT we.id, we.user_id, we.guest_name, we.status, we.restaurant_id,
                   r.nom AS restaurant_nom, r.owner_id
            FROM waitlist_entries we
            JOIN restaurants r ON r.id = we.restaurant_id
            WHERE we.id = :id
        ");
        $stmt->execute([':id' => $entryId]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Entree non trouvee']);
            return;
        }

        if ((int)$entry['owner_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acces refuse']);
            return;
        }

        if ($entry['status'] !== 'waiting') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cette entree n\'est plus en attente (statut: ' . $entry['status'] . ')']);
            return;
        }

        try {
            $updStmt = $this->db->prepare("
                UPDATE waitlist_entries SET status = 'notified', notified_at = NOW(), updated_at = NOW()
                WHERE id = :id
            ");
            $updStmt->execute([':id' => $entryId]);

            // Notify user if registered
            if ($entry['user_id']) {
                $notifService = new NotificationService($this->db);
                $notifService->create(
                    (int)$entry['user_id'],
                    'waitlist_ready',
                    'Votre table est prete !',
                    'Votre table au restaurant ' . $entry['restaurant_nom'] . ' est prete. Presentez-vous a l\'accueil.',
                    ['link' => '/restaurant/' . $entry['restaurant_id']]
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Client notifie',
            ]);
        } catch (\Exception $e) {
            Logger::error('WaitlistController::notify error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la notification']);
        }
    }

    /**
     * API - Proprietaire confirme que le client est installe
     * POST /api/waitlist/{id}/seat
     */
    public function seat(Request $request): void
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
        $entryId = (int)$request->param('id');

        // Fetch entry + ownership check
        $stmt = $this->db->prepare("
            SELECT we.id, we.status, r.owner_id
            FROM waitlist_entries we
            JOIN restaurants r ON r.id = we.restaurant_id
            WHERE we.id = :id
        ");
        $stmt->execute([':id' => $entryId]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Entree non trouvee']);
            return;
        }

        if ((int)$entry['owner_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acces refuse']);
            return;
        }

        if (!in_array($entry['status'], ['waiting', 'notified'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Statut invalide pour cette action (statut: ' . $entry['status'] . ')']);
            return;
        }

        try {
            $updStmt = $this->db->prepare("
                UPDATE waitlist_entries SET status = 'seated', seated_at = NOW(), updated_at = NOW()
                WHERE id = :id
            ");
            $updStmt->execute([':id' => $entryId]);

            echo json_encode([
                'success' => true,
                'message' => 'Client marque comme installe',
            ]);
        } catch (\Exception $e) {
            Logger::error('WaitlistController::seat error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise a jour']);
        }
    }

    /**
     * API - Utilisateur ou proprietaire annule une entree
     * POST /api/waitlist/{id}/cancel
     */
    public function cancel(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $entryId = (int)$request->param('id');

        // Fetch entry
        $stmt = $this->db->prepare("
            SELECT we.id, we.user_id, we.status, we.restaurant_id, r.owner_id
            FROM waitlist_entries we
            JOIN restaurants r ON r.id = we.restaurant_id
            WHERE we.id = :id
        ");
        $stmt->execute([':id' => $entryId]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$entry) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Entree non trouvee']);
            return;
        }

        // Only the user themselves or the restaurant owner can cancel
        $currentUserId = $this->isAuthenticated() ? (int)$_SESSION['user']['id'] : null;
        $isOwner = $currentUserId && (int)$entry['owner_id'] === $currentUserId;
        $isUser = $currentUserId && $entry['user_id'] && (int)$entry['user_id'] === $currentUserId;

        if (!$isOwner && !$isUser) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acces refuse']);
            return;
        }

        if (!in_array($entry['status'], ['waiting', 'notified'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cette entree ne peut plus etre annulee (statut: ' . $entry['status'] . ')']);
            return;
        }

        try {
            $updStmt = $this->db->prepare("
                UPDATE waitlist_entries SET status = 'cancelled', updated_at = NOW()
                WHERE id = :id
            ");
            $updStmt->execute([':id' => $entryId]);

            echo json_encode([
                'success' => true,
                'message' => 'Entree annulee',
            ]);
        } catch (\Exception $e) {
            Logger::error('WaitlistController::cancel error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'annulation']);
        }
    }

    /**
     * API - Proprietaire recupere la liste d'attente active de son restaurant
     * GET /api/owner/restaurant/{id}/waitlist
     */
    public function ownerList(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $restaurantId = (int)$request->param('id');

        // Ownership check
        $ownerStmt = $this->db->prepare("SELECT id FROM restaurants WHERE id = :rid AND owner_id = :uid");
        $ownerStmt->execute([':rid' => $restaurantId, ':uid' => $userId]);
        if (!$ownerStmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Acces refuse']);
            return;
        }

        // Fetch active waitlist entries
        $stmt = $this->db->prepare("
            SELECT we.id, we.user_id, we.guest_name, we.guest_phone, we.party_size,
                   we.position, we.estimated_wait_minutes, we.notes, we.status,
                   we.created_at, we.notified_at, we.seated_at,
                   u.name AS user_name, u.email AS user_email
            FROM waitlist_entries we
            LEFT JOIN users u ON u.id = we.user_id
            WHERE we.restaurant_id = :rid AND we.status IN ('waiting', 'notified')
            ORDER BY we.position ASC
        ");
        $stmt->execute([':rid' => $restaurantId]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Also get today's completed/cancelled count for context
        $statsStmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN status = 'seated' THEN 1 ELSE 0 END) AS seated_today,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_today
            FROM waitlist_entries
            WHERE restaurant_id = :rid AND DATE(created_at) = CURDATE()
        ");
        $statsStmt->execute([':rid' => $restaurantId]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'entries' => $entries,
            'stats' => [
                'active_count' => count($entries),
                'seated_today' => (int)($stats['seated_today'] ?? 0),
                'cancelled_today' => (int)($stats['cancelled_today'] ?? 0),
            ],
        ]);
    }
}
