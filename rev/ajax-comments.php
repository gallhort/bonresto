<?php
session_start();

// Activer le logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// (A) CONNECT TO DATABASE
// Utiliser le connecteur centralisé (connect.php) pour récupérer la connexion PDO ($dbh)
include_once __DIR__ . '/../connect.php';
if (!isset($dbh) || !$dbh) {
    die(json_encode(["error" => "Impossible de se connecter à la BDD"]));
}
// $dbh est un PDO provenant de connect.php
$pdo = $dbh; // compatibilité avec le reste du code

// ⭐ IMPORTANT : TRAITER LES VOTES EN PREMIER (AVANT TOUT LE RESTE)
if (isset($_POST['id']) && isset($_POST['useful'])) {

    $commentId = intval($_POST['id']);
    $value = intval($_POST['useful']);
    $user = $_SESSION['user'] ?? 'anon';

    try {
        // Vérifier si l'utilisateur a déjà voté
        $check = $pdo->prepare("SELECT value FROM votes_useful WHERE user_id = ? AND comment_id = ?");
        $check->execute([$user, $commentId]);
        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Si l'utilisateur clique la même chose → suppression du vote
            if ($existing['value'] == $value) {
                
                $del = $pdo->prepare("DELETE FROM votes_useful WHERE user_id = ? AND comment_id = ?");
                $del->execute([$user, $commentId]);

                $pdo->prepare("UPDATE comments SET useful = useful - ? WHERE comment_id = ?")
                    ->execute([$value, $commentId]);

                $stmt = $pdo->prepare("
                    SELECT
                        COALESCE(SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END), 0) AS utiles,
                        COALESCE(SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END), 0) AS inutiles
                    FROM votes_useful
                    WHERE comment_id = ?
                ");
                $stmt->execute([$commentId]);
                $votes = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $u = (int)$votes['utiles'];
                $i = (int)$votes['inutiles'];
                
                if ($u == 0 && $i == 0) {
                    $voteText = "Soyez le premier à voter";
                } else {
                    $voteText = 
                        $u . " utile" . ($u > 1 ? "s" : "") .
                        " • " .
                        $i . " pas utile" . ($i > 1 ? "s" : "");
                }

                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'removed',
                    'user_vote' => 0,
                    'utiles' => $u,
                    'inutiles' => $i,
                    'vote_text' => $voteText
                ]);
                exit;
            }

            // Mise à jour du vote existant
            $upd = $pdo->prepare("UPDATE votes_useful SET value = ? WHERE user_id = ? AND comment_id = ?");
            $upd->execute([$value, $user, $commentId]);

            // Corriger le total dans comments.useful
            $diff = $value - $existing['value'];
            $pdo->prepare("UPDATE comments SET useful = useful + ? WHERE comment_id = ?")
                ->execute([$diff, $commentId]);

        } else {

            // Nouveau vote
            $ins = $pdo->prepare("INSERT INTO votes_useful (user_id, comment_id, value) VALUES (?,?,?)");
            $ins->execute([$user, $commentId, $value]);

            // Mise à jour score
            $pdo->prepare("UPDATE comments SET useful = useful + ? WHERE comment_id = ?")
                ->execute([$value, $commentId]);
        }

        // ✅ RÉCUPÉRER LES COMPTEURS FINAUX
        $stmt = $pdo->prepare("
            SELECT
                COALESCE(SUM(CASE WHEN value = 1 THEN 1 ELSE 0 END), 0) AS utiles,
                COALESCE(SUM(CASE WHEN value = -1 THEN 1 ELSE 0 END), 0) AS inutiles
            FROM votes_useful
            WHERE comment_id = ?
        ");
        $stmt->execute([$commentId]);
        $votes = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $u = (int)$votes['utiles'];
        $i = (int)$votes['inutiles'];
        
        if ($u == 0 && $i == 0) {
            $voteText = "Soyez le premier à voter";
        } else {
            $voteText = 
                $u . " utile" . ($u > 1 ? "s" : "") .
                " • " .
                $i . " pas utile" . ($i > 1 ? "s" : "");
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'ok',
            'user_vote' => $value,
            'utiles' => $u,
            'inutiles' => $i,
            'vote_text' => $voteText
        ]);
        
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $e->getMessage()
        ]);
    }
    
    exit; // ⭐ TRÈS IMPORTANT : SORTIR ICI
}

// ====================================================================
// 2. AJOUT D'UN COMMENTAIRE (après les votes)
// ====================================================================

