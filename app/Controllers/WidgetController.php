<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use App\Services\RateLimiter;
use PDO;

/**
 * F23 - Widget Embarquable
 * Allows restaurant owners to generate an embeddable iframe widget
 * showing their restaurant card, rating, and latest reviews.
 */
class WidgetController extends Controller
{
    /**
     * GET /widget/{token}
     * Renders a standalone HTML page (no layout) for embedding via iframe.
     * Shows restaurant card with name, rating, and latest reviews.
     */
    public function embed(Request $request): void
    {
        $token = trim($request->param('token', ''));

        if (strlen($token) < 10) {
            http_response_code(400);
            echo '<!DOCTYPE html><html><body><p>Widget invalide.</p></body></html>';
            return;
        }

        // Fetch widget config
        $stmt = $this->db->prepare("
            SELECT w.id, w.restaurant_id, w.token, w.theme, w.max_reviews, w.show_rating, w.show_reviews,
                   r.nom, r.slug, r.ville, r.adresse, r.note_globale, r.type_cuisine, r.nombre_avis
            FROM restaurant_widgets w
            INNER JOIN restaurants r ON r.id = w.restaurant_id
            WHERE w.token = :token AND r.status = 'validated'
        ");
        $stmt->execute([':token' => $token]);
        $widget = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$widget) {
            http_response_code(404);
            echo '<!DOCTYPE html><html><body><p>Widget introuvable.</p></body></html>';
            return;
        }

        // Fetch main photo
        $photoStmt = $this->db->prepare("
            SELECT path FROM restaurant_photos
            WHERE restaurant_id = :rid AND type = 'main'
            LIMIT 1
        ");
        $photoStmt->execute([':rid' => $widget['restaurant_id']]);
        $mainPhoto = $photoStmt->fetchColumn() ?: null;

        // Fetch latest approved reviews if enabled
        $reviews = [];
        if ((int)($widget['show_reviews'] ?? 1)) {
            $maxReviews = max(1, min(10, (int)($widget['max_reviews'] ?? 3)));
            $reviewStmt = $this->db->prepare("
                SELECT rv.note, rv.commentaire, rv.created_at,
                       u.prenom, u.nom AS user_nom
                FROM reviews rv
                INNER JOIN users u ON u.id = rv.user_id
                WHERE rv.restaurant_id = :rid AND rv.status = 'approved'
                ORDER BY rv.created_at DESC
                LIMIT :maxr
            ");
            $reviewStmt->bindValue(':rid', (int)$widget['restaurant_id'], PDO::PARAM_INT);
            $reviewStmt->bindValue(':maxr', $maxReviews, PDO::PARAM_INT);
            $reviewStmt->execute();
            $reviews = $reviewStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Track widget impression
        try {
            $this->db->prepare("
                UPDATE restaurant_widgets SET impressions = impressions + 1 WHERE id = :wid
            ")->execute([':wid' => $widget['id']]);
        } catch (\Exception $e) {
            // Non-critical
        }

        // Build base URL for links
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'lebonresto.dz';
        $baseUrl  = $protocol . '://' . $host;
        $slug     = $widget['slug'] ?: $widget['restaurant_id'];
        $restaurantUrl = $baseUrl . '/restaurant/' . htmlspecialchars($slug);

        $theme = ($widget['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
        $showRating = (int)($widget['show_rating'] ?? 1);

        // Render standalone HTML (no layout/View::render)
        $this->renderWidgetHtml($widget, $mainPhoto, $reviews, $restaurantUrl, $baseUrl, $theme, $showRating);
    }

    /**
     * POST /api/widget/create
     * Owner creates a widget for their restaurant.
     * Generates a unique token. Returns widget config + embed code.
     */
    public function create(Request $request): void
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

        // Rate limit: 10 widget creations per hour
        if (!RateLimiter::attempt("widget_create_{$userId}", 10, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de requêtes. Réessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $restaurantId = (int)($input['restaurant_id'] ?? 0);
        $theme        = in_array($input['theme'] ?? '', ['light', 'dark'], true) ? $input['theme'] : 'light';
        $maxReviews   = max(1, min(10, (int)($input['max_reviews'] ?? 3)));
        $showRating   = (int)(!empty($input['show_rating']));
        $showReviews  = (int)(!empty($input['show_reviews']));

        // Verify ownership
        $restaurant = $this->getOwnedRestaurant($restaurantId, $userId);
        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Restaurant introuvable ou non autorisé']);
            return;
        }

        try {
            // Check if widget already exists for this restaurant
            $existStmt = $this->db->prepare("
                SELECT id, token FROM restaurant_widgets WHERE restaurant_id = :rid LIMIT 1
            ");
            $existStmt->execute([':rid' => $restaurantId]);
            $existing = $existStmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Update existing widget configuration
                $updateStmt = $this->db->prepare("
                    UPDATE restaurant_widgets
                    SET theme = :theme, max_reviews = :maxr, show_rating = :sr, show_reviews = :srev, updated_at = NOW()
                    WHERE id = :wid
                ");
                $updateStmt->execute([
                    ':theme' => $theme,
                    ':maxr'  => $maxReviews,
                    ':sr'    => $showRating,
                    ':srev'  => $showReviews,
                    ':wid'   => $existing['id'],
                ]);
                $widgetToken = $existing['token'];
            } else {
                // Create new widget with unique token
                $widgetToken = bin2hex(random_bytes(16));

                $insertStmt = $this->db->prepare("
                    INSERT INTO restaurant_widgets (restaurant_id, token, theme, max_reviews, show_rating, show_reviews, impressions, created_at, updated_at)
                    VALUES (:rid, :token, :theme, :maxr, :sr, :srev, 0, NOW(), NOW())
                ");
                $insertStmt->execute([
                    ':rid'   => $restaurantId,
                    ':token' => $widgetToken,
                    ':theme' => $theme,
                    ':maxr'  => $maxReviews,
                    ':sr'    => $showRating,
                    ':srev'  => $showReviews,
                ]);
            }

            // Build embed URL and snippet
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host     = $_SERVER['HTTP_HOST'] ?? 'lebonresto.dz';
            $baseUrl  = $protocol . '://' . $host;
            $embedUrl = $baseUrl . '/widget/' . $widgetToken;

            $embedCode = '<iframe src="' . htmlspecialchars($embedUrl) . '" '
                . 'width="350" height="450" frameborder="0" '
                . 'style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;" '
                . 'title="' . htmlspecialchars($restaurant['nom']) . ' - LeBonResto">'
                . '</iframe>';

            echo json_encode([
                'success'    => true,
                'token'      => $widgetToken,
                'embed_url'  => $embedUrl,
                'embed_code' => $embedCode,
                'config'     => [
                    'theme'        => $theme,
                    'max_reviews'  => $maxReviews,
                    'show_rating'  => $showRating,
                    'show_reviews' => $showReviews,
                ],
            ]);

        } catch (\Exception $e) {
            Logger::error("WidgetController::create error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * GET /api/widget/{restaurantId}/code
     * Returns the embed HTML snippet for the owner to copy.
     */
    public function apiGetCode(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $restaurantId = (int)$request->param('restaurantId');
        $userId       = (int)$_SESSION['user']['id'];

        // Verify ownership
        $restaurant = $this->getOwnedRestaurant($restaurantId, $userId);
        if (!$restaurant) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Restaurant introuvable ou non autorisé']);
            return;
        }

        $stmt = $this->db->prepare("
            SELECT token, theme, max_reviews, show_rating, show_reviews, impressions
            FROM restaurant_widgets
            WHERE restaurant_id = :rid
            LIMIT 1
        ");
        $stmt->execute([':rid' => $restaurantId]);
        $widget = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$widget) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Aucun widget trouvé. Créez-en un d\'abord.']);
            return;
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'lebonresto.dz';
        $baseUrl  = $protocol . '://' . $host;
        $embedUrl = $baseUrl . '/widget/' . $widget['token'];

        $embedCode = '<iframe src="' . htmlspecialchars($embedUrl) . '" '
            . 'width="350" height="450" frameborder="0" '
            . 'style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;" '
            . 'title="' . htmlspecialchars($restaurant['nom']) . ' - LeBonResto">'
            . '</iframe>';

        echo json_encode([
            'success'     => true,
            'token'       => $widget['token'],
            'embed_url'   => $embedUrl,
            'embed_code'  => $embedCode,
            'impressions' => (int)$widget['impressions'],
            'config'      => [
                'theme'        => $widget['theme'],
                'max_reviews'  => (int)$widget['max_reviews'],
                'show_rating'  => (int)$widget['show_rating'],
                'show_reviews' => (int)$widget['show_reviews'],
            ],
        ]);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Get restaurant owned by a specific user
     */
    private function getOwnedRestaurant(int $restaurantId, int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, nom, slug FROM restaurants WHERE id = :rid AND owner_id = :uid
        ");
        $stmt->execute([':rid' => $restaurantId, ':uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Render standalone widget HTML (no layout).
     * Outputs directly and returns.
     */
    private function renderWidgetHtml(
        array $widget,
        ?string $mainPhoto,
        array $reviews,
        string $restaurantUrl,
        string $baseUrl,
        string $theme,
        int $showRating
    ): void {
        $bgColor   = $theme === 'dark' ? '#1f2937' : '#ffffff';
        $textColor = $theme === 'dark' ? '#f9fafb' : '#1f2937';
        $mutedColor = $theme === 'dark' ? '#9ca3af' : '#6b7280';
        $borderColor = $theme === 'dark' ? '#374151' : '#e5e7eb';
        $cardBg    = $theme === 'dark' ? '#111827' : '#f9fafb';
        $starColor = '#f59e0b';

        $nom = htmlspecialchars($widget['nom']);
        $ville = htmlspecialchars($widget['ville'] ?? '');
        $cuisine = htmlspecialchars($widget['type_cuisine'] ?? '');
        $note = $widget['note_globale'] ? number_format((float)$widget['note_globale'], 1) : null;
        $nbAvis = (int)($widget['nombre_avis'] ?? 0);

        $photoHtml = '';
        if ($mainPhoto) {
            $photoSrc = htmlspecialchars($baseUrl . '/' . ltrim($mainPhoto, '/'));
            $photoHtml = '<img src="' . $photoSrc . '" alt="' . $nom . '" style="width:100%;height:140px;object-fit:cover;border-radius:8px 8px 0 0;">';
        }

        // Build star display
        $starsHtml = '';
        if ($showRating && $note !== null) {
            $fullStars = (int)floor((float)$note);
            $halfStar  = ((float)$note - $fullStars) >= 0.5;
            for ($i = 0; $i < $fullStars; $i++) {
                $starsHtml .= '<span style="color:' . $starColor . ';">&#9733;</span>';
            }
            if ($halfStar) {
                $starsHtml .= '<span style="color:' . $starColor . ';">&#9733;</span>';
                $fullStars++;
            }
            for ($i = $fullStars + ($halfStar ? 0 : 0); $i < 5; $i++) {
                if ($halfStar && $i === $fullStars) continue;
                $starsHtml .= '<span style="color:' . $borderColor . ';">&#9733;</span>';
            }
        }

        // Build reviews HTML
        $reviewsHtml = '';
        foreach ($reviews as $rv) {
            $authorName = htmlspecialchars(trim(($rv['prenom'] ?? '') . ' ' . ($rv['user_nom'] ?? '')));
            $rvNote     = (int)$rv['note'];
            $rvText     = htmlspecialchars(mb_substr(strip_tags($rv['commentaire'] ?? ''), 0, 100));
            if (mb_strlen($rv['commentaire'] ?? '') > 100) {
                $rvText .= '...';
            }
            $rvDate  = date('d/m/Y', strtotime($rv['created_at']));
            $rvStars = str_repeat('&#9733;', $rvNote) . str_repeat('&#9734;', 5 - $rvNote);

            $reviewsHtml .= '
            <div style="padding:8px 0;border-bottom:1px solid ' . $borderColor . ';">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <strong style="font-size:12px;">' . $authorName . '</strong>
                    <span style="font-size:11px;color:' . $mutedColor . ';">' . $rvDate . '</span>
                </div>
                <div style="color:' . $starColor . ';font-size:12px;margin-bottom:4px;">' . $rvStars . '</div>
                <p style="font-size:12px;color:' . $textColor . ';margin:0;line-height:1.4;">' . $rvText . '</p>
            </div>';
        }

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $nom . ' - LeBonResto Widget</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: ' . $bgColor . ';
            color: ' . $textColor . ';
            padding: 12px;
        }
        a { color: inherit; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div style="background:' . $cardBg . ';border-radius:8px;border:1px solid ' . $borderColor . ';overflow:hidden;">
        ' . $photoHtml . '
        <div style="padding:12px;">
            <a href="' . htmlspecialchars($restaurantUrl) . '" target="_blank" rel="noopener">
                <h2 style="font-size:16px;margin-bottom:4px;">' . $nom . '</h2>
            </a>
            <p style="font-size:12px;color:' . $mutedColor . ';margin-bottom:8px;">'
                . ($cuisine ? $cuisine . ' &middot; ' : '') . $ville . '</p>';

        if ($showRating && $note !== null) {
            $html .= '
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:10px;">
                <span style="font-size:18px;font-weight:700;">' . $note . '</span>
                <span style="font-size:14px;">' . $starsHtml . '</span>
                <span style="font-size:11px;color:' . $mutedColor . ';">(' . $nbAvis . ' avis)</span>
            </div>';
        }

        if (!empty($reviewsHtml)) {
            $html .= '<div style="margin-top:8px;">' . $reviewsHtml . '</div>';
        }

        $html .= '
            <div style="margin-top:12px;text-align:center;">
                <a href="' . htmlspecialchars($restaurantUrl) . '" target="_blank" rel="noopener"
                   style="display:inline-block;padding:6px 16px;background:#f97316;color:#fff;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;">
                    Voir sur LeBonResto
                </a>
            </div>
        </div>
    </div>
    <div style="text-align:center;margin-top:8px;">
        <a href="' . htmlspecialchars($baseUrl) . '" target="_blank" rel="noopener"
           style="font-size:10px;color:' . $mutedColor . ';">
            Propuls&eacute; par LeBonResto
        </a>
    </div>
</body>
</html>';

        echo $html;
    }
}
