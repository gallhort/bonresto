<?php
/**
 * PROFIL PUBLIC UTILISATEUR
 * Visible par tous — /user/{id}
 */
$u = $profileUser;
$initiale = strtoupper(substr($u['prenom'] ?? '', 0, 1));
$memberSince = date('F Y', strtotime($u['created_at']));
$badge = $loyaltyInfo['badge'] ?? 'Explorateur';
$points = $loyaltyInfo['points'] ?? 0;
$badgeIcon = $loyaltyInfo['badge_icon'] ?? '🔍';
$badgeColor = $loyaltyInfo['badge_color'] ?? '#6b7280';
?>
<style>
.pub-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
.pub-header { background: white; padding: 32px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.pub-top { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
.pub-avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #34e0a1 0%, #2cc890 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; font-weight: 700; flex-shrink: 0; }
.pub-avatar img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; }
.pub-name { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
.pub-meta { color: #888; font-size: 14px; }
.pub-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-top: 6px; }
.pub-badge-icon { font-size: 15px; }
.pub-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
.pub-stat { text-align: center; }
.pub-stat-num { font-size: 1.5rem; font-weight: 700; color: #34e0a1; }
.pub-stat-label { font-size: 13px; color: #888; }
.pub-reviews { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.pub-reviews h2 { font-size: 1.2rem; margin-bottom: 16px; }
.pub-review-item { padding: 16px 0; border-bottom: 1px solid #f0f0f0; }
.pub-review-item:last-child { border-bottom: none; }
.pub-review-resto { font-weight: 600; color: #333; text-decoration: none; }
.pub-review-resto:hover { color: #34e0a1; }
.pub-review-meta { font-size: 13px; color: #999; margin: 4px 0; }
.pub-review-text { font-size: 14px; color: #555; line-height: 1.5; margin-top: 6px; }
.pub-stars { color: #f59e0b; }
.pub-empty { text-align: center; color: #999; padding: 40px; }
</style>

<div class="pub-container">
    <div class="pub-header">
        <div class="pub-top">
            <div class="pub-avatar">
                <?php if (!empty($u['photo_profil'])): ?>
                    <img src="/<?= htmlspecialchars($u['photo_profil']) ?>" alt="Photo">
                <?php else: ?>
                    <?= $initiale ?>
                <?php endif; ?>
            </div>
            <div>
                <div class="pub-name"><?= htmlspecialchars($u['prenom'] . ' ' . substr($u['nom'], 0, 1) . '.') ?></div>
                <div class="pub-meta">
                    <?php if (!empty($u['ville'])): ?>
                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($u['ville']) ?> &middot;
                    <?php endif; ?>
                    Membre depuis <?= $memberSince ?>
                </div>
                <span class="pub-badge" style="background:<?= htmlspecialchars($badgeColor) ?>15;color:<?= htmlspecialchars($badgeColor) ?>">
                    <span class="pub-badge-icon"><?= $badgeIcon ?></span> <?= htmlspecialchars($badge) ?> &middot; <?= number_format($points) ?> pts
                </span>
                <?php if ($points >= 1200): ?>
                    <span class="pub-badge" style="background:#fef3c7;color:#92400e;margin-left:6px;"><i class="fas fa-award"></i> Top contributeur</span>
                <?php endif; ?>
                <?php if (!empty($userTitles)): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">
                        <?php foreach (array_slice($userTitles, 0, 5) as $title): ?>
                            <span class="pub-badge" style="background:<?= htmlspecialchars($title['title_color']) ?>15;color:<?= htmlspecialchars($title['title_color']) ?>;font-size:12px;">
                                <?= $title['title_icon'] ?> <?= htmlspecialchars($title['title_label']) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="pub-stats">
            <div class="pub-stat">
                <div class="pub-stat-num"><?= (int)($stats['nb_avis'] ?? 0) ?></div>
                <div class="pub-stat-label">Avis</div>
            </div>
            <div class="pub-stat">
                <div class="pub-stat-num"><?= (int)($stats['nb_photos'] ?? 0) ?></div>
                <div class="pub-stat-label">Photos</div>
            </div>
            <div class="pub-stat">
                <div class="pub-stat-num"><?= $totalHelpful ?></div>
                <div class="pub-stat-label">Votes utiles</div>
            </div>
            <div class="pub-stat">
                <div class="pub-stat-num"><?= (int)($stats['nb_restaurants_visites'] ?? 0) ?></div>
                <div class="pub-stat-label">Restaurants</div>
            </div>
        </div>
    </div>

    <div class="pub-reviews">
        <h2>Ses avis (<?= count($reviews) ?>)</h2>

        <?php if (empty($reviews)): ?>
            <div class="pub-empty">Aucun avis pour le moment.</div>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <div class="pub-review-item">
                    <a class="pub-review-resto" href="/restaurant/<?= (int)$rev['restaurant_id'] ?>">
                        <?= htmlspecialchars($rev['restaurant_nom']) ?>
                    </a>
                    <span style="color:#999; font-size:13px;">
                        &middot; <?= htmlspecialchars($rev['restaurant_ville'] ?? '') ?>
                        <?php if (!empty($rev['restaurant_type'])): ?>
                            &middot; <?= htmlspecialchars($rev['restaurant_type']) ?>
                        <?php endif; ?>
                    </span>
                    <div class="pub-review-meta">
                        <span class="pub-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <?php if ($i <= round($rev['note_globale'])): ?>
                                    <i class="fas fa-star"></i>
                                <?php else: ?>
                                    <i class="far fa-star"></i>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </span>
                        <?= number_format($rev['note_globale'], 1) ?>/5
                        &middot; <?= date('d/m/Y', strtotime($rev['created_at'])) ?>
                    </div>
                    <?php if (!empty($rev['title'])): ?>
                        <div style="font-weight:600; margin-top:4px;"><?= htmlspecialchars($rev['title']) ?></div>
                    <?php endif; ?>
                    <div class="pub-review-text">
                        <?= nl2br(htmlspecialchars(mb_substr($rev['message'], 0, 300))) ?>
                        <?php if (mb_strlen($rev['message']) > 300): ?>...<?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
