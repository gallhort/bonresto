 <?php
require_once 'auth/auth.php'; // gère session + cookie remember_user

// Récupérer le nombre de favoris si l'utilisateur est connecté
$nbrFav = 0;
if (isset($_SESSION['user'])) {
    $stmtFav = $dbh->prepare("SELECT COUNT(*) FROM wishlist WHERE user = ?");
    $stmtFav->execute([$_SESSION['user']]);
    $nbrFav = $stmtFav->fetchColumn();
}

?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #FF385C;
            --primary-dark: #E31C5F;
            --dark: #222222;
            --gray: #717171;
            --light-gray: #F7F7F7;
            --white: #FFFFFF;
            --border: #DDDDDD;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        /* TOP BAR */
        .top-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 0;
            transition: all 0.3s ease;
        }

        .top-bar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-left {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            opacity: 0.95;
        }

        .contact-item:hover {
            opacity: 1;
            transform: translateX(3px);
        }

        .contact-item i {
            font-size: 16px;
        }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-link {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            color: white;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .social-link:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .add-listing-btn {
            background: white;
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .add-listing-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            background: var(--light-gray);
        }

        /* MAIN HEADER */
        .main-header {
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 9999;
            transition: all 0.3s ease;
        }

        .main-header.scrolled {
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.12);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 60px;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo img {
            height: 45px;
            width: auto;
        }

        /* NAVIGATION */
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 40px;
            margin: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: var(--dark);
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 28px 0;
            display: block;
            position: relative;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 24px;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 2px;
        }

        .nav-link:hover,
        .nav-item.active .nav-link {
            color: var(--primary);
        }

        .nav-link:hover::after,
        .nav-item.active .nav-link::after {
            width: 100%;
        }

        /* HEADER RIGHT */
        .header-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .header-icon-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s ease;
            padding: 8px 12px;
            border-radius: 12px;
            position: relative;
        }

        .header-icon-btn i {
            font-size: 22px;
            transition: all 0.3s ease;
        }

        .header-icon-btn span {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-icon-btn:hover {
            background: var(--light-gray);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .header-icon-btn:hover i {
            transform: scale(1.1);
        }

        /* Badge pour favoris */
        .header-icon-btn.favorites {
            position: relative;
        }

        .favorites-badge {
            position: absolute;
            top: 4px;
            right: 8px;
            background: var(--primary);
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        /* User Menu */
        .user-menu {
            position: relative;
        }

        .user-menu-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            border: 1px solid var(--border);
            border-radius: 50px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-menu-btn:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .user-menu-btn i {
            font-size: 20px;
            color: var(--gray);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        /* Mobile Menu Toggle */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: var(--dark);
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            background: var(--light-gray);
        }

        /* User Dropdown */
        .user-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .user-dropdown-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.2s ease;
            border-bottom: 1px solid var(--light-gray);
        }

        .user-dropdown-item:last-child {
            border-bottom: none;
        }

        .user-dropdown-item:hover {
            background: var(--light-gray);
            padding-left: 24px;
        }

        .user-dropdown-item i {
            font-size: 18px;
            color: var(--gray);
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .header-container,
            .top-bar-container {
                padding: 0 20px;
            }

            .nav-menu {
                gap: 25px;
            }

            .header-left {
                gap: 40px;
            }
        }

        @media (max-width: 768px) {
            .top-bar {
                padding: 10px 0;
            }

            .top-bar-left {
                flex-direction: column;
                gap: 8px;
                align-items: flex-start;
            }

            .contact-item {
                font-size: 12px;
            }

            .top-bar-right {
                flex-direction: column;
                gap: 10px;
            }

            .add-listing-btn {
                font-size: 12px;
                padding: 6px 16px;
            }

            .header-container {
                height: 70px;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: white;
                flex-direction: column;
                padding: 30px;
                gap: 0;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
                transition: left 0.3s ease;
                overflow-y: auto;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-link {
                padding: 20px 0;
                border-bottom: 1px solid var(--light-gray);
                width: 100%;
            }

            .nav-link::after {
                display: none;
            }

            .header-right {
                gap: 10px;
            }

            .header-icon-btn span {
                display: none;
            }

            .header-icon-btn {
                padding: 8px;
            }

            .social-links {
                gap: 10px;
            }
        }

        /* Animation for page load */
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .main-header {
            animation: slideDown 0.5s ease-out;
        }

        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
 
    <!-- TOP BAR -->
        <div class="top-bar">
            <div class="top-bar-container">
                <div class="top-bar-left">
                    <a href="tel:+33767883631" class="contact-item">
                        <i class="fas fa-phone-alt"></i>
                        <span>+33 767 883 631</span>
                    </a>
                    <a href="mailto:sourtirane@yahoo.fr" class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>sourtirane@yahoo.fr</span>
                    </a>
                </div>
                <div class="top-bar-right">
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Pinterest">
                            <i class="fab fa-pinterest-p"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                        <?php
    // Vérifier si l'utilisateur est connecté 
 
    if (isset($_SESSION['user'])) { ?>
        
                    <a href="inscription-restaurant.php" class="add-listing-btn">
                        <i class="fas fa-plus"></i>
                        <span>Ajouter établissement</span>
                    </a>
         <?php } ?>
    
   

   



               
           
                </div>
            </div>
        </div>

    <!-- MAIN HEADER -->
    <header class="main-header" id="mainHeader">
        <div class="header-container">
            <div class="header-left">
                <a href="index.php" class="logo">
                    <img src="images/icons/logo.png" alt="Logo" height="45">
                </a>

                <nav>
                    <ul class="nav-menu" id="navMenu">
                        <li class="nav-item active">
                            <a href="index.php" class="nav-link">Home</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">Qui sommes-nous ?</a>
                        </li>
                        <li class="nav-item">
                            <a href="contact.html" class="nav-link">Contact</a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="header-right">
<a href="profil.php?tab=wishlist" class="header-icon-btn favorites">
    <i class="far fa-heart"></i>
    <span>Favoris</span>
    <?php if ($nbrFav > 0): ?>
        <span class="favorites-badge"><?= $nbrFav ?></span>
    <?php endif; ?>
</a>
 

 <?php if (!isset($_SESSION['user'])): ?>

    <!-- Version NON connecté -->
    <a href="auth/login_signup.php" class="header-icon-btn">
        <i class="fas fa-user"></i>
        <span>Connexion</span>
    </a>

<?php else: ?>

    <?php
    // Vérifier si l'utilisateur est admin
    $isAdmin = false;
    if (isset($_SESSION['user'])) {
        $stmtAdmin = $dbh->prepare("SELECT admin FROM users WHERE login = ?");
        $stmtAdmin->execute([$_SESSION['user']]);
        $userAdmin = $stmtAdmin->fetch();
        $isAdmin = ($userAdmin && $userAdmin['admin'] == 1);
    }
    ?>

    <!-- Version CONNECTÉ -->
    <div class="user-menu">
        <div class="user-menu-btn" onclick="toggleUserDropdown()">
            <i class="fas fa-bars"></i>
            <div class="user-avatar <?= $isAdmin ? 'admin-avatar' : '' ?>">
                <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
            </div>
        </div>

        <div class="user-dropdown" id="userDropdown">
            <?php if ($isAdmin): ?>
                <a href="admin-dashboard.php" class="user-dropdown-item admin-item">
                    <i class="fas fa-crown"></i>
                    <span>Dashboard Admin</span>
                </a>
                <div class="dropdown-divider"></div>
            <?php endif; ?>
            
            <a href="profil.php" class="user-dropdown-item">
                <i class="fas fa-user-circle"></i>
                <span>Mon profil</span>
            </a>
            <a href="mes-reservations.php" class="user-dropdown-item">
                <i class="fas fa-calendar-check"></i>
                <span>Mes réservations</span>
            </a>
            <a href="parametres.php" class="user-dropdown-item">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
            <a href="auth/login_signup.php?logout" class="user-dropdown-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

<?php endif; ?>

<style>
/* Style pour l'avatar admin */
.admin-avatar {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
    box-shadow: 0 0 15px rgba(240, 147, 251, 0.5);
}

/* Style pour l'item admin dans le dropdown */
.admin-item {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white !important;
    font-weight: 600;
}

.admin-item i {
    color: #ffd700 !important;
}

.admin-item:hover {
    background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
    transform: translateX(5px);
}

/* Séparateur */
.dropdown-divider {
    height: 1px;
    background: #e2e8f0;
    margin: 8px 0;
}
</style>


                <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

 

    <script>
        // Toggle Mobile Menu
        function toggleMobileMenu() {
            const navMenu = document.getElementById('navMenu');
            const menuToggle = document.querySelector('.mobile-menu-toggle i');
            navMenu.classList.toggle('active');
            menuToggle.classList.toggle('fa-bars');
            menuToggle.classList.toggle('fa-times');
        }

        // Toggle User Dropdown
        function toggleUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const dropdown = document.getElementById('userDropdown');
            if (userMenu && !userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });

        // Header scroll effect
        let lastScroll = 0;
        const header = document.getElementById('mainHeader');

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });



        // Active nav item on click
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navLinks.forEach(l => l.parentElement.classList.remove('active'));
                this.parentElement.classList.add('active');
            });
        });

        // Close mobile menu when clicking nav link
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                const navMenu = document.getElementById('navMenu');
                const menuToggle = document.querySelector('.mobile-menu-toggle i');
                navMenu.classList.remove('active');
                menuToggle.classList.add('fa-bars');
                menuToggle.classList.remove('fa-times');
            });
        });
    </script>
 