<?php
session_start();
include('../connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

try {
    $stmt = $dbh->prepare("DELETE FROM wishlist WHERE user = ? AND resto = ?");
    $stmt->execute([$_SESSION['user'], $_POST['resto']]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}