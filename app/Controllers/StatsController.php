<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CacheService;
use PDO;

class StatsController extends Controller
{
    /**
     * F24 - Page statistiques publiques
     * GET /stats
     */
    public function index(): void
    {
        $cache = new CacheService();

        $stats = $cache->remember('public_stats_global', function() {
            return $this->getGlobalStats();
        }, 3600);

        $topCities = $cache->remember('public_stats_top_cities', function() {
            $stmt = $this->db->query("
                SELECT r.ville, COUNT(*) as nb_restos, ROUND(AVG(r.note_moyenne), 1) as avg_note,
                       (SELECT COUNT(*) FROM reviews rv JOIN restaurants r2 ON r2.id = rv.restaurant_id WHERE r2.ville = r.ville AND r2.status = 'validated' AND rv.status = 'approved') as total_avis
                FROM restaurants r
                WHERE r.status = 'validated' AND r.ville IS NOT NULL AND r.ville != ''
                GROUP BY r.ville
                ORDER BY nb_restos DESC
                LIMIT 15
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 3600);

        $topCuisines = $cache->remember('public_stats_top_cuisines', function() {
            $stmt = $this->db->query("
                SELECT type_cuisine, COUNT(*) as nb_restos, ROUND(AVG(note_moyenne), 1) as avg_note
                FROM restaurants
                WHERE status = 'validated' AND type_cuisine IS NOT NULL AND type_cuisine != ''
                GROUP BY type_cuisine
                ORDER BY nb_restos DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 3600);

        $noteDistribution = $cache->remember('public_stats_notes_dist', function() {
            $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            $stmt = $this->db->query("
                SELECT ROUND(note_globale) as star, COUNT(*) as cnt
                FROM reviews WHERE status = 'approved' AND note_globale > 0
                GROUP BY star
            ");
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $s = max(1, min(5, (int)$row['star']));
                $dist[$s] += (int)$row['cnt'];
            }
            return $dist;
        }, 3600);

        $monthlyGrowth = $cache->remember('public_stats_monthly_growth', function() {
            $stmt = $this->db->query("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month_key,
                       DATE_FORMAT(created_at, '%b %Y') as month_label,
                       COUNT(*) as cnt
                FROM restaurants
                WHERE status = 'validated' AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY month_key
                ORDER BY month_key ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 3600);

        $topRestaurants = $cache->remember('public_stats_top_restos', function() {
            $stmt = $this->db->query("
                SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.note_moyenne, r.nb_avis,
                       r.popularity_score, rp.path as main_photo
                FROM restaurants r
                LEFT JOIN restaurant_photos rp ON rp.restaurant_id = r.id AND rp.type = 'main' AND rp.ordre = 0
                WHERE r.status = 'validated' AND r.popularity_score > 0
                GROUP BY r.id
                ORDER BY r.popularity_score DESC
                LIMIT 10
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 1800);

        $this->render('stats.index', [
            'title' => 'Statistiques | Le Bon Resto',
            'meta_description' => 'Decouvrez les statistiques de la plateforme Le Bon Resto : restaurants, avis, villes, cuisines les plus populaires en Algerie.',
            'stats' => $stats,
            'topCities' => $topCities,
            'topCuisines' => $topCuisines,
            'noteDistribution' => $noteDistribution,
            'monthlyGrowth' => $monthlyGrowth,
            'topRestaurants' => $topRestaurants,
        ]);
    }

    /**
     * Stats par ville
     * GET /stats/{ville}
     */
    public function cityStats($request): void
    {
        $ville = $request->param('ville') ?? '';
        if (empty($ville)) {
            $this->redirect('/stats');
            return;
        }

        $ville = urldecode($ville);
        $cache = new CacheService();
        $cacheKey = 'public_stats_city_' . md5($ville);

        $cityData = $cache->remember($cacheKey, function() use ($ville) {
            $params = [':ville' => $ville];

            // KPIs ville
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as nb_restos,
                       ROUND(AVG(note_moyenne), 1) as avg_note,
                       (SELECT COUNT(*) FROM reviews rv JOIN restaurants r2 ON r2.id = rv.restaurant_id WHERE r2.ville = :ville2 AND r2.status = 'validated' AND rv.status = 'approved') as total_avis,
                       SUM(vues_total) as total_vues
                FROM restaurants
                WHERE status = 'validated' AND ville = :ville
            ");
            $params[':ville2'] = $ville;
            $stmt->execute($params);
            $kpis = $stmt->fetch(PDO::FETCH_ASSOC);

            // Top restos de la ville
            $stmt2 = $this->db->prepare("
                SELECT r.id, r.nom, r.slug, r.type_cuisine, r.note_moyenne, r.nb_avis,
                       r.popularity_score, rp.path as main_photo
                FROM restaurants r
                LEFT JOIN restaurant_photos rp ON rp.restaurant_id = r.id AND rp.type = 'main' AND rp.ordre = 0
                WHERE r.status = 'validated' AND r.ville = :ville
                GROUP BY r.id
                ORDER BY r.popularity_score DESC
                LIMIT 10
            ");
            $stmt2->execute($params);
            $topRestos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Cuisines populaires dans la ville
            $stmt3 = $this->db->prepare("
                SELECT type_cuisine, COUNT(*) as cnt
                FROM restaurants
                WHERE status = 'validated' AND ville = :ville AND type_cuisine IS NOT NULL AND type_cuisine != ''
                GROUP BY type_cuisine
                ORDER BY cnt DESC
                LIMIT 8
            ");
            $stmt3->execute($params);
            $cuisines = $stmt3->fetchAll(PDO::FETCH_ASSOC);

            // Distribution des notes dans la ville
            $stmt4 = $this->db->prepare("
                SELECT ROUND(rv.note_globale) as star, COUNT(*) as cnt
                FROM reviews rv
                JOIN restaurants r ON r.id = rv.restaurant_id
                WHERE rv.status = 'approved' AND r.ville = :ville AND rv.note_globale > 0
                GROUP BY star
            ");
            $stmt4->execute($params);
            $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($stmt4->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $s = max(1, min(5, (int)$row['star']));
                $dist[$s] += (int)$row['cnt'];
            }

            return [
                'kpis' => $kpis,
                'topRestos' => $topRestos,
                'cuisines' => $cuisines,
                'noteDistribution' => $dist,
            ];
        }, 3600);

        if (empty($cityData['kpis']['nb_restos'])) {
            $this->notFound('Aucune statistique pour cette ville');
            return;
        }

        $this->render('stats.city', [
            'title' => 'Statistiques ' . $ville . ' | Le Bon Resto',
            'meta_description' => 'Statistiques des restaurants a ' . $ville . ' : top restaurants, cuisines populaires, notes moyennes.',
            'ville' => $ville,
            'cityData' => $cityData,
        ]);
    }

    private function getGlobalStats(): array
    {
        $s = [];

        $row = $this->db->query("
            SELECT COUNT(*) as total,
                   ROUND(AVG(note_moyenne), 1) as avg_note,
                   SUM(vues_total) as total_vues,
                   COUNT(DISTINCT ville) as nb_villes
            FROM restaurants WHERE status = 'validated'
        ")->fetch(PDO::FETCH_ASSOC);

        $s['total_restaurants'] = (int)($row['total'] ?? 0);
        $s['avg_note'] = (float)($row['avg_note'] ?? 0);
        $s['total_vues'] = (int)($row['total_vues'] ?? 0);
        $s['nb_villes'] = (int)($row['nb_villes'] ?? 0);

        $s['total_avis'] = (int)$this->db->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn();
        $s['total_users'] = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $s['total_orders'] = (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $s['total_activities'] = (int)$this->db->query("SELECT COUNT(*) FROM activities")->fetchColumn();

        return $s;
    }
}
