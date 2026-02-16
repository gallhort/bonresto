<?php
function buildUrl($filters, $param, $value) {
    $newFilters = $filters;
    if ($value === '' || $value === null || $value === 'all') {
        unset($newFilters[$param]);
    } else {
        $newFilters[$param] = $value;
    }
    if (!isset($newFilters['view'])) {
        $newFilters['view'] = $filters['view'] ?? 'list';
    }
    if ($param !== 'page' && $param !== 'view') {
        unset($newFilters['page']);
    }
    return '/search?' . http_build_query($newFilters);
}

$view = $filters['view'] ?? 'list';
$defaultSort = $filters['sort'] ?? 'distance';
$hasGeo = !empty($filters['lat']) && !empty($filters['lng']);

// Determine search origin city name
$searchCity = null;
if (!empty($filters['ville'])) {
    $searchCity = $filters['ville'];
} elseif (!empty($filters['q'])) {
    $searchCity = $filters['q'];
}

// Count active filters
$activeFilterCount = 0;
if ($hasGeo && !empty($filters['radius'])) $activeFilterCount++;
if (!empty($filters['type'])) $activeFilterCount++;
if (!empty($filters['price'])) $activeFilterCount++;
if (!empty($filters['rating'])) $activeFilterCount++;
if (!empty($filters['amenities'])) $activeFilterCount += count(array_filter(explode(',', $filters['amenities'])));

