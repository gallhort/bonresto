<?php

namespace App\Core;

use PDO; 

/**
 * Base Controller
 * Tous les contrôleurs héritent de cette classe
 */
abstract class Controller
{
    protected Request $request;
    protected Response $response;
    protected View $view;
        protected PDO $db; // AJOUTER CETTE LIGNE


    public function __construct()
    {
        $this->response = new Response();
        $this->view = new View();
                $this->db = Database::getInstance()->getPdo(); // AJOUTER CETTE LIGNE

    }
    
    /**
     * Rend une vue
     */
    protected function render(string $viewPath, array $data = []): void
    {
        $this->view->render($viewPath, $data);
    }
    
    /**
     * Retourne une réponse JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }
    
    /**
     * Redirige vers une URL
     */
    protected function redirect(string $url): void
    {
        $this->response->redirect($url);
    }
    
    /**
     * Retourne une erreur 404
     */
    protected function notFound(string $message = 'Page non trouvée'): void
    {
        $this->response->notFound($message);
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
    }
    
    /**
     * Vérifie si l'utilisateur est admin
     */
    protected function isAdmin(): bool
    {
        return isset($_SESSION['user']) && isset($_SESSION['user']['is_admin']) && (int)$_SESSION['user']['is_admin'] === 1;
    }
    
    /**
     * Middleware : Requiert l'authentification
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }
    
    /**
     * Middleware : Requiert admin
     */
    protected function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            $this->redirect('/');
            exit;
        }
    }
}
