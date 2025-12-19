<?php
 


?>




<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Le bon resto annuaire des restaurants Halal en France</title>
    <link rel="apple-touch-icon" sizes="180x180" href="images/icons/logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="images/icons/logo.png">
    <link rel="icon" type="image/png" sizes="16x16" href="images/icons/logo.png">
    <link rel="manifest" href="assets/images/favicons/site.webmanifest">
    <meta name="description" content="Retrouvez et réservez les meilleurs restaurants Halal et sans alcool partout en France avec le bon resto halal ">

    <meta name="keywords" content="halal hallal restaurant muslim">
    <!-- Fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sacramento&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">

    <!-- Css-->
    <?php include_once('dependencies.php'); ?>

</head>

<body>
 

    <div class="page-wrapper">


       <?php include_once('header.php'); ?>



        <!-- Banner Section One Start -->

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            overflow-x: hidden;
        }

        :root {
            --primary: #FF385C;
            --primary-dark: #E31C5F;
            --dark: #222222;
            --gray: #717171;
            --light-gray: #F7F7F7;
            --white: #FFFFFF;
        }

        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .background-slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .bg-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 2s ease-in-out;
            animation: kenburns 20s ease-in-out infinite;
        }

        .bg-slide.active {
            opacity: 0.4;
        }

        @keyframes kenburns {
            0%, 100% { transform: scale(1) translate(0, 0); }
            50% { transform: scale(1.1) translate(-2%, -2%); }
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);
            z-index: 1;
        }

        .container {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .hero-content {
            text-align: center;
            color: white;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .subtitle {
            font-size: 1.5rem;
            font-weight: 300;
            margin-bottom: 1rem;
            opacity: 0.95;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .main-title {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .description {
            font-size: 1.3rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            font-weight: 300;
        }

        .tabs-container {
            margin-bottom: 2rem;
        }

        .tabs {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
        }

        .tab-btn {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 12px 32px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .tab-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .tab-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .tab-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .tab-btn.active {
            background: white;
            color: var(--dark);
            border-color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .tab-btn span {
            position: relative;
            z-index: 1;
        }

        .search-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideUp 0.6s ease-out 0.3s both;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .search-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .input-group {
            position: relative;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 18px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: var(--dark);
            font-weight: 500;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 56, 92, 0.1);
            transform: translateY(-2px);
        }

        .input-group input::placeholder {
            color: var(--gray);
            font-weight: 400;
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 18px 48px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 20px rgba(255, 56, 92, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
        }

        .search-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(255, 56, 92, 0.4);
        }

        .search-btn:active {
            transform: translateY(-1px);
        }

        .search-icon {
            width: 20px;
            height: 20px;
        }

        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1;
        }

        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: float 6s ease-in-out infinite;
        }

        .circle-1 {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .circle-2 {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .circle-3 {
            width: 80px;
            height: 80px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) rotate(180deg);
            }
        }

        @media (max-width: 768px) {
            .main-title {
                font-size: 2.5rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .description {
                font-size: 1rem;
            }

            .search-card {
                padding: 24px;
            }

            .tabs {
                gap: 1rem;
            }

            .tab-btn {
                padding: 10px 20px;
                font-size: 0.9rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
 
    <section class="hero-section">
        <div class="background-slider" id="bgSlider">
            <div class="bg-slide active" style="background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920&q=80');"></div>
            <div class="bg-slide" style="background-image: url('https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1920&q=80');"></div>
            <div class="bg-slide" style="background-image: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920&q=80');"></div>
            <div class="bg-slide" style="background-image: url('https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1920&q=80');"></div>
        </div>
        
        <div class="overlay"></div>
        
        <div class="floating-elements">
            <div class="floating-circle circle-1"></div>
            <div class="floating-circle circle-2"></div>
            <div class="floating-circle circle-3"></div>
        </div>

        <div class="container">
            <div class="hero-content">
                <div class="subtitle">Découvrir</div>
                <h1 class="main-title">Des merveilleux endroits</h1>
                <p class="description">Découvrez les meilleurs lieux où séjourner, manger, faire du shopping près de chez vous.</p>

                <div class="tabs-container">
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="restaurants">
                            <span>🍽️ Restaurants</span>
                        </button>
                        <button class="tab-btn" data-tab="places">
                            <span>📍 Lieux</span>
                        </button>
                        <button class="tab-btn" data-tab="events">
                            <span>🎉 Événements</span>
                        </button>
                        <button class="tab-btn" data-tab="jobs">
                            <span>💼 Jobs</span>
                        </button>
                    </div>
<style>
    /* ============================================
   AUTOCOMPLETE CITY - CSS MODERNE
   ============================================ */

:root {
    --ac-primary: #4f46e5;
    --ac-primary-dark: #4338ca;
    --ac-bg: #ffffff;
    --ac-border: #e5e7eb;
    --ac-hover: #f3f4f6;
    --ac-selected: #eef2ff;
    --ac-text: #1f2937;
    --ac-text-secondary: #6b7280;
    --ac-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    --ac-radius: 8px;
    --ac-transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Wrapper de l'input */
.autocomplete-wrapper {
    position: relative;
    width: 100%;
}

/* Dropdown container */
.autocomplete-dropdown {
    position: absolute;
    z-index: 9999;
    background: var(--ac-bg);
    border: 1px solid var(--ac-border);
    border-radius: var(--ac-radius);
    box-shadow: var(--ac-shadow);
    max-height: 300px;
    overflow: hidden;
    opacity: 0;
    transform: translateY(-10px);
    pointer-events: none;
    transition: var(--ac-transition);
}

.autocomplete-dropdown.open {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}

.autocomplete-dropdown.open-upward {
    transform-origin: bottom;
}

/* Liste des résultats */
.autocomplete-list {
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 4px;
}

/* Scrollbar personnalisée */
.autocomplete-list::-webkit-scrollbar {
    width: 8px;
}

.autocomplete-list::-webkit-scrollbar-track {
    background: transparent;
}

.autocomplete-list::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.autocomplete-list::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Item de résultat */
.autocomplete-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    border-radius: 6px;
    transition: var(--ac-transition);
    color: var(--ac-text);
    font-size: 14px;
    line-height: 1.5;
}

.autocomplete-item:hover {
    background: var(--ac-hover);
}

.autocomplete-item.selected {
    background: var(--ac-selected);
    color: var(--ac-primary);
}

.autocomplete-item.no-result,
.autocomplete-item.error,
.autocomplete-item.loading {
    cursor: default;
    color: var(--ac-text-secondary);
    justify-content: center;
    padding: 20px;
}

.autocomplete-item.error {
    color: #dc2626;
}

/* Icône */
.autocomplete-icon {
    font-size: 18px;
    flex-shrink: 0;
}

/* Texte */
.autocomplete-text {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.autocomplete-text strong {
    color: var(--ac-primary);
    font-weight: 600;
}

/* Loader animation */
.autocomplete-loader {
    width: 16px;
    height: 16px;
    border: 2px solid var(--ac-border);
    border-top-color: var(--ac-primary);
    border-radius: 50%;
    animation: autocomplete-spin 0.8s linear infinite;
}

@keyframes autocomplete-spin {
    to { transform: rotate(360deg); }
}

/* Animation d'entrée */
@keyframes autocomplete-fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .autocomplete-dropdown {
        max-height: 250px;
        left: 10px !important;
        right: 10px !important;
        width: calc(100% - 20px) !important;
    }
    
    .autocomplete-list {
        max-height: 250px;
    }
    
    .autocomplete-item {
        padding: 14px 12px;
        font-size: 15px;
    }
}

/* Focus visible pour accessibilité */
.autocomplete-item:focus-visible {
    outline: 2px solid var(--ac-primary);
    outline-offset: -2px;
}

/* État désactivé */
.autocomplete-wrapper input:disabled ~ .autocomplete-dropdown {
    opacity: 0.5;
    pointer-events: none;
}

/* Thème sombre (optionnel) */
@media (prefers-color-scheme: dark) {
    :root {
        --ac-bg: #1f2937;
        --ac-border: #374151;
        --ac-hover: #374151;
        --ac-selected: #312e81;
        --ac-text: #f9fafb;
        --ac-text-secondary: #9ca3af;
        --ac-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    }
}

</style>
                    <div class="search-card">
                        <div class="tab-content active" id="restaurants">
                               <form class="search-form" method="post" action="result.php">
        <input type="hidden" id="currentgps" name="currentgps">
        
        <div class="form-row">
            <div class="input-group">
                <input type="text" id="autoC" name="adresse" placeholder="📍 Quelle adresse ?" required>
            </div>
            
            <div class="input-group">
                <select name="type_list[]" id="typelist" required>
                    <option value="" disabled selected>🍴 Spécialités ?</option>
                    <option value="Tous">Tous</option>
                    <option value="Restaurant Italien">Italien</option>
                    <option value="Restaurant Français">Français</option>
                    <option value="Fast Food">Fast Food</option>
                    <option value="Pizzeria">Pizzeria</option>
                    <option value="Café">Café</option>
                </select>
            </div>
        </div>
        
        <!-- NOUVEAU: Radius Slider -->
        <div class="radius-group">
            <div class="radius-label">
                <span>📍 Distance de recherche</span>
                <span class="radius-value" id="radiusDisplay">5 km</span>
            </div>
            <div class="radius-slider-container">
                <input 
                    type="range" 
                    id="searchRadius" 
                    name="searchRadius" 
                    min="1" 
                    max="50" 
                    value="5" 
                    step="1"
                >
                <div class="radius-marks">
                    <span>1 km</span>
                    <span>10 km</span>
                    <span>25 km</span>
                    <span>50 km</span>
                </div>
            </div>
        </div>
        
        <button type="submit" class="search-btn">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Rechercher
        </button>
    </form>

    <script>
        // Mise à jour de l'affichage du radius en temps réel
        const radiusSlider = document.getElementById('searchRadius');
        const radiusDisplay = document.getElementById('radiusDisplay');

        radiusSlider.addEventListener('input', function() {
            const value = this.value;
            radiusDisplay.textContent = value + ' km';
            
            // Animation du badge quand on change la valeur
            radiusDisplay.style.transform = 'scale(1.1)';
            setTimeout(() => {
                radiusDisplay.style.transform = 'scale(1)';
            }, 200);
        });

        // Animation au chargement
        radiusDisplay.style.transition = 'transform 0.2s ease';
    </script>
    <style>  /* ===== RADIUS SLIDER ===== */
        .radius-group {
            margin-bottom: 25px;
        }

        .radius-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-weight: 600;
            color: #333;
        }

        .radius-value {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            min-width: 80px;
            text-align: center;
        }

        .radius-slider-container {
            position: relative;
            padding: 10px 0;
        }

        .radius-marks {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 12px;
            color: #999;
        }

        /* Style du range slider */
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 8px;
            border-radius: 5px;
            background: linear-gradient(to right, #667eea 0%, #764ba2 100%);
            outline: none;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        input[type="range"]:hover {
            opacity: 1;
        }

        /* Thumb Chrome/Safari */
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            border: 3px solid #667eea;
            transition: all 0.3s;
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        /* Thumb Firefox */
        input[type="range"]::-moz-range-thumb {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            border: 3px solid #667eea;
            transition: all 0.3s;
        }

        input[type="range"]::-moz-range-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        /* Track Firefox */
        input[type="range"]::-moz-range-track {
            height: 8px;
            border-radius: 5px;
            background: linear-gradient(to right, #667eea 0%, #764ba2 100%);
        }</style>
                        </div>

                        <div class="tab-content" id="places">
                            <form class="search-form" method="post" action="result.php">
                                <div class="form-row">
                                    <div class="input-group">
                                        <input type="text" name="adresse" placeholder="📍 Quelle adresse ?" required>
                                    </div>
                                    <div class="input-group">
                                        <select name="category" required>
                                            <option value="" disabled selected>🏛️ Catégorie ?</option>
                                            <option value="tous">Tous</option>
                                            <option value="musees">Musées</option>
                                            <option value="parcs">Parcs</option>
                                            <option value="monuments">Monuments</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="search-btn">
                                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </button>
                            </form>
                        </div>

                        <div class="tab-content" id="events">
                            <form class="search-form" method="post" action="test.php">
                                <div class="form-row">
                                    <div class="input-group">
                                        <input type="text" name="adresse" placeholder="📍 Quelle adresse ?" required>
                                    </div>
                                    <div class="input-group">
                                        <select name="eventType" required>
                                            <option value="" disabled selected>🎪 Type d'événement ?</option>
                                            <option value="tous">Tous</option>
                                            <option value="concerts">Concerts</option>
                                            <option value="spectacles">Spectacles</option>
                                            <option value="expositions">Expositions</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="search-btn">
                                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </button>
                            </form>
                        </div>

                        <div class="tab-content" id="jobs">
                            <form class="search-form" method="post" action="result.php">
                                <div class="form-row">
                                    <div class="input-group">
                                        <input type="text" name="location" placeholder="📍 Quelle ville ?" required>
                                    </div>
                                    <div class="input-group">
                                        <select name="jobType" required>
                                            <option value="" disabled selected>💼 Type de poste ?</option>
                                            <option value="tous">Tous</option>
                                            <option value="temps-plein">Temps plein</option>
                                            <option value="temps-partiel">Temps partiel</option>
                                            <option value="freelance">Freelance</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="search-btn">
                                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Gestion des onglets
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetTab = btn.getAttribute('data-tab');
                
                tabBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    if (content.id === targetTab) {
                        content.classList.add('active');
                    }
                });
            });
        });

        // Slider d'arrière-plan
        const slides = document.querySelectorAll('.bg-slide');
        let currentSlide = 0;

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(nextSlide, 5000);

        // Animation au scroll (si vous ajoutez du contenu en dessous)
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.hero-content > *').forEach(el => {
            observer.observe(el);
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
  AOS.init({
    duration: 800,
    once: true,
    offset: 50
  });
</script>
 
<!-- ============================================
     SECTION COMMENT ÇA MARCHE - VERSION RÉELLE
     ============================================ -->

<section class="how-it-works-section">
    <div class="container">
        <!-- En-tête -->
        <div class="section-header-htw">
            <span class="section-tag-htw">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Simple & Rapide
            </span>
            <h2 class="section-title-htw">Comment ça marche ?</h2>
            <p class="section-desc-htw">Découvrez les meilleurs restaurants Halal d'Alger en 3 étapes</p>
        </div>

        <!-- Grille des étapes -->
        <div class="steps-grid-htw">
            
            <!-- Étape 1 -->
            <div class="step-card-htw" data-step="1">
                <div class="step-icon-wrapper-htw">
                    <div class="step-number-htw">1</div>
                    <div class="step-icon-htw">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                
                <div class="step-content-htw">
                    <h3 class="step-title-htw">Recherchez</h3>
                    <p class="step-desc-htw">Entrez votre quartier à Alger ou le type de cuisine que vous recherchez</p>
                    
                    <ul class="step-features-htw">
                        <li>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Recherche par quartier</span>
                        </li>
                        <li>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Tous types de cuisines</span>
                        </li>
                    </ul>
                </div>

                <!-- Flèche de connexion -->
                <div class="step-arrow-htw">
                    <svg width="60" height="60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
            </div>

            <!-- Étape 2 -->
            <div class="step-card-htw" data-step="2">
                <div class="step-icon-wrapper-htw">
                    <div class="step-number-htw">2</div>
                    <div class="step-icon-htw">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                </div>
                
                <div class="step-content-htw">
                    <h3 class="step-title-htw">Découvrez</h3>
                    <p class="step-desc-htw">Consultez les notes, photos, menus et avis authentiques de la communauté</p>
                    
                    <ul class="step-features-htw">
                        <li>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Avis clients vérifiés</span>
                        </li>
                        <li>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Photos des plats</span>
                        </li>
                    </ul>
                </div>

                <!-- Flèche de connexion -->
                <div class="step-arrow-htw">
                    <svg width="60" height="60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
            </div>

            <!-- Étape 3 -->
            <div class="step-card-htw" data-step="3">
                <div class="step-icon-wrapper-htw">
                    <div class="step-number-htw">3</div>
                    <div class="step-icon-htw">
                        <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                
                <div class="step-content-htw">
                    <h3 class="step-title-htw">Localisez</h3>
                    <p class="step-desc-htw">Obtenez l'adresse exacte, les horaires et le contact pour appeler directement</p>
                    
                    <ul class="step-features-htw">
                        <li>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Itinéraire GPS</span>
                        </li>
                        <li>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Appel direct</span>
                        </li>
                    </ul>
                </div>
            </div>

        </div>

        <!-- Statistiques RÉELLES depuis la BDD -->
        <div class="trust-stats-htw">
            <?php
            include_once('connect.php');
            
            // Nombre total de restaurants
            $nb_restos = $dbh->query("SELECT COUNT(DISTINCT Nom) as total FROM vendeur")->fetch()['total'];
            
            // Nombre total d'avis
            $nb_avis = $dbh->query("SELECT COUNT(*) as total FROM comments")->fetch()['total'];
            
            // Note moyenne
            $note_moyenne = $dbh->query("SELECT ROUND(AVG(note), 1) as moyenne FROM vendeur WHERE note > 0")->fetch()['moyenne'];
            
            // Nombre de types de cuisines
            $nb_cuisines = $dbh->query("SELECT COUNT(DISTINCT Type) as total FROM vendeur WHERE Type IS NOT NULL AND Type != ''")->fetch()['total'];
            ?>
            
            <div class="stat-item-htw">
                <div class="stat-icon-htw">🍽️</div>
                <div class="stat-number-htw" data-target="<?php echo $nb_restos; ?>">0</div>
                <div class="stat-label-htw">Restaurants Halal</div>
            </div>
            
            <div class="stat-item-htw">
                <div class="stat-icon-htw">⭐</div>
                <div class="stat-number-htw" data-target="<?php echo $nb_avis; ?>">0</div>
                <div class="stat-label-htw">Avis Authentiques</div>
            </div>
            
            <div class="stat-item-htw">
                <div class="stat-icon-htw">📊</div>
                <div class="stat-number-htw-special"><?php echo $note_moyenne; ?>/5</div>
                <div class="stat-label-htw">Note Moyenne</div>
            </div>
            
            <div class="stat-item-htw">
                <div class="stat-icon-htw">🌍</div>
                <div class="stat-number-htw" data-target="<?php echo $nb_cuisines; ?>">0</div>
                <div class="stat-label-htw">Types de Cuisines</div>
            </div>
        </div>

        <!-- CTA final -->
        <div class="cta-wrapper-htw">
            <h3 class="cta-title-htw">Prêt à découvrir les meilleurs restaurants d'Alger ?</h3>
            <p class="cta-subtitle-htw">100% Halal • 100% Vérifiés • 100% Gratuit</p>
            <a href="#autoC" class="cta-button-htw">
                <span>Commencer ma recherche</span>
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<style>
/* ============================================
   CSS COMMENT ÇA MARCHE - VERSION RÉELLE
   ============================================ */

.how-it-works-section {
    padding: 100px 0;
    background: linear-gradient(180deg, #FFFFFF 0%, #F9FAFB 100%);
    position: relative;
    overflow: hidden;
}

/* Décoration subtile */
.how-it-works-section::before {
    content: '';
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(0, 170, 108, 0.05) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.how-it-works-section::after {
    content: '';
    position: absolute;
    bottom: -150px;
    left: -150px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(0, 170, 108, 0.03) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

/* En-tête */
.section-header-htw {
    text-align: center;
    margin-bottom: 70px;
    animation: fadeInUp 0.8s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.section-tag-htw {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    background: rgba(0, 170, 108, 0.1);
    border: 1px solid rgba(0, 170, 108, 0.2);
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    color: #00AA6C;
    margin-bottom: 20px;
}

.section-title-htw {
    font-size: 42px;
    font-weight: 800;
    color: #1A1A1A;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
}

.section-desc-htw {
    font-size: 18px;
    color: #6B7280;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Grille des étapes */
.steps-grid-htw {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
    margin-bottom: 80px;
    position: relative;
}

/* Carte étape */
.step-card-htw {
    background: white;
    border-radius: 24px;
    padding: 40px 32px;
    position: relative;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeInUp 0.8s ease-out backwards;
}

.step-card-htw[data-step="1"] {
    animation-delay: 0.1s;
}

.step-card-htw[data-step="2"] {
    animation-delay: 0.2s;
}

.step-card-htw[data-step="3"] {
    animation-delay: 0.3s;
}

.step-card-htw:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(0, 170, 108, 0.15);
}

/* Icône et numéro */
.step-icon-wrapper-htw {
    position: relative;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.step-number-htw {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #00AA6C 0%, #00D084 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
    color: white;
    box-shadow: 0 8px 20px rgba(0, 170, 108, 0.3);
}

.step-icon-htw {
    width: 64px;
    height: 64px;
    background: rgba(0, 170, 108, 0.1);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #00AA6C;
    transition: all 0.3s ease;
}

.step-card-htw:hover .step-icon-htw {
    background: #00AA6C;
    color: white;
    transform: scale(1.1) rotate(5deg);
}

/* Contenu */
.step-content-htw {
    text-align: left;
}

.step-title-htw {
    font-size: 24px;
    font-weight: 700;
    color: #1A1A1A;
    margin-bottom: 12px;
}

.step-desc-htw {
    font-size: 15px;
    color: #6B7280;
    line-height: 1.6;
    margin-bottom: 20px;
}

/* Features liste */
.step-features-htw {
    list-style: none;
    padding: 0;
    margin: 0;
}

.step-features-htw li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    color: #374151;
    margin-bottom: 10px;
}

.step-features-htw li:last-child {
    margin-bottom: 0;
}

.step-features-htw svg {
    color: #00AA6C;
    flex-shrink: 0;
}

/* Flèches de connexion */
.step-arrow-htw {
    position: absolute;
    right: -50px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(0, 170, 108, 0.2);
    animation: arrowFloat 2s ease-in-out infinite;
}

@keyframes arrowFloat {
    0%, 100% {
        transform: translateY(-50%) translateX(0);
    }
    50% {
        transform: translateY(-50%) translateX(8px);
    }
}

.step-card-htw:last-child .step-arrow-htw {
    display: none;
}

/* Statistiques RÉELLES */
.trust-stats-htw {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 32px;
    padding: 60px 0;
    border-top: 1px solid #E5E7EB;
    border-bottom: 1px solid #E5E7EB;
}

.stat-item-htw {
    text-align: center;
    animation: fadeInUp 1s ease-out backwards;
}

.stat-item-htw:nth-child(1) { animation-delay: 0.4s; }
.stat-item-htw:nth-child(2) { animation-delay: 0.5s; }
.stat-item-htw:nth-child(3) { animation-delay: 0.6s; }
.stat-item-htw:nth-child(4) { animation-delay: 0.7s; }

.stat-icon-htw {
    font-size: 48px;
    margin-bottom: 12px;
}

.stat-number-htw {
    font-size: 40px;
    font-weight: 800;
    color: #00AA6C;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-number-htw::after {
    content: '+';
    font-size: 32px;
}

/* Note moyenne sans + */
.stat-number-htw-special {
    font-size: 40px;
    font-weight: 800;
    color: #00AA6C;
    margin-bottom: 8px;
    line-height: 1;
}

.stat-label-htw {
    font-size: 15px;
    color: #6B7280;
    font-weight: 500;
}

/* CTA final */
.cta-wrapper-htw {
    text-align: center;
    margin-top: 60px;
}

.cta-title-htw {
    font-size: 28px;
    font-weight: 700;
    color: #1A1A1A;
    margin-bottom: 12px;
}

.cta-subtitle-htw {
    font-size: 16px;
    color: #00AA6C;
    font-weight: 600;
    margin-bottom: 24px;
}

.cta-button-htw {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 18px 40px;
    background: linear-gradient(135deg, #00AA6C 0%, #00D084 100%);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 700;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(0, 170, 108, 0.3);
}

.cta-button-htw:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 40px rgba(0, 170, 108, 0.4);
}

/* Responsive */
@media (max-width: 1024px) {
    .steps-grid-htw {
        grid-template-columns: 1fr;
        gap: 60px;
    }
    
    .step-arrow-htw {
        display: none;
    }
    
    .trust-stats-htw {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .how-it-works-section {
        padding: 60px 0;
    }
    
    .section-title-htw {
        font-size: 32px;
    }
    
    .step-card-htw {
        padding: 32px 24px;
    }
    
    .trust-stats-htw {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .cta-title-htw {
        font-size: 22px;
    }
}
</style>

<script>
// ============================================
// JS COMMENT ÇA MARCHE - Animation compteurs RÉELS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Animation des compteurs
    const counters = document.querySelectorAll('.stat-number-htw');
    
    const animateCounter = (counter) => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2000; // 2 secondes
        const increment = target / (duration / 16); // 60 FPS
        let current = 0;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    };
    
    // Intersection Observer pour démarrer l'animation au scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                animateCounter(counter);
                observer.unobserve(counter); // Une seule animation
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => observer.observe(counter));
    
    // Smooth scroll pour le CTA
    document.querySelector('.cta-button-htw')?.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector('#autoC');
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => target.focus(), 800);
        }
    });
});
</script>

