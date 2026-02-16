<?php

namespace App\Services;

use PDO;

/**
 * Service de fil d'actualité social
 * Enregistre et récupère les activités des utilisateurs
 */
class ActivityFeedService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Enregistrer une activité
     */
    public function log(int $userId, string $actionType, ?string $targetType = null, ?int $targetId = null, array $metadata = []): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_feed (user_id, action_type, target_type, target_id, metadata)
                VALUES (:uid, :action, :ttype, :tid, :meta)
            ");
            $stmt->execute([
                ':uid' => $userId,
                ':action' => $actionType,
                ':ttype' => $targetType,
                ':tid' => $targetId,
                ':meta' => !empty($metadata) ? json_encode($metadata) : null,
            ]);
        } catch (\Exception $e) {
            Logger::error("ActivityFeed error: " . $e->getMessage());
        }
    }

    /**
     * Récupérer le fil d'actualité global (public)
     */
    public function getPublicFeed(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT af.*,
                   u.prenom, u.nom as user_nom, u.photo_profil as user_photo, u.badge as user_badge,
                   r.nom as restaurant_nom, r.slug as restaurant_slug, r.ville as restaurant_ville,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as restaurant_photo,
                   rev.message as review_message,
                   rev.note_globale as review_note,
                   (SELECT GROUP_CONCAT(rph.photo_path ORDER BY rph.display_order SEPARATOR '|||')
                    FROM review_photos rph WHERE rph.review_id = rev.id) as review_photos
            FROM activity_feed af
            INNER JOIN users u ON u.id = af.user_id
            LEFT JOIN restaurants r ON r.id = af.target_id AND af.target_type = 'restaurant'
            LEFT JOIN reviews rev ON rev.id = (
                SELECT rv.id FROM reviews rv
                WHERE rv.user_id = af.user_id AND rv.restaurant_id = af.target_id AND af.action_type = 'review'
                ORDER BY rv.created_at DESC LIMIT 1
            )
            ORDER BY af.created_at DESC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($activities as &$act) {
            $act['metadata'] = $act['metadata'] ? json_decode($act['metadata'], true) : [];
            $act['time_ago'] = $this->timeAgo($act['created_at']);
        }

        return $activities;
    }

    /**
     * Récupérer le fil des utilisateurs suivis
     */
    public function getFollowingFeed(int $userId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT af.*,
                   u.prenom, u.nom as user_nom, u.photo_profil as user_photo, u.badge as user_badge,
                   r.nom as restaurant_nom, r.slug as restaurant_slug, r.ville as restaurant_ville,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as restaurant_photo,
                   rev.message as review_message,
                   rev.note_globale as review_note,
                   (SELECT GROUP_CONCAT(rph.photo_path ORDER BY rph.display_order SEPARATOR '|||')
                    FROM review_photos rph WHERE rph.review_id = rev.id) as review_photos
            FROM activity_feed af
            INNER JOIN users u ON u.id = af.user_id
            INNER JOIN user_follows uf ON uf.followed_id = af.user_id AND uf.follower_id = :uid
            LEFT JOIN restaurants r ON r.id = af.target_id AND af.target_type = 'restaurant'
            LEFT JOIN reviews rev ON rev.id = (
                SELECT rv.id FROM reviews rv
                WHERE rv.user_id = af.user_id AND rv.restaurant_id = af.target_id AND af.action_type = 'review'
                ORDER BY rv.created_at DESC LIMIT 1
            )
            ORDER BY af.created_at DESC
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($activities as &$act) {
            $act['metadata'] = $act['metadata'] ? json_decode($act['metadata'], true) : [];
            $act['time_ago'] = $this->timeAgo($act['created_at']);
        }

        return $activities;
    }

    /**
     * Récupérer le fil d'un utilisateur spécifique
     */
    public function getUserFeed(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT af.*,
                   r.nom as restaurant_nom, r.slug as restaurant_slug, r.ville as restaurant_ville,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as restaurant_photo
            FROM activity_feed af
            LEFT JOIN restaurants r ON r.id = af.target_id AND af.target_type = 'restaurant'
            WHERE af.user_id = :uid
            ORDER BY af.created_at DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($activities as &$act) {
            $act['metadata'] = $act['metadata'] ? json_decode($act['metadata'], true) : [];
            $act['time_ago'] = $this->timeAgo($act['created_at']);
        }

        return $activities;
    }

    /**
     * Formater le temps relatif
     */
    private function timeAgo(string $datetime): string
    {
        $now = new \DateTime();
        $then = new \DateTime($datetime);
        $diff = $now->diff($then);

        if ($diff->y > 0) return 'il y a ' . $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) return 'il y a ' . $diff->m . ' mois';
        if ($diff->d > 0) return 'il y a ' . $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        if ($diff->h > 0) return 'il y a ' . $diff->h . 'h';
        if ($diff->i > 0) return 'il y a ' . $diff->i . ' min';
        return "a l'instant";
    }

    /**
     * Formatage d'une activité en texte lisible
     */
    public static function formatActivity(array $activity): string
    {
        $name = htmlspecialchars($activity['prenom'] ?? '');
        $resto = htmlspecialchars($activity['restaurant_nom'] ?? '');

        switch ($activity['action_type']) {
            case 'review':
                $note = $activity['metadata']['rating'] ?? '?';
                return "$name a laisse un avis ($note/5) sur <strong>$resto</strong>";
            case 'checkin':
                return "$name a fait un check-in chez <strong>$resto</strong>";
            case 'collection':
                $colName = htmlspecialchars($activity['metadata']['collection_name'] ?? '');
                return "$name a cree la collection <strong>$colName</strong>";
            case 'photo':
                $count = $activity['metadata']['count'] ?? 1;
                return "$name a ajoute $count photo(s) sur <strong>$resto</strong>";
            case 'badge':
                $badge = htmlspecialchars($activity['metadata']['badge_name'] ?? '');
                return "$name a obtenu le badge <strong>$badge</strong>";
            case 'reservation':
                return "$name a reserve chez <strong>$resto</strong>";
            default:
                return "$name a effectue une action";
        }
    }

    /**
     * Icône par type d'activité
     */
    public static function getActivityIcon(string $type): string
    {
        return match ($type) {
            'review' => 'fa-star',
            'checkin' => 'fa-map-marker-alt',
            'collection' => 'fa-folder-plus',
            'photo' => 'fa-camera',
            'badge' => 'fa-trophy',
            'reservation' => 'fa-calendar-check',
            default => 'fa-bolt',
        };
    }

    /**
     * Couleur par type d'activité
     */
    public static function getActivityColor(string $type): string
    {
        return match ($type) {
            'review' => '#f59e0b',
            'checkin' => '#10b981',
            'collection' => '#6366f1',
            'photo' => '#ec4899',
            'badge' => '#eab308',
            'reservation' => '#3b82f6',
            default => '#6b7280',
        };
    }
}
