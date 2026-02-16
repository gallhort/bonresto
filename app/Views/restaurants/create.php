<?php
$cuisineTypes = $cuisineTypes ?? [];
$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un restaurant - LeBonResto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
            background: #f5f5f5; 
            color: #1a1a1a;
            min-height: 100vh;
        }
        
        /* Header simple */
        .page-header {
            background: white;
            padding: 16px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .page-header-content {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .page-header a {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }
        .page-header .logo {
            font-size: 24px;
        }
        
        /* Container principal */
        .form-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        /* Titre */
        .form-title {
            text-align: center;
            margin-bottom: 40px;
        }
        .form-title h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .form-title p {
            color: #666;
            font-size: 16px;
        }
        
        /* Progress bar */
        .progress-container {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .progress-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
        }
        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 40px;
            right: 40px;
            height: 3px;
            background: #e0e0e0;
        }
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 1;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }
        .progress-step.active .step-circle {
            background: #00635a;
            color: white;
        }
        .progress-step.completed .step-circle {
            background: #00635a;
            color: white;
        }
        .step-label {
            font-size: 12px;
            color: #999;
            font-weight: 500;
            text-align: center;
        }
        .progress-step.active .step-label,
        .progress-step.completed .step-label {
            color: #00635a;
        }
        
        /* Form card */
        .form-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin-bottom: 24px;
        }
        .form-card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .form-card-title i {
            color: #00635a;
        }
        .form-card-subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 24px;
        }
        
        /* Form elements */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group.full-width {
            grid-column: span 2;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group label .required {
            color: #e74c3c;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #00635a;
            box-shadow: 0 0 0 3px rgba(0,99,90,0.1);
        }
        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }
        .form-group small {
            display: block;
            margin-top: 6px;
            font-size: 13px;
            color: #999;
        }
        
        /* Price selector */
        .price-selector {
            display: flex;
            gap: 12px;
        }
        .price-option {
            flex: 1;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .price-option:hover {
            border-color: #00635a;
        }
        .price-option.selected {
            border-color: #00635a;
            background: rgba(0,99,90,0.05);
        }
        .price-option input {
            display: none;
        }
        .price-option .price-symbol {
            font-size: 20px;
            font-weight: 700;
            color: #00635a;
            margin-bottom: 4px;
        }
        .price-option .price-label {
            font-size: 12px;
            color: #666;
        }
        
        /* Map container */
        .map-container {
            height: 300px;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 16px;
            border: 1px solid #e0e0e0;
        }
        #locationMap {
            height: 100%;
            width: 100%;
        }
        .locate-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 12px;
            transition: all 0.2s;
        }
        .locate-btn:hover {
            background: #e8e8e8;
        }
        
        /* Horaires */
        .horaires-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .horaire-row {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 16px;
            align-items: center;
            padding: 16px;
            background: #f9f9f9;
            border-radius: 10px;
        }
        .horaire-jour {
            font-weight: 600;
            color: #333;
        }
        .horaire-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .horaire-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .horaire-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #00635a;
        }
        .horaire-times {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .horaire-times input[type="time"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .horaire-times span {
            color: #999;
            font-size: 13px;
        }
        .horaire-service-type {
            display: flex;
            gap: 12px;
        }
        .service-type-btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
        }
        .service-type-btn.active {
            background: #00635a;
            color: white;
            border-color: #00635a;
        }
        
        /* Options grid */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .option-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .option-item:hover {
            border-color: #00635a;
        }
        .option-item.selected {
            border-color: #00635a;
            background: rgba(0,99,90,0.05);
        }
        .option-item input {
            display: none;
        }
        .option-item i {
            font-size: 20px;
            color: #00635a;
        }
        .option-item span {
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Photo upload */
        .photo-upload-zone {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 20px;
        }
        .photo-upload-zone:hover,
        .photo-upload-zone.dragover {
            border-color: #00635a;
            background: rgba(0,99,90,0.02);
        }
        .photo-upload-zone i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 16px;
        }
        .photo-upload-zone h3 {
            font-size: 18px;
            margin-bottom: 8px;
        }
        .photo-upload-zone p {
            color: #999;
            font-size: 14px;
        }
        .photo-preview-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .photo-preview {
            position: relative;
            aspect-ratio: 4/3;
            border-radius: 10px;
            overflow: hidden;
            background: #f5f5f5;
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-preview .photo-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #00635a;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .photo-preview .photo-remove {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            background: rgba(0,0,0,0.6);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 12px;
        }
        .photo-preview .photo-type-select {
            position: absolute;
            bottom: 8px;
            left: 8px;
            right: 8px;
        }
        .photo-preview .photo-type-select select {
            width: 100%;
            padding: 6px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            background: rgba(255,255,255,0.9);
        }
        
        /* Navigation buttons */
        .form-navigation {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 24px;
        }
        .btn {
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: none;
        }
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e8e8e8;
        }
        .btn-primary {
            background: #00635a;
            color: white;
        }
        .btn-primary:hover {
            background: #004d46;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #219a52;
        }
        
        /* Step sections */
        .step-section {
            display: none;
        }
        .step-section.active {
            display: block;
        }
        
        /* Messages */
        .alert {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .alert-error {
            background: #ffebee;
            color: #c62828;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .options-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .photo-preview-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .progress-steps {
                overflow-x: auto;
                padding-bottom: 10px;
            }
            .step-label {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .options-grid {
                grid-template-columns: 1fr;
            }
            .price-selector {
                flex-wrap: wrap;
            }
            .price-option {
                flex: 1 1 45%;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<header class="page-header">
    <div class="page-header-content">
        <a href="/">
            <svg width="30" height="30" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#00635a"/><path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M28 12v6c0 3 2 5 4 5v-11" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 23v13" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            <span>LeBonResto</span>
        </a>
        <a href="/">
            <i class="fas fa-times"></i>
            Fermer
        </a>
    </div>
</header>

<div class="form-container">
    <!-- Titre -->
    <div class="form-title">
        <h1>Ajouter votre restaurant</h1>
        <p>Rejoignez LeBonResto et attirez de nouveaux clients</p>
        <p style="margin-top: 12px; font-size: 14px; color: #888;">Vous n'etes pas proprietaire ? <a href="/proposer-restaurant" style="color: #00635a; font-weight: 600; text-decoration: none;">Proposez un restaurant</a> que vous aimez et gagnez des points !</p>
    </div>
    
    <!-- Progress bar -->
    <div class="progress-container">
        <div class="progress-steps">
            <div class="progress-step active" data-step="1">
                <div class="step-circle">1</div>
                <span class="step-label">Infos</span>
            </div>
            <div class="progress-step" data-step="2">
                <div class="step-circle">2</div>
                <span class="step-label">Localisation</span>
            </div>
            <div class="progress-step" data-step="3">
                <div class="step-circle">3</div>
                <span class="step-label">Contact</span>
            </div>
            <div class="progress-step" data-step="4">
                <div class="step-circle">4</div>
                <span class="step-label">Horaires</span>
            </div>
            <div class="progress-step" data-step="5">
                <div class="step-circle">5</div>
                <span class="step-label">Options</span>
            </div>
            <div class="progress-step" data-step="6">
                <div class="step-circle">6</div>
                <span class="step-label">Photos</span>
            </div>
        </div>
    </div>
    
    <?php if (!$currentUser): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <span>Vous devez être connecté pour ajouter un restaurant. <a href="/login" style="color: inherit; font-weight: 600;">Se connecter</a></span>
    </div>
    <?php endif; ?>
    
    <form id="addRestaurantForm" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- ÉTAPE 1 : INFORMATIONS DE BASE -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="step-section active" data-step="1">
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-utensils"></i>
                    Informations générales
                </h2>
                <p class="form-card-subtitle">Présentez votre établissement</p>
                
                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="nom">Nom du restaurant <span class="required">*</span></label>
                        <input type="text" id="nom" name="nom" placeholder="Ex: Le Petit Bistrot" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="type_cuisine">Type de cuisine <span class="required">*</span></label>
                        <select id="type_cuisine" name="type_cuisine" required>
                            <option value="">Sélectionner...</option>
                            <?php foreach ($cuisineTypes as $type): ?>
                                <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                            <?php endforeach; ?>
                            <option value="autre">Autre...</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="customTypeGroup" style="display: none;">
                        <label for="custom_type">Précisez le type</label>
                        <input type="text" id="custom_type" name="custom_type" placeholder="Ex: Cuisine fusion">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Décrivez votre restaurant, votre cuisine, votre ambiance..."></textarea>
                        <small>Max 500 caractères</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Gamme de prix <span class="required">*</span></label>
                    <div class="price-selector">
                        <label class="price-option">
                            <input type="radio" name="price_range" value="€" required>
                            <div class="price-symbol">€</div>
                            <div class="price-label">Économique</div>
                        </label>
                        <label class="price-option">
                            <input type="radio" name="price_range" value="€€">
                            <div class="price-symbol">€€</div>
                            <div class="price-label">Modéré</div>
                        </label>
                        <label class="price-option">
                            <input type="radio" name="price_range" value="€€€">
                            <div class="price-symbol">€€€</div>
                            <div class="price-label">Haut de gamme</div>
                        </label>
                        <label class="price-option">
                            <input type="radio" name="price_range" value="€€€€">
                            <div class="price-symbol">€€€€</div>
                            <div class="price-label">Luxe</div>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-navigation">
                <div></div>
                <button type="button" class="btn btn-primary" onclick="nextStep()">
                    Continuer <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- ÉTAPE 2 : LOCALISATION -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="step-section" data-step="2">
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Localisation
                </h2>
                <p class="form-card-subtitle">Où se trouve votre restaurant ?</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="pays">Pays <span class="required">*</span></label>
                        <select id="pays" name="pays" required>
                            <option value="Algérie" selected>Algérie</option>
                            <option value="France">France</option>
                            <option value="Maroc">Maroc</option>
                            <option value="Tunisie">Tunisie</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="wilaya">Wilaya <span class="required">*</span></label>
                        <input type="text" id="wilaya" name="wilaya" placeholder="Ex: Alger" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ville">Ville <span class="required">*</span></label>
                        <input type="text" id="ville" name="ville" placeholder="Ex: Alger Centre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quartier">Quartier</label>
                        <input type="text" id="quartier" name="quartier" placeholder="Ex: Didouche Mourad">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="adresse">Adresse complète <span class="required">*</span></label>
                        <input type="text" id="adresse" name="adresse" placeholder="Ex: 123 Rue Didouche Mourad" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" name="code_postal" placeholder="Ex: 16000">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Position GPS</label>
                    <div class="form-row">
                        <input type="text" id="gps_latitude" name="gps_latitude" placeholder="Latitude" readonly>
                        <input type="text" id="gps_longitude" name="gps_longitude" placeholder="Longitude" readonly>
                    </div>
                    <button type="button" class="locate-btn" onclick="locateAddress()">
                        <i class="fas fa-crosshairs"></i>
                        Localiser automatiquement
                    </button>
                    <div class="map-container">
                        <div id="locationMap"></div>
                    </div>
                    <small>Cliquez sur la carte pour ajuster la position si nécessaire</small>
                </div>
            </div>
            
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" onclick="prevStep()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">
                    Continuer <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- ÉTAPE 3 : CONTACT -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="step-section" data-step="3">
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-phone-alt"></i>
                    Coordonnées
                </h2>
                <p class="form-card-subtitle">Comment vous contacter ?</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Téléphone <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="Ex: 0555 12 34 56" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="whatsapp">WhatsApp</label>
                        <input type="tel" id="whatsapp" name="whatsapp" placeholder="Ex: 0555 12 34 56">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Ex: contact@monresto.dz">
                    </div>
                    
                    <div class="form-group">
                        <label for="website">Site web</label>
                        <input type="url" id="website" name="website" placeholder="Ex: https://monresto.dz">
                    </div>
                </div>
            </div>
            
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-share-alt"></i>
                    Réseaux sociaux
                </h2>
                <p class="form-card-subtitle">Optionnel mais recommandé</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="facebook"><i class="fab fa-facebook" style="color: #1877f2;"></i> Facebook</label>
                        <input type="url" id="facebook" name="facebook" placeholder="https://facebook.com/monresto">
                    </div>
                    
                    <div class="form-group">
                        <label for="instagram"><i class="fab fa-instagram" style="color: #e4405f;"></i> Instagram</label>
                        <input type="url" id="instagram" name="instagram" placeholder="https://instagram.com/monresto">
                    </div>
                </div>
            </div>
            
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" onclick="prevStep()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">
                    Continuer <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- ÉTAPE 4 : HORAIRES -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="step-section" data-step="4">
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-clock"></i>
                    Horaires d'ouverture
                </h2>
                <p class="form-card-subtitle">Définissez vos horaires pour chaque jour</p>
                
                <div class="horaires-grid">
                    <?php 
                    $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                    foreach ($jours as $index => $jour): 
                    ?>
                    <div class="horaire-row" data-jour="<?= $index ?>">
                        <div class="horaire-jour"><?= $jour ?></div>
                        <div class="horaire-controls">
                            <div class="horaire-checkbox">
                                <input type="checkbox" 
                                       id="ferme_<?= $index ?>" 
                                       name="horaires[<?= $index ?>][ferme]" 
                                       value="1"
                                       onchange="toggleHoraire(<?= $index ?>)">
                                <label for="ferme_<?= $index ?>">Fermé</label>
                            </div>
                            
                            <div class="horaire-content" id="horaire_content_<?= $index ?>">
                                <div class="horaire-service-type">
                                    <button type="button" class="service-type-btn active" onclick="setServiceType(<?= $index ?>, 'normal')">
                                        Midi & Soir
                                    </button>
                                    <button type="button" class="service-type-btn" onclick="setServiceType(<?= $index ?>, 'continu')">
                                        Service continu
                                    </button>
                                </div>
                                
                                <input type="hidden" name="horaires[<?= $index ?>][service_continu]" id="service_continu_<?= $index ?>" value="0">
                                
                                <!-- Horaires normaux (midi + soir) -->
                                <div class="horaire-times horaire-normal" id="horaire_normal_<?= $index ?>">
                                    <span>Midi:</span>
                                    <input type="time" name="horaires[<?= $index ?>][ouverture_matin]" value="12:00">
                                    <span>-</span>
                                    <input type="time" name="horaires[<?= $index ?>][fermeture_matin]" value="14:30">
                                    <span style="margin-left: 12px;">Soir:</span>
                                    <input type="time" name="horaires[<?= $index ?>][ouverture_soir]" value="19:00">
                                    <span>-</span>
                                    <input type="time" name="horaires[<?= $index ?>][fermeture_soir]" value="22:30">
                                </div>
                                
                                <!-- Service continu -->
                                <div class="horaire-times horaire-continu" id="horaire_continu_<?= $index ?>" style="display: none;">
                                    <span>De:</span>
                                    <input type="time" name="horaires[<?= $index ?>][continu_ouverture]" value="11:00">
                                    <span>à:</span>
                                    <input type="time" name="horaires[<?= $index ?>][continu_fermeture]" value="23:00">
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="copyToAllDays()">
                        <i class="fas fa-copy"></i> Appliquer à tous les jours
                    </button>
                </div>
            </div>
            
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" onclick="prevStep()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">
                    Continuer <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- ÉTAPE 5 : OPTIONS / AMENITIES -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="step-section" data-step="5">
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-concierge-bell"></i>
                    Services & Équipements
                </h2>
                <p class="form-card-subtitle">Cochez les services proposés par votre établissement</p>
                
                <div class="options-grid">
                    <label class="option-item">
                        <input type="checkbox" name="options[wifi]" value="1">
                        <i class="fas fa-wifi"></i>
                        <span>WiFi gratuit</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[parking]" value="1">
                        <i class="fas fa-parking"></i>
                        <span>Parking</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[terrace]" value="1">
                        <i class="fas fa-umbrella-beach"></i>
                        <span>Terrasse</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[air_conditioning]" value="1">
                        <i class="fas fa-snowflake"></i>
                        <span>Climatisation</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[delivery]" value="1">
                        <i class="fas fa-motorcycle"></i>
                        <span>Livraison</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[takeaway]" value="1">
                        <i class="fas fa-shopping-bag"></i>
                        <span>À emporter</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[accessible_pmr]" value="1">
                        <i class="fas fa-wheelchair"></i>
                        <span>Accessible PMR</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[baby_chair]" value="1">
                        <i class="fas fa-baby"></i>
                        <span>Chaise bébé</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[game_zone]" value="1">
                        <i class="fas fa-gamepad"></i>
                        <span>Espace enfants</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[private_room]" value="1">
                        <i class="fas fa-door-closed"></i>
                        <span>Salon privé</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[prayer_room]" value="1">
                        <i class="fas fa-mosque"></i>
                        <span>Salle de prière</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[valet_service]" value="1">
                        <i class="fas fa-car"></i>
                        <span>Voiturier</span>
                    </label>
                    
                    <label class="option-item">
                        <input type="checkbox" name="options[pets_allowed]" value="1">
                        <i class="fas fa-paw"></i>
                        <span>Animaux acceptés</span>
                    </label>
                </div>
            </div>
            
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-certificate"></i>
                    Certifications
                </h2>
                
            </div>
            
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" onclick="prevStep()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">
                    Continuer <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <!-- ÉTAPE 6 : PHOTOS -->
        <!-- ═══════════════════════════════════════════════════════════════════ -->
        <div class="step-section" data-step="6">
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-camera"></i>
                    Photos de votre restaurant
                </h2>
                <p class="form-card-subtitle">Ajoutez des photos pour attirer plus de clients (min. 1 photo)</p>
                
                <div class="photo-upload-zone" id="photoDropZone">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h3>Glissez vos photos ici</h3>
                    <p>ou cliquez pour sélectionner des fichiers</p>
                    <p><small>JPG, PNG • Max 5 Mo par image • Max 10 photos</small></p>
                    <input type="file" id="photoInput" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" style="display: none;">
                </div>
                
                <div class="photo-preview-grid" id="photoPreviewGrid">
                    <!-- Les previews seront ajoutées ici par JavaScript -->
                </div>
            </div>
            
            <div class="form-card">
                <h2 class="form-card-title">
                    <i class="fas fa-check-circle"></i>
                    Récapitulatif
                </h2>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Votre demande sera examinée par notre équipe.</strong><br>
                        Vous recevrez une notification une fois votre restaurant validé (généralement sous 24-48h).
                    </div>
                </div>
                
                <div id="formRecap">
                    <!-- Rempli par JavaScript -->
                </div>
            </div>
            
            <div class="form-navigation">
                <button type="button" class="btn btn-secondary" onclick="prevStep()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
                <button type="submit" class="btn btn-success" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Soumettre mon restaurant
                </button>
            </div>
        </div>
        
    </form>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ═══════════════════════════════════════════════════════════════════
// VARIABLES GLOBALES
// ═══════════════════════════════════════════════════════════════════
let currentStep = 1;
const totalSteps = 6;
let map = null;
let marker = null;
let uploadedPhotos = [];

// ═══════════════════════════════════════════════════════════════════
// NAVIGATION ENTRE ÉTAPES
// ═══════════════════════════════════════════════════════════════════
function nextStep() {
    if (!validateStep(currentStep)) return;
    
    if (currentStep < totalSteps) {
        currentStep++;
        updateStepDisplay();
        
        // Initialiser la carte si on arrive à l'étape 2
        if (currentStep === 2 && !map) {
            setTimeout(initMap, 100);
        }
        
        // Générer le récap si on arrive à la dernière étape
        if (currentStep === totalSteps) {
            generateRecap();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepDisplay();
    }
}

function goToStep(step) {
    if (step >= 1 && step <= totalSteps) {
        currentStep = step;
        updateStepDisplay();
    }
}

function updateStepDisplay() {
    // Mettre à jour les sections
    document.querySelectorAll('.step-section').forEach(section => {
        section.classList.remove('active');
        if (parseInt(section.dataset.step) === currentStep) {
            section.classList.add('active');
        }
    });
    
    // Mettre à jour la progress bar
    document.querySelectorAll('.progress-step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        step.classList.remove('active', 'completed');
        
        if (stepNum === currentStep) {
            step.classList.add('active');
        } else if (stepNum < currentStep) {
            step.classList.add('completed');
        }
    });
    
    // Scroll en haut
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
    const section = document.querySelector(`.step-section[data-step="${step}"]`);
    const requiredFields = section.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = '#e74c3c';
            isValid = false;
        } else {
            field.style.borderColor = '#ddd';
        }
    });
    
    // Validation spécifique pour l'étape 1 (prix)
    if (step === 1) {
        const priceSelected = document.querySelector('input[name="price_range"]:checked');
        if (!priceSelected) {
            alert('Veuillez sélectionner une gamme de prix');
            return false;
        }
    }
    
    if (!isValid) {
        alert('Veuillez remplir tous les champs obligatoires');
    }
    
    return isValid;
}

