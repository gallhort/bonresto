<?php
function buildActivityUrl($filters, $param, $value) {
    $f = $filters;
    if ($value === '' || $value === null || $value === 'all') unset($f[$param]);
    else $f[$param] = $value;
    if ($param !== 'page') unset($f['page']);
    return '/activites?' . http_build_query($f);
}

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
$priceLabels = ['gratuit' => 'Gratuit', 'pas_cher' => 'Pas cher', 'moyen' => 'Moyen', 'cher' => 'Cher'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Activités & Sorties' ?></title>
    <meta name="description" content="Découvrez les meilleures activités et sorties en Algérie : plages, parcs, monuments, musées et plus encore. <?= $total ?> lieux à explorer.">
    <meta property="og:title" content="<?= htmlspecialchars($title ?? 'Activités & Sorties') ?>">
    <meta property="og:description" content="Explorez <?= $total ?> activités et sorties à travers l'Algérie.">
    <meta property="og:type" content="website">
    <link rel="canonical" href="<?= htmlspecialchars(strtok($_SERVER['REQUEST_URI'] ?? '/activites', '?') . (!empty($_GET) ? '?' . http_build_query($_GET) : '')) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00635a;
            --primary-dark: #004d44;
            --primary-light: #e8f5f3;
            --accent: #f59e0b;
            --dark: #1a1a1a;
            --gray-900: #2d2d2d;
            --gray-700: #555;
            --gray-500: #888;
            --gray-300: #d0d0d0;
            --gray-200: #e8e8e8;
            --gray-100: #f4f4f4;
            --white: #fff;
            --font-serif: 'DM Serif Display', Georgia, serif;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-sans); background: var(--gray-100); color: var(--gray-900); }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }

        /* ═══════ LAYOUT ═══════ */
        .page-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: calc(100vh - 64px);
            margin-top: 64px;
        }
        .page-list {
            overflow-y: auto;
            padding: 24px;
        }
        .page-map {
            position: sticky;
            top: 64px;
            height: calc(100vh - 64px);
        }
        .page-map #actMap { width: 100%; height: 100%; }

        @media (max-width: 1200px) {
            .page-layout { grid-template-columns: 1fr 45%; }
        }
        @media (max-width: 900px) {
            .page-layout { grid-template-columns: 1fr; height: auto; margin-top: 56px; }
            .page-map { display: none; }
        }

        /* ═══════ TOP BAR ═══════ */
        .top-bar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex; align-items: center; gap: 12px;
            padding: 12px 24px; height: 64px;
        }
        .top-bar-logo { font-size: 24px; text-decoration: none; }
        .top-bar-search {
            flex: 1; display: flex; align-items: center; gap: 8px;
            background: var(--gray-100); border-radius: 24px; padding: 8px 16px;
            max-width: 480px;
        }
        .top-bar-search i { color: var(--gray-500); font-size: 14px; }
        .top-bar-search input {
            flex: 1; border: none; background: none; font-size: 14px; outline: none;
            font-family: var(--font-sans);
        }
        .top-bar-back {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; color: var(--primary); font-weight: 600;
        }

        @media (max-width: 900px) {
            .top-bar { height: 56px; padding: 8px 16px; }
        }

        /* ═══════ FILTERS ═══════ */
        .filters-bar {
            display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;
        }
        .filter-chip {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;
            background: white; border: 1px solid var(--gray-200); color: var(--gray-700);
            cursor: pointer; transition: all 0.2s; white-space: nowrap;
        }
        .filter-chip:hover { border-color: var(--primary); color: var(--primary); }
        .filter-chip.active { background: var(--primary); color: white; border-color: var(--primary); }
        .filter-chip i { font-size: 12px; }

        .filter-dropdown {
            position: relative; display: inline-block;
        }
        .filter-dropdown-menu {
            display: none; position: absolute; top: calc(100% + 4px); left: 0;
            background: white; border-radius: var(--radius); box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200); z-index: 50; min-width: 160px;
            max-height: 300px; overflow-y: auto;
        }
        .filter-dropdown-menu.show { display: block; }
        .filter-dropdown-menu a {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 16px; font-size: 13px; color: var(--gray-700);
            transition: background 0.15s;
        }
        .filter-dropdown-menu a:hover { background: var(--gray-100); }
        .filter-dropdown-menu a.active { color: var(--primary); font-weight: 600; }

        /* ═══════ RESULTS HEADER ═══════ */
        .results-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 16px;
        }
        .results-count { font-size: 15px; color: var(--gray-700); }
        .results-count strong { color: var(--gray-900); }
        .sort-select {
            padding: 6px 12px; border: 1px solid var(--gray-200); border-radius: var(--radius-sm);
            font-size: 13px; background: white; cursor: pointer; font-family: var(--font-sans);
        }

        /* ═══════ CARDS GRID ═══════ */
        .act-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
        }

        .act-card {
            background: white; border-radius: var(--radius); overflow: hidden;
            box-shadow: var(--shadow-sm); transition: all 0.25s; cursor: pointer;
        }
        .act-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }

        .act-card-image {
            position: relative; height: 170px; overflow: hidden; background: var(--gray-100);
        }
        .act-card-image img {
            width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;
        }
        .act-card:hover .act-card-image img { transform: scale(1.05); }
        .act-card-noimg {
            width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: var(--gray-300);
        }
        .act-card-category {
            position: absolute; top: 10px; left: 10px;
            background: rgba(0,0,0,0.6); color: white; padding: 4px 10px;
            border-radius: 12px; font-size: 11px; font-weight: 600;
            backdrop-filter: blur(4px);
        }
        .act-card-category i { margin-right: 4px; }
        .act-card-free {
            position: absolute; top: 10px; right: 10px;
            background: #10b981; color: white; padding: 4px 10px;
            border-radius: 12px; font-size: 11px; font-weight: 700;
        }

        .act-card-body { padding: 14px; }
        .act-card-name {
            font-size: 15px; font-weight: 700; color: var(--gray-900);
            line-height: 1.3; margin-bottom: 6px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .act-card-location {
            font-size: 13px; color: var(--gray-500); margin-bottom: 8px;
            display: flex; align-items: center; gap: 4px;
        }
        .act-card-location i { font-size: 11px; color: var(--primary); }
        .act-card-price { margin-left: auto; font-weight: 600; color: var(--primary); }

        .act-card-bottom { display: flex; align-items: center; }
        .act-card-rating { display: flex; align-items: center; gap: 6px; }
        .rating-pill {
            background: var(--primary); color: white; padding: 3px 8px;
            border-radius: 6px; font-size: 13px; font-weight: 700;
        }
        .rating-count { font-size: 12px; color: var(--gray-500); }
        .act-card-no-rating { font-size: 12px; color: var(--gray-500); font-style: italic; }

        /* ═══════ PAGINATION ═══════ */
        .pagination {
            display: flex; justify-content: center; gap: 4px; margin-top: 32px;
        }
        .pagination a, .pagination span {
            display: flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 10px;
            border-radius: var(--radius-sm); font-size: 14px; font-weight: 500;
            border: 1px solid var(--gray-200); background: white; color: var(--gray-700);
            transition: all 0.2s;
        }
        .pagination a:hover { border-color: var(--primary); color: var(--primary); }
        .pagination .current {
            background: var(--primary); color: white; border-color: var(--primary);
        }

        /* ═══════ ACTIVE FILTERS BAR ═══════ */
        .active-filters {
            display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
            margin-bottom: 16px; padding: 10px 14px;
            background: var(--primary-light); border-radius: var(--radius-sm);
        }
        .active-filters-label { font-size: 12px; font-weight: 600; color: var(--primary-dark); text-transform: uppercase; letter-spacing: 0.5px; }
        .active-filter-tag {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; background: white; border-radius: 16px;
            font-size: 12px; font-weight: 500; color: var(--gray-900);
            border: 1px solid var(--primary); transition: all 0.15s;
        }
        .active-filter-tag:hover { background: #fef2f2; border-color: #ef4444; }
        .active-filter-tag i { font-size: 10px; color: var(--gray-500); cursor: pointer; }
        .active-filter-tag i:hover { color: #ef4444; }
        .active-filters-clear {
            margin-left: auto; font-size: 12px; color: var(--primary);
            font-weight: 600; cursor: pointer; white-space: nowrap;
        }
        .active-filters-clear:hover { text-decoration: underline; }

        /* ═══════ EMPTY STATE ═══════ */
        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--gray-500);
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; color: var(--gray-300); }
        .empty-state h3 { font-size: 18px; color: var(--gray-700); margin-bottom: 8px; }
        .empty-state .reset-link {
            display: inline-block; margin-top: 12px; padding: 8px 20px;
            background: var(--primary); color: white; border-radius: 20px;
            font-size: 13px; font-weight: 600;
        }

        /* ═══════ MOBILE MAP TOGGLE ═══════ */
        .map-toggle-btn {
            display: none; position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
            z-index: 90; padding: 12px 24px; border-radius: 24px; border: none;
            background: var(--dark); color: white; font-size: 14px; font-weight: 600;
            cursor: pointer; box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            font-family: var(--font-sans);
        }
        .map-toggle-btn i { margin-right: 6px; }
        @media (max-width: 900px) {
            .map-toggle-btn { display: flex; align-items: center; }
            .page-map.mobile-show { display: block !important; position: fixed; top: 56px; left: 0; right: 0; bottom: 60px; z-index: 80; height: auto; }
        }

        /* ═══════ MAP LEGEND ═══════ */
        .map-legend {
            position: absolute; bottom: 10px; left: 10px; z-index: 1000;
            background: white; border-radius: var(--radius-sm); padding: 10px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 11px; max-height: 200px; overflow-y: auto;
        }
        .map-legend-title { font-weight: 700; font-size: 12px; margin-bottom: 6px; color: var(--gray-900); }
        .map-legend-item { display: flex; align-items: center; gap: 6px; padding: 2px 0; }
        .map-legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }

        /* ═══════ CARD HIGHLIGHT ═══════ */
        .act-card.map-highlight {
            outline: 2px solid var(--primary);
            outline-offset: -2px;
            box-shadow: 0 0 0 4px rgba(0,99,90,0.15), var(--shadow-md);
        }

        /* ═══════ MAP MARKERS ═══════ */
        .act-marker {
            width: 38px; height: 38px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
            border: 2.5px solid white;
            animation: markerFade 0.3s ease-out both;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .act-marker i {
            transform: rotate(45deg);
            font-size: 14px; color: white;
        }
        .act-marker.highlighted {
            background: var(--accent) !important;
            transform: rotate(-45deg) scale(1.3);
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.5);
            z-index: 1000 !important;
        }
        @keyframes markerFade { from { opacity: 0; transform: rotate(-45deg) scale(0.5); } to { opacity: 1; transform: rotate(-45deg) scale(1); } }

        /* ═══════ DYNAMIC SEARCH TOGGLE ═══════ */
        .map-search-toggle {
            position: absolute; top: 12px; left: 50%; transform: translateX(-50%);
            z-index: 1000; background: white; border-radius: 24px;
            padding: 8px 18px; font-size: 13px; font-weight: 500;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15); cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            user-select: none; transition: all 0.2s; white-space: nowrap;
            font-family: var(--font-sans); color: var(--gray-700);
        }
        .map-search-toggle:hover { box-shadow: 0 3px 14px rgba(0,0,0,0.2); }
        .map-search-toggle.active { background: var(--primary); color: white; }
        .map-search-toggle.loading { opacity: 0.7; pointer-events: none; }
        .map-search-toggle .toggle-dot {
            width: 18px; height: 18px; border-radius: 50%;
            border: 2px solid var(--gray-300); transition: all 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .map-search-toggle.active .toggle-dot {
            background: white; border-color: white;
        }
        .map-search-toggle.active .toggle-dot::after {
            content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
            font-size: 10px; color: var(--primary);
        }
        .map-search-toggle .spinner {
            display: none; width: 14px; height: 14px;
            border: 2px solid rgba(255,255,255,0.3); border-top-color: white;
            border-radius: 50%; animation: spin 0.6s linear infinite;
        }
        .map-search-toggle.loading .spinner { display: block; }
        .map-search-toggle.loading .toggle-dot { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .dynamic-results-count {
            font-size: 14px; color: var(--gray-700); font-weight: 500;
            margin-bottom: 12px; padding: 8px 0;
            display: none;
        }
        .dynamic-results-count.visible { display: block; }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar" role="banner">
    <a href="/" class="top-bar-logo" aria-label="Accueil LeBonResto"><svg width="28" height="28" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#00635a"/><path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M28 12v6c0 3 2 5 4 5v-11" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 23v13" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></a>
    <a href="/" class="top-bar-back"><i class="fas fa-arrow-left"></i> Accueil</a>
    <form class="top-bar-search" action="/activites" method="GET" role="search" aria-label="Rechercher des activités">
        <i class="fas fa-search" aria-hidden="true"></i>
        <input type="text" name="q" placeholder="Rechercher une activité, un lieu..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>" aria-label="Rechercher une activité">
        <?php if (!empty($filters['ville'])): ?><input type="hidden" name="ville" value="<?= htmlspecialchars($filters['ville']) ?>"><?php endif; ?>
        <?php if (!empty($filters['category'])): ?><input type="hidden" name="category" value="<?= htmlspecialchars($filters['category']) ?>"><?php endif; ?>
    </form>
</div>

<div class="page-layout">
    <!-- LIST -->
    <div class="page-list">
        <!-- Filters -->
        <div class="filters-bar">
            <!-- Ville dropdown -->
            <div class="filter-dropdown">
                <button class="filter-chip <?= !empty($filters['ville']) ? 'active' : '' ?>" onclick="toggleDropdown(this)">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= !empty($filters['ville']) ? htmlspecialchars($filters['ville']) : 'Ville' ?>
                    <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <div class="filter-dropdown-menu">
                    <a href="<?= buildActivityUrl($filters, 'ville', '') ?>" class="<?= empty($filters['ville']) ? 'active' : '' ?>">Toutes les villes</a>
                    <?php foreach ($villes as $v): ?>
                        <a href="<?= buildActivityUrl($filters, 'ville', $v) ?>" class="<?= ($filters['ville'] ?? '') === $v ? 'active' : '' ?>">
                            <?= htmlspecialchars($v) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Category chips -->
            <a href="<?= buildActivityUrl($filters, 'category', '') ?>" class="filter-chip <?= empty($filters['category']) ? 'active' : '' ?>">Tout</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= buildActivityUrl($filters, 'category', $cat) ?>" class="filter-chip <?= ($filters['category'] ?? '') === $cat ? 'active' : '' ?>">
                    <i class="fas <?= $categoryIcons[$cat] ?? 'fa-map-pin' ?>"></i>
                    <?= $categoryLabels[$cat] ?? ucfirst($cat) ?>
                </a>
            <?php endforeach; ?>

            <!-- Price dropdown -->
            <div class="filter-dropdown">
                <button class="filter-chip <?= !empty($filters['price']) ? 'active' : '' ?>" onclick="toggleDropdown(this)">
                    <i class="fas fa-tag"></i>
                    <?= !empty($filters['price']) ? ($priceLabels[$filters['price']] ?? 'Prix') : 'Prix' ?>
                    <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <div class="filter-dropdown-menu">
                    <a href="<?= buildActivityUrl($filters, 'price', '') ?>">Tous les prix</a>
                    <?php foreach ($priceLabels as $k => $v): ?>
                        <a href="<?= buildActivityUrl($filters, 'price', $k) ?>" class="<?= ($filters['price'] ?? '') === $k ? 'active' : '' ?>">
                            <?= $v ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Rating filter -->
            <div class="filter-dropdown">
                <button class="filter-chip <?= !empty($filters['rating']) ? 'active' : '' ?>" onclick="toggleDropdown(this)">
                    <i class="fas fa-star"></i> Note
                    <i class="fas fa-chevron-down" style="font-size:10px;"></i>
                </button>
                <div class="filter-dropdown-menu">
                    <a href="<?= buildActivityUrl($filters, 'rating', '') ?>">Toutes notes</a>
                    <a href="<?= buildActivityUrl($filters, 'rating', '4') ?>" class="<?= ($filters['rating'] ?? '') == '4' ? 'active' : '' ?>">4+ Excellent</a>
                    <a href="<?= buildActivityUrl($filters, 'rating', '3') ?>" class="<?= ($filters['rating'] ?? '') == '3' ? 'active' : '' ?>">3+ Bien</a>
                    <a href="<?= buildActivityUrl($filters, 'rating', '2') ?>" class="<?= ($filters['rating'] ?? '') == '2' ? 'active' : '' ?>">2+ Correct</a>
                </div>
            </div>
        </div>

        <!-- Active filters summary -->
        <?php
        $hasActiveFilters = !empty($filters['q']) || !empty($filters['ville']) || !empty($filters['category']) || !empty($filters['price']) || !empty($filters['rating']);
        ?>
        <?php if ($hasActiveFilters): ?>
            <div class="active-filters">
                <span class="active-filters-label">Filtres actifs</span>
                <?php if (!empty($filters['q'])): ?>
                    <a href="<?= buildActivityUrl($filters, 'q', '') ?>" class="active-filter-tag">
                        « <?= htmlspecialchars($filters['q']) ?> » <i class="fas fa-times" aria-label="Supprimer"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($filters['ville'])): ?>
                    <a href="<?= buildActivityUrl($filters, 'ville', '') ?>" class="active-filter-tag">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i> <?= htmlspecialchars($filters['ville']) ?> <i class="fas fa-times" aria-label="Supprimer"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($filters['category'])): ?>
                    <a href="<?= buildActivityUrl($filters, 'category', '') ?>" class="active-filter-tag">
                        <i class="fas <?= $categoryIcons[$filters['category']] ?? 'fa-tag' ?>" aria-hidden="true"></i> <?= $categoryLabels[$filters['category']] ?? $filters['category'] ?> <i class="fas fa-times" aria-label="Supprimer"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($filters['price'])): ?>
                    <a href="<?= buildActivityUrl($filters, 'price', '') ?>" class="active-filter-tag">
                        <?= $priceLabels[$filters['price']] ?? $filters['price'] ?> <i class="fas fa-times" aria-label="Supprimer"></i>
                    </a>
                <?php endif; ?>
                <?php if (!empty($filters['rating'])): ?>
                    <a href="<?= buildActivityUrl($filters, 'rating', '') ?>" class="active-filter-tag">
                        <?= $filters['rating'] ?>+ <i class="fas fa-star" style="font-size:9px;color:var(--accent);" aria-hidden="true"></i> <i class="fas fa-times" aria-label="Supprimer"></i>
                    </a>
                <?php endif; ?>
                <a href="/activites" class="active-filters-clear">Tout effacer</a>
            </div>
        <?php endif; ?>

        <!-- Results header -->
        <div class="results-header">
            <div class="results-count">
                <strong><?= $total ?></strong> activité<?= $total > 1 ? 's' : '' ?>
                <?php if (!empty($filters['ville'])): ?> à <?= htmlspecialchars($filters['ville']) ?><?php endif; ?>
                <?php if (!empty($filters['category'])): ?> — <?= $categoryLabels[$filters['category']] ?? $filters['category'] ?><?php endif; ?>
            </div>
            <select class="sort-select" onchange="window.location.href=this.value" aria-label="Trier les résultats">
                <option value="<?= buildActivityUrl($filters, 'sort', 'popular') ?>" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Populaires</option>
                <option value="<?= buildActivityUrl($filters, 'sort', 'rating') ?>" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Mieux notés</option>
                <option value="<?= buildActivityUrl($filters, 'sort', 'newest') ?>" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Récents</option>
                <option value="<?= buildActivityUrl($filters, 'sort', 'name') ?>" <?= ($filters['sort'] ?? '') === 'name' ? 'selected' : '' ?>>A-Z</option>
            </select>
        </div>

        <!-- Dynamic results count (shown when map search is active) -->
        <div class="dynamic-results-count" id="dynamicResultsCount"></div>

        <!-- Cards Grid -->
        <?php if (empty($activities)): ?>
            <div class="empty-state">
                <i class="fas fa-binoculars"></i>
                <h3>Aucune activité trouvée</h3>
                <p>Essayez d'autres filtres ou une autre ville.</p>
                <?php if ($hasActiveFilters): ?>
                    <a href="/activites" class="reset-link">Réinitialiser les filtres</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="act-grid">
                <?php foreach ($activities as $act): ?>
                    <?php include __DIR__ . '/_card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($pagination && $pagination['totalPages'] > 1): ?>
            <nav class="pagination" aria-label="Pagination des résultats">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="<?= buildActivityUrl($filters, 'page', $pagination['page'] - 1) ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>

                <?php
                $start = max(1, $pagination['page'] - 2);
                $end   = min($pagination['totalPages'], $pagination['page'] + 2);
                for ($p = $start; $p <= $end; $p++):
                ?>
                    <?php if ($p == $pagination['page']): ?>
                        <span class="current"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= buildActivityUrl($filters, 'page', $p) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                    <a href="<?= buildActivityUrl($filters, 'page', $pagination['page'] + 1) ?>" aria-label="Page suivante"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>

    <!-- MAP -->
    <div class="page-map" id="pageMap">
        <div id="actMap"></div>
        <div class="map-search-toggle" id="dynamicSearchToggle">
            <span class="toggle-dot"></span>
            <span class="spinner"></span>
            Rechercher quand je déplace la carte
        </div>
    </div>
