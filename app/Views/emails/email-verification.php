<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérifiez votre email - LeBonResto</title>
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
                                Vérifiez votre email
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Bonjour <strong><?= htmlspecialchars($prenom) ?></strong>,
                            </p>

                            <p style="color: #333333; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Merci de vous être inscrit sur <strong>LeBonResto</strong>. Pour activer votre compte et commencer à partager vos avis, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous.
                            </p>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?= htmlspecialchars($verifyUrl) ?>"
                                   style="display: inline-block; background: linear-gradient(135deg, #00635a, #00897b); color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 8px; font-size: 16px; font-weight: 700;">
                                    Vérifier mon email
                                </a>
                            </div>

                            <p style="color: #888888; font-size: 13px; line-height: 1.6; margin: 20px 0 0 0;">
                                Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :<br>
                                <a href="<?= htmlspecialchars($verifyUrl) ?>" style="color: #00635a; word-break: break-all;"><?= htmlspecialchars($verifyUrl) ?></a>
                            </p>

                            <p style="color: #888888; font-size: 13px; margin-top: 20px;">
                                Ce lien expire dans 24 heures. Si vous n'avez pas créé de compte sur LeBonResto, ignorez cet email.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; font-size: 12px; margin: 0;">
                                &copy; <?= date('Y') ?> LeBonResto - Les meilleurs restaurants d'Algérie
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
