<?php

namespace App\Core;

/**
 * Router avec DEBUG
 * Gère le routing de l'application
 */
class Router
{
    private array $routes = [];
    /**
     * Ajoute une route GET
     */
    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    /**
     * Ajoute une route POST
     */
    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    /**
     * Ajoute une route pour tous les méthodes
     */
    public function any(string $path, $handler): void
    {
        $this->addRoute('GET|POST', $path, $handler);
    }
    
    /**
     * Ajoute une route
     */
    private function addRoute(string $method, string $path, $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    /**
     * Dispatche la requête vers le bon contrôleur
     */
    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if (!$this->methodMatches($route['method'], $method)) {
                continue;
            }

            $params = $this->matchPath($route['path'], $uri);

            if ($params !== false) {
                $request->setParams($params);
                $this->executeHandler($route['handler'], $request);
                return;
            }
        }

        $response = new Response();
        $response->notFound();
    }
    
    /**
     * Vérifie si la méthode HTTP correspond
     */
    private function methodMatches(string $routeMethod, string $requestMethod): bool
    {
        $methods = explode('|', $routeMethod);
        return in_array($requestMethod, $methods);
    }
    
    /**
     * Vérifie si le chemin correspond au pattern
     * Retourne les paramètres extraits ou false
     */
    private function matchPath(string $pattern, string $uri)
    {
        // Convertir les paramètres {id} en regex
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        
        if (preg_match($regex, $uri, $matches)) {
            // Extraire uniquement les paramètres nommés
            $params = [];
            foreach ($matches as $key => $value) {
                if (is_string($key)) {
                    $params[$key] = $value;
                }
            }
            return $params;
        }
        
        return false;
    }
    
    /**
     * Exécute le handler (contrôleur ou callback)
     */
    private function executeHandler($handler, Request $request): void
    {
        if (is_callable($handler)) {
            // Si c'est une fonction anonyme
            call_user_func($handler, $request);
        } elseif (is_string($handler)) {
            // Si c'est une string "Controller@method"
            $this->executeControllerMethod($handler, $request);
        }
    }
    
    /**
     * Exécute une méthode de contrôleur
     */
    private function executeControllerMethod(string $handler, Request $request): void
    {
        list($controllerName, $method) = explode('@', $handler);
        
        // Ajouter le namespace complet
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in {$controllerClass}");
        }
        
        $controller->$method($request);
    }
}
