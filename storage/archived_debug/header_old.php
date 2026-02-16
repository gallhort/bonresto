<style>
:root {
    --primary: #FF385C;
    --primary-dark: #E31C5F;
    --dark: #222222;
    --gray: #717171;
    --light-gray: #F7F7F7;
    --white: #FFFFFF;
    --border: #DDDDDD;
}

/* TOP BAR */
.top-bar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 12px 0;
}

.top-bar-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.contact-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: white;
    text-decoration: none;
    font-size: 14px;
    margin-right: 20px;
}

.contact-item:hover {
    opacity: 0.8;
}

/* MAIN NAVBAR */
.navbar {
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.navbar-brand {
    font-size: 28px;
    font-weight: 800;
    color: var(--primary);
    text-decoration: none;
}

.navbar-menu {
    display: flex;
    gap: 40px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.navbar-menu a {
    color: var(--dark);
    text-decoration: none;
    font-weight: 500;
    font-size: 15px;
    transition: color 0.3s;
}

.navbar-menu a:hover {
    color: var(--primary);
}

.navbar-actions {
    display: flex;
    gap: 15px;
    align-items: center;
}

.btn {
    padding: 10px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

.btn-outline {
    background: transparent;
    border: 2px solid var(--primary);
    color: var(--primary);
}

.btn-outline:hover {
    background: var(--primary);
    color: white;
}

.wishlist-badge {
    position: relative;
}

.wishlist-badge .badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: bold;
}
</style>

<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-container">
        <div class="top-bar-left">
            <a href="tel:+33123456789" class="contact-item">
                <i class="fas fa-phone"></i>
                <span>01 23 45 67 89</span>
            </a>
            <a href="mailto:contact@lebonresto.fr" class="contact-item">
                <i class="fas fa-envelope"></i>
                <span>contact@lebonresto.fr</span>
            </a>
        </div>
        <div class="top-bar-right">
            <a href="#" class="contact-item">
                <i class="fab fa-facebook"></i>
            </a>
            <a href="#" class="contact-item">
                <i class="fab fa-instagram"></i>
            </a>
            <a href="#" class="contact-item">
                <i class="fab fa-twitter"></i>
            </a>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="<?= url('/') ?>" class="navbar-brand">
            🍽️ Le Bon Resto
        </a>
        
        <ul class="navbar-menu">
            <li><a href="<?= url('/') ?>">Accueil</a></li>
            <li><a href="<?= url('/search') ?>">Rechercher</a></li>
            <li><a href="<?= url('/restaurants') ?>">Tous les restaurants</a></li>
            <li><a href="<?= url('/add-restaurant') ?>">Ajouter un restaurant</a></li>
        </ul>
        
        <div class="navbar-actions">
            <?php if(isAuth()): ?>
                <a href="<?= url('/wishlist') ?>" class="btn btn-outline wishlist-badge">
                    <i class="fas fa-heart"></i> Favoris
                    <?php 
                    $wishlistCount = 0;
                    if(isset($_SESSION['user_id'])) {
                        // TODO: Compter les favoris
                        $wishlistCount = 0;
                    }
                    if($wishlistCount > 0): 
                    ?>
                        <span class="badge"><?= $wishlistCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= url('/profil') ?>" class="btn btn-outline">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="<?= url('/logout') ?>" class="btn btn-primary">
                    Déconnexion
                </a>
            <?php else: ?>
                <a href="<?= url('/login') ?>" class="btn btn-outline">
                    Connexion
                </a>
                <a href="<?= url('/register') ?>" class="btn btn-primary">
                    Inscription
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
