<?php
/**
 * FICHIER DE PROTECTION ADMIN
 * À inclure en haut de chaque page admin
 */

// Démarrer la session si pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 1️⃣ Vérification du droit admin
 */
if (!isset($_SESSION['admin']) || (int)$_SESSION['admin'] !== 1) {
    header('Location: admin-login.php?error=access');
    exit;
}

/**
 * 2️⃣ Expiration de session (2 heures)
 */
$timeout = 7200; // 2 heures

if (isset($_SESSION['admin_login_time'])) {
    if ((time() - $_SESSION['admin_login_time']) > $timeout) {
        session_unset();
        session_destroy();
        header('Location: admin-login.php?error=timeout');
        exit;
    }
}

// Mise à jour de l’activité
$_SESSION['admin_login_time'] = time();

/**
 * 3️⃣ Variables admin sécurisées (ANTI WARNING)
 */
$admin_id   = $_SESSION['admin_id']   ?? null;
$admin_name = $_SESSION['admin_name'] ?? ($_SESSION['user'] ?? 'Administrateur');
$admin_mail = $_SESSION['admin_mail'] ?? null;
