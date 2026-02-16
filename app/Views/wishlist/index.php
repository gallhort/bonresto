<?php
/**
 * PAGE MES FAVORIS
 */
$favorites = $favorites ?? [];
$total = $total ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Mes Favoris') ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #1a1a1a;
            --accent: #00875A;
            --accent-light: #00a86b;
            --accent-bg: #e8f5f0;
            --warning: #f59e0b;
            --danger: #ef4444;
            --gray-50: #fafafa;
            --gray-100: #f4f4f5;
            --gray-200: #e4e4e7;
            --gray-300: #d4d4d8;
            --gray-400: #a1a1aa;
            --gray-500: #71717a;
            --gray-600: #52525b;
            --gray-700: #3f3f46;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow: 0 4px 24px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 48px rgba(0,0,0,0.12);
            --font-display: 'Playfair Display', Georgia, serif;
            --font-body: 'DM Sans', -apple-system, sans-serif;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-body); background: var(--gray-50); color: var(--primary); line-height: 1.6; }
        a { color: inherit; text-decoration: none; }
        button { font-family: inherit; cursor: pointer; border: none; background: none; }
        img { max-width: 100%; height: auto; }

        /* HEADER */
        .page-header {
            background: white;
            border-bottom: 1px solid var(--gray-200);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .back-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .back-btn:hover { background: var(--gray-200); }
        .page-title {
            font-family: var(--font-display);
            font-size: 28px;
            font-weight: 600;
        }
        .favorites-count {
            background: var(--accent);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        /* MAIN */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        .empty-icon {
            width: 120px;
            height: 120px;
            background: var(--gray-100);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }
        .empty-icon i {
            font-size: 48px;
            color: var(--gray-400);
        }
        .empty-title {
            font-family: var(--font-display);
            font-size: 24px;
            margin-bottom: 12px;
        }
        .empty-text {
            color: var(--gray-500);
            margin-bottom: 24px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-explore {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            background: var(--accent);
            color: white;
            border-radius: var(--radius-sm);
            font-weight: 600;
            transition: var(--transition);
        }
        .btn-explore:hover {
            background: var(--accent-light);
            transform: translateY(-2px);
        }

        /* FAVORITES GRID */
        .favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 24px;
        }
        @media (max-width: 480px) {
            .favorites-grid {
                grid-template-columns: 1fr;
            }
        }

        /* FAVORITE CARD */
        .favorite-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }
        .favorite-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .favorite-card.removing {
            opacity: 0.5;
            transform: scale(0.95);
        }

        .card-photo {
            height: 200px;
            background: var(--gray-200);
            position: relative;
            overflow: hidden;
        }
        .card-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .favorite-card:hover .card-photo img {
            transform: scale(1.05);
        }
        .card-photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: rgba(255,255,255,0.3);
            font-size: 60px;
        }

        .card-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--accent);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .card-wishlist-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #e74c3c;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }
        .card-wishlist-btn:hover {
            transform: scale(1.1);
        }
        .card-wishlist-btn.active i {
            color: #e74c3c;
        }

        .card-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-name {
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .card-name a:hover {
            color: var(--accent);
        }

        .card-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .rating-score {
            font-weight: 700;
            color: var(--primary);
        }
        .rating-stars {
            display: flex;
            gap: 2px;
        }
        .rating-stars i {
            color: var(--warning);
            font-size: 14px;
        }
        .rating-stars i.empty {
            color: var(--gray-300);
        }
        .rating-count {
            color: var(--gray-500);
            font-size: 14px;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            font-size: 14px;
            color: var(--gray-600);
            margin-bottom: 16px;
        }
        .card-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card-address {
            font-size: 14px;
            color: var(--gray-500);
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 16px;
        }
        .card-address i {
            margin-top: 3px;
            color: var(--accent);
        }

        .card-actions {
            display: flex;
            gap: 12px;
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid var(--gray-100);
        }
        .card-btn {
            flex: 1;
            padding: 12px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
        }
        .card-btn-primary {
            background: var(--accent);
            color: white;
        }
        .card-btn-primary:hover {
            background: var(--accent-light);
        }
        .card-btn-secondary {
            background: var(--gray-100);
            color: var(--primary);
        }
        .card-btn-secondary:hover {
            background: var(--gray-200);
        }

        .added-date {
            font-size: 12px;
            color: var(--gray-400);
            text-align: right;
            margin-top: 12px;
        }

        /* ANIMATIONS */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .favorite-card {
            animation: fadeIn 0.4s ease forwards;
        }
        .favorite-card:nth-child(1) { animation-delay: 0.05s; }
        .favorite-card:nth-child(2) { animation-delay: 0.1s; }
        .favorite-card:nth-child(3) { animation-delay: 0.15s; }
        .favorite-card:nth-child(4) { animation-delay: 0.2s; }
        .favorite-card:nth-child(5) { animation-delay: 0.25s; }
        .favorite-card:nth-child(6) { animation-delay: 0.3s; }
    </style>
</head>
<body>

<header class="page-header">
    <div class="header-content">
        <a href="/" class="back-btn">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="page-title">Mes Favoris</h1>
        <?php if ($total > 0): ?>
            <span class="favorites-count"><?= $total ?></span>
        <?php endif; ?>
    </div>
</header>

<main class="main-container">
    
    <?php if (empty($favorites)): ?>
        <!-- EMPTY STATE -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="far fa-heart"></i>
            </div>
            <h2 class="empty-title">Aucun favori pour le moment</h2>
            <p class="empty-text">
                Explorez nos restaurants et ajoutez vos coups de cœur en cliquant sur le ❤️
            </p>
            <a href="/search" class="btn-explore">
                <i class="fas fa-search"></i>
                Explorer les restaurants
            </a>
        </div>
    <?php else: ?>
        <!-- FAVORITES GRID -->
        <div class="favorites-grid" id="favoritesGrid">
            <?php foreach ($favorites as $resto): ?>
                <article class="favorite-card" data-restaurant-id="<?= $resto['id'] ?>">
                    <div class="card-photo">
                        <?php if (!empty($resto['main_photo'])): ?>
                            <a href="/restaurant/<?= $resto['id'] ?>">
                                <img loading="lazy" src="/<?= htmlspecialchars($resto['main_photo']) ?>" 
                                     alt="<?= htmlspecialchars($resto['nom']) ?>"
                                     loading="lazy">
                            </a>
                        <?php else: ?>
                            <a href="/restaurant/<?= $resto['id'] ?>">
                                <div class="card-photo-placeholder">
                                    <i class="fas fa-utensils"></i>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <button class="card-wishlist-btn active" 
                                data-wishlist="<?= $resto['id'] ?>"
                                title="Retirer des favoris">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    
                    <div class="card-content">
                        <h3 class="card-name">
                            <a href="/restaurant/<?= $resto['id'] ?>">
                                <?= htmlspecialchars($resto['nom']) ?>
                            </a>
                        </h3>
                        
                        <?php if ($resto['note_moyenne'] > 0): ?>
                            <div class="card-rating">
                                <span class="rating-score"><?= number_format($resto['note_moyenne'], 1) ?></span>
                                <div class="rating-stars">
                                    <?php 
                                    $full = floor($resto['note_moyenne']);
                                    for ($i = 0; $i < 5; $i++):
                                        echo $i < $full ? '<i class="fas fa-star"></i>' : '<i class="fas fa-star empty"></i>';
                                    endfor;
                                    ?>
                                </div>
                                <span class="rating-count">(<?= $resto['nb_avis'] ?> avis)</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-meta">
                            <?php if (!empty($resto['type_cuisine'])): ?>
                                <span><i class="fas fa-utensils"></i> <?= htmlspecialchars($resto['type_cuisine']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($resto['price_range'])): ?>
                                <span><?= htmlspecialchars($resto['price_range']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($resto['adresse']) || !empty($resto['ville'])): ?>
                            <div class="card-address">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>
                                    <?= htmlspecialchars($resto['adresse'] ?? '') ?>
                                    <?php if ($resto['ville']): ?>
                                        <?= $resto['adresse'] ? ', ' : '' ?><?= htmlspecialchars($resto['ville']) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-actions">
                            <a href="/restaurant/<?= $resto['id'] ?>" class="card-btn card-btn-primary">
                                <i class="fas fa-eye"></i>
                                Voir
                            </a>
                            <?php if (!empty($resto['gps_latitude']) && !empty($resto['gps_longitude'])): ?>
                                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $resto['gps_latitude'] ?>,<?= $resto['gps_longitude'] ?>" 
                                   class="card-btn card-btn-secondary" target="_blank">
                                    <i class="fas fa-directions"></i>
                                    Y aller
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="added-date">
                            Ajouté le <?= date('d/m/Y', strtotime($resto['added_at'])) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
</main>

<script src="/assets/js/wishlist.js"></script>
<script>
    // Écouter les changements de wishlist pour retirer la carte
    window.addEventListener('wishlist:changed', (e) => {
        const { restaurantId, isFavorite } = e.detail;
        
        if (!isFavorite) {
            // Retirer la carte avec animation
            const card = document.querySelector(`.favorite-card[data-restaurant-id="${restaurantId}"]`);
            if (card) {
                card.classList.add('removing');
                setTimeout(() => {
                    card.remove();
                    
                    // Mettre à jour le compteur
                    const countEl = document.querySelector('.favorites-count');
                    const grid = document.getElementById('favoritesGrid');
                    const remaining = grid ? grid.querySelectorAll('.favorite-card').length : 0;
                    
                    if (countEl) {
                        if (remaining > 0) {
                            countEl.textContent = remaining;
                        } else {
                            countEl.remove();
                        }
                    }
                    
                    // Afficher empty state si plus de favoris
                    if (remaining === 0) {
                        location.reload();
                    }
                }, 300);
            }
        }
    });
</script>

</body>
</html>