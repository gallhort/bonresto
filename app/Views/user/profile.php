<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Mon profil' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; }
        
        .profile-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .profile-header { background: white; padding: 32px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        .profile-top { display: flex; align-items: center; gap: 24px; margin-bottom: 24px; }
        
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #34e0a1 0%, #2cc890 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 40px; font-weight: 700; flex-shrink: 0; }
        
        .profile-info h1 { font-size: 1.75rem; margin-bottom: 8px; }
        
        .profile-meta { color: #666; font-size: 14px; }
        
        .profile-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; padding-top: 24px; border-top: 1px solid #e0e0e0; }
        
        .stat-card { text-align: center; padding: 16px; background: #f8f9fa; border-radius: 8px; }
        
        .stat-number { font-size: 2rem; font-weight: 700; color: #34e0a1; margin-bottom: 4px; }
        
        .stat-label { color: #666; font-size: 14px; }
        
        .profile-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 24px; }
        
        .profile-card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        
        .card-title { font-size: 1.25rem; font-weight: 600; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #34e0a1; }
        
        .form-group { margin-bottom: 20px; }
        
        .form-group label { display: block; font-weight: 500; margin-bottom: 8px; color: #333; }
        
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; }
        
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #34e0a1; box-shadow: 0 0 0 3px rgba(52,224,161,0.1); }
        
        .btn { padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; border: none; transition: all 0.3s; }
        
        .btn-primary { background: #34e0a1; color: white; }
        .btn-primary:hover { background: #2cc890; }
        
        .btn-secondary { background: white; border: 2px solid #ddd; color: #666; }
        .btn-secondary:hover { border-color: #999; color: #333; }
        
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #e8f5e9; border-left: 4px solid #4caf50; color: #388e3c; }
        .alert-error { background: #ffebee; border-left: 4px solid #f44336; color: #c62828; }
        
        .review-item { padding: 16px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 12px; }
        
        .review-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        
        .review-resto { font-weight: 600; color: #1a1a1a; }
        
        .review-rating { color: #34e0a1; font-size: 18px; }
        
        .review-meta { display: flex; gap: 16px; font-size: 14px; color: #666; margin-bottom: 12px; }
        
        .review-status { padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-pending { background: #fff3e0; color: #f57c00; }
        .status-approved { background: #e8f5e9; color: #388e3c; }
        .status-rejected { background: #ffebee; color: #c62828; }
        
        .review-text { color: #333; line-height: 1.5; }
        
        .no-reviews { text-align: center; padding: 40px; color: #999; }
        
        @media (max-width: 768px) {
            .profile-grid { grid-template-columns: 1fr; }
            .profile-top { flex-direction: column; text-align: center; }
            .profile-stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <!-- HEADER -->
        <div class="profile-header">
            <div class="profile-top">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                </div>
                <div class="profile-info">
                    <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
                    <div class="profile-meta">
                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?> •
                        <i class="fas fa-calendar"></i> Membre depuis <?= date('F Y', strtotime($user['created_at'])) ?>
                        <?php if($user['is_admin']): ?>
                        • <i class="fas fa-shield-alt"></i> Administrateur
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="profile-stats">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['nb_avis'] ?? 0 ?></div>
                    <div class="stat-label">Avis publiés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['nb_photos'] ?? 0 ?></div>
                    <div class="stat-label">Photos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['nb_restaurants_visites'] ?? 0 ?></div>
                    <div class="stat-label">Restaurants visités</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= strtoupper($stats['niveau'] ?? 'bronze') ?></div>
                    <div class="stat-label">Niveau</div>
                </div>
            </div>
        </div>
        
        <!-- GRID -->
        <div class="profile-grid">
            <!-- SIDEBAR -->
            <div>
                <!-- MODIFIER PROFIL -->
                <div class="profile-card">
                    <h2 class="card-title">👤 Informations</h2>
                    
                    <div id="profileAlert"></div>
                    
                    <form id="profileForm">
                        <div class="form-group">
                            <label for="prenom">Prénom</label>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nom">Nom</label>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="genre">Genre</label>
                            <select id="genre" name="genre">
                                <option value="homme" <?= $user['genre'] == 'homme' ? 'selected' : '' ?>>Homme</option>
                                <option value="femme" <?= $user['genre'] == 'femme' ? 'selected' : '' ?>>Femme</option>
                                <option value="autre" <?= $user['genre'] == 'autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-save"></i> Enregistrer
                        </button>
                    </form>
                </div>
                
                <!-- CHANGER MOT DE PASSE -->
                <div class="profile-card" style="margin-top: 24px;">
                    <h2 class="card-title">🔒 Mot de passe</h2>
                    
                    <div id="passwordAlert"></div>
                    
                    <form id="passwordForm">
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmer</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-key"></i> Changer le mot de passe
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- MES AVIS -->
            <div class="profile-card">
                <h2 class="card-title">⭐ Mes avis (<?= count($reviews) ?>)</h2>
                
                <?php if(count($reviews) > 0): ?>
                    <?php foreach($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-resto">
                                    <i class="fas fa-utensils"></i>
                                    <?= htmlspecialchars($review['restaurant_nom']) ?>
                                </div>
                                <div class="review-rating">
                                    <?= str_repeat('★', $review['note_globale']) ?><?= str_repeat('☆', 10 - $review['note_globale']) ?>
                                    <?= $review['note_globale'] ?>/10
                                </div>
                            </div>
                            
                            <div class="review-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($review['restaurant_ville']) ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($review['created_at'])) ?></span>
                                <?php if($review['visit_month']): ?>
                                <span><i class="fas fa-clock"></i> Visite : <?= $review['visit_month'] ?> <?= $review['visit_year'] ?></span>
                                <?php endif; ?>
                                <?php if($review['trip_type']): ?>
                                <span><i class="fas fa-users"></i> <?= $review['trip_type'] ?></span>
                                <?php endif; ?>
                                <span class="review-status status-<?= $review['status'] ?>">
                                    <?= $review['status'] == 'pending' ? '⏳ En attente' : ($review['status'] == 'approved' ? '✅ Publié' : '❌ Rejeté') ?>
                                </span>
                            </div>
                            
                            <?php if($review['title']): ?>
                                <div style="font-weight: 600; margin-bottom: 8px;"><?= htmlspecialchars($review['title']) ?></div>
                            <?php endif; ?>
                            
                            <div class="review-text"><?= htmlspecialchars($review['message']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="fas fa-star" style="font-size: 48px; color: #ddd; margin-bottom: 16px;"></i>
                        <p>Vous n'avez pas encore laissé d'avis</p>
                        <a href="/" class="btn btn-primary" style="margin-top: 16px; display: inline-block;">
                            Découvrir des restaurants
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Mise à jour du profil
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const alertDiv = document.getElementById('profileAlert');
            
            try {
                const response = await fetch('/profil', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alertDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    setTimeout(() => alertDiv.innerHTML = '', 3000);
                } else {
                    alertDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                }
            } catch (error) {
                alertDiv.innerHTML = `<div class="alert alert-error">Erreur de connexion</div>`;
            }
        });
        
        // Changement de mot de passe
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const alertDiv = document.getElementById('passwordAlert');
            
            try {
                const response = await fetch('/profil/password', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alertDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    this.reset();
                    setTimeout(() => alertDiv.innerHTML = '', 3000);
                } else {
                    alertDiv.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
                }
            } catch (error) {
                alertDiv.innerHTML = `<div class="alert alert-error">Erreur de connexion</div>`;
            }
        });
    </script>
</body>
</html>
