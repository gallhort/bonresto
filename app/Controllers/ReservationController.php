<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\NotificationService;
use App\Services\ActivityFeedService;
use App\Services\RateLimiter;
use PDO;

/**
 * Controller pour les réservations en ligne
 * Uniquement disponible pour les restaurants dont le propriétaire a claimé
 */
class ReservationController extends Controller
{
    /**
     * API - Créer une demande de réservation
     * POST /api/restaurants/{id}/reservation
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

        // Rate limit: 5 réservations par heure
        if (!RateLimiter::attempt("reservation_$userId", 5, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de demandes. Reessayez plus tard.']);
            return;
        }

        // Vérifier que le restaurant existe, est claimé et a les réservations activées
        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.owner_id, r.reservations_enabled
            FROM restaurants r
            WHERE r.id = :rid AND r.status = 'validated' AND r.owner_id IS NOT NULL
        ");
        $stmt->execute([':rid' => $restaurantId]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant introuvable ou reservations non disponibles']);
            return;
        }

        if (!(int)$restaurant['reservations_enabled']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Ce restaurant n\'accepte pas les reservations en ligne']);
            return;
        }

        // On ne peut pas réserver son propre restaurant
        if ((int)$restaurant['owner_id'] === $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas reserver votre propre restaurant']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $date = $input['date'] ?? '';
        $heure = $input['heure'] ?? '';
        $nbPersonnes = (int)($input['nb_personnes'] ?? 2);
        $telephone = trim($input['telephone'] ?? '');
        $message = trim($input['message'] ?? '');

        // Validations
        $errors = [];
        if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $errors[] = 'Date invalide';
        } elseif ($date < date('Y-m-d')) {
            $errors[] = 'La date doit etre dans le futur';
        }
        if (!$heure || !preg_match('/^\d{2}:\d{2}$/', $heure)) {
            $errors[] = 'Heure invalide';
        }
        if ($nbPersonnes < 1 || $nbPersonnes > 20) {
            $errors[] = 'Nombre de personnes invalide (1-20)';
        }
        if ($telephone && !preg_match('/^[\d\s\+\-\.]{8,20}$/', $telephone)) {
            $errors[] = 'Numero de telephone invalide';
        }
        if (strlen($message) > 500) {
            $errors[] = 'Message trop long (500 caracteres max)';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Vérifier qu'il n'y a pas déjà une réservation pending pour ce créneau
        $existStmt = $this->db->prepare("
            SELECT id FROM reservations
            WHERE user_id = :uid AND restaurant_id = :rid
            AND date_souhaitee = :date AND status = 'pending'
        ");
        $existStmt->execute([':uid' => $userId, ':rid' => $restaurantId, ':date' => $date]);
        if ($existStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Vous avez deja une demande en attente pour ce restaurant a cette date.']);
            return;
        }

        $insertStmt = $this->db->prepare("
            INSERT INTO reservations (user_id, restaurant_id, date_souhaitee, heure, nb_personnes, telephone, message)
            VALUES (:uid, :rid, :date, :heure, :nb, :tel, :msg)
        ");
        $insertStmt->execute([
            ':uid' => $userId,
            ':rid' => $restaurantId,
            ':date' => $date,
            ':heure' => $heure,
            ':nb' => $nbPersonnes,
            ':tel' => $telephone ?: null,
            ':msg' => $message ?: null,
        ]);

        // Notifier le propriétaire
        $notifService = new NotificationService($this->db);
        $notifService->create(
            (int)$restaurant['owner_id'],
            'reservation_request',
            'Nouvelle demande de reservation',
            "Demande pour le $date a $heure ({$nbPersonnes} pers.)",
            ['restaurant_id' => $restaurantId, 'reservation_id' => (int)$this->db->lastInsertId()]
        );

        // Log activité
        $feedService = new ActivityFeedService($this->db);
        $feedService->log($userId, 'reservation', 'restaurant', $restaurantId, [
            'restaurant_name' => $restaurant['nom'],
            'date' => $date,
            'nb_personnes' => $nbPersonnes,
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Demande de reservation envoyee ! Le proprietaire vous repondra rapidement.',
        ]);
    }

    /**
     * API - Propriétaire accepte/refuse une réservation
     * POST /api/reservations/{id}/respond
     */
    public function respond(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $reservationId = (int)$request->param('id');

        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $ownerNote = trim($input['note'] ?? '');

        if (!in_array($action, ['accept', 'refuse'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
            return;
        }

        // Vérifier que la réservation existe et que l'utilisateur est le propriétaire
        $stmt = $this->db->prepare("
            SELECT res.*, r.nom as restaurant_nom, r.owner_id
            FROM reservations res
            INNER JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.id = :rid AND res.status = 'pending'
        ");
        $stmt->execute([':rid' => $reservationId]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation || (int)$reservation['owner_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorise']);
            return;
        }

        $newStatus = $action === 'accept' ? 'accepted' : 'refused';

        $updateStmt = $this->db->prepare("
            UPDATE reservations SET status = :status, owner_note = :note, updated_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            ':status' => $newStatus,
            ':note' => $ownerNote ?: null,
            ':id' => $reservationId,
        ]);

        // Notifier le client
        $notifService = new NotificationService($this->db);
        $title = $action === 'accept'
            ? 'Reservation confirmee !'
            : 'Reservation refusee';
        $message = $action === 'accept'
            ? "Votre reservation chez {$reservation['restaurant_nom']} le {$reservation['date_souhaitee']} a {$reservation['heure']} est confirmee."
            : "Votre reservation chez {$reservation['restaurant_nom']} n'a pas pu etre acceptee.";

        if ($ownerNote) {
            $message .= " Note du proprietaire : $ownerNote";
        }

        $notifService->create(
            (int)$reservation['user_id'],
            'reservation_response',
            $title,
            $message,
            ['restaurant_id' => (int)$reservation['restaurant_id'], 'status' => $newStatus]
        );

        echo json_encode([
            'success' => true,
            'message' => $action === 'accept' ? 'Reservation acceptee' : 'Reservation refusee',
            'new_status' => $newStatus,
        ]);
    }

    /**
     * API - Mes réservations (utilisateur)
     * GET /api/my-reservations
     */
    public function myReservations(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT res.*, r.nom as restaurant_nom, r.slug as restaurant_slug, r.ville,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as restaurant_photo
            FROM reservations res
            INNER JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.user_id = :uid
            ORDER BY res.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([':uid' => $userId]);

        echo json_encode([
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    /**
     * API - Réservations reçues (propriétaire)
     * GET /api/owner/reservations
     */
    public function ownerReservations(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $status = $_GET['status'] ?? 'pending';

        $stmt = $this->db->prepare("
            SELECT res.*, r.nom as restaurant_nom, r.slug as restaurant_slug,
                   u.prenom as client_prenom, u.nom as client_nom, u.email as client_email
            FROM reservations res
            INNER JOIN restaurants r ON r.id = res.restaurant_id AND r.owner_id = :uid
            INNER JOIN users u ON u.id = res.user_id
            WHERE res.status = :status
            ORDER BY res.date_souhaitee ASC, res.heure ASC
            LIMIT 50
        ");
        $stmt->execute([':uid' => $userId, ':status' => $status]);

        echo json_encode([
            'success' => true,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    /**
     * API - Annuler une réservation (utilisateur)
     * POST /api/reservations/{id}/cancel
     */
    public function cancel(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $reservationId = (int)$request->param('id');

        $stmt = $this->db->prepare("
            UPDATE reservations SET status = 'cancelled', updated_at = NOW()
            WHERE id = :id AND user_id = :uid AND status = 'pending'
        ");
        $stmt->execute([':id' => $reservationId, ':uid' => $userId]);

        echo json_encode([
            'success' => true,
            'cancelled' => $stmt->rowCount() > 0,
            'message' => 'Reservation annulee',
        ]);
    }
}
