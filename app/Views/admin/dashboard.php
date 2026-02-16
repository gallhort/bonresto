<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - BonResto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* VARIABLES CSS */
        :root {
            --bg-primary: #ffffff; --bg-secondary: #f5f5f5; --bg-sidebar: #1e293b;
            --text-primary: #1f2937; --text-secondary: #6b7280; --text-sidebar: #e2e8f0;
            --border-color: #e5e7eb; --shadow: 0 2px 8px rgba(0,0,0,0.1); --accent: #3b82f6;
            --sidebar-width: 260px; --sidebar-collapsed: 70px;
        }
        [data-theme="dark"] {
            --bg-primary: #1f2937; --bg-secondary: #111827; --bg-sidebar: #0f172a;
            --text-primary: #f9fafb; --text-secondary: #9ca3af; --text-sidebar: #cbd5e1;
            --border-color: #374151; --shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg-secondary); color: var(--text-primary); transition: background 0.3s, color 0.3s; }
        
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: var(--bg-sidebar); color: var(--text-sidebar); transition: width 0.3s ease; z-index: 1000; overflow-x: hidden; overflow-y: auto; }
        .sidebar.collapsed { width: var(--sidebar-collapsed); }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; }
        .sidebar-logo { font-size: 20px; font-weight: 700; white-space: nowrap; }
        .sidebar-toggle { background: rgba(255,255,255,0.1); border: none; color: var(--text-sidebar); width: 36px; height: 36px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
        .sidebar-toggle:hover { background: rgba(255,255,255,0.2); }
        .sidebar-menu { list-style: none; padding: 20px 10px; }
        .menu-item { margin-bottom: 5px; }
        .menu-link { display: flex; align-items: center; padding: 12px 15px; color: var(--text-sidebar); text-decoration: none; border-radius: 8px; transition: all 0.2s; }
        .menu-link:hover { background: rgba(255,255,255,0.1); }
        .menu-link.active { background: var(--accent); color: white; }
        .menu-icon { width: 20px; text-align: center; margin-right: 15px; font-size: 18px; }
        .menu-text { flex: 1; white-space: nowrap; }
        .menu-badge { background: #ef4444; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: auto; }
        .sidebar.collapsed .menu-text, .sidebar.collapsed .menu-badge, .sidebar.collapsed .sidebar-logo { opacity: 0; display: none; }
        
        .main-content { margin-left: var(--sidebar-width); transition: margin-left 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed); }
        
        .topbar { background: var(--bg-primary); border-bottom: 1px solid var(--border-color); padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .topbar-left h1 { font-size: 24px; color: var(--text-primary); }
        .topbar-right { display: flex; gap: 15px; align-items: center; }
        .theme-toggle, .notifications-btn { background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); width: 40px; height: 40px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.2s; position: relative; }
        .theme-toggle:hover { background: var(--accent); color: white; border-color: var(--accent); }
        .notif-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; width: 20px; height: 20px; border-radius: 50%; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; }
        
        .notifications-dropdown { position: absolute; top: 55px; right: 30px; width: 350px; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: var(--shadow); max-height: 400px; overflow-y: auto; display: none; z-index: 200; }
        .notifications-dropdown.show { display: block; }
        .notif-header { padding: 15px 20px; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-primary); }
        .notif-item { padding: 15px 20px; border-bottom: 1px solid var(--border-color); cursor: pointer; transition: background 0.2s; display: flex; align-items: start; gap: 10px; }
        .notif-item:hover { background: var(--bg-secondary); }
        .notif-item-icon { font-size: 20px; }
        .notif-item-text { color: var(--text-secondary); font-size: 14px; flex: 1; }
        
        .filters-bar { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; align-items: center; }
        .filter-group { display: flex; align-items: center; gap: 10px; }
        .filter-label { font-weight: 600; color: var(--text-primary); font-size: 14px; }
        .filter-select { padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px; cursor: pointer; min-width: 150px; }
        .search-input { padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-primary); color: var(--text-primary); font-size: 14px; min-width: 250px; }
        
        .container { padding: 30px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--bg-primary); padding: 20px; border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); position: relative; overflow: hidden; }
        .stat-card h3 { color: var(--text-secondary); font-size: 14px; margin-bottom: 10px; font-weight: 500; }
        .stat-card .number { font-size: 32px; font-weight: 700; color: var(--text-primary); }
        .stat-card small { color: var(--text-secondary); font-size: 12px; }
        .stat-trend { position: absolute; top: 15px; right: 15px; font-size: 12px; font-weight: 600; padding: 4px 8px; border-radius: 6px; }
        .trend-up { background: #d1fae5; color: #065f46; }
        .trend-down { background: #fee2e2; color: #991b1b; }
        
        .section { background: var(--bg-primary); padding: 25px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 25px; border: 1px solid var(--border-color); }
        .section h2 { margin-bottom: 20px; color: var(--text-primary); font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: var(--bg-secondary); font-weight: 600; color: var(--text-primary); font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { color: var(--text-primary); }
        
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-validated { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-active { background: #dbeafe; color: #1e40af; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .period-filter { background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); }
        .period-filter.btn-success { background: #10b981; color: white; border-color: #10b981; }
        .empty { text-align: center; padding: 50px; color: var(--text-secondary); }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { padding: 15px; }
            .container { padding: 15px; }
            .filters-bar { flex-direction: column; align-items: stretch; }
            .filter-select, .search-input { width: 100%; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><svg width="24" height="24" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#00635a"/><path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M28 12v6c0 3 2 5 4 5v-11" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 23v13" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg> LeBonResto</div>
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item"><a href="/admin/dashboard" class="menu-link active"><i class="menu-icon fas fa-home"></i><span class="menu-text">Dashboard</span><?php if (!empty($notifications)): ?><span class="menu-badge"><?= count($notifications) ?></span><?php endif; ?></a></li>
            <li class="menu-item"><a href="/admin/restaurants" class="menu-link"><i class="menu-icon fas fa-utensils"></i><span class="menu-text">Restaurants</span><?php if (($stats['pending_restaurants'] ?? 0) > 0): ?><span class="menu-badge"><?= $stats['pending_restaurants'] ?></span><?php endif; ?></a></li>
            <li class="menu-item"><a href="/admin/reviews" class="menu-link"><i class="menu-icon fas fa-star"></i><span class="menu-text">Avis</span><?php if (($stats['pending_reviews'] ?? 0) > 0): ?><span class="menu-badge"><?= $stats['pending_reviews'] ?></span><?php endif; ?></a></li>
            <li class="menu-item"><a href="/admin/reviews/ai-stats" class="menu-link"><i class="menu-icon fas fa-brain"></i><span class="menu-text">AI stats</span></a></li>

            <li class="menu-item"><a href="/admin/users" class="menu-link"><i class="menu-icon fas fa-users"></i><span class="menu-text">Utilisateurs</span></a></li>
            <li class="menu-item"><a href="/admin/suggestions" class="menu-link"><i class="menu-icon fas fa-lightbulb"></i><span class="menu-text">Suggestions</span></a></li>
            <li class="menu-item"><a href="/admin/analytics" class="menu-link"><i class="menu-icon fas fa-chart-line"></i><span class="menu-text">Analytics</span></a></li>
            <li class="menu-item"><a href="/admin/contacts" class="menu-link"><i class="menu-icon fas fa-envelope"></i><span class="menu-text">Messages</span></a></li>
            <li class="menu-item"><a href="/admin/moderation-log" class="menu-link"><i class="menu-icon fas fa-clipboard-list"></i><span class="menu-text">Modération</span></a></li>
            <li class="menu-item"><a href="/admin/settings" class="menu-link"><i class="menu-icon fas fa-cog"></i><span class="menu-text">Paramètres</span></a></li>
            <li class="menu-item" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);"><a href="/logout" class="menu-link"><i class="menu-icon fas fa-sign-out-alt"></i><span class="menu-text">Déconnexion</span></a></li>
        </ul>
    </aside>
    
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left"><h1>Dashboard Analytics</h1></div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" title="Changer de thème"><i class="fas fa-moon" id="theme-icon"></i></button>
                <button class="notifications-btn" onclick="toggleNotifications()" title="Notifications"><i class="fas fa-bell"></i><?php if (!empty($notifications)): ?><span class="notif-badge"><?= count($notifications) ?></span><?php endif; ?></button>
            </div>
        </div>
        
        <div class="notifications-dropdown" id="notifications-dropdown">
            <div class="notif-header">Notifications (<?= count($notifications ?? []) ?>)</div>
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notif): ?>
                    <a href="<?= $notif['url'] ?>" class="notif-item">
                        <div class="notif-item-icon"><?= $notif['icon'] ?></div>
                        <div class="notif-item-text"><?= $notif['message'] ?></div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="notif-item"><div class="notif-item-text">Aucune notification</div></div>
            <?php endif; ?>
        </div>
        
        <div class="container">
            <!-- FILTRES AVANCÉS -->
            <div class="filters-bar">
                <div class="filter-group">
                    <label class="filter-label">Ville:</label>
                    <select class="filter-select" id="filter-ville" onchange="applyFilters()">
                        <option value="">Toutes</option>
                        <?php foreach ($availableVilles ?? [] as $ville): ?>
                            <option value="<?= $ville ?>" <?= ($filters['ville'] ?? '') === $ville ? 'selected' : '' ?>><?= $ville ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Cuisine:</label>
                    <select class="filter-select" id="filter-cuisine" onchange="applyFilters()">
                        <option value="">Toutes</option>
                        <?php foreach ($availableCuisines ?? [] as $cuisine): ?>
                            <option value="<?= $cuisine ?>" <?= ($filters['cuisine'] ?? '') === $cuisine ? 'selected' : '' ?>><?= $cuisine ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Statut:</label>
                    <select class="filter-select" id="filter-status" onchange="applyFilters()">
                        <option value="">Tous</option>
                        <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>En attente</option>
                        <option value="validated" <?= ($filters['status'] ?? '') === 'validated' ? 'selected' : '' ?>>Validé</option>
                        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejeté</option>
                    </select>
                </div>
                <input type="text" class="search-input" placeholder="🔍 Rechercher un restaurant..." id="search-input" onkeyup="searchRestaurants()">
                <button class="btn btn-danger" onclick="resetFilters()" style="margin-left: auto;">Réinitialiser</button>
            </div>
            
            <!-- WIDGETS COMPARAISON -->
            <div class="stats">
                <div class="stat-card">
                    <h3>Vues cette semaine</h3>
                    <div class="number"><?= number_format($compareStats['this_week_views'] ?? 0) ?></div>
                    <?php 
                    $change = $compareStats['views_change'] ?? 0;
                    if ($change != 0):
                    ?>
                        <span class="stat-trend <?= $change > 0 ? 'trend-up' : 'trend-down' ?>">
                            <?= $change > 0 ? '↑' : '↓' ?> <?= abs($change) ?>%
                        </span>
                    <?php endif; ?>
                    <small>vs semaine dernière</small>
                </div>
                <div class="stat-card">
                    <h3>Nouveaux restos (30j)</h3>
                    <div class="number"><?= $stats['pending_restaurants'] ?? 0 ?></div>
                    <?php 
                    $change = $compareStats['restos_change'] ?? 0;
                    if ($change != 0):
                    ?>
                        <span class="stat-trend <?= $change > 0 ? 'trend-up' : 'trend-down' ?>">
                            <?= $change > 0 ? '↑' : '↓' ?> <?= abs($change) ?>%
                        </span>
                    <?php endif; ?>
                    <small>vs mois dernier</small>
                </div>
                <div class="stat-card">
                    <h3>Top Contributeur</h3>
                    <div class="number" style="font-size: 20px;">
                        <?php if (!empty($topContributors)): ?>
                            <?= htmlspecialchars($topContributors[0]['prenom']) ?> <?= htmlspecialchars($topContributors[0]['nom']) ?>
                        <?php else: ?>
                            Aucun
                        <?php endif; ?>
                    </div>
                    <small><?= $topContributors[0]['review_count'] ?? 0 ?> avis publiés</small>
                </div>
            </div>
            
        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Restaurants</h3>
                <div class="number"><?= $stats['total_restaurants'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>En attente</h3>
                <div class="number"><?= $stats['pending_restaurants'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Validés</h3>
                <div class="number"><?= $stats['validated_restaurants'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Avis</h3>
                <div class="number"><?= $stats['total_reviews'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Avis en attente</h3>
                <div class="number"><?= $stats['pending_reviews'] ?? 0 ?></div>
            </div>
            <div class="stat-card">
                <h3>Utilisateurs</h3>
                <div class="number"><?= $stats['total_users'] ?? 0 ?></div>
            </div>
        </div>
        
        <!-- Statistiques Analytics -->
        <h2 style="margin: 30px 0 20px 0; color: #333;">📊 Analytics & Performances</h2>
        <div class="stats">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h3 style="color: rgba(255,255,255,0.9);">Vues Totales</h3>
                <div class="number" style="color: white;"><?= number_format($stats['analytics_total_views'] ?? 0) ?></div>
                <small style="opacity: 0.8;">Aujourd'hui: <?= $stats['analytics_views_today'] ?? 0 ?></small>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                <h3 style="color: rgba(255,255,255,0.9);">Visiteurs Uniques</h3>
                <div class="number" style="color: white;"><?= number_format($stats['analytics_unique_visitors'] ?? 0) ?></div>
                <small style="opacity: 0.8;">7 derniers jours: <?= $stats['analytics_views_7_days'] ?? 0 ?></small>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                <h3 style="color: rgba(255,255,255,0.9);">📞 Clics Téléphone</h3>
                <div class="number" style="color: white;"><?= number_format($stats['analytics_clicks_phone'] ?? 0) ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                <h3 style="color: rgba(255,255,255,0.9);">🗺️ Clics Itinéraire</h3>
                <div class="number" style="color: white;"><?= number_format($stats['analytics_clicks_directions'] ?? 0) ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
                <h3 style="color: rgba(255,255,255,0.9);">🌐 Clics Site Web</h3>
                <div class="number" style="color: white;"><?= number_format($stats['analytics_clicks_website'] ?? 0) ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white;">
                <h3 style="color: rgba(255,255,255,0.9);">❤️ Favoris Ajoutés</h3>
                <div class="number" style="color: white;"><?= number_format($stats['analytics_wishlist_adds'] ?? 0) ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #333;">
                <h3 style="color: #666;">📸 Galeries Ouvertes</h3>
                <div class="number"><?= number_format($stats['analytics_gallery_opens'] ?? 0) ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #333;">
                <h3 style="color: #666;">📤 Partages</h3>
                <div class="number"><?= number_format($stats['analytics_shares'] ?? 0) ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333;">
                <h3 style="color: #666;">🔥 Taux Engagement</h3>
                <div class="number"><?= $stats['analytics_engagement_rate'] ?? 0 ?>%</div>
                <small style="opacity: 0.7;">Clics / Vues</small>
            </div>
        </div>
        
        <!-- Restaurants en attente -->
        <div class="section">
            <h2>Restaurants en attente de validation</h2>
            
            <?php if (empty($pendingRestaurants)): ?>
                <div class="empty">Aucun restaurant en attente</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Ville</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingRestaurants as $restaurant): ?>
                            <tr>
                                <td><?= htmlspecialchars($restaurant['nom']) ?></td>
                                <td><?= htmlspecialchars($restaurant['ville'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($restaurant['type_cuisine'] ?? 'N/A') ?></td>
                                <td><?= date('d/m/Y', strtotime($restaurant['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-success" onclick="validateRestaurant(<?= $restaurant['id'] ?>)">Valider</button>
                                    <button class="btn btn-danger" onclick="rejectRestaurant(<?= $restaurant['id'] ?>)">Rejeter</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Avis en attente -->
        <div class="section">
            <h2>Avis en attente de modération</h2>
            
            <?php if (empty($pendingReviews)): ?>
                <div class="empty">Aucun avis en attente</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Restaurant</th>
                            <th>Auteur</th>
                            <th>Note</th>
                            <th>Commentaire</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingReviews as $review): ?>
                            <tr>
                                <td><?= htmlspecialchars($review['restaurant_nom'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($review['author_name'] ?? ($review['prenom'] . ' ' . $review['nom'])) ?></td>
                                <td><?= $review['note_globale'] ?>/10</td>
                                <td><?= substr(htmlspecialchars($review['message']), 0, 100) ?>...</td>
                                <td>
                                    <button class="btn btn-success" onclick="approveReview(<?= $review['id'] ?>)">Approuver</button>
                                    <button class="btn btn-danger" onclick="rejectReview(<?= $review['id'] ?>)">Rejeter</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Top 10 Restaurants -->
        <div class="section">
            <h2>🏆 Top 10 Restaurants (vues 30 derniers jours)</h2>
            
            <?php if (empty($topRestaurants)): ?>
                <div class="empty">Aucune donnée analytics disponible</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Restaurant</th>
                            <th>Ville</th>
                            <th>Type</th>
                            <th>Note</th>
                            <th>Vues (30j)</th>
                            <th>Vues (total)</th>
                            <th>📞 Téléphone</th>
                            <th>🗺️ Itinéraire</th>
                            <th>❤️ Favoris</th>
                            <th>🔥 Engagement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topRestaurants as $index => $resto): ?>
                            <tr style="<?= $index < 3 ? 'background: #fff9e6;' : '' ?>">
                                <td style="font-weight: bold; font-size: 18px;">
                                    <?php if ($index === 0): ?>🥇
                                    <?php elseif ($index === 1): ?>🥈
                                    <?php elseif ($index === 2): ?>🥉
                                    <?php else: ?><?= $index + 1 ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($resto['nom']) ?></strong><br>
                                    <small style="color: #999;"><?= $resto['nb_avis'] ?> avis</small>
                                </td>
                                <td><?= htmlspecialchars($resto['ville'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($resto['type_cuisine'] ?? 'N/A') ?></td>
                                <td>
                                    <span style="color: #f59e0b; font-weight: 600;">
                                        ⭐ <?= number_format($resto['note_moyenne'] ?? 0, 1) ?>
                                    </span>
                                </td>
                                <td><strong style="color: #667eea;"><?= number_format($resto['views_30_days'] ?? 0) ?></strong></td>
                                <td><?= number_format($resto['views_total'] ?? 0) ?></td>
                                <td><?= number_format($resto['clicks_phone'] ?? 0) ?></td>
                                <td><?= number_format($resto['clicks_directions'] ?? 0) ?></td>
                                <td><?= number_format($resto['wishlist_adds'] ?? 0) ?></td>
                                <td>
                                    <span style="
                                        padding: 4px 8px; 
                                        border-radius: 4px; 
                                        background: <?= ($resto['engagement_rate'] ?? 0) > 10 ? '#dcfce7' : '#f3f4f6' ?>; 
                                        color: <?= ($resto['engagement_rate'] ?? 0) > 10 ? '#166534' : '#666' ?>;
                                        font-weight: 600;
                                    ">
                                        <?= $resto['engagement_rate'] ?? 0 ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- FILTRES PÉRIODE -->
        <div style="margin: 30px 0; display: flex; gap: 10px; align-items: center;">
            <span style="font-weight: 600;">Période :</span>
            <button class="btn period-filter <?= ($currentPeriod ?? '30') == '7' ? 'btn-success' : '' ?>" data-period="7">7 jours</button>
            <button class="btn period-filter <?= ($currentPeriod ?? '30') == '30' ? 'btn-success' : '' ?>" data-period="30">30 jours</button>
            <button class="btn period-filter <?= ($currentPeriod ?? '30') == 'all' ? 'btn-success' : '' ?>" data-period="all">Tout</button>
            <a href="/admin/export-stats" class="btn" style="margin-left: auto; background: #10b981; color: white; text-decoration: none;">📥 Exporter CSV</a>
        </div>
        
        <!-- GRAPHIQUES -->
        <h2 style="margin: 30px 0 20px 0; color: #333;">📈 Graphiques & Tendances</h2>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 30px;">
            <!-- Évolution des vues -->
            <div class="section">
                <h3 style="margin-bottom: 15px;">Évolution des vues</h3>
                <canvas id="viewsChart" style="max-height: 250px;"></canvas>
            </div>
            
            <!-- Répartition devices -->
            <div class="section">
                <h3 style="margin-bottom: 15px;">Répartition par appareil</h3>
                <canvas id="devicesChart" style="max-height: 250px;"></canvas>
            </div>
            
            <!-- Top cuisines (nombre restos) -->
            <div class="section">
                <h3 style="margin-bottom: 15px;">Top types de cuisine (restaurants)</h3>
                <canvas id="cuisineChart" style="max-height: 250px;"></canvas>
            </div>
            
            <!-- Top cuisines (vues) -->
            <div class="section">
                <h3 style="margin-bottom: 15px;">Top types de cuisine (vues)</h3>
                <canvas id="cuisineViewsChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
        
        <!-- TIMELINE ACTIVITÉ RÉCENTE -->
        <div class="section">
            <h2>⚡ Activité Récente</h2>
            <div style="max-height: 500px; overflow-y: auto;">
                <?php if (empty($recentActivity)): ?>
                    <div class="empty">Aucune activité récente</div>
                <?php else: ?>
                    <?php foreach ($recentActivity as $item): ?>
                        <div style="display: flex; gap: 15px; padding: 12px; border-bottom: 1px solid #e5e7eb; align-items: start;">
                            <div style="font-size: 24px; flex-shrink: 0;"><?= $item['icon'] ?></div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #374151;"><?= $item['title'] ?></div>
                                <div style="color: #6b7280; font-size: 14px; margin-top: 2px;"><?= $item['description'] ?></div>
                                <div style="display: flex; gap: 10px; margin-top: 5px; align-items: center;">
                                    <span style="font-size: 12px; color: #9ca3af;">
                                        <?php
                                            $time = strtotime($item['created_at']);
                                            $diff = time() - $time;
                                            if ($diff < 3600) echo floor($diff/60) . ' min';
                                            elseif ($diff < 86400) echo floor($diff/3600) . ' h';
                                            else echo floor($diff/86400) . ' j';
                                        ?>
                                    </span>
                                    <?php if (isset($item['status'])): ?>
                                        <span class="badge badge-<?= $item['status'] === 'pending' ? 'pending' : ($item['status'] === 'validated' || $item['status'] === 'approved' || $item['status'] === 'active' ? 'validated' : 'rejected') ?>">
                                            <?= ucfirst($item['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Avis Signalés -->
        <?php if (!empty($reportedReviews)): ?>
        <div class="section">
            <h2>🚩 Avis Signalés (<?= count($reportedReviews) ?>)</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Restaurant</th>
                        <th>Auteur</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Signalements</th>
                        <th>Raison</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportedReviews as $report): ?>
                        <tr style="background: #fef2f2;">
                            <td><?= htmlspecialchars($report['restaurant_nom'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars(($report['author_prenom'] ?? '') . ' ' . ($report['author_nom'] ?? '')) ?></td>
                            <td>⭐ <?= $report['note_globale'] ?>/5</td>
                            <td>
                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                    <?= htmlspecialchars(substr($report['commentaire'], 0, 100)) ?>...
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: #fee2e2; color: #991b1b; font-weight: 600;">
                                    <?= $report['report_count'] ?> signalement(s)
                                </span>
                            </td>
                            <td><?= htmlspecialchars($report['reason'] ?? 'N/A') ?></td>
                            <td>
                                <button class="btn btn-success" onclick="approveReview(<?= $report['review_id'] ?>)">Valider</button>
                                <button class="btn btn-danger" onclick="deleteReportedReview(<?= $report['review_id'] ?>)">Supprimer</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function deleteReportedReview(id) {
            if (!confirm('Supprimer définitivement cet avis ?')) return;
            rejectReview(id);
        }
        
        async function validateRestaurant(id) {
            if (!confirm('Valider ce restaurant ?')) return;
            
            try {
                const response = await fetch('/admin/restaurant/validate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }
        
        async function rejectRestaurant(id) {
            const reason = prompt('Raison du rejet (optionnel):');
            if (reason === null) return;
            
            try {
                const response = await fetch('/admin/restaurant/reject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}&reason=${encodeURIComponent(reason)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }
        
        async function approveReview(id) {
            if (!confirm('Approuver cet avis ?')) return;
            
            try {
                const response = await fetch('/admin/review/approve', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }
        
        async function rejectReview(id) {
            if (!confirm('Rejeter cet avis ?')) return;
            
            try {
                const response = await fetch('/admin/review/reject', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            }
        }

        </div>
    </div>
    
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Variables globales pour les charts
        let viewsChart, devicesChart, cuisineChart, cuisineViewsChart;
        
        // Données initiales PHP → JS
        let chartData = <?= json_encode($chartData ?? []) ?>;
        
        // Fonction pour initialiser/mettre à jour les graphiques
        function updateCharts(data) {
            // 1. Graphique évolution des vues
            if (data.views_evolution && data.views_evolution.length > 0) {
                const viewsCtx = document.getElementById('viewsChart').getContext('2d');
                
                // Détruire l'ancien si existe
                if (viewsChart) viewsChart.destroy();
                
                viewsChart = new Chart(viewsCtx, {
                    type: 'line',
                    data: {
                        labels: data.views_evolution.map(d => {
                            const date = new Date(d.date);
                            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
                        }),
                        datasets: [{
                            label: 'Vues',
                            data: data.views_evolution.map(d => d.views),
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
            
            // 2. Graphique devices (doughnut)
            if (data.device_stats && data.device_stats.length > 0) {
                const devicesCtx = document.getElementById('devicesChart').getContext('2d');
                
                if (devicesChart) devicesChart.destroy();
                
                devicesChart = new Chart(devicesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: data.device_stats.map(d => {
                            const names = { 'mobile': '📱 Mobile', 'desktop': '🖥️ Desktop', 'tablet': '📟 Tablet' };
                            return names[d.device_type] || d.device_type;
                        }),
                        datasets: [{
                            data: data.device_stats.map(d => d.count),
                            backgroundColor: ['#667eea', '#f093fb', '#4facfe']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
            
            // 3. Top cuisines (bar horizontal)
            if (data.cuisine_stats && data.cuisine_stats.length > 0) {
                const cuisineCtx = document.getElementById('cuisineChart').getContext('2d');
                
                if (cuisineChart) cuisineChart.destroy();
                
                cuisineChart = new Chart(cuisineCtx, {
                    type: 'bar',
                    data: {
                        labels: data.cuisine_stats.map(d => d.type_cuisine),
                        datasets: [{
                            label: 'Restaurants',
                            data: data.cuisine_stats.map(d => d.count),
                            backgroundColor: '#10b981'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                });
            }
            
            // 4. Cuisines par vues (bar)
            if (data.cuisine_views && data.cuisine_views.length > 0) {
                const cuisineViewsCtx = document.getElementById('cuisineViewsChart').getContext('2d');
                
                if (cuisineViewsChart) cuisineViewsChart.destroy();
                
                cuisineViewsChart = new Chart(cuisineViewsCtx, {
                    type: 'bar',
                    data: {
                        labels: data.cuisine_views.map(d => d.type_cuisine),
                        datasets: [{
                            label: 'Vues totales',
                            data: data.cuisine_views.map(d => d.total_views),
                            backgroundColor: '#f59e0b'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true } }
                    }
                });
            }
        }
        
        // Initialiser les graphiques au chargement
        updateCharts(chartData);
        
        // AJAX pour filtres de période
        document.querySelectorAll('.period-filter').forEach(btn => {
            btn.addEventListener('click', async function() {
                const period = this.dataset.period;
                
                // Update UI des boutons
                document.querySelectorAll('.period-filter').forEach(b => b.classList.remove('btn-success'));
                this.classList.add('btn-success');
                
                try {
                    // Récupérer les nouvelles données
                    const response = await fetch(`/admin/api/chart-data?period=${period}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        // Mettre à jour les graphiques
                        updateCharts(data.chartData);
                    }
                } catch (error) {
                    console.error('Erreur AJAX:', error);
                }
            });
        });
    </script>

    <script>
        // SIDEBAR
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
        
        // DARK MODE
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
        
        // NOTIFICATIONS
        function toggleNotifications() {
            const dropdown = document.getElementById('notifications-dropdown');
            dropdown.classList.toggle('show');
        }
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.notifications-btn') && !e.target.closest('.notifications-dropdown')) {
                document.getElementById('notifications-dropdown').classList.remove('show');
            }
        });
        
        // FILTRES
        function applyFilters() {
            const ville = document.getElementById('filter-ville').value;
            const cuisine = document.getElementById('filter-cuisine').value;
            const status = document.getElementById('filter-status').value;
            const url = new URL(window.location);
            if (ville) url.searchParams.set('ville', ville); else url.searchParams.delete('ville');
            if (cuisine) url.searchParams.set('cuisine', cuisine); else url.searchParams.delete('cuisine');
            if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');
            window.location = url;
        }
        
        function resetFilters() {
            window.location = '/admin/dashboard';
        }
        
        function searchRestaurants() {
            const input = document.getElementById('search-input').value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }
    </script>
</body>
</html>