</div>

<!-- Mobile map toggle -->
<button class="map-toggle-btn" id="mapToggle" aria-label="Afficher la carte">
    <i class="fas fa-map"></i> Carte
</button>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ═══════ FILTER DROPDOWNS ═══════
function toggleDropdown(btn) {
    var menu = btn.nextElementSibling;
    document.querySelectorAll('.filter-dropdown-menu.show').forEach(function(m) {
        if (m !== menu) m.classList.remove('show');
    });
    menu.classList.toggle('show');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.filter-dropdown')) {
        document.querySelectorAll('.filter-dropdown-menu.show').forEach(function(m) { m.classList.remove('show'); });
    }
});

// ═══════ MOBILE MAP TOGGLE ═══════
(function() {
    var toggleBtn = document.getElementById('mapToggle');
    var mapPane = document.getElementById('pageMap');
    var showing = false;
    if (toggleBtn && mapPane) {
        toggleBtn.addEventListener('click', function() {
            showing = !showing;
            mapPane.classList.toggle('mobile-show', showing);
            toggleBtn.innerHTML = showing
                ? '<i class="fas fa-list"></i> Liste'
                : '<i class="fas fa-map"></i> Carte';
            if (showing && window.actMap) window.actMap.invalidateSize();
        });
    }
})();

