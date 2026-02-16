<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use App\Middleware\AdminMiddleware;
use App\Services\Logger;
use App\Services\NotificationService;

class DashboardController extends Controller
{
    private Restaurant $restaurantModel;
    private Review $reviewModel;
    private User $userModel;
    
    public function __construct()
    {
        parent::__construct();
        AdminMiddleware::handle();
        
        $this->restaurantModel = new Restaurant();
        $this->reviewModel = new Review();
        $this->userModel = new User();
    }
    
    /**
     * Log a moderation action for audit trail
     */
    private function logModeration(string $action, string $targetType, int $targetId, ?string $reason = null, ?array $details = null): void
    {
        try {
            $adminId = $_SESSION['user']['id'] ?? 0;
            $stmt = $this->db->prepare("
                INSERT INTO moderation_log (admin_id, action, target_type, target_id, reason, details, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $adminId,
                $action,
                $targetType,
                $targetId,
                $reason,
                $details ? json_encode($details) : null
            ]);
        } catch (\Exception $e) {
            // Table might not exist yet, silently fail
            Logger::error("Moderation log failed: " . $e->getMessage());
        }
    }

    /**
     * Page d'accueil du dashboard admin
     */
    public function index(Request $request): void
    {
        $period = $request->get('period', '30'); // 7, 30, ou 'all'
        $filterVille = $request->get('ville', '');
        $filterCuisine = $request->get('cuisine', '');
        $filterStatus = $request->get('status', '');
        
        $data = [
            'title' => 'Dashboard Admin',
            'stats' => $this->getStats(),
            'pendingRestaurants' => $this->restaurantModel->getPending(),
            'pendingReviews' => $this->reviewModel->getPending(),
            'topRestaurants' => $this->getTopRestaurants(10, $filterVille, $filterCuisine, $filterStatus),
            'reportedReviews' => $this->getReportedReviews(),
            'recentActivity' => $this->getRecentActivity(),
            'chartData' => $this->getChartData($period),
            'currentPeriod' => $period,
            'notifications' => $this->getNotifications(),
            'topContributors' => $this->getTopContributors(),
            'compareStats' => $this->getCompareStats(),
            'availableVilles' => $this->getAvailableVilles(),
            'availableCuisines' => $this->getAvailableCuisines(),
            'filters' => [
                'ville' => $filterVille,
                'cuisine' => $filterCuisine,
                'status' => $filterStatus
            ]
        ];
        
        $this->render('admin/dashboard', $data);
    }
    
