<?php
session_start();
require_once 'config/database.php'; // ton fichier de connexion DB

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileupload'])) {
    
    $nomRestaurant = $_POST['nom'];
    $nomPosteur = $_SESSION['user'];
    $file = $_FILES['fileupload'];
    
    // Validation du fichier
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        die('Type de fichier non autorisé');
    }
    
    if ($file['size'] > $maxSize) {
        die('Fichier trop volumineux (max 5MB)');
    }
    
    // Créer le dossier du restaurant s'il n'existe pas
    $restaurantFolder = 'img/' . preg_replace('/[^a-zA-Z0-9-_]/', '_', $nomRestaurant);
    if (!file_exists($restaurantFolder)) {
        mkdir($restaurantFolder, 0755, true);
    }
    
    // Générer un nom unique po upload-photo.phpur la photo
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $restaurantFolder . '/' . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        
        // Insérer dans la base de données
        $stmt = $pdo->prepare("
            INSERT INTO photos_restaurants (nom_restaurant, chemin_photo, nom_posteur) 
            VALUES (?, ?, ?)
        ");
        
        if ($stmt->execute([$nomRestaurant, $destination, $nomPosteur])) {
            echo json_encode(['success' => true, 'path' => $destination]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur BD']);
        }
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur upload']);
    }
}
?>