// ═══════ LEAFLET MAP — DYNAMIC (Airbnb-style) ═══════
var mapData = <?= json_encode($mapActivities ?? [], JSON_HEX_TAG | JSON_HEX_AMP) ?>;

var catStyles = {
    plage:          { icon: 'fa-umbrella-beach',   color: '#0ea5e9', label: 'Plage' },
    parc:           { icon: 'fa-tree',             color: '#22c55e', label: 'Parc' },
    monument:       { icon: 'fa-landmark',         color: '#8b5cf6', label: 'Monument' },
    musee:          { icon: 'fa-building-columns', color: '#a855f7', label: 'Musée' },
    shopping:       { icon: 'fa-bag-shopping',     color: '#f43f5e', label: 'Shopping' },
    divertissement: { icon: 'fa-masks-theater',    color: '#f59e0b', label: 'Divertissement' },
    nightlife:      { icon: 'fa-moon',             color: '#6366f1', label: 'Vie nocturne' },
    cafe:           { icon: 'fa-mug-hot',          color: '#d97706', label: 'Café' },
    nature:         { icon: 'fa-mountain-sun',     color: '#16a34a', label: 'Nature' },
    sport:          { icon: 'fa-futbol',           color: '#ef4444', label: 'Sport' },
    religieux:      { icon: 'fa-mosque',           color: '#0d9488', label: 'Religieux' },
    culturel:       { icon: 'fa-palette',          color: '#ec4899', label: 'Culturel' },
};
var defaultStyle = { icon: 'fa-map-pin', color: '#00635a', label: 'Lieu' };

