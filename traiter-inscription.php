<?php
session_start();
include_once('connect.php');

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inscription-restaurant.php');
    exit;
}

// ============================================
// 1. RÉCUPÉRATION ET NETTOYAGE DES DONNÉES
// ============================================

$nom = trim($_POST['nom']);
$type = trim($_POST['type']);
$adresse = trim($_POST['adresse']);
$codePostal = trim($_POST['codePostal']);
$ville = trim($_POST['ville']);
$descriptif = trim($_POST['descriptif']);
$phone = trim($_POST['phone']);
$web = trim($_POST['web']);
$pricerange = $_POST['pricerange'];

// GPS
$gps_lat = !empty($_POST['gps_lat']) ? trim($_POST['gps_lat']) : null;
$gps_lng = !empty($_POST['gps_lng']) ? trim($_POST['gps_lng']) : null;
$gps = ($gps_lat && $gps_lng) ? $gps_lat . ',' . $gps_lng : null;

// Options (0 ou 1)
$parking = isset($_POST['parking']) ? 1 : 0;
$wifi = isset($_POST['wifi']) ? 1 : 0;
$gamezone = isset($_POST['gamezone']) ? 1 : 0;
$baby = isset($_POST['baby']) ? 1 : 0;
$handi = isset($_POST['handi']) ? 1 : 0;
$priere = isset($_POST['priere']) ? 1 : 0;
$private = isset($_POST['private']) ? 1 : 0;
$voiturier = isset($_POST['voiturier']) ? 1 : 0;

// ============================================
// 2. GESTION DES UPLOADS DE PHOTOS
// ============================================

$upload_dir = 'assets/images/vendeur/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$photos = ['main', 'slide1', 'slide2', 'slide3'];
$uploaded_photos = [];

foreach ($photos as $photo_name) {
    if (isset($_FILES[$photo_name]) && $_FILES[$photo_name]['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES[$photo_name]['tmp_name'];
        $file_name = $_FILES[$photo_name]['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Vérifier l'extension
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_ext, $allowed_ext)) {
            continue;
        }
        
        // Générer un nom unique
        $new_name = $nom . '_' . $photo_name . '_' . time() . '.' . $file_ext;
        $new_name = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $new_name);
        $destination = $upload_dir . $new_name;
        
        // Déplacer le fichier
        if (move_uploaded_file($file_tmp, $destination)) {
            $uploaded_photos[$photo_name] = $destination;
        }
    } else {
        $uploaded_photos[$photo_name] = null;
    }
}

// ============================================
// 3. INSERTION DANS LA TABLE "addresto"
// ============================================

try {
    $sql = "INSERT INTO addresto (
        Nom, Type, adresse, codePostal, ville, descriptif, phone, web,
        main, slide1, slide2, slide3,
        baby, gamezone, handi, parking, priere, private, voiturier, wifi,
        gps, pricerange
    ) VALUES (
        :nom, :type, :adresse, :codePostal, :ville, :descriptif, :phone, :web,
        :main, :slide1, :slide2, :slide3,
        :baby, :gamezone, :handi, :parking, :priere, :private, :voiturier, :wifi,
        :gps, :pricerange
    )";
    
    $stmt = $dbh->prepare($sql);
    
    $stmt->execute([
        ':nom' => $nom,
        ':type' => $type,
        ':adresse' => $adresse,
        ':codePostal' => $codePostal,
        ':ville' => $ville,
        ':descriptif' => $descriptif,
        ':phone' => $phone,
        ':web' => $web,
        ':main' => $uploaded_photos['main'],
        ':slide1' => $uploaded_photos['slide1'],
        ':slide2' => $uploaded_photos['slide2'],
        ':slide3' => $uploaded_photos['slide3'],
        ':baby' => $baby,
        ':gamezone' => $gamezone,
        ':handi' => $handi,
        ':parking' => $parking,
        ':priere' => $priere,
        ':private' => $private,
        ':voiturier' => $voiturier,
        ':wifi' => $wifi,
        ':gps' => $gps,
        ':pricerange' => $pricerange
    ]);
    
    $restaurant_id = $dbh->lastInsertId();
    
} catch (PDOException $e) {
    die("Erreur lors de l'insertion : " . $e->getMessage());
}

// ============================================
// 4. INSERTION DES HORAIRES DANS "opening"
// ============================================

$jours = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'];
$horaires = [];

foreach ($jours as $jour) {
    // Matin
    $mat_open = !empty($_POST[$jour . '_mat_open']) ? str_replace(':', '', $_POST[$jour . '_mat_open']) : '9999';
    $mat_close = !empty($_POST[$jour . '_mat_close']) ? str_replace(':', '', $_POST[$jour . '_mat_close']) : '0000';
    $horaires[$jour . '_mat'] = ($mat_open !== '9999') ? $mat_open . '-' . $mat_close : '9999-0000';
    
    // Après-midi
    $ap_open = !empty($_POST[$jour . '_ap_open']) ? str_replace(':', '', $_POST[$jour . '_ap_open']) : '9999';
    $ap_close = !empty($_POST[$jour . '_ap_close']) ? str_replace(':', '', $_POST[$jour . '_ap_close']) : '0000';
    $horaires[$jour . '_ap'] = ($ap_open !== '9999') ? $ap_open . '-' . $ap_close : '9999-0000';
}

try {
    $sql_horaires = "INSERT INTO opening (
        Nom, 
        lun_mat, lun_ap, mar_mat, mar_ap, mer_mat, mer_ap, 
        jeu_mat, jeu_ap, ven_mat, ven_ap, sam_mat, sam_ap, dim_mat, dim_ap
    ) VALUES (
        :nom,
        :lun_mat, :lun_ap, :mar_mat, :mar_ap, :mer_mat, :mer_ap,
        :jeu_mat, :jeu_ap, :ven_mat, :ven_ap, :sam_mat, :sam_ap, :dim_mat, :dim_ap
    )";
    
    $stmt_horaires = $dbh->prepare($sql_horaires);
    $stmt_horaires->execute([
        ':nom' => $nom,
        ':lun_mat' => $horaires['lun_mat'],
        ':lun_ap' => $horaires['lun_ap'],
        ':mar_mat' => $horaires['mar_mat'],
        ':mar_ap' => $horaires['mar_ap'],
        ':mer_mat' => $horaires['mer_mat'],
        ':mer_ap' => $horaires['mer_ap'],
        ':jeu_mat' => $horaires['jeu_mat'],
        ':jeu_ap' => $horaires['jeu_ap'],
        ':ven_mat' => $horaires['ven_mat'],
        ':ven_ap' => $horaires['ven_ap'],
        ':sam_mat' => $horaires['sam_mat'],
        ':sam_ap' => $horaires['sam_ap'],
        ':dim_mat' => $horaires['dim_mat'],
        ':dim_ap' => $horaires['dim_ap']
    ]);
    
} catch (PDOException $e) {
    die("Erreur lors de l'insertion des horaires : " . $e->getMessage());
}

// ============================================
// 5. REDIRECTION VERS PAGE DE CONFIRMATION
// ============================================

$_SESSION['success_message'] = "Votre restaurant a été soumis avec succès ! Nous allons le vérifier et il sera en ligne sous 24-48h.";
header('Location: confirmation-inscription.php');
exit;

?>