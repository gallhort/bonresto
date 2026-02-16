<?php

namespace App\Services;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SERVICE DE LOGGING - LEBONRESTO
 * Remplace error_log() avec gestion par environnement
 * ═══════════════════════════════════════════════════════════════════════════
 */
class Logger
{
    private static ?Logger $instance = null;
    private bool $enabled;
    private string $logPath;
    private static bool $isLogging = false; // ✅ Prévenir récursion infinie
    
    private function __construct()
    {
        $this->enabled = getenv('APP_DEBUG') === 'true';
        $this->logPath = __DIR__ . '/../../storage/logs/app.log';
        
        // Créer le dossier logs s'il n'existe pas
        $logDir = dirname($this->logPath);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Singleton
     */
    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }
    
    /**
     * Log de niveau DEBUG
     */
    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->log('DEBUG', $message, $context);
    }
    
    /**
     * Log de niveau INFO
     */
    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->log('INFO', $message, $context);
    }
    
    /**
     * Log de niveau WARNING
     */
    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->log('WARNING', $message, $context);
    }
    
    /**
     * Log de niveau ERROR
     */
    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->log('ERROR', $message, $context);
    }
    
    /**
     * Log de niveau CRITICAL (toujours logué, même en production)
     */
    public static function critical(string $message, array $context = []): void
    {
        self::getInstance()->log('CRITICAL', $message, $context, true);
    }
    
    /**
     * Méthode centrale de logging
     */
    private function log(string $level, string $message, array $context = [], bool $forceLog = false): void
    {
        // ✅ PROTECTION : Éviter récursion infinie
        if (self::$isLogging) {
            return;
        }
        
        try {
            self::$isLogging = true;
            
            // En production, seuls ERROR et CRITICAL sont loggés
            if (!$this->enabled && !$forceLog && !in_array($level, ['ERROR', 'CRITICAL'])) {
                return;
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
            
            $logMessage = sprintf(
                "[%s] %s: %s %s\n",
                $timestamp,
                $level,
                $message,
                $contextStr
            );
            
            // Écrire dans le fichier
            @file_put_contents($this->logPath, $logMessage, FILE_APPEND);
            
            // En mode debug, aussi afficher dans error_log PHP
            if ($this->enabled) {
                @error_log($logMessage);
            }
            
        } catch (\Throwable $e) {
            // ✅ SILENCIEUX : Ne rien faire en cas d'erreur pour éviter boucle infinie
            // On pourrait utiliser error_log natif PHP ici
            @error_log("Logger error: " . $e->getMessage());
        } finally {
            self::$isLogging = false;
        }
    }
    
    /**
     * Nettoyer les vieux logs (> 30 jours)
     */
    public static function cleanup(): void
    {
        try {
            $instance = self::getInstance();
            
            if (!file_exists($instance->logPath)) {
                return;
            }
            
            $fileTime = filemtime($instance->logPath);
            $thirtyDaysAgo = time() - (30 * 24 * 60 * 60);
            
            if ($fileTime < $thirtyDaysAgo) {
                // Archiver l'ancien fichier
                $archivePath = $instance->logPath . '.' . date('Y-m-d', $fileTime);
                @rename($instance->logPath, $archivePath);
            }
        } catch (\Throwable $e) {
            // Silencieux
            @error_log("Logger cleanup error: " . $e->getMessage());
        }
    }
}