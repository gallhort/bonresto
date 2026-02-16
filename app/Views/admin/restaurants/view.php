<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Modération restaurant' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; }
        
        .admin-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: #34e0a1; text-decoration: none; margin-bottom: 20px; font-weight: 500; }
        
        .back-link:hover { text-decoration: underline; }
        
        .resto-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; }
        
        .resto-header { padding: 24px; border-bottom: 1px solid #e0e0e0; }
        
        .resto-name { font-size: 1.75rem; font-weight: 700; margin-bottom: 8px; }
        
        .resto-meta { color: #666; font-size: 14px; }
        
        .resto-body { padding: 24px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 24px; }
        
        .info-item { }
        
        .info-label { font-weight: 600; color: #333; margin-bottom: 4px; font-size: 14px; }
        
        .info-value { color: #666; }
        
        .description-section { margin-top: 24px; padding-top: 24px; border-top: 1px solid #e0e0e0; }
        
        .section-title { font-weight: 600; margin-bottom: 12px; color: #333; }
        
        .description-text { color: #666; line-height: 1.6; }
        
        .actions-bar { padding: 24px; background: #f8f9fa; border-top: 1px solid #e0e0e0; display: flex; gap: 12px; justify-content: center; }
        
        .btn { padding: 12px 32px; border-radius: 8px; font-size: 16px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.3s; cursor: pointer; border: none; }
        
        .btn-success { background: #4caf50; color: white; }
        .btn-success:hover { background: #45a049; }
        
        .btn-danger { background: #f44336; color: white; }
        .btn-danger:hover { background: #da190b; }
        
        .btn-secondary { background: white; border: 2px solid #ddd; color: #666; }
        .btn-secondary:hover { border-color: #999; }
        
        .badge { padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; display: inline-block; }
        
        .badge-pending { background: #fff3e0; color: #f57c00; }
    </style>
</head>
<body>
    <div class="admin-container">
        <a href="/admin/restaurants/pending" class="back-link">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
        
        <div class="resto-card">
            <div class="resto-header">
                <h1 class="resto-name"><?= htmlspecialchars($restaurant['nom']) ?></h1>
                <div class="resto-meta">
                    <span class="badge badge-pending">En attente de validation</span> •
                    Soumis le <?= date('d/m/Y à H:i', strtotime($restaurant['created_at'])) ?>
                </div>
            </div>
            
            <div class="resto-body">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">📍 Ville</div>
                        <div class="info-value"><?= htmlspecialchars($restaurant['ville']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">🍽️ Type de cuisine</div>
                        <div class="info-value"><?= htmlspecialchars($restaurant['type_cuisine'] ?? '-') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">📞 Téléphone</div>
                        <div class="info-value"><?= htmlspecialchars($restaurant['telephone'] ?? '-') ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">💰 Gamme de prix</div>
                        <div class="info-value"><?= htmlspecialchars($restaurant['price_range'] ?? '-') ?></div>
                    </div>
                    
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">📍 Adresse</div>
                        <div class="info-value"><?= htmlspecialchars($restaurant['adresse'] ?? '-') ?></div>
                    </div>
                    
                    <?php if($restaurant['website']): ?>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <div class="info-label">🌐 Site web</div>
                        <div class="info-value">
                            <a href="<?= htmlspecialchars($restaurant['website']) ?>" target="_blank" style="color: #34e0a1;">
                                <?= htmlspecialchars($restaurant['website']) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if(!empty($restaurant['description'])): ?>
                <div class="description-section">
                    <div class="section-title">📝 Description</div>
                    <div class="description-text"><?= nl2br(htmlspecialchars($restaurant['description'])) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($restaurant['horaires'])): ?>
                <div class="description-section">
                    <div class="section-title">🕐 Horaires</div>
                    <div class="description-text"><?= nl2br(htmlspecialchars($restaurant['horaires'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="actions-bar">
                <form action="/admin/restaurants/<?= $restaurant['id'] ?>/validate" method="POST" style="display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Valider ce restaurant ?')">
                        <i class="fas fa-check"></i> Valider
                    </button>
                </form>
                
                <form action="/admin/restaurants/<?= $restaurant['id'] ?>/reject" method="POST" style="display: inline;">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Rejeter ce restaurant ?')">
                        <i class="fas fa-times"></i> Rejeter
                    </button>
                </form>
                
                <a href="/admin/restaurants/pending" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </div>
    </div>
</body>
</html>
