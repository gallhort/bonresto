<?php
session_start();
include('../connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

try {
    $stmt = $dbh->prepare("
        UPDATE users 
        SET fname = ?, lname = ?, mail = ?, adresse = ?, ville = ?, cp = ?
        WHERE login = ?
    ");
    
    $stmt->execute([
        $_POST['fname'],
        $_POST['lname'],
        $_POST['mail'],
        $_POST['address'] ?? '',
        $_POST['city'] ?? '',
        $_POST['cp'] ?? '',
        $_SESSION['user']
    ]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}