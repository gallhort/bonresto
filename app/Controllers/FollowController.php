<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use PDO;

class FollowController extends Controller
{
    /**
     * API - Follow/Unfollow un utilisateur
     * POST /api/users/{id}/follow
     */
    public function toggle(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifie']);
            return;
        }

        $followedId = (int)$request->param('id');
        $followerId = (int)$_SESSION['user']['id'];

        if ($followerId === $followedId) {
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas vous suivre vous-meme']);
            return;
        }

        // Check if already following
        $check = $this->db->prepare("SELECT id FROM user_follows WHERE follower_id = :fid AND followed_id = :tid");
        $check->execute([':fid' => $followerId, ':tid' => $followedId]);

        if ($check->fetch()) {
            // Unfollow
            $this->db->prepare("DELETE FROM user_follows WHERE follower_id = :fid AND followed_id = :tid")
                ->execute([':fid' => $followerId, ':tid' => $followedId]);
            echo json_encode(['success' => true, 'following' => false]);
        } else {
            // Follow
            $this->db->prepare("INSERT INTO user_follows (follower_id, followed_id) VALUES (:fid, :tid)")
                ->execute([':fid' => $followerId, ':tid' => $followedId]);
            echo json_encode(['success' => true, 'following' => true]);
        }
    }

    /**
     * API - Statut follow
     * GET /api/users/{id}/follow-status
     */
    public function status(Request $request): void
    {
        header('Content-Type: application/json');
        $targetId = (int)$request->param('id');

        $following = false;
        if ($this->isAuthenticated()) {
            $stmt = $this->db->prepare("SELECT id FROM user_follows WHERE follower_id = :fid AND followed_id = :tid");
            $stmt->execute([':fid' => (int)$_SESSION['user']['id'], ':tid' => $targetId]);
            $following = (bool)$stmt->fetch();
        }

        // Counts
        $followers = $this->db->prepare("SELECT COUNT(*) FROM user_follows WHERE followed_id = :id");
        $followers->execute([':id' => $targetId]);
        $followersCount = (int)$followers->fetchColumn();

        $followingCount = $this->db->prepare("SELECT COUNT(*) FROM user_follows WHERE follower_id = :id");
        $followingCount->execute([':id' => $targetId]);

        echo json_encode([
            'success' => true,
            'following' => $following,
            'followers_count' => $followersCount,
            'following_count' => (int)$followingCount->fetchColumn()
        ]);
    }

    /**
     * API - Liste des abonnes
     * GET /api/users/{id}/followers
     */
    public function followers(Request $request): void
    {
        header('Content-Type: application/json');
        $userId = (int)$request->param('id');

        $stmt = $this->db->prepare("
            SELECT u.id, u.prenom, u.nom, u.photo_profil, u.badge, u.points,
                   uf.created_at as followed_at
            FROM user_follows uf
            INNER JOIN users u ON u.id = uf.follower_id
            WHERE uf.followed_id = :uid
            ORDER BY uf.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([':uid' => $userId]);

        echo json_encode(['success' => true, 'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }
}
