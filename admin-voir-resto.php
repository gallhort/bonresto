<?php
include_once('admin-protect.php');
include_once('connect.php');

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin-liste-attente.php');
    exit;
}

$nomResto = trim($_GET['id']);

// ============================================
// RÉCUPÉRER LES DONNÉES DU RESTAURANT
// ============================================
$stmt = $conn->prepare("SELECT * FROM addresto WHERE Nom = ? LIMIT 1");
if (!$stmt) {
    die("Erreur préparation requête : " . $conn->error);
}
$stmt->bind_param("s", $nomResto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin-liste-attente.php?error=notfound');
    exit;
}

$resto = $result->fetch_assoc();

// ============================================
// RÉCUPÉRER LES HORAIRES
// ============================================
$stmtHoraires = $conn->prepare("SELECT * FROM horaires WHERE Nom = ? LIMIT 1");
if ($stmtHoraires) {
    $stmtHoraires->bind_param("s", $nomResto);
    $stmtHoraires->execute();
    $resultHoraires = $stmtHoraires->get_result();
    $horaires = $resultHoraires->num_rows > 0 ? $resultHoraires->fetch_assoc() : null;
} else {
    $horaires = null;
}

// Fonction pour formater les horaires
function formatHoraire($matin, $aprem) {
    if ($matin == "9999-0000" && $aprem == "9999-0000") {
        return "Fermé";
    }
    
    $output = "";
    if ($matin != "9999-0000") {
        $output .= str_replace("-", " - ", $matin);
    }
    if ($aprem != "9999-0000") {
        if ($output != "") $output .= " / ";
        $output .= str_replace("-", " - ", $aprem);
    }
    
    return $output ?: "Fermé";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails - <?php echo htmlspecialchars($resto['Nom']); ?></title>
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

        /* ===== HEADER ===== */
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

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px;
        }

        /* ===== GALLERY ===== */
        .gallery-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .main-photo {
            width: 100%;
            height: 400px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 80px;
        }

        .main-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnails {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0;
        }

        .thumbnail {
            height: 150px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #bdc3c7;
            border-right: 2px solid white;
        }

        .thumbnail:last-child {
            border-right: none;
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ===== INFO SECTION ===== */
        .info-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .section-header h2 {
            font-size: 22px;
            color: #2c3e50;
        }

        .section-icon {
            font-size: 28px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .info-label {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .info-value {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 600;
        }

        .description-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            line-height: 1.6;
        }

        /* ===== OPTIONS ===== */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-weight: 500;
        }

        .option-item.active {
            background: #d4edda;
            color: #155724;
            border-left: 3px solid #28a745;
        }

        .option-item.inactive {
            background: #f8d7da;
            color: #721c24;
            border-left: 3px solid #dc3545;
            opacity: 0.6;
        }

        /* ===== HORAIRES ===== */
        .horaires-table {
            width: 100%;
            border-collapse: collapse;
        }

        .horaires-table th,
        .horaires-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .horaires-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .horaires-table tr:hover {
            background: #f8f9fa;
        }

        .status-open {
            color: #27ae60;
            font-weight: 600;
        }

        .status-closed {
            color: #e74c3c;
            font-weight: 600;
        }

        /* ===== ACTIONS ===== */
        .actions-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-validate {
            background: #27ae60;
            color: white;
        }

        .btn-validate:hover {
            background: #229954;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(39,174,96,0.4);
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn-reject:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(231,76,60,0.4);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }

            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .main-photo {
                height: 250px;
            }

            .thumbnails {
                grid-template-columns: repeat(3, 1fr);
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="admin-header">
        <div class="header-left">
            <h1>👁️ Détails du Restaurant</h1>
            <p><?php echo htmlspecialchars($resto['Nom']); ?></p>
        </div>
        <a href="admin-liste-attente.php" class="btn-back">← Retour à la Liste</a>
    </div>

    <!-- CONTAINER -->
    <div class="container">
        
        <!-- GALERIE PHOTOS -->
        <div class="gallery-section">
            <div class="main-photo">
                <?php if (!empty($resto['main']) && file_exists($resto['main'])): ?>
                    <img src="<?php echo htmlspecialchars($resto['main']); ?>" alt="Photo principale">
                <?php else: ?>
                    🍽️
                <?php endif; ?>
            </div>
            <div class="thumbnails">
                <?php 
                $slides = ['slide1', 'slide2', 'slide3'];
                foreach ($slides as $slide): 
                ?>
                    <div class="thumbnail">
                        <?php if (!empty($resto[$slide]) && file_exists($resto[$slide])): ?>
                            <img src="<?php echo htmlspecialchars($resto[$slide]); ?>" alt="Photo <?php echo $slide; ?>">
                        <?php else: ?>
                            📷
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- INFORMATIONS GÉNÉRALES -->
        <div class="info-section">
            <div class="section-header">
                <span class="section-icon">ℹ️</span>
                <h2>Informations Générales</h2>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nom du Restaurant</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['Nom']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Type de Cuisine</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['Type']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Propriétaire</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['owner'] ?: 'Non renseigné'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Adresse</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['adresse']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Code Postal</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['codePostal']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ville</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['ville']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Téléphone</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['phone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Site Web</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['web']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fourchette de Prix</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['pricerange']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Coordonnées GPS</div>
                    <div class="info-value"><?php echo htmlspecialchars($resto['gps'] ?: 'Non renseigné'); ?></div>
                </div>
            </div>
        </div>

        <!-- DESCRIPTION -->
        <div class="info-section">
            <div class="section-header">
                <span class="section-icon">📝</span>
                <h2>Description</h2>
            </div>
            <div class="description-box">
                <?php echo nl2br(htmlspecialchars($resto['descriptif'])); ?>
            </div>
        </div>

        <!-- OPTIONS / SERVICES -->
        <div class="info-section">
            <div class="section-header">
                <span class="section-icon">⭐</span>
                <h2>Services et Options</h2>
            </div>
            <div class="options-grid">
                <div class="option-item <?php echo $resto['baby'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['baby'] ? '✅' : '❌'; ?> Espace Bébé
                </div>
                <div class="option-item <?php echo $resto['gamezone'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['gamezone'] ? '✅' : '❌'; ?> Zone de Jeux
                </div>
                <div class="option-item <?php echo $resto['handi'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['handi'] ? '✅' : '❌'; ?> Accès Handicapé
                </div>
                <div class="option-item <?php echo $resto['parking'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['parking'] ? '✅' : '❌'; ?> Parking
                </div>
                <div class="option-item <?php echo $resto['priere'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['priere'] ? '✅' : '❌'; ?> Salle de Prière
                </div>
                <div class="option-item <?php echo $resto['private'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['private'] ? '✅' : '❌'; ?> Salon Privé
                </div>
                <div class="option-item <?php echo $resto['voiturier'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['voiturier'] ? '✅' : '❌'; ?> Voiturier
                </div>
                <div class="option-item <?php echo $resto['wifi'] ? 'active' : 'inactive'; ?>">
                    <?php echo $resto['wifi'] ? '✅' : '❌'; ?> WiFi
                </div>
            </div>
        </div>

        <!-- HORAIRES -->
        <?php if ($horaires): ?>
        <div class="info-section">
            <div class="section-header">
                <span class="section-icon">🕒</span>
                <h2>Horaires d'Ouverture</h2>
            </div>
            <table class="horaires-table">
                <thead>
                    <tr>
                        <th>Jour</th>
                        <th>Horaires</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Lundi</strong></td>
                        <td class="<?php echo ($horaires['lun_mat'] == '9999-0000' && $horaires['lun_ap'] == '9999-0000') ? 'status-closed' : 'status-open'; ?>">
                            <?php echo formatHoraire($horaires['lun_mat'], $horaires['lun_ap']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Mardi</strong></td>
                        <td class="<?php echo ($horaires['mar_mat'] == '9999-0000' && $horaires['mar_ap'] == '9999-0000') ? 'status-closed' : 'status-open'; ?>">
                            <?php echo formatHoraire($horaires['mar_mat'], $horaires['mar_ap']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Mercredi</strong></td>
                        <td class="<?php echo ($horaires['mer_mat'] == '9999-0000' && $horaires['mer_ap'] == '9999-0000') ? 'status-closed' : 'status-open'; ?>">
                            <?php echo formatHoraire($horaires['mer_mat'], $horaires['mer_ap']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Jeudi</strong></td>
                        <td class="<?php echo ($horaires['jeu_mat'] == '9999-0000' && $horaires['jeu_ap'] == '9999-0000') ? 'status-closed' : 'status-open'; ?>">
                            <?php echo formatHoraire($horaires['jeu_mat'], $horaires['jeu_ap']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Vendredi</strong></td>
                        <td class="<?php echo ($horaires['ven_mat'] == '9999-0000' && $horaires['ven_ap'] == '9999-0000') ? 'status-closed' : 'status-open'; ?>">
                            <?php echo formatHoraire($horaires['ven_mat'], $horaires['ven_ap']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Samedi</strong></td>
                        <td class="<?php echo ($horaires['sam_mat'] == '9999-0000' && $horaires['sam_ap'] == '9999-0000') ? 'status-closed' : 'status-open'; ?>">
                            <?php echo formatHoraire($horaires['sam_mat'], $horaires['sam_ap']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Dimanche</strong></td>
                        <td class="<?php echo ($horaires['dim_mat'] == '9999-0000' && $horaires['dim_ap'] == '9999-0000') ? 'status-closed' : 'status-open'; ?>">
                            <?php echo formatHoraire($horaires['dim_mat'], $horaires['dim_ap']); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- ACTIONS -->
        <div class="actions-section">
            <h2 style="margin-bottom: 20px;">Que souhaitez-vous faire ?</h2>
            <div class="action-buttons">
                <a href="admin-valider.php?nom=<?php echo urlencode($resto['Nom']); ?>" 
                   class="btn btn-validate"
                   onclick="return confirm('Êtes-vous sûr de vouloir valider ce restaurant ?')">
                    ✅ Valider le Restaurant
                </a>
                <a href="admin-rejeter.php?nom=<?php echo urlencode($resto['Nom']); ?>" 
                   class="btn btn-reject">
                    ❌ Rejeter le Restaurant
                </a>
                <a href="admin-liste-attente.php" class="btn btn-secondary">
                    ← Retour à la Liste
                </a>
            </div>
        </div>

    </div>
</body>
</html>