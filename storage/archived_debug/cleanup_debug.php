#!/usr/bin/env php
<?php

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SCRIPT DE NETTOYAGE DEBUG - LEBONRESTO
 * Remplace automatiquement error_log() par Logger::
 * ═══════════════════════════════════════════════════════════════════════════
 */

$dryRun = in_array('--dry-run', $argv);
$verbose = in_array('-v', $argv) || in_array('--verbose', $argv);

if ($dryRun) {
    echo "🔍 MODE DRY-RUN : Aucune modification ne sera effectuée\n\n";
}

$baseDir = __DIR__;
$directories = [
    $baseDir . '/app/Controllers',
    $baseDir . '/app/Models',
    $baseDir . '/app/Services',
];

$stats = [
    'files_scanned' => 0,
    'files_modified' => 0,
    'error_log_replaced' => 0,
    'imports_added' => 0,
];

function scanDirectory($dir, &$files = []) {
    if (!is_dir($dir)) return $files;
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            scanDirectory($path, $files);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $files[] = $path;
        }
    }
    return $files;
}

function needsLoggerImport($content) {
    return !preg_match('/use\s+App\\\\Services\\\\Logger;/', $content);
}

function addLoggerImport($content) {
    // Trouver la position après le namespace
    if (preg_match('/(namespace\s+[^;]+;)/', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $pos = $matches[0][1] + strlen($matches[0][0]);
        
        // Vérifier s'il y a déjà des use statements
        if (preg_match('/\nuse\s+/', substr($content, $pos, 500))) {
            // Ajouter après les use existants
            $content = preg_replace(
                '/(namespace\s+[^;]+;\s*\n(?:use[^;]+;\s*\n)*)/s',
                "$1use App\\Services\\Logger;\n",
                $content,
                1
            );
        } else {
            // Ajouter juste après le namespace
            $content = preg_replace(
                '/(namespace\s+[^;]+;\s*\n)/',
                "$1\nuse App\\Services\\Logger;\n",
                $content,
                1
            );
        }
    }
    
    return $content;
}

function replaceErrorLog($content) {
    $count = 0;
    
    // Cas 1 : error_log("message simple")
    $content = preg_replace_callback(
        '/error_log\s*\(\s*"([^"]+)"\s*\);?/',
        function($matches) use (&$count) {
            $count++;
            $message = str_replace('\\"', '"', $matches[1]);
            // Déterminer le niveau (error ou debug)
            $level = (stripos($message, 'erreur') !== false || stripos($message, 'error') !== false)
                ? 'error' : 'debug';
            return "Logger::{$level}(\"{$message}\");";
        },
        $content
    );
    
    // Cas 2 : error_log("message: " . $var)
    $content = preg_replace_callback(
        '/error_log\s*\(\s*"([^"]+)"\s*\.\s*([^)]+)\);?/',
        function($matches) use (&$count) {
            $count++;
            $message = str_replace('\\"', '"', $matches[1]);
            $var = trim($matches[2]);
            $level = (stripos($message, 'erreur') !== false || stripos($message, 'error') !== false)
                ? 'error' : 'debug';
            return "Logger::{$level}(trim(\"{$message}\"), [{$var}]);";
        },
        $content
    );
    
    // Cas 3 : error_log($var)
    $content = preg_replace_callback(
        '/error_log\s*\(\s*([^)]+)\);?/',
        function($matches) use (&$count) {
            $var = trim($matches[1]);
            // Si déjà converti, ignorer
            if (strpos($var, 'Logger::') !== false) return $matches[0];
            $count++;
            return "Logger::debug({$var});";
        },
        $content
    );
    
    return [$content, $count];
}

echo "🧹 NETTOYAGE DES DEBUG - LEBONRESTO\n";
echo "════════════════════════════════════════════════════════════════════════\n\n";

// Scanner tous les fichiers
$files = [];
foreach ($directories as $dir) {
    scanDirectory($dir, $files);
}

echo "📁 Fichiers trouvés : " . count($files) . "\n\n";

foreach ($files as $file) {
    $stats['files_scanned']++;
    
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileModified = false;
    
    // Compter error_log avant
    $errorLogCount = preg_match_all('/error_log\s*\(/', $content);
    
    if ($errorLogCount > 0) {
        if ($verbose) {
            echo "📝 " . basename($file) . " : {$errorLogCount} error_log trouvés\n";
        }
        
        // Ajouter import Logger si nécessaire
        if (needsLoggerImport($content)) {
            $content = addLoggerImport($content);
            $stats['imports_added']++;
            $fileModified = true;
        }
        
        // Remplacer error_log
        list($content, $replaced) = replaceErrorLog($content);
        $stats['error_log_replaced'] += $replaced;
        
        if ($replaced > 0) {
            $fileModified = true;
        }
        
        // Sauvegarder si modifié
        if ($fileModified && !$dryRun) {
            file_put_contents($file, $content);
            $stats['files_modified']++;
            echo "✅ " . basename($file) . " : {$replaced} remplacements\n";
        } elseif ($fileModified && $dryRun) {
            echo "🔍 [DRY-RUN] " . basename($file) . " : {$replaced} remplacements détectés\n";
        }
    }
}

echo "\n════════════════════════════════════════════════════════════════════════\n";
echo "📊 STATISTIQUES\n";
echo "════════════════════════════════════════════════════════════════════════\n\n";
echo "Fichiers scannés     : {$stats['files_scanned']}\n";
echo "Fichiers modifiés    : {$stats['files_modified']}\n";
echo "error_log remplacés  : {$stats['error_log_replaced']}\n";
echo "Imports Logger ajoutés : {$stats['imports_added']}\n\n";

if ($dryRun) {
    echo "ℹ️  Pour appliquer les modifications, relancez sans --dry-run\n\n";
} else {
    echo "✅ Nettoyage terminé !\n\n";
    echo "IMPORTANT : Vérifiez manuellement certains fichiers complexes :\n";
    echo "  - ReviewController.php\n";
    echo "  - RestaurantController.php\n\n";
}

echo "════════════════════════════════════════════════════════════════════════\n";