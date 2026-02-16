<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CacheService;
use PDO;

class LeaderboardController extends Controller
{
    /**
     * Page classement
     * GET /classement or GET /classement/{ville}
     */
    public function index(Request $request): void
    {
        $ville = $request->param('ville') ?? ($_GET['ville'] ?? null);
        $period = $_GET['period'] ?? 'month'; // month, all
        $cache = new CacheService();

        // Get available cities with contributors (cache 1h)
        $cities = $cache->remember('leaderboard_cities', function() {
            $citiesStmt = $this->db->query("
                SELECT DISTINCT r.ville, COUNT(DISTINCT rev.user_id) as contributors
                FROM reviews rev
                INNER JOIN restaurants r ON r.id = rev.restaurant_id
                WHERE rev.status = 'approved' AND r.ville IS NOT NULL AND r.ville != ''
                GROUP BY r.ville
                HAVING contributors >= 1
                ORDER BY contributors DESC
                LIMIT 20
            ");
            return $citiesStmt->fetchAll(PDO::FETCH_ASSOC);
        }, 3600);

        // Sanitize period to prevent cache key pollution
        if ($period !== 'month' && $period !== 'all') {
            $period = 'month';
        }

        // Leaderboard results (cache 1h)
        $cacheKey = 'leaderboard_' . ($ville ?? 'all') . '_' . $period;
        $db = $this->db;

        $leaderboard = $cache->remember($cacheKey, function() use ($db, $ville, $period) {
            $params = [];
            $villeClause = '';
            $periodClause = '';

            if ($ville) {
                $villeClause = 'AND r.ville = :ville';
                $params[':ville'] = $ville;
            }

            if ($period === 'month') {
                $periodClause = 'AND rev.created_at >= DATE_FORMAT(NOW(), "%Y-%m-01")';
            }

            $sql = "
                SELECT u.id, u.prenom, u.nom, u.photo_profil, u.badge, u.points,
                       COUNT(DISTINCT rev.id) as review_count,
                       COUNT(DISTINCT rph.id) as photo_count,
                       ROUND(AVG(rev.note_globale), 1) as avg_rating,
                       COUNT(DISTINCT rev.restaurant_id) as restaurants_visited
                FROM users u
                INNER JOIN reviews rev ON rev.user_id = u.id AND rev.status = 'approved' $periodClause
                INNER JOIN restaurants r ON r.id = rev.restaurant_id $villeClause
                LEFT JOIN review_photos rph ON rph.review_id = rev.id
                GROUP BY u.id, u.prenom, u.nom, u.photo_profil, u.badge, u.points
                ORDER BY review_count DESC, photo_count DESC, u.points DESC
                LIMIT 30
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 3600);

        // Current user rank (NOT cached - user-specific)
        $myRank = null;
        if ($this->isAuthenticated()) {
            $myId = (int)$_SESSION['user']['id'];
            foreach ($leaderboard as $i => $entry) {
                if ((int)$entry['id'] === $myId) {
                    $myRank = $i + 1;
                    break;
                }
            }
        }

        $this->render('leaderboard/index', [
            'title' => 'Classement' . ($ville ? ' - ' . $ville : ''),
            'leaderboard' => $leaderboard,
            'cities' => $cities,
            'currentVille' => $ville,
            'currentPeriod' => $period,
            'myRank' => $myRank,
        ]);
    }
}
