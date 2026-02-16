<style>
    .ref-container { max-width: 700px; margin: 0 auto; padding: 32px 20px; }
    .ref-header { margin-bottom: 32px; }
    .ref-header h1 { font-size: 24px; margin: 0 0 6px; }
    .ref-header p { color: #6b7280; font-size: 14px; margin: 0; }

    .ref-card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); margin-bottom: 24px; }
    .ref-card h2 { font-size: 17px; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
    .ref-card h2 i { color: #00635a; }

    .ref-code-box { background: #f0fdf4; border: 2px dashed #00635a; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 16px; }
    .ref-code { font-size: 28px; font-weight: 800; color: #00635a; letter-spacing: 3px; margin-bottom: 10px; }
    .ref-link { font-size: 13px; color: #6b7280; word-break: break-all; }

    .ref-share-btns { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; justify-content: center; }
    .ref-share-btn { padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: transform 0.2s; }
    .ref-share-btn:hover { transform: translateY(-1px); }
    .ref-btn-copy { background: #00635a; color: #fff; }
    .ref-btn-whatsapp { background: #25d366; color: #fff; }
    .ref-btn-share { background: #3b82f6; color: #fff; }

    .ref-how { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-top: 16px; }
    .ref-step { text-align: center; padding: 16px; }
    .ref-step-num { width: 36px; height: 36px; border-radius: 50%; background: #00635a; color: #fff; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 10px; }
    .ref-step-title { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
    .ref-step-desc { font-size: 13px; color: #6b7280; }

    .ref-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
    .ref-stat { text-align: center; padding: 16px; background: #f9fafb; border-radius: 10px; }
    .ref-stat-val { font-size: 24px; font-weight: 800; color: #00635a; }
    .ref-stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }

    .ref-list { margin-top: 16px; }
    .ref-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
    .ref-item:last-child { border-bottom: none; }
    .ref-item-name { font-weight: 600; font-size: 14px; }
    .ref-item-date { font-size: 12px; color: #9ca3af; }
    .ref-badge-pending { background: #fef3c7; color: #92400e; font-size: 11px; padding: 3px 8px; border-radius: 10px; font-weight: 600; }
    .ref-badge-done { background: #d1fae5; color: #065f46; font-size: 11px; padding: 3px 8px; border-radius: 10px; font-weight: 600; }
</style>

<div class="ref-container">
    <div class="ref-header">
        <h1><i class="fas fa-gift"></i> Programme de parrainage</h1>
        <p>Invitez vos amis et gagnez des points ensemble !</p>
    </div>

    <!-- Code & Share -->
    <div class="ref-card">
        <h2><i class="fas fa-share-alt"></i> Votre code parrain</h2>
        <div class="ref-code-box">
            <div class="ref-code" id="refCode"><?= htmlspecialchars($referral_code) ?></div>
            <div class="ref-link" id="refLink"><?= htmlspecialchars(($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? 'lebonresto.dz')) ?>/register?ref=<?= htmlspecialchars($referral_code) ?></div>
        </div>
        <div class="ref-share-btns">
            <button class="ref-share-btn ref-btn-copy" onclick="copyRefLink()"><i class="fas fa-copy"></i> Copier le lien</button>
            <a class="ref-share-btn ref-btn-whatsapp" href="https://wa.me/?text=<?= urlencode('Rejoins LeBonResto avec mon code ' . $referral_code . ' et gagne 200 points bonus ! ' . ($_SERVER['REQUEST_SCHEME'] ?? 'https') . '://' . ($_SERVER['HTTP_HOST'] ?? 'lebonresto.dz') . '/register?ref=' . $referral_code) ?>" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            <button class="ref-share-btn ref-btn-share" onclick="shareRef()"><i class="fas fa-share"></i> Partager</button>
        </div>
    </div>

    <!-- How it works -->
    <div class="ref-card">
        <h2><i class="fas fa-question-circle"></i> Comment ca marche ?</h2>
        <div class="ref-how">
            <div class="ref-step">
                <div class="ref-step-num">1</div>
                <div class="ref-step-title">Partagez votre code</div>
                <div class="ref-step-desc">Envoyez votre lien a vos amis</div>
            </div>
            <div class="ref-step">
                <div class="ref-step-num">2</div>
                <div class="ref-step-title">Ils s'inscrivent</div>
                <div class="ref-step-desc">Votre ami recoit 50 points bonus</div>
            </div>
            <div class="ref-step">
                <div class="ref-step-num">3</div>
                <div class="ref-step-title">Premier avis poste</div>
                <div class="ref-step-desc">Vous gagnez 100 points !</div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="ref-card">
        <h2><i class="fas fa-chart-bar"></i> Vos statistiques</h2>
        <div class="ref-stats">
            <div class="ref-stat">
                <div class="ref-stat-val"><?= (int)($stats['total_referrals'] ?? 0) ?></div>
                <div class="ref-stat-label">Amis invites</div>
            </div>
            <div class="ref-stat">
                <div class="ref-stat-val"><?= (int)($stats['completed'] ?? 0) ?></div>
                <div class="ref-stat-label">Parrainages valides</div>
            </div>
            <div class="ref-stat">
                <div class="ref-stat-val"><?= (int)($stats['total_points'] ?? 0) ?></div>
                <div class="ref-stat-label">Points gagnes</div>
            </div>
        </div>

        <?php if (!empty($referrals)): ?>
        <div class="ref-list">
            <?php foreach ($referrals as $ref): ?>
            <div class="ref-item">
                <div>
                    <div class="ref-item-name"><?= htmlspecialchars($ref['prenom'] . ' ' . substr($ref['user_nom'], 0, 1) . '.') ?></div>
                    <div class="ref-item-date"><?= date('d/m/Y', strtotime($ref['created_at'])) ?></div>
                </div>
                <?php if ($ref['status'] === 'completed'): ?>
                    <span class="ref-badge-done"><i class="fas fa-check"></i> +<?= $ref['points_awarded'] ?> pts</span>
                <?php else: ?>
                    <span class="ref-badge-pending">En attente du 1er avis</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="text-align:center;color:#9ca3af;padding:20px 0">Aucun parrainage pour le moment. Partagez votre code !</p>
        <?php endif; ?>
    </div>
</div>

<script>
function copyRefLink() {
    const link = document.getElementById('refLink').textContent;
    navigator.clipboard.writeText(link).then(() => {
        const btn = document.querySelector('.ref-btn-copy');
        btn.innerHTML = '<i class="fas fa-check"></i> Copie !';
        setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i> Copier le lien', 2000);
    });
}
function shareRef() {
    if (navigator.share) {
        navigator.share({
            title: 'Rejoins LeBonResto !',
            text: 'Utilise mon code <?= htmlspecialchars($referral_code) ?> pour gagner 200 points bonus !',
            url: document.getElementById('refLink').textContent
        });
    } else {
        copyRefLink();
    }
}
</script>
