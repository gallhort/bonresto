<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Stats Modération IA - BonResto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --bg-primary: #ffffff; --bg-secondary: #f5f5f5; --bg-sidebar: #1e293b;
            --text-primary: #1f2937; --text-secondary: #6b7280; --text-sidebar: #e2e8f0;
            --border-color: #e5e7eb; --shadow: 0 2px 8px rgba(0,0,0,0.1); --accent: #3b82f6;
            --sidebar-width: 260px; --sidebar-collapsed: 70px;
        }
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
        
        [data-theme="dark"] {
            --bg-primary: #1e293b;
            --bg-secondary: #0f172a;
            --bg-tertiary: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --border-color: #334155;
            --shadow: 0 1px 3px rgba(0,0,0,0.3);
                        --bg-primary: #1f2937; --bg-secondary: #111827; --bg-sidebar: #0f172a;
            --text-primary: #f9fafb; --text-secondary: #9ca3af; --text-sidebar: #cbd5e1;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg-secondary); color: var(--text-primary); }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: var(--bg-sidebar); color: var(--text-sidebar); z-index: 1000; overflow: hidden; }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .topbar { background: var(--bg-primary); border-bottom: 1px solid var(--border-color); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .theme-toggle { background: var(--bg-secondary); border: 1px solid var(--border-color); width: 40px; height: 40px; border-radius: 8px; cursor: pointer; }
        .container { padding: 30px; }
        .charts-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .chart-card { background: var(--bg-primary); padding: 25px; border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); }
        .chart-card h3 { margin-bottom: 20px; color: var(--text-primary); font-size: 16px; }
        .stats-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: var(--bg-primary); padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color); }
        .stat-box .number { font-size: 36px; font-weight: 700; margin: 10px 0; }
        .stat-box .label { color: var(--text-secondary); font-size: 14px; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <h1><i class="fas fa-robot"></i> Statistiques Modération IA</h1>
            <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i></button>
        </div>
        
        <div class="container">
            <!-- STATS RÉSUMÉ -->
            <div class="stats-summary" id="stats-summary">
                <div class="stat-box">
                    <div class="label">Total Avis Analysés</div>
                    <div class="number" id="total-analyzed">-</div>
                </div>
                <div class="stat-box">
                    <div class="label">Auto-approuvés</div>
                    <div class="number" style="color: #10b981;" id="auto-approved">-</div>
                </div>
                <div class="stat-box">
                    <div class="label">Auto-rejetés</div>
                    <div class="number" style="color: #ef4444;" id="auto-rejected">-</div>
                </div>
                <div class="stat-box">
                    <div class="label">Taux Override</div>
                    <div class="number" style="color: #f59e0b;" id="override-rate">-</div>
                </div>
            </div>
            
            <!-- GRAPHIQUES -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>📊 Modération Auto vs Manuelle</h3>
                    <canvas id="moderationTypeChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <h3>🎯 Distribution Scores Qualité</h3>
                    <canvas id="scoreDistributionChart"></canvas>
                </div>
                
                <div class="chart-card" style="grid-column: span 2;">
                    <h3>📈 Évolution Modération (30 jours)</h3>
                    <canvas id="evolutionChart"></canvas>
                </div>
                
                <div class="chart-card" style="grid-column: span 2;">
                    <h3>🔍 Top 10 Raisons de Rejet</h3>
                    <canvas id="penaltiesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // THEME
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
        
        // CHARGER STATS
        let charts = {};
        
        async function loadAiStats() {
            try {
                const response = await fetch('/admin/api/ai-stats');
                const data = await response.json();
                
                if (data.success) {
                    renderStats(data.stats);
                    renderCharts(data.stats);
                } else {
                    console.error('Erreur chargement stats');
                }
            } catch (error) {
                console.error('Erreur:', error);
            }
        }
        
        function renderStats(stats) {
            // Calculer totaux
            const aiCount = stats.moderation_type.find(m => m.moderated_by === 'ai')?.count || 0;
            const manualCount = stats.moderation_type.find(m => m.moderated_by === 'manual')?.count || 0;
            const total = aiCount + manualCount;
            
            const overridden = stats.override_rate?.overridden || 0;
            const totalRejected = stats.override_rate?.total_rejected || 1;
            const overridePercent = ((overridden / totalRejected) * 100).toFixed(1);
            
            document.getElementById('total-analyzed').textContent = total.toLocaleString();
            document.getElementById('auto-approved').textContent = aiCount.toLocaleString();
            document.getElementById('auto-rejected').textContent = totalRejected.toLocaleString();
            document.getElementById('override-rate').textContent = overridePercent + '%';
        }
        
        function renderCharts(stats) {
            // CHART 1: Modération Auto vs Manuel (Pie)
            const moderationData = stats.moderation_type;
            const aiCount = moderationData.find(m => m.moderated_by === 'ai')?.count || 0;
            const manualCount = moderationData.find(m => m.moderated_by === 'manual')?.count || 0;
            
            if (charts.moderationType) charts.moderationType.destroy();
            charts.moderationType = new Chart(document.getElementById('moderationTypeChart'), {
                type: 'doughnut',
                data: {
                    labels: ['🤖 Auto (IA)', '✋ Manuel'],
                    datasets: [{
                        data: [aiCount, manualCount],
                        backgroundColor: ['#8b5cf6', '#6b7280']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
            
            // CHART 2: Distribution Scores (Bar)
            const scoreData = stats.score_distribution;
            
            if (charts.scoreDistribution) charts.scoreDistribution.destroy();
            charts.scoreDistribution = new Chart(document.getElementById('scoreDistributionChart'), {
                type: 'bar',
                data: {
                    labels: scoreData.map(s => s.score_range),
                    datasets: [{
                        label: 'Nombre d\'avis',
                        data: scoreData.map(s => s.count),
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            
            // CHART 3: Évolution (Line)
            const evolutionData = stats.evolution;
            
            if (charts.evolution) charts.evolution.destroy();
            charts.evolution = new Chart(document.getElementById('evolutionChart'), {
                type: 'line',
                data: {
                    labels: evolutionData.map(e => new Date(e.date).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' })),
                    datasets: [
                        {
                            label: '🤖 Auto',
                            data: evolutionData.map(e => e.auto),
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            fill: true,
                            tension: 0.4
                        },
                        {
                            label: '✋ Manuel',
                            data: evolutionData.map(e => e.manual),
                            borderColor: '#6b7280',
                            backgroundColor: 'rgba(107, 114, 128, 0.1)',
                            fill: true,
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
            
            // CHART 4: Top Penalties (Horizontal Bar)
            const penaltiesData = stats.top_penalties;
            
            if (charts.penalties) charts.penalties.destroy();
            charts.penalties = new Chart(document.getElementById('penaltiesChart'), {
                type: 'bar',
                data: {
                    labels: penaltiesData.map(p => p.rule),
                    datasets: [{
                        label: 'Occurrences',
                        data: penaltiesData.map(p => p.count),
                        backgroundColor: '#ef4444'
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        }
        
        // LOAD ON READY
        loadAiStats();
    </script>
</body>
</html>
