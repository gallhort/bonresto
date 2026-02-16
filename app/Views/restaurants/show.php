<?php
/**
 * PAGE DÉTAILS RESTAURANT - LEBONRESTO
 * Design Premium inspiré TheFork / Google Maps / TripAdvisor
 */
$r = $restaurant;
$photos = $r['photos'] ?? [];
$horaires = $r['horaires'] ?? [];
$reviews = $r['reviews'] ?? [];
$stats = $r['rating_stats'] ?? [];
$similar = $r['similar'] ?? [];
$isOpen = $r['is_open_now'] ?? ['is_open' => false, 'message' => 'Horaires non disponibles'];

$mainPhoto = null;
foreach ($photos as $p) {
    if ($p['type'] === 'main') { $mainPhoto = $p['path']; break; }
}
if (!$mainPhoto && !empty($photos)) { $mainPhoto = $photos[0]['path']; }

$joursComplets = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$jourActuel = (int)(new DateTime())->format('N') - 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? $r['nom']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($meta_description ?? '') ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    
    <style>
        :root {
            --primary: #1a1a1a;
            --accent: #00875A;
            --accent-light: #00a86b;
            --accent-bg: #e8f5f0;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #fafafa;
            --gray-100: #f4f4f5;
            --gray-200: #e4e4e7;
            --gray-300: #d4d4d8;
            --gray-400: #a1a1aa;
            --gray-500: #71717a;
            --gray-600: #52525b;
            --gray-700: #3f3f46;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow: 0 4px 24px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 48px rgba(0,0,0,0.12);
            --font-display: 'Playfair Display', Georgia, serif;
            --font-body: 'DM Sans', -apple-system, sans-serif;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: var(--font-body); background: var(--gray-50); color: var(--primary); line-height: 1.6; -webkit-font-smoothing: antialiased; }
        img { max-width: 100%; height: auto; }
        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; cursor: pointer; border: none; background: none; }

        /* HEADER STICKY */
        .page-header {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--gray-200);
            transform: translateY(-100%); transition: var(--transition);
        }
        .page-header.visible { transform: translateY(0); }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .header-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .header-back { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--gray-100); transition: var(--transition); }
        .header-back:hover { background: var(--gray-200); }
        .header-title { font-size: 16px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .header-rating { display: flex; align-items: center; gap: 4px; font-size: 14px; color: var(--gray-600); }
        .header-rating i { color: var(--warning); font-size: 12px; }
        .header-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .header-btn { padding: 10px 20px; border-radius: 24px; font-size: 14px; font-weight: 600; transition: var(--transition); }
        .header-btn.primary { background: var(--accent); color: white; }
        .header-btn.primary:hover { background: var(--accent-light); }

        /* HERO GALLERY */
        .hero-gallery { position: relative; height: 55vh; min-height: 400px; max-height: 600px; background: var(--primary); overflow: hidden; }
        .gallery-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 4px; height: 100%; }
        .gallery-item { position: relative; overflow: hidden; cursor: pointer; }
        .gallery-item:first-child { grid-row: span 2; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .gallery-item:hover img { transform: scale(1.05); }
        .gallery-item::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.3)); pointer-events: none; }
        .gallery-placeholder { width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 80px; color: rgba(255,255,255,0.3); }
        .gallery-nav { position: absolute; top: 20px; left: 20px; right: 20px; display: flex; justify-content: space-between; align-items: flex-start; z-index: 10; }
        .nav-btn { width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.95); display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--primary); transition: var(--transition); box-shadow: var(--shadow); }
        .nav-btn:hover { transform: scale(1.1); box-shadow: var(--shadow-lg); }
        .nav-actions { display: flex; gap: 10px; }
        .nav-btn.active, .nav-btn.active i { color: #e74c3c; }
        .btn-photos { position: absolute; bottom: 20px; right: 20px; z-index: 10; padding: 12px 20px; background: rgba(0,0,0,0.75); color: white; border-radius: 24px; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 8px; transition: var(--transition); }
        .btn-photos:hover { background: rgba(0,0,0,0.9); }

        @media (max-width: 768px) {
            .hero-gallery { height: 45vh; min-height: 320px; }
            .gallery-grid { grid-template-columns: 1fr; grid-template-rows: 1fr; }
            .gallery-item:not(:first-child) { display: none; }
            .gallery-item:first-child { grid-row: span 1; }
        }

        /* LAYOUT */
        .main-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .content-grid { display: grid; grid-template-columns: 1fr 380px; gap: 40px; padding: 40px 0; }
        @media (max-width: 1024px) { .content-grid { grid-template-columns: 1fr; gap: 24px; padding: 24px 0; } }

        /* RESTO HEADER */
        .resto-header { padding-bottom: 32px; border-bottom: 1px solid var(--gray-200); margin-bottom: 32px; }
        .resto-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 500; }
        .badge-halal { background: #dcfce7; color: #166534; }
        .badge-verified { background: #dbeafe; color: #1e40af; }
        .badge-featured { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; }
        .badge-award-tc { background: linear-gradient(135deg, #fef3c7, #fbbf24); color: #78350f; font-weight: 600; }
        .badge-award-top { background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #3730a3; }
        .badge-award-cuisine { background: linear-gradient(135deg, #fce7f3, #fbcfe8); color: #9d174d; }
        .badge-award-trending { background: linear-gradient(135deg, #fee2e2, #fca5a5); color: #991b1b; }
        .badge-award-new { background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #065f46; }
        .resto-title { font-family: var(--font-display); font-size: clamp(32px, 5vw, 48px); font-weight: 600; line-height: 1.2; margin-bottom: 16px; letter-spacing: -0.02em; }
        .resto-subtitle { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; font-size: 15px; color: var(--gray-600); margin-bottom: 20px; }
        .resto-subtitle span { display: flex; align-items: center; gap: 6px; }
        .resto-subtitle .separator { width: 4px; height: 4px; background: var(--gray-400); border-radius: 50%; }
        .resto-ranking-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .resto-rank-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 20px; font-size: 13px; color: #166534; text-decoration: none; transition: all 0.2s; }
        .resto-rank-badge:hover { background: #dcfce7; border-color: #86efac; }
        .resto-rank-badge.cuisine { background: #fefce8; border-color: #fde68a; color: #854d0e; }
        .resto-rank-badge.cuisine:hover { background: #fef9c3; border-color: #fcd34d; }
        .resto-rank-badge .rank-num { font-weight: 800; font-size: 15px; }
        .resto-rank-badge .rank-text { font-weight: 500; }
        .rating-large { display: flex; align-items: center; gap: 16px; padding: 20px 24px; background: var(--gray-100); border-radius: var(--radius); }
        .rating-score { display: flex; align-items: baseline; gap: 4px; }
        .rating-score .number { font-family: var(--font-display); font-size: 42px; font-weight: 700; color: var(--primary); line-height: 1; }
        .rating-score .max { font-size: 18px; color: var(--gray-500); }
        .rating-stars { display: flex; gap: 4px; }
        .rating-stars i { font-size: 20px; color: var(--warning); }
        .rating-stars i.empty { color: var(--gray-300); }
        .rating-meta { font-size: 14px; color: var(--gray-600); }
        .rating-meta a { color: var(--accent); font-weight: 500; }
        .rating-meta a:hover { text-decoration: underline; }

        /* SECTIONS */
        .section { margin-bottom: 40px; scroll-margin-top: 70px; }
        .section-title { font-family: var(--font-display); font-size: 24px; font-weight: 600; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        .section-title i { color: var(--accent); }

        /* STICKY SUB-NAV (TripAdvisor style) */
        .section-nav { background: #fff; border-bottom: 1px solid var(--gray-200); position: relative; z-index: 900; }
        .section-nav.sticky { position: fixed; top: 0; left: 0; right: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.08); animation: slideDown 0.25s ease; }
        @keyframes slideDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }
        .section-nav-inner { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; gap: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .section-nav-inner::-webkit-scrollbar { display: none; }
        .section-nav-link { padding: 14px 18px; font-size: 14px; font-weight: 600; color: var(--gray-500); white-space: nowrap; border-bottom: 3px solid transparent; transition: color 0.2s, border-color 0.2s; text-decoration: none; display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .section-nav-link:hover { color: var(--primary); }
        .section-nav-link.active { color: var(--accent); border-bottom-color: var(--accent); }
        .section-nav-link i { font-size: 13px; }
        @media (max-width: 600px) {
            .section-nav-link { padding: 12px 14px; font-size: 13px; }
        }

        /* MENU ACCORDION (expandable categories) */
        .menu-accordion { border: 1px solid var(--gray-200); border-radius: var(--radius-sm); margin-bottom: 10px; overflow: hidden; }
        .menu-accordion-header { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: var(--gray-50); border: none; cursor: pointer; transition: background 0.2s; }
        .menu-accordion-header:hover { background: var(--gray-100); }
        .menu-accordion-title { display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 700; color: var(--primary); }
        .menu-accordion-title i { color: var(--accent); font-size: 14px; }
        .menu-accordion-count { background: var(--accent-bg); color: var(--accent); font-size: 12px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
        .menu-accordion-arrow { color: var(--gray-400); font-size: 13px; transition: transform 0.3s; }
        .menu-accordion-header.open .menu-accordion-arrow { transform: rotate(180deg); }
        .menu-accordion-body { max-height: 0; overflow: hidden; transition: max-height 0.35s ease; padding: 0 18px; }
        .menu-accordion-header.open + .menu-accordion-body { padding: 4px 18px 14px; }
        .menu-accordion-item { display: flex; gap: 14px; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--gray-100); }
        .menu-accordion-item:last-child { border-bottom: none; }
        .menu-accordion-photo { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; flex-shrink: 0; transition: transform 0.2s; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .menu-accordion-photo:hover { transform: scale(1.05); }
        .menu-accordion-info { flex: 1; min-width: 0; }
        .menu-accordion-name { font-weight: 600; font-size: 15px; color: var(--primary); }
        .menu-accordion-desc { font-size: 13px; color: var(--gray-500); margin: 3px 0 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .menu-accordion-price { font-weight: 700; color: var(--accent); font-size: 16px; white-space: nowrap; flex-shrink: 0; background: var(--accent-bg); padding: 4px 12px; border-radius: 8px; }
        .menu-order-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: var(--accent); color: #fff; border-radius: 10px; font-weight: 700; font-size: 15px; text-decoration: none; transition: background 0.2s; }
        .menu-order-btn:hover { background: var(--accent-light); }

        /* Menu photo lightbox */
        .menu-lightbox { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 3000; align-items: center; justify-content: center; cursor: pointer; }
        .menu-lightbox.open { display: flex; }
        .menu-lightbox img { max-width: 90%; max-height: 85vh; border-radius: 12px; object-fit: contain; box-shadow: 0 8px 40px rgba(0,0,0,0.5); }
        .menu-lightbox-close { position: absolute; top: 20px; right: 24px; color: #fff; font-size: 32px; cursor: pointer; background: rgba(0,0,0,0.4); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; }
        .description-text { font-size: 16px; line-height: 1.8; color: var(--gray-700); }

        /* HORAIRES */
        .horaires-status { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 24px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
        .horaires-status.open { background: #dcfce7; color: #166534; }
        .horaires-status.closed { background: #fee2e2; color: #991b1b; }
        .horaires-status .pulse { width: 8px; height: 8px; border-radius: 50%; background: currentColor; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.2); } }
        .horaires-list { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: hidden; }
        .horaire-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; font-size: 14px; border-bottom: 1px solid var(--gray-100); transition: background 0.2s; }
        .horaire-row:last-child { border-bottom: none; }
        .horaire-row:hover { background: var(--gray-50); }
        .horaire-row.today { background: var(--accent-bg); font-weight: 600; }
        .horaire-row .jour { color: var(--gray-700); min-width: 100px; }
        .horaire-row .heures { color: var(--primary); text-align: right; }
        .horaire-row .heures.ferme { color: var(--danger); }
    </style>
    <style>
        /* REVIEWS STATS */
        .reviews-stats { display: grid; grid-template-columns: auto 1fr; gap: 32px; padding: 24px; background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); margin-bottom: 24px; }
        .stats-overview { text-align: center; padding-right: 32px; border-right: 1px solid var(--gray-200); }
        .stats-score { font-family: var(--font-display); font-size: 56px; font-weight: 700; line-height: 1; margin-bottom: 8px; }
        .stats-stars { display: flex; justify-content: center; gap: 4px; margin-bottom: 8px; }
        .stats-stars i { font-size: 18px; color: var(--warning); }
        .stats-count { font-size: 14px; color: var(--gray-500); }
        .stats-bars { display: flex; flex-direction: column; gap: 8px; justify-content: center; }
        .stats-bar-row { display: flex; align-items: center; gap: 12px; font-size: 13px; }
        .stats-bar-row .label { width: 20px; text-align: right; color: var(--gray-600); }
        .stats-bar-track { flex: 1; height: 8px; background: var(--gray-200); border-radius: 4px; overflow: hidden; }
        .stats-bar-fill { height: 100%; background: var(--accent); border-radius: 4px; transition: width 0.5s ease; }
        .stats-bar-row .count { width: 30px; font-size: 12px; color: var(--gray-500); }
        .stats-categories { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--gray-200); grid-column: span 2; }
        .stat-category { text-align: center; }
        .stat-category .value { font-size: 20px; font-weight: 700; color: var(--primary); }
        .stat-category .label { font-size: 12px; color: var(--gray-500); margin-top: 4px; }
        @media (max-width: 640px) {
            .reviews-stats { grid-template-columns: 1fr; gap: 20px; }
            .stats-overview { padding-right: 0; padding-bottom: 20px; border-right: none; border-bottom: 1px solid var(--gray-200); }
            .stats-categories { grid-template-columns: repeat(2, 1fr); grid-column: span 1; }
        }

        /* REVIEW CARDS */
        .review-card { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 24px; margin-bottom: 16px; transition: var(--transition); }
        .review-card:hover { box-shadow: var(--shadow); }
        .review-header { display: flex; gap: 16px; margin-bottom: 16px; }
        .review-avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), var(--accent-light)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 18px; flex-shrink: 0; overflow: hidden; }
        .review-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .review-author { flex: 1; min-width: 0; }
        .review-author-name { font-weight: 600; font-size: 15px; margin-bottom: 4px; }
        .review-author-meta { font-size: 13px; color: var(--gray-500); display: flex; flex-wrap: wrap; gap: 8px; }
        .review-rating { display: flex; align-items: center; gap: 8px; }
        .review-rating .stars { display: flex; gap: 2px; }
        .review-rating .stars i { font-size: 14px; color: var(--warning); }
        .review-rating .stars i.empty { color: var(--gray-300); }
        .review-title { font-weight: 600; font-size: 16px; margin-bottom: 8px; }
        .review-content { font-size: 15px; line-height: 1.7; color: var(--gray-700); }
        .review-visit { display: flex; gap: 12px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-100); font-size: 13px; color: var(--gray-500); }
        .review-visit span { display: flex; align-items: center; gap: 6px; }
        .review-helpful { display: flex; align-items: center; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-100); flex-wrap: wrap; }
        .review-helpful .reactions-label { font-size: 13px; color: var(--gray-500); margin-right: 4px; }
        .reaction-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; border: 1px solid var(--gray-200); border-radius: 20px; font-size: 13px; color: var(--gray-600); transition: all .2s; cursor: pointer; background: transparent; }
        .reaction-btn:hover { border-color: var(--accent); color: var(--accent); }
        .reaction-btn.active { border-color: var(--accent); color: var(--accent); background: var(--accent-bg); }
        .reaction-btn[data-reaction="funny"].active { border-color: #f59e0b; color: #d97706; background: #fef3c7; }
        .reaction-btn[data-reaction="love"].active { border-color: #ef4444; color: #dc2626; background: #fef2f2; }
        .helpful-btn { display: flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid var(--gray-200); border-radius: 20px; font-size: 13px; color: var(--gray-600); transition: var(--transition); }
        .helpful-btn:hover { border-color: var(--accent); color: var(--accent); }
        .btn-load-more { width: 100%; padding: 16px; background: var(--gray-100); border-radius: var(--radius); font-weight: 600; font-size: 14px; color: var(--primary); transition: var(--transition); }
        .btn-load-more:hover { background: var(--gray-200); }
        .write-review-prompt { background: linear-gradient(135deg, var(--accent-bg), #f0fdf4); border: 2px dashed var(--accent); border-radius: var(--radius); padding: 32px; text-align: center; margin-top: 24px; }
        .write-review-prompt h4 { font-family: var(--font-display); font-size: 20px; margin-bottom: 8px; }
        .write-review-prompt p { color: var(--gray-600); margin-bottom: 20px; }

        /* SIDEBAR */
        .sidebar { position: relative; }
        .sidebar-sticky {
            position: sticky; top: 80px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--gray-300) transparent;
        }
        .sidebar-sticky::-webkit-scrollbar { width: 4px; }
        .sidebar-sticky::-webkit-scrollbar-track { background: transparent; }
        .sidebar-sticky::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: 4px; }
        .sidebar-sticky::-webkit-scrollbar-thumb:hover { background: var(--gray-400); }
        @media (max-width: 1024px) {
            .sidebar-sticky { position: static; max-height: none; overflow-y: visible; }
        }
        .card { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: hidden; margin-bottom: 20px; }
        .card-body { padding: 20px; }
        .action-buttons { display: flex; flex-direction: column; gap: 12px; padding: 20px; }
        .btn-action { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 16px 24px; border-radius: var(--radius-sm); font-size: 15px; font-weight: 600; transition: var(--transition); }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { background: var(--accent-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0, 135, 90, 0.3); }
        .btn-secondary { background: var(--gray-100); color: var(--primary); }
        .btn-secondary:hover { background: var(--gray-200); }
        .btn-outline { border: 2px solid var(--gray-200); color: var(--primary); }
        .btn-outline:hover { border-color: var(--primary); background: var(--gray-50); }
        .contact-list { display: flex; flex-direction: column; }
        .contact-item { display: flex; align-items: flex-start; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--gray-100); }
        .contact-item:last-child { border-bottom: none; }
        .contact-icon { width: 40px; height: 40px; background: var(--gray-100); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--accent); flex-shrink: 0; }
        .contact-content { flex: 1; min-width: 0; }
        .contact-label { font-size: 12px; color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .contact-value { font-size: 14px; font-weight: 500; color: var(--primary); word-break: break-word; }
        .contact-value a { color: var(--accent); }
        .contact-value a:hover { text-decoration: underline; }
        .social-links { display: flex; gap: 12px; padding: 20px; border-top: 1px solid var(--gray-100); }
        .social-link { width: 44px; height: 44px; border-radius: 50%; background: var(--gray-100); display: flex; align-items: center; justify-content: center; font-size: 18px; color: var(--gray-600); transition: var(--transition); }
        .social-link:hover { background: var(--primary); color: white; transform: translateY(-3px); }
        .social-link.whatsapp:hover { background: #25D366; }
        .social-link.facebook:hover { background: #1877F2; }
        .social-link.instagram:hover { background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); }
        .mini-map { height: 200px; background: var(--gray-200); position: relative; }
        #sidebarMap { width: 100%; height: 100%; }
        .map-overlay-link { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.4); color: white; font-weight: 600; opacity: 0; transition: var(--transition); }
        .mini-map:hover .map-overlay-link { opacity: 1; }
        .gmaps-embed { margin-top: 0; border-radius: 0; overflow: hidden; background: var(--gray-200); }
        .gmaps-embed iframe { width: 100%; height: 180px; border: none; display: block; }
        .map-action-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 12px 16px;
        }
        .map-action-btn {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 8px; border-radius: 8px;
            font-size: 12px; font-weight: 600; text-align: center;
            border: 1px solid var(--gray-200); background: white; color: var(--gray-600);
            transition: all 0.2s; cursor: pointer; text-decoration: none;
        }
        .map-action-btn:hover { border-color: var(--accent); color: var(--accent); background: #f0fdf4; }
        .map-action-btn i { font-size: 13px; }
        .map-action-btn.sv { color: #f59e0b; }
        .map-action-btn.sv:hover { border-color: #f59e0b; background: #fffbeb; }

        /* BREADCRUMBS */
        .breadcrumbs { max-width: 1200px; margin: 16px auto 0; padding: 0 20px; font-size: 13px; color: var(--gray-500); }
        .breadcrumbs a { color: var(--accent); }
        .breadcrumbs a:hover { text-decoration: underline; }
        .breadcrumbs .sep { margin: 0 6px; }

        /* REVIEW SUMMARY */
        .review-summary { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1px solid #bbf7d0; border-radius: var(--radius); padding: 20px 24px; margin-bottom: 24px; }
        .review-summary-title { font-weight: 600; font-size: 15px; margin-bottom: 12px; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .review-summary-title i { color: var(--accent); }
        .summary-keywords { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .summary-keyword { padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: 500; }
        .summary-keyword.positive { background: #dcfce7; color: #166534; }
        .summary-keyword.negative { background: #fee2e2; color: #991b1b; }
        .summary-scores { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .summary-score-item { text-align: center; }
        .summary-score-item .score-val { font-size: 18px; font-weight: 700; color: var(--primary); }
        .summary-score-item .score-lbl { font-size: 11px; color: var(--gray-500); }
        @media (max-width: 480px) { .summary-scores { grid-template-columns: repeat(2, 1fr); } }

        /* OFFERS BADGE */
        .offer-banner { background: linear-gradient(135deg, #fef3c7, #fde68a); border: 1px solid #fbbf24; border-radius: var(--radius); padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 16px; }
        .offer-discount { background: #dc2626; color: white; font-size: 20px; font-weight: 800; padding: 10px 14px; border-radius: 12px; white-space: nowrap; }
        .offer-details h4 { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .offer-details p { font-size: 13px; color: var(--gray-600); }
        .offer-conditions { font-size: 11px; color: var(--gray-500); margin-top: 4px; }

        /* SKELETON LOADING */
        .skeleton { background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-100) 50%, var(--gray-200) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 8px; }
        @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .skeleton-text { height: 14px; margin-bottom: 8px; }
        .skeleton-title { height: 24px; width: 60%; margin-bottom: 12px; }
        .skeleton-card { height: 180px; margin-bottom: 16px; }

        /* VERIFIED OWNER INFO */
        .owner-verified-banner { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: #dbeafe; border-radius: var(--radius-sm); margin-bottom: 16px; font-size: 13px; color: #1e40af; }
        .owner-verified-banner i { font-size: 16px; }

        /* SIMILAR */
        .similar-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        @media (max-width: 900px) { .similar-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .similar-grid { grid-template-columns: 1fr; } }
        .similar-card { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: hidden; transition: var(--transition); }
        .similar-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .similar-photo { height: 160px; background: var(--gray-200); position: relative; overflow: hidden; }
        .similar-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .similar-card:hover .similar-photo img { transform: scale(1.1); }
        .similar-info { padding: 16px; }
        .similar-name { font-weight: 600; font-size: 15px; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .similar-meta { font-size: 13px; color: var(--gray-500); display: flex; align-items: center; gap: 8px; }
        .similar-meta .rating { display: flex; align-items: center; gap: 4px; }
        .similar-meta .rating i { color: var(--warning); font-size: 12px; }

        /* MODAL GALLERY */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 9999; display: none; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-close { position: absolute; top: 20px; right: 20px; width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.1); color: white; font-size: 24px; display: flex; align-items: center; justify-content: center; transition: var(--transition); z-index: 10; }
        .modal-close:hover { background: rgba(255,255,255,0.2); }
        .gallery-modal-content { width: 100%; height: 100%; display: flex; flex-direction: column; }
        .gallery-main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 60px 80px; position: relative; }
        .gallery-main img { max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px; }
        .gallery-nav-btn { position: absolute; top: 50%; transform: translateY(-50%); width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.1); color: white; font-size: 24px; display: flex; align-items: center; justify-content: center; transition: var(--transition); }
        .gallery-nav-btn:hover { background: rgba(255,255,255,0.2); }
        .gallery-nav-btn.prev { left: 20px; }
        .gallery-nav-btn.next { right: 20px; }
        .gallery-thumbs { display: flex; gap: 8px; padding: 20px; overflow-x: auto; justify-content: center; background: rgba(0,0,0,0.5); }
        .gallery-thumb { width: 80px; height: 60px; border-radius: 6px; overflow: hidden; cursor: pointer; opacity: 0.5; transition: var(--transition); flex-shrink: 0; }
        .gallery-thumb:hover, .gallery-thumb.active { opacity: 1; }
        .gallery-thumb.active { outline: 3px solid var(--accent); outline-offset: 2px; }
        .gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }

        /* GALLERY FILTER TABS */
        .gallery-filters { display: flex; gap: 8px; padding: 12px 20px 0; justify-content: center; flex-wrap: wrap; background: rgba(0,0,0,0.5); }
        .gallery-filter-btn { padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.25); font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; background: transparent; color: rgba(255,255,255,0.7); }
        .gallery-filter-btn:hover { background: rgba(255,255,255,0.1); color: white; }
        .gallery-filter-btn.active { background: var(--accent); color: white; border-color: var(--accent); }
        .gallery-filter-count { font-size: 11px; opacity: 0.7; margin-left: 4px; }

        /* MOBILE CTA */
        .mobile-cta { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: white; padding: 12px 16px; padding-bottom: calc(12px + env(safe-area-inset-bottom)); border-top: 1px solid var(--gray-200); z-index: 100; box-shadow: 0 -4px 24px rgba(0,0,0,0.1); }
        .mobile-cta-content { display: flex; gap: 12px; }
        .mobile-cta .btn-action { flex: 1; padding: 14px; }
        @media (max-width: 1024px) { .mobile-cta { display: block; } body { padding-bottom: 80px; } }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.6s ease forwards; }

        /* ========================================
   GALERIE PHOTOS AVIS
   ======================================== */
.review-photos {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 8px;
    margin: 16px 0;
}

.review-photo-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s;
}

.review-photo-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.review-photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.review-photo-count {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

/* LIGHTBOX */
.lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.95);
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.lightbox.active {
    display: flex;
}

.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.lightbox-content img {
    max-width: 100%;
    max-height: 90vh;
    object-fit: contain;
    border-radius: 8px;
}

.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 32px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    z-index: 10001;
}

.lightbox-close:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 32px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    z-index: 10001;
}

.lightbox-nav:hover {
    background: rgba(255,255,255,0.3);
}

.lightbox-nav.prev {
    left: 20px;
}

.lightbox-nav.next {
    right: 20px;
}

.lightbox-counter {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}
    </style>
    <!-- Lightbox CSS -->
<link rel="stylesheet" href="/assets/css/lightbox.css">
<!-- Schema.org JSON-LD -->
<?php
$schemaData = [
    '@context' => 'https://schema.org',
    '@type' => 'Restaurant',
    'name' => $restaurant['nom'],
    'description' => $restaurant['description'] ?? '',
    'image' => $restaurant['main_photo'] ?? '',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $restaurant['adresse'] ?? '',
        'addressLocality' => $restaurant['ville'] ?? '',
        'addressCountry' => 'DZ'
    ],
    'servesCuisine' => $restaurant['type_cuisine'] ?? 'Algérien',
    'url' => 'https://lebonresto.dz/restaurant/' . (int)$restaurant['id']
];
if (!empty($restaurant['telephone'])) $schemaData['telephone'] = $restaurant['telephone'];
if (!empty($restaurant['gps_latitude']) && !empty($restaurant['gps_longitude'])) {
    $schemaData['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float)$restaurant['gps_latitude'], 'longitude' => (float)$restaurant['gps_longitude']];
}
if (!empty($restaurant['price_range'])) $schemaData['priceRange'] = $restaurant['price_range'];
if (!empty($restaurant['note_moyenne']) && !empty($restaurant['nb_avis'])) {
    $schemaData['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => round((float)$restaurant['note_moyenne'], 1), 'bestRating' => 5, 'worstRating' => 1, 'reviewCount' => (int)$restaurant['nb_avis']];
}
?>
<script type="application/ld+json"><?= json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?></script>
<?php if (!empty($breadcrumb_json)): ?>
<script type="application/ld+json"><?= $breadcrumb_json ?></script>
<?php endif; ?>
</head>
<body>
<!-- HEADER STICKY -->
<header class="page-header" id="pageHeader">
    <div class="header-content">
        <div class="header-left">
            <a href="/search" class="header-back"><i class="fas fa-arrow-left"></i></a>
            <div>
                <div class="header-title"><?= htmlspecialchars($r['nom']) ?></div>
                <div class="header-rating">
                    <i class="fas fa-star"></i>
                    <span><?= number_format(min($r['note_moyenne'], 5), 1) ?></span>
                    <span>(<?= $r['nb_avis'] ?> avis)</span>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a href="#avis" class="header-btn primary">Donner mon avis</a>
        </div>
    </div>
</header>

<!-- HERO GALLERY -->
<section class="hero-gallery">
    <div class="gallery-grid">
        <?php if (!empty($photos)): ?>
            <?php foreach (array_slice($photos, 0, 5) as $i => $photo): ?>
                <div class="gallery-item" onclick="openGallery(<?= $i ?>)">
                    <img loading="lazy" src="/<?= htmlspecialchars($photo['path']) ?>" alt="<?= htmlspecialchars($photo['legende'] ?? $r['nom']) ?>" loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                </div>
            <?php endforeach; ?>
            <?php for ($j = count($photos); $j < 5; $j++): ?>
                <div class="gallery-item"><div class="gallery-placeholder"><i class="fas fa-utensils"></i></div></div>
            <?php endfor; ?>
        <?php else: ?>
            <div class="gallery-item"><div class="gallery-placeholder"><i class="fas fa-utensils"></i></div></div>
        <?php endif; ?>
    </div>
    
    <div class="gallery-nav">
        <a href="/search" class="nav-btn"><i class="fas fa-arrow-left"></i></a>
        <div class="nav-actions">
             <button class="nav-btn" data-wishlist="<?= $r['id'] ?>" id="wishlistBtn"><i class="far fa-heart"></i></button>
            <button class="nav-btn" onclick="openShareModal('restaurant', <?= $r['id'] ?>, '<?= addslashes(htmlspecialchars($r['nom'], ENT_QUOTES)) ?>')"><i class="fas fa-share-alt"></i></button>
        </div>
    </div>
    <!-- Compare button — positioned bottom-right to avoid overlap with back arrow -->
    <button class="nav-btn cw-card-btn" data-id="<?= $r['id'] ?>"
            onclick="cwToggleResto(<?= $r['id'] ?>, '<?= addslashes(htmlspecialchars($r['nom'], ENT_QUOTES)) ?>', '<?= addslashes($mainPhoto ?? '') ?>', '<?= addslashes(htmlspecialchars($r['ville'] ?? '', ENT_QUOTES)) ?>');"
            title="Ajouter au comparateur"
            style="position:absolute;bottom:60px;right:16px;opacity:1;z-index:5;">
        <i class="fas fa-balance-scale"></i>
    </button>
    
    <?php if (count($photos) > 0): ?>
        <button class="btn-photos" onclick="openGallery(0)">
            <i class="fas fa-images"></i>
            <?= count($photos) > 5 ? 'Voir les ' . count($photos) . ' photos' : 'Voir la galerie' ?>
        </button>
    <?php endif; ?>
</section>

<!-- BREADCRUMBS -->
<nav class="breadcrumbs" aria-label="Fil d'Ariane">
    <a href="/">Accueil</a><span class="sep">&rsaquo;</span>
    <a href="/search">Restaurants</a><span class="sep">&rsaquo;</span>
    <?php if (!empty($r['ville'])): ?>
        <a href="/search?ville=<?= urlencode($r['ville']) ?>"><?= htmlspecialchars($r['ville']) ?></a><span class="sep">&rsaquo;</span>
    <?php endif; ?>
    <span><?= htmlspecialchars($r['nom']) ?></span>
</nav>

<?php
// Pre-compute section visibility for the nav
$hasRealHoraires = false;
if (!empty($horaires)) {
    foreach ($horaires as $h) {
        if ($h['ferme'] || !empty($h['periodes'])) { $hasRealHoraires = true; break; }
    }
}
?>

<!-- SECTION NAV (TripAdvisor style sticky sub-menu) -->
<nav class="section-nav" id="sectionNav">
    <div class="section-nav-inner">
        <?php if (!empty($r['description']) || !empty($r['descriptif'])): ?>
            <a href="#apropos" class="section-nav-link" data-section="apropos"><i class="fas fa-info-circle"></i> À propos</a>
        <?php endif; ?>
        <?php if ($hasRealHoraires): ?>
            <a href="#horaires" class="section-nav-link" data-section="horaires"><i class="fas fa-clock"></i> Horaires</a>
        <?php endif; ?>
        <a href="#avis" class="section-nav-link" data-section="avis"><i class="fas fa-star"></i> Avis (<?= $r['nb_avis'] ?? 0 ?>)</a>
        <a href="#conseils" class="section-nav-link" data-section="conseils"><i class="fas fa-lightbulb"></i> Conseils</a>
        <a href="#qa" class="section-nav-link" data-section="qa"><i class="fas fa-question-circle"></i> Q&R</a>
        <?php if (!empty($r['owner_id'])): ?>
            <a href="/restaurant/<?= $r['id'] ?>/posts" class="section-nav-link"><i class="fas fa-bullhorn"></i> Actus</a>
        <?php endif; ?>
        <?php
        $menuItems2 = $r['menu_items'] ?? [];
        if (!empty($menuItems2) && !empty($r['menu_enabled'])):
        ?>
            <a href="#menu-section" class="section-nav-link" data-section="menu-section"><i class="fas fa-book-open"></i> Menu</a>
        <?php endif; ?>
        <?php if (!empty($r['owner_id']) && !empty($r['orders_enabled'])): ?>
            <a href="/commander/<?= htmlspecialchars($r['slug'] ?? $r['id']) ?>" class="section-nav-link" style="color:var(--accent)"><i class="fas fa-shopping-bag"></i> Commander</a>
        <?php endif; ?>
    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="main-container">
    <div class="content-grid">

        <!-- COLONNE PRINCIPALE -->
        <div class="main-content">

            <?php // ACTIVE OFFERS
            $offers = $r['offers'] ?? [];
            if (!empty($offers)): foreach (array_slice($offers, 0, 2) as $offer): ?>
                <div class="offer-banner">
                    <div class="offer-discount">
                        <?php if ($offer['discount_percent'] > 0): ?>
                            -<?= (int)$offer['discount_percent'] ?>%
                        <?php else: ?>
                            <i class="fas fa-gift"></i>
                        <?php endif; ?>
                    </div>
                    <div class="offer-details">
                        <h4><?= htmlspecialchars($offer['title']) ?></h4>
                        <?php if (!empty($offer['description'])): ?>
                            <p><?= htmlspecialchars($offer['description']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($offer['conditions'])): ?>
                            <div class="offer-conditions"><?= htmlspecialchars($offer['conditions']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; endif; ?>

            <!-- Header Restaurant -->
            <header class="resto-header animate-in">
                <div class="resto-badges">
                    <?php if ($r['verified_owner']): ?><span class="badge badge-verified"><i class="fas fa-user-check"></i> Propriétaire vérifié</span><?php endif; ?>
                    <?php if ($r['featured']): ?><span class="badge badge-featured"><i class="fas fa-crown"></i> Recommandé</span><?php endif; ?>
                    <?php
                    $awards = $r['awards'] ?? [];
                    foreach ($awards as $award):
                        $awardConfig = match($award['award_type']) {
                            'travelers_choice' => ['icon' => 'fa-trophy', 'label' => "Travelers' Choice " . $award['award_year'], 'class' => 'badge-award-tc'],
                            'top_city' => ['icon' => 'fa-medal', 'label' => 'Top ' . ($award['rank_position'] ?? '') . ' ' . ($award['city'] ?? '') . ' ' . $award['award_year'], 'class' => 'badge-award-top'],
                            'best_cuisine' => ['icon' => 'fa-award', 'label' => 'Meilleur ' . ($award['cuisine_type'] ?? '') . ' ' . $award['award_year'], 'class' => 'badge-award-cuisine'],
                            'trending' => ['icon' => 'fa-fire', 'label' => 'Tendance ' . $award['award_year'], 'class' => 'badge-award-trending'],
                            'newcomer' => ['icon' => 'fa-star', 'label' => 'Nouveau ' . $award['award_year'], 'class' => 'badge-award-new'],
                            default => null,
                        };
                        if ($awardConfig):
                    ?>
                        <span class="badge <?= $awardConfig['class'] ?>"><i class="fas <?= $awardConfig['icon'] ?>"></i> <?= htmlspecialchars($awardConfig['label']) ?></span>
                    <?php endif; endforeach; ?>
                </div>
                
                <h1 class="resto-title"><?= htmlspecialchars($r['nom']) ?></h1>
                
                <div class="resto-subtitle">
                    <?php if ($r['type_cuisine']): ?><span><i class="fas fa-utensils"></i> <?= htmlspecialchars($r['type_cuisine']) ?></span><span class="separator"></span><?php endif; ?>
                    <?php if ($r['price_range']): ?><span><?= htmlspecialchars($r['price_range']) ?></span><span class="separator"></span><?php endif; ?>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['ville'] ?? '') ?></span>
                </div>

                <?php if (!empty($ranking)): ?>
                <div class="resto-ranking-badges">
                    <?php if (!empty($ranking['global'])): ?>
                    <a href="/classement-restaurants?ville=<?= urlencode($ranking['global']['region']) ?>" class="resto-rank-badge">
                        <span class="rank-num">N°<?= $ranking['global']['rank'] ?></span>
                        <span class="rank-text">sur <?= $ranking['global']['total'] ?> restaurants a <?= htmlspecialchars($ranking['global']['region']) ?></span>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($ranking['cuisine'])): ?>
                    <a href="/search?ville=<?= urlencode($ranking['cuisine']['region']) ?>&type=<?= urlencode($ranking['cuisine']['cuisine']) ?>&sort=popularity" class="resto-rank-badge cuisine">
                        <span class="rank-num">N°<?= $ranking['cuisine']['rank'] ?></span>
                        <span class="rank-text">sur <?= $ranking['cuisine']['total'] ?> <?= htmlspecialchars($ranking['cuisine']['cuisine']) ?> a <?= htmlspecialchars($ranking['cuisine']['region']) ?></span>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($r['note_moyenne'] > 0): ?>
                    <div class="rating-large">
                        <div class="rating-score">
                            <span class="number"><?= number_format(min($r['note_moyenne'], 5), 1) ?></span>
                            <span class="max">/5</span>
                        </div>
                        <div>
                            <div class="rating-stars">
                                <?php for ($i = 0; $i < 5; $i++): echo $i < floor($r['note_moyenne']) ? '<i class="fas fa-star"></i>' : '<i class="fas fa-star empty"></i>'; endfor; ?>
                            </div>
                            <div class="rating-meta">
                                <a href="#avis"><?= $r['nb_avis'] ?> avis</a> · <?= number_format($r['vues_total']) ?> vues
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </header>
            
            <!-- Description -->
            <?php if (!empty($r['description']) || !empty($r['descriptif'])): ?>
                <section class="section animate-in" id="apropos">
                    <h2 class="section-title"><i class="fas fa-info-circle"></i> À propos</h2>
                    <div class="description-text"><?= nl2br(htmlspecialchars($r['description'] ?? $r['descriptif'])) ?></div>
                </section>
            <?php endif; ?>
            
            <!-- Horaires -->
            <?php if ($hasRealHoraires): ?>
                <section class="section animate-in" id="horaires">
                    <h2 class="section-title"><i class="fas fa-clock"></i> Horaires d'ouverture</h2>

                    <div class="horaires-status <?= $isOpen['is_open'] ? 'open' : 'closed' ?>">
                        <span class="pulse"></span>
                        <?= htmlspecialchars($isOpen['message']) ?>
                    </div>

                    <div class="horaires-list">
                        <?php foreach ($horaires as $index => $h): ?>
                            <div class="horaire-row <?= $index === $jourActuel ? 'today' : '' ?>">
                                <span class="jour"><?= $h['jour'] ?></span>
                                <span class="heures <?= $h['ferme'] ? 'ferme' : '' ?>">
                                    <?php if ($h['ferme']): ?>Fermé
                                    <?php elseif (!empty($h['periodes'])): ?>
                                        <?= implode(' · ', array_map(fn($p) => $p['debut'] . ' - ' . $p['fin'], $h['periodes'])) ?>
                                    <?php else: ?>—<?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Peak Hours Heatmap (loaded via AJAX) -->
            <section class="section animate-in" id="peak-hours-section" style="display:none">
                <h2 class="section-title"><i class="fas fa-chart-bar"></i> Heures de pointe</h2>
                <p style="font-size:13px;color:#6b7280;margin-bottom:12px">Affluence estimée sur les 30 derniers jours</p>
                <div id="peakHoursGrid" style="overflow-x:auto"></div>
                <div id="peakHoursLegend" style="display:flex;gap:8px;margin-top:8px;font-size:11px;color:#6b7280;flex-wrap:wrap">
                    <span><span style="display:inline-block;width:12px;height:12px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:2px;vertical-align:middle"></span> Calme</span>
                    <span><span style="display:inline-block;width:12px;height:12px;background:#bbf7d0;border-radius:2px;vertical-align:middle"></span> Modéré</span>
                    <span><span style="display:inline-block;width:12px;height:12px;background:#fbbf24;border-radius:2px;vertical-align:middle"></span> Fréquenté</span>
                    <span><span style="display:inline-block;width:12px;height:12px;background:#ef4444;border-radius:2px;vertical-align:middle"></span> Très fréquenté</span>
                </div>
            </section>
            <script>
            (function(){
                var rid = <?= (int)$r['id'] ?>;
                fetch('/api/restaurants/' + rid + '/peak-hours')
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (!data.success || !data.grid || data.total_views < 5) return;
                    document.getElementById('peak-hours-section').style.display = '';
                    var g = data.grid;
                    var html = '<table style="width:100%;border-collapse:collapse;font-size:11px;text-align:center">';
                    html += '<tr><th style="padding:4px;min-width:60px"></th>';
                    // Only show hours 8-23 (restaurant hours)
                    for (var h = 8; h <= 23; h++) html += '<th style="padding:3px;color:#9ca3af;font-weight:500">' + h + 'h</th>';
                    html += '</tr>';
                    for (var d = 0; d < 7; d++) {
                        html += '<tr><td style="padding:4px 6px;font-weight:600;color:#374151;text-align:left;white-space:nowrap">' + g[d].day.substr(0,3) + '</td>';
                        for (var h = 8; h <= 23; h++) {
                            var cell = g[d].hours[h];
                            var bg = '#f3f4f6';
                            if (cell.intensity > 75) bg = '#ef4444';
                            else if (cell.intensity > 50) bg = '#fbbf24';
                            else if (cell.intensity > 25) bg = '#bbf7d0';
                            else if (cell.intensity > 0) bg = '#dcfce7';
                            html += '<td style="padding:2px"><div title="' + g[d].day + ' ' + h + 'h: ' + cell.label + ' (' + cell.count + ')" style="width:100%;height:22px;background:' + bg + ';border-radius:3px"></div></td>';
                        }
                        html += '</tr>';
                    }
                    html += '</table>';
                    document.getElementById('peakHoursGrid').innerHTML = html;
                }).catch(function(){});
            })();
            </script>

            <!-- Avis Section -->
          <?php
/**
 * PAGE DÉTAILS RESTAURANT - LEBONRESTO v2.0
 * Features: Load More, Tri, Filtre, Vote, Photos avis, Réponse proprio
 */

$r = $restaurant;
$photos = $r['photos'] ?? [];
$horaires = $r['horaires'] ?? [];
$reviews = $r['reviews'] ?? [];
$stats = $r['rating_stats'] ?? [];
$similar = $r['similar'] ?? [];
$isOpen = $r['is_open_now'] ?? ['is_open' => false, 'message' => 'Horaires non disponibles'];

// Limiter à 3 avis initiaux (les autres seront chargés via AJAX)
$initialReviews = array_slice($reviews, 0, 3);
$totalReviews = count($reviews);

$mainPhoto = null;
foreach ($photos as $p) {
    if ($p['type'] === 'main') { $mainPhoto = $p['path']; break; }
}
if (!$mainPhoto && !empty($photos)) { $mainPhoto = $photos[0]['path']; }

$joursComplets = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$jourActuel = (int)(new DateTime())->format('N') - 1;

// Vérifier si le user connecté est le propriétaire
$isOwner = false;
if (isset($_SESSION['user']['id']) && isset($r['owner_id'])) {
    $isOwner = ((int)$_SESSION['user']['id'] === (int)$r['owner_id']);
}

// Helper pour calculer le temps relatif
function getRelativeTime($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    
    if ($diff->y > 0) return $diff->y === 1 ? 'Il y a 1 an' : "Il y a {$diff->y} ans";
    if ($diff->m > 0) return $diff->m === 1 ? 'Il y a 1 mois' : "Il y a {$diff->m} mois";
    if ($diff->d > 0) return $diff->d === 1 ? 'Il y a 1 jour' : "Il y a {$diff->d} jours";
    if ($diff->h > 0) return $diff->h === 1 ? 'Il y a 1 heure' : "Il y a {$diff->h} heures";
    if ($diff->i > 0) return $diff->i === 1 ? 'Il y a 1 minute' : "Il y a {$diff->i} minutes";
    return 'À l\'instant';
}
?>
 <section class="section" id="avis" data-restaurant-id="<?= $r['id'] ?>" data-total-reviews="<?= $totalReviews ?>">
    
    <!-- Bandeau propriétaire (si connecté en tant que owner) -->
    <?php if ($isOwner): ?>
        <div class="owner-banner">
            <div class="owner-banner-content">
                <div class="owner-banner-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="owner-banner-text">
                    <h4>Vous gérez cet établissement</h4>
                    <p>Répondez aux avis pour engager avec vos clients</p>
                </div>
            </div>
            <div class="owner-banner-actions">
                <button class="owner-btn" onclick="window.location.href='/dashboard/reviews'">
                    <i class="fas fa-chart-line"></i> Tableau de bord
                </button>
            </div>
        </div>
        
        <!-- Stats propriétaire -->
        <?php
        $totalOwnerReviews = count($reviews);
        $respondedReviews = 0;
        $pendingReviews = 0;
        foreach ($reviews as $rev) {
            if (!empty($rev['owner_response'])) $respondedReviews++;
            else $pendingReviews++;
        }
        $responseRate = $totalOwnerReviews > 0 ? round(($respondedReviews / $totalOwnerReviews) * 100) : 0;
        ?>
        <div class="owner-stats">
            <div class="owner-stat-item">
                <div class="owner-stat-value"><?= $responseRate ?>%</div>
                <div class="owner-stat-label">Taux de réponse</div>
            </div>
            <div class="owner-stat-item">
                <div class="owner-stat-value"><?= $respondedReviews ?>/<?= $totalOwnerReviews ?></div>
                <div class="owner-stat-label">Avis répondus</div>
            </div>
            <div class="owner-stat-item">
                <div class="owner-stat-value"><?= $pendingReviews ?></div>
                <div class="owner-stat-label">Sans réponse</div>
            </div>
        </div>
    <?php endif; ?>


    <?php if (!empty($r['friends_reviews'])): ?>
    <div class="friends-reviews-section">
        <h3 class="friends-reviews-title"><i class="fas fa-user-friends"></i> Vos abonnements connaissent ce restaurant</h3>
        <div class="friends-reviews-list">
            <?php foreach ($r['friends_reviews'] as $fr): ?>
            <div class="friend-review-card">
                <div class="friend-review-header">
                    <div class="friend-avatar">
                        <?php if (!empty($fr['user_photo'])): ?>
                            <img src="/uploads/avatars/<?= htmlspecialchars($fr['user_photo']) ?>" alt="">
                        <?php else: ?>
                            <?= strtoupper(mb_substr($fr['prenom'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="friend-info">
                        <a href="/user/<?= (int)$fr['user_id'] ?>" class="friend-name"><?= htmlspecialchars($fr['prenom'] . ' ' . mb_substr($fr['user_nom'], 0, 1) . '.') ?></a>
                        <div class="friend-rating">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="fas fa-star" style="color:<?= $s <= $fr['note_globale'] ? '#f59e0b' : '#e5e7eb' ?>;font-size:12px"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($fr['message'])): ?>
                    <p class="friend-review-text"><?= htmlspecialchars(mb_substr($fr['message'], 0, 120)) ?><?= mb_strlen($fr['message']) > 120 ? '...' : '' ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <h2 class="section-title"><i class="fas fa-star"></i> Avis des clients</h2>

    <?php // REVIEW SUMMARY (AI keyword extraction)
    $summary = $r['review_summary'] ?? null;
    if ($summary && !empty($summary['positive_keywords'])): ?>
        <div class="review-summary">
            <div class="review-summary-title"><i class="fas fa-sparkles"></i> Ce que disent les clients</div>
            <div class="summary-keywords">
                <?php foreach (array_slice($summary['positive_keywords'], 0, 5) as $kw): ?>
                    <span class="summary-keyword positive"><i class="fas fa-thumbs-up"></i> <?= htmlspecialchars(ucfirst($kw)) ?></span>
                <?php endforeach; ?>
                <?php foreach (array_slice($summary['negative_keywords'] ?? [], 0, 3) as $kw): ?>
                    <span class="summary-keyword negative"><i class="fas fa-thumbs-down"></i> <?= htmlspecialchars(ucfirst($kw)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($summary['cuisine_score']) || !empty($summary['service_score'])): ?>
            <div class="summary-scores">
                <?php if ($summary['cuisine_score']): ?>
                    <div class="summary-score-item"><div class="score-val"><?= number_format($summary['cuisine_score'], 1) ?></div><div class="score-lbl">Cuisine</div></div>
                <?php endif; ?>
                <?php if ($summary['service_score']): ?>
                    <div class="summary-score-item"><div class="score-val"><?= number_format($summary['service_score'], 1) ?></div><div class="score-lbl">Service</div></div>
                <?php endif; ?>
                <?php if ($summary['ambiance_score']): ?>
                    <div class="summary-score-item"><div class="score-val"><?= number_format($summary['ambiance_score'], 1) ?></div><div class="score-lbl">Ambiance</div></div>
                <?php endif; ?>
                <?php if ($summary['price_score']): ?>
                    <div class="summary-score-item"><div class="score-val"><?= number_format($summary['price_score'], 1) ?></div><div class="score-lbl">Prix</div></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($stats) && $stats['total'] > 0): ?>
        <div class="reviews-stats">
            <div class="stats-overview">
                <div class="stats-score"><?= number_format($stats['moyenne'], 1) ?></div>
                <div class="stats-stars">
                    <?php for ($i = 0; $i < 5; $i++): echo $i < floor($stats['moyenne']) ? '<i class="fas fa-star"></i>' : '<i class="fas fa-star" style="color: var(--gray-300)"></i>'; endfor; ?>
                </div>
                <div class="stats-count"><?= $stats['total'] ?> avis</div>
            </div>
            
            <div class="stats-bars">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="stats-bar-row" data-rating-filter="<?= $i ?>">
                        <span class="label"><?= $i ?></span>
                        <div class="stats-bar-track">
                            <div class="stats-bar-fill" style="width: <?= $stats['distribution'][$i]['percent'] ?>%"></div>
                        </div>
                        <span class="count"><?= $stats['distribution'][$i]['count'] ?></span>
                    </div>
                <?php endfor; ?>
            </div>

            <?php if (!empty($stats['categories'])): ?>
                <div class="stats-categories">
                    <?php if ($stats['categories']['nourriture'] > 0): ?><div class="stat-category"><div class="value"><?= $stats['categories']['nourriture'] ?></div><div class="label">Nourriture</div></div><?php endif; ?>
                    <?php if ($stats['categories']['service'] > 0): ?><div class="stat-category"><div class="value"><?= $stats['categories']['service'] ?></div><div class="label">Service</div></div><?php endif; ?>
                    <?php if ($stats['categories']['ambiance'] > 0): ?><div class="stat-category"><div class="value"><?= $stats['categories']['ambiance'] ?></div><div class="label">Ambiance</div></div><?php endif; ?>
                    <?php if ($stats['categories']['prix'] > 0): ?><div class="stat-category"><div class="value"><?= $stats['categories']['prix'] ?></div><div class="label">Qualité/prix</div></div><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Context Tags -->
        <?php if (!empty($r['context_tags'])): ?>
        <div class="context-tags-section" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px">
            <?php
            $tagLabels = ['romantique'=>'Romantique','familial'=>'Familial','business'=>'Business lunch','terrasse'=>'Belle terrasse','vue'=>'Belle vue','calme'=>'Calme','anime'=>'Anime','bon_rapport'=>'Bon rapport Q/P','grandes_portions'=>'Grandes portions','service_rapide'=>'Service rapide','livraison'=>'Bonne livraison'];
            $tagIcons = ['romantique'=>'fa-heart','familial'=>'fa-child','business'=>'fa-briefcase','terrasse'=>'fa-umbrella-beach','vue'=>'fa-mountain-sun','calme'=>'fa-volume-low','anime'=>'fa-music','bon_rapport'=>'fa-coins','grandes_portions'=>'fa-utensils','service_rapide'=>'fa-bolt','livraison'=>'fa-motorcycle'];
            foreach ($r['context_tags'] as $ct): ?>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:20px;font-size:12px;font-weight:600;color:#166534">
                    <i class="fas <?= $tagIcons[$ct['tag']] ?? 'fa-tag' ?>" style="font-size:11px"></i>
                    <?= htmlspecialchars($tagLabels[$ct['tag']] ?? $ct['tag']) ?>
                    <span style="background:#dcfce7;padding:1px 6px;border-radius:10px;font-size:10px"><?= $ct['vote_count'] ?></span>
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Contrôles Tri + Filtre + Recherche -->
        <div class="reviews-controls">
            <div class="sort-control">
                <label for="reviewSort"><i class="fas fa-sort"></i> Trier par :</label>
                <select id="reviewSort" class="sort-select">
                    <option value="recent">Plus recents</option>
                    <option value="helpful">Plus utiles</option>
                    <option value="rating">Meilleure note</option>
                </select>
            </div>
            <div style="display:flex;align-items:center;gap:8px">
                <div style="position:relative">
                    <input type="text" id="reviewSearchInput" placeholder="Rechercher dans les avis..." style="padding:8px 12px 8px 32px;border:2px solid var(--gray-200);border-radius:8px;font-size:13px;width:220px;transition:border-color 0.2s" onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--gray-200)'">
                    <i class="fas fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--gray-400);font-size:12px"></i>
                </div>
            </div>
            <div id="activeFilterBadge" style="display: none;"></div>
        </div>
        
        <!-- Liste des avis -->
        <div class="reviews-list" id="reviewsList">
            <?php foreach ($initialReviews as $review): ?>
<?php
$reviewCardClass = 'review-card animated';
$upts = (int)($review['user_points'] ?? 0);
if ($upts >= 5000) $reviewCardClass .= ' review-legendaire';
elseif ($upts >= 2500) $reviewCardClass .= ' review-ambassadeur';
?>
<article class="<?= $reviewCardClass ?>" data-review-id="<?= $review['id'] ?>" data-rating="<?= (int)$review['note_globale'] ?>">
                        <div class="review-header">
                        <div class="review-avatar">
                            <?php if (!empty($review['user_photo'])): ?>
                                <img loading="lazy" src="/<?= htmlspecialchars($review['user_photo']) ?>" alt="Photo de <?= htmlspecialchars($review['user_prenom'] ?? 'utilisateur') ?>">
                            <?php else: ?>
                                <?= strtoupper(substr($review['user_prenom'] ?? $review['author_name'] ?? 'A', 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="review-author">
                            <div class="review-author-name">
                                <?= htmlspecialchars(($review['user_prenom'] ?? '') . ' ' . substr($review['user_nom'] ?? '', 0, 1) . '.') ?: htmlspecialchars($review['author_name'] ?? 'Anonyme') ?>
                            </div>
                            <div class="review-author-meta">
                                <?php if (!empty($review['user_ville'])): ?><span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($review['user_ville']) ?></span><?php endif; ?>
                                <?php if (!empty($review['user_total_reviews'])): ?><span><?= $review['user_total_reviews'] ?> avis</span><?php endif; ?>
                                <?php if (!empty($review['has_checkin'])): ?><span class="review-checkin-badge"><i class="fas fa-location-dot"></i> Visite confirmée</span><?php endif; ?>
                                <?php if (!empty($review['photos'])): ?><span class="review-photo-badge"><i class="fas fa-camera"></i> Avec photos</span><?php endif; ?>
                                <?php if (!empty($review['user_badge']) && $review['user_badge'] !== 'Explorateur'): ?>
                                    <span class="review-loyalty-badge" style="background:<?= htmlspecialchars($review['user_badge_color'] ?? '#f1f5f9') ?>20;color:<?= htmlspecialchars($review['user_badge_color'] ?? '#64748b') ?>">
                                        <span class="rlb-icon"><?= $review['user_badge_icon'] ?? '' ?></span> <?= htmlspecialchars($review['user_badge']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (($review['user_points'] ?? 0) >= 1200): ?>
                                    <span class="review-top-contributor"><i class="fas fa-award"></i> Top contributeur</span>
                                <?php endif; ?>
                                <?php if (!empty($review['user_title'])): ?>
                                    <span class="review-loyalty-badge" style="background:<?= htmlspecialchars($review['user_title_color'] ?? '#6b7280') ?>15;color:<?= htmlspecialchars($review['user_title_color'] ?? '#6b7280') ?>">
                                        <span class="rlb-icon"><?= $review['user_title_icon'] ?? '' ?></span> <?= htmlspecialchars($review['user_title']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (($review['user_visits_this_resto'] ?? 0) >= 3): ?>
                                    <span class="review-loyalty-badge" style="background:#fef3c715;color:#d97706;border:1px solid #fbbf2433">
                                        <i class="fas fa-crown" style="font-size:11px"></i> Client fidele
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="review-rating">
                            <div class="stars">
                                <?php $note = (int)$review['note_globale']; for ($i = 0; $i < 5; $i++): echo $i < $note ? '<i class="fas fa-star"></i>' : '<i class="fas fa-star empty"></i>'; endfor; ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($review['title'])): ?><h4 class="review-title"><?= htmlspecialchars($review['title']) ?></h4><?php endif; ?>
                    <?php if (!empty($review['message'])): ?><p class="review-content"><?= nl2br(htmlspecialchars($review['message'])) ?></p><?php endif; ?>

                    <?php if (!empty($review['pros'])): ?>
                    <div style="margin-top:10px;padding:8px 12px;background:#f0fdf4;border-radius:8px;font-size:14px">
                        <strong style="color:#16a34a"><i class="fas fa-check-circle"></i> Points forts :</strong>
                        <span><?= nl2br(htmlspecialchars($review['pros'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($review['cons'])): ?>
                    <div style="margin-top:6px;padding:8px 12px;background:#fef3c7;border-radius:8px;font-size:14px">
                        <strong style="color:#d97706"><i class="fas fa-exclamation-circle"></i> Points faibles :</strong>
                        <span><?= nl2br(htmlspecialchars($review['cons'])) ?></span>
                    </div>
                    <?php endif; ?>

                        <?php if (!empty($review['photos'])):
    $photos = explode('|||', $review['photos']);
    if (count($photos) > 0 && !empty($photos[0])):
?>
    <div class="review-photos">
        <?php foreach ($photos as $index => $photoPath): 
            if (empty($photoPath)) continue;
        ?>
            <div class="review-photo-item" onclick="openLightbox(<?= $review['id'] ?>, <?= $index ?>)" data-review-id="<?= $review['id'] ?>" data-photo-index="<?= $index ?>">
                <img loading="lazy" src="/<?= htmlspecialchars($photoPath) ?>" alt="Photo avis" loading="lazy">
                <?php if ($index === 0 && count($photos) > 1): ?>
                    <span class="review-photo-count">+<?= count($photos) - 1 ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; endif; ?>

                    <div class="review-visit">
                        <span><i class="far fa-calendar"></i> <?= getRelativeTime($review['created_at']) ?></span>
                        <?php if (!empty($review['trip_type'])): ?><span><i class="fas fa-users"></i> <?= htmlspecialchars($review['trip_type']) ?></span><?php endif; ?>
                        <?php if ($review['source'] !== 'site'): ?><span><i class="fas fa-external-link-alt"></i> <?= ucfirst($review['source']) ?></span><?php endif; ?>
                    </div>
                    
                    <?php if (!empty($review['owner_response'])): ?>
                        <div class="owner-response">
                            <div class="owner-response-header">
                                <div class="owner-badge">
                                    <i class="fas fa-store"></i>
                                    <span>Réponse du propriétaire</span>
                                </div>
                                <span class="owner-response-date"><?= getRelativeTime($review['updated_at'] ?? $review['created_at']) ?></span>
                            </div>
                            <p class="owner-response-text"><?= nl2br(htmlspecialchars($review['owner_response'])) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ($isOwner && empty($review['owner_response'])): ?>
    <button class="btn-respond" data-review-id="<?= $review['id'] ?>">
        <i class="fas fa-reply"></i> Répondre à cet avis
    </button>
<?php endif; ?>
 
                    <div class="review-helpful">
                        <span class="reactions-label">Reactions :</span>
                        <button class="reaction-btn" data-review-id="<?= $review['id'] ?>" data-reaction="useful">
                            <i class="far fa-thumbs-up"></i> Utile <span class="vote-count"><?= (int)($review['votes_utiles'] ?? 0) ?></span>
                        </button>
                        <button class="reaction-btn" data-review-id="<?= $review['id'] ?>" data-reaction="funny">
                            <i class="far fa-face-laugh"></i> Drole <span class="vote-count"><?= (int)($review['votes_funny'] ?? 0) ?></span>
                        </button>
                        <button class="reaction-btn" data-review-id="<?= $review['id'] ?>" data-reaction="love">
                            <i class="far fa-heart"></i> J'adore <span class="vote-count"><?= (int)($review['votes_love'] ?? 0) ?></span>
                        </button>
                        <button class="btn-report-review" data-review-id="<?= $review['id'] ?>" style="margin-left:auto">
    <i class="fas fa-flag"></i> Signaler
</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <?php if ($totalReviews > 3): ?>
            <button class="btn-load-more" id="loadMoreReviews">
                <i class="fas fa-chevron-down"></i> Voir plus d'avis
            </button>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Quick Tips -->
    <?php if (!empty($r['tips']) || isset($_SESSION['user'])): ?>
    <div id="conseils" style="margin:32px 0;padding:24px;background:#fffbeb;border-radius:12px;border:1px solid #fde68a;scroll-margin-top:70px">
        <h3 style="font-size:16px;margin:0 0 14px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-lightbulb" style="color:#f59e0b"></i> Conseils des visiteurs
            <?php if (!empty($r['tips'])): ?>
                <span style="background:#fef3c7;color:#92400e;font-size:12px;padding:2px 8px;border-radius:10px;font-weight:700"><?= count($r['tips']) ?></span>
            <?php endif; ?>
        </h3>
        <div id="tipsList">
            <?php if (!empty($r['tips'])): ?>
                <?php foreach ($r['tips'] as $tip): ?>
                <div style="display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #fef3c7">
                    <div style="width:32px;height:32px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;color:#92400e;font-weight:700">
                        <?= strtoupper(substr($tip['prenom'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <p style="margin:0;font-size:13px;color:#1f2937">"<?= htmlspecialchars($tip['message']) ?>"</p>
                        <div style="font-size:11px;color:#9ca3af;margin-top:4px">
                            <?= htmlspecialchars($tip['prenom'] . ' ' . substr($tip['user_nom'] ?? '', 0, 1) . '.') ?>
                            &middot; <?= date('d/m/Y', strtotime($tip['created_at'])) ?>
                            <?php if ($tip['votes'] > 0): ?>&middot; <i class="fas fa-thumbs-up"></i> <?= $tip['votes'] ?><?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="font-size:13px;color:#92400e;margin:0;opacity:0.6">Aucun conseil pour le moment. Soyez le premier !</p>
            <?php endif; ?>
        </div>
        <?php if (isset($_SESSION['user'])): ?>
        <div style="margin-top:12px;display:flex;gap:8px">
            <input type="text" id="tipInput" maxlength="200" placeholder="Partagez un conseil rapide (max 200 car.)..." style="flex:1;padding:8px 12px;border:1px solid #fde68a;border-radius:8px;font-size:13px;background:#fff">
            <button onclick="submitTip()" style="padding:8px 16px;background:#f59e0b;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Envoyer</button>
        </div>
        <div style="font-size:11px;color:#9ca3af;margin-top:4px"><span id="tipCharCount">0</span>/200 &middot; +3 points fidelite</div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Popular Dishes -->
    <?php if (!empty($r['popular_dishes'])): ?>
    <div style="margin:24px 0;padding:20px;background:#fff;border-radius:12px;border:1px solid var(--gray-200)">
        <h3 style="font-size:16px;margin:0 0 14px;display:flex;align-items:center;gap:8px">
            <i class="fas fa-fire" style="color:#ef4444"></i> Plats populaires
        </h3>
        <div style="display:flex;flex-wrap:wrap;gap:10px">
            <?php foreach ($r['popular_dishes'] as $dish): ?>
            <div style="display:flex;align-items:center;gap:8px;padding:8px 14px;background:var(--gray-50);border-radius:10px;border:1px solid var(--gray-200)">
                <span style="font-size:14px;font-weight:600;color:var(--primary)"><?= htmlspecialchars($dish['name']) ?></span>
                <span style="font-size:11px;color:var(--gray-500)">(<?= $dish['mentions'] ?> mention<?= $dish['mentions'] > 1 ? 's' : '' ?>)</span>
                <?php if ($dish['price']): ?><span style="font-size:12px;font-weight:700;color:var(--accent)"><?= number_format($dish['price'], 0) ?> DA</span><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="write-review-prompt">
        <h4>Vous avez visite ce restaurant ?</h4>
        <p>Partagez votre experience avec la communaute</p>
        <a href="/restaurant/<?= $r['id'] ?>/review" class="btn-action btn-primary"><i class="fas fa-pen"></i> Ecrire un avis</a>
    </div>
</section>

<style> /* NOUVELLES FEATURES CSS */
        
        /* Tri et filtre des avis */
        .reviews-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .sort-control {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sort-control label {
            font-size: 14px;
            font-weight: 500;
            color: var(--gray-600);
        }
        
        .sort-select {
            padding: 10px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 500;
            background: white;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .sort-select:hover, .sort-select:focus {
            border-color: var(--accent);
            outline: none;
        }
        
        /* Badge filtre actif */
        .filter-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--accent-bg);
            color: var(--accent);
            border-radius: 24px;
            font-size: 14px;
            font-weight: 600;
            animation: slideDown 0.3s ease;
        }
        
        .filter-badge-close {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--accent);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            transition: var(--transition);
        }
        
        .filter-badge-close:hover {
            transform: scale(1.1);
        }
        
        /* Barres de stats cliquables */
        .stats-bar-row {
            cursor: pointer;
            transition: var(--transition);
            padding: 4px;
            border-radius: 8px;
        }
        
        .stats-bar-row:hover {
            background: var(--gray-50);
        }
        
        .stats-bar-row.active {
            background: var(--accent-bg);
        }
        
        .stats-bar-row.active .stats-bar-fill {
            background: var(--accent-light);
            box-shadow: 0 0 12px rgba(0, 135, 90, 0.3);
        }
        
        /* Photos dans les avis */
        .review-photos {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 16px;
            margin-bottom: 16px;
        }
        
        .review-photo-thumb {
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            overflow: hidden;
            cursor: pointer;
            background-size: cover;
            background-position: center;
            transition: var(--transition);
            position: relative;
        }
        
        .review-photo-thumb:hover {
            transform: scale(1.05);
            box-shadow: var(--shadow);
        }
        
        .review-photo-more {
            aspect-ratio: 1;
            border-radius: var(--radius-sm);
            background: rgba(0,0,0,0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
        }
        
        /* Réponse du propriétaire */
        .owner-response {
            margin-top: 16px;
            padding: 16px;
            background: #f8fafc;
            border-left: 4px solid var(--accent);
            border-radius: var(--radius-sm);
        }
        
        .owner-response-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .owner-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 13px;
            color: var(--accent);
        }
        
        .owner-badge i {
            font-size: 16px;
        }
        
        .owner-response-date {
            font-size: 12px;
            color: var(--gray-500);
        }
        
        .owner-response-text {
            font-size: 14px;
            line-height: 1.7;
            color: var(--gray-700);
        }
        
        /* Badge "Visite confirmée" (check-in GPS) */
        .review-checkin-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #dcfce7;
            color: #166534;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .review-checkin-badge i { font-size: 10px; }

        /* Badge "Avec photos" */
        .review-photo-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .review-photo-badge i { font-size: 10px; }

        /* Badge fidélité sur les avis */
        .review-loyalty-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
        }
        .review-loyalty-badge .rlb-icon { font-size: 12px; }
        .review-top-contributor {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: #92400e;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }

        /* Encadré spécial Ambassadeur / Legendaire */
        .review-card.review-ambassadeur { border-left: 3px solid #eab308; }
        .review-card.review-legendaire { border-left: 3px solid #dc2626; background: linear-gradient(135deg, #fff 0%, #fef2f2 100%); }
        
        /* Vote utile - état voté */
        .helpful-btn.voted {
            background: var(--accent-bg);
            border-color: var(--accent);
            color: var(--accent);
            cursor: not-allowed;
        }
        
        .helpful-btn.voted i {
            color: var(--accent);
        }
        
        /* Bouton Signaler */
        .btn-report-review {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: transparent;
            color: var(--gray-500);
            border: 1px solid var(--gray-200);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-report-review:hover {
            color: var(--danger);
            border-color: var(--danger);
            background: rgba(239, 68, 68, 0.05);
        }
        
        .btn-report-review.reported {
            color: var(--accent);
            border-color: var(--accent);
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        /* Modal Signalement */
        .report-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            padding: 20px;
        }
        
        .report-modal-overlay.active {
            opacity: 1;
        }
        
        .report-modal {
            background: white;
            border-radius: var(--radius);
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.9) translateY(20px);
            transition: transform 0.3s ease;
            position: relative;
        }
        
        .report-modal-overlay.active .report-modal {
            transform: scale(1) translateY(0);
        }
        
        .report-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--gray-100);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            z-index: 10;
        }
        
        .report-modal-close:hover {
            background: var(--gray-200);
            transform: rotate(90deg);
        }
        
        .report-modal-header {
            padding: 32px 24px 24px;
            text-align: center;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .report-modal-header i {
            font-size: 48px;
            color: var(--danger);
            margin-bottom: 16px;
        }
        
        .report-modal-header h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .report-modal-header p {
            font-size: 14px;
            color: var(--gray-500);
        }
        
        .report-modal-body {
            padding: 24px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--gray-700);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            font-family: var(--font-body);
            font-size: 14px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        .report-warning {
            display: flex;
            gap: 12px;
            padding: 12px 16px;
            background: #fef3c7;
            border-left: 3px solid var(--warning);
            border-radius: 8px;
            font-size: 13px;
            color: var(--gray-700);
        }
        
        .report-warning i {
            color: var(--warning);
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .report-modal-footer {
            padding: 20px 24px;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        /* Message "Aucun avis" */
        .no-reviews-message {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border: 2px dashed var(--gray-200);
            border-radius: var(--radius);
        }
        
        .no-reviews-message i {
            font-size: 48px;
            color: var(--gray-300);
            margin-bottom: 16px;
        }
        
        .no-reviews-message h4 {
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--gray-700);
        }
        
        .no-reviews-message p {
            color: var(--gray-500);
            font-size: 14px;
        }
        
        /* Skeleton loader */
        .skeleton {
            animation: pulse 1.5s ease-in-out infinite;
        }
        
        .skeleton-header {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .skeleton-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--gray-200);
        }
        
        .skeleton-author {
            flex: 1;
        }
        
        .skeleton-line {
            height: 12px;
            background: var(--gray-200);
            border-radius: 6px;
            margin-bottom: 8px;
        }
        
        .skeleton-line.short {
            width: 60%;
        }
        
        .skeleton-content {
            margin-top: 16px;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Formulaire de réponse propriétaire */
        .response-form {
            margin-top: 16px;
            padding: 20px;
            background: white;
            border: 2px solid var(--accent);
            border-radius: var(--radius);
            animation: slideDown 0.3s ease;
        }
        
        .response-form-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .response-form-header h5 {
            font-size: 16px;
            font-weight: 600;
            color: var(--accent);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .response-textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius-sm);
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            transition: var(--transition);
        }
        
        .response-textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-bg);
        }
        
        .response-form-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        
        .btn-cancel-response,
        .btn-submit-response {
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            cursor: pointer;
        }
        
        .btn-cancel-response {
            background: var(--gray-100);
            color: var(--gray-700);
            border: 2px solid var(--gray-200);
        }
        
        .btn-cancel-response:hover {
            background: var(--gray-200);
        }
        
        .btn-submit-response {
            background: var(--accent);
            color: white;
            border: 2px solid var(--accent);
        }
        
        .btn-submit-response:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 135, 90, 0.3);
        }
        
        .btn-submit-response:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .response-form-tip {
            margin-top: 12px;
            padding: 10px 12px;
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            border-radius: 6px;
            font-size: 13px;
            color: var(--gray-700);
        }

        .response-form-tip strong {
            color: var(--primary);
        }

        /* AI Response Templates */
        .response-templates { margin-bottom: 14px; background: #f5f3ff; border: 1px solid #e9e5ff; border-radius: var(--radius-sm); overflow: hidden; }
        .response-templates.collapsed .response-templates-list { display: none; }
        .response-templates.collapsed .response-templates-toggle i { transform: rotate(180deg); }
        .response-templates-header { display: flex; align-items: center; gap: 8px; padding: 10px 14px; font-size: 13px; font-weight: 600; color: #6d28d9; }
        .response-templates-toggle { margin-left: auto; background: none; border: none; color: #6d28d9; cursor: pointer; padding: 2px 6px; }
        .response-templates-toggle i { transition: transform 0.2s; }
        .response-templates-list { padding: 0 14px 14px; display: flex; flex-direction: column; gap: 8px; }
        .response-template-btn { text-align: left; background: #fff; border: 1px solid #e9e5ff; border-radius: 8px; padding: 10px 14px; font-size: 13px; line-height: 1.5; color: var(--gray-700); cursor: pointer; transition: all 0.2s; }
        .response-template-btn:hover { border-color: #8b5cf6; background: #faf5ff; transform: translateX(4px); }
        
        /* Bouton "Répondre" */
        .btn-respond {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--accent-bg);
            color: var(--accent);
            border: 2px solid var(--accent);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 16px;
            margin-bottom: 16px;
        }
        
        .btn-respond:hover {
            background: var(--accent);
            color: white;
            transform: translateX(4px);
        }
        
        /* Notifications Toast */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            padding: 16px 24px;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            min-width: 300px;
            max-width: 500px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .toast-notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast-notification i {
            font-size: 20px;
        }
        
        .toast-success {
            border-left: 4px solid #10b981;
        }
        
        .toast-success i {
            color: #10b981;
        }
        
        .toast-error {
            border-left: 4px solid #ef4444;
        }
        
        .toast-error i {
            color: #ef4444;
        }
        
        @media (max-width: 640px) {
            .toast-notification {
                left: 20px;
                right: 20px;
                min-width: auto;
            }
        }
        
        /* Bandeau propriétaire */
        .owner-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px 24px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow);
        }
        
        .owner-banner-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .owner-banner-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .owner-banner-text h4 {
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .owner-banner-text p {
            font-size: 13px;
            opacity: 0.9;
        }
        
        .owner-banner-actions {
            display: flex;
            gap: 12px;
        }
        
        .owner-btn {
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            border-radius: 24px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .owner-btn:hover {
            background: white;
            color: #667eea;
        }
        
        /* Stats proprio */
        .owner-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            padding: 20px;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            margin-bottom: 24px;
        }
        
        .owner-stat-item {
            text-align: center;
            padding: 12px;
            border-radius: var(--radius-sm);
            background: var(--gray-50);
        }
        
        .owner-stat-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 4px;
        }
        
        .owner-stat-label {
            font-size: 12px;
            color: var(--gray-600);
        }
        
        @media (max-width: 768px) {
            .owner-banner {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }
            
            .owner-banner-content {
                flex-direction: column;
            }
            
            .owner-stats {
                grid-template-columns: 1fr;
            }
            
            .review-photos {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        /* ═══ Q&A Section ═══ */
        .qa-section { margin-top: 32px; }
        .qa-list { display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px; }
        .qa-item { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 20px; transition: var(--transition); }
        .qa-item:hover { box-shadow: var(--shadow); }
        .qa-question-header { display: flex; gap: 12px; align-items: flex-start; }
        .qa-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--accent-bg); color: var(--accent); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; flex-shrink: 0; overflow: hidden; }
        .qa-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .qa-question-content { flex: 1; }
        .qa-question-text { font-weight: 600; font-size: 15px; color: var(--primary); margin-bottom: 6px; line-height: 1.4; }
        .qa-question-meta { font-size: 13px; color: var(--gray-500); display: flex; align-items: center; gap: 8px; }
        .qa-answers { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-100); display: flex; flex-direction: column; gap: 12px; }
        .qa-answer { display: flex; gap: 12px; padding: 12px; background: var(--gray-50); border-radius: var(--radius-sm); }
        .qa-answer.owner-answer { background: #f0fdf4; border: 1px solid #bbf7d0; }
        .qa-answer-content { flex: 1; }
        .qa-answer-author { font-weight: 600; font-size: 13px; color: var(--primary); margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
        .qa-owner-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; background: var(--accent); color: white; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .qa-answer-text { font-size: 14px; color: var(--gray-600); line-height: 1.5; }
        .qa-answer-date { font-size: 12px; color: var(--gray-400); margin-top: 4px; }
        .qa-reply-toggle { background: none; border: none; color: var(--accent); font-size: 13px; font-weight: 600; cursor: pointer; padding: 4px 0; margin-top: 8px; }
        .qa-reply-toggle:hover { text-decoration: underline; }
        .qa-reply-form { margin-top: 12px; display: none; }
        .qa-reply-form.visible { display: block; }
        .qa-reply-form textarea { width: 100%; padding: 10px 14px; border: 2px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; resize: vertical; min-height: 60px; transition: border-color 0.2s; }
        .qa-reply-form textarea:focus { outline: none; border-color: var(--accent); }
        .qa-reply-actions { display: flex; gap: 8px; margin-top: 8px; justify-content: flex-end; }
        .qa-btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
        .qa-btn-primary { background: var(--accent); color: white; }
        .qa-btn-primary:hover { background: var(--accent-light); }
        .qa-btn-cancel { background: var(--gray-100); color: var(--gray-600); }
        .qa-ask-form { background: white; border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 20px; }
        .qa-ask-form h4 { font-size: 15px; margin-bottom: 12px; color: var(--primary); }
        .qa-ask-form textarea { width: 100%; padding: 12px 16px; border: 2px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; resize: vertical; min-height: 80px; transition: border-color 0.2s; }
        .qa-ask-form textarea:focus { outline: none; border-color: var(--accent); }
        .qa-ask-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 12px; }
        .qa-char-count { font-size: 12px; color: var(--gray-400); }
        .qa-no-questions { text-align: center; padding: 32px; color: var(--gray-400); }
        .qa-no-questions i { font-size: 32px; margin-bottom: 12px; display: block; }
        .qa-answer-count { font-size: 13px; color: var(--accent); font-weight: 600; cursor: pointer; }

        /* Friends reviews section */
        .friends-reviews-section { background: linear-gradient(135deg, #f0fdf4, #ecfdf5); border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .friends-reviews-title { font-size: 16px; font-weight: 700; color: #166534; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
        .friends-reviews-title i { color: #22c55e; }
        .friends-reviews-list { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 4px; }
        .friend-review-card { flex-shrink: 0; width: 220px; background: white; border-radius: 10px; padding: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .friend-review-header { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; }
        .friend-avatar { width: 36px; height: 36px; border-radius: 50%; background: #00635a; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; overflow: hidden; flex-shrink: 0; }
        .friend-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .friend-name { font-size: 13px; font-weight: 600; color: #111827; text-decoration: none; }
        .friend-name:hover { text-decoration: underline; }
        .friend-rating { margin-top: 2px; }
        .friend-review-text { font-size: 13px; color: #4b5563; line-height: 1.4; margin: 0; }
</style>

            <!-- ═══ SECTION Q&A ═══ -->
            <?php $questions = $r['questions'] ?? []; ?>
            <section class="section qa-section" id="qa">
                <h2 class="section-title"><i class="fas fa-question-circle"></i> Questions & Réponses</h2>

                <?php if (!empty($questions)): ?>
                    <div class="qa-list">
                        <?php foreach ($questions as $q): ?>
                            <div class="qa-item" data-question-id="<?= $q['id'] ?>">
                                <div class="qa-question-header">
                                    <div class="qa-avatar">
                                        <?php if (!empty($q['user_photo'])): ?>
                                            <img src="/<?= htmlspecialchars($q['user_photo']) ?>" alt="Photo de <?= htmlspecialchars($q['user_prenom'] ?? 'utilisateur') ?>">
                                        <?php else: ?>
                                            <?= strtoupper(substr($q['user_prenom'] ?? 'A', 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="qa-question-content">
                                        <div class="qa-question-text"><?= htmlspecialchars($q['question']) ?></div>
                                        <div class="qa-question-meta">
                                            <span><?= htmlspecialchars(($q['user_prenom'] ?? '') . ' ' . substr($q['user_nom'] ?? '', 0, 1) . '.') ?></span>
                                            <span>&middot;</span>
                                            <span><?= date('d/m/Y', strtotime($q['created_at'])) ?></span>
                                            <?php if ($q['answer_count'] > 0): ?>
                                                <span>&middot;</span>
                                                <span class="qa-answer-count"><?= $q['answer_count'] ?> réponse<?= $q['answer_count'] > 1 ? 's' : '' ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($q['answers'])): ?>
                                    <div class="qa-answers">
                                        <?php foreach ($q['answers'] as $a): ?>
                                            <div class="qa-answer <?= $a['is_owner_answer'] ? 'owner-answer' : '' ?>">
                                                <div class="qa-avatar" style="width:28px;height:28px;font-size:12px;">
                                                    <?php if (!empty($a['user_photo'])): ?>
                                                        <img src="/<?= htmlspecialchars($a['user_photo']) ?>" alt="Photo de <?= htmlspecialchars($a['user_prenom'] ?? 'utilisateur') ?>">
                                                    <?php else: ?>
                                                        <?= strtoupper(substr($a['user_prenom'] ?? 'A', 0, 1)) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="qa-answer-content">
                                                    <div class="qa-answer-author">
                                                        <?= htmlspecialchars(($a['user_prenom'] ?? '') . ' ' . substr($a['user_nom'] ?? '', 0, 1) . '.') ?>
                                                        <?php if ($a['is_owner_answer']): ?>
                                                            <span class="qa-owner-badge"><i class="fas fa-store"></i> Propriétaire</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="qa-answer-text"><?= nl2br(htmlspecialchars($a['answer'])) ?></div>
                                                    <div class="qa-answer-date"><?= date('d/m/Y', strtotime($a['created_at'])) ?></div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Formulaire répondre -->
                                <button class="qa-reply-toggle" onclick="toggleReplyForm(<?= $q['id'] ?>)">
                                    <i class="fas fa-reply"></i> Répondre
                                </button>
                                <div class="qa-reply-form" id="replyForm_<?= $q['id'] ?>">
                                    <textarea id="replyText_<?= $q['id'] ?>" placeholder="Votre réponse..." maxlength="1000"></textarea>
                                    <div class="qa-reply-actions">
                                        <button class="qa-btn qa-btn-cancel" onclick="toggleReplyForm(<?= $q['id'] ?>)">Annuler</button>
                                        <button class="qa-btn qa-btn-primary" onclick="submitAnswer(<?= $q['id'] ?>)">Publier</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="qa-no-questions">
                        <i class="fas fa-comments"></i>
                        <p>Aucune question pour le moment. Soyez le premier à poser une question !</p>
                    </div>
                <?php endif; ?>

                <!-- Formulaire poser une question -->
                <div class="qa-ask-form">
                    <h4><i class="fas fa-plus-circle"></i> Poser une question</h4>
                    <textarea id="qaNewQuestion" placeholder="Votre question sur ce restaurant... (min. 10 caractères)" maxlength="500"></textarea>
                    <div class="qa-ask-actions">
                        <span class="qa-char-count"><span id="qaCharCount">0</span>/500</span>
                        <button class="qa-btn qa-btn-primary" id="qaSubmitBtn" onclick="submitQuestion()">
                            <i class="fas fa-paper-plane"></i> Publier
                        </button>
                    </div>
                </div>
            </section>

            <!-- ═══ SECTION MENU AVEC PRIX ═══ -->
            <?php
            $menuItems = $r['menu_items'] ?? [];
            if (!empty($menuItems) && !empty($r['menu_enabled'])):
                $menuByCategory = [];
                foreach ($menuItems as $mi) $menuByCategory[$mi['category']][] = $mi;
            ?>
            <section class="section" id="menu-section">
                <h2 class="section-title"><i class="fas fa-book-open"></i> Menu & Prix</h2>

                <?php $catIndex = 0; foreach ($menuByCategory as $cat => $items): ?>
                <div class="menu-accordion">
                    <button class="menu-accordion-header <?= $catIndex === 0 ? 'open' : '' ?>" onclick="toggleMenuAccordion(this)">
                        <span class="menu-accordion-title">
                            <i class="fas fa-utensils"></i> <?= htmlspecialchars($cat) ?>
                            <span class="menu-accordion-count"><?= count($items) ?></span>
                        </span>
                        <i class="fas fa-chevron-down menu-accordion-arrow"></i>
                    </button>
                    <div class="menu-accordion-body" <?= $catIndex === 0 ? 'style="max-height:2000px"' : '' ?>>
                        <?php foreach ($items as $item): ?>
                        <div class="menu-accordion-item">
                            <?php if (!empty($item['photo_path'])): ?>
                                <img src="/uploads/menu/<?= htmlspecialchars($item['photo_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="menu-accordion-photo" loading="lazy" onclick="openMenuLightbox(this.src)" style="cursor:pointer">
                            <?php endif; ?>
                            <div class="menu-accordion-info">
                                <span class="menu-accordion-name"><?= htmlspecialchars($item['name']) ?></span>
                                <?php if (!empty($item['description'])): ?>
                                    <p class="menu-accordion-desc"><?= htmlspecialchars($item['description']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($item['allergens'])): ?>
                                    <div class="menu-allergens" style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px">
                                        <?php
                                        $algLabels = ['gluten'=>'Gluten','dairy'=>'Lait','eggs'=>'Œufs','fish'=>'Poisson','shellfish'=>'Crustacés','nuts'=>'Fruits à coque','peanuts'=>'Arachides','soy'=>'Soja','celery'=>'Céleri','mustard'=>'Moutarde','sesame'=>'Sésame','sulfites'=>'Sulfites','lupin'=>'Lupin','mollusks'=>'Mollusques'];
                                        foreach ($item['allergens'] as $alg):
                                            $lbl = $algLabels[$alg] ?? $alg;
                                        ?>
                                        <span title="<?= htmlspecialchars($lbl) ?>" style="display:inline-flex;align-items:center;gap:3px;font-size:11px;padding:2px 6px;background:#fef2f2;color:#dc2626;border-radius:4px;border:1px solid #fecaca"><i class="fas fa-exclamation-triangle" style="font-size:9px"></i> <?= htmlspecialchars($lbl) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($item['price']): ?>
                                <span class="menu-accordion-price"><?= number_format((float)$item['price'], 0) ?> DA</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $catIndex++; endforeach; ?>

                <?php if (!empty($r['owner_id']) && !empty($r['orders_enabled'])): ?>
                <div style="text-align:center;margin-top:20px">
                    <a href="/commander/<?= htmlspecialchars($r['slug'] ?? $r['id']) ?>" class="menu-order-btn">
                        <i class="fas fa-shopping-bag"></i> Commander ce menu en ligne
                    </a>
                </div>
                <?php endif; ?>
            </section>

            <!-- Menu photo lightbox -->
            <div class="menu-lightbox" id="menuLightbox" onclick="closeMenuLightbox(event)">
                <button class="menu-lightbox-close" onclick="closeMenuLightbox(event)">&times;</button>
                <img id="menuLightboxImg" src="" alt="Photo du plat">
            </div>
            <?php endif; ?>

            <!-- Restaurants similaires -->
            <?php if (!empty($similar)): ?>
                <section class="section">
                    <h2 class="section-title"><i class="fas fa-compass"></i> Restaurants similaires</h2>
                    <div class="similar-grid">
                        <?php foreach ($similar as $sim): ?>
                            <a href="/restaurant/<?= $sim['id'] ?>" class="similar-card">
                                <div class="similar-photo">
                                    <?php if (!empty($sim['main_photo'])): ?>
                                        <img loading="lazy" src="/<?= htmlspecialchars($sim['main_photo']) ?>" alt="<?= htmlspecialchars($sim['nom']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <div class="gallery-placeholder" style="height:100%"><i class="fas fa-utensils"></i></div>
                                    <?php endif; ?>
                                </div>
                                <div class="similar-info">
                                    <h4 class="similar-name"><?= htmlspecialchars($sim['nom']) ?></h4>
                                    <div class="similar-meta">
                                        <?php if ($sim['note_moyenne'] > 0): ?><span class="rating"><i class="fas fa-star"></i> <?= number_format(min($sim['note_moyenne'], 5), 1) ?></span><?php endif; ?>
                                        <span><?= htmlspecialchars($sim['type_cuisine'] ?? $sim['ville']) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

        </div>
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-sticky">
                
                <div class="card">
                    <div class="action-buttons">
                        <?php if (!empty($r['phone'])): ?>
<a href="tel:<?= htmlspecialchars($r['phone']) ?>" class="btn-action btn-primary" data-track="phone">
                                    <i class="fas fa-phone"></i> Appeler
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($r['whatsapp'])): ?>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $r['whatsapp']) ?>" class="btn-action btn-secondary" target="_blank">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        <?php endif; ?>
                        
<a href="https://www.google.com/maps/dir/?api=1&destination=<?= $r['gps_latitude'] ?>,<?= $r['gps_longitude'] ?>" class="btn-action btn-outline" target="_blank" data-track="directions">
                                <i class="fas fa-directions"></i> Itinéraire
                        </a>

                        <?php if (!empty($r['owner_id']) && !empty($r['orders_enabled'])): ?>
                        <a href="/commander/<?= htmlspecialchars($r['slug'] ?? $r['id']) ?>" class="btn-action btn-outline" style="border-color:#00635a;color:#00635a;text-decoration:none">
                            <i class="fas fa-shopping-bag"></i> Commander en ligne
                        </a>
                        <?php endif; ?>

                        <?php if (!empty($r['owner_id']) && !empty($r['reservations_enabled'])): ?>
                        <button class="btn-action btn-outline" onclick="openReservationModal()" style="border-color:#3b82f6;color:#3b82f6">
                            <i class="fas fa-calendar-check"></i> Reserver
                        </button>
                        <?php endif; ?>

                        <button class="btn-action btn-outline" onclick="doCheckin()" id="checkinBtn" style="border-color:#10b981;color:#10b981">
                            <i class="fas fa-map-marker-alt"></i> Check-in
                        </button>

                        <button class="btn-action btn-outline" onclick="openCollectionModal()" style="border-color:#6366f1;color:#6366f1">
                            <i class="fas fa-folder-plus"></i> Ajouter a une collection
                        </button>
                    </div>
                    
                    <div class="card-body contact-list">
                        <div class="contact-item">
                            <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-content">
                                <div class="contact-label">Adresse</div>
                                <div class="contact-value">
                                    <?= htmlspecialchars($r['adresse'] ?? '') ?>
                                    <?php if ($r['code_postal'] || $r['ville']): ?><br><?= htmlspecialchars(trim(($r['code_postal'] ?? '') . ' ' . ($r['ville'] ?? ''))) ?><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($r['phone'])): ?>
                            <div class="contact-item">
                                <div class="contact-icon"><i class="fas fa-phone"></i></div>
                                <div class="contact-content">
                                    <div class="contact-label">Téléphone</div>
                                    <div class="contact-value"><a href="tel:<?= htmlspecialchars($r['phone']) ?>" data-track="phone"><?= htmlspecialchars($r['phone']) ?></a></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($r['email'])): ?>
                            <div class="contact-item">
                                <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                                <div class="contact-content">
                                    <div class="contact-label">Email</div>
                                    <div class="contact-value"><a href="mailto:<?= htmlspecialchars($r['email']) ?>"><?= htmlspecialchars($r['email']) ?></a></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($r['website'])): ?>
                            <div class="contact-item">
                                <div class="contact-icon"><i class="fas fa-globe"></i></div>
                                <div class="contact-content">
                                    <div class="contact-label">Site web</div>
                                    <div class="contact-value"><a href="<?= htmlspecialchars($r['website']) ?>" target="_blank" data-track="website"><?= htmlspecialchars(preg_replace('#^https?://#', '', $r['website'])) ?></a></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($r['facebook']) || !empty($r['instagram']) || !empty($r['whatsapp'])): ?>
                        <div class="social-links">
                            <?php if (!empty($r['whatsapp'])): ?><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $r['whatsapp']) ?>" class="social-link whatsapp" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a><?php endif; ?>
                            <?php if (!empty($r['facebook'])): ?><a href="<?= htmlspecialchars($r['facebook']) ?>" class="social-link facebook" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                            <?php if (!empty($r['instagram'])): ?><a href="<?= htmlspecialchars($r['instagram']) ?>" class="social-link instagram" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($r['gps_latitude'] && $r['gps_longitude']): ?>
                    <?php $rLat = (float)$r['gps_latitude']; $rLng = (float)$r['gps_longitude']; ?>
                    <div class="card">
                        <div class="mini-map">
                            <div id="sidebarMap"></div>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= $rLat ?>,<?= $rLng ?>" class="map-overlay-link" target="_blank">
                                <span><i class="fas fa-expand-arrows-alt"></i> Agrandir</span>
                            </a>
                        </div>
                        <div class="gmaps-embed">
                            <iframe
                                src="https://maps.google.com/maps?q=<?= $rLat ?>,<?= $rLng ?>&z=17&output=embed"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen
                                title="Google Maps - <?= htmlspecialchars($r['nom']) ?>"
                            ></iframe>
                        </div>
                        <div class="map-action-row">
                            <a href="https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=<?= $rLat ?>,<?= $rLng ?>"
                               target="_blank" rel="noopener noreferrer" class="map-action-btn sv"
                               aria-label="Street View">
                                <i class="fas fa-street-view"></i> Street View
                            </a>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= $rLat ?>,<?= $rLng ?>"
                               target="_blank" rel="noopener noreferrer" class="map-action-btn"
                               aria-label="Google Maps">
                                <i class="fas fa-map-location-dot"></i> Google Maps
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
            </div>
        </aside>
        
    </div>
</main>

<!-- MOBILE CTA -->
<div class="mobile-cta">
    <div class="mobile-cta-content">
        <?php if (!empty($r['phone'])): ?>
            <a href="tel:<?= htmlspecialchars($r['phone']) ?>" class="btn-action btn-primary" data-track="phone"><i class="fas fa-phone"></i> Appeler</a>
        <?php endif; ?>
        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $r['gps_latitude'] ?>,<?= $r['gps_longitude'] ?>" class="btn-action btn-secondary" target="_blank" data-track="directions"><i class="fas fa-directions"></i> Y aller</a>
    </div>
</div>

<!-- MODAL GALLERY -->
<div class="modal-overlay" id="galleryModal" role="dialog" aria-modal="true" aria-label="Galerie photos">
    <button class="modal-close" onclick="closeGallery()" aria-label="Fermer la galerie"><i class="fas fa-times" aria-hidden="true"></i></button>
    <div class="gallery-modal-content">
        <div class="gallery-filters" id="galleryFilters"></div>
        <div class="gallery-main">
            <button class="gallery-nav-btn prev" onclick="prevPhoto()" aria-label="Photo précédente"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
            <img loading="lazy" src="" alt="Photo du restaurant" id="galleryMainImage">
            <button class="gallery-nav-btn next" onclick="nextPhoto()" aria-label="Photo suivante"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
        </div>
        <div class="gallery-thumbs" id="galleryThumbs"></div>
    </div>
</div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
 

const restaurant = {
    id: <?= $r['id'] ?>,
    nom: <?= json_encode($r['nom']) ?>,
    lat: <?= $r['gps_latitude'] ?? 'null' ?>,
    lng: <?= $r['gps_longitude'] ?? 'null' ?>
};

const photos = <?php
// Sécurisation : vérifier que $photos est bien un array
if (!isset($photos) || !is_array($photos)) {
    echo '[]';
} else {
    $photos_formatted = array_map(function($p) {
        // Si $p est un string, le convertir en array
        if (is_string($p)) {
            return ['path' => '/' . $p, 'legende' => '', 'category' => 'other'];
        }
        // Si $p est déjà un array
        return [
            'path' => '/' . ($p['path'] ?? $p),
            'legende' => $p['legende'] ?? '',
            'category' => $p['ai_category'] ?? $p['type'] ?? 'other'
        ];
    }, $photos);
    echo json_encode($photos_formatted);
}
?>;

// ═══════════════════════════════════════════════════════════════
// HEADER STICKY
// ═══════════════════════════════════════════════════════════════
const pageHeader = document.getElementById('pageHeader');
const sidebarSticky = document.querySelector('.sidebar-sticky');
window.addEventListener('scroll', () => {
    pageHeader.classList.toggle('visible', window.scrollY > 400);
    // Reset sidebar scroll when page is near the top (hero visible)
    if (sidebarSticky && window.scrollY < 300) {
        sidebarSticky.scrollTop = 0;
    }
});

// ═══════════════════════════════════════════════════════════════
// STICKY SECTION NAV (TripAdvisor style)
// ═══════════════════════════════════════════════════════════════
(function() {
    const nav = document.getElementById('sectionNav');
    if (!nav) return;
    const navTop = nav.offsetTop;
    const navHeight = nav.offsetHeight;
    let placeholder = null;

    // Sticky on scroll — sits below page-header when both are visible
    const pageHeaderEl = document.getElementById('pageHeader');
    function updateStickyNav() {
        if (window.scrollY >= navTop) {
            if (!nav.classList.contains('sticky')) {
                nav.classList.add('sticky');
                if (!placeholder) {
                    placeholder = document.createElement('div');
                    placeholder.style.height = navHeight + 'px';
                    placeholder.id = 'sectionNavPlaceholder';
                    nav.parentNode.insertBefore(placeholder, nav.nextSibling);
                }
            }
            // Position below page-header when it's visible
            if (pageHeaderEl && pageHeaderEl.classList.contains('visible')) {
                nav.style.top = pageHeaderEl.offsetHeight + 'px';
            } else {
                nav.style.top = '0';
            }
        } else {
            nav.classList.remove('sticky');
            nav.style.top = '';
            if (placeholder) { placeholder.remove(); placeholder = null; }
        }
    }
    window.addEventListener('scroll', updateStickyNav, { passive: true });

    // Active link highlighting with IntersectionObserver
    const navLinks = nav.querySelectorAll('.section-nav-link[data-section]');
    const sectionIds = Array.from(navLinks).map(l => l.dataset.section);
    const sections = sectionIds.map(id => document.getElementById(id)).filter(Boolean);

    if (sections.length > 0) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    navLinks.forEach(l => l.classList.remove('active'));
                    const link = nav.querySelector('[data-section="' + entry.target.id + '"]');
                    if (link) {
                        link.classList.add('active');
                        // Scroll nav to show active link on mobile
                        link.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
                    }
                }
            });
        }, { rootMargin: '-80px 0px -60% 0px', threshold: 0 });

        sections.forEach(s => observer.observe(s));
    }

    // Smooth scroll on click
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById(this.dataset.section);
            if (target) target.scrollIntoView({ behavior: 'smooth' });
        });
    });
})();

// ═══════════════════════════════════════════════════════════════
// MENU ACCORDION (expandable categories)
// ═══════════════════════════════════════════════════════════════
function toggleMenuAccordion(btn) {
    const body = btn.nextElementSibling;
    const isOpen = btn.classList.contains('open');
    btn.classList.toggle('open');
    if (isOpen) {
        body.style.maxHeight = '0';
        body.style.padding = '0 18px';
    } else {
        body.style.maxHeight = body.scrollHeight + 'px';
        body.style.padding = '4px 18px 14px';
    }
}

function openMenuLightbox(src) {
    document.getElementById('menuLightboxImg').src = src;
    document.getElementById('menuLightbox').classList.add('open');
}

function closeMenuLightbox(e) {
    if (e && e.target.tagName === 'IMG') return;
    document.getElementById('menuLightbox').classList.remove('open');
}

// ═══════════════════════════════════════════════════════════════
// GALERIE PHOTOS (avec filtres par catégorie)
// ═══════════════════════════════════════════════════════════════
let filteredPhotos = [...photos];
let currentFilter = 'all';

// Mapping catégories → labels français
const categoryLabels = {
    all: 'Toutes',
    food: 'Plats',
    ambiance: 'Ambiance',
    exterior: 'Exterieur',
    menu: 'Menu',
    main: 'Principale',
    slide: 'Galerie',
    plat: 'Plats',
    other: 'Autres'
};

function buildGalleryFilters() {
    const filtersContainer = document.getElementById('galleryFilters');
    if (!filtersContainer || photos.length === 0) return;

    // Compter les photos par catégorie
    const counts = {};
    photos.forEach(p => {
        const cat = p.category || 'other';
        counts[cat] = (counts[cat] || 0) + 1;
    });

    // Ne pas afficher les filtres s'il n'y a qu'une seule catégorie
    const uniqueCategories = Object.keys(counts);
    if (uniqueCategories.length <= 1) {
        filtersContainer.style.display = 'none';
        return;
    }

    let html = `<button class="gallery-filter-btn active" data-filter="all">Toutes <span class="gallery-filter-count">(${photos.length})</span></button>`;

    // Ordre préféré pour les catégories
    const preferredOrder = ['food', 'plat', 'ambiance', 'exterior', 'menu', 'main', 'slide', 'other'];
    const sortedCategories = uniqueCategories.sort((a, b) => {
        const ia = preferredOrder.indexOf(a);
        const ib = preferredOrder.indexOf(b);
        return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
    });

    sortedCategories.forEach(cat => {
        const label = categoryLabels[cat] || cat.charAt(0).toUpperCase() + cat.slice(1);
        html += `<button class="gallery-filter-btn" data-filter="${cat}">${label} <span class="gallery-filter-count">(${counts[cat]})</span></button>`;
    });

    filtersContainer.innerHTML = html;

    // Attacher les événements click aux boutons
    filtersContainer.querySelectorAll('.gallery-filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            filtersContainer.querySelectorAll('.gallery-filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            applyGalleryFilter();
        });
    });
}

function applyGalleryFilter() {
    if (currentFilter === 'all') {
        filteredPhotos = [...photos];
    } else {
        filteredPhotos = photos.filter(p => (p.category || 'other') === currentFilter);
    }

    if (filteredPhotos.length === 0) {
        filteredPhotos = [...photos];
    }

    currentPhotoIndex = 0;
    renderGalleryThumbs();
    updateGalleryImage();
}

function renderGalleryThumbs() {
    const thumbsContainer = document.getElementById('galleryThumbs');
    if (!thumbsContainer) return;

    thumbsContainer.innerHTML = filteredPhotos.map((p, i) => `
        <div class="gallery-thumb ${i === currentPhotoIndex ? 'active' : ''}" data-category="${p.category || 'other'}" onclick="goToPhoto(${i})">
            <img loading="lazy" src="${p.path}" alt="${p.legende || 'Photo du restaurant'}">
        </div>
    `).join('');
}

function openGallery(index = 0) {
    if (photos.length === 0) return;

    // Reset filter to "all" when opening
    currentFilter = 'all';
    filteredPhotos = [...photos];
    currentPhotoIndex = Math.min(index, filteredPhotos.length - 1);

    buildGalleryFilters();
    renderGalleryThumbs();
    updateGalleryImage();

    const modal = document.getElementById('galleryModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeGallery() {
    document.getElementById('galleryModal').classList.remove('active');
    document.body.style.overflow = '';
}

function updateGalleryImage() {
    if (filteredPhotos.length === 0) return;
    const mainImg = document.getElementById('galleryMainImage');
    mainImg.src = filteredPhotos[currentPhotoIndex].path;
    mainImg.alt = filteredPhotos[currentPhotoIndex].legende || restaurant.nom;
    document.querySelectorAll('.gallery-thumb').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === currentPhotoIndex);
    });
}

function prevPhoto() {
    if (filteredPhotos.length === 0) return;
    currentPhotoIndex = (currentPhotoIndex - 1 + filteredPhotos.length) % filteredPhotos.length;
    updateGalleryImage();
}

function nextPhoto() {
    if (filteredPhotos.length === 0) return;
    currentPhotoIndex = (currentPhotoIndex + 1) % filteredPhotos.length;
    updateGalleryImage();
}

function goToPhoto(index) {
    currentPhotoIndex = index;
    updateGalleryImage();
}

// Navigation clavier
document.addEventListener('keydown', (e) => {
    if (!document.getElementById('galleryModal').classList.contains('active')) return;
    if (e.key === 'Escape') closeGallery();
    if (e.key === 'ArrowLeft') prevPhoto();
    if (e.key === 'ArrowRight') nextPhoto();
});

// ═══════════════════════════════════════════════════════════════
// CARTE LEAFLET
// ═══════════════════════════════════════════════════════════════
if (restaurant.lat && restaurant.lng) {
    const map = L.map('sidebarMap', { 
        zoomControl: false, 
        dragging: false, 
        scrollWheelZoom: false 
    }).setView([restaurant.lat, restaurant.lng], 15);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { 
        attribution: '© OpenStreetMap' 
    }).addTo(map);
    
    const icon = L.divIcon({
        className: 'custom-marker',
        html: `<div style="background:#00875A;width:36px;height:36px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.3)"><i class="fas fa-utensils" style="color:white;transform:rotate(45deg);font-size:14px"></i></div>`,
        iconSize: [36, 36], 
        iconAnchor: [18, 36]
    });
    
    L.marker([restaurant.lat, restaurant.lng], { icon }).addTo(map);
}

// ═══════════════════════════════════════════════════════════════
// WISHLIST
// ═══════════════════════════════════════════════════════════════
function toggleWishlist() {
    const btn = document.getElementById('wishlistBtn');
    const icon = btn.querySelector('i');
    btn.classList.toggle('active');
    icon.classList.toggle('far');
    icon.classList.toggle('fas');
}

// ═══════════════════════════════════════════════════════════════
// ANIMATIONS INTERSECTION OBSERVER
// ═══════════════════════════════════════════════════════════════
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

document.querySelectorAll('.section, .similar-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// ═══════════════════════════════════════════════════════════════
// RECENTLY VIEWED (localStorage for all users)
// ═══════════════════════════════════════════════════════════════
(function() {
    try {
        var key = 'lbr_recently_viewed';
        var current = JSON.parse(localStorage.getItem(key) || '[]');
        var entry = {
            id: restaurant.id,
            nom: restaurant.nom,
            photo: <?= json_encode($mainPhoto ? '/' . $mainPhoto : null, JSON_HEX_TAG) ?>,
            cuisine: <?= json_encode($r['type_cuisine'] ?? '', JSON_HEX_TAG) ?>,
            note: <?= json_encode(number_format(min($r['note_moyenne'], 5), 1)) ?>,
            ville: <?= json_encode($r['ville'] ?? '', JSON_HEX_TAG) ?>,
            ts: Date.now()
        };
        current = current.filter(function(r) { return r.id !== entry.id; });
        current.unshift(entry);
        current = current.slice(0, 10);
        localStorage.setItem(key, JSON.stringify(current));
    } catch(e) {}
})();

</script>

<!-- Scripts externes -->
<script src="/assets/js/reviews.js?v=20260215"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const section = document.getElementById('avis');
        if (section) {
            const rid = section.dataset.restaurantId;
            if (rid) new ReviewsManager(parseInt(rid));
        }
    });
</script>

<script src="/assets/js/wishlist.js"></script>
 <script src="/assets/js/analytics.js"></script>
 
 <!-- LIGHTBOX PHOTOS -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()">
        <i class="fas fa-times"></i>
    </button>
    <button class="lightbox-nav prev" onclick="navigateLightbox(-1)">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="lightbox-nav next" onclick="navigateLightbox(1)">
        <i class="fas fa-chevron-right"></i>
    </button>
    <div class="lightbox-content">
        <img loading="lazy" id="lightboxImage" src="" alt="Photo">
    </div>
    <div class="lightbox-counter" id="lightboxCounter"></div>
</div>
                                                                       
<script>
 
   // ========================================
// LIGHTBOX PHOTOS
// ========================================
let currentLightboxReview = null;
let currentLightboxIndex = 0;
let currentLightboxPhotos = [];

function openLightbox(reviewId, photoIndex) {
    const reviewCard = document.querySelector(".review-card[data-review-id='" + reviewId + "'], .review-item[data-review-id='" + reviewId + "']");
    if (!reviewCard) return;

    const photoElements = reviewCard.querySelectorAll('.review-photo-item img');
    if (photoElements.length === 0) return;

    currentLightboxPhotos = Array.from(photoElements).map(img => img.src);
    currentLightboxReview = reviewId;
    currentLightboxIndex = photoIndex;

    showLightboxPhoto();
    document.getElementById('lightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
    document.body.style.overflow = '';
}

function navigateLightbox(direction) {
    currentLightboxIndex += direction;
    if (currentLightboxIndex < 0) currentLightboxIndex = currentLightboxPhotos.length - 1;
    else if (currentLightboxIndex >= currentLightboxPhotos.length) currentLightboxIndex = 0;
    showLightboxPhoto();
}

function showLightboxPhoto() {
    const img = document.getElementById('lightboxImage');
    const counter = document.getElementById('lightboxCounter');
    if (!img) return;

    img.src = currentLightboxPhotos[currentLightboxIndex];
    if (counter) counter.textContent = (currentLightboxIndex + 1) + ' / ' + currentLightboxPhotos.length;

    const prevBtn = document.querySelector('.lightbox-nav.prev');
    const nextBtn = document.querySelector('.lightbox-nav.next');
    if (prevBtn && nextBtn) {
        prevBtn.style.display = currentLightboxPhotos.length > 1 ? 'flex' : 'none';
        nextBtn.style.display = currentLightboxPhotos.length > 1 ? 'flex' : 'none';
    }
}

document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('lightbox');
    if (!lightbox || !lightbox.classList.contains('active')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
});

document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target.id === 'lightbox') closeLightbox();
});
</script>
 <script src="/assets/js/lightbox.js"></script>

<!-- Q&A JavaScript -->
<script>
const restaurantId = <?= (int)$r['id'] ?>;

// Compteur de caractères question
const qaInput = document.getElementById('qaNewQuestion');
const qaCharCount = document.getElementById('qaCharCount');
if (qaInput) {
    qaInput.addEventListener('input', () => {
        qaCharCount.textContent = qaInput.value.length;
    });
}

function toggleReplyForm(questionId) {
    const form = document.getElementById('replyForm_' + questionId);
    if (form) form.classList.toggle('visible');
}

async function submitQuestion() {
    const text = document.getElementById('qaNewQuestion').value.trim();
    if (text.length < 10) {
        alert('La question doit faire au moins 10 caractères.');
        return;
    }
    const btn = document.getElementById('qaSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';

    try {
        const formData = new FormData();
        formData.append('question', text);
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const headers = {};
        if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content;
        const res = await fetch('/api/restaurant/' + restaurantId + '/question', {
            method: 'POST',
            headers,
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier';
        }
    } catch (err) {
        alert('Erreur réseau. Réessayez.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier';
    }
}

async function submitAnswer(questionId) {
    const text = document.getElementById('replyText_' + questionId).value.trim();
    if (text.length < 5) {
        alert('La réponse doit faire au moins 5 caractères.');
        return;
    }

    try {
        const formData = new FormData();
        formData.append('answer', text);
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const headers = {};
        if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content;
        const res = await fetch('/api/question/' + questionId + '/answer', {
            method: 'POST',
            headers,
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Erreur');
        }
    } catch (err) {
        alert('Erreur réseau. Réessayez.');
    }
}
</script>

<!-- ═══════════════════════════════════════════════════════════
     MODALS & JS: CHECK-IN, RESERVATION, COLLECTIONS
     ═══════════════════════════════════════════════════════════ -->

<!-- Reservation Modal -->
<div id="reservationModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:28px;width:90%;max-width:440px">
        <h3 style="margin:0 0 18px;font-size:18px"><i class="fas fa-calendar-check" style="color:#3b82f6"></i> Reserver chez <?= htmlspecialchars($r['nom']) ?></h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:3px">Date</label>
                <input type="date" id="resDate" min="<?= date('Y-m-d') ?>" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;box-sizing:border-box">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:3px">Heure</label>
                <input type="time" id="resHeure" value="19:00" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;box-sizing:border-box">
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
            <div>
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:3px">Personnes</label>
                <select id="resNb" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px">
                    <?php for ($i = 1; $i <= 10; $i++): ?><option value="<?= $i ?>" <?= $i === 2 ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?>
                </select>
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;display:block;margin-bottom:3px">Telephone</label>
                <input type="tel" id="resTel" placeholder="06..." style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;box-sizing:border-box">
            </div>
        </div>
        <label style="font-size:13px;font-weight:600;display:block;margin-bottom:3px">Message (optionnel)</label>
        <textarea id="resMsg" placeholder="Demandes speciales..." style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;height:60px;resize:none;box-sizing:border-box;margin-bottom:14px"></textarea>
        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button onclick="closeReservationModal()" style="padding:8px 18px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer">Annuler</button>
            <button onclick="submitReservation()" style="padding:8px 18px;border:none;border-radius:6px;background:#3b82f6;color:#fff;cursor:pointer;font-weight:600">Envoyer</button>
        </div>
    </div>
</div>

<!-- Collection Modal -->
<div id="collectionModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:12px;padding:28px;width:90%;max-width:400px">
        <h3 style="margin:0 0 16px;font-size:18px"><i class="fas fa-folder-plus" style="color:#6366f1"></i> Ajouter a une collection</h3>
        <div id="colListTarget" style="margin-bottom:14px;max-height:200px;overflow-y:auto">
            <p style="color:#9ca3af;font-size:13px">Chargement...</p>
        </div>
        <div style="border-top:1px solid #e5e7eb;padding-top:12px">
            <input type="text" id="newColName" placeholder="Creer une nouvelle collection..." style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;box-sizing:border-box;margin-bottom:8px">
            <button onclick="createAndAdd()" style="width:100%;padding:8px;border:none;border-radius:6px;background:#6366f1;color:#fff;cursor:pointer;font-weight:600;font-size:13px">+ Creer et ajouter</button>
        </div>
        <button onclick="closeCollectionModal()" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;background:#fff;cursor:pointer;margin-top:8px;font-size:13px">Fermer</button>
    </div>
</div>

<script>
const RESTO_ID = <?= (int)$r['id'] ?>;

/* ═══ CHECK-IN ═══ */
function doCheckin() {
    if (!navigator.geolocation) {
        alert('La geolocalisation n\'est pas supportee par votre navigateur');
        return;
    }
    const btn = document.getElementById('checkinBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation...';
    btn.disabled = true;

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const res = await fetch('/api/restaurants/' + RESTO_ID + '/checkin', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude })
                });
                const data = await res.json();
                if (data.success) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Check-in valide !';
                    btn.style.borderColor = '#059669';
                    btn.style.background = '#d1fae5';
                    btn.style.color = '#059669';
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Check-in';
                        btn.style = 'border-color:#10b981;color:#10b981';
                        btn.disabled = false;
                    }, 3000);
                } else {
                    alert(data.error || 'Erreur');
                    btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Check-in';
                    btn.disabled = false;
                }
            } catch (e) {
                alert('Erreur reseau');
                btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Check-in';
                btn.disabled = false;
            }
        },
        () => {
            alert('Impossible d\'obtenir votre position. Activez la geolocalisation.');
            btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Check-in';
            btn.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

/* ═══ RESERVATION ═══ */
function openReservationModal() {
    document.getElementById('reservationModal').style.display = 'flex';
}
function closeReservationModal() {
    document.getElementById('reservationModal').style.display = 'none';
}
async function submitReservation() {
    const data = {
        date: document.getElementById('resDate').value,
        heure: document.getElementById('resHeure').value,
        nb_personnes: parseInt(document.getElementById('resNb').value),
        telephone: document.getElementById('resTel').value,
        message: document.getElementById('resMsg').value,
    };
    if (!data.date) { alert('Veuillez choisir une date'); return; }
    try {
        const res = await fetch('/api/restaurants/' + RESTO_ID + '/reservation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            closeReservationModal();
            alert(result.message);
        } else {
            alert(result.errors ? result.errors.join('\n') : (result.error || 'Erreur'));
        }
    } catch (e) { alert('Erreur reseau'); }
}

/* ═══ COLLECTIONS ═══ */
function openCollectionModal() {
    document.getElementById('collectionModal').style.display = 'flex';
    loadCollections();
}
function closeCollectionModal() {
    document.getElementById('collectionModal').style.display = 'none';
}
async function loadCollections() {
    try {
        const res = await fetch('/api/my-collections?restaurant_id=' + RESTO_ID);
        const data = await res.json();
        const target = document.getElementById('colListTarget');
        if (!data.success || !data.collections.length) {
            target.innerHTML = '<p style="color:#9ca3af;font-size:13px">Aucune collection. Creez-en une ci-dessous.</p>';
            return;
        }
        target.innerHTML = data.collections.map(c => `
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px;border-radius:6px;cursor:pointer;${c.contains_restaurant ? 'background:#d1fae5' : 'background:#f9fafb'};margin-bottom:4px"
                 onclick="addToCollection(${c.id}, this)">
                <span style="font-size:14px;font-weight:600">${c.title} <span style="font-size:11px;color:#6b7280">(${c.count})</span></span>
                <span style="font-size:12px">${c.contains_restaurant ? '<i class="fas fa-check" style="color:#059669"></i>' : '<i class="fas fa-plus" style="color:#6366f1"></i>'}</span>
            </div>
        `).join('');
    } catch (e) {
        document.getElementById('colListTarget').innerHTML = '<p style="color:#ef4444;font-size:13px">Connectez-vous pour utiliser les collections</p>';
    }
}
async function addToCollection(colId, el) {
    try {
        const res = await fetch('/api/collections/' + colId + '/add', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ restaurant_id: RESTO_ID })
        });
        const data = await res.json();
        if (data.success) {
            el.style.background = '#d1fae5';
            el.querySelector('span:last-child').innerHTML = '<i class="fas fa-check" style="color:#059669"></i>';
        }
    } catch (e) { alert('Erreur'); }
}
async function createAndAdd() {
    const name = document.getElementById('newColName').value.trim();
    if (!name) return;
    try {
        const res = await fetch('/api/collections', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: name, is_public: 1 })
        });
        const data = await res.json();
        if (data.success) {
            await fetch('/api/collections/' + data.collection.id + '/add', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ restaurant_id: RESTO_ID })
            });
            document.getElementById('newColName').value = '';
            loadCollections();
        } else {
            alert(data.error || 'Erreur');
        }
    } catch (e) { alert('Erreur'); }
}

