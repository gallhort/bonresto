<?php

namespace App\Models;

use App\Core\Model;

/**
 * User Model
 * Gère les utilisateurs
 */
class User extends Model
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';
    
    /**
     * Trouve un utilisateur par email
     */
    public function findByEmail(string $email): ?array
    {
        $results = $this->where('email', $email);
        return $results[0] ?? null;
    }
    
    /**
     * Trouve un utilisateur par username
     */
    public function findByUsername(string $username): ?array
    {
        $results = $this->where('username', $username);
        return $results[0] ?? null;
    }
    
    /**
     * Crée un nouvel utilisateur
     */
    public function createUser(array $data): int|false
    {
        // Hash le mot de passe
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }
        
        $sql = "INSERT INTO {$this->table} 
                (prenom, nom, email, username, password_hash, genre, created_at)
                VALUES 
                (:prenom, :nom, :email, :username, :password_hash, :genre, NOW())";
        
        $this->query($sql, $data);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Vérifie le mot de passe
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Met à jour le mot de passe
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        return $this->update($userId, [
            'password_hash' => $hash,
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
    }
    
    /**
     * Génère un token de réinitialisation de mot de passe
     */
    public function generatePasswordResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $this->update($userId, [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires
        ]);
        
        return $token;
    }
    
    /**
     * Vérifie un token de réinitialisation
     */
    public function verifyPasswordResetToken(string $token): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE password_reset_token = :token 
                AND password_reset_expires > NOW()";
        
        $results = $this->query($sql, ['token' => $token]);
        
        return $results[0] ?? null;
    }
    
    /**
     * Marque l'email comme vérifié
     */
    public function verifyEmail(int $userId): bool
    {
        return $this->update($userId, [
            'email_verified' => 1,
            'email_verification_token' => null
        ]);
    }
    
    /**
     * Met à jour le dernier login
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Récupère la wishlist d'un utilisateur
     */
    public function getWishlist(int $userId): array
    {
        $sql = "SELECT r.*, w.created_at as added_at
                FROM wishlist w
                JOIN restaurants r ON r.id = w.restaurant_id
                WHERE w.user_id = :user_id
                ORDER BY w.created_at DESC";
        
        return $this->query($sql, ['user_id' => $userId]);
    }
    
    /**
     * Ajoute un restaurant à la wishlist
     */
    public function addToWishlist(int $userId, int $restaurantId): bool
    {
        $sql = "INSERT IGNORE INTO wishlist (user_id, restaurant_id, created_at) 
                VALUES (:user_id, :restaurant_id, NOW())";
        
        $this->query($sql, [
            'user_id' => $userId,
            'restaurant_id' => $restaurantId
        ]);
        
        return true;
    }
    
    /**
     * Retire un restaurant de la wishlist
     */
    public function removeFromWishlist(int $userId, int $restaurantId): bool
    {
        $sql = "DELETE FROM wishlist 
                WHERE user_id = :user_id AND restaurant_id = :restaurant_id";
        
        $this->query($sql, [
            'user_id' => $userId,
            'restaurant_id' => $restaurantId
        ]);
        
        return true;
    }
    
    /**
     * Vérifie si un restaurant est dans la wishlist
     */
    public function isInWishlist(int $userId, int $restaurantId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM wishlist 
                WHERE user_id = :user_id AND restaurant_id = :restaurant_id";
        
        $results = $this->query($sql, [
            'user_id' => $userId,
            'restaurant_id' => $restaurantId
        ]);
        
        return ($results[0]['count'] ?? 0) > 0;
    }
    /**
     * Hash un mot de passe (compatible MD5 legacy + bcrypt)
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    
    /**
     * Récupère les statistiques détaillées d'un utilisateur
     */
    public function getUserStats(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT r.id) as nb_avis,
                    COUNT(DISTINCT rp.id) as nb_photos,
                    COUNT(DISTINCT r.restaurant_id) as nb_restaurants_visites,
                    COALESCE(AVG(r.note_globale), 0) as note_moyenne_donnee
                FROM users u
                LEFT JOIN reviews r ON r.user_id = u.id AND r.status = 'approved'
                LEFT JOIN review_photos rp ON rp.review_id = r.id
                WHERE u.id = :user_id";
        
        $results = $this->query($sql, ['user_id' => $userId]);
        
        $stats = $results[0] ?? [];
        
        // Calculer le niveau
        $nbAvis = $stats['nb_avis'] ?? 0;
        if ($nbAvis >= 50) {
            $niveau = 'platine';
        } elseif ($nbAvis >= 20) {
            $niveau = 'or';
        } elseif ($nbAvis >= 10) {
            $niveau = 'argent';
        } else {
            $niveau = 'bronze';
        }
        
        $stats['niveau'] = $niveau;
        $stats['points_total'] = ($nbAvis * 10) + ($stats['nb_photos'] * 5);
        
        return $stats;
    }
}

