<?php
/**
 * RESULT.PHP - Design TripAdvisor Style MODERNE
 * Version Complète avec Modal Carte + Toggle Liste/Grille + Scroll Bidirectionnel
 */

session_start();
  // ===== CONFIGURATION BDD =====
$servername = 'localhost';
$username = 'sam';
$password = '123';
$db = 'lebonresto';

// ===== PAGINATION =====
if (isset($_GET['pg']) && !empty($_GET['pg'])) {
    $pg = (int) $_GET['pg'];
} else {
    $pg = 1;
}

if (isset($_GET['nb']) && !empty($_GET['nb'])) {
    $nb = (int) $_GET['nb'];
} else {
    $nb = 12;
}

// ===== RÉCUPÉRATION DU FILTRE PRIX (AVANT LE BLOC POST/GET) =====
$priceFilter = isset($_GET['priceFilter']) ? $_GET['priceFilter'] : 'all';

// ===== RÉCUPÉRATION DES PARAMÈTRES DE RECHERCHE =====
if (isset($_POST['adresse'])) {
     $addr = $_POST['adresse'];
    $type = $_POST['type_list'][0];
    $radius = isset($_POST['searchRadius']) ? (int)$_POST['searchRadius'] : 3000;
    $currentgps = $_POST['currentgps'];
    $tri = 1;
    $options = null;
 $_SESSION['resultURL'] = "result.php?tri=1&adresse=" . urlencode($addr) . 
                         "&foodType=" . urlencode($type) . 
                         "&searchRadius=" . $radius . 
                         "&currentgps=" . urlencode($currentgps) .
                         "&priceFilter=" . urlencode($priceFilter);
    
} else {
 
    $addr = $_GET['adresse'];
    $type = $_GET['foodType'];
    $radius = 1000;
    $currentgps = $_GET['currentgps'];
    
    if (isset($_GET['tri'])) {
        $_SESSION['resultURL'] = $_SERVER['REQUEST_URI'];
        $tri = $_GET['tri'];
    } else {
        $tri = 1;
    }

    if (isset($_GET['options_list'])) {
        $options = $_GET['options_list'];
        foreach ($options as $val) {
            $disOpt[$val] = 1;
        }
    } else {
        $options = null;
    }
}

// ===== PARSER LES COORDONNÉES GPS =====
$gpsArray = explode(',', $currentgps);
if (count($gpsArray) != 2 || empty(trim($gpsArray[0])) || empty(trim($gpsArray[1]))) {
    die("Erreur : Coordonnées GPS invalides. GPS reçu : " . htmlspecialchars($currentgps));
}

$geoc = [
    'lat' => trim($gpsArray[0]),
    'lon' => trim($gpsArray[1])
];

// ===== CONNEXION BDD =====
$conn = new mysqli($servername, $username, $password, $db);
mysqli_set_charset($conn, "utf8mb4");

if ($conn->connect_error) {
    die('Erreur de connexion : ' . $conn->connect_error);
}

// ===== FORMULE DE DISTANCE =====
$distanceFormula = "(((acos(sin((" . $geoc['lat'] . "*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((" . $geoc['lat'] . "*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((" . $geoc['lon'] . "- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344)";
// ===== CLAUSE PRIX POUR SQL =====
$priceClause = "";
if ($priceFilter !== 'all') {
    // Convertir € en $ pour matcher la base de données
    $priceFilter_sql = str_replace('€', '$', $priceFilter);
    $priceFilter_escaped = mysqli_real_escape_string($conn, $priceFilter_sql);
    $priceClause = " AND v.pricerange = '{$priceFilter_escaped}'";
}



// ===== REQUÊTE COUNT =====
if ($type == 'Tous') {
$req = "SELECT COUNT(*) AS nbre FROM vendeur v 
        LEFT JOIN options o ON v.Nom = o.Nom 
        WHERE {$distanceFormula} <= " . $radius . $priceClause;

if (isset($options)) {
        foreach ($options as $val) {
            $val = mysqli_real_escape_string($conn, $val);
            $req .= " AND o.{$val} = '1'";
        }
    }
} else {
    $type_escaped = mysqli_real_escape_string($conn, $type);
$req = "SELECT COUNT(*) AS nbre FROM vendeur v 
        JOIN options o ON v.Nom = o.Nom 
        WHERE {$distanceFormula} <= " . $radius . " 
        AND v.Type = '{$type_escaped}'" . $priceClause;
            
    if (isset($options)) {
        foreach ($options as $val) {
            $val = mysqli_real_escape_string($conn, $val);
            $req .= " AND o.{$val} = '1'";
        }
    }
}

$res = mysqli_query($conn, $req);
if (!$res) {
    die("Erreur SQL lors du comptage des restaurants");
}

$row = mysqli_fetch_assoc($res);
$nbresto = $row['nbre'];

?>
<script>
// Stocker les paramètres de recherche pour JavaScript
window.searchParams = {
    adresse: <?php echo json_encode($addr); ?>,
    foodType: <?php echo json_encode($type); ?>,
    searchRadius: <?php echo json_encode($radius); ?>,
    currentgps: <?php echo json_encode($currentgps); ?>,
    tri: <?php echo json_encode($tri); ?>
};
console.log('📦 Paramètres chargés:', window.searchParams);
</script>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="SamTech">
    <meta name="description" content="Retrouvez et réservez les meilleurs restaurants Halal et sans alcool partout en France">
    <meta name="keywords" content="halal restaurant muslim">
    
    <title>Restaurants - Le bon resto halal</title>
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Existants -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/set1.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <?php include_once('dependencies.php'); ?>



</head>
<body>

<!-- Données cachées pour JavaScript -->
<div class="hidden-data">
    <p id="paddr"><?php echo htmlspecialchars($addr); ?></p>
    <p id="pradius"><?php echo $radius; ?></p>
    <p id="pType"><?php echo htmlspecialchars($type); ?></p>
    <p id="currentgps"><?php echo htmlspecialchars($currentgps); ?></p>
    <p id="start"><?php echo ($pg - 1) * $nb; ?></p>
    <p id="nb"><?php echo $nb; ?></p>
    <p id="tri"><?php echo $tri; ?></p>
    <p id="mOptions"><?php echo base64_encode(serialize($options)); ?></p>
</div>

<!-- Header Original -->
 

<!-- Search Header Sticky -->
<div class="search-header" id="searchHeader">
    <div class="container">
        <div class="search-summary">
            <div class="search-info">
                <h1>
                    <?php echo $nbresto; ?> 
                    <?php echo $type == 'Tous' ? "restaurants" : "restaurants " . htmlspecialchars($type); ?>
                </h1>
                <div class="search-meta">
                    <i class="fas fa-map-marker-alt"></i>
                    Dans un rayon de <strong><?php echo $radius; ?>km</strong> autour de 
                    <strong><?php echo htmlspecialchars($addr); ?></strong>
                </div>
            </div>
            
            <div class="header-actions">
                <!-- Toggle Vue Liste/Grille -->
                <div class="view-toggle">
                    <button class="view-toggle-btn active" id="listViewBtn" onclick="switchView('list')" title="Vue liste">
                        <i class="fas fa-list"></i>
                    </button>
                    <button class="view-toggle-btn" id="gridViewBtn" onclick="switchView('grid')" title="Vue grille">
                        <i class="fas fa-th"></i>
                    </button>
                </div>
                
                <!-- Bouton Modal Carte -->
                <button class="btn-icon" onclick="openMapModal()">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Voir la carte</span>
                </button>
                
                <!-- Bouton Modifier Recherche -->
                <button class="btn-icon btn-primary-custom" onclick="window.location.href='index.php'">
                    <i class="fas fa-search"></i>
                    <span>Modifier</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="results-container">
    <div class="restaurants-section">
        
        <!-- Filters Bar -->
