<?php
/**
 * Page de revendication d'un restaurant
 */
?>
<div class="claim-page">
    <div class="claim-container">
        <a href="/restaurant/<?= $restaurant['id'] ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour au restaurant
        </a>

        <h1>Revendiquer : <?= htmlspecialchars($restaurant['nom']) ?></h1>
        <p class="subtitle"><?= htmlspecialchars($restaurant['adresse'] ?? '') ?>, <?= htmlspecialchars($restaurant['ville'] ?? '') ?></p>

        <?php if (!empty($existingClaim)): ?>
            <?php if ($existingClaim['status'] === 'approved'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    Ce restaurant a déjà été revendiqué et validé.
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i>
                    Une demande de revendication est déjà en cours de traitement pour ce restaurant.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="claim-info">
                <h3><i class="fas fa-info-circle"></i> Comment ça marche ?</h3>
                <ol>
                    <li>Remplissez le formulaire ci-dessous avec vos informations professionnelles</li>
                    <li>Fournissez une preuve de propriété (photo, facture, registre de commerce...)</li>
                    <li>Notre équipe vérifiera votre demande sous 48h</li>
                    <li>Une fois validé, vous aurez accès au dashboard propriétaire</li>
                </ol>
            </div>

            <form action="/restaurant/<?= $restaurant['id'] ?>/claim" method="POST" enctype="multipart/form-data" class="claim-form">
                <div class="form-group">
                    <label for="email_pro">Email professionnel *</label>
                    <input type="email" id="email_pro" name="email_pro" required placeholder="contact@monrestaurant.dz">
                </div>

                <div class="form-group">
                    <label for="phone">Téléphone du restaurant *</label>
                    <input type="tel" id="phone" name="phone" required placeholder="0555 12 34 56">
                </div>

                <div class="form-group">
                    <label for="message">Message (optionnel)</label>
                    <textarea id="message" name="message" rows="4" placeholder="Décrivez votre rôle (propriétaire, gérant...)"></textarea>
                </div>

                <div class="form-group">
                    <label for="proof">Preuve de propriété (photo, PDF)</label>
                    <input type="file" id="proof" name="proof" accept="image/jpeg,image/png,application/pdf">
                    <small>Formats : JPG, PNG, PDF — Max 5 Mo</small>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Soumettre ma demande
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<style>
.claim-page { max-width: 700px; margin: 40px auto; padding: 0 20px; }
.claim-container { background: white; border-radius: 12px; padding: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
.back-link { color: #00635a; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 20px; font-size: 14px; }
.back-link:hover { text-decoration: underline; }
h1 { font-size: 22px; color: #1f2937; margin-bottom: 4px; }
.subtitle { color: #6b7280; margin-bottom: 24px; }
.alert { padding: 16px 20px; border-radius: 8px; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.claim-info { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin-bottom: 24px; }
.claim-info h3 { font-size: 15px; margin-bottom: 12px; color: #166534; }
.claim-info ol { padding-left: 20px; font-size: 14px; color: #374151; line-height: 1.8; }
.claim-form .form-group { margin-bottom: 20px; }
.claim-form label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; color: #374151; }
.claim-form input[type="email"],
.claim-form input[type="tel"],
.claim-form textarea { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
.claim-form input:focus, .claim-form textarea:focus { outline: none; border-color: #00635a; box-shadow: 0 0 0 3px rgba(0,99,90,0.1); }
.claim-form small { display: block; margin-top: 4px; color: #9ca3af; font-size: 12px; }
.btn-submit { display: inline-flex; align-items: center; gap: 8px; background: #00635a; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; }
.btn-submit:hover { background: #004d44; }
</style>
