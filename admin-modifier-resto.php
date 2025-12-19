<?php
include_once('admin-protect.php');
include_once('connect.php');

// Vérifier que le nom est fourni
if (!isset($_GET['nom']) || empty($_GET['nom'])) {
    header('Location: admin-liste-valides.php');
    exit;
}

$nomResto = trim($_GET['nom']);

// Si formulaire soumis
 // ============================================
// PARTIE 1 : À AJOUTER DANS LA SECTION "Si formulaire soumis"
// Remplace la ligne 21 à 47 de ton fichier admin-modifier-resto.php
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = trim($_POST['type']);
    $adresse = trim($_POST['adresse']);
    $codePostal = trim($_POST['codePostal']);
    $ville = trim($_POST['ville']);
    $owner = trim($_POST['owner']);
    $gps = trim($_POST['gps']);
    $pricerange = trim($_POST['pricerange']);
    $descriptif = trim($_POST['descriptif']);
    $phone = trim($_POST['phone']);
    $web = trim($_POST['web']);
    $mea = isset($_POST['mea']) ? 1 : 0;
    
    // Mise à jour des infos générales
    $stmt = $conn->prepare("UPDATE vendeur SET Type=?, adresse=?, codePostal=?, ville=?, owner=?, gps=?, pricerange=?, descriptif=?, phone=?, web=?, mea=? WHERE Nom=?");
    $stmt->bind_param("ssssssssssss", $type, $adresse, $codePostal, $ville, $owner, $gps, $pricerange, $descriptif, $phone, $web, $mea, $nomResto);
    
    if ($stmt->execute()) {
        // MISE À JOUR DES HORAIRES
        $jours = ['lun', 'mar', 'mer', 'jeu', 'ven', 'sam', 'dim'];
        $horairesData = [];
        
        foreach ($jours as $jour) {
            // Matin
            if (isset($_POST[$jour . '_ferme_mat'])) {
                $horairesData[$jour . '_mat'] = '9999-0000';
            } else {
                $heure_debut_mat = str_replace(':', '', $_POST[$jour . '_debut_mat'] ?? '0800');
                $heure_fin_mat = str_replace(':', '', $_POST[$jour . '_fin_mat'] ?? '1200');
                $horairesData[$jour . '_mat'] = $heure_debut_mat . '-' . $heure_fin_mat;
            }
            
            // Après-midi
            if (isset($_POST[$jour . '_ferme_ap'])) {
                $horairesData[$jour . '_ap'] = '9999-0000';
            } else {
                $heure_debut_ap = str_replace(':', '', $_POST[$jour . '_debut_ap'] ?? '1400');
                $heure_fin_ap = str_replace(':', '', $_POST[$jour . '_fin_ap'] ?? '2000');
                $horairesData[$jour . '_ap'] = $heure_debut_ap . '-' . $heure_fin_ap;
            }
        }
        
        // Vérifier si les horaires existent déjà
        $stmtCheckHoraires = $conn->prepare("SELECT id FROM horaires WHERE Nom = ?");
        $stmtCheckHoraires->bind_param("s", $nomResto);
        $stmtCheckHoraires->execute();
        $resultCheck = $stmtCheckHoraires->get_result();
        
        if ($resultCheck->num_rows > 0) {
            // UPDATE
            $stmtUpdateHoraires = $conn->prepare("UPDATE horaires SET 
                lun_mat=?, lun_ap=?, mar_mat=?, mar_ap=?, mer_mat=?, mer_ap=?, 
                jeu_mat=?, jeu_ap=?, ven_mat=?, ven_ap=?, sam_mat=?, sam_ap=?, 
                dim_mat=?, dim_ap=? WHERE Nom=?");
            $stmtUpdateHoraires->bind_param("sssssssssssssss",
                $horairesData['lun_mat'], $horairesData['lun_ap'],
                $horairesData['mar_mat'], $horairesData['mar_ap'],
                $horairesData['mer_mat'], $horairesData['mer_ap'],
                $horairesData['jeu_mat'], $horairesData['jeu_ap'],
                $horairesData['ven_mat'], $horairesData['ven_ap'],
                $horairesData['sam_mat'], $horairesData['sam_ap'],
                $horairesData['dim_mat'], $horairesData['dim_ap'],
                $nomResto
            );
            $stmtUpdateHoraires->execute();
        } else {
            // INSERT
            $stmtInsertHoraires = $conn->prepare("INSERT INTO horaires 
                (Nom, lun_mat, lun_ap, mar_mat, mar_ap, mer_mat, mer_ap, 
                jeu_mat, jeu_ap, ven_mat, ven_ap, sam_mat, sam_ap, dim_mat, dim_ap) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmtInsertHoraires->bind_param("sssssssssssssss",
                $nomResto,
                $horairesData['lun_mat'], $horairesData['lun_ap'],
                $horairesData['mar_mat'], $horairesData['mar_ap'],
                $horairesData['mer_mat'], $horairesData['mer_ap'],
                $horairesData['jeu_mat'], $horairesData['jeu_ap'],
                $horairesData['ven_mat'], $horairesData['ven_ap'],
                $horairesData['sam_mat'], $horairesData['sam_ap'],
                $horairesData['dim_mat'], $horairesData['dim_ap']
            );
            $stmtInsertHoraires->execute();
        }
        
        header('Location: admin-liste-valides.php?success=updated&nom=' . urlencode($nomResto));
        exit;
    } else {
        $error = "Erreur lors de la mise à jour";
    }
}


// Récupérer les données du restaurant
$stmt = $conn->prepare("SELECT * FROM vendeur WHERE Nom = ? LIMIT 1");
if (!$stmt) {
    die("Erreur : " . $conn->error);
}
$stmt->bind_param("s", $nomResto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin-liste-valides.php?error=notfound');
    exit;
}

$resto = $result->fetch_assoc();

// Récupérer les photos
$stmtPhotos = $conn->prepare("SELECT * FROM photos WHERE Nom = ? LIMIT 1");
$photos = null;
if ($stmtPhotos) {
    $stmtPhotos->bind_param("s", $nomResto);
    $stmtPhotos->execute();
    $resultPhotos = $stmtPhotos->get_result();
    if ($resultPhotos->num_rows > 0) {
        $photos = $resultPhotos->fetch_assoc();
    }
}

// Récupérer les horaires
$stmtHoraires = $conn->prepare("SELECT * FROM horaires WHERE Nom = ? LIMIT 1");
$horaires = null;
if ($stmtHoraires) {
    $stmtHoraires->bind_param("s", $nomResto);
    $stmtHoraires->execute();
    $resultHoraires = $stmtHoraires->get_result();
    if ($resultHoraires->num_rows > 0) {
        $horaires = $resultHoraires->fetch_assoc();
    }
}

// ============================================
// PARTIE 2 : FONCTION PHP À AJOUTER APRÈS LA RÉCUPÉRATION DES HORAIRES
// Juste avant le <!DOCTYPE html>
// ============================================

// Fonction pour parser les horaires
function parseHoraire($horaire) {
    // Si vide ou invalide
    if (empty($horaire) || !is_string($horaire)) {
        return ['ferme' => true, 'debut' => '08:00', 'fin' => '12:00'];
    }
    
    // Si fermé
    if ($horaire == '9999-0000' || $horaire == '9999-9999') {
        return ['ferme' => true, 'debut' => '08:00', 'fin' => '12:00'];
    }
    
    // Parser le format HHMM-HHMM
    $parts = explode('-', $horaire);
    
    // Vérifier qu'on a bien 2 parties
    if (count($parts) != 2) {
        return ['ferme' => true, 'debut' => '08:00', 'fin' => '12:00'];
    }
    
    // Vérifier que chaque partie fait 4 caractères
    if (strlen($parts[0]) != 4 || strlen($parts[1]) != 4) {
        return ['ferme' => true, 'debut' => '08:00', 'fin' => '12:00'];
    }
    
    return [
        'ferme' => false,
        'debut' => substr($parts[0], 0, 2) . ':' . substr($parts[0], 2, 2),
        'fin' => substr($parts[1], 0, 2) . ':' . substr($parts[1], 2, 2)
    ];
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier - <?php echo htmlspecialchars($resto['Nom']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
        }

        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header-left p {
            opacity: 0.9;
            font-size: 14px;
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: white;
            color: #667eea;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px 40px;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .edit-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .edit-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .checkbox-wrapper:hover {
            background: #e9ecef;
        }

        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .checkbox-wrapper label {
            cursor: pointer;
            font-weight: 500;
        }

        /* SIDEBAR */
        .sidebar-card {
            position: sticky;
            top: 20px;
        }

        .preview-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 64px;
        }

        .preview-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .info-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-label {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
        }

        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }

        .horaires-readonly {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .horaires-readonly strong {
            color: #2c3e50;
        }

        .note-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }

        @media (max-width: 968px) {
            .edit-grid {
                grid-template-columns: 1fr;
            }

            .sidebar-card {
                position: relative;
                top: 0;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="header-left">
            <h1>✏️ Modifier un Restaurant</h1>
            <p><?php echo htmlspecialchars($resto['Nom']); ?></p>
        </div>
        <a href="admin-liste-valides.php" class="btn-back">← Retour</a>
    </div>

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="error-message">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="edit-grid">
            <!-- COLONNE PRINCIPALE -->
            <div>
                <!-- INFORMATIONS GÉNÉRALES -->
                <div class="edit-card">
                    <h2 class="section-title">ℹ️ Informations générales</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Nom du restaurant (non modifiable)</label>
                        <input type="text" class="form-input" value="<?php echo htmlspecialchars($resto['Nom']); ?>" disabled>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="type">Type de cuisine *</label>
                            <input type="text" class="form-input" id="type" name="type" value="<?php echo htmlspecialchars($resto['Type']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="pricerange">Fourchette de prix *</label>
                            <select class="form-select" id="pricerange" name="pricerange" required>
                                <option value="$" <?php echo $resto['pricerange'] == '$' ? 'selected' : ''; ?>>$ Économique</option>
                                <option value="$$" <?php echo $resto['pricerange'] == '$$' ? 'selected' : ''; ?>>$$ Moyen</option>
                                <option value="$$$" <?php echo $resto['pricerange'] == '$$$' ? 'selected' : ''; ?>>$$$ Cher</option>
                                <option value="$$$$" <?php echo $resto['pricerange'] == '$$$$' ? 'selected' : ''; ?>>$$$$ Très cher</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="descriptif">Description *</label>
                        <textarea class="form-textarea" id="descriptif" name="descriptif" required><?php echo htmlspecialchars($resto['descriptif']); ?></textarea>
                    </div>
                </div>

                <!-- ADRESSE -->
                <div class="edit-card" style="margin-top: 30px;">
                    <h2 class="section-title">📍 Adresse</h2>
                    
                    <div class="form-group">
                        <label class="form-label" for="adresse">Adresse complète *</label>
                        <input type="text" class="form-input" id="adresse" name="adresse" value="<?php echo htmlspecialchars($resto['adresse']); ?>" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="codePostal">Code postal *</label>
                            <input type="text" class="form-input" id="codePostal" name="codePostal" value="<?php echo htmlspecialchars($resto['codePostal']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="ville">Ville *</label>
                            <input type="text" class="form-input" id="ville" name="ville" value="<?php echo htmlspecialchars($resto['ville']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="gps">Coordonnées GPS</label>
                        <input type="text" class="form-input" id="gps" name="gps" value="<?php echo htmlspecialchars($resto['gps']); ?>" placeholder="48.8566,2.3522">
                    </div>
                </div>

                <!-- CONTACT -->
                <div class="edit-card" style="margin-top: 30px;">
                    <h2 class="section-title">📞 Contact</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="phone">Téléphone *</label>
                            <input type="tel" class="form-input" id="phone" name="phone" value="<?php echo htmlspecialchars($resto['phone']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="web">Site web</label>
                            <input type="url" class="form-input" id="web" name="web" value="<?php echo htmlspecialchars($resto['web']); ?>" placeholder="https://">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="owner">Propriétaire</label>
                        <input type="text" class="form-input" id="owner" name="owner" value="<?php echo htmlspecialchars($resto['owner']); ?>">
                    </div>
                </div>

                <!-- MISE EN AVANT -->
                <div class="edit-card" style="margin-top: 30px;">
                    <h2 class="section-title">⭐ Options</h2>
                    
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="mea" name="mea" value="1" <?php echo $resto['mea'] == 1 ? 'checked' : ''; ?>>
                        <label for="mea">Mettre ce restaurant en avant sur la page d'accueil</label>
                    </div>

                    <div class="note-box">
                        <strong>Note :</strong> Les restaurants mis en avant apparaissent en priorité sur la page d'accueil du site.
                    </div>
                </div>
            </div>

            <!-- SIDEBAR -->
            <div>
                <div class="edit-card sidebar-card">
                    <h2 class="section-title">📸 Aperçu</h2>
                    
                    <div class="preview-image">
                        <?php if ($photos && !empty($photos['main']) && file_exists($photos['main'])): ?>
                            <img src="<?php echo htmlspecialchars($photos['main']); ?>" alt="Photo principale">
                        <?php else: ?>
                            🍽️
                        <?php endif; ?>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Note actuelle</span>
                        <span class="info-value"><?php echo $resto['note'] ? number_format($resto['note'], 1) . '/5' : 'Aucune'; ?></span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Mise en avant</span>
                        <span class="info-value"><?php echo $resto['mea'] ? '⭐ Oui' : 'Non'; ?></span>
                    </div>

                   <!-- ============================================ -->
<!-- PARTIE 3 : HTML À REMPLACER -->
<!-- Dans admin-modifier-resto.php, REMPLACE toute la section horaires -->
<!-- Cherche : <?php if ($horaires): ?> -->
<!-- Et remplace TOUT jusqu'à <?php endif; ?> par ce code : -->
<!-- ============================================ -->

<h3 class="section-title" style="margin-top: 20px; font-size: 16px;">🕒 Horaires d'ouverture</h3>

<?php 
// Si pas d'horaires, créer un tableau par défaut
if (!$horaires) {
    $horaires = [
        'lun_mat' => '0800-1200', 'lun_ap' => '1400-2000',
        'mar_mat' => '0800-1200', 'mar_ap' => '1400-2000',
        'mer_mat' => '0800-1200', 'mer_ap' => '1400-2000',
        'jeu_mat' => '0800-1200', 'jeu_ap' => '1400-2000',
        'ven_mat' => '0800-1200', 'ven_ap' => '1400-2000',
        'sam_mat' => '0800-1200', 'sam_ap' => '1400-2000',
        'dim_mat' => '9999-0000', 'dim_ap' => '9999-0000'
    ];
}

$joursFr = [
    'lun' => 'Lundi',
    'mar' => 'Mardi', 
    'mer' => 'Mercredi',
    'jeu' => 'Jeudi',
    'ven' => 'Vendredi',
    'sam' => 'Samedi',
    'dim' => 'Dimanche'
];

foreach ($joursFr as $jour => $nomJour):
    $matin = parseHoraire($horaires[$jour . '_mat']);
    $aprem = parseHoraire($horaires[$jour . '_ap']);
?>

<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 12px;">
    <div style="font-weight: 600; margin-bottom: 10px; color: #2c3e50;"><?php echo $nomJour; ?></div>
    
    <!-- MATIN -->
    <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 8px;">
        <span style="width: 80px; font-size: 13px; color: #7f8c8d;">☀️ Matin</span>
        <input type="checkbox" 
               id="<?php echo $jour; ?>_ferme_mat" 
               name="<?php echo $jour; ?>_ferme_mat"
               <?php echo $matin['ferme'] ? 'checked' : ''; ?>
               onchange="toggleHoraire('<?php echo $jour; ?>_mat')">
        <label for="<?php echo $jour; ?>_ferme_mat" style="margin-right: 10px; font-size: 13px;">Fermé</label>
        
        <div id="<?php echo $jour; ?>_mat_fields" style="display: <?php echo $matin['ferme'] ? 'none' : 'flex'; ?>; gap: 5px; align-items: center;">
            <input type="time" 
                   name="<?php echo $jour; ?>_debut_mat" 
                   value="<?php echo $matin['debut']; ?>"
                   style="padding: 6px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px;">
            <span>→</span>
            <input type="time" 
                   name="<?php echo $jour; ?>_fin_mat" 
                   value="<?php echo $matin['fin']; ?>"
                   style="padding: 6px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px;">
        </div>
    </div>
    
    <!-- APRÈS-MIDI -->
    <div style="display: flex; gap: 10px; align-items: center;">
        <span style="width: 80px; font-size: 13px; color: #7f8c8d;">🌙 Soir</span>
        <input type="checkbox" 
               id="<?php echo $jour; ?>_ferme_ap" 
               name="<?php echo $jour; ?>_ferme_ap"
               <?php echo $aprem['ferme'] ? 'checked' : ''; ?>
               onchange="toggleHoraire('<?php echo $jour; ?>_ap')">
        <label for="<?php echo $jour; ?>_ferme_ap" style="margin-right: 10px; font-size: 13px;">Fermé</label>
        
        <div id="<?php echo $jour; ?>_ap_fields" style="display: <?php echo $aprem['ferme'] ? 'none' : 'flex'; ?>; gap: 5px; align-items: center;">
            <input type="time" 
                   name="<?php echo $jour; ?>_debut_ap" 
                   value="<?php echo $aprem['debut']; ?>"
                   style="padding: 6px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px;">
            <span>→</span>
            <input type="time" 
                   name="<?php echo $jour; ?>_fin_ap" 
                   value="<?php echo $aprem['fin']; ?>"
                   style="padding: 6px; border: 1px solid #ddd; border-radius: 5px; font-size: 13px;">
        </div>
    </div>
</div>

<?php endforeach; ?>

<!-- JAVASCRIPT POUR TOGGLE -->
<script>
function toggleHoraire(id) {
    const checkbox = document.getElementById(id + '_ferme');
    const fields = document.getElementById(id + '_fields');
    fields.style.display = checkbox.checked ? 'none' : 'flex';
}
</script>

                    <button type="submit" class="btn-submit">💾 Enregistrer les modifications</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>