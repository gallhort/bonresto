<?php

namespace App\Core;

/**
 * Response Handler
 * Gère les réponses HTTP
 */
class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';
    
    /**
     * Définit le code de statut HTTP
     */
    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }
    
    /**
     * Ajoute un header
     */
    public function setHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }
    
    /**
     * Définit le contenu de la réponse
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Envoie la réponse
     */
    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $key => $value) {
            // Prevent header injection by stripping newlines
            $safeValue = str_replace(["\r", "\n"], '', $value);
            header("{$key}: {$safeValue}", true);
        }
        
        echo $this->body;
    }
    
    /**
     * Réponse JSON
     */
    public function json(array $data, int $statusCode = 200): void
    {
        $this->setStatusCode($statusCode)
             ->setHeader('Content-Type', 'application/json')
             ->setBody(json_encode($data))
             ->send();
    }
    
    /**
     * Redirection
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->setStatusCode($statusCode)
             ->setHeader('Location', $url)
             ->send();
        exit;
    }
    
    /**
     * Réponse 404
     */
    public function notFound(string $message = 'Page non trouvée'): void
    {
        http_response_code(404);
        $errorPage = __DIR__ . '/../Views/errors/404.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "<h1>404 - {$message}</h1>";
        }
        exit;
    }

    /**
     * Réponse 500
     */
    public function serverError(): void
    {
        http_response_code(500);
        $errorPage = __DIR__ . '/../Views/errors/500.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "<h1>500 - Erreur serveur</h1>";
        }
        exit;
    }
}
