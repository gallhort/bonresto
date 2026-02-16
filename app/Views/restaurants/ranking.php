<?php
$awardLabels = [
    'travelers_choice' => ['label' => "Travelers' Choice", 'icon' => 'fa-trophy', 'color' => '#f59e0b'],
    'top_city' => ['label' => 'Top Ville', 'icon' => 'fa-city', 'color' => '#3b82f6'],
    'best_cuisine' => ['label' => 'Meilleure Cuisine', 'icon' => 'fa-utensils', 'color' => '#ef4444'],
    'trending' => ['label' => 'Tendance', 'icon' => 'fa-fire', 'color' => '#f97316'],
    'newcomer' => ['label' => 'Nouveau', 'icon' => 'fa-seedling', 'color' => '#10b981'],
];
$ratingLabels = ['', 'Mauvais', 'Moyen', 'Bon', 'Tres bon', 'Excellent'];
?>

<style>
.rk-page { max-width: 1100px; margin: 0 auto; padding: 24px 20px 60px; }
.rk-hero { background: linear-gradient(135deg, #00635a 0%, #004d40 60%, #00352e 100%); border-radius: 20px; padding: 32px 28px; color: #fff; margin-bottom: 24px; position: relative; overflow: hidden; }
.rk-hero::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.04); border-radius: 50%; }
.rk-hero h1 { font-size: 26px; font-weight: 800; margin: 0 0 6px; }
.rk-hero p { color: rgba(255,255,255,0.7); margin: 0 0 16px; font-size: 14px; }