if (!isset($_POST['nom'])) {
    echo json_encode(["error" => "Aucune donnée reçue"]);
    exit;
}

$nom = $_POST['nom'];
$nomDB = htmlentities($_POST['nom']);

$stmtN = $pdo->prepare("SELECT COUNT(*) as nbr FROM comments WHERE user = ? AND nom = ?");
$stmtN->execute([$_POST['name'] ?? '', $_POST['nom'] ?? '']);
$nrows = $stmtN->fetch();

if ($nrows['nbr'] == 0) {
  try {

    $note = ($_POST['food_quality'] + $_POST['service'] + $_POST['location'] + $_POST['price']) / 4;
    $source = 'site';

    $stmt = $pdo->prepare("INSERT INTO `comments`
      (`user`, `message`, `nom`, `note`, `title`, `food`, `service`, `location`, `price`, `source`)
      VALUES (?,?,?,?,?,?,?,?,?,?)");

    $stmt->execute([
      htmlentities($_POST['name']),
      htmlentities($_POST['msg']),
      $nomDB,
      $note,
      htmlentities($_POST['title']),
      htmlentities($_POST['food_quality']),
      htmlentities($_POST['service']),
      htmlentities($_POST['location']),
      htmlentities($_POST['price']),
      $source
    ]);

    // ====================================================================
    // 🆕 GESTION DE L'UPLOAD DE PHOTOS MULTIPLES
    // ====================================================================
    
    // Récupérer l'ID du commentaire qui vient d'être inséré
    $commentId = $pdo->lastInsertId();
    
    if (isset($_FILES['fileupload']) && !empty($_FILES['fileupload']['name'][0])) {
        
        $nomPosteur = htmlentities($_POST['name']);
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $maxPhotos = 5;
        
        // Créer le dossier du restaurant s'il n'existe pas
        $restaurantFolder = '../img/' . preg_replace('/[^a-zA-Z0-9-_]/', '_', $nom);
        if (!file_exists($restaurantFolder)) {
            mkdir($restaurantFolder, 0755, true);
        }
        
        // Traiter chaque fichier
        $uploadedCount = 0;
        foreach ($_FILES['fileupload']['tmp_name'] as $key => $tmpName) {
            
            if ($uploadedCount >= $maxPhotos) break;
            
            $fileError = $_FILES['fileupload']['error'][$key];
            $fileSize = $_FILES['fileupload']['size'][$key];
            $fileType = $_FILES['fileupload']['type'][$key];
            $fileName = $_FILES['fileupload']['name'][$key];
            
            // Validation
            if ($fileError !== UPLOAD_ERR_OK) continue;
            if ($fileSize > $maxSize) continue;
            if (!in_array($fileType, $allowedTypes)) continue;
            
            // Générer un nom unique
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '_' . $uploadedCount . '.' . $extension;
            $destination = $restaurantFolder . '/' . $filename;
            
            // Déplacer le fichier
            if (move_uploaded_file($tmpName, $destination)) {
                
                // Chemin relatif pour la BDD
                $cheminBDD = 'img/' . preg_replace('/[^a-zA-Z0-9-_]/', '_', $nom) . '/' . $filename;
                
                // Insérer dans la base de données avec le comment_id
                $stmtPhoto = $pdo->prepare("
                    INSERT INTO photos_users (comment_id, nom_restaurant, chemin_photo, nom_posteur) 
                    VALUES (?, ?, ?, ?)
                ");
                
                $stmtPhoto->execute([$commentId, $nom, $cheminBDD, $nomPosteur]);
                $uploadedCount++;
            }
        }
    }
    // ====================================================================

    // Mise à jour de la note moyenne dans vendeur
    $sql = "UPDATE vendeur v 
            SET v.note = (SELECT SUM(c.note)/COUNT(c.note) 
                          FROM comments c 
                          WHERE c.nom = ? AND c.note > 0)
            WHERE v.nom = ?";
    $rtmt = $pdo->prepare($sql);
    $rtmt->execute([$nom, $nom]);

  } catch (Exception $ex) {
    die($ex->getMessage());
  }

  header("Location: ../detail-restaurant-2.php?ret=1&nom=" . urlencode($nom));
  exit;

} else {

  header("Location: ../detail-restaurant-2.php?ret=2&nom=" . urlencode($nom));
  exit;

}

// close
$stmt = null;
$pdo = null;
?>