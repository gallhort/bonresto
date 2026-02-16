<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Avis - BonResto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* VARIABLES CSS */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f5f5f5;
            --bg-sidebar: #1e293b;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-sidebar: #e2e8f0;
            --border-color: #e5e7eb;
            --shadow: 0 2px 8px rgba(0,0,0,0.1);
            --accent: #3b82f6;
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
        }
        
        [data-theme="dark"] {
            --bg-primary: #1f2937;
            --bg-secondary: #111827;
            --bg-sidebar: #0f172a;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --text-sidebar: #cbd5e1;
            --border-color: #374151;
            --shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: background 0.3s, color 0.3s;
        }
        
        /* SIDEBAR */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            color: var(--text-sidebar);
            transition: width 0.3s ease;
            z-index: 1000;
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        .sidebar.collapsed { width: var(--sidebar-collapsed); }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sidebar-logo { font-size: 20px; font-weight: 700; white-space: nowrap; }
        
        .sidebar-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            color: var(--text-sidebar);
            width: 36px;
            height: 36px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        
        .sidebar-toggle:hover { background: rgba(255,255,255,0.2); }
        
        .sidebar-menu { list-style: none; padding: 20px 10px; }
        .menu-item { margin-bottom: 5px; }
        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: var(--text-sidebar);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .menu-link:hover { background: rgba(255,255,255,0.1); }
        .menu-link.active { background: var(--accent); color: white; }
        .menu-icon { width: 20px; text-align: center; margin-right: 15px; font-size: 18px; }
        .menu-text { flex: 1; white-space: nowrap; }
        .menu-badge {
            background: #ef4444;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: auto;
        }
        
        .sidebar.collapsed .menu-text,
        .sidebar.collapsed .menu-badge,
        .sidebar.collapsed .sidebar-logo { opacity: 0; display: none; }
        
        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            min-height: 100vh;
        }
        
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed); }
        
        /* TOPBAR */
        .topbar {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .topbar-left h1 { font-size: 24px; color: var(--text-primary); }
        .topbar-right { display: flex; gap: 15px; align-items: center; }
        .theme-toggle {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            width: 40px;
            height: 40px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.2s;
        }
        
        .theme-toggle:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        /* CONTAINER */
        .container { padding: 30px; }
        
        /* STATS CARDS */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-primary);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .stat-card h3 {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        /* TABS */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 15px;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        
        .tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        
        /* FILTERS BAR */
        .filters-bar {
            background: var(--bg-primary);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            position: relative;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }
        
        .search-input {
            width: 100%;
            padding: 10px 15px 10px 45px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 14px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent);
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 14px;
            cursor: pointer;
            min-width: 150px;
        }
        
        .date-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .date-input {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 14px;
        }
        
        /* BULK ACTIONS */
        .bulk-actions {
            background: var(--bg-primary);
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            display: none;
            align-items: center;
            gap: 15px;
        }
        
        .bulk-actions.show { display: flex; }
        
        .bulk-actions-text {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        /* SECTION */
        .section {
            background: var(--bg-primary);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            border: 1px solid var(--border-color);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section h2 {
            color: var(--text-primary);
            font-size: 18px;
        }
        
        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background: var(--bg-secondary);
            font-weight: 600;
            color: var(--text-primary);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        th input[type="checkbox"] {
            cursor: pointer;
        }
        
        td {
            color: var(--text-primary);
        }
        
        tr:hover {
            background: var(--bg-secondary);
        }
        
        /* BADGES */
        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        
        .rating-stars {
            color: #f59e0b;
            font-size: 16px;
        }
        
        /* BUTTONS */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-primary { background: var(--accent); color: white; }
        .btn-primary:hover { opacity: 0.9; }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
        
        /* PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding: 20px 0;
        }
        
        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            background: var(--bg-primary);
            color: var(--text-primary);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-btn:hover {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        .pagination-btn.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .pagination-info {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.show { display: flex; }
        
        .modal-content {
            background: var(--bg-primary);
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            color: var(--text-primary);
            font-size: 20px;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-secondary);
        }
        
        .modal-body {
            color: var(--text-primary);
        }
        
        .review-detail {
            margin-bottom: 15px;
        }
        
        .review-detail strong {
            color: var(--text-secondary);
            display: block;
            margin-bottom: 5px;
        }
        
        .empty {
            text-align: center;
            padding: 50px;
            color: var(--text-secondary);
        }
        
        /* RESPONSIVE */
        
        .score-badge:hover {
            opacity: 0.8;
            transform: scale(1.05);
        }
        
        .score-badge {
            transition: all 0.2s;
        }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { padding: 15px; }
            .container { padding: 15px; }
            .filters-bar { flex-direction: column; align-items: stretch; }
            .search-box { min-width: 100%; }
            table { font-size: 12px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <h1><i class="fas fa-star"></i> Gestion des Avis</h1>
            </div>
            <div class="topbar-right">
                <div style="position: relative; display: inline-block;">
                    <button class="btn btn-primary" onclick="toggleExportMenu()">
                        <i class="fas fa-download"></i> Exporter <i class="fas fa-caret-down"></i>
                    </button>
                    <div id="export-menu" style="display: none; position: absolute; top: 45px; right: 0; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: var(--shadow); min-width: 150px; z-index: 1000;">
                        <button onclick="exportReviews('csv')" style="display: block; width: 100%; padding: 10px 15px; border: none; background: none; text-align: left; cursor: pointer; color: var(--text-primary);">
                            <i class="fas fa-file-csv"></i> CSV (.csv)
                        </button>
                        <button onclick="exportReviews('xlsx')" style="display: block; width: 100%; padding: 10px 15px; border: none; background: none; text-align: left; cursor: pointer; color: var(--text-primary); border-top: 1px solid var(--border-color);">
                            <i class="fas fa-file-excel"></i> Excel (.xls)
                        </button>
                    </div>
                </div>
                <button class="theme-toggle" onclick="toggleTheme()" title="Changer de thème">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
            </div>
        </div>
        
        <div class="container">
            <!-- STATS RAPIDES -->
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Avis</h3>
                    <div class="number"><?= $totalReviews ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>En attente</h3>
                    <div class="number"><?= $statsCount['pending'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Approuvés</h3>
                    <div class="number"><?= $statsCount['approved'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h3>Note moyenne</h3>
                    <div class="number"><?= number_format($avgRating ?? 0, 1) ?></div>
                </div>
            </div>
            
            <!-- TABS -->
            <div class="tabs">
                <button class="tab <?= ($currentStatus ?? 'pending') === 'pending' ? 'active' : '' ?>" onclick="location.href='/admin/reviews?status=pending'">
                    <i class="fas fa-clock"></i> En attente (<?= $statsCount['pending'] ?? 0 ?>)
                </button>
                <button class="tab <?= ($currentStatus ?? '') === 'approved' ? 'active' : '' ?>" onclick="location.href='/admin/reviews?status=approved'">
                    <i class="fas fa-check"></i> Approuvés (<?= $statsCount['approved'] ?? 0 ?>)
                </button>
                <button class="tab <?= ($currentStatus ?? '') === 'rejected' ? 'active' : '' ?>" onclick="location.href='/admin/reviews?status=rejected'">
                    <i class="fas fa-times"></i> Rejetés (<?= $statsCount['rejected'] ?? 0 ?>)
                </button>
                <button class="tab <?= ($currentStatus ?? '') === 'ai_rejected' ? 'active' : '' ?>" onclick="location.href='/admin/reviews?status=ai_rejected'" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                    <i class="fas fa-robot"></i> Rejetés IA (<?= $statsCount['ai_rejected'] ?? 0 ?>)
                </button>
                <button class="tab <?= ($currentStatus ?? '') === 'all' ? 'active' : '' ?>" onclick="location.href='/admin/reviews?status=all'">
                    <i class="fas fa-list"></i> Tous (<?= $totalReviews ?? 0 ?>)
                </button>
            </div>
            
            <!-- FILTRES & RECHERCHE -->
            <div class="filters-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="search-input" id="search-input" placeholder="Rechercher par restaurant, auteur, commentaire..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                
                <select class="filter-select" id="filter-rating" onchange="applyFilters()">
                    <option value="">Toutes les notes</option>
                    <option value="5" <?= ($filters['rating'] ?? '') == '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5+)</option>
                    <option value="4" <?= ($filters['rating'] ?? '') == '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ (4+)</option>
                    <option value="3" <?= ($filters['rating'] ?? '') == '3' ? 'selected' : '' ?>>⭐⭐⭐ (3+)</option>
                    <option value="2" <?= ($filters['rating'] ?? '') == '2' ? 'selected' : '' ?>>⭐⭐ (2+)</option>
                    <option value="1" <?= ($filters['rating'] ?? '') == '1' ? 'selected' : '' ?>>⭐ (1+)</option>
                </select>
                
                <div class="date-filter">
                    <input type="date" class="date-input" id="date-from" value="<?= $filters['from'] ?? '' ?>" onchange="applyFilters()">
                    <span style="color: var(--text-secondary);">à</span>
                    <input type="date" class="date-input" id="date-to" value="<?= $filters['to'] ?? '' ?>" onchange="applyFilters()">
                </div>
                
                <button class="btn btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
            
            <!-- ACTIONS EN MASSE -->
            <div class="bulk-actions" id="bulk-actions">
                <span class="bulk-actions-text">
                    <span id="selected-count">0</span> avis sélectionné(s)
                </span>
                <button class="btn btn-success btn-sm" onclick="bulkApprove()">
                    <i class="fas fa-check"></i> Approuver
                </button>
                <button class="btn btn-danger btn-sm" onclick="bulkReject()">
                    <i class="fas fa-times"></i> Rejeter
                </button>
                <button class="btn btn-secondary btn-sm" onclick="bulkDelete()">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
            
            <!-- TABLE AVIS -->
            <div class="section">
                <?php if (empty($reviews)): ?>
                    <div class="empty">
                        <i class="fas fa-star" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 15px;"></i>
                        <p>Aucun avis trouvé</p>
                    </div>
                <?php else: ?>
                    <table id="reviews-table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="select-all" onchange="toggleSelectAll()">
                                </th>
                                <th onclick="sortBy('restaurant_nom')" style="cursor: pointer;">
                                    Restaurant 
                                    <?php if ($sortBy === 'restaurant_nom'): ?>
                                        <i class="fas fa-sort-<?= $sortOrder === 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sortBy('prenom')" style="cursor: pointer;">
                                    Auteur
                                    <?php if ($sortBy === 'prenom'): ?>
                                        <i class="fas fa-sort-<?= $sortOrder === 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sortBy('note_globale')" style="cursor: pointer;">
                                    Note
                                    <?php if ($sortBy === 'note_globale'): ?>
                                        <i class="fas fa-sort-<?= $sortOrder === 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th onclick="sortBy('spam_score')" style="cursor: pointer;" title="Score qualité IA">
                                    🤖 Score IA
                                    <?php if ($sortBy === 'spam_score'): ?>
                                        <i class="fas fa-sort-<?= $sortOrder === 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Commentaire</th>
                                <th onclick="sortBy('created_at')" style="cursor: pointer;">
                                    Date
                                    <?php if ($sortBy === 'created_at'): ?>
                                        <i class="fas fa-sort-<?= $sortOrder === 'ASC' ? 'up' : 'down' ?>"></i>
                                    <?php endif; ?>
                                </th>
                                <th>Statut</th>
                                <th width="200">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr data-id="<?= $review['id'] ?>">
                                    <td>
                                        <input type="checkbox" class="review-checkbox" value="<?= $review['id'] ?>" onchange="updateBulkActions()">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($review['restaurant_nom'] ?? 'N/A') ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars(($review['prenom'] ?? '') . ' ' . ($review['nom'] ?? '')) ?></td>
                                    <td>
                                        <span class="rating-stars">
                                            <?= str_repeat('⭐', $review['note_globale']) ?>
                                        </span>
                                        <?= $review['note_globale'] ?>/5
                                    </td>
                                    <td style="text-align: center;">
                                        <?php 
                                        $score = $review['spam_score'] ?? 100;
                                        $badge_color = $score >= 80 ? '#10b981' : ($score >= 50 ? '#f59e0b' : '#ef4444');
                                        $badge_icon = $score >= 80 ? '🟢' : ($score >= 50 ? '🟡' : '🔴');
                                        ?>
                                        <span class="score-badge" 
                                              style="background: <?= $badge_color ?>20; color: <?= $badge_color ?>; padding: 4px 10px; border-radius: 6px; font-weight: 600; cursor: help;"
                                              title="Score qualité: <?= $score ?>/100"
                                              onclick="showSpamDetails(<?= $review['id'] ?>, <?= $score ?>, '<?= htmlspecialchars($review['spam_details'] ?? '{}', ENT_QUOTES) ?>')">
                                            <?= $badge_icon ?> <?= $score ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= htmlspecialchars(substr($review['message'], 0, 80)) ?>...
                                        </div>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $review['status'] ?>">
                                            <?= ucfirst($review['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary btn-sm" onclick="viewReview(<?= $review['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($review['status'] === 'pending'): ?>
                                            <button class="btn btn-success btn-sm" onclick="approveReview(<?= $review['id'] ?>)">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="rejectReview(<?= $review['id'] ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php elseif (($review['ai_rejected'] ?? 0) == 1): ?>
                                            <button class="btn btn-success btn-sm" onclick="overrideAiDecision(<?= $review['id'] ?>)" title="Approuver quand même">
                                                <i class="fas fa-hand-paper"></i> Override
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- PAGINATION -->
                    <div class="pagination">
                        <button class="pagination-btn" onclick="goToPage(1)" <?= $currentPage <= 1 ? 'disabled' : '' ?>>
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button class="pagination-btn" onclick="goToPage(<?= $currentPage - 1 ?>)" <?= $currentPage <= 1 ? 'disabled' : '' ?>>
                            <i class="fas fa-angle-left"></i>
                        </button>
                        
                        <span class="pagination-info">
                            Page <strong><?= $currentPage ?></strong> sur <strong><?= $totalPages ?></strong> 
                            (<?= $totalReviews ?> avis au total)
                        </span>
                        
                        <button class="pagination-btn" onclick="goToPage(<?= $currentPage + 1 ?>)" <?= $currentPage >= $totalPages ? 'disabled' : '' ?>>
                            <i class="fas fa-angle-right"></i>
                        </button>
                        <button class="pagination-btn" onclick="goToPage(<?= $totalPages ?>)" <?= $currentPage >= $totalPages ? 'disabled' : '' ?>>
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                        
                        <select class="filter-select" id="per-page" onchange="changePerPage()" style="margin-left: 20px;">
                            <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10 par page</option>
                            <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25 par page</option>
                            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50 par page</option>
                            <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100 par page</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    

    
    <!-- MODAL DÉTAILS SPAM IA -->
    <div class="modal" id="spam-details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-robot"></i> Analyse IA Anti-Spam</h3>
                <button class="modal-close" onclick="closeSpamModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="font-size: 48px;" id="spam-icon">🟢</div>
                    <div style="font-size: 32px; font-weight: bold; margin: 10px 0;" id="spam-score">95/100</div>
                    <div style="color: var(--text-secondary);" id="spam-label">Avis de qualité</div>
                </div>
                <hr style="border: 1px solid var(--border-color); margin: 20px 0;">
                <div id="spam-penalties">
                    <!-- Pénalités chargées dynamiquement -->
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL DÉTAIL AVIS -->
    <div class="modal" id="review-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-star"></i> Détails de l'avis</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
    
    <script>
        // SIDEBAR & THEME
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }
        
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            localStorage.setItem('theme', newTheme);
        }
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('theme-icon').className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        
        // TRI PAR COLONNES
        function sortBy(column) {
            const url = new URL(window.location);
            const currentSort = url.searchParams.get('sort');
            const currentOrder = url.searchParams.get('order');
            
            // Toggle ASC/DESC si même colonne, sinon DESC par défaut
            let newOrder = 'DESC';
            if (currentSort === column && currentOrder === 'DESC') {
                newOrder = 'ASC';
            }
            
            url.searchParams.set('sort', column);
            url.searchParams.set('order', newOrder);
            window.location = url;
        }
        
        // RECHERCHE (submit au lieu de temps réel pour économiser requêtes)
        let searchTimeout;
        document.getElementById('search-input').addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500); // 500ms debounce
        });
        
        // FILTRES
        function applyFilters() {
            const search = document.getElementById('search-input').value;
            const rating = document.getElementById('filter-rating').value;
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;
            
            const url = new URL(window.location);
            url.searchParams.set('status', '<?= $currentStatus ?? 'pending' ?>');
            if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
            if (rating) url.searchParams.set('rating', rating); else url.searchParams.delete('rating');
            if (dateFrom) url.searchParams.set('from', dateFrom); else url.searchParams.delete('from');
            if (dateTo) url.searchParams.set('to', dateTo); else url.searchParams.delete('to');
            
            window.location = url;
        }
        
        function resetFilters() {
            window.location = '/admin/reviews?status=<?= $currentStatus ?? 'pending' ?>';
        }
        
        // SÉLECTION EN MASSE
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('.review-checkbox');
            const selectAll = document.getElementById('select-all').checked;
            checkboxes.forEach(cb => cb.checked = selectAll);
            updateBulkActions();
        }
        
        function updateBulkActions() {
            const checked = document.querySelectorAll('.review-checkbox:checked');
            const bulkActions = document.getElementById('bulk-actions');
            const count = document.getElementById('selected-count');
            
            count.textContent = checked.length;
            
            if (checked.length > 0) {
                bulkActions.classList.add('show');
            } else {
                bulkActions.classList.remove('show');
            }
        }
        
        // ACTIONS EN MASSE (VRAIES APIs)
        async function bulkApprove() {
            const ids = getSelectedIds();
            if (!confirm(`Approuver ${ids.length} avis ?`)) return;
            
            try {
                const response = await fetch('/admin/reviews/bulk-approve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }
        
        async function bulkReject() {
            const ids = getSelectedIds();
            if (!confirm(`Rejeter ${ids.length} avis ?`)) return;
            
            try {
                const response = await fetch('/admin/reviews/bulk-reject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }
        
        async function bulkDelete() {
            const ids = getSelectedIds();
            if (!confirm(`⚠️ SUPPRIMER définitivement ${ids.length} avis ? Cette action est irréversible !`)) return;
            
            try {
                const response = await fetch('/admin/reviews/bulk-delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }
        
        function getSelectedIds() {
            const checkboxes = document.querySelectorAll('.review-checkbox:checked');
            return Array.from(checkboxes).map(cb => parseInt(cb.value));
        }
        
        // ACTIONS INDIVIDUELLES
        async function approveReview(id, reload = true) {
            try {
                const response = await fetch('/admin/review/approve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (reload) location.reload();
                    return true;
                } else {
                    alert(data.message || 'Erreur');
                    return false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
                return false;
            }
        }
        
        async function rejectReview(id, reload = true) {
            try {
                const response = await fetch('/admin/review/reject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (reload) location.reload();
                    return true;
                } else {
                    alert(data.message || 'Erreur');
                    return false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
                return false;
            }
        }
        
        // MODAL DÉTAILS (avec API)
        async function viewReview(id) {
            document.getElementById('modal-body').innerHTML = '<p style="text-align: center;">Chargement...</p>';
            document.getElementById('review-modal').classList.add('show');
            
            try {
                const response = await fetch(`/admin/api/review-details?id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const review = data.review;
                    const details = `
                        <div class="review-detail">
                            <strong>Restaurant:</strong>
                            ${review.restaurant_nom} (${review.restaurant_ville || 'N/A'})
                        </div>
                        <div class="review-detail">
                            <strong>Auteur:</strong>
                            ${review.prenom} ${review.nom}
                        </div>
                        <div class="review-detail">
                            <strong>Email:</strong>
                            ${review.email}
                        </div>
                        <div class="review-detail">
                            <strong>Note:</strong>
                            ${'⭐'.repeat(review.note_globale)} (${review.note_globale}/5)
                        </div>
                        <div class="review-detail">
                            <strong>Date:</strong>
                            ${new Date(review.created_at).toLocaleString('fr-FR')}
                        </div>
                        <div class="review-detail">
                            <strong>Statut:</strong>
                            <span class="badge badge-${review.status}">${review.status}</span>
                        </div>
                        <div class="review-detail">
                            <strong>Commentaire complet:</strong>
                            <p style="background: var(--bg-secondary); padding: 15px; border-radius: 8px; margin-top: 10px; white-space: pre-wrap;">
                                ${review.message}
                            </p>
                        </div>
                    `;
                    
                    document.getElementById('modal-body').innerHTML = details;
                } else {
                    document.getElementById('modal-body').innerHTML = '<p style="color: red;">Erreur: ' + data.message + '</p>';
                }
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('modal-body').innerHTML = '<p style="color: red;">Erreur réseau</p>';
            }
        }
        
        function closeModal() {
            document.getElementById('review-modal').classList.remove('show');
        }
        
        // PAGINATION (vraies pages)
        function goToPage(page) {
            const url = new URL(window.location);
            url.searchParams.set('page', page);
            window.location = url;
        }
        
        function changePerPage() {
            const perPage = document.getElementById('per-page').value;
            const url = new URL(window.location);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1); // Reset à page 1
            window.location = url;
        }
        
        // EXPORT
        function exportReviews() {
            window.location.href = '/admin/reviews/export?status=<?= $currentStatus ?? 'all' ?>';
        }
        
        // EXPORT avec choix format
        function toggleExportMenu() {
            const menu = document.getElementById('export-menu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
        
        function exportReviews(format) {
            window.location.href = `/admin/reviews/export?status=<?= $currentStatus ?? 'all' ?>&format=${format}`;
            document.getElementById('export-menu').style.display = 'none';
        }
        
        // Fermer menu export au clic extérieur
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.topbar-right > div')) {
                document.getElementById('export-menu').style.display = 'none';
            }
        });
        

        
        // MODAL SPAM DETAILS
        function showSpamDetails(reviewId, score, detailsJson) {
            try {
                const details = JSON.parse(detailsJson || '{}');
                
                // Icône et couleur
                const icon = score >= 80 ? '🟢' : (score >= 50 ? '🟡' : '🔴');
                const label = score >= 80 ? 'Avis de qualité' : (score >= 50 ? 'Qualité moyenne - À vérifier' : 'Spam probable - Rejeté automatiquement');
                
                document.getElementById('spam-icon').textContent = icon;
                document.getElementById('spam-score').textContent = score + '/100';
                document.getElementById('spam-label').textContent = label;
                
                // Pénalités
                const penaltiesDiv = document.getElementById('spam-penalties');
                
                if (!details.penalties || details.penalties.length === 0) {
                    penaltiesDiv.innerHTML = '<p style="color: var(--text-secondary); text-align: center;">✅ Aucun problème détecté</p>';
                } else {
                    let html = '<h4 style="margin-bottom: 15px;">Problèmes détectés :</h4><ul style="list-style: none; padding: 0;">';
                    
                    details.penalties.forEach(penalty => {
                        const icon = penalty.penalty > 15 ? '🔴' : '⚠️';
                        html += `
                            <li style="margin-bottom: 15px; padding: 12px; background: var(--bg-secondary); border-radius: 8px;">
                                <div style="font-weight: 600; margin-bottom: 5px;">
                                    ${icon} ${penalty.rule} <span style="color: #ef4444;">(-${penalty.penalty} pts)</span>
                                </div>
                                <div style="color: var(--text-secondary); font-size: 14px;">
                                    ${penalty.detail}
                                </div>
                            </li>
                        `;
                    });
                    
                    html += '</ul>';
                    html += '<p style="margin-top: 15px; padding: 12px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px;">';
                    html += '<strong>Total pénalités :</strong> ' + (details.total_penalty || 0) + ' points';
                    html += '</p>';
                    
                    penaltiesDiv.innerHTML = html;
                }
                
                document.getElementById('spam-details-modal').classList.add('show');
            } catch (error) {
                console.error('Erreur parsing spam details:', error);
                alert('Erreur lors de l\'affichage des détails');
            }
        }
        
        function closeSpamModal() {
            document.getElementById('spam-details-modal').classList.remove('show');
        }
        
        // OVERRIDE DÉCISION IA
        async function overrideAiDecision(id) {
            if (!confirm('Approuver cet avis malgré le rejet automatique de l\'IA ?')) return;
            
            try {
                const response = await fetch('/admin/review/override-ai', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }
        
        // Fermer modal spam au clic extérieur
        // Fermer modal en cliquant à l'extérieur
        document.getElementById('review-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
