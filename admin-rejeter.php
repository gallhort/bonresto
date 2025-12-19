<?php
include_once('admin-protect.php');
include_once('connect.php');

// Vérifier que le nom du restaurant est fourni
if (!isset($_GET['nom']) || empty($_GET['nom'])) {
    header('Location: admin-liste-attente.php');
    exit;
}

$nomResto = trim($_GET['nom']);

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raison = trim($_POST['raison'] ?? '');
    
    if (empty($raison)) {
        $error = "Veuillez indiquer une raison de rejet";
    } else {
        // Récupérer les infos du restaurant avant suppression
        $stmt = $conn->prepare("SELECT * FROM addresto WHERE Nom = ? LIMIT 1");
        $stmt->bind_param("s", $nomResto);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $resto = $result->fetch_assoc();
            
            // Supprimer les horaires
            $stmtDeleteHoraires = $conn->prepare("DELETE FROM horaires WHERE Nom = ?");
            if ($stmtDeleteHoraires) {
                $stmtDeleteHoraires->bind_param("s", $nomResto);
                $stmtDeleteHoraires->execute();
            }
            
            // Supprimer de addresto
            $stmtDelete = $conn->prepare("DELETE FROM addresto WHERE Nom = ?");
            $stmtDelete->bind_param("s", $nomResto);
            $stmtDelete->execute();
            
            // Supprimer les photos du serveur
            $photos = [$resto['main'], $resto['slide1'], $resto['slide2'], $resto['slide3']];
            foreach ($photos as $photo) {
                if (!empty($photo) && file_exists($photo)) {
                    unlink($photo);
                }
            }
            
            // Envoyer un email de notification
            $to = "sourtirane@yahoo.fr";
            $subject = "❌ Votre demande d'inscription a été refusée";
            $message = "
Bonjour,

Nous avons examiné votre demande d'inscription pour le restaurant '{$resto['Nom']}'.

Malheureusement, nous ne pouvons pas valider votre demande pour la raison suivante :

{$raison}

Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir plus d'informations, n'hésitez pas à nous contacter.

Détails de votre demande :
- Nom : {$resto['Nom']}
- Type : {$resto['Type']}
- Adresse : {$resto['adresse']}, {$resto['codePostal']} {$resto['ville']}

Cordialement,
L'équipe de gestion
";
            
            $headers = "From: sourtirane@yahoo.fr\r\n";
            $headers .= "Reply-To: sourtirane@yahoo.fr\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            mail($to, $subject, $message, $headers);
            
            // Redirection avec succès
            header('Location: admin-liste-attente.php?success=rejected&nom=' . urlencode($nomResto));
            exit;
        }
    }
}

// Récupérer les infos du restaurant pour l'affichage
$stmt = $conn->prepare("SELECT Nom, Type, ville FROM addresto WHERE Nom = ? LIMIT 1");
$stmt->bind_param("s", $nomResto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin-liste-attente.php?error=notfound');
    exit;
}

$resto = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejeter le Restaurant - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reject-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }

        .reject-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .reject-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .reject-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }

        .reject-form {
            padding: 40px;
        }

        .resto-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #e74c3c;
        }

        .resto-info h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .resto-info p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #2c3e50;
            font-weight: 600;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            resize: vertical;
            min-height: 150px;
            transition: border-color 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: #e74c3c;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
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

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }

        @media (max-width: 768px) {
            .reject-form {
                padding: 30px 20px;
            }

            .buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="reject-container">
        <div class="reject-header">
            <div class="reject-icon">⚠️</div>
            <h1>Rejeter le Restaurant</h1>
            <p>Cette action est irréversible</p>
        </div>

        <div class="reject-form">
            <?php if (isset($error)): ?>
                <div class="error-message">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="resto-info">
                <h3><?php echo htmlspecialchars($resto['Nom']); ?></h3>
                <p><?php echo htmlspecialchars($resto['Type']); ?> • <?php echo htmlspecialchars($resto['ville']); ?></p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="raison">Raison du rejet *</label>
                    <textarea id="raison" 
                              name="raison" 
                              placeholder="Expliquez pourquoi vous rejetez cette demande (informations incomplètes, photos non conformes, doublon, etc.)"
                              required></textarea>
                </div>

                <div class="buttons">
                    <button type="submit" class="btn btn-reject">❌ Confirmer le Rejet</button>
                    <a href="admin-liste-attente.php" class="btn btn-cancel">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>