.rk-filters { display: flex; gap: 8px; flex-wrap: wrap; }
.rk-filter-btn { padding: 7px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s; border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); background: none; }
.rk-filter-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
.rk-filter-btn.active { background: #fff; color: #00635a; border-color: #fff; }

/* Podium */
.rk-podium { display: flex; justify-content: center; align-items: flex-end; gap: 16px; margin-bottom: 32px; }
.rk-podium-item { text-align: center; text-decoration: none; color: inherit; transition: transform 0.2s; }
.rk-podium-item:hover { transform: translateY(-4px); }
.rk-podium-photo { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin: 0 auto 8px; display: block; border: 3px solid #e5e7eb; }
.rk-podium-item:nth-child(2) .rk-podium-photo { width: 100px; height: 100px; border-color: #f59e0b; }
.rk-podium-medal { font-size: 28px; margin-bottom: 4px; }
.rk-podium-item:nth-child(2) .rk-podium-medal { font-size: 36px; }
.rk-podium-name { font-size: 14px; font-weight: 700; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0 auto; }
.rk-podium-ville { font-size: 11px; color: #6b7280; }
.rk-podium-note { font-size: 13px; font-weight: 700; color: #00635a; margin-top: 2px; }
.rk-podium-score { font-size: 11px; color: #9ca3af; }

/* List */
.rk-list { background: #fff; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; }
.rk-row { display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-bottom: 1px solid #f3f4f6; text-decoration: none; color: inherit; transition: background 0.15s; }
.rk-row:last-child { border-bottom: none; }
.rk-row:hover { background: #fafafa; }
.rk-rank { width: 32px; height: 32px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; color: #6b7280; flex-shrink: 0; }
.rk-row:nth-child(-n+3) .rk-rank { background: linear-gradient(135deg, #00635a, #10b981); color: #fff; }
.rk-photo { width: 56px; height: 56px; border-radius: 10px; object-fit: cover; flex-shrink: 0; background: #e5e7eb; }
.rk-info { flex: 1; min-width: 0; }
.rk-name { font-size: 15px; font-weight: 700; margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rk-meta { font-size: 12px; color: #6b7280; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
.rk-award { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; }
.rk-right { text-align: right; flex-shrink: 0; }
.rk-note { font-size: 18px; font-weight: 800; color: #00635a; }
.rk-avis { font-size: 11px; color: #6b7280; }
.rk-score-bar { width: 60px; height: 4px; background: #e5e7eb; border-radius: 2px; margin-top: 4px; overflow: hidden; }
.rk-score-fill { height: 100%; background: linear-gradient(90deg, #00635a, #10b981); border-radius: 2px; }

@media (max-width: 600px) {
    .rk-podium { gap: 8px; }
    .rk-podium-photo { width: 60px; height: 60px; }
    .rk-podium-item:nth-child(2) .rk-podium-photo { width: 76px; height: 76px; }
    .rk-row { padding: 10px 12px; gap: 10px; }
    .rk-photo { width: 44px; height: 44px; }
    .rk-name { font-size: 13px; }
}
</style>

<div class="rk-page">

<?php
$currentCuisine = $currentCuisine ?? null;
$cuisines = $cuisines ?? [];
function rankingUrl($ville, $cuisine) {
    $params = [];
    if ($ville) $params['ville'] = $ville;
    if ($cuisine) $params['cuisine'] = $cuisine;
    return '/classement-restaurants' . ($params ? '?' . http_build_query($params) : '');
}
?>
<div class="rk-hero">
    <h1><i class="fas fa-crown"></i> Classement <?= $currentCuisine ? htmlspecialchars($currentCuisine) : 'des restaurants' ?></h1>
    <p><?= $totalRanked ?> <?= $currentCuisine ? htmlspecialchars($currentCuisine) : 'restaurants' ?> classes par popularite<?= $currentVille ? ' a ' . htmlspecialchars($currentVille) : ' en Algerie' ?></p>
    <div class="rk-filters">
        <a href="<?= rankingUrl(null, $currentCuisine) ?>" class="rk-filter-btn <?= !$currentVille ? 'active' : '' ?>">Toute l'Algerie</a>
        <?php foreach (array_slice($cities, 0, 8) as $city): ?>
        <a href="<?= rankingUrl($city['ville'], $currentCuisine) ?>" class="rk-filter-btn <?= $currentVille === $city['ville'] ? 'active' : '' ?>"><?= htmlspecialchars($city['ville']) ?> (<?= $city['nb_restos'] ?>)</a>
        <?php endforeach; ?>
    </div>
    <?php if (!empty($cuisines)): ?>
    <div class="rk-filters" style="margin-top:8px;">
        <a href="<?= rankingUrl($currentVille, null) ?>" class="rk-filter-btn <?= !$currentCuisine ? 'active' : '' ?>">Tous types</a>
        <?php foreach (array_slice($cuisines, 0, 8) as $cuis): ?>
        <a href="<?= rankingUrl($currentVille, $cuis['type_cuisine']) ?>" class="rk-filter-btn <?= $currentCuisine === $cuis['type_cuisine'] ? 'active' : '' ?>"><?= htmlspecialchars($cuis['type_cuisine']) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php if (empty($restaurants)): ?>
    <div style="text-align:center;padding:60px 20px;">
        <i class="fas fa-trophy" style="font-size:48px;color:#d1d5db;margin-bottom:12px;display:block;"></i>
        <h2 style="font-size:20px;color:#374151;margin-bottom:8px;">Aucun restaurant classe</h2>
        <p style="font-size:14px;color:#6b7280;">Les restaurants apparaitront ici une fois qu'ils auront des avis et des visites.</p>
    </div>
<?php else: ?>

<?php
$maxScore = max(array_column($restaurants, 'popularity_score') ?: [1]);
$podium = array_slice($restaurants, 0, 3);
// Reorder for podium display: 2nd, 1st, 3rd
if (count($podium) >= 3) {
    $podiumDisplay = [$podium[1], $podium[0], $podium[2]];
    $medals = ['🥈', '🥇', '🥉'];
} elseif (count($podium) === 2) {
    $podiumDisplay = [$podium[1], $podium[0]];
    $medals = ['🥈', '🥇'];
} else {
    $podiumDisplay = $podium;
    $medals = ['🥇'];
}
?>

<!-- Podium Top 3 -->
<div class="rk-podium">
    <?php foreach ($podiumDisplay as $pi => $r):
        $photo = $r['main_photo'] ?? '';
        $photoUrl = $photo ? '/' . ltrim($photo, '/') : '/assets/images/placeholder-resto.jpg';
        $note = min(5, (float)($r['note_moyenne'] ?? 0));
    ?>
    <a href="/restaurant/<?= $r['slug'] ?? $r['id'] ?>" class="rk-podium-item">
        <div class="rk-podium-medal"><?= $medals[$pi] ?></div>
        <img class="rk-podium-photo" src="<?= htmlspecialchars($photoUrl) ?>" alt="<?= htmlspecialchars($r['nom']) ?>" loading="lazy">
        <div class="rk-podium-name"><?= htmlspecialchars($r['nom']) ?></div>
        <div class="rk-podium-ville"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['ville'] ?? '') ?></div>
        <div class="rk-podium-note">★ <?= number_format($note, 1) ?></div>
        <div class="rk-podium-score"><?= number_format($r['nb_avis']) ?> avis <small style="color:#b0b0b0;font-size:9px;"><i class="fab fa-google"></i></small></div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Full List -->
<div class="rk-list">
    <?php foreach ($restaurants as $i => $r):
        $rank = $i + 1;
        $photo = $r['main_photo'] ?? '';
        $photoUrl = $photo ? '/' . ltrim($photo, '/') : '';
        $note = min(5, (float)($r['note_moyenne'] ?? 0));
        $scorePct = $maxScore > 0 ? round(($r['popularity_score'] / $maxScore) * 100) : 0;
        $award = $r['top_award'] ?? null;
    ?>
    <a href="/restaurant/<?= $r['slug'] ?? $r['id'] ?>" class="rk-row">
        <div class="rk-rank"><?= $rank ?></div>
        <?php if ($photoUrl): ?>
        <img class="rk-photo" src="<?= htmlspecialchars($photoUrl) ?>" alt="" loading="lazy">
        <?php else: ?>
        <div class="rk-photo" style="display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:20px;"><i class="fas fa-store"></i></div>
        <?php endif; ?>
        <div class="rk-info">
            <h3 class="rk-name"><?= htmlspecialchars($r['nom']) ?></h3>
            <div class="rk-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['ville'] ?? '') ?></span>
                <span><?= htmlspecialchars($r['type_cuisine'] ?? '') ?></span>
                <?php if (!empty($r['orders_enabled'])): ?><span style="color:#10b981"><i class="fas fa-shopping-bag"></i> Commande</span><?php endif; ?>
            </div>
            <?php if ($award && isset($awardLabels[$award])): ?>
            <span class="rk-award" style="background:<?= $awardLabels[$award]['color'] ?>15;color:<?= $awardLabels[$award]['color'] ?>"><i class="fas <?= $awardLabels[$award]['icon'] ?>"></i> <?= $awardLabels[$award]['label'] ?></span>
            <?php endif; ?>
        </div>
        <button class="cw-card-btn" data-id="<?= $r['id'] ?>"
                onclick="event.preventDefault(); event.stopPropagation(); cwToggleResto(<?= $r['id'] ?>, '<?= addslashes(htmlspecialchars($r['nom'], ENT_QUOTES)) ?>', '<?= addslashes($photo) ?>', '<?= addslashes(htmlspecialchars($r['ville'] ?? '', ENT_QUOTES)) ?>');"
                title="Ajouter au comparateur" style="opacity:1;position:relative;flex-shrink:0;">
            <i class="fas fa-balance-scale"></i>
        </button>
        <div class="rk-right">
            <div class="rk-note"><?= number_format($note, 1) ?> ★</div>
            <div class="rk-avis"><?= number_format($r['nb_avis']) ?> avis <i class="fab fa-google" style="font-size:9px;color:#b0b0b0;" title="Avis Google"></i></div>
            <?php if (!empty($r['platform_reviews'])): ?>
            <div class="rk-avis" style="color:#00635a;font-weight:600;"><?= $r['platform_reviews'] ?> avis site</div>
            <?php endif; ?>
            <div class="rk-score-bar"><div class="rk-score-fill" style="width:<?= $scorePct ?>%"></div></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php endif; ?>

</div>
