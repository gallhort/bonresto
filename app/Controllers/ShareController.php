<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use App\Services\LoyaltyService;
use App\Services\RateLimiter;
use PDO;

/**
 * F22 - Partage Social
 * Generates share cards with social URLs and logs share actions for loyalty points.
 */
class ShareController extends Controller
{
    /**
     * GET /api/share/card?type=restaurant&id=123
     * Returns JSON with share data: title, description, image, and share URLs
     * for Facebook, Twitter, WhatsApp, Telegram, and copy link.
     */
    public function shareCard(Request $request): void
    {
        header('Content-Type: application/json');

        $type = trim($_GET['type'] ?? '');
        $id   = (int)($_GET['id'] ?? 0);

        if (!in_array($type, ['restaurant', 'activity', 'collection', 'review'], true) || $id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Paramètres invalides (type et id requis)']);
            return;
        }

        $shareData = null;

        switch ($type) {
            case 'restaurant':
                $shareData = $this->getRestaurantShareData($id);
                break;
            case 'activity':
                $shareData = $this->getActivityShareData($id);
                break;
            case 'collection':
                $shareData = $this->getCollectionShareData($id);
                break;
            case 'review':
                $shareData = $this->getReviewShareData($id);
                break;
        }

        if (!$shareData) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Contenu introuvable']);
            return;
        }

        // Build share URLs
        $url         = $shareData['url'];
        $title       = $shareData['title'];
        $description = $shareData['description'];
        $shareText   = $title . ' - ' . $description;

        $encodedUrl  = urlencode($url);
        $encodedText = urlencode($shareText);
        $encodedTitle = urlencode($title);

        $shareUrls = [
            'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $encodedUrl,
            'twitter'   => 'https://twitter.com/intent/tweet?text=' . $encodedTitle . '&url=' . $encodedUrl,
            'whatsapp'  => 'https://wa.me/?text=' . urlencode($shareText . ' ' . $url),
            'telegram'  => 'https://t.me/share/url?url=' . $encodedUrl . '&text=' . $encodedText,
            'copy_link' => $url,
        ];