// ═══════════════════════════════════════════════════════════════════
// CARTE & GÉOLOCALISATION
// ═══════════════════════════════════════════════════════════════════
function initMap() {
    // Position par défaut (Alger)
    const defaultLat = 36.7538;
    const defaultLng = 3.0588;
    
    map = L.map('locationMap').setView([defaultLat, defaultLng], 12);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);
    
    marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
    
    // Mise à jour des coordonnées quand on déplace le marqueur
    marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        document.getElementById('gps_latitude').value = pos.lat.toFixed(8);
        document.getElementById('gps_longitude').value = pos.lng.toFixed(8);
    });
    
    // Clic sur la carte pour repositionner
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('gps_latitude').value = e.latlng.lat.toFixed(8);
        document.getElementById('gps_longitude').value = e.latlng.lng.toFixed(8);
    });
}

async function locateAddress() {
    const adresse = document.getElementById('adresse').value;
    const ville = document.getElementById('ville').value;
    const wilaya = document.getElementById('wilaya').value;
    const pays = document.getElementById('pays').value;
    
    const fullAddress = `${adresse}, ${ville}, ${wilaya}, ${pays}`;
    
    if (!adresse || !ville) {
        alert('Veuillez remplir l\'adresse et la ville');
        return;
    }
    
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(fullAddress)}`);
        const data = await response.json();
        
        if (data && data.length > 0) {
            const lat = parseFloat(data[0].lat);
            const lng = parseFloat(data[0].lon);
            
            document.getElementById('gps_latitude').value = lat.toFixed(8);
            document.getElementById('gps_longitude').value = lng.toFixed(8);
            
            if (map) {
                map.setView([lat, lng], 16);
                marker.setLatLng([lat, lng]);
            }
        } else {
            alert('Adresse non trouvée. Vous pouvez positionner manuellement le marqueur sur la carte.');
        }
    } catch (error) {
        console.error('Erreur géocodage:', error);
        alert('Erreur lors de la localisation. Veuillez positionner manuellement.');
    }
}

// ═══════════════════════════════════════════════════════════════════
// HORAIRES
// ═══════════════════════════════════════════════════════════════════
function toggleHoraire(jour) {
    const checkbox = document.getElementById(`ferme_${jour}`);
    const content = document.getElementById(`horaire_content_${jour}`);
    
    if (checkbox.checked) {
        content.style.display = 'none';
    } else {
        content.style.display = 'flex';
    }
}

function setServiceType(jour, type) {
    const normalDiv = document.getElementById(`horaire_normal_${jour}`);
    const continuDiv = document.getElementById(`horaire_continu_${jour}`);
    const hiddenInput = document.getElementById(`service_continu_${jour}`);
    const buttons = document.querySelector(`.horaire-row[data-jour="${jour}"]`).querySelectorAll('.service-type-btn');
    
    buttons.forEach(btn => btn.classList.remove('active'));
    
    if (type === 'continu') {
        normalDiv.style.display = 'none';
        continuDiv.style.display = 'flex';
        hiddenInput.value = '1';
        buttons[1].classList.add('active');
    } else {
        normalDiv.style.display = 'flex';
        continuDiv.style.display = 'none';
        hiddenInput.value = '0';
        buttons[0].classList.add('active');
    }
}

function copyToAllDays() {
    // Copier les valeurs du lundi vers tous les autres jours
    const source = document.querySelector('.horaire-row[data-jour="0"]');
    const sourceInputs = source.querySelectorAll('input[type="time"]');
    const sourceFerme = source.querySelector('input[type="checkbox"]').checked;
    const sourceContinu = document.getElementById('service_continu_0').value;
    
    for (let jour = 1; jour <= 6; jour++) {
        // Copier fermé
        document.getElementById(`ferme_${jour}`).checked = sourceFerme;
        toggleHoraire(jour);
        
        // Copier type de service
        setServiceType(jour, sourceContinu === '1' ? 'continu' : 'normal');
        
        // Copier les heures
        const destRow = document.querySelector(`.horaire-row[data-jour="${jour}"]`);
        const destInputs = destRow.querySelectorAll('input[type="time"]');
        
        sourceInputs.forEach((input, index) => {
            if (destInputs[index]) {
                destInputs[index].value = input.value;
            }
        });
    }
    
    alert('Horaires copiés sur tous les jours !');
}

// ═══════════════════════════════════════════════════════════════════
// OPTIONS (AMENITIES)
// ═══════════════════════════════════════════════════════════════════
document.querySelectorAll('.option-item input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
        this.closest('.option-item').classList.toggle('selected', this.checked);
    });
});

// Prix selector
document.querySelectorAll('.price-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.price-option').forEach(opt => opt.classList.remove('selected'));
        this.closest('.price-option').classList.add('selected');
    });
});

// Type cuisine "autre"
document.getElementById('type_cuisine').addEventListener('change', function() {
    const customGroup = document.getElementById('customTypeGroup');
    if (this.value === 'autre') {
        customGroup.style.display = 'block';
    } else {
        customGroup.style.display = 'none';
    }
});

// ═══════════════════════════════════════════════════════════════════
// UPLOAD PHOTOS
// ═══════════════════════════════════════════════════════════════════
const dropZone = document.getElementById('photoDropZone');
const photoInput = document.getElementById('photoInput');
const previewGrid = document.getElementById('photoPreviewGrid');

dropZone.addEventListener('click', () => photoInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

photoInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
});

function handleFiles(files) {
    const maxFiles = 10;
    const maxSize = 5 * 1024 * 1024; // 5 Mo
    
    Array.from(files).forEach(file => {
        if (uploadedPhotos.length >= maxFiles) {
            alert('Maximum 10 photos');
            return;
        }
        
        if (file.size > maxSize) {
            alert(`${file.name} est trop volumineux (max 5 Mo)`);
            return;
        }
        
        if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
            alert(`${file.name} n'est pas un format accepté`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const photoData = {
                file: file,
                preview: e.target.result,
                type: uploadedPhotos.length === 0 ? 'main' : 'slide',
                id: Date.now() + Math.random()
            };
            uploadedPhotos.push(photoData);
            renderPhotoPreviews();
        };
        reader.readAsDataURL(file);
    });
}

