<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\LoyaltyService;
use PDO;

class ReferralController extends Controller
{
    /**
     * Page - Mon programme de parrainage
     * GET /parrainage
     */
    public function index(Request $request): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login?redirect=/parrainage');
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        // Generate referral code if not exists
        $user = $this->db->prepare("SELECT referral_code FROM users WHERE id = ?");
        $user->execute([$userId]);
        $code = $user->fetchColumn();

        if (!$code) {
            $code = $this->generateCode($userId);
            $this->db->prepare("UPDATE users SET referral_code = ? WHERE id = ?")->execute([$code, $userId]);
        }

        // Stats
        $stats = $this->db->prepare("
            SELECT
                COUNT(*) as total_referrals,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(points_awarded) as total_points
            FROM referrals WHERE referrer_id = ?
        ");
        $stats->execute([$userId]);
        $referralStats = $stats->fetch(PDO::FETCH_ASSOC);

        // Recent referrals
        $recent = $this->db->prepare("
            SELECT r.*, u.prenom, u.nom as user_nom
            FROM referrals r
            INNER JOIN users u ON u.id = r.referred_id
            WHERE r.referrer_id = ?
            ORDER BY r.created_at DESC
            LIMIT 20
        ");
        $recent->execute([$userId]);

        $this->render('referral/index', [
            'title' => 'Programme de parrainage',
            'referral_code' => $code,
            'stats' => $referralStats,
            'referrals' => $recent->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    /**
     * API - Valider un code parrainage (appele a l'inscription)
     */
    public static function processReferral(PDO $db, int $newUserId, ?string $refCode): void
    {
        if (empty($refCode)) return;

        $referrer = $db->prepare("SELECT id FROM users WHERE referral_code = ? AND id != ?");
        $referrer->execute([$refCode, $newUserId]);
        $referrerId = $referrer->fetchColumn();

        if (!$referrerId) return;

        // Check limit (max 20 referrals)
        $count = $db->prepare("SELECT COUNT(*) FROM referrals WHERE referrer_id = ?");
        $count->execute([$referrerId]);
        if ((int)$count->fetchColumn() >= 20) return;

        // Create pending referral
        $db->prepare("
            INSERT INTO referrals (referrer_id, referred_id, status)
            VALUES (?, ?, 'pending')
        ")->execute([$referrerId, $newUserId]);

        // Give referree welcome bonus (50 pts)
        try {
            $loyalty = new LoyaltyService($db);
            $loyalty->addPoints($newUserId, 'referral_bonus', $referrerId, 'user');
        } catch (\Exception $e) {}
    }

    /**
     * Complete referral when referred user posts first review
     */
    public static function completeReferral(PDO $db, int $userId): void
    {
        $referral = $db->prepare("
            SELECT id, referrer_id FROM referrals
            WHERE referred_id = ? AND status = 'pending' LIMIT 1
        ");
        $referral->execute([$userId]);
        $ref = $referral->fetch(PDO::FETCH_ASSOC);

        if (!$ref) return;

        $db->prepare("
            UPDATE referrals SET status = 'completed', points_awarded = 100, completed_at = NOW()
            WHERE id = ?
        ")->execute([$ref['id']]);

        // Award referrer 100 pts
        try {
            $loyalty = new LoyaltyService($db);
            $loyalty->addPoints((int)$ref['referrer_id'], 'referral_complete', $userId, 'user');
        } catch (\Exception $e) {}
    }

    private function generateCode(int $userId): string
    {
        $user = $this->db->prepare("SELECT prenom FROM users WHERE id = ?");
        $user->execute([$userId]);
        $name = $user->fetchColumn();
        $base = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name ?: 'USER'), 0, 5));
        return $base . str_pad((string)$userId, 4, '0', STR_PAD_LEFT);
    }
}
