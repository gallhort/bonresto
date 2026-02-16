<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CacheService;
use PDO;

/**
 * F30 - Peak Hours Controller
 * Analyse des heures de pointe basee sur les analytics_events
 * Retourne une grille 7 jours x 24 heures de frequentation
 */
class PeakHoursController extends Controller
{
    /**
     * Recuperer les heures de pointe pour un restaurant
     * GET /api/restaurants/{id}/peak-hours
     *
     * Analyse les vues des 30 derniers jours pour determiner
     * les moments les plus frequentes (heatmap jour/heure)
     */
    public function getForRestaurant(Request $request): void
    {
        header('Content-Type: application/json');

        $restaurantId = (int)$request->param('id');

        if ($restaurantId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID restaurant invalide']);
            return;
        }

        // Verify restaurant exists
        $checkStmt = $this->db->prepare("SELECT id, nom FROM restaurants WHERE id = :rid");
        $checkStmt->execute([':rid' => $restaurantId]);
        $restaurant = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant introuvable']);
            return;
        }

        // Cache for 6 hours (21600 seconds)
        $cache = new CacheService();
        $cacheKey = "peak_hours_restaurant_{$restaurantId}";

        $data = $cache->remember($cacheKey, function () use ($restaurantId) {
            return $this->computePeakHours($restaurantId);
        }, 21600);

        echo json_encode([
            'success' => true,
            'restaurant_id' => $restaurantId,
            'restaurant_name' => $restaurant['nom'],
            'period' => '30_days',
            'grid' => $data['grid'],
            'peak_times' => $data['peak_times'],
            'total_views' => $data['total_views'],
        ]);
    }

    /**
     * Calculer la grille d'heures de pointe
     * DAYOFWEEK: 1=Dimanche, 2=Lundi, ..., 7=Samedi
     */
    private function computePeakHours(int $restaurantId): array
    {
        $start = date('Y-m-d H:i:s', strtotime('-30 days'));

        $stmt = $this->db->prepare("
            SELECT DAYOFWEEK(created_at) AS dow,
                   HOUR(created_at) AS h,
                   COUNT(*) AS cnt
            FROM analytics_events
            WHERE event_type = 'view'
              AND restaurant_id = :rid
              AND created_at >= :start
            GROUP BY dow, h
        ");
        $stmt->execute([
            ':rid' => $restaurantId,
            ':start' => $start,
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Initialize 7x24 grid (index 0-6 for days, 0-23 for hours)
        // Map DAYOFWEEK (1=Sun..7=Sat) to our index (0=Lun..6=Dim)
        $dayMap = [
            2 => 0, // Lundi
            3 => 1, // Mardi
            4 => 2, // Mercredi
            5 => 3, // Jeudi
            6 => 4, // Vendredi
            7 => 5, // Samedi
            1 => 6, // Dimanche
        ];

        $dayNames = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];

        $grid = [];
        for ($d = 0; $d < 7; $d++) {
            $grid[$d] = array_fill(0, 24, 0);
        }

        $totalViews = 0;
        $maxCount = 0;

        foreach ($rows as $row) {
            $dow = (int)$row['dow'];
            $hour = (int)$row['h'];
            $count = (int)$row['cnt'];

            $dayIndex = $dayMap[$dow] ?? null;
            if ($dayIndex === null || $hour < 0 || $hour > 23) {
                continue;
            }

            $grid[$dayIndex][$hour] = $count;
            $totalViews += $count;
            if ($count > $maxCount) {
                $maxCount = $count;
            }
        }

        // Normalize grid to 0-100 intensity scale
        $normalizedGrid = [];
        for ($d = 0; $d < 7; $d++) {
            $normalizedGrid[$d] = [
                'day' => $dayNames[$d],
                'day_index' => $d,
                'hours' => [],
            ];
            for ($h = 0; $h < 24; $h++) {
                $raw = $grid[$d][$h];
                $intensity = $maxCount > 0 ? round(($raw / $maxCount) * 100) : 0;
                $normalizedGrid[$d]['hours'][$h] = [
                    'hour' => $h,
                    'count' => $raw,
                    'intensity' => $intensity,
                    'label' => $this->getIntensityLabel($intensity),
                ];
            }
        }

        // Find top 5 peak times
        $peakTimes = [];
        foreach ($rows as $row) {
            $dow = (int)$row['dow'];
            $dayIndex = $dayMap[$dow] ?? null;
            if ($dayIndex === null) continue;

            $peakTimes[] = [
                'day' => $dayNames[$dayIndex],
                'day_index' => $dayIndex,
                'hour' => (int)$row['h'],
                'hour_label' => sprintf('%02d:00 - %02d:59', (int)$row['h'], (int)$row['h']),
                'count' => (int)$row['cnt'],
            ];
        }

        // Sort by count descending, take top 5
        usort($peakTimes, function ($a, $b) {
            return $b['count'] - $a['count'];
        });
        $peakTimes = array_slice($peakTimes, 0, 5);

        return [
            'grid' => $normalizedGrid,
            'peak_times' => $peakTimes,
            'total_views' => $totalViews,
        ];
    }

    /**
     * Label textuel pour un niveau d'intensite
     */
    private function getIntensityLabel(int $intensity): string
    {
        if ($intensity === 0) return 'vide';
        if ($intensity <= 25) return 'calme';
        if ($intensity <= 50) return 'modere';
        if ($intensity <= 75) return 'frequente';
        return 'tres_frequente';
    }
}
