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

$moisFr = ['', 'Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];
$joursFr = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

$type = $event['event_type'] ?? 'autre';
$typeLabel = $eventTypeLabels[$type] ?? ucfirst($type);
$typeIcon = $eventTypeIcons[$type] ?? 'fa-calendar';
$typeColor = $eventTypeColors[$type] ?? '#00635a';

$date = new DateTime($event['event_date']);
$dayName = $joursFr[(int)$date->format('w')];
$dayNum = $date->format('d');
$monthName = $moisFr[(int)$date->format('n')];
$year = $date->format('Y');
$formattedDate = "$dayName $dayNum $monthName $year";

$startTime = !empty($event['start_time']) ? substr($event['start_time'], 0, 5) : '';
$endTime = !empty($event['end_time']) ? substr($event['end_time'], 0, 5) : '';
$price = $event['price'] ?? 0;
$maxParticipants = (int)($event['max_participants'] ?? 0);
$currentParticipants = (int)($event['current_participants'] ?? 0);
$spotsLeft = $spotsLeft ?? max(0, $maxParticipants - $currentParticipants);
$isFull = $spotsLeft <= 0 && $maxParticipants > 0;
$isRegistered = $isRegistered ?? false;
$hasPhoto = !empty($event['photo_path']);
$restoSlug = $restaurant['slug'] ?? '';
?>
<style>
    /* Hero */
    .evs-hero {
        position: relative;
        min-height: 340px;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
    }
    .evs-hero-bg {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-size: cover;
        background-position: center;
        filter: brightness(0.4);
    }
    .evs-hero-gradient {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(135deg, rgba(0,99,90,0.85) 0%, rgba(0,77,64,0.9) 100%);
    }
    .evs-hero-content {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 20px 36px;
        width: 100%;
    }
    .evs-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 20px;
        transition: color 0.2s;
    }
    .evs-back:hover { color: #fff; }
    .evs-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 12px;
    }
    .evs-hero h1 {
        font-size: 32px;
        font-weight: 800;
        color: #fff;
        margin: 0 0 10px;
        line-height: 1.2;
    }
    .evs-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 15px;
        color: rgba(255,255,255,0.9);
    }
    .evs-hero-meta span {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .evs-hero-meta i { font-size: 14px; }
    .evs-hero-meta a { color: #fff; text-decoration: underline; font-weight: 600; }
    .evs-hero-meta a:hover { opacity: 0.85; }

    /* Body */
    .evs-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 32px 20px 60px;
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 32px;
    }

    /* Left column */
    .evs-main {}
    .evs-section {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        margin-bottom: 20px;
    }
    .evs-section h2 {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .evs-section h2 i { color: #00635a; font-size: 16px; }
    .evs-desc {
        font-size: 15px;
        color: #4b5563;
        line-height: 1.7;
        white-space: pre-line;
    }
    .evs-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .evs-detail-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px;
        background: #f3f4f6;
        border-radius: 10px;
    }
    .evs-detail-icon {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        background: #e6f2f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #00635a;
        font-size: 16px;
        flex-shrink: 0;
    }
    .evs-detail-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 2px;
    }
    .evs-detail-value {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }

    /* Restaurant card */
    .evs-resto-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        background: #f3f4f6;
        border-radius: 10px;
        text-decoration: none;
        color: inherit;
        transition: background 0.2s;
    }
    .evs-resto-card:hover { background: #e6f2f0; }
    .evs-resto-avatar {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: #00635a;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        flex-shrink: 0;
    }
    .evs-resto-name {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }
    .evs-resto-loc {
        font-size: 13px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* Right column - sidebar */
    .evs-sidebar {}
    .evs-action-card {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        position: sticky;
        top: 90px;
    }
    .evs-price-display {
        text-align: center;
        margin-bottom: 20px;
    }
    .evs-price-tag {
        font-size: 32px;
        font-weight: 800;
        color: #00635a;
    }
    .evs-price-tag.free {
        font-size: 24px;
        color: #10b981;
    }
    .evs-price-unit {
        font-size: 14px;
        color: #6b7280;
        font-weight: 400;
    }
    .evs-spots-info {
        text-align: center;
        margin-bottom: 20px;
    }
    .evs-spots-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 8px;
    }
    .evs-spots-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .evs-spots-text {
        font-size: 13px;
        color: #6b7280;
    }
    .evs-spots-text strong { color: #111827; }
    .evs-spots-low strong { color: #ef4444; }

    .evs-btn-register {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 14px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
    }
    .evs-btn-register.primary {
        background: #00635a;
        color: #fff;
    }
    .evs-btn-register.primary:hover { background: #004d40; }
    .evs-btn-register.registered {
        background: #ecfdf5;
        color: #10b981;
        border: 2px solid #10b981;
    }
    .evs-btn-register.full {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
    }
    .evs-btn-register:disabled { cursor: not-allowed; opacity: 0.6; }

    .evs-register-msg {
        text-align: center;
        margin-top: 12px;
        font-size: 13px;
        padding: 10px;
        border-radius: 8px;
        display: none;
    }
    .evs-register-msg.success { display: block; background: #ecfdf5; color: #059669; }
    .evs-register-msg.error { display: block; background: #fef2f2; color: #dc2626; }

    .evs-sidebar-info {
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
        font-size: 12px;
        color: #9ca3af;
        text-align: center;
    }

    @media (max-width: 768px) {
        .evs-container {
            grid-template-columns: 1fr;
        }
        .evs-hero h1 { font-size: 24px; }
        .evs-details-grid { grid-template-columns: 1fr; }
        .evs-action-card { position: static; }
    }
</style>

<!-- Hero -->
<div class="evs-hero">
    <?php if ($hasPhoto): ?>
        <div class="evs-hero-bg" style="background-image: url('/<?= htmlspecialchars($event['photo_path']) ?>');"></div>
    <?php endif; ?>
    <div class="evs-hero-gradient"></div>
    <div class="evs-hero-content">
        <a href="/evenements" class="evs-back"><i class="fas fa-arrow-left"></i> Retour aux evenements</a>
        <div>
            <span class="evs-type-badge" style="background: <?= $typeColor ?>;">
                <i class="fas <?= $typeIcon ?>"></i> <?= htmlspecialchars($typeLabel) ?>
            </span>
        </div>
        <h1><?= htmlspecialchars($event['title']) ?></h1>
        <div class="evs-hero-meta">
            <span><i class="fas fa-calendar-day"></i> <?= $formattedDate ?></span>
            <?php if ($startTime): ?>
                <span><i class="fas fa-clock"></i> <?= $startTime ?><?= $endTime ? ' - ' . $endTime : '' ?></span>
            <?php endif; ?>
            <?php if (!empty($restaurant)): ?>
                <span><i class="fas fa-store"></i>
                    <?php if ($restoSlug): ?>
                        <a href="/restaurant/<?= htmlspecialchars($restoSlug) ?>"><?= htmlspecialchars($restaurant['nom'] ?? '') ?></a>
                    <?php else: ?>
                        <?= htmlspecialchars($restaurant['nom'] ?? '') ?>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Body -->
<div class="evs-container">
    <!-- Left: details -->
    <div class="evs-main">
        <!-- Description -->
        <?php if (!empty($event['description'])): ?>
        <div class="evs-section">
            <h2><i class="fas fa-align-left"></i> Description</h2>
            <div class="evs-desc"><?= nl2br(htmlspecialchars($event['description'])) ?></div>
        </div>
        <?php endif; ?>

        <!-- Details grid -->
        <div class="evs-section">
            <h2><i class="fas fa-info-circle"></i> Details de l'evenement</h2>
            <div class="evs-details-grid">
                <div class="evs-detail-item">
                    <div class="evs-detail-icon"><i class="fas fa-calendar-day"></i></div>
                    <div>
                        <div class="evs-detail-label">Date</div>
                        <div class="evs-detail-value"><?= $formattedDate ?></div>
                    </div>
                </div>
                <div class="evs-detail-item">
                    <div class="evs-detail-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <div class="evs-detail-label">Horaire</div>
                        <div class="evs-detail-value"><?= $startTime ?: '---' ?><?= $endTime ? ' - ' . $endTime : '' ?></div>
                    </div>
                </div>
                <div class="evs-detail-item">
                    <div class="evs-detail-icon"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="evs-detail-label">Participants</div>
                        <div class="evs-detail-value">
                            <?php if ($maxParticipants > 0): ?>
                                <?= $currentParticipants ?>/<?= $maxParticipants ?>
                            <?php else: ?>
                                <?= $currentParticipants ?> inscrit<?= $currentParticipants > 1 ? 's' : '' ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="evs-detail-item">
                    <div class="evs-detail-icon"><i class="fas fa-tag"></i></div>
                    <div>
                        <div class="evs-detail-label">Prix</div>
                        <div class="evs-detail-value">
                            <?= $price > 0 ? number_format($price, 0, ',', ' ') . ' DZD' : 'Gratuit' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restaurant -->
        <?php if (!empty($restaurant)): ?>
        <div class="evs-section">
            <h2><i class="fas fa-store"></i> Organise par</h2>
            <a href="<?= $restoSlug ? '/restaurant/' . htmlspecialchars($restoSlug) : '#' ?>" class="evs-resto-card">
                <div class="evs-resto-avatar">
                    <?= strtoupper(mb_substr($restaurant['nom'] ?? 'R', 0, 1)) ?>
                </div>
                <div>
                    <div class="evs-resto-name"><?= htmlspecialchars($restaurant['nom'] ?? '') ?></div>
                    <?php if (!empty($restaurant['ville'])): ?>
                        <div class="evs-resto-loc"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($restaurant['ville']) ?></div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: action sidebar -->
    <div class="evs-sidebar">
        <div class="evs-action-card">
            <!-- Price -->
            <div class="evs-price-display">
                <?php if ($price > 0): ?>
                    <div class="evs-price-tag"><?= number_format($price, 0, ',', ' ') ?> <span class="evs-price-unit">DZD</span></div>
                <?php else: ?>
                    <div class="evs-price-tag free"><i class="fas fa-gift"></i> Gratuit</div>
                <?php endif; ?>
            </div>

            <!-- Spots progress -->
            <?php if ($maxParticipants > 0): ?>
            <div class="evs-spots-info">
                <?php
                    $fillPercent = min(100, ($currentParticipants / $maxParticipants) * 100);
                    $fillColor = $fillPercent >= 90 ? '#ef4444' : ($fillPercent >= 70 ? '#f59e0b' : '#10b981');
                ?>
                <div class="evs-spots-bar">
                    <div class="evs-spots-fill" style="width: <?= $fillPercent ?>%; background: <?= $fillColor ?>;"></div>
                </div>
                <div class="evs-spots-text <?= $spotsLeft <= 5 ? 'evs-spots-low' : '' ?>">
                    <strong><?= $spotsLeft ?></strong> place<?= $spotsLeft > 1 ? 's' : '' ?> restante<?= $spotsLeft > 1 ? 's' : '' ?> sur <?= $maxParticipants ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Register button -->
            <?php if ($isRegistered): ?>
                <button class="evs-btn-register registered" disabled>
                    <i class="fas fa-check-circle"></i> Vous etes inscrit(e)
                </button>
            <?php elseif ($isFull): ?>
                <button class="evs-btn-register full" disabled>
                    <i class="fas fa-ban"></i> Complet
                </button>
            <?php else: ?>
                <button class="evs-btn-register primary" id="evRegisterBtn" onclick="registerForEvent(<?= (int)$event['id'] ?>)">
                    <i class="fas fa-ticket"></i> S'inscrire
                </button>
            <?php endif; ?>

            <div class="evs-register-msg" id="evRegisterMsg"></div>

            <div class="evs-sidebar-info">
                <i class="fas fa-shield-halved"></i> Inscription securisee. Vous pouvez annuler a tout moment.
            </div>
        </div>
    </div>
</div>

<script>
async function registerForEvent(eventId) {
    var btn = document.getElementById('evRegisterBtn');
    var msg = document.getElementById('evRegisterMsg');
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription...';
    msg.className = 'evs-register-msg';
    msg.style.display = 'none';

    try {
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var headers = { 'Content-Type': 'application/json' };
        if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content;

        var res = await fetch('/api/events/' + eventId + '/register', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({})
        });
        var data = await res.json();

        if (data.success) {
            btn.className = 'evs-btn-register registered';
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Vous etes inscrit(e)';
            btn.disabled = true;
            msg.className = 'evs-register-msg success';
            msg.textContent = data.message || 'Inscription confirmee ! Vous recevrez un rappel avant l\'evenement.';
            msg.style.display = 'block';

            // Update spots if available
            var spotsText = document.querySelector('.evs-spots-text');
            if (spotsText && typeof data.spots_left !== 'undefined') {
                var left = parseInt(data.spots_left);
                spotsText.innerHTML = '<strong>' + left + '</strong> place' + (left > 1 ? 's' : '') + ' restante' + (left > 1 ? 's' : '');
                if (left <= 5) spotsText.classList.add('evs-spots-low');
            }
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-ticket"></i> S\'inscrire';
            msg.className = 'evs-register-msg error';
            msg.textContent = data.error || 'Une erreur est survenue. Veuillez reessayer.';
            msg.style.display = 'block';
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-ticket"></i> S\'inscrire';
        msg.className = 'evs-register-msg error';
        msg.textContent = 'Erreur de connexion. Verifiez votre connexion internet.';
        msg.style.display = 'block';
    }
}
</script>