<!-- ============================================
     SECTION BADGES DE CONFIANCE + FILTRES RAPIDES
     ============================================ -->

<section class="trust-filters-section">
    <div class="container">
        
        <!-- Badges de Confiance -->
        <div class="trust-badges-wrapper">
            <?php
            include_once('connect.php');
            
            // Statistiques réelles
            $nb_restos = $dbh->query("SELECT COUNT(DISTINCT Nom) as total FROM vendeur")->fetch()['total'];
            $nb_avis = $dbh->query("SELECT COUNT(*) as total FROM comments")->fetch()['total'];
            $note_moyenne = $dbh->query("SELECT ROUND(AVG(note), 1) as moyenne FROM vendeur WHERE note > 0")->fetch()['moyenne'];
            ?>
            
            <div class="trust-badge-card">
                <div class="badge-icon certified">
                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <h4 class="badge-title">100% Certifié Halal</h4>
                    <p class="badge-desc"><?php echo $nb_restos; ?> restaurants vérifiés et certifiés</p>
                </div>
            </div>

            <div class="trust-badge-card">
                <div class="badge-icon verified">
                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <h4 class="badge-title"><?php echo number_format($nb_avis, 0, ',', ' '); ?>+ Avis</h4>
                    <p class="badge-desc">Avis authentiques de vrais clients</p>
                </div>
            </div>

            <div class="trust-badge-card">
                <div class="badge-icon gold">
                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <h4 class="badge-title">Note Moyenne <?php echo $note_moyenne; ?>/5</h4>
                    <p class="badge-desc">Qualité garantie par nos utilisateurs</p>
                </div>
            </div>

            <div class="trust-badge-card">
                <div class="badge-icon support">
                    <svg width="32" height="32" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                    </svg>
                </div>
                <div class="badge-content">
                    <h4 class="badge-title">Gratuit & Sans Engagement</h4>
                    <p class="badge-desc">Service 100% gratuit pour tous</p>
                </div>
            </div>
        </div>




    </div>