function renderPhotoPreviews() {
    previewGrid.innerHTML = uploadedPhotos.map((photo, index) => `
        <div class="photo-preview" data-id="${photo.id}">
            <img src="${photo.preview}" alt="Preview">
            ${index === 0 ? '<span class="photo-badge">Photo principale</span>' : ''}
            <button type="button" class="photo-remove" onclick="removePhoto('${photo.id}')">
                <i class="fas fa-times"></i>
            </button>
            <div class="photo-type-select">
                <select onchange="updatePhotoType('${photo.id}', this.value)">
                    <option value="main" ${photo.type === 'main' ? 'selected' : ''}>Principale</option>
                    <option value="slide" ${photo.type === 'slide' ? 'selected' : ''}>Galerie</option>
                    <option value="ambiance" ${photo.type === 'ambiance' ? 'selected' : ''}>Ambiance</option>
                    <option value="plat" ${photo.type === 'plat' ? 'selected' : ''}>Plat</option>
                    <option value="menu" ${photo.type === 'menu' ? 'selected' : ''}>Menu</option>
                </select>
            </div>
        </div>
    `).join('');
}

function removePhoto(id) {
    uploadedPhotos = uploadedPhotos.filter(p => p.id != id);
    renderPhotoPreviews();
}

function updatePhotoType(id, type) {
    const photo = uploadedPhotos.find(p => p.id == id);
    if (photo) {
        photo.type = type;
    }
}

