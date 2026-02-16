<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SERVICE EMAIL - LEBONRESTO
 * Gestion centralisée de tous les emails transactionnels
 * Version sans PHPMailer (fallback gracieux)
 * ═══════════════════════════════════════════════════════════════════════════
 */
class EmailService
{
    private $mailer = null;
    private string $fromEmail;
    private string $fromName;
    private bool $enabled;
    private bool $phpMailerAvailable = false;
    
    public function __construct()
    {
        // Configuration depuis .env ou config
        $this->fromEmail = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@lebonresto.dz';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'LeBonResto';
        $this->enabled = getenv('MAIL_ENABLED') !== 'false';
        
        // ✅ Vérifier si PHPMailer est disponible
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $this->phpMailerAvailable = true;
            $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            if ($this->enabled) {
                $this->configureSMTP();
            }
        } else {
            // PHPMailer non disponible - mode fallback
            $this->phpMailerAvailable = false;
            if ($this->enabled) {
                @error_log("⚠️ PHPMailer non installé - Les emails seront simulés. Installez avec: composer require phpmailer/phpmailer");
            }
        }
    }
    
    /**
     * Configure le serveur SMTP
     */
    private function configureSMTP(): void
    {
        if (!$this->phpMailerAvailable) return;
        
        try {
            $driver = getenv('MAIL_DRIVER') ?: 'smtp';
            
            if ($driver === 'smtp') {
                $this->mailer->isSMTP();
                $this->mailer->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
                $this->mailer->SMTPAuth = true;
                $this->mailer->Username = getenv('MAIL_USERNAME') ?: '';
                $this->mailer->Password = getenv('MAIL_PASSWORD') ?: '';
                $this->mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $this->mailer->Port = (int)(getenv('MAIL_PORT') ?: 587);
            }
            
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->CharSet = 'UTF-8';
            $this->mailer->isHTML(true);
        } catch (\Throwable $e) {
            @error_log("EmailService SMTP config error: " . $e->getMessage());
        }
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * EMAILS D'AUTHENTIFICATION
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * 📧 EMAIL: Bienvenue (inscription)
     */
    public function sendWelcomeEmail(string $email, string $prenom): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email bienvenue: {$email}");
            return true;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $prenom);
            
            $this->mailer->Subject = "🎉 Bienvenue sur LeBonResto !";
            
            $html = $this->renderTemplate('email-welcome', [
                'prenom' => $prenom
            ]);
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);
            
            return $this->mailer->send();
            
        } catch (\Throwable $e) {
            @error_log("EmailService error (welcome): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 📧 EMAIL: Réinitialisation mot de passe
     */
    public function sendPasswordResetEmail(string $email, string $prenom, string $resetUrl): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email reset password: {$email} - URL: {$resetUrl}");
            return true;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $prenom);
            
            $this->mailer->Subject = "🔐 Réinitialisation de votre mot de passe - LeBonResto";
            
            $html = $this->renderTemplate('email-password-reset', [
                'prenom' => $prenom,
                'resetUrl' => $resetUrl
            ]);
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);
            
            return $this->mailer->send();
            
        } catch (\Throwable $e) {
            @error_log("EmailService error (reset): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ═══════════════════════════════════════════════════════════════════════
     * EMAILS D'AVIS
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * 📧 EMAIL: Avis approuvé par IA/Admin
     */
    public function sendReviewApproved(array $user, array $review, array $restaurant): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email avis approuvé: {$user['email']}");
            return true;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']);
            
            $this->mailer->Subject = "✅ Votre avis sur {$restaurant['nom']} a été publié !";
            
            $html = $this->renderTemplate('email-review-approved', [
                'user_name' => $user['prenom'],
                'restaurant_name' => $restaurant['nom'],
                'restaurant_url' => getenv('APP_URL') . '/restaurant/' . $restaurant['id'],
                'review_title' => $review['title'] ?? '',
                'review_rating' => $review['note_globale'],
                'review_message' => substr($review['message'], 0, 150) . '...',
                'spam_score' => $review['spam_score'] ?? 100,
                'moderated_by' => $review['moderated_by'] === 'ai' ? 'Intelligence Artificielle' : 'Modérateur'
            ]);
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);
            
            return $this->mailer->send();
            
        } catch (\Throwable $e) {
            @error_log("EmailService error (review approved): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 📧 EMAIL: Avis rejeté par IA
     */
    public function sendReviewRejected(array $user, array $review, array $restaurant, array $reasons): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email avis rejeté: {$user['email']}");
            return true;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']);
            
            $this->mailer->Subject = "❌ Votre avis sur {$restaurant['nom']} n'a pas été publié";
            
            $html = $this->renderTemplate('email-review-rejected', [
                'user_name' => $user['prenom'],
                'restaurant_name' => $restaurant['nom'],
                'reasons' => $reasons,
                'spam_score' => $review['spam_score'],
                'support_url' => getenv('APP_URL') . '/contact',
                'guidelines_url' => getenv('APP_URL') . '/avis-regles'
            ]);
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);
            
            return $this->mailer->send();
            
        } catch (\Throwable $e) {
            @error_log("EmailService error (review rejected): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 📧 EMAIL: Nouvel avis en attente → Admin
     */
    public function sendNewReviewPendingAdmin(array $review, array $restaurant, array $user): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email admin avis pending");
            return true;
        }
        
        try {
            // Récupérer emails admins (à adapter selon ta structure)
            $adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@lebonresto.dz';
            
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($adminEmail);
            
            $this->mailer->Subject = "⏳ Nouvel avis à modérer - {$restaurant['nom']}";
            
            $html = $this->renderTemplate('email-review-pending-admin', [
                'restaurant_name' => $restaurant['nom'],
                'user_name' => $user['prenom'] . ' ' . $user['nom'],
                'review_rating' => $review['note_globale'],
                'review_message' => substr($review['message'], 0, 200) . '...',
                'spam_score' => $review['spam_score'] ?? 100,
                'admin_url' => getenv('APP_URL') . '/admin/reviews',
                'review_id' => $review['id'] ?? 'N/A'
            ]);
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);
            
            return $this->mailer->send();
            
        } catch (\Throwable $e) {
            @error_log("EmailService error (admin pending): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 📧 EMAIL: Réponse du propriétaire
     */
    public function sendOwnerResponse(array $user, array $restaurant, array $review, string $ownerResponse): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email réponse proprio: {$user['email']}");
            return true;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($user['email'], $user['prenom'] . ' ' . $user['nom']);
            
            $this->mailer->Subject = "💬 {$restaurant['nom']} a répondu à votre avis";
            
            $html = $this->renderTemplate('owner-response', [
                'user_name' => $user['prenom'],
                'restaurant_name' => $restaurant['nom'],
                'review_title' => $review['title'] ?? 'Votre avis',
                'owner_response' => $ownerResponse,
                'review_url' => getenv('APP_URL') . '/restaurant/' . $restaurant['id'] . '#review-' . $review['id']
            ]);
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);
            
            return $this->mailer->send();
            
        } catch (\Throwable $e) {
            @error_log("EmailService error (owner response): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 📧 EMAIL: Vérification d'email
     */
    public function sendVerificationEmail(string $email, string $prenom, string $verifyUrl): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email vérification: {$email} - URL: {$verifyUrl}");
            return true;
        }

        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $prenom);

            $this->mailer->Subject = "Vérifiez votre adresse email - LeBonResto";

            $html = $this->renderTemplate('email-verification', [
                'prenom' => $prenom,
                'verifyUrl' => $verifyUrl
            ]);

            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            return $this->mailer->send();

        } catch (\Throwable $e) {
            @error_log("EmailService error (verification): " . $e->getMessage());
            return false;
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════
     * MÉTHODES UTILITAIRES
     * ═══════════════════════════════════════════════════════════════════════
     */
    
    /**
     * Rend un template HTML d'email
     */
    private function renderTemplate(string $template, array $data): string
    {
        $templatePath = __DIR__ . "/../Views/emails/{$template}.php";
        
        if (!file_exists($templatePath)) {
            return $this->getDefaultTemplate($data);
        }
        
        try {
            ob_start();
            extract($data);
            include $templatePath;
            return ob_get_clean();
        } catch (\Throwable $e) {
            @error_log("Email template error: " . $e->getMessage());
            return $this->getDefaultTemplate($data);
        }
    }
    
    /**
     * Template par défaut si fichier manquant
     */
    private function getDefaultTemplate(array $data): string
    {
        $content = '';
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $content .= "<p><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> {$value}</p>";
            }
        }
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #00635a 0%, #00897b 100%); color: white; padding: 30px; text-align: center; }
        .content { padding: 30px; }
        .footer { background: #f9f9f9; padding: 20px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ LeBonResto</h1>
        </div>
        <div class="content">
            {$content}
        </div>
        <div class="footer">
            <p>&copy; 2024 LeBonResto - Tous droits réservés</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Envoi d'email générique
     */
    public function send(string $to, string $subject, string $body, string $toName = ''): bool
    {
        if (!$this->enabled || !$this->phpMailerAvailable) {
            @error_log("📧 [SIMULATED] Email: {$to} - {$subject}");
            return true;
        }
        
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $toName);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            return $this->mailer->send();
            
        } catch (\Throwable $e) {
            @error_log("EmailService error (generic): " . $e->getMessage());
            return false;
        }
    }
}