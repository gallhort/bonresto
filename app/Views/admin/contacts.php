<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title>Messages de contact - BonResto Admin</title>
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

        .status-tabs { display: flex; gap: 8px; margin-bottom: 24px; }
        .status-tab { padding: 8px 16px; border-radius: 8px; background: var(--bg-secondary); border: 1px solid var(--border-color); cursor: pointer; text-decoration: none; color: var(--text-primary); font-size: 13px; font-weight: 500; }
        .status-tab.active { background: var(--accent); color: white; border-color: var(--accent); }
        .status-tab .count { background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 10px; font-size: 11px; margin-left: 4px; }

        .contact-card { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 12px; transition: box-shadow 0.2s; }
        .contact-card:hover { box-shadow: var(--shadow); }
        .contact-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .contact-sender { font-weight: 600; font-size: 15px; }
        .contact-email { font-size: 13px; color: var(--text-secondary); }
        .contact-subject { display: inline-block; padding: 4px 10px; background: #dbeafe; color: #1e40af; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .contact-date { font-size: 12px; color: var(--text-secondary); }
        .contact-message { font-size: 14px; line-height: 1.6; color: var(--text-primary); padding: 12px; background: var(--bg-secondary); border-radius: 8px; margin-bottom: 12px; }
        .contact-status { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
        .contact-status.new { background: #fee2e2; color: #991b1b; }
        .contact-status.read { background: #fef3c7; color: #92400e; }
        .contact-status.replied { background: #dcfce7; color: #166534; }
        .contact-status.archived { background: #e5e7eb; color: #6b7280; }

        .reply-form { margin-top: 12px; display: flex; gap: 8px; align-items: flex-end; }
        .reply-form textarea { flex: 1; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13px; font-family: inherit; resize: vertical; min-height: 60px; }
        .reply-form button { padding: 8px 16px; background: var(--accent); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; white-space: nowrap; }
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <h1><i class="fas fa-envelope"></i> Messages de contact</h1>
            <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i></button>
        </div>
        <div class="container">
            <div class="status-tabs">
                <a href="/admin/contacts" class="status-tab <?= $currentStatus === 'all' ? 'active' : '' ?>">Tous <span class="count"><?= array_sum($counts) ?></span></a>
                <a href="/admin/contacts?status=new" class="status-tab <?= $currentStatus === 'new' ? 'active' : '' ?>">Nouveaux <span class="count"><?= $counts['new'] ?? 0 ?></span></a>
                <a href="/admin/contacts?status=read" class="status-tab <?= $currentStatus === 'read' ? 'active' : '' ?>">Lus <span class="count"><?= $counts['read'] ?? 0 ?></span></a>
                <a href="/admin/contacts?status=replied" class="status-tab <?= $currentStatus === 'replied' ? 'active' : '' ?>">Répondus <span class="count"><?= $counts['replied'] ?? 0 ?></span></a>
            </div>

            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div style="background:#dcfce7;color:#166534;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:14px;">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (empty($contacts)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucun message</h3>
                    <p>Les messages de contact apparaitront ici.</p>
                </div>
            <?php else: ?>
                <?php foreach ($contacts as $msg): ?>
                    <div class="contact-card">
                        <div class="contact-card-header">
                            <div>
                                <div class="contact-sender"><?= htmlspecialchars($msg['name']) ?></div>
                                <div class="contact-email"><?= htmlspecialchars($msg['email']) ?></div>
                            </div>
                            <div style="text-align:right;">
                                <span class="contact-status <?= $msg['status'] ?>"><?= ucfirst($msg['status']) ?></span>
                                <div class="contact-date"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></div>
                            </div>
                        </div>
                        <div style="margin-bottom:8px;"><span class="contact-subject"><?= htmlspecialchars($msg['subject']) ?></span></div>
                        <div class="contact-message"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                        <?php if (!empty($msg['admin_notes'])): ?>
                            <div style="font-size:13px;color:var(--text-secondary);padding:8px 12px;background:#f0f9ff;border-radius:6px;border-left:3px solid var(--accent);">
                                <strong>Note admin:</strong> <?= nl2br(htmlspecialchars($msg['admin_notes'])) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($msg['status'] !== 'replied'): ?>
                            <form class="reply-form" action="/admin/contacts/reply" method="POST">
                                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                                <textarea name="admin_notes" placeholder="Note/réponse admin..."></textarea>
                                <button type="submit"><i class="fas fa-check"></i> Marquer répondu</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
