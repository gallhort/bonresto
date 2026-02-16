<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">
                                🍽️ LeBonResto
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Badge erreur -->
                    <tr>
                        <td style="padding: 0 30px; margin-top: -30px;" align="center">
                            <div style="background: white; border-radius: 50%; width: 80px; height: 80px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                                <span style="font-size: 48px;">❌</span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Contenu -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #1a1a1a; font-size: 24px; font-weight: 700; text-align: center;">
                                Avis non publié
                            </h2>
                            
                            <p style="margin: 0 0 25px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Bonjour <strong><?= htmlspecialchars($user_name) ?></strong>,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Malheureusement, votre avis sur <strong><?= htmlspecialchars($restaurant_name) ?></strong> 
                                n'a pas pu être publié car il ne respecte pas nos critères de qualité.
                            </p>
                            
                            <!-- Score IA -->
                            <div style="background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; padding: 20px; margin: 25px 0;">
                                <p style="margin: 0 0 15px 0; color: #991b1b; font-size: 14px;">
                                    <strong>🤖 Score de qualité:</strong> <?= $spam_score ?>/100 
                                    <span style="color: #dc2626;">(Seuil requis: 50/100)</span>
                                </p>
                                
                                <p style="margin: 0; color: #7f1d1d; font-size: 13px;">
                                    Votre avis a été analysé automatiquement par notre système de modération IA.
                                </p>
                            </div>
                            
                            <!-- Raisons du rejet -->
                            <h3 style="margin: 25px 0 15px 0; color: #1a1a1a; font-size: 18px; font-weight: 600;">
                                Raisons du rejet :
                            </h3>
                            
                            <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                                <?php if (!empty($reasons)): ?>
                                    <?php foreach ($reasons as $reason): ?>
                                        <div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
                                            <p style="margin: 0 0 5px 0; color: #1a1a1a; font-size: 14px; font-weight: 600;">
                                                <?= $reason['penalty'] > 15 ? '🔴' : '⚠️' ?> 
                                                <?= htmlspecialchars($reason['rule']) ?>
                                            </p>
                                            <p style="margin: 0; color: #666; font-size: 13px;">
                                                <?= htmlspecialchars($reason['detail']) ?>
                                                <span style="color: #ef4444; font-weight: 600;">(-<?= $reason['penalty'] ?> pts)</span>
                                            </p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="margin: 0; color: #666; font-size: 14px;">
                                        Votre avis ne respecte pas nos standards de qualité.
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Conseils -->
                            <div style="background: #fff8e1; border-left: 3px solid #f59e0b; border-radius: 6px; padding: 20px; margin: 25px 0;">
                                <h4 style="margin: 0 0 10px 0; color: #92400e; font-size: 15px; font-weight: 600;">
                                    💡 Conseils pour un avis de qualité :
                                </h4>
                                <ul style="margin: 0; padding-left: 20px; color: #78350f; font-size: 13px; line-height: 1.7;">
                                    <li>Rédigez un texte d'au moins 20 caractères</li>
                                    <li>Évitez les majuscules excessives</li>
                                    <li>Soyez spécifique et détaillé</li>
                                    <li>Restez respectueux et constructif</li>
                                    <li>Évitez les liens externes</li>
                                </ul>
                            </div>
                            
                            <p style="margin: 25px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Vous pouvez soumettre un nouvel avis en respectant ces critères. 
                                N'hésitez pas à nous contacter si vous pensez qu'il s'agit d'une erreur.
                            </p>
                            
                            <!-- Boutons CTA -->
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="<?= $guidelines_url ?>" style="display: inline-block; background: #f59e0b; color: white; text-decoration: none; padding: 14px 30px; border-radius: 30px; font-weight: 600; font-size: 15px; margin-right: 10px;">
                                    Lire le guide
                                </a>
                                <a href="<?= $support_url ?>" style="display: inline-block; background: white; color: #34e0a1; border: 2px solid #34e0a1; text-decoration: none; padding: 12px 28px; border-radius: 30px; font-weight: 600; font-size: 15px;">
                                    Contacter le support
                                </a>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f9f9f9; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 10px 0; color: #999; font-size: 12px;">
                                LeBonResto - Votre guide des meilleurs restaurants en Algérie
                            </p>
                            <p style="margin: 0; color: #999; font-size: 11px;">
                                &copy; 2024 LeBonResto. Tous droits réservés.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
