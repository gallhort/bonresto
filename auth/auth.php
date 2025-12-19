<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CHEMIN RELATIF AU FICHIER connect.php
require_once __DIR__ . '/../connect.php';

function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit;
    }
}

function loginUser($username, $remember = false) {
    $_SESSION['user'] = $username;

    if ($remember) {
        setcookie(
            'remember_user',
            $username,
            time() + (86400 * 30), // 30 jours
            '/',
            '',
            false,
            true
        );
    }
}

function logoutUser() {
    session_unset();
    session_destroy();
    setcookie('remember_user', '', time() - 3600, '/');
}

// Auto-login via cookie
if (!isLoggedIn() && !empty($_COOKIE['remember_user'])) {
    $_SESSION['user'] = $_COOKIE['remember_user'];
}