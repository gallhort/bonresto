<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

session_start();
$sessionURL = $_SESSION['resultURL'];

// Parser l'URL pour extraire les paramètres
$urlParts = parse_url($sessionURL);
parse_str($urlParts['query'] ?? '', $params);
 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo htmlspecialchars($_GET['nom'] ?? '', ENT_QUOTES, 'UTF-8'); ?> - Le Bon Resto Halal</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    
    <style>
        /* ============================================
           VARIABLES & RESET
           ============================================ */
        :root {
            --primary-color: #00aa6c;
            --primary-hover: #008c59;
            --secondary-color: #ff5a5f;
            --text-dark: #1a1a1a;
            --text-gray: #666;
            --text-light: #999;
            --border-color: #e0e0e0;
            --bg-light: #fafafa;
            --bg-white: #ffffff;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.16);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* ============================================
           STICKY HEADER
           ============================================ */
        .modern-header {
            background: white;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: var(--transition);
        }
        
        .modern-header.scrolled {
            box-shadow: var(--shadow-md);
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
        }
        
        .header-logo img {
            height: 40px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-header {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-header:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-header.primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-header.primary:hover {
            background: var(--primary-hover);
        }
        
        /* ============================================
           HERO SECTION
           ============================================ */
        .hero-section {
            background: white;
            padding-bottom: 30px;
        }
        
        .breadcrumb-modern {
            padding: 15px 0;
            background: transparent;
            margin: 0;
        }
        
        .breadcrumb-modern a {
            color: var(--text-gray);
            text-decoration: none;
            font-size: 14px;
            transition: var(--transition);
        }
        
        .breadcrumb-modern a:hover {
            color: var(--primary-color);
        }
        
        .breadcrumb-modern span {
            color: var(--text-light);
            margin: 0 10px;
        }
        
        .restaurant-hero {
            padding: 20px 0;
        }
        
        .restaurant-title-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            gap: 30px;
        }
        
        .restaurant-main-info h1 {
            font-size: 36px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .restaurant-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-gray);
            font-size: 15px;
        }
        
        .meta-item i {
            color: var(--primary-color);
        }
        
        .meta-item.rating {
            background: linear-gradient(135deg, var(--primary-color), #00d084);
            color: white;
            padding: 8px 16px;
            border-radius: 24px;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0,170,108,0.3);
        }
        
        .meta-item.rating i {
            color: #ffd700;
        }
        
        .restaurant-tags {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .tag {
            background: var(--bg-light);
            color: var(--text-dark);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        
        .tag:hover {
            border-color: var(--primary-color);
            background: #f0fdf7;
        }
        
        .hero-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-end;
        }
        
        .rating-large {
            background: linear-gradient(135deg, var(--primary-color), #00d084);
            color: white;
            padding: 20px;
            border-radius: var(--radius-md);
            text-align: center;
            box-shadow: var(--shadow-lg);
            min-width: 120px;
        }
        
        .rating-large .score {
            font-size: 48px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 8px;
        }
        
        .rating-large .status {
            font-size: 14px;
            font-weight: 600;
            opacity: 0.95;
        }
        
        .rating-large .reviews {
            font-size: 12px;
            opacity: 0.85;
            margin-top: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            background: white;
            border: 2px solid var(--border-color);
            color: var(--text-dark);
            padding: 12px 20px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        
        .btn-action:hover {
            border-color: var(--primary-color);
            background: #f0fdf7;
            transform: translateY(-2px);
        }
        
        .btn-action.favorite {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }
        
        .btn-action.favorite:hover {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-action.favorite.active {
            background: var(--secondary-color);
            color: white;
        }
        
        /* ============================================
           PHOTO GALLERY MODERN
           ============================================ */
        .photo-gallery-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            grid-template-rows: 300px 300px;
            gap: 8px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .gallery-item {
            position: relative;
            overflow: hidden;
            cursor: pointer;
            background: var(--bg-light);
        }
        
        .gallery-item:first-child {
            grid-row: 1 / 3;
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .gallery-item:hover img {
            transform: scale(1.1);
        }
        
        .gallery-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }
        
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        
        .gallery-overlay i {
            color: white;
            font-size: 48px;
        }
        
        .view-all-photos {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: white;
            color: var(--text-dark);
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            z-index: 10;
        }
        
        .view-all-photos:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 992px) {
            .restaurant-title-section {
                flex-direction: column;
            }
            
            .hero-actions {
                width: 100%;
                align-items: stretch;
            }
            
            .rating-large {
                width: 100%;
            }
            
            .action-buttons {
                width: 100%;
            }
            
            .btn-action {
                flex: 1;
                justify-content: center;
            }
            
            .photo-gallery-grid {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: 250px 250px;
            }
            
            .gallery-item:first-child {
                grid-row: 1 / 2;
                grid-column: 1 / 3;
            }
        }
        
        @media (max-width: 576px) {
            .restaurant-main-info h1 {
                font-size: 26px;
            }
            
            .header-actions {
                gap: 8px;
            }
            
            .btn-header span {
                display: none;
            }
            
            .photo-gallery-grid {
                grid-template-columns: 1fr;
                grid-template-rows: 200px 200px 200px;
            }
            
            .gallery-item:first-child {
                grid-row: 1 / 2;
                grid-column: 1 / 2;
            }
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</head>
<body>
 


	<?php

	$nom = $_GET['nom'];
	include('connect.php');


	// Modifier tous les foreach de cette page par des requete avec fetch -> voir test.php pour l'exemple

	//
	include_once('opening.php');


	// Récupération des notes de commentaire du restaurant en cours
$repas=0;
$service=0;
$location=0;
$price=0;
$compt=0;

	foreach ($dbh->query("SELECT count(*) as nbComment, avg(price) as price, avg(service) as service, avg(location) as location, avg(food) as food from comments c 
							JOIN vendeur v on c.Nom=v.Nom 
							WHERE c.NOM='$nom'") as $row) {

	$repas+=$row['food'];
	$service+=$row['service'];
	$location+=$row['location'];
	$price+=$row['price'];
	$nbComment=$row['nbComment'];
	}



	// Récupération des informations du restaurant en cours
$nom_escaped = $conn->real_escape_string($nom);

$query = "SELECT v.*, o.*, p.*
    FROM vendeur v
    LEFT JOIN options o ON v.Nom = o.Nom 
    LEFT JOIN photos p ON v.Nom = p.Nom 
    WHERE v.Nom = '$nom_escaped'";

$result = $conn->query($query);
$row = $result->fetch_assoc();

		$type = $row['Type'];
 
		$adresse = $row['adresse'];
		$cp = $row['codePostal'];
		$ville = $row['ville'];
		$descriptif = $row['descriptif'];
		$phone = $row['phone'];
		$web = $row['web'];
		$note = $row['note'];
		$main = $row['main'];
		$slide1 = $row['slide1'];
		$slide2 = $row['slide2'];
		$slide3 = $row['slide3'];
 



$noteStatus="";

if($note>0 AND $note<2){
	$noteStatus="Non recommandé";
}elseif($note>2 AND $note<3.5){
	$noteStatus="Bon";
}elseif($note>3.5 AND $note<4){
	$noteStatus="Très bon";
}elseif($note>4 AND $note<5){
	$noteStatus="Excellent";
}elseif($note==5){
	$noteStatus="Le Top du Top";
}
 




	?>


<!-- STICKY HEADER -->
<header class="modern-header" id="modernHeader">
    <div class="container">
        <div class="header-content">
            <div class="header-logo">
                <a href="index.php">
                    <img src="images/icons/logo2_footer.png" alt="Le Bon Resto Halal">
                </a>
            </div>
            
            <div class="header-actions">
                <a href="<?php echo $_SESSION['resultURL']; ?>" class="btn-header">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour aux résultats</span>
                </a>
                <button class="btn-header" id="shareBtn">
                    <i class="fas fa-share-alt"></i>
                    <span>Partager</span>
                </button>
                <button class="btn-header primary" id="bookTableBtn">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Réserver</span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb-modern">
            <a href="index.php">Accueil</a>
            <span>/</span>
 <?php
$newParams = $params;
$newParams['foodType'] = $type;
$newURL = 'result.php?' . http_build_query($newParams);
?>
<a href="<?php echo $sessionURL; ?>">Restaurants</a>
<span>/</span>
<a href="<?php echo $newURL; ?>"><?php echo $type; ?></a>
            <span>/</span>
            <strong><?php echo $nom; ?></strong>
        </nav>
        
        <!-- Restaurant Hero Info -->
        <div class="restaurant-hero">
            <div class="restaurant-title-section">
                <div class="restaurant-main-info">
                    <h1><?php echo $nom; ?></h1>
                    
                    <div class="restaurant-meta">
                        <div class="meta-item rating">
                            <i class="fas fa-star"></i>
                            <span><?php echo $note; ?>/5</span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-utensils"></i>
                            <span><?php echo $type; ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-euro-sign"></i>
                            <span><?php echo str_repeat('€', rand(2,3)); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-comment-dots"></i>
                            <span><?php echo $nbComment; ?> avis</span>
                        </div>
                    </div>
                    
                    <div class="meta-item" style="margin-bottom: 15px;">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo $adresse . ", " . $cp . " " . $ville; ?></span>
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($adresse . " " . $cp . " " . $ville); ?>&origin=" 
                           target="_blank" 
                           style="color: var(--primary-color); text-decoration: none; margin-left: 10px; font-weight: 600;">
                            <i class="fas fa-directions"></i> Itinéraire
                        </a>
                    </div>
                    
                    <div class="restaurant-tags">
                        <span class="tag"><i class="fas fa-check-circle"></i> Certifié Halal</span>
                        <span class="tag"><i class="fas fa-wifi"></i> WiFi gratuit</span>
                        <span class="tag"><i class="fas fa-parking"></i> Parking</span>
                        <span class="tag"><i class="fas fa-wheelchair"></i> Accessible</span>
                    </div>
                </div>
                
                <div class="hero-actions">
                    <div class="rating-large">
                        <div class="score"><?php echo $note; ?></div>
                        <div class="status"><?php echo $noteStatus; ?></div>
                        <div class="reviews"><?php echo $nbComment; ?> avis clients</div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn-action favorite" id="favoriteBtn">
                            <i class="far fa-heart"></i>
                            <span>Favori</span>
                        </button>
                        <button class="btn-action" onclick="window.print()">
                            <i class="fas fa-print"></i>
                            <span>Imprimer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PHOTO GALLERY -->
<section class="container" style="margin-bottom: 40px;">
    <div class="photo-gallery-grid">

        <?php if (!empty($main)) : ?>
        <div class="gallery-item" onclick="openGallery(0)">
            <img src="<?= htmlspecialchars($main) ?>" alt="Photo principale">
            <div class="gallery-overlay">
                <i class="fas fa-search-plus"></i>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($slide1)) : ?>
        <div class="gallery-item" onclick="openGallery(1)">
            <img src="<?= htmlspecialchars($slide1) ?>" alt="Photo 1">
            <div class="gallery-overlay">
                <i class="fas fa-search-plus"></i>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($slide2)) : ?>
        <div class="gallery-item" onclick="openGallery(2)">
            <img src="<?= htmlspecialchars($slide2) ?>" alt="Photo 2">
            <div class="gallery-overlay">
                <i class="fas fa-search-plus"></i>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($slide3)) : ?>
        <div class="gallery-item" onclick="openGallery(3)">
            <img src="<?= htmlspecialchars($slide3) ?>" alt="Photo 3">
            <div class="gallery-overlay">
                <i class="fas fa-search-plus"></i>
            </div>
        </div>
        <?php endif; ?>

    </div>

   <?php 
$images = [$main, $slide1, $slide2, $slide3]; 
if (count(array_filter($images)) > 1) : 
?>
    <button class="view-all-photos" onclick="openGallery(0)">
        <i class="fas fa-images"></i>
        <span>Voir toutes les photos</span>
    </button>
    <?php endif; ?>

</section>


<script>
// Header Scroll Effect
window.addEventListener('scroll', function() {
    const header = document.getElementById('modernHeader');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Favorite Toggle
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.getElementById('favoriteBtn');
    const restoName = '<?php echo $nom; ?>';
    const userName = '<?php echo $_SESSION['user']; ?>';

    // Vérifier si le resto est déjà dans les favoris au chargement
    fetch(`api/wishlist.php?action=test&resto=${restoName}&user=${userName}`)
        .then(response => response.text())
        .then(data => {
            if (data == '1') {
                favoriteBtn.classList.add('active');
                favoriteBtn.querySelector('i').classList.remove('far');
                favoriteBtn.querySelector('i').classList.add('fas');
            } else {
                favoriteBtn.classList.remove('active');
                favoriteBtn.querySelector('i').classList.remove('fas');
                favoriteBtn.querySelector('i').classList.add('far');
            }
        })
        .catch(err => console.error('Erreur fetch test favoris:', err));

    // Gestion du clic
    favoriteBtn.addEventListener('click', function() {
        const isActive = this.classList.contains('active');
        const action = isActive ? 'remove' : 'add';

        fetch(`api/wishlist.php?action=${action}&resto=${restoName}&user=${userName}`)
            .then(response => response.text())
            .then(data => {
                console.log('Favoris action:', data);

                // Mettre à jour l'UI du header
                this.classList.toggle('active');
                const icon = this.querySelector('i');
                if (this.classList.contains('active')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }

                // Mettre à jour la sidebar si elle existe
                const addSidebar = document.getElementById('addWishSidebar');
                const removeSidebar = document.getElementById('removeWishSidebar');
                if (addSidebar && removeSidebar) {
                    if (this.classList.contains('active')) {
                        addSidebar.style.display = 'none';
                        removeSidebar.style.display = 'inline-block';
                    } else {
                        addSidebar.style.display = 'inline-block';
                        removeSidebar.style.display = 'none';
                    }
                }
            })
            .catch(err => console.error('Erreur fetch toggle favoris:', err));
    });
});

// Gallery Functions (to be implemented)
function openGallery(index) {
    console.log('Open gallery at index:', index);
    // Implementation in next part
}
</script>
<style>
/* ============================================
   QUICK INFO CARDS
   ============================================ */
.quick-info-section {
    background: white;
    padding: 40px 0;
    margin-bottom: 30px;
}

.quick-info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.info-card {
    background: var(--bg-light);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 24px;
    transition: var(--transition);
    text-align: center;
}

.info-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.info-card-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), #00d084);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: white;
    font-size: 24px;
    box-shadow: 0 4px 12px rgba(0,170,108,0.3);
}

.info-card h4 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 8px;
}

