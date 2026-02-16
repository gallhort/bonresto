<?php
$cuisineImages = [
    'Barbecue restaurant'            => 'barbecue',
    'Classique'                      => 'classique',
    'Café'                           => 'cafe',
    'classique'                      => 'classique',
    'Coffee shop'                    => 'coffee_shop',
    'Fast food restaurant'           => 'fastfood',
    'Hamburger restaurant'           => 'burger',
    'Hôtel'                          => 'hotel',
    'Japonais'                       => 'japanese',
    "Magasin d'alimentation"         => 'grocery',
    'Marchand de glaces'             => 'ice_cream',
    'Night club'                     => 'night_club',
    'Pizzeria'                       => 'pizza',
    'Restaurant américain'           => 'american',
    'Restaurant asiatique'           => 'asian',
    'Restaurant brunch'              => 'brunch',
    'Restaurant chinois'             => 'chinese',
    'Restaurant coréen'              => 'korean',
    'Restaurant de fruits de mer'    => 'seafood',
    'Restaurant de grillades'        => 'grill',
    'Restaurant de hamburgers'       => 'burger',
    'Restaurant de plats à emporter' => 'takeaway',
    'Restaurant de sushis'           => 'sushi',
    'Restaurant français'            => 'french',
    'Restaurant indien'              => 'indian',
    'Restaurant italien'             => 'italian',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'LeBonResto - Découvrez les meilleurs restaurants d\'Algérie' ?></title>
    <meta name="description" content="Découvrez les meilleurs restaurants d'Algérie. Plus de <?= $totalRestaurants ?? 0 ?> restaurants référencés avec des avis vérifiés.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #00635a;
            --primary-dark: #004d46;
            --primary-light: #e8f5f0;
            --accent: #f59e0b;
            --accent-light: #fef3c7;
            --dark: #0f172a;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-500: #64748b;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --font-serif: 'DM Serif Display', Georgia, serif;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --radius: 16px;
            --radius-sm: 8px;
            --radius-full: 9999px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.04), 0 10px 20px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.04), 0 20px 48px rgba(0,0,0,0.08);
            --shadow-xl: 0 20px 50px rgba(0,0,0,0.12);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-sans);
            background: var(--gray-50);
            color: var(--gray-900);
            -webkit-font-smoothing: antialiased;
        }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; height: auto; display: block; }
        button { font-family: inherit; cursor: pointer; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* ═══════════════════════════════════════════════════════════ */
        /* HERO                                                       */
        /* ═══════════════════════════════════════════════════════════ */
        .hero {
            position: relative;
            min-height: 520px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #003d36 0%, #00635a 40%, #00897b 100%);
            overflow: hidden;
            padding: 120px 20px 80px;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&h=600&fit=crop') center/cover no-repeat;
            opacity: 0.12;
        }
        .hero::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 80px;
            background: linear-gradient(to top, var(--gray-50), transparent);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 780px;
            width: 100%;
        }
        .hero-title {
            font-family: var(--font-serif);
            font-size: 56px;
            font-weight: 400;
            color: white;
            line-height: 1.15;
            margin-bottom: 16px;
        }
        .hero-subtitle {
            font-size: 18px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 40px;
            font-weight: 400;
        }

        /* Vertical tabs */
        .hero-tabs {
            display: flex;
            justify-content: center;
            gap: 4px;
            margin-bottom: 24px;
            background: rgba(255,255,255,0.15);
            border-radius: var(--radius-full);
            padding: 4px;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }
        .hero-tab {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--radius-full);
            font-size: 15px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            cursor: pointer;
            transition: all 0.25s;
            border: none;
            background: none;
            white-space: nowrap;
        }
        .hero-tab:hover { color: white; background: rgba(255,255,255,0.1); }
        .hero-tab.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .hero-tab i { font-size: 16px; }

        /* Search bar */
        .hero-search {
            position: relative;
            display: flex;
            align-items: center;
            background: white;
            border-radius: var(--radius-full);
            padding: 6px 6px 6px 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            max-width: 640px;
            margin: 0 auto 48px;
        }
        .hero-search-inner {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .hero-search-inner i { color: var(--gray-300); font-size: 18px; }
        .hero-search-inner input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 16px;
            color: var(--gray-900);
            background: transparent;
            padding: 14px 0;
        }
        .hero-search-inner input::placeholder { color: var(--gray-300); }
        .hero-search-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: var(--radius-full);
            font-size: 15px;
            font-weight: 600;
            transition: background 0.2s;
            white-space: nowrap;
        }
        .hero-search-btn:hover { background: var(--primary-dark); }

        /* Autocomplete dropdown */
        .autocomplete-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0; right: 0;
            background: white;
            border-radius: var(--radius);
            max-height: 450px;
            overflow-y: auto;
            z-index: 9999;
            box-shadow: var(--shadow-xl);
            display: none;
            border: 1px solid var(--gray-200);
        }
        .autocomplete-dropdown.show { display: block; }
        .autocomplete-item {
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid var(--gray-100);
            transition: background 0.15s;
        }
        .autocomplete-item:last-child { border-bottom: none; }
        .autocomplete-item:hover, .autocomplete-item.selected { background: var(--gray-50); }
        .autocomplete-item-photo {
            width: 52px; height: 52px; min-width: 52px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            background: var(--gray-100);
        }
        .autocomplete-item-content { flex: 1; min-width: 0; text-align: left; }
        .autocomplete-item-title { font-weight: 600; color: var(--gray-900); font-size: 15px; }
        .autocomplete-item-title mark { background: transparent; font-weight: 700; }
        .autocomplete-item-subtitle { font-size: 13px; color: var(--gray-500); margin-top: 2px; }
        .autocomplete-item-rating { color: var(--primary); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .autocomplete-item-rating i { color: var(--accent); font-size: 12px; }
        .autocomplete-item-icon-small {
            width: 44px; height: 44px; border-radius: 50%;
            background: var(--gray-100);
            display: flex; align-items: center; justify-content: center;
            color: var(--gray-500); font-size: 16px;
        }
        .autocomplete-item-arrow { color: var(--gray-300); font-size: 14px; margin-left: auto; }
        .autocomplete-voir-tous-item { background: var(--gray-50); }
        .autocomplete-voir-tous-item .autocomplete-item-title { color: var(--primary); }
        .autocomplete-loading, .autocomplete-empty { padding: 30px 20px; text-align: center; color: var(--gray-500); font-size: 14px; }
        .autocomplete-recent-header {
            padding: 14px 16px 10px;
            font-size: 12px; font-weight: 600; color: var(--gray-500);
            background: var(--gray-50);
            display: flex; align-items: center; gap: 8px;
            border-bottom: 1px solid var(--gray-100);
        }

        /* Counters */
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 48px;
        }
        .hero-stat { text-align: center; }
        .hero-stat-number {
            font-family: var(--font-serif);
            font-size: 36px;
            color: white;
            display: block;
        }
        .hero-stat-label {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            margin-top: 4px;
        }

        @media (max-width: 768px) {
            .hero { padding: 100px 16px 60px; min-height: 440px; }
            .hero-title { font-size: 36px; }
            .hero-subtitle { font-size: 16px; }
            .hero-tabs { max-width: 100%; margin-bottom: 20px; }
            .hero-tab { padding: 10px 16px; font-size: 13px; }
            .hero-search {
                flex-direction: column;
                border-radius: var(--radius);
                padding: 12px;
                gap: 12px;
            }
            .hero-search-inner { width: 100%; padding: 8px 12px; background: var(--gray-50); border-radius: var(--radius-sm); }
            .hero-search-btn { width: 100%; border-radius: var(--radius-sm); }
            .hero-stats { gap: 24px; }
            .hero-stat-number { font-size: 28px; }
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* SECTIONS                                                   */
        /* ═══════════════════════════════════════════════════════════ */
        .section { padding: 56px 0; }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }
        .section-title {
            font-family: var(--font-serif);
            font-size: 28px;
            color: var(--gray-900);
        }
        .section-link {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 6px;
            transition: gap 0.2s;
        }
        .section-link:hover { gap: 10px; }

        /* ═══════════════════════════════════════════════════════════ */
        /* RESTAURANT CAROUSEL                                        */
        /* ═══════════════════════════════════════════════════════════ */
        .carousel-wrapper { position: relative; }
        .carousel-track {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            padding: 4px 0 8px;
        }
        .carousel-track::-webkit-scrollbar { display: none; }
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px; height: 44px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: var(--gray-700);
            z-index: 10;
            box-shadow: var(--shadow-md);
            transition: all 0.2s;
        }
        .carousel-arrow:hover { background: var(--gray-900); color: white; border-color: var(--gray-900); }
        .carousel-arrow.left { left: -16px; }
        .carousel-arrow.right { right: -16px; }
        @media (max-width: 768px) {
            .carousel-arrow { display: none; }
        }

        /* Restaurant card */
        .resto-card {
            min-width: 280px;
            max-width: 280px;
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
            scroll-snap-align: start;
            flex-shrink: 0;
        }
        .resto-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }
        .resto-card-image {
            position: relative;
            height: 180px;
            overflow: hidden;
        }
        .resto-card-image img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }
        .resto-card:hover .resto-card-image img { transform: scale(1.06); }
        .resto-card-badge {
            position: absolute;
            top: 12px; left: 12px;
            background: var(--accent);
            color: white;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .resto-card-wishlist {
            position: absolute;
            top: 12px; right: 12px;
            width: 36px; height: 36px;
            background: white;
            border-radius: 50%;
            border: none;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            transition: transform 0.2s;
        }
        .resto-card-wishlist:hover { transform: scale(1.15); }
        .resto-card-wishlist i { color: var(--gray-300); font-size: 15px; }
        .resto-card-wishlist.active i { color: #ef4444; font-weight: 900; }
        .resto-card-content { padding: 16px; }
        .resto-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
        .resto-card-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--gray-900);
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .resto-card-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            background: var(--primary);
            color: white;
            padding: 4px 10px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .resto-card-rating i { font-size: 10px; }
        .resto-card-info { font-size: 13px; color: var(--gray-500); margin-bottom: 8px; }
        .resto-card-meta {
            display: flex; align-items: center; gap: 12px;
            font-size: 13px; color: var(--gray-500);
        }
        .resto-card-meta span { display: flex; align-items: center; gap: 4px; }
        .resto-card-price { color: var(--primary); font-weight: 600; }

        /* Grid mode (for section backgrounds) */
        .resto-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .resto-grid-4 .resto-card { min-width: unset; max-width: unset; }
        @media (max-width: 1024px) { .resto-grid-4 { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .resto-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .resto-grid-4 { grid-template-columns: 1fr; } }

        /* ═══════════════════════════════════════════════════════════ */
        /* CATEGORIES                                                 */
        /* ═══════════════════════════════════════════════════════════ */
        .categories-grid {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            scrollbar-width: none;
            padding: 4px 0 8px;
        }
        .categories-grid::-webkit-scrollbar { display: none; }
        .category-card {
            min-width: 180px;
            height: 120px;
            border-radius: var(--radius);
            overflow: hidden;
            position: relative;
            flex-shrink: 0;
        }
        .category-card img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s;
        }
        .category-card:hover img { transform: scale(1.08); }
        .category-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 60%, transparent 100%);
        }
        .category-card span {
            position: absolute;
            bottom: 14px;
            left: 14px;
            color: white;
            font-weight: 700;
            font-size: 14px;
            z-index: 1;
            text-shadow: 0 1px 4px rgba(0,0,0,0.3);
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* RECOMMENDATIONS                                            */
        /* ═══════════════════════════════════════════════════════════ */
        .reco-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .reco-card {
            border-radius: var(--radius);
            overflow: hidden;
            color: white;
            position: relative;
            transition: transform 0.3s;
        }
        .reco-card:hover { transform: translateY(-4px); }
        .reco-green { background: linear-gradient(135deg, #00635a, #00897b); }
        .reco-dark { background: linear-gradient(135deg, #0f172a, #1e293b); }
        .reco-amber { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .reco-card-image { height: 140px; overflow: hidden; }
        .reco-card-image img { width: 100%; height: 100%; object-fit: cover; opacity: 0.7; transition: transform 0.4s; }
        .reco-card:hover .reco-card-image img { transform: scale(1.06); }
        .reco-card h3 { font-family: var(--font-serif); font-size: 20px; padding: 20px 20px 8px; }
        .reco-card p { font-size: 14px; opacity: 0.85; padding: 0 20px; line-height: 1.5; }
        .reco-card-link { display: block; padding: 16px 20px; font-size: 14px; font-weight: 600; }
        .reco-card-link i { margin-left: 6px; transition: margin-left 0.2s; }
        .reco-card:hover .reco-card-link i { margin-left: 10px; }
        @media (max-width: 768px) { .reco-grid { grid-template-columns: 1fr; } }

        /* ═══════════════════════════════════════════════════════════ */
        /* ACTIVITY TICKER                                            */
        /* ═══════════════════════════════════════════════════════════ */
        .activity-section {
            background: white;
            border-top: 1px solid var(--gray-200);
            border-bottom: 1px solid var(--gray-200);
            padding: 14px 0;
            overflow: hidden;
        }
        .activity-track {
            display: flex;
            gap: 48px;
            animation: ticker 30s linear infinite;
            width: max-content;
        }
        .activity-item {
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            font-size: 14px;
            color: var(--gray-700);
        }
        .activity-item-avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: var(--primary); font-weight: 700;
        }
        .activity-item strong { color: var(--gray-900); }
        .activity-item .activity-stars { color: var(--accent); }
        @keyframes ticker {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* TOP BANNER                                                 */
        /* ═══════════════════════════════════════════════════════════ */
        .top-banner {
            display: flex;
            align-items: center;
            background: var(--dark);
            border-radius: var(--radius);
            overflow: hidden;
            color: white;
            transition: transform 0.3s;
        }
        .top-banner:hover { transform: translateY(-4px); }
        .top-banner-content { flex: 1; padding: 36px 40px; }
        .top-banner h2 { font-family: var(--font-serif); font-size: 28px; margin-bottom: 8px; }
        .top-banner p { font-size: 15px; opacity: 0.7; margin-bottom: 16px; }
        .top-banner-link { font-size: 14px; font-weight: 600; color: #5eead4; }
        .top-banner-image { width: 320px; height: 160px; overflow: hidden; flex-shrink: 0; }
        .top-banner-image img { width: 100%; height: 100%; object-fit: cover; }
        @media (max-width: 768px) {
            .top-banner { flex-direction: column; }
            .top-banner-content { padding: 28px 24px; }
            .top-banner-image { width: 100%; height: 120px; }
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* CITIES                                                     */
        /* ═══════════════════════════════════════════════════════════ */
        .cities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }
        .city-card {
            height: 140px;
            border-radius: var(--radius);
            overflow: hidden;
            position: relative;
            transition: transform 0.3s;
        }
        .city-card:hover { transform: translateY(-4px); }
        .city-card img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
        .city-card:hover img { transform: scale(1.08); }
        .city-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.65), transparent 60%);
        }
        .city-card-info {
            position: absolute;
            bottom: 14px; left: 14px;
            z-index: 1;
        }
        .city-card-info span {
            color: white;
            font-weight: 700;
            font-size: 16px;
            display: block;
        }
        .city-card-info small {
            color: rgba(255,255,255,0.75);
            font-size: 12px;
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* HOW IT WORKS                                               */
        /* ═══════════════════════════════════════════════════════════ */
        .how-section { background: white; padding: 64px 0; }
        .how-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
        }
        .how-item { text-align: center; padding: 20px; }
        .how-icon {
            width: 64px; height: 64px;
            background: var(--primary-light);
            border-radius: var(--radius);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }
        .how-icon i { font-size: 24px; color: var(--primary); }
        .how-title { font-size: 16px; font-weight: 700; color: var(--gray-900); margin-bottom: 8px; }
        .how-desc { font-size: 14px; color: var(--gray-500); line-height: 1.6; }
        @media (max-width: 768px) { .how-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; } }
        @media (max-width: 480px) { .how-grid { grid-template-columns: 1fr; } }

        /* ═══════════════════════════════════════════════════════════ */
        /* CTA RESTAURATEUR                                           */
        /* ═══════════════════════════════════════════════════════════ */
        .cta-section {
            background: var(--dark);
            padding: 80px 0;
        }
        .cta-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }
        .cta-text { color: white; }
        .cta-text h2 { font-family: var(--font-serif); font-size: 36px; margin-bottom: 16px; }
        .cta-text p { font-size: 16px; opacity: 0.8; line-height: 1.7; margin-bottom: 32px; }
        .cta-features { list-style: none; margin-bottom: 32px; }
        .cta-features li {
            display: flex; align-items: center; gap: 12px;
            font-size: 15px; color: rgba(255,255,255,0.9);
            padding: 8px 0;
        }
        .cta-features li i { color: #5eead4; font-size: 16px; }
        .cta-buttons { display: flex; gap: 16px; }
        .btn-cta-primary {
            background: var(--primary);
            color: white;
            padding: 14px 32px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 15px;
            border: none;
            transition: all 0.2s;
        }
        .btn-cta-primary:hover { background: #00897b; transform: translateY(-2px); }
        .btn-cta-outline {
            background: transparent;
            color: white;
            padding: 14px 32px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 15px;
            border: 2px solid rgba(255,255,255,0.3);
            transition: all 0.2s;
        }
        .btn-cta-outline:hover { border-color: white; }
        .cta-image {
            height: 380px;
            border-radius: var(--radius);
            overflow: hidden;
        }
        .cta-image img { width: 100%; height: 100%; object-fit: cover; }
        @media (max-width: 900px) {
            .cta-box { grid-template-columns: 1fr; gap: 32px; }
            .cta-image { height: 250px; }
            .cta-text h2 { font-size: 28px; }
        }

        /* ═══════════════════════════════════════════════════════════ */
        /* FOOTER                                                     */
        /* ═══════════════════════════════════════════════════════════ */
        .footer {
            background: #0c1222;
            color: rgba(255,255,255,0.7);
            padding: 60px 0 32px;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .footer-brand h3 {
            font-family: var(--font-serif);
            font-size: 24px;
            color: white;
            margin-bottom: 12px;
        }
        .footer-brand p { font-size: 14px; line-height: 1.7; margin-bottom: 20px; }
        .footer-socials { display: flex; gap: 12px; }
        .footer-socials a {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,0.6);
            transition: all 0.2s;
        }
        .footer-socials a:hover { background: var(--primary); color: white; }
        .footer-col h4 {
            color: white;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .footer-col a {
            display: block;
            font-size: 14px;
            padding: 5px 0;
            transition: color 0.2s;
        }
        .footer-col a:hover { color: white; }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        .footer-bottom-links { display: flex; gap: 20px; }
        .footer-bottom-links a:hover { color: white; }
        @media (max-width: 768px) {
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
        }
        @media (max-width: 480px) { .footer-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- HERO                                                    -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-content">
        <!-- Vertical tabs (Booking/TripAdvisor style) -->
        <div class="hero-tabs" id="heroTabs">
            <button class="hero-tab active" data-vertical="restaurants" onclick="switchVertical('restaurants')">
                <i class="fas fa-utensils"></i> Restaurants
            </button>
            <button class="hero-tab" data-vertical="activites" onclick="switchVertical('activites')">
                <i class="fas fa-compass"></i> Activités
            </button>
        </div>

        <h1 class="hero-title" id="heroTitle">Les meilleures tables d'Algérie</h1>
        <p class="hero-subtitle" id="heroSubtitle">Découvrez, comparez et choisissez parmi des centaines de restaurants vérifiés.</p>

        <form class="hero-search" action="/search" method="GET" id="heroSearchForm">
            <input type="hidden" name="lat" id="searchLat">
            <input type="hidden" name="lng" id="searchLng">
            <input type="hidden" name="radius" id="searchRadius" value="10">
            <input type="hidden" name="ville" id="searchVille">

            <div class="hero-search-inner">
                <i class="fas fa-search" id="heroSearchIcon"></i>
                <input type="text" id="globalSearchInput" name="q" placeholder="Une ville, un restaurant..." autocomplete="off">
            </div>
            <button type="submit" class="hero-search-btn">Rechercher</button>

            <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
        </form>

        <div class="hero-stats">
            <div class="hero-stat" id="heroStat1">
                <span class="hero-stat-number"><?= number_format($totalRestaurants ?? 0) ?></span>
                <span class="hero-stat-label">Restaurants</span>
            </div>
            <div class="hero-stat" id="heroStat2">
                <span class="hero-stat-number"><?= number_format($totalReviews ?? 757) ?></span>
                <span class="hero-stat-label">Avis</span>
            </div>
            <div class="hero-stat" id="heroStat3">
                <span class="hero-stat-number"><?= number_format($totalCities ?? 0) ?></span>
                <span class="hero-stat-label">Villes</span>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- CONTENU RESTAURANTS (visible par défaut)                    -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="contentRestaurants" data-vertical="restaurants">

<!-- RECENTLY VIEWED (from localStorage) -->
<section class="section" id="recentlyViewedSection" style="display:none">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-clock-rotate-left" style="color:var(--accent)"></i> Vus récemment</h2>
        </div>
        <div class="carousel-wrapper">
            <div class="carousel-track" id="recentlyViewedTrack" style="display:flex;gap:16px;overflow-x:auto;scroll-behavior:smooth;scrollbar-width:none;padding-bottom:8px"></div>
        </div>
    </div>
</section>
<script>
(function() {
    try {
        var items = JSON.parse(localStorage.getItem('lbr_recently_viewed') || '[]');
        if (items.length < 2) return;
        var track = document.getElementById('recentlyViewedTrack');
        var section = document.getElementById('recentlyViewedSection');
        if (!track || !section) return;
        items.forEach(function(r) {
            var card = document.createElement('a');
            card.href = '/restaurant/' + r.id;
            card.style.cssText = 'min-width:220px;max-width:220px;background:white;border:1px solid #e4e4e7;border-radius:14px;overflow:hidden;text-decoration:none;color:inherit;transition:transform 0.2s;flex-shrink:0';
            card.onmouseenter = function() { card.style.transform = 'translateY(-4px)'; };
            card.onmouseleave = function() { card.style.transform = ''; };
            var imgHtml = r.photo
                ? '<img src="' + r.photo + '" alt="' + (r.nom||'').replace(/"/g,'&quot;') + '" style="width:100%;height:120px;object-fit:cover" loading="lazy">'
                : '<div style="width:100%;height:120px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:32px;color:rgba(255,255,255,0.3)"><i class="fas fa-utensils"></i></div>';
            card.innerHTML = imgHtml + '<div style="padding:12px"><div style="font-weight:600;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">' + (r.nom||'') + '</div><div style="font-size:12px;color:#71717a;margin-top:4px">' + (r.cuisine||'') + (r.ville ? ' · ' + r.ville : '') + '</div>' + (r.note > 0 ? '<div style="margin-top:6px;font-size:12px"><i class="fas fa-star" style="color:#f59e0b"></i> ' + r.note + '</div>' : '') + '</div>';
            track.appendChild(card);
        });
        section.style.display = '';
    } catch(e) {}
})();
</script>

<?php if (!empty($topByRegion)): ?>
<?php foreach ($topByRegion as $regionIdx => $region): ?>
<section class="section" <?= $regionIdx % 2 === 1 ? 'style="background:#f8fafb"' : '' ?>>
    <div class="container">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-map-marker-alt" style="color:#00635a;font-size:20px"></i> Les meilleurs restos à <?= htmlspecialchars($region['wilaya']) ?></h2>
            <a href="/search?ville=<?= urlencode($region['wilaya']) ?>&sort=rating" class="section-link">
                Voir les <?= $region['count'] ?> restos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="carousel-wrapper">
            <?php if (count($region['restaurants']) > 4): ?>
            <button class="carousel-arrow left" data-target="regionCarousel<?= $regionIdx ?>"><i class="fas fa-chevron-left"></i></button>
            <?php endif; ?>
            <div class="carousel-track" id="regionCarousel<?= $regionIdx ?>">
                <?php foreach ($region['restaurants'] as $resto): ?>
                    <a href="/restaurant/<?= $resto['id'] ?>" class="resto-card">
                        <div class="resto-card-image">
                            <img src="<?= htmlspecialchars($resto['main_photo'] ?? '/assets/images/default-restaurant.jpg') ?>"
                                 alt="<?= htmlspecialchars($resto['nom']) ?>"
                                 loading="lazy"
                                 onerror="this.src='/assets/images/default-restaurant.jpg'">
                            <button class="resto-card-wishlist" data-id="<?= $resto['id'] ?>" onclick="event.preventDefault(); toggleWishlist(<?= $resto['id'] ?>)">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="resto-card-content">
                            <div class="resto-card-header">
                                <h3 class="resto-card-name"><?= htmlspecialchars($resto['nom']) ?></h3>
                                <?php if (!empty($resto['note_moyenne'])): ?>
                                    <div class="resto-card-rating">
                                        <?= number_format(min($resto['note_moyenne'], 5), 1) ?> <i class="fas fa-star"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="resto-card-info">
                                <?= htmlspecialchars($resto['type'] ?? 'Restaurant') ?> · <?= htmlspecialchars($resto['ville'] ?? '') ?>
                            </div>
                            <div class="resto-card-meta">
                                <?php if (!empty($resto['total_avis'])): ?>
                                    <span><i class="fas fa-comment"></i> <?= $resto['total_avis'] ?> avis</span>
                                <?php endif; ?>
                                <?php if (!empty($resto['price_range'])): ?>
                                    <span class="resto-card-price"><?= htmlspecialchars($resto['price_range']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (count($region['restaurants']) > 4): ?>
            <button class="carousel-arrow right" data-target="regionCarousel<?= $regionIdx ?>"><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($recentReviews)): ?>
<div class="activity-section">
    <div class="activity-track">
        <?php foreach ($recentReviews as $review): ?>
            <div class="activity-item">
                <div class="activity-item-avatar"><?= strtoupper(mb_substr($review['user_prenom'] ?? 'U', 0, 1)) ?></div>
                <span><strong><?= htmlspecialchars($review['user_prenom'] ?? 'Un utilisateur') ?></strong> a noté
                <strong><?= htmlspecialchars($review['restaurant_nom'] ?? 'un restaurant') ?></strong>
                <span class="activity-stars"><?= str_repeat('★', round($review['note_globale'] ?? 4)) ?></span></span>
            </div>
        <?php endforeach; ?>
        <?php foreach ($recentReviews as $review): ?>
            <div class="activity-item">
                <div class="activity-item-avatar"><?= strtoupper(mb_substr($review['user_prenom'] ?? 'U', 0, 1)) ?></div>
                <span><strong><?= htmlspecialchars($review['user_prenom'] ?? 'Un utilisateur') ?></strong> a noté
                <strong><?= htmlspecialchars($review['restaurant_nom'] ?? 'un restaurant') ?></strong>
                <span class="activity-stars"><?= str_repeat('★', round($review['note_globale'] ?? 4)) ?></span></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($popularCuisines)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Vos envies du moment</h2>
        </div>
        <div class="categories-grid">
            <?php foreach ($popularCuisines as $cuisine):
                $cuisineType = $cuisine['type'] ?? '';
                $imageKey = $cuisineImages[$cuisineType] ?? 'default';
            ?>
                <a href="/search?type=<?= urlencode($cuisineType) ?>" class="category-card">
                    <img src="/assets/images/cuisines/<?= $imageKey ?>.jpg"
                         alt="<?= htmlspecialchars($cuisineType) ?>"
                         loading="lazy"
                         onerror="this.src='/assets/images/cuisines/default.jpg'">
                    <span><?= htmlspecialchars($cuisineType) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($latestRestaurants)): ?>
<section class="section" style="background: white;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Derniers ajouts</h2>
            <a href="/search" class="section-link">Voir tout <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="resto-grid-4">
            <?php foreach (array_slice($latestRestaurants, 0, 4) as $resto): ?>
                <a href="/restaurant/<?= $resto['id'] ?>" class="resto-card">
                    <div class="resto-card-image">
                        <img src="<?= htmlspecialchars($resto['main_photo'] ?? '/assets/images/default-restaurant.jpg') ?>"
                             alt="<?= htmlspecialchars($resto['nom']) ?>" loading="lazy"
                             onerror="this.src='/assets/images/default-restaurant.jpg'">
                        <button class="resto-card-wishlist" data-id="<?= $resto['id'] ?>" onclick="event.preventDefault(); toggleWishlist(<?= $resto['id'] ?>)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="resto-card-content">
                        <div class="resto-card-header">
                            <h3 class="resto-card-name"><?= htmlspecialchars($resto['nom']) ?></h3>
                            <?php if (!empty($resto['note_moyenne'])): ?>
                                <div class="resto-card-rating"><?= number_format(min($resto['note_moyenne'], 5), 1) ?> <i class="fas fa-star"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="resto-card-info"><?= htmlspecialchars($resto['type'] ?? 'Restaurant') ?> · <?= htmlspecialchars($resto['ville'] ?? '') ?></div>
                        <div class="resto-card-meta">
                            <?php if (!empty($resto['total_avis'])): ?><span><i class="fas fa-comment"></i> <?= $resto['total_avis'] ?> avis</span><?php endif; ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">LeBonResto recommande</h2>
        </div>
        <div class="reco-grid">
            <a href="/search?type=Restaurant+de+grillades&sort=rating" class="reco-card reco-green">
                <div class="reco-card-image">
                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=300&h=200&fit=crop" alt="Grillades">
                </div>
                <h3>Restaurants de grillades</h3>
                <p>Les meilleures grillades et brochettes d'Algérie</p>
                <span class="reco-card-link">Voir les restaurants <i class="fas fa-arrow-right"></i></span>
            </a>
            <a href="/search?sort=rating" class="reco-card reco-dark">
                <div class="reco-card-image">
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=300&h=200&fit=crop" alt="Top notés">
                </div>
                <h3>Les mieux notés</h3>
                <p>Les restaurants plébiscités par notre communauté</p>
                <span class="reco-card-link">Voir les restaurants <i class="fas fa-arrow-right"></i></span>
            </a>
            <a href="/search?sort=newest" class="reco-card reco-amber">
                <div class="reco-card-image">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=300&h=200&fit=crop" alt="Nouveautés">
                </div>
                <h3>Nouveautés</h3>
                <p>Les derniers restaurants ajoutés sur LeBonResto</p>
                <span class="reco-card-link">Voir les restaurants <i class="fas fa-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<section class="section" style="padding: 0 0 56px;">
    <div class="container">
        <a href="/search?sort=rating&top=100" class="top-banner">
            <div class="top-banner-content">
                <h2>Découvrez le Top 100</h2>
                <p>Les meilleurs restaurants d'Algérie selon vos avis</p>
                <span class="top-banner-link">Explorer le classement <i class="fas fa-arrow-right"></i></span>
            </div>
            <div class="top-banner-image">
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=200&fit=crop" alt="Top 100">
            </div>
        </a>
    </div>
</section>

</div><!-- /contentRestaurants -->

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- CONTENU ACTIVITES (masqué par défaut)                      -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="contentActivites" data-vertical="activites" style="display:none;">

<?php
$actCategoryIcons = [
    'plage' => 'fa-umbrella-beach', 'parc' => 'fa-tree', 'monument' => 'fa-landmark',
    'musee' => 'fa-building-columns', 'shopping' => 'fa-bag-shopping', 'divertissement' => 'fa-masks-theater',
    'nightlife' => 'fa-moon', 'cafe' => 'fa-mug-hot', 'nature' => 'fa-mountain-sun',
    'sport' => 'fa-futbol', 'religieux' => 'fa-mosque', 'culturel' => 'fa-palette',
];
$actCategoryLabels = [
    'plage' => 'Plage', 'parc' => 'Parc', 'monument' => 'Monument', 'musee' => 'Musée',
    'shopping' => 'Shopping', 'divertissement' => 'Divertissement', 'nightlife' => 'Vie nocturne',
    'cafe' => 'Café', 'nature' => 'Nature', 'sport' => 'Sport', 'religieux' => 'Religieux', 'culturel' => 'Culturel',
];
$actCategoryImages = [
    'plage' => 'photo-1507525428034-b723cf961d3e', 'parc' => 'photo-1441974231531-c6227db76b6e',
    'monument' => 'photo-1564507592333-c60657eea523', 'musee' => 'photo-1554907984-15263bfd63bd',
    'shopping' => 'photo-1441986300917-64674bd600d8', 'divertissement' => 'photo-1513151233558-d860c5398176',
    'nightlife' => 'photo-1514525253161-7a46d19cd819', 'cafe' => 'photo-1501339847302-ac426a4a7cbb',
    'nature' => 'photo-1469474968028-56623f02e42e', 'sport' => 'photo-1461896836934-bd45ba7e5a6a',
    'religieux' => 'photo-1585129777188-94600bc7b4b3', 'culturel' => 'photo-1518998053901-5348d3961a04',
];
?>

<!-- Popular activities carousel -->
<?php if (!empty($popularActivities)): ?>
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Lieux populaires</h2>
            <a href="/activites?sort=popular" class="section-link">Voir tout <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="carousel-wrapper">
            <div class="carousel-track" id="actPopCarousel">
                <?php foreach ($popularActivities as $pact):
                    $pCat = $pact['category'] ?? '';
                    $pCatIcon = $actCategoryIcons[$pCat] ?? 'fa-map-pin';
                    $pCatLabel = $actCategoryLabels[$pCat] ?? ucfirst($pCat);
                ?>
                <a href="/activite/<?= htmlspecialchars($pact['slug']) ?>" class="resto-card">
                    <div class="resto-card-image">
                        <?php if (!empty($pact['main_photo'])): ?>
                            <img src="<?= htmlspecialchars($pact['main_photo']) ?>" alt="<?= htmlspecialchars($pact['nom']) ?>" loading="lazy"
                                 onerror="this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=400&h=250&fit=crop'">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=400&h=250&fit=crop" alt="<?= htmlspecialchars($pact['nom']) ?>" loading="lazy">
                        <?php endif; ?>
                        <span class="resto-card-badge" style="background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);">
                            <i class="fas <?= $pCatIcon ?>"></i> <?= $pCatLabel ?>
                        </span>
                    </div>
                    <div class="resto-card-content">
                        <div class="resto-card-header">
                            <h3 class="resto-card-name"><?= htmlspecialchars($pact['nom']) ?></h3>
                            <?php if (!empty($pact['note_moyenne']) && $pact['note_moyenne'] > 0): ?>
                                <div class="resto-card-rating"><?= number_format(min($pact['note_moyenne'], 5), 1) ?> <i class="fas fa-star"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="resto-card-info"><i class="fas fa-map-marker-alt" style="font-size:12px;color:#00635a;"></i> <?= htmlspecialchars($pact['ville'] ?? '') ?></div>
                        <div class="resto-card-meta">
                            <?php if (!empty($pact['nb_avis'])): ?><span><i class="fas fa-comment"></i> <?= $pact['nb_avis'] ?> avis</span><?php endif; ?>
                            <?php if (($pact['price_range'] ?? '') === 'gratuit'): ?><span style="color:#10b981;font-weight:600;">Gratuit</span><?php endif; ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Activity categories -->
<section class="section" style="background:white;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Explorer par catégorie</h2>
        </div>
        <div class="categories-grid">
            <?php
            $showCats = ['plage','parc','monument','musee','shopping','divertissement','nightlife','nature'];
            foreach ($showCats as $sCat):
                $sIcon = $actCategoryIcons[$sCat] ?? 'fa-map-pin';
                $sLabel = $actCategoryLabels[$sCat] ?? ucfirst($sCat);
                $sImg = $actCategoryImages[$sCat] ?? 'photo-1469474968028-56623f02e42e';
            ?>
                <a href="/activites?category=<?= $sCat ?>" class="category-card">
                    <img src="https://images.unsplash.com/<?= $sImg ?>?w=300&h=180&fit=crop" alt="<?= $sLabel ?>" loading="lazy">
                    <span><i class="fas <?= $sIcon ?>"></i> <?= $sLabel ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Reco cards for activities -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Nos suggestions</h2>
        </div>
        <div class="reco-grid">
            <a href="/activites?category=plage" class="reco-card reco-green">
                <div class="reco-card-image">
                    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300&h=200&fit=crop" alt="Plages">
                </div>
                <h3>Plages & Bord de mer</h3>
                <p>Les plus belles plages d'Algérie</p>
                <span class="reco-card-link">Voir les plages <i class="fas fa-arrow-right"></i></span>
            </a>
            <a href="/activites?category=monument&sort=rating" class="reco-card reco-dark">
                <div class="reco-card-image">
                    <img src="https://images.unsplash.com/photo-1564507592333-c60657eea523?w=300&h=200&fit=crop" alt="Monuments">
                </div>
                <h3>Monuments & Histoire</h3>
                <p>Les sites historiques incontournables</p>
                <span class="reco-card-link">Voir les monuments <i class="fas fa-arrow-right"></i></span>
            </a>
            <a href="/activites?category=parc" class="reco-card reco-amber">
                <div class="reco-card-image">
                    <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=300&h=200&fit=crop" alt="Nature">
                </div>
                <h3>Parcs & Nature</h3>
                <p>Espaces verts et escapades nature</p>
                <span class="reco-card-link">Voir les parcs <i class="fas fa-arrow-right"></i></span>
            </a>
        </div>
    </div>
</section>

<!-- CTA banner activities -->
<section class="section" style="padding: 0 0 56px;">
    <div class="container">
        <a href="/activites" class="top-banner">
            <div class="top-banner-content">
                <h2><?= $totalActivities ?? 0 ?> lieux à découvrir</h2>
                <p>Parcs, plages, monuments, musées à travers l'Algérie</p>
                <span class="top-banner-link">Explorer les activités <i class="fas fa-arrow-right"></i></span>
            </div>
            <div class="top-banner-image">
                <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=400&h=200&fit=crop" alt="Activités">
            </div>
        </a>
    </div>
</section>

</div><!-- /contentActivites -->

<!-- ═══════════════════════════════════════════════════════ -->
<!-- VILLES POPULAIRES                                       -->
<!-- ═══════════════════════════════════════════════════════ -->
<?php if (!empty($popularCities)): ?>
<section class="section" style="background: white;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Explorer par ville</h2>
            <a href="/search" class="section-link">
                Voir plus <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="cities-grid">
            <?php foreach ($popularCities as $city): ?>
                <a href="/search?ville=<?= urlencode($city['ville'] ?? '') ?>" class="city-card">
                    <img src="/assets/images/cities/<?= strtolower($city['ville'] ?? 'default') ?>.jpg"
                         alt="<?= htmlspecialchars($city['ville'] ?? '') ?>"
                         loading="lazy"
                         onerror="this.src='/assets/images/cities/default.jpg'">
                    <div class="city-card-info">
                        <span><?= htmlspecialchars($city['ville'] ?? '') ?></span>
                        <small><?= $city['count'] ?? '' ?> restaurants</small>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- COMMENT CA MARCHE                                       -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="how-section">
    <div class="container">
        <div class="section-header" style="justify-content: center;">
            <h2 class="section-title">Comment ça marche ?</h2>
        </div>
        <div class="how-grid">
            <div class="how-item">
                <div class="how-icon"><i class="fas fa-search"></i></div>
                <h3 class="how-title">Choisissez un restaurant</h3>
                <p class="how-desc">Filtrez selon vos envies : cuisine, localisation, avis et budget.</p>
            </div>
            <div class="how-item">
                <div class="how-icon"><i class="fas fa-star"></i></div>
                <h3 class="how-title">Consultez les avis</h3>
                <p class="how-desc">Des centaines d'avis authentiques pour faire le bon choix.</p>
            </div>
            <div class="how-item">
                <div class="how-icon"><i class="fas fa-map-marker-alt"></i></div>
                <h3 class="how-title">Trouvez l'adresse</h3>
                <p class="how-desc">Localisez facilement le restaurant sur la carte interactive.</p>
            </div>
            <div class="how-item">
                <div class="how-icon"><i class="fas fa-utensils"></i></div>
                <h3 class="how-title">Bon appétit !</h3>
                <p class="how-desc">Profitez de votre repas et partagez votre expérience.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- CTA RESTAURATEUR                                        -->
<!-- ═══════════════════════════════════════════════════════ -->
<section class="cta-section">
    <div class="container">
        <div class="cta-box">
            <div class="cta-text">
                <h2>Êtes-vous restaurateur ?</h2>
                <p>Rejoignez LeBonResto et atteignez des milliers de clients potentiels chaque jour.</p>
                <ul class="cta-features">
                    <li><i class="fas fa-check-circle"></i> Page personnalisée pour votre restaurant</li>
                    <li><i class="fas fa-check-circle"></i> Répondez aux avis de vos clients</li>
                    <li><i class="fas fa-check-circle"></i> Statistiques de visibilité détaillées</li>
                    <li><i class="fas fa-check-circle"></i> Inscription gratuite et rapide</li>
                </ul>
                <div class="cta-buttons">
                    <a href="/add-restaurant" class="btn-cta-primary">Inscrire mon restaurant</a>
                    <a href="/contact" class="btn-cta-outline">En savoir plus</a>
                </div>
            </div>
            <div class="cta-image">
                <img src="https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=500&h=400&fit=crop" alt="Restaurateur">
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- FOOTER                                                  -->
<!-- ═══════════════════════════════════════════════════════ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>LeBonResto</h3>
                <p>Le guide des meilleurs restaurants d'Algérie. Avis, photos, cartes et horaires pour vous aider à trouver la table parfaite.</p>
                <div class="footer-socials">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h4>Découvrir</h4>
                <a href="/search?sort=rating">Les mieux notés</a>
                <a href="/search?sort=newest">Nouveautés</a>
                <a href="/search">Tous les restaurants</a>
                <a href="/activites">Activités & Sorties</a>
                <a href="/search?type=Pizzeria">Pizzerias</a>
            </div>
            <div class="footer-col">
                <h4>Villes</h4>
                <?php foreach (array_slice($popularCities ?? [], 0, 5) as $city): ?>
                    <a href="/search?ville=<?= urlencode($city['ville'] ?? '') ?>"><?= htmlspecialchars($city['ville'] ?? '') ?></a>
                <?php endforeach; ?>
            </div>
            <div class="footer-col">
                <h4>Informations</h4>
                <a href="/add-restaurant">Ajouter un restaurant</a>
                <a href="/contact">Contact</a>
                <a href="/cgu">Conditions d'utilisation</a>
                <a href="/confidentialite">Confidentialité</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?= date('Y') ?> LeBonResto. Tous droits réservés.</span>
            <div class="footer-bottom-links">
                <a href="/cgu">CGU</a>
                <a href="/confidentialite">Politique de confidentialité</a>
            </div>
        </div>
    </div>
</footer>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- JAVASCRIPT                                              -->
<!-- ═══════════════════════════════════════════════════════ -->
<script>
// ═══════════════════════════════════════
// HERO VERTICAL TABS
// ═══════════════════════════════════════
function switchVertical(vertical) {
    // Update active tab
    document.querySelectorAll('.hero-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.hero-tab[data-vertical="${vertical}"]`).classList.add('active');

    const form = document.getElementById('heroSearchForm');
    const input = document.getElementById('globalSearchInput');
    const title = document.getElementById('heroTitle');
    const subtitle = document.getElementById('heroSubtitle');
    const icon = document.getElementById('heroSearchIcon');

    var s1 = document.querySelector('#heroStat1 .hero-stat-number');
    var l1 = document.querySelector('#heroStat1 .hero-stat-label');
    var s2 = document.querySelector('#heroStat2 .hero-stat-number');
    var l2 = document.querySelector('#heroStat2 .hero-stat-label');
    var s3 = document.querySelector('#heroStat3 .hero-stat-number');
    var l3 = document.querySelector('#heroStat3 .hero-stat-label');

    if (vertical === 'restaurants') {
        form.action = '/search';
        input.placeholder = 'Une ville, un restaurant...';
        title.textContent = "Les meilleures tables d'Algérie";
        subtitle.textContent = 'Découvrez, comparez et choisissez parmi des centaines de restaurants vérifiés.';
        icon.className = 'fas fa-search';
        s1.textContent = '<?= number_format($totalRestaurants ?? 0) ?>'; l1.textContent = 'Restaurants';
        s2.textContent = '<?= number_format($totalReviews ?? 0) ?>'; l2.textContent = 'Avis';
        s3.textContent = '<?= number_format($totalCities ?? 0) ?>'; l3.textContent = 'Villes';
    } else if (vertical === 'activites') {
        form.action = '/activites';
        input.placeholder = 'Une ville, une activité, un lieu...';
        title.textContent = "Les meilleures sorties d'Algérie";
        subtitle.textContent = 'Parcs, monuments, plages, musées — explorez les incontournables près de chez vous.';
        icon.className = 'fas fa-compass';
        s1.textContent = '<?= number_format($activityStats['total'] ?? 0) ?>'; l1.textContent = 'Lieux & Activités';
        s2.textContent = '<?= $activityStats['categories'] ?? 0 ?>'; l2.textContent = 'Catégories';
        s3.textContent = '<?= $activityStats['villes'] ?? 0 ?>'; l3.textContent = 'Villes';
    }

    // Toggle content sections (only target content divs, not hero tab buttons)
    document.getElementById('contentRestaurants').style.display = (vertical === 'restaurants') ? '' : 'none';
    document.getElementById('contentActivites').style.display = (vertical === 'activites') ? '' : 'none';

    // Persist in URL hash so browser back button restores state
    history.replaceState({ vertical: vertical }, '', '#' + vertical);

    // Clear and refocus
    input.value = '';
    input.focus();

    // Scroll to top of content smoothly
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Restore vertical from URL hash on page load
(function() {
    var hash = window.location.hash.replace('#', '');
    if (hash === 'activites') {
        switchVertical('activites');
    }
})();

// ═══════════════════════════════════════
// CAROUSEL ARROWS
// ═══════════════════════════════════════
document.querySelectorAll('.carousel-arrow').forEach(btn => {
    btn.addEventListener('click', function() {
        const track = document.getElementById(this.dataset.target);
        if (!track) return;
        const scrollAmount = 300;
        track.scrollBy({
            left: this.classList.contains('left') ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
        });
    });
});

// ═══════════════════════════════════════
// AUTOCOMPLETE
// ═══════════════════════════════════════
(function() {
    const input = document.getElementById('globalSearchInput');
    const dropdown = document.getElementById('autocompleteDropdown');
    const form = document.getElementById('heroSearchForm');
    const latInput = document.getElementById('searchLat');
    const lngInput = document.getElementById('searchLng');
    const radiusInput = document.getElementById('searchRadius');
    const villeInput = document.getElementById('searchVille');

    let debounceTimer = null;
    let selectedIndex = -1;
    let currentItems = [];
    const RECENT_KEY = 'lebonresto_recent_searches';
    const MAX_RECENT = 5;

    input.addEventListener('focus', function() {
        if (this.value.trim().length < 2) showRecentSearches();
    });

    input.addEventListener('input', function() {
        const query = this.value.trim();
        selectedIndex = -1;
        if (query.length < 2) { showRecentSearches(); return; }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => searchAutocomplete(query), 300);
    });

    input.addEventListener('keydown', function(e) {
        if (!dropdown.classList.contains('show')) return;
        const items = dropdown.querySelectorAll('.autocomplete-item');
        if (e.key === 'ArrowDown') { e.preventDefault(); selectedIndex = Math.min(selectedIndex + 1, items.length - 1); updateSelection(items); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); selectedIndex = Math.max(selectedIndex - 1, 0); updateSelection(items); }
        else if (e.key === 'Enter' && selectedIndex >= 0) { e.preventDefault(); items[selectedIndex].click(); }
        else if (e.key === 'Escape') { hideDropdown(); }
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) hideDropdown();
    });

    async function searchAutocomplete(query) {
        showLoading();
        try {
            const response = await fetch(`/api/search/autocomplete?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            if (!data.success) { showEmpty('Erreur de recherche'); return; }
            renderResults(data);
        } catch (error) {
            showEmpty('Erreur de connexion');
        }
    }

    function renderResults(data) {
        const { villes, restaurants, voir_tous, query } = data;
        if (villes.length === 0 && restaurants.length === 0) { showEmpty('Aucun résultat pour "' + escapeHtml(query) + '"'); return; }
        let html = '';
        currentItems = [];

        if (villes.length > 0) {
            villes.forEach(ville => {
                currentItems.push({ type: 'ville', data: ville });
                const wilayaSlug = ville.wilaya.toLowerCase().replace(/\s+/g, '-').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                html += `<div class="autocomplete-item" data-type="ville" data-index="${currentItems.length - 1}">
                    <img src="/assets/images/wilayas/${wilayaSlug}.jpg" class="autocomplete-item-photo" alt="${escapeHtml(ville.commune)}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=100&h=100&fit=crop'">
                    <div class="autocomplete-item-content">
                        <div class="autocomplete-item-title">${highlightMatch(ville.commune, query)}</div>
                        <div class="autocomplete-item-subtitle">${escapeHtml(ville.wilaya)}, Algérie</div>
                    </div>
                </div>`;
            });
        }

        if (restaurants.length > 0) {
            restaurants.forEach(resto => {
                currentItems.push({ type: 'restaurant', data: resto });
                const photoUrl = resto.photo || 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=100&h=100&fit=crop';
                const ratingHtml = resto.note_moyenne > 0 ? `<span class="autocomplete-item-rating"><i class="fas fa-star"></i> ${resto.note_moyenne}</span>` : '';
                html += `<div class="autocomplete-item" data-type="restaurant" data-index="${currentItems.length - 1}">
                    <img src="${escapeHtml(photoUrl)}" class="autocomplete-item-photo" alt="${escapeHtml(resto.nom)}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=100&h=100&fit=crop'">
                    <div class="autocomplete-item-content">
                        <div class="autocomplete-item-title">${highlightMatch(resto.nom, query)}</div>
                        <div class="autocomplete-item-subtitle">${escapeHtml(resto.ville || '')}${resto.wilaya && resto.wilaya !== resto.ville ? ', ' + escapeHtml(resto.wilaya) : ''} ${ratingHtml}</div>
                    </div>
                </div>`;
            });
        }

        if (voir_tous) {
            currentItems.push({ type: 'voir_tous', data: voir_tous });
            html += `<div class="autocomplete-item autocomplete-voir-tous-item" data-type="voir_tous" data-index="${currentItems.length - 1}">
                <div class="autocomplete-item-icon-small"><i class="fas fa-search"></i></div>
                <div class="autocomplete-item-content"><div class="autocomplete-item-title">${escapeHtml(voir_tous.label)}</div></div>
                <i class="fas fa-chevron-right autocomplete-item-arrow"></i>
            </div>`;
        }

        dropdown.innerHTML = html;
        showDropdown();
        attachItemListeners();
    }

    function showRecentSearches() {
        const recent = getRecentSearches();
        if (recent.length === 0) { hideDropdown(); return; }
        let html = '<div class="autocomplete-recent-header"><i class="fas fa-clock"></i> Consultés récemment</div>';
        currentItems = [];
        recent.forEach((item, i) => {
            currentItems.push(item);
            if (item.type === 'ville') {
                const wilayaSlug = item.data.wilaya.toLowerCase().replace(/\s+/g, '-').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                html += `<div class="autocomplete-item" data-type="ville" data-index="${i}">
                    <img src="/assets/images/wilayas/${wilayaSlug}.jpg" class="autocomplete-item-photo" alt="${escapeHtml(item.data.commune)}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=100&h=100&fit=crop'">
                    <div class="autocomplete-item-content">
                        <div class="autocomplete-item-title">${escapeHtml(item.data.commune)}</div>
                        <div class="autocomplete-item-subtitle">${escapeHtml(item.data.wilaya)}, Algérie</div>
                    </div>
                </div>`;
            } else if (item.type === 'restaurant') {
                const photoUrl = item.data.photo || 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=100&h=100&fit=crop';
                html += `<div class="autocomplete-item" data-type="restaurant" data-index="${i}">
                    <img src="${escapeHtml(photoUrl)}" class="autocomplete-item-photo" alt="${escapeHtml(item.data.nom)}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=100&h=100&fit=crop'">
                    <div class="autocomplete-item-content">
                        <div class="autocomplete-item-title">${escapeHtml(item.data.nom)}</div>
                        <div class="autocomplete-item-subtitle">${escapeHtml(item.data.ville || '')}, Algérie</div>
                    </div>
                </div>`;
            }
        });
        dropdown.innerHTML = html;
        showDropdown();
        attachItemListeners();
    }

    function attachItemListeners() {
        dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
                const type = this.dataset.type;
                const index = parseInt(this.dataset.index);
                const itemData = currentItems[index];
                if (type === 'ville') selectVille(itemData.data);
                else if (type === 'restaurant') selectRestaurant(itemData.data);
                else if (type === 'voir_tous') selectVoirTous(itemData.data);
            });
        });
    }

    function selectVille(ville) {
        saveRecentSearch({ type: 'ville', data: ville });
        input.value = ville.commune;
        latInput.value = ville.lat; lngInput.value = ville.lng;
        villeInput.value = ville.commune; radiusInput.value = '10';
        hideDropdown(); form.submit();
    }

    function selectRestaurant(resto) {
        saveRecentSearch({ type: 'restaurant', data: resto });
        window.location.href = '/restaurant/' + resto.id;
    }

    function selectVoirTous(voirTous) { window.location.href = voirTous.url; }

    function getRecentSearches() {
        try { const data = localStorage.getItem(RECENT_KEY); return data ? JSON.parse(data) : []; }
        catch { return []; }
    }

    function saveRecentSearch(item) {
        try {
            let recent = getRecentSearches();
            recent = recent.filter(r => {
                if (r.type !== item.type) return true;
                if (item.type === 'ville') return r.data.commune !== item.data.commune;
                if (item.type === 'restaurant') return r.data.id !== item.data.id;
                return true;
            });
            recent.unshift(item);
            recent = recent.slice(0, MAX_RECENT);
            localStorage.setItem(RECENT_KEY, JSON.stringify(recent));
        } catch (e) {}
    }

    function showDropdown() { dropdown.classList.add('show'); }
    function hideDropdown() { dropdown.classList.remove('show'); selectedIndex = -1; }
    function showLoading() { dropdown.innerHTML = '<div class="autocomplete-loading"><i class="fas fa-spinner fa-spin"></i> Recherche...</div>'; showDropdown(); }
    function showEmpty(message) { dropdown.innerHTML = `<div class="autocomplete-empty">${escapeHtml(message)}</div>`; showDropdown(); }
    function updateSelection(items) {
        items.forEach((item, i) => item.classList.toggle('selected', i === selectedIndex));
        if (selectedIndex >= 0 && items[selectedIndex]) items[selectedIndex].scrollIntoView({ block: 'nearest' });
    }
    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        return escapeHtml(text).replace(new RegExp(`(${escapeRegex(query)})`, 'gi'), '<mark>$1</mark>');
    }
    function escapeHtml(text) { if (!text) return ''; const d = document.createElement('div'); d.textContent = text; return d.innerHTML; }
    function escapeRegex(string) { return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
})();

// ═══════════════════════════════════════
// WISHLIST
// ═══════════════════════════════════════
async function toggleWishlist(restaurantId) {
    try {
        const response = await fetch('/api/wishlist/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ restaurant_id: restaurantId })
        });
        const data = await response.json();
        if (data.success) {
            document.querySelectorAll(`.resto-card-wishlist[data-id="${restaurantId}"]`).forEach(btn => {
                btn.classList.toggle('active', data.added);
                const icon = btn.querySelector('i');
                icon.classList.toggle('far', !data.added);
                icon.classList.toggle('fas', data.added);
            });
        } else if (data.message === 'Non authentifié') {
            openAuthModal();
        }
    } catch (error) {}
}

document.addEventListener('DOMContentLoaded', async function() {
    const wishlistBtns = document.querySelectorAll('.resto-card-wishlist');
    if (wishlistBtns.length === 0) return;
    const ids = Array.from(wishlistBtns).map(btn => btn.dataset.id);
    try {
        const response = await fetch('/api/wishlist/check-multiple', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: ids })
        });
        const data = await response.json();
        if (data.success && data.wishlist) {
            data.wishlist.forEach(id => {
                document.querySelectorAll(`.resto-card-wishlist[data-id="${id}"]`).forEach(btn => {
                    btn.classList.add('active');
                    const icon = btn.querySelector('i');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                });
            });
        }
    } catch (error) {}
});
</script>

</body>
</html>
