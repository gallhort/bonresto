<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Restaurants - BonResto Admin</title>
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
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: var(--bg-secondary); color: var(--text-primary); }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: var(--bg-sidebar); color: var(--text-sidebar); transition: width 0.3s ease; z-index: 1000; overflow-x: hidden; overflow-y: auto; }
        .sidebar.collapsed { width: var(--sidebar-collapsed); }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; }
        .sidebar-logo { font-size: 20px; font-weight: 700; white-space: nowrap; }
        .sidebar-toggle { background: rgba(255,255,255,0.1); border: none; color: var(--text-sidebar); width: 36px; height: 36px; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
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
        .theme-toggle { background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); width: 40px; height: 40px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .theme-toggle:hover { background: var(--accent); color: white; border-color: var(--accent); }
        .container { padding: 30px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 30px; border-bottom: 2px solid var(--border-color); }
        .tab { padding: 12px 24px; background: none; border: none; color: var(--text-secondary); cursor: pointer; font-size: 15px; font-weight: 500; border-bottom: 3px solid transparent; transition: all 0.2s; }
        .tab.active { color: var(--accent); border-bottom-color: var(--accent); }
        .section { background: var(--bg-primary); padding: 25px; border-radius: 12px; box-shadow: var(--shadow); margin-bottom: 25px; border: 1px solid var(--border-color); }
        .section h2 { margin-bottom: 20px; color: var(--text-primary); font-size: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: var(--bg-secondary); font-weight: 600; color: var(--text-primary); font-size: 13px; text-transform: uppercase; }
        td { color: var(--text-primary); }
        .badge { padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-validated { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn:hover { opacity: 0.9; }
        .empty { text-align: center; padding: 50px; color: var(--text-secondary); }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left"><h1><i class="fas fa-utensils"></i> Gestion des Restaurants</h1></div>
            <div class="topbar-right">
                <button class="theme-toggle" onclick="toggleTheme()"><i class="fas fa-moon" id="theme-icon"></i></button>
            </div>
        </div>
        
        <div class="container">
            <div class="tabs">
                <button class="tab <?= ($currentStatus ?? 'all') === 'all' ? 'active' : '' ?>" onclick="location.href='/admin/restaurants?status=all'">
                    Tous (<?= count($restaurants ?? []) ?>)
                </button>
                <button class="tab <?= ($currentStatus ?? '') === 'pending' ? 'active' : '' ?>" onclick="location.href='/admin/restaurants?status=pending'">
                    En attente (<?= count(array_filter($restaurants ?? [], fn($r) => $r['status'] === 'pending')) ?>)
                </button>
                <button class="tab <?= ($currentStatus ?? '') === 'validated' ? 'active' : '' ?>" onclick="location.href='/admin/restaurants?status=validated'">
                    Validés (<?= count(array_filter($restaurants ?? [], fn($r) => $r['status'] === 'validated')) ?>)
                </button>
            </div>
            
            <div class="section">
                <?php if (empty($restaurants)): ?>
                    <div class="empty">
                        <i class="fas fa-utensils" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 15px;"></i>
                        <p>Aucun restaurant trouvé</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Ville</th>
                                <th>Type</th>
                                <th>Note</th>
                                <th>Statut</th>
                                <th>Date création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($restaurants as $resto): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($resto['nom']) ?></strong></td>
                                    <td><?= htmlspecialchars($resto['ville'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($resto['type_cuisine'] ?? 'N/A') ?></td>
                                    <td>⭐ <?= number_format($resto['note_moyenne'] ?? 0, 1) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $resto['status'] ?>">
                                            <?= ucfirst($resto['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($resto['created_at'])) ?></td>
                                    <td>
                                        <?php if ($resto['status'] === 'pending'): ?>
                                            <button class="btn btn-success" onclick="validateRestaurant(<?= $resto['id'] ?>)">Valider</button>
                                            <button class="btn btn-danger" onclick="rejectRestaurant(<?= $resto['id'] ?>)">Rejeter</button>
                                        <?php else: ?>
                                            <button class="btn" style="background: #6b7280; color: white;" onclick="location.href='/restaurant/<?= $resto['id'] ?>'">Voir</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
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
        
        async function validateRestaurant(id) {
            if (!confirm('Valider ce restaurant ?')) return;
            try {
                const response = await fetch('/admin/restaurant/validate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `id=${id}`
                });
                const data = await response.json();
                if (data.success) location.reload();
                else alert(data.message || 'Erreur');
            } catch (error) {
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
                if (data.success) location.reload();
                else alert(data.message || 'Erreur');
            } catch (error) {
                alert('Erreur réseau');
            }
        }
    </script>
</body>
</html>
