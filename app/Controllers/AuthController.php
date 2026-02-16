<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;
use App\Services\LoyaltyService;
use App\Services\Logger;
use App\Services\EmailService;
use App\Services\RateLimiter;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * AUTH CONTROLLER - LEBONRESTO
 * Gestion complète de l'authentification (login, register, reset password)
 * ═══════════════════════════════════════════════════════════════════════════
 */
class AuthController extends Controller
{
    private User $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        // ✅ EmailService instancié uniquement quand nécessaire
    }
    
    /**
     * Affiche le formulaire de connexion
     */
    public function login(Request $request): void
    {
        $data = [
            'title' => 'Connexion',
            'error' => $_SESSION['error'] ?? null
        ];
        
        // Nettoyer l'erreur après affichage
        unset($_SESSION['error']);
        
        $this->render('auth/login', $data);
    }
    
    /**
     * Traite la connexion
     */
    public function doLogin(Request $request): void
    {
        // Vérification CSRF
        if (!\verify_csrf()) {
            Logger::warning('Tentative de login avec CSRF invalide', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            http_response_code(403);
            $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide'
            ], 403);
            return;
        }
        
        // Rate limiting : 5 tentatives par 15 minutes
        if (!RateLimiter::attempt('login', 5, 900)) {
            $this->json([
                'success' => false,
                'message' => 'Trop de tentatives de connexion. Réessayez dans quelques minutes.'
            ], 429);
            return;
        }

        $identifier = $request->post('email'); // Peut être email OU username
        $password = $request->post('password');

        // Validation
        if (!$identifier || !$password) {
            $this->json([
                'success' => false,
                'message' => 'Email/Username et mot de passe requis'
            ], 400);
            return;
        }
        
        // Chercher l'utilisateur par email OU username
        $user = $this->userModel->findByEmail($identifier);
        if (!$user) {
            $user = $this->userModel->findByUsername($identifier);
        }
        
        if (!$user) {
            Logger::info('Tentative de login avec identifiant inconnu', [
                'identifier' => $identifier
            ]);
            
            $this->json([
                'success' => false,
                'message' => 'Identifiants incorrects'
            ], 401);
            return;
        }
        
        // Vérifier le mot de passe (bcrypt uniquement)
        $passwordValid = password_verify($password, $user['password_hash']);
        
        if (!$passwordValid) {
            Logger::info('Tentative de login avec mot de passe incorrect', [
                'user_id' => $user['id'],
                'email' => $user['email']
            ]);
            
            $this->json([
                'success' => false,
                'message' => 'Identifiants incorrects'
            ], 401);
            return;
        }
        
        // Régénérer l'ID de session (prévention fixation de session)
        session_regenerate_id(true);

        // Créer la session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'prenom' => $user['prenom'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'username' => $user['username'],
            'is_admin' => $user['is_admin']
        ];
        
        // Mettre à jour le dernier login
        $this->userModel->updateLastLogin($user['id']);
        
        // Log connexion réussie
        Logger::info('Connexion réussie', [
            'user_id' => $user['id'],
            'email' => $user['email']
        ]);
        
        // ═══════════════════════════════════════════════════════════════
        // FIDÉLITÉ - Point de connexion quotidienne
        // ═══════════════════════════════════════════════════════════════
        try {
            $loyaltyService = new LoyaltyService($this->db);
            $loyaltyService->addPoints((int)$user['id'], 'daily_login');
        } catch (\Exception $e) {
            Logger::error('Erreur attribution points login', [
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
        }
        // ═══════════════════════════════════════════════════════════════
        
        // Gérer la redirection
        $redirectUrl = $request->get('redirect') ?? '/';
        
        $this->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'redirect' => $redirectUrl
        ]);
    }
    
    /**
     * Vérifie si un email/username existe (pour formulaire)
     */
    public function checkEmail(Request $request): void
    {
        $identifier = $request->get('email'); // email OU username
        
        if (!$identifier) {
            $this->json(['exists' => false]);
            return;
        }
        
        // Chercher par email OU username
        $user = $this->userModel->findByEmail($identifier);
        if (!$user) {
            $user = $this->userModel->findByUsername($identifier);
        }
        
        $this->json(['exists' => $user !== null]);
    }
    
    /**
     * Affiche le formulaire d'inscription
     */
    public function register(Request $request): void
    {
        $data = [
            'title' => 'Inscription',
            'error' => null
        ];
        
        $this->render('auth/register', $data);
    }
    
    /**
     * Traite l'inscription
     */
    public function doRegister(Request $request): void
    {
        $prenom = $request->post('prenom');
        $nom = $request->post('nom');
        $email = $request->post('email');
        $username = $request->post('username');
        $password = $request->post('password');
        $passwordConfirm = $request->post('password_confirm');
        $genre = $request->post('genre');
        
        // Validation
        $errors = [];
        
        if (!$prenom) $errors[] = 'Prénom requis';
        if (!$nom) $errors[] = 'Nom requis';
        if (!$email) $errors[] = 'Email requis';
        if (!$username) $errors[] = 'Nom d\'utilisateur requis';
        if (!$password) $errors[] = 'Mot de passe requis';
        if ($password && strlen($password) < 8) $errors[] = 'Le mot de passe doit faire au moins 8 caractères';
        if ($password !== $passwordConfirm) $errors[] = 'Les mots de passe ne correspondent pas';
        
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => implode(', ', $errors)
            ], 400);
            return;
        }
        
        // Vérifier si l'email existe déjà
        if ($this->userModel->findByEmail($email)) {
            $this->json([
                'success' => false,
                'message' => 'Cet email est déjà utilisé'
            ], 400);
            return;
        }
        
        // Vérifier si le username existe déjà
        if ($this->userModel->findByUsername($username)) {
            $this->json([
                'success' => false,
                'message' => 'Ce nom d\'utilisateur est déjà utilisé'
            ], 400);
            return;
        }
        
        // Créer l'utilisateur
        $userId = $this->userModel->createUser([
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'genre' => $genre
        ]);
        
        if ($userId) {
            Logger::info('Nouvelle inscription', [
                'user_id' => $userId,
                'email' => $email,
                'username' => $username
            ]);
            
            // ═══════════════════════════════════════════════════════════════
            // FIDÉLITÉ - Points de bienvenue
            // ═══════════════════════════════════════════════════════════════
            try {
                $loyaltyService = new LoyaltyService($this->db);
                $loyaltyService->addPoints($userId, 'register');
            } catch (\Exception $e) {
                Logger::error('Erreur attribution points inscription', [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }
            // ═══════════════════════════════════════════════════════════════
            
            // ═══════════════════════════════════════════════════════════════
            // PARRAINAGE - Check referral code
            // ═══════════════════════════════════════════════════════════════
            $refCode = $request->post('ref') ?? ($_GET['ref'] ?? null);
            if ($refCode) {
                try {
                    ReferralController::processReferral($this->db, $userId, $refCode);
                } catch (\Exception $e) {
                    Logger::error('Erreur parrainage', ['error' => $e->getMessage()]);
                }
            }
            // ═══════════════════════════════════════════════════════════════
            
            // Générer token de vérification email
            $verificationToken = bin2hex(random_bytes(32));
            try {
                $verifyStmt = $this->db->prepare("
                    INSERT INTO email_verifications (user_id, email, token, created_at, expires_at)
                    VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))
                ");
                $verifyStmt->execute([$userId, $email, $verificationToken]);
            } catch (\Exception $e) {
                Logger::error('Erreur création token vérification', ['error' => $e->getMessage()]);
            }

            // Envoyer email de bienvenue + vérification
            try {
                $emailService = new EmailService();
                $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'lebonresto.dz');
                $verifyUrl = $baseUrl . '/verify-email?token=' . $verificationToken;
                $emailService->sendVerificationEmail($email, $prenom, $verifyUrl);
                $emailService->sendWelcomeEmail($email, $prenom);
            } catch (\Exception $e) {
                Logger::error('Erreur envoi email bienvenue', [
                    'user_id' => $userId,
                    'error' => $e->getMessage()
                ]);
            }

            $this->json([
                'success' => true,
                'message' => 'Inscription réussie ! Vérifiez votre email pour activer votre compte.',
                'redirect' => '/login'
            ]);
        } else {
            Logger::error('Erreur création utilisateur', [
                'email' => $email,
                'username' => $username
            ]);
            
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription'
            ], 500);
        }
    }
    
    /**
     * Déconnexion
     */
    public function logout(Request $request): void
    {
        $userId = $_SESSION['user']['id'] ?? null;
        
        Logger::info('Déconnexion', [
            'user_id' => $userId
        ]);
        
        session_destroy();
        header('Location: /');
        exit;
    }
    
    /**
     * Vérifie l'email via le token
     */
    public function verifyEmail(Request $request): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $_SESSION['error'] = 'Token de vérification manquant';
            header('Location: /login');
            exit;
        }

        $stmt = $this->db->prepare("
            SELECT ev.*, u.prenom
            FROM email_verifications ev
            INNER JOIN users u ON u.id = ev.user_id
            WHERE ev.token = ?
            AND ev.expires_at > NOW()
            AND ev.verified_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $verification = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$verification) {
            $this->render('auth/verify-email', [
                'title' => 'Vérification email',
                'success' => false,
                'message' => 'Ce lien de vérification est invalide ou a expiré.'
            ]);
            return;
        }

        // Marquer le token comme utilisé
        $this->db->prepare("UPDATE email_verifications SET verified_at = NOW() WHERE id = ?")->execute([$verification['id']]);

        // Marquer l'utilisateur comme vérifié
        $this->db->prepare("UPDATE users SET email_verified = 1, email_verification_token = NULL WHERE id = ?")->execute([$verification['user_id']]);

        Logger::info('Email vérifié', ['user_id' => $verification['user_id'], 'email' => $verification['email']]);

        $this->render('auth/verify-email', [
            'title' => 'Email vérifié',
            'success' => true,
            'prenom' => $verification['prenom'],
            'message' => 'Votre adresse email a été vérifiée avec succès !'
        ]);
    }

    /**
     * Renvoyer l'email de vérification
     */
    public function resendVerification(Request $request): void
    {
        if (!RateLimiter::attempt('resend_verification', 3, 600)) {
            $this->json(['success' => false, 'message' => 'Trop de tentatives. Réessayez dans 10 minutes.'], 429);
            return;
        }

        $email = $request->post('email');
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Email requis'], 400);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if (!$user) {
            $this->json(['success' => true, 'message' => 'Si cet email existe, un lien de vérification a été envoyé.']);
            return;
        }

        if (!empty($user['email_verified'])) {
            $this->json(['success' => true, 'message' => 'Cet email est déjà vérifié.']);
            return;
        }

        // Créer nouveau token
        $token = bin2hex(random_bytes(32));
        $this->db->prepare("
            INSERT INTO email_verifications (user_id, email, token, created_at, expires_at)
            VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 24 HOUR))
        ")->execute([$user['id'], $email, $token]);

        try {
            $emailService = new EmailService();
            $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'lebonresto.dz');
            $emailService->sendVerificationEmail($email, $user['prenom'], $baseUrl . '/verify-email?token=' . $token);
        } catch (\Exception $e) {
            Logger::error('Erreur renvoi email vérification', ['error' => $e->getMessage()]);
        }

        $this->json(['success' => true, 'message' => 'Un nouveau lien de vérification a été envoyé.']);
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════
     * RESET PASSWORD - WORKFLOW COMPLET
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Affiche le formulaire "mot de passe oublié"
     */
    public function forgotPassword(Request $request): void
    {
        $data = [
            'title' => 'Mot de passe oublié'
        ];
        
        $this->render('auth/forgot-password', $data);
    }
    
    /**
     * Traite la demande de réinitialisation
     */
    public function doForgotPassword(Request $request): void
    {
        $email = $request->post('email');
        
        if (!$email) {
            $this->json([
                'success' => false,
                'message' => 'Email requis'
            ], 400);
            return;
        }
        
        $user = $this->userModel->findByEmail($email);
        
        if ($user) {
            // Générer le token
            $token = bin2hex(random_bytes(32));
            
            // Stocker en base
            $stmt = $this->db->prepare("
                INSERT INTO password_resets (email, token, created_at) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$email, $token]);
            
            // Envoyer l'email
            try {
                $emailService = new EmailService(); // ✅ Instancié ici
                $resetUrl = url('reset-password?token=' . $token);
                $emailService->sendPasswordResetEmail(
                    $email,
                    $user['prenom'],
                    $resetUrl
                );
                
                Logger::info('Email reset password envoyé', [
                    'email' => $email,
                    'user_id' => $user['id']
                ]);
            } catch (\Exception $e) {
                Logger::error('Erreur envoi email reset password', [
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            Logger::info('Demande reset password pour email inconnu', [
                'email' => $email
            ]);
        }
        
        // Toujours retourner le même message (sécurité)
        // Ne pas révéler si l'email existe ou non
        $this->json([
            'success' => true,
            'message' => 'Si cet email existe, un lien de réinitialisation vous a été envoyé'
        ]);
    }
    
    /**
     * Formulaire de réinitialisation avec token
     */
    public function resetPassword(Request $request): void
    {
        $token = $request->get('token');
        
        if (!$token) {
            $_SESSION['error'] = 'Token manquant';
            header('Location: /login');
            exit;
        }
        
        // Vérifier le token (valide < 24h)
        $stmt = $this->db->prepare("
            SELECT pr.*, u.prenom 
            FROM password_resets pr
            INNER JOIN users u ON u.email = pr.email
            WHERE pr.token = ? 
            AND pr.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY pr.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$reset) {
            $data = [
                'title' => 'Lien invalide',
                'error' => 'Ce lien de réinitialisation est invalide ou a expiré',
                'token' => null
            ];
        } else {
            $data = [
                'title' => 'Réinitialiser le mot de passe',
                'token' => $token,
                'prenom' => $reset['prenom'],
                'error' => null
            ];
        }
        
        $this->render('auth/reset-password', $data);
    }
    
    /**
     * Traite la réinitialisation du mot de passe
     */
    public function doResetPassword(Request $request): void
    {
        $token = $request->post('token');
        $password = $request->post('password');
        $passwordConfirm = $request->post('password_confirm');
        
        if (!$token || !$password || $password !== $passwordConfirm) {
            $this->json([
                'success' => false,
                'message' => 'Données invalides'
            ], 400);
            return;
        }
        
        // Vérifier le token
        $stmt = $this->db->prepare("
            SELECT pr.*, u.id as user_id 
            FROM password_resets pr
            INNER JOIN users u ON u.email = pr.email
            WHERE pr.token = ? 
            AND pr.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY pr.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$reset) {
            $this->json([
                'success' => false,
                'message' => 'Token invalide ou expiré'
            ], 400);
            return;
        }
        
        // Mettre à jour le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password_hash = ? 
            WHERE id = ?
        ");
        
        if ($stmt->execute([$hashedPassword, $reset['user_id']])) {
            // Supprimer le token utilisé
            $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            Logger::info('Mot de passe réinitialisé', [
                'user_id' => $reset['user_id'],
                'email' => $reset['email']
            ]);
            
            $this->json([
                'success' => true,
                'message' => 'Mot de passe réinitialisé avec succès',
                'redirect' => '/login'
            ]);
        } else {
            Logger::error('Erreur réinitialisation mot de passe', [
                'user_id' => $reset['user_id']
            ]);
            
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation'
            ], 500);
        }
    }
}