<!-- Filters Bar - VERSION CORRIGÉE -->
<div class="filters-bar">
<div class="filters-left">
    <button class="filter-chip <?php echo $priceFilter === 'all' ? 'active' : ''; ?>" 
            onclick="filterByPriceServer('all')" data-price="all">
        <i class="fas fa-check-circle"></i> Tous les prix
    </button>
    <button class="filter-chip <?php echo $priceFilter === '€' ? 'active' : ''; ?>" 
            onclick="filterByPriceServer('€')" data-price="€">
        € Bon marché
    </button>
    <button class="filter-chip <?php echo $priceFilter === '€€' ? 'active' : ''; ?>" 
            onclick="filterByPriceServer('€€')" data-price="€€">
        €€ Moyen
    </button>
    <button class="filter-chip <?php echo $priceFilter === '€€€' ? 'active' : ''; ?>" 
            onclick="filterByPriceServer('€€€')" data-price="€€€">
        €€€ Élevé
    </button>
</div>
    
    <div class="sort-dropdown">
        <form method="GET" action="result.php" id="sortForm">
            <select name="tri" onchange="this.form.submit()">
                <option value="1" <?php echo $tri == 1 ? 'selected' : ''; ?>>
                    <i class="fas fa-sort-alpha-down"></i> Alphabétique
                </option>
                <option value="2" <?php echo $tri == 2 ? 'selected' : ''; ?>>
                    <i class="fas fa-map-marker-alt"></i> Distance
                </option>
                <option value="3" <?php echo $tri == 3 ? 'selected' : ''; ?>>
                    <i class="fas fa-star"></i> Note
                </option>
            </select>
            <input type="hidden" name="adresse" value="<?php echo htmlspecialchars($addr); ?>">
            <input type="hidden" name="foodType" value="<?php echo htmlspecialchars($type); ?>">
            <input type="hidden" name="searchRadius" value="<?php echo $radius; ?>">
            <input type="hidden" name="currentgps" value="<?php echo htmlspecialchars($currentgps); ?>">
            <input type="hidden" name="pg" value="<?php echo $pg; ?>">
            <input type="hidden" name="nb" value="<?php echo $nb; ?>">
        </form>
    </div>
</div>

        <!-- Liste/Grille des restaurants -->
        <div class="restaurants-grid list-view" id="restaurantsGrid">
        <?php
        $start = ($pg - 1) * $nb;
        
        // REQUÊTE PRINCIPALE
        if ($type == 'Tous') {
$requete = "SELECT 
    {$distanceFormula} AS distance,
    v.gps, v.note, v.nom, v.type, v.adresse, v.codePostal, 
    v.descriptif, v.ville, v.pricerange, p.main
    FROM vendeur v
    LEFT JOIN photos p ON v.Nom = p.Nom
    LEFT JOIN options o ON v.Nom = o.Nom
    WHERE {$distanceFormula} <= " . $radius . $priceClause;
        } else {
            $type_escaped = mysqli_real_escape_string($conn, $type);
            $requete = "SELECT 
    {$distanceFormula} AS distance,
    v.gps, v.note, v.nom, v.type, v.adresse, v.codePostal, 
    v.descriptif, v.ville, v.pricerange, p.main
    FROM vendeur v
    LEFT JOIN photos p ON v.Nom = p.Nom
    LEFT JOIN options o ON v.Nom = o.Nom
    WHERE {$distanceFormula} <= " . $radius . " 
    AND v.Type = '{$type_escaped}'" . $priceClause;
        }

        if (isset($options)) {
            foreach ($options as $val) {
                $val = mysqli_real_escape_string($conn, $val);
                $requete .= " AND o.{$val} = '1'";
            }
        }

        switch ($tri) {
            case 1: $requete .= " ORDER BY v.nom ASC LIMIT {$start}, {$nb}"; break;
            case 2: $requete .= " ORDER BY distance ASC LIMIT {$start}, {$nb}"; break;
            case 3: $requete .= " ORDER BY v.note DESC LIMIT {$start}, {$nb}"; break;
            default: $requete .= " LIMIT {$start}, {$nb}"; break;
        }

        $resultat = mysqli_query($conn, $requete);

    $resultat = mysqli_query($conn, $requete);

// AJOUTEZ CES LIGNES ICI :
// Récupérer TOUS les restaurants pour la carte (sans pagination)
$requete_all = str_replace("LIMIT {$start}, {$nb}", "", $requete);
$resultat_all = mysqli_query($conn, $requete_all);
$all_restaurants_data = [];
if ($resultat_all) {
    while ($ligne_all = $resultat_all->fetch_assoc()) {
        $all_restaurants_data[] = $ligne_all;
    }
}
 // FIN AJOUT

