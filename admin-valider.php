<?php
include_once('admin-protect.php');
include_once('connect.php');

// Vérifier que le nom du restaurant est fourni
if (!isset($_GET['nom']) || empty($_GET['nom'])) {
    header('Location: admin-liste-attente.php');
    exit;
}

$nomResto = trim($_GET['nom']);

// ============================================
// RÉCUPÉRER LES DONNÉES DU RESTAURANT
// ============================================
$stmt = $conn->prepare("SELECT * FROM addresto WHERE Nom = ? LIMIT 1");
$stmt->bind_param("s", $nomResto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin-liste-attente.php?error=notfound');
    exit;
}

$resto = $result->fetch_assoc();

// ============================================
// ÉTAPE 1 : INSÉRER DANS LA TABLE VENDEUR
// ============================================
$stmtVendeur = $conn->prepare("
    INSERT INTO vendeur (
        Nom, Type, adresse, codePostal, ville, owner, gps, pricerange, 
        note, descriptif, mea, phone, web
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 0, ?, ?)
");

$stmtVendeur->bind_param(
    "sssssssssss",
    $resto['Nom'],
    $resto['Type'],
    $resto['adresse'],
    $resto['codePostal'],
    $resto['ville'],
    $resto['owner'],
    $resto['gps'],
    $resto['pricerange'],
    $resto['descriptif'],
    $resto['phone'],
    $resto['web']
);

if (!$stmtVendeur->execute()) {
    die("Erreur lors de l'insertion dans vendeur : " . $conn->error);
}

// ============================================
// ÉTAPE 2 : INSÉRER DANS LA TABLE PHOTOS
// ============================================
$stmtPhotos = $conn->prepare("
    INSERT INTO photos (Nom, main, slide1, slide2, slide3)
    VALUES (?, ?, ?, ?, ?)
");

$stmtPhotos->bind_param(
    "sssss",
    $resto['Nom'],
    $resto['main'],
    $resto['slide1'],
    $resto['slide2'],
    $resto['slide3']
);

if (!$stmtPhotos->execute()) {
    die("Erreur lors de l'insertion dans photos : " . $conn->error);
}

// ============================================
// ÉTAPE 3 : RÉCUPÉRER ET INSÉRER LES HORAIRES
// ============================================
$stmtHoraires = $conn->prepare("SELECT * FROM horaires WHERE Nom = ? LIMIT 1");
$stmtHoraires->bind_param("s", $nomResto);
$stmtHoraires->execute();
$resultHoraires = $stmtHoraires->get_result();

if ($resultHoraires->num_rows > 0) {
    $horaires = $resultHoraires->fetch_assoc();
    
    // Les horaires existent déjà dans la table horaires, pas besoin de les dupliquer
    // (la table opehoraires ning est commune pour tous les restaurants)
}

// ============================================
// ÉTAPE 4 : SUPPRIMER DE LA TABLE ADDRESTO
// ============================================
$stmtDelete = $conn->prepare("DELETE FROM addresto WHERE Nom = ?");
$stmtDelete->bind_param("s", $nomResto);
$stmtDelete->execute();

// ============================================
// ÉTAPE 5 : ENVOYER UN EMAIL DE CONFIRMATION
// ============================================
$to = "sourtirane@yahoo.fr"; // Email du propriétaire (tu peux ajouter un champ email dans addresto)
$subject = "✅ Votre restaurant a été validé !";
$message = "
Bonjour,

Excellente nouvelle ! Votre restaurant '{$resto['Nom']}' a été validé par notre équipe.

Il est désormais visible sur notre plateforme et accessible à tous nos utilisateurs.

Détails de votre établissement :
- Nom : {$resto['Nom']}
- Type : {$resto['Type']}
- Adresse : {$resto['adresse']}, {$resto['codePostal']} {$resto['ville']}
- Téléphone : {$resto['phone']}

Vous pouvez maintenant vous connecter avec votre compte propriétaire pour gérer vos informations.

Merci de votre confiance !

L'équipe de gestion
";

$headers = "From: sourtirane@yahoo.fr\r\n";
$headers .= "Reply-To: sourtirane@yahoo.fr\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

mail($to, $subject, $message, $headers);

// ============================================
// REDIRECTION AVEC MESSAGE DE SUCCÈS
// ============================================
header('Location: admin-liste-attente.php?success=validated&nom=' . urlencode($nomResto));
exit;
?>