<?php
include_once('admin-protect.php');
include_once('connect.php');

// ============================================
// STATISTIQUES
// ============================================

// Restaurants en attente
$stmt = $conn->query("SELECT COUNT(*) as total FROM addresto");
$enAttente = $stmt->fetch_assoc()['total'];

// Restaurants validés (total)
$stmt = $conn->query("SELECT COUNT(*) as total FROM vendeur");
$totalValides = $stmt->fetch_assoc()['total'];

// Restaurants validés ce mois
$stmt = $conn->query("SELECT COUNT(*) as total FROM vendeur WHERE MONTH(NOW()) = MONTH(NOW())");
$validesCeMois = $stmt->fetch_assoc()['total'];

// Dernières inscriptions (5 dernières)
$stmt = $conn->query("SELECT Nom, Type, ville, phone FROM addresto ORDER BY Nom DESC LIMIT 5");
$dernieresInscriptions = $stmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Gestion Restaurants</title>
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

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-info {
            text-align: right;
        }

        .admin-info span {
            display: block;
            font-size: 14px;
            opacity: 0.9;
        }

        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-logout:hover {
            background: white;
            color: #667eea;
        }

        /* ===== NAVIGATION ===== */
        .admin-nav {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            list-style: none;
        }

        .nav-links a {
            color: #667eea;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: #667eea;
            color: white;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px 40px;
        }

        /* ===== STATS CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .stat-value {
            font-size: 42px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            color: #7f8c8d;
        }

        .stat-card.pending { border-left: 5px solid #f39c12; }
        .stat-card.validated { border-left: 5px solid #27ae60; }
        .stat-card.month { border-left: 5px solid #3498db; }

        /* ===== ACTIONS RAPIDES ===== */
        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 40px;
        }

        .quick-actions h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 15px 30px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }

        .btn-secondary {
            background: #ecf0f1;
            color: #2c3e50;
        }

        .btn-secondary:hover {
            background: #bdc3c7;
        }

        /* ===== DERNIÈRES INSCRIPTIONS ===== */
        .recent-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .recent-section h2 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .recent-list {
            list-style: none;
        }

        .recent-item {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-item:hover {
            background: #f8f9fa;
        }

        .resto-info h3 {
            font-size: 16px;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .resto-info p {
            font-size: 13px;
            color: #7f8c8d;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #95a5a6;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 15px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .header-right {
                flex-direction: column;
            }

            .admin-nav {
                padding: 15px 20px;
            }

            .nav-links {
                flex-direction: column;
                gap: 10px;
            }

            .container {
                padding: 0 20px 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="admin-header">
        <div class="header-left">
            <h1>🎯 Panel Administration</h1>
            <p>Gestion des restaurants</p>
        </div>
        <div class="header-right">
            <div class="admin-info">
                <span>👤 <?php echo htmlspecialchars($admin_name); ?></span>
                <span style="font-size: 12px; margin-top: 5px;"><?php echo htmlspecialchars($admin_mail); ?></span>
            </div>
            <a href="admin-logout.php" class="btn-logout">Déconnexion</a>
        </div>
    </div>

    <!-- NAVIGATION -->
    <div class="admin-nav">
        <ul class="nav-links">
            <li><a href="admin-dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="admin-liste-attente.php">📝 Restaurants en attente (<?php echo $enAttente; ?>)</a></li>
            <li><a href="admin-liste-valides.php">✅ Restaurants validés</a></li>
        </ul>
    </div>

    <!-- CONTAINER PRINCIPAL -->
    <div class="container">
        
        <!-- STATISTIQUES -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <div class="stat-icon">⏳</div>
                <div class="stat-value"><?php echo $enAttente; ?></div>
                <div class="stat-label">En attente de validation</div>
            </div>

            <div class="stat-card validated">
                <div class="stat-icon">✅</div>
                <div class="stat-value"><?php echo $totalValides; ?></div>
                <div class="stat-label">Restaurants validés (total)</div>
            </div>

            <div class="stat-card month">
                <div class="stat-icon">📅</div>
                <div class="stat-value"><?php echo $validesCeMois; ?></div>
                <div class="stat-label">Validés ce mois</div>
            </div>
        </div>

        <!-- ACTIONS RAPIDES -->
        <div class="quick-actions">
            <h2>⚡ Actions rapides</h2>
            <div class="action-buttons">
                <a href="admin-liste-attente.php" class="btn-action btn-primary">
                    <span>📋</span> Gérer les demandes en attente
                </a>
                <a href="admin-liste-valides.php" class="btn-action btn-secondary">
                    <span>🏪</span> Voir tous les restaurants
                </a>
            </div>
        </div>

        <!-- DERNIÈRES INSCRIPTIONS -->
        <div class="recent-section">
            <h2>🕒 Dernières inscriptions</h2>
            <?php if (count($dernieresInscriptions) > 0): ?>
                <ul class="recent-list">
                    <?php foreach ($dernieresInscriptions as $resto): ?>
                        <li class="recent-item">
                            <div class="resto-info">
                                <h3><?php echo htmlspecialchars($resto['Nom']); ?></h3>
                                <p><?php echo htmlspecialchars($resto['Type']); ?> • <?php echo htmlspecialchars($resto['ville']); ?> • <?php echo htmlspecialchars($resto['phone']); ?></p>
                            </div>
                            <span class="badge badge-pending">En attente</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>Aucune inscription en attente</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>
</html>