if (!$resultat) {
    echo "<p style='color:red; text-align:center;'>Erreur lors de la récupération des restaurants</p>";
        } else {
            $compteur = 1;
            $allRestaurants = []; // Pour stocker toutes les données
            
            while ($ligne = $resultat->fetch_assoc()) {
                $allRestaurants[] = $ligne; // Stocker pour le modal
                
                $pics = !empty($ligne['main']) 
                    ? $ligne['main'] 
                    : 'assets/images/vendeur/' . $compteur . '.jpg';
                
                $restoId = 'resto-' . preg_replace('/[^a-zA-Z0-9]/', '-', $ligne['nom']);
                $urlDetail = 'detail-restaurant-2.php?nom=' . urlencode($ligne['nom']);
                
// Convertir $ en € pour l'affichage
$priceRange = !empty($ligne['pricerange']) && $ligne['pricerange'] !== 'null' 
    ? str_replace('$', '€', $ligne['pricerange']) 
    : '€€';                $note = !empty($ligne['note']) ? number_format($ligne['note'], 1) : 'N/A';
                $distance = round($ligne['distance'], 1);
                
                $compteur++;
                if ($compteur > 20) { $compteur = 1; }
        ?>
                <div class="restaurant-card" 
                     id="<?php echo $restoId; ?>" 
                     data-resto-name="<?php echo htmlspecialchars($ligne['nom']); ?>" 
                     data-resto-gps="<?php echo htmlspecialchars($ligne['gps']); ?>"
                     data-resto-type="<?php echo htmlspecialchars($ligne['type']); ?>"
                     data-resto-distance="<?php echo $distance; ?>"
                     data-resto-note="<?php echo $note; ?>"
                     data-resto-price="<?php echo $priceRange; ?>"
                     data-resto-image="<?php echo htmlspecialchars($pics); ?>"
                     onclick="window.location.href='<?php echo $urlDetail; ?>'">
            
                    <div class="card-content">
                        <!-- Image -->
                        <div class="card-image">
                             <img src="<?php echo $pics; ?>" 
                                 alt="<?php echo htmlspecialchars($ligne['nom']); ?>"
                                 onerror="this.src='assets/images/vendeur/1.jpg'">
                            <div class="image-badge">
                                <i class="fas fa-route"></i> <?php echo $distance; ?> km
                            </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="card-info">
                            <div>
                                <div class="card-header">
                                    <div>
                                        <h3 class="restaurant-name"><?php echo htmlspecialchars($ligne['nom']); ?></h3>
                                        <p class="restaurant-type">
                                            <i class="fas fa-utensils"></i> 
                                            <?php echo htmlspecialchars($ligne['type']); ?>
                                        </p>
                                    </div>
                                    
                                    <?php if ($note !== 'N/A'): ?>
                                    <div class="rating-badge">
                                        <i class="fas fa-star"></i>
                                        <?php echo $note; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="card-details">
                                    <div class="detail-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($ligne['adresse']); ?>, <?php echo htmlspecialchars($ligne['ville']); ?></span>
                                    </div>
                                    
                                    <?php if (!empty($ligne['descriptif'])): ?>
                                    <div class="detail-item">
                                        <i class="fas fa-info-circle"></i>
                                        <span><?php echo htmlspecialchars(substr($ligne['descriptif'], 0, 120)); ?>...</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="price-range">
                                    <?php echo $priceRange; ?>
                                </div>
                                
                                <button class="view-on-map-btn" 
                                        onclick="event.stopPropagation(); openMapModalAndFocus('<?php echo htmlspecialchars($ligne['nom']); ?>', '<?php echo htmlspecialchars($ligne['gps']); ?>')">
                                    <i class="fas fa-map-pin"></i> Voir sur la carte
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
            }
        }
        ?>
</div>

        <!-- AJOUTEZ CE BLOC ICI -->
        <script>
        var allRestaurantsForMap = <?php echo json_encode($all_restaurants_data); ?>;
         </script>
        <!-- FIN AJOUT -->

        <!-- Pagination -->
        <nav class="pagination-container">
            <ul class="pagination">
            <?php
            $pgs = ceil($nbresto / $nb);
            
            // Bouton Précédent
            if ($pg > 1) {
echo '<li class="page-item">
       <a class="page-link" href="result.php?' . 
       (isset($tri) ? "tri={$tri}&" : '') . 
       'pg=' . ($pg - 1) . 
       '&nb=' . $nb . 
       '&adresse=' . urlencode($addr) . 
       '&foodType=' . urlencode($type) . 
       '&searchRadius=' . $radius . 
       '&currentgps=' . urlencode($currentgps) . 
       '&priceFilter=' . urlencode($priceFilter) . '">
                       <i class="fas fa-chevron-left"></i> Précédente
                       </a>
                     </li>';
            }

            // Numéros de page
            for ($i = 1; $i <= $pgs; $i++) {
 echo '<li class="page-item ' . ($pg === $i ? 'active' : '') . '">
       <a class="page-link" href="result.php?' . 
       (isset($tri) ? "tri={$tri}&" : '') . 
       'pg=' . $i . 
       '&nb=' . $nb . 
       '&adresse=' . urlencode($addr) . 
       '&foodType=' . urlencode($type) . 
       '&searchRadius=' . $radius . 
       '&currentgps=' . urlencode($currentgps) . 
       '&priceFilter=' . urlencode($priceFilter) . '">' . $i . '</a>
     </li>';
            }

            // Bouton Suivant
            if ($pg < $pgs) {
echo '<li class="page-item">
       <a class="page-link" href="result.php?' . 
       (isset($tri) ? "tri={$tri}&" : '') . 
       'pg=' . ($pg + 1) . 
       '&nb=' . $nb . 
       '&adresse=' . urlencode($addr) . 
       '&foodType=' . urlencode($type) . 
       '&searchRadius=' . $radius . 
       '&currentgps=' . urlencode($currentgps) . 
       '&priceFilter=' . urlencode($priceFilter) . '">
                       Suivante <i class="fas fa-chevron-right"></i>
                       </a>
                     </li>';
            }
            
            mysqli_close($conn);
            ?>
            </ul>
        </nav>
        
    </div>
</div>

<!-- MODAL CARTE (STYLE TRIPADVISOR) -->
<!-- MODAL CARTE (STYLE TRIPADVISOR) - VERSION CORRIGÉE -->
<<div class="map-modal" id="mapModal">
    <div class="map-modal-wrapper">
        
        <!-- Header -->
        <div class="modal-map-header">
            <div class="modal-header-left">
                <button class="modal-close-btn" onclick="closeMapModal()">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="modal-title-section">
                    <h2>Carte des restaurants</h2>
                    <div class="modal-subtitle">
                        <span class="count" id="visibleCount">0</span> restaurants visibles sur 
                        <span class="count" id="totalCount">0</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-search-bar">
                <input type="text" 
                       placeholder="Rechercher un restaurant..." 
                       id="modalSearchInput"
                       oninput="searchRestaurants(this.value)">
                <i class="fas fa-search modal-search-icon"></i>
            </div>
        </div>
        
        <!-- Filtres -->
        <div class="modal-filters-bar" id="modalFiltersBar">
            <!-- Prix -->
            <!-- Prix -->
<button class="filter-btn-modal active" onclick="filterModalBy('price', 'all')" data-filter="price-all">
    <i class="fas fa-euro-sign"></i>
    <span>Tous les prix</span>
</button>
<button class="filter-btn-modal" onclick="filterModalBy('price', '1')" data-filter="price-1">
    <span>€</span>
</button>
<button class="filter-btn-modal" onclick="filterModalBy('price', '2')" data-filter="price-2">
    <span>€€</span>
</button>
<button class="filter-btn-modal" onclick="filterModalBy('price', '3')" data-filter="price-3">
    <span>€€€</span>
</button>

<!-- Note (CORRIGÉ) -->
<button class="filter-btn-modal" onclick="filterModalBy('rating', '3')" data-filter="rating-3">
    <i class="fas fa-star"></i>
    <span>3+</span>
</button>
<button class="filter-btn-modal" onclick="filterModalBy('rating', '4')" data-filter="rating-4">
    <i class="fas fa-star"></i>
    <span>4+</span>
</button>
<button class="filter-btn-modal" onclick="filterModalBy('rating', '4.5')" data-filter="rating-4.5">
    <i class="fas fa-star"></i>
    <span>4.5+</span>
