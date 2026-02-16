<?php

namespace App\Core;

/**
 * Request Handler
 * Encapsule les données de la requête HTTP
 */
class Request
{
    private string $method;
    private string $uri;
    private array $params;
    private array $query;
    private array $post;
    private array $files;
    private array $server;
    
    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = $this->parseUri();
        $this->params = [];
        $this->query = $_GET ?? [];
        $this->post = $_POST ?? [];
        $this->files = $_FILES ?? [];
        $this->server = $_SERVER ?? [];
    }
    
    /**
     * Parse l'URI et enlève les query strings
     */
    private function parseUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Enlever les query strings
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        
        // Enlever les slashes en trop
        return '/' . trim($uri, '/');
    }
    
    /**
     * Getters
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    public function getUri(): string
    {
        return $this->uri;
    }
    
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }
    
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }
    
    /**
     * Récupère une valeur GET/POST
     */
    public function get(string $key, $default = null)
    {
        return $this->query[$key] ?? $this->post[$key] ?? $default;
    }
    
    /**
     * Récupère une valeur GET uniquement
     */
    public function query(string $key, $default = null)
    {
        return $this->query[$key] ?? $default;
    }
    
    /**
     * Récupère une valeur POST uniquement
     */
    public function post(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }
    
    /**
     * Récupère tous les paramètres GET
     */
    public function allQuery(): array
    {
        return $this->query;
    }
    
    /**
     * Récupère tous les paramètres POST
     */
    public function allPost(): array
    {
        return $this->post;
    }
    
    /**
     * Récupère tous les paramètres (GET + POST)
     */
    public function all(): array
    {
        return array_merge($this->query, $this->post);
    }
    
    /**
     * Vérifie si une clé existe
     */
    public function has(string $key): bool
    {
        return isset($this->query[$key]) || isset($this->post[$key]);
    }
    
    /**
     * Récupère un fichier uploadé
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }
    
    /**
     * Définit les paramètres de route (appelé par le Router)
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Accès aux propriétés protégées (compatibilité $request->params)
     */
    public function __get(string $name)
    {
        if ($name === 'params') {
            return $this->params;
        }
        return null;
    }

    public function __isset(string $name): bool
    {
        if ($name === 'params') {
            return true;
        }
        return false;
    }
    
    /**
     * Récupère un paramètre de route
     */
    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }
    
    /**
     * Récupère l'IP du client
     */
    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Récupère le User-Agent
     */
    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}
