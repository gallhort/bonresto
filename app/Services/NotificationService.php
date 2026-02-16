<?php

namespace App\Services;

use PDO;

class NotificationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Créer une notification pour un utilisateur
     */
    public function create(int $userId, string $type, string $title, string $message = '', array $data = []): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, data)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $type,
                $title,
                $message,
                !empty($data) ? json_encode($data) : null
            ]);
        } catch (\Exception $e) {
            Logger::error("NotificationService error: " . $e->getMessage());
        }
    }

    /**
     * Notifier le propriétaire d'un restaurant quand un nouvel avis est publié
     */
    public function notifyNewReview(int $restaurantId, string $reviewerName, float $rating): void
    {
        $owner = $this->getRestaurantOwner($restaurantId);
        if (!$owner) return;

        $stars = str_repeat('★', (int)$rating) . str_repeat('☆', 5 - (int)$rating);
        $this->create(
            $owner['owner_id'],
            'new_review',
            'Nouvel avis ' . $stars,
            $reviewerName . ' a laissé un avis sur votre restaurant.',
            ['restaurant_id' => $restaurantId, 'rating' => $rating]
        );
    }

    /**
     * Notifier l'auteur quand son avis est approuvé
     */
    public function notifyReviewApproved(int $userId, int $restaurantId, string $restaurantName): void
    {
        $this->create(
            $userId,
            'review_approved',
            'Avis publié !',
            'Votre avis sur ' . $restaurantName . ' est maintenant visible.',
            ['restaurant_id' => $restaurantId]
        );
    }

    /**
     * Notifier l'auteur quand son avis est rejeté
     */
    public function notifyReviewRejected(int $userId, int $restaurantId, string $restaurantName): void
    {
        $this->create(
            $userId,
            'review_rejected',
            'Avis non publié',
            'Votre avis sur ' . $restaurantName . ' n\'a pas été retenu.',
            ['restaurant_id' => $restaurantId]
        );
    }

    /**
     * Notifier l'auteur quand le propriétaire répond à son avis
     */
    public function notifyOwnerResponse(int $userId, int $restaurantId, string $restaurantName): void
    {
        $this->create(
            $userId,
            'owner_response',
            'Réponse du propriétaire',
            'Le propriétaire de ' . $restaurantName . ' a répondu à votre avis.',
            ['restaurant_id' => $restaurantId]
        );
    }

    /**
     * Notifier quand une demande de revendication est approuvée
     */
    public function notifyClaimApproved(int $userId, int $restaurantId, string $restaurantName): void
    {
        $this->create(
            $userId,
            'claim_approved',
            'Restaurant revendiqué !',
            'Votre demande de revendication pour ' . $restaurantName . ' a été acceptée.',
            ['restaurant_id' => $restaurantId]
        );
    }

    /**
     * Notifier quand un badge/achievement est gagné
     */
    public function notifyBadgeEarned(int $userId, string $badgeName): void
    {
        $this->create(
            $userId,
            'badge_earned',
            'Nouveau badge !',
            'Vous avez obtenu le badge "' . $badgeName . '" !',
            ['badge' => $badgeName]
        );
    }

    /**
     * Notifier quand quelqu'un répond à une question Q&A
     */
    public function notifyQaAnswer(int $userId, int $restaurantId, int $questionId, bool $isOwner): void
    {
        $label = $isOwner ? 'Le propriétaire a répondu' : 'Nouvelle réponse';
        $this->create(
            $userId,
            'qa_answer',
            $label . ' à votre question',
            '',
            ['restaurant_id' => $restaurantId, 'question_id' => $questionId]
        );
    }

    /**
     * Notifier le propriétaire quand une question est posée
     */
    public function notifyNewQuestion(int $ownerId, int $restaurantId, string $questionPreview): void
    {
        $this->create(
            $ownerId,
            'new_question',
            'Nouvelle question',
            mb_substr($questionPreview, 0, 100),
            ['restaurant_id' => $restaurantId]
        );
    }

    /**
     * Récupérer le propriétaire d'un restaurant
     */
    private function getRestaurantOwner(int $restaurantId): ?array
    {
        $stmt = $this->db->prepare("SELECT owner_id FROM restaurants WHERE id = ? AND owner_id IS NOT NULL");
        $stmt->execute([$restaurantId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
