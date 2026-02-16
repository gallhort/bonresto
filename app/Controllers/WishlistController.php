<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;
use App\Services\LoyaltyService;
use App\Services\Logger;
/**
 * ═══════════════════════════════════════════════════════════════════════════
 * WISHLIST CONTROLLER - API Favoris
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Endpoints:
 * - POST   /api/wishlist/toggle     → Ajouter/Supprimer un favori
 * - GET    /api/wishlist/check/{id} → Vérifier si en favori
 * - GET    /api/wishlist            → Liste des favoris de l'utilisateur
 * - DELETE /api/wishlist/{id}       → Supprimer un favori
 */
class WishlistController extends Controller
{
    /**
     * Page Mes Favoris (vue HTML)
     * GET /wishlist
     */
    public function index($request = null): void
    {
        $userId = $this->getCurrentUserId();
        
        if (!$userId) {
            // Rediriger vers login
            header('Location: /login?redirect=/wishlist');
            exit;
        }

        $stmt = $this->db->prepare("
            SELECT 
                w.id as wishlist_id,
                w.created_at as added_at,
                r.id,
                r.nom,
                r.slug,
                r.type_cuisine,
                r.price_range,
                r.ville,
                r.adresse,
                r.gps_latitude,
                r.gps_longitude,
                COALESCE(r.note_moyenne, 0) as note_moyenne,
                COALESCE(r.nb_avis, 0) as nb_avis,
                (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as main_photo
            FROM wishlist w
            INNER JOIN restaurants r ON r.id = w.restaurant_id AND r.status = 'validated'
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('wishlist/index', [
            'title' => 'Mes Favoris',
            'favorites' => $favorites,
            'total' => count($favorites)
        ]);
    }

    /**
     * Ajouter aux favoris (compatibilité ancien système)
     * POST /wishlist/add
     */
    public function add($request = null): void
    {
        $this->toggle($request);
    }

    /**
     * Toggle favori (ajouter ou supprimer)
     * POST /api/wishlist/toggle
     * Body: { restaurant_id: int }
     */
    public function toggle($request = null): void
    {
        header('Content-Type: application/json');

        // Vérifier authentification
        $userId = $this->getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Vous devez être connecté pour ajouter aux favoris',
                'require_login' => true
            ]);
            return;
        }

        // Récupérer restaurant_id
        $input = json_decode(file_get_contents('php://input'), true);
        $restaurantId = (int)($input['restaurant_id'] ?? $_POST['restaurant_id'] ?? 0);

