<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Paramètres - BonResto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --bg-primary: #ffffff; --bg-secondary: #f5f5f5; --bg-sidebar: #1e293b; --text-primary: #1f2937; --text-secondary: #6b7280; --text-sidebar: #e2e8f0; --border-color: #e5e7eb; --shadow: 0 2px 8px rgba(0,0,0,0.1); --accent: #3b82f6; --sidebar-width: 260px; --sidebar-collapsed: 70px; }
        [data-theme="dark"] { --bg-primary: #1f2937; --bg-secondary: #111827; --bg-sidebar: #0f172a; --text-primary: #f9fafb; --text-secondary: #9ca3af; --text-sidebar: #cbd5e1; --border-color: #374151; --shadow: 0 2px 8px rgba(0,0,0,0.3); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg-secondary); color: var(--text-primary); }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: var(--bg-sidebar); color: var(--text-sidebar); transition: width 0.3s ease; z-index: 1000; overflow: hidden; }
        .sidebar.collapsed { width: var(--sidebar-collapsed); }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; }
        .sidebar-logo { font-size: 20px; font-weight: 700; }
        .sidebar-toggle { background: rgba(255,255,255,0.1); border: none; color: var(--text-sidebar); width: 36px; height: 36px; border-radius: 6px; cursor: pointer; }
        .sidebar-menu { list-style: none; padding: 20px 10px; }
        .menu-item { margin-bottom: 5px; }
        .menu-link { display: flex; align-items: center; padding: 12px 15px; color: var(--text-sidebar); text-decoration: none; border-radius: 8px; }
        .menu-link:hover { background: rgba(255,255,255,0.1); }
        .menu-link.active { background: var(--accent); color: white; }
        .menu-icon { width: 20px; margin-right: 15px; }
        .sidebar.collapsed .sidebar-logo { display: none; }
        .main-content { margin-left: var(--sidebar-width); min-height: 100vh; }
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed); }
        .topbar { background: var(--bg-primary); border-bottom: 1px solid var(--border-color); padding: 15px 30px; display: flex; justify-content: space-between; }
        .theme-toggle { background: var(--bg-secondary); border: 1px solid var(--border-color); width: 40px; height: 40px; border-radius: 8px; cursor: pointer; }
        .container { padding: 30px; }
        .section { background: var(--bg-primary); padding: 25px; border-radius: 12px; margin-bottom: 25px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: var(--bg-secondary); }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <h1><i class="fas fa-cog"></i> Paramètres</h1>
            <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i></button>
        </div>
        <div class="container">
            <div class="section">
                <h2>⚙️ Paramètres du site</h2>
<p style="color: var(--text-secondary); margin-bottom: 20px;">Configuration et paramètres globaux de LeBonResto</p>
<div style="padding: 20px; background: var(--bg-secondary); border-radius: 8px;">
    <p><strong>Nom du site:</strong> LeBonResto</p>
    <p><strong>Version:</strong> 1.0.0</p>
    <p><strong>Mode:</strong> Production</p>
</div>
            </div>
        </div>
    </div>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('collapsed'); }
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
    </script>
</body>
</html>