<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use PDO;

/**
 * Controller pour les offres/promotions de restaurants (style TheFork -30%)
 * Gestion des offres par les proprietaires et consultation par les utilisateurs
 */
class OfferController extends Controller
{
    /**
     * Verifier que l'utilisateur est proprietaire du restaurant
     */
    private function getOwnedRestaurant(int $restaurantId): ?array
    {
        if (!$this->isAuthenticated()) return null;

        $userId = (int)$_SESSION['user']['id'];
        $stmt = $this->db->prepare("
            SELECT * FROM restaurants WHERE id = :rid AND owner_id = :uid
        ");
        $stmt->execute([':rid' => $restaurantId, ':uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * API - Liste des offres actives pour un restaurant
     * GET /api/restaurant/{id}/offers
     */
    public function apiList(Request $request): void
    {
        header('Content-Type: application/json');

        $restaurantId = (int)$request->param('id');

        if ($restaurantId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Restaurant invalide']);
            return;
        }

        // Verifier que le restaurant existe
        $checkStmt = $this->db->prepare("SELECT id FROM restaurants WHERE id = :rid");
        $checkStmt->execute([':rid' => $restaurantId]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant introuvable']);
            return;
        }

        $now = date('Y-m-d');
        $currentDay = (int)date('w'); // 0=dimanche, 6=samedi
        $currentTime = date('H:i:s');

        $stmt = $this->db->prepare("
            SELECT id, restaurant_id, title, description, discount_percent, offer_type,
                   valid_from, valid_to, days_of_week, time_start, time_end,
                   conditions, max_uses, current_uses, created_at
            FROM restaurant_offers
            WHERE restaurant_id = :rid
              AND status = 'active'
              AND valid_from <= :now1
              AND valid_to >= :now2
            ORDER BY discount_percent DESC, created_at DESC
        ");
        $stmt->execute([
            ':rid' => $restaurantId,
            ':now1' => $now,
            ':now2' => $now,
        ]);
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filtrer par jour de la semaine et horaires
        $filtered = [];
        foreach ($offers as $offer) {
            // Verifier max_uses
            if ($offer['max_uses'] > 0 && $offer['current_uses'] >= $offer['max_uses']) {
                continue;
            }

            // Verifier jour de la semaine
            if (!empty($offer['days_of_week'])) {
                $days = json_decode($offer['days_of_week'], true);
                if (is_array($days) && !empty($days) && !in_array($currentDay, $days)) {
                    continue;
                }
            }

            // Verifier creneaux horaires
            if (!empty($offer['time_start']) && !empty($offer['time_end'])) {
                if ($currentTime < $offer['time_start'] || $currentTime > $offer['time_end']) {
                    continue;
                }
            }

            $filtered[] = $offer;
        }

        echo json_encode(['success' => true, 'offers' => $filtered]);
    }

    /**
     * API - Creer une offre (proprietaire)
     * POST /api/owner/restaurant/{id}/offer
     */
    public function apiStore(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        // Rate limit: 10 offres par heure
        if (!RateLimiter::attempt("offer_create_$userId", 10, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Limite atteinte (10 offres/heure). Reessayez plus tard.']);
            return;
        }

        $restaurantId = (int)$request->param('id');
        $restaurant = $this->getOwnedRestaurant($restaurantId);

        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouve ou vous n\'etes pas le proprietaire']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $discountPercent = (int)($input['discount_percent'] ?? 0);
        $offerType = trim($input['offer_type'] ?? 'discount');
        $validFrom = trim($input['valid_from'] ?? '');
        $validTo = trim($input['valid_to'] ?? '');
        $daysOfWeek = $input['days_of_week'] ?? null;
        $timeStart = trim($input['time_start'] ?? '');
        $timeEnd = trim($input['time_end'] ?? '');
        $conditions = trim($input['conditions'] ?? '');
        $maxUses = (int)($input['max_uses'] ?? 0);

        // Validations
        $errors = [];

        if (empty($title) || mb_strlen($title) < 3 || mb_strlen($title) > 200) {
            $errors[] = 'Le titre doit faire entre 3 et 200 caracteres';
        }

        if (mb_strlen($description) > 1000) {
            $errors[] = 'La description ne doit pas depasser 1000 caracteres';
        }

        $validOfferTypes = ['discount', 'happy_hour', 'special_menu', 'free_item'];
        if (!in_array($offerType, $validOfferTypes)) {
            $errors[] = 'Type d\'offre invalide';
        }

        if ($offerType === 'discount' && ($discountPercent < 1 || $discountPercent > 100)) {
            $errors[] = 'La reduction doit etre entre 1% et 100%';
        }

        if (empty($validFrom) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $validFrom)) {
            $errors[] = 'Date de debut invalide';
        }

        if (empty($validTo) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $validTo)) {
            $errors[] = 'Date de fin invalide';
        }

        if (!empty($validFrom) && !empty($validTo) && $validTo < $validFrom) {
            $errors[] = 'La date de fin doit etre apres la date de debut';
        }

        if (!empty($validTo) && $validTo < date('Y-m-d')) {
            $errors[] = 'La date de fin doit etre dans le futur';
        }

        // Valider days_of_week si present
        if ($daysOfWeek !== null) {
            if (!is_array($daysOfWeek)) {
                $errors[] = 'days_of_week doit etre un tableau';
            } else {
                foreach ($daysOfWeek as $day) {
                    if (!is_int($day) || $day < 0 || $day > 6) {
                        $errors[] = 'Jours de la semaine invalides (0=dimanche a 6=samedi)';
                        break;
                    }
                }
            }
        }

        // Valider time_start / time_end
        if (!empty($timeStart) && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeStart)) {
            $errors[] = 'Heure de debut invalide (format HH:MM)';
        }
        if (!empty($timeEnd) && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeEnd)) {
            $errors[] = 'Heure de fin invalide (format HH:MM)';
        }
        if (!empty($timeStart) && !empty($timeEnd) && $timeEnd <= $timeStart) {
            $errors[] = 'L\'heure de fin doit etre apres l\'heure de debut';
        }

        if (mb_strlen($conditions) > 500) {
            $errors[] = 'Les conditions ne doivent pas depasser 500 caracteres';
        }

        if ($maxUses < 0) {
            $errors[] = 'Le nombre maximum d\'utilisations doit etre positif (0 = illimite)';
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }

        // Encoder days_of_week en JSON
        $daysJson = ($daysOfWeek !== null && is_array($daysOfWeek))
            ? json_encode($daysOfWeek)
            : null;

        $stmt = $this->db->prepare("
            INSERT INTO restaurant_offers
                (restaurant_id, title, description, discount_percent, offer_type,
                 valid_from, valid_to, days_of_week, time_start, time_end,
                 conditions, max_uses, current_uses, status, created_at)
            VALUES
                (:rid, :title, :description, :discount, :offer_type,
                 :valid_from, :valid_to, :days, :time_start, :time_end,
                 :conditions, :max_uses, 0, 'active', NOW())
        ");
        $stmt->execute([
            ':rid' => $restaurantId,
            ':title' => $title,
            ':description' => $description ?: null,
            ':discount' => $discountPercent,
            ':offer_type' => $offerType,
            ':valid_from' => $validFrom,
            ':valid_to' => $validTo,
            ':days' => $daysJson,
            ':time_start' => $timeStart ?: null,
            ':time_end' => $timeEnd ?: null,
            ':conditions' => $conditions ?: null,
            ':max_uses' => $maxUses,
        ]);

        $offerId = (int)$this->db->lastInsertId();

        echo json_encode([
            'success' => true,
            'offer_id' => $offerId,
            'message' => 'Offre creee avec succes !',
        ]);
    }

    /**
     * API - Supprimer une offre (proprietaire)
     * POST /api/owner/restaurant/{id}/offer/delete
     */
    public function apiDelete(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $restaurantId = (int)$request->param('id');
        $restaurant = $this->getOwnedRestaurant($restaurantId);

        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouve ou vous n\'etes pas le proprietaire']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $offerId = (int)($input['offer_id'] ?? 0);

        if ($offerId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de l\'offre invalide']);
            return;
        }