        echo json_encode([
            'success'     => true,
            'type'        => $type,
            'id'          => $id,
            'title'       => $title,
            'description' => $description,
            'image'       => $shareData['image'] ?? null,
            'url'         => $url,
            'share_urls'  => $shareUrls,
        ]);
    }

    /**
     * POST /api/share/log
     * Logs a share action. Requires auth. Awards 2 loyalty points via LoyaltyService.
     * Body JSON: { shareable_type, shareable_id, platform }
     */
    public function logShare(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        // Rate limit: 20 shares per hour per user
        if (!RateLimiter::attempt("share_{$userId}", 20, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de partages. Réessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $shareableType = trim($input['shareable_type'] ?? '');
        $shareableId   = (int)($input['shareable_id'] ?? 0);
        $platform      = trim($input['platform'] ?? '');

        $allowedTypes     = ['restaurant', 'activity', 'collection', 'review'];
        $allowedPlatforms = ['facebook', 'twitter', 'whatsapp', 'telegram', 'copy_link'];

        if (!in_array($shareableType, $allowedTypes, true) || $shareableId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Type ou ID invalide']);
            return;
        }

        if (!in_array($platform, $allowedPlatforms, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Plateforme invalide']);
            return;
        }

        try {
            // Log the share in the database
            $stmt = $this->db->prepare("
                INSERT INTO share_logs (user_id, shareable_type, shareable_id, platform, created_at)
                VALUES (:user_id, :type, :sid, :platform, NOW())
            ");
            $stmt->execute([
                ':user_id'  => $userId,
                ':type'     => $shareableType,
                ':sid'      => $shareableId,
                ':platform' => $platform,
            ]);

            // Award 2 loyalty points for sharing
            $loyaltyService = new LoyaltyService($this->db);
            $loyaltyService->addPoints($userId, 'share', $shareableId, $shareableType);

            echo json_encode([
                'success' => true,
                'message' => 'Partage enregistré. +2 points de fidélité !',
            ]);

        } catch (\Exception $e) {
            Logger::error("ShareController::logShare error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    // =====================================================
    // HELPERS - Fetch share data by type
    // =====================================================

    /**
     * Build base URL for the site (protocol + host)
     */
    private function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'lebonresto.dz';
        return $protocol . '://' . $host;
    }

    /**
     * Get share data for a restaurant
     */
    private function getRestaurantShareData(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.adresse, r.description,
                   r.note_globale, r.type_cuisine,
                   (SELECT rp.path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) AS photo
            FROM restaurants r
            WHERE r.id = :id AND r.status = 'validated'
        ");
        $stmt->execute([':id' => $id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$r) {
            return null;
        }

        $rating = $r['note_globale'] ? number_format((float)$r['note_globale'], 1) . '/5' : '';
        $desc   = $r['type_cuisine'] ? $r['type_cuisine'] . ' - ' . $r['ville'] : $r['ville'];
        if ($rating) {
            $desc .= ' (' . $rating . ')';
        }

        $base = $this->getBaseUrl();
        $slug = $r['slug'] ?: $r['id'];

        return [
            'title'       => $r['nom'],
            'description' => $desc,
            'image'       => $r['photo'] ? $base . '/' . ltrim($r['photo'], '/') : null,
            'url'         => $base . '/restaurant/' . $slug,
        ];
    }

    /**
     * Get share data for an activity
     */
    private function getActivityShareData(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT a.id, a.name, a.slug, a.city, a.category, a.description,
                   a.main_photo
            FROM activities a
            WHERE a.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$a) {
            return null;
        }

        $base = $this->getBaseUrl();
        $slug = $a['slug'] ?: $a['id'];

        return [
            'title'       => $a['name'],
            'description' => ($a['category'] ? $a['category'] . ' - ' : '') . ($a['city'] ?? ''),
            'image'       => $a['main_photo'] ? $base . '/' . ltrim($a['main_photo'], '/') : null,
            'url'         => $base . '/activite/' . $slug,
        ];
    }

    /**
     * Get share data for a collection
     */
    private function getCollectionShareData(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.name, c.description, c.is_public,
                   u.prenom, u.nom AS user_nom
            FROM collections c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.id = :id AND c.is_public = 1
        ");
        $stmt->execute([':id' => $id]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$c) {
            return null;
        }

        $base      = $this->getBaseUrl();
        $authorName = trim(($c['prenom'] ?? '') . ' ' . ($c['user_nom'] ?? ''));

        return [
            'title'       => $c['name'],
            'description' => $c['description'] ?: ('Collection par ' . $authorName),
            'image'       => null,
            'url'         => $base . '/collections/' . $c['id'],
        ];
    }

    /**
     * Get share data for a review
     */
    private function getReviewShareData(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT rv.id, rv.note, rv.commentaire,
                   r.nom AS restaurant_nom, r.slug AS restaurant_slug, r.id AS restaurant_id,
                   u.prenom, u.nom AS user_nom
            FROM reviews rv
            INNER JOIN restaurants r ON r.id = rv.restaurant_id
            INNER JOIN users u ON u.id = rv.user_id
            WHERE rv.id = :id AND rv.status = 'approved'
        ");
        $stmt->execute([':id' => $id]);
        $rv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rv) {
            return null;
        }

        $base       = $this->getBaseUrl();
        $authorName = trim(($rv['prenom'] ?? '') . ' ' . ($rv['user_nom'] ?? ''));
        $excerpt    = mb_substr(strip_tags($rv['commentaire'] ?? ''), 0, 120);
        if (mb_strlen($rv['commentaire'] ?? '') > 120) {
            $excerpt .= '...';
        }
        $slug = $rv['restaurant_slug'] ?: $rv['restaurant_id'];

        return [
            'title'       => 'Avis sur ' . $rv['restaurant_nom'] . ' par ' . $authorName,
            'description' => $excerpt,
            'image'       => null,
            'url'         => $base . '/restaurant/' . $slug . '#review-' . $rv['id'],
        ];
    }
}
