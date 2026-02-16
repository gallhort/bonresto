<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Restaurants en attente' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; }
        
        .admin-container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        
        .admin-header { background: white; padding: 24px 32px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        
        .admin-title { font-size: 1.75rem; font-weight: 700; }
        
        .admin-nav { display: flex; gap: 12px; }
        
        .nav-link { padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.2s; }
        
        .nav-link.active { background: #34e0a1; color: white; }
        
        .nav-link:not(.active) { background: #f0f0f0; color: #666; }
        
        .nav-link:not(.active):hover { background: #e0e0e0; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px; }
        
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        .stat-number { font-size: 2rem; font-weight: 700; color: #34e0a1; margin-bottom: 4px; }
        
        .stat-label { color: #666; font-size: 14px; }
        
        .table-container { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        
        table { width: 100%; border-collapse: collapse; }
        
        thead { background: #f8f9fa; }
        
        th { padding: 16px; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; }
        
        td { padding: 16px; border-bottom: 1px solid #f0f0f0; }
        
        tr:hover { background: #f8f9fa; }
        
        .resto-name { font-weight: 600; color: #1a1a1a; }
        
        .resto-meta { font-size: 14px; color: #666; margin-top: 4px; }
        
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        
        .badge-pending { background: #fff3e0; color: #f57c00; }
        
        .badge-validated { background: #e8f5e9; color: #388e3c; }
        
        .badge-rejected { background: #ffebee; color: #c62828; }
        
        .btn { padding: 8px 16px; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none; display: inline-block; transition: all 0.2s; cursor: pointer; border: none; }
        
        .btn-primary { background: #34e0a1; color: white; }
        .btn-primary:hover { background: #2cc890; }
        
        .btn-secondary { background: #f0f0f0; color: #666; }
        .btn-secondary:hover { background: #e0e0e0; }
        
        .empty-state { text-align: center; padding: 60px 20px; }
        
        .empty-icon { font-size: 64px; color: #ddd; margin-bottom: 16px; }
        
        .empty-text { color: #999; font-size: 1.1rem; }
        
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #e8f5e9; border-left: 4px solid #4caf50; color: #388e3c; }
        .alert-error { background: #ffebee; border-left: 4px solid #f44336; color: #c62828; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="admin-title">🛡️ Admin - Restaurants</h1>
            <div class="admin-nav">
                <a href="/admin/restaurants/pending" class="nav-link active">En attente</a>
                <a href="/admin/restaurants/validated" class="nav-link">Validés</a>
                <a href="/admin" class="nav-link">Dashboard</a>
            </div>
        </div>
        
        <?php if(isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <?php unset($_SESSION['flash_success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($_SESSION['flash_error']) ?>
                <?php unset($_SESSION['flash_error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= count($restaurants) ?></div>
                <div class="stat-label">En attente</div>
            </div>
        </div>
        
        <div class="table-container">
            <?php if(count($restaurants) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Restaurant</th>
                            <th>Type</th>
                            <th>Ville</th>
                            <th>Date soumission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($restaurants as $resto): ?>
                            <tr>
                                <td>
                                    <div class="resto-name"><?= htmlspecialchars($resto['nom']) ?></div>
                                    <div class="resto-meta">
<?php if(isset($resto['telephone']) && $resto['telephone']): ?>
    <i class="fas fa-phone"></i> <?= htmlspecialchars($resto['telephone']) ?>
<?php endif; ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($resto['type_cuisine'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($resto['ville']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($resto['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/restaurants/<?= $resto['id'] ?>/view" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">✅</div>
                    <p class="empty-text">Aucun restaurant en attente de validation</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