// ═══════════════════════════════════════════════════════════════════
// RÉCAPITULATIF
// ═══════════════════════════════════════════════════════════════════
function generateRecap() {
    const nom = document.getElementById('nom').value;
    const type = document.getElementById('type_cuisine').value;
    const ville = document.getElementById('ville').value;
    const adresse = document.getElementById('adresse').value;
    const phone = document.getElementById('phone').value;
    
    const selectedOptions = [];
    document.querySelectorAll('.option-item input:checked').forEach(cb => {
        selectedOptions.push(cb.closest('.option-item').querySelector('span').textContent);
    });
    
    document.getElementById('formRecap').innerHTML = `
        <div style="display: grid; gap: 12px;">
            <div><strong>Restaurant :</strong> ${escapeHtml(nom)}</div>
            <div><strong>Cuisine :</strong> ${escapeHtml(type)}</div>
            <div><strong>Adresse :</strong> ${escapeHtml(adresse)}, ${escapeHtml(ville)}</div>
            <div><strong>Téléphone :</strong> ${escapeHtml(phone)}</div>
            <div><strong>Photos :</strong> ${uploadedPhotos.length} photo(s)</div>
            ${selectedOptions.length > 0 ? `<div><strong>Services :</strong> ${selectedOptions.join(', ')}</div>` : ''}
        </div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// ═══════════════════════════════════════════════════════════════════
// SOUMISSION DU FORMULAIRE
// ═══════════════════════════════════════════════════════════════════
document.getElementById('addRestaurantForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (uploadedPhotos.length === 0) {
        alert('Veuillez ajouter au moins une photo');
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    
    const formData = new FormData(this);
    
    // Ajouter les photos avec leur type
    uploadedPhotos.forEach((photo, index) => {
        formData.append('photos[]', photo.file);
        formData.append('photo_types[]', photo.type);
    });
    
    try {
        const response = await fetch('/add-restaurant', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Votre restaurant a été soumis avec succès ! Vous serez notifié une fois validé.');
            window.location.href = data.redirect || '/';
        } else {
            alert(data.message || 'Erreur lors de la soumission');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Soumettre mon restaurant';
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Soumettre mon restaurant';
    }
});
</script>

</body>
</html>