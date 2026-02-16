<?php
/**
 * Dashboard Proprietaire - LeBonResto
 * 5 onglets : Vue d'ensemble | Commandes | Avis | Analytique | Communication
 */
$currentUser = $_SESSION['user'] ?? null;
$csrfToken = $_SESSION['csrf_token'] ?? '';
$eventLabels = [
    'view' => ['label' => 'Vue de page', 'icon' => 'fa-eye', 'color' => '#3b82f6'],
    'click_phone' => ['label' => 'Appels', 'icon' => 'fa-phone', 'color' => '#10b981'],
    'click_directions' => ['label' => 'Itineraires', 'icon' => 'fa-directions', 'color' => '#3b82f6'],
    'click_website' => ['label' => 'Site web', 'icon' => 'fa-globe', 'color' => '#8b5cf6'],
    'click_menu' => ['label' => 'Menu', 'icon' => 'fa-utensils', 'color' => '#f59e0b'],
    'click_booking' => ['label' => 'Reservation', 'icon' => 'fa-calendar-check', 'color' => '#ec4899'],
    'wishlist_add' => ['label' => 'Favoris', 'icon' => 'fa-heart', 'color' => '#ef4444'],
    'share' => ['label' => 'Partages', 'icon' => 'fa-share-alt', 'color' => '#06b6d4'],
    'gallery_open' => ['label' => 'Galerie', 'icon' => 'fa-images', 'color' => '#84cc16'],
    'review_form_open' => ['label' => 'Form. avis', 'icon' => 'fa-edit', 'color' => '#f97316'],
    'review_submitted' => ['label' => 'Avis soumis', 'icon' => 'fa-star', 'color' => '#eab308'],
];
$orderStatusLabels = [
    'pending' => ['label' => 'En attente', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
    'confirmed' => ['label' => 'Confirmee', 'color' => '#3b82f6', 'bg' => '#dbeafe'],
    'preparing' => ['label' => 'En prep.', 'color' => '#8b5cf6', 'bg' => '#ede9fe'],
    'ready' => ['label' => 'Prete', 'color' => '#10b981', 'bg' => '#d1fae5'],
    'delivering' => ['label' => 'Livraison', 'color' => '#06b6d4', 'bg' => '#cffafe'],
    'delivered' => ['label' => 'Livree', 'color' => '#059669', 'bg' => '#d1fae5'],
    'cancelled' => ['label' => 'Annulee', 'color' => '#ef4444', 'bg' => '#fee2e2'],
    'refused' => ['label' => 'Refusee', 'color' => '#dc2626', 'bg' => '#fee2e2'],
];
$resStatusLabels = [
    'pending' => ['label' => 'En attente', 'color' => '#f59e0b', 'bg' => '#fef3c7'],
    'accepted' => ['label' => 'Acceptee', 'color' => '#10b981', 'bg' => '#d1fae5'],
    'refused' => ['label' => 'Refusee', 'color' => '#ef4444', 'bg' => '#fee2e2'],
    'cancelled' => ['label' => 'Annulee', 'color' => '#6b7280', 'bg' => '#f3f4f6'],
];
$postTypeLabels = [
    'news' => ['label' => 'Actualite', 'icon' => 'fa-newspaper', 'color' => '#3b82f6'],
    'promo' => ['label' => 'Promotion', 'icon' => 'fa-tags', 'color' => '#ef4444'],
    'event' => ['label' => 'Evenement', 'icon' => 'fa-calendar-star', 'color' => '#8b5cf6'],
    'photo' => ['label' => 'Photo', 'icon' => 'fa-camera', 'color' => '#10b981'],
    'menu_update' => ['label' => 'Menu', 'icon' => 'fa-utensils', 'color' => '#f59e0b'],
];
$awardLabels = [
    'travelers_choice' => ['label' => "Travelers' Choice", 'icon' => 'fa-trophy', 'color' => '#f59e0b'],
    'top_city' => ['label' => 'Top Ville', 'icon' => 'fa-city', 'color' => '#3b82f6'],
    'best_cuisine' => ['label' => 'Meilleure Cuisine', 'icon' => 'fa-utensils', 'color' => '#ef4444'],
    'trending' => ['label' => 'Tendance', 'icon' => 'fa-fire', 'color' => '#f97316'],
    'newcomer' => ['label' => 'Nouveau', 'icon' => 'fa-seedling', 'color' => '#10b981'],
];
$notifIcons = [
    'new_review' => 'fa-star', 'review_approved' => 'fa-check-circle', 'review_rejected' => 'fa-times-circle',
    'owner_response' => 'fa-reply', 'claim_approved' => 'fa-store', 'badge_earned' => 'fa-medal',
    'qa_answer' => 'fa-comment', 'new_question' => 'fa-question-circle', 'order_placed' => 'fa-shopping-bag',
    'new_message' => 'fa-envelope',
];
$dayNames = ['', 'Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
?>

<style>
.db-page { max-width: 1400px; margin: 0 auto; padding: 24px 20px 60px; }

/* Hero */
.db-hero { background: linear-gradient(135deg, #00635a 0%, #004d40 60%, #00352e 100%); border-radius: 20px; padding: 32px 28px; margin-bottom: 24px; color: #fff; position: relative; overflow: hidden; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.db-hero::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.04); border-radius: 50%; }
.db-hero-left h1 { font-size: 26px; font-weight: 800; margin: 0 0 6px; letter-spacing: -0.5px; }
.db-hero-left p { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; }
.db-hero-actions { display: none; }

/* Tabs */
.db-tabs { display: flex; align-items: center; justify-content: space-between; background: #f3f4f6; border-radius: 14px; padding: 5px; margin-bottom: 24px; position: sticky; top: 64px; z-index: 50; box-shadow: 0 2px 8px rgba(0,0,0,0); transition: box-shadow 0.2s; gap: 8px; }
.db-tabs.stuck { box-shadow: 0 4px 16px rgba(0,0,0,0.1); border-radius: 0 0 14px 14px; }
.db-tabs-left { display: flex; gap: 4px; overflow-x: auto; -webkit-overflow-scrolling: touch; flex: 1; min-width: 0; }
.db-period-pills { display: flex; gap: 3px; flex-shrink: 0; background: rgba(0,0,0,0.06); border-radius: 9px; padding: 3px; }
.db-period-btn { padding: 6px 14px; border: none; background: none; border-radius: 7px; color: #6b7280; font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap; }
.db-period-btn:hover { color: #374151; background: rgba(255,255,255,0.5); }
.db-period-btn.active { background: #00635a; color: #fff; box-shadow: 0 2px 6px rgba(0,99,90,0.25); }
.db-tab-btn { flex-shrink: 0; padding: 10px 18px; font-size: 13px; font-weight: 600; color: #6b7280; border: none; background: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; gap: 7px; transition: all 0.2s; white-space: nowrap; }
.db-tab-btn:hover { color: #374151; background: rgba(255,255,255,0.5); }
.db-tab-btn.active { color: #00635a; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.db-tab-badge { background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 10px; min-width: 16px; text-align: center; }
.db-tab-panel { display: none; }
.db-tab-panel.active { display: block; }

/* Cards */
.db-card { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.db-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.db-card-title { font-size: 15px; font-weight: 700; color: #111827; display: flex; align-items: center; gap: 8px; margin: 0; }
.db-card-link { font-size: 12px; color: #00635a; text-decoration: none; font-weight: 600; }
.db-card-link:hover { text-decoration: underline; }

/* Action Center */
.db-actions-bar { display: flex; gap: 10px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px; }
.db-action-item { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 12px; text-decoration: none; color: inherit; cursor: pointer; border: none; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.06); flex-shrink: 0; transition: all 0.2s; font-size: 13px; font-weight: 600; font-family: inherit; }
.db-action-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.db-action-item.urgent { background: #fef3c7; border: 1px solid #fde68a; }
.db-action-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; color: #fff; flex-shrink: 0; }
.db-action-count { font-size: 18px; font-weight: 800; color: #111827; }

/* Stats */
.db-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 24px; }
.db-stat { background: #fff; border-radius: 14px; padding: 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.db-stat-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.db-stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; color: #fff; }
.db-stat-trend { font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
.db-stat-trend.up { color: #059669; background: #d1fae5; }
.db-stat-trend.down { color: #dc2626; background: #fee2e2; }
.db-stat-trend.stable { color: #6b7280; background: #f3f4f6; }
.db-stat-value { font-size: 24px; font-weight: 800; color: #111827; letter-spacing: -0.5px; }
.db-stat-label { font-size: 12px; color: #6b7280; margin-top: 2px; }

/* Grids */
.db-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.db-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px; }

/* Restaurant cards */
.db-resto-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 16px; }
.db-resto-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; display: flex; transition: all 0.2s; }
.db-resto-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); transform: translateY(-2px); }
.db-resto-photo { width: 100px; min-height: 100px; background: linear-gradient(135deg,#e5e7eb,#d1d5db); flex-shrink: 0; overflow: hidden; }
.db-resto-photo img { width: 100%; height: 100%; object-fit: cover; }
.db-resto-info { padding: 14px; flex: 1; min-width: 0; }
.db-resto-name { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.db-resto-meta { font-size: 12px; color: #6b7280; margin-bottom: 8px; }
.db-resto-meta i { margin-right: 3px; }
.db-resto-badges { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
.db-resto-badge { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; }
.db-resto-badge.validated { background: #d1fae5; color: #059669; }
.db-resto-badge.pending { background: #fef3c7; color: #d97706; }
.db-resto-badge.rejected { background: #fee2e2; color: #dc2626; }
.db-resto-actions { display: flex; gap: 6px; }
.db-resto-btn { padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px; }
.db-resto-btn.edit { background: #f0fdf4; color: #00635a; }
.db-resto-btn.edit:hover { background: #dcfce7; }
.db-resto-btn.view { background: #eff6ff; color: #3b82f6; }
.db-resto-btn.view:hover { background: #dbeafe; }

/* Reviews */
.db-review { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
.db-review:last-child { border-bottom: none; }
.db-review-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg,#00635a,#10b981); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; }
.db-review-body { flex: 1; min-width: 0; }
.db-review-top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
.db-review-name { font-size: 13px; font-weight: 600; }
.db-review-stars { color: #f59e0b; font-size: 11px; }
.db-review-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
.db-review-badge.answered { background: #d1fae5; color: #059669; }
.db-review-badge.pending { background: #fef3c7; color: #d97706; }
.db-review-text { font-size: 13px; color: #4b5563; line-height: 1.5; }
.db-review-time { font-size: 11px; color: #9ca3af; margin-top: 4px; }

/* Score box */
.db-score-box { background: linear-gradient(135deg,#00635a,#004d40); border-radius: 14px; padding: 24px; color: #fff; text-align: center; margin-bottom: 20px; }
.db-score-big { font-size: 48px; font-weight: 900; letter-spacing: -1px; }
.db-score-stars { font-size: 20px; color: #fbbf24; margin: 6px 0; }
.db-score-count { font-size: 13px; color: rgba(255,255,255,0.7); }

/* Distribution bars */
.db-dist-row { display: flex; align-items: center; gap: 10px; padding: 4px 0; }
.db-dist-label { font-size: 13px; font-weight: 600; min-width: 30px; text-align: right; color: #374151; }
.db-dist-bar { flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.db-dist-fill { height: 100%; border-radius: 4px; transition: width 0.6s; }
.db-dist-count { font-size: 12px; color: #6b7280; min-width: 30px; }

/* Orders table */
.db-table { width: 100%; border-collapse: collapse; }
.db-table th { padding: 10px 12px; text-align: left; font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f3f4f6; }
.db-table td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #f9fafb; }
.db-table tr:hover td { background: #fafafa; }
.db-status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }

/* Heatmap */
.db-heatmap { display: grid; grid-template-columns: 44px repeat(24, 1fr); gap: 2px; margin: 16px 0; }
.db-hm-label { font-size: 11px; font-weight: 600; color: #6b7280; display: flex; align-items: center; padding: 2px 4px; }
.db-hm-header { font-size: 9px; color: #9ca3af; text-align: center; padding: 2px 0; }
.db-hm-cell { aspect-ratio: 1; border-radius: 3px; cursor: pointer; position: relative; min-width: 0; }
.db-hm-cell:hover { outline: 2px solid #00635a; z-index: 1; }
.db-hm-tooltip { display: none; position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); background: #111827; color: #fff; font-size: 11px; padding: 4px 8px; border-radius: 6px; white-space: nowrap; z-index: 10; pointer-events: none; }
.db-hm-cell:hover .db-hm-tooltip { display: block; }

/* Funnel */
.db-funnel { margin: 20px 0; }
.db-funnel-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.db-funnel-label { font-size: 13px; font-weight: 600; min-width: 110px; color: #374151; }
.db-funnel-bar-wrap { flex: 1; height: 32px; background: #f3f4f6; border-radius: 8px; overflow: hidden; position: relative; }
.db-funnel-bar { height: 100%; border-radius: 8px; transition: width 1s ease; display: flex; align-items: center; justify-content: flex-end; padding-right: 10px; color: #fff; font-size: 12px; font-weight: 700; min-width: 40px; }
.db-funnel-count { font-size: 13px; font-weight: 700; min-width: 50px; text-align: right; color: #111827; }

/* Respond inline */
.db-respond-form { margin-top: 10px; padding: 14px; background: #f9fafb; border-radius: 10px; border: 1px solid #e5e7eb; }
.db-respond-templates { display: flex; flex-direction: column; gap: 6px; margin-bottom: 10px; }
.db-respond-tpl { padding: 8px 12px; background: #f5f3ff; border: 1px solid #e9e5ff; border-radius: 8px; font-size: 12px; color: #6d28d9; cursor: pointer; text-align: left; transition: all 0.2s; }
.db-respond-tpl:hover { background: #ede9fe; }
.db-respond-textarea { width: 100%; min-height: 80px; padding: 10px; border: 1.5px solid #e5e7eb; border-radius: 8px; font-size: 13px; font-family: inherit; resize: vertical; }
.db-respond-textarea:focus { border-color: #00635a; outline: none; }
.db-respond-actions { display: flex; gap: 8px; margin-top: 8px; }
.db-respond-btn { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; transition: all 0.2s; }
.db-respond-btn.primary { background: #00635a; color: #fff; }
.db-respond-btn.primary:hover { background: #004d40; }
.db-respond-btn.secondary { background: #f3f4f6; color: #6b7280; }
.db-respond-btn:disabled { opacity: 0.5; }

/* Message items */
.db-msg-item { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; text-decoration: none; color: inherit; }
.db-msg-item:last-child { border-bottom: none; }
.db-msg-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0; overflow: hidden; }
.db-msg-avatar img { width: 100%; height: 100%; object-fit: cover; }
.db-msg-body { flex: 1; min-width: 0; }
.db-msg-name { font-size: 13px; font-weight: 600; }
.db-msg-preview { font-size: 12px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.db-msg-time { font-size: 11px; color: #9ca3af; white-space: nowrap; }
.db-msg-unread { width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; flex-shrink: 0; align-self: center; }

/* Post items */
.db-post-item { padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
.db-post-item:last-child { border-bottom: none; }
.db-post-type { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 6px; }
.db-post-title { font-size: 13px; font-weight: 600; color: #111827; margin: 4px 0 2px; }
.db-post-meta { font-size: 11px; color: #9ca3af; }

/* Notif items */
.db-notif-item { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f9fafb; }
.db-notif-item:last-child { border-bottom: none; }
.db-notif-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; flex-shrink: 0; }
.db-notif-text { flex: 1; font-size: 12px; color: #374151; }
.db-notif-time { font-size: 11px; color: #9ca3af; }

/* Devices */
.db-devices { display: flex; gap: 16px; justify-content: center; }
.db-device { text-align: center; padding: 16px 12px; flex: 1; }
.db-device i { font-size: 28px; margin-bottom: 6px; }
.db-device-val { font-size: 22px; font-weight: 800; color: #111827; }
.db-device-label { font-size: 12px; color: #6b7280; }

/* Sources */
.db-source { display: flex; align-items: center; gap: 10px; padding: 8px 0; }
.db-source-name { flex: 1; font-size: 13px; font-weight: 500; min-width: 80px; }
.db-source-bar { flex: 2; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; }
.db-source-fill { height: 100%; background: #00635a; border-radius: 3px; transition: width 0.5s; }
.db-source-count { font-size: 13px; font-weight: 600; min-width: 40px; text-align: right; }

/* Events */
.db-event { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f9fafb; }
.db-event:last-child { border-bottom: none; }
.db-event-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; flex-shrink: 0; }
.db-event-name { flex: 1; font-size: 13px; font-weight: 500; }
.db-event-count { font-size: 15px; font-weight: 700; color: #111827; }

/* Awards */
.db-awards { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.db-award { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }

/* Empty states */
.db-empty { text-align: center; padding: 40px 20px; }
.db-empty i { font-size: 48px; color: #d1d5db; margin-bottom: 12px; display: block; }
.db-empty h2 { font-size: 20px; font-weight: 700; color: #374151; margin-bottom: 8px; }
.db-empty p { font-size: 14px; color: #6b7280; margin-bottom: 20px; }
.db-empty-btn { display: inline-flex; align-items: center; gap: 6px; padding: 12px 24px; background: #00635a; color: #fff; text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 14px; }
.db-empty-btn:hover { background: #004d46; }
.db-empty-small { text-align: center; padding: 24px; color: #9ca3af; font-size: 13px; }

/* Chart */
.db-chart-wrap { height: 260px; position: relative; }
.db-sparkline-wrap { height: 100px; }

/* Separator */
.db-separator { border: none; border-top: 2px solid #f3f4f6; margin: 24px 0; }

/* Top items */
.db-top-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f9fafb; }
.db-top-item:last-child { border-bottom: none; }
.db-top-rank { width: 24px; height: 24px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #6b7280; flex-shrink: 0; }
.db-top-name { flex: 1; font-size: 13px; font-weight: 600; }
.db-top-qty { font-size: 13px; font-weight: 700; color: #111827; }
.db-top-rev { font-size: 12px; color: #6b7280; }

@media (max-width: 900px) {
    .db-grid-2, .db-grid-3 { grid-template-columns: 1fr; }
    .db-stats { grid-template-columns: repeat(2, 1fr); }
    .db-hero { padding: 24px 20px; }
    .db-page { padding: 16px 12px 40px; }
    .db-resto-cards { grid-template-columns: 1fr; }
    .db-heatmap { overflow-x: auto; min-width: 600px; }
    .db-table th:nth-child(n+5), .db-table td:nth-child(n+5) { display: none; }
}
@media (max-width: 600px) {
    .db-stats { grid-template-columns: 1fr 1fr; }
    .db-tabs { flex-wrap: wrap; gap: 4px; padding: 4px; }
    .db-tabs-left { gap: 2px; }
    .db-tab-btn { padding: 8px 12px; font-size: 12px; }
    .db-period-pills { width: 100%; justify-content: center; margin-top: 2px; }
}
</style>

<div class="db-page">

<?php if (empty($hasRestaurants)): ?>
    <div class="db-card">
        <div class="db-empty">
            <i class="fas fa-store"></i>
            <h2>Bienvenue sur votre Dashboard</h2>
            <p>Ajoutez votre premier restaurant pour suivre vos performances</p>
            <a href="/add-restaurant" class="db-empty-btn"><i class="fas fa-plus"></i> Ajouter un restaurant</a>
        </div>
    </div>
<?php else: ?>

<!-- Hero -->
<div class="db-hero">
    <div class="db-hero-left">
        <h1><i class="fas fa-chart-line"></i> Tableau de bord</h1>
        <p>Bonjour <?= htmlspecialchars($currentUser['prenom'] ?? '') ?>, voici vos performances</p>
    </div>
</div>

<!-- Tabs -->
<?php
$pendingOrders = (int)($ordersStats['pending'] ?? 0);
$pendingRes = (int)($reservationsStats['pending'] ?? 0);
$toRespondCount = count($reviewsToRespond ?? []);
$unreadMsgCount = (int)($unreadMessages ?? 0);
$qaCount = count($unansweredQA ?? []);
?>
<div class="db-tabs">
    <div class="db-tabs-left">
        <button class="db-tab-btn active" data-tab="overview"><i class="fas fa-home"></i> Vue d'ensemble</button>
        <button class="db-tab-btn" data-tab="orders"><i class="fas fa-shopping-bag"></i> Commandes<?php if ($pendingOrders > 0): ?> <span class="db-tab-badge"><?= $pendingOrders ?></span><?php endif; ?></button>
        <button class="db-tab-btn" data-tab="reviews"><i class="fas fa-star"></i> Avis<?php if ($toRespondCount > 0): ?> <span class="db-tab-badge"><?= $toRespondCount ?></span><?php endif; ?></button>
        <button class="db-tab-btn" data-tab="analytics"><i class="fas fa-chart-bar"></i> Analytique</button>
        <button class="db-tab-btn" data-tab="comms"><i class="fas fa-comments"></i> Communication<?php if ($unreadMsgCount > 0): ?> <span class="db-tab-badge"><?= $unreadMsgCount ?></span><?php endif; ?></button>
    </div>
    <div class="db-period-pills">
        <a href="/dashboard?period=7" class="db-period-btn <?= ($period ?? 30) == 7 ? 'active' : '' ?>">7j</a>
        <a href="/dashboard?period=30" class="db-period-btn <?= ($period ?? 30) == 30 ? 'active' : '' ?>">30j</a>
        <a href="/dashboard?period=90" class="db-period-btn <?= ($period ?? 30) == 90 ? 'active' : '' ?>">90j</a>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TAB 1: VUE D'ENSEMBLE                                          -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="db-tab-panel active" id="tab-overview">

    <!-- Action Center -->
    <?php if ($pendingOrders + $pendingRes + $toRespondCount + $unreadMsgCount + $qaCount > 0): ?>
    <div class="db-actions-bar">
        <?php if ($pendingOrders > 0): ?>
        <button class="db-action-item urgent" onclick="switchTab('orders')">
            <div class="db-action-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-shopping-bag"></i></div>
            <div><div class="db-action-count"><?= $pendingOrders ?></div>Commande(s) en attente</div>
        </button>
        <?php endif; ?>
        <?php if ($pendingRes > 0): ?>
        <button class="db-action-item urgent" onclick="switchTab('orders')">
            <div class="db-action-icon" style="background:linear-gradient(135deg,#ec4899,#db2777)"><i class="fas fa-calendar-check"></i></div>
            <div><div class="db-action-count"><?= $pendingRes ?></div>Reservation(s) en attente</div>
        </button>
        <?php endif; ?>
        <?php if ($toRespondCount > 0): ?>
        <button class="db-action-item urgent" onclick="switchTab('reviews')">
            <div class="db-action-icon" style="background:linear-gradient(135deg,#f59e0b,#eab308)"><i class="fas fa-reply"></i></div>
            <div><div class="db-action-count"><?= $toRespondCount ?></div>Avis a repondre</div>
        </button>
        <?php endif; ?>
        <?php if ($unreadMsgCount > 0): ?>
        <button class="db-action-item" onclick="switchTab('comms')">
            <div class="db-action-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)"><i class="fas fa-envelope"></i></div>
            <div><div class="db-action-count"><?= $unreadMsgCount ?></div>Message(s) non lu(s)</div>
        </button>
        <?php endif; ?>
        <?php if ($qaCount > 0): ?>
        <button class="db-action-item" onclick="switchTab('comms')">
            <div class="db-action-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)"><i class="fas fa-question-circle"></i></div>
            <div><div class="db-action-count"><?= $qaCount ?></div>Question(s) Q&A</div>
        </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="db-stats">
        <div class="db-stat">
            <div class="db-stat-top">
                <div class="db-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)"><i class="fas fa-eye"></i></div>
                <?php if (!empty($globalStats['views_trend'])): ?>
                <span class="db-stat-trend <?= $globalStats['views_trend']['direction'] ?>">
                    <i class="fas fa-arrow-<?= $globalStats['views_trend']['direction'] == 'up' ? 'up' : ($globalStats['views_trend']['direction'] == 'down' ? 'down' : 'right') ?>"></i>
                    <?= $globalStats['views_trend']['value'] ?>%
                </span>
                <?php endif; ?>
            </div>
            <div class="db-stat-value"><?= number_format($globalStats['total_views'] ?? 0) ?></div>
            <div class="db-stat-label">Vues totales</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top">
                <div class="db-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-users"></i></div>
            </div>
            <div class="db-stat-value"><?= number_format($globalStats['unique_visitors'] ?? 0) ?></div>
            <div class="db-stat-label">Visiteurs uniques</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top">
                <div class="db-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-receipt"></i></div>
            </div>
            <div class="db-stat-value"><?= number_format($ordersStats['revenue'] ?? 0) ?> <small style="font-size:13px;color:#6b7280">DA</small></div>
            <div class="db-stat-label">Chiffre d'affaires</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top">
                <div class="db-stat-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2)"><i class="fas fa-shopping-bag"></i></div>
            </div>
            <div class="db-stat-value"><?= number_format($ordersStats['today'] ?? 0) ?></div>
            <div class="db-stat-label">Commandes aujourd'hui</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top">
                <div class="db-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#eab308)"><i class="fas fa-star"></i></div>
            </div>
            <div class="db-stat-value"><?= $avgRating ?? 0 ?> <small style="font-size:13px;color:#6b7280">/ 5</small></div>
            <div class="db-stat-label"><?= number_format($totalReviewsCount ?? 0) ?> avis</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top">
                <div class="db-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)"><i class="fas fa-percentage"></i></div>
            </div>
            <div class="db-stat-value"><?= $globalStats['conversion_rate'] ?? 0 ?>%</div>
            <div class="db-stat-label">Taux de conversion</div>
        </div>
    </div>

    <!-- Mini Sparkline -->
    <div class="db-card" style="margin-bottom:24px">
        <div class="db-card-header">
            <h3 class="db-card-title"><i class="fas fa-chart-area"></i> Trafic — <?= (int)($period ?? 30) ?> derniers jours</h3>
        </div>
        <div class="db-sparkline-wrap">
            <canvas id="sparklineChart"></canvas>
        </div>
    </div>

    <!-- Restaurants -->
    <div class="db-card-header" style="margin-bottom:12px">
        <h3 class="db-card-title"><i class="fas fa-store"></i> Mes restaurants</h3>
        <a href="/add-restaurant" class="db-card-link"><i class="fas fa-plus"></i> Ajouter</a>
    </div>
    <div class="db-resto-cards">
        <?php foreach ($myRestaurants as $r):
            $photo = $photosMap[(int)$r['id']] ?? '';
        ?>
        <div class="db-resto-card">
            <div class="db-resto-photo">
                <?php if ($photo): ?>
                <img src="/<?= htmlspecialchars(ltrim($photo, '/')) ?>" alt="" loading="lazy">
                <?php else: ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:28px"><i class="fas fa-store"></i></div>
                <?php endif; ?>
            </div>
            <div class="db-resto-info">
                <div class="db-resto-name"><?= htmlspecialchars($r['nom']) ?></div>
                <div class="db-resto-meta"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($r['ville'] ?? '') ?> &middot; <i class="fas fa-star" style="color:#f59e0b"></i> <?= $r['note_moyenne'] ?? '-' ?> (<?= $r['nb_avis'] ?? 0 ?>)</div>
                <div class="db-resto-badges">
                    <span class="db-resto-badge <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                    <span style="font-size:11px;color:#6b7280"><i class="fas fa-eye"></i> <?= number_format($r['vues_total'] ?? 0) ?></span>
                </div>
                <div class="db-resto-actions">
                    <a href="/owner/restaurant/<?= $r['id'] ?>/edit" class="db-resto-btn edit"><i class="fas fa-cog"></i> Gerer</a>
                    <a href="/restaurant/<?= $r['id'] ?>" class="db-resto-btn view"><i class="fas fa-external-link-alt"></i> Voir</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TAB 2: COMMANDES & RESERVATIONS                                -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="db-tab-panel" id="tab-orders">

    <!-- Order Stats -->
    <div class="db-stats">
        <div class="db-stat">
            <div class="db-stat-top"><div class="db-stat-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)"><i class="fas fa-shopping-bag"></i></div></div>
            <div class="db-stat-value"><?= number_format($ordersStats['total'] ?? 0) ?></div>
            <div class="db-stat-label">Commandes totales</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top"><div class="db-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-coins"></i></div></div>
            <div class="db-stat-value"><?= number_format($ordersStats['revenue'] ?? 0) ?> <small style="font-size:13px;color:#6b7280">DA</small></div>
            <div class="db-stat-label">Chiffre d'affaires</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top"><div class="db-stat-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)"><i class="fas fa-receipt"></i></div></div>
            <div class="db-stat-value"><?= number_format($avgOrderValue ?? 0) ?> <small style="font-size:13px;color:#6b7280">DA</small></div>
            <div class="db-stat-label">Panier moyen</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top"><div class="db-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-clock"></i></div></div>
            <div class="db-stat-value"><?= number_format($pendingOrders) ?></div>
            <div class="db-stat-label">En attente</div>
        </div>
    </div>

    <!-- Orders Charts + Top Items -->
    <div class="db-grid-2">
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-chart-bar"></i> Commandes par jour</h3></div>
            <div class="db-chart-wrap"><canvas id="ordersBarChart"></canvas></div>
        </div>
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-chart-pie"></i> Repartition</h3></div>
            <div class="db-chart-wrap"><canvas id="ordersDonutChart"></canvas></div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="db-card" style="margin-bottom:24px">
        <div class="db-card-header">
            <h3 class="db-card-title"><i class="fas fa-list"></i> Dernieres commandes</h3>
        </div>
        <?php if (!empty($recentOrders)): ?>
        <div style="overflow-x:auto">
        <table class="db-table">
            <thead><tr><th>#</th><th>Client</th><th>Restaurant</th><th>Statut</th><th>Type</th><th>Montant</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($recentOrders as $ord):
                $os = $orderStatusLabels[$ord['status']] ?? ['label' => $ord['status'], 'color' => '#6b7280', 'bg' => '#f3f4f6'];
                $dt = new DateTime($ord['created_at']);
            ?>
            <tr>
                <td><strong><?= $ord['id'] ?></strong></td>
                <td><?= htmlspecialchars($ord['client_name'] ?? 'Client') ?></td>
                <td><?= htmlspecialchars($ord['resto_nom'] ?? '') ?></td>
                <td><span class="db-status-badge" style="background:<?= $os['bg'] ?>;color:<?= $os['color'] ?>"><?= $os['label'] ?></span></td>
                <td><?= ($ord['order_type'] ?? '') === 'delivery' ? '<i class="fas fa-truck"></i> Livraison' : '<i class="fas fa-store"></i> Emporter' ?></td>
                <td><strong><?= number_format($ord['grand_total'] ?? 0) ?> DA</strong></td>
                <td><?= $dt->format('d/m H:i') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
        <p class="db-empty-small">Aucune commande pour cette periode</p>
        <?php endif; ?>
    </div>

    <!-- Top Items -->
    <?php if (!empty($topItems)): ?>
    <div class="db-card" style="margin-bottom:24px">
        <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-fire"></i> Top plats commandes</h3></div>
        <?php foreach ($topItems as $i => $item): ?>
        <div class="db-top-item">
            <div class="db-top-rank"><?= $i + 1 ?></div>
            <div class="db-top-name"><?= htmlspecialchars($item['item_name']) ?></div>
            <div class="db-top-qty"><?= (int)$item['total_qty'] ?>x</div>
            <div class="db-top-rev"><?= number_format($item['total_revenue'] ?? 0) ?> DA</div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Reservations Section -->
    <hr class="db-separator">
    <div class="db-card-header" style="margin-bottom:16px"><h3 class="db-card-title"><i class="fas fa-calendar-check"></i> Reservations</h3></div>
    <div class="db-stats" style="margin-bottom:20px">
        <div class="db-stat">
            <div class="db-stat-top"><div class="db-stat-icon" style="background:linear-gradient(135deg,#ec4899,#db2777)"><i class="fas fa-calendar"></i></div></div>
            <div class="db-stat-value"><?= number_format($reservationsStats['total'] ?? 0) ?></div>
            <div class="db-stat-label">Total</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top"><div class="db-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-hourglass-half"></i></div></div>
            <div class="db-stat-value"><?= number_format($reservationsStats['pending'] ?? 0) ?></div>
            <div class="db-stat-label">En attente</div>
        </div>
        <div class="db-stat">
            <div class="db-stat-top"><div class="db-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check-circle"></i></div></div>
            <div class="db-stat-value"><?= number_format($reservationsStats['accepted'] ?? 0) ?></div>
            <div class="db-stat-label">Acceptees</div>
        </div>
    </div>

    <?php if (!empty($recentReservations)): ?>
    <div class="db-card">
        <div style="overflow-x:auto">
        <table class="db-table">
            <thead><tr><th>Client</th><th>Restaurant</th><th>Date</th><th>Heure</th><th>Personnes</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($recentReservations as $res):
                $rs = $resStatusLabels[$res['status']] ?? ['label' => $res['status'], 'color' => '#6b7280', 'bg' => '#f3f4f6'];
            ?>
            <tr>
                <td><?= htmlspecialchars(($res['prenom'] ?? '') . ' ' . ($res['user_nom'] ?? '')) ?></td>
                <td><?= htmlspecialchars($res['resto_nom'] ?? '') ?></td>
                <td><?= date('d/m/Y', strtotime($res['date_souhaitee'])) ?></td>
                <td><?= htmlspecialchars($res['heure'] ?? '') ?></td>
                <td><i class="fas fa-users"></i> <?= (int)$res['nb_personnes'] ?></td>
                <td><span class="db-status-badge" style="background:<?= $rs['bg'] ?>;color:<?= $rs['color'] ?>"><?= $rs['label'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <?php else: ?>
    <div class="db-card"><p class="db-empty-small">Aucune reservation pour cette periode</p></div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TAB 3: AVIS & REPUTATION                                       -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="db-tab-panel" id="tab-reviews">

    <div class="db-grid-2">
        <!-- Score -->
        <div>
            <div class="db-score-box">
                <div class="db-score-big"><?= $avgRating ?? 0 ?></div>
                <div class="db-score-stars">
                    <?= str_repeat('<i class="fas fa-star"></i>', (int)round($avgRating ?? 0)) . str_repeat('<i class="far fa-star"></i>', 5 - (int)round($avgRating ?? 0)) ?>
                </div>
                <div class="db-score-count"><?= number_format($totalReviewsCount ?? 0) ?> avis au total</div>
            </div>

            <!-- Distribution -->
            <div class="db-card">
                <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-chart-bar"></i> Distribution</h3></div>
                <?php for ($s = 5; $s >= 1; $s--):
                    $cnt = $reviewsDist[$s] ?? 0;
                    $pct = $totalReviewsCount > 0 ? round(($cnt / $totalReviewsCount) * 100) : 0;
                    $color = $s >= 4 ? '#10b981' : ($s === 3 ? '#f59e0b' : '#ef4444');
                ?>
                <div class="db-dist-row">
                    <div class="db-dist-label"><?= $s ?> <i class="fas fa-star" style="color:#f59e0b;font-size:10px"></i></div>
                    <div class="db-dist-bar"><div class="db-dist-fill" style="width:<?= $pct ?>%;background:<?= $color ?>"></div></div>
                    <div class="db-dist-count"><?= $cnt ?></div>
                </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Metrics -->
        <div>
            <!-- Response rate -->
            <div class="db-card" style="margin-bottom:16px">
                <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-reply-all"></i> Taux de reponse</h3></div>
                <div style="text-align:center;padding:10px 0">
                    <div style="font-size:42px;font-weight:900;color:<?= $responseRate >= 80 ? '#059669' : ($responseRate >= 50 ? '#f59e0b' : '#ef4444') ?>"><?= $responseRate ?>%</div>
                    <div style="font-size:13px;color:#6b7280"><?= $totalResponded ?? 0 ?> / <?= $totalReviewsCount ?? 0 ?> avis repondus</div>
                    <div style="height:6px;background:#e5e7eb;border-radius:3px;margin-top:10px;overflow:hidden">
                        <div style="height:100%;width:<?= $responseRate ?>%;background:<?= $responseRate >= 80 ? '#10b981' : ($responseRate >= 50 ? '#f59e0b' : '#ef4444') ?>;border-radius:3px;transition:width 0.6s"></div>
                    </div>
                </div>
            </div>

            <!-- Sentiment -->
            <div class="db-card" style="margin-bottom:16px">
                <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-smile-beam"></i> Sentiment</h3></div>
                <?php
                $pos = ($reviewsDist[4] ?? 0) + ($reviewsDist[5] ?? 0);
                $neu = $reviewsDist[3] ?? 0;
                $neg = ($reviewsDist[1] ?? 0) + ($reviewsDist[2] ?? 0);
                $posPct = $totalReviewsCount > 0 ? round(($pos / $totalReviewsCount) * 100) : 0;
                $neuPct = $totalReviewsCount > 0 ? round(($neu / $totalReviewsCount) * 100) : 0;
                $negPct = $totalReviewsCount > 0 ? round(($neg / $totalReviewsCount) * 100) : 0;
                ?>
                <div class="db-dist-row"><div class="db-dist-label" style="color:#10b981"><i class="fas fa-smile"></i></div><div class="db-dist-bar"><div class="db-dist-fill" style="width:<?= $posPct ?>%;background:#10b981"></div></div><div class="db-dist-count"><?= $posPct ?>%</div></div>
                <div class="db-dist-row"><div class="db-dist-label" style="color:#f59e0b"><i class="fas fa-meh"></i></div><div class="db-dist-bar"><div class="db-dist-fill" style="width:<?= $neuPct ?>%;background:#f59e0b"></div></div><div class="db-dist-count"><?= $neuPct ?>%</div></div>
                <div class="db-dist-row"><div class="db-dist-label" style="color:#ef4444"><i class="fas fa-frown"></i></div><div class="db-dist-bar"><div class="db-dist-fill" style="width:<?= $negPct ?>%;background:#ef4444"></div></div><div class="db-dist-count"><?= $negPct ?>%</div></div>
            </div>

            <!-- Awards -->
            <?php if (!empty($awards)): ?>
            <div class="db-card">
                <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-trophy"></i> Distinctions</h3></div>
                <div class="db-awards">
                    <?php foreach ($awards as $aw):
                        $ai = $awardLabels[$aw['award_type']] ?? ['label' => $aw['award_type'], 'icon' => 'fa-award', 'color' => '#6b7280'];
                    ?>
                    <div class="db-award" style="background:<?= $ai['color'] ?>15;color:<?= $ai['color'] ?>">
                        <i class="fas <?= $ai['icon'] ?>"></i> <?= $ai['label'] ?> <?= $aw['award_year'] ?> — <?= htmlspecialchars($aw['resto_nom']) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reviews to respond -->
    <div class="db-card" style="margin-top:20px">
        <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-reply"></i> Avis recents — repondre</h3></div>
        <?php if (!empty($recentReviews)): ?>
            <?php foreach ($recentReviews as $rv):
                $stars = str_repeat('<i class="fas fa-star"></i>', (int)$rv['note_globale']) . str_repeat('<i class="far fa-star"></i>', 5 - (int)$rv['note_globale']);
                $initials = strtoupper(mb_substr($rv['prenom'] ?? '', 0, 1));
                $dt = new DateTime($rv['created_at']);
                $hasResponse = !empty($rv['owner_response']);
            ?>
            <div class="db-review" id="review-row-<?= $rv['id'] ?>">
                <div class="db-review-avatar"><?= $initials ?></div>
                <div class="db-review-body">
                    <div class="db-review-top">
                        <span class="db-review-name"><?= htmlspecialchars($rv['prenom'] . ' ' . ($rv['user_nom'] ?? '')) ?></span>
                        <span class="db-review-stars"><?= $stars ?></span>
                        <?php if ($hasResponse): ?>
                            <span class="db-review-badge answered">Repondu</span>
                        <?php else: ?>
                            <span class="db-review-badge pending" id="badge-<?= $rv['id'] ?>">A repondre</span>
                        <?php endif; ?>
                    </div>
                    <div class="db-review-text"><?= htmlspecialchars(mb_substr($rv['message'] ?? '', 0, 150)) ?></div>
                    <div class="db-review-time">
                        <i class="fas fa-store" style="font-size:10px"></i> <?= htmlspecialchars($rv['resto_nom']) ?> &middot; <?= $dt->format('d/m/Y') ?>
                    </div>
                    <?php if (!$hasResponse): ?>
                    <button class="db-respond-btn primary" style="margin-top:8px;font-size:12px;padding:6px 12px" onclick="toggleRespondForm(<?= $rv['id'] ?>, <?= (int)$rv['note_globale'] ?>)">
                        <i class="fas fa-reply"></i> Repondre
                    </button>
                    <div id="respond-form-<?= $rv['id'] ?>" style="display:none"></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <p class="db-empty-small">Aucun avis recent</p>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TAB 4: ANALYTIQUE                                               -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="db-tab-panel" id="tab-analytics">

    <!-- Traffic Chart -->
    <div class="db-card" style="margin-bottom:24px">
        <div class="db-card-header">
            <h3 class="db-card-title"><i class="fas fa-chart-area"></i> Evolution du trafic — <?= (int)($period ?? 30) ?>j</h3>
        </div>
        <div class="db-chart-wrap"><canvas id="trafficChart"></canvas></div>
    </div>

    <!-- Heatmap -->
    <div class="db-card" style="margin-bottom:24px">
        <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-th"></i> Heures de pointe</h3></div>
        <div style="overflow-x:auto">
        <div class="db-heatmap">
            <!-- Header row -->
            <div class="db-hm-label"></div>
            <?php for ($h = 0; $h < 24; $h++): ?>
            <div class="db-hm-header"><?= $h ?>h</div>
            <?php endfor; ?>
            <!-- Data rows (1=Dim, 2=Lun, ..., 7=Sam in MySQL DAYOFWEEK) -->
            <?php
            $dowOrder = [2,3,4,5,6,7,1]; // Lun→Sam→Dim
            foreach ($dowOrder as $dow):
            ?>
            <div class="db-hm-label"><?= $dayNames[$dow] ?></div>
            <?php for ($h = 0; $h < 24; $h++):
                $cnt = $peakHours[$dow][$h] ?? 0;
                $opacity = $peakMax > 0 ? round($cnt / $peakMax, 2) : 0;
                $bgColor = $cnt > 0 ? "rgba(0,99,90,{$opacity})" : '#f3f4f6';
            ?>
            <div class="db-hm-cell" style="background:<?= $bgColor ?>">
                <div class="db-hm-tooltip"><?= $dayNames[$dow] ?> <?= $h ?>h — <?= $cnt ?> vue(s)</div>
            </div>
            <?php endfor; ?>
            <?php endforeach; ?>
        </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin-top:10px;font-size:11px;color:#6b7280">
            <span>Peu</span>
            <div style="display:flex;gap:2px">
                <div style="width:14px;height:14px;border-radius:2px;background:#f3f4f6"></div>
                <div style="width:14px;height:14px;border-radius:2px;background:rgba(0,99,90,0.2)"></div>
                <div style="width:14px;height:14px;border-radius:2px;background:rgba(0,99,90,0.5)"></div>
                <div style="width:14px;height:14px;border-radius:2px;background:rgba(0,99,90,0.8)"></div>
                <div style="width:14px;height:14px;border-radius:2px;background:rgba(0,99,90,1)"></div>
            </div>
            <span>Beaucoup</span>
        </div>
    </div>

    <!-- Devices + Sources + Events -->
    <div class="db-grid-3">
        <!-- Devices -->
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-mobile-alt"></i> Appareils</h3></div>
            <?php
            $devMap = [];
            foreach ($deviceStats ?? [] as $ds) {
                $devMap[$ds['device'] ?? 'unknown'] = (int)($ds['count'] ?? 0);
            }
            $totalDevices = max(1, array_sum($devMap));
            $mPct = round((($devMap['mobile'] ?? 0) / $totalDevices) * 100);
            $dPct = round((($devMap['desktop'] ?? 0) / $totalDevices) * 100);
            $tPct = 100 - $mPct - $dPct;
            ?>
            <div class="db-devices">
                <div class="db-device"><i class="fas fa-mobile-alt" style="color:#3b82f6"></i><div class="db-device-val"><?= $mPct ?>%</div><div class="db-device-label">Mobile</div></div>
                <div class="db-device"><i class="fas fa-desktop" style="color:#8b5cf6"></i><div class="db-device-val"><?= $dPct ?>%</div><div class="db-device-label">Desktop</div></div>
                <div class="db-device"><i class="fas fa-tablet-alt" style="color:#10b981"></i><div class="db-device-val"><?= $tPct ?>%</div><div class="db-device-label">Tablet</div></div>
            </div>
        </div>

        <!-- Sources -->
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-globe"></i> Sources de trafic</h3></div>
            <?php
            $totalTraffic = max(1, array_sum(array_column($trafficSources ?? [], 'count')));
            if (!empty($trafficSources)):
                foreach (array_slice($trafficSources, 0, 5) as $source):
                    $pct = round(($source['count'] / $totalTraffic) * 100);
            ?>
            <div class="db-source">
                <span class="db-source-name"><?= htmlspecialchars($source['source']) ?></span>
                <div class="db-source-bar"><div class="db-source-fill" style="width:<?= $pct ?>%"></div></div>
                <span class="db-source-count"><?= number_format($source['count']) ?></span>
            </div>
            <?php endforeach; else: ?>
            <p class="db-empty-small">Pas de donnees</p>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-hand-pointer"></i> Actions visiteurs</h3></div>
            <?php if (!empty($topEvents)):
                foreach (array_slice($topEvents, 0, 6) as $event):
                    $info = $eventLabels[$event['event_type']] ?? ['label' => $event['event_type'], 'icon' => 'fa-circle', 'color' => '#6b7280'];
            ?>
            <div class="db-event">
                <div class="db-event-icon" style="background:<?= $info['color'] ?>"><i class="fas <?= $info['icon'] ?>"></i></div>
                <span class="db-event-name"><?= $info['label'] ?></span>
                <span class="db-event-count"><?= number_format($event['count']) ?></span>
            </div>
            <?php endforeach; else: ?>
            <p class="db-empty-small">Pas de donnees</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Conversion Funnel -->
    <div class="db-card">
        <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-filter"></i> Funnel de conversion</h3></div>
        <?php
        $fViews = max(1, $funnelViews ?? 1);
        $iPct = round(($funnelInteractions / $fViews) * 100, 1);
        $cPct = round(($funnelConversions / $fViews) * 100, 1);
        ?>
        <div class="db-funnel">
            <div class="db-funnel-row">
                <div class="db-funnel-label">Vues</div>
                <div class="db-funnel-bar-wrap"><div class="db-funnel-bar" style="width:100%;background:linear-gradient(90deg,#3b82f6,#1d4ed8)"><?= number_format($funnelViews) ?></div></div>
                <div class="db-funnel-count">100%</div>
            </div>
            <div class="db-funnel-row">
                <div class="db-funnel-label">Interactions</div>
                <div class="db-funnel-bar-wrap"><div class="db-funnel-bar" style="width:<?= min(100, $iPct) ?>%;background:linear-gradient(90deg,#f59e0b,#d97706)"><?= number_format($funnelInteractions) ?></div></div>
                <div class="db-funnel-count"><?= $iPct ?>%</div>
            </div>
            <div class="db-funnel-row">
                <div class="db-funnel-label">Conversions</div>
                <div class="db-funnel-bar-wrap"><div class="db-funnel-bar" style="width:<?= min(100, max(3, $cPct)) ?>%;background:linear-gradient(90deg,#10b981,#059669)"><?= number_format($funnelConversions) ?></div></div>
                <div class="db-funnel-count"><?= $cPct ?>%</div>
            </div>
        </div>
        <div style="font-size:12px;color:#6b7280;margin-top:8px"><i class="fas fa-info-circle"></i> Conversions = appels ou commandes</div>
    </div>

    <!-- Weekday + Hourly Charts -->
    <div class="db-grid-2" style="margin-top:24px">
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-calendar-week"></i> Vues par jour de semaine</h3></div>
            <div class="db-chart-wrap"><canvas id="weekdayChart"></canvas></div>
        </div>
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-clock"></i> Vues par heure</h3></div>
            <div class="db-chart-wrap"><canvas id="hourlyChart"></canvas></div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="db-card" style="margin-top:24px">
        <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-stream"></i> Activite recente</h3></div>
        <?php if (!empty($recentActivity)):
            foreach ($recentActivity as $act):
                $actInfo = $eventLabels[$act['event_type']] ?? ['label' => $act['event_type'], 'icon' => 'fa-circle', 'color' => '#6b7280'];
                $actDt = new DateTime($act['created_at']);
                $deviceIcon = ($act['device_type'] ?? '') === 'mobile' ? 'fa-mobile-alt' : (($act['device_type'] ?? '') === 'tablet' ? 'fa-tablet-alt' : 'fa-desktop');
        ?>
        <div class="db-event">
            <div class="db-event-icon" style="background:<?= $actInfo['color'] ?>"><i class="fas <?= $actInfo['icon'] ?>"></i></div>
            <span class="db-event-name"><?= $actInfo['label'] ?> <span style="color:#9ca3af;font-weight:400">— <?= htmlspecialchars($act['resto_nom'] ?? '') ?></span></span>
            <span style="font-size:11px;color:#9ca3af"><i class="fas <?= $deviceIcon ?>"></i></span>
            <span style="font-size:11px;color:#9ca3af;min-width:70px;text-align:right"><?= $actDt->format('d/m H:i') ?></span>
        </div>
        <?php endforeach; else: ?>
        <p class="db-empty-small">Pas d'activite recente</p>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- TAB 5: COMMUNICATION                                            -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div class="db-tab-panel" id="tab-comms">

    <div class="db-grid-2">
        <!-- Messages -->
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title"><i class="fas fa-envelope"></i> Messages recus</h3>
                <a href="/messages" class="db-card-link">Voir tous</a>
            </div>
            <?php if (!empty($recentMessages)): ?>
                <?php foreach ($recentMessages as $msg):
                    $initials = strtoupper(mb_substr($msg['prenom'] ?? '', 0, 1));
                    $dt = new DateTime($msg['created_at']);
                ?>
                <a href="/messages/conversation/<?= (int)$msg['sender_id'] ?>" class="db-msg-item">
                    <?php if (!(int)$msg['is_read']): ?><div class="db-msg-unread"></div><?php endif; ?>
                    <div class="db-msg-avatar">
                        <?php if (!empty($msg['photo_profil'])): ?>
                        <img src="/uploads/avatars/<?= htmlspecialchars($msg['photo_profil']) ?>" alt="">
                        <?php else: ?>
                        <?= $initials ?>
                        <?php endif; ?>
                    </div>
                    <div class="db-msg-body">
                        <div class="db-msg-name"><?= htmlspecialchars(($msg['prenom'] ?? '') . ' ' . ($msg['user_nom'] ?? '')) ?></div>
                        <div class="db-msg-preview"><?= htmlspecialchars(mb_substr($msg['body'] ?? '', 0, 60)) ?></div>
                    </div>
                    <span class="db-msg-time"><?= $dt->format('d/m H:i') ?></span>
                </a>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="db-empty-small"><i class="fas fa-inbox"></i> Aucun message</p>
            <?php endif; ?>
        </div>

        <!-- Q&A -->
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-question-circle"></i> Questions sans reponse</h3></div>
            <?php if (!empty($unansweredQA)): ?>
                <?php foreach ($unansweredQA as $qa):
                    $dt = new DateTime($qa['created_at']);
                ?>
                <div style="padding:10px 0;border-bottom:1px solid #f3f4f6">
                    <div style="font-size:13px;font-weight:600;color:#111827;margin-bottom:4px">"<?= htmlspecialchars(mb_substr($qa['question'], 0, 100)) ?>"</div>
                    <div style="font-size:11px;color:#9ca3af">
                        <i class="fas fa-user"></i> <?= htmlspecialchars($qa['prenom'] ?? '') ?> &middot;
                        <i class="fas fa-store"></i> <?= htmlspecialchars($qa['resto_nom']) ?> &middot;
                        <?= $dt->format('d/m/Y') ?>
                    </div>
                    <a href="/restaurant/<?= $qa['restaurant_id'] ?>#questions" style="font-size:12px;color:#00635a;font-weight:600;text-decoration:none">Repondre →</a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="db-empty-small"><i class="fas fa-check-circle" style="color:#10b981"></i> Toutes les questions ont une reponse !</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="db-grid-2" style="margin-top:20px">
        <!-- Posts -->
        <div class="db-card">
            <div class="db-card-header">
                <h3 class="db-card-title"><i class="fas fa-bullhorn"></i> Derniers posts</h3>
                <?php
                $validatedResto = null;
                foreach ($myRestaurants as $r) { if ($r['status'] === 'validated') { $validatedResto = $r; break; } }
                if ($validatedResto): ?>
                <a href="/restaurant/<?= $validatedResto['id'] ?>/posts" class="db-card-link"><i class="fas fa-plus"></i> Nouveau</a>
                <?php endif; ?>
            </div>
            <?php if (!empty($recentPosts)): ?>
                <?php foreach ($recentPosts as $post):
                    $pt = $postTypeLabels[$post['type']] ?? ['label' => $post['type'], 'icon' => 'fa-file', 'color' => '#6b7280'];
                    $dt = new DateTime($post['created_at']);
                ?>
                <div class="db-post-item">
                    <span class="db-post-type" style="background:<?= $pt['color'] ?>15;color:<?= $pt['color'] ?>"><i class="fas <?= $pt['icon'] ?>"></i> <?= $pt['label'] ?></span>
                    <div class="db-post-title"><?= htmlspecialchars($post['title'] ?: mb_substr($post['content'] ?? '', 0, 60)) ?></div>
                    <div class="db-post-meta"><i class="fas fa-heart"></i> <?= (int)$post['likes_count'] ?> &middot; <?= htmlspecialchars($post['resto_nom']) ?> &middot; <?= $dt->format('d/m/Y') ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="db-empty-small">Aucun post publie</p>
            <?php endif; ?>
        </div>

        <!-- Notifications -->
        <div class="db-card">
            <div class="db-card-header"><h3 class="db-card-title"><i class="fas fa-bell"></i> Notifications recentes</h3></div>
            <?php if (!empty($recentNotifications)): ?>
                <?php foreach ($recentNotifications as $notif):
                    $icon = $notifIcons[$notif['type']] ?? 'fa-bell';
                    $dt = new DateTime($notif['created_at']);
                    $isRead = !empty($notif['read_at']);
                ?>
                <div class="db-notif-item" style="<?= !$isRead ? 'background:#eff6ff;border-radius:6px;padding:8px' : '' ?>">
                    <div class="db-notif-icon" style="background:#00635a"><i class="fas <?= $icon ?>"></i></div>
                    <div class="db-notif-text">
                        <strong><?= htmlspecialchars($notif['title']) ?></strong><br>
                        <?= htmlspecialchars(mb_substr($notif['message'] ?? '', 0, 80)) ?>
                    </div>
                    <span class="db-notif-time"><?= $dt->format('d/m H:i') ?></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <p class="db-empty-small">Aucune notification</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php endif; /* hasRestaurants */ ?>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const chartsInitialized = { overview: false, orders: false, analytics: false };
const fullChartData = <?= json_encode($chartData ?? ['labels'=>[],'views'=>[],'clicks'=>[]]) ?>;
let trafficChartInstance = null;

// ═══════════════════════════════════════════════════════════════
// TAB SWITCHING
// ═══════════════════════════════════════════════════════════════
function switchTab(tab) {
    document.querySelectorAll('.db-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.db-tab-panel').forEach(p => p.classList.remove('active'));
    const btn = document.querySelector(`.db-tab-btn[data-tab="${tab}"]`);
    const panel = document.getElementById('tab-' + tab);
    if (btn) btn.classList.add('active');
    if (panel) panel.classList.add('active');
    localStorage.setItem('dashboard_tab', tab);
    initChartsForTab(tab);
}

document.querySelectorAll('.db-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => switchTab(btn.dataset.tab));
});

// Restore saved tab
const savedTab = localStorage.getItem('dashboard_tab');
if (savedTab && document.getElementById('tab-' + savedTab)) {
    switchTab(savedTab);
} else {
    initChartsForTab('overview');
}

// Sticky tabs shadow
const tabsEl = document.querySelector('.db-tabs');
if (tabsEl) {
    const observer = new IntersectionObserver(
        ([e]) => tabsEl.classList.toggle('stuck', e.intersectionRatio < 1),
        { threshold: [1], rootMargin: '-65px 0px 0px 0px' }
    );
    observer.observe(tabsEl);
}

// ═══════════════════════════════════════════════════════════════
// LAZY CHART INIT
// ═══════════════════════════════════════════════════════════════
function initChartsForTab(tab) {
    if (typeof Chart === 'undefined') return;

    if (tab === 'overview' && !chartsInitialized.overview) {
        chartsInitialized.overview = true;
        initSparkline();
    }
    if (tab === 'orders' && !chartsInitialized.orders) {
        chartsInitialized.orders = true;
        initOrdersCharts();
    }
    if (tab === 'analytics' && !chartsInitialized.analytics) {
        chartsInitialized.analytics = true;
        initTrafficChart();
        initWeekdayChart();
        initHourlyChart();
    }
}

// ═══════════════════════════════════════════════════════════════
// SPARKLINE (Tab 1)
// ═══════════════════════════════════════════════════════════════
function initSparkline() {
    const ctx = document.getElementById('sparklineChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: fullChartData.labels || [],
            datasets: [{
                data: fullChartData.views || [],
                borderColor: '#00635a',
                backgroundColor: 'rgba(0,99,90,0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#00635a',
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false, beginAtZero: true }
            }
        }
    });
}

// ═══════════════════════════════════════════════════════════════
// ORDERS CHARTS (Tab 2)
// ═══════════════════════════════════════════════════════════════
function initOrdersCharts() {
    // Bar chart
    const barCtx = document.getElementById('ordersBarChart');
    if (barCtx) {
        const ocd = <?= json_encode($ordersChartData ?? []) ?>;
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ocd.map(d => d.d?.substring(5) || ''),
                datasets: [{
                    label: 'Commandes',
                    data: ocd.map(d => d.cnt || 0),
                    backgroundColor: 'rgba(0,99,90,0.7)',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    }
    // Donut chart
    const donutCtx = document.getElementById('ordersDonutChart');
    if (donutCtx) {
        const obs = <?= json_encode($ordersByStatus ?? []) ?>;
        const statusColors = {
            pending: '#f59e0b', confirmed: '#3b82f6', preparing: '#8b5cf6',
            ready: '#10b981', delivering: '#06b6d4', delivered: '#059669',
            cancelled: '#ef4444', refused: '#dc2626'
        };
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: obs.map(s => s.status || ''),
                datasets: [{
                    data: obs.map(s => s.cnt || 0),
                    backgroundColor: obs.map(s => statusColors[s.status] || '#6b7280'),
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } }
                },
                cutout: '60%',
            }
        });
    }
}

// ═══════════════════════════════════════════════════════════════
// TRAFFIC CHART (Tab 4)
// ═══════════════════════════════════════════════════════════════
function initTrafficChart() {
    const ctx = document.getElementById('trafficChart');
    if (!ctx) return;
    trafficChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: fullChartData.labels || [],
            datasets: [
                {
                    label: 'Vues',
                    data: fullChartData.views || [],
                    borderColor: '#00635a',
                    backgroundColor: 'rgba(0,99,90,0.08)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 2,
                },
                {
                    label: 'Interactions',
                    data: fullChartData.clicks || [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 2,
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { padding: 16, font: { size: 12 } } } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
            },
            interaction: { mode: 'index', intersect: false }
        }
    });
}

// ═══════════════════════════════════════════════════════════════
// RESPOND INLINE (Tab 3)
// ═══════════════════════════════════════════════════════════════
function toggleRespondForm(reviewId, rating) {
    const container = document.getElementById('respond-form-' + reviewId);
    if (!container) return;

    if (container.style.display === 'block') {
        container.style.display = 'none';
        return;
    }

    const category = rating >= 4 ? 'positive' : (rating === 3 ? 'neutral' : 'negative');

    container.innerHTML = `
        <div class="db-respond-form">
            <div id="tpl-loading-${reviewId}" style="color:#9ca3af;font-size:12px;padding:8px"><i class="fas fa-spinner fa-spin"></i> Chargement des suggestions...</div>
            <div class="db-respond-templates" id="tpl-list-${reviewId}"></div>
            <textarea class="db-respond-textarea" id="respond-text-${reviewId}" placeholder="Redigez votre reponse... (min. 20 caracteres)"></textarea>
            <div class="db-respond-actions">
                <button class="db-respond-btn secondary" onclick="toggleRespondForm(${reviewId}, ${rating})">Annuler</button>
                <button class="db-respond-btn primary" id="respond-btn-${reviewId}" onclick="submitResponse(${reviewId})"><i class="fas fa-paper-plane"></i> Publier</button>
            </div>
        </div>
    `;
    container.style.display = 'block';

    // Load templates
    fetch('/api/response-templates?category=' + category)
        .then(r => r.json())
        .then(data => {
            const loading = document.getElementById('tpl-loading-' + reviewId);
            if (loading) loading.remove();
            if (data.success && data.templates?.length > 0) {
                const list = document.getElementById('tpl-list-' + reviewId);
                if (list) {
                    list.innerHTML = data.templates.map(t =>
                        `<button class="db-respond-tpl" onclick="document.getElementById('respond-text-${reviewId}').value=this.textContent;document.getElementById('respond-text-${reviewId}').focus()">${escapeHtml(t.template_fr)}</button>`
                    ).join('');
                }
            }
        })
        .catch(() => {
            const loading = document.getElementById('tpl-loading-' + reviewId);
            if (loading) loading.remove();
        });
}

async function submitResponse(reviewId) {
    const textarea = document.getElementById('respond-text-' + reviewId);
    const btn = document.getElementById('respond-btn-' + reviewId);
    const text = textarea?.value?.trim();

    if (!text || text.length < 20) {
        alert('Votre reponse doit contenir au moins 20 caracteres.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication...';

    try {
        const res = await fetch('/api/reviews/' + reviewId + '/respond', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ response: text })
        });
        const data = await res.json();
        if (data.success) {
            // Replace form with success
            const container = document.getElementById('respond-form-' + reviewId);
            if (container) container.innerHTML = '<div style="padding:10px;background:#d1fae5;border-radius:8px;color:#059669;font-size:13px;font-weight:600;margin-top:8px"><i class="fas fa-check"></i> Reponse publiee !</div>';
            // Update badge
            const badge = document.getElementById('badge-' + reviewId);
            if (badge) { badge.textContent = 'Repondu'; badge.className = 'db-review-badge answered'; }
            // Hide respond button
            const row = document.getElementById('review-row-' + reviewId);
            const respondBtn = row?.querySelector('.db-respond-btn.primary');
            if (respondBtn) respondBtn.style.display = 'none';
        } else {
            alert(data.error || 'Erreur');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier';
        }
    } catch (e) {
        alert('Erreur reseau');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier';
    }
}

// ═══════════════════════════════════════════════════════════════
// WEEKDAY CHART (Tab 4)
// ═══════════════════════════════════════════════════════════════
function initWeekdayChart() {
    const ctx = document.getElementById('weekdayChart');
    if (!ctx) return;
    const wd = <?= json_encode($weekdayStats ?? ['labels'=>[],'data'=>[]]) ?>;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: wd.labels || [],
            datasets: [{
                label: 'Vues',
                data: wd.data || [],
                backgroundColor: ['#ef4444','#3b82f6','#3b82f6','#3b82f6','#3b82f6','#3b82f6','#f59e0b'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// ═══════════════════════════════════════════════════════════════
// HOURLY CHART (Tab 4)
// ═══════════════════════════════════════════════════════════════
function initHourlyChart() {
    const ctx = document.getElementById('hourlyChart');
    if (!ctx) return;
    const hourly = <?= json_encode($hourlyData ?? array_fill(0, 24, 0)) ?>;
    const labels = [];
    for (let i = 0; i < 24; i++) labels.push(i + 'h');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Vues',
                data: hourly,
                backgroundColor: 'rgba(0,99,90,0.6)',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
</script>
