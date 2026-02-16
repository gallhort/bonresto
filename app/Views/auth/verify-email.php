<?php
/**
 * PAGE VÉRIFICATION EMAIL - Résultat
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Vérification email') ?> - LeBonResto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .verify-card { background: white; border-radius: 16px; padding: 48px 40px; text-align: center; max-width: 480px; width: 100%; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .verify-icon { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 36px; }
        .verify-icon.success { background: #dcfce7; color: #166534; }
        .verify-icon.error { background: #fee2e2; color: #991b1b; }
        .verify-card h1 { font-size: 24px; margin-bottom: 12px; color: #1f2937; }
        .verify-card p { font-size: 15px; color: #6b7280; line-height: 1.6; margin-bottom: 24px; }
        .btn-primary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 32px; background: #00635a; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 15px; transition: all 0.2s; }
        .btn-primary:hover { background: #004d44; transform: translateY(-1px); }
        .logo { font-size: 28px; font-weight: 700; color: #00635a; margin-bottom: 32px; }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="logo">LeBonResto</div>
        <?php if ($success): ?>
            <div class="verify-icon success"><i class="fas fa-check"></i></div>
            <h1>Email verifie !</h1>
            <p>Bienvenue <?= htmlspecialchars($prenom ?? '') ?> ! Votre adresse email a ete verifiee avec succes. Vous pouvez maintenant profiter pleinement de LeBonResto.</p>
            <a href="/login" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
        <?php else: ?>
            <div class="verify-icon error"><i class="fas fa-times"></i></div>
            <h1>Lien invalide</h1>
            <p><?= htmlspecialchars($message ?? 'Ce lien de vérification est invalide ou a expiré.') ?></p>
            <a href="/login" class="btn-primary"><i class="fas fa-arrow-left"></i> Retour connexion</a>
        <?php endif; ?>
    </div>
</body>
</html>
