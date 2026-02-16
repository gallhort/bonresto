<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use PDO;

/**
 * Controller pour la gestion du restaurant par le propriétaire
 * Accessible uniquement aux utilisateurs ayant un owner_id sur un restaurant
 */
class OwnerController extends Controller
{
    /**
     * Vérifier que l'utilisateur est propriétaire du restaurant
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
     * Page - Éditer mon restaurant
     * GET /owner/restaurant/{id}/edit
     */
    public function edit(Request $request): void
    {
        $this->requireAuth();
        $restaurantId = (int)$request->param('id');
        $restaurant = $this->getOwnedRestaurant($restaurantId);

        if (!$restaurant) {
            $this->notFound('Restaurant non trouve ou vous n\'etes pas le proprietaire');
            return;
        }

        // Récupérer les horaires
        $hoursStmt = $this->db->prepare("
            SELECT * FROM restaurant_horaires WHERE restaurant_id = :rid ORDER BY jour_semaine ASC
        ");
        $hoursStmt->execute([':rid' => $restaurantId]);
        $horaires = $hoursStmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les photos
        $photosStmt = $this->db->prepare("
            SELECT * FROM restaurant_photos WHERE restaurant_id = :rid ORDER BY type, ordre
        ");
        $photosStmt->execute([':rid' => $restaurantId]);
        $photos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupérer les options/amenities (colonnes booléennes)
        $optStmt = $this->db->prepare("
            SELECT * FROM restaurant_options WHERE restaurant_id = :rid
        ");
        $optStmt->execute([':rid' => $restaurantId]);
        $optionsRow = $optStmt->fetch(PDO::FETCH_ASSOC) ?: [];
        $options = [];
        $optionFields = ['baby_chair','game_zone','handicap_access','parking','prayer_room','private_room','valet_service','wifi','terrace','delivery','takeaway','pets_allowed','air_conditioning'];
        foreach ($optionFields as $f) {
            if (!empty($optionsRow[$f])) $options[] = $f;
        }

        // Menu items
        $menuStmt = $this->db->prepare("
            SELECT * FROM restaurant_menu_items WHERE restaurant_id = :rid ORDER BY category, position
        ");
        $menuStmt->execute([':rid' => $restaurantId]);
        $menuItems = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

        // Réservations en attente
        $resStmt = $this->db->prepare("
            SELECT res.*, u.prenom, u.nom as client_nom, u.email
            FROM reservations res
            INNER JOIN users u ON u.id = res.user_id
            WHERE res.restaurant_id = :rid AND res.status = 'pending'
            ORDER BY res.date_souhaitee ASC
        ");
        $resStmt->execute([':rid' => $restaurantId]);
        $pendingReservations = $resStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('owner/edit', [
            'title' => 'Gerer ' . $restaurant['nom'],
            'restaurant' => $restaurant,
            'horaires' => $horaires,
            'photos' => $photos,
            'options' => $options,
            'menuItems' => $menuItems,
            'pendingReservations' => $pendingReservations,
        ]);
    }

    /**
     * API - Mettre à jour les infos du restaurant
     * POST /api/owner/restaurant/{id}/update
     */
    public function apiUpdate(Request $request): void
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

        $restaurantId = (int)$request->param('id');
        $restaurant = $this->getOwnedRestaurant($restaurantId);

        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorise']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        // Champs modifiables par le propriétaire (noms = colonnes DB)
        $allowedFields = [
            'description', 'phone', 'website', 'email',
            'price_range', 'prix_min', 'prix_max', 'reservations_enabled', 'menu_enabled',
            'orders_enabled', 'delivery_enabled', 'delivery_fee', 'delivery_min_order', 'delivery_max_km',
        ];

        $sets = [];
        $params = [':rid' => $restaurantId];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $input)) {
                $sets[] = "$field = :$field";
                $params[":$field"] = $input[$field];
            }
        }

        if (empty($sets)) {
            echo json_encode(['success' => true, 'message' => 'Rien a modifier']);
            return;
        }

        $sql = "UPDATE restaurants SET " . implode(', ', $sets) . " WHERE id = :rid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Restaurant mis a jour']);
    }

    /**
     * API - Mettre à jour les horaires
     * POST /api/owner/restaurant/{id}/hours
     */
    public function apiUpdateHours(Request $request): void
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

        $restaurantId = (int)$request->param('id');
        $restaurant = $this->getOwnedRestaurant($restaurantId);

        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorise']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $horaires = $input['horaires'] ?? [];

        $this->db->beginTransaction();
        try {
            $this->db->prepare("DELETE FROM restaurant_horaires WHERE restaurant_id = :rid")
                ->execute([':rid' => $restaurantId]);

            $insertStmt = $this->db->prepare("
                INSERT INTO restaurant_horaires (restaurant_id, jour_semaine, ferme, service_continu, ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir)
                VALUES (:rid, :jour, :ferme, :continu, :ouv_m, :ferm_m, :ouv_s, :ferm_s)
            ");

            foreach ($horaires as $h) {
                $ferme = (int)($h['est_ferme'] ?? 0);
                $continu = (int)($h['service_continu'] ?? 0);
                $insertStmt->execute([
                    ':rid' => $restaurantId,
                    ':jour' => (int)$h['jour'],
                    ':ferme' => $ferme,
                    ':continu' => $ferme ? 0 : $continu,
                    ':ouv_m' => $ferme ? null : ($h['ouverture_matin'] ?? null),
                    ':ferm_m' => $ferme ? null : ($h['fermeture_matin'] ?? null),
                    ':ouv_s' => ($ferme || $continu) ? null : ($h['ouverture_soir'] ?? null),
                    ':ferm_s' => ($ferme || $continu) ? null : ($h['fermeture_soir'] ?? null),
                ]);
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Horaires mis a jour']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error("Owner hours update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * API - Ajouter/modifier un item de menu
     * POST /api/owner/restaurant/{id}/menu
     */
    public function apiUpdateMenu(Request $request): void
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

        $restaurantId = (int)$request->param('id');
        $restaurant = $this->getOwnedRestaurant($restaurantId);

        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorise']);
            return;
        }

        // Support both JSON and FormData (for photo uploads)
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
        } else {
            $input = $_POST;
        }
        $action = $input['action'] ?? 'add';

        if ($action === 'add') {
            $name = trim($input['name'] ?? '');
            $category = trim($input['category'] ?? 'Plats');
            $price = isset($input['price']) && $input['price'] !== '' ? (float)$input['price'] : null;
            $description = trim($input['description'] ?? '');

            if (strlen($name) < 2) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Nom du plat requis']);
                return;
            }

            // Handle photo upload
            $photoPath = null;
            if (!empty($_FILES['photo']['tmp_name'])) {
                $photoPath = $this->handleMenuPhotoUpload($restaurantId);
                if ($photoPath === false) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Photo invalide (JPG/PNG, max 5 Mo)']);
                    return;
                }
            }

            $posStmt = $this->db->prepare("
                SELECT COALESCE(MAX(position), 0) + 1 FROM restaurant_menu_items
                WHERE restaurant_id = :rid AND category = :cat
            ");
            $posStmt->execute([':rid' => $restaurantId, ':cat' => $category]);
            $nextPos = (int)$posStmt->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO restaurant_menu_items (restaurant_id, category, name, description, price, position, photo_path)
                VALUES (:rid, :cat, :name, :desc, :price, :pos, :photo)
            ");
            $stmt->execute([
                ':rid' => $restaurantId,
                ':cat' => $category,
                ':name' => $name,
                ':desc' => $description ?: null,
                ':price' => $price,
                ':pos' => $nextPos,
                ':photo' => $photoPath,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Plat ajoute',
                'item_id' => (int)$this->db->lastInsertId(),
            ]);

        } elseif ($action === 'delete') {
            $itemId = (int)($input['item_id'] ?? 0);
            $stmt = $this->db->prepare("
                DELETE FROM restaurant_menu_items WHERE id = :id AND restaurant_id = :rid
            ");
            $stmt->execute([':id' => $itemId, ':rid' => $restaurantId]);
            echo json_encode(['success' => true, 'message' => 'Plat supprime']);

        } elseif ($action === 'update') {
            $itemId = (int)($input['item_id'] ?? 0);
            $name = trim($input['name'] ?? '');
            $price = isset($input['price']) ? (float)$input['price'] : null;
            $description = trim($input['description'] ?? '');
            $isAvailable = (int)($input['is_available'] ?? 1);

            $stmt = $this->db->prepare("
                UPDATE restaurant_menu_items
                SET name = :name, price = :price, description = :desc, is_available = :avail
                WHERE id = :id AND restaurant_id = :rid
            ");
            $stmt->execute([
                ':name' => $name,
                ':price' => $price,
                ':desc' => $description ?: null,
                ':avail' => $isAvailable,
                ':id' => $itemId,
                ':rid' => $restaurantId,
            ]);
            echo json_encode(['success' => true, 'message' => 'Plat mis a jour']);

        } elseif ($action === 'toggle_available') {
            $itemId = (int)($input['item_id'] ?? 0);
            $isAvailable = (int)($input['is_available'] ?? 1);

            $stmt = $this->db->prepare("
                UPDATE restaurant_menu_items SET is_available = :avail WHERE id = :id AND restaurant_id = :rid
            ");
            $stmt->execute([':avail' => $isAvailable, ':id' => $itemId, ':rid' => $restaurantId]);
            echo json_encode(['success' => true, 'message' => $isAvailable ? 'Plat disponible' : 'Plat indisponible']);

        } elseif ($action === 'upload_photo') {
            $itemId = (int)($input['item_id'] ?? 0);

            $photoPath = $this->handleMenuPhotoUpload($restaurantId);
            if ($photoPath === false) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Photo invalide (JPG/PNG, max 5 Mo)']);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE restaurant_menu_items SET photo_path = :photo WHERE id = :id AND restaurant_id = :rid
            ");
            $stmt->execute([':photo' => $photoPath, ':id' => $itemId, ':rid' => $restaurantId]);
            echo json_encode(['success' => true, 'message' => 'Photo ajoutee', 'photo_path' => $photoPath]);
        }
    }

    /**
     * API - Toggle réservations
     * POST /api/owner/restaurant/{id}/toggle-reservations
     */
    public function apiToggleReservations(Request $request): void
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
            echo json_encode(['success' => false, 'error' => 'Non autorise']);
            return;
        }

        $newValue = (int)$restaurant['reservations_enabled'] ? 0 : 1;
        $this->db->prepare("UPDATE restaurants SET reservations_enabled = :val WHERE id = :rid")
            ->execute([':val' => $newValue, ':rid' => $restaurantId]);

        echo json_encode([
            'success' => true,
            'reservations_enabled' => (bool)$newValue,
            'message' => $newValue ? 'Reservations activees' : 'Reservations desactivees',
        ]);
    }

    /**
     * Handle menu item photo upload
     * Returns filename on success, false on failure
     */
    private function handleMenuPhotoUpload(int $restaurantId): string|false
    {
        if (empty($_FILES['photo']['tmp_name'])) {
            return false;
        }

        $file = $_FILES['photo'];

        // Validate size (5 MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        // Validate image type
        $info = @getimagesize($file['tmp_name']);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP])) {
            return false;
        }

        // Create upload directory (use ROOT_PATH/public for correct path)
        $uploadDir = ROOT_PATH . '/public/uploads/menu/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $ext = match ($info[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => 'jpg',
        };
        $filename = 'menu_' . $restaurantId . '_' . uniqid() . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            return false;
        }

        return $filename;
    }
}
