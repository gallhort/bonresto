<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avis approuvé</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.1);">
                    
                    <!-- Header avec gradient -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #34e0a1 0%, #2cc890 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">
                                🍽️ LeBonResto
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Badge succès -->
                    <tr>
                        <td style="padding: 0 30px; margin-top: -30px;" align="center">
                            <div style="background: white; border-radius: 50%; width: 80px; height: 80px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                                <span style="font-size: 48px;">✅</span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Contenu -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #1a1a1a; font-size: 24px; font-weight: 700; text-align: center;">
                                Votre avis a été publié !
                            </h2>
                            
                            <p style="margin: 0 0 25px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Bonjour <strong><?= htmlspecialchars($user_name) ?></strong>,
                            </p>
                            
                            <p style="margin: 0 0 25px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Bonne nouvelle ! Votre avis sur <strong style="color: #34e0a1;"><?= htmlspecialchars($restaurant_name) ?></strong> 
                                a été approuvé et est maintenant visible par tous les utilisateurs.
                            </p>
                            
                            <!-- Bloc avis -->
                            <div style="background: #f9f9f9; border-left: 4px solid #34e0a1; border-radius: 8px; padding: 20px; margin: 25px 0;">
                                <div style="margin-bottom: 12px;">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <span style="color: <?= $i < floor($review_rating) ? '#f59e0b' : '#e0e0e0' ?>; font-size: 18px;">★</span>
                                    <?php endfor; ?>
                                </div>
                                
                                <?php if (!empty($review_title)): ?>
                                <h3 style="margin: 0 0 10px 0; color: #1a1a1a; font-size: 16px; font-weight: 600;">
                                    <?= htmlspecialchars($review_title) ?>
                                </h3>
                                <?php endif; ?>
                                
                                <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.5;">
                                    <?= htmlspecialchars($review_message) ?>
                                </p>
                            </div>
                            
                            <!-- Stats IA -->
                            <div style="background: #e8f5f0; border-radius: 8px; padding: 15px; margin: 25px 0;">
                                <p style="margin: 0; color: #065f46; font-size: 13px;">
                                    <strong>🤖 Analyse IA:</strong> Score de qualité <strong><?= $spam_score ?>/100</strong> 
                                    (Modéré par <?= $moderated_by ?>)
                                </p>
                            </div>
                            
                            <p style="margin: 25px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Merci d'avoir pris le temps de partager votre expérience. 
                                Votre contribution aide d'autres utilisateurs à faire les meilleurs choix !
                            </p>
                            
                            <!-- Bouton CTA -->
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="<?= $restaurant_url ?>" style="display: inline-block; background: linear-gradient(135deg, #34e0a1 0%, #2cc890 100%); color: white; text-decoration: none; padding: 16px 40px; border-radius: 30px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 12px rgba(52, 224, 161, 0.3);">
                                    Voir mon avis
                                </a>
                            </div>
                            
                            <!-- Tips -->
                            <div style="background: #fff8e1; border-left: 3px solid #f59e0b; border-radius: 6px; padding: 15px; margin-top: 30px;">
                                <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.5;">
                                    💡 <strong>Le saviez-vous ?</strong> Les utilisateurs qui laissent des avis détaillés obtiennent 3x plus de votes "utiles" !
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f9f9f9; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0 0 15px 0; color: #999; font-size: 12px;">
                                LeBonResto - Votre guide des meilleurs restaurants en Algérie
                            </p>
                            
                            <div style="margin: 15px 0;">
                                <a href="#" style="color: #34e0a1; text-decoration: none; margin: 0 10px; font-size: 12px;">Facebook</a>
                                <a href="#" style="color: #34e0a1; text-decoration: none; margin: 0 10px; font-size: 12px;">Instagram</a>
                                <a href="#" style="color: #34e0a1; text-decoration: none; margin: 0 10px; font-size: 12px;">Twitter</a>
                            </div>
                            
                            <p style="margin: 15px 0 0 0; color: #999; font-size: 11px;">
                                &copy; 2024 LeBonResto. Tous droits réservés.<br>
                                <a href="#" style="color: #999; text-decoration: underline;">Se désabonner</a>
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
