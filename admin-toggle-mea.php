<?php
include_once('admin-protect.php');
include_once('connect.php');

// Vérifier les paramètres
if (!isset($_GET['nom']) || !isset($_GET['current'])) {
    header('Location: admin-liste-valides.php');
    exit;
}

$nomResto = trim($_GET['nom']);
$currentMea = (int)$_GET['current'];

// Inverser la valeur : 0 devient 1, 1 devient 0
$newMea = $currentMea == 1 ? 0 : 1;

// Mettre à jour dans la base
$stmt = $conn->prepare("UPDATE vendeur SET mea = ? WHERE Nom = ?");
if (!$stmt) {
    die("Erreur préparation requête : " . $conn->error);
}

$stmt->bind_param("is", $newMea, $nomResto);

if ($stmt->execute()) {
    $message = $newMea == 1 ? "activée" : "désactivée";
    header('Location: admin-liste-valides.php?success=mea&action=' . $message . '&nom=' . urlencode($nomResto));
} else {
    header('Location: admin-liste-valides.php?error=mea');
}

exit;
?>