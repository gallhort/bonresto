<?php

/**
 * Helper Functions
 * Fonctions utilitaires globales
 */

if (!function_exists('env')) {
    /**
     * Récupère une variable d'environnement
     */
    function env(string $key, $default = null)
    {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convertir les booléens
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}

if (!function_exists('asset')) {
    /**
     * Génère une URL vers un asset
     */
    function asset(string $path): string
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        return $baseUrl . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Génère une URL complète
     */
    function url(string $path = ''): string
    {
        $baseUrl = rtrim(env('APP_URL', ''), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirige vers une URL
     */
    function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die (pour debug)
     */
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}

if (!function_exists('old')) {
    /**
     * Récupère une ancienne valeur de formulaire
     */
    function old(string $key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Génère un token CSRF
     */
    function csrf_token(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Génère un champ hidden CSRF
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('session')) {
    /**
     * Gère les sessions
     */
    function session(string $key = null, $default = null)
    {
        if ($key === null) {
            return $_SESSION;
        }
        
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('auth')) {
    /**
     * Retourne l'utilisateur connecté
     */
    function auth()
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('isGuest')) {
    /**
     * Vérifie si l'utilisateur est invité (non connecté)
     */
    function isGuest(): bool
    {
        return !isset($_SESSION['user_id']);
    }
}

if (!function_exists('isAuth')) {
    /**
     * Vérifie si l'utilisateur est authentifié
     */
    function isAuth(): bool
    {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('sanitize')) {
    /**
     * Nettoie une chaîne pour l'affichage HTML
     */
    function sanitize(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('e')) {
    /**
     * Alias de sanitize
     */
    function e(string $string): string
    {
        return sanitize($string);
    }
}

if (!function_exists('__')) {
    /**
     * Traduction i18n — Retourne la traduction pour la clé donnée
     * Usage: __('nav.explore') ou __('auth.login_btn')
     */
    function __(string $key, string $default = ''): string
    {
        static $translations = null;
        static $loadedLocale = null;

        $locale = $_SESSION['locale'] ?? 'fr';

        // Reload if locale changed
        if ($translations === null || $loadedLocale !== $locale) {
            $langFile = ROOT_PATH . '/config/lang/' . $locale . '.php';
            if (file_exists($langFile)) {
                $data = include $langFile;
                $translations = is_array($data) ? _flattenTranslations($data) : [];
            } else {
                $translations = [];
            }
            $loadedLocale = $locale;
        }

        return $translations[$key] ?? ($default ?: $key);
    }
}

if (!function_exists('_flattenTranslations')) {
    /**
     * Aplatit un tableau imbriqué en notation point
     */
    function _flattenTranslations(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $fullKey = $prefix !== '' ? $prefix . '.' . $key : $key;
            if (is_array($value)) {
                $result = array_merge($result, _flattenTranslations($value, $fullKey));
            } else {
                $result[$fullKey] = (string)$value;
            }
        }
        return $result;
    }
}

if (!function_exists('verify_csrf')) {
    /**
     * Vérifie le token CSRF
     */
    function verify_csrf(): bool
    {
        // Check POST data, then X-CSRF-TOKEN header (for AJAX/JSON requests)
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || empty($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}