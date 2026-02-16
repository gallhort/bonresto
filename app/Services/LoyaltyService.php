<?php

namespace App\Services;

use PDO;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SERVICE DE FIDÉLITÉ - LEBONRESTO
 * Gestion des points, badges et récompenses
 * ═══════════════════════════════════════════════════════════════════════════
 */
class LoyaltyService
{
    private PDO $db;
    
    /**
     * Configuration des points par action
     */
    private array $pointsConfig = [
        'register'           => 20,
        'complete_profile'   => 15,
        'review_posted'      => 10,
        'review_with_photo'  => 5,
        'review_multi_photo' => 10,
        'review_long'        => 5,
        'vote_received'      => 2,
        'share'              => 2,
        'wishlist_add'       => 1,
        'daily_login'        => 1,
        'first_review_month' => 10,
        'referral'           => 50,
        'checkin'            => 10,
        'collection_create'  => 5,
        'reservation'        => 3,
        'tip'                => 3,
        'referral_bonus'     => 50,
        'referral_complete'  => 100,
        'suggestion'         => 10,
        'suggestion_approved'=> 50,
        'order_placed'       => 10,
        'survey'             => 5,
    ];

    /**
     * Configuration des badges (paliers de points)
     */
    private array $badgesConfig = [
        ['name' => 'Explorateur',   'slug' => 'explorateur',   'points' => 0,    'icon' => '🔍', 'color' => '#6b7280'],
        ['name' => 'Gourmet',       'slug' => 'gourmet',       'points' => 150,  'icon' => '🍽️', 'color' => '#94a3b8'],
        ['name' => 'Connaisseur',   'slug' => 'connaisseur',   'points' => 500,  'icon' => '🥇', 'color' => '#f59e0b'],
        ['name' => 'Expert',        'slug' => 'expert',        'points' => 1200, 'icon' => '⭐', 'color' => '#8b5cf6'],
        ['name' => 'Ambassadeur',   'slug' => 'ambassadeur',   'points' => 2500, 'icon' => '👑', 'color' => '#eab308'],
        ['name' => 'Legendaire',    'slug' => 'legendaire',    'points' => 5000, 'icon' => '🔱', 'color' => '#dc2626'],
    ];

