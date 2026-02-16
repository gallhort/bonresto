<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\Logger;
use App\Services\RateLimiter;
use PDO;

/**
 * PostController - F13 Restaurant Posts / Feed
 *
 * Allows restaurant owners to publish posts (news, promos, events, photos, menu updates)
 * and visitors to view the feed and like posts.
 */
class PostController extends Controller
{
    /**
     * GET /restaurant/{id}/posts
     * Public feed page showing all posts for a restaurant.
     */
    public function feed(Request $request): void
    {
        $restaurantId = (int)$request->param('id');

        // Fetch restaurant
        $stmt = $this->db->prepare("
            SELECT id, nom, slug, adresse, ville, description, owner_id
            FROM restaurants
            WHERE id = :id AND status = 'validated'
        ");
        $stmt->execute([':id' => $restaurantId]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            $this->notFound('Restaurant non trouve');
            return;
        }

        // Fetch posts with author info
        $postsStmt = $this->db->prepare("
            SELECT p.id, p.restaurant_id, p.user_id, p.type, p.title, p.content,
                   p.photo_path, p.is_pinned, p.likes_count, p.created_at, p.updated_at,
                   u.prenom AS author_prenom, u.nom AS author_nom, u.photo_profil AS author_photo
            FROM restaurant_posts p
            INNER JOIN users u ON u.id = p.user_id
            WHERE p.restaurant_id = :rid
            ORDER BY p.is_pinned DESC, p.created_at DESC
            LIMIT 20
        ");
        $postsStmt->execute([':rid' => $restaurantId]);
        $posts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if current user liked each post
        $userId = $this->isAuthenticated() ? (int)$_SESSION['user']['id'] : null;
        if ($userId && !empty($posts)) {
            $postIds = array_column($posts, 'id');
            $namedPlaceholders = [];
            $params = [':uid' => $userId];
            foreach (array_values($postIds) as $i => $pid) {
                $key = ':pid' . $i;
                $namedPlaceholders[] = $key;
                $params[$key] = (int)$pid;
            }
            $inClause = implode(',', $namedPlaceholders);
            $likesStmt = $this->db->prepare("
                SELECT post_id FROM restaurant_post_likes
                WHERE user_id = :uid AND post_id IN ($inClause)
            ");
            $likesStmt->execute($params);
            $likedPostIds = $likesStmt->fetchAll(PDO::FETCH_COLUMN);
            $likedMap = array_flip($likedPostIds);

            foreach ($posts as &$post) {
                $post['user_liked'] = isset($likedMap[$post['id']]);
            }
            unset($post);
        } else {
            foreach ($posts as &$post) {
                $post['user_liked'] = false;
            }
            unset($post);
        }

        $this->render('posts.feed', [
            'title' => 'Actualites - ' . $restaurant['nom'],
            'restaurant' => $restaurant,
            'posts' => $posts,
        ]);
    }

    /**
     * GET /api/restaurant/{id}/posts
     * JSON API for posts (AJAX pagination via ?offset=).
     */
    public function apiList(Request $request): void
    {
        header('Content-Type: application/json');

        $restaurantId = (int)$request->param('id');
        $offset = max(0, (int)($request->query('offset', 0)));
        $limit = 20;

        // Verify restaurant exists
        $checkStmt = $this->db->prepare("
            SELECT id FROM restaurants WHERE id = :id AND status = 'validated'
        ");
        $checkStmt->execute([':id' => $restaurantId]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant non trouve']);
            return;
        }

        // Fetch posts
        $postsStmt = $this->db->prepare("
            SELECT p.id, p.restaurant_id, p.user_id, p.type, p.title, p.content,
                   p.photo_path, p.is_pinned, p.likes_count, p.created_at, p.updated_at,
                   u.prenom AS author_prenom, u.nom AS author_nom, u.photo_profil AS author_photo
            FROM restaurant_posts p
            INNER JOIN users u ON u.id = p.user_id
            WHERE p.restaurant_id = :rid
            ORDER BY p.is_pinned DESC, p.created_at DESC
            LIMIT :lim OFFSET :off
        ");
        $postsStmt->bindValue(':rid', $restaurantId, PDO::PARAM_INT);
        $postsStmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $postsStmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $postsStmt->execute();
        $posts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Check user likes if authenticated
        $userId = $this->isAuthenticated() ? (int)$_SESSION['user']['id'] : null;
        if ($userId && !empty($posts)) {
            $postIds = array_column($posts, 'id');
            $namedPlaceholders = [];
            $params = [':uid' => $userId];
            foreach (array_values($postIds) as $i => $pid) {
                $key = ':pid' . $i;
                $namedPlaceholders[] = $key;
                $params[$key] = (int)$pid;
            }
            $inClause = implode(',', $namedPlaceholders);
            $likesStmt = $this->db->prepare("
                SELECT post_id FROM restaurant_post_likes
                WHERE user_id = :uid AND post_id IN ($inClause)
            ");
            $likesStmt->execute($params);
            $likedPostIds = $likesStmt->fetchAll(PDO::FETCH_COLUMN);
            $likedMap = array_flip($likedPostIds);

            foreach ($posts as &$post) {
                $post['user_liked'] = isset($likedMap[$post['id']]);
            }
            unset($post);
        } else {
            foreach ($posts as &$post) {
                $post['user_liked'] = false;
            }
            unset($post);
        }

        // Total count for pagination
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM restaurant_posts WHERE restaurant_id = :rid
        ");
        $countStmt->execute([':rid' => $restaurantId]);
        $total = (int)$countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'posts' => $posts,
            'total' => $total,
            'has_more' => ($offset + $limit) < $total,
        ]);
    }

    /**
     * POST /api/restaurant/{id}/posts
     * Create a new post (owner only).
     */
    public function store(Request $request): void
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

        $restaurantId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];

        // Verify ownership
        $ownerStmt = $this->db->prepare("
            SELECT id FROM restaurants WHERE id = :id AND owner_id = :uid
        ");
        $ownerStmt->execute([':id' => $restaurantId, ':uid' => $userId]);
        if (!$ownerStmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Vous n\'etes pas proprietaire de ce restaurant']);
            return;
        }

        // Rate limit: 10 posts per day (86400 seconds)
        if (!RateLimiter::attempt("post_create_{$userId}", 10, 86400)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Limite atteinte. Vous pouvez publier 10 posts par jour maximum.']);
            return;
        }

        // Read form data (sent as FormData for file upload support)
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $type = trim($_POST['type'] ?? 'news');

        // Validate type
        $validTypes = ['news', 'promo', 'event', 'photo', 'menu_update'];
        if (!in_array($type, $validTypes, true)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Type de post invalide']);
            return;
        }

        // Validate title
        if ($title === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le titre est obligatoire']);
            return;
        }
        if (mb_strlen($title) > 200) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le titre ne doit pas depasser 200 caracteres']);
            return;
        }

        // Validate content
        if (mb_strlen($content) > 2000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le contenu ne doit pas depasser 2000 caracteres']);
            return;
        }

        // Handle optional photo upload
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['photo'];

            // Validate file type
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowedMimes, true)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Format d\'image non supporte. Utilisez JPG, PNG, WebP ou GIF.']);
                return;
            }

            // Validate file size (max 5 MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'L\'image ne doit pas depasser 5 Mo']);
                return;
            }

            // Determine extension from mime
            $extMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
            ];
            $ext = $extMap[$mimeType] ?? 'jpg';

            // Generate unique filename
            $filename = 'post_' . $restaurantId . '_' . uniqid() . '.' . $ext;
            $uploadDir = ROOT_PATH . '/public/uploads/posts/';

            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . $filename;
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'upload de l\'image']);
                return;
            }

            $photoPath = '/uploads/posts/' . $filename;
        }

        // Sanitize content
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $content = $content !== '' ? htmlspecialchars($content, ENT_QUOTES, 'UTF-8') : null;

        // Insert post
        try {
            $insertStmt = $this->db->prepare("
                INSERT INTO restaurant_posts (restaurant_id, user_id, type, title, content, photo_path)
                VALUES (:rid, :uid, :type, :title, :content, :photo)
            ");
            $insertStmt->execute([
                ':rid'     => $restaurantId,
                ':uid'     => $userId,
                ':type'    => $type,
                ':title'   => $title,
                ':content' => $content,
                ':photo'   => $photoPath,
            ]);

            $postId = (int)$this->db->lastInsertId();

            echo json_encode([
                'success' => true,
                'post_id' => $postId,
                'message' => 'Post publie avec succes',
            ]);
        } catch (\Exception $e) {
            Logger::error('PostController::store error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de la creation du post']);
        }
    }

    /**
     * POST /api/restaurant/posts/{id}/delete
     * Delete a post (owner only).
     */
    public function delete(Request $request): void
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

        $postId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];

        // Get the post and verify ownership of the restaurant
        $postStmt = $this->db->prepare("
            SELECT p.id, p.restaurant_id, p.photo_path
            FROM restaurant_posts p
            INNER JOIN restaurants r ON r.id = p.restaurant_id
            WHERE p.id = :pid AND r.owner_id = :uid
        ");
        $postStmt->execute([':pid' => $postId, ':uid' => $userId]);
        $post = $postStmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Post non trouve ou non autorise']);
            return;
        }

        // Delete photo file if it exists
        if ($post['photo_path']) {
            $fullPath = ROOT_PATH . '/public' . $post['photo_path'];
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        // Delete the post (cascades to likes via FK)
        try {
            $deleteStmt = $this->db->prepare("
                DELETE FROM restaurant_posts WHERE id = :id
            ");
            $deleteStmt->execute([':id' => $postId]);

            echo json_encode(['success' => true, 'message' => 'Post supprime']);
        } catch (\Exception $e) {
            Logger::error('PostController::delete error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de la suppression']);
        }
    }

    /**
     * POST /api/restaurant/posts/{id}/like
     * Toggle like on a post (authenticated users).
     */
    public function toggleLike(Request $request): void
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

        $postId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];

        // Verify post exists
        $postStmt = $this->db->prepare("
            SELECT id, likes_count FROM restaurant_posts WHERE id = :pid
        ");
        $postStmt->execute([':pid' => $postId]);
        $post = $postStmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Post non trouve']);
            return;
        }

        // Check if already liked
        $likeStmt = $this->db->prepare("
            SELECT id FROM restaurant_post_likes
            WHERE post_id = :pid AND user_id = :uid
        ");
        $likeStmt->execute([':pid' => $postId, ':uid' => $userId]);
        $existingLike = $likeStmt->fetch(PDO::FETCH_ASSOC);

        $this->db->beginTransaction();
        try {
            if ($existingLike) {
                // Unlike: remove like and decrement count
                $this->db->prepare("
                    DELETE FROM restaurant_post_likes WHERE post_id = :pid AND user_id = :uid
                ")->execute([':pid' => $postId, ':uid' => $userId]);

                $this->db->prepare("
                    UPDATE restaurant_posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = :pid
                ")->execute([':pid' => $postId]);

                $liked = false;
            } else {
                // Like: insert like and increment count
                $this->db->prepare("
                    INSERT INTO restaurant_post_likes (post_id, user_id) VALUES (:pid, :uid)
                ")->execute([':pid' => $postId, ':uid' => $userId]);

                $this->db->prepare("
                    UPDATE restaurant_posts SET likes_count = likes_count + 1 WHERE id = :pid
                ")->execute([':pid' => $postId]);

                $liked = true;
            }

            $this->db->commit();

            // Fetch updated count
            $countStmt = $this->db->prepare("
                SELECT likes_count FROM restaurant_posts WHERE id = :pid
            ");
            $countStmt->execute([':pid' => $postId]);
            $likesCount = (int)$countStmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'liked' => $liked,
                'likes_count' => $likesCount,
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('PostController::toggleLike error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * POST /api/restaurant/posts/{id}/pin
     * Toggle pin on a post (owner only).
     */
    public function togglePin(Request $request): void
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

        $postId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];

        // Get the post and verify ownership of the restaurant
        $postStmt = $this->db->prepare("
            SELECT p.id, p.is_pinned, p.restaurant_id
            FROM restaurant_posts p
            INNER JOIN restaurants r ON r.id = p.restaurant_id
            WHERE p.id = :pid AND r.owner_id = :uid
        ");
        $postStmt->execute([':pid' => $postId, ':uid' => $userId]);
        $post = $postStmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Post non trouve ou non autorise']);
            return;
        }

        try {
            $updateStmt = $this->db->prepare("
                UPDATE restaurant_posts SET is_pinned = 1 - is_pinned WHERE id = :pid
            ");
            $updateStmt->execute([':pid' => $postId]);

            $newPinned = (int)$post['is_pinned'] === 0;

            echo json_encode([
                'success' => true,
                'is_pinned' => $newPinned,
                'message' => $newPinned ? 'Post epingle' : 'Post desepingle',
            ]);
        } catch (\Exception $e) {
            Logger::error('PostController::togglePin error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }
}
