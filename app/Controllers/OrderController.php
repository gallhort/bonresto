<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use App\Services\NotificationService;
use App\Services\LoyaltyService;
use App\Services\ActivityFeedService;
use App\Services\RateLimiter;
use PDO;

class OrderController extends Controller
{
    /**
     * Page commande client - Affiche le menu du restaurant
     * GET /commander/{slug}
     */
    public function orderPage(Request $request): void
    {
        $slug = $request->param('slug');

        // Support both slug and numeric ID
        if (ctype_digit($slug)) {
            $stmt = $this->db->prepare("
                SELECT r.id, r.nom, r.slug, r.adresse, r.ville, r.phone, r.email,
                       r.orders_enabled, r.delivery_enabled, r.delivery_fee, r.delivery_min_order, r.delivery_max_km,
                       r.owner_id, r.gps_latitude, r.gps_longitude
                FROM restaurants r
                WHERE r.id = :id AND r.status = 'validated'
            ");
            $stmt->execute([':id' => (int)$slug]);
        } else {
            $stmt = $this->db->prepare("
                SELECT r.id, r.nom, r.slug, r.adresse, r.ville, r.phone, r.email,
                       r.orders_enabled, r.delivery_enabled, r.delivery_fee, r.delivery_min_order, r.delivery_max_km,
                       r.owner_id, r.gps_latitude, r.gps_longitude
                FROM restaurants r
                WHERE r.slug = :slug AND r.status = 'validated'
            ");
            $stmt->execute([':slug' => $slug]);
        }
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            $this->notFound('Restaurant non trouvé');
            return;
        }

        // Fetch menu items
        $menuStmt = $this->db->prepare("
            SELECT id, category, name, description, price, is_available, photo_path
            FROM restaurant_menu_items
            WHERE restaurant_id = :rid
            ORDER BY category, position
        ");
        $menuStmt->execute([':rid' => $restaurant['id']]);
        $menuItems = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

        // Load allergens for menu items
        $menuIds = array_column($menuItems, 'id');
        if (!empty($menuIds)) {
            $ph = implode(',', array_fill(0, count($menuIds), '?'));
            $algStmt = $this->db->prepare("SELECT menu_item_id, allergen FROM menu_item_allergens WHERE menu_item_id IN ({$ph})");
            $algStmt->execute($menuIds);
            $algMap = [];
            foreach ($algStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $algMap[(int)$row['menu_item_id']][] = $row['allergen'];
            }
            foreach ($menuItems as &$mi) {
                $mi['allergens'] = $algMap[(int)$mi['id']] ?? [];
            }
            unset($mi);
        }

        // Group by category
        $menuByCategory = [];
        foreach ($menuItems as $item) {
            $menuByCategory[$item['category']][] = $item;
        }

        // Get main photo
        $photoStmt = $this->db->prepare("
            SELECT path FROM restaurant_photos WHERE restaurant_id = :rid AND type = 'main' LIMIT 1
        ");
        $photoStmt->execute([':rid' => $restaurant['id']]);
        $mainPhoto = $photoStmt->fetchColumn() ?: null;

        $this->render('order.menu', [
            'title' => 'Commander - ' . $restaurant['nom'],
            'restaurant' => $restaurant,
            'menuByCategory' => $menuByCategory,
            'mainPhoto' => $mainPhoto,
        ]);
    }

    /**
     * API - Soumettre une commande
     * POST /api/orders
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

        // Rate limit: 3 pending orders per hour
        if (!RateLimiter::attempt("order_{$userId}", 3, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de commandes en attente. Réessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $slug = trim($input['slug'] ?? '');
        $orderType = $input['order_type'] ?? 'pickup';
        $clientPhone = trim($input['client_phone'] ?? '');
        $deliveryAddress = trim($input['delivery_address'] ?? '');
        $deliveryCity = trim($input['delivery_city'] ?? '');
        $instructions = trim($input['special_instructions'] ?? '');
        $cartItems = $input['items'] ?? [];
        $clientLat = isset($input['client_lat']) ? (float)$input['client_lat'] : null;
        $clientLng = isset($input['client_lng']) ? (float)$input['client_lng'] : null;

        // Validate basic fields
        if (!in_array($orderType, ['pickup', 'delivery'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Type de commande invalide']);
            return;
        }

        if (!preg_match('/^[0-9+\s\-]{8,20}$/', $clientPhone)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Numéro de téléphone invalide']);
            return;
        }

        if (empty($cartItems) || !is_array($cartItems)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le panier est vide']);
            return;
        }

        // Fetch restaurant
        $stmt = $this->db->prepare("
            SELECT id, nom, slug, orders_enabled, delivery_enabled, delivery_fee, delivery_min_order,
                   delivery_max_km, owner_id, gps_latitude, gps_longitude
            FROM restaurants
            WHERE slug = :slug AND status = 'validated' AND owner_id IS NOT NULL AND orders_enabled = 1
        ");
        $stmt->execute([':slug' => $slug]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouvé ou commandes désactivées']);
            return;
        }

        // Cannot order from own restaurant
        if ((int)$restaurant['owner_id'] === $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas commander dans votre propre restaurant']);
            return;
        }

        // Delivery checks
        if ($orderType === 'delivery') {
            if (!(int)$restaurant['delivery_enabled']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La livraison n\'est pas disponible pour ce restaurant']);
                return;
            }
            if (strlen($deliveryAddress) < 5) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Adresse de livraison requise']);
                return;
            }

            // Distance check (Haversine)
            $maxKm = (float)($restaurant['delivery_max_km'] ?? 0);
            if ($maxKm > 0 && $clientLat && $clientLng && $restaurant['gps_latitude'] && $restaurant['gps_longitude']) {
                $distance = $this->haversineDistance(
                    (float)$restaurant['gps_latitude'], (float)$restaurant['gps_longitude'],
                    $clientLat, $clientLng
                );
                if ($distance > $maxKm) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Vous êtes trop loin pour la livraison (max ' . number_format($maxKm, 1) . ' km). Distance: ' . number_format($distance, 1) . ' km.']);
                    return;
                }
            }
        }

        // Validate and price each item from DB (server-side truth)
        $itemIds = array_map(fn($it) => (int)($it['menu_item_id'] ?? 0), $cartItems);
        $itemIds = array_filter($itemIds, fn($id) => $id > 0);

        if (empty($itemIds)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Aucun article valide']);
            return;
        }

        $namedPlaceholders = [];
        $params = [':rid' => $restaurant['id']];
        foreach (array_values($itemIds) as $i => $id) {
            $key = ':item' . $i;
            $namedPlaceholders[] = $key;
            $params[$key] = $id;
        }
        $inClause = implode(',', $namedPlaceholders);
        $dbItemsStmt = $this->db->prepare("
            SELECT id, name, price, is_available
            FROM restaurant_menu_items
            WHERE id IN ($inClause) AND restaurant_id = :rid
        ");
        $dbItemsStmt->execute($params);
        $dbItems = [];
        foreach ($dbItemsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dbItems[(int)$row['id']] = $row;
        }

        // Validate each cart item
        $validatedItems = [];
        $itemsTotal = 0;

        foreach ($cartItems as $ci) {
            $menuItemId = (int)($ci['menu_item_id'] ?? 0);
            $quantity = max(1, min(99, (int)($ci['quantity'] ?? 1)));
            $notes = trim($ci['notes'] ?? '');

            if (!isset($dbItems[$menuItemId])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Article introuvable: ' . htmlspecialchars($ci['name'] ?? 'inconnu')]);
                return;
            }

            $dbItem = $dbItems[$menuItemId];

            if (!(int)$dbItem['is_available']) {
                http_response_code(409);
                echo json_encode(['success' => false, 'error' => 'Article indisponible: ' . htmlspecialchars($dbItem['name']), 'unavailable_item' => $menuItemId]);
                return;
            }

            $price = (float)$dbItem['price'];
            $lineTotal = $price * $quantity;
            $itemsTotal += $lineTotal;

            $validatedItems[] = [
                'menu_item_id' => $menuItemId,
                'item_name' => $dbItem['name'],
                'item_price' => $price,
                'quantity' => $quantity,
                'special_requests' => $notes ?: null,
            ];
        }

        // Delivery fee and minimum
        $deliveryFee = 0;
        if ($orderType === 'delivery') {
            $deliveryFee = (float)($restaurant['delivery_fee'] ?? 0);
            $minOrder = (float)($restaurant['delivery_min_order'] ?? 0);
            if ($minOrder > 0 && $itemsTotal < $minOrder) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Minimum de commande pour la livraison: ' . number_format($minOrder, 0) . ' DA']);
                return;
            }
        }

        $grandTotal = $itemsTotal + $deliveryFee;

        // Client name from session
        $clientName = trim(($_SESSION['user']['prenom'] ?? '') . ' ' . ($_SESSION['user']['nom'] ?? ''));
        if (strlen($clientName) < 2) $clientName = 'Client';

        // Create order in transaction
        $this->db->beginTransaction();
        try {
            $orderStmt = $this->db->prepare("
                INSERT INTO orders (restaurant_id, user_id, order_type, status, items_total, delivery_fee, grand_total,
                    client_name, client_phone, delivery_address, delivery_city, special_instructions)
                VALUES (:rid, :uid, :otype, 'pending', :items_total, :dfee, :gtotal,
                    :cname, :cphone, :daddr, :dcity, :instructions)
            ");
            $orderStmt->execute([
                ':rid' => $restaurant['id'],
                ':uid' => $userId,
                ':otype' => $orderType,
                ':items_total' => $itemsTotal,
                ':dfee' => $deliveryFee,
                ':gtotal' => $grandTotal,
                ':cname' => $clientName,
                ':cphone' => $clientPhone,
                ':daddr' => $orderType === 'delivery' ? $deliveryAddress : null,
                ':dcity' => $orderType === 'delivery' ? $deliveryCity : null,
                ':instructions' => $instructions ?: null,
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // Insert order items
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (order_id, menu_item_id, item_name, item_price, quantity, special_requests)
                VALUES (:oid, :mid, :name, :price, :qty, :notes)
            ");

            foreach ($validatedItems as $vi) {
                $itemStmt->execute([
                    ':oid' => $orderId,
                    ':mid' => $vi['menu_item_id'],
                    ':name' => $vi['item_name'],
                    ':price' => $vi['item_price'],
                    ':qty' => $vi['quantity'],
                    ':notes' => $vi['special_requests'],
                ]);
            }

            // Notify owner
            $notifService = new NotificationService($this->db);
            $notifService->create(
                (int)$restaurant['owner_id'],
                'order_placed',
                'Nouvelle commande !',
                $clientName . ' a passé une commande de ' . number_format($grandTotal, 0) . ' DA.',
                ['restaurant_id' => $restaurant['id'], 'order_id' => $orderId]
            );

            // Loyalty points
            $loyaltyService = new LoyaltyService($this->db);
            $loyaltyService->addPoints($userId, 'order_placed', $orderId, 'order');

            // Activity feed
            $feedService = new ActivityFeedService($this->db);
            $feedService->log($userId, 'order', 'restaurant', $restaurant['id'], [
                'order_id' => $orderId,
                'restaurant_name' => $restaurant['nom'],
                'total' => $grandTotal,
            ]);

            // Track recently viewed
            try {
                $this->db->prepare("
                    INSERT INTO user_recently_viewed (user_id, restaurant_id) VALUES (:uid, :rid)
                    ON DUPLICATE KEY UPDATE viewed_at = NOW()
                ")->execute([':uid' => $userId, ':rid' => $restaurant['id']]);
            } catch (\Exception $e) {
                // Non-critical, ignore
            }

            $this->db->commit();

            echo json_encode([
                'success' => true,
                'order_id' => $orderId,
                'grand_total' => $grandTotal,
                'message' => 'Commande envoyée ! Le restaurant va confirmer sous peu.',
            ]);

        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error("Order creation error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de la création de la commande']);
        }
    }

    /**
     * API - Owner: liste des commandes
     * GET /api/owner/restaurant/{id}/orders
     */
    public function ownerOrders(Request $request): void
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
            echo json_encode(['success' => false, 'error' => 'Non autorisé']);
            return;
        }

        $status = $_GET['status'] ?? null;
        $validStatuses = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered', 'cancelled', 'refused'];

        $sql = "
            SELECT o.*, u.prenom, u.nom as user_nom, u.photo_profil
            FROM orders o
            INNER JOIN users u ON u.id = o.user_id
            WHERE o.restaurant_id = :rid
        ";
        $params = [':rid' => $restaurantId];

        if ($status && in_array($status, $validStatuses)) {
            $sql .= " AND o.status = :status";
            $params[':status'] = $status;
        } else {
            // Default: active orders (not delivered/cancelled/refused)
            $sql .= " AND o.status NOT IN ('delivered', 'cancelled', 'refused')";
        }

        $sql .= " ORDER BY o.created_at DESC LIMIT 50";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Batch fetch items for all orders
        if (!empty($orders)) {
            $orderIds = array_column($orders, 'id');
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $itemsStmt = $this->db->prepare("
                SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY id
            ");
            $itemsStmt->execute($orderIds);
            $allItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $itemsByOrder = [];
            foreach ($allItems as $item) {
                $itemsByOrder[(int)$item['order_id']][] = $item;
            }

            foreach ($orders as &$order) {
                $order['items'] = $itemsByOrder[(int)$order['id']] ?? [];
            }
            unset($order);
        }

        // Pending count for badge
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM orders WHERE restaurant_id = :rid AND status = 'pending'
        ");
        $countStmt->execute([':rid' => $restaurantId]);
        $pendingCount = (int)$countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'orders' => $orders,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * API - Owner: accepter/refuser une commande
     * POST /api/owner/orders/{id}/respond
     */
    public function respond(Request $request): void
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

        $orderId = (int)$request->param('id');
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $estimatedMinutes = (int)($input['estimated_minutes'] ?? 30);
        $cancelReason = trim($input['reason'] ?? '');

        // Get order and verify ownership
        $order = $this->getOrderForOwner($orderId);
        if (!$order) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Commande non trouvée ou non autorisée']);
            return;
        }

        if ($order['status'] !== 'pending') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cette commande ne peut plus être acceptée/refusée']);
            return;
        }

        $notifService = new NotificationService($this->db);

        if ($action === 'confirm') {
            $stmt = $this->db->prepare("
                UPDATE orders SET status = 'confirmed', estimated_minutes = :mins, confirmed_at = NOW()
                WHERE id = :oid
            ");
            $stmt->execute([':mins' => max(5, min(180, $estimatedMinutes)), ':oid' => $orderId]);

            $notifService->create(
                (int)$order['user_id'],
                'order_confirmed',
                'Commande confirmée !',
                $order['restaurant_nom'] . ' a confirmé votre commande. Prêt dans ~' . $estimatedMinutes . ' min.',
                ['restaurant_id' => (int)$order['restaurant_id'], 'order_id' => $orderId]
            );

            echo json_encode(['success' => true, 'message' => 'Commande confirmée', 'new_status' => 'confirmed']);

        } elseif ($action === 'refuse') {
            $stmt = $this->db->prepare("
                UPDATE orders SET status = 'refused', cancel_reason = :reason
                WHERE id = :oid
            ");
            $stmt->execute([':reason' => $cancelReason ?: 'Refusée par le restaurant', ':oid' => $orderId]);

            $notifService->create(
                (int)$order['user_id'],
                'order_refused',
                'Commande refusée',
                $order['restaurant_nom'] . ' a refusé votre commande.' . ($cancelReason ? " Raison: $cancelReason" : ''),
                ['restaurant_id' => (int)$order['restaurant_id'], 'order_id' => $orderId]
            );

            echo json_encode(['success' => true, 'message' => 'Commande refusée', 'new_status' => 'refused']);

        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Action invalide']);
        }
    }

    /**
     * API - Owner: mettre à jour le statut d'une commande
     * POST /api/owner/orders/{id}/status
     */
    public function updateStatus(Request $request): void
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

        $orderId = (int)$request->param('id');
        $input = json_decode(file_get_contents('php://input'), true);
        $newStatus = $input['status'] ?? '';

        $order = $this->getOrderForOwner($orderId);
        if (!$order) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Commande non trouvée ou non autorisée']);
            return;
        }

        // State machine validation
        $validTransitions = [
            'confirmed' => ['preparing'],
            'preparing' => ['ready'],
            'ready'     => ['delivering', 'delivered'],
            'delivering'=> ['delivered'],
        ];

        $allowedNext = $validTransitions[$order['status']] ?? [];
        if (!in_array($newStatus, $allowedNext)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Transition de statut invalide']);
            return;
        }

        $extraSql = '';
        if ($newStatus === 'ready') {
            $extraSql = ', ready_at = NOW()';
        } elseif ($newStatus === 'delivered') {
            $extraSql = ', delivered_at = NOW()';
        }

        $stmt = $this->db->prepare("UPDATE orders SET status = :status {$extraSql} WHERE id = :oid");
        $stmt->execute([':status' => $newStatus, ':oid' => $orderId]);

        // Notify client
        $notifService = new NotificationService($this->db);
        $statusMessages = [
            'preparing'  => ['En préparation', 'Votre commande est en cours de préparation.'],
            'ready'      => ['Commande prête !', $order['order_type'] === 'delivery' ? 'Votre commande est prête et va être livrée.' : 'Votre commande est prête ! Venez la récupérer.'],
            'delivering' => ['En livraison', 'Votre commande est en cours de livraison.'],
            'delivered'  => ['Commande livrée', 'Votre commande a été livrée. Bon appétit !'],
        ];

        if (isset($statusMessages[$newStatus])) {
            $notifService->create(
                (int)$order['user_id'],
                'order_' . $newStatus,
                $statusMessages[$newStatus][0],
                $order['restaurant_nom'] . ' — ' . $statusMessages[$newStatus][1],
                ['restaurant_id' => (int)$order['restaurant_id'], 'order_id' => $orderId]
            );
        }

        echo json_encode(['success' => true, 'message' => 'Statut mis à jour', 'new_status' => $newStatus]);
    }

