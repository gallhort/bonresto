<?php
/**
 * Dashboard Stats détaillées par restaurant
 */

// Fonction timeAgo pour afficher le temps relatif
function timeAgo(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return floor($diff / 60) . ' min';
    if ($diff < 86400) return floor($diff / 3600) . ' h';
    if ($diff < 604800) return floor($diff / 86400) . ' j';
    
    return date('d/m', $time);
}

$eventLabels = [
    'view' => ['label' => 'Vues de page', 'icon' => 'fa-eye', 'color' => '#3b82f6'],
    'click_phone' => ['label' => 'Appels', 'icon' => 'fa-phone', 'color' => '#10b981'],
    'click_directions' => ['label' => 'Itinéraires', 'icon' => 'fa-map-marker-alt', 'color' => '#f59e0b'],
    'click_website' => ['label' => 'Site web', 'icon' => 'fa-globe', 'color' => '#8b5cf6'],
    'click_menu' => ['label' => 'Menu', 'icon' => 'fa-utensils', 'color' => '#ec4899'],
    'click_booking' => ['label' => 'Réservation', 'icon' => 'fa-calendar-check', 'color' => '#06b6d4'],
    'wishlist_add' => ['label' => 'Favoris', 'icon' => 'fa-heart', 'color' => '#ef4444'],
    'share' => ['label' => 'Partages', 'icon' => 'fa-share-alt', 'color' => '#6366f1'],
    'gallery_open' => ['label' => 'Galerie', 'icon' => 'fa-images', 'color' => '#84cc16'],
    'review_form_open' => ['label' => 'Form. avis ouvert', 'icon' => 'fa-edit', 'color' => '#f97316'],
    'review_submitted' => ['label' => 'Avis soumis', 'icon' => 'fa-star', 'color' => '#eab308'],
];

$sourceIcons = [
    'Direct' => 'fa-arrow-right',
    'Google' => 'fa-google',
    'Facebook' => 'fa-facebook',
    'Instagram' => 'fa-instagram',
    'Recherche interne' => 'fa-search',
    'Autre' => 'fa-link'
];

