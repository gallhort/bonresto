<style>
    /* ═══════════════════════════════════════════════════════════
       LOYALTY PAGE - DESIGN TOKENS
       ═══════════════════════════════════════════════════════════ */
    :root {
        --ly-primary: #00635a;
        --ly-primary-light: #00897b;
        --ly-bg: #f8fafc;
        --ly-card: #ffffff;
        --ly-text: #1e293b;
        --ly-text-light: #64748b;
        --ly-border: #e2e8f0;
        --ly-gold: #eab308;
        --ly-success: #10b981;
        --ly-warning: #f59e0b;
        --ly-danger: #ef4444;
        --ly-blue: #3b82f6;
        --ly-purple: #8b5cf6;
    }

    .loyalty-page { max-width: 1100px; margin: 0 auto; padding: 24px 20px 60px; }

    /* ═══════════════════════════════════════════════════════════
       HERO HEADER
       ═══════════════════════════════════════════════════════════ */
    .loyalty-hero {
        background: linear-gradient(135deg, var(--ly-primary) 0%, var(--ly-primary-light) 50%, #26a69a 100%);
        border-radius: 20px;
        padding: 36px 40px;
        color: white;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .loyalty-hero::before {
        content: '';
        position: absolute;
        top: -60%;
        right: -15%;
        width: 350px;
        height: 350px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .loyalty-hero-inner {
        display: flex;
        align-items: center;
        gap: 28px;
        position: relative;
        z-index: 1;
    }
    .loyalty-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        border: 3px solid rgba(255,255,255,0.3);
        flex-shrink: 0;
    }
    .loyalty-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
    .loyalty-user-info h1 { font-size: 24px; font-weight: 700; margin: 0 0 6px; }
    .loyalty-user-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.18);
        padding: 6px 16px;
        border-radius: 30px;
        font-size: 15px;
        font-weight: 600;
    }
    .loyalty-points-box {
        margin-left: auto;
        text-align: center;
    }
    .loyalty-points-value { font-size: 48px; font-weight: 800; line-height: 1; }
    .loyalty-points-label { font-size: 14px; opacity: 0.85; margin-top: 4px; }
    .loyalty-rank-chip {
        position: absolute;
        top: 20px;
        right: 24px;
        background: rgba(255,255,255,0.18);
        padding: 8px 18px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 14px;
        z-index: 2;
    }

    /* ═══════════════════════════════════════════════════════════
       PROGRESSION BAR
       ═══════════════════════════════════════════════════════════ */
    .progression-card {
        background: var(--ly-card);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 28px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .progression-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    .progression-title { font-size: 16px; font-weight: 700; color: var(--ly-text); }
    .progression-info { font-size: 13px; color: var(--ly-text-light); }
    .progression-info strong { color: var(--ly-text); }
    .progression-track {
        background: var(--ly-border);
        border-radius: 10px;
        height: 14px;
        overflow: hidden;
        margin-bottom: 18px;
    }
    .progression-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--ly-primary), var(--ly-primary-light), #26a69a);
        border-radius: 10px;
        transition: width 1.2s ease;
        position: relative;
    }
    .progression-fill::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: ly-shimmer 2.5s infinite;
    }
    @keyframes ly-shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

    /* Level milestones */
    .levels-row {
        display: flex;
        justify-content: space-between;
    }
    .level-step {
        text-align: center;
        flex: 1;
        position: relative;
    }
    .level-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 6px;
        border: 3px solid var(--ly-border);
        background: var(--ly-bg);
        transition: all 0.3s;
    }
    .level-step.active .level-icon {
        border-color: var(--ly-primary);
        background: rgba(0,99,90,0.08);
        box-shadow: 0 0 0 4px rgba(0,99,90,0.1);
    }
    .level-step.locked .level-icon {
        opacity: 0.35;
        filter: grayscale(1);
    }
    .level-name { font-size: 11px; font-weight: 600; color: var(--ly-text); }
    .level-pts { font-size: 10px; color: var(--ly-text-light); }
    .level-step.locked .level-name,
    .level-step.locked .level-pts { opacity: 0.4; }

    /* ═══════════════════════════════════════════════════════════
       QUICK STATS
       ═══════════════════════════════════════════════════════════ */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }
    .stat-card {
        background: var(--ly-card);
        border-radius: 14px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .stat-value { font-size: 28px; font-weight: 800; color: var(--ly-primary); }
    .stat-label { font-size: 12px; color: var(--ly-text-light); margin-top: 4px; }

    /* ═══════════════════════════════════════════════════════════
       ACHIEVEMENTS GRID
       ═══════════════════════════════════════════════════════════ */
    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--ly-text);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .section-title i { color: var(--ly-primary); }

    .achievements-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 12px;
        margin-bottom: 32px;
    }
    .achievement-card {
        background: var(--ly-card);
        border-radius: 14px;
        padding: 20px 14px;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        position: relative;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .achievement-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .achievement-card.locked {
        opacity: 0.5;
        filter: grayscale(0.8);
    }
    .achievement-card.locked:hover { transform: none; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
    .achievement-icon {
        font-size: 36px;
        margin-bottom: 10px;
        display: block;
    }
    .achievement-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--ly-text);
        margin-bottom: 4px;
    }
    .achievement-desc {
        font-size: 11px;
        color: var(--ly-text-light);
        line-height: 1.4;
    }
    .achievement-check {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--ly-success);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }
    .achievement-date {
        font-size: 10px;
        color: var(--ly-primary);
        margin-top: 6px;
    }
    .achievement-lock {
        position: absolute;
        top: 10px;
        right: 10px;
        color: var(--ly-text-light);
        font-size: 12px;
    }

    /* ═══════════════════════════════════════════════════════════
       MAIN GRID (Earn + History | Sidebar)
       ═══════════════════════════════════════════════════════════ */
    .loyalty-grid {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 24px;
    }
    .ly-card {
        background: var(--ly-card);
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        margin-bottom: 24px;
    }
    .ly-card-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ly-card-title i { color: var(--ly-primary); font-size: 15px; }

    /* Earn points */
    .earn-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .earn-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--ly-bg);
        border-radius: 10px;
        transition: background 0.2s;
    }
    .earn-item:hover { background: #f1f5f9; }
    .earn-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
        flex-shrink: 0;
    }
    .earn-action { font-size: 12px; font-weight: 600; color: var(--ly-text); }
    .earn-pts { font-size: 13px; font-weight: 700; color: var(--ly-primary); }

    /* History */
    .history-list { max-height: 340px; overflow-y: auto; }
    .history-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid var(--ly-border);
    }
    .history-item:last-child { border-bottom: none; }
    .history-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        flex-shrink: 0;
    }
    .history-dot.positive { background: rgba(16,185,129,0.12); color: var(--ly-success); }
    .history-dot.negative { background: rgba(239,68,68,0.12); color: var(--ly-danger); }
    .history-info { flex: 1; min-width: 0; }
    .history-desc { font-size: 13px; font-weight: 500; }
    .history-date { font-size: 11px; color: var(--ly-text-light); }
    .history-pts { font-size: 15px; font-weight: 700; white-space: nowrap; }
    .history-pts.positive { color: var(--ly-success); }
    .history-pts.negative { color: var(--ly-danger); }

    /* Leaderboard */
    .lb-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid var(--ly-border);
    }
    .lb-item:last-child { border-bottom: none; }
    .lb-item.me {
        background: rgba(0,99,90,0.06);
        margin: 0 -24px;
        padding: 10px 24px;
        border-radius: 8px;
    }
    .lb-rank {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 12px;
        flex-shrink: 0;
    }
    .lb-rank.r1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #fff; }
    .lb-rank.r2 { background: linear-gradient(135deg, #cbd5e1, #94a3b8); color: #fff; }
    .lb-rank.r3 { background: linear-gradient(135deg, #fdba74, #f97316); color: #fff; }
    .lb-rank.rn { background: var(--ly-border); color: var(--ly-text-light); }
    .lb-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--ly-border);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    .lb-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
    .lb-info { flex: 1; min-width: 0; }
    .lb-name { font-size: 13px; font-weight: 600; }
    .lb-badge { font-size: 11px; color: var(--ly-text-light); }
    .lb-pts { font-weight: 700; font-size: 13px; color: var(--ly-primary); }

    /* Empty state */
    .ly-empty { text-align: center; padding: 28px; color: var(--ly-text-light); }
    .ly-empty i { font-size: 32px; margin-bottom: 10px; opacity: 0.3; display: block; }

    /* Titles section */
    .titles-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 32px;
    }
    .title-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        border: 2px solid;
        transition: transform 0.2s;
    }
    .title-chip:hover { transform: scale(1.04); }
    .title-chip .t-icon { font-size: 16px; }
    .title-chip .t-date { font-size: 10px; opacity: 0.7; margin-left: 4px; }
    .titles-empty {
        color: var(--ly-text-light);
        font-size: 13px;
        font-style: italic;
    }
    .title-primary {
        position: relative;
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    }
    .title-primary::before {
        content: '★';
        position: absolute;
        top: -6px;
        right: -4px;
        font-size: 14px;
        color: #eab308;
    }

    /* Perks per level */
    .perk-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid var(--ly-border);
    }
    .perk-item:last-child { border-bottom: none; }
    .perk-icon { font-size: 22px; flex-shrink: 0; width: 32px; text-align: center; }
    .perk-info { flex: 1; min-width: 0; }
    .perk-name { font-size: 13px; font-weight: 700; color: var(--ly-text); }
    .perk-desc { font-size: 11px; color: var(--ly-text-light); }
    .perk-check { color: var(--ly-success); font-size: 13px; }
    .perk-current { color: var(--ly-primary); font-size: 12px; animation: ly-pulse 1.5s infinite; }
    .perk-lock { color: var(--ly-border); font-size: 12px; }
    .perk-item.locked { opacity: 0.45; }
    .perk-item.current { background: rgba(0,99,90,0.05); margin: 0 -24px; padding: 10px 24px; border-radius: 8px; }
    @keyframes ly-pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }

    /* ═══════════════════════════════════════════════════════════
       RESPONSIVE
       ═══════════════════════════════════════════════════════════ */
    @media (max-width: 1024px) {
        .loyalty-grid { grid-template-columns: 1fr; }
        .loyalty-hero-inner { flex-direction: column; text-align: center; }
        .loyalty-points-box { margin: 16px 0 0; }
        .achievements-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 640px) {
        .loyalty-page { padding: 16px 12px 40px; }
        .loyalty-hero { padding: 24px; }
        .loyalty-points-value { font-size: 38px; }
        .stats-row { grid-template-columns: 1fr; }
        .earn-grid { grid-template-columns: 1fr; }
        .achievements-grid { grid-template-columns: repeat(2, 1fr); }
        .levels-row { gap: 4px; }
        .level-icon { width: 36px; height: 36px; font-size: 18px; }
    }