    /**
     * Page - Mes commandes (client)
     * GET /mes-commandes
     */
    public function myOrders(Request $request): void
    {
        $this->requireAuth();

        $this->render('order.my-orders', [
            'title' => 'Mes commandes',
        ]);
    }

    /**
     * API - Mes commandes (client) - AJAX
     * GET /api/my-orders
     */
    public function myOrdersApi(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = max(0, (int)($_GET['offset'] ?? 0));

        $stmt = $this->db->prepare("
            SELECT o.*, r.nom as restaurant_nom, r.slug as restaurant_slug,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as restaurant_photo
            FROM orders o
            INNER JOIN restaurants r ON r.id = o.restaurant_id
            WHERE o.user_id = :uid
            ORDER BY o.created_at DESC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Batch fetch items
        if (!empty($orders)) {
            $orderIds = array_column($orders, 'id');
            $placeholders = implode(',', array_fill(0, count($orderIds), '?'));
            $itemsStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders)");
            $itemsStmt->execute($orderIds);
            $allItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            $itemsByOrder = [];
            foreach ($allItems as $item) {
                $itemsByOrder[(int)$item['order_id']][] = $item;
            }
            foreach ($orders as &$order) {
                $order['items'] = $itemsByOrder[(int)$order['id']] ?? [];
            }
            unset($order);
        }

        // Total count
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :uid");
        $countStmt->execute([':uid' => $userId]);
        $total = (int)$countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'orders' => $orders,
            'total' => $total,
            'has_more' => ($offset + $limit) < $total,
        ]);
    }

    /**
     * API - Détail d'une commande
     * GET /api/orders/{id}
     */
    public function orderDetail(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $orderId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT o.*, r.nom as restaurant_nom, r.slug as restaurant_slug, r.phone as restaurant_tel
            FROM orders o
            INNER JOIN restaurants r ON r.id = o.restaurant_id
            WHERE o.id = :oid AND o.user_id = :uid
        ");
        $stmt->execute([':oid' => $orderId, ':uid' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Commande non trouvée']);
            return;
        }

        // Fetch items
        $itemsStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :oid");
        $itemsStmt->execute([':oid' => $orderId]);
        $order['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'order' => $order]);
    }

    /**
     * API - Annuler une commande (client)
     * POST /api/orders/{id}/cancel
     */
    public function cancelOrder(Request $request): void
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

        $orderId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT o.*, r.nom as restaurant_nom, r.owner_id
            FROM orders o
            INNER JOIN restaurants r ON r.id = o.restaurant_id
            WHERE o.id = :oid AND o.user_id = :uid
        ");
        $stmt->execute([':oid' => $orderId, ':uid' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Commande non trouvée']);
            return;
        }

        if (!in_array($order['status'], ['pending', 'confirmed'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Cette commande ne peut plus être annulée']);
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE orders SET status = 'cancelled', cancel_reason = 'Annulée par le client' WHERE id = :oid
        ");
        $stmt->execute([':oid' => $orderId]);

        // Notify owner
        if ($order['owner_id']) {
            $notifService = new NotificationService($this->db);
            $notifService->create(
                (int)$order['owner_id'],
                'order_cancelled',
                'Commande annulée',
                $order['client_name'] . ' a annulé sa commande #' . $orderId . '.',
                ['restaurant_id' => (int)$order['restaurant_id'], 'order_id' => $orderId]
            );
        }

        echo json_encode(['success' => true, 'message' => 'Commande annulée']);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Verify current user owns the restaurant for this order
     */
    private function getOrderForOwner(int $orderId): ?array
    {
        $userId = (int)$_SESSION['user']['id'];
        $stmt = $this->db->prepare("
            SELECT o.*, r.nom as restaurant_nom, r.owner_id
            FROM orders o
            INNER JOIN restaurants r ON r.id = o.restaurant_id
            WHERE o.id = :oid AND r.owner_id = :uid
        ");
        $stmt->execute([':oid' => $orderId, ':uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get restaurant owned by current user
     */
    private function getOwnedRestaurant(int $restaurantId): ?array
    {
        $userId = (int)$_SESSION['user']['id'];
        $stmt = $this->db->prepare("
            SELECT * FROM restaurants WHERE id = :rid AND owner_id = :uid
        ");
        $stmt->execute([':rid' => $restaurantId, ':uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     * Returns distance in kilometers
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