</button>
            
            <!-- Distance -->
            <button class="filter-btn-modal" onclick="filterModalBy('distance', '2')" data-filter="distance-2">
                <i class="fas fa-map-marker-alt"></i>
                <span>< 2km</span>
            </button>
            <button class="filter-btn-modal" onclick="filterModalBy('distance', '5')" data-filter="distance-5">
                <i class="fas fa-map-marker-alt"></i>
                <span>< 5km</span>
            </button>
            
            <!-- Ouvert maintenant (à implémenter plus tard) -->
            <button class="filter-btn-modal" onclick="filterModalBy('open', 'now')" data-filter="open-now">
                <i class="fas fa-clock"></i>
                <span>Ouvert maintenant</span>
            </button>
            
            <!-- Reset -->
            <button class="filter-btn-modal" onclick="resetModalFilters()" style="border-color: var(--secondary-color); color: var(--secondary-color);">
                <i class="fas fa-redo"></i>
                <span>Réinitialiser</span>
            </button>
        </div>
        
        <!-- Contenu principal -->
        <div class="modal-map-content">
            
            <!-- Carte -->
          
            
            <!-- Liste -->
            <div class="modal-list-section">
                <div class="modal-list-header">
                    <h3>Restaurants</h3>
                    <select class="modal-sort-select" onchange="sortModalRestaurants(this.value)">
                        <option value="distance">Trier par : Distance</option>
                        <option value="rating">Trier par : Note</option>
                        <option value="name">Trier par : Nom</option>
                        <option value="price-asc">Trier par : Prix (croissant)</option>
                        <option value="price-desc">Trier par : Prix (décroissant)</option>
                    </select>
                </div>
                
                <div class="modal-restaurants-list" id="modalRestoList">
                    <!-- Rempli dynamiquement par JS -->
                </div>
            </div>
             <div class="modal-map-section">
                <div id="modalMap"></div>
            </div>
        </div>
        
    </div>
</div>

<!-- Footer -->
<footer class="main-block dark-bg">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="copyright">
                    <p>Le Bon Resto Halal © 2024</p>
                    <ul>
                        <li><a href="#"><span class="ti-facebook"></span></a></li>
                        <li><a href="#"><span class="ti-twitter-alt"></span></a></li>
                        <li><a href="#"><span class="ti-instagram"></span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
console.log('🔧 Chargement du script principal...');

// ==================== FONCTIONS GLOBALES PRIORITAIRES ====================
// Définir IMMÉDIATEMENT les fonctions appelées par onclick

window.filterByPriceServer = function(priceLevel) {
    console.log('🔄 Fonction filterByPriceServer appelée avec:', priceLevel);
    
    var urlParams = new URLSearchParams();
    
    // Si on a des paramètres GET dans l'URL, les récupérer
    var currentParams = new URLSearchParams(window.location.search);
    
    // Sinon utiliser les paramètres stockés depuis PHP
    if (currentParams.toString() === '' && window.searchParams) {
        // Première navigation après POST - utiliser les données PHP
        urlParams.set('adresse', window.searchParams.adresse);
        urlParams.set('foodType', window.searchParams.foodType);
        urlParams.set('searchRadius', window.searchParams.searchRadius);
        urlParams.set('currentgps', window.searchParams.currentgps);
        urlParams.set('tri', window.searchParams.tri);
    } else {
        // Navigation GET - conserver tous les paramètres existants
        currentParams.forEach((value, key) => {
            urlParams.set(key, value);
        });
    }
    
    // Ajouter/modifier le filtre de prix et réinitialiser la pagination
    if (priceLevel === 'all') {
        urlParams.delete('priceFilter');
    } else {
        urlParams.set('priceFilter', priceLevel);
    }
    urlParams.set('pg', '1');
    
    var newUrl = 'result.php?' + urlParams.toString();
    console.log('📍 Redirection vers:', newUrl);
    
    window.location.href = newUrl;
};

window.resetAllFilters = function() {
    console.log('🔄 Réinitialisation des filtres');
    window.filterByPriceServer('all');
};

console.log('✅ Fonctions de filtrage chargées');

// Tester immédiatement
if (typeof window.filterByPriceServer === 'function') {
    console.log('✅ filterByPriceServer est accessible');
} else {
    console.error('❌ filterByPriceServer N\'EST PAS accessible');
}

// ==================== VARIABLES GLOBALES ====================
var modalMapInstance = null;
var modalMarkersLayer = null;
var allModalMarkers = {};
var userModalMarker = null;
var currentView = 'list';
var allRestaurantsData = []; // Tous les restaurants
var visibleRestaurantsData = []; // Restaurants visibles sur la carte
var mapBounds = null;
var activeFilters = {
    price: 'all',
    rating: null,
    distance: null,
    open: null,
    search: ''
};
var isFirstMapLoad = true; // 🔥 AJOUTEZ CETTE LIGNE

var currentSort = 'distance';

// ==================== SWITCH VIEW (LISTE / GRILLE) ====================
window.switchView = function(view) {
    currentView = view;
    var grid = document.getElementById('restaurantsGrid');
    var listBtn = document.getElementById('listViewBtn');
    var gridBtn = document.getElementById('gridViewBtn');
    
    if (view === 'list') {
        grid.classList.remove('grid-view');
        grid.classList.add('list-view');
        listBtn.classList.add('active');
        gridBtn.classList.remove('active');
    } else {
        grid.classList.remove('list-view');
        grid.classList.add('grid-view');
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
    }
    
    sessionStorage.setItem('viewPreference', view);
};