    /**
     * Récupère le Top 10 des restaurants par vues
     */
    private function getTopRestaurants(int $limit = 10, string $ville = '', string $cuisine = '', string $status = ''): array
    {
        try {
            $whereClauses = ["r.status = 'validated'"];
            $params = [];
            
            if ($ville) {
                $whereClauses[] = "r.ville = ?";
                $params[] = $ville;
            }
            
            if ($cuisine) {
                $whereClauses[] = "r.type_cuisine = ?";
                $params[] = $cuisine;
            }
            
            if ($status && $status !== 'validated') {
                $whereClauses[0] = "r.status = ?";
                array_unshift($params, $status);
            }
            
            $whereSQL = implode(' AND ', $whereClauses);
            
            $sql = "
                SELECT 
                    r.id,
                    r.nom,
                    r.ville,
                    r.type_cuisine,
                    r.note_moyenne,
                    r.nb_avis,
                    ra.views_total,
                    ra.views_unique,
                    ra.clicks_phone,
                    ra.clicks_directions,
                    ra.clicks_website,
                    ra.wishlist_adds,
                    (SELECT COUNT(*) FROM analytics_events 
                     WHERE restaurant_id = r.id 
                     AND event_type = 'view' 
                     AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as views_30_days,
                    CASE 
                        WHEN ra.views_total > 0 
                        THEN ROUND(((ra.clicks_phone + ra.clicks_directions + ra.clicks_website) / ra.views_total) * 100, 2)
                        ELSE 0 
                    END as engagement_rate
                FROM restaurants r
                LEFT JOIN restaurant_analytics ra ON r.id = ra.restaurant_id
                WHERE {$whereSQL}
                ORDER BY views_30_days DESC, ra.views_total DESC
                LIMIT ?
            ";
            
            $params[] = $limit;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            Logger::error("Error fetching top restaurants: ", [$e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Récupère les avis signalés
     */
    private function getReportedReviews(): array
    {
        try {
            // Vérifier si la table existe
            $tableExists = $this->db->query("
                SELECT COUNT(*) as count 
                FROM information_schema.tables 
                WHERE table_schema = DATABASE() 
                AND table_name = 'review_reports'
            ")->fetch(\PDO::FETCH_ASSOC);
            
            if (!$tableExists || $tableExists['count'] == 0) {
                return [];
            }
            
            $sql = "
                SELECT 
                    rr.id as report_id,
                    rr.review_id,
                    rr.reason,
                    rr.details,
                    rr.status as report_status,
                    rr.created_at as reported_at,
                    rev.note_globale,
                    rev.message as commentaire,
                    rev.created_at as review_date,
                    rev.status as review_status,
                    u.prenom as author_prenom,
                    u.nom as author_nom,
                    r.nom as restaurant_nom,
                    COUNT(DISTINCT rr2.id) as report_count
                FROM review_reports rr
                INNER JOIN reviews rev ON rr.review_id = rev.id
                LEFT JOIN users u ON rev.user_id = u.id
                LEFT JOIN restaurants r ON rev.restaurant_id = r.id
                LEFT JOIN review_reports rr2 ON rr2.review_id = rr.review_id
                WHERE rr.status = 'pending'
                GROUP BY rr.review_id
                ORDER BY report_count DESC, rr.created_at DESC
                LIMIT 20
            ";
            
            return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            Logger::error(trim("Error fetching reported reviews: "), [$e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Liste des restaurants
     */
    public function restaurants(Request $request): void
    {
        $status = $request->get('status', 'all');
        
        if ($status === 'pending') {
            $restaurants = $this->restaurantModel->getPending();
        } elseif ($status === 'validated') {
            $restaurants = $this->restaurantModel->getValidated();
        } else {
            $restaurants = $this->restaurantModel->all();
        }
        
        $data = [
            'title' => 'Gestion des Restaurants',
            'restaurants' => $restaurants,
            'currentStatus' => $status
        ];
        
        $this->render('admin/restaurants', $data);
    }
    
    /**
     * Valider un restaurant
     */
    public function validateRestaurant(Request $request): void
    {
        $id = $request->post('id');
        
        if ($this->restaurantModel->validate($id)) {
            $this->json([
                'success' => true,
                'message' => 'Restaurant validé avec succès'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la validation'
            ], 400);
        }
    }
    
    /**
     * Rejeter un restaurant
     */
    public function rejectRestaurant(Request $request): void
    {
        $id = $request->post('id');
        $reason = $request->post('reason');
        
        if ($this->restaurantModel->reject($id, $reason)) {
            $this->json([
                'success' => true,
                'message' => 'Restaurant rejeté'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors du rejet'
            ], 400);
        }
    }
    
    /**
     * Toggle featured status
     */
    public function toggleFeatured(Request $request): void
    {
        $id = $request->post('id');
        
        if ($this->restaurantModel->toggleFeatured($id)) {
            $this->json([
                'success' => true,
                'message' => 'Statut mis en avant modifié'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur'
            ], 400);
        }
    }
    
    /**
     * Liste des avis
     */
    /**
     * Liste des avis avec pagination et filtres
     */
    public function reviews(Request $request): void
    {
        $status = $request->get('status', 'pending');
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 25);
        $search = $request->get('search', '');
        $rating = $request->get('rating', '');
        $dateFrom = $request->get('from', '');
        $dateTo = $request->get('to', '');
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'DESC');
        
        // Sécurité : colonnes autorisées
        $allowedSort = ['created_at', 'note_globale', 'restaurant_nom', 'prenom', 'spam_score'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Construction de la requête avec filtres
        $whereClauses = [];
        $params = [];
        
        // Filtre statut SPÉCIAL pour "ai_rejected"
        if ($status === 'ai_rejected') {
            $whereClauses[] = "rev.ai_rejected = 1";
        } elseif ($status !== 'all') {
            $whereClauses[] = "rev.status = ?";
            $params[] = $status;
        }
        
        // Filtre note (>= pour inclure 4.5, 3.5, etc.)
        if ($rating) {
            $whereClauses[] = "rev.note_globale >= ? AND rev.note_globale < ?";
            $params[] = $rating;
            $params[] = $rating + 1;
        }
        
        // Filtre date
        if ($dateFrom) {
            $whereClauses[] = "DATE(rev.created_at) >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $whereClauses[] = "DATE(rev.created_at) <= ?";
            $params[] = $dateTo;
        }
        
        // Filtre recherche
        if ($search) {
            $whereClauses[] = "(r.nom LIKE ? OR u.prenom LIKE ? OR u.nom LIKE ? OR rev.message LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereSQL = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';
        
        // Compter total pour pagination
        $countSQL = "
            SELECT COUNT(*) as total
            FROM reviews rev
            LEFT JOIN users u ON rev.user_id = u.id
            LEFT JOIN restaurants r ON rev.restaurant_id = r.id
            {$whereSQL}
        ";
        
        $stmt = $this->db->prepare($countSQL);
        $stmt->execute($params);
        $total = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
        
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        // Récupérer les avis paginés
        $sql = "
            SELECT 
                rev.id,
                rev.note_globale,
                rev.message,
                rev.created_at,
                rev.status,
                rev.spam_score,
                rev.spam_details,
                rev.ai_rejected,
                rev.moderated_by,
                u.prenom,
                u.nom,
                r.nom as restaurant_nom,
                r.id as restaurant_id
            FROM reviews rev
            LEFT JOIN users u ON rev.user_id = u.id
            LEFT JOIN restaurants r ON rev.restaurant_id = r.id
            {$whereSQL}
            ORDER BY {$sortBy} {$sortOrder}
            LIMIT {$perPage} OFFSET {$offset}
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Stats globales (toute la BDD)
        $statsCount = [
            'pending' => (int) $this->db->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn(),
            'approved' => (int) $this->db->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn(),
            'rejected' => (int) $this->db->query("SELECT COUNT(*) FROM reviews WHERE status = 'rejected'")->fetchColumn(),
            'ai_rejected' => (int) $this->db->query("SELECT COUNT(*) FROM reviews WHERE ai_rejected = 1")->fetchColumn()
        ];
        
        // Note moyenne globale
        $avgRating = (float) $this->db->query("SELECT AVG(note_globale) FROM reviews")->fetchColumn();
        
        $data = [
            'title' => 'Gestion des Avis',
            'reviews' => $reviews,
            'currentStatus' => $status,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'totalReviews' => $total,
            'statsCount' => $statsCount,
            'avgRating' => $avgRating,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'filters' => [
                'search' => $search,
                'rating' => $rating,
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];
        
        $this->render('admin/reviews', $data);
    }
    
    /**
     * Approuver un avis
     */
    public function approveReview(Request $request): void
    {
        $id = (int)$request->post('id');

        if ($this->reviewModel->approve($id)) {
            $this->logModeration('approve_review', 'review', $id);

            // Notifier l'auteur + le propriétaire
            try {
                $rev = $this->db->prepare("SELECT user_id, restaurant_id, note_globale, author_name FROM reviews WHERE id = ?");
                $rev->execute([$id]);
                $revData = $rev->fetch(\PDO::FETCH_ASSOC);
                if ($revData) {
                    $resto = $this->db->prepare("SELECT nom FROM restaurants WHERE id = ?");
                    $resto->execute([$revData['restaurant_id']]);
                    $restoData = $resto->fetch(\PDO::FETCH_ASSOC);
                    $notifService = new NotificationService($this->db);
                    $notifService->notifyReviewApproved((int)$revData['user_id'], (int)$revData['restaurant_id'], $restoData['nom'] ?? '');
                    $notifService->notifyNewReview((int)$revData['restaurant_id'], $revData['author_name'] ?? 'Quelqu\'un', (float)$revData['note_globale']);
                }
            } catch (\Exception $e) { /* non critique */ }

            $this->json([
                'success' => true,
                'message' => 'Avis approuvé'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur'
            ], 400);
        }
    }

    /**
     * Rejeter un avis
     */
    public function rejectReview(Request $request): void
    {
        $id = (int)$request->post('id');
        $reason = $request->post('reason') ?? null;

        if ($this->reviewModel->reject($id)) {
            $this->logModeration('reject_review', 'review', $id, $reason);

            // Notifier l'auteur
            try {
                $rev = $this->db->prepare("SELECT user_id, restaurant_id FROM reviews WHERE id = ?");
                $rev->execute([$id]);
                $revData = $rev->fetch(\PDO::FETCH_ASSOC);
                if ($revData) {
                    $resto = $this->db->prepare("SELECT nom FROM restaurants WHERE id = ?");
                    $resto->execute([$revData['restaurant_id']]);
                    $restoData = $resto->fetch(\PDO::FETCH_ASSOC);
                    $notifService = new NotificationService($this->db);
                    $notifService->notifyReviewRejected((int)$revData['user_id'], (int)$revData['restaurant_id'], $restoData['nom'] ?? '');
                }
            } catch (\Exception $e) { /* non critique */ }

            $this->json([
                'success' => true,
                'message' => 'Avis rejeté'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Erreur'
            ], 400);
        }
    }

    /**
     * Renverser décision IA (approuver un avis rejeté par IA)
     */
    public function overrideAiDecision(Request $request): void
    {
        $id = (int)$request->post('id');

        try {
            $stmt = $this->db->prepare("
                UPDATE reviews
                SET status = 'approved',
                    ai_rejected = 0,
                    moderated_by = 'manual',
                    moderated_at = NOW()
                WHERE id = ?
            ");

            $stmt->execute([$id]);
            $this->logModeration('approve_review', 'review', $id, 'Override AI decision');

            $this->json([
                'success' => true,
                'message' => 'Décision IA renversée, avis approuvé'
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'override'
            ], 500);
        }
    }
    
    /**
     * Page stats IA
     */
    public function aiStatsPage(Request $request): void
    {
        $this->render('admin/ai-stats', [
            'title' => 'Stats Modération IA'
        ]);
    }
    
    /**
     * API Stats IA pour graphiques
     */
    public function getAiStats(Request $request): void
    {
        try {
            // Stats modération IA
            $stats = [
                // Répartition auto vs manuel
                'moderation_type' => $this->db->query("
                    SELECT 
                        moderated_by,
                        COUNT(*) as count
                    FROM reviews
                    WHERE moderated_by IN ('ai', 'manual')
                    GROUP BY moderated_by
                ")->fetchAll(\PDO::FETCH_ASSOC),
                
                // Distribution scores
                'score_distribution' => $this->db->query("
                    SELECT 
                        CASE 
                            WHEN spam_score >= 80 THEN '80-100 (Qualité)'
                            WHEN spam_score >= 50 THEN '50-79 (Moyen)'
                            ELSE '0-49 (Spam)'
                        END as score_range,
                        COUNT(*) as count
                    FROM reviews
                    WHERE spam_score IS NOT NULL
                    GROUP BY score_range
                    ORDER BY MIN(spam_score) DESC
                ")->fetchAll(\PDO::FETCH_ASSOC),
                
                // Évolution 30 derniers jours
                'evolution' => $this->db->query("
                    SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN moderated_by = 'ai' THEN 1 ELSE 0 END) as auto,
                        SUM(CASE WHEN moderated_by = 'manual' THEN 1 ELSE 0 END) as manual
                    FROM reviews
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC
                ")->fetchAll(\PDO::FETCH_ASSOC),
                
                // Top raisons rejet
                'top_penalties' => $this->getTopPenalties(),
                
                // Taux override (faux positifs)
                'override_rate' => $this->db->query("
                    SELECT 
                        COUNT(CASE WHEN ai_rejected = 1 AND status = 'approved' THEN 1 END) as overridden,
                        COUNT(CASE WHEN ai_rejected = 1 THEN 1 END) as total_rejected
                    FROM reviews
                ")->fetch(\PDO::FETCH_ASSOC)
            ];
            
            $this->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Logger::error(trim("AI Stats error: "), [$e->getMessage()]);
            $this->json([
                'success' => false,
                'message' => 'Erreur stats IA'
            ], 500);
        }
    }
    
    /**
     * Extrait les raisons de rejet les plus fréquentes
     */
    private function getTopPenalties(): array
    {
        try {
            $reviews = $this->db->query("
                SELECT spam_details 
                FROM reviews 
                WHERE spam_details IS NOT NULL 
                AND spam_details != ''
                LIMIT 1000
            ")->fetchAll(\PDO::FETCH_COLUMN);
            
            $penaltyCounts = [];
            
            foreach ($reviews as $details) {
                $data = json_decode($details, true);
                if (isset($data['penalties'])) {
                    foreach ($data['penalties'] as $penalty) {
                        $rule = $penalty['rule'];
                        if (!isset($penaltyCounts[$rule])) {
                            $penaltyCounts[$rule] = 0;
                        }
                        $penaltyCounts[$rule]++;
                    }
                }
            }
            
            arsort($penaltyCounts);
            
            $result = [];
            $i = 0;
            foreach ($penaltyCounts as $rule => $count) {
                if ($i++ >= 10) break;
                $result[] = ['rule' => $rule, 'count' => $count];
            }
            
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Récupérer détails d'un avis (API pour modal)
     */
    public function getReviewDetails(Request $request): void
    {
        $id = (int)$request->get('id');

        try {
            $stmt = $this->db->prepare("
                SELECT
                    rev.*,
                    u.prenom,
                    u.nom,
                    u.email,
                    r.nom as restaurant_nom,
                    r.ville as restaurant_ville
                FROM reviews rev
                LEFT JOIN users u ON rev.user_id = u.id
                LEFT JOIN restaurants r ON rev.restaurant_id = r.id
                WHERE rev.id = :id
            ");
            $stmt->execute([':id' => $id]);
            $review = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($review) {
                $this->json([
                    'success' => true,
                    'review' => $review
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Avis introuvable'
                ], 404);
            }
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }
    
    /**
     * Suppression en masse
     */
    public function bulkDeleteReviews(Request $request): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'Aucun ID fourni'], 400);
            return;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("DELETE FROM reviews WHERE id IN ({$placeholders})");
            $stmt->execute($ids);

            foreach ($ids as $id) {
                $this->logModeration('delete_review', 'review', (int)$id, 'Bulk delete');
            }

            $this->json([
                'success' => true,
                'message' => count($ids) . ' avis supprimé(s)',
                'deleted' => $stmt->rowCount()
            ]);
        } catch (\Exception $e) {
            Logger::error("Bulk delete error: ", [$e->getMessage()]);
            $this->json(['success' => false, 'message' => 'Erreur lors de la suppression'], 500);
        }
    }

    /**
     * Approbation en masse
     */
    public function bulkApproveReviews(Request $request): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'Aucun ID'], 400);
            return;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("UPDATE reviews SET status = 'approved' WHERE id IN ({$placeholders})");
            $stmt->execute($ids);

            foreach ($ids as $id) {
                $this->logModeration('approve_review', 'review', (int)$id, 'Bulk approve');
            }

            $this->json([
                'success' => true,
                'message' => $stmt->rowCount() . ' avis approuvé(s)'
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }

    /**
     * Rejet en masse
     */
    public function bulkRejectReviews(Request $request): void
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'Aucun ID'], 400);
            return;
        }

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("UPDATE reviews SET status = 'rejected' WHERE id IN ({$placeholders})");
            $stmt->execute($ids);

            foreach ($ids as $id) {
                $this->logModeration('reject_review', 'review', (int)$id, 'Bulk reject');
            }

            $this->json([
                'success' => true,
                'message' => $stmt->rowCount() . ' avis rejeté(s)'
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }
    
    /**
     * Export avis en CSV
     */
    public function exportReviews(Request $request): void
    {
        $status = $request->get('status', 'all');
        $format = $request->get('format', 'csv'); // csv ou xlsx

        // Requête sécurisée
        $allowedStatuses = ['pending', 'approved', 'rejected'];
        $params = [];
        $whereSQL = '';
        if ($status !== 'all' && in_array($status, $allowedStatuses, true)) {
            $whereSQL = 'WHERE rev.status = :status';
            $params[':status'] = $status;
        }

        $stmt = $this->db->prepare("
            SELECT
                rev.id,
                r.nom as restaurant,
                CONCAT(u.prenom, ' ', u.nom) as auteur,
                u.email,
                rev.note_globale,
                rev.message,
                rev.created_at,
                rev.status
            FROM reviews rev
            LEFT JOIN users u ON rev.user_id = u.id
            LEFT JOIN restaurants r ON rev.restaurant_id = r.id
            {$whereSQL}
            ORDER BY rev.created_at DESC
        ");
        $stmt->execute($params);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if ($format === 'xlsx') {
            $this->exportExcel($reviews);
        } else {
            $this->exportCsv($reviews);
        }
    }
    
    /**
     * Export CSV
     */
    private function exportCsv(array $reviews): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="avis_lebonresto_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        fputcsv($output, [
            'ID',
            'Restaurant',
            'Auteur',
            'Email',
            'Note',
            'Commentaire',
            'Date',
            'Statut'
        ], ';');
        
        foreach ($reviews as $review) {
            fputcsv($output, [
                $review['id'],
                $review['restaurant'],
                $review['auteur'],
                $review['email'],
                $review['note_globale'],
                $review['message'],
                date('d/m/Y H:i', strtotime($review['created_at'])),
                $review['status']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Export Excel (.xlsx) - VERSION SIMPLE sans librairie
     */
    private function exportExcel(array $reviews): void
    {
        // Version fallback : export en XML Excel (compatible sans PhpSpreadsheet)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="avis_lebonresto_' . date('Y-m-d') . '.xls"');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        echo '<Worksheet ss:Name="Avis"><Table>';
        
        // Headers
        echo '<Row>';
        echo '<Cell><Data ss:Type="String">ID</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Restaurant</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Auteur</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Email</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Note</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Commentaire</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Date</Data></Cell>';
        echo '<Cell><Data ss:Type="String">Statut</Data></Cell>';
        echo '</Row>';
        
        // Données
        foreach ($reviews as $review) {
            echo '<Row>';
            echo '<Cell><Data ss:Type="Number">' . $review['id'] . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($review['restaurant']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($review['auteur']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($review['email']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="Number">' . $review['note_globale'] . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($review['message']) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . date('d/m/Y H:i', strtotime($review['created_at'])) . '</Data></Cell>';
            echo '<Cell><Data ss:Type="String">' . $review['status'] . '</Data></Cell>';
            echo '</Row>';
        }
        
        echo '</Table></Worksheet></Workbook>';
        exit;
    }
    
    /**
     * Récupère les statistiques du dashboard
     */
    private function getStats(): array
    {
        // Stats de base
        $baseStats = [
            'total_restaurants' => $this->restaurantModel->rawQuery("SELECT COUNT(*) as count FROM restaurants")[0]['count'] ?? 0,
            'pending_restaurants' => $this->restaurantModel->rawQuery("SELECT COUNT(*) as count FROM restaurants WHERE status = 'pending'")[0]['count'] ?? 0,
            'validated_restaurants' => $this->restaurantModel->rawQuery("SELECT COUNT(*) as count FROM restaurants WHERE status = 'validated'")[0]['count'] ?? 0,
            'total_reviews' => $this->reviewModel->rawQuery("SELECT COUNT(*) as count FROM reviews")[0]['count'] ?? 0,
            'pending_reviews' => $this->reviewModel->rawQuery("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")[0]['count'] ?? 0,
            'total_users' => $this->userModel->rawQuery("SELECT COUNT(*) as count FROM users")[0]['count'] ?? 0,
        ];
        
        // Stats analytics (nouvelles)
        $analyticsStats = $this->getAnalyticsStats();
        
        return array_merge($baseStats, $analyticsStats);
    }
    
    /**
     * Récupère les statistiques analytics globales
     */
    private function getAnalyticsStats(): array
    {
        try {
            // Stats globales de la table restaurant_analytics
            $globalStats = $this->db->query("
                SELECT 
                    COALESCE(SUM(views_total), 0) as total_views,
                    COALESCE(SUM(views_unique), 0) as total_unique_visitors,
                    COALESCE(SUM(clicks_phone), 0) as total_clicks_phone,
                    COALESCE(SUM(clicks_directions), 0) as total_clicks_directions,
                    COALESCE(SUM(clicks_website), 0) as total_clicks_website,
                    COALESCE(SUM(wishlist_adds), 0) as total_wishlist_adds,
                    COALESCE(SUM(gallery_opens), 0) as total_gallery_opens,
                    COALESCE(SUM(shares), 0) as total_shares
                FROM restaurant_analytics
            ")->fetch(\PDO::FETCH_ASSOC);
            
            // Vues des 30 derniers jours
            $last30Days = $this->db->query("
                SELECT COUNT(*) as views_30_days
                FROM analytics_events
                WHERE event_type = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")->fetch(\PDO::FETCH_ASSOC);
            
            // Vues des 7 derniers jours
            $last7Days = $this->db->query("
                SELECT COUNT(*) as views_7_days
                FROM analytics_events
                WHERE event_type = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ")->fetch(\PDO::FETCH_ASSOC);
            
            // Vues aujourd'hui
            $today = $this->db->query("
                SELECT COUNT(*) as views_today
                FROM analytics_events
                WHERE event_type = 'view'
                AND DATE(created_at) = CURDATE()
            ")->fetch(\PDO::FETCH_ASSOC);
            
            // Calcul taux d'engagement moyen (clics / vues)
            $totalViews = (int)($globalStats['total_views'] ?? 0);
            $totalClicks = (int)($globalStats['total_clicks_phone'] ?? 0) + 
                          (int)($globalStats['total_clicks_directions'] ?? 0) + 
                          (int)($globalStats['total_clicks_website'] ?? 0);
            
            $engagementRate = $totalViews > 0 ? round(($totalClicks / $totalViews) * 100, 2) : 0;
            
            return [
                'analytics_total_views' => $globalStats['total_views'] ?? 0,
                'analytics_unique_visitors' => $globalStats['total_unique_visitors'] ?? 0,
                'analytics_clicks_phone' => $globalStats['total_clicks_phone'] ?? 0,
                'analytics_clicks_directions' => $globalStats['total_clicks_directions'] ?? 0,
                'analytics_clicks_website' => $globalStats['total_clicks_website'] ?? 0,
                'analytics_wishlist_adds' => $globalStats['total_wishlist_adds'] ?? 0,
                'analytics_gallery_opens' => $globalStats['total_gallery_opens'] ?? 0,
                'analytics_shares' => $globalStats['total_shares'] ?? 0,
                'analytics_views_30_days' => $last30Days['views_30_days'] ?? 0,
                'analytics_views_7_days' => $last7Days['views_7_days'] ?? 0,
                'analytics_views_today' => $today['views_today'] ?? 0,
                'analytics_engagement_rate' => $engagementRate
            ];
            
        } catch (\Exception $e) {
            Logger::error(trim("Error fetching analytics stats: "), [$e->getMessage()]);
            // Retourner des valeurs par défaut en cas d'erreur
            return [
                'analytics_total_views' => 0,
                'analytics_unique_visitors' => 0,
                'analytics_clicks_phone' => 0,
                'analytics_clicks_directions' => 0,
                'analytics_clicks_website' => 0,
                'analytics_wishlist_adds' => 0,
                'analytics_gallery_opens' => 0,
                'analytics_shares' => 0,
                'analytics_views_30_days' => 0,
                'analytics_views_7_days' => 0,
                'analytics_views_today' => 0,
                'analytics_engagement_rate' => 0
            ];
        }
    }
    
    /**
     * Récupère l'activité récente (timeline)
     */
    private function getRecentActivity(): array
    {
        try {
            $activity = [];
            
            // Derniers restaurants inscrits (5)
            $recentRestaurants = $this->db->query("
                SELECT id, nom, ville, created_at, status
                FROM restaurants
                ORDER BY created_at DESC
                LIMIT 5
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($recentRestaurants as $resto) {
                $activity[] = [
                    'type' => 'restaurant',
                    'icon' => '🍽️',
                    'title' => 'Nouveau restaurant',
                    'description' => htmlspecialchars($resto['nom']) . ' (' . htmlspecialchars($resto['ville']) . ')',
                    'status' => $resto['status'],
                    'created_at' => $resto['created_at']
                ];
            }
            
            // Derniers utilisateurs inscrits (5)
            $recentUsers = $this->db->query("
                SELECT id, prenom, nom, email, created_at
                FROM users
                ORDER BY created_at DESC
                LIMIT 5
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($recentUsers as $user) {
                $activity[] = [
                    'type' => 'user',
                    'icon' => '👤',
                    'title' => 'Nouvel utilisateur',
                    'description' => htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']),
                    'status' => 'active',
                    'created_at' => $user['created_at']
                ];
            }
            
            // Derniers avis publiés (10)
            $recentReviews = $this->db->query("
                SELECT 
                    rev.id,
                    rev.note_globale,
                    rev.created_at,
                    rev.status,
                    u.prenom,
                    u.nom,
                    r.nom as restaurant_nom
                FROM reviews rev
                LEFT JOIN users u ON rev.user_id = u.id
                LEFT JOIN restaurants r ON rev.restaurant_id = r.id
                ORDER BY rev.created_at DESC
                LIMIT 10
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            foreach ($recentReviews as $review) {
                $activity[] = [
                    'type' => 'review',
                    'icon' => '⭐',
                    'title' => 'Nouvel avis',
                    'description' => htmlspecialchars($review['prenom'] ?? 'Anonyme') . ' a noté ' . 
                                    htmlspecialchars($review['restaurant_nom']) . ' (' . $review['note_globale'] . '/5)',
                    'status' => $review['status'],
                    'created_at' => $review['created_at']
                ];
            }
            
            // Trier par date décroissante
            usort($activity, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Limiter à 15 éléments
            return array_slice($activity, 0, 15);
            
        } catch (\Exception $e) {
            Logger::error(trim("Error fetching recent activity: "), [$e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Récupère les données pour les graphiques
     */
    private function getChartData(string $period): array
    {
        try {
            $days = $period === 'all' ? 365 : (int)$period;
            
            // Évolution des vues (derniers jours)
            $viewsEvolution = $this->db->query("
                SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as views
                FROM analytics_events
                WHERE event_type = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Répartition devices
            $deviceStats = $this->db->query("
                SELECT 
                    device_type,
                    COUNT(*) as count
                FROM analytics_events
                WHERE device_type IS NOT NULL
                AND created_at >= DATE_SUB(NOW(), INTERVAL {$days} DAY)
                GROUP BY device_type
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Top types de cuisine (par nombre de restaurants)
            $cuisineStats = $this->db->query("
                SELECT 
                    type_cuisine,
                    COUNT(*) as count
                FROM restaurants
                WHERE status = 'validated'
                AND type_cuisine IS NOT NULL
                GROUP BY type_cuisine
                ORDER BY count DESC
                LIMIT 10
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Vues par type de cuisine (avec analytics)
            $cuisineViews = $this->db->query("
                SELECT 
                    r.type_cuisine,
                    SUM(ra.views_total) as total_views
                FROM restaurants r
                LEFT JOIN restaurant_analytics ra ON r.id = ra.restaurant_id
                WHERE r.status = 'validated'
                AND r.type_cuisine IS NOT NULL
                GROUP BY r.type_cuisine
                ORDER BY total_views DESC
                LIMIT 8
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'views_evolution' => $viewsEvolution,
                'device_stats' => $deviceStats,
                'cuisine_stats' => $cuisineStats,
                'cuisine_views' => $cuisineViews
            ];
            
        } catch (\Exception $e) {
            Logger::error(trim("Error fetching chart data: "), [$e->getMessage()]);
            return [
                'views_evolution' => [],
                'device_stats' => [],
                'cuisine_stats' => [],
                'cuisine_views' => []
            ];
        }
    }
    
    /**
     * API : Récupérer les données des graphiques (AJAX)
     * GET /admin/api/chart-data?period=7
     */
    public function getChartDataApi(Request $request): void
    {
        header('Content-Type: application/json');
        
        $period = $request->get('period', '30');
        $chartData = $this->getChartData($period);
        
        echo json_encode([
            'success' => true,
            'chartData' => $chartData
        ]);
    }
    
    /**
     * Export stats en CSV
     */
    public function exportStats(Request $request): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="stats_lebonresto_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM UTF-8 pour Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes
        fputcsv($output, [
            'Restaurant',
            'Ville',
            'Type',
            'Note',
            'Nb Avis',
            'Vues (30j)',
            'Vues (total)',
            'Clics Téléphone',
            'Clics Itinéraire',
            'Favoris',
            'Engagement %'
        ], ';');
        
        // Données
        $restaurants = $this->getTopRestaurants(100);
        
        foreach ($restaurants as $resto) {
            fputcsv($output, [
                $resto['nom'],
                $resto['ville'] ?? 'N/A',
                $resto['type_cuisine'] ?? 'N/A',
                number_format($resto['note_moyenne'] ?? 0, 1),
                $resto['nb_avis'] ?? 0,
                $resto['views_30_days'] ?? 0,
                $resto['views_total'] ?? 0,
                $resto['clicks_phone'] ?? 0,
                $resto['clicks_directions'] ?? 0,
                $resto['wishlist_adds'] ?? 0,
                $resto['engagement_rate'] ?? 0
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Récupère les notifications (pour badge + dropdown)
     */
    private function getNotifications(): array
    {
        try {
            $notifications = [];
            
            // Restaurants en attente
            $pendingCount = $this->db->query("
                SELECT COUNT(*) as count FROM restaurants WHERE status = 'pending'
            ")->fetch(\PDO::FETCH_ASSOC)['count'];
            
            if ($pendingCount > 0) {
                $notifications[] = [
                    'type' => 'restaurant',
                    'icon' => '🍽️',
                    'message' => "{$pendingCount} restaurant(s) en attente de validation",
                    'url' => '/admin/restaurants/pending',
                    'count' => $pendingCount
                ];
            }
            
            // Avis en attente
            $reviewsCount = $this->db->query("
                SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'
            ")->fetch(\PDO::FETCH_ASSOC)['count'];
            
            if ($reviewsCount > 0) {
                $notifications[] = [
                    'type' => 'review',
                    'icon' => '⭐',
                    'message' => "{$reviewsCount} avis en attente de modération",
                    'url' => '/admin/reviews/pending',
                    'count' => $reviewsCount
                ];
            }
            
            // Avis signalés
            $reportsCount = $this->db->query("
                SELECT COUNT(DISTINCT review_id) as count 
                FROM review_reports 
                WHERE status = 'pending'
            ")->fetch(\PDO::FETCH_ASSOC)['count'];
            
            if ($reportsCount > 0) {
                $notifications[] = [
                    'type' => 'report',
                    'icon' => '🚩',
                    'message' => "{$reportsCount} avis signalé(s)",
                    'url' => '/admin/dashboard#reported',
                    'count' => $reportsCount
                ];
            }
            
            return $notifications;
            
        } catch (\Exception $e) {
            Logger::error(trim("Error fetching notifications: "), [$e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Récupère les top contributeurs (users avec le plus d'avis)
     */
    private function getTopContributors(int $limit = 5): array
    {
        try {
            return $this->db->query("
                SELECT 
                    u.id,
                    u.prenom,
                    u.nom,
                    u.email,
                    COUNT(r.id) as review_count,
                    AVG(r.note_globale) as avg_rating,
                    MAX(r.created_at) as last_review
                FROM users u
                INNER JOIN reviews r ON u.id = r.user_id
                WHERE r.status = 'approved'
                GROUP BY u.id
                ORDER BY review_count DESC
                LIMIT {$limit}
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            Logger::error(trim("Error fetching top contributors: "), [$e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Comparaison stats avec période précédente
     */
    private function getCompareStats(): array
    {
        try {
            // Vues cette semaine vs semaine dernière
            $thisWeek = $this->db->query("
                SELECT COUNT(*) as count 
                FROM analytics_events 
                WHERE event_type = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ")->fetch(\PDO::FETCH_ASSOC)['count'];
            
            $lastWeek = $this->db->query("
                SELECT COUNT(*) as count 
                FROM analytics_events 
                WHERE event_type = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ")->fetch(\PDO::FETCH_ASSOC)['count'];
            
            $viewsChange = $lastWeek > 0 ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1) : 0;
            
            // Nouveaux restos ce mois vs mois dernier
            $thisMonth = $this->db->query("
                SELECT COUNT(*) as count 
                FROM restaurants 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")->fetch(\PDO::FETCH_ASSOC)['count'];
            
            $lastMonth = $this->db->query("
                SELECT COUNT(*) as count 
                FROM restaurants 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
                AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            ")->fetch(\PDO::FETCH_ASSOC)['count'];
            
            $restosChange = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;
            
            return [
                'views_change' => $viewsChange,
                'restos_change' => $restosChange,
                'this_week_views' => $thisWeek,
                'last_week_views' => $lastWeek
            ];
            
        } catch (\Exception $e) {
            Logger::error(trim("Error fetching compare stats: "), [$e->getMessage()]);
            return [
                'views_change' => 0,
                'restos_change' => 0,
                'this_week_views' => 0,
                'last_week_views' => 0
            ];
        }
    }
    
    /**
     * Liste des villes disponibles (pour filtre)
     */
    private function getAvailableVilles(): array
    {
        try {
            return $this->db->query("
                SELECT DISTINCT ville 
                FROM restaurants 
                WHERE ville IS NOT NULL 
                AND ville != ''
            ORDER BY ville ASC
            ")->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Liste des types cuisine disponibles (pour filtre)
     */
    private function getAvailableCuisines(): array
    {
        try {
            return $this->db->query("
                SELECT DISTINCT type_cuisine 
                FROM restaurants 
                WHERE type_cuisine IS NOT NULL 
                AND type_cuisine != ''
                ORDER BY type_cuisine ASC
            ")->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Vérifie que l'utilisateur est admin
     */
 
}
