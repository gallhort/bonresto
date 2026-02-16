<?php

namespace App\Services;

/**
 * CacheService - Cache simple basé sur fichiers
 */
class CacheService
{
    private string $cacheDir;
    
    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? (defined('ROOT_PATH') ? ROOT_PATH . '/storage/cache/' : sys_get_temp_dir() . '/lebonresto_cache/');

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Récupère du cache ou exécute le callback
     */
    public function remember(string $key, callable $callback, int $ttl = 3600)
    {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $data = $callback();
        $this->set($key, $data, $ttl);
        
        return $data;
    }
    
    public function get(string $key)
    {
        $file = $this->cacheDir . md5($key) . '.cache';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $content = @file_get_contents($file);
        if ($content === false) {
            return null;
        }
        
        $data = @json_decode($content, true);
        if (!is_array($data)) {
            return null;
        }
        
        if (isset($data['expires_at']) && time() > $data['expires_at']) {
            @unlink($file);
            return null;
        }
        
        return $data['value'] ?? null;
    }
    
    public function set(string $key, $value, int $ttl = 3600): bool
    {
        $file = $this->cacheDir . md5($key) . '.cache';
        
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];
        
        return @file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }
    
    public function delete(string $key): bool
    {
        $file = $this->cacheDir . md5($key) . '.cache';

        if (file_exists($file)) {
            return @unlink($file);
        }

        return true;
    }

    /**
     * Flush all cache files
     */
    public function flush(): int
    {
        $count = 0;
        $files = glob($this->cacheDir . '*.cache');
        if ($files) {
            foreach ($files as $file) {
                if (@unlink($file)) $count++;
            }
        }
        return $count;
    }
}