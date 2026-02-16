<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;
use App\Models\Review;

/**
 * Controller pour la gestion du profil utilisateur
 */
class UserController extends Controller
{
    private User $userModel;
    private Review $reviewModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->reviewModel = new Review();
    }
    /**
 * Dashboard propriétaire - Statistiques de mes restaurants
 */
public function dashboard(Request $request): void
{
    if (!$this->isAuthenticated()) {
        $this->redirect('/login?redirect=/dashboard');
        return;
    }
    
    $userId = $_SESSION['user']['id'];
    $period = (int)($request->get('period') ?? 30);
    
    // Récupérer les restaurants de l'utilisateur
    $stmt = $this->db->prepare("
        SELECT id, nom, slug, ville, type_cuisine, status, 
               note_moyenne, nb_avis, vues_total, created_at
        FROM restaurants 
        WHERE owner_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId]);
    $myRestaurants = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    if (empty($myRestaurants)) {
        $this->render('user/dashboard', [
            'title' => 'Mon Dashboard',
            'noFooter' => true,
            'hasRestaurants' => false,
            'period' => $period
        ]);
        return;
    }
    
    $restaurantIds = array_column($myRestaurants, 'id');

    // Stats globales agrégées
    $globalStats = $this->getGlobalAnalytics($restaurantIds, $period);

    // Stats détaillées par restaurant
    $restaurantsStats = $this->getRestaurantsDetailedStats($restaurantIds, $period);

    // Données pour les graphiques
    $chartData = $this->getChartData($restaurantIds, $period);

    // Top événements
    $topEvents = $this->getTopEvents($restaurantIds, $period);

    // Sources de trafic
    $trafficSources = $this->getTrafficSources($restaurantIds, $period);

    // Appareils
    $deviceStats = $this->getDeviceStats($restaurantIds, $period);

    // === Business data (orders, reservations, recent reviews) ===
    $startDate = date('Y-m-d', strtotime("-{$period} days"));

    // Recent reviews (last 5)
    $namedPh = [];
    $params = [];
    foreach (array_values($restaurantIds) as $i => $rid) {
        $key = ':rid' . $i;
        $namedPh[] = $key;
        $params[$key] = (int)$rid;
    }
    $inClause = implode(',', $namedPh);

    // Recent reviews (10)
    $recentReviewsStmt = $this->db->prepare("
        SELECT rv.id, rv.note_globale, rv.message, rv.created_at, rv.owner_response,
               u.prenom, u.nom AS user_nom,
               r.nom AS resto_nom, r.id AS restaurant_id
        FROM reviews rv
        JOIN users u ON u.id = rv.user_id
        JOIN restaurants r ON r.id = rv.restaurant_id
        WHERE rv.restaurant_id IN ($inClause) AND rv.status = 'approved'
        ORDER BY rv.created_at DESC
        LIMIT 10
    ");
    $recentReviewsStmt->execute($params);
    $recentReviews = $recentReviewsStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Reviews distribution (1-5 stars) + response rate
    $distStmt = $this->db->prepare("
        SELECT note_globale, COUNT(*) AS cnt,
               SUM(CASE WHEN owner_response IS NOT NULL AND owner_response != '' THEN 1 ELSE 0 END) AS responded
        FROM reviews
        WHERE restaurant_id IN ($inClause) AND status = 'approved'
        GROUP BY note_globale
    ");
    $distStmt->execute($params);
    $distRows = $distStmt->fetchAll(\PDO::FETCH_ASSOC);
    $reviewsDist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
    $totalReviewsCount = 0;
    $totalResponded = 0;
    foreach ($distRows as $dr) {
        $star = max(1, min(5, (int)round((float)$dr['note_globale'])));
        $reviewsDist[$star] += (int)$dr['cnt'];
        $totalReviewsCount += (int)$dr['cnt'];
        $totalResponded += (int)$dr['responded'];
    }
    $responseRate = $totalReviewsCount > 0 ? round(($totalResponded / $totalReviewsCount) * 100) : 0;

    // Reviews to respond (10, no owner_response)
    $toRespondStmt = $this->db->prepare("
        SELECT rv.id, rv.note_globale, rv.message, rv.created_at,
               u.prenom, u.nom AS user_nom,
               r.nom AS resto_nom, r.id AS restaurant_id
        FROM reviews rv
        JOIN users u ON u.id = rv.user_id
        JOIN restaurants r ON r.id = rv.restaurant_id
        WHERE rv.restaurant_id IN ($inClause) AND rv.status = 'approved'
              AND (rv.owner_response IS NULL OR rv.owner_response = '')
        ORDER BY rv.created_at DESC
        LIMIT 10
    ");
    $toRespondStmt->execute($params);
    $reviewsToRespond = $toRespondStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Average rating
    $avgRating = 0;
    if ($totalReviewsCount > 0) {
        $sum = 0;
        foreach ($reviewsDist as $star => $cnt) {
            $sum += $star * $cnt;
        }
        $avgRating = round($sum / $totalReviewsCount, 1);
    }

    // Orders stats
    $ordersStats = ['total' => 0, 'revenue' => 0, 'pending' => 0, 'today' => 0];
    $recentOrders = [];
    $ordersByStatus = [];
    $ordersChartData = [];
    $topItems = [];
    $avgOrderValue = 0;
    try {
        $ordParams = $params;
        $ordParams[':start_date'] = $startDate;

        $ordStmt = $this->db->prepare("
            SELECT COUNT(*) AS total,
                   COALESCE(SUM(grand_total), 0) AS revenue,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                   SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today
            FROM orders
            WHERE restaurant_id IN ($inClause) AND created_at >= :start_date
        ");
        $ordStmt->execute($ordParams);
        $ordersStats = $ordStmt->fetch(\PDO::FETCH_ASSOC) ?: $ordersStats;

        // Avg order value
        $avgOrdStmt = $this->db->prepare("
            SELECT COALESCE(AVG(grand_total), 0) AS avg_val
            FROM orders
            WHERE restaurant_id IN ($inClause) AND created_at >= :start_date
                  AND status NOT IN ('cancelled','refused')
        ");
        $avgOrdStmt->execute($ordParams);
        $avgOrderValue = round((float)$avgOrdStmt->fetchColumn(), 0);

        // Recent orders (10)
        $recentOrdStmt = $this->db->prepare("
            SELECT o.id, o.status, o.grand_total, o.order_type, o.created_at,
                   o.client_name, o.client_phone, r.nom AS resto_nom
            FROM orders o
            JOIN restaurants r ON r.id = o.restaurant_id
            WHERE o.restaurant_id IN ($inClause)
            ORDER BY o.created_at DESC
            LIMIT 10
        ");
        $recentOrdStmt->execute($params);
        $recentOrders = $recentOrdStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Orders by status
        $obsStmt = $this->db->prepare("
            SELECT status, COUNT(*) AS cnt
            FROM orders
            WHERE restaurant_id IN ($inClause) AND created_at >= :start_date
            GROUP BY status
        ");
        $obsStmt->execute($ordParams);
        $ordersByStatus = $obsStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Orders per day chart
        $ocdStmt = $this->db->prepare("
            SELECT DATE(created_at) AS d, COUNT(*) AS cnt, COALESCE(SUM(grand_total), 0) AS revenue
            FROM orders
            WHERE restaurant_id IN ($inClause) AND created_at >= :start_date
            GROUP BY d ORDER BY d
        ");
        $ocdStmt->execute($ordParams);
        $ordersChartData = $ocdStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Top ordered items
        $tiStmt = $this->db->prepare("
            SELECT oi.item_name, SUM(oi.quantity) AS total_qty,
                   SUM(oi.item_price * oi.quantity) AS total_revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE o.restaurant_id IN ($inClause) AND o.created_at >= :start_date
            GROUP BY oi.item_name ORDER BY total_qty DESC LIMIT 5
        ");
        $tiStmt->execute($ordParams);
        $topItems = $tiStmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        // orders table may not exist
    }

    // Reservations stats
    $reservationsStats = ['total' => 0, 'pending' => 0, 'accepted' => 0];
    $recentReservations = [];
    try {
        $resParams = $params;
        $resParams[':start_date'] = $startDate;

        $resStmt = $this->db->prepare("
            SELECT COUNT(*) AS total,
                   SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                   SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted
            FROM reservations
            WHERE restaurant_id IN ($inClause) AND created_at >= :start_date
        ");
        $resStmt->execute($resParams);
        $reservationsStats = $resStmt->fetch(\PDO::FETCH_ASSOC) ?: $reservationsStats;

        // Recent reservations (5)
        $rrStmt = $this->db->prepare("
            SELECT res.id, res.date_souhaitee, res.heure, res.nb_personnes, res.status,
                   res.created_at, u.prenom, u.nom AS user_nom, u.telephone,
                   r.nom AS resto_nom
            FROM reservations res
            JOIN users u ON u.id = res.user_id
            JOIN restaurants r ON r.id = res.restaurant_id
            WHERE res.restaurant_id IN ($inClause)
            ORDER BY res.created_at DESC LIMIT 5
        ");
        $rrStmt->execute($params);
        $recentReservations = $rrStmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}

    // Unread messages count + recent messages
    $unreadMsgStmt = $this->db->prepare("
        SELECT COUNT(*) FROM messages
        WHERE receiver_id = :uid AND is_read = 0 AND deleted_by_receiver = 0
    ");
    $unreadMsgStmt->execute([':uid' => $userId]);
    $unreadMessages = (int)$unreadMsgStmt->fetchColumn();

    $recentMsgStmt = $this->db->prepare("
        SELECT m.id, m.subject, m.body, m.created_at, m.is_read, m.sender_id,
               u.prenom, u.nom AS user_nom, u.photo_profil
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.receiver_id = :uid AND m.deleted_by_receiver = 0
        ORDER BY m.created_at DESC LIMIT 5
    ");
    $recentMsgStmt->execute([':uid' => $userId]);
    $recentMessages = $recentMsgStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Peak hours heatmap
    $peakParams = $params;
    $peakParams[':start_date'] = $startDate;
    $peakStmt = $this->db->prepare("
        SELECT DAYOFWEEK(created_at) AS dow, HOUR(created_at) AS h, COUNT(*) AS cnt
        FROM analytics_events
        WHERE event_type = 'view' AND restaurant_id IN ($inClause) AND created_at >= :start_date
        GROUP BY dow, h
    ");
    $peakStmt->execute($peakParams);
    $peakHoursRaw = $peakStmt->fetchAll(\PDO::FETCH_ASSOC);
    $peakHours = [];
    $peakMax = 1;
    foreach ($peakHoursRaw as $ph) {
        $peakHours[(int)$ph['dow']][(int)$ph['h']] = (int)$ph['cnt'];
        if ((int)$ph['cnt'] > $peakMax) $peakMax = (int)$ph['cnt'];
    }

    // Conversion funnel
    $funnelParams = $params;
    $funnelParams[':start_date'] = $startDate;
    $funnelStmt = $this->db->prepare("
        SELECT event_type, COUNT(*) AS cnt
        FROM analytics_events
        WHERE restaurant_id IN ($inClause) AND created_at >= :start_date
        GROUP BY event_type
    ");
    $funnelStmt->execute($funnelParams);
    $funnelRows = $funnelStmt->fetchAll(\PDO::FETCH_ASSOC);
    $funnelViews = 0;
    $funnelInteractions = 0;
    $interactionTypes = ['click_phone','click_directions','click_website','click_menu','click_booking'];
    foreach ($funnelRows as $fr) {
        if ($fr['event_type'] === 'view') $funnelViews = (int)$fr['cnt'];
        if (in_array($fr['event_type'], $interactionTypes)) $funnelInteractions += (int)$fr['cnt'];
    }
    // Conversions = appels + commandes
    $funnelCalls = 0;
    foreach ($funnelRows as $fr) {
        if ($fr['event_type'] === 'click_phone') $funnelCalls += (int)$fr['cnt'];
    }
    $funnelConversions = $funnelCalls + (int)($ordersStats['total'] ?? 0);

    // Unanswered Q&A
    $unansweredQA = [];
    try {
        $qaStmt = $this->db->prepare("
            SELECT q.id, q.question, q.created_at, u.prenom,
                   r.nom AS resto_nom, r.id AS restaurant_id
            FROM restaurant_qa q
            JOIN users u ON u.id = q.user_id
            JOIN restaurants r ON r.id = q.restaurant_id
            WHERE q.restaurant_id IN ($inClause) AND q.status = 'active'
                  AND NOT EXISTS (
                      SELECT 1 FROM restaurant_qa_answers a
                      WHERE a.question_id = q.id AND a.is_owner_answer = 1
                  )
            ORDER BY q.created_at DESC LIMIT 5
        ");
        $qaStmt->execute($params);
        $unansweredQA = $qaStmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}

    // Recent posts
    $recentPosts = [];
    try {
        $rpStmt = $this->db->prepare("
            SELECT p.id, p.type, p.title, p.content, p.likes_count, p.created_at,
                   r.nom AS resto_nom, r.id AS restaurant_id
            FROM restaurant_posts p
            JOIN restaurants r ON r.id = p.restaurant_id
            WHERE p.restaurant_id IN ($inClause)
            ORDER BY p.created_at DESC LIMIT 5
        ");
        $rpStmt->execute($params);
        $recentPosts = $rpStmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}

    // Recent notifications
    $notifStmt = $this->db->prepare("
        SELECT id, type, title, message, data, read_at, created_at
        FROM notifications
        WHERE user_id = :uid
        ORDER BY created_at DESC LIMIT 5
    ");
    $notifStmt->execute([':uid' => $userId]);
    $recentNotifications = $notifStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Awards
    $awards = [];
    try {
        $awStmt = $this->db->prepare("
            SELECT a.award_type, a.award_year, a.city, a.cuisine_type, a.rank_position,
                   r.nom AS resto_nom
            FROM restaurant_awards a
            JOIN restaurants r ON r.id = a.restaurant_id
            WHERE a.restaurant_id IN ($inClause)
            ORDER BY a.award_year DESC
        ");
        $awStmt->execute($params);
        $awards = $awStmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}

    // Restaurant photos (main photo per restaurant)
    $photoStmt = $this->db->prepare("
        SELECT restaurant_id, path FROM restaurant_photos
        WHERE restaurant_id IN ($inClause) AND type = 'main'
    ");
    $photoStmt->execute($params);
    $photosMap = [];
    foreach ($photoStmt->fetchAll(\PDO::FETCH_ASSOC) as $ph) {
        $photosMap[(int)$ph['restaurant_id']] = $ph['path'];
    }

    // Weekday stats (views per day of week)
    $wdParams = $params;
    $wdParams[':start_date'] = $startDate;
    $wdStmt = $this->db->prepare("
        SELECT DAYOFWEEK(created_at) AS day_num, COUNT(*) AS cnt
        FROM analytics_events
        WHERE restaurant_id IN ($inClause) AND event_type = 'view' AND created_at >= :start_date
        GROUP BY day_num ORDER BY day_num
    ");
    $wdStmt->execute($wdParams);
    $wdRows = $wdStmt->fetchAll(\PDO::FETCH_ASSOC);
    $weekdayLabels = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    $weekdayData = array_fill(0, 7, 0);
    foreach ($wdRows as $wd) {
        $weekdayData[(int)$wd['day_num'] - 1] = (int)$wd['cnt'];
    }
    $weekdayStats = ['labels' => $weekdayLabels, 'data' => $weekdayData];

    // Recent activity (last 15 events across all restaurants)
    $raStmt = $this->db->prepare("
        SELECT ae.event_type, ae.device_type, ae.created_at, r.nom AS resto_nom
        FROM analytics_events ae
        JOIN restaurants r ON r.id = ae.restaurant_id
        WHERE ae.restaurant_id IN ($inClause)
        ORDER BY ae.created_at DESC LIMIT 15
    ");
    $raStmt->execute($params);
    $recentActivity = $raStmt->fetchAll(\PDO::FETCH_ASSOC);

    // Hourly stats (aggregated views by hour)
    $hrParams = $params;
    $hrParams[':start_date'] = $startDate;
    $hrStmt = $this->db->prepare("
        SELECT HOUR(created_at) AS h, COUNT(*) AS cnt
        FROM analytics_events
        WHERE restaurant_id IN ($inClause) AND event_type = 'view' AND created_at >= :start_date
        GROUP BY h ORDER BY h
    ");
    $hrStmt->execute($hrParams);
    $hrRows = $hrStmt->fetchAll(\PDO::FETCH_ASSOC);
    $hourlyData = array_fill(0, 24, 0);
    foreach ($hrRows as $hr) {
        $hourlyData[(int)$hr['h']] = (int)$hr['cnt'];
    }

    $this->render('user/dashboard', [
        'title' => 'Mon Dashboard',
        'noFooter' => true,
        'hasRestaurants' => true,
        'myRestaurants' => $myRestaurants,
        'globalStats' => $globalStats,
        'restaurantsStats' => $restaurantsStats,
        'chartData' => $chartData,
        'topEvents' => $topEvents,
        'trafficSources' => $trafficSources,
        'deviceStats' => $deviceStats,
        'period' => $period,
        'recentReviews' => $recentReviews,
        'reviewsDist' => $reviewsDist,
        'totalReviewsCount' => $totalReviewsCount,
        'responseRate' => $responseRate,
        'totalResponded' => $totalResponded,
        'reviewsToRespond' => $reviewsToRespond,
        'avgRating' => $avgRating,
        'ordersStats' => $ordersStats,
        'recentOrders' => $recentOrders,
        'ordersByStatus' => $ordersByStatus,
        'ordersChartData' => $ordersChartData,
        'topItems' => $topItems,
        'avgOrderValue' => $avgOrderValue,
        'reservationsStats' => $reservationsStats,
        'recentReservations' => $recentReservations,
        'unreadMessages' => $unreadMessages,
        'recentMessages' => $recentMessages,
        'peakHours' => $peakHours,
        'peakMax' => $peakMax,
        'funnelViews' => $funnelViews,
        'funnelInteractions' => $funnelInteractions,
        'funnelConversions' => $funnelConversions,
        'unansweredQA' => $unansweredQA,
        'recentPosts' => $recentPosts,
        'recentNotifications' => $recentNotifications,
        'awards' => $awards,
        'photosMap' => $photosMap,
        'weekdayStats' => $weekdayStats,
        'recentActivity' => $recentActivity,
        'hourlyData' => $hourlyData,
    ]);
}

/**
 * Stats détaillées d'un restaurant
 */
public function restaurantStats(Request $request): void
{
    if (!$this->isAuthenticated()) {
        $this->redirect('/login?redirect=/dashboard');
        return;
    }
    
    $userId = $_SESSION['user']['id'];
    $restaurantId = (int) $request->param('id');
    $period = (int)($request->get('period') ?? 30);
    
    // Vérifier que le restaurant appartient à l'utilisateur
    $stmt = $this->db->prepare("
        SELECT id, nom, slug, ville, type_cuisine, status, phone, website,
               note_moyenne, nb_avis, vues_total, created_at
        FROM restaurants 
        WHERE id = ? AND owner_id = ?
    ");
    $stmt->execute([$restaurantId, $userId]);
    $restaurant = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$restaurant) {
        $this->redirect('/dashboard');
        return;
    }
    
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    $previousStart = date('Y-m-d', strtotime("-" . ($period * 2) . " days"));
    
    // Stats principales
    $mainStats = $this->getRestaurantMainStats($restaurantId, $startDate, $previousStart);
    
    // Graphique évolution journalière
    $chartData = $this->getRestaurantChartData($restaurantId, min($period, 30));
    
    // Stats par heure (heures populaires)
    $hourlyStats = $this->getHourlyStats($restaurantId, $startDate);
    
    // Toutes les actions détaillées
    $actionsStats = $this->getActionsStats($restaurantId, $startDate);
    
    // Sources de trafic
    $trafficSources = $this->getRestaurantTrafficSources($restaurantId, $startDate);
    
    // Appareils
    $deviceStats = $this->getRestaurantDeviceStats($restaurantId, $startDate);
    
    // Dernières activités
    $recentActivity = $this->getRecentActivity($restaurantId, 20);
    
    // Stats par jour de la semaine
    $weekdayStats = $this->getWeekdayStats($restaurantId, $startDate);
    
    $this->render('user/restaurant-stats', [
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

private function getRestaurantMainStats(int $restaurantId, string $startDate, string $previousStart): array
{
    // Période actuelle
    $stmt = $this->db->prepare("
        SELECT 
            COUNT(*) as total_events,
            SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
            COUNT(DISTINCT session_id) as unique_visitors,
            COUNT(DISTINCT DATE(created_at)) as active_days
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
            SUM(CASE WHEN event_type = 'gallery_open' THEN 1 ELSE 0 END) as gallery_opens,
            SUM(CASE WHEN event_type = 'review_submitted' THEN 1 ELSE 0 END) as reviews
        FROM analytics_events 
        WHERE restaurant_id = ? AND created_at >= ?
    ");
    $stmt->execute([$restaurantId, $startDate]);
    $actions = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    $totalInteractions = ($actions['clicks_phone'] ?? 0) + ($actions['clicks_directions'] ?? 0) + 
                        ($actions['clicks_website'] ?? 0) + ($actions['clicks_menu'] ?? 0);
    
    $conversionRate = ($current['views'] ?? 0) > 0 
        ? round(($totalInteractions / $current['views']) * 100, 1) : 0;
    
    $avgViewsPerDay = ($current['active_days'] ?? 0) > 0 
        ? round($current['views'] / $current['active_days'], 1) : 0;
    
    return [
        'views' => $current['views'] ?? 0,
        'unique_visitors' => $current['unique_visitors'] ?? 0,
        'views_trend' => $this->calculateTrend($current['views'] ?? 0, $previous['views'] ?? 0),
        'total_interactions' => $totalInteractions,
        'conversion_rate' => $conversionRate,
        'avg_views_per_day' => $avgViewsPerDay,
        'actions' => $actions
    ];
}

private function getRestaurantChartData(int $restaurantId, int $days): array
{
    $startDate = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));

    // Une seule requête GROUP BY DATE au lieu d'une boucle par jour
    $stmt = $this->db->prepare("
        SELECT
            DATE(created_at) as day,
            SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
            COUNT(DISTINCT CASE WHEN event_type = 'view' THEN session_id END) as visitors,
            SUM(CASE WHEN event_type IN ('click_phone', 'click_directions', 'click_website') THEN 1 ELSE 0 END) as clicks
        FROM analytics_events
        WHERE restaurant_id = ? AND DATE(created_at) >= ?
        GROUP BY DATE(created_at)
        ORDER BY day
    ");
    $stmt->execute([$restaurantId, $startDate]);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    // Indexer par date
    $dataByDate = [];
    foreach ($rows as $row) {
        $dataByDate[$row['day']] = $row;
    }

    // Remplir chaque jour (même ceux sans données)
    $labels = [];
    $viewsData = [];
    $visitorsData = [];
    $clicksData = [];

    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('d/m', strtotime($date));
        $day = $dataByDate[$date] ?? null;
        $viewsData[] = (int)($day['views'] ?? 0);
        $visitorsData[] = (int)($day['visitors'] ?? 0);
        $clicksData[] = (int)($day['clicks'] ?? 0);
    }

    return [
        'labels' => $labels,
        'views' => $viewsData,
        'visitors' => $visitorsData,
        'clicks' => $clicksData
    ];
}

private function getHourlyStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT 
            HOUR(created_at) as hour,
            COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY HOUR(created_at)
        ORDER BY hour
    ");
    $stmt->execute([$restaurantId, $startDate]);
    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    // Remplir les 24 heures
    $hours = array_fill(0, 24, 0);
    foreach ($results as $row) {
        $hours[(int)$row['hour']] = (int)$row['count'];
    }
    
    return $hours;
}

private function getActionsStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT event_type, COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND created_at >= ?
        GROUP BY event_type
        ORDER BY count DESC
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
                WHEN referer LIKE '%lebonresto%' OR referer LIKE '%localhost%' THEN 'Recherche interne'
                ELSE 'Autre'
            END as source,
            COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY source
        ORDER BY count DESC
    ");
    $stmt->execute([$restaurantId, $startDate]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getRestaurantDeviceStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT 
            COALESCE(device_type, 'unknown') as device,
            COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY device_type
        ORDER BY count DESC
    ");
    $stmt->execute([$restaurantId, $startDate]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getRecentActivity(int $restaurantId, int $limit = 20): array
{
    $stmt = $this->db->prepare("
        SELECT event_type, device_type, referer, created_at
        FROM analytics_events 
        WHERE restaurant_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$restaurantId, $limit]);
    
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getWeekdayStats(int $restaurantId, string $startDate): array
{
    $stmt = $this->db->prepare("
        SELECT 
            DAYOFWEEK(created_at) as day_num,
            COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id = ? AND event_type = 'view' AND created_at >= ?
        GROUP BY DAYOFWEEK(created_at)
        ORDER BY day_num
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

private function getGlobalAnalytics(array $restaurantIds, int $period): array
{
    $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    $previousStart = date('Y-m-d', strtotime("-" . ($period * 2) . " days"));
    
    // Vues période actuelle
    $stmt = $this->db->prepare("
        SELECT COUNT(*) as total_views, COUNT(DISTINCT session_id) as unique_visitors
        FROM analytics_events 
        WHERE restaurant_id IN ({$placeholders}) AND event_type = 'view' AND created_at >= ?
    ");
    $stmt->execute(array_merge($restaurantIds, [$startDate]));
    $current = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    // Vues période précédente
    $stmt = $this->db->prepare("
        SELECT COUNT(*) as total_views
        FROM analytics_events 
        WHERE restaurant_id IN ({$placeholders}) AND event_type = 'view' AND created_at >= ? AND created_at < ?
    ");
    $stmt->execute(array_merge($restaurantIds, [$previousStart, $startDate]));
    $previous = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    // Clics actions
    $stmt = $this->db->prepare("
        SELECT 
            SUM(CASE WHEN event_type = 'click_phone' THEN 1 ELSE 0 END) as clicks_phone,
            SUM(CASE WHEN event_type = 'click_directions' THEN 1 ELSE 0 END) as clicks_directions,
            SUM(CASE WHEN event_type = 'click_website' THEN 1 ELSE 0 END) as clicks_website,
            SUM(CASE WHEN event_type = 'click_menu' THEN 1 ELSE 0 END) as clicks_menu,
            SUM(CASE WHEN event_type = 'wishlist_add' THEN 1 ELSE 0 END) as wishlist_adds,
            SUM(CASE WHEN event_type = 'share' THEN 1 ELSE 0 END) as shares
        FROM analytics_events 
        WHERE restaurant_id IN ({$placeholders}) AND created_at >= ?
    ");
    $stmt->execute(array_merge($restaurantIds, [$startDate]));
    $actions = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    $viewsTrend = $this->calculateTrend($current['total_views'] ?? 0, $previous['total_views'] ?? 0);
    $totalInteractions = ($actions['clicks_phone'] ?? 0) + ($actions['clicks_directions'] ?? 0) + 
                       ($actions['clicks_website'] ?? 0) + ($actions['clicks_menu'] ?? 0);
    $conversionRate = ($current['total_views'] ?? 0) > 0 
        ? round(($totalInteractions / $current['total_views']) * 100, 1) : 0;
    
    return [
        'total_views' => $current['total_views'] ?? 0,
        'unique_visitors' => $current['unique_visitors'] ?? 0,
        'views_trend' => $viewsTrend,
        'clicks_phone' => $actions['clicks_phone'] ?? 0,
        'clicks_directions' => $actions['clicks_directions'] ?? 0,
        'clicks_website' => $actions['clicks_website'] ?? 0,
        'clicks_menu' => $actions['clicks_menu'] ?? 0,
        'wishlist_adds' => $actions['wishlist_adds'] ?? 0,
        'shares' => $actions['shares'] ?? 0,
        'total_interactions' => $totalInteractions,
        'conversion_rate' => $conversionRate
    ];
}

private function getRestaurantsDetailedStats(array $restaurantIds, int $period): array
{
    if (empty($restaurantIds)) {
        return [];
    }

    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    $placeholders = [];
    $params = [':start_date' => $startDate];
    foreach ($restaurantIds as $i => $id) {
        $key = ':rid_' . $i;
        $params[$key] = (int)$id;
        $placeholders[] = $key;
    }
    $inClause = implode(',', $placeholders);

    $stmt = $this->db->prepare("
        SELECT restaurant_id,
               COUNT(*) as views,
               COUNT(DISTINCT session_id) as unique_views,
               SUM(CASE WHEN event_type = 'click_phone' THEN 1 ELSE 0 END) as clicks_phone,
               SUM(CASE WHEN event_type = 'click_directions' THEN 1 ELSE 0 END) as clicks_directions
        FROM analytics_events
        WHERE restaurant_id IN ({$inClause}) AND created_at >= :start_date
        GROUP BY restaurant_id
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $stats = [];
    foreach ($rows as $row) {
        $stats[$row['restaurant_id']] = $row;
    }
    // Fill missing restaurants with zeros
    foreach ($restaurantIds as $id) {
        if (!isset($stats[$id])) {
            $stats[$id] = ['views' => 0, 'unique_views' => 0, 'clicks_phone' => 0, 'clicks_directions' => 0];
        }
    }
    return $stats;
}

private function getChartData(array $restaurantIds, int $days): array
{
    if (empty($restaurantIds)) {
        return ['labels' => [], 'views' => [], 'clicks' => []];
    }

    $placeholders = [];
    $params = [];
    foreach ($restaurantIds as $i => $id) {
        $key = ':rid_' . $i;
        $params[$key] = (int)$id;
        $placeholders[] = $key;
    }
    $inClause = implode(',', $placeholders);
    $startDate = date('Y-m-d', strtotime("-" . ($days - 1) . " days"));
    $params[':start_date'] = $startDate;

    $stmt = $this->db->prepare("
        SELECT DATE(created_at) as event_date,
               SUM(CASE WHEN event_type = 'view' THEN 1 ELSE 0 END) as views,
               SUM(CASE WHEN event_type IN ('click_phone', 'click_directions', 'click_website') THEN 1 ELSE 0 END) as clicks
        FROM analytics_events
        WHERE restaurant_id IN ({$inClause}) AND created_at >= :start_date
        GROUP BY DATE(created_at)
        ORDER BY event_date
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $dataByDate = [];
    foreach ($rows as $row) {
        $dataByDate[$row['event_date']] = $row;
    }

    $labels = []; $viewsData = []; $clicksData = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $labels[] = date('d/m', strtotime($date));
        $viewsData[] = (int)($dataByDate[$date]['views'] ?? 0);
        $clicksData[] = (int)($dataByDate[$date]['clicks'] ?? 0);
    }
    return ['labels' => $labels, 'views' => $viewsData, 'clicks' => $clicksData];
}

private function getTopEvents(array $restaurantIds, int $period): array
{
    $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    
    $stmt = $this->db->prepare("
        SELECT event_type, COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id IN ({$placeholders}) AND created_at >= ? AND event_type != 'view'
        GROUP BY event_type ORDER BY count DESC LIMIT 10
    ");
    $stmt->execute(array_merge($restaurantIds, [$startDate]));
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getTrafficSources(array $restaurantIds, int $period): array
{
    $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    
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
        WHERE restaurant_id IN ({$placeholders}) AND event_type = 'view' AND created_at >= ?
        GROUP BY source ORDER BY count DESC
    ");
    $stmt->execute(array_merge($restaurantIds, [$startDate]));
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function getDeviceStats(array $restaurantIds, int $period): array
{
    $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
    $startDate = date('Y-m-d', strtotime("-{$period} days"));
    
    $stmt = $this->db->prepare("
        SELECT COALESCE(device_type, 'unknown') as device, COUNT(*) as count
        FROM analytics_events 
        WHERE restaurant_id IN ({$placeholders}) AND event_type = 'view' AND created_at >= ?
        GROUP BY device_type ORDER BY count DESC
    ");
    $stmt->execute(array_merge($restaurantIds, [$startDate]));
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}

private function calculateTrend(int $current, int $previous): array
{
    if ($previous == 0) {
        return ['value' => $current > 0 ? 100 : 0, 'direction' => $current > 0 ? 'up' : 'neutral'];
    }
    $percent = round((($current - $previous) / $previous) * 100, 1);
    return ['value' => abs($percent), 'direction' => $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'neutral')];
}
    /**
     * Profil public d'un utilisateur
     * GET /user/{id}
     */
    public function publicProfile(Request $request): void
    {
        $userId = (int)$request->param('id');

        if (!$userId) {
            http_response_code(404);
            echo "Utilisateur non trouvé";
            return;
        }

        $user = $this->userModel->find($userId);

        if (!$user) {
            http_response_code(404);
            echo "Utilisateur non trouvé";
            return;
        }

        // Stats publiques
        $stats = $this->userModel->getUserStats($userId);

        // Avis publics de cet utilisateur
        $stmt = $this->db->prepare("
            SELECT rev.*, r.nom as restaurant_nom, r.slug as restaurant_slug, r.ville as restaurant_ville,
                   r.type_cuisine as restaurant_type
            FROM reviews rev
            INNER JOIN restaurants r ON r.id = rev.restaurant_id
            WHERE rev.user_id = ? AND rev.status = 'approved'
            ORDER BY rev.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$userId]);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Badges/niveau fidélité
        $loyaltyInfo = null;
        try {
            $loyaltyStmt = $this->db->prepare("
                SELECT u.points, u.badge, b.icon as badge_icon, b.color as badge_color
                FROM users u
                LEFT JOIN badges b ON b.name = u.badge
                WHERE u.id = ?
            ");
            $loyaltyStmt->execute([$userId]);
            $loyaltyInfo = $loyaltyStmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Fallback
        }

        // Nombre de votes utiles reçus
        $votesStmt = $this->db->prepare("
            SELECT COALESCE(SUM(rev.votes_utiles), 0) as total_helpful
            FROM reviews rev
            WHERE rev.user_id = ? AND rev.status = 'approved'
        ");
        $votesStmt->execute([$userId]);
        $totalHelpful = (int)$votesStmt->fetchColumn();

        // Titres personnalisés
        $userTitles = [];
        try {
            $titlesStmt = $this->db->prepare("
                SELECT * FROM user_titles WHERE user_id = ? AND is_active = 1 ORDER BY earned_at DESC
            ");
            $titlesStmt->execute([$userId]);
            $userTitles = $titlesStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            // Table might not exist
        }

        $this->render('user/public-profile', [
            'title' => $user['prenom'] . ' ' . substr($user['nom'], 0, 1) . '. - Profil',
            'profileUser' => $user,
            'stats' => $stats,
            'reviews' => $reviews,
            'loyaltyInfo' => $loyaltyInfo,
            'totalHelpful' => $totalHelpful,
            'userTitles' => $userTitles
        ]);
    }

    /**
     * Affiche le profil de l'utilisateur
     */
    public function profile(Request $request): void
    {
        // Vérifier authentification
        if (!$this->isAuthenticated()) {
            $this->redirect('/login?redirect=/profil');
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        
        // Récupérer les infos complètes de l'user
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            $this->redirect('/logout');
            return;
        }
        
        // Récupérer les statistiques
        $stats = $this->userModel->getUserStats($userId);
        
        // Récupérer les avis de l'user
        $reviews = $this->reviewModel->getUserReviews($userId);
        
        $data = [
            'title' => 'Mon profil - ' . $user['prenom'] . ' ' . $user['nom'],
            'user' => $user,
            'stats' => $stats,
            'reviews' => $reviews
        ];
        
        $this->render('user/profile', $data);
    }
    
    /**
     * Met à jour le profil
     */
    public function updateProfile(Request $request): void
    {
        // Vérifier authentification
        if (!$this->isAuthenticated()) {
            $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        
        // Récupérer les données
        $prenom = trim($request->post('prenom'));
        $nom = trim($request->post('nom'));
        $email = trim($request->post('email'));
        $genre = $request->post('genre');
        
        // Validation
        if (empty($prenom) || empty($nom) || empty($email)) {
            $this->json(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
            return;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Email invalide'], 400);
            return;
        }
        
        // Vérifier si l'email existe déjà (pour un autre user)
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser && $existingUser['id'] != $userId) {
            $this->json(['success' => false, 'message' => 'Cet email est déjà utilisé'], 400);
            return;
        }
        
        // Mettre à jour
        $updated = $this->userModel->update($userId, [
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'genre' => $genre
        ]);
        
        if ($updated) {
            // Mettre à jour la session
            $_SESSION['user']['prenom'] = $prenom;
            $_SESSION['user']['nom'] = $nom;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['genre'] = $genre;
            
            $this->json(['success' => true, 'message' => 'Profil mis à jour avec succès']);
        } else {
            $this->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
        }
    }
    
    /**
     * Change le mot de passe
     */
    public function updatePassword(Request $request): void
    {
        // Vérifier authentification
        if (!$this->isAuthenticated()) {
            $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
            return;
        }
        
        $userId = $_SESSION['user']['id'];
        
        // Récupérer les données
        $currentPassword = $request->post('current_password');
        $newPassword = $request->post('new_password');
        $confirmPassword = $request->post('confirm_password');
        
        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->json(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            $this->json(['success' => false, 'message' => 'Les mots de passe ne correspondent pas'], 400);
            return;
        }
        
        if (strlen($newPassword) < 8) {
            $this->json(['success' => false, 'message' => 'Le mot de passe doit faire au moins 8 caractères'], 400);
            return;
        }
        
        // Vérifier l'ancien mot de passe
        $user = $this->userModel->find($userId);
        
        if (!$this->userModel->verifyPassword($currentPassword, $user['password_hash'])) {
            $this->json(['success' => false, 'message' => 'Mot de passe actuel incorrect'], 400);
            return;
        }
        
        // Hasher le nouveau mot de passe
        $newHash = $this->userModel->hashPassword($newPassword);
        
        // Mettre à jour
        $updated = $this->userModel->update($userId, [
            'password_hash' => $newHash
        ]);
        
        if ($updated) {
            $this->json(['success' => true, 'message' => 'Mot de passe changé avec succès']);
        } else {
            $this->json(['success' => false, 'message' => 'Erreur lors du changement de mot de passe'], 500);
        }
    }
    private function timeAgo(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 604800) return floor($diff / 86400) . ' j';

    return date('d/m', $time);
}

    // ═══════════════════════════════════════════════════════════════
    // PHASE 6 — ESPACE PROPRIETAIRE
    // ═══════════════════════════════════════════════════════════════

    /**
     * Page "Revendiquer ce restaurant"
     */
    public function claimRestaurant(Request $request): void
    {
        $this->requireAuth();
        $restaurantId = (int) $request->param('id');

        $stmt = $this->db->prepare("SELECT id, nom, ville, adresse FROM restaurants WHERE id = ? AND status = 'validated'");
        $stmt->execute([$restaurantId]);
        $restaurant = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$restaurant) {
            $this->redirect('/restaurants');
            return;
        }

        // Vérifier si déjà revendiqué
        $stmt = $this->db->prepare("SELECT id, status FROM restaurant_claims WHERE restaurant_id = ? AND status IN ('pending', 'approved')");
        $stmt->execute([$restaurantId]);
        $existingClaim = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->render('user/claim-restaurant', [
            'title' => 'Revendiquer ' . $restaurant['nom'],
            'restaurant' => $restaurant,
            'existingClaim' => $existingClaim
        ]);
    }

    /**
     * Soumettre la revendication
     */
    public function doClaimRestaurant(Request $request): void
    {
        $this->requireAuth();
        $restaurantId = (int) $request->param('id');
        $userId = $_SESSION['user']['id'];

        $stmt = $this->db->prepare("SELECT id FROM restaurants WHERE id = ? AND status = 'validated'");
        $stmt->execute([$restaurantId]);
        if (!$stmt->fetch()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Restaurant non trouvé'];
            $this->redirect('/restaurants');
            return;
        }

        // Vérifier si déjà revendiqué par cet utilisateur
        $stmt = $this->db->prepare("SELECT id FROM restaurant_claims WHERE restaurant_id = ? AND user_id = ? AND status IN ('pending', 'approved')");
        $stmt->execute([$restaurantId, $userId]);
        if ($stmt->fetch()) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vous avez déjà soumis une demande pour ce restaurant'];
            $this->redirect('/restaurant/' . $restaurantId);
            return;
        }

        $emailPro = trim($request->post('email_pro') ?? '');
        $phone = trim($request->post('phone') ?? '');
        $message = trim($request->post('message') ?? '');

        if (empty($emailPro) || empty($phone)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Email professionnel et téléphone requis'];
            $this->redirect('/restaurant/' . $restaurantId . '/claim');
            return;
        }

        // Upload preuve
        $proofPath = null;
        if (!empty($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'application/pdf'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['proof']['tmp_name']);

            if (in_array($mime, $allowed) && $_FILES['proof']['size'] <= 5 * 1024 * 1024) {
                $ext = $mime === 'application/pdf' ? 'pdf' : ($mime === 'image/png' ? 'png' : 'jpg');
                $filename = 'claim_' . $restaurantId . '_' . $userId . '_' . time() . '.' . $ext;
                $uploadDir = ROOT_PATH . '/public/uploads/claims/';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                move_uploaded_file($_FILES['proof']['tmp_name'], $uploadDir . $filename);
                $proofPath = '/uploads/claims/' . $filename;
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO restaurant_claims (restaurant_id, user_id, email_pro, phone, message, proof_path)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$restaurantId, $userId, $emailPro, $phone, $message, $proofPath]);

        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Votre demande a été soumise. Un administrateur la vérifiera sous 48h.'];
        $this->redirect('/restaurant/' . $restaurantId);
    }

    /**
     * API — Derniers avis sur mes restaurants (dashboard proprio)
     */
    public function apiMyRestaurantReviews(Request $request): void
    {
        header('Content-Type: application/json');
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false]);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $stmt = $this->db->prepare("
            SELECT rev.id, rev.note_globale, rev.message, rev.created_at, rev.owner_response,
                   r.nom as restaurant_nom, r.id as restaurant_id,
                   u.prenom as user_prenom, u.nom as user_nom
            FROM reviews rev
            INNER JOIN restaurants r ON r.id = rev.restaurant_id
            LEFT JOIN users u ON u.id = rev.user_id
            WHERE r.owner_id = ? AND rev.status = 'approved'
            ORDER BY rev.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'reviews' => $reviews]);
    }

    /**
     * API — Notifications
     */
    public function apiNotifications(Request $request): void
    {
        header('Content-Type: application/json');
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false]);
            return;
        }

        $userId = $_SESSION['user']['id'];
        $stmt = $this->db->prepare("
            SELECT id, type, title, message, data, read_at, created_at
            FROM notifications WHERE user_id = ?
            ORDER BY created_at DESC LIMIT 20
        ");
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $unreadCount = 0;
        foreach ($notifications as &$n) {
            if ($n['read_at'] === null) $unreadCount++;
            $n['data'] = $n['data'] ? json_decode($n['data'], true) : null;
        }

        echo json_encode(['success' => true, 'notifications' => $notifications, 'unread_count' => $unreadCount]);
    }

    /**
     * API — Marquer les notifications comme lues
     */
    public function apiMarkNotificationsRead(Request $request): void
    {
        header('Content-Type: application/json');
        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false]);
            return;
        }

        $this->db->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL")
                 ->execute([$_SESSION['user']['id']]);

        echo json_encode(['success' => true]);
    }

    /**
     * Suppression de compte (RGPD — droit à l'oubli)
     */
    public function deleteAccount(Request $request): void
    {
        $this->requireAuth();
        $userId = $_SESSION['user']['id'];

        $password = $request->post('password');
        if (empty($password)) {
            $this->json(['success' => false, 'message' => 'Mot de passe requis pour confirmer la suppression'], 400);
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user || !$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->json(['success' => false, 'message' => 'Mot de passe incorrect'], 400);
            return;
        }

        try {
            $this->db->beginTransaction();

            // Anonymiser les avis (garder le contenu mais retirer l'auteur)
            $this->db->prepare("UPDATE reviews SET user_id = NULL WHERE user_id = ?")->execute([$userId]);

            // Supprimer les données personnelles liées
            $this->db->prepare("DELETE FROM wishlist WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM review_votes WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM user_loyalty WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM loyalty_history WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM email_verifications WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM restaurant_qa_answers WHERE user_id = ?")->execute([$userId]);
            $this->db->prepare("DELETE FROM restaurant_qa WHERE user_id = ?")->execute([$userId]);

            // Supprimer le compte
            $this->db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);

            $this->db->commit();

            // Détruire la session
            session_destroy();

            $this->json(['success' => true, 'message' => 'Votre compte a été supprimé. Vos avis ont été anonymisés.', 'redirect' => '/']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->json(['success' => false, 'message' => 'Erreur lors de la suppression'], 500);
        }
    }
}
