<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\NotificationService;
use App\Services\RateLimiter;
use PDO;

/**
 * F10 + F11 - Extensions du systeme de reservations
 * F10: Rappels automatiques (24h et 2h avant)
 * F11: Gestion des no-shows et score de fiabilite
 */
class ReservationExtController extends Controller
{
    /**
     * GET /api/cron/reservation-reminders
     * Envoie des rappels pour les reservations acceptees a venir.
     * - Rappel 24h: reservations dont date_souhaitee est demain
     * - Rappel 2h: reservations dont date_souhaitee + heure est dans ~2h
     * Destiné à être appelé par un cron job (toutes les 15-30 minutes idéalement).
     */
    public function sendReminders(): void
    {
        header('Content-Type: application/json');

        $now = new \DateTime();
        $sent24h = 0;
        $sent2h = 0;

        // ──────────────────────────────────────────────
        // RAPPEL 24H: reservations demain, pas encore notifiees
        // ──────────────────────────────────────────────
        $tomorrow = (new \DateTime('+1 day'))->format('Y-m-d');

        $stmt24 = $this->db->prepare("
            SELECT res.id, res.user_id, res.restaurant_id, res.date_souhaitee, res.heure,
                   res.nb_personnes, r.nom AS restaurant_nom
            FROM reservations res
            INNER JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.status = 'accepted'
              AND res.date_souhaitee = :tomorrow
              AND res.reminder_24h_sent = 0
        ");
        $stmt24->execute([':tomorrow' => $tomorrow]);
        $reminders24 = $stmt24->fetchAll(PDO::FETCH_ASSOC);

        $notifService = new NotificationService($this->db);

        $updateStmt24 = $this->db->prepare("
            UPDATE reservations SET reminder_24h_sent = 1 WHERE id = :rid
        ");

        foreach ($reminders24 as $res) {
            $notifService->create(
                (int)$res['user_id'],
                'reservation_reminder',
                'Rappel : réservation demain',
                'Votre réservation chez ' . $res['restaurant_nom']
                    . ' est prévue demain (' . $res['date_souhaitee'] . ') à ' . $res['heure']
                    . ' pour ' . $res['nb_personnes'] . ' personne(s).',
                [
                    'restaurant_id'  => (int)$res['restaurant_id'],
                    'reservation_id' => (int)$res['id'],
                    'reminder_type'  => '24h',
                ]
            );

            $updateStmt24->execute([':rid' => (int)$res['id']]);
            $sent24h++;
        }

        // ──────────────────────────────────────────────
        // RAPPEL 2H: reservations aujourd'hui dans ~2h, pas encore notifiees
        // On prend les reservations entre maintenant+1h30 et maintenant+2h30
        // pour avoir une fenetre de tolerance
        // ──────────────────────────────────────────────
        $today = $now->format('Y-m-d');
        $windowStart = (clone $now)->modify('+90 minutes')->format('H:i');
        $windowEnd   = (clone $now)->modify('+150 minutes')->format('H:i');

        $stmt2 = $this->db->prepare("
            SELECT res.id, res.user_id, res.restaurant_id, res.date_souhaitee, res.heure,
                   res.nb_personnes, r.nom AS restaurant_nom
            FROM reservations res
            INNER JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.status = 'accepted'
              AND res.date_souhaitee = :today
              AND res.heure >= :window_start
              AND res.heure <= :window_end
              AND res.reminder_2h_sent = 0
        ");
        $stmt2->execute([
            ':today'        => $today,
            ':window_start' => $windowStart,
            ':window_end'   => $windowEnd,
        ]);
        $reminders2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $updateStmt2 = $this->db->prepare("
            UPDATE reservations SET reminder_2h_sent = 1 WHERE id = :rid
        ");

        foreach ($reminders2 as $res) {
            $notifService->create(
                (int)$res['user_id'],
                'reservation_reminder',
                'Rappel : réservation dans 2h',
                'Votre réservation chez ' . $res['restaurant_nom']
                    . ' est prévue aujourd\'hui à ' . $res['heure']
                    . ' pour ' . $res['nb_personnes'] . ' personne(s). À bientôt !',
                [
                    'restaurant_id'  => (int)$res['restaurant_id'],
                    'reservation_id' => (int)$res['id'],
                    'reminder_type'  => '2h',
                ]
            );

            $updateStmt2->execute([':rid' => (int)$res['id']]);
            $sent2h++;
        }

        echo json_encode([
            'success'       => true,
            'sent_24h'      => $sent24h,
            'sent_2h'       => $sent2h,
            'total_sent'    => $sent24h + $sent2h,
            'executed_at'   => $now->format('Y-m-d H:i:s'),
        ]);
        exit;
    }

    /**
     * POST /api/reservations/{id}/no-show
     * Le proprietaire marque une reservation comme no-show.
     * Met a jour la reservation + les stats no-show de l'utilisateur.
     */
    public function markNoShow(Request $request): void
    {
        header('Content-Type: application/json');

        // Auth check
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            exit;
        }

        // CSRF check
        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            exit;
        }

        $ownerId = (int)$_SESSION['user']['id'];
        $reservationId = (int)$request->param('id');

        if ($reservationId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID invalide']);
            exit;
        }

        // Rate limit: 20 no-show markings per hour per owner
        if (!RateLimiter::attempt("noshow_{$ownerId}", 20, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de requêtes. Réessayez plus tard.']);
            exit;
        }

        // Recuperer la reservation et verifier que l'owner est bien le proprietaire du restaurant
        $stmt = $this->db->prepare("
            SELECT res.id, res.user_id, res.restaurant_id, res.date_souhaitee, res.heure,
                   res.status, res.no_show,
                   r.nom AS restaurant_nom, r.owner_id
            FROM reservations res
            INNER JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.id = :rid
        ");
        $stmt->execute([':rid' => $reservationId]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Réservation non trouvée']);
            exit;
        }

        // Verifier la propriete
        if ((int)$reservation['owner_id'] !== $ownerId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé']);
            exit;
        }

        // La reservation doit etre acceptee pour pouvoir etre marquee no-show
        if ($reservation['status'] !== 'accepted') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Seules les réservations acceptées peuvent être marquées comme no-show']);
            exit;
        }

        // Deja marquee comme no-show
        if ((int)$reservation['no_show'] === 1) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cette réservation est déjà marquée comme no-show']);
            exit;
        }

        $clientUserId = (int)$reservation['user_id'];

        // Transaction: update reservation + update user stats + notify
        $this->db->beginTransaction();
        try {
            // 1. Marquer la reservation comme no-show
            $updateRes = $this->db->prepare("
                UPDATE reservations
                SET no_show = 1, no_show_at = NOW()
                WHERE id = :rid
            ");
            $updateRes->execute([':rid' => $reservationId]);

            // 2. Upsert dans user_no_show_stats
            // D'abord, compter le total de reservations acceptees de l'utilisateur
            $totalResStmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservations
                WHERE user_id = :uid AND status = 'accepted'
            ");
            $totalResStmt->execute([':uid' => $clientUserId]);
            $totalReservations = (int)$totalResStmt->fetchColumn();

            // Compter les no-shows
            $totalNoShowsStmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservations
                WHERE user_id = :uid AND no_show = 1
            ");
            $totalNoShowsStmt->execute([':uid' => $clientUserId]);
            $totalNoShows = (int)$totalNoShowsStmt->fetchColumn();

            // Calculer le score de fiabilite: 1 - (no_shows / total_reservations)
            $reliabilityScore = $totalReservations > 0
                ? round(1 - ($totalNoShows / $totalReservations), 2)
                : 1.00;

            // Clamper entre 0 et 1
            $reliabilityScore = max(0.00, min(1.00, $reliabilityScore));

            // Upsert dans user_no_show_stats
            $upsertStmt = $this->db->prepare("
                INSERT INTO user_no_show_stats (user_id, total_no_shows, total_reservations, last_no_show_at, reliability_score)
                VALUES (:uid, :no_shows, :total_res, NOW(), :score)
                ON DUPLICATE KEY UPDATE
                    total_no_shows = :no_shows2,
                    total_reservations = :total_res2,
                    last_no_show_at = NOW(),
                    reliability_score = :score2
            ");
            $upsertStmt->execute([
                ':uid'        => $clientUserId,
                ':no_shows'   => $totalNoShows,
                ':total_res'  => $totalReservations,
                ':score'      => $reliabilityScore,
                ':no_shows2'  => $totalNoShows,
                ':total_res2' => $totalReservations,
                ':score2'     => $reliabilityScore,
            ]);

            // 3. Notifier l'utilisateur
            $notifService = new NotificationService($this->db);
            $notifService->create(
                $clientUserId,
                'reservation_noshow',
                'Absence constatée',
                'Vous avez été marqué(e) absent(e) pour votre réservation du '
                    . $reservation['date_souhaitee'] . ' à ' . $reservation['heure']
                    . ' chez ' . $reservation['restaurant_nom'] . '. '
                    . 'Les absences répétées peuvent affecter votre score de fiabilité.',
                [
                    'restaurant_id'  => (int)$reservation['restaurant_id'],
                    'reservation_id' => $reservationId,
                ]
            );

            $this->db->commit();

            echo json_encode([
                'success'           => true,
                'message'           => 'Réservation marquée comme no-show',
                'user_reliability'  => [
                    'total_no_shows'      => $totalNoShows,
                    'total_reservations'  => $totalReservations,
                    'reliability_score'   => $reliabilityScore,
                ],
            ]);
            exit;

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
            exit;
        }
    }

    /**
     * GET /api/users/{id}/reliability
     * Retourne le score de fiabilite et les stats no-show d'un utilisateur.
     * Accessible aux owners pour evaluer un client avant d'accepter une reservation.
     */
    public function getUserReliability(Request $request): void
    {
        header('Content-Type: application/json');

        // Auth check - seuls les utilisateurs connectes (owners) peuvent consulter
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            exit;
        }

        $targetUserId = (int)$request->param('id');

        if ($targetUserId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID utilisateur invalide']);
            exit;
        }

        // Verifier que le demandeur est bien un owner (possede au moins un restaurant)
        $currentUserId = (int)$_SESSION['user']['id'];
        $ownerCheck = $this->db->prepare("
            SELECT COUNT(*) FROM restaurants
            WHERE owner_id = :uid AND status = 'validated'
        ");
        $ownerCheck->execute([':uid' => $currentUserId]);
        $isOwner = (int)$ownerCheck->fetchColumn() > 0;

        // Autoriser aussi les admins
        if (!$isOwner && !$this->isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès réservé aux propriétaires de restaurants']);
            exit;
        }

        // Verifier que l'utilisateur cible existe
        $userStmt = $this->db->prepare("
            SELECT id, prenom, nom FROM users WHERE id = :uid
        ");
        $userStmt->execute([':uid' => $targetUserId]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
            exit;
        }

        // Recuperer les stats no-show
        $statsStmt = $this->db->prepare("
            SELECT total_no_shows, total_reservations, last_no_show_at, reliability_score
            FROM user_no_show_stats
            WHERE user_id = :uid
        ");
        $statsStmt->execute([':uid' => $targetUserId]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        if (!$stats) {
            // Pas de stats = pas de no-show = score parfait
            // Compter les reservations acceptees de l'utilisateur quand meme
            $countStmt = $this->db->prepare("
                SELECT COUNT(*) FROM reservations
                WHERE user_id = :uid AND status = 'accepted'
            ");
            $countStmt->execute([':uid' => $targetUserId]);
            $totalReservations = (int)$countStmt->fetchColumn();

            $stats = [
                'total_no_shows'     => 0,
                'total_reservations' => $totalReservations,
                'last_no_show_at'    => null,
                'reliability_score'  => '1.00',
            ];
        }

        // Determiner le label de fiabilite
        $score = (float)$stats['reliability_score'];
        if ($score >= 0.95) {
            $label = 'Excellent';
            $color = 'green';
        } elseif ($score >= 0.80) {
            $label = 'Bon';
            $color = 'blue';
        } elseif ($score >= 0.60) {
            $label = 'Moyen';
            $color = 'orange';
        } else {
            $label = 'Faible';
            $color = 'red';
        }

        echo json_encode([
            'success' => true,
            'user'    => [
                'id'     => (int)$user['id'],
                'prenom' => $user['prenom'],
                'nom'    => $user['nom'],
            ],
            'reliability' => [
                'score'              => (float)$stats['reliability_score'],
                'label'              => $label,
                'color'              => $color,
                'total_no_shows'     => (int)$stats['total_no_shows'],
                'total_reservations' => (int)$stats['total_reservations'],
                'last_no_show_at'    => $stats['last_no_show_at'],
            ],
        ]);
        exit;
    }
}
