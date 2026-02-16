<?php
$categoryLabels = [
    'plage' => 'Plage', 'parc' => 'Parc', 'monument' => 'Monument', 'musee' => 'Musée',
    'shopping' => 'Shopping', 'divertissement' => 'Divertissement', 'nightlife' => 'Vie nocturne',
    'cafe' => 'Café', 'nature' => 'Nature', 'sport' => 'Sport', 'religieux' => 'Religieux', 'culturel' => 'Culturel',
];
$categoryIcons = [
    'plage' => 'fa-umbrella-beach', 'parc' => 'fa-tree', 'monument' => 'fa-landmark',
    'musee' => 'fa-building-columns', 'shopping' => 'fa-bag-shopping', 'divertissement' => 'fa-masks-theater',
    'nightlife' => 'fa-moon', 'cafe' => 'fa-mug-hot', 'nature' => 'fa-mountain-sun',
    'sport' => 'fa-futbol', 'religieux' => 'fa-mosque', 'culturel' => 'fa-palette',
];
$cat = $act['category'] ?? '';
$catLabel = $categoryLabels[$cat] ?? ucfirst($cat);
$catIcon = $categoryIcons[$cat] ?? 'fa-map-pin';
$priceLabels = ['gratuit' => 'Gratuit', 'pas_cher' => '€', 'moyen' => '€€', 'cher' => '€€€'];
$priceLabel = $priceLabels[$act['price_range'] ?? ''] ?? '';
?>
<a class="act-card"
   data-id="<?= $act['id'] ?>"
   data-lat="<?= $act['gps_latitude'] ?? '' ?>"
   data-lng="<?= $act['gps_longitude'] ?? '' ?>"
   href="/activite/<?= htmlspecialchars($act['slug']) ?>">

    <div class="act-card-image">
        <?php if (!empty($act['main_photo'])): ?>
            <img loading="lazy" src="<?= htmlspecialchars($act['main_photo']) ?>" alt="<?= htmlspecialchars($act['nom']) ?>"
                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=400&h=250&fit=crop'">
        <?php else: ?>
            <div class="act-card-noimg"><i class="fas <?= $catIcon ?>"></i></div>
        <?php endif; ?>

        <span class="act-card-category"><i class="fas <?= $catIcon ?>"></i> <?= $catLabel ?></span>

        <?php if ($priceLabel === 'Gratuit'): ?>
            <span class="act-card-free">Gratuit</span>
        <?php endif; ?>
    </div>

    <div class="act-card-body">
        <h3 class="act-card-name"><?= htmlspecialchars($act['nom']) ?></h3>

        <div class="act-card-location">
            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($act['ville'] ?? '') ?>
            <?php if ($priceLabel && $priceLabel !== 'Gratuit'): ?>
                <span class="act-card-price"><?= $priceLabel ?></span>
            <?php endif; ?>
        </div>

        <div class="act-card-bottom">
            <?php $note = min($act['note_moyenne'] ?? 0, 5); ?>
            <?php if ($note > 0): ?>
                <div class="act-card-rating">
                    <span class="rating-pill"><?= number_format($note, 1) ?></span>
                    <span class="rating-count"><?= (int)($act['nb_avis'] ?? 0) ?> avis</span>
                </div>
            <?php else: ?>
                <div class="act-card-no-rating">Pas encore d'avis</div>
            <?php endif; ?>
        </div>
    </div>
</a>
