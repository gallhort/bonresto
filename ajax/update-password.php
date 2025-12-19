<?php
session_start();
include('../connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

try {
    // Vérifier l'ancien mot de passe
    $stmt = $dbh->prepare("SELECT passwd FROM users WHERE login = ?");
    $stmt->execute([$_SESSION['user']]);
    $user = $stmt->fetch();
    
    if (!password_verify($_POST['current_password'], $user['passwd'])) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe actuel incorrect']);
        exit;
    }
    
    // Mettre à jour
    $newHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $stmt = $dbh->prepare("UPDATE users SET passwd = ? WHERE login = ?");
    $stmt->execute([$newHash, $_SESSION['user']]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}