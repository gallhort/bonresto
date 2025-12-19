<?php
session_start();

// Si pas de message de succès, rediriger
if (!isset($_SESSION['success_message'])) {
    header('Location: index.php');
    exit;
}

$message = $_SESSION['success_message'];
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription réussie - Le Bon Resto</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #00AA6C 0%, #00D084 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .confirmation-card {
            background: white;
            border-radius: 24px;
            padding: 60px 48px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 32px;
            background: linear-gradient(135deg, #00AA6C 0%, #00D084 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.5s ease-out 0.3s backwards;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon svg {
            width: 60px;
            height: 60px;
            color: white;
        }

        .checkmark {
            animation: drawCheck 0.5s ease-out 0.5s backwards;
        }

        @keyframes drawCheck {
            from {
                stroke-dasharray: 1000;
                stroke-dashoffset: 1000;
            }
            to {
                stroke-dasharray: 1000;
                stroke-dashoffset: 0;
            }
        }

        h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1A1A1A;
            margin-bottom: 16px;
        }

        .message {
            font-size: 18px;
            color: #6B7280;
            line-height: 1.7;
            margin-bottom: 40px;
        }

        .info-box {
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            text-align: left;
        }

        .info-box h3 {
            font-size: 16px;
            font-weight: 700;
            color: #1A1A1A;
            margin-bottom: 12px;
        }

        .info-box ul {
            list-style: none;
            padding: 0;
        }

        .info-box li {
            font-size: 14px;
            color: #6B7280;
            margin-bottom: 8px;
            padding-left: 24px;
            position: relative;
        }

        .info-box li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #00AA6C;
            font-weight: 700;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 16px 32px;
            background: linear-gradient(135deg, #00AA6C 0%, #00D084 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 170, 108, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 170, 108, 0.4);
        }

        .contact-info {
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid #F3F4F6;
            font-size: 14px;
            color: #6B7280;
        }

        .contact-info a {
            color: #00AA6C;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="confirmation-card">
        <div class="success-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path class="checkmark" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1>🎉 Inscription réussie !</h1>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>

        <div class="info-box">
            <h3>Prochaines étapes :</h3>
            <ul>
                <li>Notre équipe vérifie vos informations</li>
                <li>Vous recevrez une confirmation par téléphone</li>
                <li>Votre restaurant sera publié sous 24-48h</li>
                <li>Vous pourrez ensuite gérer votre profil</li>
            </ul>
        </div>

        <a href="index.php" class="btn">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Retour à l'accueil
        </a>

        <div class="contact-info">
            Une question ? Contactez-nous au <a href="tel:+213567883631">07 67 88 36 31</a>
        </div>
    </div>

</body>
</html>