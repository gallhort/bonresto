<?php

namespace App\Core;

/**
 * View Renderer
 * Moteur de template simple avec layouts
 */
class View
{
    private string $viewsPath;
    private string $layout = 'layouts/app';
    private array $data = [];
    
    public function __construct()
    {
        $this->viewsPath = dirname(__DIR__) . '/Views/';
    }
    
    /**
     * Définit le layout à utiliser
     */
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Rend une vue avec son layout
     */
  public function render(string $view, array $data = []): void
{
    $this->data = $data;
    
    // Capturer le contenu de la vue
    ob_start();
    $viewFile = $this->viewsPath . str_replace('.', '/', $view) . '.php';
    
    if (!file_exists($viewFile)) {
        throw new \Exception("View file not found: {$viewFile}");
    }
    
    // Extraire les variables APRÈS avoir construit le chemin (EXTR_SKIP protège les vars internes)
    extract($data, EXTR_SKIP);
    
    include $viewFile;
    $content = ob_get_clean();
    
    // Inclure le layout si défini
    if ($this->layout) {
        $layoutFile = $this->viewsPath . str_replace('.', '/', $this->layout) . '.php';
        
        if (!file_exists($layoutFile)) {
            throw new \Exception("Layout file not found: {$layoutFile}");
        }
        
        include $layoutFile;
    } else {
        echo $content;
    }
}
    
    /**
     * Rend une vue sans layout
     */
    public function renderPartial(string $view, array $data = []): void
    {
        $previousLayout = $this->layout;
        $this->layout = '';
        $this->render($view, $data);
        $this->layout = $previousLayout;
    }
    
    /**
     * Inclut un partial
     */
    public function include(string $partial, array $data = []): void
    {
        extract(array_merge($this->data, $data), EXTR_SKIP);
        
        $partialFile = $this->viewsPath . 'partials/' . $partial . '.php';
        
        if (!file_exists($partialFile)) {
            throw new \Exception("Partial file not found: {$partialFile}");
        }
        
        include $partialFile;
    }
}