$sourceColors = [
    'Direct' => '#6366f1',
    'Google' => '#ea4335',
    'Facebook' => '#1877f2',
    'Instagram' => '#e4405f',
    'Recherche interne' => '#00635a',
    'Autre' => '#6b7280'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Statistiques') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #00635a;
            --primary-light: #00897b;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }
        
        /* ═══════════════════════════════════════════════════════════════
           HEADER
           ═══════════════════════════════════════════════════════════════ */
        .stats-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            padding: 0;
        }
        
        .header-top {
            padding: 16px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb a:hover { color: white; }
        .breadcrumb span { color: rgba(255,255,255,0.5); }
        
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .period-select {
            padding: 10px 16px;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 14px;
            cursor: pointer;
            backdrop-filter: blur(10px);
        }
        
        .period-select option { color: #333; }
        
        .btn-outline {
            padding: 10px 20px;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .btn-outline:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
        }
        
        .header-main {
            padding: 32px;
        }
        
        .restaurant-info {
            display: flex;
            align-items: flex-start;
            gap: 24px;
        }
        
        .restaurant-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.15);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            backdrop-filter: blur(10px);
        }
        
        .restaurant-details h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .restaurant-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .restaurant-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
        }
        
        .status-badge.validated { background: rgba(16, 185, 129, 0.3); }
        .status-badge.pending { background: rgba(245, 158, 11, 0.3); }
        
        /* ═══════════════════════════════════════════════════════════════
           CONTAINER
           ═══════════════════════════════════════════════════════════════ */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 32px;
        }
        
        /* ═══════════════════════════════════════════════════════════════
           KPI CARDS
           ═══════════════════════════════════════════════════════════════ */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .kpi-card {
            background: var(--card);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 4px 12px rgba(0,0,0,0.03);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .kpi-card.blue::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .kpi-card.green::before { background: linear-gradient(90deg, #10b981, #34d399); }
        .kpi-card.orange::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .kpi-card.purple::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
        .kpi-card.pink::before { background: linear-gradient(90deg, #ec4899, #f472b6); }
        .kpi-card.cyan::before { background: linear-gradient(90deg, #06b6d4, #22d3ee); }
        
        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .kpi-icon.blue { background: #eff6ff; color: #3b82f6; }
        .kpi-icon.green { background: #ecfdf5; color: #10b981; }
        .kpi-icon.orange { background: #fffbeb; color: #f59e0b; }
        .kpi-icon.purple { background: #f5f3ff; color: #8b5cf6; }
        .kpi-icon.pink { background: #fdf2f8; color: #ec4899; }
        .kpi-icon.cyan { background: #ecfeff; color: #06b6d4; }
        
        .kpi-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
        }
        
        .kpi-trend.up { background: #dcfce7; color: #16a34a; }
        .kpi-trend.down { background: #fee2e2; color: #dc2626; }
        .kpi-trend.neutral { background: #f1f5f9; color: #64748b; }
        
        .kpi-value {
            font-size: 36px;
            font-weight: 800;
            color: var(--text);
            line-height: 1.2;
            margin-bottom: 4px;
        }
        
        .kpi-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .kpi-sub {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed var(--border);
        }
        
        /* ═══════════════════════════════════════════════════════════════
           SECTIONS
           ═══════════════════════════════════════════════════════════════ */
        .section {
            background: var(--card);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid var(--border);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        /* ═══════════════════════════════════════════════════════════════
           GRID LAYOUTS
           ═══════════════════════════════════════════════════════════════ */
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        
        @media (max-width: 1024px) {
            .grid-3 { grid-template-columns: repeat(2, 1fr); }
        }
        
        @media (max-width: 768px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .container { padding: 16px; }
            .header-main { padding: 20px; }
            .restaurant-info { flex-direction: column; }
        }
        
        /* ═══════════════════════════════════════════════════════════════
           CHARTS
           ═══════════════════════════════════════════════════════════════ */
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .chart-container-small {
            position: relative;
            height: 200px;
        }
        
        /* ═══════════════════════════════════════════════════════════════
           ACTIONS GRID
           ═══════════════════════════════════════════════════════════════ */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 16px;
        }
        
        .action-item {
            background: var(--bg);
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        
        .action-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .action-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }
        
        .action-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 4px;
        }
        
        .action-label {
            font-size: 13px;
            color: var(--text-light);
            font-weight: 500;
        }
        
        /* ═══════════════════════════════════════════════════════════════
           TRAFFIC SOURCES
           ═══════════════════════════════════════════════════════════════ */
        .source-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .source-item {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .source-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .source-info {
            flex: 1;
            min-width: 0;
        }
        
        .source-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
        }
        
        .source-bar {
            height: 8px;
            background: var(--bg);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .source-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .source-stats {
            text-align: right;
            flex-shrink: 0;
        }
        
        .source-count {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }
        
        .source-percent {
            font-size: 12px;
            color: var(--text-light);
        }
        
        /* ═══════════════════════════════════════════════════════════════
           DEVICES
           ═══════════════════════════════════════════════════════════════ */
        .devices-grid {
            display: flex;
            justify-content: space-around;
            gap: 24px;
            padding: 20px 0;
        }
        
        .device-item {
            text-align: center;
            flex: 1;
        }
        
        .device-icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            position: relative;
        }
        
        .device-icon-wrapper.mobile {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #3b82f6;
        }
        
        .device-icon-wrapper.desktop {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            color: #10b981;
        }
        
        .device-icon-wrapper.tablet {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            color: #f59e0b;
        }
        
        .device-percent {
            font-size: 32px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 4px;
        }
        
        .device-label {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }
        
        .device-count {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 4px;
        }
        
        /* ═══════════════════════════════════════════════════════════════
           ACTIVITY FEED
           ═══════════════════════════════════════════════════════════════ */
        .activity-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        
        .activity-item:last-child { border-bottom: none; }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        .activity-type {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .activity-meta {
            font-size: 12px;
            color: var(--text-light);
            display: flex;
            gap: 12px;
        }
        
        .activity-time {
            font-size: 12px;
            color: var(--text-light);
            white-space: nowrap;
        }
        
        /* ═══════════════════════════════════════════════════════════════
           HOURLY HEATMAP
           ═══════════════════════════════════════════════════════════════ */
        .heatmap-grid {
            display: grid;
            grid-template-columns: repeat(24, 1fr);
            gap: 4px;
        }
        
        .heatmap-cell {
            aspect-ratio: 1;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 600;
            color: white;
            position: relative;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .heatmap-cell:hover {
            transform: scale(1.2);
            z-index: 10;
        }
        
        .heatmap-cell[data-level="0"] { background: #f1f5f9; color: #94a3b8; }
        .heatmap-cell[data-level="1"] { background: #bbf7d0; color: #166534; }
        .heatmap-cell[data-level="2"] { background: #86efac; color: #166534; }
        .heatmap-cell[data-level="3"] { background: #4ade80; color: white; }
        .heatmap-cell[data-level="4"] { background: #22c55e; color: white; }
        .heatmap-cell[data-level="5"] { background: #16a34a; color: white; }
        
        .heatmap-labels {
            display: grid;
            grid-template-columns: repeat(24, 1fr);
            gap: 4px;
            margin-top: 8px;
        }
        
        .heatmap-label {
            text-align: center;
            font-size: 10px;
            color: var(--text-light);
        }
        
        /* ═══════════════════════════════════════════════════════════════
           RESPONSIVE
           ═══════════════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .kpi-value { font-size: 28px; }
            .actions-grid { grid-template-columns: repeat(2, 1fr); }
            .heatmap-grid { gap: 2px; }
            .heatmap-cell { font-size: 8px; }
            .devices-grid { flex-direction: column; }
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════════════════
     HEADER
     ═══════════════════════════════════════════════════════════════════════ -->
<header class="stats-header">
    <div class="header-top">
        <nav class="breadcrumb">
            <a href="/"><i class="fas fa-home"></i></a>
            <span>/</span>
            <a href="/dashboard">Dashboard</a>
            <span>/</span>
            <span><?= htmlspecialchars($restaurant['nom']) ?></span>
        </nav>
        
        <div class="header-actions">
            <select class="period-select" onchange="changePeriod(this.value)">
                <option value="7" <?= $period == 7 ? 'selected' : '' ?>>7 derniers jours</option>
                <option value="30" <?= $period == 30 ? 'selected' : '' ?>>30 derniers jours</option>
                <option value="90" <?= $period == 90 ? 'selected' : '' ?>>90 derniers jours</option>
            </select>
            <a href="/restaurant/<?= $restaurant['id'] ?>" class="btn-outline" target="_blank">
                <i class="fas fa-external-link-alt"></i> Voir la fiche
            </a>
        </div>
    </div>
    
    <div class="header-main">
        <div class="restaurant-info">
            <div class="restaurant-icon">
                <i class="fas fa-store"></i>
            </div>
            <div class="restaurant-details">
                <h1><?= htmlspecialchars($restaurant['nom']) ?></h1>
                <div class="restaurant-meta">
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($restaurant['ville'] ?? 'N/A') ?></span>
                    <span><i class="fas fa-utensils"></i> <?= htmlspecialchars($restaurant['type_cuisine'] ?? 'N/A') ?></span>
                    <?php if ($restaurant['note_moyenne']): ?>
                    <span><i class="fas fa-star"></i> <?= number_format($restaurant['note_moyenne'], 1) ?>/5 (<?= $restaurant['nb_avis'] ?> avis)</span>
                    <?php endif; ?>
                    <span class="status-badge <?= $restaurant['status'] ?>">
                        <i class="fas fa-<?= $restaurant['status'] === 'validated' ? 'check-circle' : 'clock' ?>"></i>
                        <?= $restaurant['status'] === 'validated' ? 'Validé' : 'En attente' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container">
    
    <!-- ═══════════════════════════════════════════════════════════════════
         KPI CARDS
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="kpi-grid">
        <div class="kpi-card blue">
            <div class="kpi-header">
                <div class="kpi-icon blue"><i class="fas fa-eye"></i></div>
                <?php if (!empty($mainStats['views_trend'])): ?>
                <span class="kpi-trend <?= $mainStats['views_trend']['direction'] ?>">
                    <i class="fas fa-arrow-<?= $mainStats['views_trend']['direction'] === 'up' ? 'up' : ($mainStats['views_trend']['direction'] === 'down' ? 'down' : 'right') ?>"></i>
                    <?= $mainStats['views_trend']['value'] ?>%
                </span>
                <?php endif; ?>
            </div>
            <div class="kpi-value"><?= number_format($mainStats['views'] ?? 0) ?></div>
            <div class="kpi-label">Vues totales</div>
            <div class="kpi-sub"><i class="fas fa-chart-line"></i> ~<?= $mainStats['avg_views_per_day'] ?? 0 ?> vues/jour</div>
        </div>
        
        <div class="kpi-card green">
            <div class="kpi-header">
                <div class="kpi-icon green"><i class="fas fa-users"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($mainStats['unique_visitors'] ?? 0) ?></div>
            <div class="kpi-label">Visiteurs uniques</div>
            <div class="kpi-sub"><i class="fas fa-user-check"></i> Sessions distinctes</div>
        </div>
        
        <div class="kpi-card orange">
            <div class="kpi-header">
                <div class="kpi-icon orange"><i class="fas fa-hand-pointer"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($mainStats['total_interactions'] ?? 0) ?></div>
            <div class="kpi-label">Interactions</div>
            <div class="kpi-sub"><i class="fas fa-phone"></i> Appels, itinéraires, site web</div>
        </div>
        
        <div class="kpi-card purple">
            <div class="kpi-header">
                <div class="kpi-icon purple"><i class="fas fa-percentage"></i></div>
            </div>
            <div class="kpi-value"><?= $mainStats['conversion_rate'] ?? 0 ?>%</div>
            <div class="kpi-label">Taux de conversion</div>
            <div class="kpi-sub"><i class="fas fa-funnel-dollar"></i> Interactions / Vues</div>
        </div>
        
        <div class="kpi-card pink">
            <div class="kpi-header">
                <div class="kpi-icon pink"><i class="fas fa-phone"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($mainStats['actions']['clicks_phone'] ?? 0) ?></div>
            <div class="kpi-label">Appels téléphone</div>
            <div class="kpi-sub"><i class="fas fa-arrow-right"></i> Clients potentiels</div>
        </div>
        
        <div class="kpi-card cyan">
            <div class="kpi-header">
                <div class="kpi-icon cyan"><i class="fas fa-directions"></i></div>
            </div>
            <div class="kpi-value"><?= number_format($mainStats['actions']['clicks_directions'] ?? 0) ?></div>
            <div class="kpi-label">Itinéraires</div>
            <div class="kpi-sub"><i class="fas fa-map-marked-alt"></i> Intentions de visite</div>
        </div>
    </div>
    
    <!-- ═══════════════════════════════════════════════════════════════════
         GRAPHIQUE PRINCIPAL
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-chart-area"></i> Évolution du trafic</h2>
        </div>
        <div class="chart-container">
            <canvas id="mainChart"></canvas>
        </div>
    </div>
    
    <div class="grid-2">
        <!-- ═══════════════════════════════════════════════════════════════
             HEURES POPULAIRES
             ═══════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-clock"></i> Heures populaires</h2>
            </div>
            <div class="heatmap-grid">
                <?php 
                $maxHourly = max($hourlyStats) ?: 1;
                foreach ($hourlyStats as $hour => $count): 
                    $level = $count == 0 ? 0 : min(5, ceil(($count / $maxHourly) * 5));
                ?>
                <div class="heatmap-cell" data-level="<?= $level ?>" title="<?= $hour ?>h: <?= $count ?> vues">
                    <?= $count ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="heatmap-labels">
                <?php for ($h = 0; $h < 24; $h++): ?>
                <div class="heatmap-label"><?= $h ?>h</div>
                <?php endfor; ?>
            </div>
        </div>
        
        <!-- ═══════════════════════════════════════════════════════════════
             JOURS DE LA SEMAINE
             ═══════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-calendar-week"></i> Jours de la semaine</h2>
            </div>
            <div class="chart-container-small">
                <canvas id="weekdayChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- ═══════════════════════════════════════════════════════════════════
         ACTIONS DÉTAILLÉES
         ═══════════════════════════════════════════════════════════════════ -->
    <div class="section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-hand-pointer"></i> Détail des actions</h2>
        </div>
        <div class="actions-grid">
            <?php foreach ($actionsStats as $action): 
                $info = $eventLabels[$action['event_type']] ?? ['label' => $action['event_type'], 'icon' => 'fa-circle', 'color' => '#6b7280'];
            ?>
            <div class="action-item">
                <div class="action-icon" style="background: <?= $info['color'] ?>">
                    <i class="fas <?= $info['icon'] ?>"></i>
                </div>
                <div class="action-value"><?= number_format($action['count']) ?></div>
                <div class="action-label"><?= $info['label'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="grid-3">
        <!-- ═══════════════════════════════════════════════════════════════
             SOURCES DE TRAFIC
             ═══════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-globe"></i> Sources de trafic</h2>
            </div>
            <div class="source-list">
                <?php 
                $totalTraffic = array_sum(array_column($trafficSources, 'count'));
                foreach ($trafficSources as $source): 
                    $percent = $totalTraffic > 0 ? round(($source['count'] / $totalTraffic) * 100) : 0;
                    $icon = $sourceIcons[$source['source']] ?? 'fa-link';
                    $color = $sourceColors[$source['source']] ?? '#6b7280';
                ?>
                <div class="source-item">
                    <div class="source-icon" style="background: <?= $color ?>">
                        <i class="fab <?= $icon ?>"></i>
                    </div>
                    <div class="source-info">
                        <div class="source-name"><?= htmlspecialchars($source['source']) ?></div>
                        <div class="source-bar">
                            <div class="source-bar-fill" style="width: <?= $percent ?>%; background: <?= $color ?>"></div>
                        </div>
                    </div>
                    <div class="source-stats">
                        <div class="source-count"><?= number_format($source['count']) ?></div>
                        <div class="source-percent"><?= $percent ?>%</div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- ═══════════════════════════════════════════════════════════════
             APPAREILS
             ═══════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-mobile-alt"></i> Appareils</h2>
            </div>
            <div class="devices-grid">
                <?php 
                $deviceIcons = ['mobile' => 'fa-mobile-alt', 'desktop' => 'fa-desktop', 'tablet' => 'fa-tablet-alt'];
                $totalDevices = array_sum(array_column($deviceStats, 'count'));
                foreach (['mobile', 'desktop', 'tablet'] as $device):
                    $found = array_filter($deviceStats, fn($d) => $d['device'] === $device);
                    $count = !empty($found) ? array_values($found)[0]['count'] : 0;
                    $percent = $totalDevices > 0 ? round(($count / $totalDevices) * 100) : 0;
                ?>
                <div class="device-item">
                    <div class="device-icon-wrapper <?= $device ?>">
                        <i class="fas <?= $deviceIcons[$device] ?>"></i>
                    </div>
                    <div class="device-percent"><?= $percent ?>%</div>
                    <div class="device-label"><?= ucfirst($device) ?></div>
                    <div class="device-count"><?= number_format($count) ?> visites</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- ═══════════════════════════════════════════════════════════════
             ACTIVITÉ RÉCENTE
             ═══════════════════════════════════════════════════════════════ -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-stream"></i> Activité récente</h2>
            </div>
            <div class="activity-list">
                <?php foreach ($recentActivity as $activity): 
                    $info = $eventLabels[$activity['event_type']] ?? ['label' => $activity['event_type'], 'icon' => 'fa-circle', 'color' => '#6b7280'];
$timeAgo = timeAgo($activity['created_at'] ?? 'now');                ?>
                <div class="activity-item">
                    <div class="activity-icon" style="background: <?= $info['color'] ?>">
                        <i class="fas <?= $info['icon'] ?>"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-type"><?= $info['label'] ?></div>
                        <div class="activity-meta">
                            <span><i class="fas fa-<?= ($activity['device_type'] ?? 'desktop') === 'mobile' ? 'mobile-alt' : 'desktop' ?>"></i> <?= ucfirst($activity['device_type'] ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="activity-time"><?= $timeAgo ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
</div>

<script>
function changePeriod(period) {
    window.location.href = '/dashboard/restaurant/<?= $restaurant['id'] ?>?period=' + period;
}

// ═══════════════════════════════════════════════════════════════════════════
// GRAPHIQUE PRINCIPAL
// ═══════════════════════════════════════════════════════════════════════════
const mainCtx = document.getElementById('mainChart').getContext('2d');
new Chart(mainCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartData['labels']) ?>,
        datasets: [
            {
                label: 'Vues',
                data: <?= json_encode($chartData['views']) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            },
            {
                label: 'Visiteurs uniques',
                data: <?= json_encode($chartData['visitors']) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            },
            {
                label: 'Clics',
                data: <?= json_encode($chartData['clicks']) ?>,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: { usePointStyle: true, padding: 20 }
            }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        },
        interaction: { intersect: false, mode: 'index' }
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// GRAPHIQUE JOURS DE LA SEMAINE
// ═══════════════════════════════════════════════════════════════════════════
const weekCtx = document.getElementById('weekdayChart').getContext('2d');
new Chart(weekCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($weekdayStats['labels']) ?>,
        datasets: [{
            label: 'Vues',
            data: <?= json_encode($weekdayStats['data']) ?>,
            backgroundColor: [
                'rgba(239, 68, 68, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(6, 182, 212, 0.8)'
            ],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});
</script>

</body>
</html>