.info-card p {
    font-size: 14px;
    color: var(--text-gray);
    margin: 0;
    line-height: 1.6;
}

.info-card a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
}

.info-card a:hover {
    text-decoration: underline;
}

/* ============================================
   MODERN TABS NAVIGATION
   ============================================ */
.tabs-modern-section {
    background: white;
    margin-bottom: 40px;
}

.tabs-nav-sticky {
    position: sticky;
    top: 71px;
    background: white;
    border-bottom: 2px solid var(--border-color);
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.tabs-nav {
    display: flex;
    gap: 0;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.tabs-nav::-webkit-scrollbar {
    display: none;
}

.tab-link {
    background: transparent;
    border: none;
    color: var(--text-gray);
    padding: 20px 30px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    transition: var(--transition);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tab-link:hover {
    color: var(--primary-color);
    background: var(--bg-light);
}

.tab-link.active {
    color: var(--primary-color);
}

.tab-link.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--primary-color);
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: scaleX(0);
    }
    to {
        transform: scaleX(1);
    }
}

.tab-content-modern {
    padding: 40px 0;
}

.tab-pane-modern {
    display: none;
    animation: fadeInUp 0.4s ease;
}

.tab-pane-modern.active {
    display: block;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ============================================
   APERÇU / OVERVIEW
   ============================================ */
.overview-section h2 {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 20px;
}

.overview-section h3 {
    font-size: 20px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 30px 0 15px;
}

.description-text {
    font-size: 16px;
    line-height: 1.8;
    color: var(--text-gray);
    margin-bottom: 30px;
}

.certification-badge {
    background: linear-gradient(135deg, #00aa6c, #00d084);
    color: white;
    padding: 20px 30px;
    border-radius: var(--radius-md);
    display: inline-flex;
    align-items: center;
    gap: 15px;
    margin: 20px 0;
    box-shadow: 0 4px 16px rgba(0,170,108,0.3);
}

.certification-badge i {
    font-size: 32px;
}

.certification-info h4 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 5px;
}

.certification-info p {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
}

/* Options Grid */
.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin: 30px 0;
}

.option-item {
    background: var(--bg-light);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    padding: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: var(--transition);
}

.option-item:hover {
    border-color: var(--primary-color);
    background: #f0fdf7;
}

.option-item i {
    color: var(--primary-color);
    font-size: 20px;
    width: 24px;
}

.option-item span {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-dark);
}

/* ============================================
   PHOTOS USER SECTION
   ============================================ */
.photos-user-section {
    margin: 40px 0;
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
}

.photo-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: var(--radius-sm);
    overflow: hidden;
    cursor: pointer;
    background: var(--bg-light);
}

.photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.photo-item:hover img {
    transform: scale(1.15);
}

.photo-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.photo-item:hover .photo-overlay {
    opacity: 1;
}

.photo-overlay i {
    color: white;
    font-size: 32px;
}

.photo-more {
    position: relative;
}

.photo-more::before {
    content: '+10';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 48px;
    font-weight: 900;
    color: white;
    z-index: 2;
}

.photo-more::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 1;
}

/* ============================================
   LOCATION / MAP SECTION
   ============================================ */
.location-section h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 20px;
}

.location-details {
    background: var(--bg-light);
    border-radius: var(--radius-md);
    padding: 30px;
    margin-bottom: 20px;
}

.location-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.location-item h4 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.location-item h4 i {
    color: var(--primary-color);
}

.location-item p {
    font-size: 14px;
    color: var(--text-gray);
    line-height: 1.8;
    margin: 0;
}

.map-container {
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    margin-top: 20px;
}

