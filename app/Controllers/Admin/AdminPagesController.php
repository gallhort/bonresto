<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;

use App\Services\Logger;
class AdminPagesController extends Controller
{
    private User $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->requireAdmin();
    }
    
    /**
     * Page Users
     */
    public function users(Request $request): void
    {
        try {
            $users = $this->db->query("
                SELECT 
                    u.id,
                    u.prenom,
                    u.nom,
                    u.email,
                    u.created_at,
                    COUNT(DISTINCT r.id) as review_count,
                    AVG(r.note_globale) as avg_rating
                FROM users u
                LEFT JOIN reviews r ON u.id = r.user_id AND r.status = 'approved'
                GROUP BY u.id
                ORDER BY u.created_at DESC
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->render('admin/users', [
                'title' => 'Gestion Utilisateurs',
                'users' => $users
            ]);
        } catch (\Exception $e) {
            Logger::error(trim("Error in users page: "), [$e->getMessage()]);
            $this->render('admin/users', ['title' => 'Gestion Utilisateurs', 'users' => []]);
        }
    }
    
    /**
     * Page Analytics
     */
    public function analytics(Request $request): void
    {
        try {
            // Top événements
            $topEvents = $this->db->query("
                SELECT 
                    event_type,
                    COUNT(*) as count,
                    DATE(created_at) as date
                FROM analytics_events
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY event_type, DATE(created_at)
                ORDER BY date DESC, count DESC
                LIMIT 50
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            // Stats par device
            $deviceStats = $this->db->query("
                SELECT 
                    device_type,
                    COUNT(*) as count
                FROM analytics_events
                GROUP BY device_type
            ")->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->render('admin/analytics', [
                'title' => 'Analytics Détaillées',
                'topEvents' => $topEvents,
                'deviceStats' => $deviceStats
            ]);
        } catch (\Exception $e) {
            Logger::error(trim("Error in analytics page: "), [$e->getMessage()]);
            $this->render('admin/analytics', ['title' => 'Analytics', 'topEvents' => [], 'deviceStats' => []]);
        }
    }
    
    /**
     * Page Settings
     */
    public function settings(Request $request): void
    {
        $this->render('admin/settings', [
            'title' => 'Paramètres'
        ]);
    }
    
    /**
     * Contact messages admin page
     */
    public function contacts(Request $request): void
    {
        try {
            $status = $_GET['status'] ?? 'all';
            $contactParams = [];
            $where = '';
            if ($status !== 'all') {
                $where = "WHERE cm.status = :status";
                $contactParams[':status'] = $status;
            }

            $contactStmt = $this->db->prepare("
                SELECT cm.*, u.prenom as user_prenom, u.nom as user_nom
                FROM contact_messages cm
                LEFT JOIN users u ON u.id = cm.user_id
                {$where}
                ORDER BY cm.created_at DESC
                LIMIT 100
            ");
            $contactStmt->execute($contactParams);
            $contacts = $contactStmt->fetchAll(\PDO::FETCH_ASSOC);

            $counts = $this->db->query("
                SELECT status, COUNT(*) as cnt
                FROM contact_messages
                GROUP BY status
            ")->fetchAll(\PDO::FETCH_KEY_PAIR);

            $this->render('admin/contacts', [
                'title' => 'Messages de contact',
                'contacts' => $contacts,
                'counts' => $counts,
                'currentStatus' => $status
            ]);
        } catch (\Exception $e) {
            Logger::error("Error in contacts page: " . $e->getMessage());
            $this->render('admin/contacts', [
                'title' => 'Messages de contact',
                'contacts' => [],
                'counts' => [],
                'currentStatus' => 'all'
            ]);
        }
    }

    /**
     * Reply to a contact message
     */
    public function replyContact(Request $request): void
    {
        $id = (int)$request->post('id');
        $notes = trim($request->post('admin_notes') ?? '');

        $stmt = $this->db->prepare("
            UPDATE contact_messages
            SET status = 'replied', admin_notes = ?, replied_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$notes, $id]);

        $_SESSION['flash_success'] = 'Message marqué comme répondu.';
        $this->redirect('/admin/contacts');
    }

    /**
     * Moderation audit log page
     */
    public function moderationLog(Request $request): void
    {
        try {
            $logs = $this->db->query("
                SELECT ml.*, u.prenom as admin_prenom, u.nom as admin_nom
                FROM moderation_log ml
                LEFT JOIN users u ON u.id = ml.admin_id
                ORDER BY ml.created_at DESC
                LIMIT 200
            ")->fetchAll(\PDO::FETCH_ASSOC);

            $this->render('admin/moderation-log', [
                'title' => 'Journal de modération',
                'logs' => $logs
            ]);
        } catch (\Exception $e) {
            Logger::error("Error in moderation log: " . $e->getMessage());
            $this->render('admin/moderation-log', [
                'title' => 'Journal de modération',
                'logs' => []
            ]);
        }
    }

    /**
     * Vérifie que l'utilisateur est admin
     */
    protected function requireAdmin(): void
    {
        if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
            header('Location: /');
            exit;
        }
    }
}