</section>

<style>
/* ============================================
   CSS BADGES DE CONFIANCE + FILTRES RAPIDES
   ============================================ */

.trust-filters-section {
    padding: 80px 0;
    background: white;
    position: relative;
}

/* ===== BADGES DE CONFIANCE ===== */
.trust-badges-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
    margin-bottom: 60px;
}

.trust-badge-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 24px;
    background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
    border: 2px solid #F3F4F6;
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeInUp 0.6s ease-out backwards;
}

.trust-badge-card:nth-child(1) { animation-delay: 0.1s; }
.trust-badge-card:nth-child(2) { animation-delay: 0.2s; }
.trust-badge-card:nth-child(3) { animation-delay: 0.3s; }
.trust-badge-card:nth-child(4) { animation-delay: 0.4s; }

.trust-badge-card:hover {
    transform: translateY(-4px);
    border-color: #00AA6C;
    box-shadow: 0 8px 24px rgba(0, 170, 108, 0.15);
}

.badge-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.badge-icon.certified {
    background: linear-gradient(135deg, rgba(0, 170, 108, 0.1) 0%, rgba(0, 208, 132, 0.1) 100%);
    color: #00AA6C;
}

.badge-icon.verified {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
    color: #3B82F6;
}

.badge-icon.gold {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
    color: #F59E0B;
}

.badge-icon.support {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(234, 88, 12, 0.1) 100%);
    color: #F97316;
}

.trust-badge-card:hover .badge-icon {
    transform: scale(1.1) rotate(5deg);
}

.badge-content {
    flex: 1;
}

.badge-title {
    font-size: 16px;
    font-weight: 700;
    color: #1A1A1A;
    margin-bottom: 4px;
}

.badge-desc {
    font-size: 13px;
    color: #6B7280;
    line-height: 1.4;
    margin: 0;
}

/* ===== SÉPARATEUR ===== */
.section-divider {
    position: relative;
    text-align: center;
    margin: 60px 0 50px;
}

.section-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, #E5E7EB 50%, transparent 100%);
}

.divider-text {
    position: relative;
    display: inline-block;
    padding: 0 24px;
    background: white;
    font-size: 14px;
    font-weight: 600;
    color: #6B7280;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ===== FILTRES RAPIDES ===== */
.quick-filters-wrapper {
    text-align: center;
}

.filters-title {
    font-size: 32px;
    font-weight: 800;
    color: #1A1A1A;
    margin-bottom: 8px;
}

.filters-subtitle {
    font-size: 16px;
    color: #6B7280;
    margin-bottom: 40px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
    max-width: 1200px;
    margin: 0 auto;
}

.filter-chip {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 16px 20px;
    background: white;
    border: 2px solid #E5E7EB;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-decoration: none;
}

.filter-chip.animated {
    animation: fadeInUp 0.6s ease-out backwards;
}

.filter-chip.animated:nth-child(1) { animation-delay: 0.5s; }
.filter-chip.animated:nth-child(2) { animation-delay: 0.6s; }
.filter-chip.animated:nth-child(3) { animation-delay: 0.7s; }
.filter-chip.animated:nth-child(4) { animation-delay: 0.8s; }
.filter-chip.animated:nth-child(5) { animation-delay: 0.9s; }
.filter-chip.animated:nth-child(6) { animation-delay: 1s; }

.filter-chip::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
    height: 100%;
    background: linear-gradient(135deg, #00AA6C 0%, #00D084 100%);
    transition: width 0.4s ease;
    z-index: 0;
}

.filter-chip:hover {
    border-color: #00AA6C;
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 170, 108, 0.2);
}

.filter-chip:hover::before {
    width: 100%;
}

.filter-chip:hover .chip-icon,
.filter-chip:hover .chip-text,
.filter-chip:hover .chip-badge,
.filter-chip:hover .chip-count {
    color: white;
    position: relative;
    z-index: 1;
}

.filter-chip.all-restos {
    border-color: #00AA6C;
    background: rgba(0, 170, 108, 0.05);
}

