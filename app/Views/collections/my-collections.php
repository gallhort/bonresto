<style>
    .mc-container { max-width: 900px; margin: 0 auto; padding: 32px 20px; }
    .mc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
    .mc-header h1 { font-size: 24px; margin: 0; }
    .mc-btn { padding: 10px 18px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: #00635a; color: #fff; }
    .mc-btn:hover { background: #004d40; }

    .mc-list { display: grid; gap: 12px; }
    .mc-item { display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
    .mc-item-info h3 { margin: 0 0 4px; font-size: 16px; }
    .mc-item-info h3 a { color: #111827; text-decoration: none; }
    .mc-item-info h3 a:hover { color: #00635a; }
    .mc-item-meta { font-size: 12px; color: #6b7280; display: flex; gap: 12px; }
    .mc-item-meta i { margin-right: 3px; }
    .mc-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; }
    .mc-badge-public { background: #d1fae5; color: #065f46; }
    .mc-badge-private { background: #fef3c7; color: #92400e; }
    .mc-empty { text-align: center; padding: 60px; color: #9ca3af; }
</style>

<div class="mc-container">
    <div class="mc-header">
        <h1><i class="fas fa-folder"></i> Mes Collections</h1>
        <button class="mc-btn" onclick="window.location.href='/collections'">
            <i class="fas fa-globe"></i> Explorer
        </button>
    </div>

    <?php if (!empty($collections)): ?>
    <div class="mc-list">
        <?php foreach ($collections as $col): ?>
        <div class="mc-item">
            <div class="mc-item-info">
                <h3>
                    <a href="/collections/<?= htmlspecialchars($col['slug']) ?>"><?= htmlspecialchars($col['title']) ?></a>
                    <span class="mc-badge <?= $col['is_public'] ? 'mc-badge-public' : 'mc-badge-private' ?>">
                        <?= $col['is_public'] ? 'Publique' : 'Privee' ?>
                    </span>
                </h3>
                <div class="mc-item-meta">
                    <span><i class="fas fa-utensils"></i> <?= (int)$col['restaurant_count'] ?> restaurants</span>
                    <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($col['created_at'])) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="mc-empty">
        <i class="fas fa-folder-open" style="font-size:48px;display:block;margin-bottom:12px;opacity:0.3"></i>
        <p>Vous n'avez pas encore de collection.</p>
        <button class="mc-btn" onclick="window.location.href='/collections'" style="margin-top:12px">Decouvrir les collections</button>
    </div>
    <?php endif; ?>
</div>
