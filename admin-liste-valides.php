<?php
include_once('admin-protect.php');
include_once('connect.php');

// ============================================
// RÉCUPÉRER TOUS LES RESTAURANTS VALIDÉS
// ============================================
$stmt = $conn->query("SELECT v.*, p.main FROM vendeur v LEFT JOIN photos p ON v.Nom = p.Nom ORDER BY v.Nom ASC");
if (!$stmt) {
    die("Erreur requête : " . $conn->error);
}
$restaurants = $stmt->fetch_all(MYSQLI_ASSOC);

// ============================================
// RÉCUPÉRER LA LISTE DES VILLES
// ============================================
$stmtVilles = $conn->query("SELECT DISTINCT ville FROM vendeur ORDER BY ville ASC");
$villes = $stmtVilles->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants Validés - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            color: #2c3e50;
        }

        /* ===== HEADER ===== */
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 40px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header-left p {
            opacity: 0.9;
            font-size: 14px;
        }

        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: white;
            color: #667eea;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px;
        }

        /* ===== TABS ===== */
        .tabs {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .tabs-header {
            display: flex;
            border-bottom: 2px solid #ecf0f1;
        }

        .tab-button {
            flex: 1;
            padding: 20px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            color: #7f8c8d;
            position: relative;
        }

        .tab-button.active {
            color: #667eea;
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: #667eea;
        }

        .tab-button:hover {
            background: #f8f9fa;
        }

        .tab-badge {
            display: inline-block;
            background: #ecf0f1;
            color: #7f8c8d;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 8px;
        }

        .tab-button.active .tab-badge {
            background: #667eea;
            color: white;
        }

        /* ===== FILTERS ===== */
        .filters {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .filters-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            flex: 1;
            min-width: 250px;
        }

        .filter-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 14px;
            color: #2c3e50;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .filter-input:focus,
        .filter-select:focus {
            outline: none;
            border-color: #667eea;
        }

        /* ===== STATS BAR ===== */
        .stats-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stat-icon {
            font-size: 32px;
        }

        .stat-content .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }

        .stat-content .stat-label {
            font-size: 12px;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        /* ===== RESTAURANTS GRID ===== */
        .restaurants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .restaurant-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .badge-validated {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #27ae60;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
        }

        .badge-mea {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #f39c12;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 10;
        }

        .card-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 64px;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-content {
            padding: 20px;
        }

        .card-header {
            margin-bottom: 15px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .card-type {
            display: inline-block;
            background: #ecf0f1;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #667eea;
        }

        .card-info {
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .info-icon {
            font-size: 16px;
        }

        .card-stats {
            display: flex;
            gap: 15px;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
        }

        .stat-box {
            flex: 1;
            text-align: center;
        }

        .stat-box .value {
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }

        .stat-box .label {
            font-size: 11px;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .card-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            font-size: 13px;
            display: block;
        }

        .btn-edit {
            background: #3498db;
            color: white;
        }

        .btn-edit:hover {
            background: #2980b9;
        }

        .btn-toggle-mea {
            background: #f39c12;
            color: white;
        }

        .btn-toggle-mea:hover {
            background: #e67e22;
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .empty-state p {
            color: #7f8c8d;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .restaurants-grid {
                grid-template-columns: 1fr;
            }

            .stats-bar {
                flex-direction: column;
            }

            .tabs-header {
                flex-direction: column;
            }

            .filters-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="admin-header">
        <div class="header-left">
            <h1>✅ Restaurants Validés</h1>
            <p><?php echo count($restaurants); ?> restaurant(s) en ligne</p>
        </div>
        <a href="admin-dashboard.php" class="btn-back">← Retour Dashboard</a>
    </div>

    <!-- CONTAINER -->
    <div class="container">
        
        <!-- MESSAGES DE SUCCÈS -->
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px 20px; border-radius: 10px; border-left: 4px solid #28a745; margin-bottom: 30px;">
                <?php if ($_GET['success'] === 'updated'): ?>
                    ✅ Le restaurant <strong><?php echo htmlspecialchars($_GET['nom'] ?? ''); ?></strong> a été mis à jour avec succès !
                <?php elseif ($_GET['success'] === 'mea'): ?>
                    ⭐ La mise en avant a été <?php echo htmlspecialchars($_GET['action'] ?? 'modifiée'); ?> pour <strong><?php echo htmlspecialchars($_GET['nom'] ?? ''); ?></strong>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <!-- TABS -->
        <div class="tabs">
            <div class="tabs-header">
                <button class="tab-button active" data-tab="all">
                    🏪 Tous les restaurants
                    <span class="tab-badge"><?php echo count($restaurants); ?></span>
                </button>
                <button class="tab-button" data-tab="mea">
                    ⭐ Mis en avant
                    <span class="tab-badge">
                        <?php 
                        $mea_count = 0;
                        foreach ($restaurants as $r) {
                            if ($r['mea'] == 1) $mea_count++;
                        }
                        echo $mea_count;
                        ?>
                    </span>
                </button>
            </div>
        </div>

        <!-- FILTERS -->
        <div class="filters">
            <div class="filters-row">
                <div class="filter-group">
                    <label class="filter-label" for="searchInput">🔍 Recherche par nom</label>
                    <input type="text" 
                           class="filter-input" 
                           id="searchInput" 
                           placeholder="Nom du restaurant...">
                </div>
                <div class="filter-group">
                    <label class="filter-label" for="villeFilter">📍 Filtrer par ville</label>
                    <select class="filter-select" id="villeFilter">
                        <option value="">Toutes les villes</option>
                        <?php foreach ($villes as $ville): ?>
                            <option value="<?php echo htmlspecialchars($ville['ville']); ?>">
                                <?php echo htmlspecialchars($ville['ville']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label" for="typeFilter">🍽️ Filtrer par type</label>
                    <input type="text" 
                           class="filter-input" 
                           id="typeFilter" 
                           placeholder="Type de cuisine...">
                </div>
            </div>
        </div>

        <!-- STATS BAR -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-icon">🏪</span>
                <div class="stat-content">
                    <div class="stat-value" id="statsTotal"><?php echo count($restaurants); ?></div>
                    <div class="stat-label">Restaurants affichés</div>
                </div>
            </div>
            <div class="stat-item">
                <span class="stat-icon">⭐</span>
                <div class="stat-content">
                    <div class="stat-value"><?php echo $mea_count; ?></div>
                    <div class="stat-label">Mis en avant</div>
                </div>
            </div>
        </div>

        <!-- RESTAURANTS GRID -->
        <div class="restaurants-grid" id="restaurantsGrid">
            <?php foreach ($restaurants as $resto): ?>
                <div class="restaurant-card" 
                     data-search="<?php echo strtolower($resto['Nom'] . ' ' . $resto['Type'] . ' ' . $resto['ville']); ?>"
                     data-ville="<?php echo strtolower($resto['ville']); ?>"
                     data-type="<?php echo strtolower($resto['Type']); ?>"
                     data-mea="<?php echo $resto['mea']; ?>">
                    
                    <!-- BADGES -->
                    <span class="badge-validated">✓ Validé</span>
                    <?php if ($resto['mea'] == 1): ?>
                        <span class="badge-mea">⭐ Mise en avant</span>
                    <?php endif; ?>

                    <!-- IMAGE -->
                    <div class="card-image">
                        <?php if (!empty($resto['main']) && file_exists($resto['main'])): ?>
                            <img src="<?php echo htmlspecialchars($resto['main']); ?>" alt="<?php echo htmlspecialchars($resto['Nom']); ?>">
                        <?php else: ?>
                            🍽️
                        <?php endif; ?>
                    </div>

                    <!-- CONTENT -->
                    <div class="card-content">
                        <div class="card-header">
                            <h3 class="card-title"><?php echo htmlspecialchars($resto['Nom']); ?></h3>
                            <span class="card-type"><?php echo htmlspecialchars($resto['Type']); ?></span>
                        </div>

                        <div class="card-info">
                            <div class="info-item">
                                <span class="info-icon">📍</span>
                                <span><?php echo htmlspecialchars($resto['ville']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">📞</span>
                                <span><?php echo htmlspecialchars($resto['phone']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-icon">💰</span>
                                <span><?php echo htmlspecialchars($resto['pricerange']); ?></span>
                            </div>
                        </div>

                        <!-- STATS -->
                        <div class="card-stats">
                            <div class="stat-box">
                                <div class="value"><?php echo $resto['note'] ? number_format($resto['note'], 1) : '-'; ?></div>
                                <div class="label">Note</div>
                            </div>
                            <div class="stat-box">
                                <div class="value"><?php echo $resto['mea'] ? 'OUI' : 'NON'; ?></div>
                                <div class="label">MEA</div>
                            </div>
                        </div>

                        <!-- ACTIONS -->
                        <div class="card-actions">
                            <a href="admin-modifier-resto.php?nom=<?php echo urlencode($resto['Nom']); ?>" 
                               class="btn btn-edit">✏️ Modifier</a>
                            <a href="admin-toggle-mea.php?nom=<?php echo urlencode($resto['Nom']); ?>&current=<?php echo $resto['mea']; ?>" 
                               class="btn btn-toggle-mea">
                                <?php echo $resto['mea'] ? '★ Retirer MEA' : '☆ Activer MEA'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- EMPTY STATE (caché par défaut) -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <div class="empty-state-icon">🔍</div>
            <h2>Aucun restaurant trouvé</h2>
            <p>Modifiez vos filtres de recherche</p>
        </div>

    </div>

    <!-- SCRIPT FILTRES ET TABS -->
    <script>
        const cards = document.querySelectorAll('.restaurant-card');
        const searchInput = document.getElementById('searchInput');
        const villeFilter = document.getElementById('villeFilter');
        const typeFilter = document.getElementById('typeFilter');
        const tabButtons = document.querySelectorAll('.tab-button');
        const emptyState = document.getElementById('emptyState');
        const statsTotal = document.getElementById('statsTotal');
        const grid = document.getElementById('restaurantsGrid');

        let currentTab = 'all';

        // FONCTION DE FILTRAGE
        function filterCards() {
            const searchTerm = searchInput.value.toLowerCase();
            const villeValue = villeFilter.value.toLowerCase();
            const typeValue = typeFilter.value.toLowerCase();

            let visibleCount = 0;

            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                const villeData = card.getAttribute('data-ville');
                const typeData = card.getAttribute('data-type');
                const meaData = card.getAttribute('data-mea');

                // Vérifier les filtres
                const matchSearch = searchData.includes(searchTerm);
                const matchVille = villeValue === '' || villeData === villeValue;
                const matchType = typeValue === '' || typeData.includes(typeValue);
                const matchTab = currentTab === 'all' || (currentTab === 'mea' && meaData === '1');

                if (matchSearch && matchVille && matchType && matchTab) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Afficher/masquer empty state
            if (visibleCount === 0) {
                grid.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                grid.style.display = 'grid';
                emptyState.style.display = 'none';
            }

            // Mettre à jour le compteur
            statsTotal.textContent = visibleCount;
        }

        // ÉVÉNEMENTS FILTRES
        searchInput.addEventListener('input', filterCards);
        villeFilter.addEventListener('change', filterCards);
        typeFilter.addEventListener('input', filterCards);

        // ÉVÉNEMENTS TABS
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Retirer active de tous
                tabButtons.forEach(btn => btn.classList.remove('active'));
                // Ajouter active au bouton cliqué
                this.classList.add('active');
                // Changer le tab actuel
                currentTab = this.getAttribute('data-tab');
                // Réappliquer les filtres
                filterCards();
            });
        });
    </script>
</body>
</html>