// State
var map = null;
var markers = {};
var dynamicSearchEnabled = false;
var searchInProgress = false;
var moveDebounceTimer = null;
var dynamicToggleEl = document.getElementById('dynamicSearchToggle');
var dynamicCountEl = document.getElementById('dynamicResultsCount');
var currentFilters = {
    q: <?= json_encode($filters['q'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    ville: <?= json_encode($filters['ville'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    category: <?= json_encode($filters['category'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    price: <?= json_encode($filters['price'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    rating: <?= json_encode($filters['rating'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>,
    sort: <?= json_encode($filters['sort'] ?? 'popular', JSON_HEX_TAG | JSON_HEX_AMP) ?>,
};

if (document.getElementById('actMap')) {
    map = L.map('actMap', { zoomControl: false });
    window.actMap = map;
    L.control.zoom({ position: 'topright' }).addTo(map);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // Initial markers
    if (mapData.length > 0) {
        addMarkers(mapData);
        var initBounds = [];
        mapData.forEach(function(act) {
            if (act.gps_latitude && act.gps_longitude) initBounds.push([parseFloat(act.gps_latitude), parseFloat(act.gps_longitude)]);
        });
        if (initBounds.length > 0) map.fitBounds(initBounds, { padding: [30, 30], maxZoom: 13 });
    } else {
        // Default to Algeria center
        map.setView([28.0339, 1.6596], 6);
    }

    // Build legend
    buildLegend();

    // ── Dynamic search toggle (controls API fetching for new areas) ──
    if (dynamicToggleEl) {
        dynamicToggleEl.addEventListener('click', function() {
            dynamicSearchEnabled = !dynamicSearchEnabled;
            this.classList.toggle('active', dynamicSearchEnabled);
            if (dynamicSearchEnabled) searchInZone();
        });
    }

    // ── Map move/zoom handler ──
    map.on('moveend', onMapMove);
    map.on('zoomend', onMapMove);

    // ── Initial sync: list matches visible map area ──
    // Delay slightly so fitBounds finishes first
    setTimeout(function() {
        updateVisibleList();
        initCardInteractions();
    }, 300);
}

function onMapMove() {
    if (!map) return;
    clearTimeout(moveDebounceTimer);
    moveDebounceTimer = setTimeout(function() {
        // Always sync list with map bounds
        updateVisibleList();
        // Only fetch new data from API if toggle is active
        if (dynamicSearchEnabled && !searchInProgress) {
            searchInZone();
        }
    }, 400);
}

// ═══════ SEARCH IN ZONE (AJAX) ═══════
function searchInZone() {
    if (!map || searchInProgress) return;
    var bounds = map.getBounds();
    searchInProgress = true;
    if (dynamicToggleEl) dynamicToggleEl.classList.add('loading');

    var params = new URLSearchParams({
        forMap: '1',
        bounds_sw_lat: bounds.getSouthWest().lat.toFixed(6),
        bounds_sw_lng: bounds.getSouthWest().lng.toFixed(6),
        bounds_ne_lat: bounds.getNorthEast().lat.toFixed(6),
        bounds_ne_lng: bounds.getNorthEast().lng.toFixed(6),
        q: currentFilters.q,
        ville: currentFilters.ville,
        category: currentFilters.category,
        price: currentFilters.price,
        rating: currentFilters.rating,
        sort: currentFilters.sort,
    });

    fetch('/api/activities/filter?' + params.toString())
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                // Clear old markers
                clearMarkers();
                // Replace data
                mapData.length = 0;
                data.data.forEach(function(a) { mapData.push(a); });
                // Redraw
                addMarkers(mapData);
                updateVisibleList();
                buildLegend();
            }
            searchInProgress = false;
            if (dynamicToggleEl) dynamicToggleEl.classList.remove('loading');
        })
        .catch(function() {
            searchInProgress = false;
            if (dynamicToggleEl) dynamicToggleEl.classList.remove('loading');
        });
}

// ═══════ ADD / CLEAR MARKERS ═══════
function clearMarkers() {
    Object.keys(markers).forEach(function(id) {
        if (markers[id].marker) map.removeLayer(markers[id].marker);
    });
    markers = {};
}

function addMarkers(activities) {
    activities.forEach(function(act, i) {
        if (!act.gps_latitude || !act.gps_longitude) return;
        var lat = parseFloat(act.gps_latitude);
        var lng = parseFloat(act.gps_longitude);
        if (isNaN(lat) || isNaN(lng)) return;

        var s = catStyles[act.category] || defaultStyle;
        var delay = Math.min(i * 15, 600);
        var icon = L.divIcon({
            className: '',
            html: '<div class="act-marker" style="background:' + s.color + ';animation-delay:' + delay + 'ms" data-id="' + act.id + '">'
                + '<i class="fas ' + s.icon + '"></i></div>',
            iconSize: [38, 38],
            iconAnchor: [19, 38],
        });

        var marker = L.marker([lat, lng], { icon: icon }).addTo(map);
        marker.bindPopup(buildPopupHTML(act, s), { maxWidth: 260 });

        // Click marker → scroll to card
        marker.on('click', function() {
            var card = document.querySelector('.act-card[data-id="' + act.id + '"]');
            if (card) {
                document.querySelectorAll('.act-card.map-highlight').forEach(function(c) { c.classList.remove('map-highlight'); });
                card.classList.add('map-highlight');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function() { card.classList.remove('map-highlight'); }, 3000);
            }
        });

        markers[act.id] = { marker: marker, lat: lat, lng: lng, category: act.category };
    });
}

function buildPopupHTML(act, s) {
    var photoHtml = act.main_photo
        ? '<img src="' + act.main_photo + '" style="width:100%;height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px;" alt="' + (act.nom || '').replace(/"/g, '') + '" onerror="this.style.display=\'none\'">'
        : '';
    var ratingHtml = parseFloat(act.note_moyenne) > 0
        ? '<span style="background:#00635a;color:#fff;padding:2px 6px;border-radius:4px;font-size:12px;font-weight:700;">' + parseFloat(act.note_moyenne).toFixed(1) + '</span> '
          + '<span style="font-size:11px;color:#888;">' + (act.nb_avis || 0) + ' avis</span>'
        : '<span style="font-size:11px;color:#888;font-style:italic;">Pas encore noté</span>';

    return '<div style="min-width:200px;max-width:240px;">'
        + photoHtml
        + '<strong style="font-size:14px;line-height:1.3;display:block;margin-bottom:4px;">' + (act.nom || '') + '</strong>'
        + '<div style="font-size:12px;color:#888;margin-bottom:6px;">'
        + '<i class="fas fa-map-marker-alt" style="color:' + s.color + ';margin-right:3px;"></i>' + (act.ville || '')
        + '</div>'
        + '<div style="margin-bottom:6px;">' + ratingHtml + '</div>'
        + '<a href="/activite/' + (act.slug || act.id) + '" style="color:#00635a;font-weight:600;font-size:13px;">Voir détails &rarr;</a>'
        + '</div>';
}

// ═══════ UPDATE VISIBLE LIST (always synced with map bounds) ═══════
function updateVisibleList() {
    if (!map) return;
    var bounds = map.getBounds();

    // Filter to activities visible on map
    var visible = mapData.filter(function(a) {
        if (!a.gps_latitude || !a.gps_longitude) return false;
        return bounds.contains([parseFloat(a.gps_latitude), parseFloat(a.gps_longitude)]);
    });

    // Update both static and dynamic count
    var staticCount = document.querySelector('.results-count');
    if (staticCount) {
        staticCount.innerHTML = '<strong>' + visible.length + '</strong> activité' + (visible.length !== 1 ? 's' : '') + ' dans cette zone';
    }
    if (dynamicCountEl) {
        dynamicCountEl.textContent = visible.length + ' activité' + (visible.length !== 1 ? 's' : '') + ' dans cette zone';
        dynamicCountEl.classList.add('visible');
    }

    // Rebuild cards from current map data
    var grid = document.querySelector('.act-grid');
    var existingEmpty = document.querySelector('.empty-state:not(.dynamic-empty)');
    // Hide static pagination (list is now map-driven)
    var pagination = document.querySelector('nav.pagination');
    if (pagination) pagination.style.display = 'none';
    // Hide the initial server-rendered empty state if it exists
    if (existingEmpty) existingEmpty.style.display = 'none';

    if (visible.length === 0) {
        if (grid) grid.innerHTML = '';
        var dynEmpty = document.querySelector('.dynamic-empty');
        if (!dynEmpty) {
            var wrap = grid ? grid.parentNode : document.querySelector('.page-list');
            var emptyDiv = document.createElement('div');
            emptyDiv.className = 'empty-state dynamic-empty';
            emptyDiv.innerHTML = '<i class="fas fa-binoculars"></i><h3>Aucune activité dans cette zone</h3><p>Déplacez la carte pour explorer d\'autres zones.</p>';
            wrap.appendChild(emptyDiv);
        }
    } else {
        // Remove dynamic empty state if present
        var dynEmpty = document.querySelector('.dynamic-empty');
        if (dynEmpty) dynEmpty.remove();

        if (!grid) {
            var wrap = document.querySelector('.page-list');
            grid = document.createElement('div');
            grid.className = 'act-grid';
            var dynCount = document.getElementById('dynamicResultsCount');
            if (dynCount && dynCount.nextSibling) dynCount.parentNode.insertBefore(grid, dynCount.nextSibling);
            else wrap.appendChild(grid);
        }
        grid.innerHTML = visible.map(function(a) { return buildActivityCardHTML(a); }).join('');
    }

    // Re-bind interactions
    initCardInteractions();
}

// ═══════ BUILD CARD HTML (for dynamic rendering) ═══════
function buildActivityCardHTML(act) {
    var s = catStyles[act.category] || defaultStyle;
    var priceLabels = { gratuit: 'Gratuit', pas_cher: '€', moyen: '€€', cher: '€€€' };
    var catLabels = {};
    Object.keys(catStyles).forEach(function(k) { catLabels[k] = catStyles[k].label; });
    var catLabel = catLabels[act.category] || (act.category ? act.category.charAt(0).toUpperCase() + act.category.slice(1) : 'Lieu');
    var priceLabel = priceLabels[act.price_range] || '';
    var note = Math.min(parseFloat(act.note_moyenne) || 0, 5);
    var slug = act.slug || act.id;

    var imgHtml = act.main_photo
        ? '<img loading="lazy" src="' + act.main_photo + '" alt="' + (act.nom || '').replace(/"/g, '') + '" onerror="this.onerror=null;this.src=\'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=400&h=250&fit=crop\'">'
        : '<div class="act-card-noimg"><i class="fas ' + s.icon + '"></i></div>';

    var freeTag = priceLabel === 'Gratuit' ? '<span class="act-card-free">Gratuit</span>' : '';
    var priceSpan = (priceLabel && priceLabel !== 'Gratuit') ? '<span class="act-card-price">' + priceLabel + '</span>' : '';

    var ratingHtml = note > 0
        ? '<div class="act-card-rating"><span class="rating-pill">' + note.toFixed(1) + '</span><span class="rating-count">' + (parseInt(act.nb_avis) || 0) + ' avis</span></div>'
        : '<div class="act-card-no-rating">Pas encore d\'avis</div>';

    return '<a class="act-card" data-id="' + act.id + '" data-lat="' + (act.gps_latitude || '') + '" data-lng="' + (act.gps_longitude || '') + '" href="/activite/' + slug + '">'
        + '<div class="act-card-image">' + imgHtml
        + '<span class="act-card-category"><i class="fas ' + s.icon + '"></i> ' + catLabel + '</span>'
        + freeTag + '</div>'
        + '<div class="act-card-body">'
        + '<h3 class="act-card-name">' + (act.nom || '') + '</h3>'
        + '<div class="act-card-location"><i class="fas fa-map-marker-alt"></i> ' + (act.ville || '') + priceSpan + '</div>'
        + '<div class="act-card-bottom">' + ratingHtml + '</div>'
        + '</div></a>';
}

// ═══════ CARD ↔ MARKER INTERACTIONS ═══════
function initCardInteractions() {
    document.querySelectorAll('.act-card').forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            var id = card.dataset.id;
            // Highlight marker (change color to yellow) — no map panning
            var el = document.querySelector('.act-marker[data-id="' + id + '"]');
            if (el) el.classList.add('highlighted');
        });
        card.addEventListener('mouseleave', function() {
            document.querySelectorAll('.act-marker.highlighted').forEach(function(m) { m.classList.remove('highlighted'); });
        });
    });
}

// ═══════ MAP LEGEND ═══════
var legendControl = null;
function buildLegend() {
    // Collect used categories
    var usedCats = {};
    mapData.forEach(function(act) {
        if (act.gps_latitude && act.gps_longitude) {
            usedCats[act.category || '_default'] = catStyles[act.category] || defaultStyle;
        }
    });
    var catKeys = Object.keys(usedCats);

    // Remove existing legend
    if (legendControl && map) { map.removeControl(legendControl); legendControl = null; }

    if (catKeys.length > 1) {
        var legendHtml = '<div class="map-legend"><div class="map-legend-title">Catégories</div>';
        catKeys.forEach(function(key) {
            var s = usedCats[key];
            legendHtml += '<div class="map-legend-item"><span class="map-legend-dot" style="background:' + s.color + '"></span> ' + s.label + '</div>';
        });
        legendHtml += '</div>';
        legendControl = L.control({ position: 'bottomleft' });
        legendControl.onAdd = function() {
            var div = L.DomUtil.create('div');
            div.innerHTML = legendHtml;
            return div;
        };
        legendControl.addTo(map);
    }
}
</script>

<?php include __DIR__ . '/../partials/header.php'; ?>
</body>
</html>
