<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Analytics' ?> - LeBonResto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* VARIABLES */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f5f5f5;
            --bg-sidebar: #1e293b;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --text-sidebar: #e2e8f0;
            --border-color: #e5e7eb;
            --shadow: 0 2px 8px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --accent: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 260px;
        }
        
        [data-theme="dark"] {
            --bg-primary: #1f2937;
            --bg-secondary: #111827;
            --bg-sidebar: #0f172a;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
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
        
        /* LAYOUT */
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; transition: margin-left 0.3s; }
        .sidebar.collapsed ~ .main-content { margin-left: 70px; }
        
        /* TOPBAR */
        .topbar {
            background: var(--bg-primary);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        
        .topbar-left { display: flex; align-items: center; gap: 15px; }
        .topbar-left h1 { font-size: 28px; font-weight: 700; color: var(--text-primary); }
        .topbar-left h1 i { color: var(--accent); margin-right: 10px; }
        
        .topbar-right { display: flex; gap: 15px; align-items: center; }
        
        /* FILTRES */
        .period-filters {
            display: flex;
            gap: 8px;
            background: var(--bg-secondary);
            padding: 4px;
            border-radius: 8px;
        }
        
        .period-btn {
            padding: 8px 16px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .period-btn:hover { background: var(--bg-primary); color: var(--text-primary); }
        .period-btn.active { background: var(--accent); color: white; }
        
        .export-btn {
            padding: 10px 20px;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .export-btn:hover { background: #059669; transform: translateY(-2px); }
        
        .theme-toggle {
            width: 44px;
            height: 44px;
            border: 2px solid var(--border-color);
            background: var(--bg-primary);
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        
        .theme-toggle:hover { border-color: var(--accent); color: var(--accent); }
        
        /* CONTAINER */
        .container { padding: 30px; max-width: 1600px; margin: 0 auto; }
        
        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--bg-primary);
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            position: relative;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--success));
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.blue { background: rgba(59, 130, 246, 0.1); color: var(--accent); }
        .stat-icon.green { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-icon.orange { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.1); color: #8b5cf6; }
        
        .stat-trend {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .stat-trend.up { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .stat-trend.down { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .stat-trend.neutral { background: var(--bg-secondary); color: var(--text-secondary); }
        
        .stat-title {
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .stat-subtitle {
            color: var(--text-secondary);
            font-size: 13px;
        }
        
        /* SECTIONS */
        .section {
            background: var(--bg-primary);
            padding: 24px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i { color: var(--accent); }
        
        /* CHARTS */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: var(--bg-primary);
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        /* TABLES */
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 14px;
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
        
        td { color: var(--text-primary); font-size: 14px; }
        
        tr:hover { background: var(--bg-secondary); }
        
        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: rgba(16, 185, 129, 0.1); color: var(--success); }
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: var(--warning); }
        .badge-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
        .badge-info { background: rgba(59, 130, 246, 0.1); color: var(--accent); }
        
        /* AI STATS */
        .ai-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        
        .ai-stat-item {
            padding: 16px;
            background: var(--bg-secondary);
            border-radius: 12px;
            text-align: center;
        }
        
        .ai-stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 4px;
        }
        
        .ai-stat-label {
            font-size: 12px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        
        /* RESPONSIVE */
        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .topbar { flex-direction: column; gap: 15px; }
            .charts-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-left">
                <h1><i class="fas fa-chart-line"></i> Analytics & Rapports</h1>
            </div>
            <div class="topbar-right">
                <!-- Filtres période -->
                <div class="period-filters">
                    <button class="period-btn <?= $period == 7 ? 'active' : '' ?>" onclick="window.location.href='?period=7'">7 jours</button>
                    <button class="period-btn <?= $period == 30 ? 'active' : '' ?>" onclick="window.location.href='?period=30'">30 jours</button>
                    <button class="period-btn <?= $period == 90 ? 'active' : '' ?>" onclick="window.location.href='?period=90'">90 jours</button>
                    <button class="period-btn <?= $period == 365 ? 'active' : '' ?>" onclick="window.location.href='?period=365'">1 an</button>
                </div>
                
                <!-- Export dropdown -->
                <div style="position: relative;">
                    <button class="export-btn" onclick="toggleExportMenu()">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                    <div id="exportMenu" style="display: none; position: absolute; top: 50px; right: 0; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 8px; box-shadow: var(--shadow-lg); min-width: 200px; z-index: 1000;">
                        <a href="/admin/analytics/export?type=reviews&period=<?= $period ?>" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);"><i class="fas fa-star"></i> Avis CSV</a>
                        <a href="/admin/analytics/export?type=restaurants&period=<?= $period ?>" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none; border-bottom: 1px solid var(--border-color);"><i class="fas fa-utensils"></i> Restaurants CSV</a>
                        <a href="/admin/analytics/export?type=users&period=<?= $period ?>" style="display: block; padding: 12px 16px; color: var(--text-primary); text-decoration: none;"><i class="fas fa-users"></i> Utilisateurs CSV</a>
                    </div>
                </div>
                
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
            </div>
        </div>
        
        <div class="container">
            <!-- STATS CARDS -->
            <div class="stats-grid">
                <!-- Restaurants -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon blue">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <?php if (isset($stats['restaurants']['trend'])): ?>
                            <div class="stat-trend <?= $stats['restaurants']['trend']['direction'] ?>">
                                <i class="fas fa-<?= $stats['restaurants']['trend']['direction'] == 'up' ? 'arrow-up' : ($stats['restaurants']['trend']['direction'] == 'down' ? 'arrow-down' : 'minus') ?>"></i>
                                <?= $stats['restaurants']['trend']['value'] ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-title">Restaurants</div>
                    <div class="stat-value"><?= number_format($stats['restaurants']['total']) ?></div>
                    <div class="stat-subtitle">
                        <?= $stats['restaurants']['validated'] ?> validés • 
                        <?= $stats['restaurants']['pending'] ?> en attente • 
                        +<?= $stats['restaurants']['new'] ?> nouveaux
                    </div>
                </div>
                
                <!-- Avis -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon green">
                            <i class="fas fa-star"></i>
                        </div>
                        <?php if (isset($stats['reviews']['trend'])): ?>
                            <div class="stat-trend <?= $stats['reviews']['trend']['direction'] ?>">
                                <i class="fas fa-<?= $stats['reviews']['trend']['direction'] == 'up' ? 'arrow-up' : ($stats['reviews']['trend']['direction'] == 'down' ? 'arrow-down' : 'minus') ?>"></i>
                                <?= $stats['reviews']['trend']['value'] ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-title">Avis</div>
                    <div class="stat-value"><?= number_format($stats['reviews']['total']) ?></div>
                    <div class="stat-subtitle">
                        Note moyenne: <?= $stats['reviews']['avg_rating'] ?>/5 • 
                        <?= $stats['reviews']['approved'] ?> approuvés
                    </div>
                </div>
                
                <!-- Utilisateurs -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon orange">
                            <i class="fas fa-users"></i>
                        </div>
                        <?php if (isset($stats['users']['trend'])): ?>
                            <div class="stat-trend <?= $stats['users']['trend']['direction'] ?>">
                                <i class="fas fa-<?= $stats['users']['trend']['direction'] == 'up' ? 'arrow-up' : ($stats['users']['trend']['direction'] == 'down' ? 'arrow-down' : 'minus') ?>"></i>
                                <?= $stats['users']['trend']['value'] ?>%
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-title">Utilisateurs</div>
                    <div class="stat-value"><?= number_format($stats['users']['total']) ?></div>
                    <div class="stat-subtitle">
                        <?= $stats['users']['active'] ?> actifs • 
                        +<?= $stats['users']['new'] ?> nouveaux
                    </div>
                </div>
                
                <!-- Modération IA -->
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon purple">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="stat-trend up">
                            <i class="fas fa-check"></i>
                            <?= $aiStats['automation_rate'] ?>%
                        </div>
                    </div>
                    <div class="stat-title">Modération IA</div>
                    <div class="stat-value"><?= number_format($aiStats['total_analyzed']) ?></div>
                    <div class="stat-subtitle">
                        <?= $aiStats['auto_approved'] ?> auto-approuvés • 
                        <?= $aiStats['auto_rejected'] ?> auto-rejetés
                    </div>
                </div>
            </div>
            
            <!-- GRAPHIQUES -->
            <div class="charts-grid">
                <!-- Évolution -->
                <div class="chart-container">
                    <div class="section-header" style="border: none; margin-bottom: 16px;">
                        <h3 class="section-title"><i class="fas fa-chart-area"></i> Évolution</h3>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
                
                <!-- Distribution qualité IA -->
                <div class="chart-container">
                    <div class="section-header" style="border: none; margin-bottom: 16px;">
                        <h3 class="section-title"><i class="fas fa-pie-chart"></i> Qualité Avis (IA)</h3>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="qualityChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- STATS IA DÉTAILLÉES -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-robot"></i> Performance Modération IA</h2>
                </div>
                
                <div class="ai-stats-grid">
                    <div class="ai-stat-item">
                        <div class="ai-stat-value"><?= $aiStats['automation_rate'] ?>%</div>
                        <div class="ai-stat-label">Taux d'automatisation</div>
                    </div>
                    <div class="ai-stat-item">
                        <div class="ai-stat-value"><?= $aiStats['avg_score'] ?></div>
                        <div class="ai-stat-label">Score moyen</div>
                    </div>
                    <div class="ai-stat-item">
                        <div class="ai-stat-value" style="color: var(--success);"><?= $aiStats['quality_distribution']['high'] ?></div>
                        <div class="ai-stat-label">Haute qualité (80+)</div>
                    </div>
                    <div class="ai-stat-item">
                        <div class="ai-stat-value" style="color: var(--warning);"><?= $aiStats['quality_distribution']['medium'] ?></div>
                        <div class="ai-stat-label">Moyenne (50-79)</div>
                    </div>
                    <div class="ai-stat-item">
                        <div class="ai-stat-value" style="color: var(--danger);"><?= $aiStats['quality_distribution']['low'] ?></div>
                        <div class="ai-stat-label">Basse (<50)</div>
                    </div>
                </div>
            </div>
            
            <!-- TOP RESTAURANTS -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-trophy"></i> Top 10 Restaurants</h2>
                </div>
                
                <?php if (!empty($topRestaurants)): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Restaurant</th>
                                    <th>Ville</th>
                                    <th>Type</th>
                                    <th>Avis</th>
                                    <th>Note moyenne</th>
                                    <th>Votes utiles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topRestaurants as $index => $resto): ?>
                                    <tr>
                                        <td><strong><?= $index + 1 ?></strong></td>
                                        <td><strong><?= htmlspecialchars($resto['nom']) ?></strong></td>
                                        <td><?= htmlspecialchars($resto['ville']) ?></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($resto['type_cuisine']) ?></span></td>
                                        <td><?= $resto['review_count'] ?></td>
                                        <td>
                                            <strong style="color: var(--warning);">
                                                <?= number_format($resto['avg_rating'], 1) ?>/5
                                            </strong>
                                        </td>
                                        <td><?= $resto['total_votes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-bar"></i>
                        <p>Aucune donnée pour cette période</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- TOP CONTRIBUTEURS -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-medal"></i> Top Contributeurs</h2>
                </div>
                
                <?php if (!empty($topUsers)): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Utilisateur</th>
                                    <th>Ville</th>
                                    <th>Avis publiés</th>
                                    <th>Note moyenne</th>
                                    <th>Votes "utile" reçus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topUsers as $index => $user): ?>
                                    <tr>
                                        <td><strong><?= $index + 1 ?></strong></td>
                                        <td><strong><?= htmlspecialchars($user['prenom'] . ' ' . substr($user['nom'], 0, 1)) ?>.</strong></td>
                                        <td><?= htmlspecialchars($user['ville'] ?? 'N/A') ?></td>
                                        <td><span class="badge badge-success"><?= $user['review_count'] ?></span></td>
                                        <td><?= number_format($user['avg_rating'], 1) ?>/5</td>
                                        <td><?= $user['total_helpful_votes'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>Aucun contributeur pour cette période</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // THEME TOGGLE
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const newTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            localStorage.setItem('theme', newTheme);
        }
        
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('theme-icon').className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        
        // EXPORT MENU
        function toggleExportMenu() {
            const menu = document.getElementById('exportMenu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
        
        // Close menu on outside click
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('exportMenu');
            const btn = document.querySelector('.export-btn');
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
        
        // SIDEBAR TOGGLE
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
        
        // GRAPHIQUE ÉVOLUTION
        const chartData = <?= json_encode($charts) ?>;
        
        const ctxEvolution = document.getElementById('evolutionChart').getContext('2d');
        new Chart(ctxEvolution, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
        
        // GRAPHIQUE QUALITÉ
        const ctxQuality = document.getElementById('qualityChart').getContext('2d');
        new Chart(ctxQuality, {
            type: 'doughnut',
            data: {
                labels: ['Haute qualité (80+)', 'Moyenne (50-79)', 'Basse (<50)'],
                datasets: [{
                    data: [
                        <?= $aiStats['quality_distribution']['high'] ?>,
                        <?= $aiStats['quality_distribution']['medium'] ?>,
                        <?= $aiStats['quality_distribution']['low'] ?>
                    ],
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>
