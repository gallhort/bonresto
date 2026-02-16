<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use App\Services\NotificationService;
use App\Services\ActivityFeedService;
use App\Services\Logger;
use PDO;

/**
 * Controller pour la fonctionnalité "Proposer un restaurant"
 * Permet aux utilisateurs de suggérer des restaurants manquants
 * et aux admins de modérer ces suggestions.
 */
class SuggestionController extends Controller
{
    /**
     * GET /proposer-restaurant
     * Affiche le formulaire de suggestion et l'historique des suggestions de l'utilisateur
     */
    public function index(Request $request): void
    {
        $this->requireAuth();

        $userId = (int) $_SESSION['user']['id'];

        // Récupérer les suggestions de l'utilisateur
        $stmt = $this->db->prepare("
            SELECT * FROM restaurant_suggestions
            WHERE user_id = :uid
            ORDER BY created_at DESC
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compter les suggestions approuvées (pour affichage badge)
        $approvedCount = 0;
        foreach ($suggestions as $s) {
            if ($s['status'] === 'approved') {
                $approvedCount++;
            }
        }

        // Récupérer le flash message éventuel
        $success = $_SESSION['suggestion_success'] ?? null;
        $errors = $_SESSION['suggestion_errors'] ?? [];
        $old = $_SESSION['suggestion_old'] ?? [];
        unset($_SESSION['suggestion_success'], $_SESSION['suggestion_errors'], $_SESSION['suggestion_old']);

        $this->render('suggestion/index', [
            'title' => 'Proposer un restaurant - Le Bon Resto',
            'meta_description' => 'Suggérez un restaurant manquant sur Le Bon Resto et aidez la communauté à découvrir de nouvelles adresses.',
            'suggestions' => $suggestions,
            'approvedCount' => $approvedCount,
            'success' => $success,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    /**
     * POST /proposer-restaurant
     * Traite la soumission d'une nouvelle suggestion
     */
    public function store(Request $request): void
    {
        $this->requireAuth();

        if (!verify_csrf()) {
            $_SESSION['suggestion_errors'] = ['Token CSRF invalide. Veuillez rafraichir la page.'];
            $this->redirect('/proposer-restaurant');
            return;
        }

        $userId = (int) $_SESSION['user']['id'];

        // Rate limiting: 5 suggestions par heure
        if (!RateLimiter::attempt('suggestion_' . $userId, 5, 3600)) {
            $_SESSION['suggestion_errors'] = ['Vous avez soumis trop de suggestions. Veuillez réessayer dans une heure.'];
            $this->redirect('/proposer-restaurant');
            return;
        }

        // Récupération et nettoyage des données
        $nom = trim($request->post('nom') ?? '');
        $ville = trim($request->post('ville') ?? '');
        $typeCuisine = trim($request->post('type_cuisine') ?? '');
        $adresse = trim($request->post('adresse') ?? '');
        $description = trim($request->post('description') ?? '');
        $pourquoi = trim($request->post('pourquoi') ?? '');

        // Honeypot anti-bot
        if (!empty($request->post('website'))) {
            $this->redirect('/proposer-restaurant');
            return;
        }

        // Validation
        $errors = [];

        if (empty($nom) || mb_strlen($nom) < 2) {
            $errors[] = 'Le nom du restaurant est requis (minimum 2 caractères).';
        }
        if (mb_strlen($nom) > 200) {
            $errors[] = 'Le nom du restaurant ne doit pas dépasser 200 caractères.';
        }
        if (empty($ville) || mb_strlen($ville) < 2) {
            $errors[] = 'La ville est requise (minimum 2 caractères).';
        }
        if (mb_strlen($ville) > 100) {
            $errors[] = 'La ville ne doit pas dépasser 100 caractères.';
        }
        if (!empty($adresse) && mb_strlen($adresse) > 500) {
            $errors[] = 'L\'adresse ne doit pas dépasser 500 caractères.';
        }
        if (!empty($typeCuisine) && mb_strlen($typeCuisine) > 100) {
            $errors[] = 'Le type de cuisine ne doit pas dépasser 100 caractères.';
        }
        if (!empty($description) && mb_strlen($description) > 1000) {
            $errors[] = 'La description ne doit pas dépasser 1000 caractères.';
        }
        if (!empty($pourquoi) && mb_strlen($pourquoi) > 500) {
            $errors[] = 'Le champ "Pourquoi" ne doit pas dépasser 500 caractères.';
        }

        if (!empty($errors)) {
            $_SESSION['suggestion_errors'] = $errors;
            $_SESSION['suggestion_old'] = [
                'nom' => $nom, 'ville' => $ville, 'type_cuisine' => $typeCuisine,
                'adresse' => $adresse, 'description' => $description, 'pourquoi' => $pourquoi,
            ];
            $this->redirect('/proposer-restaurant');
            return;
        }

        // Vérifier les doublons (même utilisateur, même nom+ville, encore en pending)
        $stmtDup = $this->db->prepare("
            SELECT id FROM restaurant_suggestions
            WHERE user_id = :uid AND nom = :nom AND ville = :ville AND status = 'pending'
        ");
        $stmtDup->execute([
            ':uid' => $userId,
            ':nom' => $nom,
            ':ville' => $ville,
        ]);

        if ($stmtDup->fetch()) {
            $_SESSION['suggestion_errors'] = ['Vous avez déjà une suggestion en attente pour ce restaurant dans cette ville.'];
            $_SESSION['suggestion_old'] = [
                'nom' => $nom, 'ville' => $ville, 'type_cuisine' => $typeCuisine,
                'adresse' => $adresse, 'description' => $description, 'pourquoi' => $pourquoi,
            ];
            $this->redirect('/proposer-restaurant');
            return;
        }

        // Insertion de la suggestion
        try {
            $stmtInsert = $this->db->prepare("
                INSERT INTO restaurant_suggestions (user_id, nom, adresse, ville, type_cuisine, description, pourquoi)
                VALUES (:uid, :nom, :adresse, :ville, :type_cuisine, :description, :pourquoi)
            ");
            $stmtInsert->execute([
                ':uid' => $userId,
                ':nom' => $nom,
                ':adresse' => !empty($adresse) ? $adresse : null,
                ':ville' => $ville,
                ':type_cuisine' => !empty($typeCuisine) ? $typeCuisine : null,
                ':description' => !empty($description) ? $description : null,
                ':pourquoi' => !empty($pourquoi) ? $pourquoi : null,
            ]);

            // Attribuer 10 points de fidélité
            $stmtPoints = $this->db->prepare("
                UPDATE users SET points = points + 10 WHERE id = :uid
            ");
            $stmtPoints->execute([':uid' => $userId]);

            // Vérifier le badge "Eclaireur" (3+ suggestions approuvées)
            $this->checkEclaireurBadge($userId);

            // Logger dans le fil d'activité
            try {
                $feedService = new ActivityFeedService($this->db);
                $feedService->log($userId, 'suggestion', null, null, [
                    'nom' => $nom,
                    'ville' => $ville,
                ]);
            } catch (\Exception $e) {
                Logger::error('Suggestion activity feed error: ' . $e->getMessage());
            }

            $_SESSION['suggestion_success'] = 'Merci pour votre suggestion ! Elle sera examinée par notre équipe. Vous avez gagné 10 points.';
        } catch (\Exception $e) {
            Logger::error('Suggestion store error: ' . $e->getMessage());
            $_SESSION['suggestion_errors'] = ['Une erreur est survenue. Veuillez réessayer.'];
            $_SESSION['suggestion_old'] = [
                'nom' => $nom, 'ville' => $ville, 'type_cuisine' => $typeCuisine,
                'adresse' => $adresse, 'description' => $description, 'pourquoi' => $pourquoi,
            ];
        }

        $this->redirect('/proposer-restaurant');
    }

    // ========================================================================
    // Admin Methods
    // ========================================================================

    /**
     * GET /admin/suggestions
     * Liste toutes les suggestions avec filtre par statut
     */
    public function adminList(Request $request): void
    {
        $this->requireAdmin();

        $statusFilter = $_GET['status'] ?? null;
        $allowedStatuses = ['pending', 'approved', 'rejected', 'duplicate'];

        // Construire la requête avec filtre optionnel
        $params = [];
        $whereClause = '';

        if ($statusFilter && in_array($statusFilter, $allowedStatuses, true)) {
            $whereClause = 'WHERE rs.status = :status';
            $params[':status'] = $statusFilter;
        }

        $stmt = $this->db->prepare("
            SELECT rs.*, u.prenom, u.nom AS user_nom, u.email
            FROM restaurant_suggestions rs
            INNER JOIN users u ON u.id = rs.user_id
            {$whereClause}
            ORDER BY rs.created_at DESC
        ");
        $stmt->execute($params);
        $suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compter par statut pour les onglets
        $stmtCounts = $this->db->prepare("
            SELECT status, COUNT(*) AS cnt
            FROM restaurant_suggestions
            GROUP BY status
        ");
        $stmtCounts->execute();
        $countsRaw = $stmtCounts->fetchAll(PDO::FETCH_ASSOC);

        $counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'duplicate' => 0];
        foreach ($countsRaw as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
            $counts['all'] += (int) $row['cnt'];
        }

        $this->render('admin/suggestions', [
            'title' => 'Suggestions de restaurants - Admin',
            'suggestions' => $suggestions,
            'counts' => $counts,
            'currentStatus' => $statusFilter,
        ]);
    }

    /**
     * POST /admin/suggestions/{id}/approve
     * Approuver une suggestion
     */
    public function adminApprove(Request $request): void
    {
        $this->requireAdmin();

        $suggestionId = (int) $request->param('id');

        if ($suggestionId <= 0) {
            $this->redirect('/admin/suggestions');
            return;
        }

        // Récupérer la suggestion
        $stmt = $this->db->prepare("
            SELECT * FROM restaurant_suggestions WHERE id = :id
        ");
        $stmt->execute([':id' => $suggestionId]);
        $suggestion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$suggestion) {
            $_SESSION['admin_error'] = 'Suggestion introuvable.';
            $this->redirect('/admin/suggestions');
            return;
        }

        if ($suggestion['status'] !== 'pending') {
            $_SESSION['admin_error'] = 'Cette suggestion a déjà été traitée.';
            $this->redirect('/admin/suggestions');
            return;
        }

        $adminNote = trim($_POST['admin_note'] ?? '');

        try {
            // Mettre à jour le statut
            $stmtUpdate = $this->db->prepare("
                UPDATE restaurant_suggestions
                SET status = 'approved', admin_note = :note
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':note' => !empty($adminNote) ? $adminNote : null,
                ':id' => $suggestionId,
            ]);

            $userId = (int) $suggestion['user_id'];
            $nom = $suggestion['nom'];

            // Attribuer 50 points bonus
            $stmtPoints = $this->db->prepare("
                UPDATE users SET points = points + 50 WHERE id = :uid
            ");
            $stmtPoints->execute([':uid' => $userId]);

            // Notifier l'utilisateur
            try {
                $notifService = new NotificationService($this->db);
                $notifService->create(
                    $userId,
                    'suggestion_approved',
                    'Suggestion approuvée !',
                    'Votre suggestion "' . $nom . '" a été approuvée ! +100 points',
                    ['suggestion_id' => $suggestionId, 'link' => '/proposer-restaurant']
                );
            } catch (\Exception $e) {
                Logger::error('Suggestion approve notification error: ' . $e->getMessage());
            }

            // Vérifier le badge "Eclaireur"
            $this->checkEclaireurBadge($userId);

            // Logger dans moderation_log
            $this->logModeration('approve_suggestion', 'suggestion', $suggestionId, null, [
                'nom' => $nom,
                'ville' => $suggestion['ville'],
                'user_id' => $userId,
            ]);

            $_SESSION['admin_success'] = 'Suggestion "' . htmlspecialchars($nom) . '" approuvée. L\'utilisateur a reçu 100 points.';
        } catch (\Exception $e) {
            Logger::error('Suggestion approve error: ' . $e->getMessage());
            $_SESSION['admin_error'] = 'Erreur lors de l\'approbation.';
        }

        $this->redirect('/admin/suggestions');
    }

    /**
     * POST /admin/suggestions/{id}/reject
     * Rejeter une suggestion
     */
    public function adminReject(Request $request): void
    {
        $this->requireAdmin();

        $suggestionId = (int) $request->param('id');

        if ($suggestionId <= 0) {
            $this->redirect('/admin/suggestions');
            return;
        }

        // Récupérer la suggestion
        $stmt = $this->db->prepare("
            SELECT * FROM restaurant_suggestions WHERE id = :id
        ");
        $stmt->execute([':id' => $suggestionId]);
        $suggestion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$suggestion) {
            $_SESSION['admin_error'] = 'Suggestion introuvable.';
            $this->redirect('/admin/suggestions');
            return;
        }

        if ($suggestion['status'] !== 'pending') {
            $_SESSION['admin_error'] = 'Cette suggestion a déjà été traitée.';
            $this->redirect('/admin/suggestions');
            return;
        }

        $adminNote = trim($_POST['admin_note'] ?? '');

        try {
            // Mettre à jour le statut
            $stmtUpdate = $this->db->prepare("
                UPDATE restaurant_suggestions
                SET status = 'rejected', admin_note = :note
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':note' => !empty($adminNote) ? $adminNote : null,
                ':id' => $suggestionId,
            ]);

            $userId = (int) $suggestion['user_id'];
            $nom = $suggestion['nom'];

            // Notifier l'utilisateur
            try {
                $notifService = new NotificationService($this->db);
                $message = 'Votre suggestion "' . $nom . '" n\'a pas été retenue.';
                if (!empty($adminNote)) {
                    $message .= ' Raison : ' . $adminNote;
                }
                $notifService->create(
                    $userId,
                    'suggestion_rejected',
                    'Suggestion non retenue',
                    $message,
                    ['suggestion_id' => $suggestionId, 'link' => '/proposer-restaurant']
                );
            } catch (\Exception $e) {
                Logger::error('Suggestion reject notification error: ' . $e->getMessage());
            }

            // Logger dans moderation_log
            $this->logModeration('reject_suggestion', 'suggestion', $suggestionId, $adminNote, [
                'nom' => $nom,
                'ville' => $suggestion['ville'],
                'user_id' => $userId,
            ]);

            $_SESSION['admin_success'] = 'Suggestion "' . htmlspecialchars($nom) . '" rejetée.';
        } catch (\Exception $e) {
            Logger::error('Suggestion reject error: ' . $e->getMessage());
            $_SESSION['admin_error'] = 'Erreur lors du rejet.';
        }

        $this->redirect('/admin/suggestions');
    }

    // ========================================================================
    // Private Helpers
    // ========================================================================

    /**
     * Vérifie si l'utilisateur mérite le badge "Eclaireur" (3+ suggestions approuvées)
     */
    private function checkEclaireurBadge(int $userId): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) AS cnt
                FROM restaurant_suggestions
                WHERE user_id = :uid AND status = 'approved'
            ");
            $stmt->execute([':uid' => $userId]);
            $count = (int) $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

            if ($count >= 3) {
                // Insérer le titre "Eclaireur" s'il n'existe pas déjà
                $stmtTitle = $this->db->prepare("
                    INSERT INTO user_titles (user_id, title_type, title_label, title_icon, title_color, context)
                    VALUES (:uid, :type, :label, :icon, :color, :ctx)
                    ON DUPLICATE KEY UPDATE title_label = :label2, title_icon = :icon2, title_color = :color2, is_active = 1
                ");
                $stmtTitle->execute([
                    ':uid' => $userId,
                    ':type' => 'eclaireur',
                    ':label' => 'Eclaireur',
                    ':icon' => "\xF0\x9F\x94\xA6", // flashlight emoji (UTF-8)
                    ':color' => '#f59e0b',
                    ':ctx' => 'suggestions',
                    ':label2' => 'Eclaireur',
                    ':icon2' => "\xF0\x9F\x94\xA6",
                    ':color2' => '#f59e0b',
                ]);

                // Notifier l'utilisateur du badge gagné
                try {
                    $notifService = new NotificationService($this->db);
                    $notifService->notifyBadgeEarned($userId, 'Eclaireur');
                } catch (\Exception $e) {
                    Logger::error('Eclaireur badge notification error: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Logger::error('checkEclaireurBadge error: ' . $e->getMessage());
        }
    }

    /**
     * Logger une action de modération dans la table moderation_log
     */
    private function logModeration(string $action, string $targetType, int $targetId, ?string $reason = null, ?array $details = null): void
    {
        try {
            $adminId = (int) ($_SESSION['user']['id'] ?? 0);
            $stmt = $this->db->prepare("
                INSERT INTO moderation_log (admin_id, action, target_type, target_id, reason, details, created_at)
                VALUES (:admin_id, :action, :target_type, :target_id, :reason, :details, NOW())
            ");
            $stmt->execute([
                ':admin_id' => $adminId,
                ':action' => $action,
                ':target_type' => $targetType,
                ':target_id' => $targetId,
                ':reason' => $reason,
                ':details' => $details ? json_encode($details) : null,
            ]);
        } catch (\Exception $e) {
            Logger::error('Suggestion moderation log failed: ' . $e->getMessage());
        }
    }
}
