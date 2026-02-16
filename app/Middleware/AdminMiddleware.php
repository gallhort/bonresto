<?php

namespace App\Middleware;

use App\Services\Logger;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * MIDDLEWARE ADMIN - LEBONRESTO
 * Protège les routes admin contre l'accès non autorisé
 * ═══════════════════════════════════════════════════════════════════════════
 */
class AdminMiddleware
{
    /**
     * Vérifie que l'utilisateur est connecté ET admin
     */
    public static function handle(): void
    {
        // Démarrer session si pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Vérifier si utilisateur connecté
        if (!isset($_SESSION['user'])) {
            Logger::warning('Tentative accès admin sans authentification', [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
            
            $_SESSION['error'] = 'Vous devez être connecté pour accéder à cette page';
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        // Vérifier si utilisateur est admin
        if (!isset($_SESSION['user']['is_admin']) || $_SESSION['user']['is_admin'] !== 1) {
            Logger::warning('Tentative accès admin par utilisateur non-admin', [
                'user_id' => $_SESSION['user']['id'] ?? 'unknown',
                'email' => $_SESSION['user']['email'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
            
            $_SESSION['error'] = 'Accès refusé. Cette section est réservée aux administrateurs.';
            header('Location: /');
            exit;
        }
        
    }
    
    /**
     * Vérifie les permissions spécifiques (pour actions sensibles)
     */
    public static function requirePermission(string $permission): void
    {
        self::handle(); // Vérifie d'abord qu'on est admin
        
        // Pour l'instant, tous les admins ont toutes les permissions
        // À étendre plus tard avec un système de rôles granulaire
        
    }
}
