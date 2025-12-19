<?php
include('auth/auth.php');
include('connect.php');

// Redirection si non connecté
if (!isset($_SESSION['user'])) {
    header('Location: auth/login_signup.php');
    exit;
}

$currentUser = $_SESSION['user'];

// Récupérer les infos utilisateur
$stmt = $dbh->prepare("SELECT * FROM users WHERE login = ?");
$stmt->execute([$currentUser]);
$userInfo = $stmt->fetch();

// Nombre de favoris
$stmtFav = $dbh->prepare("SELECT COUNT(*) FROM wishlist WHERE user = ?");
$stmtFav->execute([$currentUser]);
$nbrFav = $stmtFav->fetchColumn();

// Nombre de commentaires
$stmtComments = $dbh->prepare("SELECT COUNT(*) FROM comments WHERE user = ?");
$stmtComments->execute([$currentUser]);
$nbrComments = $stmtComments->fetchColumn();

$genre = $userInfo['genre'] ?? 'homme';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Mon Profil - Lebonresto</title>
    
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="css/bootstrap_customized.min.css" rel="stylesheet">
    <link href="css/style2.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .profile-wrapper {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
        }
        
        /* Profile Header Card */
        .profile-header-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .profile-cover {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }
        
        .profile-info-section {
            padding: 0 30px 30px;
            position: relative;
        }
        
        .profile-avatar-wrapper {
            margin-top: -60px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .profile-avatar-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
            margin: 15px 0 5px;
            text-align: center;
        }
        
        .profile-username {
            color: #718096;
            font-size: 15px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .profile-stats-row {
            display: flex;
            justify-content: center;
            gap: 50px;
            padding: 20px 0;
            border-top: 1px solid #e2e8f0;
        }
        
        .stat-box {
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 28px;
            font-weight: 700;
            color: #667eea;
            line-height: 1;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Tabs */
        .profile-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            background: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            color: #718096;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            font-size: 15px;
        }
        
        .tab-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .tab-btn i {
            margin-right: 8px;
        }
        
        .badge-count {
            background: #fc8181;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 8px;
        }
        
        /* Content Card */
        .content-card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            display: none;
        }
        
        .content-card.active {
            display: block;
        }
        
        .content-title {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        /* Form */
        .form-group label {
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px 40px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* Wishlist Item */
        .wishlist-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            transition: all 0.3s;
            position: relative;
        }
        
        .wishlist-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .wishlist-thumb {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .wishlist-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .wishlist-info {
            flex: 1;
        }
        
        .wishlist-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .wishlist-type {
            color: #718096;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .wishlist-address {
            color: #a0aec0;
            font-size: 13px;
        }
        
        .wishlist-address i {
            margin-right: 5px;
        }
        
        .btn-remove {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fc8181;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-remove:hover {
            background: #f56565;
            transform: scale(1.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #cbd5e0;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #a0aec0;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .profile-stats-row {
                flex-direction: column;
                gap: 20px;
            }
            
            .profile-tabs {
                flex-direction: column;
            }
            
            .tab-btn {
                width: 100%;
            }
            
            .wishlist-item {
                flex-direction: column;
                text-align: center;
            }
            
            .wishlist-thumb {
                width: 100%;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    
    <div class="profile-wrapper">
        <!-- Profile Header -->
        <div class="profile-header-card">
            <div class="profile-cover"></div>
            <div class="profile-info-section">
                <div class="profile-avatar-wrapper">
                    <img src="images/icons/<?= $genre === 'homme' ? 'userm.png' : 'userf.png' ?>" 
                         alt="<?= htmlspecialchars($currentUser) ?>" 
                         class="profile-avatar-img">
                </div>
                <h1 class="profile-name"><?= htmlspecialchars($userInfo['fname'] . ' ' . $userInfo['lname']) ?></h1>
                <p class="profile-username">@<?= htmlspecialchars($currentUser) ?></p>
                
                <div class="profile-stats-row">
                    <div class="stat-box">
                        <span class="stat-number"><?= $nbrComments ?></span>
                        <span class="stat-label">Avis publiés</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number"><?= $nbrFav ?></span>
                        <span class="stat-label">Favoris</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-number"><?= date('Y', strtotime($userInfo['created_at'] ?? 'now')) ?></span>
                        <span class="stat-label">Membre depuis</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="profile-tabs">
            <button class="tab-btn active" onclick="switchTab('profil')">
                <i class="fas fa-user"></i> Mon Profil
            </button>
            <button class="tab-btn" onclick="switchTab('wishlist')">
                <i class="fas fa-heart"></i> Mes Favoris
                <?php if ($nbrFav > 0): ?>
                    <span class="badge-count"><?= $nbrFav ?></span>
                <?php endif; ?>
            </button>
            <button class="tab-btn" onclick="switchTab('security')">
                <i class="fas fa-lock"></i> Sécurité
            </button>
        </div>
        
        <!-- Tab Content -->
        
        <!-- Profil Tab -->
        <div id="profil" class="content-card active">
            <h2 class="content-title">Informations personnelles</h2>
            <form id="profileForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Prénom</label>
                            <input type="text" class="form-control" name="fname" value="<?= htmlspecialchars($userInfo['fname'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nom</label>
                            <input type="text" class="form-control" name="lname" value="<?= htmlspecialchars($userInfo['lname'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-control" name="mail" value="<?= htmlspecialchars($userInfo['mail'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Adresse</label>
                    <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($userInfo['adresse'] ?? '') ?>">
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Ville</label>
                            <input type="text" class="form-control" name="city" value="<?= htmlspecialchars($userInfo['ville'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Code postal</label>
                            <input type="text" class="form-control" name="cp" value="<?= htmlspecialchars($userInfo['cp'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Wishlist Tab -->
        <div id="wishlist" class="content-card">
            <h2 class="content-title">Mes restaurants favoris</h2>
            <div id="wishlistContainer">
                <?php
                $stmtWishlist = $dbh->prepare("
                    SELECT w.*, v.Nom, v.Type, v.adresse, v.ville
                    FROM wishlist w
                    LEFT JOIN vendeur v ON w.resto = v.Nom
                    WHERE w.user = ?
                    ORDER BY w.date_added DESC
                ");
                $stmtWishlist->execute([$currentUser]);
                $wishlists = $stmtWishlist->fetchAll();
                
                if (count($wishlists) > 0) {
                    foreach ($wishlists as $wish) {
                        $stmtPhoto = $dbh->prepare("SELECT main FROM photos WHERE Nom = ? LIMIT 1");
                        $stmtPhoto->execute([$wish['resto']]);
                        $photo = $stmtPhoto->fetch();
                        $photoPath = $photo ? 'images/vendeur/' . $photo['main'] : 'img/default-restaurant.jpg';
                ?>
                <div class="wishlist-item" data-resto="<?= htmlspecialchars($wish['resto']) ?>">
                    <div class="wishlist-thumb">
                        <img src="<?= htmlspecialchars($photoPath) ?>" alt="<?= htmlspecialchars($wish['Nom']) ?>">
                    </div>
                    <div class="wishlist-info">
                        <h3 class="wishlist-title"><?= htmlspecialchars($wish['Nom']) ?></h3>
                        <p class="wishlist-type"><?= htmlspecialchars($wish['Type']) ?></p>
                        <p class="wishlist-address">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($wish['adresse'] . ', ' . $wish['ville']) ?>
                        </p>
                    </div>
                    <button class="btn-remove" onclick="removeFromWishlist('<?= htmlspecialchars($wish['resto']) ?>')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php
                    }
                } else {
                ?>
                <div class="empty-state">
                    <i class="fas fa-heart-broken"></i>
                    <h3>Aucun favori pour le moment</h3>
                    <p>Explorez nos restaurants et ajoutez vos coups de cœur !</p>
                    <a href="index.php" class="btn-save">
                        <i class="fas fa-search mr-2"></i> Découvrir des restaurants
                    </a>
                </div>
                <?php
                }
                ?>
            </div>
        </div>
        
        <!-- Security Tab -->
        <div id="security" class="content-card">
            <h2 class="content-title">Changer mon mot de passe</h2>
            <form id="passwordForm">
                <div class="form-group">
                    <label>Mot de passe actuel</label>
                    <input type="password" class="form-control" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" class="form-control" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label>Confirmer le nouveau mot de passe</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn-save">
                        <i class="fas fa-key mr-2"></i> Modifier le mot de passe
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php include('footer.php'); ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="js/common_scripts.min.js"></script>
    
    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.content-card').forEach(card => {
                card.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.closest('.tab-btn').classList.add('active');
        }
        
        // Update Profile
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'ajax/update-profile.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✅ Profil mis à jour avec succès !');
                        location.reload();
                    } else {
                        alert('❌ Erreur : ' + response.message);
                    }
                },
                error: function() {
                    alert('❌ Une erreur est survenue');
                }
            });
        });
        
        // Update Password
        $('#passwordForm').on('submit', function(e) {
            e.preventDefault();
            const newPass = $('input[name="new_password"]').val();
            const confirmPass = $('input[name="confirm_password"]').val();
            
            if (newPass !== confirmPass) {
                alert('❌ Les mots de passe ne correspondent pas');
                return;
            }
            
            $.ajax({
                url: 'ajax/update-password.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✅ Mot de passe modifié avec succès !');
                        $('#passwordForm')[0].reset();
                    } else {
                        alert('❌ Erreur : ' + response.message);
                    }
                },
                error: function() {
                    alert('❌ Une erreur est survenue');
                }
            });
        });
        
        // Remove from Wishlist
        function removeFromWishlist(resto) {
            if (!confirm('Voulez-vous vraiment retirer ce restaurant de vos favoris ?')) {
                return;
            }
            
            $.ajax({
                url: 'ajax/remove-wishlist.php',
                method: 'POST',
                data: { resto: resto },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('.wishlist-item[data-resto="' + resto + '"]').fadeOut(400, function() {
                            $(this).remove();
                            
                            if ($('.wishlist-item').length === 0) {
                                $('#wishlistContainer').html(`
                                    <div class="empty-state">
                                        <i class="fas fa-heart-broken"></i>
                                        <h3>Aucun favori pour le moment</h3>
                                        <p>Explorez nos restaurants et ajoutez vos coups de cœur !</p>
                                        <a href="index.php" class="btn-save">
                                            <i class="fas fa-search mr-2"></i> Découvrir des restaurants
                                        </a>
                                    </div>
                                `);
                            }
                        });
                    } else {
                        alert('❌ Erreur : ' + response.message);
                    }
                },
                error: function() {
                    alert('❌ Une erreur est survenue');
                }
            });
        }
    </script>
</body>
</html>