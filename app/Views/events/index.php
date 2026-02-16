<?php
$eventTypeLabels = [
    'degustation' => 'Degustation',
    'atelier' => 'Atelier culinaire',
    'soiree' => 'Soiree a theme',
    'brunch' => 'Brunch',
    'concert' => 'Concert & Live',
    'popup' => 'Pop-up',
    'autre' => 'Evenement',
];
$eventTypeIcons = [
    'degustation' => 'fa-wine-glass',
    'atelier' => 'fa-utensils',
    'soiree' => 'fa-champagne-glasses',
    'brunch' => 'fa-mug-saucer',
    'concert' => 'fa-music',
    'popup' => 'fa-store',
    'autre' => 'fa-calendar-star',
];
$eventTypeColors = [
    'degustation' => '#8b5cf6',
    'atelier' => '#f59e0b',
    'soiree' => '#ec4899',
    'brunch' => '#f97316',
    'concert' => '#6366f1',
    'popup' => '#14b8a6',
    'autre' => '#00635a',
];

$currentVille = $_GET['ville'] ?? '';
?>
<style>
    /* Hero */
    .ev-hero {
        background: linear-gradient(135deg, #00635a 0%, #004d40 100%);
        color: #fff;
        padding: 56px 0 48px;
        text-align: center;
    }
    .ev-hero-inner { max-width: 700px; margin: 0 auto; padding: 0 20px; }
    .ev-hero h1 {
        font-size: 32px;
        font-weight: 800;
        margin: 0 0 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }
    .ev-hero p { font-size: 16px; opacity: 0.85; margin: 0; line-height: 1.5; }

    /* Filters */
    .ev-filters {
        max-width: 1200px;
        margin: -24px auto 0;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }
    .ev-filter-bar {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .ev-filter-bar label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ev-filter-bar select {
        padding: 8px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        background: #fff;
        cursor: pointer;
        color: #374151;
        font-family: inherit;
        min-width: 180px;
    }
    .ev-filter-bar select:focus { outline: none; border-color: #00635a; }
    .ev-filter-reset {
        margin-left: auto;
        font-size: 13px;
        color: #00635a;
        text-decoration: none;
        font-weight: 600;
    }
    .ev-filter-reset:hover { text-decoration: underline; }

    /* Grid */
    .ev-container { max-width: 1200px; margin: 0 auto; padding: 32px 20px 60px; }
    .ev-count {
        font-size: 15px;
        color: #6b7280;
        margin-bottom: 20px;
    }
    .ev-count strong { color: #111827; }
    .ev-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
    }

    /* Card */
    .ev-card {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        transition: transform 0.25s, box-shadow 0.25s;
        display: flex;
        flex-direction: column;
    }
    .ev-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .ev-card-img {
        position: relative;
        height: 190px;
        background: linear-gradient(135deg, #e6f2f0, #d1d5db);
        overflow: hidden;
    }
    .ev-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }
    .ev-card:hover .ev-card-img img { transform: scale(1.05); }
    .ev-card-img .ev-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 48px;
        color: #00635a;
        opacity: 0.25;
    }
    .ev-date-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: #fff;
        border-radius: 10px;
        padding: 6px 10px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,.15);
        min-width: 52px;
    }
    .ev-date-badge .ev-day {
        font-size: 20px;
        font-weight: 800;
        color: #00635a;
        line-height: 1;
    }
    .ev-date-badge .ev-month {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .ev-type-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .ev-status-badge {
        position: absolute;
        bottom: 12px;
        left: 12px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
    }
    .ev-status-complet { background: #ef4444; }
    .ev-status-annule { background: #6b7280; }

    .ev-card-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }
    .ev-card-title {
        font-size: 17px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 6px;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .ev-card-resto {
        font-size: 13px;
        color: #00635a;
        font-weight: 600;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .ev-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 12px;
    }
    .ev-card-meta span {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .ev-card-meta i { font-size: 12px; color: #00635a; }
    .ev-card-desc {
        font-size: 13px;
        color: #6b7280;
        line-height: 1.5;
        margin-bottom: 14px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }
    .ev-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 12px;
        border-top: 1px solid #f3f4f6;
    }
    .ev-card-price {
        font-size: 18px;
        font-weight: 800;
        color: #00635a;
    }
    .ev-card-price.free {
        font-size: 14px;
        font-weight: 700;
        color: #10b981;
        background: #ecfdf5;
        padding: 4px 12px;
        border-radius: 20px;
    }
    .ev-card-participants {
        font-size: 12px;
        color: #6b7280;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .ev-card-participants .ev-spots-low { color: #ef4444; font-weight: 600; }
    .ev-btn-register {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: #00635a;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
    }
    .ev-btn-register:hover { background: #004d40; }
    .ev-btn-register.full {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* Empty */
    .ev-empty {
        text-align: center;
        padding: 80px 20px;
        color: #9ca3af;
    }
    .ev-empty i {
        font-size: 56px;
        margin-bottom: 16px;
        display: block;
        opacity: 0.3;
    }
    .ev-empty h3 {
        font-size: 20px;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .ev-empty p { font-size: 14px; }

    @media (max-width: 640px) {
        .ev-hero h1 { font-size: 24px; }
        .ev-grid { grid-template-columns: 1fr; }
        .ev-filter-bar { flex-direction: column; align-items: stretch; }
        .ev-filter-bar select { min-width: 100%; }
        .ev-filter-reset { margin-left: 0; text-align: center; }
    }
</style>

<!-- Hero -->
<div class="ev-hero">
    <div class="ev-hero-inner">
        <h1><i class="fas fa-calendar-days"></i> Evenements Culinaires</h1>
        <p>Degustations, ateliers cuisine, soirees a theme... Decouvrez les evenements gastronomiques pres de chez vous et inscrivez-vous en un clic.</p>
    </div>
</div>

<!-- Filters -->
<div class="ev-filters">
    <div class="ev-filter-bar">
        <label><i class="fas fa-map-marker-alt"></i> Ville</label>
        <select id="evVilleFilter" onchange="filterByVille(this.value)">
            <option value="">Toutes les villes</option>
            <?php if (!empty($villes)): ?>
                <?php foreach ($villes as $v): ?>
                    <option value="<?= htmlspecialchars($v) ?>" <?= $currentVille === $v ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <?php if ($currentVille): ?>
            <a href="/evenements" class="ev-filter-reset"><i class="fas fa-times"></i> Reinitialiser</a>
        <?php endif; ?>
    </div>
</div>

<!-- Events grid -->
<div class="ev-container">
    <?php if (!empty($events)): ?>
        <p class="ev-count"><strong><?= count($events) ?></strong> evenement<?= count($events) > 1 ? 's' : '' ?><?= $currentVille ? ' a ' . htmlspecialchars($currentVille) : '' ?></p>

        <div class="ev-grid">
            <?php
            $moisFr = ['', 'Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aout', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($events as $ev):
                $date = new DateTime($ev['event_date']);
                $day = $date->format('d');
                $monthNum = (int)$date->format('n');
                $month = $moisFr[$monthNum] ?? $date->format('M');
                $type = $ev['event_type'] ?? 'autre';
                $typeLabel = $eventTypeLabels[$type] ?? ucfirst($type);
                $typeIcon = $eventTypeIcons[$type] ?? 'fa-calendar';
                $typeColor = $eventTypeColors[$type] ?? '#00635a';
                $spotsLeft = max(0, ($ev['max_participants'] ?? 0) - ($ev['current_participants'] ?? 0));
                $isFull = $spotsLeft <= 0 && ($ev['max_participants'] ?? 0) > 0;
                $isCancelled = ($ev['status'] ?? '') === 'cancelled';
                $startTime = !empty($ev['start_time']) ? substr($ev['start_time'], 0, 5) : '';
                $endTime = !empty($ev['end_time']) ? substr($ev['end_time'], 0, 5) : '';
                $price = $ev['price'] ?? 0;
            ?>
            <div class="ev-card" data-aos="fade-up">
                <div class="ev-card-img">
                    <?php if (!empty($ev['photo_path'])): ?>
                        <img src="/<?= htmlspecialchars($ev['photo_path']) ?>" alt="<?= htmlspecialchars($ev['title']) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="ev-placeholder"><i class="fas <?= $typeIcon ?>"></i></div>
                    <?php endif; ?>
                    <div class="ev-date-badge">
                        <div class="ev-day"><?= $day ?></div>
                        <div class="ev-month"><?= $month ?></div>
                    </div>
                    <div class="ev-type-badge" style="background: <?= $typeColor ?>;">
                        <i class="fas <?= $typeIcon ?>"></i> <?= htmlspecialchars($typeLabel) ?>
                    </div>
                    <?php if ($isFull): ?>
                        <div class="ev-status-badge ev-status-complet">Complet</div>
                    <?php elseif ($isCancelled): ?>
                        <div class="ev-status-badge ev-status-annule">Annule</div>
                    <?php endif; ?>
                </div>
                <div class="ev-card-body">
                    <h3 class="ev-card-title"><?= htmlspecialchars($ev['title']) ?></h3>
                    <div class="ev-card-resto">
                        <i class="fas fa-store"></i>
                        <?= htmlspecialchars($ev['resto_nom'] ?? '') ?>
                        <?php if (!empty($ev['ville'])): ?>
                            <span style="color:#9ca3af;font-weight:400;"> &middot; <?= htmlspecialchars($ev['ville']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="ev-card-meta">
                        <?php if ($startTime): ?>
                            <span><i class="fas fa-clock"></i> <?= $startTime ?><?= $endTime ? ' - ' . $endTime : '' ?></span>
                        <?php endif; ?>
                        <?php if (($ev['max_participants'] ?? 0) > 0): ?>
                            <span>
                                <i class="fas fa-users"></i>
                                <?php if ($spotsLeft <= 5 && $spotsLeft > 0): ?>
                                    <span class="ev-spots-low"><?= $spotsLeft ?> place<?= $spotsLeft > 1 ? 's' : '' ?> restante<?= $spotsLeft > 1 ? 's' : '' ?></span>
                                <?php else: ?>
                                    <?= (int)$ev['current_participants'] ?>/<?= (int)$ev['max_participants'] ?>
                                <?php endif; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($ev['description'])): ?>
                        <p class="ev-card-desc"><?= htmlspecialchars($ev['description']) ?></p>
                    <?php endif; ?>
                    <div class="ev-card-footer">
                        <?php if ($price > 0): ?>
                            <span class="ev-card-price"><?= number_format($price, 0, ',', ' ') ?> DZD</span>
                        <?php else: ?>
                            <span class="ev-card-price free"><i class="fas fa-gift"></i> Gratuit</span>
                        <?php endif; ?>
                        <?php if ($isFull || $isCancelled): ?>
                            <span class="ev-btn-register full"><i class="fas fa-ban"></i> <?= $isCancelled ? 'Annule' : 'Complet' ?></span>
                        <?php else: ?>
                            <a href="/evenements/<?= (int)$ev['id'] ?>" class="ev-btn-register">
                                <i class="fas fa-ticket"></i> S'inscrire
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="ev-empty">
            <i class="fas fa-calendar-xmark"></i>
            <h3>Aucun evenement a venir</h3>
            <p>Il n'y a pas d'evenements prevus pour le moment<?= $currentVille ? ' a ' . htmlspecialchars($currentVille) : '' ?>. Revenez bientot !</p>
            <?php if ($currentVille): ?>
                <a href="/evenements" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#00635a;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;">Voir tous les evenements</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function filterByVille(ville) {
    if (ville) {
        window.location.href = '/evenements?ville=' + encodeURIComponent(ville);
    } else {
        window.location.href = '/evenements';
    }
}
</script>
