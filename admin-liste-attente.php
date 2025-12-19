<?php
include_once('admin-protect.php');
include_once('connect.php');

// ============================================
// RÉCUPÉRER TOUS LES RESTAURANTS EN ATTENTE
// ============================================
$stmt = $conn->query("SELECT * FROM addresto ORDER BY Nom ASC");
$restaurants = $stmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurants en Attente - Admin</title>
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

        /* ===== SEARCH BAR ===== */
        .search-bar {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .search-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
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
        }

        .restaurant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
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

        .card-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            font-size: 14px;
        }

        .btn-view {
            background: #3498db;
            color: white;
        }

        .btn-view:hover {
            background: #2980b9;
        }

        .btn-validate {
            background: #27ae60;
            color: white;
        }

        .btn-validate:hover {
            background: #229954;
        }

        .btn-reject {
            background: #e74c3c;
            color: white;
        }

        .btn-reject:hover {
            background: #c0392b;
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

            .card-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="admin-header">
        <div class="header-left">
            <h1>📝 Restaurants en Attente</h1>
            <p><?php echo count($restaurants); ?> demande(s) à traiter</p>
        </div>
        <a href="admin-dashboard.php" class="btn-back">← Retour Dashboard</a>
    </div>

    <!-- MESSAGES DE SUCCÈS -->
    <?php if (isset($_GET['success'])): ?>
        <div style="max-width: 1400px; margin: 20px auto; padding: 0 40px;">
            <?php if ($_GET['success'] === 'validated'): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px 20px; border-radius: 10px; border-left: 4px solid #28a745;">
                    ✅ Le restaurant <strong><?php echo htmlspecialchars($_GET['nom'] ?? ''); ?></strong> a été validé avec succès !
                </div>
            <?php elseif ($_GET['success'] === 'rejected'): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 10px; border-left: 4px solid #dc3545;">
                    ❌ Le restaurant <strong><?php echo htmlspecialchars($_GET['nom'] ?? ''); ?></strong> a été rejeté.
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- CONTAINER -->
    <div class="container">
        
        <!-- SEARCH BAR -->
        <div class="search-bar">
            <input type="text" 
                   class="search-input" 
                   id="searchInput" 
                   placeholder="🔍 Rechercher un restaurant (nom, ville, type...)">
        </div>

        <!-- RESTAURANTS GRID -->
        <?php if (count($restaurants) > 0): ?>
            <div class="restaurants-grid" id="restaurantsGrid">
                <?php foreach ($restaurants as $resto): ?>
                    <div class="restaurant-card" data-search="<?php echo strtolower($resto['Nom'] . ' ' . $resto['Type'] . ' ' . $resto['ville']); ?>">
                        
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
                                    <span><?php echo htmlspecialchars($resto['adresse']); ?>, <?php echo htmlspecialchars($resto['codePostal']); ?> <?php echo htmlspecialchars($resto['ville']); ?></span>
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

                            <!-- ACTIONS -->
                            <div class="card-actions">
                                <a href="admin-voir-resto.php?id=<?php echo urlencode($resto['Nom']); ?>" class="btn btn-view">👁️ Détails</a>
                                <a href="admin-valider.php?nom=<?php echo urlencode($resto['Nom']); ?>" 
                                   class="btn btn-validate"
                                   onclick="return confirm('Valider ce restaurant ?')">✅ Valider</a>
                                <a href="admin-rejeter.php?nom=<?php echo urlencode($resto['Nom']); ?>" 
                                   class="btn btn-reject"
                                   onclick="return confirm('Rejeter ce restaurant ?')">❌ Rejeter</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">🎉</div>
                <h2>Aucune demande en attente</h2>
                <p>Toutes les demandes ont été traitées !</p>
            </div>
        <?php endif; ?>

    </div>

    <!-- SCRIPT DE RECHERCHE -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const cards = document.querySelectorAll('.restaurant-card');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            cards.forEach(card => {
                const searchData = card.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>