// Amenities mapping
$amenitiesMap = [
    'wifi' => ['icon' => 'fa-wifi', 'label' => 'Wi-Fi'],
    'parking' => ['icon' => 'fa-parking', 'label' => 'Parking'],
    'terrasse' => ['icon' => 'fa-umbrella-beach', 'label' => 'Terrasse'],
    'climatisation' => ['icon' => 'fa-snowflake', 'label' => 'Climatisation'],
    'livraison' => ['icon' => 'fa-truck', 'label' => 'Livraison'],
    'emporter' => ['icon' => 'fa-bag-shopping', 'label' => 'A emporter'],
    'accessible_pmr' => ['icon' => 'fa-wheelchair', 'label' => 'PMR'],
    'espace_enfants' => ['icon' => 'fa-child', 'label' => 'Enfants'],
    'espace_prive' => ['icon' => 'fa-door-closed', 'label' => 'Salle privee'],
    'priere' => ['icon' => 'fa-mosque', 'label' => 'Salle de priere'],
    'voiturier' => ['icon' => 'fa-car', 'label' => 'Voiturier'],
];
$selectedAmenities = array_filter(explode(',', $filters['amenities'] ?? ''));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Recherche' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ═══════════════════════════════════════════
           DESIGN TOKENS
           ═══════════════════════════════════════════ */
        :root {
            --primary: #00635a;
            --primary-dark: #004d44;
            --primary-light: #e8f5f3;
            --accent: #d4a855;
            --dark: #1a1a1a;
            --gray-900: #2d2d2d;
            --gray-700: #555;
            --gray-500: #888;
            --gray-400: #aaa;
            --gray-300: #d0d0d0;
            --gray-200: #e8e8e8;
            --gray-100: #f4f4f4;
            --white: #fff;
            --font-serif: 'DM Serif Display', Georgia, serif;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --radius-sm: 6px;
            --radius: 10px;
            --radius-lg: 16px;
            --radius-pill: 50px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow: 0 2px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 30px rgba(0,0,0,0.12);
            --transition: 0.2s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-sans);
            background: var(--gray-100);
            color: var(--dark);
            -webkit-font-smoothing: antialiased;
        }

        /* ═══════════════════════════════════════════
           SEARCH HEADER — sticky bar
           ═══════════════════════════════════════════ */
        .search-header {
            background: var(--white);
            padding: 14px 20px;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
            position: relative;
            z-index: 1003;
        }
        .header-row {
            display: flex;
            align-items: center;
            gap: 12px;
            max-width: 1800px;
            margin: 0 auto;
        }

        /* Search input */
        .search-bar {
            flex: 1;
            position: relative;
            max-width: 480px;
        }
        .search-bar input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-pill);
            font-size: 14px;
            font-family: var(--font-sans);
            transition: var(--transition);
            background: var(--gray-100);
        }
        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(0,99,90,0.1);
        }
        .search-bar .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 14px;
        }

        /* Autocomplete dropdown */
        .search-autocomplete {
            position: absolute;
            top: calc(100% + 6px);
            left: 0; right: 0;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            max-height: 380px;
            overflow-y: auto;
            display: none;
            border: 1px solid var(--gray-200);
        }
        .search-autocomplete.show { display: block; }
        .ac-item {
            padding: 10px 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--gray-100);
            transition: background 0.15s;
        }
        .ac-item:last-child { border-bottom: none; }
        .ac-item:hover, .ac-item.selected { background: #f8fffe; }
        .ac-photo {
            width: 44px; height: 44px; min-width: 44px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            background: var(--gray-100);
        }
        .ac-icon {
            width: 40px; height: 40px; min-width: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex; align-items: center; justify-content: center;
            color: var(--primary); font-size: 14px;
        }
        .ac-content { flex: 1; min-width: 0; }
        .ac-title { font-weight: 600; color: var(--dark); font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ac-title mark { background: transparent; font-weight: 700; color: var(--primary); }
        .ac-sub { font-size: 12px; color: var(--gray-500); margin-top: 1px; }
        .ac-rating { color: var(--primary); font-weight: 600; font-size: 12px; }
        .ac-rating i { color: var(--accent); font-size: 11px; }
        .ac-voir-tous { background: var(--gray-100); }
        .ac-voir-tous .ac-title { color: var(--primary); }
        .ac-loading, .ac-empty { padding: 24px 16px; text-align: center; color: var(--gray-500); font-size: 13px; }
        .ac-section { padding: 8px 14px 4px; font-size: 11px; font-weight: 600; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.5px; }

        /* View toggles */
        .view-toggles {
            display: flex;
            gap: 4px;
            background: var(--gray-100);
            border-radius: var(--radius-pill);
            padding: 3px;
        }
        .view-btn {
            padding: 8px 16px;
            border: none;
            background: transparent;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-sans);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--gray-700);
            white-space: nowrap;
        }
        .view-btn:hover { color: var(--dark); }
        .view-btn.active {
            background: var(--white);
            color: var(--dark);
            box-shadow: var(--shadow-sm);
        }

        /* Sort */
        .sort-select {
            padding: 8px 32px 8px 14px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-sans);
            background: var(--white);
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
        }
        .sort-select:focus { outline: none; border-color: var(--primary); }

        /* ═══════════════════════════════════════════
           FILTER BAR
           ═══════════════════════════════════════════ */
        .filter-bar {
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            position: sticky;
            top: 0;
            z-index: 1002;
        }
        .filter-bar-inner {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            max-width: 1800px;
            margin: 0 auto;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .filter-bar-inner::-webkit-scrollbar { display: none; }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 500;
            font-family: var(--font-sans);
            background: var(--white);
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
            text-decoration: none;
            color: var(--dark);
        }
        .filter-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .filter-chip.active {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--white);
        }
        .filter-chip select {
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 500;
            font-family: var(--font-sans);
            cursor: pointer;
            color: inherit;
            padding-right: 4px;
        }
        .filter-chip select:focus { outline: none; }
        .filter-chip.active select { color: var(--dark); background: var(--white); border-radius: 20px; padding: 2px 6px; }
        button.filter-chip { font-family: var(--font-sans); }

        /* Filter dropdown panels (body-level, fixed position) */
        .dd-arrow { font-size: 10px; margin-left: 2px; transition: transform .2s; }
        .fdd-panel {
            display: none;
            position: fixed;
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 99999;
            min-width: 200px;
            padding: 6px 0;
        }
        .fdd-panel.open { display: block; }
        .fdd-scrollable { max-height: 320px; overflow-y: auto; }
        .fdd-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            text-decoration: none;
            cursor: pointer;
            transition: background .1s;
        }
        .fdd-item:hover { background: var(--gray-100); }
        .fdd-item.selected {
            color: var(--primary);
            font-weight: 600;
            background: var(--primary-light);
        }
        .fdd-check {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--dark);
            transition: background .1s;
        }
        .fdd-check:hover { background: var(--gray-100); }
        .fdd-check input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
            flex-shrink: 0;
        }
        .fdd-check i { width: 16px; text-align: center; color: var(--primary); font-size: 13px; }
        .fdd-actions {
            padding: 8px 14px 4px;
            border-top: 1px solid var(--gray-200);
            margin-top: 4px;
        }
        .fdd-apply {
            width: 100%;
            padding: 8px 16px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-sans);
            cursor: pointer;
            transition: background .2s;
        }
        .fdd-apply:hover { background: var(--primary-dark); }

        .filter-chip-clear {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 8px 14px;
            border: 1.5px dashed var(--gray-300);
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 500;
            color: var(--gray-500);
            background: transparent;
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
            text-decoration: none;
        }
        .filter-chip-clear:hover { border-color: #c0392b; color: #c0392b; }

        .filter-origin {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            background: var(--primary-light);
            border-radius: var(--radius-pill);
            white-space: nowrap;
            border: 1.5px solid rgba(0,99,90,0.2);
        }

        .results-count-bar {
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-700);
            background: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
        }

        /* ═══════════════════════════════════════════
           SPLIT-SCREEN LAYOUT (desktop)
           ═══════════════════════════════════════════ */
        .search-layout {
            display: flex;
            min-height: 100vh;
        }

        .results-panel {
            flex: 1;
            background: var(--gray-100);
            min-width: 0;
        }

        .map-panel {
            width: 50%;
            min-width: 400px;
            position: sticky;
            top: 0;
            height: 100vh;
            display: none;
        }
        .map-panel.visible { display: block; }
        #map { width: 100%; height: 100%; }

        .map-locate-btn {
            position: absolute;
            bottom: 24px;
            right: 12px;
            z-index: 500;
            width: 40px;
            height: 40px;
            background: var(--white);
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .map-locate-btn:hover { background: var(--primary); }
        .map-locate-btn:hover i { color: var(--white); }
        .map-locate-btn i { font-size: 16px; color: var(--gray-700); }

        /* Geolocation chip in filter bar */
        .geo-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1.5px solid var(--primary);
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-sans);
            background: var(--primary);
            color: var(--white);
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
        }
        .geo-chip:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
        .geo-chip i { font-size: 12px; }
        .geo-chip.loading { opacity: 0.7; pointer-events: none; }

        /* Dynamic map toggle (Abritel style) */
        .map-dynamic-toggle {
            position: absolute;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: var(--white);
            border-radius: var(--radius-pill);
            box-shadow: var(--shadow-lg);
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-sans);
            color: var(--dark);
            cursor: pointer;
            user-select: none;
            transition: var(--transition);
            border: 1.5px solid var(--gray-200);
        }
        .map-dynamic-toggle:hover { border-color: var(--primary); }
        .map-dynamic-toggle.active { border-color: var(--primary); background: var(--primary-light); }
        .map-dynamic-toggle .toggle-switch {
            position: relative;
            width: 36px;
            height: 20px;
            background: var(--gray-300);
            border-radius: 10px;
            transition: background 0.25s;
            flex-shrink: 0;
        }
        .map-dynamic-toggle.active .toggle-switch { background: var(--primary); }
        .map-dynamic-toggle .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            background: var(--white);
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            transition: transform 0.25s;
        }
        .map-dynamic-toggle.active .toggle-switch::after { transform: translateX(16px); }
        .map-dynamic-loading {
            display: none;
            width: 14px; height: 14px;
            border: 2px solid var(--gray-300);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .map-dynamic-toggle.loading .map-dynamic-loading { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ═══════════════════════════════════════════
           RESTAURANT CARDS — LIST VIEW (Abritel style)
           ═══════════════════════════════════════════ */
        .resto-list {
            background: var(--white);
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .resto-card {
            display: flex;
            gap: 0;
            padding: 0;
            border-bottom: 1px solid var(--gray-200);
            border-radius: 0;
            cursor: pointer;
            transition: background var(--transition);
            background: var(--white);
            text-decoration: none;
            color: inherit;
            box-shadow: none;
        }
        .resto-card:hover {
            background: #fafffe;
        }
        .resto-card.highlight {
            background: var(--primary-light);
            border-left: 3px solid var(--primary);
            animation: highlightPulse 0.4s ease;
        }
        @keyframes highlightPulse {
            0% { background: var(--white); }
            50% { background: #d1fae5; }
            100% { background: var(--primary-light); }
        }
        .resto-card.hidden { display: none; }

        .resto-photo {
            width: 220px;
            height: 180px;
            border-radius: 12px;
            overflow: hidden;
            flex-shrink: 0;
            position: relative;
            background: var(--gray-200);
            margin: 12px;
            margin-right: 0;
            align-self: flex-start;
        }
        .resto-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .resto-card:hover .resto-photo img { transform: scale(1.04); }
        .resto-no-photo {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            font-size: 36px;
        }

        .wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.95);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            transition: transform var(--transition);
            z-index: 2;
        }
        .wishlist-btn:hover { transform: scale(1.15); }
        .wishlist-btn i { color: #e74c3c; font-size: 14px; }

        .map-marker-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 28px;
            height: 28px;
            background: var(--primary);
            border: none;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
        .map-marker-btn i { color: var(--white); font-size: 12px; }

        /* Badge sur photo (hidden en list, visible en grid) */
        .resto-badge { display: none; }

        .resto-info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 14px 18px;
        }
        .resto-info-top {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .resto-info-bottom {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: auto;
        }

        .resto-location {
            font-size: 12px;
            color: var(--gray-500);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .resto-location i { font-size: 10px; color: var(--primary); }

        .resto-name {
            font-family: var(--font-sans);
            font-size: 17px;
            font-weight: 700;
            color: #1a56db;
            line-height: 1.35;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .resto-card:hover .resto-name { color: #1e429f; text-decoration: underline; }

        .resto-meta {
            color: var(--gray-500);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
            line-height: 1.3;
        }
        .resto-rank {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            background: var(--primary-light);
            padding: 2px 8px;
            border-radius: 4px;
            letter-spacing: -0.2px;
            white-space: nowrap;
        }
        .resto-rank i { font-size: 10px; }

        .resto-award-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            width: fit-content;
        }
        .resto-award-tag i { font-size: 10px; }

        .resto-rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .rating-value {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            min-width: 34px;
        }
        .rating-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--dark);
        }
        .rating-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: var(--white);
            font-size: 13px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            min-width: 34px;
        }
        .rating-dots { display: flex; gap: 2px; }
        .rating-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
        }
        .rating-dot.empty { background: var(--gray-300); }
        .reviews-count { color: var(--gray-500); font-size: 13px; }

        .meta-sep { color: var(--gray-300); }
        .card-reviews { }
        .review-title {
            font-size: 13px;
            color: var(--gray-700);
            font-style: italic;
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.5;
        }
        .no-review { font-size: 13px; color: var(--gray-400); font-style: italic; }

        .resto-no-rating {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--primary);
            background: var(--primary-light);
            padding: 6px 12px;
            border-radius: 6px;
            width: fit-content;
        }
        .resto-no-rating i { font-size: 14px; }

        .resto-no-reviews {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--gray-500);
            font-style: italic;
        }
        .resto-no-reviews i { font-size: 13px; color: var(--gray-400); }

        /* ═══════════════════════════════════════════
           GRID VIEW
           ═══════════════════════════════════════════ */
        .resto-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 16px;
            padding: 16px 20px;
            background: var(--gray-100);
        }
        .resto-grid .resto-card {
            flex-direction: column;
            padding: 0;
            min-height: auto;
            border: 1px solid var(--gray-200);
            border-bottom: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: box-shadow var(--transition), transform var(--transition);
            background: var(--white);
        }
        .resto-grid .resto-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
            background: var(--white);
        }
        .resto-grid .resto-photo { width: 100%; height: 180px; min-height: auto; border-radius: 0; margin: 0; }
        .resto-grid .resto-badge { display: block; }
        .resto-grid .resto-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: var(--accent);
            color: var(--white);
            padding: 3px 8px;
            border-radius: var(--radius-sm);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .resto-grid .resto-badge.award {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #78350f;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .resto-grid .resto-info { padding: 14px 16px; }
        .resto-grid .resto-name { white-space: normal; font-size: 15px; color: var(--dark); }
        .resto-grid .resto-card:hover .resto-name { color: var(--primary); text-decoration: none; }
        .resto-grid .resto-award-tag { display: none; }
        .resto-grid .resto-location { display: none; }

        /* ═══════════════════════════════════════════
           MOBILE MAP MODAL
           ═══════════════════════════════════════════ */
        .map-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
            z-index: 9999;
            display: none;
            flex-direction: column;
        }
        .map-modal.active { display: flex; }

        .map-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            flex-shrink: 0;
            z-index: 100;
        }
        .map-modal-title { font-size: 15px; font-weight: 700; }
        .map-modal-close {
            width: 36px;
            height: 36px;
            border: none;
            background: var(--gray-100);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .map-modal-close:hover { background: var(--gray-200); }
        .map-modal-close i { font-size: 16px; color: var(--gray-700); }

        .map-modal-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Map section in modal */
        .map-section {
            height: 45vh;
            min-height: 280px;
            position: relative;
            flex-shrink: 0;
        }
        .map-section #modalMap { width: 100%; height: 100%; }

        /* List section in modal */
        .map-list-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--white);
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            margin-top: -20px;
            position: relative;
            z-index: 100;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        .map-list-handle {
            padding: 12px 0 8px;
            display: flex;
            justify-content: center;
            flex-shrink: 0;
        }
        .map-list-handle::before {
            content: '';
            width: 40px;
            height: 4px;
            background: var(--gray-300);
            border-radius: 2px;
        }

        .map-list-controls {
            padding: 0 16px 12px;
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }
        .map-position-btn {
            flex: 1;
            padding: 10px 16px;
            background: var(--primary);
            color: var(--white);
            border: none;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 13px;
            font-family: var(--font-sans);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
        }
        .map-position-btn:hover { background: var(--primary-dark); }

        .map-filters-btn {
            padding: 10px 18px;
            background: var(--white);
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 13px;
            font-family: var(--font-sans);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }
        .map-filters-btn:hover { background: var(--gray-100); }
        .map-filters-btn.has-filters {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }

        .map-filters-panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: var(--gray-100);
            border-top: 1px solid var(--gray-200);
            flex-shrink: 0;
        }
        .map-filters-panel.open { max-height: 300px; }
        .map-filters-content {
            padding: 14px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .map-filter-group {
            background: var(--white);
            padding: 10px 12px;
            border-radius: var(--radius);
        }
        .map-filter-group label {
            font-weight: 600;
            font-size: 11px;
            color: var(--gray-500);
            display: block;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .map-filter-group select {
            width: 100%;
            padding: 7px;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: var(--font-sans);
        }

        .map-filters-reset {
            grid-column: 1 / -1;
            padding: 8px;
            background: transparent;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 12px;
            color: var(--gray-500);
            cursor: pointer;
            transition: var(--transition);
            font-family: var(--font-sans);
        }
        .map-filters-reset:hover { background: var(--gray-200); color: var(--dark); }

        .map-results-count {
            padding: 10px 16px;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid var(--gray-200);
            flex-shrink: 0;
            color: var(--gray-700);
        }

        .map-resto-list {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .map-resto-list .resto-card { padding: 12px 16px; gap: 12px; }
        .map-resto-list .resto-photo { width: 90px; height: 90px; }
        .map-resto-list .resto-name { font-size: 14px; }
        .map-resto-list .map-marker-btn { display: flex; }

        /* ═══════════════════════════════════════════
           CUSTOM MAP MARKERS
           ═══════════════════════════════════════════ */
        @keyframes markerFadeIn {
            from { opacity: 0; transform: scale(0.4); }
            to { opacity: 1; transform: scale(1); }
        }
        .marker-icon {
            width: 34px;
            height: 34px;
            background: var(--primary);
            border-radius: 50%;
            border: 3px solid var(--white);
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            cursor: pointer;
            animation: markerFadeIn 0.3s ease-out both;
        }
        .marker-icon svg {
            width: 16px;
            height: 16px;
            fill: var(--white);
            transition: fill 0.15s ease;
        }
        .marker-icon.active,
        .marker-icon:hover {
            background: var(--white);
            border-color: var(--primary);
            transform: scale(1.2);
            z-index: 1000 !important;
        }
        .marker-icon.active svg,
        .marker-icon:hover svg {
            fill: var(--primary);
        }

        .marker-dot {
            width: 18px;
            height: 18px;
            background: var(--primary);
            border-radius: 50%;
            border: 2px solid var(--white);
            box-shadow: 0 2px 6px rgba(0,0,0,0.25);
            transition: all 0.15s ease;
            cursor: pointer;
            animation: markerFadeIn 0.3s ease-out both;
        }
        .marker-dot.active,
        .marker-dot:hover {
            background: var(--white);
            border-color: var(--primary);
            transform: scale(1.3);
            z-index: 1000 !important;
        }

        /* ═══════════════════════════════════════════
           PAGINATION
           ═══════════════════════════════════════════ */
        .pagination-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
            gap: 10px;
            background: var(--white);
            border-top: 1px solid var(--gray-200);
        }
        .pagination-info { font-size: 13px; color: var(--gray-500); }
        .pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .pagination-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius);
            background: var(--white);
            color: var(--dark);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            font-family: var(--font-sans);
        }
        .pagination-btn:hover:not(.disabled):not(.active) {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }
        .pagination-btn.active {
            background: var(--primary);
            color: var(--white);
            border-color: var(--primary);
        }
        .pagination-btn.disabled {
            color: var(--gray-300);
            cursor: not-allowed;
            background: var(--gray-100);
        }
        .pagination-ellipsis { padding: 0 6px; color: var(--gray-400); }

        /* ═══════════════════════════════════════════
           EMPTY STATE
           ═══════════════════════════════════════════ */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--gray-400);
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; }
        .empty-state p { font-size: 15px; }

        .hidden { display: none !important; }
        .filtered-out { display: none !important; }

        /* ═══════════════════════════════════════════
           MOBILE FILTER MODAL
           ═══════════════════════════════════════════ */
        .filter-modal-btn {
            display: none; /* shown only on mobile */
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            font-family: var(--font-sans);
            background: var(--white);
            color: var(--dark);
            cursor: pointer;
            white-space: nowrap;
            position: relative;
        }
        .filter-modal-badge {
            position: absolute;
            top: -6px; right: -6px;
            width: 20px; height: 20px;
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }
        .fm-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 10000;
        }
        .fm-overlay.active { display: block; }
        .fm-modal {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            max-height: 90vh;
            background: #fff;
            border-radius: 20px 20px 0 0;
            z-index: 10001;
            flex-direction: column;
            animation: fmSlideUp .3s ease;
        }
        .fm-modal.active { display: flex; }
        @keyframes fmSlideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        .fm-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--gray-200);
        }
        .fm-title { font-size: 17px; font-weight: 700; color: var(--dark); }
        .fm-close {
            width: 36px; height: 36px;
            border: none; background: var(--gray-100);
            border-radius: 50%; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: var(--gray-600);
        }
        .fm-close:hover { background: var(--gray-200); }
        .fm-body {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }
        .fm-section {
            padding: 12px 20px;
        }
        .fm-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--gray-600);
            margin-bottom: 10px;
        }
        .fm-label i { width: 16px; text-align: center; margin-right: 4px; color: var(--primary); }
        .fm-options {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .fm-options::-webkit-scrollbar { display: none; }
        .fm-options-wrap { flex-wrap: wrap; overflow-x: visible; }
        .fm-opt {
            padding: 8px 14px;
            border: 1.5px solid var(--gray-200);
            border-radius: 20px;
            background: #fff;
            font-size: 13px;
            font-weight: 500;
            font-family: var(--font-sans);
            color: var(--dark);
            cursor: pointer;
            white-space: nowrap;
            transition: all .15s;
        }
        .fm-opt:hover { border-color: var(--primary); color: var(--primary); }
        .fm-opt.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .fm-select {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--gray-200);
            border-radius: 10px;
            font-size: 14px;
            font-family: var(--font-sans);
            color: var(--dark);
            background: #fff;
            cursor: pointer;
        }
        .fm-select:focus { border-color: var(--primary); outline: none; }
        .fm-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-top: 1px solid var(--gray-200);
            background: #fff;
        }
        .fm-reset {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray-500);
            text-decoration: underline;
            background: none; border: none;
            cursor: pointer;
            font-family: var(--font-sans);
        }
        .fm-reset:hover { color: var(--dark); }
        .fm-apply {
            padding: 12px 28px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: var(--font-sans);
            cursor: pointer;
            transition: background .2s;
        }
        .fm-apply:hover { background: var(--primary-dark, #004d44); }

        /* ═══════════════════════════════════════════
           DESKTOP ≥ 1025px
           ═══════════════════════════════════════════ */
        @media (min-width: 1025px) {
            .map-panel { display: block; }
            .mobile-map-toggle { display: none !important; }

            .map-modal-content { flex-direction: row; }
            .map-list-section {
                width: 400px;
                flex-shrink: 0;
                border-radius: 0;
                margin-top: 0;
                box-shadow: 2px 0 8px rgba(0,0,0,0.08);
                order: 1;
            }
            .map-list-handle { display: none; }
            .map-section {
                flex: 1;
                height: 100%;
                order: 2;
            }
            .map-resto-list .resto-photo { width: 100px; height: 80px; }
        }

        /* ═══════════════════════════════════════════
           TABLET & MOBILE ≤ 1024px
           ═══════════════════════════════════════════ */
        @media (max-width: 1024px) {
            .map-panel { display: none !important; }

            .resto-photo { width: 180px; height: 160px; margin: 10px; margin-right: 0; }
            .resto-name { font-size: 16px; }
            .resto-grid { grid-template-columns: 1fr 1fr; padding: 12px; gap: 12px; }
        }

        @media (max-width: 768px) {
            .desktop-filter { display: none !important; }
            .filter-modal-btn { display: inline-flex; }
            .filter-bar-inner { padding: 8px 12px; gap: 8px; }
            .geo-chip { padding: 7px 14px; font-size: 13px; }
        }

        @media (max-width: 600px) {
            .header-row { flex-wrap: wrap; }
            .search-bar { order: 3; flex-basis: 100%; max-width: none; }
            .view-toggles { display: none; }
            .sort-select { margin-left: auto; }

            .resto-card { flex-direction: column; }
            .resto-photo { width: auto; height: 200px; margin: 0; border-radius: 0; align-self: stretch; }
            .resto-info { padding: 12px 14px; }
            .resto-name { font-size: 15px; }
            .resto-grid { grid-template-columns: 1fr; }

            .map-filters-content { grid-template-columns: 1fr; }

            .pagination-btn { min-width: 34px; height: 34px; padding: 0 8px; font-size: 12px; }
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 28px;
            right: calc(50% + 20px);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.18);
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity .3s, visibility .3s, transform .3s, background .2s;
            z-index: 9999;
        }
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .back-to-top:hover {
            background: var(--primary-dark, #004d44);
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        @media (max-width: 1024px) {
            .back-to-top { right: 28px; }
        }
        @media (max-width: 600px) {
            .back-to-top { right: 16px; bottom: 20px; width: 40px; height: 40px; font-size: 16px; }
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../partials/header.php'; ?>

    <!-- ═══════════════════════════════════════════
         SEARCH HEADER
         ═══════════════════════════════════════════ -->
    <div class="search-header">
        <div class="header-row">
            <form class="search-bar" action="/search" method="GET" id="searchBarForm">
                <i class="fas fa-search icon"></i>
                <input type="text" name="q" id="liveSearch" placeholder="Rechercher un restaurant, une ville..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>" autocomplete="off">
                <input type="hidden" name="lat" id="searchLat" value="<?= htmlspecialchars($filters['lat'] ?? '') ?>">
                <input type="hidden" name="lng" id="searchLng" value="<?= htmlspecialchars($filters['lng'] ?? '') ?>">
                <input type="hidden" name="radius" id="searchRadius" value="<?= htmlspecialchars($filters['radius'] ?? '') ?>">
                <input type="hidden" name="ville" id="searchVille" value="<?= htmlspecialchars($filters['ville'] ?? '') ?>">
                <input type="hidden" name="view" value="<?= htmlspecialchars($filters['view'] ?? 'list') ?>">
                <div class="search-autocomplete" id="searchAutocomplete"></div>
            </form>

            <div class="view-toggles">
                <button class="view-btn <?= $view === 'list' ? 'active' : '' ?>" onclick="window.location.href='<?= buildUrl($filters, 'view', 'list') ?>'">
                    <i class="fas fa-list"></i> <span class="btn-label">Liste</span>
                </button>
                <button class="view-btn <?= $view === 'grid' ? 'active' : '' ?>" onclick="window.location.href='<?= buildUrl($filters, 'view', 'grid') ?>'">
                    <i class="fas fa-th-large"></i> <span class="btn-label">Grille</span>
                </button>
            </div>
            <button class="view-btn mobile-map-toggle" id="openMapBtn">
                <i class="fas fa-map"></i> <span class="btn-label">Carte</span>
            </button>

            <select class="sort-select" onchange="window.location.href=this.value">
                <?php if ($hasGeo): ?>
                <option value="<?= buildUrl($filters, 'sort', 'distance') ?>" <?= $defaultSort === 'distance' ? 'selected' : '' ?>>Distance</option>
                <?php endif; ?>
                <option value="<?= buildUrl($filters, 'sort', 'relevance') ?>" <?= $defaultSort === 'relevance' ? 'selected' : '' ?>>Pertinence</option>
                <option value="<?= buildUrl($filters, 'sort', 'rating') ?>" <?= $defaultSort === 'rating' ? 'selected' : '' ?>>Meilleures notes</option>
                <option value="<?= buildUrl($filters, 'sort', 'price_low') ?>" <?= $defaultSort === 'price_low' ? 'selected' : '' ?>>Prix croissant</option>
                <option value="<?= buildUrl($filters, 'sort', 'price_high') ?>" <?= $defaultSort === 'price_high' ? 'selected' : '' ?>>Prix d&eacute;croissant</option>
                <option value="<?= buildUrl($filters, 'sort', 'newest') ?>" <?= $defaultSort === 'newest' ? 'selected' : '' ?>>Plus r&eacute;cents</option>
                <option value="<?= buildUrl($filters, 'sort', 'popularity') ?>" <?= $defaultSort === 'popularity' ? 'selected' : '' ?>>Popularit&eacute;</option>
            </select>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         FILTER CHIPS
         ═══════════════════════════════════════════ -->
    <div class="filter-bar">
        <div class="filter-bar-inner">
            <!-- Autour de moi -->
            <button class="geo-chip" id="geoSearchBtn" type="button" aria-label="Rechercher autour de ma position">
                <i class="fas fa-location-crosshairs"></i>
                <span>Autour de moi</span>
            </button>

            <!-- Mobile: Filtres button -->
            <button class="filter-modal-btn" id="openFilterModal" type="button">
                <i class="fas fa-sliders-h"></i>
                <span>Filtres</span>
                <?php if ($activeFilterCount > 0): ?>
                    <span class="filter-modal-badge"><?= $activeFilterCount ?></span>
                <?php endif; ?>
            </button>

            <!-- Search origin -->
            <?php if ($searchCity): ?>
            <span class="filter-origin">
                <i class="fas fa-crosshairs" style="font-size:11px;color:var(--primary);"></i>
                <?= htmlspecialchars($searchCity) ?>
            </span>
            <?php endif; ?>

            <!-- Desktop chips (hidden on mobile) -->
            <!-- Radius (only shown when GPS coordinates are available) -->
            <?php if ($hasGeo): ?>
            <button class="filter-chip desktop-filter <?= !empty($filters['radius']) ? 'active' : '' ?>" data-dd="dd-radius" type="button">
                <i class="fas fa-location-dot" style="font-size:12px;"></i>
                <span><?= !empty($filters['radius']) ? $filters['radius'] . ' km' : 'Rayon' ?></span>
                <i class="fas fa-chevron-down dd-arrow"></i>
            </button>
            <?php endif; ?>

            <!-- Type cuisine -->
            <button class="filter-chip desktop-filter <?= !empty($filters['type']) ? 'active' : '' ?>" data-dd="dd-cuisine" type="button">
                <i class="fas fa-utensils" style="font-size:12px;"></i>
                <span><?= !empty($filters['type']) ? htmlspecialchars($filters['type']) : 'Cuisine' ?></span>
                <i class="fas fa-chevron-down dd-arrow"></i>
            </button>

            <!-- Price -->
            <button class="filter-chip desktop-filter <?= !empty($filters['price']) ? 'active' : '' ?>" data-dd="dd-price" type="button">
                <i class="fas fa-wallet" style="font-size:12px;"></i>
                <span><?= !empty($filters['price']) ? str_replace('$', '&euro;', htmlspecialchars($filters['price'])) : 'Prix' ?></span>
                <i class="fas fa-chevron-down dd-arrow"></i>
            </button>

            <!-- Rating -->
            <button class="filter-chip desktop-filter <?= !empty($filters['rating']) ? 'active' : '' ?>" data-dd="dd-rating" type="button">
                <i class="fas fa-star" style="font-size:12px;"></i>
                <span><?= !empty($filters['rating']) ? $filters['rating'] . '+ / 5' : 'Note' ?></span>
                <i class="fas fa-chevron-down dd-arrow"></i>
            </button>

            <!-- Amenities (multi-select) -->
            <button class="filter-chip desktop-filter <?= !empty($filters['amenities']) ? 'active' : '' ?>" data-dd="dd-amenities" type="button">
                <i class="fas fa-concierge-bell" style="font-size:12px;"></i>
                <span>Services<?php if (!empty($selectedAmenities)): ?> (<?= count($selectedAmenities) ?>)<?php endif; ?></span>
                <i class="fas fa-chevron-down dd-arrow"></i>
            </button>

            <?php if ($activeFilterCount > 0): ?>
            <a href="/search?<?= http_build_query(array_filter(['view' => $view, 'q' => $filters['q'] ?? '', 'ville' => $filters['ville'] ?? '', 'lat' => $filters['lat'] ?? '', 'lng' => $filters['lng'] ?? ''])) ?>" class="filter-chip-clear desktop-filter">
                <i class="fas fa-times" style="font-size:11px;"></i> Effacer (<?= $activeFilterCount ?>)
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         MOBILE FILTER MODAL
         ═══════════════════════════════════════════ -->
    <div class="fm-overlay" id="filterModalOverlay"></div>
    <div class="fm-modal" id="filterModal">
        <div class="fm-header">
            <span class="fm-title">Filtres</span>
            <button class="fm-close" id="closeFilterModal"><i class="fas fa-times"></i></button>
        </div>
        <div class="fm-body">
            <!-- Rayon (only when GPS active) -->
            <?php if ($hasGeo): ?>
            <div class="fm-section">
                <label class="fm-label"><i class="fas fa-location-dot"></i> Rayon</label>
                <div class="fm-options" data-filter="radius">
                    <button class="fm-opt <?= empty($filters['radius']) ? 'active' : '' ?>" data-val="">Tous</button>
                    <button class="fm-opt <?= ($filters['radius'] ?? '') == '5' ? 'active' : '' ?>" data-val="5">5 km</button>
                    <button class="fm-opt <?= ($filters['radius'] ?? '') == '10' ? 'active' : '' ?>" data-val="10">10 km</button>
                    <button class="fm-opt <?= ($filters['radius'] ?? '') == '20' ? 'active' : '' ?>" data-val="20">20 km</button>
                    <button class="fm-opt <?= ($filters['radius'] ?? '') == '50' ? 'active' : '' ?>" data-val="50">50 km</button>
                    <button class="fm-opt <?= ($filters['radius'] ?? '') == '100' ? 'active' : '' ?>" data-val="100">100 km</button>
                </div>
            </div>
            <?php endif; ?>
            <!-- Cuisine -->
            <div class="fm-section">
                <label class="fm-label"><i class="fas fa-utensils"></i> Type de cuisine</label>
                <select class="fm-select" data-filter="type">
                    <option value="">Toutes les cuisines</option>
                    <?php foreach($types ?? [] as $t): ?>
                        <option value="<?= htmlspecialchars($t['type_cuisine']) ?>" <?= ($filters['type'] ?? '') === $t['type_cuisine'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['type_cuisine']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Prix -->
            <div class="fm-section">
                <label class="fm-label"><i class="fas fa-wallet"></i> Prix</label>
                <div class="fm-options" data-filter="price">
                    <button class="fm-opt <?= empty($filters['price']) ? 'active' : '' ?>" data-val="">Tous</button>
                    <button class="fm-opt <?= ($filters['price'] ?? '') === '$' ? 'active' : '' ?>" data-val="$">&euro;</button>
                    <button class="fm-opt <?= ($filters['price'] ?? '') === '$$' ? 'active' : '' ?>" data-val="$$">&euro;&euro;</button>
                    <button class="fm-opt <?= ($filters['price'] ?? '') === '$$$' ? 'active' : '' ?>" data-val="$$$">&euro;&euro;&euro;</button>
                    <button class="fm-opt <?= ($filters['price'] ?? '') === '$$$$' ? 'active' : '' ?>" data-val="$$$$">&euro;&euro;&euro;&euro;</button>
                </div>
            </div>
            <!-- Note -->
            <div class="fm-section">
                <label class="fm-label"><i class="fas fa-star"></i> Note minimum</label>
                <div class="fm-options" data-filter="rating">
                    <button class="fm-opt <?= empty($filters['rating']) ? 'active' : '' ?>" data-val="">Toutes</button>
                    <button class="fm-opt <?= ($filters['rating'] ?? '') == '3' ? 'active' : '' ?>" data-val="3">3+ / 5</button>
                    <button class="fm-opt <?= ($filters['rating'] ?? '') == '4' ? 'active' : '' ?>" data-val="4">4+ / 5</button>
                    <button class="fm-opt <?= ($filters['rating'] ?? '') == '5' ? 'active' : '' ?>" data-val="5">5 / 5</button>
                </div>
            </div>
            <!-- Services (multi-select) -->
            <div class="fm-section">
                <label class="fm-label"><i class="fas fa-concierge-bell"></i> Services</label>
                <div class="fm-options fm-options-wrap" data-filter="amenities">
                    <button class="fm-opt <?= empty($selectedAmenities) ? 'active' : '' ?>" data-val="">Tous</button>
                    <?php foreach ($amenitiesMap as $key => $am): ?>
                        <button class="fm-opt <?= in_array($key, $selectedAmenities) ? 'active' : '' ?>" data-val="<?= $key ?>">
                            <i class="fas <?= $am['icon'] ?>"></i> <?= $am['label'] ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Tri -->
            <div class="fm-section">
                <label class="fm-label"><i class="fas fa-sort"></i> Trier par</label>
                <div class="fm-options fm-options-wrap" data-filter="sort">
                    <?php if ($hasGeo): ?>
                    <button class="fm-opt <?= ($filters['sort'] ?? '') === 'distance' ? 'active' : '' ?>" data-val="distance">Distance</button>
                    <?php endif; ?>
                    <button class="fm-opt <?= ($filters['sort'] ?? '') === 'rating' ? 'active' : '' ?>" data-val="rating">Note</button>
                    <button class="fm-opt <?= ($filters['sort'] ?? '') === 'popularity' ? 'active' : '' ?>" data-val="popularity">Popularite</button>
                    <button class="fm-opt <?= ($filters['sort'] ?? '') === 'price_low' ? 'active' : '' ?>" data-val="price_low">Prix &uarr;</button>
                    <button class="fm-opt <?= ($filters['sort'] ?? '') === 'newest' ? 'active' : '' ?>" data-val="newest">Recents</button>
                </div>
            </div>
        </div>
        <div class="fm-footer">
            <a href="/search?view=<?= $view ?>&lat=<?= $filters['lat'] ?? '' ?>&lng=<?= $filters['lng'] ?? '' ?>" class="fm-reset">Tout effacer</a>
            <button class="fm-apply" id="applyFilters">Voir les resultats</button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         SPLIT SCREEN: RESULTS + MAP
         ═══════════════════════════════════════════ -->
    <div class="search-layout">
        <!-- LEFT: Results -->
        <div class="results-panel">
            <div class="results-count-bar" id="desktopResultsCount">
                <?= $total ?? 0 ?> restaurant<?= ($total ?? 0) > 1 ? 's' : '' ?>
                <?php if (!empty($filters['q'])): ?>
                    pour &laquo; <?= htmlspecialchars($filters['q']) ?> &raquo;
                <?php endif; ?>
            </div>

            <?php if($view === 'list'): ?>
                <div class="resto-list">
                    <?php if(!empty($restaurants)): ?>
                        <?php foreach($restaurants as $resto): ?>
                            <?php include __DIR__ . '/_card.php'; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <p>Aucun restaurant trouv&eacute;</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if($view === 'grid'): ?>
                <div class="resto-grid">
                    <?php if(!empty($restaurants)): ?>
                        <?php foreach($restaurants as $resto): ?>
                            <?php include __DIR__ . '/_card.php'; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <i class="fas fa-search"></i>
                            <p>Aucun restaurant trouv&eacute;</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- PAGINATION -->
            <?php if(!empty($pagination) && $pagination['totalPages'] > 1): ?>
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    <?= $pagination['from'] ?> - <?= $pagination['to'] ?> sur <?= $pagination['totalItems'] ?> restaurants
                </div>
                <div class="pagination">
                    <?php if($pagination['hasPrevPage']): ?>
                        <a href="<?= buildUrl($filters, 'page', $pagination['currentPage'] - 1) ?>" class="pagination-btn">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn disabled"><i class="fas fa-chevron-left"></i></span>
                    <?php endif; ?>

                    <?php
                    $totalPages = $pagination['totalPages'];
                    $currentPage = $pagination['currentPage'];
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    if($startPage > 1): ?>
                        <a href="<?= buildUrl($filters, 'page', 1) ?>" class="pagination-btn">1</a>
                        <?php if($startPage > 2): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                        <?php if($i == $currentPage): ?>
                            <span class="pagination-btn active"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= buildUrl($filters, 'page', $i) ?>" class="pagination-btn"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if($endPage < $totalPages): ?>
                        <?php if($endPage < $totalPages - 1): ?>
                            <span class="pagination-ellipsis">...</span>
                        <?php endif; ?>
                        <a href="<?= buildUrl($filters, 'page', $totalPages) ?>" class="pagination-btn"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <?php if($pagination['hasNextPage']): ?>
                        <a href="<?= buildUrl($filters, 'page', $pagination['currentPage'] + 1) ?>" class="pagination-btn">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="pagination-btn disabled"><i class="fas fa-chevron-right"></i></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Persistent map (desktop) -->
        <div class="map-panel" id="desktopMapPanel">
            <div id="map"></div>
            <label class="map-dynamic-toggle" id="dynamicMapToggle" title="Rechercher quand je déplace la carte">
                <span class="toggle-switch"></span>
                <span class="toggle-label">Rechercher en déplaçant la carte</span>
                <span class="map-dynamic-loading"></span>
            </label>
            <button class="map-locate-btn" id="desktopLocateBtn" title="Ma position">
                <i class="fas fa-location-arrow"></i>
            </button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════
         MOBILE MAP MODAL
         ═══════════════════════════════════════════ -->
    <div class="map-modal" id="mapModal">
        <div class="map-modal-header">
            <div class="map-modal-title" id="mapModalTitle"><?= isset($mapRestaurants) ? count($mapRestaurants) : ($total ?? 0) ?> restaurants</div>
            <button class="map-modal-close" id="closeMapBtn" title="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="map-modal-content">
            <div class="map-section">
                <div id="modalMap"></div>
                <button class="map-locate-btn" id="mapLocateBtn" title="Ma position">
                    <i class="fas fa-location-arrow"></i>
                </button>
            </div>

            <div class="map-list-section">
                <div class="map-list-handle"></div>

                <div class="map-list-controls">
                    <button class="map-position-btn" id="geolocateBtn">
                        <i class="fas fa-location-arrow"></i>
                        Ma position
                    </button>
                    <button class="map-filters-btn" id="mapFiltersBtn">
                        <i class="fas fa-sliders-h"></i>
                        Filtres
                    </button>
                </div>

                <div class="map-filters-panel" id="mapFiltersPanel">
                    <div class="map-filters-content">
                        <div class="map-filter-group">
                            <label>Rayon</label>
                            <select id="filterRadius" onchange="applyMapFilters()">
                                <option value="">Tous</option>
                                <option value="5">5 km</option>
                                <option value="10">10 km</option>
                                <option value="20">20 km</option>
                                <option value="50">50 km</option>
                            </select>
                        </div>
                        <div class="map-filter-group">
                            <label>Type</label>
                            <select id="filterType" onchange="applyMapFilters()">
                                <option value="">Tous</option>
                                <?php foreach($types ?? [] as $t): ?>
                                    <option value="<?= htmlspecialchars($t['type_cuisine']) ?>">
                                        <?= htmlspecialchars($t['type_cuisine']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="map-filter-group">
                            <label>Prix</label>
                            <select id="filterPrice" onchange="applyMapFilters()">
                                <option value="">Tous</option>
                                <option value="$">$</option>
                                <option value="$$">$$</option>
                                <option value="$$$">$$$</option>
                                <option value="$$$$">$$$$</option>
                            </select>
                        </div>
                        <div class="map-filter-group">
                            <label>Note</label>
                            <select id="filterRating" onchange="applyMapFilters()">
                                <option value="">Toutes</option>
                                <option value="3">3+</option>
                                <option value="4">4+</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                        <button class="map-filters-reset" onclick="resetMapFilters()">
                            <i class="fas fa-undo"></i> R&eacute;initialiser
                        </button>
                    </div>
                </div>

                <?php $mapCount = isset($mapRestaurants) ? count($mapRestaurants) : ($total ?? 0); ?>
                <div class="map-results-count" id="mapResultsCount"><?= $mapCount ?> restaurant<?= $mapCount > 1 ? 's' : '' ?></div>

                <div class="map-resto-list" id="mapRestoList">
                    <?php if(!empty($mapRestaurants)): ?>
                        <?php foreach($mapRestaurants as $resto): ?>
                            <?php include __DIR__ . '/_card.php'; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <p>Aucun restaurant trouv&eacute;</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // ═══════════════════════════════════════════
        // GLOBALS
        // ═══════════════════════════════════════════
        let map = null;
        let desktopMap = null;
        let markers = {};
        let desktopMarkers = {};
        const defaultLat = <?= !empty($filters['lat']) ? (float)$filters['lat'] : 36.7538 ?>;
        const defaultLng = <?= !empty($filters['lng']) ? (float)$filters['lng'] : 3.0588 ?>;
        const radiusKm = <?= !empty($filters['radius']) ? (float)$filters['radius'] : 10 ?>;
        const restaurants = <?= json_encode($restaurants ?? []) ?>;
        const mapRestaurants = <?= json_encode($mapRestaurants ?? $restaurants ?? []) ?>;

        const PROXIMITY_THRESHOLD = 50;

        const cutlerySVG = `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/>
        </svg>`;

        const isDesktop = window.innerWidth >= 1025;

        // ═══════════════════════════════════════════
        // DESKTOP PERSISTENT MAP (Airbnb-style)
        // ═══════════════════════════════════════════
        let desktopMapUserMoved = false;
        let dynamicSearchEnabled = false;
        let dynamicToggleEl = null;
        let moveDebounceTimer = null;
        let searchInProgress = false;

        function initDesktopMap() {
            if (!isDesktop) return;

            const panel = document.getElementById('desktopMapPanel');
            if (!panel) return;
            panel.classList.add('visible');
            dynamicToggleEl = document.getElementById('dynamicMapToggle');

            desktopMap = L.map('map', {
                zoomControl: true,
                attributionControl: false
            }).setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(desktopMap);

            // Search radius circle
            L.circle([defaultLat, defaultLng], {
                radius: radiusKm * 1000,
                color: '#00635a',
                fillColor: '#00635a',
                fillOpacity: 0.06,
                weight: 1.5
            }).addTo(desktopMap);

            // Center dot
            L.circleMarker([defaultLat, defaultLng], {
                radius: 7,
                color: '#00635a',
                fillColor: '#00635a',
                fillOpacity: 1,
                weight: 3
            }).addTo(desktopMap);

            // Add markers for all map restaurants
            addDesktopMarkers(mapRestaurants);

            // Fit bounds
            const bounds = L.latLngBounds(
                mapRestaurants
                    .filter(r => r.gps_latitude && r.gps_longitude)
                    .map(r => [parseFloat(r.gps_latitude), parseFloat(r.gps_longitude)])
            );
            if (bounds.isValid()) {
                desktopMap.fitBounds(bounds, { padding: [40, 40] });
            }

            // Save initial state
            desktopMap.whenReady(() => {
                setTimeout(() => {
                    updateDesktopMarkerStyles();
                    updateDesktopVisibleList();
                }, 300);
            });

            // Hover interaction: list card → map marker
            initDesktopInteractions();

            // On map move: update visible list + auto-search if toggle is on
            desktopMap.on('moveend', onDesktopMapMove);
            desktopMap.on('zoomend', onDesktopMapMove);

            // Dynamic search toggle (Abritel-style)
            dynamicToggleEl?.addEventListener('click', function() {
                dynamicSearchEnabled = !dynamicSearchEnabled;
                this.classList.toggle('active', dynamicSearchEnabled);
                // If just enabled, trigger an immediate search
                if (dynamicSearchEnabled) {
                    searchInZone();
                }
            });
        }

        function addDesktopMarkers(restos) {
            restos.forEach((resto, idx) => {
                if (resto.gps_latitude && resto.gps_longitude) {
                    const lat = parseFloat(resto.gps_latitude);
                    const lng = parseFloat(resto.gps_longitude);

                    const delay = Math.min(idx * 15, 600);
                    const marker = L.marker([lat, lng], {
                        icon: createMarkerIcon(resto.id, false, delay)
                    }).addTo(desktopMap);

                    marker.bindPopup(`
                        <div style="min-width:160px;padding:4px;font-family:Inter,sans-serif;">
                            <div style="font-weight:700;font-size:14px;margin-bottom:4px;">${(resto.nom || '').replace(/</g,'&lt;')}</div>
                            <div style="font-size:12px;color:#666;">
                                ${resto.note_moyenne ? '&#9733; ' + Math.min(Number(resto.note_moyenne), 5).toFixed(1) + '/5' : ''}
                                ${resto.distance ? ' &middot; ' + Number(resto.distance).toFixed(1) + ' km' : ''}
                            </div>
                            <a href="/restaurant/${resto.id}" style="display:inline-block;margin-top:6px;font-size:12px;color:#00635a;font-weight:600;text-decoration:none;">Voir &rarr;</a>
                        </div>
                    `);

                    marker.restoId = resto.id;
                    desktopMarkers[resto.id] = marker;

                    marker.on('click', () => {
                        highlightListCard(resto.id);
                    });
                }
            });
        }

        function onDesktopMapMove() {
            if (!desktopMap || !isMapVisible()) return;
            clearTimeout(moveDebounceTimer);
            moveDebounceTimer = setTimeout(() => {
                updateDesktopMarkerStyles();
                updateDesktopVisibleList();

                // Auto-search if dynamic toggle is enabled
                if (dynamicSearchEnabled && !searchInProgress) {
                    searchInZone();
                }
            }, 400);
        }

        // ═══════════════════════════════════════════
        // CLIENT-SIDE PAGINATION
        // ═══════════════════════════════════════════
        const PAGE_SIZE = 30;
        let currentVisibleRestos = [];
        let currentListPage = 1;
        let originalListHTML = null;  // backup PHP-rendered cards
        let originalCountHTML = null; // backup PHP count bar
        let originalPagHTML = null;   // backup PHP pagination
        let jsControllingList = false; // whether JS has taken over the list

        function isMapVisible() {
            const panel = document.getElementById('desktopMapPanel');
            return panel && panel.offsetParent !== null && window.innerWidth >= 1025;
        }

        function updateDesktopVisibleList() {
            if (!desktopMap || !isMapVisible()) return;
            const bounds = desktopMap.getBounds();

            // Filter mapRestaurants by current viewport
            currentVisibleRestos = mapRestaurants.filter(r => {
                if (!r.gps_latitude || !r.gps_longitude) return false;
                return bounds.contains([parseFloat(r.gps_latitude), parseFloat(r.gps_longitude)]);
            });

            // Sort visible by rating desc
            currentVisibleRestos.sort((a, b) => (parseFloat(b.note_moyenne) || 0) - (parseFloat(a.note_moyenne) || 0));

            // Backup original PHP-rendered content (once, before first JS takeover)
            if (!jsControllingList) {
                const listContainer = document.querySelector('.results-panel .resto-list') || document.querySelector('.results-panel .resto-grid');
                const countBar = document.getElementById('desktopResultsCount');
                const phpPag = document.querySelector('.pagination-wrapper');
                if (listContainer) originalListHTML = listContainer.innerHTML;
                if (countBar) originalCountHTML = countBar.innerHTML;
                if (phpPag) originalPagHTML = phpPag.outerHTML;
                jsControllingList = true;
            }

            // Update count
            const countBar = document.getElementById('desktopResultsCount');
            if (countBar) {
                countBar.textContent = currentVisibleRestos.length + ' restaurant' + (currentVisibleRestos.length > 1 ? 's' : '') + ' dans cette zone';
            }

            // Reset to page 1 when viewport changes
            currentListPage = 1;
            renderListPage(currentListPage);
        }

        // Restore original PHP-rendered content when switching from desktop to mobile
        function restorePhpList() {
            if (!jsControllingList || !originalListHTML) return;
            const listContainer = document.querySelector('.results-panel .resto-list') || document.querySelector('.results-panel .resto-grid');
            const countBar = document.getElementById('desktopResultsCount');
            if (listContainer) listContainer.innerHTML = originalListHTML;
            if (countBar && originalCountHTML) countBar.innerHTML = originalCountHTML;

            // Remove JS pagination and restore PHP pagination
            const jsPag = document.getElementById('jsPagination');
            if (jsPag) jsPag.remove();
            const phpPag = document.querySelector('.pagination-wrapper');
            if (phpPag) phpPag.style.display = '';

            jsControllingList = false;
        }

        // Handle window resize: restore PHP list when going below desktop breakpoint
        let resizeTimer = null;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                // Close filter modal if open (e.g. mobile→desktop transition)
                const fmModal = document.getElementById('filterModal');
                const fmOverlay = document.getElementById('filterModalOverlay');
                if (fmModal && fmModal.classList.contains('active')) {
                    fmModal.classList.remove('active');
                    if (fmOverlay) fmOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }

                if (!isMapVisible() && jsControllingList) {
                    restorePhpList();
                } else if (isMapVisible() && !jsControllingList && desktopMap) {
                    // Came back to desktop — re-sync list with map
                    desktopMap.invalidateSize();
                    setTimeout(function() { updateDesktopVisibleList(); }, 200);
                }
            }, 300);
        });

        function renderListPage(page, scrollToTop) {
            const listContainer = document.querySelector('.results-panel .resto-list') || document.querySelector('.results-panel .resto-grid');
            if (!listContainer) return;

            const totalPages = Math.max(1, Math.ceil(currentVisibleRestos.length / PAGE_SIZE));
            page = Math.max(1, Math.min(page, totalPages));
            currentListPage = page;

            const start = (page - 1) * PAGE_SIZE;
            const end = start + PAGE_SIZE;
            const pageRestos = currentVisibleRestos.slice(start, end);

            // Build cards HTML
            listContainer.innerHTML = pageRestos.length ? pageRestos.map(r => buildCardHTML(r)).join('') : `
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <p>Aucun restaurant dans cette zone</p>
                </div>`;

            // Render JS pagination (replace PHP pagination)
            renderJsPagination(page, totalPages, currentVisibleRestos.length, start + 1, Math.min(end, currentVisibleRestos.length));

            // Re-bind hover interactions
            initDesktopInteractions();

            // Scroll to top of results only when user clicks pagination
            if (scrollToTop) {
                const countBar = document.getElementById('desktopResultsCount');
                if (countBar) countBar.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function renderJsPagination(current, totalPages, totalItems, from, to) {
            // Hide PHP pagination
            const phpPag = document.querySelector('.pagination-wrapper');
            if (phpPag) phpPag.style.display = 'none';

            // Get or create JS pagination container
            let jsPag = document.getElementById('jsPagination');
            if (!jsPag) {
                jsPag = document.createElement('div');
                jsPag.id = 'jsPagination';
                jsPag.className = 'pagination-wrapper';
                const panel = document.querySelector('.results-panel');
                if (panel) panel.appendChild(jsPag);
            }

            if (totalPages <= 1) {
                jsPag.innerHTML = `<div class="pagination-info">${totalItems} restaurant${totalItems > 1 ? 's' : ''}</div>`;
                return;
            }

            const startPage = Math.max(1, current - 2);
            const endPage = Math.min(totalPages, current + 2);

            let btns = '';
            // Prev
            btns += current > 1
                ? `<button class="pagination-btn" onclick="goToListPage(${current - 1})"><i class="fas fa-chevron-left"></i></button>`
                : `<span class="pagination-btn disabled"><i class="fas fa-chevron-left"></i></span>`;

            // First page
            if (startPage > 1) {
                btns += `<button class="pagination-btn" onclick="goToListPage(1)">1</button>`;
                if (startPage > 2) btns += `<span class="pagination-ellipsis">...</span>`;
            }

            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                btns += i === current
                    ? `<span class="pagination-btn active">${i}</span>`
                    : `<button class="pagination-btn" onclick="goToListPage(${i})">${i}</button>`;
            }

            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) btns += `<span class="pagination-ellipsis">...</span>`;
                btns += `<button class="pagination-btn" onclick="goToListPage(${totalPages})">${totalPages}</button>`;
            }

            // Next
            btns += current < totalPages
                ? `<button class="pagination-btn" onclick="goToListPage(${current + 1})"><i class="fas fa-chevron-right"></i></button>`
                : `<span class="pagination-btn disabled"><i class="fas fa-chevron-right"></i></span>`;

            jsPag.innerHTML = `
                <div class="pagination-info">${from} - ${to} sur ${totalItems} restaurants</div>
                <div class="pagination">${btns}</div>`;
        }

        window.goToListPage = function(page) {
            renderListPage(page, true);
        };

        // Dynamic search — fetch restaurants for current map viewport
        function searchInZone() {
            if (!desktopMap || searchInProgress) return;
            const bounds = desktopMap.getBounds();
            const center = desktopMap.getCenter();

            // Calculate radius from bounds
            const ne = bounds.getNorthEast();
            const radiusM = center.distanceTo(ne);
            const radiusNewKm = Math.min(Math.ceil(radiusM / 1000), 200);

            searchInProgress = true;
            if (dynamicToggleEl) dynamicToggleEl.classList.add('loading');

            const params = new URLSearchParams({
                lat: center.lat.toFixed(6),
                lng: center.lng.toFixed(6),
                radius: radiusNewKm,
                forMap: '1',
                type: '<?= htmlspecialchars($filters['type'] ?? '') ?>',
                price: '<?= htmlspecialchars($filters['price'] ?? '') ?>',
                rating: '<?= htmlspecialchars($filters['rating'] ?? '') ?>',
                amenities: '<?= htmlspecialchars($filters['amenities'] ?? '') ?>',
                sort: '<?= htmlspecialchars($filters['sort'] ?? 'distance') ?>'
            });

            fetch('/api/restaurants/filter?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data) {
                        // Clear existing markers
                        Object.values(desktopMarkers).forEach(m => desktopMap.removeLayer(m));
                        desktopMarkers = {};

                        // Update data
                        mapRestaurants.length = 0;
                        data.data.forEach(r => mapRestaurants.push(r));

                        // Add new markers
                        addDesktopMarkers(mapRestaurants);

                        // Update list
                        updateDesktopMarkerStyles();
                        updateDesktopVisibleList();
                    }

                    searchInProgress = false;
                    if (dynamicToggleEl) dynamicToggleEl.classList.remove('loading');
                })
                .catch(err => {
                    searchInProgress = false;
                    if (dynamicToggleEl) dynamicToggleEl.classList.remove('loading');
                });
        }

        function buildCardHTML(r) {
            const note = Math.min(parseFloat(r.note_moyenne) || 0, 5);
            let ratingLabel = '';
            if (note >= 4.5) ratingLabel = 'Excellent';
            else if (note >= 4.0) ratingLabel = 'Très bien';
            else if (note >= 3.0) ratingLabel = 'Bien';
            else if (note >= 2.0) ratingLabel = 'Moyen';
            else if (note > 0) ratingLabel = 'Décevant';

            const photoHTML = r.main_photo
                ? `<img loading="lazy" src="/${r.main_photo}" alt="${(r.nom||'').replace(/"/g,'&quot;')}">`
                : `<div class="resto-no-photo">🍽️</div>`;

            const distHTML = r.distance ? ` · ${Number(r.distance).toFixed(1)} km` : '';
            const villeHTML = r.ville ? `<span class="resto-location"><i class="fas fa-map-marker-alt"></i> ${r.ville}${distHTML}</span>` : '';
            const typeHTML = r.type_cuisine ? r.type_cuisine : '';
            const priceHTML = r.price_range ? ` · ${r.price_range}` : '';
            const rankHTML = r.cuisine_rank ? ` · <span class="resto-rank"><i class="fas fa-trophy"></i> N°${r.cuisine_rank.rank} / ${r.cuisine_rank.total} ${r.cuisine_rank.cuisine} &mdash; ${r.cuisine_rank.region}</span>` : '';

            const ratingHTML = note > 0
                ? `<div class="resto-rating">
                    <span class="rating-value">${note.toFixed(1)}</span>
                    <span class="rating-label">${ratingLabel}</span>
                    <span class="reviews-count">${r.nb_avis || 0} avis</span>
                   </div>`
                : `<div class="resto-no-rating"><i class="far fa-star"></i> Pas encore de note</div>`;

            const safeNom = (r.nom || 'Restaurant').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            const safePhoto = (r.main_photo || '').replace(/'/g, "\\'");
            const safeVille = (r.ville || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
            return `<a class="resto-card" data-id="${r.id}" data-lat="${r.gps_latitude}" data-lng="${r.gps_longitude}" href="/restaurant/${r.id}">
                <div class="resto-photo">${photoHTML}
                    <button class="cw-card-btn" data-id="${r.id}" onclick="event.preventDefault();event.stopPropagation();cwToggleResto(${r.id},'${safeNom}','${safePhoto}','${safeVille}');" title="Ajouter au comparateur"><i class="fas fa-balance-scale"></i></button>
                    <button class="wishlist-btn" onclick="event.preventDefault();event.stopPropagation();" aria-label="Ajouter aux favoris"><i class="far fa-heart"></i></button>
                </div>
                <div class="resto-info">
                    <div class="resto-info-top">
                        ${villeHTML}
                        <h3 class="resto-name">${r.nom || 'Restaurant'}</h3>
                        <div class="resto-meta">${typeHTML}${priceHTML}${rankHTML}</div>
                    </div>
                    <div class="resto-info-bottom">${ratingHTML}
                    </div>
                </div>
            </a>`;
        }

        function initDesktopInteractions() {
            document.querySelectorAll('.results-panel .resto-card').forEach(card => {
                const id = card.dataset.id;
                card.addEventListener('mouseenter', () => {
                    const el = document.querySelector(`#map [data-resto-id="${id}"]`);
                    if (el) el.classList.add('active');
                });
                card.addEventListener('mouseleave', () => {
                    const el = document.querySelector(`#map [data-resto-id="${id}"]`);
                    if (el) el.classList.remove('active');
                });
            });
        }

        function highlightListCard(id) {
            document.querySelectorAll('.results-panel .resto-card').forEach(c => c.classList.remove('highlight'));
            let card = document.querySelector(`.results-panel .resto-card[data-id="${id}"]`);

            // Card not on current page? Find which page it's on and switch
            if (!card && currentVisibleRestos.length > 0) {
                const idx = currentVisibleRestos.findIndex(r => String(r.id) === String(id));
                if (idx >= 0) {
                    const targetPage = Math.floor(idx / PAGE_SIZE) + 1;
                    if (targetPage !== currentListPage) {
                        renderListPage(targetPage);
                    }
                    card = document.querySelector(`.results-panel .resto-card[data-id="${id}"]`);
                }
            }

            if (card) {
                card.classList.add('highlight');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Flash effect
                setTimeout(() => card.classList.remove('highlight'), 3000);
            }
        }

        function updateDesktopMarkerStyles() {
            if (!desktopMap) return;
            const positions = [];
            Object.keys(desktopMarkers).forEach(id => {
                const marker = desktopMarkers[id];
                const latLng = marker.getLatLng();
                if (desktopMap.getBounds().contains(latLng)) {
                    const pt = desktopMap.latLngToContainerPoint(latLng);
                    positions.push({ id, x: pt.x, y: pt.y, marker });
                }
            });

            const close = new Set();
            for (let i = 0; i < positions.length; i++) {
                for (let j = i + 1; j < positions.length; j++) {
                    const dx = positions[i].x - positions[j].x;
                    const dy = positions[i].y - positions[j].y;
                    if (Math.sqrt(dx*dx + dy*dy) < PROXIMITY_THRESHOLD) {
                        close.add(positions[i].id);
                        close.add(positions[j].id);
                    }
                }
            }

            Object.keys(desktopMarkers).forEach(id => {
                const marker = desktopMarkers[id];
                const shouldDot = close.has(id);
                const isDot = marker.getIcon().options.html?.includes('marker-dot');
                if (shouldDot !== isDot) {
                    marker.setIcon(createMarkerIcon(id, shouldDot));
                }
            });
        }

        // ═══════════════════════════════════════════
        // MARKER ICON FACTORY
        // ═══════════════════════════════════════════
        function createMarkerIcon(restoId, isDot, delay) {
            const delayStyle = delay ? `animation-delay:${delay}ms;` : '';
            if (isDot) {
                return L.divIcon({
                    className: '',
                    html: `<div class="marker-dot" style="${delayStyle}" data-resto-id="${restoId}"></div>`,
                    iconSize: [18, 18],
                    iconAnchor: [9, 9]
                });
            }
            return L.divIcon({
                className: '',
                html: `<div class="marker-icon" style="${delayStyle}" data-resto-id="${restoId}">${cutlerySVG}</div>`,
                iconSize: [34, 34],
                iconAnchor: [17, 17]
            });
        }

        // ═══════════════════════════════════════════
        // MOBILE MAP MODAL
        // ═══════════════════════════════════════════
        let radiusCircle = null;

        document.getElementById('openMapBtn')?.addEventListener('click', function() {
            document.getElementById('mapModal').classList.add('active');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                if (!map) {
                    initMobileMap();
                } else {
                    map.invalidateSize();
                    updateVisibleList();
                    updateMarkerStyles();
                }
            }, 100);
        });

        document.getElementById('closeMapBtn')?.addEventListener('click', closeMapModal);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMapModal(); });

        function closeMapModal() {
            document.getElementById('mapModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function initMobileMap() {
            map = L.map('modalMap', {
                zoomControl: true,
                attributionControl: false
            }).setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(map);

            radiusCircle = L.circle([defaultLat, defaultLng], {
                radius: radiusKm * 1000,
                color: '#00635a',
                fillColor: '#00635a',
                fillOpacity: 0.06,
                weight: 1.5
            }).addTo(map);

            L.circleMarker([defaultLat, defaultLng], {
                radius: 7,
                color: '#00635a',
                fillColor: '#00635a',
                fillOpacity: 1,
                weight: 3
            }).addTo(map);

            mapRestaurants.forEach((resto, idx) => {
                if (resto.gps_latitude && resto.gps_longitude) {
                    const lat = parseFloat(resto.gps_latitude);
                    const lng = parseFloat(resto.gps_longitude);

                    const delay = Math.min(idx * 15, 600);
                    const marker = L.marker([lat, lng], {
                        icon: createMarkerIcon(resto.id, false, delay)
                    }).addTo(map);

                    marker.bindPopup(`
                        <div style="min-width:140px;padding:2px;font-family:Inter,sans-serif;">
                            <div style="font-weight:700;font-size:14px;margin-bottom:4px;">${resto.nom}</div>
                            <div style="font-size:12px;color:#666;">
                                ${resto.note_moyenne ? '&#9733; ' + Math.min(Number(resto.note_moyenne), 5).toFixed(1) + '/5' : ''}
                                ${resto.distance ? '<br>' + Number(resto.distance).toFixed(1) + ' km' : ''}
                            </div>
                        </div>
                    `);

                    marker.restoId = resto.id;
                    marker.restoData = resto;
                    markers[resto.id] = marker;
                    marker.on('click', () => highlightCard(resto.id));
                }
            });

            map.on('moveend', onMapChange);
            map.on('zoomend', onMapChange);

            const bounds = L.latLngBounds(
                mapRestaurants
                    .filter(r => r.gps_latitude && r.gps_longitude)
                    .map(r => [parseFloat(r.gps_latitude), parseFloat(r.gps_longitude)])
            );
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }

            initMapCardInteractions();
            setTimeout(() => { updateVisibleList(); updateMarkerStyles(); }, 200);
        }

        // ═══════════════════════════════════════════
        // MAP VIEWPORT EVENTS
        // ═══════════════════════════════════════════
        function onMapChange() {
            updateVisibleList();
            updateMarkerStyles();
        }

        function updateVisibleList() {
            if (!map) return;
            const bounds = map.getBounds();
            let visibleCount = 0;

            document.querySelectorAll('#mapRestoList .resto-card').forEach(card => {
                const id = card.dataset.id;
                const marker = markers[id];

                if (card.classList.contains('filtered-out')) {
                    card.classList.add('hidden');
                    return;
                }

                if (marker && map.hasLayer(marker)) {
                    const latLng = marker.getLatLng();
                    if (bounds.contains(latLng)) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                } else {
                    card.classList.add('hidden');
                }
            });

            const countEl = document.getElementById('mapResultsCount');
            const titleEl = document.getElementById('mapModalTitle');
            const text = `${visibleCount} restaurant${visibleCount > 1 ? 's' : ''}`;
            if (countEl) countEl.textContent = text;
            if (titleEl) titleEl.textContent = text;
        }

        function updateMarkerStyles() {
            if (!map) return;
            const positions = [];
            Object.keys(markers).forEach(id => {
                const marker = markers[id];
                const latLng = marker.getLatLng();
                if (map.getBounds().contains(latLng)) {
                    const pt = map.latLngToContainerPoint(latLng);
                    positions.push({ id, x: pt.x, y: pt.y, marker });
                }
            });

            const close = new Set();
            for (let i = 0; i < positions.length; i++) {
                for (let j = i + 1; j < positions.length; j++) {
                    const dx = positions[i].x - positions[j].x;
                    const dy = positions[i].y - positions[j].y;
                    if (Math.sqrt(dx*dx + dy*dy) < PROXIMITY_THRESHOLD) {
                        close.add(positions[i].id);
                        close.add(positions[j].id);
                    }
                }
            }

            Object.keys(markers).forEach(id => {
                const marker = markers[id];
                const shouldDot = close.has(id);
                const isDot = marker.getIcon().options.html?.includes('marker-dot');
                if (shouldDot !== isDot) {
                    marker.setIcon(createMarkerIcon(id, shouldDot));
                }
            });
        }

        // ═══════════════════════════════════════════
        // CARD ↔ MARKER INTERACTIONS
        // ═══════════════════════════════════════════
        function initMapCardInteractions() {
            document.querySelectorAll('#mapRestoList .resto-card').forEach(card => {
                const id = card.dataset.id;

                card.addEventListener('mouseenter', () => setMarkerActive(id, true));
                card.addEventListener('mouseleave', () => setMarkerActive(id, false));

                const markerBtn = card.querySelector('.map-marker-btn');
                markerBtn?.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const lat = parseFloat(card.dataset.lat);
                    const lng = parseFloat(card.dataset.lng);
                    if (lat && lng && markers[id]) {
                        map.setView([lat, lng], 16);
                        setTimeout(() => {
                            markers[id].openPopup();
                            updateMarkerStyles();
                        }, 300);
                    }
                });
            });
        }

        function setMarkerActive(restoId, isActive) {
            const el = document.querySelector(`[data-resto-id="${restoId}"]`);
            if (el) el.classList.toggle('active', isActive);
        }

        function highlightCard(id) {
            document.querySelectorAll('#mapRestoList .resto-card.highlight').forEach(c => c.classList.remove('highlight'));
            const card = document.querySelector(`#mapRestoList .resto-card[data-id="${id}"]`);
            if (card && !card.classList.contains('hidden')) {
                card.classList.add('highlight');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // ═══════════════════════════════════════════
        // MAP FILTERS (modal)
        // ═══════════════════════════════════════════
        document.getElementById('mapFiltersBtn')?.addEventListener('click', function() {
            document.getElementById('mapFiltersPanel')?.classList.toggle('open');
        });

        let activeFilters = { radius: '', type: '', price: '', rating: '' };

        function getDistanceKm(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        function applyMapFilters() {
            activeFilters.radius = document.getElementById('filterRadius')?.value || '';
            activeFilters.type = document.getElementById('filterType')?.value || '';
            activeFilters.price = document.getElementById('filterPrice')?.value || '';
            activeFilters.rating = document.getElementById('filterRating')?.value || '';

            const hasActive = activeFilters.radius || activeFilters.type || activeFilters.price || activeFilters.rating;
            const filterBtn = document.getElementById('mapFiltersBtn');
            if (filterBtn) filterBtn.classList.toggle('has-filters', hasActive);

            if (radiusCircle) {
                const newRadius = activeFilters.radius ? parseFloat(activeFilters.radius) : radiusKm;
                radiusCircle.setRadius(newRadius * 1000);
                map.fitBounds(radiusCircle.getBounds(), { padding: [30, 30] });
            }

            const currentRadius = activeFilters.radius ? parseFloat(activeFilters.radius) : null;

            mapRestaurants.forEach(resto => {
                const id = resto.id;
                const marker = markers[id];
                const card = document.querySelector(`#mapRestoList .resto-card[data-id="${id}"]`);
                if (!marker || !card) return;

                let isVisible = true;

                if (currentRadius) {
                    const lat = parseFloat(resto.gps_latitude);
                    const lng = parseFloat(resto.gps_longitude);
                    if (lat && lng && getDistanceKm(defaultLat, defaultLng, lat, lng) > currentRadius) {
                        isVisible = false;
                    }
                }
                if (activeFilters.type) {
                    if ((resto.type_cuisine || '').toLowerCase().trim() !== activeFilters.type.toLowerCase().trim()) isVisible = false;
                }
                if (activeFilters.price) {
                    if ((resto.price_range || '').trim() !== activeFilters.price.trim()) isVisible = false;
                }
                if (activeFilters.rating) {
                    if ((parseFloat(resto.note_moyenne) || 0) < parseFloat(activeFilters.rating)) isVisible = false;
                }

                if (isVisible) {
                    card.classList.remove('filtered-out');
                    marker.addTo(map);
                } else {
                    card.classList.add('filtered-out');
                    map.removeLayer(marker);
                }
            });

            setTimeout(() => { updateVisibleList(); updateMarkerStyles(); }, 100);
        }

        function resetMapFilters() {
            document.getElementById('filterRadius').value = '';
            document.getElementById('filterType').value = '';
            document.getElementById('filterPrice').value = '';
            document.getElementById('filterRating').value = '';
            applyMapFilters();
        }

        // ═══════════════════════════════════════════
        // GEOLOCATION
        // ═══════════════════════════════════════════
        function geoRedirect(lat, lng) {
            const params = new URLSearchParams(window.location.search);
            params.set('lat', parseFloat(lat).toFixed(6));
            params.set('lng', parseFloat(lng).toFixed(6));
            if (!params.get('radius')) params.set('radius', '10');
            params.delete('q');
            params.delete('ville');
            window.location.href = '/search?' + params.toString();
        }

        function resetGeoBtn() {
            const geoBtn = document.getElementById('geoSearchBtn');
            if (geoBtn) { geoBtn.classList.remove('loading'); geoBtn.querySelector('span').textContent = 'Autour de moi'; }
        }

        // Algérie bounding box: lat 19-37, lng -9 to 12
        function isInAlgeria(lat, lng) {
            return lat >= 19 && lat <= 37.5 && lng >= -9 && lng <= 12;
        }

        function geolocate() {
            if (!navigator.geolocation) {
                alert('Géolocalisation non supportée par votre navigateur.');
                resetGeoBtn();
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    if (isInAlgeria(lat, lng)) {
                        geoRedirect(lat, lng);
                    } else {
                        alert('Cette fonctionnalité n\'est disponible qu\'en Algérie. Votre position a été détectée hors du territoire algérien.');
                        resetGeoBtn();
                    }
                },
                function(err) {
                    let msg = 'Impossible d\'obtenir votre position.';
                    if (err.code === 1) msg = 'Accès à la position refusé. Activez la géolocalisation dans votre navigateur.';
                    else if (err.code === 2) msg = 'Position indisponible.';
                    else if (err.code === 3) msg = 'Délai d\'attente dépassé.';
                    alert(msg);
                    resetGeoBtn();
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }

        // "Autour de moi" button in filter bar
        document.getElementById('geoSearchBtn')?.addEventListener('click', function() {
            this.classList.add('loading');
            this.querySelector('span').textContent = 'Localisation...';
            geolocate();
        });

        document.getElementById('geolocateBtn')?.addEventListener('click', geolocate);
        document.getElementById('mapLocateBtn')?.addEventListener('click', geolocate);
        document.getElementById('desktopLocateBtn')?.addEventListener('click', geolocate);

        // ═══════════════════════════════════════════
        // SEARCH BAR AUTOCOMPLETE
        // ═══════════════════════════════════════════
        (function() {
            const input = document.getElementById('liveSearch');
            const dropdown = document.getElementById('searchAutocomplete');
            const form = document.getElementById('searchBarForm');
            const latInput = document.getElementById('searchLat');
            const lngInput = document.getElementById('searchLng');
            const radiusInput = document.getElementById('searchRadius');
            const villeInput = document.getElementById('searchVille');
            if (!input || !dropdown) return;

            let debounceTimer = null;
            let selectedIndex = -1;
            let currentItems = [];
            const RECENT_KEY = 'lebonresto_recent_searches';

            input.addEventListener('focus', function() {
                if (this.value.trim().length < 2) showRecentSearches();
            });

            input.addEventListener('input', function() {
                const query = this.value.trim();
                selectedIndex = -1;
                if (query.length < 2) { showRecentSearches(); return; }
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => searchAutocomplete(query), 250);
            });

            input.addEventListener('keydown', function(e) {
                if (dropdown.classList.contains('show')) {
                    const items = dropdown.querySelectorAll('.ac-item');
                    if (e.key === 'ArrowDown') { e.preventDefault(); selectedIndex = Math.min(selectedIndex + 1, items.length - 1); updateSelection(items); return; }
                    if (e.key === 'ArrowUp') { e.preventDefault(); selectedIndex = Math.max(selectedIndex - 1, 0); updateSelection(items); return; }
                    if (e.key === 'Enter' && selectedIndex >= 0) { e.preventDefault(); items[selectedIndex].click(); return; }
                    if (e.key === 'Escape') { hideDropdown(); return; }
                }
                // Enter without selection → clear ville/GPS if query changed
                if (e.key === 'Enter') {
                    hideDropdown();
                    if (villeInput.value && this.value !== villeInput.value) {
                        villeInput.value = '';
                        latInput.value = '';
                        lngInput.value = '';
                    }
                }
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) hideDropdown();
            });

            async function searchAutocomplete(query) {
                showLoading();
                try {
                    const res = await fetch('/api/search/autocomplete?q=' + encodeURIComponent(query));
                    const data = await res.json();
                    if (!data.success) { showEmpty('Erreur de recherche'); return; }
                    renderResults(data);
                } catch (err) {
                    showEmpty('Erreur de connexion');
                }
            }

            function renderResults(data) {
                const { villes, restaurants, voir_tous, query } = data;
                if ((!villes || villes.length === 0) && (!restaurants || restaurants.length === 0)) {
                    showEmpty('Aucun résultat pour "' + escapeHtml(query) + '"');
                    return;
                }
                let html = '';
                currentItems = [];

                if (villes && villes.length > 0) {
                    html += '<div class="ac-section">Villes</div>';
                    villes.forEach(ville => {
                        currentItems.push({ type: 'ville', data: ville });
                        const slug = ville.wilaya.toLowerCase().replace(/\s+/g, '-').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        html += `<div class="ac-item" data-type="ville" data-index="${currentItems.length - 1}">
                            <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="ac-content">
                                <div class="ac-title">${highlightMatch(ville.commune, query)}</div>
                                <div class="ac-sub">${escapeHtml(ville.wilaya)}, Algérie</div>
                            </div>
                        </div>`;
                    });
                }

                if (restaurants && restaurants.length > 0) {
                    html += '<div class="ac-section">Restaurants</div>';
                    restaurants.forEach(resto => {
                        currentItems.push({ type: 'restaurant', data: resto });
                        const ratingHtml = resto.note_moyenne > 0 ? `<span class="ac-rating"><i class="fas fa-star"></i> ${resto.note_moyenne}</span>` : '';
                        html += `<div class="ac-item" data-type="restaurant" data-index="${currentItems.length - 1}">
                            <div class="ac-icon"><i class="fas fa-utensils"></i></div>
                            <div class="ac-content">
                                <div class="ac-title">${highlightMatch(resto.nom, query)}</div>
                                <div class="ac-sub">${escapeHtml(resto.ville || '')} ${ratingHtml}</div>
                            </div>
                        </div>`;
                    });
                }

                if (voir_tous) {
                    currentItems.push({ type: 'voir_tous', data: voir_tous });
                    html += `<div class="ac-item ac-voir-tous" data-type="voir_tous" data-index="${currentItems.length - 1}">
                        <div class="ac-icon"><i class="fas fa-search"></i></div>
                        <div class="ac-content"><div class="ac-title">${escapeHtml(voir_tous.label)}</div></div>
                    </div>`;
                }

                dropdown.innerHTML = html;
                showDropdown();
                attachListeners();
            }

            function showRecentSearches() {
                try {
                    const recent = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
                    if (recent.length === 0) { hideDropdown(); return; }
                    let html = '<div class="ac-section"><i class="fas fa-clock"></i> Récents</div>';
                    currentItems = [];
                    recent.forEach((item, i) => {
                        currentItems.push(item);
                        if (item.type === 'ville') {
                            html += `<div class="ac-item" data-type="ville" data-index="${i}">
                                <div class="ac-icon"><i class="fas fa-map-marker-alt"></i></div>
                                <div class="ac-content">
                                    <div class="ac-title">${escapeHtml(item.data.commune)}</div>
                                    <div class="ac-sub">${escapeHtml(item.data.wilaya)}, Algérie</div>
                                </div>
                            </div>`;
                        } else if (item.type === 'restaurant') {
                            html += `<div class="ac-item" data-type="restaurant" data-index="${i}">
                                <div class="ac-icon"><i class="fas fa-utensils"></i></div>
                                <div class="ac-content">
                                    <div class="ac-title">${escapeHtml(item.data.nom)}</div>
                                    <div class="ac-sub">${escapeHtml(item.data.ville || '')}</div>
                                </div>
                            </div>`;
                        }
                    });
                    dropdown.innerHTML = html;
                    showDropdown();
                    attachListeners();
                } catch (e) { hideDropdown(); }
            }

            function attachListeners() {
                dropdown.querySelectorAll('.ac-item').forEach(el => {
                    el.addEventListener('click', function() {
                        const type = this.dataset.type;
                        const idx = parseInt(this.dataset.index);
                        const item = currentItems[idx];
                        if (type === 'ville') selectVille(item.data);
                        else if (type === 'restaurant') selectRestaurant(item.data);
                        else if (type === 'voir_tous') window.location.href = item.data.url;
                    });
                });
            }

            function selectVille(ville) {
                saveRecent({ type: 'ville', data: ville });
                input.value = ville.commune;
                latInput.value = ville.lat || '';
                lngInput.value = ville.lng || '';
                villeInput.value = ville.commune;
                if (!radiusInput.value) radiusInput.value = '10';
                hideDropdown();
                form.submit();
            }

            function selectRestaurant(resto) {
                saveRecent({ type: 'restaurant', data: resto });
                window.location.href = '/restaurant/' + resto.id;
            }

            function saveRecent(item) {
                try {
                    let recent = JSON.parse(localStorage.getItem(RECENT_KEY) || '[]');
                    recent = recent.filter(r => {
                        if (r.type !== item.type) return true;
                        if (item.type === 'ville') return r.data.commune !== item.data.commune;
                        return r.data.id !== item.data.id;
                    });
                    recent.unshift(item);
                    localStorage.setItem(RECENT_KEY, JSON.stringify(recent.slice(0, 5)));
                } catch (e) {}
            }

            function showDropdown() { dropdown.classList.add('show'); }
            function hideDropdown() { dropdown.classList.remove('show'); selectedIndex = -1; }
            function showLoading() { dropdown.innerHTML = '<div class="ac-loading"><i class="fas fa-spinner fa-spin"></i> Recherche...</div>'; showDropdown(); }
            function showEmpty(msg) { dropdown.innerHTML = '<div class="ac-empty">' + escapeHtml(msg) + '</div>'; showDropdown(); }
            function updateSelection(items) {
                items.forEach((el, i) => el.classList.toggle('selected', i === selectedIndex));
                if (selectedIndex >= 0 && items[selectedIndex]) items[selectedIndex].scrollIntoView({ block: 'nearest' });
            }
            function highlightMatch(text, query) {
                if (!query) return escapeHtml(text);
                return escapeHtml(text).replace(new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi'), '<mark>$1</mark>');
            }
            function escapeHtml(t) { if (!t) return ''; const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
        })();

        // ═══════════════════════════════════════════
        // PRESERVE SEARCH STATE (back button fix)
        // ═══════════════════════════════════════════
        (function() {
            const SEARCH_KEY = 'lebonresto_last_search';
            const params = new URLSearchParams(window.location.search);
            const hasRealParams = params.get('q') || params.get('lat') || params.get('ville') || params.get('type') || params.get('rating');

            if (hasRealParams) {
                // Page has search criteria → save the full URL
                sessionStorage.setItem(SEARCH_KEY, window.location.href);
            } else {
                // No search criteria → check if we came back from a restaurant page
                const savedUrl = sessionStorage.getItem(SEARCH_KEY);
                if (savedUrl && savedUrl !== window.location.href) {
                    // Detect back/forward navigation
                    const navEntries = performance.getEntriesByType('navigation');
                    const isBackNav = navEntries.length > 0 && navEntries[0].type === 'back_forward';
                    if (isBackNav) {
                        window.location.replace(savedUrl);
                        return;
                    }
                }
            }
        })();

        // ═══════════════════════════════════════════
        // DESKTOP FILTER DROPDOWNS (unified system)
        // ═══════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', function() {
            let openPanel = null;
            let openChip = null;

            function closeAll() {
                if (openPanel) { openPanel.classList.remove('open'); openPanel = null; }
                if (openChip) { openChip = null; }
            }

            // All chips with data-dd attribute
            document.querySelectorAll('.filter-chip[data-dd]').forEach(chip => {
                chip.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const panelId = this.dataset.dd;
                    const panel = document.getElementById(panelId);
                    if (!panel) return;

                    if (openPanel === panel) {
                        closeAll();
                        return;
                    }
                    closeAll();

                    // Position panel below chip
                    const rect = this.getBoundingClientRect();
                    panel.style.top = (rect.bottom + 6) + 'px';
                    panel.style.left = rect.left + 'px';
                    panel.classList.add('open');
                    openPanel = panel;
                    openChip = this;
                });
            });

            // Close on outside click
            document.addEventListener('click', function(e) {
                if (openPanel && !openPanel.contains(e.target)) {
                    closeAll();
                }
            });

            // Amenities apply button
            const amenitiesApply = document.getElementById('amenitiesApply');
            if (amenitiesApply) {
                amenitiesApply.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const panel = document.getElementById('dd-amenities');
                    const checked = panel.querySelectorAll('input[type="checkbox"]:checked');
                    const vals = Array.from(checked).map(cb => cb.value);
                    const params = new URLSearchParams(window.location.search);
                    if (vals.length > 0) {
                        params.set('amenities', vals.join(','));
                    } else {
                        params.delete('amenities');
                    }
                    params.delete('page');
                    window.location.href = '/search?' + params.toString();
                });
            }
        });

        // ═══════════════════════════════════════════
        // MOBILE FILTER MODAL
        // ═══════════════════════════════════════════
        (function() {
            const modal = document.getElementById('filterModal');
            const overlay = document.getElementById('filterModalOverlay');
            const openBtn = document.getElementById('openFilterModal');
            const closeBtn = document.getElementById('closeFilterModal');
            const applyBtn = document.getElementById('applyFilters');
            if (!modal || !openBtn) return;

            function openModal() {
                modal.classList.add('active');
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            function closeModal() {
                modal.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            openBtn.addEventListener('click', openModal);
            closeBtn.addEventListener('click', closeModal);
            overlay.addEventListener('click', closeModal);

            // Toggle option buttons
            modal.querySelectorAll('.fm-options').forEach(group => {
                const isMulti = group.dataset.filter === 'amenities';
                group.querySelectorAll('.fm-opt').forEach(btn => {
                    btn.addEventListener('click', function() {
                        if (isMulti) {
                            // Multi-select for amenities
                            if (this.dataset.val === '') {
                                // "Tous" → clear all others
                                group.querySelectorAll('.fm-opt').forEach(b => b.classList.remove('active'));
                                this.classList.add('active');
                            } else {
                                // Specific amenity → toggle, remove "Tous"
                                group.querySelector('.fm-opt[data-val=""]')?.classList.remove('active');
                                this.classList.toggle('active');
                                // If nothing selected, re-activate "Tous"
                                if (!group.querySelector('.fm-opt.active')) {
                                    group.querySelector('.fm-opt[data-val=""]')?.classList.add('active');
                                }
                            }
                        } else {
                            // Single-select for everything else
                            group.querySelectorAll('.fm-opt').forEach(b => b.classList.remove('active'));
                            this.classList.add('active');
                        }
                    });
                });
            });

            // Apply filters → build URL and navigate
            applyBtn.addEventListener('click', function() {
                const params = new URLSearchParams();
                // Preserve GPS coords and view
                const curParams = new URLSearchParams(window.location.search);
                if (curParams.get('lat')) params.set('lat', curParams.get('lat'));
                if (curParams.get('lng')) params.set('lng', curParams.get('lng'));
                if (curParams.get('q')) params.set('q', curParams.get('q'));
                if (curParams.get('ville')) params.set('ville', curParams.get('ville'));
                params.set('view', '<?= htmlspecialchars($filters['view'] ?? 'list') ?>');

                // Collect values from modal
                modal.querySelectorAll('.fm-options').forEach(group => {
                    const filter = group.dataset.filter;
                    if (filter === 'amenities') {
                        // Multi-select: collect all active amenities
                        const activeAmenities = Array.from(group.querySelectorAll('.fm-opt.active'))
                            .map(b => b.dataset.val)
                            .filter(v => v !== '');
                        if (activeAmenities.length > 0) {
                            params.set('amenities', activeAmenities.join(','));
                        }
                    } else {
                        const active = group.querySelector('.fm-opt.active');
                        if (active && active.dataset.val) {
                            params.set(filter, active.dataset.val);
                        }
                    }
                });

                // Cuisine select
                const cuisineSelect = modal.querySelector('.fm-select[data-filter="type"]');
                if (cuisineSelect && cuisineSelect.value) {
                    params.set('type', cuisineSelect.value);
                }

                window.location.href = '/search?' + params.toString();
            });
        })();

        // ═══════════════════════════════════════════
        // INIT
        // ═══════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', function() {
            initDesktopMap();
        });
    </script>
    <!-- Back to top button -->
    <button class="back-to-top" id="backToTop" aria-label="Retour en haut" title="Retour en haut">
        <i class="fas fa-chevron-up"></i>
    </button>
    <script>
    (function(){
        var btn = document.getElementById('backToTop');
        var threshold = 400;
        var visible = false;
        function checkScroll() {
            if (window.scrollY > threshold) {
                if (!visible) { btn.classList.add('visible'); visible = true; }
            } else {
                if (visible) { btn.classList.remove('visible'); visible = false; }
            }
        }
        window.addEventListener('scroll', checkScroll, {passive: true});
        checkScroll();
        btn.addEventListener('click', function(){
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
    })();
    </script>

    <!-- Filter dropdown panels (body-level to avoid stacking context issues) -->
    <div class="fdd-panel" id="dd-radius">
        <a class="fdd-item <?= empty($filters['radius']) ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'radius', '') ?>">Tous</a>
        <a class="fdd-item <?= ($filters['radius'] ?? '') == '5' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'radius', '5') ?>">5 km</a>
        <a class="fdd-item <?= ($filters['radius'] ?? '') == '10' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'radius', '10') ?>">10 km</a>
        <a class="fdd-item <?= ($filters['radius'] ?? '') == '20' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'radius', '20') ?>">20 km</a>
        <a class="fdd-item <?= ($filters['radius'] ?? '') == '50' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'radius', '50') ?>">50 km</a>
        <a class="fdd-item <?= ($filters['radius'] ?? '') == '100' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'radius', '100') ?>">100 km</a>
    </div>

    <div class="fdd-panel fdd-scrollable" id="dd-cuisine">
        <a class="fdd-item <?= empty($filters['type']) ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'type', '') ?>">Toutes les cuisines</a>
        <?php foreach($types ?? [] as $t): ?>
            <a class="fdd-item <?= ($filters['type'] ?? '') === $t['type_cuisine'] ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'type', $t['type_cuisine']) ?>"><?= htmlspecialchars($t['type_cuisine']) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="fdd-panel" id="dd-price">
        <a class="fdd-item <?= empty($filters['price']) ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'price', '') ?>">Tous les prix</a>
        <a class="fdd-item <?= ($filters['price'] ?? '') === '$' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'price', '$') ?>">&euro; &mdash; Bon march&eacute;</a>
        <a class="fdd-item <?= ($filters['price'] ?? '') === '$$' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'price', '$$') ?>">&euro;&euro; &mdash; Moyen</a>
        <a class="fdd-item <?= ($filters['price'] ?? '') === '$$$' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'price', '$$$') ?>">&euro;&euro;&euro; &mdash; Haut de gamme</a>
        <a class="fdd-item <?= ($filters['price'] ?? '') === '$$$$' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'price', '$$$$') ?>">&euro;&euro;&euro;&euro; &mdash; Gastronomique</a>
    </div>

    <div class="fdd-panel" id="dd-rating">
        <a class="fdd-item <?= empty($filters['rating']) ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'rating', '') ?>">Toutes les notes</a>
        <a class="fdd-item <?= ($filters['rating'] ?? '') == '3' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'rating', '3') ?>"><i class="fas fa-star" style="color:var(--accent);font-size:11px;"></i> 3+ / 5</a>
        <a class="fdd-item <?= ($filters['rating'] ?? '') == '4' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'rating', '4') ?>"><i class="fas fa-star" style="color:var(--accent);font-size:11px;"></i> 4+ / 5</a>
        <a class="fdd-item <?= ($filters['rating'] ?? '') == '5' ? 'selected' : '' ?>" href="<?= buildUrl($filters, 'rating', '5') ?>"><i class="fas fa-star" style="color:var(--accent);font-size:11px;"></i> 5 / 5</a>
    </div>

    <div class="fdd-panel" id="dd-amenities">
        <?php foreach ($amenitiesMap as $key => $am): ?>
            <label class="fdd-check">
                <input type="checkbox" value="<?= $key ?>" <?= in_array($key, $selectedAmenities) ? 'checked' : '' ?>>
                <i class="fas <?= $am['icon'] ?>"></i>
                <span><?= $am['label'] ?></span>
            </label>
        <?php endforeach; ?>
        <div class="fdd-actions">
            <button type="button" class="fdd-apply" id="amenitiesApply">Appliquer</button>
        </div>
    </div>
<?php include __DIR__ . '/../partials/_compare_widget.php'; ?>
</body>
</html>