// ==================== OPEN MAP MODAL ====================
window.openMapModal = function() {
    var modal = document.getElementById('mapModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Construire allRestaurantsData depuis les données PHP
    if (typeof allRestaurantsForMap !== 'undefined' && allRestaurantsData.length === 0) {
        allRestaurantsData = allRestaurantsForMap.map(function(resto) {
            return {
                name: resto.nom,
                gps: resto.gps,
                type: resto.type,
                distance: resto.distance,
                note: resto.note,
                price: resto.pricerange,
                adresse: resto.adresse,
                ville: resto.ville,
                main: resto.main,
                lat: parseFloat(resto.gps.split(',')[0]),
                lng: parseFloat(resto.gps.split(',')[1])
            };
        });
    }
    
    if (!modalMapInstance) {
        setTimeout(function() {
            initModalMap();
            updateVisibleRestaurants();
        }, 300);
    } else {
        setTimeout(function() {
            modalMapInstance.invalidateSize();
            updateVisibleRestaurants();
        }, 300);
    }
};

// ✨ NOUVELLE FONCTION : METTRE À JOUR LES RESTAURANTS VISIBLES
function updateVisibleRestaurants() {
    if (!modalMapInstance) return;
    
    console.log('🔄 updateVisibleRestaurants: Début');
    
    // Obtenir les limites de la carte
    mapBounds = modalMapInstance.getBounds();
    
    // 🔥 FIX : Partir des restaurants DÉJÀ FILTRÉS par prix/note/distance
    var baseRestaurants = applyCurrentFiltersToArray(allRestaurantsData);
    
    console.log('📦 Restaurants après filtres actifs:', baseRestaurants.length);
    
    // Filtrer par visibilité carte
    visibleRestaurantsData = baseRestaurants.filter(function(resto) {
        return mapBounds.contains([resto.lat, resto.lng]);
    });
    
    console.log('📍 Restaurants visibles sur carte:', visibleRestaurantsData.length);
    
    // 🔥 NOUVEAU : TRIER avant d'afficher
    visibleRestaurantsData = sortRestaurantsArray(visibleRestaurantsData, currentSort);
    console.log('🔀 Tri appliqué:', currentSort);
    
    // Mettre à jour les compteurs
    document.getElementById('visibleCount').textContent = visibleRestaurantsData.length;
    document.getElementById('totalCount').textContent = allRestaurantsData.length;
    
    // Remplir la liste avec seulement les restaurants visibles
    populateModalListDynamic(visibleRestaurantsData);
    
    // Masquer le banner
    var banner = document.getElementById('mapUpdateBanner');
    if (banner) banner.classList.remove('show');
    
    console.log('✅ updateVisibleRestaurants: Terminé');
} 

// ✨ NOUVELLE FONCTION : AFFICHER LE BANNER "RECHERCHER"
 

// ✨ NOUVELLE FONCTION : POPULATE LISTE DYNAMIQUE
// ==================== POPULATE LISTE (MODIFIÉE) ====================
function populateModalListDynamic(restaurants) {
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('📋 populateModalListDynamic appelée');
    console.log('📥 Restaurants reçus:', restaurants.length);
    console.log('📝 Noms reçus:', restaurants.map(r => r.name));
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    
    var modalList = document.getElementById('modalRestoList');
    
    if (!modalList) {
        console.error('❌ Element #modalRestoList introuvable !');
        return;
    }
    
    modalList.innerHTML = '';
    
    if (restaurants.length === 0) {
        modalList.innerHTML = '<div class="modal-empty-state">' +
            '<i class="fas fa-search"></i>' +
            '<h4>Aucun restaurant trouvé</h4>' +
            '<p>Essayez de modifier vos filtres ou de déplacer la carte</p>' +
            '</div>';
        console.log('⚠️ Liste vide affichée');
        return;
    }
    
    console.log('🔨 Génération de', restaurants.length, 'items...');
    
    restaurants.forEach(function(resto, index) {
        var imgSrc = resto.main || 'assets/images/vendeur/1.jpg';
           console.log("XXXXXXXXXXXXXXXXXXXXXXXXX" + resto.main);

        var itemDiv = document.createElement('div');
        itemDiv.className = 'modal-resto-item';
        itemDiv.setAttribute('data-resto-name', resto.name);
        
        itemDiv.onclick = function(e) {
            if (!e.target.classList.contains('modal-resto-map-btn')) {
                focusOnModalMarker(resto.name, resto.gps);
            }
        };
        
        itemDiv.innerHTML = 
            '<button class="modal-resto-map-btn" onclick="event.stopPropagation(); focusOnModalMarker(\'' + resto.name.replace(/'/g, "\\'") + '\', \'' + resto.gps + '\')">' +
                '<i class="fas fa-map-marker-alt"></i> Carte' +
            '</button>' +
            '<div class="modal-resto-content">' +
                '<a href="detail-restaurant-2.php?nom=' + encodeURIComponent(resto.name) + '"> <img src="' + imgSrc + '" alt="' + resto.name + '" class="modal-resto-image" onerror="this.src=\'assets/images/vendeur/1.jpg\'"></a>' +
                '<div class="modal-resto-details">' +
                    '<div class="modal-resto-name">' + resto.name + '</div>' +
                    '<div class="modal-resto-type"><i class="fas fa-utensils"></i> ' + resto.type + '</div>' +
                    '<div class="modal-resto-meta">' +
                        '<div class="modal-resto-info"><i class="fas fa-route"></i> ' + parseFloat(resto.distance).toFixed(1) + ' km</div>' +
                        (resto.price && resto.price !== 'null' ? '<div class="modal-resto-info"><i class="fas fa-euro-sign"></i> ' + resto.price.replace(/\$/g, '€') + '</div>' : '') +
                        (resto.note && resto.note !== 'N/A' ? '<div class="modal-resto-rating"><i class="fas fa-star"></i> ' + resto.note + '</div>' : '') +
                    '</div>' +
                '</div>' +
            '</div>';
        
        modalList.appendChild(itemDiv);
     });
    
    console.log('✅ populateModalListDynamic terminée');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
}

// ==================== INIT AU CHARGEMENT ====================
$(document).ready(function() {
    // Initialiser les filtres au premier chargement
    console.log('🎯 Initialisation des filtres modal');
});
// ==================== CLOSE MAP MODAL ====================
window.closeMapModal = function() {
    var modal = document.getElementById('mapModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    console.log('❌ Fermeture du modal carte');
    
    document.querySelectorAll('.modal-resto-item').forEach(function(item) {
        item.classList.remove('active');
    });
};

// ==================== INIT MODAL MAP ====================
function initModalMap() {
    if (modalMapInstance !== null) {
        return;
    }

    var currentgps = document.getElementById('currentgps').textContent.trim();
    var radius = parseFloat(document.getElementById('pradius').textContent.trim());
    
    var gpsArray = currentgps.split(',');
    if (gpsArray.length !== 2 || !gpsArray[0].trim() || !gpsArray[1].trim()) {
        alert('Erreur : Coordonnées GPS invalides');
        closeMapModal();
        return;
    }
    
    var userLat = parseFloat(gpsArray[0].trim());
    var userLon = parseFloat(gpsArray[1].trim());

    modalMapInstance = L.map('modalMap', {
        zoomControl: true,
        scrollWheelZoom: true
    }).setView([userLat, userLon], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(modalMapInstance);
    
    modalMarkersLayer = L.markerClusterGroup({
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 16
    });
    
    var userIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    L.marker([userLat, userLon], {icon: userIcon})
        .addTo(modalMapInstance)
        .bindPopup('<div class="custom-popup"><b>📍 Votre position</b></div>');
    
    L.circle([userLat, userLon], {
        color: '#00aa6c',
        fillColor: '#00aa6c',
        fillOpacity: 0.1,
        radius: radius * 1000
    }).addTo(modalMapInstance);
    
    loadRestaurantsOnModalMap();
    
    // ✨ NOUVEAU : Événements pour la carte dynamique
 // ✅ AJOUTEZ CES LIGNES dans initModalMap()
modalMapInstance.on('moveend', function() {
    updateVisibleRestaurants(); // Met à jour automatiquement
});

modalMapInstance.on('zoomend', function() {
    updateVisibleRestaurants(); // Met à jour automatiquement
});
}
 


// ==================== FONCTION FILTRE GÉNÉRIQUE ====================
function filterModalBy(filterType, value) {
    console.log('🔍 Filtre:', filterType, '=', value);
    
    // Mettre à jour l'état du filtre
    if (activeFilters[filterType] === value) {
        // Si déjà actif, le désactiver
        activeFilters[filterType] = (filterType === 'price') ? 'all' : null;
    } else {
        activeFilters[filterType] = value;
    }
    
    // Mettre à jour les boutons visuellement
    updateFilterButtons(filterType, activeFilters[filterType]);
    
    // Appliquer les filtres
    applyAllFilters();
}

// ==================== FONCTION : GÉNÉRER LES CARDS MANQUANTES ====================
function ensureAllCardsExist() {
    console.log('🔧 ensureAllCardsExist: Vérification des cards manquantes');
    
    var existingCards = new Set();
    document.querySelectorAll('.restaurant-card').forEach(function(card) {
        existingCards.add(card.getAttribute('data-resto-name'));
    });
    
     
    // Trouver les restaurants manquants
    var missingRestaurants = allRestaurantsData.filter(function(resto) {
        return !existingCards.has(resto.name);
    });
    
    if (missingRestaurants.length === 0) {
         return;
    }
    
     
    // Trouver le conteneur
    var container = document.querySelector('#restaurantsGrid');
    
    if (!container) {
        console.error('❌ Conteneur de restaurants introuvable');
        return;
    }
    
    // Générer les cards manquantes
    missingRestaurants.forEach(function(resto) {
        var priceRange = resto.price ? resto.price.replace(/\$/g, '€') : '€€';
        var note = resto.note && resto.note !== 'N/A' ? parseFloat(resto.note).toFixed(1) : 'N/A';
        var distance = parseFloat(resto.distance).toFixed(1);
        var imgSrc = resto.main || 'assets/images/vendeur/1.jpg';
        
        var card = document.createElement('div');
        card.className = 'restaurant-card';
        card.setAttribute('data-resto-name', resto.name);
        card.setAttribute('data-resto-type', resto.type);
        card.setAttribute('data-resto-distance', distance);
        card.setAttribute('data-resto-note', note);
        card.setAttribute('data-resto-gps', resto.gps);
        card.setAttribute('data-resto-price', priceRange);
        card.setAttribute('data-resto-image', imgSrc);
        
        // ✅ IMPORTANT : NE PAS masquer par défaut !
        // Les cards seront masquées par hideUnfilteredCards() si nécessaire
        card.style.display = 'none'; // Masquée par défaut car pas dans la pagination initiale
        
        var urlDetail = 'detail-restaurant-2.php?nom=' + encodeURIComponent(resto.name);
        
        card.onclick = function() {
            window.location.href = urlDetail;
        };
        
        card.innerHTML = `
            <div class="card-content">
                <div class="card-image">
                    <img src="${imgSrc}" 
                         alt="${resto.name}"
                         onerror="this.src='assets/images/vendeur/1.jpg'">
                    <div class="image-badge">
                        <i class="fas fa-route"></i> ${distance} km
                    </div>
                </div>
                
                <div class="card-info">
                    <div>
                        <div class="card-header">
                            <div>
                                <h3 class="restaurant-name">${resto.name}</h3>
                                <p class="restaurant-type">
                                    <i class="fas fa-utensils"></i> 
                                    ${resto.type}
                                </p>
                            </div>
                            
                            ${note !== 'N/A' ? `
                            <div class="rating-badge">
                                <i class="fas fa-star"></i>
                                ${note}
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="card-details">
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${resto.adresse}, ${resto.ville}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="price-range">
                            ${priceRange}
                        </div>
                        
                        <button class="view-on-map-btn" 
                                onclick="event.stopPropagation(); openMapModalAndFocus('${resto.name.replace(/'/g, "\\'")}', '${resto.gps}')">
                            <i class="fas fa-map-pin"></i> Voir sur la carte
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(card);
     });
    
 }

// ==================== METTRE À JOUR LES BOUTONS VISUELLEMENT ====================
function updateFilterButtons(filterType, activeValue) {
    // Retirer active de tous les boutons de ce type
    document.querySelectorAll('.filter-btn-modal[data-filter^="' + filterType + '-"]').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Ajouter active au bouton sélectionné
    if (activeValue) {
        var activeBtn = document.querySelector('.filter-btn-modal[data-filter="' + filterType + '-' + activeValue + '"]');
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }
}
// ===== FIX 2 : NOUVELLE FONCTION - MASQUER LES CARDS HTML =====
ensureAllCardsExist();
function hideUnfilteredCards(filteredRestaurants) {
     
    // Créer un Set des noms filtrés
    var filteredNames = new Set(filteredRestaurants.map(r => r.name));
    
    // Récupérer toutes les cards HTML
    var allCards = document.querySelectorAll('.restaurant-card');
    
    if (allCards.length === 0) {
        console.error('❌ ERREUR : Aucune card trouvée !');
        return;
    }
    
 
    
    var hidden = 0;
    var shown = 0;
    
    // ✅ PARCOURIR TOUTES LES CARDS
    allCards.forEach(function(card) {
        var cardName = card.getAttribute('data-resto-name');
        var shouldBeVisible = filteredNames.has(cardName);
        
        // ✅ LOGIQUE SIMPLE ET CLAIRE
        if (shouldBeVisible) {
            // Cette card DOIT être visible
            if (card.style.display === 'none') {
                card.style.display = '';
                shown++;
             }
        } else {
            // Cette card DOIT être masquée
            if (card.style.display !== 'none') {
                card.style.display = 'none';
                hidden++;
                console.log('  ❌ Masquée:', cardName);
            }
        }
    });
    
 
    // ✅ VÉRIFICATION FINALE
    var visibleAfter = Array.from(allCards).filter(c => c.style.display !== 'none').length;
     
    if (visibleAfter !== filteredRestaurants.length) {
        console.error('⚠️ ATTENTION: Décalage détecté !');
        console.error('   Attendu:', filteredRestaurants.length);
        console.error('   Réel:', visibleAfter);
    } else {
     }
}


// ✨ NOUVELLE FONCTION : FILTRER PAR VISIBILITÉ CARTE
function filterByMapBounds(restaurants) {
    if (!modalMapInstance) {
         return restaurants;
    }
    
    var bounds = modalMapInstance.getBounds();
    
    var visible = restaurants.filter(function(resto) {
        return bounds.contains([resto.lat, resto.lng]);
    });
    
     
    return visible;
}

// 🔥 NOUVELLE FONCTION : APPLIQUER LES FILTRES ACTIFS À UN TABLEAU
function applyCurrentFiltersToArray(restaurants) {
    return restaurants.filter(function(resto) {
        
        // FILTRE PRIX
        if (activeFilters.price !== 'all') {
            var restoPrice = resto.price ? resto.price.replace(/[^€$]/g, '').length : 0;
            var filterPrice = parseInt(activeFilters.price);
            
            if (restoPrice !== filterPrice) {
                return false;
            }
        }
        
        // FILTRE NOTE
        if (activeFilters.rating) {
            var restoNote = parseFloat(resto.note);
            var minRating = parseFloat(activeFilters.rating);
            
            if (isNaN(restoNote) || restoNote < minRating) {
                return false;
            }
        }
        
        // FILTRE DISTANCE
        if (activeFilters.distance) {
            var restoDistance = parseFloat(resto.distance);
            var maxDistance = parseFloat(activeFilters.distance);
            if (isNaN(restoDistance) || restoDistance > maxDistance) {
                return false;
            }
        }
        
        // FILTRE RECHERCHE
        if (activeFilters.search) {
            var searchTerm = activeFilters.search.toLowerCase();
            var restoName = resto.name.toLowerCase();
            var restoType = resto.type ? resto.type.toLowerCase() : '';
            
            if (!restoName.includes(searchTerm) && !restoType.includes(searchTerm)) {
                return false;
            }
        }
        
        return true;
    });
}

 

// ==================== APPLIQUER TOUS LES FILTRES ====================

// ==================== APPLIQUER TOUS LES FILTRES ====================
function applyAllFilters() {
 
    
    // Génerer les cards manquantes
    ensureAllCardsExist();
    
    var filtered = allRestaurantsData.filter(function(resto) {
        
        // FILTRE PRIX
        if (activeFilters.price !== 'all') {
            var restoPrice = resto.price ? resto.price.replace(/[^€$]/g, '').length : 0;
            var filterPrice = parseInt(activeFilters.price);
            
            if (restoPrice !== filterPrice) {
                return false;
            }
        }
        
        // FILTRE NOTE
        if (activeFilters.rating) {
            var restoNote = parseFloat(resto.note);
            var minRating = parseFloat(activeFilters.rating);
            
            if (isNaN(restoNote) || restoNote < minRating) {
                return false;
            }
        }
        
        // FILTRE DISTANCE
        if (activeFilters.distance) {
            var restoDistance = parseFloat(resto.distance);
            var maxDistance = parseFloat(activeFilters.distance);
            if (isNaN(restoDistance) || restoDistance > maxDistance) {
                return false;
            }
        }
        
        // FILTRE RECHERCHE
        if (activeFilters.search) {
            var searchTerm = activeFilters.search.toLowerCase();
            var restoName = resto.name.toLowerCase();
            var restoType = resto.type ? resto.type.toLowerCase() : '';
            
            if (!restoName.includes(searchTerm) && !restoType.includes(searchTerm)) {
                return false;
            }
        }
        
        return true;
    });
    
     
    // Trier
    filtered = sortRestaurantsArray(filtered, currentSort);
    
 
    hideUnfilteredCards(filtered);
    
     // 🔥 Vérifier si isFirstMapLoad existe
    var shouldPreserveView = (typeof isFirstMapLoad !== 'undefined' && !isFirstMapLoad && modalMapInstance !== null);
     updateMapMarkers(filtered, shouldPreserveView);
    
    // 🔥 FIX : Filtrer ENCORE par visibilité sur la carte
     var visibleFiltered = filterByMapBounds(filtered);
    
      populateModalListDynamic(visibleFiltered);
    
    // Mettre à jour les compteurs
    if (document.getElementById('visibleCount')) {
        document.getElementById('visibleCount').textContent = visibleFiltered.length;
    }
    if (document.getElementById('totalCount')) {
        document.getElementById('totalCount').textContent = allRestaurantsData.length;
    }
    
 
}





 
function loadAllRestaurants() {
    console.log('🔄 loadAllRestaurants: Chargement forcé de tous les restaurants');
    
    // Vérifier si toutes les cards sont déjà chargées
    var currentCards = document.querySelectorAll('.restaurant-card').length;
    console.log('📄 Cards actuellement dans le DOM:', currentCards);
    console.log('📦 Total attendu:', allRestaurantsData.length);
    
    if (currentCards >= allRestaurantsData.length) {
        console.log('✅ Toutes les cards sont déjà chargées');
        return;
    }
    
    // ✅ OPTION 1 : Si vous avez un bouton "Voir plus"
    var loadMoreBtn = document.querySelector('.load-more-btn, #loadMoreBtn, [data-action="load-more"]');
    if (loadMoreBtn) {
        console.log('🔘 Bouton "Voir plus" trouvé, simulation de clics...');
        while (document.querySelectorAll('.restaurant-card').length < allRestaurantsData.length) {
            loadMoreBtn.click();
        }
        return;
    }
    
    // ✅ OPTION 2 : Si vous avez une fonction loadMore() ou displayRestaurants()
    if (typeof displayRestaurants === 'function') {
        console.log('📋 Fonction displayRestaurants trouvée, affichage de tous');
        displayRestaurants(allRestaurantsData); // Afficher TOUS les restos
        return;
    }
    
    // ✅ OPTION 3 : Désactiver la limite de pagination
    if (typeof itemsPerPage !== 'undefined') {
        console.log('📄 Variable itemsPerPage trouvée, désactivation de la limite');
        var originalLimit = itemsPerPage;
        itemsPerPage = 9999; // Limite très haute
        
        // Recharger l'affichage
        if (typeof renderRestaurants === 'function') {
            renderRestaurants();
        }
        
        // Restaurer la limite après filtrage
        setTimeout(function() {
            itemsPerPage = originalLimit;
        }, 1000);
        return;
    }
    
    console.warn('⚠️ Impossible de charger automatiquement tous les restaurants');
    console.log('💡 Conseil : Cherchez dans votre code la fonction qui affiche les restaurants');
}

// ==================== RECHERCHE ====================
function searchRestaurants(searchTerm) {
    activeFilters.search = searchTerm;
    applyAllFilters();
}

// ==================== RESET FILTRES ====================
function resetModalFilters() {
    console.log('🔄 Réinitialisation des filtres');
    
    activeFilters = {
        price: 'all',
        rating: null,
        distance: null,
        open: null,
        search: ''
    };
    
    // Réinitialiser les boutons
    document.querySelectorAll('.filter-btn-modal').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector('.filter-btn-modal[data-filter="price-all"]').classList.add('active');
    
    // Réinitialiser la recherche
    var searchInput = document.getElementById('modalSearchInput');
    if (searchInput) searchInput.value = '';
    
    // Réinitialiser le tri
    currentSort = 'distance';
    var sortSelect = document.querySelector('.modal-sort-select');
    if (sortSelect) sortSelect.value = 'distance';
    
    // ✅ RÉAFFICHER TOUTES LES CARDS HTML
    document.querySelectorAll('.restaurant-card').forEach(card => {
        card.style.display = '';
    });
    
    // Réappliquer
    applyAllFilters();
}
// ==================== TRI ====================
function sortModalRestaurants(sortType) {
    console.log('🔀 Tri:', sortType);
    currentSort = sortType;
    applyAllFilters();
}

function sortRestaurantsArray(restaurants, sortType) {
    var sorted = [...restaurants]; // Copie
    
    switch(sortType) {
        case 'distance':
            sorted.sort((a, b) => parseFloat(a.distance) - parseFloat(b.distance));
            break;
            
        case 'rating':
            sorted.sort((a, b) => {
                var noteA = parseFloat(a.note) || 0;
                var noteB = parseFloat(b.note) || 0;
                return noteB - noteA; // Décroissant
            });
            break;
            
        case 'name':
            sorted.sort((a, b) => a.name.localeCompare(b.name));
            break;
            
        case 'price-asc':
            sorted.sort((a, b) => {
                var priceA = a.price ? a.price.length : 0;
                var priceB = b.price ? b.price.length : 0;
                return priceA - priceB;
            });
            break;
            
        case 'price-desc':
            sorted.sort((a, b) => {
                var priceA = a.price ? a.price.length : 0;
                var priceB = b.price ? b.price.length : 0;
                return priceB - priceA;
            });
            break;
    }
    
    return sorted;
}

// ==================== METTRE À JOUR LES MARQUEURS CARTE ====================
function updateMapMarkers(restaurants) {
    if (!modalMarkersLayer || !modalMapInstance) return;
    
    // Vider les marqueurs
    modalMarkersLayer.clearLayers();
    allModalMarkers = {};
    
    var restoIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    var bounds = [];
    
    restaurants.forEach(function(resto) {
        if (!resto.gps || resto.gps === 'N/A') return;
        
        var lat = resto.lat;
        var lng = resto.lng;
        
        if (isNaN(lat) || isNaN(lng)) return;
        
        bounds.push([lat, lng]);
        
        var popupContent = '<div class="custom-popup">' +
            '<h5>' + resto.name + '</h5>' +
            '<p><strong>Type:</strong> ' + resto.type + '<br>' +
            '<strong>Distance:</strong> ' + parseFloat(resto.distance).toFixed(1) + ' km<br>' +
            (resto.price ? '<strong>Prix:</strong> ' + resto.price.replace(/\$/g, '€') + '<br>' : '') +
            '</p>' +
            (resto.note !== 'N/A' ? '<div class="rating">⭐ ' + resto.note + '</div>' : '') +
            '<button class="popup-view-btn" onclick="highlightRestoInList(\'' + resto.name.replace(/'/g, "\\'") + '\')">Voir dans la liste</button>' +
            '</div>';
        
        var marker = L.marker([lat, lng], {icon: restoIcon}).bindPopup(popupContent);
        
        marker.on('click', function() {
            highlightRestoInList(resto.name);
        });
        
        modalMarkersLayer.addLayer(marker);
        allModalMarkers[resto.name] = marker;
    });
    
    modalMapInstance.addLayer(modalMarkersLayer);
    
    // Ajuster la vue si des restaurants
    if (bounds.length > 0) {
        // Ajouter la position utilisateur
        var currentgps = document.getElementById('currentgps').textContent.trim();
        var gpsArray = currentgps.split(',');
        if (gpsArray.length === 2) {
            var userLat = parseFloat(gpsArray[0].trim());
            var userLon = parseFloat(gpsArray[1].trim());
            if (!isNaN(userLat) && !isNaN(userLon)) {
                bounds.push([userLat, userLon]);
            }
        }
        
        modalMapInstance.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 14
        });
    }
}

// ==================== LOAD RESTAURANTS ON MODAL MAP ====================
function loadRestaurantsOnModalMap() {
    if (!modalMarkersLayer) return;
    
    modalMarkersLayer.clearLayers();
    allModalMarkers = {};
    
    var restoIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
    
    allRestaurantsData.forEach(function(resto) {
        if (!resto.gps || resto.gps === 'N/A') return;
        
        var gps = resto.gps.split(',');
        if (gps.length !== 2) return;
        
        var lat = parseFloat(gps[0].trim());
        var lon = parseFloat(gps[1].trim());
        
        if (isNaN(lat) || isNaN(lon)) return;
        
        var popupContent = '<div class="custom-popup">' +
            '<h5>' + resto.name + '</h5>' +
            '<p><strong>Type:</strong> ' + resto.type + '<br>' +
            '<strong>Distance:</strong> ' + (resto.distance ? parseFloat(resto.distance).toFixed(1) : '0') + ' km</p>' +
            (resto.note !== 'N/A' ? '<div class="rating">⭐ ' + resto.note + '</div>' : '') +
            '<button class="popup-view-btn" onclick="highlightRestoInList(\'' + resto.name.replace(/'/g, "\\'") + '\')">Voir dans la liste</button>' +
            '</div>';
        
        var marker = L.marker([lat, lon], {icon: restoIcon}).bindPopup(popupContent);
        
        // ✨ NOUVEAU : Au clic sur marker → highlight liste
        marker.on('click', function() {
            highlightRestoInList(resto.name);
        });
        
        modalMarkersLayer.addLayer(marker);
        allModalMarkers[resto.name] = marker;
    });
    
    modalMapInstance.addLayer(modalMarkersLayer);
}
function highlightRestoInList(restoName) {
    // Retirer tous les highlights
    document.querySelectorAll('.modal-resto-item').forEach(function(item) {
        item.classList.remove('active', 'highlight');
    });
    
    // Trouver l'item
    var item = document.querySelector('.modal-resto-item[data-resto-name="' + restoName + '"]');
    if (item) {
        item.classList.add('active', 'highlight');
        
        // Scroll vers l'item
        item.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
        
        // Retirer le highlight après 2s
        setTimeout(function() {
            item.classList.remove('highlight');
        }, 2000);
    }
}
// ==================== FOCUS ON MODAL MARKER ====================
function focusOnModalMarker(restoName, restoGps) {
    if (!modalMapInstance) return;
    
    var gps = restoGps.split(',');
    var lat = parseFloat(gps[0].trim());
    var lon = parseFloat(gps[1].trim());
    
    // Centrer la carte
    modalMapInstance.setView([lat, lon], 16, {
        animate: true,
        duration: 0.5
    });
    
    // Ouvrir le popup
    if (allModalMarkers[restoName]) {
        setTimeout(function() {
            allModalMarkers[restoName].openPopup();
        }, 500);
    }
    
    // Highlight dans la liste
    document.querySelectorAll('.modal-resto-item').forEach(function(item) {
        item.classList.remove('active');
    });
    
    var activeItem = document.querySelector('.modal-resto-item[data-resto-name="' + restoName + '"]');
    if (activeItem) {
        activeItem.classList.add('active');
    }
}
// ==================== OPEN MODAL AND FOCUS ====================
window.openMapModalAndFocus = function(restoName, restoGps) {
    openMapModal();
    setTimeout(function() {
        focusOnModalMarker(restoName, restoGps);
    }, 600);
};

// ==================== SCROLL TO RESTO AND CLOSE ====================
window.scrollToRestoAndClose = function(restoName) {
    closeMapModal();
    setTimeout(function() {
        scrollToResto(restoName);
    }, 300);
};

// ==================== SCROLL TO RESTO ====================
window.scrollToResto = function(restoName) {
    var restoElement = document.querySelector('[data-resto-name="' + restoName + '"]');
    if (restoElement) {
        restoElement.classList.add('highlight');
        
        $('html, body').animate({
            scrollTop: $(restoElement).offset().top - 200
        }, 500);
        
        setTimeout(function() {
            restoElement.classList.remove('highlight');
        }, 2000);
    }
};

// ==================== STICKY HEADER SCROLL EFFECT ====================
var lastScroll = 0;
window.addEventListener('scroll', function() {
    var header = document.getElementById('searchHeader');
    var currentScroll = window.pageYOffset;
    
    if (currentScroll > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
});

// ==================== CLOSE MODAL WITH ESC ====================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMapModal();
    }
});

// ==================== CLOSE MODAL CLICK OUTSIDE ====================
document.getElementById('mapModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMapModal();
    }
});

// ==================== INIT ON LOAD ====================
$(document).ready(function() {
    console.log('📄 Document prêt');
    
    var savedView = sessionStorage.getItem('viewPreference');
    if (savedView === 'grid') {
        switchView('grid');
    }
    
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $(this.getAttribute('href'));
        if(target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 600);
        }
    });
});

// ==================== RESPONSIVE MODAL MAP ====================
$(window).on('resize', function() {
    if (modalMapInstance) {
        setTimeout(function() {
            modalMapInstance.invalidateSize();
        }, 300);
    }
});

console.log('✅ Script principal chargé complètement');
</script>

</body>
</html>