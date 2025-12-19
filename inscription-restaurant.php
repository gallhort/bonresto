<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscrire mon restaurant - Le Bon Resto</title>
    <meta name="description" content="Inscrivez votre restaurant gratuitement sur Le Bon Resto et touchez des milliers de clients">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #00AA6C;
            --primary-dark: #008C5A;
            --primary-light: #E6F7F1;
            --dark: #1A1A1A;
            --gray: #6B7280;
            --light-gray: #F3F4F6;
            --border: #E5E7EB;
            --white: #FFFFFF;
            --error: #EF4444;
            --success: #10B981;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #F9FAFB 0%, #FFFFFF 100%);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
        }

        .header-help {
            color: var(--gray);
            font-size: 14px;
        }

        .header-help a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        /* Container principal */
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 24px;
        }

        /* Progress bar */
        .progress-wrapper {
            margin-bottom: 40px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }

        .progress-step:last-child::before {
            display: none;
        }

        .progress-step.active::before {
            background: var(--primary);
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-gray);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin: 0 auto 8px;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .progress-step.active .step-number {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .progress-step.completed .step-number {
            background: var(--primary);
            color: white;
        }

        .step-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--gray);
        }

        .progress-step.active .step-label {
            color: var(--primary);
        }

        /* Card principale */
        .form-card {
            background: white;
            border-radius: 24px;
            padding: 48px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-title {
            font-size: 32px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 12px;
        }

        .form-subtitle {
            font-size: 16px;
            color: var(--gray);
        }

        /* Sections du formulaire */
        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Groupes de champs */
        .form-group {
            margin-bottom: 24px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        label .required {
            color: var(--error);
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="url"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Checkboxes stylées */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .checkbox-wrapper {
            position: relative;
        }

        .checkbox-wrapper input[type="checkbox"] {
            position: absolute;
            opacity: 0;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 2px solid var(--border);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-label {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .checkbox-icon {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-label .checkbox-icon {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-wrapper input[type="checkbox"]:checked + .checkbox-label .checkbox-icon::after {
            content: '✓';
            color: white;
            font-size: 12px;
            font-weight: 700;
        }

        /* Upload photos */
        .upload-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .upload-box {
            position: relative;
            border: 2px dashed var(--border);
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--light-gray);
        }

        .upload-box:hover {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .upload-box input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .upload-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            color: var(--gray);
        }

        .upload-text {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .upload-hint {
            font-size: 12px;
            color: var(--gray);
        }

        .upload-preview {
            margin-top: 12px;
            border-radius: 8px;
            overflow: hidden;
            display: none;
        }

        .upload-preview img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        /* Horaires */
        .horaires-grid {
            display: grid;
            gap: 16px;
        }

        .horaire-row {
            display: grid;
            grid-template-columns: 100px 1fr 1fr;
            gap: 12px;
            align-items: center;
        }

        .day-label {
            font-weight: 600;
            color: var(--dark);
        }

        .time-inputs {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 8px;
            align-items: center;
        }

        .time-separator {
            color: var(--gray);
            font-weight: 600;
        }

        input[type="time"] {
            padding: 10px 12px;
        }

        /* Boutons */
        .form-actions {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 40px;
            padding-top: 32px;
            border-top: 2px solid var(--light-gray);
        }

        .btn {
            padding: 14px 32px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            text-decoration: none;
        }

        .btn-secondary {
            background: var(--light-gray);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background: var(--border);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(0, 170, 108, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 170, 108, 0.4);
        }

        /* Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 2px solid var(--success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 2px solid var(--error);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-card {
                padding: 32px 24px;
            }

            .form-row,
            .upload-grid,
            .options-grid {
                grid-template-columns: 1fr;
            }

            .horaire-row {
                grid-template-columns: 1fr;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <a href="index.php" class="logo">Le Bon Resto</a>
            <div class="header-help">
                Besoin d'aide ? <a href="tel:+213567883631">07 67 88 36 31</a>
            </div>
        </div>
    </div>

    <!-- Container principal -->
    <div class="container">

        <!-- Progress bar -->
        <div class="progress-wrapper">
            <div class="progress-steps">
                <div class="progress-step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Informations</div>
                </div>
                <div class="progress-step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Détails</div>
                </div>
                <div class="progress-step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Photos & Horaires</div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">Inscrivez votre restaurant</h1>
                <p class="form-subtitle">Remplissez les informations ci-dessous. C'est rapide et gratuit !</p>
            </div>

            <form id="inscriptionForm" method="POST" action="traiter-inscription.php" enctype="multipart/form-data">

                <!-- ÉTAPE 1: Informations de base -->
                <div class="form-section active" data-section="1">
                    <div class="form-group">
                        <label>Nom du restaurant <span class="required">*</span></label>
                        <input type="text" name="nom" id="nom" placeholder="Ex: Restaurant Le Méditerranéen" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Type de cuisine <span class="required">*</span></label>
                            <select name="type" id="type" required>
                                <option value="">Sélectionner...</option>
                                <option value="Algérienne">Algérienne</option>
                                <option value="Française">Française</option>
                                <option value="Italienne">Italienne</option>
                                <option value="Asiatique">Asiatique</option>
                                <option value="Burger">Burger</option>
                                <option value="Pizza">Pizza</option>
                                <option value="Fast-food">Fast-food</option>
                                <option value="Kebab">Kebab</option>
                                <option value="Fruits de mer">Fruits de mer</option>
                                <option value="Grill">Grill</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Téléphone <span class="required">*</span></label>
                            <input type="tel" name="phone" id="phone" placeholder="0555 12 34 56" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Adresse complète <span class="required">*</span></label>
                        <input type="text" name="adresse" id="adresse" placeholder="123 Rue Didouche Mourad, Alger Centre" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Code postal <span class="required">*</span></label>
                            <input type="text" name="codePostal" id="codePostal" placeholder="16000" required>
                        </div>

                        <div class="form-group">
                            <label>Ville <span class="required">*</span></label>
                            <input type="text" name="ville" id="ville" value="Alger" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>GPS - Latitude</label>
                            <input type="text" name="gps_lat" id="gps_lat" placeholder="36.7538">
                        </div>

                        <div class="form-group">
                            <label>GPS - Longitude</label>
                            <input type="text" name="gps_lng" id="gps_lng" placeholder="3.0588">
                        </div>
                    </div>
                </div>

                <!-- ÉTAPE 2: Détails -->
                <div class="form-section" data-section="2">
                    <div class="form-group">
                        <label>Description du restaurant</label>
                        <textarea name="descriptif" id="descriptif" placeholder="Décrivez votre restaurant, vos spécialités, l'ambiance..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Site web</label>
                        <input type="url" name="web" id="web" placeholder="https://www.votrerestaurant.com">
                    </div>

                    <div class="form-group">
                        <label>Gamme de prix <span class="required">*</span></label>
                        <select name="pricerange" id="pricerange" required>
                            <option value="">Sélectionner...</option>
                            <option value="€">€ - Bon marché (moins de 500 DA)</option>
                            <option value="€€">€€ - Moyen (500-1000 DA)</option>
                            <option value="€€€">€€€ - Élevé (1000-2000 DA)</option>
                            <option value="€€€€">€€€€ - Très élevé (plus de 2000 DA)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Options disponibles</label>
                        <div class="options-grid">
                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="parking" id="parking" value="1">
                                <label for="parking" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Parking</span>
                                </label>
                            </div>

                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="wifi" id="wifi" value="1">
                                <label for="wifi" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Wifi</span>
                                </label>
                            </div>

                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="gamezone" id="gamezone" value="1">
                                <label for="gamezone" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Aire de jeux</span>
                                </label>
                            </div>

                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="baby" id="baby" value="1">
                                <label for="baby" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Zone bébé</span>
                                </label>
                            </div>

                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="handi" id="handi" value="1">
                                <label for="handi" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Handi-friendly</span>
                                </label>
                            </div>

                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="priere" id="priere" value="1">
                                <label for="priere" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Salle de prière</span>
                                </label>
                            </div>

                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="private" id="private" value="1">
                                <label for="private" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Salons privés</span>
                                </label>
                            </div>

                            <div class="checkbox-wrapper">
                                <input type="checkbox" name="voiturier" id="voiturier" value="1">
                                <label for="voiturier" class="checkbox-label">
                                    <span class="checkbox-icon"></span>
                                    <span>Voiturier</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ÉTAPE 3: Photos & Horaires -->
                <div class="form-section" data-section="3">
                    <div class="form-group">
                        <label>Photos du restaurant</label>
                        <div class="upload-grid">
                            <div class="upload-box">
                                <input type="file" name="main" id="main" accept="image/*" onchange="previewImage(this, 'preview-main')">
                                <div class="upload-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="upload-text">Photo principale</div>
                                <div class="upload-hint">JPG, PNG (max 5MB)</div>
                                <div class="upload-preview" id="preview-main"></div>
                            </div>

                            <div class="upload-box">
                                <input type="file" name="slide1" id="slide1" accept="image/*" onchange="previewImage(this, 'preview-slide1')">
                                <div class="upload-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="upload-text">Photo 2</div>
                                <div class="upload-hint">JPG, PNG (max 5MB)</div>
                                <div class="upload-preview" id="preview-slide1"></div>
                            </div>

                            <div class="upload-box">
                                <input type="file" name="slide2" id="slide2" accept="image/*" onchange="previewImage(this, 'preview-slide2')">
                                <div class="upload-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="upload-text">Photo 3</div>
                                <div class="upload-hint">JPG, PNG (max 5MB)</div>
                                <div class="upload-preview" id="preview-slide2"></div>
                            </div>

                            <div class="upload-box">
                                <input type="file" name="slide3" id="slide3" accept="image/*" onchange="previewImage(this, 'preview-slide3')">
                                <div class="upload-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="upload-text">Photo 4</div>
                                <div class="upload-hint">JPG, PNG (max 5MB)</div>
                                <div class="upload-preview" id="preview-slide3"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Horaires d'ouverture</label>
                        <div class="horaires-grid">
                            <?php
                            $jours = [
                                'lun' => 'Lundi',
                                'mar' => 'Mardi',
                                'mer' => 'Mercredi',
                                'jeu' => 'Jeudi',
                                'ven' => 'Vendredi',
                                'sam' => 'Samedi',
                                'dim' => 'Dimanche'
                            ];

                            foreach ($jours as $code => $nom) {
                                echo "<div class='horaire-row'>
                                    <div class='day-label'>{$nom}</div>
                                    <div class='time-inputs'>
                                        <input type='time' name='{$code}_mat_open' placeholder='08:00'>
                                        <span class='time-separator'>-</span>
                                        <input type='time' name='{$code}_mat_close' placeholder='12:00'>
                                    </div>
                                    <div class='time-inputs'>
                                        <input type='time' name='{$code}_ap_open' placeholder='14:00'>
                                        <span class='time-separator'>-</span>
                                        <input type='time' name='{$code}_ap_close' placeholder='20:00'>
                                    </div>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Boutons de navigation -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Précédent
                    </button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                        Suivant
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        // Changer d'étape
        function changeStep(direction) {
            // Validation avant de passer à l'étape suivante
            if (direction === 1 && !validateCurrentStep()) {
                return;
            }

            // Cacher l'étape actuelle
            document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.remove('active');
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.remove('active');
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('completed');

            // Calculer la nouvelle étape
            currentStep += direction;

            // Afficher la nouvelle étape
            document.querySelector(`.form-section[data-section="${currentStep}"]`).classList.add('active');
            document.querySelector(`.progress-step[data-step="${currentStep}"]`).classList.add('active');

            // Gérer les boutons
            document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'inline-flex';
            
            if (currentStep === totalSteps) {
                document.getElementById('nextBtn').textContent = 'Envoyer ma demande';
                document.getElementById('nextBtn').onclick = () => document.getElementById('inscriptionForm').submit();
            } else {
                document.getElementById('nextBtn').innerHTML = `
                    Suivant
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                `;
                document.getElementById('nextBtn').onclick = () => changeStep(1);
            }

            // Scroll vers le haut
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Validation de l'étape actuelle
        function validateCurrentStep() {
            const currentSection = document.querySelector(`.form-section[data-section="${currentStep}"]`);
            const requiredInputs = currentSection.querySelectorAll('[required]');
            
            for (let input of requiredInputs) {
                if (!input.value.trim()) {
                    input.focus();
                    input.style.borderColor = 'var(--error)';
                    setTimeout(() => {
                        input.style.borderColor = '';
                    }, 2000);
                    return false;
                }
            }
            
            return true;
        }

        // Preview des images
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.style.display = 'block';
                };
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Auto-fill GPS depuis l'adresse (optionnel - nécessite Google Maps API)
        document.getElementById('adresse')?.addEventListener('blur', function() {
            // TODO: Implémenter geocoding si API Google Maps disponible
        });

        // Validation téléphone algérien
        document.getElementById('phone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substr(0, 10);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>