.chip-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.chip-icon.green { background: rgba(0, 170, 108, 0.1); color: #00AA6C; }
.chip-icon.blue { background: rgba(59, 130, 246, 0.1); color: #3B82F6; }
.chip-icon.purple { background: rgba(168, 85, 247, 0.1); color: #A855F7; }
.chip-icon.pink { background: rgba(236, 72, 153, 0.1); color: #EC4899; }
.chip-icon.orange { background: rgba(249, 115, 22, 0.1); color: #F97316; }
.chip-icon.gray { background: rgba(107, 114, 128, 0.1); color: #6B7280; }

.chip-text {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    position: relative;
    z-index: 1;
}

.chip-badge {
    padding: 4px 10px;
    background: #00AA6C;
    color: white;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.5px;
    position: relative;
    z-index: 1;
}

.chip-badge.pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.chip-badge.new {
    background: #EC4899;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.chip-count {
    min-width: 28px;
    height: 28px;
    padding: 0 8px;
    background: #F3F4F6;
    border-radius: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #00AA6C;
    position: relative;
    z-index: 1;
}

/* Animations */
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

/* Responsive */
@media (max-width: 768px) {
    .trust-badges-wrapper {
        grid-template-columns: 1fr;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-title {
        font-size: 24px;
    }
    
    .trust-filters-section {
        padding: 60px 0;
    }
}

@media (max-width: 480px) {
    .chip-text {
        font-size: 13px;
    }
    
    .filter-chip {
        padding: 14px 16px;
    }
}
</style>

<script>
// Animation au scroll
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.trust-badge-card, .filter-chip.animated');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(el => observer.observe(el));
});
</script>
       <!-- ============================================
     SECTION CATÉGORIES - VERSION PROFESSIONNELLE
     ============================================ -->

<section class="categories-section-pro">
    <div class="container">
        
        <!-- En-tête élégant -->
        <div class="section-header-pro">
            <h2 class="section-title-pro">Explorez nos cuisines</h2>
            <p class="section-subtitle-pro">Découvrez une sélection de restaurants authentiques près de chez vous</p>
        </div>

        <!-- Navigation par onglets -->
        <div class="categories-tabs">
            <button class="tab-btn active" data-tab="popular">
                Les plus populaires
            </button>
            <button class="tab-btn" data-tab="all">
                Toutes les cuisines
                <span class="tab-count" id="allCategoriesCount">0</span>
            </button>
        </div>

        <!-- Contenu des onglets -->
        <div class="tabs-content">
            
            <!-- TAB: Populaires (6-8 catégories) -->
            <div class="tab-pane active" id="popular-tab">
                <div class="categories-grid-pro">
                    <?php
                    include('connect.php');

                    // Requête pour récupérer les catégories
                    $sql = "
                        SELECT 
                            v.Type,
                            COUNT(DISTINCT v.Nom) as nb_restaurants,
                            ROUND(AVG(v.note), 1) as note_moyenne,
                            COUNT(DISTINCT c.comment_id) as nb_avis,
                            GROUP_CONCAT(DISTINCT v.pricerange) as price_ranges,
                            p.main as sample_image
                        FROM vendeur v
                        LEFT JOIN comments c ON c.nom = v.Nom
                        LEFT JOIN photos p ON p.Nom = v.Nom AND p.main IS NOT NULL
                        WHERE v.Type IS NOT NULL AND v.Type != ''
                        GROUP BY v.Type
                        ORDER BY nb_restaurants DESC, note_moyenne DESC
                        LIMIT 8
                    ";

                    $result = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);

                    // Images de fond par type de cuisine (Unsplash - haute qualité)
                    $categoryImages = [
                        'Italien' => 'https://images.unsplash.com/photo-1595295333158-4742f28fbd85?w=800&q=80',
                        'Italienne' => 'https://images.unsplash.com/photo-1595295333158-4742f28fbd85?w=800&q=80',
                        'Français' => 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=800&q=80',
                        'Francais' => 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=800&q=80',
                        'Française' => 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=800&q=80',
                        'Asiatique' => 'https://images.unsplash.com/photo-1617196034796-73dfa7b1fd56?w=800&q=80',
                        'Chinois' => 'https://images.unsplash.com/photo-1526318896980-cf78c088247c?w=800&q=80',
                        'Japonais' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=800&q=80',
                        'Indien' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
                        'Mexicain' => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&q=80',
                        'Américain' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=800&q=80',
                        'Americain' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?w=800&q=80',
                        'Burger' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                        'Burgers' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80',
                        'Pizza' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                        'Pizzeria' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80',
                        'Sushi' => 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=800&q=80',
                        'Kebab' => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=800&q=80',
                        'Libanais' => 'https://images.unsplash.com/photo-1605379399642-870262d3d051?w=800&q=80',
                        'Turc' => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=800&q=80',
                        'Fast-food' => 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=800&q=80',
                        'Fast food' => 'https://images.unsplash.com/photo-1561758033-d89a9ad46330?w=800&q=80',
                        'Fruits de mer' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=800&q=80',
                        'Poisson' => 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=800&q=80',
                        'Café' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                        'Salon de thé' => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80',
                        'Boulangerie' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&q=80',
                        'Pâtisserie' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80',
                        'Dessert' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80',
                    ];

                    // Fonction pour obtenir l'image
                    function getCategoryImage($type, $images, $sampleImage = null) {
                        // Si image BDD disponible
                        if ($sampleImage && file_exists('images/vendeur/' . $sampleImage)) {
                            return 'images/vendeur/' . $sampleImage;
                        }
                        
                        // Chercher image par type
                        if (isset($images[$type])) {
                            return $images[$type];
                        }
                        
                        // Chercher correspondance partielle
                        foreach ($images as $key => $img) {
                            if (stripos($type, $key) !== false) {
                                return $img;
                            }
                        }
                        
                        // Image par défaut
                        return 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80';
                    }

                    // Prix moyen
                    function getAveragePrice($price_ranges) {
                        if (empty($price_ranges)) return '€€';
                        $prices = explode(',', $price_ranges);
                        $total = 0;
                        $count = 0;
                        foreach ($prices as $price) {
                            $total += strlen(trim($price));
                            $count++;
                        }
                        if ($count == 0) return '€€';
                        $avg = round($total / $count);
                        return str_repeat('€', max(1, min(4, $avg)));
                    }

                    // Générer les cartes TOP 8
                    foreach ($result as $index => $cat) {
                        $type = htmlspecialchars($cat['Type']);
                        $nb_resto = $cat['nb_restaurants'];
                        $note = $cat['note_moyenne'] ?: 0;
                        $avg_price = getAveragePrice($cat['price_ranges']);
                        $image = getCategoryImage($type, $categoryImages, $cat['sample_image']);
                        
                        // Badge si populaire
                        $badge = '';
                        if ($nb_resto >= 15) {
                            $badge = '<span class="category-badge-pro">Populaire</span>';
                        } elseif ($note >= 4.5) {
                            $badge = '<span class="category-badge-pro badge-top">Top noté</span>';
                        }
                        
                        echo "
                        <form method='post' action='result.php' class='category-card-pro-form'>
                            <input type='hidden' name='type_list[]' value='{$type}'>
                            <input type='hidden' name='adresse' value=''>
                            <input type='hidden' name='currentgps' value='36.7668582,3.0532282'>
                            <input type='hidden' name='searchRadius' value='1000'>
                            
                            <button type='submit' class='category-card-pro'>
                                <div class='category-image-pro'>
                                    <img src='{$image}' alt='{$type}' loading='lazy'>
                                    <div class='category-overlay-pro'></div>
                                </div>
                                
                                {$badge}
                                
                                <div class='category-info-pro'>
                                    <h3 class='category-name-pro'>{$type}</h3>
                                    <div class='category-meta-pro'>
                                        <span class='meta-item-pro'>
                                            <svg width='16' height='16' fill='currentColor' viewBox='0 0 20 20'>
                                                <path d='M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z'/>
                                            </svg>
                                            {$nb_resto}
                                        </span>";
                        
                        if ($note > 0) {
                            echo "
                                        <span class='meta-item-pro'>
                                            <svg width='16' height='16' fill='currentColor' viewBox='0 0 20 20'>
                                                <path d='M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z'/>
                                            </svg>
                                            {$note}
                                        </span>";
                        }
                        
                        echo "
                                        <span class='meta-item-pro price-pro'>{$avg_price}</span>
                                    </div>
                                </div>
                                
                                <div class='category-cta-pro'>
                                    <span>Explorer</span>
                                    <svg width='20' height='20' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 8l4 4m0 0l-4 4m4-4H3'/>
                                    </svg>
                                </div>
                            </button>
                        </form>
                        ";
                    }
                    ?>
                </div>
            </div>

            <!-- TAB: Toutes (liste complète avec recherche) -->
            <div class="tab-pane" id="all-tab">
                <div class="categories-search-pro">
                    <input type="text" 
                           id="searchAllCategories" 
                           placeholder="Rechercher une cuisine..."
                           class="search-input-pro">
                    <svg class="search-icon-pro" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <div class="categories-list-pro" id="allCategoriesList">
                    <?php
                    // Requête TOUTES les catégories
                    $sqlAll = "
                        SELECT 
                            v.Type,
                            COUNT(DISTINCT v.Nom) as nb_restaurants,
                            ROUND(AVG(v.note), 1) as note_moyenne
                        FROM vendeur v
                        WHERE v.Type IS NOT NULL AND v.Type != ''
                        GROUP BY v.Type
                        ORDER BY v.Type ASC
                    ";

                    $resultAll = $dbh->query($sqlAll)->fetchAll(PDO::FETCH_ASSOC);
                    $totalCategories = count($resultAll);

                    foreach ($resultAll as $cat) {
                        $type = htmlspecialchars($cat['Type']);
                        $nb_resto = $cat['nb_restaurants'];
                        $note = $cat['note_moyenne'] ?: 0;
                        
                        echo "
                        <form method='post' action='result.php' class='category-item-pro-form'>
                            <input type='hidden' name='type_list[]' value='{$type}'>
                            <input type='hidden' name='adresse' value=''>
                            <input type='hidden' name='currentgps' value='36.7668582,3.0532282'>
                            <input type='hidden' name='searchRadius' value='1000'>
                            
                            <button type='submit' class='category-item-pro' data-type='{$type}'>
                                <div class='category-item-left'>
                                    <h4 class='category-item-name'>{$type}</h4>
                                    <span class='category-item-count'>{$nb_resto} restaurant" . ($nb_resto > 1 ? 's' : '') . "</span>
                                </div>
                                
                                <div class='category-item-right'>";
                        
                        if ($note > 0) {
                            echo "<span class='category-item-rating'>{$note} ★</span>";
                        }
                        
                        echo "
                                    <svg width='20' height='20' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'/>
                                    </svg>
                                </div>
                            </button>
                        </form>
                        ";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ============================================
   CSS PROFESSIONNEL MINIMALISTE
   ============================================ */

.categories-section-pro {
    padding: 80px 0;
    background: #ffffff;
    position: relative;
}
.category-info-pro{color:white!important;;}
/* En-tête */
.section-header-pro {
    text-align: center;
    margin-bottom: 50px;
}

.section-title-pro {
    font-size: 36px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
    letter-spacing: -0.5px;
}

.section-subtitle-pro {
    font-size: 17px;
    color: #666;
    font-weight: 400;
}

/* Onglets */
.categories-tabs {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-bottom: 40px;
    border-bottom: 2px solid #f0f0f0;
    padding-bottom: 0;
}

.tab-btn {
    background: none;
    border: none;
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    position: relative;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
}

.tab-btn.active {
    color: #00aa6c;
    border-bottom-color: #00aa6c;
}

.tab-btn:hover:not(.active) {
    color: #1a1a1a;
}

.tab-count {
    display: inline-block;
    background: #f0f0f0;
    color: #666;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 6px;
}

.tab-btn.active .tab-count {
    background: #00aa6c;
    color: white;
}

/* Contenu onglets */
.tabs-content {
    position: relative;
}

.tab-pane {
    display: none;
    animation: fadeIn 0.4s ease;
}

.tab-pane.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Grille catégories */
.categories-grid-pro {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}

/* Carte catégorie */
.category-card-pro-form {
    width: 100%;
    height: 100%;
}

.category-card-pro {
    width: 100%;
    height: 320px;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    border: none;
    padding: 0;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.category-card-pro:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.16);
}

/* Image */
.category-image-pro {
    position: absolute;
    inset: 0;
}

.category-image-pro img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.category-card-pro:hover .category-image-pro img {
    transform: scale(1.08);
}

.category-overlay-pro {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.7) 100%);
}

/* Badge */
.category-badge-pro {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(10px);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    z-index: 2;
}

.category-badge-pro.badge-top {
    background: linear-gradient(135deg, #00aa6c, #00d084);
}

/* Info */
.category-info-pro {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 24px;
    z-index: 2;
    }

.category-name-pro {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    text-align: left;
    color:white;
}

.category-meta-pro {
    display: flex;
    gap: 16px;
    align-items: center;
}

.meta-item-pro {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    opacity: 0.95;
}

.meta-item-pro svg {
    opacity: 0.8;
}

.meta-item-pro.price-pro {
    font-weight: 700;
    letter-spacing: 1px;
}

/* CTA */
.category-cta-pro {
    position: absolute;
    bottom: 24px;
    right: 24px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.4s;
    z-index: 2;
}

.category-card-pro:hover .category-cta-pro {
    opacity: 1;
    transform: translateX(0);
}

/* Recherche toutes catégories */
.categories-search-pro {
    position: relative;
    max-width: 600px;
    margin: 0 auto 32px;
}

.search-input-pro {
    width: 100%;
    padding: 14px 48px 14px 48px;
    border: 2px solid #e0e0e0;
    border-radius: 50px;
    font-size: 15px;
    transition: all 0.3s;
}

.search-input-pro:focus {
    outline: none;
    border-color: #00aa6c;
    box-shadow: 0 0 0 4px rgba(0,170,108,0.1);
}

.search-icon-pro {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    pointer-events: none;
}

/* Liste catégories */
.categories-list-pro {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 12px;
}

.category-item-pro-form {
    width: 100%;
}

.category-item-pro {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: white;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
}

.category-item-pro:hover {
    border-color: #00aa6c;
    background: #f9fffe;
    transform: translateX(4px);
}

.category-item-left {
    text-align: left;
}

.category-item-name {
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.category-item-count {
    font-size: 13px;
    color: #999;
}

.category-item-right {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #666;
}

.category-item-rating {
    font-size: 14px;
    font-weight: 600;
    color: #f59e0b;
}

/* Responsive */
@media (max-width: 768px) {
    .section-title-pro {
        font-size: 28px;
    }
    
    .categories-grid-pro {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
    }
    
    .category-card-pro {
        height: 280px;
    }
    
    .categories-list-pro {
        grid-template-columns: 1fr;
    }
    
    .categories-tabs {
        overflow-x: auto;
        justify-content: flex-start;
        padding-bottom: 12px;
    }
}

@media (max-width: 480px) {
    .categories-grid-pro {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des onglets
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            
            // Retirer active
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Ajouter active
            this.classList.add('active');
            document.getElementById(tabId + '-tab').classList.add('active');
        });
    });
    
    // Recherche dans toutes les catégories
    const searchInput = document.getElementById('searchAllCategories');
    const allItems = document.querySelectorAll('.category-item-pro');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            allItems.forEach(item => {
                const type = item.dataset.type.toLowerCase();
                if (type.includes(query)) {
                    item.closest('.category-item-pro-form').style.display = 'block';
                } else {
                    item.closest('.category-item-pro-form').style.display = 'none';
                }
            });
        });
    }
    
    // Compter total catégories
    const totalCount = document.querySelectorAll('#all-tab .category-item-pro').length;
    const countElem = document.getElementById('allCategoriesCount');
    if (countElem) {
        countElem.textContent = totalCount;
    }
});
</script>

        
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --gold: #D4AF37;
            --gold-light: #F4E5B8;
            --dark: #1a1a1a;
            --gray: #8c8c8c;
            --white: #FFFFFF;
            --black: #000000;
        }

        body {
            font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            overflow-x: hidden;
        }

        .luxury-section {
            position: relative;
            padding: 120px 0;
            overflow: hidden;
        }

        .luxury-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(212, 175, 55, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(212, 175, 55, 0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 0 60px;
            position: relative;
            z-index: 1;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
            animation: fadeInDown 1s ease-out;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-subtitle {
            color: var(--gold);
            font-size: 13px;
            font-weight: 400;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .section-subtitle::before,
        .section-subtitle::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 40px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold));
        }

        .section-subtitle::before {
            right: calc(100% + 20px);
        }

        .section-subtitle::after {
            left: calc(100% + 20px);
            background: linear-gradient(90deg, var(--gold), transparent);
        }

        .section-title {
            font-size: 4.5rem;
            font-weight: 300;
            color: var(--white);
            margin-bottom: 24px;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .section-description {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 300;
        }

        .luxuryslider-container {
            position: relative;
            margin-top: 60px;
        }

        .luxuryslider-wrapper {
            overflow: hidden;
            border-radius: 30px;
            position: relative;
        }

        .luxuryslider-track {
            display: flex;
            transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1);
        }

        .luxuryslide {
            min-width: 100%;
            position: relative;
            height: 700px;
            background: var(--black);
        }

        .luxuryslide-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100%;
            gap: 0;
        }

        .luxuryslide-image {
            position: relative;
            overflow: hidden;
        }

        .luxuryslide-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .luxuryslide.active .luxuryslide-image img {
            transform: scale(1.05);
        }

        .luxuryslide-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(26, 26, 26, 0.4) 100%);
        }

        .luxuryslide-info {
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
        }

        .luxury-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 50px;
            color: var(--gold);
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 30px;
            width: fit-content;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .luxury-badge i {
            font-size: 14px;
        }

        .luxuryslide-title {
            font-size: 3.8rem;
            font-weight: 300;
            color: var(--white);
            margin-bottom: 20px;
            line-height: 1.1;
            letter-spacing: -1px;
        }

        .luxuryslide-subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 40px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 300;
        }

        .info-grid {
            display: grid;
            gap: 20px;
            margin-bottom: 40px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 16px;
            color: var(--white);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 0.95rem;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 10px;
            color: var(--gold);
            flex-shrink: 0;
        }

        .luxuryslide-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .price-display {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .price-label {
            font-size: 0.85rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .price-value {
            font-size: 2rem;
            color: var(--gold);
            letter-spacing: 2px;
        }

        .rating-display {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .rating-score {
            font-size: 2.5rem;
            font-weight: 300;
            color: var(--white);
            line-height: 1;
        }

        .rating-stars {
            display: flex;
            gap: 4px;
        }

        .rating-stars i {
            font-size: 16px;
            color: var(--gold);
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 18px 40px;
            background: linear-gradient(135deg, var(--gold) 0%, #C19A2E 100%);
            color: var(--black);
            text-decoration: none;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 40px;
            width: fit-content;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            box-shadow: 0 10px 40px rgba(212, 175, 55, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 50px rgba(212, 175, 55, 0.5);
        }

        .luxuryslider-nav {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 50px;
        }

        .nav-button {
            width: 60px;
            height: 60px;
            border: 1px solid rgba(212, 175, 55, 0.3);
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--gold);
            font-size: 18px;
        }

        .nav-button:hover {
            background: var(--gold);
            color: var(--black);
            transform: scale(1.1);
        }

        .luxuryslider-dots {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 30px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(212, 175, 55, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dot.active {
            background: var(--gold);
            transform: scale(1.3);
        }

        .favorite-btn {
            position: absolute;
            top: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .favorite-btn:hover {
            background: var(--gold);
            transform: scale(1.1);
        }

        .favorite-btn i {
            font-size: 20px;
            color: var(--gold);
            transition: all 0.3s ease;
        }

        .favorite-btn:hover i {
            color: var(--black);
        }

        .favorite-btn.active i {
            color: var(--gold);
        }

        @media (max-width: 1200px) {
            .luxuryslide-content {
                grid-template-columns: 1fr;
            }

            .luxuryslide {
                height: auto;
            }

            .luxuryslide-image {
                height: 400px;
            }

            .luxuryslide-info {
                padding: 60px 40px;
            }

            .section-title {
                font-size: 3rem;
            }

            .luxuryslide-title {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 20px;
            }

            .luxury-section {
                padding: 80px 0;
            }

            .section-title {
                font-size: 2.5rem;
            }

            .luxuryslide-title {
                font-size: 2rem;
            }

            .luxuryslide-info {
                padding: 40px 30px;
            }

            .nav-button {
                width: 50px;
                height: 50px;
            }

            .section-subtitle::before,
            .section-subtitle::after {
                width: 20px;
            }
        }
    </style>
 
    <!--Latest Listings Luxury luxuryslider Start-->
    <section class="luxury-section">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <div class="section-subtitle">Sélection exclusive</div>
                <h2 class="section-title">Les nouveautés</h2>
                <p class="section-description">Découvrez nos derniers établissements d'exception, où l'art culinaire rencontre le raffinement absolu</p>
            </div>

            <!-- luxuryslider Container -->
            <div class="luxuryslider-container">
                <div class="luxuryslider-wrapper">
                    <div class="luxuryslider-track">
                        <?php
                        include('connect.php');
                        
                        $result = $dbh->query("SELECT * FROM vendeur v JOIN photos p ON p.Nom=v.Nom LIMIT 20,5")->fetchAll();
                        
                        foreach ($result as $index => $ligne) {
                            $note = floatval($ligne['note']);
                            $fullStars = floor($note);
                            $priceLevel = str_repeat('€', min(5, max(1, intval($ligne['prix'] ?? 3))));
                            
                            echo '
                            <div class="luxuryslide' . ($index === 0 ? ' active' : '') . '">
                                <div class="luxuryslide-content">
                                    <div class="luxuryslide-image">
                                        <img src="' . htmlspecialchars($ligne['main']) . '" 
                                             alt="' . htmlspecialchars($ligne['Nom']) . '"
                                             onerror="this.src=\'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&q=80\'">
                                        
                                        <button class="favorite-btn" onclick="luxuryToggleFavorite(event, this, \'' . htmlspecialchars($ligne['Nom']) . '\')">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="luxuryslide-info">
                                        <div class="luxury-badge">
                                            <i class="fas fa-crown"></i>
                                            <span>Ouvert maintenant</span>
                                        </div>
                                        
                                        <h3 class="luxuryslide-title">' . htmlspecialchars($ligne['Nom']) . '</h3>
                                        <p class="luxuryslide-subtitle">' . htmlspecialchars($ligne['description'] ?? 'Une expérience gastronomique inoubliable') . '</p>
                                        
                                        <div class="info-grid">
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </div>
                                                <span>' . htmlspecialchars($ligne['ville']) . ' - ' . htmlspecialchars($ligne['codePostal']) . '</span>
                                            </div>
                                            <div class="info-item">
                                                <div class="info-icon">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <span>' . htmlspecialchars($ligne['adresse']) . '</span>
                                            </div>
                                        </div>
                                        
                                        <div class="luxuryslide-footer">
                                            <div class="price-display">
                                                <span class="price-label">Gamme</span>
                                                <span class="price-value">' . $priceLevel . '</span>
                                            </div>
                                            <div class="rating-display">
                                                <span class="rating-score">' . number_format($note, 1) . '</span>
                                                <div class="rating-stars">';
                            
                            for ($i = 0; $i < 5; $i++) {
                                if ($i < $fullStars) {
                                    echo '<i class="fas fa-star"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            
                            echo '
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <a href="detail-restaurant-2.php?nom=' . urlencode($ligne['Nom']) . '" class="cta-button">
                                            <span>Découvrir</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="luxuryslider-nav">
                    <button class="nav-button" onclick="luxuryPreviousluxuryslide()">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="nav-button" onclick="luxuryNextluxuryslide()">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

                <!-- Dots -->
                <div class="luxuryslider-dots" id="luxurysliderDots"></div>
            </div>
        </div>
    </section>
    <!--Latest Listings Luxury luxuryslider End-->

    <script>
        let luxuryCurrentluxuryslide = 0;
        const luxuryluxuryslides = document.querySelectorAll('.luxury-section .luxuryslide');
        const luxuryTrack = document.querySelector('.luxury-section .luxuryslider-track');
        const luxuryDotsContainer = document.getElementById('luxurysliderDots');

        // Create dots
        luxuryluxuryslides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.className = 'dot' + (index === 0 ? ' active' : '');
            dot.onclick = () => luxuryGoToluxuryslide(index);
            luxuryDotsContainer.appendChild(dot);
        });

        function luxuryUpdateluxuryslider() {
            luxuryTrack.style.transform = `translateX(-${luxuryCurrentluxuryslide * 100}%)`;
            
            luxuryluxuryslides.forEach((luxuryslide, index) => {
                luxuryslide.classList.toggle('active', index === luxuryCurrentluxuryslide);
            });
            
            document.querySelectorAll('.luxury-section .dot').forEach((dot, index) => {
                dot.classList.toggle('active', index === luxuryCurrentluxuryslide);
            });
        }

        function luxuryNextluxuryslide() {
            luxuryCurrentluxuryslide = (luxuryCurrentluxuryslide + 1) % luxuryluxuryslides.length;
            luxuryUpdateluxuryslider();
        }

        function luxuryPreviousluxuryslide() {
            luxuryCurrentluxuryslide = (luxuryCurrentluxuryslide - 1 + luxuryluxuryslides.length) % luxuryluxuryslides.length;
            luxuryUpdateluxuryslider();
        }

        function luxuryGoToluxuryslide(index) {
            luxuryCurrentluxuryslide = index;
            luxuryUpdateluxuryslider();
        }

        // Auto-advance
        setInterval(luxuryNextluxuryslide, 6000);

        // Toggle Favorite
        function luxuryToggleFavorite(event, button, restaurantName) {
            event.preventDefault();
            event.stopPropagation();
            
            button.classList.toggle('active');
            const icon = button.querySelector('i');
            
            if (button.classList.contains('active')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                
                fetch('add_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'restaurant=' + encodeURIComponent(restaurantName)
                })
                .catch(error => console.error('Erreur:', error));
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                
                fetch('remove_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'restaurant=' + encodeURIComponent(restaurantName)
                })
                .catch(error => console.error('Erreur:', error));
            }
        }
    </script>

 

<!-- ============================================
     SECTION 1 : POPULAR LOCATIONS - DESIGN LUXE
     ============================================ -->

<<section class="popular-locations-luxe">

    <div class="container">
        <!-- En-tête -->
        <div class="section-header-luxe">
            <span class="section-tag">📍 Destinations</span>
            <h2 class="section-title-luxe">Restaurants près de chez vous</h2>
            <p class="section-desc-luxe">Découvrez les meilleurs établissements dans votre région</p>
        </div>

        <!-- Grille des villes -->
        <div class="locations-grid-luxe">
            <?php
            // Récupération des villes depuis la BDD
            $sql = "SELECT ville, 
                           COUNT(DISTINCT Nom) as nb_restaurants,
                           ROUND(AVG(note), 1) as note_moyenne
                    FROM vendeur 
                    WHERE ville IS NOT NULL AND ville != ''
                    GROUP BY ville 
                    ORDER BY nb_restaurants DESC 
                    LIMIT 6";
            
            $cities = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            
            // Images de fond par ville
            $cityImages = [
                'Alger' => 'https://images.unsplash.com/photo-1566417713940-fe7c737a9ef2?w=800&q=80',
                'Paris' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&q=80',
                'Lyon' => 'https://images.unsplash.com/photo-1524396309943-e03f5249f002?w=800&q=80',
                'Marseille' => 'https://images.unsplash.com/photo-1608116783733-5626219ef0f4?w=800&q=80',
                'Toulouse' => 'https://images.unsplash.com/photo-1580712364788-ff2a9e2c2f4e?w=800&q=80',
                'Nice' => 'https://images.unsplash.com/photo-1570939274717-7eda259b50ed?w=800&q=80',
                'Bordeaux' => 'https://images.unsplash.com/photo-1556628181-1f6f8db63a68?w=800&q=80',
            ];
            
            // Boucle pour afficher les villes de la BDD
            foreach ($cities as $city) {
                $ville = htmlspecialchars($city['ville']);
                $nb = $city['nb_restaurants'];
                $note = $city['note_moyenne'] ?: 0;
                $image = $cityImages[$ville] ?? 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=80';
               
                echo "
                <form method='post' action='result.php' class='location-card-form-luxe'>
                    <input type='hidden' name='type_list[]' value='Tous'>
                    <input type='hidden' name='adresse' value='Hydra, Bir Mourad Rais, Alger'>
                    <input type='hidden' name='currentgps' value='36.7474259,3.0401832'>
                    <input type='hidden' name='searchRadius' value='5000'>
                    
                    <button type='submit' class='location-card-luxe'>
                        <div class='location-bg-luxe'>
                            <img src='{$image}' alt='{$ville}'>
                            <div class='location-overlay-luxe'></div>
                        </div>
                        
                        <div class='location-content-luxe'>
                            <h3 class='location-name-luxe' style='color:white'>{$ville}</h3>
                            <div class='location-stats-luxe'>
                                <span class='stat-luxe'>
                                    <svg width='16' height='16' fill='currentColor' viewBox='0 0 20 20'>
                                        <path fill-rule='evenodd' d='M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z' clip-rule='evenodd'/>
                                    </svg>
                                    {$nb} restaurants
                                </span>";
                
                if ($note > 0) {
                    echo "<span class='stat-luxe'>⭐ {$note}</span>";
                }
                
                echo "
                            </div>
                            
                            <div class='location-cta-luxe'>
                                <span>Explorer</span>
                                <svg width='20' height='20' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 8l4 4m0 0l-4 4m4-4H3'/>
                                </svg>
                            </div>
                        </div>
                    </button>
                </form>
                ";
            }
            ?>

            <!-- Card statique pour Oran -->
            <form method='post' action='result.php' class='location-card-form-luxe'>
                <input type='hidden' name='type_list[]' value='Tous'>
                <input type='hidden' name='adresse' value='Oran, Algérie'>
                <input type='hidden' name='currentgps' value='35.6969,-0.6331'>
                <input type='hidden' name='searchRadius' value='5000'>
                
                <button type='submit' class='location-card-luxe'>
                    <div class='location-bg-luxe'>
                        <img src='https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=800&q=80' alt='Oran'>
                        <div class='location-overlay-luxe'></div>
                    </div>
                    
                    <div class='location-content-luxe'>
                        <h3 class='location-name-luxe' style='color:white'>Oran</h3>
                        <div class='location-stats-luxe'>
                            <span class='stat-luxe'>
                                <svg width='16' height='16' fill='currentColor' viewBox='0 0 20 20'>
                                    <path fill-rule='evenodd' d='M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z' clip-rule='evenodd'/>
                                </svg>
                                127 restaurants
                            </span>
                            <span class='stat-luxe'>⭐ 4.3</span>
                        </div>
                        
                        <div class='location-cta-luxe'>
                            <span>Explorer</span>
                            <svg width='20' height='20' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 8l4 4m0 0l-4 4m4-4H3'/>
                            </svg>
                        </div>
                    </div>
                </button>
            </form>

            <!-- Card statique pour Béjaïa
            <form method='post' action='result.php' class='location-card-form-luxe'>
                <input type='hidden' name='type_list[]' value='Tous'>
                <input type='hidden' name='adresse' value='Béjaïa, Algérie'>
                <input type='hidden' name='currentgps' value='36.7525,5.0689'>
                <input type='hidden' name='searchRadius' value='5000'>
                
                <button type='submit' class='location-card-luxe'>
                    <div class='location-bg-luxe'>
                        <img src='https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&q=80' alt='Béjaïa'>
                        <div class='location-overlay-luxe'></div>
                    </div>
                    
                    <div class='location-content-luxe'>
                        <h3 class='location-name-luxe' style='color:white'>Béjaïa</h3>
                        <div class='location-stats-luxe'>
                            <span class='stat-luxe'>
                                <svg width='16' height='16' fill='currentColor' viewBox='0 0 20 20'>
                                    <path fill-rule='evenodd' d='M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z' clip-rule='evenodd'/>
                                </svg>
                                85 restaurants
                            </span>
                            <span class='stat-luxe'>⭐ 4.5</span>
                        </div>
                        
                        <div class='location-cta-luxe'>
                            <span>Explorer</span>
                            <svg width='20' height='20' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 8l4 4m0 0l-4 4m4-4H3'/>
                            </svg>
                        </div>
                    </div>
                </button>
            </form> -->

            <!-- Card statique pour Constantine -->
            <form method='post' action='result.php' class='location-card-form-luxe'>
                <input type='hidden' name='type_list[]' value='Tous'>
                <input type='hidden' name='adresse' value='Constantine, Algérie'>
                <input type='hidden' name='currentgps' value='36.3650,6.6147'>
                <input type='hidden' name='searchRadius' value='5000'>
                
                <button type='submit' class='location-card-luxe'>
                    <div class='location-bg-luxe'>
                        <img src='https://images.unsplash.com/photo-1513002749550-c59d786b8e6c?w=800&q=80' alt='Constantine'>
                        <div class='location-overlay-luxe'></div>
                    </div>
                    
                    <div class='location-content-luxe'>
                        <h3 class='location-name-luxe' style='color:white'>Constantine</h3>
                        <div class='location-stats-luxe'>
                            <span class='stat-luxe'>
                                <svg width='16' height='16' fill='currentColor' viewBox='0 0 20 20'>
                                    <path fill-rule='evenodd' d='M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z' clip-rule='evenodd'/>
                                </svg>
                                98 restaurants
                            </span>
                            <span class='stat-luxe'>⭐ 4.4</span>
                        </div>
                        
                        <div class='location-cta-luxe'>
                            <span>Explorer</span>
                            <svg width='20' height='20' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 8l4 4m0 0l-4 4m4-4H3'/>
                            </svg>
                        </div>
                    </div>
                </button>
            </form>
        </div>
    </div>
</section>

<style>
.popular-locations-luxe {
    padding: 100px 0;
    background: linear-gradient(180deg, #fafafa 0%, #ffffff 100%);
}

.section-header-luxe {
    text-align: center;
    margin-bottom: 60px;
}

.section-tag {
    display: inline-block;
    padding: 8px 20px;
    background: #f0f0f0;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    color: #666;
    margin-bottom: 20px;
}

.section-title-luxe {
    font-size: 42px;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 16px;
    letter-spacing: -1px;
}

.section-desc-luxe {
    font-size: 18px;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.locations-grid-luxe {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
}

.location-card-form-luxe {
    width: 100%;
    height: 100%;
}

.location-card-luxe {
    width: 100%;
    height: 400px;
    border-radius: 24px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    border: none;
    padding: 0;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.location-card-luxe:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 48px rgba(0,0,0,0.2);
}

.location-bg-luxe {
    position: absolute;
    inset: 0;
}

.location-bg-luxe img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.7s ease;
}

.location-card-luxe:hover .location-bg-luxe img {
    transform: scale(1.1);
}

.location-overlay-luxe {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%);
}

.location-content-luxe {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 32px;
    z-index: 2;
    color: white;
}

.location-name-luxe {
    font-size: 32px;
    font-weight: 800;
    margin-bottom: 16px;
    text-align: left;
}

.location-stats-luxe {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.stat-luxe {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 15px;
    font-weight: 600;
    opacity: 0.95;
}

.location-cta-luxe {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 700;
    opacity: 0;
    transform: translateX(-10px);
    transition: all 0.4s;
}

.location-card-luxe:hover .location-cta-luxe {
    opacity: 1;
    transform: translateX(0);
}

@media (max-width: 1024px) {
    .locations-grid-luxe {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .locations-grid-luxe {
        grid-template-columns: 1fr;
    }
    
    .section-title-luxe {
        font-size: 32px;
    }
    
    .location-card-luxe {
        height: 320px;
    }
}
</style>


<!-- ============================================
     SECTION CTA RESTAURATEURS
     ============================================ -->

<section class="cta-restaurateurs-section">
    <div class="container">
        <div class="cta-content-wrapper">
            
            <!-- Partie gauche : Texte + CTA -->
            <div class="cta-left">
                <span class="cta-tag">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                    </svg>
                    Espace Professionnel
                </span>
                
                <h2 class="cta-title">
                    Vous êtes restaurateur ?
                    <span class="title-highlight">Rejoignez-nous !</span>
                </h2>
                
                <p class="cta-description">
                    Référencez votre établissement gratuitement et touchez des milliers de clients potentiels chaque mois. Simple, rapide et sans engagement.
                </p>
                
                <!-- Avantages -->
                <div class="benefits-grid">
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="benefit-content">
                            <h4>100% Gratuit</h4>
                            <p>Aucun frais d'inscription</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </div>
                        <div class="benefit-content">
                            <h4>Visibilité Maximale</h4>
                            <p>Des milliers de visites/mois</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="benefit-content">
                            <h4>En Ligne en 24h</h4>
                            <p>Activation rapide</p>
                        </div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div class="benefit-content">
                            <h4>Gestion Facile</h4>
                            <p>Modifiez vos infos en ligne</p>
                        </div>
                    </div>
                </div>
                
                <!-- Boutons CTA -->
                <div class="cta-buttons">
                    <a href="inscription-restaurant.php" class="btn-primary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span>Inscrire mon restaurant</span>
                    </a>
                    
                    <a href="#contact" class="btn-secondary">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>Nous contacter</span>
                    </a>
                </div>
                
                <!-- Statistiques réalistes -->
                <div class="social-proof">
                    <?php
                    include_once('connect.php');
                    $nb_restos_total = $dbh->query("SELECT COUNT(DISTINCT Nom) as total FROM vendeur")->fetch()['total'];
                    $nb_quartiers = $dbh->query("SELECT COUNT(DISTINCT SUBSTRING_INDEX(adresse, ',', -1)) as total FROM vendeur WHERE adresse IS NOT NULL")->fetch()['total'];
                    ?>
                    <div class="proof-item">
                        <div class="proof-avatars">
                            <span class="avatar">🍽️</span>
                            <span class="avatar">🥘</span>
                            <span class="avatar">🍕</span>
                            <span class="avatar">🍔</span>
                        </div>
                        <p><strong><?php echo $nb_restos_total; ?> restaurants</strong> répertoriés dans <strong><?php echo $nb_quartiers; ?> quartiers</strong> d'Alger</p>
                    </div>
                </div>
            </div>
            
            <!-- Partie droite : Visuel/Mockup -->
            <div class="cta-right">
                <div class="mockup-wrapper">
                    <!-- Dashboard preview -->
                    <div class="mockup-card main-card">
                        <div class="card-header">
                            <div class="card-title">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                                </svg>
                                <span>Tableau de bord</span>
                            </div>
                            <span class="status-badge">En ligne</span>
                        </div>
                        
                        <div class="stats-row">
                            <div class="stat-box">
                                <div class="stat-value">2,547</div>
                                <div class="stat-label">Vues ce mois</div>
                                <div class="stat-trend up">+23%</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value">156</div>
                                <div class="stat-label">Clics téléphone</div>
                                <div class="stat-trend up">+12%</div>
                            </div>
                        </div>
                        
                        <div class="chart-placeholder">
                            <svg viewBox="0 0 200 80" class="mini-chart">
                                <polyline points="0,60 20,45 40,50 60,30 80,35 100,20 120,25 140,15 160,20 180,10 200,5" fill="none" stroke="url(#gradient)" stroke-width="2"/>
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#00AA6C;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#00D084;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Floating badges -->
                    <div class="floating-badge badge-1">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span>4.8/5</span>
                    </div>
                    
                    <div class="floating-badge badge-2">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Vérifié</span>
                    </div>
                    
                    <div class="floating-badge badge-3">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                        </svg>
                        <span>+45 avis</span>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<style>
/* ============================================
   CSS CTA RESTAURATEURS
   ============================================ */

.cta-restaurateurs-section {
    padding: 100px 0;
    background: linear-gradient(135deg, #00AA6C 0%, #00D084 100%);
    position: relative;
    overflow: hidden;
}

/* Effets de fond */
.cta-restaurateurs-section::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
    border-radius: 50%;
}

.cta-restaurateurs-section::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -15%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
    border-radius: 50%;
}

.container {
    position: relative;
    z-index: 1;
}

.cta-content-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
}

/* ===== PARTIE GAUCHE ===== */
.cta-left {
    color: white;
}

.cta-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
    animation: fadeInUp 0.6s ease-out;
}

.cta-title {
    font-size: 48px;
    font-weight: 900;
    line-height: 1.2;
    margin-bottom: 20px;
    animation: fadeInUp 0.6s ease-out 0.1s backwards;
}

.title-highlight {
    display: block;
    background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.cta-description {
    font-size: 18px;
    line-height: 1.7;
    opacity: 0.95;
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease-out 0.2s backwards;
}

/* Grille des avantages */
.benefits-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease-out 0.3s backwards;
}

.benefit-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    transition: all 0.3s ease;
}

.benefit-item:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateY(-4px);
}

.benefit-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.benefit-content h4 {
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 4px;
}

.benefit-content p {
    font-size: 14px;
    opacity: 0.9;
    margin: 0;
}

/* Boutons */
.cta-buttons {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 40px;
    animation: fadeInUp 0.6s ease-out 0.4s backwards;
}

.btn-primary,
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 32px;
    border-radius: 50px;
    font-size: 16px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-primary {
    background: white;
    color: #00AA6C;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.btn-primary:hover {
    transform: translateY(-4px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-2px);
}

/* Preuve sociale */
.social-proof {
    animation: fadeInUp 0.6s ease-out 0.5s backwards;
}

.proof-item {
    display: flex;
    align-items: center;
    gap: 16px;
}

.proof-avatars {
    display: flex;
}

.avatar {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    margin-left: -12px;
}

.avatar:first-child {
    margin-left: 0;
}

.proof-item p {
    font-size: 14px;
    opacity: 0.95;
    margin: 0;
}

/* ===== PARTIE DROITE : MOCKUP ===== */
.cta-right {
    animation: fadeInRight 0.8s ease-out 0.3s backwards;
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(40px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.mockup-wrapper {
    position: relative;
    perspective: 1000px;
}

.mockup-card {
    background: white;
    border-radius: 24px;
    padding: 32px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotateY(5deg); }
    50% { transform: translateY(-20px) rotateY(-5deg); }
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #F3F4F6;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 700;
    color: #1A1A1A;
}

.status-badge {
    padding: 6px 14px;
    background: rgba(0, 170, 108, 0.1);
    color: #00AA6C;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.stat-box {
    padding: 20px;
    background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
    border: 2px solid #F3F4F6;
    border-radius: 16px;
}

.stat-value {
    font-size: 28px;
    font-weight: 800;
    color: #00AA6C;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 13px;
    color: #6B7280;
    margin-bottom: 8px;
}

.stat-trend {
    font-size: 12px;
    font-weight: 700;
}

.stat-trend.up {
    color: #00AA6C;
}

.stat-trend.up::before {
    content: '↗ ';
}

.chart-placeholder {
    height: 100px;
    background: linear-gradient(135deg, rgba(0, 170, 108, 0.05) 0%, rgba(0, 208, 132, 0.05) 100%);
    border-radius: 12px;
    padding: 16px;
}

.mini-chart {
    width: 100%;
    height: 100%;
}

/* Badges flottants */
.floating-badge {
    position: absolute;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: white;
    border-radius: 50px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    font-size: 14px;
    font-weight: 700;
    color: #1A1A1A;
    animation: floatBadge 4s ease-in-out infinite;
}

@keyframes floatBadge {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.badge-1 {
    top: -20px;
    right: -20px;
    animation-delay: 0s;
}

.badge-2 {
    bottom: 60px;
    left: -30px;
    animation-delay: 1s;
}

.badge-3 {
    bottom: -20px;
    right: 40px;
    animation-delay: 2s;
}

.floating-badge svg {
    color: #00AA6C;
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 1024px) {
    .cta-content-wrapper {
        grid-template-columns: 1fr;
        gap: 60px;
    }
    
    .cta-title {
        font-size: 36px;
    }
    
    .benefits-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .cta-restaurateurs-section {
        padding: 60px 0;
    }
    
    .cta-title {
        font-size: 32px;
    }
    
    .cta-buttons {
        flex-direction: column;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }
    
    .floating-badge {
        display: none;
    }
}
</style>

<script>
// Animation au scroll
document.addEventListener('DOMContentLoaded', function() {
    const ctaSection = document.querySelector('.cta-restaurateurs-section');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.2 });
    
    if (ctaSection) {
        observer.observe(ctaSection);
    }
});
</script>





<!-- ============================================
     SECTION 2 : BEST RATED - DESIGN LUXE
     ============================================ -->

<section class="best-rated-luxe">
    <div class="container">
        <!-- En-tête -->
        <div class="section-header-luxe">
            <span class="section-tag">⭐ Excellence</span>
            <h2 class="section-title-luxe">Les restaurants les mieux notés</h2>
            <p class="section-desc-luxe">Une sélection des établissements préférés de nos clients</p>
        </div>

        <!-- Carousel des restaurants -->
        <div class="best-rated-carousel-luxe">
            <div class="carousel-track-luxe">
                <?php
                // Récupération des meilleurs restaurants
                $sql = "SELECT v.Nom, v.Type, v.adresse, v.ville, v.note, v.gps, 
                               COUNT(c.comment_id) as nb_avis, p.main
                        FROM vendeur v
                        LEFT JOIN comments c ON c.nom = v.Nom
                        LEFT JOIN photos p ON p.Nom = v.Nom
                        WHERE v.note >= 4.5
                        GROUP BY v.Nom
                        ORDER BY v.note DESC, nb_avis DESC
                        LIMIT 8";
                
                $bestRated = $dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($bestRated as $resto) {
                    $nom = htmlspecialchars($resto['Nom']);
                    $type = htmlspecialchars($resto['Type']);
                    $ville = htmlspecialchars($resto['ville']);
                    $note = $resto['note'];
                    $nb_avis = $resto['nb_avis'];
                    $image = $resto['main'] ;
                    
                    echo "
                    <div class='restaurant-card-luxe'>
                        <a href='detail-restaurant-2.php?nom=" . urlencode($resto['Nom']) . "' class='card-link-luxe'>
                            <div class='card-image-luxe'>
                                <img src='{$image}' alt='{$nom}'>
                                <div class='card-badge-luxe'>
                                    <svg width='16' height='16' fill='currentColor' viewBox='0 0 20 20'>
                                        <path d='M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z'/>
                                    </svg>
                                    {$note}
                                </div>
                            </div>
                            
                            <div class='card-content-luxe'>
                                <h3 class='card-title-luxe'>{$nom}</h3>
                                <p class='card-subtitle-luxe'>{$type} • {$ville}</p>
                                <div class='card-footer-luxe'>
                                    <span class='card-reviews-luxe'>{$nb_avis} avis</span>
                                    <svg width='20' height='20' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'/>
                                    </svg>
                                </div>
                            </div>
                        </a>
                    </div>
                    ";
                }
                ?>
            </div>
            
            <!-- Navigation -->
            <button class='carousel-btn-luxe prev-luxe' onclick='scrollCarousel(-1)'>
                <svg width='24' height='24' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 19l-7-7 7-7'/>
                </svg>
            </button>
            <button class='carousel-btn-luxe next-luxe' onclick='scrollCarousel(1)'>
                <svg width='24' height='24' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'/>
                </svg>
            </button>
        </div>
    </div>
</section>

<style>
.best-rated-luxe {
    padding: 100px 0;
    background: #ffffff;
}

.best-rated-carousel-luxe {
    position: relative;
    overflow: hidden;
    padding: 0 60px;
}

.carousel-track-luxe {
    display: flex;
    gap: 24px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 20px 0;
}

.carousel-track-luxe::-webkit-scrollbar {
    display: none;
}

.restaurant-card-luxe {
    flex: 0 0 320px;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.4s;
}

.restaurant-card-luxe:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.16);
}

.card-link-luxe {
    text-decoration: none;
    color: inherit;
    display: block;
}

.card-image-luxe {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.card-image-luxe img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s;
}

.restaurant-card-luxe:hover .card-image-luxe img {
    transform: scale(1.1);
}

.card-badge-luxe {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(0,0,0,0.75);
    backdrop-filter: blur(10px);
    color: white;
    padding: 8px 14px;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 700;
    font-size: 15px;
}

.card-content-luxe {
    padding: 24px;
}

.card-title-luxe {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 8px;
}

.card-subtitle-luxe {
    font-size: 14px;
    color: #666;
    margin-bottom: 16px;
}

.card-footer-luxe {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #00aa6c;
    font-weight: 600;
}

.card-reviews-luxe {
    font-size: 14px;
}

.carousel-btn-luxe {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    z-index: 10;
}

.carousel-btn-luxe:hover {
    background: #00aa6c;
    color: white;
    transform: translateY(-50%) scale(1.1);
}

.prev-luxe {
    left: 0;
}

.next-luxe {
    right: 0;
}

@media (max-width: 768px) {
    .best-rated-carousel-luxe {
        padding: 0 20px;
    }
    
    .restaurant-card-luxe {
        flex: 0 0 280px;
    }
}
</style>

<script>
function scrollCarousel(direction) {
    const track = document.querySelector('.carousel-track-luxe');
    const cardWidth = 320 + 24; // largeur carte + gap
    const scrollAmount = cardWidth * 2 * direction;
    
    track.scrollBy({
        left: scrollAmount,
        behavior: 'smooth'
    });
}
</script>

<!-- ============================================
     SECTION 3 : APP DOWNLOAD - DESIGN LUXE
     ============================================ -->

<section class="app-download-luxe">
    <div class="container">
        <div class="app-content-luxe">
            <div class="app-left-luxe">
                <span class="app-tag-luxe">📱 Application Mobile</span>
                <h2 class="app-title-luxe">Réservez en un clic</h2>
                <p class="app-desc-luxe">
                    Téléchargez notre application et profitez d'une expérience optimisée pour réserver vos tables préférées en quelques secondes.
                </p>
                
                <ul class="app-features-luxe">
                    <li>
                        <svg width='24' height='24' fill='currentColor' viewBox='0 0 20 20'>
                            <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'/>
                        </svg>
                        <span>Réservation instantanée</span>
                    </li>
                    <li>
                        <svg width='24' height='24' fill='currentColor' viewBox='0 0 20 20'>
                            <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'/>
                        </svg>
                        <span>Offres exclusives</span>
                    </li>
                    <li>
                        <svg width='24' height='24' fill='currentColor' viewBox='0 0 20 20'>
                            <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' clip-rule='evenodd'/>
                        </svg>
                        <span>Notifications personnalisées</span>
                    </li>
                </ul>
                
                <div class="app-buttons-luxe">
                    <a href="#" class="app-btn-luxe">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                        </svg>
                        <div>
                            <span class="btn-small">Télécharger sur</span>
                            <span class="btn-large">App Store</span>
                        </div>
                    </a>
                    
                    <a href="#" class="app-btn-luxe">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M3.609 1.814L13.792 12 3.61 22.186a.996.996 0 0 1-.61-.92V2.734a1 1 0 0 1 .609-.92zm10.89 10.893l2.302 2.302-10.937 6.333 8.635-8.635zm3.199-3.198l2.807 1.626a1 1 0 0 1 0 1.73l-2.808 1.626L15.206 12l2.492-2.491zM5.864 2.658L16.802 8.99l-2.303 2.303-8.635-8.635z"/>
                        </svg>
                        <div>
                            <span class="btn-small">Disponible sur</span>
                            <span class="btn-large">Google Play</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="app-right-luxe">
                <div class="app-mockup-luxe">
                    <img src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=600&q=80" alt="App Screenshot">
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.app-download-luxe {
    padding: 100px 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.app-download-luxe::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

.app-content-luxe {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
    position: relative;
    z-index: 1;
}

.app-left-luxe {
    color: white;
}

.app-tag-luxe {
    display: inline-block;
    padding: 8px 20px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 24px;
}

.app-title-luxe {
    font-size: 48px;
    font-weight: 900;
    margin-bottom: 24px;
    line-height: 1.2;
}

.app-desc-luxe {
    font-size: 18px;
    line-height: 1.8;
    opacity: 0.95;
    margin-bottom: 32px;
}

.app-features-luxe {
    list-style: none;
    margin-bottom: 40px;
}

.app-features-luxe li {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
    font-size: 16px;
    font-weight: 500;
}

.app-features-luxe svg {
    flex-shrink: 0;
}

.app-buttons-luxe {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.app-btn-luxe {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 24px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(20px);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 16px;
    color: white;
    text-decoration: none;
    transition: all 0.3s;
}

.app-btn-luxe:hover {
    background: white;
    color: #667eea;
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.3);
}

.app-btn-luxe div {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.btn-small {
    font-size: 11px;
    opacity: 0.8;
}

.btn-large {
    font-size: 16px;
    font-weight: 700;
}

.app-right-luxe {
    position: relative;
}

.app-mockup-luxe {
    position: relative;
    max-width: 400px;
    margin: 0 auto;
}

.app-mockup-luxe img {
    width: 100%;
    border-radius: 32px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
}

@media (max-width: 1024px) {
    .app-content-luxe {
        grid-template-columns: 1fr;
        gap: 60px;
        text-align: center;
    }
    
    .app-left-luxe {
        order: 2;
    }
    
    .app-right-luxe {
        order: 1;
    }
    
    .app-features-luxe li {
        justify-content: center;
    }
    
    .app-buttons-luxe {
        justify-content: center;
    }
}

@media (max-width: 640px) {
    .app-title-luxe {
        font-size: 36px;
    }
    
    .app-buttons-luxe {
        flex-direction: column;
    }
    
    .app-btn-luxe {
        width: 100%;
        justify-content: center;
    }
}
</style>

        <!--Site Footer Start-->
        <footer class="site-footer">
            <div class="site_footer_shape-1" style="background-image: url(images/icons/logo.png)">
            </div>
            <div class="site_footer_map" style="background-image: url(assets/images/resources/footer-map.png)"></div>
            <div class="container">
                <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <div class="footer-widget__column footer-widget__about wow fadeInUp" data-wow-delay="100ms">
                            <div class="footer-widget__logo">
                                <a href="#"><img src="images/icons/logo.png" height=45px alt=""></a>
                            </div>
                            <div class="footer-widget_about_text">
                                <p>Retrouvez et réservez les meilleurs restaurants Halal et sans alcool partout en France avec LeBonResto.</p>
                            </div>
                            <div class="footer_contact_info">
                                <div class="footer_contact_icon">
                                    <span class="icon-calling"></span>
                                </div>
                                <div class="footer_contact_number">
                                    <p>Phone</p>
                                    <h4><a href="tel:+123456789">+33 7 67 88 36 31</a></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <div class="footer-widget__column footer-widget__explore wow fadeInUp" data-wow-delay="200ms">
                            <div class="footer-widget__title">
                                <h3>Explore</h3>
                            </div>
                            <ul class="footer-widget__explore-list list-unstyled">
                                <li><a href="#">Qui sommes-nous</a></li>
                                <li><a href="#">Mon compte</a></li>
                                <li><a href="#">Mes favoris</a></li>
                                <li><a href="#">Nos Packs</a></li>

                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-2 col-lg-2 col-md-6">
                        <div class="footer-widget__column footer-widget__categories wow fadeInUp" data-wow-delay="300ms">
                            <div class="footer-widget__title">
                                <h3>Categories</h3>
                            </div>
                            <ul class="footer-widget__categories_list list-unstyled">
                                <li><a href="#">Restaurant</a></li>
                                <li><a href="#">Culture</a></li>
                                <li><a href="#">Magasin</a></li>
                                <li><a href="#">Beauté</a></li>
                                <li><a href="#">Hotels</a></li>
                                <li><a href="#">Voyages</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-6">
                        <div class="footer-widget__column footer-widget__newsletter wow fadeInUp" data-wow-delay="400ms">
                            <div class="footer-widget__title">
                                <h3>Newsletter</h3>
                            </div>
                            <ul class="footer-widget_newsletter_address list-unstyled">
                                <li>53 rue des fleurs, Paris.</li>
                                <li><a href="mailto:sourtirane@yahoo.fr">sourtirane@yahoo.fr</a></li>
                            </ul>
                            <form>
                                <div class="footer_input-box">
                                    <input type="Email" placeholder="Entrer votre adresse mail">
                                    <button type="submit" class="button"><i class="fas fa-paper-plane"></i>s'inscrire</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <div class="site-footer_bottom">
            <div class="container">
                <div class="site-footer_bottom_copyright">
                    <p>© Copyright 2021 by <a href="#">Dalso.fr</a></p>
                </div>
                <div class="site-footer__social">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-facebook-square"></i></a>
                    <a href="#"><i class="fab fa-dribbble"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>





    </div><!-- /.page-wrapper -->

    <a href="#" data-target="html" class="scroll-to-target scroll-to-top"><i class="fa fa-angle-up"></i></a>

    <div class="side-menu__block">
        <div class="side-menu__block-overlay custom-cursor__overlay">
            <div class="cursor"></div>
            <div class="cursor-follower"></div>
        </div><!-- /.side-menu__block-overlay -->
        <div class="side-menu__block-inner ">
            <div class="side-menu__top justify-content-end">
                <a href="#" class="side-menu__toggler side-menu__close-btn"><img src="assets/images/shapes/close-1-1.png" alt=""></a>
            </div><!-- /.side-menu__top -->

            <nav class="mobile-nav__container">
                <!-- content is loading via js -->
            </nav>

            <div class="side-menu__sep"></div><!-- /.side-menu__sep -->

            <div class="side-menu__content">
                <p><a href="mailto:needhelp@tripo.com">sourtirane@yahoo.fr</a> <br> <a href="tel:07 67 88 36 31">07 67 88 36 31</a></p>
                <div class="side-menu__social">
                    <a href="#"><i class="fab fa-facebook-square"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest-p"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="search-popup">
        <div class="search-popup__overlay custom-cursor__overlay">
            <div class="cursor"></div>
            <div class="cursor-follower"></div>
        </div><!-- /.search-popup__overlay -->
        <div class="search-popup__inner">
            <form action="#" class="search-popup__form">
                <input type="text" name="search" placeholder="Type here to Search....">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>
    </div>



    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/waypoints.min.js"></script>
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/TweenMax.min.js"></script>
    <script src="assets/js/wow.js"></script>
    <script src="assets/js/jquery.magnific-popup.min.js"></script>
    <script src="assets/js/jquery.ajaxchimp.min.js"></script>
    <script src="assets/js/swiper.min.js"></script>
    <script src="assets/js/typed-2.0.11.js"></script>
    <script src="assets/js/vegas.min.js"></script>
    <script src="assets/js/jquery.validate.min.js"></script>
    <script src="assets/js/bootstrap-select.min.js"></script>
    <script src="assets/js/countdown.min.js"></script>
    <script src="assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="assets/js/bootstrap-datepicker.min.js"></script>
    <script src="assets/js/nouislider.min.js"></script>
    <script src="assets/js/isotope.js"></script>
    <script src="assets/js/appear.js"></script>
    <script src="assets/js/addr.js"></script>
    <script src="assets/js/show.js"></script>

    <!-- template scripts -->
    <script src="assets/js/theme.js"></script>
     <script>

    </script>
    <style>
        .tooltip-inner {

            background-color: #FC3C3C;
            color: white;
            border-radius: 2px;
            white-space: nowrap;
        }

        .slider-selection {
            background: red;
            color: green;

        }

        .slider-handle {
            background-color: white;
            background-image: none;
            -webkit-box-shadow: none;
            box-shadow: none;
            border: 1px solid #cad1d9;

        }

        .slider.slider-reset .slider-handle,
        .slider.slider-untouched .slider-handle {
            background-color: #FF4DFF;
        }

        .tooltip .arrow::before {

            border-color: red transparent transparent;

        }



        #rangeSlider {
            margin-top: 1rem;

        }

        #range .slider-track-low {
            background: #FFFFFF;
        }

        .divRadius {

            margin-top: 3rem;

        }

        .ulLand {
            list-style: none;
            margin-top: 1px;

        }

        .liLand {
            padding-top: 3px;
            padding-bottom: 3px;
            background: black;
            color: white;
        }

        .liLand:hover {

            background-color: red;

        }
    </style>
</body>

</html>