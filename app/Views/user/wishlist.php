<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Ma wishlist' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; }
        
        .wishlist-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .wishlist-header { background: white; padding: 32px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); text-align: center; }
        
        .wishlist-header h1 { font-size: 2rem; margin-bottom: 8px; }
        
        .wishlist-count { color: #666; font-size: 1.1rem; }
        
        .wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        
        .resto-card { background: white; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; transition: all 0.2s; position: relative; }
        
        .resto-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); transform: translateY(-2px); }
        
        .resto-img-wrap { position: relative; width: 100%; height: 180px; overflow: hidden; background: #f0f0f0; }
        
        .resto-img { width: 100%; height: 100%; object-fit: cover; }
        
        .resto-no-photo { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 48px; }
        
        .wishlist-heart { position: absolute; top: 12px; right: 12px; width: 44px; height: 44px; background: rgba(255,255,255,0.95); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; border: none; }
        
        .wishlist-heart:hover { background: #fff; transform: scale(1.1); }
        
        .wishlist-heart i { color: #e74c3c; font-size: 20px; }
        
        .resto-content { padding: 16px; }
        
        .resto-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; gap: 8px; }
        
        .resto-name { font-size: 1.1rem; font-weight: 600; color: #1a1a1a; flex: 1; }
        
        .rating-circle { display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; background: #34e0a1; color: white; border-radius: 50%; font-weight: 700; font-size: 14px; }
        
        .resto-type { color: #666; font-size: 14px; margin-bottom: 8px; }
        
        .resto-location { display: flex; align-items: center; gap: 6px; color: #666; font-size: 14px; margin-bottom: 8px; }
        
        .resto-meta { display: flex; align-items: center; gap: 12px; padding-top: 12px; border-top: 1px solid #e0e0e0; font-size: 14px; }
        
        .resto-added { color: #999; font-size: 13px; margin-top: 8px; }
        
        .empty-state { text-align: center; padding: 80px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        .empty-icon { font-size: 64px; color: #ddd; margin-bottom: 24px; }
        
        .empty-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 12px; color: #333; }
        
        .empty-text { color: #666; margin-bottom: 24px; }
        
        .btn { padding: 12px 32px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; border: none; transition: all 0.3s; text-decoration: none; display: inline-block; }
        
        .btn-primary { background: #34e0a1; color: white; }
        .btn-primary:hover { background: #2cc890; }
        
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .alert-success { background: #e8f5e9; border-left: 4px solid #4caf50; color: #388e3c; }
        .alert-error { background: #ffebee; border-left: 4px solid #f44336; color: #c62828; }
        
        @media (max-width: 768px) {
            .wishlist-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="wishlist-container">
        <!-- HEADER -->
        <div class="wishlist-header">
            <h1>❤️ Ma wishlist</h1>
            <p class="wishlist-count"><?= count($wishlist) ?> restaurant<?= count($wishlist) > 1 ? 's' : '' ?> favori<?= count($wishlist) > 1 ? 's' : '' ?></p>
        </div>
        
        <!-- ALERT -->
        <div id="alertContainer"></div>
        
        <!-- GRID -->
        <?php if(count($wishlist) > 0): ?>
            <div class="wishlist-grid">
                <?php foreach($wishlist as $resto): ?>
                    <div class="resto-card" id="card-<?= $resto['id'] ?>">
                        <div class="resto-img-wrap">
                            <?php if(!empty($resto['main_photo'])): ?>
                                <img loading="lazy" src="/<?= htmlspecialchars($resto['main_photo']) ?>" class="resto-img" alt="<?= htmlspecialchars($resto['nom'] ?? 'Restaurant') ?>">
                            <?php else: ?>
                                <div class="resto-no-photo">🍽️</div>
                            <?php endif; ?>
                            
                            <button class="wishlist-heart" onclick="removeFromWishlist(<?= $resto['id'] ?>)">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="resto-content">
                            <div class="resto-header">
                                <a href="/restaurant/<?= $resto['id'] ?>" class="resto-name" style="text-decoration: none; color: inherit;">
                                    <?= htmlspecialchars($resto['nom']) ?>
                                </a>
                                <?php if($resto['note_moyenne'] > 0): ?>
                                    <div class="rating-circle"><?= number_format($resto['note_moyenne'], 1) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="resto-type"><?= htmlspecialchars($resto['type_cuisine'] ?? 'Restaurant') ?></div>
                            
                            <div class="resto-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($resto['ville']) ?>
                            </div>
                            
                            <div class="resto-meta">
                                <span><?= htmlspecialchars($resto['price_range'] ?? '€€') ?></span>
                                <?php if($resto['nb_avis'] > 0): ?>
                                    <span><?= $resto['nb_avis'] ?> avis</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="resto-added">
                                Ajouté le <?= date('d/m/Y', strtotime($resto['added_at'])) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">❤️</div>
                <h2 class="empty-title">Votre wishlist est vide</h2>
                <p class="empty-text">Ajoutez vos restaurants préférés pour les retrouver facilement !</p>
                <a href="/" class="btn btn-primary">
                    <i class="fas fa-search"></i> Découvrir des restaurants
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        async function removeFromWishlist(restaurantId) {
            const formData = new FormData();
            formData.append('restaurant_id', restaurantId);
            
            try {
                const response = await fetch('/wishlist/remove', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Retirer la card avec animation
                    const card = document.getElementById(`card-${restaurantId}`);
                    if (card) {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            card.remove();
                            
                            // Mettre à jour le compteur
                            updateCount();
                            
                            // Afficher empty state si plus rien
                            checkIfEmpty();
                        }, 300);
                    }
                    
                    // Afficher message succès
                    showAlert(data.message, 'success');
                } else {
                    showAlert(data.message, 'error');
                }
            } catch (error) {
                showAlert('Erreur de connexion', 'error');
            }
        }
        
        function updateCount() {
            const remaining = document.querySelectorAll('.resto-card').length;
            const countEl = document.querySelector('.wishlist-count');
            if (countEl) {
                countEl.textContent = `${remaining} restaurant${remaining > 1 ? 's' : ''} favori${remaining > 1 ? 's' : ''}`;
            }
        }
        
        function checkIfEmpty() {
            const cards = document.querySelectorAll('.resto-card');
            if (cards.length === 0) {
                const grid = document.querySelector('.wishlist-grid');
                if (grid) {
                    grid.innerHTML = `
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <div class="empty-icon">❤️</div>
                            <h2 class="empty-title">Votre wishlist est vide</h2>
                            <p class="empty-text">Ajoutez vos restaurants préférés pour les retrouver facilement !</p>
                            <a href="/" class="btn btn-primary">
                                <i class="fas fa-search"></i> Découvrir des restaurants
                            </a>
                        </div>
                    `;
                }
            }
        }
        
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
            
            alertContainer.innerHTML = `<div class="alert ${alertClass}">${message}</div>`;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 3000);
        }
    </script>
</body>
</html>
