<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\NotificationService;
use App\Services\RateLimiter;
use App\Services\Logger;
use PDO;

/**
 * F34 - EventController
 * Gestion des evenements restaurants (soirees, concerts, degustations, etc.)
 */
class EventController extends Controller
{
    /**
     * Page publique listant les evenements a venir
     * GET /evenements
     */
    public function index(Request $request): void
    {
        $ville = trim($request->query('ville', ''));
        $page = max(1, (int)$request->query('page', 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE e.event_date >= CURDATE() AND e.status = 'upcoming' AND r.status = 'validated'";
        $params = [];

        if ($ville !== '') {
            $where .= " AND r.ville = :ville";
            $params[':ville'] = $ville;
        }

        // Count total for pagination
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM restaurant_events e
            JOIN restaurants r ON r.id = e.restaurant_id
            $where
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Fetch events
        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare("
            SELECT e.id, e.title, e.description, e.event_date, e.start_time, e.end_time,
                   e.event_type, e.max_participants, e.current_participants, e.price, e.photo_path,
                   e.status, e.created_at,
                   r.id AS restaurant_id, r.nom AS restaurant_nom, r.slug AS restaurant_slug,
                   r.ville, r.adresse
            FROM restaurant_events e
            JOIN restaurants r ON r.id = e.restaurant_id
            $where
            ORDER BY e.event_date ASC, e.start_time ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            if ($key === ':limit' || $key === ':offset') continue;
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get distinct villes for filter
        $villesStmt = $this->db->query("
            SELECT DISTINCT r.ville
            FROM restaurant_events e
            JOIN restaurants r ON r.id = e.restaurant_id
            WHERE e.event_date >= CURDATE() AND e.status = 'upcoming' AND r.status = 'validated'
            ORDER BY r.ville
        ");
        $villes = $villesStmt->fetchAll(PDO::FETCH_COLUMN);

        $totalPages = max(1, ceil($total / $perPage));

        $this->render('events.index', [
            'title' => 'Evenements',
            'events' => $events,
            'villes' => $villes,
            'selectedVille' => $ville,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ]);
    }

    /**
     * Page detail d'un evenement
     * GET /evenement/{id}
     */
    public function show(Request $request): void
    {
        $eventId = (int)$request->param('id');

        $stmt = $this->db->prepare("
            SELECT e.*, r.nom AS restaurant_nom, r.slug AS restaurant_slug,
                   r.ville, r.adresse, r.phone AS restaurant_phone, r.owner_id
            FROM restaurant_events e
            JOIN restaurants r ON r.id = e.restaurant_id
            WHERE e.id = :id AND r.status = 'validated'
        ");
        $stmt->execute([':id' => $eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            $this->notFound('Evenement non trouve');
            return;
        }

        // Check if current user is registered
        $isRegistered = false;
        if ($this->isAuthenticated()) {
            $regStmt = $this->db->prepare("
                SELECT id FROM event_registrations
                WHERE event_id = :eid AND user_id = :uid AND status = 'registered'
            ");
            $regStmt->execute([':eid' => $eventId, ':uid' => (int)$_SESSION['user']['id']]);
            $isRegistered = (bool)$regStmt->fetch();
        }

        // Check remaining spots
        $spotsLeft = null;
        if ($event['max_participants'] > 0) {
            $spotsLeft = max(0, (int)$event['max_participants'] - (int)$event['current_participants']);
        }

        $this->render('events.show', [
            'title' => htmlspecialchars($event['title']) . ' - Evenement',
            'event' => $event,
            'isRegistered' => $isRegistered,
            'spotsLeft' => $spotsLeft,
        ]);
    }

    /**
     * API - Proprietaire cree un evenement
     * POST /api/events
     */
    public function store(Request $request): void
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

        // Rate limit: 10 events per hour
        if (!RateLimiter::attempt("event_create_{$userId}", 10, 3600)) {
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
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $eventDate = trim($input['event_date'] ?? '');
        $startTime = trim($input['start_time'] ?? '');
        $endTime = trim($input['end_time'] ?? '');
        $eventType = trim($input['event_type'] ?? '');
        $maxParticipants = isset($input['max_participants']) ? (int)$input['max_participants'] : 0;
        $price = isset($input['price']) ? (float)$input['price'] : 0;
        $photo = trim($input['photo'] ?? '');

        // Ownership check
        $stmt = $this->db->prepare("SELECT id FROM restaurants WHERE id = :rid AND owner_id = :uid");
        $stmt->execute([':rid' => $restaurantId, ':uid' => $userId]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Vous n\'etes pas proprietaire de ce restaurant']);
            return;
        }

        // Validations
        $errors = [];

        if (mb_strlen($title) < 3 || mb_strlen($title) > 200) {
            $errors[] = 'Le titre doit contenir entre 3 et 200 caracteres';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            $errors[] = 'Date invalide';
        } elseif ($eventDate <= date('Y-m-d')) {
            $errors[] = 'La date doit etre dans le futur';
        }

        if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $startTime)) {
            $errors[] = 'Heure de debut invalide';
        }

        $validTypes = ['tasting', 'workshop', 'live_music', 'theme_night', 'brunch', 'promotion', 'other'];
        if (!in_array($eventType, $validTypes)) {
            $errors[] = 'Type d\'evenement invalide';
        }

        if ($endTime !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $endTime)) {
            $errors[] = 'Heure de fin invalide';
        }

        if ($maxParticipants < 0 || $maxParticipants > 10000) {
            $errors[] = 'Nombre de participants invalide';
        }

        if ($price < 0 || $price > 100000) {
            $errors[] = 'Prix invalide';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Sanitize description
        $description = mb_substr($description, 0, 5000);

        try {
            $insertStmt = $this->db->prepare("
                INSERT INTO restaurant_events (restaurant_id, title, description, event_date, start_time, end_time,
                                    event_type, max_participants, current_participants, price, photo_path, status, created_at)
                VALUES (:restaurant_id, :title, :description, :event_date, :start_time, :end_time,
                        :event_type, :max_participants, 0, :price, :photo_path, 'upcoming', NOW())
            ");
            $insertStmt->execute([
                ':restaurant_id' => $restaurantId,
                ':title' => $title,
                ':description' => $description,
                ':event_date' => $eventDate,
                ':start_time' => $startTime,
                ':end_time' => $endTime ?: null,
                ':event_type' => $eventType,
                ':max_participants' => $maxParticipants,
                ':price' => $price,
                ':photo_path' => $photo ?: null,
            ]);

            $newId = (int)$this->db->lastInsertId();

            echo json_encode([
                'success' => true,
                'message' => 'Evenement cree avec succes',
                'event_id' => $newId,
            ]);
        } catch (\Exception $e) {
            Logger::error('EventController::store error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la creation de l\'evenement']);
        }
    }

    /**
     * API - Utilisateur s'inscrit a un evenement
     * POST /api/events/{id}/register
     */
    public function register(Request $request): void
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
        $eventId = (int)$request->param('id');

        // Rate limit: 20 registrations per hour
        if (!RateLimiter::attempt("event_register_{$userId}", 20, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de requetes. Reessayez plus tard.']);
            return;
        }

        // Fetch event
        $stmt = $this->db->prepare("
            SELECT e.id, e.title, e.event_date, e.max_participants, e.current_participants,
                   e.status, r.owner_id, r.nom AS restaurant_nom
            FROM restaurant_events e
            JOIN restaurants r ON r.id = e.restaurant_id
            WHERE e.id = :id AND e.status = 'upcoming'
        ");
        $stmt->execute([':id' => $eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Evenement non trouve']);
            return;
        }

        // Check event is not past
        if ($event['event_date'] < date('Y-m-d')) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cet evenement est deja passe']);
            return;
        }

        // Check max participants
        if ((int)$event['max_participants'] > 0 && (int)$event['current_participants'] >= (int)$event['max_participants']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cet evenement est complet']);
            return;
        }

        // Check not already registered
        $checkStmt = $this->db->prepare("
            SELECT id FROM event_registrations
            WHERE event_id = :eid AND user_id = :uid AND status = 'registered'
        ");
        $checkStmt->execute([':eid' => $eventId, ':uid' => $userId]);
        if ($checkStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vous etes deja inscrit a cet evenement']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Insert registration
            $regStmt = $this->db->prepare("
                INSERT INTO event_registrations (event_id, user_id, status, created_at)
                VALUES (:eid, :uid, 'registered', NOW())
            ");
            $regStmt->execute([':eid' => $eventId, ':uid' => $userId]);

            // Increment current_participants
            $updStmt = $this->db->prepare("
                UPDATE restaurant_events SET current_participants = current_participants + 1 WHERE id = :id
            ");
            $updStmt->execute([':id' => $eventId]);

            $this->db->commit();

            // Notify owner
            if ($event['owner_id']) {
                $notifService = new NotificationService($this->db);
                $userName = htmlspecialchars($_SESSION['user']['prenom'] ?? $_SESSION['user']['name'] ?? 'Un utilisateur');
                $notifService->create(
                    (int)$event['owner_id'],
                    'event_registration',
                    'Nouvelle inscription evenement',
                    $userName . ' s\'est inscrit a "' . $event['title'] . '"',
                    ['link' => '/evenement/' . $eventId]
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Inscription confirmee !',
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('EventController::register error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'inscription']);
        }
    }

    /**
     * API - Utilisateur annule son inscription
     * POST /api/events/{id}/cancel-registration
     */
    public function cancelRegistration(Request $request): void
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
        $eventId = (int)$request->param('id');

        // Check registration exists
        $checkStmt = $this->db->prepare("
            SELECT id FROM event_registrations
            WHERE event_id = :eid AND user_id = :uid AND status = 'registered'
        ");
        $checkStmt->execute([':eid' => $eventId, ':uid' => $userId]);
        $registration = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$registration) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Inscription non trouvee']);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Cancel registration
            $cancelStmt = $this->db->prepare("
                UPDATE event_registrations SET status = 'cancelled', updated_at = NOW()
                WHERE id = :id
            ");
            $cancelStmt->execute([':id' => $registration['id']]);

            // Decrement current_participants
            $updStmt = $this->db->prepare("
                UPDATE restaurant_events SET current_participants = GREATEST(0, current_participants - 1)
                WHERE id = :id
            ");
            $updStmt->execute([':id' => $eventId]);

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Inscription annulee',
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('EventController::cancelRegistration error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'annulation']);
        }
    }

    /**
     * API - Proprietaire recupere ses evenements
     * GET /api/owner/events
     */
    public function ownerEvents(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT e.id, e.title, e.description, e.event_date, e.start_time, e.end_time,
                   e.event_type, e.max_participants, e.current_participants, e.price, e.photo_path,
                   e.status, e.created_at,
                   r.id AS restaurant_id, r.nom AS restaurant_nom
            FROM restaurant_events e
            JOIN restaurants r ON r.id = e.restaurant_id
            WHERE r.owner_id = :uid
            ORDER BY e.event_date DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'events' => $events,
        ]);
    }
}
