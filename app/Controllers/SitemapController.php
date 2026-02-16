<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CacheService;

class SitemapController extends Controller
{
    public function index(Request $request): void
    {
        $cache = new CacheService();

        $xml = $cache->remember('sitemap_xml', function () {
            return $this->generateSitemap();
        }, 86400); // Cache 24h

        header('Content-Type: application/xml; charset=UTF-8');
        echo $xml;
    }

    private function generateSitemap(): string
    {
        $baseUrl = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'lebonresto.dz'), '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Pages statiques
        $staticPages = [
            ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => '/restaurants', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => '/search', 'priority' => '0.8', 'changefreq' => 'daily'],
            ['url' => '/register', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => '/login', 'priority' => '0.5', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}{$page['url']}</loc>\n";
            $xml .= "    <changefreq>{$page['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$page['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        // Tous les restaurants validés
        $stmt = $this->db->query("
            SELECT id, slug, updated_at
            FROM restaurants
            WHERE status = 'validated'
            ORDER BY updated_at DESC
        ");
        $restaurants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($restaurants as $r) {
            $slug = !empty($r['slug']) ? $r['slug'] : $r['id'];
            $lastmod = date('Y-m-d', strtotime($r['updated_at']));
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$baseUrl}/restaurant/{$slug}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
