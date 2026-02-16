<style>
    .cs-hero { background: linear-gradient(135deg, #00635a, #004d40); color: #fff; padding: 48px 0; }
    .cs-container { max-width: 1000px; margin: 0 auto; padding: 0 20px; }
    .cs-title { font-size: 28px; margin: 0 0 6px; }
    .cs-desc { opacity: 0.85; margin: 0 0 14px; font-size: 15px; }
    .cs-meta { display: flex; gap: 16px; font-size: 13px; opacity: 0.8; align-items: center; flex-wrap: wrap; }
    .cs-meta i { margin-right: 4px; }

    .cs-actions { display: flex; gap: 10px; margin: 24px auto; max-width: 1000px; padding: 0 20px; }
    .cs-btn { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1px solid #d1d5db; background: #fff; color: #374151; display: flex; align-items: center; gap: 6px; text-decoration: none; }
    .cs-btn:hover { background: #f3f4f6; }
    .cs-btn-primary { background: #00635a; color: #fff; border-color: #00635a; }
    .cs-btn-primary:hover { background: #004d40; }
    .cs-btn-danger { color: #ef4444; border-color: #ef4444; }

    .cs-grid { max-width: 1000px; margin: 0 auto 40px; padding: 0 20px; }
    .cs-item { display: flex; gap: 16px; padding: 16px; background: #fff; border-radius: 12px; margin-bottom: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); align-items: center; transition: transform 0.2s; }
    .cs-item:hover { transform: translateX(4px); }
    .cs-item-img { width: 100px; height: 80px; border-radius: 8px; object-fit: cover; flex-shrink: 0; background: #e5e7eb; }
    .cs-item-body { flex: 1; min-width: 0; }
    .cs-item-name { font-size: 16px; font-weight: 700; margin: 0 0 4px; }
    .cs-item-name a { color: #111827; text-decoration: none; }
    .cs-item-name a:hover { color: #00635a; }
    .cs-item-info { font-size: 13px; color: #6b7280; margin: 0; }
    .cs-item-note { font-size: 12px; color: #9ca3af; font-style: italic; margin-top: 4px; }
    .cs-item-rating { display: flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 700; color: #f59e0b; }
    .cs-item-actions { flex-shrink: 0; }
    .cs-remove-btn { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 16px; padding: 6px; }

    .cs-empty { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .cs-empty i { font-size: 40px; display: block; margin-bottom: 10px; opacity: 0.3; }

    .cs-share-link { background: #f3f4f6; padding: 10px 14px; border-radius: 8px; font-size: 13px; color: #374151; word-break: break-all; display: none; margin: 12px auto; max-width: 1000px; }
    .cs-share-link.visible { display: block; padding: 10px 20px; }
</style>

<div class="cs-hero">
    <div class="cs-container">
        <h1 class="cs-title"><?= htmlspecialchars($collection['title']) ?></h1>
        <?php if (!empty($collection['description'])): ?>
            <p class="cs-desc"><?= htmlspecialchars($collection['description']) ?></p>
        <?php endif; ?>
        <div class="cs-meta">
            <span><i class="fas fa-user"></i> <?= htmlspecialchars($collection['prenom'] . ' ' . $collection['user_nom']) ?></span>
            <span><i class="fas fa-utensils"></i> <?= count($restaurants) ?> restaurants</span>
            <span><i class="fas fa-eye"></i> <?= number_format($collection['views_count']) ?> vues</span>
            <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($collection['created_at'])) ?></span>
        </div>
    </div>
</div>

<div class="cs-actions">
    <button class="cs-btn" onclick="shareCollection()">
        <i class="fas fa-share-alt"></i> Partager
    </button>
    <a href="/user/<?= (int)$collection['author_id'] ?>" class="cs-btn">
        <i class="fas fa-user"></i> Voir le profil
    </a>
    <?php if ($isOwner): ?>
        <button class="cs-btn cs-btn-danger" onclick="deleteCollection(<?= (int)$collection['id'] ?>)">
            <i class="fas fa-trash"></i> Supprimer
        </button>
    <?php endif; ?>
</div>

<div class="cs-share-link" id="shareLink"></div>

<div class="cs-grid">
    <?php if (!empty($restaurants)): ?>
        <?php foreach ($restaurants as $i => $resto): ?>
        <div class="cs-item" id="item-<?= (int)$resto['id'] ?>">
            <?php if (!empty($resto['main_photo'])): ?>
                <img class="cs-item-img" src="/<?= htmlspecialchars($resto['main_photo']) ?>" alt="<?= htmlspecialchars($resto['nom']) ?>">
            <?php else: ?>
                <div class="cs-item-img" style="display:flex;align-items:center;justify-content:center;color:#9ca3af"><i class="fas fa-utensils" style="font-size:24px"></i></div>
            <?php endif; ?>
            <div class="cs-item-body">
                <h3 class="cs-item-name">
                    <a href="/restaurant/<?= $resto['slug'] ?? $resto['id'] ?>"><?= htmlspecialchars($resto['nom']) ?></a>
                </h3>
                <p class="cs-item-info">
                    <?= htmlspecialchars($resto['type_cuisine'] ?? '') ?>
                    <?php if (!empty($resto['ville'])): ?> &middot; <?= htmlspecialchars($resto['ville']) ?><?php endif; ?>
                    <?php if (!empty($resto['price_range'])): ?> &middot; <?= htmlspecialchars($resto['price_range']) ?><?php endif; ?>
                </p>
                <?php if (!empty($resto['note_perso'])): ?>
                    <p class="cs-item-note">"<?= htmlspecialchars($resto['note_perso']) ?>"</p>
                <?php endif; ?>
            </div>
            <div class="cs-item-rating">
                <i class="fas fa-star"></i>
                <?= number_format((float)$resto['note_moyenne'], 1) ?>
                <span style="font-weight:400;color:#9ca3af;font-size:11px">(<?= (int)$resto['nb_avis'] ?>)</span>
            </div>
            <?php if ($isOwner): ?>
            <div class="cs-item-actions">
                <button class="cs-remove-btn" onclick="removeFromCollection(<?= (int)$collection['id'] ?>, <?= (int)$resto['id'] ?>, this)" title="Retirer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="cs-empty">
            <i class="fas fa-folder-open"></i>
            <p>Cette collection est vide pour le moment.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function shareCollection() {
    const url = window.location.href;
    const title = <?= json_encode($collection['title']) ?>;

    if (navigator.share) {
        navigator.share({ title: title, text: 'Decouvrez cette collection sur LeBonResto', url: url });
    } else {
        // Fallback : copier le lien
        navigator.clipboard.writeText(url).then(() => {
            const el = document.getElementById('shareLink');
            el.textContent = 'Lien copie ! ' + url;
            el.classList.add('visible');
            setTimeout(() => el.classList.remove('visible'), 3000);
        });
    }
}

async function removeFromCollection(colId, restoId, btn) {
    if (!confirm('Retirer ce restaurant de la collection ?')) return;
    try {
        const res = await fetch('/api/collections/' + colId + '/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ restaurant_id: restoId })
        });
        const data = await res.json();
        if (data.success) {
            btn.closest('.cs-item').style.opacity = '0';
            setTimeout(() => btn.closest('.cs-item').remove(), 300);
        }
    } catch (e) { alert('Erreur'); }
}

async function deleteCollection(colId) {
    if (!confirm('Supprimer cette collection ? Cette action est irreversible.')) return;
    try {
        const res = await fetch('/api/collections/' + colId + '/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = '/collections';
        }
    } catch (e) { alert('Erreur'); }
}
</script>