.map-container iframe {
    width: 100%;
    height: 400px;
    border: none;
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 768px) {
    .quick-info-cards {
        grid-template-columns: 1fr;
    }
    
    .tabs-nav {
        justify-content: flex-start;
    }
    
    .tab-link {
        padding: 15px 20px;
        font-size: 14px;
    }
    
    .options-grid {
        grid-template-columns: 1fr;
    }
    
    .location-row {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .map-container iframe {
        height: 300px;
    }
}
</style>

<!-- QUICK INFO CARDS -->
<section class="quick-info-section">
    <div class="container">
        <div class="quick-info-cards">
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h4>Téléphone</h4>
                <p><?php echo $phone; ?></p>
                <a href="tel:<?php echo $phone; ?>">
                    <i class="fas fa-phone"></i> Appeler
                </a>
            </div>
            
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h4>Horaires</h4>
                <p><strong>Déjeuner:</strong> 11h - 15h<br>
                   <strong>Dîner:</strong> 18h - 01h</p>
                <p class="<?php echo ($opening == 'Ouvert') ? 'text-success' : 'text-danger'; ?>">
                    <strong><?php echo $opening; ?></strong>
                </p>
            </div>
            
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h4>Site Web</h4>
                <p>Visitez notre site</p>
                <a href="<?php echo $web; ?>" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Ouvrir
                </a>
            </div>
            
            <div class="info-card">
                <div class="info-card-icon">
                    <i class="fas fa-directions"></i>
                </div>
                <h4>Itinéraire</h4>
                <p><?php echo $ville; ?>, <?php echo $cp; ?></p>
                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($adresse . " " . $cp . " " . $ville); ?>&origin=" target="_blank">
                    <i class="fas fa-route"></i> Y aller
                </a>
            </div>
        </div>
    </div>
</section>

<!-- TABS NAVIGATION -->
<section class="tabs-modern-section">
    <div class="tabs-nav-sticky">
        <div class="container">
            <nav class="tabs-nav" id="tabsNav">
                <button class="tab-link active" onclick="switchTab('overview')">
                    <i class="fas fa-info-circle"></i>
                    <span>Aperçu</span>
                </button>
                <button class="tab-link" onclick="switchTab('menu')">
                    <i class="fas fa-utensils"></i>
                    <span>Menu</span>
                </button>
                <button class="tab-link" onclick="switchTab('reviews')">
                    <i class="fas fa-star"></i>
                    <span>Avis (<?php echo $nbComment; ?>)</span>
                </button>
                <button class="tab-link" onclick="switchTab('photos')">
                    <i class="fas fa-camera"></i>
                    <span>Photos</span>
                </button>
                <button class="tab-link" onclick="switchTab('location')">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Localisation</span>
                </button>
            </nav>
        </div>
    </div>
    
    <!-- TAB CONTENT -->
    <div class="container">
        <div class="tab-content-modern">
            
            <!-- APERÇU TAB -->
            <div id="overview" class="tab-pane-modern active">
                <div class="overview-section">
                    <h2>À propos de <?php echo $nom; ?></h2>
                    <p class="description-text"><?php echo $descriptif; ?></p>
                    
                    <!-- Certification -->
                    <?php
                    foreach ($dbh->query("SELECT * from regime r JOIN vendeur v on r.Nom=v.Nom WHERE r.NOM='$nom'") as $row) {
                        if ($row['style'] != NULL) {
                            echo '<div class="certification-badge">
                                    <i class="fas fa-certificate"></i>
                                    <div class="certification-info">
                                        <h4>Restaurant ' . ucfirst($row['style']) . '</h4>';
                            if ($row['style'] == 'halal' || $row['style'] == 'casher') {
                                echo '<p>Certifié par : ' . $row['cert'] . '</p>';
                            }
                            echo '  </div>
                                  </div>';
                        }
                    }
                    ?>
                    
                    <!-- Options disponibles -->
                    <h3><i class="fas fa-check-circle"></i> Services et équipements</h3>
                    <div class="options-grid">
                        <?php
                        foreach ($dbh->query("SELECT * from options r JOIN vendeur v on r.Nom=v.Nom WHERE r.NOM='$nom'") as $row) {
                            if ($row['wifi'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-wifi"></i>
                                        <span>WiFi gratuit</span>
                                      </div>';
                            }
                            if ($row['parking'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-parking"></i>
                                        <span>Parking privé</span>
                                      </div>';
                            }
                            if ($row['handi'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-wheelchair"></i>
                                        <span>Accessible PMR</span>
                                      </div>';
                            }
                            if ($row['priere'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-mosque"></i>
                                        <span>Salle de prière</span>
                                      </div>';
                            }
                            if ($row['private'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-door-closed"></i>
                                        <span>Salons privés</span>
                                      </div>';
                            }
                            if ($row['voiturier'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-car"></i>
                                        <span>Service voiturier</span>
                                      </div>';
                            }
                            if ($row['gamezone'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-gamepad"></i>
                                        <span>Espace jeux</span>
                                      </div>';
                            }
                            if ($row['baby'] == 1) {
                                echo '<div class="option-item">
                                        <i class="fas fa-baby"></i>
                                        <span>Espace enfants</span>
                                      </div>';
                            }
                        }
                        ?>
                    </div>
                    

                </div>
            </div>
            
            <!-- MENU TAB (Placeholder) -->
            <div id="menu" class="tab-pane-modern">
                <h2>Menu du restaurant</h2>
                <p class="description-text">Le menu sera bientôt disponible...</p>
            </div>
            
            <!-- REVIEWS TAB (Placeholder) -->
            <div id="reviews" class="tab-pane-modern">
                <!-- <h2>Avis des clients</h2>
                <p class="description-text">Section des avis à venir dans la partie suivante...</p> -->
                <style>/* ============================================
   REVIEWS SECTION
   ============================================ */
.reviews-section {
    padding: 40px 0;
}

/* Rating Summary Card */
.rating-summary-card {
    background: linear-gradient(135deg, var(--primary-color), #00d084);
    border-radius: var(--radius-lg);
    padding: 40px;
    color: white;
    margin-bottom: 40px;
    box-shadow: var(--shadow-lg);
}

.rating-summary-grid {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 40px;
    align-items: center;
}

.rating-main {
    text-align: center;
}

.rating-score-big {
    font-size: 72px;
    font-weight: 900;
    line-height: 1;
    margin-bottom: 10px;
}

.rating-status-big {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 10px;
}

.rating-count {
    font-size: 14px;
    opacity: 0.9;
}

/* Rating Breakdown */
.rating-breakdown {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.rating-item {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-sm);
    padding: 20px;
}

.rating-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.rating-item-label {
    font-size: 15px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.rating-item-score {
    font-size: 20px;
    font-weight: 900;
}

.progress-bar-custom {
    width: 100%;
    height: 8px;
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: white;
    border-radius: 10px;
    transition: width 1s ease;
    box-shadow: 0 0 10px rgba(255,255,255,0.5);
}

/* Filter and Sort */
.reviews-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
}

.reviews-filter {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-btn {
    background: white;
    border: 2px solid var(--border-color);
    color: var(--text-dark);
    padding: 10px 20px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.filter-btn:hover {
    border-color: var(--primary-color);
    background: #f0fdf7;
}

.filter-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.reviews-sort select {
    background: white;
    border: 2px solid var(--border-color);
    color: var(--text-dark);
    padding: 10px 40px 10px 20px;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

/* Review Card */
.review-card-modern {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 30px;
    margin-bottom: 20px;
    transition: var(--transition);
}

.review-card-modern:hover {
    border-color: var(--primary-color);
    box-shadow: var(--shadow-md);
}

.review-header {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.review-avatar {
    flex-shrink: 0;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), #00d084);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    font-weight: 700;
    box-shadow: 0 4px 12px rgba(0,170,108,0.3);
}

.review-user-info {
    flex: 1;
}

.review-user-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.review-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.review-rating {
    background: linear-gradient(135deg, var(--primary-color), #00d084);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.review-rating i {
    color: #ffd700;
}

.review-date {
    color: var(--text-light);
    font-size: 14px;
}

.review-content {
    margin-bottom: 20px;
}

.review-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 12px;
}

.review-text {
    font-size: 15px;
    line-height: 1.8;
    color: var(--text-gray);
}

.review-actions {
    display: flex;
    gap: 20px;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}

.review-action-btn {
    background: transparent;
    border: none;
    color: var(--text-gray);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    padding: 8px 16px;
    border-radius: var(--radius-sm);
}

.review-action-btn:hover {
    color: var(--primary-color);
    background: var(--bg-light);
}

.review-action-btn.active {
    color: var(--primary-color);
    font-weight: 700;
}

.review-action-btn.useful-count {
    color: var(--primary-color);
    font-weight: 700;
    cursor: default;
}

.review-action-btn.useful-count:hover {
    background: transparent;
}

/* Leave Review Button */
.leave-review-section {
    text-align: center;
    padding: 60px 0;
    background: var(--bg-light);
    border-radius: var(--radius-lg);
    margin-top: 40px;
}

.leave-review-section h3 {
    font-size: 28px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.leave-review-section p {
    font-size: 16px;
    color: var(--text-gray);
    margin-bottom: 25px;
}

.btn-leave-review {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 16px 40px;
    border-radius: var(--radius-sm);
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 16px rgba(0,170,108,0.3);
    text-decoration: none;
}

.btn-leave-review:hover {
    background: var(--primary-hover);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,170,108,0.4);
}

/* Load More Button */
.load-more-section {
    text-align: center;
    margin: 40px 0;
}

.btn-load-more {
    background: white;
    border: 2px solid var(--primary-color);
    color: var(--primary-color);
    padding: 14px 40px;
    border-radius: var(--radius-sm);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
}

.btn-load-more:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 992px) {
    .rating-summary-grid {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .rating-breakdown {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .rating-summary-card {
        padding: 30px 20px;
    }
    
    .rating-score-big {
        font-size: 56px;
    }
    
    .rating-status-big {
        font-size: 20px;
    }
    
    .reviews-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .reviews-filter {
        justify-content: center;
    }
    
    .review-header {
        flex-direction: column;
        text-align: center;
    }
    
    .review-avatar {
        margin: 0 auto;
    }
}
</style>

<!-- REVIEWS SECTION (À remplacer dans le tab "reviews") -->
<div id="reviews-content" class="reviews-section">
    
    <!-- Rating Summary -->
    <div class="rating-summary-card">
        <div class="rating-summary-grid">
            <div class="rating-main">
                <div class="rating-score-big"><?php echo $note; ?></div>
                <div class="rating-status-big"><?php echo $noteStatus; ?></div>
                <div class="rating-count">Basé sur <?php echo $nbComment; ?> avis clients</div>
            </div>
            
            <div class="rating-breakdown">
                <div class="rating-item">
                    <div class="rating-item-header">
                        <div class="rating-item-label">
                            <i class="fas fa-utensils"></i>
                            <span>Repas</span>
                        </div>
                        <div class="rating-item-score"><?php echo number_format($repas, 1); ?></div>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: <?php echo $repas*20; ?>%"></div>
                    </div>
                </div>
                
                <div class="rating-item">
                    <div class="rating-item-header">
                        <div class="rating-item-label">
                            <i class="fas fa-concierge-bell"></i>
                            <span>Service</span>
                        </div>
                        <div class="rating-item-score"><?php echo number_format($service, 1); ?></div>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: <?php echo $service*20; ?>%"></div>
                    </div>
                </div>
                
                <div class="rating-item">
                    <div class="rating-item-header">
                        <div class="rating-item-label">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Emplacement</span>
                        </div>
                        <div class="rating-item-score"><?php echo number_format($location, 1); ?></div>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: <?php echo $location*20; ?>%"></div>
                    </div>
                </div>
                
                <div class="rating-item">
                    <div class="rating-item-header">
                        <div class="rating-item-label">
                            <i class="fas fa-euro-sign"></i>
                            <span>Rapport Qualité/Prix</span>
                        </div>
                        <div class="rating-item-score"><?php echo number_format($price, 1); ?></div>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: <?php echo $price*20; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reviews Controls -->
    <div class="reviews-controls">
        <div class="reviews-filter">
<button class="filter-btn active" onclick="filterReviews('all', event)">Tous les avis</button>
<button class="filter-btn" onclick="filterReviews('excellent', event)"><i class="fas fa-star"></i> Excellent</button>
<button class="filter-btn" onclick="filterReviews('good', event)"><i class="fas fa-star"></i> Très bon</button>
<button class="filter-btn" onclick="filterReviews('average', event)"><i class="fas fa-star"></i> Moyen</button>

        </div>
        
        <div class="reviews-sort">
            <select onchange="sortReviews(this.value)">
                <option value="recent">Plus récents</option>
                <option value="helpful">Plus utiles</option>
                <option value="rating-high">Note haute</option>
                <option value="rating-low">Note basse</option>
            </select>
        </div>
    </div>
 
    <!-- Reviews List -->
  <div id="reviewsList">
<?php
$stmt = $conn->prepare("SELECT * FROM comments WHERE nom = ? ORDER BY timestamp DESC");
$stmt->bind_param("s", $nom);
$stmt->execute();
$result = $stmt->get_result();

while ($comment = $result->fetch_assoc()) {
    $message = $comment['message'];
    $user = $comment['user'];
    $comment_id = $comment['comment_id'];
    $timestamp = $comment['timestamp'];
    $userNote = $comment['note'];
    $title = $comment['title'];

    // Initiale du username
    $initial = mb_strtoupper(mb_substr($user, 0, 1, 'UTF-8'), 'UTF-8');
    
    // --- Récupérer le vote de l'utilisateur courant ---
    $userId = $_SESSION['user'] ?? 'anon';
    $stmt2 = $conn->prepare("SELECT value FROM votes_useful WHERE user_id = ? AND comment_id = ?");
    $stmt2->bind_param("si", $userId, $comment_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $voteRow = $result2->fetch_assoc();
    $userVote = $voteRow ? $voteRow['value'] : 0; // 1 / -1 / 0
    $stmt2->close();

    // --- Récupérer nombre de votes utiles et inutiles ---
    $stmt3 = $conn->prepare("
        SELECT
            SUM(value = 1) AS utiles,
            SUM(value = -1) AS inutiles
        FROM votes_useful
        WHERE comment_id = ?
    ");
    $stmt3->bind_param("i", $comment_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $votes = $result3->fetch_assoc();
    $stmt3->close();

    $u = (int)$votes['utiles'];
    $i = (int)$votes['inutiles'];

    if ($u == 0 && $i == 0) {
        $voteText = "Soyez le premier à voter";
    } else {
        $voteText = 
            $u . " utile" . ($u > 1 ? "s" : "") .
            " • " .
            $i . " pas utile" . ($i > 1 ? "s" : "");
    }
?>
<div class="review-card-modern" data-comment-id="<?= $comment_id ?>" data-rating="<?= $userNote ?>">
    <div class="review-header">
        <div class="review-avatar">
            <div class="avatar-circle"><?= $initial ?></div>
        </div>
        <div class="review-user-info">
            <div class="review-user-name"><?= htmlspecialchars($user) ?></div>
            <div class="review-meta">
                <div class="review-rating">
                    <i class="fas fa-star"></i>
                    <span><?= $userNote ?>/5</span>
                </div>
                <div class="review-date">
                    <i class="far fa-clock"></i>
                    Posté le <?= date('d/m/Y', strtotime($timestamp)) ?>
                </div>
            </div>
        </div>
    </div>

<div class="review-content">
    <h4 class="review-title">"<?= htmlspecialchars($title) ?>"</h4>
    <p class="review-text"><?= htmlspecialchars($message) ?></p>
    
    <!-- Sous-notes détaillées -->
    <div class="review-ratings">
        <div class="rating-item">
            <span class="rating-label">Qualité</span>
            <div class="rating-dots">
                <?php
                $food = isset($comment['food']) ? floatval($comment['food']) : 0;
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="dot ' . ($i <= $food ? 'filled' : '') . '"></span>';
                }
                ?>
            </div>
        </div>
        
        <div class="rating-item">
            <span class="rating-label">Service</span>
            <div class="rating-dots">
                <?php
                $service = isset($comment['service']) ? floatval($comment['service']) : 0;
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="dot ' . ($i <= $service ? 'filled' : '') . '"></span>';
                }
                ?>
            </div>
        </div>
        
        <div class="rating-item">
            <span class="rating-label">Emplacement</span>
            <div class="rating-dots">
                <?php
                $location = isset($comment['location']) ? floatval($comment['location']) : 0;
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="dot ' . ($i <= $location ? 'filled' : '') . '"></span>';
                }
                ?>
            </div>
        </div>
        
        <div class="rating-item">
            <span class="rating-label">Prix</span>
            <div class="rating-dots">
                <?php
                $price = isset($comment['price']) ? floatval($comment['price']) : 0;
                for ($i = 1; $i <= 5; $i++) {
                    echo '<span class="dot ' . ($i <= $price ? 'filled' : '') . '"></span>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Photos de cet avis -->
    <?php
    $stmtPhotos = $conn->prepare("SELECT * FROM photos_users WHERE comment_id = ?");
    $stmtPhotos->bind_param("i", $comment_id);
    $stmtPhotos->execute();
    $photosResult = $stmtPhotos->get_result();
    
    if ($photosResult->num_rows > 0) {
        echo '<div class="review-photos">';
        while ($photo = $photosResult->fetch_assoc()) {
            ?>
            <img src="<?= htmlspecialchars($photo['chemin_photo']) ?>" 
                 alt="Photo de l'avis" 
                 class="review-photo"
                 onclick="openLightbox('<?= htmlspecialchars($photo['chemin_photo']) ?>')">
            <?php
        }
        echo '</div>';
    }
    $stmtPhotos->close();
    ?>
</div>

<style>
.review-ratings {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.rating-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.rating-label {
    font-size: 13px;
    color: #333;
    min-width: 80px;
    font-weight: 500;
}

.rating-dots {
    display: flex;
    gap: 4px;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #e0e0e0;
    display: inline-block;
}

.dot.filled {
    background-color: #00a680;
}

.review-photos {
    display: flex;
    gap: 8px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.review-photo {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.review-photo:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
</style>

    <div class="review-actions" data-comment-id="<?= $comment_id ?>">

        <!-- Bouton Utile -->
        <button class="review-action-btn useful-btn <?= $userVote == 1 ? 'active' : '' ?>"
                onclick="markUseful(<?= $comment_id ?>, 1, event)">
            <i class="far fa-thumbs-up"></i>
            <span>Utile</span>
        </button>

        <!-- Bouton Pas utile -->
        <button class="review-action-btn useless-btn <?= $userVote == -1 ? 'active' : '' ?>"
                onclick="markUseful(<?= $comment_id ?>, -1, event)">
            <i class="far fa-thumbs-down"></i>
            <span>Pas utile</span>
        </button>

        <!-- Compteur -->
        <div class="review-action-btn useful-count">
            <i class="fas fa-users"></i>
            <span><?= $voteText ?></span>
        </div>

        <!-- Répondre -->
        <button class="review-action-btn">
            <i class="fas fa-reply"></i>
            <span>Répondre</span>
        </button>

    </div>
</div>

<?php
} // fin while
$stmt->close();
?>

</div>
    
    <!-- Load More -->
    <div class="load-more-section">
        <button class="btn-load-more" onclick="loadMoreReviews()">
            <i class="fas fa-chevron-down"></i>
            Voir plus d'avis
        </button>
    </div>
    
    <!-- Leave Review CTA -->
    <div class="leave-review-section">
        <h3>Partagez votre expérience</h3>
        <p>Vous avez déjà visité <?php echo $nom; ?> ? Aidez les autres clients en laissant votre avis.</p>
        <a href="leave-review.php?nom=<?php echo urlencode($nom); ?>" class="btn-leave-review">
            <i class="fas fa-pen"></i>
            <span>Écrire un avis</span>
        </a>
    </div>
    
</div>

<script>
// Hidden inputs for AJAX
const currentUser = '<?php echo $_SESSION['user']; ?>';
const currentResto = '<?php echo $nom; ?>';

// Filter Reviews
function filterReviews(filter, event) {
    // Update active button
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    if (event && event.currentTarget) event.currentTarget.classList.add('active');
    else if (event && event.target) event.target.classList.add('active');

    // Filter logic
    const reviews = document.querySelectorAll('.review-card-modern');
    reviews.forEach(review => {
        const rating = parseFloat(review.getAttribute('data-rating')) || 0;
        let show = false;

        switch(filter) {
            case 'all':
                show = true;
                break;
            case 'excellent':
                show = rating >= 4;
                break;
            case 'good':
                show = rating >= 2.5 && rating < 3.5;
                break;
            case 'average':
                show = rating < 2.5;
                break;
        }


        if (show) {
            review.style.display = 'block';
            setTimeout(() => {
                review.style.opacity = '1';
                review.style.transform = 'translateY(0)';
            }, 10);
        } else {
            review.style.opacity = '0';
            review.style.transform = 'translateY(20px)';
            setTimeout(() => {
                review.style.display = 'none';
            }, 300);
        }
    });
}


// Sort Reviews
function sortReviews(sortBy) {
    const reviewsList = document.getElementById('reviewsList');
    const reviews = Array.from(reviewsList.querySelectorAll('.review-card-modern'));
    
    reviews.sort((a, b) => {
        switch(sortBy) {
            case 'rating-high':
                return parseInt(b.getAttribute('data-rating')) - parseInt(a.getAttribute('data-rating'));
            case 'rating-low':
                return parseInt(a.getAttribute('data-rating')) - parseInt(b.getAttribute('data-rating'));
            case 'helpful':
                // À implémenter avec les données de useful
                return 0;
            case 'recent':
            default:
                return 0; // Already sorted by date in PHP
        }
    });
    
    reviews.forEach(review => reviewsList.appendChild(review));
}

// Mark as Useful/Useless
function markUseful(commentId, value, event) {
    
    $.ajax({
        type: 'POST',
        url: 'rev/ajax-comments.php',
        data: { id: commentId, useful: value },
        success: function (response) {
            console.log(response);

            // Mettre à jour l’UI
            const card = event.target.closest(".review-card-modern");

            // Reset des boutons
            card.querySelector(".useful-btn").classList.remove("active");
            card.querySelector(".useless-btn").classList.remove("active");

            // Activer le bon bouton
            if (value === 1) card.querySelector(".useful-btn").classList.add("active");
            if (value === -1) card.querySelector(".useless-btn").classList.add("active");

            // Mettre à jour le compteur
            if (response.countText) {
                card.querySelector(".useful-count span").innerText = response.countText;
            }
        },
        error: function (err) {
            console.error('Erreur AJAX :', err);
        }
    });
    
}








// Load More Reviews (pagination)
function loadMoreReviews() {
    // À implémenter avec AJAX pour charger plus d'avis
    console.log('Chargement de plus d\'avis...');
}

// Animation des progress bars au chargement
document.addEventListener('DOMContentLoaded', function() {
    const progressBars = document.querySelectorAll('.progress-fill');
    progressBars.forEach((bar, index) => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 200 + (index * 100));
    });
});
</script>
            </div>
            
            <!-- PHOTOS TAB (Placeholder) -->
<div id="photos" class="tab-pane-modern">
    <h2>Toutes les photos</h2>
    <div class="photos-grid">
        <?php
        // Récupérer toutes les photos pour ce restaurant
        $stmt = $conn->prepare("SELECT p.*, c.user, c.timestamp 
                                FROM photos_users p 
                                INNER JOIN comments c ON p.comment_id = c.comment_id 
                                WHERE p.nom_restaurant = ? 
                                ORDER BY c.timestamp DESC");
        $stmt->bind_param("s", $nom);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($photo = $result->fetch_assoc()) {
                ?>
                <div class="photo-item">
                    <img src="<?php echo htmlspecialchars($photo['chemin_photo']); ?>" 
                         alt="Photo de <?php echo htmlspecialchars($photo['nom_posteur']); ?>"
                         onclick="openLightbox('<?php echo htmlspecialchars($photo['chemin_photo']); ?>')">
                    <div class="photo-info">
                        <span class="photo-author"><?php echo htmlspecialchars($photo['nom_posteur']); ?></span>
                        <span class="photo-date"><?php echo date('d/m/Y', strtotime($photo['timestamp'])); ?></span>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p class="no-photos">Aucune photo pour le moment. Soyez le premier à partager une photo !</p>';
        }
        ?>
    </div>
</div>

<!-- Lightbox pour agrandir les photos -->
<div id="photoLightbox" class="lightbox" onclick="closeLightbox()">
    <span class="lightbox-close">&times;</span>
    <img class="lightbox-content" id="lightboxImg">
</div>

<style>
.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.photo-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    cursor: pointer;
}

.photo-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.photo-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}

.photo-info {
    padding: 10px;
    background: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.photo-author {
    font-weight: 600;
    color: #333;
}

.photo-date {
    color: #999;
}

.no-photos {
    text-align: center;
    color: #999;
    padding: 40px;
    font-style: italic;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    z-index: 9999;
    padding-top: 50px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.9);
}

.lightbox-content {
    margin: auto;
    display: block;
    max-width: 90%;
    max-height: 80%;
    animation: zoom 0.3s;
}

@keyframes zoom {
    from {transform: scale(0.8)}
    to {transform: scale(1)}
}

.lightbox-close {
    position: absolute;
    top: 15px;
    right: 35px;
    color: #f1f1f1;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
}

.lightbox-close:hover {
    color: #bbb;
}
</style>

<script>
function openLightbox(src) {
    document.getElementById('photoLightbox').style.display = 'block';
    document.getElementById('lightboxImg').src = src;
}

function closeLightbox() {
    document.getElementById('photoLightbox').style.display = 'none';
}

// Fermer avec la touche Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});
</script>
            
            <!-- LOCATION TAB -->
            <div id="location" class="tab-pane-modern">
                <div class="location-section">
                    <h3>Comment se rendre à <?php echo $nom; ?></h3>
                    
                    <div class="location-details">
                        <div class="location-row">
                            <div class="location-item">
                                <h4><i class="fas fa-map-marker-alt"></i> Adresse</h4>
                                <p><?php echo $adresse; ?><br>
                                   <?php echo $cp . " " . $ville; ?></p>
                            </div>
                            
                            <div class="location-item">
                                <h4><i class="fas fa-clock"></i> Horaires</h4>
                                <p><strong>Déjeuner:</strong> Lun - Sam, 11h - 15h<br>
                                   <strong>Dîner:</strong> Lun - Sam, 18h - 01h<br>
                                   <span style="color: var(--secondary-color);">Fermé le dimanche</span></p>
                            </div>
                            
                            <div class="location-item">
                                <h4><i class="fas fa-credit-card"></i> Paiements</h4>
                                <p>Mastercard, Visa, Amex<br>
                                   Espèces acceptées</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="map-container">
                        <iframe src="https://maps.google.com/maps?q=<?php echo urlencode($adresse . " " . $cp . " " . $ville); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed"></iframe>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<script>
// Tab Switching Function
function switchTab(tabName) {
    // Remove active from all tabs
    document.querySelectorAll('.tab-link').forEach(tab => {
        tab.classList.remove('active');
    });
    
    document.querySelectorAll('.tab-pane-modern').forEach(pane => {
        pane.classList.remove('active');
    });
    
    // Add active to clicked tab
    event.target.closest('.tab-link').classList.add('active');
    document.getElementById(tabName).classList.add('active');
    
    // Smooth scroll to content
    const tabsNav = document.querySelector('.tabs-nav-sticky');
    const offset = tabsNav.offsetHeight + 71; // header height
    window.scrollTo({
        top: tabsNav.offsetTop - 71,
        behavior: 'smooth'
    });
}

// Sticky tabs on scroll
window.addEventListener('scroll', function() {
    const tabsNav = document.querySelector('.tabs-nav-sticky');
    const header = document.querySelector('.modern-header');
    
    if (window.scrollY > 800) {
        tabsNav.style.top = header.offsetHeight + 'px';
    }
});
</script>

 


<style>
/* ============================================
   LAYOUT WITH SIDEBAR
   ============================================ */
.content-with-sidebar {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 40px;
    margin-top: 40px;
}

.main-content {
    min-width: 0; /* Fix overflow */
}

.sidebar-sticky {
    position: sticky;
    top: 90px;
    height: fit-content;
    align-self: start;
}

/* ============================================
   BOOKING CARD
   ============================================ */
.booking-card {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-md);
}

.booking-card-header {
    text-align: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--border-color);
}

.booking-card-header h3 {
    font-size: 22px;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 10px;
}

.booking-price {
    font-size: 16px;
    color: var(--text-gray);
}

.booking-price strong {
    color: var(--primary-color);
    font-size: 24px;
    font-weight: 900;
}

.booking-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-group-modern {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group-modern label {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group-modern label i {
    color: var(--primary-color);
}

.form-control-modern {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 500;
    transition: var(--transition);
    background: white;
}

.form-control-modern:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(0,170,108,0.1);
}

.form-control-modern:hover {
    border-color: var(--primary-color);
}

.btn-book-now {
    background: linear-gradient(135deg, var(--primary-color), #00d084);
    color: white;
    border: none;
    padding: 16px;
    border-radius: var(--radius-sm);
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    box-shadow: 0 4px 16px rgba(0,170,108,0.3);
    margin-top: 10px;
}

.btn-book-now:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,170,108,0.4);
}

.booking-note {
    font-size: 12px;
    color: var(--text-light);
    text-align: center;
    margin-top: 10px;
}

/* ============================================
   MAP CARD
   ============================================ */
.map-card {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 20px;
    box-shadow: var(--shadow-md);
}

.map-card-header {
    padding: 20px;
    background: var(--bg-light);
    border-bottom: 2px solid var(--border-color);
}

.map-card-header h4 {
    font-size: 18px;
    font-weight: 700;
    color: var(--text-dark);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.map-card-header h4 i {
    color: var(--primary-color);
}

.map-container-sidebar {
    height: 300px;
    position: relative;
}

.map-container-sidebar iframe {
    width: 100%;
    height: 100%;
    border: none;
}

.map-overlay-btn {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
    padding: 12px 24px;
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.map-overlay-btn:hover {
    background: var(--primary-color);
    color: white;
    transform: translateX(-50%) translateY(-3px);
    box-shadow: var(--shadow-lg);
}

/* ============================================
   INFO CARD
   ============================================ */
.info-card-sidebar {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-md);
}

.info-item-sidebar {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid var(--border-color);
}

.info-item-sidebar:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-item-sidebar:first-child {
    padding-top: 0;
}

.info-icon-sidebar {
    width: 40px;
    height: 40px;
    background: var(--bg-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.info-icon-sidebar i {
    color: var(--primary-color);
    font-size: 18px;
}

.info-details-sidebar {
    flex: 1;
}

.info-details-sidebar h5 {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 5px;
}

.info-details-sidebar p {
    font-size: 14px;
    color: var(--text-gray);
    margin: 0;
    line-height: 1.6;
}

.info-details-sidebar a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
}

.info-details-sidebar a:hover {
    text-decoration: underline;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    margin-top: 5px;
}

.status-badge.open {
    background: #d4edda;
    color: #155724;
}

.status-badge.closed {
    background: #f8d7da;
    color: #721c24;
}

/* ============================================
   ACTION BUTTONS CARD
   ============================================ */
.actions-card {
    background: white;
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: var(--shadow-md);
}

.action-btn-sidebar {
    width: 100%;
    padding: 14px;
    border: 2px solid var(--border-color);
    background: white;
    color: var(--text-dark);
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 12px;
    text-decoration: none;
}

.action-btn-sidebar:last-child {
    margin-bottom: 0;
}

.action-btn-sidebar:hover {
    border-color: var(--primary-color);
    background: #f0fdf7;
    transform: translateY(-2px);
}

.action-btn-sidebar.primary {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.action-btn-sidebar.primary:hover {
    background: var(--primary-hover);
}

.action-btn-sidebar.favorite {
    border-color: var(--secondary-color);
    color: var(--secondary-color);
}

.action-btn-sidebar.favorite:hover {
    background: var(--secondary-color);
    color: white;
}

.action-btn-sidebar.favorite.active {
    background: var(--secondary-color);
    color: white;
}

/* ============================================
   SHARE CARD
   ============================================ */
.share-card {
    background: var(--bg-light);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 20px;
    text-align: center;
}

.share-card h5 {
    font-size: 16px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: 15px;
}

.share-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.share-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
}

.share-btn:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: var(--shadow-md);
}

.share-btn.facebook {
    background: #1877f2;
}

.share-btn.twitter {
    background: #1da1f2;
}

.share-btn.whatsapp {
    background: #25d366;
}

.share-btn.copy {
    background: var(--text-gray);
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 1200px) {
    .content-with-sidebar {
        grid-template-columns: 1fr 320px;
        gap: 30px;
    }
}

@media (max-width: 992px) {
    .content-with-sidebar {
        grid-template-columns: 1fr;
    }
    
    .sidebar-sticky {
        position: relative;
        top: 0;
    }
    
    .booking-card,
    .map-card,
    .info-card-sidebar,
    .actions-card {
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
}

@media (max-width: 576px) {
    .booking-card,
    .map-card,
    .info-card-sidebar,
    .actions-card,
    .share-card {
        padding: 20px;
    }
    
    .share-buttons {
        flex-wrap: wrap;
    }
}
</style>

<!-- MAIN LAYOUT WITH SIDEBAR -->
<div class="container">
    <div class="content-with-sidebar">
        
        <!-- MAIN CONTENT (Onglets déjà créés) -->
        <div class="main-content">
            <!-- Tout le contenu des onglets va ici -->
            <!-- Section tabs-modern-section déjà créée dans la partie 2 -->
        </div>
        
        <!-- SIDEBAR STICKY -->
        <aside class="sidebar-sticky">
            
            <!-- BOOKING CARD -->
            <div class="booking-card">
                <div class="booking-card-header">
                    <h3>Réserver une table</h3>
                    <div class="booking-price">
                        À partir de <strong>€€</strong> par personne
                    </div>
                </div>
                
                <form class="booking-form" onsubmit="return handleBooking(event)">
                    <div class="form-group-modern">
                        <label>
                            <i class="far fa-calendar"></i>
                            Date
                        </label>
                        <input type="date" 
                               class="form-control-modern" 
                               required
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group-modern">
                        <label>
                            <i class="far fa-clock"></i>
                            Heure
                        </label>
                        <select class="form-control-modern" required>
                            <option value="">Sélectionnez une heure</option>
                            <option value="11:00">11:00</option>
                            <option value="11:30">11:30</option>
                            <option value="12:00">12:00</option>
                            <option value="12:30">12:30</option>
                            <option value="13:00">13:00</option>
                            <option value="13:30">13:30</option>
                            <option value="14:00">14:00</option>
                            <option value="18:00">18:00</option>
                            <option value="18:30">18:30</option>
                            <option value="19:00">19:00</option>
                            <option value="19:30">19:30</option>
                            <option value="20:00">20:00</option>
                            <option value="20:30">20:30</option>
                            <option value="21:00">21:00</option>
                        </select>
                    </div>
                    
                    <div class="form-group-modern">
                        <label>
                            <i class="fas fa-users"></i>
                            Nombre de personnes
                        </label>
                        <select class="form-control-modern" required>
                            <option value="">Sélectionnez</option>
                            <option value="1">1 personne</option>
                            <option value="2">2 personnes</option>
                            <option value="3">3 personnes</option>
                            <option value="4">4 personnes</option>
                            <option value="5">5 personnes</option>
                            <option value="6">6 personnes</option>
                            <option value="7">7 personnes</option>
                            <option value="8">8 personnes</option>
                            <option value="9+">9+ personnes</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-book-now">
                        <i class="fas fa-check-circle"></i>
                        <span>Réserver maintenant</span>
                    </button>
                    
                    <p class="booking-note">
                        <i class="fas fa-info-circle"></i>
                        Réservation gratuite et instantanée
                    </p>
                </form>
            </div>
            
            <!-- MAP CARD -->
            <div class="map-card">
                <div class="map-card-header">
                    <h4>
                        <i class="fas fa-map-marker-alt"></i>
                        Localisation
                    </h4>
                </div>
                <div class="map-container-sidebar">
                    <iframe src="https://maps.google.com/maps?q=<?php echo urlencode($adresse . " " . $cp . " " . $ville); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed"></iframe>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo urlencode($adresse . " " . $cp . " " . $ville); ?>&origin=" 
                       target="_blank" 
                       class="map-overlay-btn">
                        <i class="fas fa-directions"></i>
                        <span>Obtenir l'itinéraire</span>
                    </a>
                </div>
            </div>
            
            <!-- INFO CARD -->
            <div class="info-card-sidebar">
                <div class="info-item-sidebar">
                    <div class="info-icon-sidebar">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="info-details-sidebar">
                        <h5>Téléphone</h5>
                        <p><?php echo $phone; ?></p>
                        <a href="tel:<?php echo $phone; ?>">
                            <i class="fas fa-phone"></i> Appeler maintenant
                        </a>
                    </div>
                </div>
                
                <div class="info-item-sidebar">
                    <div class="info-icon-sidebar">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-details-sidebar">
                        <h5>Horaires d'ouverture</h5>
                        <p><strong>Déjeuner:</strong> Lun - Sam, 11h - 15h<br>
                           <strong>Dîner:</strong> Lun - Sam, 18h - 01h</p>
                        <span class="status-badge <?php echo ($opening == 'Ouvert') ? 'open' : 'closed'; ?>">
                            <?php echo $opening; ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-item-sidebar">
                    <div class="info-icon-sidebar">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="info-details-sidebar">
                        <h5>Site Web</h5>
                        <a href="<?php echo $web; ?>" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Visiter le site
                        </a>
                    </div>
                </div>
                
                <div class="info-item-sidebar">
                    <div class="info-icon-sidebar">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-details-sidebar">
                        <h5>Adresse</h5>
                        <p><?php echo $adresse; ?><br>
                           <?php echo $cp . " " . $ville; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- ACTIONS CARD -->
            <div class="actions-card">
                <button class="action-btn-sidebar favorite" id="addWishSidebar">
                    <i class="far fa-heart"></i>
                    <span>Ajouter aux favoris</span>
                </button>
                
                <button class="action-btn-sidebar favorite active" id="removeWishSidebar" style="display:none;">
                    <i class="fas fa-heart"></i>
                    <span>Retirer des favoris</span>
                </button>
                
                <button class="action-btn-sidebar" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    <span>Imprimer</span>
                </button>
                
                <button class="action-btn-sidebar" onclick="shareRestaurant()">
                    <i class="fas fa-share-alt"></i>
                    <span>Partager</span>
                </button>
                
                <a href="leave-review.php?nom=<?php echo urlencode($nom); ?>" class="action-btn-sidebar primary">
                    <i class="fas fa-pen"></i>
                    <span>Écrire un avis</span>
                </a>
            </div>
            
            <!-- SHARE CARD -->
            <div class="share-card">
                <h5>Partager ce restaurant</h5>
                <div class="share-buttons">
                    <button class="share-btn facebook" onclick="shareOn('facebook')" title="Partager sur Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="share-btn twitter" onclick="shareOn('twitter')" title="Partager sur Twitter">
                        <i class="fab fa-twitter"></i>
                    </button>
                    <button class="share-btn whatsapp" onclick="shareOn('whatsapp')" title="Partager sur WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </button>
                    <button class="share-btn copy" onclick="copyLink()" title="Copier le lien">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>
            
        </aside>
        
    </div>
</div>

<script>
// Booking Form Handler
function handleBooking(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    // Simuler une réservation
    alert('Réservation en cours...\n\nCette fonctionnalité sera bientôt disponible !');
    
    return false;
}

// Share Functions
function shareRestaurant() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo $nom; ?>',
            text: 'Découvrez <?php echo $nom; ?> sur Le Bon Resto Halal',
            url: window.location.href
        }).then(() => {
            console.log('Partagé avec succès');
        }).catch(console.error);
    } else {
        copyLink();
    }
}

function shareOn(platform) {
    const url = encodeURIComponent(window.location.href);
    const text = encodeURIComponent('Découvrez <?php echo $nom; ?> sur Le Bon Resto Halal');
    
    let shareUrl = '';
    
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${text}%20${url}`;
            break;
    }
    
    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}

function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Lien copié dans le presse-papier !');
    }).catch(err => {
        console.error('Erreur lors de la copie:', err);
    });
}

// Wishlist AJAX (identique aux boutons du header)
const restoName = '<?php echo $nom; ?>';
const userName = '<?php echo $_SESSION['user']; ?>';

// Vérifier si dans favoris au chargement
console.log('AJAX add/remove', restoName, userName);

$.ajax({
    type: 'GET',
    url: 'api/wishlist.php?action=test&resto=' + restoName + '&user=' + userName,
    success: function(data) {
        console.log('Wishlist status:', data);
        if (data == '1') {
            $('#addWishSidebar').hide();
            $('#removeWishSidebar').show();
        } else {
            $('#addWishSidebar').show();
            $('#removeWishSidebar').hide();
        }
    }
});

// Ajouter aux favoris
$('#addWishSidebar').click(function() {
    $.ajax({
        type: 'GET',
        url: 'api/wishlist.php?action=add&resto=' + restoName + '&user=' + userName,
        success: function(data) {
            console.log('Ajouté aux favoris:', data);
            $('#addWishSidebar').hide();
            $('#removeWishSidebar').show();
            
            // Sync avec le header
            $('#favoriteBtn').addClass('active');
            $('#favoriteBtn i').removeClass('far').addClass('fas');
        }
    });
});

// Retirer des favoris
$('#removeWishSidebar').click(function() {
    $.ajax({
        type: 'GET',
        url: 'api/wishlist.php?action=remove&resto=' + restoName + '&user=' + userName,
        success: function(data) {
            console.log('Retiré des favoris:', data);
            $('#removeWishSidebar').hide();
            $('#addWishSidebar').show();
            
            // Sync avec le header
            $('#favoriteBtn').removeClass('active');
            $('#favoriteBtn i').removeClass('fas').addClass('far');
        }
    });
});
</script>

<style>
/* ============================================
   PHOTO GALLERY MODAL
   ============================================ */
.gallery-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.gallery-modal.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.gallery-modal-content {
    width: 90%;
    max-width: 1200px;
    height: 90%;
    position: relative;
    display: flex;
    flex-direction: column;
}

.gallery-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    color: white;
}

.gallery-modal-title {
    font-size: 20px;
    font-weight: 700;
}

.gallery-modal-counter {
    font-size: 16px;
    opacity: 0.8;
}

.gallery-close-btn {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--text-dark);
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
    z-index: 10001;
}

.gallery-close-btn:hover {
    transform: rotate(90deg) scale(1.1);
    background: var(--secondary-color);
    color: white;
}

.gallery-main-image {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
}

.gallery-main-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: var(--radius-md);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.gallery-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    border: none;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: var(--text-dark);
    transition: var(--transition);
    box-shadow: var(--shadow-lg);
}

.gallery-nav-btn:hover {
    background: white;
    transform: translateY(-50%) scale(1.1);
}

.gallery-nav-btn.prev {
    left: 20px;
}

.gallery-nav-btn.next {
    right: 20px;
}

.gallery-thumbnails {
    display: flex;
    gap: 10px;
    padding: 20px;
    overflow-x: auto;
    scrollbar-width: thin;
}

.gallery-thumbnails::-webkit-scrollbar {
    height: 6px;
}

.gallery-thumbnails::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.gallery-thumbnails::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

.gallery-thumb {
    width: 100px;
    height: 80px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    cursor: pointer;
    flex-shrink: 0;
    opacity: 0.5;
    transition: var(--transition);
    border: 3px solid transparent;
}

.gallery-thumb:hover {
    opacity: 0.8;
}

.gallery-thumb.active {
    opacity: 1;
    border-color: var(--primary-color);
}

.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ============================================
   MODERN FOOTER
   ============================================ */
.modern-footer {
    background: linear-gradient(135deg, #1a1a1a, #2d2d2d);
    color: white;
    padding: 60px 0 30px;
    margin-top: 80px;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 40px;
    margin-bottom: 40px;
}

.footer-section h4 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: white;
}

.footer-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-section ul li {
    margin-bottom: 12px;
}

.footer-section ul li a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 14px;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.footer-section ul li a:hover {
    color: var(--primary-color);
    transform: translateX(5px);
}

.footer-section ul li a i {
    color: var(--primary-color);
}

.footer-logo {
    margin-bottom: 20px;
}

.footer-logo img {
    height: 40px;
}

.footer-description {
    font-size: 14px;
    line-height: 1.8;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 20px;
}

.social-links {
    display: flex;
    gap: 12px;
}

.social-link {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: var(--transition);
    font-size: 18px;
}

.social-link:hover {
    background: var(--primary-color);
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0, 170, 108, 0.4);
}

.newsletter-form {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.newsletter-input {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-sm);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 14px;
}

.newsletter-input::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.newsletter-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.newsletter-btn {
    background: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: var(--radius-sm);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
}

.newsletter-btn:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
}

.footer-bottom {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    padding-top: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-copyright {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
}

.footer-links {
    display: flex;
    gap: 20px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 14px;
    transition: var(--transition);
}

.footer-links a:hover {
    color: var(--primary-color);
}

.payment-methods {
    display: flex;
    gap: 10px;
    align-items: center;
}

.payment-methods i {
    font-size: 32px;
    color: rgba(255, 255, 255, 0.6);
}

/* ============================================
   SCROLL TO TOP BUTTON
   ============================================ */
.scroll-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 50%;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
    z-index: 999;
}

.scroll-to-top.visible {
    display: flex;
}

.scroll-to-top:hover {
    background: var(--primary-hover);
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 170, 108, 0.4);
}

/* ============================================
   LOADING SPINNER
   ============================================ */
.page-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    transition: opacity 0.5s ease;
}

.page-loader.hidden {
    opacity: 0;
    pointer-events: none;
}

.loader-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid var(--border-color);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ============================================
   RESPONSIVE
   ============================================ */
@media (max-width: 992px) {
    .footer-content {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .footer-content {
        grid-template-columns: 1fr;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }
    
    .gallery-nav-btn {
        width: 45px;
        height: 45px;
        font-size: 20px;
    }
    
    .gallery-nav-btn.prev {
        left: 10px;
    }
    
    .gallery-nav-btn.next {
        right: 10px;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
}
</style>

 

<!-- PHOTO GALLERY MODAL -->
<div class="gallery-modal" id="galleryModal">
    <button class="gallery-close-btn" onclick="closeGallery()">
        <i class="fas fa-times"></i>
    </button>
    
    <div class="gallery-modal-content">
        <div class="gallery-modal-header">
            <div class="gallery-modal-title">Photos de <?php echo $nom; ?></div>
            <div class="gallery-modal-counter" id="galleryCounter">1 / 4</div>
        </div>
        
        <div class="gallery-main-image">
            <button class="gallery-nav-btn prev" onclick="prevImage()">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <img id="galleryMainImage" src="" alt="Photo">
            
            <button class="gallery-nav-btn next" onclick="nextImage()">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <div class="gallery-thumbnails" id="galleryThumbnails">
            <!-- Thumbnails will be inserted here -->
        </div>
    </div>
</div>

<!-- MODERN FOOTER -->
<footer class="modern-footer">
    <div class="container">
        <div class="footer-content">
            <!-- À propos -->
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="images/icons/logo2_footer.png" alt="Le Bon Resto Halal">
                </div>
                <p class="footer-description">
                    Découvrez les meilleurs restaurants halal et sans alcool partout en France. 
                    Réservez facilement et profitez d'une expérience culinaire exceptionnelle.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>
            
            <!-- Liens rapides -->
            <div class="footer-section">
                <h4>Liens rapides</h4>
                <ul>
                    <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li><a href="about.html"><i class="fas fa-info-circle"></i> Qui sommes-nous ?</a></li>
                    <li><a href="help.html"><i class="fas fa-utensils"></i> Recommander un établissement</a></li>
                    <li><a href="account.html"><i class="fas fa-user"></i> Mon compte</a></li>
                    <li><a href="blog.html"><i class="fas fa-blog"></i> Blog</a></li>
                    <li><a href="contacts.html"><i class="fas fa-envelope"></i> Contact</a></li>
                </ul>
            </div>
            
            <!-- Catégories -->
            <div class="footer-section">
                <h4>Catégories</h4>
                <ul>
                    <li><a href="#"><i class="fas fa-star"></i> Top restaurants</a></li>
                    <li><a href="#"><i class="fas fa-trophy"></i> Les mieux notés</a></li>
                    <li><a href="#"><i class="fas fa-euro-sign"></i> Meilleur rapport qualité/prix</a></li>
                    <li><a href="#"><i class="fas fa-clock"></i> Les plus récents</a></li>
                    <li><a href="#"><i class="fas fa-fire"></i> Tendances</a></li>
                    <li><a href="#"><i class="fas fa-map-marked-alt"></i> Par ville</a></li>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div class="footer-section">
                <h4>Newsletter</h4>
                <p class="footer-description">
                    Inscrivez-vous pour recevoir nos dernières offres et nouveautés.
                </p>
                <form class="newsletter-form" onsubmit="return subscribeNewsletter(event)">
                    <input type="email" 
                           class="newsletter-input" 
                           placeholder="Votre email" 
                           required>
                    <button type="submit" class="newsletter-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-copyright">
                © 2024 Le Bon Resto Halal - Tous droits réservés | Développé avec ❤️ par SamTech
            </div>
            
            <div class="footer-links">
                <a href="#">Conditions d'utilisation</a>
                <a href="#">Politique de confidentialité</a>
                <a href="#">Mentions légales</a>
            </div>
            
            <div class="payment-methods">
                <i class="fab fa-cc-visa"></i>
                <i class="fab fa-cc-mastercard"></i>
                <i class="fab fa-cc-amex"></i>
                <i class="fab fa-cc-paypal"></i>
            </div>
        </div>
    </div>
</footer>

<!-- SCROLL TO TOP BUTTON -->
<button class="scroll-to-top" id="scrollToTop" onclick="scrollToTop()">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

<script>
// ==================== GALLERY MODAL ====================
<?php
$galleryImages = [];
if (!empty($main))   $galleryImages[] = $main;
if (!empty($slide1)) $galleryImages[] = $slide1;
if (!empty($slide2)) $galleryImages[] = $slide2;
if (!empty($slide3)) $galleryImages[] = $slide3;
?>
const galleryImages = <?= json_encode($galleryImages, JSON_UNESCAPED_SLASHES) ?>;

let currentImageIndex = 0;

function openGallery(index) {
    currentImageIndex = index;
    document.getElementById('galleryModal').classList.add('active');
    document.body.style.overflow = 'hidden';
    updateGalleryImage();
    generateThumbnails();
}

function closeGallery() {
    document.getElementById('galleryModal').classList.remove('active');
    document.body.style.overflow = '';
}

function updateGalleryImage() {
    document.getElementById('galleryMainImage').src = galleryImages[currentImageIndex];
    document.getElementById('galleryCounter').textContent = 
        `${currentImageIndex + 1} / ${galleryImages.length}`;
    
    // Update active thumbnail
    document.querySelectorAll('.gallery-thumb').forEach((thumb, index) => {
        if (index === currentImageIndex) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });
}

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
    updateGalleryImage();
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
    updateGalleryImage();
}

function generateThumbnails() {
    const container = document.getElementById('galleryThumbnails');
    container.innerHTML = '';
    
    galleryImages.forEach((src, index) => {
        const thumb = document.createElement('div');
        thumb.className = 'gallery-thumb' + (index === currentImageIndex ? ' active' : '');
        thumb.innerHTML = `<img src="${src}" alt="Thumbnail ${index + 1}">`;
        thumb.onclick = () => {
            currentImageIndex = index;
            updateGalleryImage();
        };
        container.appendChild(thumb);
    });
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (document.getElementById('galleryModal').classList.contains('active')) {
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === 'Escape') closeGallery();
    }
});

// ==================== SCROLL TO TOP ====================
window.addEventListener('scroll', function() {
    const scrollBtn = document.getElementById('scrollToTop');
    if (window.scrollY > 300) {
        scrollBtn.classList.add('visible');
    } else {
        scrollBtn.classList.remove('visible');
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// ==================== NEWSLETTER SUBSCRIPTION ====================
function subscribeNewsletter(event) {
    event.preventDefault();
    const email = event.target.querySelector('input[type="email"]').value;
    
    // Simuler l'inscription
    alert('Merci pour votre inscription !\n\nVous recevrez bientôt nos newsletters à l\'adresse : ' + email);
    event.target.reset();
    
    return false;
}

 

// ==================== SMOOTH SCROLL FOR ANCHOR LINKS ====================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ==================== LAZY LOADING IMAGES ====================
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                }
                imageObserver.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}

// ==================== CONSOLE EASTER EGG ====================
console.log('%c🍽️ Le Bon Resto Halal', 'font-size: 24px; font-weight: bold; color: #00aa6c;');
console.log('%cVersion: 3.0 - TripAdvisor Style Detail Page', 'font-size: 12px; color: #666;');
console.log('%cDéveloppé avec ❤️ par SamTech', 'font-size: 12px; color: #666;');
console.log('%c\nFonctionnalités:', 'font-size: 14px; font-weight: bold; color: #00aa6c;');
console.log('✓ Design moderne type TripAdvisor');
console.log('✓ Gallery photos interactive');
console.log('✓ Système de notation détaillé');
console.log('✓ Réservation en ligne');
console.log('✓ Favoris avec AJAX');
console.log('✓ Partage sur réseaux sociaux');
console.log('✓ Responsive 100%');

// ==================== ANALYTICS (OPTIONAL) ====================
function trackEvent(category, action, label) {
    console.log('Event:', category, action, label);
    // Intégration Google Analytics ici si nécessaire
    // gtag('event', action, { 'event_category': category, 'event_label': label });
}

// Track page view
trackEvent('Page', 'view', 'Detail Restaurant - <?php echo $nom; ?>');

// Track booking clicks
document.querySelector('.btn-book-now')?.addEventListener('click', () => {
    trackEvent('Booking', 'click', '<?php echo $nom; ?>');
});

// Track favorite clicks
document.querySelectorAll('.favorite').forEach(btn => {
    btn.addEventListener('click', () => {
        trackEvent('Favorite', 'toggle', '<?php echo $nom; ?>');
    });
});

// ==================== PERFORMANCE MONITORING ====================
if ('PerformanceObserver' in window) {
    const perfObserver = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            console.log('Performance:', entry.name, entry.duration + 'ms');
        }
    });
    
    perfObserver.observe({ entryTypes: ['measure', 'navigation'] });
}
document.getElementById('shareBtn').addEventListener('click', function() {
    shareRestaurant();
});
// ==================== END OF SCRIPTS ====================
console.log('%c✅ Page chargée avec succès !', 'font-size: 14px; color: #00aa6c; font-weight: bold;');
</script>

</body>
</html>