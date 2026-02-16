<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background: #f5f5f5; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.1);">
                    
                    <!-- Header Admin -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;">
                                🛡️ Modération LeBonResto
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Badge alerte -->
                    <tr>
                        <td style="padding: 0 30px; margin-top: -30px;" align="center">
                            <div style="background: white; border-radius: 50%; width: 80px; height: 80px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                                <span style="font-size: 48px;">⏳</span>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Contenu -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <h2 style="margin: 0 0 20px 0; color: #1a1a1a; font-size: 24px; font-weight: 700; text-align: center;">
                                Nouvel avis à modérer
                            </h2>
                            
                            <p style="margin: 0 0 25px 0; color: #666; font-size: 16px; line-height: 1.6;">
                                Un nouvel avis nécessite une vérification manuelle.
                            </p>
                            
                            <!-- Info restaurant -->
                            <div style="background: #f9f9f9; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 20px; margin: 25px 0;">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="padding: 5px 0; color: #666; font-size: 14px; width: 140px;"><strong>Restaurant:</strong></td>
                                        <td style="padding: 5px 0; color: #1a1a1a; font-size: 14px;"><?= htmlspecialchars($restaurant_name) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #666; font-size: 14px;"><strong>Auteur:</strong></td>
                                        <td style="padding: 5px 0; color: #1a1a1a; font-size: 14px;"><?= htmlspecialchars($user_name) ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #666; font-size: 14px;"><strong>Note:</strong></td>
                                        <td style="padding: 5px 0; font-size: 14px;">
                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                <span style="color: <?= $i < floor($review_rating) ? '#f59e0b' : '#e0e0e0' ?>;">★</span>
                                            <?php endfor; ?>
                                            <span style="color: #666; margin-left: 5px;"><?= number_format($review_rating, 1) ?>/5</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px 0; color: #666; font-size: 14px;"><strong>Score IA:</strong></td>
                                        <td style="padding: 5px 0; font-size: 14px;">
                                            <span style="<?= $spam_score >= 80 ? 'color: #10b981;' : ($spam_score >= 50 ? 'color: #f59e0b;' : 'color: #ef4444;') ?> font-weight: 600;">
                                                <?= $spam_score ?>/100
                                            </span>
                                            <?php if ($spam_score >= 80): ?>
                                                <span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;">Qualité élevée</span>
                                            <?php elseif ($spam_score >= 50): ?>
                                                <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;">À vérifier</span>
                                            <?php else: ?>
                                                <span style="background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-left: 8px;">Suspect</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Extrait de l'avis -->
                            <div style="background: #f9fafb; border-radius: 8px; padding: 20px; margin: 25px 0; border: 1px solid #e5e7eb;">
                                <h4 style="margin: 0 0 10px 0; color: #1a1a1a; font-size: 15px; font-weight: 600;">
                                    Contenu de l'avis :
                                </h4>
                                <p style="margin: 0; color: #666; font-size: 14px; line-height: 1.6; font-style: italic;">
                                    "<?= htmlspecialchars($review_message) ?>"
                                </p>
                            </div>
                            
                            <!-- CTA Admin -->
                            <div style="text-align: center; margin: 35px 0;">
                                <a href="<?= $admin_url ?>" style="display: inline-block; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; text-decoration: none; padding: 16px 40px; border-radius: 30px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                                    Modérer maintenant
                                </a>
                            </div>
                            
                            <!-- Stats rapides -->
                            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 15px; margin-top: 30px;">
                                <p style="margin: 0; color: #0c4a6e; font-size: 12px; text-align: center;">
                                    <strong>ID Avis:</strong> #<?= $review_id ?> • 
                                    <strong>Requiert attention</strong> si score < 80
                                </p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f9f9f9; padding: 25px 30px; text-align: center; border-top: 1px solid #e0e0e0;">
                            <p style="margin: 0; color: #999; font-size: 11px;">
                                Notification automatique - Dashboard Admin LeBonResto<br>
                                &copy; 2024 LeBonResto
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
