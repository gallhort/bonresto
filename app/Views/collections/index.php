<style>
    .col-hero { background: linear-gradient(135deg, #00635a 0%, #004d40 100%); color: #fff; padding: 48px 0; }
    .col-hero h1 { font-size: 28px; margin: 0 0 8px; }
    .col-hero p { opacity: 0.8; margin: 0; }
    .col-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    .col-toolbar { display: flex; justify-content: space-between; align-items: center; margin: 24px auto; max-width: 1200px; padding: 0 20px; flex-wrap: wrap; gap: 12px; }
    .col-sort { display: flex; gap: 8px; }
    .col-sort a { padding: 6px 14px; border-radius: 20px; font-size: 13px; text-decoration: none; background: #f3f4f6; color: #374151; transition: all 0.2s; }
    .col-sort a.active { background: #00635a; color: #fff; }
    .btn-create-col { background: #00635a; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .btn-create-col:hover { background: #004d40; }
    .col-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto 40px; padding: 0 20px; }
    .col-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.08); transition: transform 0.2s, box-shadow 0.2s; text-decoration: none; color: inherit; display: block; }
    .col-card:hover { transform: translateY(-3px); box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
    .col-card-img { height: 160px; background: linear-gradient(135deg, #e2e8f0, #cbd5e1); display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .col-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .col-card-img .no-img { font-size: 40px; opacity: 0.3; }
    .col-card-body { padding: 16px; }
    .col-card-title { font-size: 16px; font-weight: 700; margin: 0 0 6px; color: #111827; }
    .col-card-desc { font-size: 13px; color: #6b7280; margin: 0 0 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .col-card-meta { display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #9ca3af; }
    .col-card-author { display: flex; align-items: center; gap: 6px; }
    .col-card-author img { width: 22px; height: 22px; border-radius: 50%; object-fit: cover; }

    /* Create modal */
    .col-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .col-modal-overlay.active { display: flex; }
    .col-modal { background: #fff; border-radius: 12px; padding: 28px; width: 90%; max-width: 440px; }
    .col-modal h2 { margin: 0 0 18px; font-size: 18px; }
    .col-modal label { display: block; font-size: 13px; font-weight: 600; margin: 0 0 4px; color: #374151; }
    .col-modal input, .col-modal textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; margin-bottom: 14px; box-sizing: border-box; }
    .col-modal textarea { height: 80px; resize: vertical; }
    .col-modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
    .col-modal-actions button { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; }
    .btn-cancel { background: #f3f4f6; color: #374151; }
    .btn-submit { background: #00635a; color: #fff; }

    .col-empty { text-align: center; padding: 60px 20px; color: #9ca3af; }
    .col-empty i { font-size: 48px; margin-bottom: 12px; display: block; opacity: 0.3; }

    /* Share button */
    .col-share { display: flex; align-items: center; gap: 4px; font-size: 12px; color: #6b7280; cursor: pointer; }
    .col-share:hover { color: #00635a; }
</style>

<div class="col-hero">
    <div class="col-container">
        <h1><i class="fas fa-layer-group"></i> Collections de restaurants</h1>
        <p>Decouvrez les listes de restaurants creees par la communaute</p>
    </div>
</div>

<div class="col-toolbar">
    <div class="col-sort">
        <a href="/collections?sort=popular" class="<?= ($sort ?? '') === 'popular' || empty($sort) ? 'active' : '' ?>">Populaires</a>
        <a href="/collections?sort=recent" class="<?= ($sort ?? '') === 'recent' ? 'active' : '' ?>">Recentes</a>
        <a href="/collections?sort=name" class="<?= ($sort ?? '') === 'name' ? 'active' : '' ?>">A-Z</a>
    </div>
    <?php if (isset($_SESSION['user'])): ?>
        <button class="btn-create-col" onclick="document.getElementById('createColModal').classList.add('active')">
            <i class="fas fa-plus"></i> Creer une collection
        </button>
    <?php endif; ?>
</div>

<?php if (!empty($collections)): ?>
<div class="col-grid">
    <?php foreach ($collections as $col): ?>
    <a href="/collections/<?= htmlspecialchars($col['slug']) ?>" class="col-card">
        <div class="col-card-img">
            <?php if (!empty($col['first_photo'])): ?>
                <img src="/<?= htmlspecialchars($col['first_photo']) ?>" alt="<?= htmlspecialchars($col['title']) ?>">
            <?php else: ?>
                <span class="no-img"><i class="fas fa-images"></i></span>
            <?php endif; ?>
        </div>
        <div class="col-card-body">
            <h3 class="col-card-title"><?= htmlspecialchars($col['title']) ?></h3>
            <?php if (!empty($col['description'])): ?>
                <p class="col-card-desc"><?= htmlspecialchars($col['description']) ?></p>
            <?php endif; ?>
            <div class="col-card-meta">
                <div class="col-card-author">
                    <?php if (!empty($col['user_photo'])): ?>
                        <img src="/uploads/avatars/<?= htmlspecialchars($col['user_photo']) ?>" alt="">
                    <?php endif; ?>
                    <span><?= htmlspecialchars($col['prenom'] . ' ' . $col['user_nom']) ?></span>
                </div>
                <span><?= (int)$col['restaurant_count'] ?> restos &middot; <?= (int)$col['views_count'] ?> vues</span>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="col-empty">
    <i class="fas fa-folder-open"></i>
    <p>Aucune collection pour le moment. Soyez le premier a en creer une !</p>
</div>
<?php endif; ?>

<!-- Modal création -->
<div class="col-modal-overlay" id="createColModal">
    <div class="col-modal">
        <h2><i class="fas fa-plus-circle"></i> Nouvelle collection</h2>
        <label for="colTitle">Titre</label>
        <input type="text" id="colTitle" placeholder="Ex: Meilleurs couscous d'Oran" maxlength="150">
        <label for="colDesc">Description (optionnel)</label>
        <textarea id="colDesc" placeholder="Decrivez votre selection..."></textarea>
        <label>
            <input type="checkbox" id="colPublic" checked> Collection publique (visible par tous)
        </label>
        <div class="col-modal-actions" style="margin-top:14px">
            <button class="btn-cancel" onclick="document.getElementById('createColModal').classList.remove('active')">Annuler</button>
            <button class="btn-submit" onclick="createCollection()">Creer</button>
        </div>
    </div>
</div>

<script>
async function createCollection() {
    const title = document.getElementById('colTitle').value.trim();
    const description = document.getElementById('colDesc').value.trim();
    const isPublic = document.getElementById('colPublic').checked ? 1 : 0;

    if (title.length < 3) {
        alert('Le titre doit contenir au moins 3 caracteres');
        return;
    }

    try {
        const res = await fetch('/api/collections', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, description, is_public: isPublic })
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = '/collections/' + data.collection.slug;
        } else {
            alert(data.error || 'Erreur');
        }
    } catch (e) {
        alert('Erreur reseau');
    }
}
</script>
