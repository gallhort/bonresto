<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de votre mot de passe</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Arial', sans-serif; background-color: #f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #00635a 0%, #00897b 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">
                                🔐 Réinitialisation de mot de passe
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Bonjour <strong><?= htmlspecialchars($prenom) ?></strong>,
                            </p>
                            
                            <p style="color: #666666; font-size: 15px; line-height: 1.6; margin: 0 0 20px 0;">
                                Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte LeBonResto.
                            </p>
                            
                            <div style="background-color: #fff9e6; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0; border-radius: 5px;">
                                <p style="color: #f59e0b; font-size: 14px; font-weight: 600; margin: 0 0 10px 0;">
                                    ⚠️ Ce lien expire dans 24 heures
                                </p>
                                <p style="color: #666666; font-size: 13px; margin: 0;">
                                    Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.
                                </p>
                            </div>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?= htmlspecialchars($resetUrl) ?>" style="display: inline-block; background-color: #00635a; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 600; font-size: 16px;">
                                    Réinitialiser mon mot de passe
                                </a>
                            </div>
                            
                            <p style="color: #999999; font-size: 13px; line-height: 1.6; margin: 30px 0 0 0;">
                                Si le bouton ne fonctionne pas, copiez-collez ce lien dans votre navigateur :<br>
                                <a href="<?= htmlspecialchars($resetUrl) ?>" style="color: #00635a; word-break: break-all;">
                                    <?= htmlspecialchars($resetUrl) ?>
                                </a>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f8f8; padding: 20px 30px; text-align: center; border-top: 1px solid #eeeeee;">
                            <p style="color: #999999; font-size: 12px; margin: 0;">
                                © <?= date('Y') ?> LeBonResto. Tous droits réservés.<br>
                                Cet email a été envoyé suite à une demande de réinitialisation de mot de passe.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
