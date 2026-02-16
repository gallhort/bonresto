<?php

/**
 * Application Entry Point
 * Point d'entrée unique de l'application
 */

// Sécuriser et démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    session_start();
}
// Démarrer le buffering de sortie
ob_start();

// Définir le chemin racine de l'application
define('ROOT_PATH', dirname(__DIR__));
// === DEBUG LOGS ===
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/storage/logs/debug.log');
if (!is_dir(ROOT_PATH . '/storage/logs')) @mkdir(ROOT_PATH . '/storage/logs', 0755, true);
// === FIN DEBUG ===
// Autoloader Composer
require_once ROOT_PATH . '/vendor/autoload.php';
// Charger les helpers
require_once ROOT_PATH . '/app/Helpers/helpers.php';
// Créer et lancer l'application
try {
    // Log rotation check (once per request, lightweight)
    \App\Services\Logger::cleanup();

    $app = new App\Core\App();
    
    // Charger les routes
    $router = $app->getRouter();
    require_once ROOT_PATH . '/config/routes.php';
    
    // Lancer l'application
    $app->run();
    
} catch (Exception $e) {
    // En cas d'erreur, afficher un message propre
    if (env('APP_DEBUG') === 'true') {
        echo "<h1>Erreur</h1>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "Une erreur est survenue. Veuillez réessayer plus tard.";
    }
    
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
}

// Envoyer le buffer
ob_end_flush();
