<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;

use App\Services\Logger;
/**
 * AnalyticsController
 * Gère le tracking des événements analytics
 */
class AnalyticsController extends Controller
{
    /**
     * Track un événement analytics
     * Route: POST /api/analytics/track
     * 
     * Body JSON attendu:
     * {
     *   "restaurant_id": 123,
     *   "event_type": "view",
     *   "session_id": "abc123...",
     *   "metadata": {},
     *   "referer": "https://...",
     *   "user_agent": "Mozilla/5.0...",
     *   "device_type": "mobile"
     * }
     */
    public function track(Request $request): void
    {
        header('Content-Type: application/json');
        
        // Récupérer les données JSON
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validation des champs obligatoires
        $restaurantId = (int)($data['restaurant_id'] ?? 0);
        $eventType = $data['event_type'] ?? null;
        $sessionId = $data['session_id'] ?? null;
        
        if (!$restaurantId || !$eventType || !$sessionId) {
            echo json_encode([
                'success' => false, 
                'error' => 'Missing required fields: restaurant_id, event_type, session_id'
            ]);
            return;
        }
        
        // Validation du type d'événement
        $validEvents = [
            'view', 'click_phone', 'click_directions', 'click_website', 
            'click_menu', 'click_booking', 'wishlist_add', 'wishlist_remove',
            'share', 'gallery_open', 'photo_view', 'search_impression',
            'search_click', 'review_form_open', 'review_submitted'
        ];
        
        if (!in_array($eventType, $validEvents)) {
            echo json_encode([
                'success' => false, 
                'error' => 'Invalid event_type'
            ]);
            return;
        }
        
        try {
            // Récupérer l'IP et user info
            $userId = $_SESSION['user_id'] ?? null;
            $ipAddress = $this->getClientIp();
            $userAgent = $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
            $deviceType = $data['device_type'] ?? $this->detectDeviceType($userAgent);
            $referer = $data['referer'] ?? $_SERVER['HTTP_REFERER'] ?? null;
            $pageUrl = $data['page_url'] ?? null;
            $metadata = isset($data['metadata']) ? json_encode($data['metadata']) : null;
            
            // 1. Insérer l'événement dans analytics_events
            $stmt = $this->db->prepare("
                INSERT INTO analytics_events 
                (restaurant_id, user_id, session_id, event_type, ip_address, 
                 referer, user_agent, device_type, page_url, metadata)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $restaurantId,
                $userId,
                $sessionId,
                $eventType,
                $ipAddress,
                $referer,
                $userAgent,
                $deviceType,
                $pageUrl,
                $metadata
            ]);
            
            // 2. Mettre à jour les compteurs agrégés
            $this->updateAggregatedStats($restaurantId, $eventType, $sessionId);
            
            echo json_encode([
                'success' => true,
                'event_id' => $this->db->lastInsertId()
            ]);
            
        } catch (\PDOException $e) {
            Logger::error("Analytics tracking error: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Database error'
            ]);
        } catch (\Exception $e) {
            Logger::error("Analytics tracking error: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Server error'
            ]);
        }
    }
    
    /**
     * Mettre à jour les statistiques agrégées
     */
    private function updateAggregatedStats(int $restaurantId, string $eventType, string $sessionId): void
    {
        // S'assurer que l'entrée existe dans restaurant_analytics
        $this->ensureAnalyticsEntry($restaurantId);
        
        // Mapping événement -> colonne
        $columnMap = [
            'view' => 'views_total',
            'click_phone' => 'clicks_phone',
            'click_directions' => 'clicks_directions',
            'click_website' => 'clicks_website',
            'click_menu' => 'clicks_menu',
            'click_booking' => 'clicks_booking',
            'wishlist_add' => 'wishlist_adds',
            'wishlist_remove' => 'wishlist_removes',
            'share' => 'shares',
            'gallery_open' => 'gallery_opens',
            'photo_view' => 'photo_views',
            'search_impression' => 'search_impressions',
            'search_click' => 'search_clicks',
            'review_form_open' => 'review_form_opens',
            'review_submitted' => 'reviews_submitted'
        ];
        
        $column = $columnMap[$eventType] ?? null;
        if (!$column) return;
        
        // Incrémenter le compteur
        $stmt = $this->db->prepare("
            UPDATE restaurant_analytics 
            SET {$column} = {$column} + 1,
                last_click_at = NOW()
            WHERE restaurant_id = ?
        ");
        $stmt->execute([$restaurantId]);
        
        // Gestion spéciale pour les vues (views_unique + last_view_at)
        if ($eventType === 'view') {
            $this->handleViewEvent($restaurantId, $sessionId);
        }
    }
    
    /**
     * Gérer un événement de vue (unique visitor tracking)
     */
    private function handleViewEvent(int $restaurantId, string $sessionId): void
    {
        // Vérifier si cette session a déjà vu ce restaurant dans la dernière heure
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM analytics_events 
            WHERE restaurant_id = ? 
            AND session_id = ? 
            AND event_type = 'view'
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute([$restaurantId, $sessionId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Si première vue de cette session dans l'heure = visiteur unique
        if ($result['count'] <= 1) {
            $stmt = $this->db->prepare("
                UPDATE restaurant_analytics 
                SET views_unique = views_unique + 1,
                    last_view_at = NOW()
                WHERE restaurant_id = ?
            ");
            $stmt->execute([$restaurantId]);
        } else {
            // Sinon, juste update last_view_at
            $stmt = $this->db->prepare("
                UPDATE restaurant_analytics 
                SET last_view_at = NOW()
                WHERE restaurant_id = ?
            ");
            $stmt->execute([$restaurantId]);
        }
    }
    
    /**
     * S'assurer qu'une entrée existe dans restaurant_analytics
     */
    private function ensureAnalyticsEntry(int $restaurantId): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO restaurant_analytics (restaurant_id) 
            VALUES (?)
        ");
        $stmt->execute([$restaurantId]);
    }
    
    /**
     * Récupérer l'IP réelle du client (gère les proxies)
     */
    private function getClientIp(): ?string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_FORWARDED_FOR',   // Proxies
            'HTTP_X_REAL_IP',         // Nginx
            'REMOTE_ADDR'             // Direct
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Si X-Forwarded-For contient plusieurs IPs, prendre la première
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Détecter le type d'appareil depuis le User-Agent
     */
    private function detectDeviceType(?string $userAgent): ?string
    {
        if (!$userAgent) return null;
        
        $userAgent = strtolower($userAgent);
        
        // Mobile
        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            return 'mobile';
        }
        
        // Tablet
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }
        
        // Desktop par défaut
        return 'desktop';
    }
    
    /**
     * API : Récupérer les stats d'un restaurant (pour dashboard propriétaire)
     * Route: GET /api/analytics/restaurant/{id}
     */
    public function getRestaurantStats(Request $request): void
    {
        header('Content-Type: application/json');
        
        $restaurantId = (int)$request->param('id');
        
        if (!$restaurantId) {
            echo json_encode(['success' => false, 'error' => 'Restaurant ID required']);
            return;
        }
        
        try {
            // Stats agrégées
            $stmt = $this->db->prepare("
                SELECT * FROM restaurant_analytics 
                WHERE restaurant_id = ?
            ");
            $stmt->execute([$restaurantId]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$stats) {
                // Créer l'entrée si n'existe pas
                $this->ensureAnalyticsEntry($restaurantId);
                $stmt->execute([$restaurantId]);
                $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
            
            // Stats des 30 derniers jours (calcul dynamique)
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as views_last_30_days
                FROM analytics_events 
                WHERE restaurant_id = ? 
                AND event_type = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute([$restaurantId]);
            $last30days = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Stats des 7 derniers jours
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as views_last_7_days
                FROM analytics_events 
                WHERE restaurant_id = ? 
                AND event_type = 'view'
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stmt->execute([$restaurantId]);
            $last7days = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'stats' => array_merge($stats, $last30days, $last7days)
            ]);
            
        } catch (\Exception $e) {
            Logger::error("Error fetching restaurant stats: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Server error']);
        }
    }
}