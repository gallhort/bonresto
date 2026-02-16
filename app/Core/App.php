<?php

namespace App\Core;

/**
 * Application Bootstrap
 * Initialise l'application et gère le routing
 */
class App
{
    private Router $router;
    private Request $request;
    
    public function __construct()
    {
        $this->loadEnvironment();
        $this->startSession();
        $this->router = new Router();
        $this->request = new Request();
    }
    
    /**
     * Charge les variables d'environnement depuis .env
     */
    private function loadEnvironment(): void
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        
        if (!file_exists($envPath)) {
            throw new \Exception('.env file not found');
        }
        
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Ignorer les commentaires
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Parser la ligne KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = array_map('trim', explode('=', $line, 2));
                $value = trim($value, "\"' ");
                
                if (!empty($key)) {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                }
            }
        }
    }
    
    /**
     * Démarre la session
     */
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Retourne le router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
    
    /**
     * Lance l'application
     */
    public function run(): void
    {
        try {
            $this->router->dispatch($this->request);
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Gestion des erreurs
     */
    private function handleError(\Exception $e): void
    {
        if (getenv('APP_DEBUG') === 'true') {
            echo "<h1>Erreur</h1>";
            echo "<p>{$e->getMessage()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
        } else {
            http_response_code(500);
            echo "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
        
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    }
}
