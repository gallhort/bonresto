<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Suggestions') ?> - BonResto Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        /* Sidebar */
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

        /* Layout */
        .main-content { margin-left: var(--sidebar-width); transition: margin-left 0.3s ease; min-height: 100vh; }
        .sidebar.collapsed ~ .main-content { margin-left: var(--sidebar-collapsed); }

        .topbar { background: var(--bg-primary); border-bottom: 1px solid var(--border-color); padding: 15px 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .topbar h1 { font-size: 22px; color: var(--text-primary); display: flex; align-items: center; gap: 10px; }
        .topbar h1 i { color: var(--accent); }
        .topbar-right { display: flex; gap: 15px; align-items: center; }
        .theme-toggle { background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); width: 40px; height: 40px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: all 0.2s; }
        .theme-toggle:hover { background: var(--accent); color: white; border-color: var(--accent); }

        .container { padding: 30px; }

        /* Stats cards */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: var(--bg-primary); padding: 18px; border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); text-align: center; }
        .stat-card .number { font-size: 28px; font-weight: 700; color: var(--text-primary); }
        .stat-card .label { font-size: 13px; color: var(--text-secondary); margin-top: 4px; }
        .stat-card.pending .number { color: #d97706; }
        .stat-card.approved .number { color: #059669; }
        .stat-card.rejected .number { color: #dc2626; }
        .stat-card.duplicate .number { color: #7c3aed; }

        /* Status tabs */
        .status-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
        .status-tab { padding: 8px 16px; border-radius: 8px; background: var(--bg-primary); border: 1px solid var(--border-color); cursor: pointer; text-decoration: none; color: var(--text-primary); font-size: 13px; font-weight: 500; transition: all 0.2s; }
        .status-tab:hover { border-color: var(--accent); color: var(--accent); }
        .status-tab.active { background: var(--accent); color: white; border-color: var(--accent); }
        .status-tab .count { background: rgba(0,0,0,0.1); padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 4px; }
        .status-tab.active .count { background: rgba(255,255,255,0.25); }

        /* Suggestion cards */
        .suggestion-card { background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 14px; transition: box-shadow 0.2s; }
        .suggestion-card:hover { box-shadow: var(--shadow); }
        .suggestion-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; gap: 16px; }
        .suggestion-card-header-left { flex: 1; min-width: 0; }
        .suggestion-card-header-right { text-align: right; flex-shrink: 0; }

        .suggestion-restaurant-name { font-size: 17px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px; }
        .suggestion-meta { display: flex; flex-wrap: wrap; gap: 12px; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; }
        .suggestion-meta i { width: 14px; text-align: center; margin-right: 3px; }

        .suggestion-user { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary); margin-bottom: 10px; }
        .suggestion-user-avatar { width: 28px; height: 28px; border-radius: 50%; background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0; }

        .suggestion-description { font-size: 14px; line-height: 1.6; color: var(--text-primary); padding: 12px; background: var(--bg-secondary); border-radius: 8px; margin-bottom: 10px; }
        .suggestion-pourquoi { font-size: 13px; line-height: 1.5; color: var(--text-secondary); padding: 10px 12px; background: var(--bg-secondary); border-left: 3px solid var(--accent); border-radius: 0 8px 8px 0; margin-bottom: 10px; }
        .suggestion-pourquoi strong { color: var(--text-primary); }

        .suggestion-date { font-size: 12px; color: var(--text-secondary); }

        /* Status badges */
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; white-space: nowrap; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-duplicate { background: #ede9fe; color: #5b21b6; }

        /* Admin note display */
        .admin-note { font-size: 13px; color: var(--text-secondary); padding: 10px 12px; background: #fef2f2; border-left: 3px solid #ef4444; border-radius: 0 8px 8px 0; margin-top: 10px; }
        [data-theme="dark"] .admin-note { background: rgba(239, 68, 68, 0.1); }
        .admin-note strong { color: #991b1b; }
        [data-theme="dark"] .admin-note strong { color: #fca5a5; }

        /* Restaurant link */
        .restaurant-link { display: inline-flex; align-items: center; gap: 5px; font-size: 13px; color: var(--accent); text-decoration: none; margin-top: 8px; font-weight: 500; }
        .restaurant-link:hover { text-decoration: underline; }

        /* Action buttons */
        .suggestion-actions { display: flex; gap: 10px; margin-top: 14px; align-items: flex-start; flex-wrap: wrap; }
        .btn { padding: 8px 16px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-approve { background: #10b981; color: white; }
        .btn-approve:hover { background: #059669; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-reject:hover { background: #dc2626; }
        .btn-duplicate { background: #7c3aed; color: white; }
        .btn-duplicate:hover { background: #6d28d9; }

        /* Reject form */
        .reject-form { display: flex; gap: 8px; align-items: center; flex: 1; min-width: 250px; }
        .reject-form input[type="text"] { flex: 1; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13px; font-family: inherit; background: var(--bg-primary); color: var(--text-primary); min-width: 150px; }
        .reject-form input[type="text"]::placeholder { color: var(--text-secondary); }

        /* Empty state */
        .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.3; display: block; }
        .empty-state h3 { margin-bottom: 8px; color: var(--text-primary); }

        /* Flash message */
        .flash-success { background: #dcfce7; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .flash-error { background: #fee2e2; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; display: flex; align-items: center; gap: 8px; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.mobile-open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { padding: 15px; }
            .container { padding: 15px; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .suggestion-card-header { flex-direction: column; }
            .suggestion-actions { flex-direction: column; }
            .reject-form { min-width: 100%; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h1><i class="fas fa-lightbulb"></i> Suggestions de restaurants</h1>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()" title="Changer de theme"><i class="fas fa-moon" id="theme-icon"></i></button>
            </div>
        </div>

        <div class="container">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="flash-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="flash-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Stats overview -->
            <?php
                $totalCount = ($statusCounts['pending'] ?? 0)
                            + ($statusCounts['approved'] ?? 0)
                            + ($statusCounts['rejected'] ?? 0)
                            + ($statusCounts['duplicate'] ?? 0);
            ?>
            <div class="stats-row">
                <div class="stat-card">
                    <div class="number"><?= (int)$totalCount ?></div>
                    <div class="label">Total suggestions</div>
                </div>
                <div class="stat-card pending">
                    <div class="number"><?= (int)($statusCounts['pending'] ?? 0) ?></div>
                    <div class="label">En attente</div>
                </div>
                <div class="stat-card approved">
                    <div class="number"><?= (int)($statusCounts['approved'] ?? 0) ?></div>
                    <div class="label">Approuvees</div>
                </div>
                <div class="stat-card rejected">
                    <div class="number"><?= (int)($statusCounts['rejected'] ?? 0) ?></div>
                    <div class="label">Rejetees</div>
                </div>
                <div class="stat-card duplicate">
                    <div class="number"><?= (int)($statusCounts['duplicate'] ?? 0) ?></div>
                    <div class="label">Doublons</div>
                </div>
            </div>

            <!-- Status filter tabs -->
            <div class="status-tabs">
                <a href="/admin/suggestions" class="status-tab <?= empty($currentStatus) ? 'active' : '' ?>">
                    Toutes <span class="count"><?= (int)$totalCount ?></span>
                </a>
                <a href="/admin/suggestions?status=pending" class="status-tab <?= ($currentStatus ?? '') === 'pending' ? 'active' : '' ?>">
                    <i class="fas fa-clock"></i> En attente <span class="count"><?= (int)($statusCounts['pending'] ?? 0) ?></span>
                </a>
                <a href="/admin/suggestions?status=approved" class="status-tab <?= ($currentStatus ?? '') === 'approved' ? 'active' : '' ?>">
                    <i class="fas fa-check"></i> Approuvees <span class="count"><?= (int)($statusCounts['approved'] ?? 0) ?></span>
                </a>
                <a href="/admin/suggestions?status=rejected" class="status-tab <?= ($currentStatus ?? '') === 'rejected' ? 'active' : '' ?>">
                    <i class="fas fa-times"></i> Rejetees <span class="count"><?= (int)($statusCounts['rejected'] ?? 0) ?></span>
                </a>
                <a href="/admin/suggestions?status=duplicate" class="status-tab <?= ($currentStatus ?? '') === 'duplicate' ? 'active' : '' ?>">
                    <i class="fas fa-copy"></i> Doublons <span class="count"><?= (int)($statusCounts['duplicate'] ?? 0) ?></span>
                </a>
            </div>

            <!-- Suggestions list -->
            <?php if (empty($suggestions)): ?>
                <div class="empty-state">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Aucune suggestion</h3>
                    <p>
                        <?php if (!empty($currentStatus)): ?>
                            Aucune suggestion avec le statut "<?= htmlspecialchars($currentStatus) ?>".
                        <?php else: ?>
                            Les suggestions de restaurants soumises par les utilisateurs apparaitront ici.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="suggestion-card">
                        <div class="suggestion-card-header">
                            <div class="suggestion-card-header-left">
                                <!-- Restaurant info -->
                                <div class="suggestion-restaurant-name">
                                    <?= htmlspecialchars($suggestion['nom']) ?>
                                </div>
                                <div class="suggestion-meta">
                                    <?php if (!empty($suggestion['ville'])): ?>
                                        <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($suggestion['ville']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($suggestion['type_cuisine'])): ?>
                                        <span><i class="fas fa-utensils"></i> <?= htmlspecialchars($suggestion['type_cuisine']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($suggestion['adresse'])): ?>
                                        <span><i class="fas fa-location-dot"></i> <?= htmlspecialchars($suggestion['adresse']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- User info -->
                                <div class="suggestion-user">
                                    <div class="suggestion-user-avatar">
                                        <?= strtoupper(mb_substr($suggestion['prenom'] ?? '?', 0, 1)) ?>
                                    </div>
                                    <span>
                                        <strong><?= htmlspecialchars(($suggestion['prenom'] ?? '') . ' ' . ($suggestion['user_nom'] ?? '')) ?></strong>
                                        &middot;
                                        <a href="mailto:<?= htmlspecialchars($suggestion['email'] ?? '') ?>" style="color: var(--accent); text-decoration: none;"><?= htmlspecialchars($suggestion['email'] ?? '') ?></a>
                                    </span>
                                </div>
                            </div>
                            <div class="suggestion-card-header-right">
                                <?php
                                    $status = $suggestion['status'] ?? 'pending';
                                    $badgeClass = 'badge-pending';
                                    $badgeLabel = 'En attente';
                                    $badgeIcon = 'fa-clock';
                                    if ($status === 'approved') {
                                        $badgeClass = 'badge-approved';
                                        $badgeLabel = 'Approuvee';
                                        $badgeIcon = 'fa-check-circle';
                                    } elseif ($status === 'rejected') {
                                        $badgeClass = 'badge-rejected';
                                        $badgeLabel = 'Rejetee';
                                        $badgeIcon = 'fa-times-circle';
                                    } elseif ($status === 'duplicate') {
                                        $badgeClass = 'badge-duplicate';
                                        $badgeLabel = 'Doublon';
                                        $badgeIcon = 'fa-copy';
                                    }
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <i class="fas <?= $badgeIcon ?>"></i> <?= $badgeLabel ?>
                                </span>
                                <div class="suggestion-date">
                                    <?= date('d/m/Y H:i', strtotime($suggestion['created_at'])) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if (!empty($suggestion['description'])): ?>
                            <div class="suggestion-description">
                                <?= nl2br(htmlspecialchars($suggestion['description'])) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Pourquoi -->
                        <?php if (!empty($suggestion['pourquoi'])): ?>
                            <div class="suggestion-pourquoi">
                                <strong><i class="fas fa-quote-left"></i> Pourquoi ce restaurant :</strong><br>
                                <?= nl2br(htmlspecialchars($suggestion['pourquoi'])) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Approved: show link to restaurant -->
                        <?php if ($status === 'approved' && !empty($suggestion['restaurant_id'])): ?>
                            <a href="/restaurant/<?= (int)$suggestion['restaurant_id'] ?>" class="restaurant-link" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Voir le restaurant cree (#<?= (int)$suggestion['restaurant_id'] ?>)
                            </a>
                        <?php endif; ?>

                        <!-- Rejected: show admin note -->
                        <?php if ($status === 'rejected' && !empty($suggestion['admin_note'])): ?>
                            <div class="admin-note">
                                <strong><i class="fas fa-comment-dots"></i> Note admin :</strong>
                                <?= nl2br(htmlspecialchars($suggestion['admin_note'])) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Duplicate: show admin note -->
                        <?php if ($status === 'duplicate' && !empty($suggestion['admin_note'])): ?>
                            <div class="admin-note" style="border-left-color: #7c3aed; background: #f5f3ff;">
                                <strong style="color: #5b21b6;"><i class="fas fa-comment-dots"></i> Note admin :</strong>
                                <?= nl2br(htmlspecialchars($suggestion['admin_note'])) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Actions for pending suggestions -->
                        <?php if ($status === 'pending'): ?>
                            <div class="suggestion-actions">
                                <!-- Approve -->
                                <form action="/admin/suggestions/<?= (int)$suggestion['id'] ?>/approve" method="POST" style="display:inline;">
                                    <button type="submit" class="btn btn-approve" onclick="return confirm('Approuver cette suggestion ?')">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>

                                <!-- Reject with note -->
                                <form action="/admin/suggestions/<?= (int)$suggestion['id'] ?>/reject" method="POST" class="reject-form">
                                    <input type="text" name="admin_note" placeholder="Raison du rejet (optionnel)..." />
                                    <button type="submit" class="btn btn-reject" onclick="return confirm('Rejeter cette suggestion ?')">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </form>

                                <!-- Mark as duplicate -->
                                <form action="/admin/suggestions/<?= (int)$suggestion['id'] ?>/reject" method="POST" style="display:inline;">
                                    <input type="hidden" name="admin_note" value="Doublon detecte" />
                                    <input type="hidden" name="mark_duplicate" value="1" />
                                    <button type="submit" class="btn btn-duplicate" onclick="return confirm('Marquer comme doublon ?')">
                                        <i class="fas fa-copy"></i> Doublon
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebar-collapsed', sidebar.classList.contains('collapsed'));
        }
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }

        // Dark mode toggle
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            localStorage.setItem('theme', newTheme);
        }
        // Restore saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('theme-icon').className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    </script>
</body>
</html>