    /**
     * Badges d'accomplissement (conditions spécifiques)
     */
    private array $achievementsConfig = [
        // ── Avis ──
        ['slug' => 'first-review',    'name' => 'Premier Pas',        'icon' => '✍️', 'condition' => 'review_count',      'value' => 1,  'desc' => 'Publier son premier avis'],
        ['slug' => 'reviewer-5',      'name' => 'Assidu',             'icon' => '📝', 'condition' => 'review_count',      'value' => 5,  'desc' => 'Publier 5 avis'],
        ['slug' => 'reviewer-10',     'name' => 'Passionné',          'icon' => '🔥', 'condition' => 'review_count',      'value' => 10, 'desc' => 'Publier 10 avis'],
        ['slug' => 'prolific',        'name' => 'Prolifique',         'icon' => '🏆', 'condition' => 'review_count',      'value' => 20, 'desc' => 'Publier 20 avis'],
        ['slug' => 'reviewer-50',     'name' => 'Légende Culinaire',  'icon' => '👑', 'condition' => 'review_count',      'value' => 50, 'desc' => 'Publier 50 avis'],
        // ── Photos ──
        ['slug' => 'photographer',    'name' => 'Photographe',        'icon' => '📸', 'condition' => 'photo_count',       'value' => 5,  'desc' => 'Ajouter 5 photos'],
        ['slug' => 'paparazzi',       'name' => 'Paparazzi',          'icon' => '🎬', 'condition' => 'photo_count',       'value' => 25, 'desc' => 'Ajouter 25 photos'],
        ['slug' => 'reporter',        'name' => 'Reporter',           'icon' => '🎥', 'condition' => 'photo_count',       'value' => 50, 'desc' => 'Ajouter 50 photos'],
        // ── Cuisines & Villes ──
        ['slug' => 'multicuisine',    'name' => 'Multicuisine',       'icon' => '🍜', 'condition' => 'cuisine_types',     'value' => 5,  'desc' => 'Gouter 5 types de cuisine'],
        ['slug' => 'cuisine-master',  'name' => 'Gastronome',         'icon' => '🧑‍🍳', 'condition' => 'cuisine_types',     'value' => 10, 'desc' => 'Gouter 10 types de cuisine'],
        ['slug' => 'globe-trotter',   'name' => 'Globe-trotter',      'icon' => '🌍', 'condition' => 'wilaya_count',      'value' => 3,  'desc' => 'Visiter 3 villes'],
        ['slug' => 'nomad',           'name' => 'Nomade DZ',          'icon' => '🗺️', 'condition' => 'wilaya_count',      'value' => 10, 'desc' => 'Visiter 10 villes'],
        // ── Votes utiles ──
        ['slug' => 'trusted-critic',  'name' => 'Critique Fiable',    'icon' => '💎', 'condition' => 'helpful_votes',     'value' => 10, 'desc' => 'Recevoir 10 votes utiles'],
        ['slug' => 'influencer',      'name' => 'Influenceur',        'icon' => '🌟', 'condition' => 'helpful_votes',     'value' => 50, 'desc' => 'Recevoir 50 votes utiles'],
        // ── Style d'avis ──
        ['slug' => 'long-writer',     'name' => 'Romancier',          'icon' => '📖', 'condition' => 'long_review_count', 'value' => 10, 'desc' => 'Publier 10 avis détaillés (200+ car.)'],
        ['slug' => 'five-star-fan',   'name' => 'Généreux',           'icon' => '⭐', 'condition' => 'five_star_count',   'value' => 10, 'desc' => 'Donner 10 notes de 5 étoiles'],
        ['slug' => 'critical-eye',    'name' => 'Oeil Critique',      'icon' => '🧐', 'condition' => 'low_rating_count',  'value' => 5,  'desc' => 'Donner 5 notes sévères (1-2 étoiles)'],
        // ── Conseils ──
        ['slug' => 'first-tip',       'name' => 'Conseiller',         'icon' => '💡', 'condition' => 'tip_count',         'value' => 1,  'desc' => 'Publier son premier conseil'],
        ['slug' => 'tip-master',      'name' => 'Mentor',             'icon' => '🎓', 'condition' => 'tip_count',         'value' => 10, 'desc' => 'Publier 10 conseils'],
        // ── Collections ──
        ['slug' => 'first-collection','name' => 'Collectionneur',     'icon' => '📁', 'condition' => 'collection_count',  'value' => 1,  'desc' => 'Créer sa première collection'],
        ['slug' => 'collector',       'name' => 'Curateur',           'icon' => '🗂️', 'condition' => 'collection_count',  'value' => 5,  'desc' => 'Créer 5 collections'],
        // ── Check-ins ──
        ['slug' => 'first-checkin',   'name' => 'Présent !',          'icon' => '📍', 'condition' => 'checkin_count',     'value' => 1,  'desc' => 'Faire son premier check-in'],
        ['slug' => 'checkin-regular', 'name' => 'Habitué',            'icon' => '🏠', 'condition' => 'checkin_count',     'value' => 10, 'desc' => 'Faire 10 check-ins'],
        // ── Favoris ──
        ['slug' => 'wishlist-lover',  'name' => 'Gourmand',           'icon' => '❤️', 'condition' => 'wishlist_count',    'value' => 10, 'desc' => 'Ajouter 10 restaurants aux favoris'],
        // ── Suggestions ──
        ['slug' => 'eclaireur',       'name' => 'Éclaireur',          'icon' => '🔦', 'condition' => 'suggestion_count',  'value' => 3,  'desc' => 'Proposer 3 restaurants'],
    ];
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * GESTION DES POINTS
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Ajouter des points à un utilisateur
     */
    public function addPoints(int $userId, string $action, ?int $referenceId = null, ?string $referenceType = null): array
    {
        $points = $this->pointsConfig[$action] ?? 0;
        
        if ($points === 0) {
            return ['success' => false, 'message' => 'Action inconnue'];
        }
        
        // Vérifier les doublons pour certaines actions
        if ($action === 'daily_login') {
            if ($this->hasActionToday($userId, $action)) {
                return ['success' => false, 'message' => 'Points déjà attribués aujourd\'hui'];
            }
        }
        
        // Vérifier first_review_month
        if ($action === 'first_review_month') {
            if ($this->hasActionThisMonth($userId, $action)) {
                return ['success' => false, 'message' => 'Bonus déjà attribué ce mois'];
            }
        }

        // Check for active multiplier
        $multStmt = $this->db->prepare("
            SELECT multiplier FROM points_multipliers
            WHERE NOW() BETWEEN start_date AND end_date
            ORDER BY multiplier DESC LIMIT 1
        ");
        $multStmt->execute();
        $activeMultiplier = $multStmt->fetchColumn();
        if ($activeMultiplier && $activeMultiplier > 1) {
            $points = (int)round($points * (float)$activeMultiplier);
        }

        // Insérer dans l'historique
        $stmt = $this->db->prepare("
            INSERT INTO user_points_history (user_id, points, action, description, reference_id, reference_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $description = $this->getActionDescription($action);
        $stmt->execute([$userId, $points, $action, $description, $referenceId, $referenceType]);
        
        // Mettre à jour le total
        $stmt = $this->db->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $stmt->execute([$points, $userId]);
        
        // Vérifier si nouveau badge
        $newBadge = $this->checkBadgeUpgrade($userId);
        
        return [
            'success' => true,
            'points_added' => $points,
            'total_points' => $this->getUserPoints($userId),
            'action' => $action,
            'new_badge' => $newBadge,
            'message' => "+{$points} points !"
        ];
    }
    
    /**
     * Ajouter des points pour un avis (avec bonus automatiques)
     */
    public function addPointsForReview(int $userId, int $reviewId, bool $hasPhoto = false, int $textLength = 0, int $photoCount = 0): array
    {
        $results = [];

        // Points de base seulement si avis > 50 caractères
        if ($textLength >= 50) {
            $results[] = $this->addPoints($userId, 'review_posted', $reviewId, 'review');
        }

        // Bonus photo
        if ($hasPhoto) {
            $results[] = $this->addPoints($userId, 'review_with_photo', $reviewId, 'review');
        }

        // Bonus multi-photos (3+)
        if ($photoCount >= 3) {
            $results[] = $this->addPoints($userId, 'review_multi_photo', $reviewId, 'review');
        }

        // Bonus avis détaillé (+200 caractères)
        if ($textLength >= 200) {
            $results[] = $this->addPoints($userId, 'review_long', $reviewId, 'review');
        }

        // Bonus premier avis du mois
        $results[] = $this->addPoints($userId, 'first_review_month', $reviewId, 'review');
        
        // Calculer total des points gagnés
        $totalPoints = 0;
        $newBadge = null;
        foreach ($results as $result) {
            if ($result['success']) {
                $totalPoints += $result['points_added'];
                if (!empty($result['new_badge'])) {
                    $newBadge = $result['new_badge'];
                }
            }
        }
        
        // Vérifier les badges d'accomplissement
        $achievements = $this->checkAchievements($userId);

        return [
            'success' => true,
            'total_points_earned' => $totalPoints,
            'new_badge' => $newBadge,
            'new_achievements' => $achievements,
            'details' => $results
        ];
    }
    
    /**
     * Retirer des points (pour annulation)
     */
    public function removePoints(int $userId, int $points, string $reason): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO user_points_history (user_id, points, action, description)
            VALUES (?, ?, 'deduction', ?)
        ");
        $stmt->execute([$userId, -$points, $reason]);
        
        $stmt = $this->db->prepare("UPDATE users SET points = GREATEST(0, points - ?) WHERE id = ?");
        $stmt->execute([$points, $userId]);
        
        return true;
    }
    
    /**
     * Obtenir les points d'un utilisateur
     */
    public function getUserPoints(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT points FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return (int)($stmt->fetchColumn() ?: 0);
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * GESTION DES BADGES
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Vérifier et upgrader le badge si nécessaire
     */
    public function checkBadgeUpgrade(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT points, badge FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return null;
        
        $currentBadge = $user['badge'];
        $points = $user['points'];
        
        // Trouver le badge approprié
        $newBadgeName = 'Explorateur';
        $newBadgeData = null;
        
        foreach ($this->badgesConfig as $badge) {
            if ($points >= $badge['points']) {
                $newBadgeName = $badge['name'];
                $newBadgeData = $badge;
            }
        }
        
        // Si changement de badge
        if ($newBadgeName !== $currentBadge) {
            // Mettre à jour le user
            $stmt = $this->db->prepare("UPDATE users SET badge = ? WHERE id = ?");
            $stmt->execute([$newBadgeName, $userId]);
            
            // Récupérer l'ID du badge et enregistrer
            $stmt = $this->db->prepare("SELECT id, icon, color FROM badges WHERE name = ?");
            $stmt->execute([$newBadgeName]);
            $badgeInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($badgeInfo) {
                $stmt = $this->db->prepare("
                    INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)
                ");
                $stmt->execute([$userId, $badgeInfo['id']]);
            }
            
            return [
                'name' => $newBadgeName,
                'icon' => $newBadgeData['icon'] ?? '🏅',
                'discount' => $newBadgeData['discount'] ?? 0,
                'message' => "Félicitations ! Vous êtes maintenant {$newBadgeName} !"
            ];
        }
        
        return null;
    }
    
    /**
     * Obtenir le badge actuel d'un utilisateur
     */
    public function getUserBadge(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.* FROM badges b
            INNER JOIN users u ON u.badge = b.name
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Obtenir tous les badges débloqués par un utilisateur
     */
    public function getUserUnlockedBadges(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, ub.unlocked_at 
            FROM badges b
            INNER JOIN user_badges ub ON ub.badge_id = b.id
            WHERE ub.user_id = ?
            ORDER BY b.points_required ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * STATISTIQUES UTILISATEUR
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Obtenir les stats complètes de fidélité d'un utilisateur
     */
    public function getUserLoyaltyStats(int $userId): array
    {
        // Infos utilisateur
        $stmt = $this->db->prepare("SELECT points, badge FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['error' => 'Utilisateur non trouvé'];
        }
        
        // Badge actuel
        $currentBadge = $this->getUserBadge($userId);
        
        // Prochain badge
        $nextBadge = null;
        $pointsToNext = 0;
        foreach ($this->badgesConfig as $badge) {
            if ($badge['points'] > $user['points']) {
                $nextBadge = $badge;
                $pointsToNext = $badge['points'] - $user['points'];
                break;
            }
        }
        
        // Tous les badges débloqués
        $unlockedBadges = $this->getUserUnlockedBadges($userId);

        // Achievements débloqués
        $unlockedAchievements = $this->getUserAchievements($userId);

        // Titres personnalisés (recalculés max 1x/heure)
        $cacheKey = 'titles_computed_' . $userId;
        $lastComputed = $_SESSION[$cacheKey] ?? 0;
        if (time() - $lastComputed > 3600) {
            $this->computeUserTitles($userId);
            $_SESSION[$cacheKey] = time();
        }
        $userTitles = $this->getUserTitles($userId);
        $primaryTitle = $this->getUserPrimaryTitle($userId);

        // Historique récent
        $stmt = $this->db->prepare("
            SELECT * FROM user_points_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 15
        ");
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Coupons disponibles
        $coupons = $this->getUserCoupons($userId);
        
        // Progression vers prochain badge
        $progressPercent = 100;
        if ($nextBadge) {
            $currentBadgePoints = 0;
            foreach ($this->badgesConfig as $b) {
                if ($b['name'] === $user['badge']) {
                    $currentBadgePoints = $b['points'];
                    break;
                }
            }
            $range = $nextBadge['points'] - $currentBadgePoints;
            $progress = $user['points'] - $currentBadgePoints;
            $progressPercent = $range > 0 ? min(100, round(($progress / $range) * 100)) : 100;
        }
        
        // Statistiques totales
        $stmt = $this->db->prepare("
            SELECT 
                SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as total_earned,
                COUNT(DISTINCT DATE(created_at)) as active_days
            FROM user_points_history 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $totals = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'points' => $user['points'],
            'badge' => $currentBadge,
            'next_badge' => $nextBadge,
            'points_to_next' => $pointsToNext,
            'progress_percent' => $progressPercent,
            'unlocked_badges' => $unlockedBadges,
            'all_badges' => $this->getAllBadges(),
            'history' => $history,
            'coupons' => $coupons,
            'discount' => $currentBadge['discount_percent'] ?? 0,
            'total_earned' => $totals['total_earned'] ?? 0,
            'active_days' => $totals['active_days'] ?? 0,
            'unlocked_achievements' => $unlockedAchievements,
            'titles' => $userTitles,
            'primary_title' => $primaryTitle
        ];
    }
    
    /**
     * Obtenir le discount actuel d'un utilisateur
     */
    public function getUserDiscount(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT b.discount_percent 
            FROM users u
            LEFT JOIN badges b ON b.name = u.badge
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return (int)($stmt->fetchColumn() ?: 0);
    }
    
    /**
     * Obtenir tous les badges disponibles
     */
    public function getAllBadges(): array
    {
        $stmt = $this->db->query("SELECT * FROM badges ORDER BY points_required ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * GESTION DES COUPONS
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Obtenir les coupons d'un utilisateur
     */
    public function getUserCoupons(int $userId, bool $onlyAvailable = false): array
    {
        $sql = "
            SELECT c.*, uc.is_used, uc.used_at, r.nom as restaurant_name
            FROM user_coupons uc
            INNER JOIN coupons c ON c.id = uc.coupon_id
            LEFT JOIN restaurants r ON r.id = c.restaurant_id
            WHERE uc.user_id = ?
        ";
        
        if ($onlyAvailable) {
            $sql .= " AND uc.is_used = 0 AND c.is_active = 1 
                      AND (c.valid_until IS NULL OR c.valid_until >= CURDATE())";
        }
        
        $sql .= " ORDER BY uc.is_used ASC, c.valid_until ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Attribuer un coupon à un utilisateur
     */
    public function assignCoupon(int $userId, int $couponId): array
    {
        // Vérifier si le coupon existe et est actif
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE id = ? AND is_active = 1");
        $stmt->execute([$couponId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            return ['success' => false, 'message' => 'Coupon non disponible'];
        }
        
        // Vérifier le badge minimum requis
        $stmt = $this->db->prepare("SELECT badge, points FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon['min_points'] > 0 && $user['points'] < $coupon['min_points']) {
            return ['success' => false, 'message' => "Il faut {$coupon['min_points']} points minimum"];
        }
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_coupons (user_id, coupon_id) VALUES (?, ?)
            ");
            $stmt->execute([$userId, $couponId]);
            return ['success' => true, 'message' => 'Coupon ajouté !'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Coupon déjà attribué'];
        }
    }
    
    /**
     * Utiliser un coupon
     */
    public function useCoupon(int $userId, string $couponCode): array
    {
        // Vérifier le coupon
        $stmt = $this->db->prepare("
            SELECT c.*, uc.id as user_coupon_id, uc.is_used
            FROM coupons c
            INNER JOIN user_coupons uc ON uc.coupon_id = c.id
            WHERE c.code = ? AND uc.user_id = ?
        ");
        $stmt->execute([$couponCode, $userId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            return ['success' => false, 'message' => 'Coupon non trouvé'];
        }
        
        if ($coupon['is_used']) {
            return ['success' => false, 'message' => 'Coupon déjà utilisé'];
        }
        
        if (!$coupon['is_active']) {
            return ['success' => false, 'message' => 'Coupon inactif'];
        }
        
        if ($coupon['valid_until'] && strtotime($coupon['valid_until']) < strtotime('today')) {
            return ['success' => false, 'message' => 'Coupon expiré'];
        }
        
        // Marquer comme utilisé
        $stmt = $this->db->prepare("
            UPDATE user_coupons SET is_used = 1, used_at = NOW() WHERE id = ?
        ");
        $stmt->execute([$coupon['user_coupon_id']]);
        
        // Incrémenter le compteur global
        $stmt = $this->db->prepare("
            UPDATE coupons SET current_uses = current_uses + 1 WHERE id = ?
        ");
        $stmt->execute([$coupon['id']]);
        
        return [
            'success' => true,
            'message' => 'Coupon appliqué !',
            'discount_percent' => $coupon['discount_percent'],
            'discount_amount' => $coupon['discount_amount'],
            'title' => $coupon['title']
        ];
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * CLASSEMENT
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Obtenir le classement des utilisateurs
     */
    public function getLeaderboard(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.id, 
                u.prenom, 
                u.nom, 
                u.photo_profil, 
                u.points, 
                u.badge,
                b.icon as badge_icon, 
                b.color as badge_color,
                (SELECT COUNT(*) FROM reviews WHERE user_id = u.id AND status = 'approved') as review_count
            FROM users u
            LEFT JOIN badges b ON b.name = u.badge
            WHERE u.points > 0
            ORDER BY u.points DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtenir le rang d'un utilisateur
     */
    public function getUserRank(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) + 1 as rank
            FROM users
            WHERE points > (SELECT points FROM users WHERE id = ?)
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * BADGES D'ACCOMPLISSEMENT
     * ═══════════════════════════════════════════════════════════════════════
     */

    /**
     * Vérifie et attribue les badges d'accomplissement
     * @return array Nouveaux badges débloqués
     */
    public function checkAchievements(int $userId): array
    {
        $newAchievements = [];

        // Récupérer les achievements déjà débloqués
        $stmt = $this->db->prepare("SELECT badge_slug FROM user_achievements WHERE user_id = ?");
        $stmt->execute([$userId]);
        $unlocked = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($this->achievementsConfig as $achievement) {
            // Déjà débloqué ? Skip
            if (in_array($achievement['slug'], $unlocked)) {
                continue;
            }

            $conditionMet = false;

            switch ($achievement['condition']) {
                case 'review_count':
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND status = 'approved'");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'photo_count':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM review_photos rp
                        INNER JOIN reviews r ON r.id = rp.review_id
                        WHERE r.user_id = ? AND r.status = 'approved'
                    ");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'cuisine_types':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(DISTINCT rest.type_cuisine) FROM reviews r
                        INNER JOIN restaurants rest ON rest.id = r.restaurant_id
                        WHERE r.user_id = ? AND r.status = 'approved' AND rest.type_cuisine IS NOT NULL
                    ");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'helpful_votes':
                    $stmt = $this->db->prepare("
                        SELECT COALESCE(SUM(votes_utiles), 0) FROM reviews
                        WHERE user_id = ? AND status = 'approved'
                    ");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'wilaya_count':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(DISTINCT rest.ville) FROM reviews r
                        INNER JOIN restaurants rest ON rest.id = r.restaurant_id
                        WHERE r.user_id = ? AND r.status = 'approved' AND rest.ville IS NOT NULL AND rest.ville != ''
                    ");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'long_review_count':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM reviews
                        WHERE user_id = ? AND status = 'approved' AND LENGTH(message) >= 200
                    ");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'five_star_count':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM reviews
                        WHERE user_id = ? AND status = 'approved' AND note_globale = 5
                    ");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'low_rating_count':
                    $stmt = $this->db->prepare("
                        SELECT COUNT(*) FROM reviews
                        WHERE user_id = ? AND status = 'approved' AND note_globale <= 2
                    ");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'tip_count':
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM restaurant_tips WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'collection_count':
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM collections WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'checkin_count':
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM checkins WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'wishlist_count':
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;

                case 'suggestion_count':
                    $stmt = $this->db->prepare("SELECT COUNT(*) FROM restaurant_suggestions WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $conditionMet = $stmt->fetchColumn() >= $achievement['value'];
                    break;
            }

            if ($conditionMet) {
                // Débloquer l'achievement
                try {
                    $stmt = $this->db->prepare("
                        INSERT IGNORE INTO user_achievements (user_id, badge_slug, badge_name, badge_icon, unlocked_at)
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([$userId, $achievement['slug'], $achievement['name'], $achievement['icon']]);

                    if ($stmt->rowCount() > 0) {
                        $newAchievements[] = $achievement;
                        // Stocker la notification
                        if (session_status() !== PHP_SESSION_NONE) {
                            $_SESSION['achievement_notification'] = $achievement;
                        }
                    }
                } catch (\Exception $e) {
                    // Table might not exist yet, silently fail
                    Logger::error('Achievement check error', [$e->getMessage()]);
                }
            }
        }

        return $newAchievements;
    }

    /**
     * Récupérer tous les achievements d'un utilisateur
     */
    public function getUserAchievements(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_achievements WHERE user_id = ? ORDER BY unlocked_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtenir la config des achievements
     */
    public function getAchievementsConfig(): array
    {
        return $this->achievementsConfig;
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════
     * HELPERS PRIVÉS
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Vérifier si une action a déjà été faite aujourd'hui
     */
    private function hasActionToday(int $userId, string $action): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM user_points_history 
            WHERE user_id = ? AND action = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$userId, $action]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Vérifier si une action a déjà été faite ce mois
     */
    private function hasActionThisMonth(int $userId, string $action): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM user_points_history 
            WHERE user_id = ? AND action = ? 
            AND YEAR(created_at) = YEAR(CURDATE()) 
            AND MONTH(created_at) = MONTH(CURDATE())
        ");
        $stmt->execute([$userId, $action]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Générer une description pour une action
     */
    private function getActionDescription(string $action): string
    {
        $descriptions = [
            'register'           => 'Bonus de bienvenue',
            'complete_profile'   => 'Profil complété',
            'review_posted'      => 'Avis publié',
            'review_with_photo'  => 'Bonus photo dans l\'avis',
            'review_long'        => 'Bonus avis détaillé',
            'vote_received'      => 'Vote "utile" reçu',
            'wishlist_add'       => 'Restaurant ajouté aux favoris',
            'share'              => 'Restaurant partagé',
            'daily_login'        => 'Connexion quotidienne',
            'first_review_month' => 'Premier avis du mois',
            'referral'           => 'Parrainage validé',
            'checkin'            => 'Check-in géographique',
            'collection_create'  => 'Collection créée',
            'reservation'        => 'Réservation effectuée',
            'deduction'          => 'Déduction de points',
            'tip'                => 'Conseil publié',
            'referral_bonus'     => 'Bonus parrainage (bienvenue)',
            'referral_complete'  => 'Parrainage complété',
            'suggestion'         => 'Restaurant proposé',
            'suggestion_approved'=> 'Suggestion validée par l\'équipe',
            'order_placed'       => 'Commande passée',
        ];
        
        return $descriptions[$action] ?? "Action: {$action}";
    }
    
    /**
     * Obtenir la configuration des points
     */
    public function getPointsConfig(): array
    {
        return $this->pointsConfig;
    }
    
    /**
     * Obtenir la configuration des badges
     */
    public function getBadgesConfig(): array
    {
        return $this->badgesConfig;
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════
     * TITRES PERSONNALISÉS
     * Badges dynamiques basés sur l'activité réelle
     * ═══════════════════════════════════════════════════════════════════════
     */

    /**
     * Calcule et attribue les titres personnalisés d'un utilisateur
     */
    public function computeUserTitles(int $userId): array
    {
        $newTitles = [];

        // ── 1. TOP VILLE : #1 reviewer dans une ville (min 3 avis) ──
        $stmt = $this->db->prepare("
            SELECT rest.ville, COUNT(*) as cnt
            FROM reviews r
            INNER JOIN restaurants rest ON rest.id = r.restaurant_id
            WHERE r.user_id = :uid AND r.status = 'approved'
              AND rest.ville IS NOT NULL AND rest.ville != ''
            GROUP BY rest.ville
            HAVING cnt >= 3
        ");
        $stmt->execute([':uid' => $userId]);
        $userCities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($userCities as $uc) {
            $city = $uc['ville'];
            // Qui est le #1 dans cette ville ?
            $topStmt = $this->db->prepare("
                SELECT r.user_id, COUNT(*) as cnt
                FROM reviews r
                INNER JOIN restaurants rest ON rest.id = r.restaurant_id
                WHERE r.status = 'approved' AND rest.ville = :ville
                GROUP BY r.user_id
                ORDER BY cnt DESC
                LIMIT 1
            ");
            $topStmt->execute([':ville' => $city]);
            $top = $topStmt->fetch(PDO::FETCH_ASSOC);

            if ($top && (int)$top['user_id'] === $userId) {
                $newTitles[] = $this->upsertTitle($userId, 'top_city', "Top $city", '🏙️', '#3b82f6', $city);
            }
        }

        // ── 2. ROI DU [CUISINE] : #1 reviewer pour un type de cuisine (min 3 avis) ──
        $stmt = $this->db->prepare("
            SELECT rest.type_cuisine, COUNT(*) as cnt
            FROM reviews r
            INNER JOIN restaurants rest ON rest.id = r.restaurant_id
            WHERE r.user_id = :uid AND r.status = 'approved'
              AND rest.type_cuisine IS NOT NULL AND rest.type_cuisine != ''
            GROUP BY rest.type_cuisine
            HAVING cnt >= 3
        ");
        $stmt->execute([':uid' => $userId]);
        $userCuisines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($userCuisines as $uc) {
            $cuisine = $uc['type_cuisine'];
            $topStmt = $this->db->prepare("
                SELECT r.user_id, COUNT(*) as cnt
                FROM reviews r
                INNER JOIN restaurants rest ON rest.id = r.restaurant_id
                WHERE r.status = 'approved' AND rest.type_cuisine = :cuisine
                GROUP BY r.user_id
                ORDER BY cnt DESC
                LIMIT 1
            ");
            $topStmt->execute([':cuisine' => $cuisine]);
            $top = $topStmt->fetch(PDO::FETCH_ASSOC);

            if ($top && (int)$top['user_id'] === $userId) {
                $newTitles[] = $this->upsertTitle($userId, 'cuisine_king', "Roi $cuisine", '👨‍🍳', '#f59e0b', $cuisine);
            }
        }

        // ── 3. EXPLORATEUR [VILLE] : 5+ restos différents reviewés dans une ville ──
        $stmt = $this->db->prepare("
            SELECT rest.ville, COUNT(DISTINCT rest.id) as resto_count
            FROM reviews r
            INNER JOIN restaurants rest ON rest.id = r.restaurant_id
            WHERE r.user_id = :uid AND r.status = 'approved'
              AND rest.ville IS NOT NULL AND rest.ville != ''
            GROUP BY rest.ville
            HAVING resto_count >= 5
        ");
        $stmt->execute([':uid' => $userId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $newTitles[] = $this->upsertTitle($userId, 'city_explorer', "Explorateur " . $row['ville'], '🧭', '#10b981', $row['ville']);
        }

        // ── 4. PREMIER FAN : premier à avoir reviewé un restaurant ──
        $stmt = $this->db->prepare("
            SELECT r.restaurant_id
            FROM reviews r
            WHERE r.user_id = :uid AND r.status = 'approved'
              AND r.id = (
                  SELECT MIN(r2.id) FROM reviews r2
                  WHERE r2.restaurant_id = r.restaurant_id AND r2.status = 'approved'
              )
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        if ($stmt->fetchColumn()) {
            $newTitles[] = $this->upsertTitle($userId, 'first_fan', 'Premier Fan', '🥇', '#ec4899', null);
        }

        // ── 5. FIDÈLE : 2+ avis sur le même restaurant ──
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM (
                SELECT restaurant_id
                FROM reviews
                WHERE user_id = :uid AND status = 'approved'
                GROUP BY restaurant_id
                HAVING COUNT(*) >= 2
            ) as sub
        ");
        $stmt->execute([':uid' => $userId]);
        if ((int)$stmt->fetchColumn() > 0) {
            $newTitles[] = $this->upsertTitle($userId, 'loyal_customer', 'Client Fidèle', '💛', '#eab308', null);
        }

        // ── 6. NOCTAMBULE : 5+ avis postés après 22h ──
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reviews
            WHERE user_id = :uid AND status = 'approved'
              AND HOUR(created_at) >= 22
        ");
        $stmt->execute([':uid' => $userId]);
        if ((int)$stmt->fetchColumn() >= 5) {
            $newTitles[] = $this->upsertTitle($userId, 'night_owl', 'Noctambule', '🦉', '#6366f1', null);
        }

        // ── 7. MATINAL : 5+ avis postés avant 8h ──
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM reviews
            WHERE user_id = :uid AND status = 'approved'
              AND HOUR(created_at) < 8
        ");
        $stmt->execute([':uid' => $userId]);
        if ((int)$stmt->fetchColumn() >= 5) {
            $newTitles[] = $this->upsertTitle($userId, 'early_bird', 'Lève-tôt', '🌅', '#f97316', null);
        }

        // ── 8. CRITIQUE DE L'ANNÉE : #1 reviewer de l'année en cours ──
        $year = date('Y');
        $stmt = $this->db->prepare("
            SELECT user_id, COUNT(*) as cnt
            FROM reviews
            WHERE status = 'approved' AND YEAR(created_at) = :year
            GROUP BY user_id
            ORDER BY cnt DESC
            LIMIT 1
        ");
        $stmt->execute([':year' => $year]);
        $topYear = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($topYear && (int)$topYear['user_id'] === $userId && (int)$topYear['cnt'] >= 5) {
            $newTitles[] = $this->upsertTitle($userId, 'critic_of_year', "Critique $year", '🏅', '#dc2626', $year);
        }

        // ── 9. PHOTOGRAPHE PRO : 15+ photos au total ──
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM review_photos rp
            INNER JOIN reviews r ON r.id = rp.review_id
            WHERE r.user_id = :uid AND r.status = 'approved'
        ");
        $stmt->execute([':uid' => $userId]);
        if ((int)$stmt->fetchColumn() >= 15) {
            $newTitles[] = $this->upsertTitle($userId, 'photo_pro', 'Photographe Pro', '📷', '#0ea5e9', null);
        }

        // ── 10. GOURMET ÉCLECTIQUE : 8+ types de cuisine différents ──
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT rest.type_cuisine)
            FROM reviews r
            INNER JOIN restaurants rest ON rest.id = r.restaurant_id
            WHERE r.user_id = :uid AND r.status = 'approved'
              AND rest.type_cuisine IS NOT NULL AND rest.type_cuisine != ''
        ");
        $stmt->execute([':uid' => $userId]);
        if ((int)$stmt->fetchColumn() >= 8) {
            $newTitles[] = $this->upsertTitle($userId, 'eclectic', 'Gourmet Éclectique', '🎨', '#8b5cf6', null);
        }

        // ── 11. INFLUENCEUR LOCAL : 20+ votes utiles au total ──
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(votes_utiles), 0)
            FROM reviews WHERE user_id = :uid AND status = 'approved'
        ");
        $stmt->execute([':uid' => $userId]);
        if ((int)$stmt->fetchColumn() >= 20) {
            $newTitles[] = $this->upsertTitle($userId, 'local_influencer', 'Influenceur Local', '📣', '#ef4444', null);
        }

        // Désactiver les titres compétitifs qui ne sont plus valides
        $this->cleanupCompetitiveTitles($userId);

        return array_filter($newTitles);
    }

    /**
     * Insert ou update un titre
     */
    private function upsertTitle(int $userId, string $type, string $label, string $icon, string $color, ?string $context): ?array
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_titles (user_id, title_type, title_label, title_icon, title_color, context)
                VALUES (:uid, :type, :label, :icon, :color, :ctx)
                ON DUPLICATE KEY UPDATE title_label = :label2, title_icon = :icon2, title_color = :color2, is_active = 1
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':type' => $type,
                ':label' => $label,
                ':icon' => $icon,
                ':color' => $color,
                ':ctx' => $context,
                ':label2' => $label,
                ':icon2' => $icon,
                ':color2' => $color,
            ]);

            if ($stmt->rowCount() > 0) {
                return ['type' => $type, 'label' => $label, 'icon' => $icon, 'color' => $color];
            }
        } catch (\Exception $e) {
            Logger::error('Title upsert error: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * Désactive les titres compétitifs périmés (si l'user n'est plus #1)
     */
    private function cleanupCompetitiveTitles(int $userId): void
    {
        $competitiveTypes = ['top_city', 'cuisine_king', 'critic_of_year'];
        try {
            $stmt = $this->db->prepare("
                SELECT id, title_type, context FROM user_titles
                WHERE user_id = :uid AND is_active = 1 AND title_type IN ('top_city', 'cuisine_king', 'critic_of_year')
            ");
            $stmt->execute([':uid' => $userId]);
            $activeTitles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($activeTitles as $title) {
                $stillValid = false;

                if ($title['title_type'] === 'top_city' && $title['context']) {
                    $check = $this->db->prepare("
                        SELECT r.user_id FROM reviews r
                        INNER JOIN restaurants rest ON rest.id = r.restaurant_id
                        WHERE r.status = 'approved' AND rest.ville = :ville
                        GROUP BY r.user_id ORDER BY COUNT(*) DESC LIMIT 1
                    ");
                    $check->execute([':ville' => $title['context']]);
                    $top = $check->fetch(PDO::FETCH_ASSOC);
                    $stillValid = $top && (int)$top['user_id'] === $userId;
                }

                if ($title['title_type'] === 'cuisine_king' && $title['context']) {
                    $check = $this->db->prepare("
                        SELECT r.user_id FROM reviews r
                        INNER JOIN restaurants rest ON rest.id = r.restaurant_id
                        WHERE r.status = 'approved' AND rest.type_cuisine = :cuisine
                        GROUP BY r.user_id ORDER BY COUNT(*) DESC LIMIT 1
                    ");
                    $check->execute([':cuisine' => $title['context']]);
                    $top = $check->fetch(PDO::FETCH_ASSOC);
                    $stillValid = $top && (int)$top['user_id'] === $userId;
                }

                if ($title['title_type'] === 'critic_of_year') {
                    $check = $this->db->prepare("
                        SELECT user_id FROM reviews
                        WHERE status = 'approved' AND YEAR(created_at) = :year
                        GROUP BY user_id ORDER BY COUNT(*) DESC LIMIT 1
                    ");
                    $check->execute([':year' => $title['context'] ?? date('Y')]);
                    $top = $check->fetch(PDO::FETCH_ASSOC);
                    $stillValid = $top && (int)$top['user_id'] === $userId;
                }

                if (!$stillValid) {
                    $del = $this->db->prepare("UPDATE user_titles SET is_active = 0 WHERE id = :id");
                    $del->execute([':id' => $title['id']]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    /**
     * Récupérer les titres actifs d'un utilisateur
     */
    public function getUserTitles(int $userId): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_titles
                WHERE user_id = :uid AND is_active = 1
                ORDER BY earned_at DESC
            ");
            $stmt->execute([':uid' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Récupérer le titre principal (le plus prestigieux) d'un utilisateur
     */
    public function getUserPrimaryTitle(int $userId): ?array
    {
        // Priorité: critic_of_year > top_city > cuisine_king > reste
        $priority = ['critic_of_year', 'top_city', 'cuisine_king', 'local_influencer', 'photo_pro', 'eclectic',
                     'city_explorer', 'night_owl', 'early_bird', 'first_fan', 'loyal_customer'];
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM user_titles
                WHERE user_id = :uid AND is_active = 1
                ORDER BY earned_at DESC
            ");
            $stmt->execute([':uid' => $userId]);
            $titles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($titles)) return null;

            // Trier par priorité
            usort($titles, function($a, $b) use ($priority) {
                $pa = array_search($a['title_type'], $priority);
                $pb = array_search($b['title_type'], $priority);
                if ($pa === false) $pa = 999;
                if ($pb === false) $pb = 999;
                return $pa <=> $pb;
            });

            return $titles[0];
        } catch (\Exception $e) {
            return null;
        }
    }
}