// ═══════════════════════════════════════════════════════════
// QUICK TIPS
// ═══════════════════════════════════════════════════════════
const tipInput = document.getElementById('tipInput');
if (tipInput) {
    tipInput.addEventListener('input', () => {
        document.getElementById('tipCharCount').textContent = tipInput.value.length;
    });
}

async function submitTip() {
    const msg = document.getElementById('tipInput')?.value?.trim();
    if (!msg || msg.length < 5) { alert('Le conseil doit faire au moins 5 caracteres'); return; }
    try {
        const tipCsrf = document.querySelector('meta[name="csrf-token"]');
        const tipHeaders = {'Content-Type': 'application/json'};
        if (tipCsrf) tipHeaders['X-CSRF-TOKEN'] = tipCsrf.content;
        const res = await fetch('/api/restaurants/<?= $r['id'] ?>/tip', {
            method: 'POST', headers: tipHeaders,
            body: JSON.stringify({message: msg})
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('tipInput').value = '';
            document.getElementById('tipCharCount').textContent = '0';
            // Add tip to list visually
            const list = document.getElementById('tipsList');
            if (list) {
                // Remove "aucun conseil" placeholder if present
                const placeholder = list.querySelector('p');
                if (placeholder && placeholder.textContent.includes('Aucun conseil')) placeholder.remove();
                const userName = '<?= addslashes($_SESSION['user']['prenom'] ?? 'Vous') ?>';
                const initial = userName.charAt(0).toUpperCase();
                const div = document.createElement('div');
                div.style.cssText = 'display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #fef3c7;animation:fadeIn 0.3s ease';
                div.innerHTML = '<div style="width:32px;height:32px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:12px;color:#92400e;font-weight:700">' + initial + '</div><div style="flex:1"><p style="margin:0;font-size:13px;color:#1f2937">"' + msg.replace(/</g,'&lt;') + '"</p><div style="font-size:11px;color:#9ca3af;margin-top:4px">' + userName + ' &middot; A l\'instant</div></div>';
                list.prepend(div);
            }
        } else { alert(data.error || 'Erreur'); }
    } catch (e) { alert('Erreur reseau'); }
}

// ═══════════════════════════════════════════════════════════
// REVIEW SEARCH
// ═══════════════════════════════════════════════════════════
let searchTimeout;
document.getElementById('reviewSearchInput')?.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (typeof reviewsManager !== 'undefined' && reviewsManager) {
            reviewsManager.currentSearchQuery = this.value.trim();
            reviewsManager.currentOffset = 0;
            reviewsManager.reloadReviews();
        }
    }, 400);
});
</script>

<?php include __DIR__ . '/../partials/_compare_widget.php'; ?>

<script>
// ── Concierge Dwell Time Tracking (STORY-016) ──
(function() {
    var params = new URLSearchParams(window.location.search);
    var recId = params.get('rec_id');
    if (!recId || params.get('ref') !== 'concierge') return;
    recId = parseInt(recId, 10);
    if (!recId) return;

    var startTime = Date.now();
    var sent = false;

    function sendDwell() {
        if (sent) return;
        sent = true;
        var seconds = Math.round((Date.now() - startTime) / 1000);
        if (seconds < 1) return;
        navigator.sendBeacon('/api/concierge/dwell', JSON.stringify({ rec_id: recId, seconds: seconds }));
    }

    window.addEventListener('beforeunload', sendDwell);
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden') sendDwell();
    });
})();
</script>

</body>
</html>