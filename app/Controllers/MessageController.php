<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\NotificationService;
use App\Services\RateLimiter;
use PDO;

class MessageController extends Controller
{
    /**
     * Boite de reception
     * GET /messages
     */
    public function inbox(Request $request): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user']['id'];

        // Fetch inbox messages with sender info
        $stmt = $this->db->prepare("
            SELECT m.id, m.sender_id, m.receiver_id, m.subject, m.body,
                   m.is_read, m.created_at,
                   u.prenom AS sender_prenom, u.nom AS sender_nom, u.photo_profil AS sender_photo
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE m.receiver_id = :user_id AND m.deleted_by_receiver = 0
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count unread
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) FROM messages
            WHERE receiver_id = :user_id AND is_read = 0 AND deleted_by_receiver = 0
        ");
        $countStmt->execute([':user_id' => $userId]);
        $unreadCount = (int) $countStmt->fetchColumn();

        $this->render('messages.inbox', [
            'title' => 'Mes messages',
            'messages' => $messages,
            'unreadCount' => $unreadCount,
            'tab' => 'inbox',
        ]);
    }

    /**
     * Messages envoyes
     * GET /messages/sent
     */
    public function sent(Request $request): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user']['id'];

        // Fetch sent messages with receiver info
        $stmt = $this->db->prepare("
            SELECT m.id, m.sender_id, m.receiver_id, m.subject, m.body,
                   m.is_read, m.created_at,
                   u.prenom AS receiver_prenom, u.nom AS receiver_nom, u.photo_profil AS receiver_photo
            FROM messages m
            JOIN users u ON u.id = m.receiver_id
            WHERE m.sender_id = :user_id AND m.deleted_by_sender = 0
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('messages.inbox', [
            'title' => 'Messages envoyés',
            'messages' => $messages,
            'tab' => 'sent',
        ]);
    }

    /**
     * Conversation avec un utilisateur
     * GET /messages/conversation/{id}
     */
    public function conversation(Request $request): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user']['id'];
        $otherUserId = (int) $request->param('id');

        if ($otherUserId <= 0) {
            $this->notFound('Utilisateur non trouvé');
            return;
        }

        // Get other user info
        $userStmt = $this->db->prepare("
            SELECT id, prenom, nom, photo_profil
            FROM users WHERE id = :other_id
        ");
        $userStmt->execute([':other_id' => $otherUserId]);
        $otherUser = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$otherUser) {
            $this->notFound('Utilisateur non trouvé');
            return;
        }

        // Get all messages between the two users (both directions), excluding soft-deleted
        $stmt = $this->db->prepare("
            SELECT m.id, m.sender_id, m.receiver_id, m.subject, m.body,
                   m.is_read, m.created_at
            FROM messages m
            WHERE (
                (m.sender_id = :user_id_1 AND m.receiver_id = :other_id_1 AND m.deleted_by_sender = 0)
                OR
                (m.sender_id = :other_id_2 AND m.receiver_id = :user_id_2 AND m.deleted_by_receiver = 0)
            )
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([
            ':user_id_1' => $userId,
            ':other_id_1' => $otherUserId,
            ':other_id_2' => $otherUserId,
            ':user_id_2' => $userId,
        ]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mark all received messages from that user as read
        $markStmt = $this->db->prepare("
            UPDATE messages
            SET is_read = 1
            WHERE sender_id = :other_id AND receiver_id = :user_id AND is_read = 0
        ");
        $markStmt->execute([
            ':other_id' => $otherUserId,
            ':user_id' => $userId,
        ]);

        $this->render('messages.conversation', [
            'title' => 'Conversation avec ' . htmlspecialchars($otherUser['prenom']),
            'messages' => $messages,
            'otherUser' => $otherUser,
        ]);
    }

    /**
     * Envoyer un message (JSON API)
     * POST /api/messages/send
     */
    public function send(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            return;
        }

        // CSRF check via X-CSRF-TOKEN header (JSON body API)
        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $userId = (int) $_SESSION['user']['id'];

        // Rate limit: 20 messages per hour
        if (!RateLimiter::attempt('message_send_' . $userId, 20, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de messages envoyés. Réessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $receiverId = (int) ($input['receiver_id'] ?? 0);
        $subject = trim($input['subject'] ?? '');
        $body = trim($input['body'] ?? '');

        // Validate body
        if (empty($body)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le message ne peut pas être vide']);
            return;
        }

        if (mb_strlen($body) > 2000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le message ne doit pas dépasser 2000 caractères']);
            return;
        }

        // Cannot send to self
        if ($receiverId === $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas vous envoyer un message']);
            return;
        }

        // Check receiver exists
        $receiverStmt = $this->db->prepare("SELECT id, prenom, nom FROM users WHERE id = :receiver_id");
        $receiverStmt->execute([':receiver_id' => $receiverId]);
        $receiver = $receiverStmt->fetch(PDO::FETCH_ASSOC);

        if (!$receiver) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Destinataire introuvable']);
            return;
        }

        // Insert message
        $insertStmt = $this->db->prepare("
            INSERT INTO messages (sender_id, receiver_id, subject, body, is_read, deleted_by_sender, deleted_by_receiver, created_at)
            VALUES (:sender_id, :receiver_id, :subject, :body, 0, 0, 0, NOW())
        ");
        $insertStmt->execute([
            ':sender_id' => $userId,
            ':receiver_id' => $receiverId,
            ':subject' => $subject,
            ':body' => $body,
        ]);

        // Send notification (NotificationService uses positional params internally — safe to call normally)
        $senderName = htmlspecialchars($_SESSION['user']['prenom'] ?? 'Utilisateur');
        $notifService = new NotificationService($this->db);
        $notifService->create(
            $receiverId,
            'new_message',
            'Nouveau message',
            $senderName . ' vous a envoyé un message',
            ['sender_id' => $userId]
        );

        echo json_encode(['success' => true, 'message' => 'Message envoyé']);
    }

    /**
     * Nombre de messages non lus (JSON API)
     * GET /api/messages/unread-count
     */
    public function apiUnreadCount(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            return;
        }

        $userId = (int) $_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM messages
            WHERE receiver_id = :user_id AND is_read = 0 AND deleted_by_receiver = 0
        ");
        $stmt->execute([':user_id' => $userId]);
        $count = (int) $stmt->fetchColumn();

        echo json_encode(['success' => true, 'count' => $count]);
    }

    /**
     * Suppression douce d'un message (JSON API)
     * POST /api/messages/{id}/delete
     */
    public function delete(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            return;
        }

        // CSRF check via X-CSRF-TOKEN header
        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $userId = (int) $_SESSION['user']['id'];
        $messageId = (int) $request->param('id');

        if ($messageId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID de message invalide']);
            return;
        }

        // Fetch the message to check ownership
        $stmt = $this->db->prepare("
            SELECT id, sender_id, receiver_id FROM messages WHERE id = :message_id
        ");
        $stmt->execute([':message_id' => $messageId]);
        $message = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$message) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Message introuvable']);
            return;
        }

        // Check the user is sender or receiver
        $isSender = (int) $message['sender_id'] === $userId;
        $isReceiver = (int) $message['receiver_id'] === $userId;

        if (!$isSender && !$isReceiver) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Accès refusé']);
            return;
        }

        // Soft delete for the appropriate side
        if ($isSender) {
            $updateStmt = $this->db->prepare("
                UPDATE messages SET deleted_by_sender = 1 WHERE id = :message_id
            ");
            $updateStmt->execute([':message_id' => $messageId]);
        }

        if ($isReceiver) {
            $updateStmt = $this->db->prepare("
                UPDATE messages SET deleted_by_receiver = 1 WHERE id = :message_id
            ");
            $updateStmt->execute([':message_id' => $messageId]);
        }

        echo json_encode(['success' => true, 'message' => 'Message supprimé']);
    }
}