        // Verifier que l'offre appartient bien a ce restaurant
        $checkStmt = $this->db->prepare("
            SELECT id FROM restaurant_offers WHERE id = :oid AND restaurant_id = :rid
        ");
        $checkStmt->execute([':oid' => $offerId, ':rid' => $restaurantId]);

        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Offre introuvable pour ce restaurant']);
            return;
        }

        $deleteStmt = $this->db->prepare("
            DELETE FROM restaurant_offers WHERE id = :oid AND restaurant_id = :rid
        ");
        $deleteStmt->execute([':oid' => $offerId, ':rid' => $restaurantId]);

        echo json_encode([
            'success' => true,
            'message' => 'Offre supprimee avec succes',
        ]);
    }

    /**
     * Helper statique - Recuperer les offres actuellement actives pour un restaurant
     * Verifie la date, le jour de la semaine et le creneau horaire
     */
    public static function getActiveOffers(int $restaurantId): array
    {
        $db = \App\Core\Database::getInstance()->getPdo();

        $now = date('Y-m-d');
        $currentDay = (int)date('w');
        $currentTime = date('H:i:s');

        $stmt = $db->prepare("
            SELECT id, restaurant_id, title, description, discount_percent, offer_type,
                   valid_from, valid_to, days_of_week, time_start, time_end,
                   conditions, max_uses, current_uses, created_at
            FROM restaurant_offers
            WHERE restaurant_id = :rid
              AND status = 'active'
              AND valid_from <= :now1
              AND valid_to >= :now2
            ORDER BY discount_percent DESC, created_at DESC
        ");
        $stmt->execute([
            ':rid' => $restaurantId,
            ':now1' => $now,
            ':now2' => $now,
        ]);
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $active = [];
        foreach ($offers as $offer) {
            // Verifier max_uses
            if ($offer['max_uses'] > 0 && $offer['current_uses'] >= $offer['max_uses']) {
                continue;
            }

            // Verifier jour de la semaine
            if (!empty($offer['days_of_week'])) {
                $days = json_decode($offer['days_of_week'], true);
                if (is_array($days) && !empty($days) && !in_array($currentDay, $days)) {
                    continue;
                }
            }

            // Verifier creneaux horaires
            if (!empty($offer['time_start']) && !empty($offer['time_end'])) {
                if ($currentTime < $offer['time_start'] || $currentTime > $offer['time_end']) {
                    continue;
                }
            }

            $active[] = $offer;
        }

        return $active;
    }
}
