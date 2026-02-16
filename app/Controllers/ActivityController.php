<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use App\Services\RateLimiter;
use App\Services\LoyaltyService;
use App\Services\ActivityFeedService;
use PDO;

class ActivityController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // BROWSE / SEARCH
    // ═══════════════════════════════════════════════════════════════

    public function index(): void
    {
        $filters = [
            'q'        => trim($_GET['q'] ?? ''),
            'ville'    => $_GET['ville'] ?? '',
            'category' => $_GET['category'] ?? '',
            'price'    => $_GET['price'] ?? '',
            'rating'   => $_GET['rating'] ?? '',
            'sort'     => $_GET['sort'] ?? 'popular',
            'page'     => max(1, (int)($_GET['page'] ?? 1)),
            'perPage'  => 20,
        ];

        try {
            // Single map query fetches all matching activities (for map + count)
            $mapResults = $this->getActivities($filters, true);
            // Pass pre-computed total to avoid a separate COUNT query
            $results    = $this->getActivities($filters, false, count($mapResults['data']));

            $categories = $this->db->query("
                SELECT DISTINCT category FROM activities WHERE status = 'active' ORDER BY category
            ")->fetchAll(PDO::FETCH_COLUMN);

            $villes = $this->db->query("
                SELECT DISTINCT ville FROM activities WHERE status = 'active' ORDER BY ville
            ")->fetchAll(PDO::FETCH_COLUMN);

            $this->view->renderPartial('activities.index', [
                'title'         => 'Activités & Sorties - LeBonResto',
                'activities'    => $results['data'],
                'mapActivities' => $mapResults['data'],
                'total'         => $results['total'],
                'pagination'    => $results['pagination'],
                'filters'       => $filters,
                'categories'    => $categories,
                'villes'        => $villes,
            ]);
        } catch (\Exception $e) {
            Logger::error('Erreur recherche activités', [$e->getMessage()]);
            http_response_code(500);
            echo "Une erreur est survenue.";
        }
    }

    public function apiFilter(): void
    {
        header('Content-Type: application/json');

        $forMap = !empty($_GET['forMap']);
        $filters = [
            'q'        => trim($_GET['q'] ?? ''),
            'ville'    => $_GET['ville'] ?? '',
            'category' => $_GET['category'] ?? '',
            'price'    => $_GET['price'] ?? '',
            'rating'   => $_GET['rating'] ?? '',
            'sort'     => $_GET['sort'] ?? 'popular',
            'page'     => max(1, (int)($_GET['page'] ?? 1)),
            'perPage'  => 20,
        ];

        // Bounds-based filtering (for dynamic map search)
        if (!empty($_GET['bounds_sw_lat']) && !empty($_GET['bounds_ne_lat'])) {
            $filters['bounds'] = [
                'sw_lat' => (float)$_GET['bounds_sw_lat'],
                'sw_lng' => (float)$_GET['bounds_sw_lng'],
                'ne_lat' => (float)$_GET['bounds_ne_lat'],
                'ne_lng' => (float)$_GET['bounds_ne_lng'],
            ];
        }

        try {
            $results = $this->getActivities($filters, $forMap);
            echo json_encode([
                'success'    => true,
                'data'       => $results['data'],
                'total'      => $results['total'],
                'pagination' => $results['pagination'] ?? null,
            ]);
        } catch (\Exception $e) {
            Logger::error('Erreur API activités', [$e->getMessage()]);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // SHOW (detail page)
    // ═══════════════════════════════════════════════════════════════

    public function show(Request $request): void
    {
        $idOrSlug = $request->param('id');

        // Support slug or numeric id
        if (is_numeric($idOrSlug)) {
            $stmt = $this->db->prepare("SELECT * FROM activities WHERE id = :id AND status = 'active'");
            $stmt->execute([':id' => (int)$idOrSlug]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM activities WHERE slug = :slug AND status = 'active'");
            $stmt->execute([':slug' => $idOrSlug]);
        }
        $activity = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$activity) {
            $this->notFound('Activité non trouvée');
            return;
        }

        // Photos
        $photosStmt = $this->db->prepare("SELECT * FROM activity_photos WHERE activity_id = :id ORDER BY type = 'main' DESC, id ASC");
        $photosStmt->execute([':id' => $activity['id']]);
        $photos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);

        // Reviews (approved, newest first)
        $reviewsStmt = $this->db->prepare("
            SELECT ar.*, u.prenom, u.nom as user_nom, u.badge
            FROM activity_reviews ar
            INNER JOIN users u ON u.id = ar.user_id
            WHERE ar.activity_id = :id AND ar.status = 'approved'
            ORDER BY ar.created_at DESC
            LIMIT 20
        ");
        $reviewsStmt->execute([':id' => $activity['id']]);
        $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Tips
        $tipsStmt = $this->db->prepare("
            SELECT t.*, u.prenom
            FROM activity_tips t
            INNER JOIN users u ON u.id = t.user_id
            WHERE t.activity_id = :id
            ORDER BY t.votes DESC, t.created_at DESC
            LIMIT 10
        ");
        $tipsStmt->execute([':id' => $activity['id']]);
        $tips = $tipsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Similar activities (same city + category, limit 4)
        $similarStmt = $this->db->prepare("
            SELECT a.*,
                   (SELECT path FROM activity_photos ap WHERE ap.activity_id = a.id AND ap.type = 'main' LIMIT 1) as main_photo
            FROM activities a
            WHERE a.status = 'active' AND a.id != :id
              AND (a.ville = :ville OR a.category = :cat)
            ORDER BY (a.ville = :ville2 AND a.category = :cat2) DESC, a.note_moyenne DESC
            LIMIT 4
        ");
        $similarStmt->execute([
            ':id'    => $activity['id'],
            ':ville' => $activity['ville'],
            ':cat'   => $activity['category'],
            ':ville2'=> $activity['ville'],
            ':cat2'  => $activity['category'],
        ]);
        $similar = $similarStmt->fetchAll(PDO::FETCH_ASSOC);

        // Rating distribution (count per star)
        $distStmt = $this->db->prepare("
            SELECT note_globale, COUNT(*) as cnt
            FROM activity_reviews
            WHERE activity_id = :id AND status = 'approved'
            GROUP BY note_globale
            ORDER BY note_globale DESC
        ");
        $distStmt->execute([':id' => $activity['id']]);
        $ratingDist = array_fill(1, 5, 0);
        foreach ($distStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $star = min(5, max(1, (int)$row['note_globale']));
            $ratingDist[$star] = (int)$row['cnt'];
        }

        // Wishlist check
        $inWishlist = false;
        if ($this->isAuthenticated()) {
            $wStmt = $this->db->prepare("SELECT id FROM activity_wishlist WHERE user_id = :uid AND activity_id = :aid");
            $wStmt->execute([':uid' => $_SESSION['user']['id'], ':aid' => $activity['id']]);
            $inWishlist = (bool)$wStmt->fetch();
        }

        $this->view->renderPartial('activities.show', [
            'title'      => htmlspecialchars($activity['nom']) . ' - LeBonResto',
            'activity'   => $activity,
            'photos'     => $photos,
            'reviews'    => $reviews,
            'tips'       => $tips,
            'similar'    => $similar,
            'inWishlist' => $inWishlist,
            'ratingDist' => $ratingDist,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // STORE REVIEW
    // ═══════════════════════════════════════════════════════════════

    public function storeReview(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId     = (int)$_SESSION['user']['id'];
        $activityId = (int)$request->param('id');

        if (!RateLimiter::attempt("activity_review_$userId", 5, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop d\'avis soumis. Réessayez plus tard.']);
            return;
        }

        $note    = (int)($_POST['note'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if ($note < 1 || $note > 5) {
            echo json_encode(['success' => false, 'error' => 'Note entre 1 et 5 requise']);
            return;
        }
        if (mb_strlen($message) < 10) {
            echo json_encode(['success' => false, 'error' => 'Avis trop court (min 10 caractères)']);
            return;
        }

        // Check activity exists
        $actStmt = $this->db->prepare("SELECT id, nom FROM activities WHERE id = :id AND status = 'active'");
        $actStmt->execute([':id' => $activityId]);
        if (!$actStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Activité non trouvée']);
            return;
        }

        // Check duplicate
        $dupStmt = $this->db->prepare("SELECT id FROM activity_reviews WHERE activity_id = :aid AND user_id = :uid");
        $dupStmt->execute([':aid' => $activityId, ':uid' => $userId]);
        if ($dupStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Vous avez déjà laissé un avis']);
            return;
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_reviews (activity_id, user_id, note_globale, message, status)
                VALUES (:aid, :uid, :note, :msg, 'approved')
            ");
            $stmt->execute([
                ':aid'  => $activityId,
                ':uid'  => $userId,
                ':note' => $note,
                ':msg'  => mb_substr($message, 0, 2000),
            ]);

            // Update activity stats
            $this->db->prepare("
                UPDATE activities SET
                    nb_avis = (SELECT COUNT(*) FROM activity_reviews WHERE activity_id = :aid1 AND status = 'approved'),
                    note_moyenne = COALESCE((SELECT AVG(note_globale) FROM activity_reviews WHERE activity_id = :aid2 AND status = 'approved'), 0)
                WHERE id = :aid3
            ")->execute([':aid1' => $activityId, ':aid2' => $activityId, ':aid3' => $activityId]);

            // Loyalty points
            $loyaltyService = new LoyaltyService($this->db);
            $loyaltyService->addPoints($userId, 'review_posted', $activityId, 'activity');

            $this->db->commit();

            echo json_encode(['success' => true, 'message' => 'Avis publié ! +50 points']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Erreur avis activité', [$e->getMessage()]);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // TOGGLE WISHLIST
    // ═══════════════════════════════════════════════════════════════

    public function toggleWishlist(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $input      = json_decode(file_get_contents('php://input'), true);
        $activityId = (int)($input['activity_id'] ?? 0);
        $userId     = (int)$_SESSION['user']['id'];

        if (!$activityId) {
            echo json_encode(['success' => false, 'error' => 'ID requis']);
            return;
        }

        $existing = $this->db->prepare("SELECT id FROM activity_wishlist WHERE user_id = :uid AND activity_id = :aid");
        $existing->execute([':uid' => $userId, ':aid' => $activityId]);

        if ($existing->fetch()) {
            $this->db->prepare("DELETE FROM activity_wishlist WHERE user_id = :uid AND activity_id = :aid")
                ->execute([':uid' => $userId, ':aid' => $activityId]);
            echo json_encode(['success' => true, 'added' => false]);
        } else {
            $this->db->prepare("INSERT INTO activity_wishlist (user_id, activity_id) VALUES (:uid, :aid)")
                ->execute([':uid' => $userId, ':aid' => $activityId]);
            echo json_encode(['success' => true, 'added' => true]);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // STORE TIP
    // ═══════════════════════════════════════════════════════════════

    public function storeTip(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId     = (int)$_SESSION['user']['id'];
        $activityId = (int)$request->param('id');

        if (!RateLimiter::attempt("activity_tip_$userId", 5, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de tips. Réessayez plus tard.']);
            return;
        }

        $input   = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');

        if (mb_strlen($message) < 5 || mb_strlen($message) > 200) {
            echo json_encode(['success' => false, 'error' => 'Tip entre 5 et 200 caractères']);
            return;
        }

        // Validate activity exists
        $actStmt = $this->db->prepare("SELECT id FROM activities WHERE id = :id AND status = 'active'");
        $actStmt->execute([':id' => $activityId]);
        if (!$actStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Activité non trouvée']);
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->db->prepare("INSERT INTO activity_tips (activity_id, user_id, message) VALUES (:aid, :uid, :msg)")
                ->execute([':aid' => $activityId, ':uid' => $userId, ':msg' => $message]);

            $loyaltyService = new LoyaltyService($this->db);
            $loyaltyService->addPoints($userId, 'tip', $activityId, 'activity');

            $this->db->commit();
            echo json_encode(['success' => true, 'message' => 'Tip ajouté ! +5 points']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('Erreur tip activité', [$e->getMessage()]);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // CHECK-IN
    // ═══════════════════════════════════════════════════════════════

    public function checkin(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId     = (int)$_SESSION['user']['id'];
        $activityId = (int)$request->param('id');

        $input   = json_decode(file_get_contents('php://input'), true);
        $userLat = (float)($input['lat'] ?? 0);
        $userLng = (float)($input['lng'] ?? 0);

        if (!$userLat || !$userLng) {
            echo json_encode(['success' => false, 'error' => 'Coordonnées GPS requises']);
            return;
        }

        $actStmt = $this->db->prepare("SELECT id, nom, gps_latitude, gps_longitude FROM activities WHERE id = :id AND status = 'active'");
        $actStmt->execute([':id' => $activityId]);
        $activity = $actStmt->fetch(PDO::FETCH_ASSOC);

        if (!$activity || !$activity['gps_latitude']) {
            echo json_encode(['success' => false, 'error' => 'Activité non trouvée ou sans GPS']);
            return;
        }

        // Cooldown 4h
        $cooldown = $this->db->prepare("
            SELECT id FROM activity_checkins
            WHERE user_id = :uid AND activity_id = :aid
            AND created_at > DATE_SUB(NOW(), INTERVAL 4 HOUR)
        ");
        $cooldown->execute([':uid' => $userId, ':aid' => $activityId]);
        if ($cooldown->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Check-in déjà fait récemment. Revenez dans quelques heures !']);
            return;
        }

        // Haversine
        $dLat = deg2rad($activity['gps_latitude'] - $userLat);
        $dLng = deg2rad($activity['gps_longitude'] - $userLng);
        $a = sin($dLat/2)**2 + cos(deg2rad($userLat)) * cos(deg2rad($activity['gps_latitude'])) * sin($dLng/2)**2;
        $dist = 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distM = (int)round($dist);

        if ($distM > 200) {
            echo json_encode(['success' => false, 'error' => "Trop loin ({$distM}m). Rapprochez-vous à moins de 200m."]);
            return;
        }

        $this->db->prepare("
            INSERT INTO activity_checkins (user_id, activity_id, user_lat, user_lng, distance_m)
            VALUES (:uid, :aid, :lat, :lng, :dist)
        ")->execute([':uid' => $userId, ':aid' => $activityId, ':lat' => $userLat, ':lng' => $userLng, ':dist' => $distM]);

        $loyaltyService = new LoyaltyService($this->db);
        $loyaltyService->addPoints($userId, 'checkin', $activityId, 'activity');

        echo json_encode(['success' => true, 'message' => 'Check-in validé ! +20 points', 'distance' => $distM]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE: QUERY BUILDER
    // ═══════════════════════════════════════════════════════════════

    private function getActivities(array $filters, bool $forMap, ?int $preTotal = null): array
    {
        $where  = ["a.status = 'active'"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]     = "(a.nom LIKE :q1 OR a.description LIKE :q2 OR a.ville LIKE :q3)";
            $params[':q1'] = '%' . $filters['q'] . '%';
            $params[':q2'] = '%' . $filters['q'] . '%';
            $params[':q3'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['ville'])) {
            $where[]          = "a.ville = :ville";
            $params[':ville'] = $filters['ville'];
        }

        if (!empty($filters['category'])) {
            $where[]        = "a.category = :cat";
            $params[':cat'] = $filters['category'];
        }

        if (!empty($filters['price'])) {
            $where[]          = "a.price_range = :price";
            $params[':price'] = $filters['price'];
        }

        if (!empty($filters['rating'])) {
            $where[]           = "a.note_moyenne >= :rating";
            $params[':rating'] = (float)$filters['rating'];
        }

        // Bounds-based filtering (dynamic map)
        if (!empty($filters['bounds'])) {
            $b = $filters['bounds'];
            $where[]            = "a.gps_latitude BETWEEN :sw_lat AND :ne_lat";
            $where[]            = "a.gps_longitude BETWEEN :sw_lng AND :ne_lng";
            $params[':sw_lat']  = min($b['sw_lat'], $b['ne_lat']);
            $params[':ne_lat']  = max($b['sw_lat'], $b['ne_lat']);
            $params[':sw_lng']  = min($b['sw_lng'], $b['ne_lng']);
            $params[':ne_lng']  = max($b['sw_lng'], $b['ne_lng']);
        }

        $whereSQL = implode(' AND ', $where);

        // Order
        $orderSQL = match ($filters['sort'] ?? 'popular') {
            'rating'  => 'a.note_moyenne DESC, a.nb_avis DESC',
            'newest'  => 'a.created_at DESC',
            'name'    => 'a.nom ASC',
            default   => 'a.featured DESC, a.nb_avis DESC, a.note_moyenne DESC',
        };

        if ($forMap) {
            $sql = "SELECT a.id, a.nom, a.slug, a.category, a.ville, a.gps_latitude, a.gps_longitude,
                           a.note_moyenne, a.nb_avis, a.price_range,
                           (SELECT path FROM activity_photos ap WHERE ap.activity_id = a.id AND ap.type = 'main' LIMIT 1) as main_photo
                    FROM activities a WHERE $whereSQL ORDER BY $orderSQL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => 0, 'pagination' => null];
        }

        // Count (skip if pre-computed from map query)
        if ($preTotal !== null) {
            $total = $preTotal;
        } else {
            $countSQL  = "SELECT COUNT(*) FROM activities a WHERE $whereSQL";
            $countStmt = $this->db->prepare($countSQL);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();
        }

        $page    = $filters['page'];
        $perPage = $filters['perPage'];
        $offset  = ($page - 1) * $perPage;

        $sql = "SELECT a.*,
                       (SELECT path FROM activity_photos ap WHERE ap.activity_id = a.id AND ap.type = 'main' LIMIT 1) as main_photo
                FROM activities a
                WHERE $whereSQL
                ORDER BY $orderSQL
                LIMIT :lim OFFSET :off";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'       => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'      => $total,
            'pagination' => [
                'page'       => $page,
                'perPage'    => $perPage,
                'totalPages' => ceil($total / $perPage),
                'total'      => $total,
            ],
        ];
    }
}
