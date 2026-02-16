<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\LoyaltyService;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * LOYALTY CONTROLLER - Gestion du programme de fidélité
 * ═══════════════════════════════════════════════════════════════════════════
 */
class LoyaltyController extends Controller
{
    private LoyaltyService $loyaltyService;
    
    public function __construct()
    {
        parent::__construct();
        $this->loyaltyService = new LoyaltyService($this->db);
    }
    
    /**
     * Récupère l'ID de l'utilisateur connecté
     */
    private function getUserId(): int
    {
        return (int) ($_SESSION['user']['id'] ?? 0);
    }
    
    /**
     * Page principale fidélité (profil utilisateur)
     * Route: GET /fidelite ou /loyalty
     */
    public function index(Request $request): void
    {
        $this->requireAuth();
        
        $userId = $this->getUserId();
        
        // Récupérer toutes les stats de fidélité
        $loyaltyStats = $this->loyaltyService->getUserLoyaltyStats($userId);
        
        // Récupérer le rang
        $rank = $this->loyaltyService->getUserRank($userId);
        
        // Récupérer le classement
        $leaderboard = $this->loyaltyService->getLeaderboard(10);
        
        // Infos utilisateur
        $stmt = $this->db->prepare("
            SELECT id, prenom, nom, email, photo_profil, created_at 
            FROM users WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $this->render('user/loyalty', [
            'title' => 'Mon Programme Fidélité',
            'user' => $user,
            'stats' => $loyaltyStats,
            'rank' => $rank,
            'leaderboard' => $leaderboard,
            'pointsConfig' => $this->loyaltyService->getPointsConfig()
        ]);
    }
    
    /**
     * API: Récupérer les stats fidélité (AJAX)
     * Route: GET /api/loyalty/stats
     */
    public function getStats(): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            return;
        }
        
        $userId = $this->getUserId();
        $stats = $this->loyaltyService->getUserLoyaltyStats($userId);
        $rank = $this->loyaltyService->getUserRank($userId);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'rank' => $rank
        ]);
    }
    
    /**
     * API: Récupérer le classement
     * Route: GET /api/loyalty/leaderboard
     */
    public function getLeaderboard(): void
    {
        header('Content-Type: application/json');
        
        $limit = (int)($_GET['limit'] ?? 10);
        $limit = min(50, max(5, $limit));
        
        $leaderboard = $this->loyaltyService->getLeaderboard($limit);
        
        echo json_encode([
            'success' => true,
            'leaderboard' => $leaderboard
        ]);
    }
    
    /**
     * API: Récupérer l'historique des points
     * Route: GET /api/loyalty/history
     */
    public function getHistory(): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            return;
        }
        
        $userId = $this->getUserId();
        $limit = (int)($_GET['limit'] ?? 20);
        
        $stmt = $this->db->prepare("
            SELECT * FROM user_points_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        $history = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'history' => $history
        ]);
    }
    
    /**
     * Utiliser un coupon
     * Route: POST /api/loyalty/use-coupon
     */
    public function useCoupon(Request $request): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $couponCode = $data['code'] ?? '';
        
        if (empty($couponCode)) {
            echo json_encode(['success' => false, 'error' => 'Code coupon requis']);
            return;
        }
        
        $userId = $this->getUserId();
        $result = $this->loyaltyService->useCoupon($userId, $couponCode);
        echo json_encode($result);
    }
    
    /**
     * Page des coupons disponibles
     * Route: GET /fidelite/coupons
     */
    public function coupons(): void
    {
        $this->requireAuth();
        
        $userId = $this->getUserId();
        
        // Coupons de l'utilisateur
        $userCoupons = $this->loyaltyService->getUserCoupons($userId);
        
        // Coupons disponibles à réclamer (selon le badge)
        $stmt = $this->db->prepare("SELECT badge, points FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Coupons non encore attribués
        $stmt = $this->db->prepare("
            SELECT c.* FROM coupons c
            WHERE c.is_active = 1 
            AND (c.valid_until IS NULL OR c.valid_until >= CURDATE())
            AND c.id NOT IN (SELECT coupon_id FROM user_coupons WHERE user_id = ?)
            ORDER BY c.discount_percent DESC
        ");
        $stmt->execute([$userId]);
        $availableCoupons = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->render('user/coupons', [
            'title' => 'Mes Coupons',
            'userCoupons' => $userCoupons,
            'availableCoupons' => $availableCoupons,
            'userBadge' => $user['badge'],
            'userPoints' => $user['points']
        ]);
    }
    
    /**
     * Réclamer un coupon
     * Route: POST /api/loyalty/claim-coupon
     */
    public function claimCoupon(): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Non connecté']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $couponId = (int)($data['coupon_id'] ?? 0);
        
        if (!$couponId) {
            echo json_encode(['success' => false, 'error' => 'ID coupon requis']);
            return;
        }
        
        $userId = $this->getUserId();
        $result = $this->loyaltyService->assignCoupon($userId, $couponId);
        echo json_encode($result);
    }
    
    /**
     * Widget mini fidélité (pour header/sidebar)
     * Route: GET /api/loyalty/widget
     */
    public function widget(): void
    {
        header('Content-Type: application/json');
        
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false]);
            return;
        }
        
        $userId = $this->getUserId();
        
        $stmt = $this->db->prepare("
            SELECT u.points, u.badge, b.icon, b.color
            FROM users u
            LEFT JOIN badges b ON b.name = u.badge
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Prochain badge
        $badgesConfig = $this->loyaltyService->getBadgesConfig();
        $nextBadge = null;
        foreach ($badgesConfig as $badge) {
            if ($badge['points'] > $data['points']) {
                $nextBadge = $badge;
                break;
            }
        }
        
        echo json_encode([
            'success' => true,
            'points' => $data['points'],
            'badge' => $data['badge'],
            'icon' => $data['icon'],
            'color' => $data['color'],
            'next_badge' => $nextBadge,
            'points_to_next' => $nextBadge ? $nextBadge['points'] - $data['points'] : 0
        ]);
    }
}