</style>

<div class="loyalty-page">

    <!-- ═══════════════════════════════════════════════════════════
         HERO HEADER
         ═══════════════════════════════════════════════════════════ -->
    <div class="loyalty-hero">
        <span class="loyalty-rank-chip">
            <i class="fas fa-trophy"></i> Rang #<?= $rank ?>
        </span>
        <div class="loyalty-hero-inner">
            <div class="loyalty-avatar">
                <?php if (!empty($user['photo_profil'])): ?>
                    <img src="<?= htmlspecialchars($user['photo_profil']) ?>" alt="Photo de profil">
                <?php else: ?>
                    <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div class="loyalty-user-info">
                <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
                <div class="loyalty-user-badge">
                    <span><?= $stats['badge']['icon'] ?? '🔍' ?></span>
                    <span><?= htmlspecialchars($stats['badge']['name'] ?? 'Explorateur') ?></span>
                </div>
                <?php if (!empty($stats['primary_title'])): ?>
                    <div class="loyalty-user-badge" style="background:<?= htmlspecialchars($stats['primary_title']['title_color']) ?>30;margin-top:6px;font-size:13px;">
                        <span><?= $stats['primary_title']['title_icon'] ?></span>
                        <span><?= htmlspecialchars($stats['primary_title']['title_label']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="loyalty-points-box">
                <div class="loyalty-points-value"><?= number_format($stats['points']) ?></div>
                <div class="loyalty-points-label">points</div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         PROGRESSION
         ═══════════════════════════════════════════════════════════ -->
    <div class="progression-card">
        <div class="progression-header">
            <div class="progression-title"><i class="fas fa-chart-line"></i> Progression</div>
            <?php if ($stats['next_badge']): ?>
                <div class="progression-info">
                    <strong><?= $stats['points_to_next'] ?></strong> points avant
                    <strong><?= $stats['next_badge']['icon'] ?> <?= $stats['next_badge']['name'] ?></strong>
                </div>
            <?php else: ?>
                <div class="progression-info"><strong>Niveau maximum atteint !</strong></div>
            <?php endif; ?>
        </div>

        <div class="progression-track">
            <div class="progression-fill" style="width: <?= $stats['progress_percent'] ?>%"></div>
        </div>

        <div class="levels-row">
            <?php
            $allBadges = $stats['all_badges'] ?? [];
            $currentPoints = $stats['points'] ?? 0;
            foreach ($allBadges as $badge):
                $isActive = $currentPoints >= $badge['points_required'];
                $cls = $isActive ? 'active' : 'locked';
            ?>
            <div class="level-step <?= $cls ?>">
                <div class="level-icon"><?= $badge['icon'] ?></div>
                <div class="level-name"><?= $badge['name'] ?></div>
                <div class="level-pts"><?= number_format($badge['points_required']) ?> pts</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         QUICK STATS
         ═══════════════════════════════════════════════════════════ -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value"><?= number_format($stats['total_earned'] ?? 0) ?></div>
            <div class="stat-label">Points gagnes au total</div>
        </div>
        <div class="stat-card">
            <?php
            $unlockedAch = $stats['unlocked_achievements'] ?? [];
            $totalAch = 25;
            ?>
            <div class="stat-value"><?= count($unlockedAch) ?>/<?= $totalAch ?></div>
            <div class="stat-label">Succes debloques</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['active_days'] ?? 0 ?></div>
            <div class="stat-label">Jours actifs</div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         ACHIEVEMENTS / SUCCES
         ═══════════════════════════════════════════════════════════ -->
    <h2 class="section-title"><i class="fas fa-medal"></i> Succes</h2>
    <div class="achievements-grid">
        <?php
        $achievementsConfig = [
            // Avis
            ['slug' => 'first-review',    'name' => 'Premier Pas',        'icon' => '✍️', 'desc' => 'Publier son premier avis'],
            ['slug' => 'reviewer-5',      'name' => 'Assidu',             'icon' => '📝', 'desc' => 'Publier 5 avis'],
            ['slug' => 'reviewer-10',     'name' => 'Passionné',          'icon' => '🔥', 'desc' => 'Publier 10 avis'],
            ['slug' => 'prolific',        'name' => 'Prolifique',         'icon' => '🏆', 'desc' => 'Publier 20 avis'],
            ['slug' => 'reviewer-50',     'name' => 'Légende Culinaire',  'icon' => '👑', 'desc' => 'Publier 50 avis'],
            // Photos
            ['slug' => 'photographer',    'name' => 'Photographe',        'icon' => '📸', 'desc' => 'Ajouter 5 photos'],
            ['slug' => 'paparazzi',       'name' => 'Paparazzi',          'icon' => '🎬', 'desc' => 'Ajouter 25 photos'],
            ['slug' => 'reporter',        'name' => 'Reporter',           'icon' => '🎥', 'desc' => 'Ajouter 50 photos'],
            // Cuisines & Villes
            ['slug' => 'multicuisine',    'name' => 'Multicuisine',       'icon' => '🍜', 'desc' => 'Gouter 5 types de cuisine'],
            ['slug' => 'cuisine-master',  'name' => 'Gastronome',         'icon' => '🧑‍🍳', 'desc' => 'Gouter 10 types de cuisine'],
            ['slug' => 'globe-trotter',   'name' => 'Globe-trotter',      'icon' => '🌍', 'desc' => 'Visiter 3 villes'],
            ['slug' => 'nomad',           'name' => 'Nomade DZ',          'icon' => '🗺️', 'desc' => 'Visiter 10 villes'],
            // Votes utiles
            ['slug' => 'trusted-critic',  'name' => 'Critique Fiable',    'icon' => '💎', 'desc' => 'Recevoir 10 votes utiles'],
            ['slug' => 'influencer',      'name' => 'Influenceur',        'icon' => '🌟', 'desc' => 'Recevoir 50 votes utiles'],
            // Style d'avis
            ['slug' => 'long-writer',     'name' => 'Romancier',          'icon' => '📖', 'desc' => 'Publier 10 avis détaillés (200+ car.)'],
            ['slug' => 'five-star-fan',   'name' => 'Généreux',           'icon' => '⭐', 'desc' => 'Donner 10 notes de 5 étoiles'],
            ['slug' => 'critical-eye',    'name' => 'Oeil Critique',      'icon' => '🧐', 'desc' => 'Donner 5 notes sévères (1-2 étoiles)'],
            // Conseils
            ['slug' => 'first-tip',       'name' => 'Conseiller',         'icon' => '💡', 'desc' => 'Publier son premier conseil'],
            ['slug' => 'tip-master',      'name' => 'Mentor',             'icon' => '🎓', 'desc' => 'Publier 10 conseils'],
            // Collections
            ['slug' => 'first-collection','name' => 'Collectionneur',     'icon' => '📁', 'desc' => 'Créer sa première collection'],
            ['slug' => 'collector',       'name' => 'Curateur',           'icon' => '🗂️', 'desc' => 'Créer 5 collections'],
            // Check-ins
            ['slug' => 'first-checkin',   'name' => 'Présent !',          'icon' => '📍', 'desc' => 'Faire son premier check-in'],
            ['slug' => 'checkin-regular', 'name' => 'Habitué',            'icon' => '🏠', 'desc' => 'Faire 10 check-ins'],
            // Favoris
            ['slug' => 'wishlist-lover',  'name' => 'Gourmand',           'icon' => '❤️', 'desc' => 'Ajouter 10 restaurants aux favoris'],
            // Suggestions
            ['slug' => 'eclaireur',       'name' => 'Éclaireur',          'icon' => '🔦', 'desc' => 'Proposer 3 restaurants'],
        ];
        $unlockedSlugs = array_column($stats['unlocked_achievements'] ?? [], 'badge_slug');
        $unlockedMap = [];
        foreach ($stats['unlocked_achievements'] ?? [] as $ua) {
            $unlockedMap[$ua['badge_slug']] = $ua['unlocked_at'] ?? null;
        }
        foreach ($achievementsConfig as $ach):
            $isUnlocked = in_array($ach['slug'], $unlockedSlugs);
        ?>
        <div class="achievement-card <?= $isUnlocked ? '' : 'locked' ?>">
            <?php if ($isUnlocked): ?>
                <span class="achievement-check"><i class="fas fa-check"></i></span>
            <?php else: ?>
                <span class="achievement-lock"><i class="fas fa-lock"></i></span>
            <?php endif; ?>
            <span class="achievement-icon"><?= $ach['icon'] ?></span>
            <div class="achievement-name"><?= htmlspecialchars($ach['name']) ?></div>
            <div class="achievement-desc"><?= htmlspecialchars($ach['desc']) ?></div>
            <?php if ($isUnlocked && !empty($unlockedMap[$ach['slug']])): ?>
                <div class="achievement-date"><?= date('d/m/Y', strtotime($unlockedMap[$ach['slug']])) ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         TITRES PERSONNALISÉS
         ═══════════════════════════════════════════════════════════ -->
    <h2 class="section-title"><i class="fas fa-id-badge"></i> Mes Titres</h2>
    <?php $titles = $stats['titles'] ?? []; ?>
    <?php if (!empty($titles)): ?>
        <div class="titles-grid">
            <?php
            $primaryId = ($stats['primary_title']['id'] ?? null);
            foreach ($titles as $t):
                $isPrimary = ($t['id'] ?? null) === $primaryId;
            ?>
            <div class="title-chip <?= $isPrimary ? 'title-primary' : '' ?>" style="background:<?= htmlspecialchars($t['title_color']) ?>15;color:<?= htmlspecialchars($t['title_color']) ?>;border-color:<?= htmlspecialchars($t['title_color']) ?>40">
                <span class="t-icon"><?= $t['title_icon'] ?></span>
                <?= htmlspecialchars($t['title_label']) ?>
                <span class="t-date"><?= date('m/Y', strtotime($t['earned_at'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="titles-grid">
            <p class="titles-empty">Publiez des avis pour debloquer vos premiers titres personnalises !</p>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════
         MAIN GRID: Earn + History | Leaderboard
         ═══════════════════════════════════════════════════════════ -->
    <div class="loyalty-grid">
        <div>
            <!-- Comment gagner des points -->
            <div class="ly-card">
                <h3 class="ly-card-title"><i class="fas fa-coins"></i> Comment gagner des points</h3>
                <div class="earn-grid">
                    <div class="earn-item">
                        <div class="earn-icon" style="background:var(--ly-success)"><i class="fas fa-star"></i></div>
                        <div><div class="earn-action">Publier un avis</div><div class="earn-pts">+<?= $pointsConfig['review_posted'] ?? 10 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:var(--ly-blue)"><i class="fas fa-camera"></i></div>
                        <div><div class="earn-action">Ajouter une photo</div><div class="earn-pts">+<?= $pointsConfig['review_with_photo'] ?? 5 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:var(--ly-purple)"><i class="fas fa-align-left"></i></div>
                        <div><div class="earn-action">Avis detaille (+200 car.)</div><div class="earn-pts">+<?= $pointsConfig['review_long'] ?? 5 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:var(--ly-warning)"><i class="fas fa-thumbs-up"></i></div>
                        <div><div class="earn-action">Vote "utile" recu</div><div class="earn-pts">+<?= $pointsConfig['vote_received'] ?? 2 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:var(--ly-danger)"><i class="fas fa-heart"></i></div>
                        <div><div class="earn-action">Ajouter aux favoris</div><div class="earn-pts">+<?= $pointsConfig['wishlist_add'] ?? 1 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:#06b6d4"><i class="fas fa-map-marker-alt"></i></div>
                        <div><div class="earn-action">Check-in sur place</div><div class="earn-pts">+<?= $pointsConfig['checkin'] ?? 10 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:#ec4899"><i class="fas fa-user-plus"></i></div>
                        <div><div class="earn-action">Parrainer un ami</div><div class="earn-pts">+<?= $pointsConfig['referral'] ?? 50 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:#f59e0b"><i class="fas fa-lightbulb"></i></div>
                        <div><div class="earn-action">Proposer un restaurant</div><div class="earn-pts">+<?= $pointsConfig['suggestion'] ?? 10 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:var(--ly-text-light)"><i class="fas fa-sign-in-alt"></i></div>
                        <div><div class="earn-action">Connexion quotidienne</div><div class="earn-pts">+<?= $pointsConfig['daily_login'] ?? 1 ?> pts</div></div>
                    </div>
                    <div class="earn-item">
                        <div class="earn-icon" style="background:#10b981"><i class="fas fa-calendar-check"></i></div>
                        <div><div class="earn-action">1er avis du mois</div><div class="earn-pts">+<?= $pointsConfig['first_review_month'] ?? 10 ?> pts</div></div>
                    </div>
                </div>
            </div>

            <!-- Historique -->
            <div class="ly-card">
                <h3 class="ly-card-title"><i class="fas fa-history"></i> Historique recent</h3>
                <?php if (!empty($stats['history'])): ?>
                    <div class="history-list">
                        <?php foreach ($stats['history'] as $item):
                            $isPos = $item['points'] > 0;
                        ?>
                        <div class="history-item">
                            <div class="history-dot <?= $isPos ? 'positive' : 'negative' ?>">
                                <i class="fas fa-<?= $isPos ? 'plus' : 'minus' ?>"></i>
                            </div>
                            <div class="history-info">
                                <div class="history-desc"><?= htmlspecialchars($item['description']) ?></div>
                                <div class="history-date"><?= date('d/m/Y H:i', strtotime($item['created_at'])) ?></div>
                            </div>
                            <div class="history-pts <?= $isPos ? 'positive' : 'negative' ?>">
                                <?= $isPos ? '+' : '' ?><?= $item['points'] ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="ly-empty">
                        <i class="fas fa-inbox"></i>
                        <p>Aucune activite pour le moment.<br>Publiez votre premier avis pour gagner des points !</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Classement -->
            <div class="ly-card">
                <h3 class="ly-card-title"><i class="fas fa-trophy"></i> Classement</h3>
                <?php foreach ($leaderboard as $i => $leader):
                    $rc = $i === 0 ? 'r1' : ($i === 1 ? 'r2' : ($i === 2 ? 'r3' : 'rn'));
                    $isMe = $leader['id'] == $user['id'];
                ?>
                <div class="lb-item <?= $isMe ? 'me' : '' ?>">
                    <div class="lb-rank <?= $rc ?>"><?= $i + 1 ?></div>
                    <div class="lb-avatar">
                        <?php if (!empty($leader['photo_profil'])): ?>
                            <img src="<?= htmlspecialchars($leader['photo_profil']) ?>" alt="<?= htmlspecialchars($leader['prenom'] ?? '') ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($leader['prenom'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="lb-info">
                        <div class="lb-name">
                            <?= htmlspecialchars($leader['prenom'] . ' ' . substr($leader['nom'], 0, 1)) ?>.
                            <?= $isMe ? '(vous)' : '' ?>
                        </div>
                        <div class="lb-badge"><?= $leader['badge_icon'] ?? '🔍' ?> <?= $leader['badge'] ?></div>
                    </div>
                    <div class="lb-pts"><?= number_format($leader['points']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Avantages par niveau -->
            <div class="ly-card">
                <h3 class="ly-card-title"><i class="fas fa-unlock-alt"></i> Avantages par niveau</h3>
                <?php
                $perks = [
                    ['icon' => '🔍', 'name' => 'Explorateur',  'pts' => 0,    'perk' => 'Accès fidélité, succès et classement'],
                    ['icon' => '🍽️', 'name' => 'Gourmet',      'pts' => 150,  'perk' => 'Badge affiché sur vos avis'],
                    ['icon' => '🥇', 'name' => 'Connaisseur',  'pts' => 500,  'perk' => 'Badge coloré sur vos avis'],
                    ['icon' => '⭐', 'name' => 'Expert',       'pts' => 1200, 'perk' => 'Tag "Top contributeur" sur vos avis'],
                    ['icon' => '👑', 'name' => 'Ambassadeur',  'pts' => 2500, 'perk' => 'Bordure dorée sur vos avis'],
                    ['icon' => '🔱', 'name' => 'Legendaire',   'pts' => 5000, 'perk' => 'Bordure rouge + fond spécial sur vos avis'],
                ];
                $currentBadgeName = $stats['badge']['name'] ?? 'Explorateur';
                $reached = true;
                foreach ($perks as $perk):
                    if ($perk['name'] === $currentBadgeName) {
                        $state = 'current';
                    } elseif ($reached) {
                        $state = 'done';
                    } else {
                        $state = 'locked';
                    }
                    if ($state === 'current') $reached = false;
                ?>
                <div class="perk-item <?= $state ?>">
                    <span class="perk-icon"><?= $perk['icon'] ?></span>
                    <div class="perk-info">
                        <div class="perk-name"><?= $perk['name'] ?> <span style="font-weight:400;color:var(--ly-text-light);font-size:10px"><?= $perk['pts'] > 0 ? number_format($perk['pts']) . ' pts' : '' ?></span></div>
                        <div class="perk-desc"><?= $perk['perk'] ?></div>
                    </div>
                    <?php if ($state === 'done'): ?>
                        <i class="fas fa-check perk-check"></i>
                    <?php elseif ($state === 'current'): ?>
                        <i class="fas fa-arrow-left perk-current"></i>
                    <?php else: ?>
                        <i class="fas fa-lock perk-lock"></i>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
