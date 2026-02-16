<?php

namespace App\Services;

/**
 * Rate Limiter basique basé sur la session
 * Limite les actions par utilisateur/IP sur une fenêtre de temps
 */
class RateLimiter
{
    /**
     * Vérifie si l'action est autorisée et l'enregistre
     *
     * @param string $action   Identifiant de l'action (ex: 'login', 'review', 'report')
     * @param int    $maxAttempts Nombre max de tentatives
     * @param int    $windowSeconds Fenêtre de temps en secondes
     * @param string|null $identifier Identifiant unique (user_id ou IP). Auto-détecté si null.
     * @return bool true si l'action est autorisée, false si limite atteinte
     */
    public static function attempt(string $action, int $maxAttempts, int $windowSeconds, ?string $identifier = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $identifier = $identifier ?? self::getIdentifier();
        $key = "rate_limit_{$action}_{$identifier}";
        $now = time();

        // Initialiser si pas encore de données
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }

        // Nettoyer les tentatives expirées
        $_SESSION[$key] = array_filter($_SESSION[$key], function ($timestamp) use ($now, $windowSeconds) {
            return ($now - $timestamp) < $windowSeconds;
        });

        // Vérifier la limite
        if (count($_SESSION[$key]) >= $maxAttempts) {
            return false;
        }

        // Enregistrer la tentative
        $_SESSION[$key][] = $now;

        return true;
    }

    /**
     * Vérifie si la limite est atteinte SANS enregistrer de tentative
     */
    public static function isLimited(string $action, int $maxAttempts, int $windowSeconds, ?string $identifier = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $identifier = $identifier ?? self::getIdentifier();
        $key = "rate_limit_{$action}_{$identifier}";
        $now = time();

        if (!isset($_SESSION[$key])) {
            return false;
        }

        $recent = array_filter($_SESSION[$key], function ($timestamp) use ($now, $windowSeconds) {
            return ($now - $timestamp) < $windowSeconds;
        });

        return count($recent) >= $maxAttempts;
    }

    /**
     * Nombre de tentatives restantes
     */
    public static function remaining(string $action, int $maxAttempts, int $windowSeconds, ?string $identifier = null): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $identifier = $identifier ?? self::getIdentifier();
        $key = "rate_limit_{$action}_{$identifier}";
        $now = time();

        if (!isset($_SESSION[$key])) {
            return $maxAttempts;
        }

        $recent = array_filter($_SESSION[$key], function ($timestamp) use ($now, $windowSeconds) {
            return ($now - $timestamp) < $windowSeconds;
        });

        return max(0, $maxAttempts - count($recent));
    }

    /**
     * Réinitialise le compteur pour une action
     */
    public static function reset(string $action, ?string $identifier = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $identifier = $identifier ?? self::getIdentifier();
        $key = "rate_limit_{$action}_{$identifier}";
        unset($_SESSION[$key]);
    }

    /**
     * Identifiant par défaut : user_id si connecté, sinon IP
     */
    private static function getIdentifier(): string
    {
        if (!empty($_SESSION['user']['id'])) {
            return 'user_' . $_SESSION['user']['id'];
        }

        return 'ip_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }
}