        if (!$restaurantId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'ID restaurant manquant'
            ]);
            return;
        }

        // Vérifier si le restaurant existe
        $stmt = $this->db->prepare("SELECT id, nom FROM restaurants WHERE id = ? AND status = 'validated'");
        $stmt->execute([$restaurantId]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Restaurant non trouvé'
            ]);
            return;
        }

        // Vérifier si déjà en favori
        $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND restaurant_id = ?");
        $stmt->execute([$userId, $restaurantId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Supprimer des favoris
            $stmt = $this->db->prepare("DELETE FROM wishlist WHERE id = ?");
            $stmt->execute([$existing['id']]);

            echo json_encode([
                'success' => true,
                'action' => 'removed',
                'is_favorite' => false,
                'message' => 'Retiré des favoris',
                'restaurant_name' => $restaurant['nom']
            ]);
        } else {
            // Ajouter aux favoris
            $stmt = $this->db->prepare("INSERT INTO wishlist (user_id, restaurant_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $restaurantId]);
            echo json_encode([
                'success' => true,
                'action' => 'added',
                'is_favorite' => true,
                'message' => 'Ajouté aux favoris',
                'restaurant_name' => $restaurant['nom']
            ]);
        }
    }

    /**
     * Vérifier si un restaurant est en favori
     * GET /api/wishlist/check/{id}
     */
    public function check($request = null): void
    {
        header('Content-Type: application/json');

        $userId = $this->getCurrentUserId();
        
        // Extraire l'ID du restaurant
        $restaurantId = $this->extractId($request, 'restaurant_id');

        if (!$restaurantId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID restaurant manquant']);
            return;
        }

        if (!$userId) {
            echo json_encode([
                'success' => true,
                'is_favorite' => false,
                'logged_in' => false
            ]);
            return;
        }

        $stmt = $this->db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND restaurant_id = ?");
        $stmt->execute([$userId, $restaurantId]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'is_favorite' => (bool)$exists,
            'logged_in' => true
        ]);
    }

    /**
     * Vérifier plusieurs restaurants en une fois
     * POST /api/wishlist/check-multiple
     * Body: { restaurant_ids: [1, 2, 3] }
     */
    public function checkMultiple($request = null): void
    {
        header('Content-Type: application/json');

        $userId = $this->getCurrentUserId();

        $input = json_decode(file_get_contents('php://input'), true);
        $restaurantIds = $input['restaurant_ids'] ?? [];

        if (empty($restaurantIds) || !is_array($restaurantIds)) {
            echo json_encode(['success' => true, 'favorites' => []]);
            return;
        }

        if (!$userId) {
            echo json_encode([
                'success' => true,
                'favorites' => [],
                'logged_in' => false
            ]);
            return;
        }

        // Nettoyer les IDs
        $restaurantIds = array_map('intval', $restaurantIds);
        $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));

        $stmt = $this->db->prepare("
            SELECT restaurant_id 
            FROM wishlist 
            WHERE user_id = ? AND restaurant_id IN ($placeholders)
        ");
        $stmt->execute(array_merge([$userId], $restaurantIds));
        $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'favorites' => array_map('intval', $favorites),
            'logged_in' => true
        ]);
    }

    /**
     * Liste des favoris de l'utilisateur
     * GET /api/wishlist
     */
    public function list($request = null): void
    {
        header('Content-Type: application/json');

        $userId = $this->getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Vous devez être connecté',
                'require_login' => true
            ]);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT 
                w.id as wishlist_id,
                w.created_at as added_at,
                r.id,
                r.nom,
                r.slug,
                r.type_cuisine,
                r.price_range,
                r.ville,
                r.adresse,
                COALESCE(r.note_moyenne, 0) as note_moyenne,
                COALESCE(r.nb_avis, 0) as nb_avis,
                (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as main_photo
            FROM wishlist w
            INNER JOIN restaurants r ON r.id = w.restaurant_id AND r.status = 'validated'
            WHERE w.user_id = ?
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'count' => count($favorites),
            'data' => $favorites
        ]);
    }

    /**
     * Supprimer un favori
     * DELETE /api/wishlist/{id}
     */
    public function remove($request = null): void
    {
        header('Content-Type: application/json');

        $userId = $this->getCurrentUserId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            return;
        }

        $restaurantId = $this->extractId($request, 'restaurant_id');

        if (!$restaurantId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID restaurant manquant']);
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM wishlist WHERE user_id = ? AND restaurant_id = ?");
        $stmt->execute([$userId, $restaurantId]);

        echo json_encode([
            'success' => true,
            'removed' => $stmt->rowCount() > 0
        ]);
    }

    /**
     * Compter les favoris de l'utilisateur
     * GET /api/wishlist/count
     */
    public function count($request = null): void
    {
        header('Content-Type: application/json');

        $userId = $this->getCurrentUserId();
        if (!$userId) {
            echo json_encode(['success' => true, 'count' => 0, 'logged_in' => false]);
            return;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $stmt->execute([$userId]);
        $count = (int)$stmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'count' => $count,
            'logged_in' => true
        ]);
    }

    /**
     * Récupérer l'ID utilisateur connecté
     */
    private function getCurrentUserId(): ?int
    {
        // Adapter selon ton système d'authentification
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Méthode 1: Session classique
        if (!empty($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }

        // Méthode 2: Session avec tableau user
        if (!empty($_SESSION['user']['id'])) {
            return (int)$_SESSION['user']['id'];
        }

        // Méthode 3: Si tu as une méthode dans ton Controller parent
        if (method_exists($this, 'getAuthUserId')) {
            return $this->getAuthUserId();
        }

        return null;
    }

    /**
     * Extraire l'ID depuis Request ou URL
     */
    private function extractId($request, string $paramName): int
    {
        // Depuis Request object
        if (is_object($request)) {
            if (method_exists($request, 'getParam')) {
                $id = $request->getParam($paramName) ?? $request->getParam('id');
                if ($id) return (int)$id;
            }
            if (isset($request->params[$paramName])) {
                return (int)$request->params[$paramName];
            }
            if (isset($request->params['id'])) {
                return (int)$request->params['id'];
            }
        }

        // Depuis GET/POST
        if (!empty($_GET[$paramName])) return (int)$_GET[$paramName];
        if (!empty($_POST[$paramName])) return (int)$_POST[$paramName];
        if (!empty($_GET['id'])) return (int)$_GET['id'];

        // Depuis URL
        $uri = $_SERVER['REQUEST_URI'];
        if (preg_match('/\/(\d+)(?:\?|$)/', $uri, $matches)) {
            return (int)$matches[1];
        }

        return 0;
    }
}