<?php

namespace App\Models;

use App\Core\Model;

/**
 * Review Model
 * Gère les avis des restaurants
 */
class Review extends Model
{
    protected string $table = 'reviews';
    protected string $primaryKey = 'id';
    
    /**
     * Récupère les avis d'un restaurant
     */
    public function getByRestaurant(int $restaurantId, string $status = 'approved'): array
    {
        $sql = "SELECT r.*, u.prenom, u.nom, u.photo_profil
                FROM {$this->table} r
                LEFT JOIN users u ON u.id = r.user_id
                WHERE r.restaurant_id = :restaurant_id 
                AND r.status = :status
                ORDER BY r.created_at DESC";
        
        return $this->query($sql, [
            'restaurant_id' => $restaurantId,
            'status' => $status
        ]);
    }
    
    /**
     * Récupère les avis en attente de modération
     */
    public function getPending(): array
    {
        $sql = "SELECT r.*, u.prenom, u.nom, rest.nom as restaurant_nom
                FROM {$this->table} r
                LEFT JOIN users u ON u.id = r.user_id
                LEFT JOIN restaurants rest ON rest.id = r.restaurant_id
                WHERE r.status = 'pending'
                ORDER BY r.created_at DESC";
        
        return $this->query($sql);
    }
    
    /**
     * Approuve un avis
     */
    public function approve(int $id): bool
    {
        return $this->update($id, ['status' => 'approved']);
    }
    
    /**
     * Rejette un avis
     */
    public function reject(int $id): bool
    {
        return $this->update($id, ['status' => 'rejected']);
    }
    
    /**
     * Crée un nouvel avis
     */
public function createReview(array $data): int|false
{
    $sql = "INSERT INTO {$this->table}
            (restaurant_id, user_id, author_name, title, message, pros, cons,
             note_globale, note_nourriture, note_service, note_ambiance, note_prix,
             visit_month, visit_year, trip_type,
             status, source,
             spam_score, spam_details, moderated_by, moderated_at, ai_rejected,
             created_at)
            VALUES
            (:restaurant_id, :user_id, :author_name, :title, :message, :pros, :cons,
             :note_globale, :note_nourriture, :note_service, :note_ambiance, :note_prix,
             :visit_month, :visit_year, :trip_type,
             :status, :source,
             :spam_score, :spam_details, :moderated_by, :moderated_at, :ai_rejected,
             NOW())";
    
    // Valeurs par défaut pour pros/cons
    $data['pros'] = $data['pros'] ?? null;
    $data['cons'] = $data['cons'] ?? null;

    // Valeurs par défaut pour colonnes IA si non présentes
    $data['spam_score'] = $data['spam_score'] ?? 100;
    $data['spam_details'] = $data['spam_details'] ?? null;
    $data['moderated_by'] = $data['moderated_by'] ?? 'manual';
    $data['moderated_at'] = $data['moderated_at'] ?? null;
    $data['ai_rejected'] = $data['ai_rejected'] ?? 0;
    $data['source'] = $data['source'] ?? 'site';
    
    $this->query($sql, $data);
    
    return $this->db->lastInsertId();
}
    
    /**
     * Vote pour un avis utile
     */
    public function vote(int $reviewId, int $userId, int $vote): bool
    {
        // Vérifier si l'user a déjà voté
        $existing = $this->query(
            "SELECT id FROM review_votes WHERE review_id = :review_id AND user_id = :user_id",
            ['review_id' => $reviewId, 'user_id' => $userId]
        );
        
        if (!empty($existing)) {
            // Update le vote existant
            $sql = "UPDATE review_votes 
                    SET vote = :vote 
                    WHERE review_id = :review_id AND user_id = :user_id";
        } else {
            // Créer un nouveau vote
            $sql = "INSERT INTO review_votes (review_id, user_id, vote, created_at) 
                    VALUES (:review_id, :user_id, :vote, NOW())";
        }
        
        $this->query($sql, [
            'review_id' => $reviewId,
            'user_id' => $userId,
            'vote' => $vote
        ]);
        
        // Mettre à jour le compteur dans reviews
        $this->updateVoteCount($reviewId);
        
        return true;
    }
    
    /**
     * Met à jour le compteur de votes utiles
     */
    private function updateVoteCount(int $reviewId): void
    {
        $sql = "UPDATE {$this->table} 
                SET votes_utiles = (
                    SELECT SUM(vote) 
                    FROM review_votes 
                    WHERE review_id = :review_id
                )
                WHERE id = :review_id";
        
        $this->query($sql, ['review_id' => $reviewId]);
    }
    /**
     * Récupère les avis récents (sans photos pour l'instant)
     */
    public function getRecent(int $limit = 6): array
    {
        $sql = "
            SELECT 
                rv.id,
                rv.restaurant_id,
                rv.title,
                rv.message,
                rv.note_globale,
                rv.created_at,
                rv.author_name,
                r.nom as restaurant_nom,
                r.slug as restaurant_slug,
                r.ville,
                r.wilaya,
                u.prenom as user_prenom,
                u.nom as user_nom,
                u.photo_profil
            FROM {$this->table} rv
            INNER JOIN restaurants r ON rv.restaurant_id = r.id
            LEFT JOIN users u ON rv.user_id = u.id
            WHERE rv.status = 'approved'
            AND r.status = 'validated'
            ORDER BY rv.created_at DESC
            LIMIT :limit
        ";
        
        return $this->query($sql, ['limit' => $limit]);
    }
    /**
     * Vérifie si un user a déjà laissé un avis pour un restaurant
     */
    public function getUserReviewForRestaurant(int $userId, int $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND restaurant_id = :restaurant_id 
                LIMIT 1";
        
        $results = $this->query($sql, [
            'user_id' => $userId,
            'restaurant_id' => $restaurantId
        ]);
        
        return $results[0] ?? null;
    }
    /**
     * Récupère les avis d'un utilisateur
     */
    public function getTotalApproved(): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE status = 'approved'";
        $result = $this->query($sql);
        return (int) ($result[0]['COUNT(*)'] ?? 0);
    }

    public function getUserReviews(int $userId): array
    {
        $sql = "SELECT r.*, 
                       rest.nom as restaurant_nom,
                       rest.ville as restaurant_ville,
                       rest.type_cuisine as restaurant_type
                FROM {$this->table} r
                LEFT JOIN restaurants rest ON rest.id = r.restaurant_id
                WHERE r.user_id = :user_id
                ORDER BY r.created_at DESC";
        
        return $this->query($sql, ['user_id' => $userId]);
    }
}