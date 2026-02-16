<?php
/**
 * SCRIPT DEBUG - À ajouter TEMPORAIREMENT en haut de public/index.php
 * 
 * IMPORTANT : Supprime après debug (sécurité)
 */

// Afficher TOUTES les erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Logger dans un fichier
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// Créer dossier logs si inexistant
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

echo "<!-- DEBUG MODE ACTIVÉ -->\n";
