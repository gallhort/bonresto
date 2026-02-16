<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Analytics' ?> - LeBonResto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ═══════════════════════════════════════════════════════════════
           VARIABLES & THEME
           ═══════════════════════════════════════════════════════════════ */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --accent: #00635a;
            --accent-light: #00897b;
            --blue: #3b82f6;
            --green: #10b981;
            --orange: #f59e0b;
            --purple: #8b5cf6;
            --pink: #ec4899;
            --cyan: #06b6d4;
            --red: #ef4444;
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
        
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: var(--bg-secondary); 
            color: var(--text-primary);
            transition: background 0.3s, color 0.3s;
            line-height: 1.6;
        }
        
        /* ═══════════════════════════════════════════════════════════════
           LAYOUT
           ═══════════════════════════════════════════════════════════════ */
        .main-content { 
            margin-left: var(--sidebar-width); 
            min-height: 100vh; 
            transition: margin-left 0.3s; 
        }
        
        .sidebar.collapsed ~ .main-content { margin-left: 70px; }
        
        .container { 
            padding: 30px; 
            max-width: 1600px; 
            margin: 0 auto; 
        }
        
        /* ═══════════════════════════════════════════════════════════════
           TOPBAR
           ═══════════════════════════════════════════════════════════════ */
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
        .topbar-left h1 { font-size: 24px; font-weight: 700; color: var(--text-primary); }
        .topbar-left h1 i { color: var(--accent); margin-right: 10px; }
        
        .topbar-right { display: flex; gap: 12px; align-items: center; }
        
        .period-filters {
            display: flex;
            gap: 4px;
            background: var(--bg-tertiary);
            padding: 4px;
            border-radius: 10px;
        }
        
        .period-btn {
            padding: 8px 16px;
            border: none;
            background: transparent;
            color: var(--text-secondary);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 13px;
            transition: all 0.2s;
        }
        
        .period-btn:hover { background: var(--bg-primary); color: var(--text-primary); }
        .period-btn.active { background: var(--accent); color: white; }
        
        .export-btn {
            padding: 10px 20px;
            background: var(--green);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .export-btn:hover { background: #059669; transform: translateY(-2px); }
        
        .export-menu {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            z-index: 1000;
            overflow: hidden;
        }
        
        .export-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: var(--text-primary);
            text-decoration: none;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        
        .export-menu a:last-child { border-bottom: none; }
        .export-menu a:hover { background: var(--bg-tertiary); }
        .export-menu a i { color: var(--accent); width: 20px; }
        
        .theme-toggle {
            width: 44px;
            height: 44px;
            border: 2px solid var(--border-color);
            background: var(--bg-primary);
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--text-primary);
            transition: all 0.2s;
        }
        
        .theme-toggle:hover { border-color: var(--accent); color: var(--accent); }
        
        /* ═══════════════════════════════════════════════════════════════
           STATS CARDS
           ═══════════════════════════════════════════════════════════════ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
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
        }
        
        .stat-card.blue::before { background: linear-gradient(90deg, var(--blue), #60a5fa); }
        .stat-card.green::before { background: linear-gradient(90deg, var(--green), #34d399); }
        .stat-card.orange::before { background: linear-gradient(90deg, var(--orange), #fbbf24); }
        .stat-card.purple::before { background: linear-gradient(90deg, var(--purple), #a78bfa); }
        
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
            font-size: 22px;
        }
        
        .stat-icon.blue { background: rgba(59, 130, 246, 0.15); color: var(--blue); }
        .stat-icon.green { background: rgba(16, 185, 129, 0.15); color: var(--green); }
        .stat-icon.orange { background: rgba(245, 158, 11, 0.15); color: var(--orange); }
        .stat-icon.purple { background: rgba(139, 92, 246, 0.15); color: var(--purple); }
        
        .stat-trend {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .stat-trend.up { background: rgba(16, 185, 129, 0.15); color: var(--green); }
        .stat-trend.down { background: rgba(239, 68, 68, 0.15); color: var(--red); }
        .stat-trend.neutral { background: var(--bg-tertiary); color: var(--text-secondary); }
        
        .stat-title { color: var(--text-secondary); font-size: 14px; font-weight: 500; margin-bottom: 8px; }
        .stat-value { font-size: 32px; font-weight: 800; color: var(--text-primary); margin-bottom: 8px; }
        .stat-subtitle { color: var(--text-secondary); font-size: 13px; }
        
        /* ═══════════════════════════════════════════════════════════════
           SECTIONS
           ═══════════════════════════════════════════════════════════════ */
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
            font-size: 18px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i { color: var(--accent); }
        
        .section-divider {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 40px 0 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .section-divider h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-divider h2 i { color: var(--accent); }
        
        .period-select {
            padding: 10px 16px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            cursor: pointer;
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        /* ═══════════════════════════════════════════════════════════════
           CHARTS
           ═══════════════════════════════════════════════════════════════ */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
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
        
        .chart-wrapper { position: relative; height: 280px; }
        
        /* ═══════════════════════════════════════════════════════════════
           TABLES
           ═══════════════════════════════════════════════════════════════ */
        .table-responsive { overflow-x: auto; }
        
        table { width: 100%; border-collapse: collapse; }
        
        th, td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background: var(--bg-tertiary);
            font-weight: 600;
            color: var(--text-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td { color: var(--text-primary); font-size: 14px; }
        tr:hover { background: var(--bg-tertiary); }
        
        .badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success { background: rgba(16, 185, 129, 0.15); color: var(--green); }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: var(--orange); }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: var(--red); }
        .badge-info { background: rgba(59, 130, 246, 0.15); color: var(--blue); }
        
        /* ═══════════════════════════════════════════════════════════════
           AI STATS
           ═══════════════════════════════════════════════════════════════ */
        .ai-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }
        
        .ai-stat-item {
            padding: 20px;
            background: var(--bg-tertiary);
            border-radius: 12px;
            text-align: center;
        }
        
        .ai-stat-value { font-size: 28px; font-weight: 800; color: var(--accent); margin-bottom: 4px; }
        .ai-stat-label { font-size: 12px; color: var(--text-secondary); font-weight: 500; }
        
        /* ═══════════════════════════════════════════════════════════════
           TRAFFIC KPI
           ═══════════════════════════════════════════════════════════════ */
        .traffic-kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        
        .traffic-kpi {
            background: var(--bg-primary);
            border-radius: 14px;
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--accent);
        }
        
        .traffic-kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }
        
        .traffic-kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: white;
        }
        
        .traffic-kpi-icon.blue { background: var(--blue); }
        .traffic-kpi-icon.green { background: var(--green); }
        .traffic-kpi-icon.orange { background: var(--orange); }
        .traffic-kpi-icon.purple { background: var(--purple); }
        
        .traffic-kpi-trend {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .traffic-kpi-trend.up { background: rgba(16, 185, 129, 0.15); color: var(--green); }
        .traffic-kpi-trend.down { background: rgba(239, 68, 68, 0.15); color: var(--red); }
        .traffic-kpi-trend.neutral { background: var(--bg-tertiary); color: var(--text-secondary); }
        
        .traffic-kpi-value { font-size: 32px; font-weight: 800; color: var(--text-primary); }
        .traffic-kpi-label { font-size: 14px; color: var(--text-secondary); margin-top: 4px; }
        
        /* ═══════════════════════════════════════════════════════════════
           ACTIONS MINI GRID
           ═══════════════════════════════════════════════════════════════ */
        .actions-mini-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .action-mini {
            background: var(--bg-primary);
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: transform 0.2s;
        }
        
        .action-mini:hover { transform: translateY(-2px); }
        
        .action-mini-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 10px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .action-mini-value { font-size: 24px; font-weight: 800; color: var(--text-primary); }
        .action-mini-label { font-size: 12px; color: var(--text-secondary); margin-top: 4px; }
        
        /* ═══════════════════════════════════════════════════════════════
           TRAFFIC GRID
           ═══════════════════════════════════════════════════════════════ */
        .traffic-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .traffic-card {
            background: var(--bg-primary);
            border-radius: 14px;
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .traffic-card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-primary);
        }
        
        .traffic-card-title i { color: var(--accent); }
        
        /* ═══════════════════════════════════════════════════════════════
           SOURCES
           ═══════════════════════════════════════════════════════════════ */
        .source-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .source-row:last-child { border-bottom: none; }
        
        .source-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            flex-shrink: 0;
        }
        
        .source-info { flex: 1; }
        .source-name { font-weight: 600; font-size: 14px; color: var(--text-primary); }
        .source-bar { height: 6px; background: var(--bg-tertiary); border-radius: 3px; margin-top: 8px; }
        .source-bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s; }
        .source-count { font-weight: 700; font-size: 16px; color: var(--text-primary); }
        
        /* ═══════════════════════════════════════════════════════════════
           DEVICES
           ═══════════════════════════════════════════════════════════════ */
        .device-row {
            display: flex;
            justify-content: space-around;
            padding: 20px 0;
        }
        
        .device-item { text-align: center; }
        
        .device-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 12px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .device-icon.mobile { background: rgba(59, 130, 246, 0.15); color: var(--blue); }
        .device-icon.desktop { background: rgba(16, 185, 129, 0.15); color: var(--green); }
        .device-icon.tablet { background: rgba(245, 158, 11, 0.15); color: var(--orange); }
        
        .device-percent { font-size: 28px; font-weight: 800; color: var(--text-primary); }
        .device-label { font-size: 13px; color: var(--text-secondary); }
        
        /* ═══════════════════════════════════════════════════════════════
           SEARCH
           ═══════════════════════════════════════════════════════════════ */
        .search-container { position: relative; }
        
        .search-input {
            width: 100%;
            padding: 14px 20px;
            font-size: 15px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            background: var(--bg-primary);
            color: var(--text-primary);
            transition: all 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(0, 99, 90, 0.1);
        }
        
        .search-input::placeholder { color: var(--text-secondary); }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--bg-primary);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            margin-top: 8px;
            border: 1px solid var(--border-color);
        }
        
        .search-results.active { display: block; }
        
        .search-result-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .search-result-item:hover { background: var(--bg-tertiary); }
        .search-result-item:last-child { border-bottom: none; }
        
        .result-info { flex: 1; }
        .result-name { font-weight: 600; font-size: 15px; color: var(--text-primary); margin-bottom: 4px; }
        .result-city { font-size: 13px; color: var(--text-secondary); }
        .result-stats { display: flex; gap: 20px; text-align: center; }
        .result-stat-value { font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .result-stat-label { font-size: 11px; color: var(--text-secondary); }
        .no-results { padding: 24px; text-align: center; color: var(--text-secondary); }
        
        /* ═══════════════════════════════════════════════════════════════
           TOP RESTAURANTS TABLE
           ═══════════════════════════════════════════════════════════════ */
        .top-restaurants-table { width: 100%; border-collapse: collapse; }
        
        .top-restaurants-table th {
            text-align: left;
            padding: 14px 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            border-bottom: 2px solid var(--border-color);
            background: transparent;
        }
        
        .top-restaurants-table td {
            padding: 16px 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-primary);
        }
        
        .top-restaurants-table tr:hover { background: var(--bg-tertiary); }
        
        .resto-rank {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--bg-tertiary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: var(--text-primary);
        }
        
        .resto-rank.gold { background: #fef3c7; color: #d97706; }
        .resto-rank.silver { background: #e5e7eb; color: #4b5563; }
        .resto-rank.bronze { background: #fed7aa; color: #c2410c; }
        
        .resto-name { font-weight: 600; color: var(--text-primary); }
        .resto-city { font-size: 12px; color: var(--text-secondary); margin-top: 2px; }
        
        /* ═══════════════════════════════════════════════════════════════
           EMPTY STATE
           ═══════════════════════════════════════════════════════════════ */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state i { font-size: 64px; margin-bottom: 16px; opacity: 0.3; }
        
        /* ═══════════════════════════════════════════════════════════════
           RESPONSIVE
           ═══════════════════════════════════════════════════════════════ */
        @media (max-width: 1024px) {
            .traffic-grid { grid-template-columns: 1fr; }
            .charts-grid { grid-template-columns: 1fr; }
        }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .topbar { flex-direction: column; gap: 15px; padding: 15px; }
            .topbar-right { flex-wrap: wrap; justify-content: center; }
            .container { padding: 15px; }
            .stats-grid { grid-template-columns: 1fr; }
            .actions-mini-grid { grid-template-columns: repeat(2, 1fr); }
            .device-row { flex-direction: column; gap: 20px; }
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
                <div class="period-filters">
                    <button class="period-btn <?= $period == 7 ? 'active' : '' ?>" onclick="window.location.href='?period=7'">7j</button>
                    <button class="period-btn <?= $period == 30 ? 'active' : '' ?>" onclick="window.location.href='?period=30'">30j</button>
                    <button class="period-btn <?= $period == 90 ? 'active' : '' ?>" onclick="window.location.href='?period=90'">90j</button>
                    <button class="period-btn <?= $period == 365 ? 'active' : '' ?>" onclick="window.location.href='?period=365'">1 an</button>
                </div>
                
                <div style="position: relative;">
                    <button class="export-btn" onclick="toggleExportMenu()">
                        <i class="fas fa-download"></i> Exporter
                    </button>
                    <div class="export-menu" id="exportMenu">
                        <a href="/admin/analytics/export?type=reviews&period=<?= $period ?>"><i class="fas fa-star"></i> Avis CSV</a>
                        <a href="/admin/analytics/export?type=restaurants&period=<?= $period ?>"><i class="fas fa-utensils"></i> Restaurants CSV</a>
                        <a href="/admin/analytics/export?type=users&period=<?= $period ?>"><i class="fas fa-users"></i> Utilisateurs CSV</a>
                    </div>
                </div>
                
                <button class="theme-toggle" onclick="toggleTheme()">
                    <i class="fas fa-moon" id="theme-icon"></i>
                </button>
            </div>
        </div>
        
        <div class="container">
            
            <!-- ═══════════════════════════════════════════════════════════
                 STATS CARDS
                 ═══════════════════════════════════════════════════════════ -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-header">
                        <div class="stat-icon blue"><i class="fas fa-utensils"></i></div>
                        <?php if (isset($stats['restaurants']['trend'])): ?>
                        <div class="stat-trend <?= $stats['restaurants']['trend']['direction'] ?>">
                            <i class="fas fa-arrow-<?= $stats['restaurants']['trend']['direction'] == 'up' ? 'up' : ($stats['restaurants']['trend']['direction'] == 'down' ? 'down' : 'right') ?>"></i>
                            <?= $stats['restaurants']['trend']['value'] ?>%
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-title">Restaurants</div>
                    <div class="stat-value"><?= number_format($stats['restaurants']['total']) ?></div>
                    <div class="stat-subtitle">
                        <?= $stats['restaurants']['validated'] ?> validés • <?= $stats['restaurants']['pending'] ?> en attente
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-header">
                        <div class="stat-icon green"><i class="fas fa-star"></i></div>
                        <?php if (isset($stats['reviews']['trend'])): ?>
                        <div class="stat-trend <?= $stats['reviews']['trend']['direction'] ?>">
                            <i class="fas fa-arrow-<?= $stats['reviews']['trend']['direction'] == 'up' ? 'up' : ($stats['reviews']['trend']['direction'] == 'down' ? 'down' : 'right') ?>"></i>
                            <?= $stats['reviews']['trend']['value'] ?>%
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-title">Avis</div>
                    <div class="stat-value"><?= number_format($stats['reviews']['total']) ?></div>
                    <div class="stat-subtitle">
                        Note moyenne: <?= $stats['reviews']['avg_rating'] ?>/5
                    </div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-header">
                        <div class="stat-icon orange"><i class="fas fa-users"></i></div>
                        <?php if (isset($stats['users']['trend'])): ?>
                        <div class="stat-trend <?= $stats['users']['trend']['direction'] ?>">
                            <i class="fas fa-arrow-<?= $stats['users']['trend']['direction'] == 'up' ? 'up' : ($stats['users']['trend']['direction'] == 'down' ? 'down' : 'right') ?>"></i>
                            <?= $stats['users']['trend']['value'] ?>%
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="stat-title">Utilisateurs</div>
                    <div class="stat-value"><?= number_format($stats['users']['total']) ?></div>
                    <div class="stat-subtitle">
                        <?= $stats['users']['active'] ?> actifs cette semaine
                    </div>
                </div>
                
                <div class="stat-card purple">
                    <div class="stat-header">
                        <div class="stat-icon purple"><i class="fas fa-robot"></i></div>
                        <div class="stat-trend up">
                            <i class="fas fa-check"></i> <?= $aiStats['automation_rate'] ?>%
                        </div>
                    </div>
                    <div class="stat-title">Modération IA</div>
                    <div class="stat-value"><?= number_format($aiStats['total_analyzed']) ?></div>
                    <div class="stat-subtitle">
                        <?= $aiStats['auto_approved'] ?> auto • <?= $aiStats['auto_rejected'] ?> rejetés
                    </div>
                </div>
            </div>
            
            <!-- ═══════════════════════════════════════════════════════════
                 GRAPHIQUES
                 ═══════════════════════════════════════════════════════════ -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="traffic-card-title"><i class="fas fa-chart-area"></i> Évolution</div>
                    <div class="chart-wrapper">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
                
                <div class="chart-container">
                    <div class="traffic-card-title"><i class="fas fa-chart-pie"></i> Qualité Avis (IA)</div>
                    <div class="chart-wrapper">
                        <canvas id="qualityChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- ═══════════════════════════════════════════════════════════
                 STATS IA
                 ═══════════════════════════════════════════════════════════ -->
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
                        <div class="ai-stat-value" style="color: var(--green);"><?= $aiStats['quality_distribution']['high'] ?></div>
                        <div class="ai-stat-label">Haute qualité (80+)</div>
                    </div>
                    <div class="ai-stat-item">
                        <div class="ai-stat-value" style="color: var(--orange);"><?= $aiStats['quality_distribution']['medium'] ?></div>
                        <div class="ai-stat-label">Moyenne (50-79)</div>
                    </div>
                    <div class="ai-stat-item">
                        <div class="ai-stat-value" style="color: var(--red);"><?= $aiStats['quality_distribution']['low'] ?></div>
                        <div class="ai-stat-label">Basse (<50)</div>
                    </div>
                </div>
            </div>
            
            <!-- ═══════════════════════════════════════════════════════════
                 TOP RESTAURANTS PAR NOTES
                 ═══════════════════════════════════════════════════════════ -->
            <div class="section">
                <div class="section-header">
                    <h2 class="section-title"><i class="fas fa-trophy"></i> Top 10 Restaurants (Notes)</h2>
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
                                <th>Note</th>
                                <th>Votes</th>
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
                                <td><strong style="color: var(--orange);"><?= number_format($resto['avg_rating'], 1) ?>/5</strong></td>
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
            
            <!-- ═══════════════════════════════════════════════════════════
                 TOP CONTRIBUTEURS
                 ═══════════════════════════════════════════════════════════ -->
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
                                <th>Avis</th>
                                <th>Note moy.</th>
                                <th>Votes reçus</th>
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
            
            <!-- ═══════════════════════════════════════════════════════════
                 SECTION TRAFIC & ANALYTICS
                 ═══════════════════════════════════════════════════════════ -->
            <div class="section-divider">
                <h2><i class="fas fa-chart-line"></i> Trafic & Interactions</h2>
            </div>
            
            <!-- KPI Cards -->
            <div class="traffic-kpi-grid">
                <div class="traffic-kpi">
                    <div class="traffic-kpi-header">
                        <div class="traffic-kpi-icon blue"><i class="fas fa-eye"></i></div>
                        <?php if (!empty($trafficStats['views_trend'])): ?>
                        <span class="traffic-kpi-trend <?= $trafficStats['views_trend']['direction'] ?>">
                            <i class="fas fa-arrow-<?= $trafficStats['views_trend']['direction'] === 'up' ? 'up' : ($trafficStats['views_trend']['direction'] === 'down' ? 'down' : 'right') ?>"></i>
                            <?= $trafficStats['views_trend']['value'] ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="traffic-kpi-value"><?= number_format($trafficStats['views'] ?? 0) ?></div>
                    <div class="traffic-kpi-label">Vues totales</div>
                </div>
                
                <div class="traffic-kpi">
                    <div class="traffic-kpi-header">
                        <div class="traffic-kpi-icon green"><i class="fas fa-users"></i></div>
                        <?php if (!empty($trafficStats['visitors_trend'])): ?>
                        <span class="traffic-kpi-trend <?= $trafficStats['visitors_trend']['direction'] ?>">
                            <i class="fas fa-arrow-<?= $trafficStats['visitors_trend']['direction'] === 'up' ? 'up' : ($trafficStats['visitors_trend']['direction'] === 'down' ? 'down' : 'right') ?>"></i>
                            <?= $trafficStats['visitors_trend']['value'] ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="traffic-kpi-value"><?= number_format($trafficStats['unique_visitors'] ?? 0) ?></div>
                    <div class="traffic-kpi-label">Visiteurs uniques</div>
                </div>
                
                <div class="traffic-kpi">
                    <div class="traffic-kpi-header">
                        <div class="traffic-kpi-icon orange"><i class="fas fa-hand-pointer"></i></div>
                    </div>
                    <div class="traffic-kpi-value"><?= number_format($trafficStats['total_interactions'] ?? 0) ?></div>
                    <div class="traffic-kpi-label">Interactions</div>
                </div>
                
                <div class="traffic-kpi">
                    <div class="traffic-kpi-header">
                        <div class="traffic-kpi-icon purple"><i class="fas fa-percentage"></i></div>
                    </div>
                    <div class="traffic-kpi-value"><?= $trafficStats['conversion_rate'] ?? 0 ?>%</div>
                    <div class="traffic-kpi-label">Taux de conversion</div>
                </div>
            </div>
            
            <!-- Actions mini grid -->
            <div class="actions-mini-grid">
                <div class="action-mini">
                    <div class="action-mini-icon" style="background: var(--green)"><i class="fas fa-phone"></i></div>
                    <div class="action-mini-value"><?= number_format($trafficStats['clicks_phone'] ?? 0) ?></div>
                    <div class="action-mini-label">Appels</div>
                </div>
                <div class="action-mini">
                    <div class="action-mini-icon" style="background: var(--blue)"><i class="fas fa-directions"></i></div>
                    <div class="action-mini-value"><?= number_format($trafficStats['clicks_directions'] ?? 0) ?></div>
                    <div class="action-mini-label">Itinéraires</div>
                </div>
                <div class="action-mini">
                    <div class="action-mini-icon" style="background: var(--purple)"><i class="fas fa-globe"></i></div>
                    <div class="action-mini-value"><?= number_format($trafficStats['clicks_website'] ?? 0) ?></div>
                    <div class="action-mini-label">Sites web</div>
                </div>
                <div class="action-mini">
                    <div class="action-mini-icon" style="background: var(--orange)"><i class="fas fa-utensils"></i></div>
                    <div class="action-mini-value"><?= number_format($trafficStats['clicks_menu'] ?? 0) ?></div>
                    <div class="action-mini-label">Menus</div>
                </div>
                <div class="action-mini">
                    <div class="action-mini-icon" style="background: var(--red)"><i class="fas fa-heart"></i></div>
                    <div class="action-mini-value"><?= number_format($trafficStats['wishlist_adds'] ?? 0) ?></div>
                    <div class="action-mini-label">Favoris</div>
                </div>
                <div class="action-mini">
                    <div class="action-mini-icon" style="background: var(--cyan)"><i class="fas fa-share-alt"></i></div>
                    <div class="action-mini-value"><?= number_format($trafficStats['shares'] ?? 0) ?></div>
                    <div class="action-mini-label">Partages</div>
                </div>
            </div>
            
            <!-- Graphique + Sources + Devices -->
            <div class="traffic-grid">
                <div class="traffic-card">
                    <h3 class="traffic-card-title"><i class="fas fa-chart-area"></i> Évolution du trafic</h3>
                    <div class="chart-wrapper">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>
                
                <div>
                    <!-- Sources -->
                    <div class="traffic-card" style="margin-bottom: 20px;">
                        <h3 class="traffic-card-title"><i class="fas fa-globe"></i> Sources</h3>
                        <?php 
                        $sourceColors = [
                            'Direct' => '#6366f1', 'Google' => '#ea4335', 'Facebook' => '#1877f2',
                            'Instagram' => '#e4405f', 'Recherche interne' => '#00635a', 'Autre' => '#6b7280'
                        ];
                        $totalSources = array_sum(array_column($trafficSources ?? [], 'count'));
                        if (!empty($trafficSources)):
                        foreach ($trafficSources as $source): 
                            $percent = $totalSources > 0 ? round(($source['count'] / $totalSources) * 100) : 0;
                            $color = $sourceColors[$source['source']] ?? '#6b7280';
                        ?>
                        <div class="source-row">
                            <div class="source-icon" style="background: <?= $color ?>"><i class="fas fa-circle"></i></div>
                            <div class="source-info">
                                <div class="source-name"><?= htmlspecialchars($source['source']) ?></div>
                                <div class="source-bar"><div class="source-bar-fill" style="width: <?= $percent ?>%; background: <?= $color ?>"></div></div>
                            </div>
                            <div class="source-count"><?= number_format($source['count']) ?></div>
                        </div>
                        <?php endforeach; else: ?>
                        <p style="color: var(--text-secondary); text-align: center; padding: 20px;">Aucune donnée</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Devices -->
                    <div class="traffic-card">
                        <h3 class="traffic-card-title"><i class="fas fa-mobile-alt"></i> Appareils</h3>
                        <div class="device-row">
                            <?php 
                            $deviceIcons = ['mobile' => 'fa-mobile-alt', 'desktop' => 'fa-desktop', 'tablet' => 'fa-tablet-alt'];
                            $totalDevices = array_sum(array_column($deviceStats ?? [], 'count'));
                            foreach (['mobile', 'desktop', 'tablet'] as $device):
                                $found = array_filter($deviceStats ?? [], fn($d) => $d['device'] === $device);
                                $count = !empty($found) ? array_values($found)[0]['count'] : 0;
                                $percent = $totalDevices > 0 ? round(($count / $totalDevices) * 100) : 0;
                            ?>
                            <div class="device-item">
                                <div class="device-icon <?= $device ?>"><i class="fas <?= $deviceIcons[$device] ?>"></i></div>
                                <div class="device-percent"><?= $percent ?>%</div>
                                <div class="device-label"><?= ucfirst($device) ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recherche Restaurant -->
            <div class="traffic-card" style="margin-bottom: 24px;">
                <h3 class="traffic-card-title"><i class="fas fa-search"></i> Rechercher un restaurant</h3>
                <div class="search-container">
                    <input type="text" id="searchRestaurant" class="search-input" placeholder="Tapez le nom d'un restaurant...">
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>
            
            <!-- Top 10 Restaurants par Trafic -->
            <div class="traffic-card">
                <h3 class="traffic-card-title"><i class="fas fa-fire"></i> Top 10 restaurants par trafic</h3>
                <table class="top-restaurants-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Restaurant</th>
                            <th>Vues</th>
                            <th>Visiteurs</th>
                            <th>Appels</th>
                            <th>Itinéraires</th>
                            <th>Total clics</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topRestaurantsByTraffic)): ?>
                            <?php foreach ($topRestaurantsByTraffic as $index => $resto): 
                                $rankClass = $index === 0 ? 'gold' : ($index === 1 ? 'silver' : ($index === 2 ? 'bronze' : ''));
                            ?>
                            <tr>
                                <td><span class="resto-rank <?= $rankClass ?>"><?= $index + 1 ?></span></td>
                                <td>
                                    <div class="resto-name"><?= htmlspecialchars($resto['nom'] ?? '') ?></div>
                                    <div class="resto-city"><?= htmlspecialchars($resto['ville'] ?? '') ?></div>
                                </td>
                                <td><strong><?= number_format((int)($resto['views'] ?? 0)) ?></strong></td>
                                <td><?= number_format((int)($resto['unique_visitors'] ?? 0)) ?></td>
                                <td><?= number_format((int)($resto['clicks_phone'] ?? 0)) ?></td>
                                <td><?= number_format((int)($resto['clicks_directions'] ?? 0)) ?></td>
                                <td><strong><?= number_format((int)($resto['total_clicks'] ?? 0)) ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center; padding:30px; color: var(--text-secondary);">Aucune donnée de trafic</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div><!-- /.container -->
    </div><!-- /.main-content -->
    
    <script>
        // ═══════════════════════════════════════════════════════════════
        // THEME TOGGLE
        // ═══════════════════════════════════════════════════════════════
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
        
        // ═══════════════════════════════════════════════════════════════
        // EXPORT MENU
        // ═══════════════════════════════════════════════════════════════
        function toggleExportMenu() {
            const menu = document.getElementById('exportMenu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }
        
        document.addEventListener('click', (e) => {
            const menu = document.getElementById('exportMenu');
            const btn = document.querySelector('.export-btn');
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
        
        // ═══════════════════════════════════════════════════════════════
        // RECHERCHE RESTAURANT
        // ═══════════════════════════════════════════════════════════════
        const searchInput = document.getElementById('searchRestaurant');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                searchResults.classList.remove('active');
                return;
            }
            
            searchTimeout = setTimeout(() => {
                fetch(`/admin/analytics/search?q=${encodeURIComponent(query)}&period=<?= $period ?? 30 ?>`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.results.length === 0) {
                            searchResults.innerHTML = '<div class="no-results">Aucun restaurant trouvé</div>';
                        } else {
                            searchResults.innerHTML = data.results.map(r => `
                                <div class="search-result-item" onclick="window.location.href='/admin/restaurant/${r.id}/stats'">
                                    <div class="result-info">
                                        <div class="result-name">${escapeHtml(r.nom)}</div>
                                        <div class="result-city">${escapeHtml(r.ville || 'N/A')}</div>
                                    </div>
                                    <div class="result-stats">
                                        <div class="result-stat">
                                            <div class="result-stat-value">${formatNumber(r.views)}</div>
                                            <div class="result-stat-label">Vues</div>
                                        </div>
                                        <div class="result-stat">
                                            <div class="result-stat-value">${formatNumber(r.unique_visitors)}</div>
                                            <div class="result-stat-label">Visiteurs</div>
                                        </div>
                                        <div class="result-stat">
                                            <div class="result-stat-value">${formatNumber(r.total_clicks)}</div>
                                            <div class="result-stat-label">Clics</div>
                                        </div>
                                    </div>
                                </div>
                            `).join('');
                        }
                        searchResults.classList.add('active');
                    });
            }, 300);
        });
        
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('active');
            }
        });
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }
        
        function formatNumber(num) {
            return new Intl.NumberFormat('fr-FR').format(num || 0);
        }
        
        // ═══════════════════════════════════════════════════════════════
        // GRAPHIQUES
        // ═══════════════════════════════════════════════════════════════
        const chartData = <?= json_encode($charts) ?>;
        
        // Evolution Chart
        new Chart(document.getElementById('evolutionChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true } }
            }
        });
        
        // Quality Chart
        new Chart(document.getElementById('qualityChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Haute qualité (80+)', 'Moyenne (50-79)', 'Basse (<50)'],
                datasets: [{
                    data: [
                        <?= $aiStats['quality_distribution']['high'] ?>,
                        <?= $aiStats['quality_distribution']['medium'] ?>,
                        <?= $aiStats['quality_distribution']['low'] ?>
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
        
        // Traffic Chart
        new Chart(document.getElementById('trafficChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($trafficChart['labels'] ?? []) ?>,
                datasets: [
                    {
                        label: 'Vues',
                        data: <?= json_encode($trafficChart['views'] ?? []) ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2
                    },
                    {
                        label: 'Visiteurs',
                        data: <?= json_encode($trafficChart['visitors'] ?? []) ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>