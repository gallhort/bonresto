<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;

/**
 * Analytics Dashboard - Métriques business complètes
 */
class AnalyticsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * Page principale Analytics
     */
 /**
 * Page principale Analytics
 */
public function index(Request $request): void
{
    $period = (int)($request->get('period') ?? '30');
    
    $data = [
        'title' => 'Analytics & Rapports',
        'stats' => $this->getGlobalStats($period),
        'charts' => $this->getChartData($period),
            'topRestaurants' => $this->getTopRestaurants($period),
        'topRestaurantsByTraffic' => $this->getTopRestaurantsByTraffic($period), // Top par trafic
        'topUsers' => $this->getTopUsers($period),
        'recentActivity' => $this->getRecentActivity(),
        'aiStats' => $this->getAiModerationStats($period),
        // AJOUT des stats de trafic 
        'trafficStats' => $this->getTrafficStats($period),
        'trafficSources' => $this->getGlobalTrafficSources($period),
        'deviceStats' => $this->getGlobalDeviceStats($period),
        'trafficChart' => $this->getTrafficChartData(min($period, 30)),
        'period' => $period
    ];
    
    $this->render('admin/analytics-v2', $data);
}
    
    /**
     * Stats globales avec comparaison période précédente
     */
    private function getGlobalStats(int $period): array
    {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        $previousStart = date('Y-m-d', strtotime("-" . ($period * 2) . " days"));
        $previousEnd = $startDate;
        
        // Restaurants
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'validated' THEN 1 ELSE 0 END) as validated,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END) as period_new,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as previous_new
            FROM restaurants
        ");
        $stmt->execute([$startDate, $endDate, $previousStart, $previousEnd]);
        $restaurants = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Avis
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                AVG(note_globale) as avg_rating,
                SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END) as period_new,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as previous_new,
                SUM(CASE WHEN created_at >= ? AND created_at <= ? AND moderated_by = 'ai' THEN 1 ELSE 0 END) as period_ai,
                SUM(CASE WHEN created_at >= ? AND created_at <= ? AND ai_rejected = 1 THEN 1 ELSE 0 END) as period_ai_rejected
            FROM reviews
        ");
        $stmt->execute([$startDate, $endDate, $previousStart, $previousEnd, $startDate, $endDate, $startDate, $endDate]);
        $reviews = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Utilisateurs
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN created_at >= ? AND created_at <= ? THEN 1 ELSE 0 END) as period_new,
                SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END) as previous_new,
                SUM(CASE WHEN last_login >= ? THEN 1 ELSE 0 END) as active_users
            FROM users
        ");
        $stmt->execute([$startDate, $endDate, $previousStart, $previousEnd, date('Y-m-d', strtotime('-7 days'))]);
        $users = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Calcul des tendances (%)
        $restaurantTrend = $this->calculateTrend($restaurants['period_new'], $restaurants['previous_new']);
        $reviewTrend = $this->calculateTrend($reviews['period_new'], $reviews['previous_new']);
        $userTrend = $this->calculateTrend($users['period_new'], $users['previous_new']);
        
        return [
            'restaurants' => [
                'total' => $restaurants['total'],
                'validated' => $restaurants['validated'],
                'pending' => $restaurants['pending'],
                'new' => $restaurants['period_new'],
                'trend' => $restaurantTrend
            ],
            'reviews' => [
                'total' => $reviews['total'],
                'approved' => $reviews['approved'],
                'pending' => $reviews['pending'],
                'avg_rating' => round($reviews['avg_rating'] ?? 0, 2),
                'new' => $reviews['period_new'],
                'ai_moderated' => $reviews['period_ai'] ?? 0,
                'ai_rejected' => $reviews['period_ai_rejected'] ?? 0,
                'trend' => $reviewTrend
            ],
            'users' => [
                'total' => $users['total'],
                'new' => $users['period_new'],
                'active' => $users['active_users'],
                'trend' => $userTrend
            ]
        ];
    }
    
    /**
     * Données pour les graphiques
     */
    private function getChartData(int $period): array
    {
        $days = min($period, 30);
        
        $dates = [];
        $restaurantsData = [];
        $reviewsData = [];
        $usersData = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dates[] = date('d/m', strtotime($date));
            
            $stmt = $this->db->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM restaurants WHERE DATE(created_at) = ?) as resto_count,
                    (SELECT COUNT(*) FROM reviews WHERE DATE(created_at) = ? AND status = 'approved') as review_count,
                    (SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?) as user_count
            ");
            $stmt->execute([$date, $date, $date]);
            $dayData = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $restaurantsData[] = (int)$dayData['resto_count'];
            $reviewsData[] = (int)$dayData['review_count'];
            $usersData[] = (int)$dayData['user_count'];
        }
        
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Restaurants',
                    'data' => $restaurantsData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Avis',
                    'data' => $reviewsData,
                    'borderColor' => 'rgb(34, 224, 161)',
                    'backgroundColor' => 'rgba(34, 224, 161, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Utilisateurs',
                    'data' => $usersData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ];
    }
    
    /**
     * Top 10 restaurants par avis/notes
     */
                  private function getTopRestaurants(int $period): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        
        $stmt = $this->db->prepare("
            SELECT 
                r.id,
                r.nom,
                r.ville,
                r.type_cuisine,
                COUNT(rev.id) as review_count,
                AVG(rev.note_globale) as avg_rating,
                SUM(rev.votes_utiles) as total_votes
            FROM restaurants r
            LEFT JOIN reviews rev ON rev.restaurant_id = r.id 
                AND rev.status = 'approved'
                AND rev.created_at >= ?
            WHERE r.status = 'validated'
            GROUP BY r.id
            ORDER BY review_count DESC, avg_rating DESC
            LIMIT 10
        ");
        $stmt->execute([$startDate]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Top contributeurs
     */
    private function getTopUsers(int $period): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.prenom,
                u.nom,
                u.email,
                u.ville,
                COUNT(r.id) as review_count,
                AVG(r.note_globale) as avg_rating,
                SUM(r.votes_utiles) as total_helpful_votes
            FROM users u
            INNER JOIN reviews r ON r.user_id = u.id
            WHERE r.created_at >= ?
                AND r.status = 'approved'
            GROUP BY u.id
            ORDER BY review_count DESC, total_helpful_votes DESC
            LIMIT 10
        ");
        $stmt->execute([$startDate]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Activité récente
     */
    private function getRecentActivity(): array
    {
        $stmt = $this->db->query("
            SELECT 
                'review' as type,
                r.id,
                r.created_at,
                CONCAT(u.prenom, ' ', u.nom) as user_name,
                rest.nom as restaurant_name,
                r.note_globale as rating,
                r.status
            FROM reviews r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN restaurants rest ON rest.id = r.restaurant_id
            ORDER BY r.created_at DESC
            LIMIT 20
        ");
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Stats modération IA
     */
    private function getAiModerationStats(int $period): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_analyzed,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as auto_approved,
                SUM(CASE WHEN ai_rejected = 1 THEN 1 ELSE 0 END) as auto_rejected,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as need_manual,
                AVG(spam_score) as avg_score,
                SUM(CASE WHEN spam_score >= 80 THEN 1 ELSE 0 END) as high_quality,
                SUM(CASE WHEN spam_score >= 50 AND spam_score < 80 THEN 1 ELSE 0 END) as medium_quality,
                SUM(CASE WHEN spam_score < 50 THEN 1 ELSE 0 END) as low_quality
            FROM reviews
            WHERE created_at >= ?
                AND moderated_by IS NOT NULL
        ");
        $stmt->execute([$startDate]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $automationRate = $stats['total_analyzed'] > 0 
            ? round((($stats['auto_approved'] + $stats['auto_rejected']) / $stats['total_analyzed']) * 100, 1)
            : 0;
        
        return [
            'total_analyzed' => $stats['total_analyzed'] ?? 0,
            'auto_approved' => $stats['auto_approved'] ?? 0,
            'auto_rejected' => $stats['auto_rejected'] ?? 0,
            'need_manual' => $stats['need_manual'] ?? 0,
            'avg_score' => round($stats['avg_score'] ?? 0, 1),
            'automation_rate' => $automationRate,
            'quality_distribution' => [
                'high' => $stats['high_quality'] ?? 0,
                'medium' => $stats['medium_quality'] ?? 0,
                'low' => $stats['low_quality'] ?? 0
            ]
        ];
    }
    
    /**
     * Calcule la tendance en %
     */
    private function calculateTrend(int $current, int $previous): array
    {
        if ($previous == 0) {
            return ['value' => $current > 0 ? 100 : 0, 'direction' => $current > 0 ? 'up' : 'neutral'];
        }
        
        $percent = round((($current - $previous) / $previous) * 100, 1);
        
        return [
            'value' => abs($percent),
            'direction' => $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'neutral')
        ];
    }
    
    /**
     * Export CSV des données
     */
    public function exportCsv(Request $request): void
    {
        $period = (int)($request->get('period') ?? '30');
        $type = $request->get('type') ?? 'reviews';
        
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="lebonresto_' . $type . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        switch ($type) {
            case 'reviews':
                $this->exportReviews($output, $period);
                break;
            case 'restaurants':
                $this->exportRestaurants($output, $period);
                break;
            case 'users':
                $this->exportUsers($output, $period);
                break;
        }
        
        fclose($output);
        exit;
    }
    
    private function exportReviews($output, int $period): void
    {
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        
        fputcsv($output, ['ID', 'Restaurant', 'Auteur', 'Note', 'Status', 'Score IA', 'Modéré par', 'Date']);
        
        $stmt = $this->db->prepare("
            SELECT 
                r.id,
                rest.nom as restaurant,
                CONCAT(u.prenom, ' ', u.nom) as auteur,
                r.note_globale,
                r.status,
                r.spam_score,
                r.moderated_by,
                r.created_at
            FROM reviews r
            LEFT JOIN users u ON u.id = r.user_id
            LEFT JOIN restaurants rest ON rest.id = r.restaurant_id
            WHERE r.created_at >= ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$startDate]);
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
    }
    
    private function exportRestaurants($output, int $period): void
    {
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        
        fputcsv($output, ['ID', 'Nom', 'Ville', 'Type', 'Status', 'Nb Avis', 'Note Moyenne', 'Date Création']);
        
        $stmt = $this->db->prepare("
            SELECT 
                r.id,
                r.nom,
                r.ville,
                r.type_cuisine,
                r.status,
                COUNT(rev.id) as review_count,
                AVG(rev.note_globale) as avg_rating,
                r.created_at
            FROM restaurants r
            LEFT JOIN reviews rev ON rev.restaurant_id = r.id AND rev.status = 'approved'
            WHERE r.created_at >= ?
            GROUP BY r.id
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$startDate]);
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
    }
    
    private function exportUsers($output, int $period): void
    {
        fputcsv($output, ['ID', 'Nom', 'Email', 'Ville', 'Nb Avis', 'Note Moyenne', 'Date Inscription']);
        
        $stmt = $this->db->query("
            SELECT 
                u.id,
                CONCAT(u.prenom, ' ', u.nom) as nom,
                u.email,
                u.ville,
                COUNT(r.id) as review_count,
                AVG(r.note_globale) as avg_rating,
                u.created_at
            FROM users u
            LEFT JOIN reviews r ON r.user_id = u.id
            GROUP BY u.id
            ORDER BY review_count DESC
        ");
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
    }
    /**
 * Stats de trafic globales pour la plateforme
 */
private function getTrafficStats(int $period = 30): array
{
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    $previousStart = date('Y-m-d', strtotime("-" . ($period * 2) . " days"));
    
    // Période actuelle
    $stmt = $this->db->prepare("
        SELECT 
            COUNT(*) as total_events,
            SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
            COUNT(DISTINCT session_id) as unique_visitors,
            COUNT(DISTINCT restaurant_id) as restaurants_viewed,
            SUM(CASE WHEN event_type = 'click_phone' THEN 1 ELSE 0 END) as clicks_phone,
            SUM(CASE WHEN event_type = 'click_directions' THEN 1 ELSE 0 END) as clicks_directions,
            SUM(CASE WHEN event_type = 'click_website' THEN 1 ELSE 0 END) as clicks_website,
            SUM(CASE WHEN event_type = 'click_menu' THEN 1 ELSE 0 END) as clicks_menu,
            SUM(CASE WHEN event_type = 'wishlist_add' THEN 1 ELSE 0 END) as wishlist_adds,
            SUM(CASE WHEN event_type = 'share' THEN 1 ELSE 0 END) as shares,
            SUM(CASE WHEN event_type = 'review_submitted' THEN 1 ELSE 0 END) as reviews
        FROM analytics_events 
        WHERE created_at >= ?
    ");
    $stmt->execute([$startDate]);
    $current = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    // Période précédente
    $stmt = $this->db->prepare("
        SELECT 
            SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
            COUNT(DISTINCT session_id) as unique_visitors
        FROM analytics_events 
        WHERE created_at >= ? AND created_at < ?
    ");
    $stmt->execute([$previousStart, $startDate]);
    $previous = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    // Calcul des tendances
    $viewsTrend = $this->calculateTrend($current['views'] ?? 0, $previous['views'] ?? 0);
    $visitorsTrend = $this->calculateTrend($current['unique_visitors'] ?? 0, $previous['unique_visitors'] ?? 0);
    
    // Taux de conversion
    $totalClicks = ($current['clicks_phone'] ?? 0) + ($current['clicks_directions'] ?? 0) + ($current['clicks_website'] ?? 0);
    $conversionRate = ($current['views'] ?? 0) > 0 ? round(($totalClicks / $current['views']) * 100, 1) : 0;
    
    return [
        'views' => $current['views'] ?? 0,
        'views_trend' => $viewsTrend,
        'unique_visitors' => $current['unique_visitors'] ?? 0,
        'visitors_trend' => $visitorsTrend,
        'restaurants_viewed' => $current['restaurants_viewed'] ?? 0,
        'clicks_phone' => $current['clicks_phone'] ?? 0,
        'clicks_directions' => $current['clicks_directions'] ?? 0,
        'clicks_website' => $current['clicks_website'] ?? 0,
        'clicks_menu' => $current['clicks_menu'] ?? 0,
        'wishlist_adds' => $current['wishlist_adds'] ?? 0,
        'shares' => $current['shares'] ?? 0,
        'reviews' => $current['reviews'] ?? 0,
        'conversion_rate' => $conversionRate,
        'total_interactions' => $totalClicks
    ];
}

/**
 * Top 10 restaurants par trafic
 */
private function getTopRestaurantsByTraffic(int $period = 30, int $limit = 10): array
{
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    
    $stmt = $this->db->prepare("
        SELECT 
            r.id,
            r.nom,
            r.ville,
            r.status,
            COALESCE(stats.views, 0) as views,
            COALESCE(stats.unique_visitors, 0) as unique_visitors,
            COALESCE(stats.clicks_phone, 0) as clicks_phone,
            COALESCE(stats.clicks_directions, 0) as clicks_directions,
            COALESCE(stats.total_clicks, 0) as total_clicks
        FROM restaurants r
        INNER JOIN (
            SELECT 
                restaurant_id,
                SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
                COUNT(DISTINCT CASE WHEN event_type = 'view' THEN session_id END) as unique_visitors,
                SUM(CASE WHEN event_type = 'click_phone' THEN 1 ELSE 0 END) as clicks_phone,
                SUM(CASE WHEN event_type = 'click_directions' THEN 1 ELSE 0 END) as clicks_directions,
                SUM(CASE WHEN event_type IN ('click_phone', 'click_directions', 'click_website') THEN 1 ELSE 0 END) as total_clicks
            FROM analytics_events
            WHERE created_at >= ?
            GROUP BY restaurant_id
        ) stats ON stats.restaurant_id = r.id
        ORDER BY stats.views DESC
        LIMIT ?
    ");
    $stmt->execute([$startDate, $limit]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Sources de trafic globales
 */
private function getGlobalTrafficSources(int $period = 30): array
{
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    
    $stmt = $this->db->prepare("
        SELECT 
            CASE 
                WHEN referer IS NULL OR referer = '' THEN 'Direct'
                WHEN referer LIKE '%google%' THEN 'Google'
                WHEN referer LIKE '%facebook%' THEN 'Facebook'
                WHEN referer LIKE '%instagram%' THEN 'Instagram'
                WHEN referer LIKE '%lebonresto%' OR referer LIKE '%localhost%' THEN 'Recherche interne'
                ELSE 'Autre'
            END as source,
            COUNT(*) as count
        FROM analytics_events 
        WHERE event_type = 'view' AND created_at >= ?
        GROUP BY source
        ORDER BY count DESC
    ");
    $stmt->execute([$startDate]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Répartition par appareils
 */
private function getGlobalDeviceStats(int $period = 30): array
{
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    
    $stmt = $this->db->prepare("
        SELECT 
            COALESCE(device_type, 'unknown') as device,
            COUNT(*) as count
        FROM analytics_events 
        WHERE event_type = 'view' AND created_at >= ?
        GROUP BY device_type
        ORDER BY count DESC
    ");
    $stmt->execute([$startDate]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

/**
 * Données pour graphique évolution trafic
 */
private function getTrafficChartData(int $days = 30): array
{
    $labels = [];
    $viewsData = [];
    $visitorsData = [];
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('d/m', strtotime($date));
        
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
                COUNT(DISTINCT CASE WHEN event_type = 'view' THEN session_id END) as visitors
            FROM analytics_events 
            WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$date]);
        $day = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $viewsData[] = (int)($day['views'] ?? 0);
        $visitorsData[] = (int)($day['visitors'] ?? 0);
    }
    
    return [
        'labels' => $labels,
        'views' => $viewsData,
        'visitors' => $visitorsData
    ];
}

 /**
 * Recherche de restaurant pour stats (API AJAX)
 */
public function searchRestaurant(): void
{
    header('Content-Type: application/json');
    
    $query = trim($_GET['q'] ?? '');
    $period = (int)($_GET['period'] ?? 30);
    
    if (strlen($query) < 2) {
        echo json_encode(['results' => []]);
        return;
    }
    
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    
    $stmt = $this->db->prepare("
        SELECT 
            r.id,
            r.nom,
            r.ville,
            r.status,
            COALESCE(SUM(CASE WHEN ae.event_type = 'view' THEN 1 ELSE 0 END), 0) as views,
            COALESCE(COUNT(DISTINCT ae.session_id), 0) as unique_visitors,
            COALESCE(SUM(CASE WHEN ae.event_type = 'click_phone' THEN 1 ELSE 0 END), 0) as clicks_phone,
            COALESCE(SUM(CASE WHEN ae.event_type = 'click_directions' THEN 1 ELSE 0 END), 0) as clicks_directions,
            COALESCE(SUM(CASE WHEN ae.event_type = 'click_website' THEN 1 ELSE 0 END), 0) as clicks_website,
            COALESCE(SUM(CASE WHEN ae.event_type IN ('click_phone', 'click_directions', 'click_website') THEN 1 ELSE 0 END), 0) as total_clicks
        FROM restaurants r
        LEFT JOIN analytics_events ae ON ae.restaurant_id = r.id AND ae.created_at >= ?
        WHERE r.nom LIKE ? OR r.ville LIKE ?
        GROUP BY r.id, r.nom, r.ville, r.status
        ORDER BY views DESC
        LIMIT 20
    ");
    
    $searchTerm = "%{$query}%";
    $stmt->execute([$startDate, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    echo json_encode(['results' => $results]);
}

/**
 * Stats détaillées d'un restaurant (admin)
 */
public function restaurantStats($request): void
{
    $restaurantId = (int) $request->param('id');
    $period = (int)($_GET['period'] ?? 30);
    
    // Récupérer le restaurant
    $stmt = $this->db->prepare("
        SELECT r.*, u.username as owner_name, u.email as owner_email
        FROM restaurants r
        LEFT JOIN users u ON r.owner_id = u.id
        WHERE r.id = ?
    ");
    $stmt->execute([$restaurantId]);
    $restaurant = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$restaurant) {
        $this->redirect('/admin/analytics');
        return;
    }
    
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    $previousStart = date('Y-m-d', strtotime("-" . ($period * 2) . " days"));
    
    // Stats principales
    $mainStats = $this->getRestaurantStats($restaurantId, $startDate, $previousStart);
    
    // Graphique
    $chartData = $this->getRestaurantChartData($restaurantId, min($period, 30));
    
    // Heures populaires
    $hourlyStats = $this->getRestaurantHourlyStats($restaurantId, $startDate);
    
    // Actions détaillées
    $actionsStats = $this->getRestaurantActionsStats($restaurantId, $startDate);
    
    // Sources de trafic
    $trafficSources = $this->getRestaurantTrafficSources($restaurantId, $startDate);
    
    // Appareils
    $deviceStats = $this->getRestaurantDeviceStats($restaurantId, $startDate);
    
    // Activité récente
    $recentActivity = $this->getRestaurantRecentActivity($restaurantId, 20);
    
    // Jours de la semaine
    $weekdayStats = $this->getRestaurantWeekdayStats($restaurantId, $startDate);
    
    $this->render('admin/restaurant-stats', [
        'title' => 'Stats - ' . $restaurant['nom'],
        'restaurant' => $restaurant,
        'mainStats' => $mainStats,
        'chartData' => $chartData,
        'hourlyStats' => $hourlyStats,
        'actionsStats' => $actionsStats,
        'trafficSources' => $trafficSources,
        'deviceStats' => $deviceStats,
        'recentActivity' => $recentActivity,
        'weekdayStats' => $weekdayStats,
        'period' => $period
    ]);
}

private function getRestaurantStats(int $restaurantId, string $startDate, string $previousStart): array
{
    // Période actuelle
    $stmt = $this->db->prepare("
        SELECT 
            COUNT(*) as total_events,
            SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
            COUNT(DISTINCT session_id) as unique_visitors
        FROM analytics_events 
        WHERE restaurant_id = ? AND created_at >= ?
    ");
    $stmt->execute([$restaurantId, $startDate]);
    $current = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    // Période précédente
    $stmt = $this->db->prepare("
        SELECT SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views
        FROM analytics_events 
        WHERE restaurant_id = ? AND created_at >= ? AND created_at < ?
    ");
    $stmt->execute([$restaurantId, $previousStart, $startDate]);
    $previous = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    // Actions
    $stmt = $this->db->prepare("
        SELECT 
            SUM(CASE WHEN event_type = 'click_phone' THEN 1 ELSE 0 END) as clicks_phone,
            SUM(CASE WHEN event_type = 'click_directions' THEN 1 ELSE 0 END) as clicks_directions,
            SUM(CASE WHEN event_type = 'click_website' THEN 1 ELSE 0 END) as clicks_website,
            SUM(CASE WHEN event_type = 'click_menu' THEN 1 ELSE 0 END) as clicks_menu,
            SUM(CASE WHEN event_type = 'wishlist_add' THEN 1 ELSE 0 END) as wishlist_adds,
            SUM(CASE WHEN event_type = 'share' THEN 1 ELSE 0 END) as shares,
            SUM(CASE WHEN event_type = 'review_submitted' THEN 1 ELSE 0 END) as reviews
        FROM analytics_events 
        WHERE restaurant_id = ? AND created_at >= ?
    ");
    $stmt->execute([$restaurantId, $startDate]);
    $actions = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    $totalClicks = ($actions['clicks_phone'] ?? 0) + ($actions['clicks_directions'] ?? 0) + ($actions['clicks_website'] ?? 0);
    $conversionRate = ($current['views'] ?? 0) > 0 ? round(($totalClicks / $current['views']) * 100, 1) : 0;
    
    return [
        'views' => (int)($current['views'] ?? 0),
        'unique_visitors' => (int)($current['unique_visitors'] ?? 0),
        'views_trend' => $this->calculateTrend((int)($current['views'] ?? 0), (int)($previous['views'] ?? 0)),
        'total_interactions' => $totalClicks,
        'conversion_rate' => $conversionRate,
        'actions' => $actions
    ];
}

private function getRestaurantChartData(int $restaurantId, int $days): array
{
    $labels = [];
    $viewsData = [];
    $clicksData = [];
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('d/m', strtotime($date));
        
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
                SUM(CASE WHEN event_type IN ('click_phone', 'click_directions', 'click_website') THEN 1 ELSE 0 END) as clicks
            FROM analytics_events 
            WHERE restaurant_id = ? AND DATE(created_at) = ?
        ");
        $stmt->execute([$restaurantId, $date]);
        $day = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $viewsData[] = (int)($day['views'] ?? 0);
        $clicksData[] = (int)($day['clicks'] ?? 0);
    }
    
    return ['labels' => $labels, 'views' => $viewsData, 'clicks' => $clicksData];
}

private function getRestaurantHourlyStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT HOUR(created_at) as hour, COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY HOUR(created_at)
    ");
    $stmt->execute([$restaurantId, $startDate]);
    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    $hours = array_fill(0, 24, 0);
    foreach ($results as $row) {
        $hours[(int)$row['hour']] = (int)$row['count'];
    }
    return $hours;
}

private function getRestaurantActionsStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT event_type, COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND created_at >= ?
        GROUP BY event_type ORDER BY count DESC
    ");
    $stmt->execute([$restaurantId, $startDate]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getRestaurantTrafficSources(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT 
            CASE 
                WHEN referer IS NULL OR referer = '' THEN 'Direct'
                WHEN referer LIKE '%google%' THEN 'Google'
                WHEN referer LIKE '%facebook%' THEN 'Facebook'
                WHEN referer LIKE '%instagram%' THEN 'Instagram'
                WHEN referer LIKE '%lebonresto%' OR referer LIKE '%localhost%' THEN 'Interne'
                ELSE 'Autre'
            END as source,
            COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY source ORDER BY count DESC
    ");
    $stmt->execute([$restaurantId, $startDate]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getRestaurantDeviceStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT COALESCE(device_type, 'unknown') as device, COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY device_type ORDER BY count DESC
    ");
    $stmt->execute([$restaurantId, $startDate]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getRestaurantRecentActivity(int $restaurantId, int $limit): array
{
    $stmt = $this->db->prepare("
        SELECT event_type, device_type, referer, created_at
        FROM analytics_events 
        WHERE restaurant_id = ?
        ORDER BY created_at DESC LIMIT ?
    ");
    $stmt->execute([$restaurantId, $limit]);
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getRestaurantWeekdayStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT DAYOFWEEK(created_at) as day_num, COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY DAYOFWEEK(created_at)
    ");
    $stmt->execute([$restaurantId, $startDate]);
    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    $days = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    $stats = array_fill(0, 7, 0);
    foreach ($results as $row) {
        $stats[(int)$row['day_num'] - 1] = (int)$row['count'];
    }
    return ['labels' => $days, 'data' => $stats];
}
}