<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Journal de modération - BonResto Admin</title>
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
        .topbar { background: var(--bg-primary); border-bottom: 1px solid var(--border-color); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .theme-toggle { background: var(--bg-secondary); border: 1px solid var(--border-color); width: 40px; height: 40px; border-radius: 8px; cursor: pointer; }
        .container { padding: 30px; }
        .section { background: var(--bg-primary); padding: 25px; border-radius: 12px; margin-bottom: 25px; }

        .log-timeline { position: relative; }
        .log-entry { display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--border-color); align-items: flex-start; }
        .log-entry:last-child { border-bottom: none; }
        .log-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 16px; }
        .log-icon.approve { background: #dcfce7; color: #166534; }
        .log-icon.reject { background: #fee2e2; color: #991b1b; }
        .log-icon.delete { background: #fef3c7; color: #92400e; }
        .log-icon.edit { background: #dbeafe; color: #1e40af; }
        .log-content { flex: 1; }
        .log-action { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
        .log-meta { font-size: 12px; color: var(--text-secondary); display: flex; gap: 12px; flex-wrap: wrap; }
        .log-reason { font-size: 13px; color: var(--text-secondary); margin-top: 4px; padding: 6px 10px; background: var(--bg-secondary); border-radius: 6px; }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <h1><i class="fas fa-clipboard-list"></i> Journal de modération</h1>
            <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i></button>
        </div>
        <div class="container">
            <div class="section">
                <?php if (empty($logs)): ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-check"></i>
                        <h3>Aucune action enregistrée</h3>
                        <p>Les actions de modération apparaitront ici automatiquement.</p>
                    </div>
                <?php else: ?>
                    <div class="log-timeline">
                        <?php foreach ($logs as $log):
                            $actionConfig = match($log['action']) {
                                'approve_review' => ['icon' => 'fa-check', 'class' => 'approve', 'label' => 'Avis approuvé'],
                                'reject_review' => ['icon' => 'fa-times', 'class' => 'reject', 'label' => 'Avis rejeté'],
                                'delete_review' => ['icon' => 'fa-trash', 'class' => 'delete', 'label' => 'Avis supprimé'],
                                'approve_restaurant' => ['icon' => 'fa-check-double', 'class' => 'approve', 'label' => 'Restaurant validé'],
                                'reject_restaurant' => ['icon' => 'fa-ban', 'class' => 'reject', 'label' => 'Restaurant rejeté'],
                                'ban_user' => ['icon' => 'fa-user-slash', 'class' => 'reject', 'label' => 'Utilisateur banni'],
                                'edit_restaurant' => ['icon' => 'fa-edit', 'class' => 'edit', 'label' => 'Restaurant modifié'],
                                default => ['icon' => 'fa-cog', 'class' => 'edit', 'label' => $log['action']],
                            };
                        ?>
                            <div class="log-entry">
                                <div class="log-icon <?= $actionConfig['class'] ?>">
                                    <i class="fas <?= $actionConfig['icon'] ?>"></i>
                                </div>
                                <div class="log-content">
                                    <div class="log-action"><?= $actionConfig['label'] ?> #<?= $log['target_id'] ?></div>
                                    <div class="log-meta">
                                        <span><i class="fas fa-user"></i> <?= htmlspecialchars(($log['admin_prenom'] ?? '') . ' ' . ($log['admin_nom'] ?? '')) ?></span>
                                        <span><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></span>
                                        <span><i class="fas fa-tag"></i> <?= ucfirst($log['target_type']) ?></span>
                                    </div>
                                    <?php if (!empty($log['reason'])): ?>
                                        <div class="log-reason"><?= htmlspecialchars($log['reason']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('collapsed'); }
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            html.dataset.theme = html.dataset.theme === 'dark' ? 'light' : 'dark';
            icon.className = html.dataset.theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    </script>
</body>
</html>
