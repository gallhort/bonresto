<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Laisser un avis' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; }
        
        .review-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        
        .review-header {
            background: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .review-header h1 {
            font-size: 1.75rem;
            margin-bottom: 8px;
            color: #1a1a1a;
        }
        
        .review-resto-name {
            color: #666;
            font-size: 1.1rem;
        }
        
        .review-form {
            background: white;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .form-section:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .form-section h2 {
            font-size: 1.25rem;
            margin-bottom: 16px;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .section-icon {
            color: #34e0a1;
        }

        /* Tag selection */
        .tag-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 13px;
            font-weight: 500;
            background: white;
            user-select: none;
        }
        .tag-option:hover {
            border-color: #34e0a1;
            background: rgba(52, 224, 161, 0.03);
        }
        .tag-option input[type="checkbox"] {
            display: none;
        }
        .tag-option i {
            color: #34e0a1;
            width: 16px;
            text-align: center;
            transition: transform 0.2s;
        }
        /* Selected state via checkbox :checked */
        .tag-option:has(input:checked) {
            border-color: #34e0a1;
            background: rgba(52, 224, 161, 0.08);
            box-shadow: 0 0 0 1px rgba(52, 224, 161, 0.3);
        }
        .tag-option:has(input:checked) i {
            transform: scale(1.15);
        }
        /* Checkmark indicator when selected */
        .tag-option::after {
            content: '';
            margin-left: auto;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid #d0d0d0;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .tag-option:has(input:checked)::after {
            border-color: #34e0a1;
            background: #34e0a1;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
            background-size: 10px;
        }

        .rating-group {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
        }
        
        .rating-label {
            min-width: 120px;
            font-weight: 500;
            color: #333;
        }
        
        .rating-stars {
            display: flex;
            gap: 8px;
        }
        
        .star {
            font-size: 28px;
            cursor: pointer;
            color: #ddd;
            transition: all 0.2s;
        }
        
        .star:hover,
        .star.active {
            color: #34e0a1;
            transform: scale(1.1);
        }
        
        .rating-value {
            min-width: 50px;
            font-weight: 600;
            color: #34e0a1;
            font-size: 18px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }
        
        .required {
            color: #e74c3c;
            margin-left: 4px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #34e0a1;
            box-shadow: 0 0 0 3px rgba(52,224,161,0.1);
        }
        
        .char-counter {
            text-align: right;
            font-size: 14px;
            color: #666;
            margin-top: 4px;
        }
        
        /* Type de voyage - Cards cliquables */
        .trip-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        
        .trip-type-card {
            position: relative;
            padding: 16px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .trip-type-card:hover {
            border-color: #34e0a1;
            box-shadow: 0 4px 12px rgba(52,224,161,0.15);
        }
        
        .trip-type-card.selected {
            border-color: #34e0a1;
            background: rgba(52,224,161,0.05);
        }
        
        .trip-type-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .trip-type-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .trip-type-label {
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        
        .trip-type-card.selected .trip-type-label {
            color: #34e0a1;
        }
        
        /* Mois de visite - Sélecteur stylé */
        .month-selector {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 8px;
            margin-top: 12px;
        }
        
        .month-option {
            position: relative;
        }
        
        .month-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .month-label {
            display: block;
            padding: 10px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            color: #666;
            font-size: 14px;
        }
        
        .month-option input:checked + .month-label {
            border-color: #34e0a1;
            background: rgba(52,224,161,0.1);
            color: #34e0a1;
        }
        
        .month-label:hover {
            border-color: #34e0a1;
        }
        
        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
        }
        
        .btn {
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-cancel {
            background: white;
            border: 2px solid #ddd;
            color: #666;
        }
        
        .btn-cancel:hover {
            border-color: #999;
            color: #333;
        }
        
        .btn-submit {
            background: #34e0a1;
            border: none;
            color: white;
        }
        
        .btn-submit:hover {
            background: #2cc890;
        }
        
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #1976d2;
        }
        
        .alert-success {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #388e3c;
        }
        
        .alert-error {
            background: #ffebee;
            border-left: 4px solid #f44336;
            color: #c62828;
        }
        
        .form-hint {
            font-size: 14px;
            color: #666;
            margin-top: 4px;
            font-style: italic;
        }

        /* ========================================
   UPLOAD PHOTOS - NOUVEAU
   ======================================== */
.photo-upload-zone {
    border: 2px dashed #34e0a1;
    border-radius: 12px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: #f8fffe;
}

.photo-upload-zone:hover {
    border-color: #22c785;
    background: #f0fff9;
    transform: translateY(-2px);
}

.photo-upload-zone.drag-over {
    border-color: #22c785;
    background: #e6fff5;
    transform: scale(1.02);
}

.upload-placeholder {
    pointer-events: none;
}

.upload-placeholder i {
    font-size: 48px;
    color: #34e0a1;
    margin-bottom: 16px;
    display: block;
}

.upload-placeholder p {
    font-size: 16px;
    color: #333;
    margin-bottom: 8px;
    font-weight: 500;
}

.upload-formats {
    font-size: 13px;
    color: #666;
}

.photo-preview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.photo-preview-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    aspect-ratio: 1;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    background: #f0f0f0;
}

.photo-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-remove-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: rgba(239, 68, 68, 0.9);
    color: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    transition: all 0.2s;
    z-index: 10;
}

.photo-remove-btn:hover {
    background: rgba(220, 38, 38, 1);
    transform: scale(1.1);
}

.photo-count {
    font-size: 13px;
    color: #666;
    margin-top: 12px;
    font-weight: 500;
}

.photo-count.max-reached {
    color: #ef4444;
}

.photo-size-error {
    background: #fee;
    border: 1px solid #fcc;
    color: #c00;
    padding: 12px;
    border-radius: 8px;
    margin-top: 12px;
    font-size: 14px;
    display: none;
}

.photo-size-error.show {
    display: block;
    animation: shake 0.3s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}
    </style>
</head>
<body>
    <div class="review-container">
        <div class="review-header">
            <h1>✍️ Laisser un avis</h1>
            <p class="review-resto-name">Pour : <?= htmlspecialchars($restaurant['nom']) ?></p>
        </div>
        
        <div class="review-form">
            <div class="alert alert-info">
                <strong>📝 Votre avis sera modéré</strong><br>
                Il sera publié après validation par notre équipe (généralement sous 24h).
            </div>
            
            <form id="reviewForm" method="POST" action="/restaurant/<?= $restaurant['id'] ?>/review">
                <?= csrf_field() ?>
                <!-- QUAND ÊTES-VOUS ALLÉ ? -->
                <div class="form-section">
                    <h2><span class="section-icon">📅</span> Quand y êtes-vous allé(e) ?</h2>
                    
                    <div class="form-group">
                        <label>Mois de votre visite <span class="required">*</span></label>
                        <div class="month-selector">
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_jan" value="Janvier" required>
                                <label for="month_jan" class="month-label">Janvier</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_feb" value="Février" required>
                                <label for="month_feb" class="month-label">Février</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_mar" value="Mars" required>
                                <label for="month_mar" class="month-label">Mars</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_apr" value="Avril" required>
                                <label for="month_apr" class="month-label">Avril</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_may" value="Mai" required>
                                <label for="month_may" class="month-label">Mai</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_jun" value="Juin" required>
                                <label for="month_jun" class="month-label">Juin</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_jul" value="Juillet" required>
                                <label for="month_jul" class="month-label">Juillet</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_aug" value="Août" required>
                                <label for="month_aug" class="month-label">Août</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_sep" value="Septembre" required>
                                <label for="month_sep" class="month-label">Septembre</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_oct" value="Octobre" required>
                                <label for="month_oct" class="month-label">Octobre</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_nov" value="Novembre" required>
                                <label for="month_nov" class="month-label">Novembre</label>
                            </div>
                            <div class="month-option">
                                <input type="radio" name="visit_month" id="month_dec" value="Décembre" required>
                                <label for="month_dec" class="month-label">Décembre</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="visit_year">Année <span class="required">*</span></label>
                        <select id="visit_year" name="visit_year" required>
                            <option value="">Sélectionnez...</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                        </select>
                    </div>
                </div>
                
                <!-- TYPE DE VOYAGE -->
                <div class="form-section">
                    <h2><span class="section-icon">👥</span> Type de voyage</h2>
                    
                    <div class="trip-type-grid">
                        <div class="trip-type-card">
                            <input type="radio" name="trip_type" id="trip_solo" value="Solo" required>
                            <label for="trip_solo">
                                <div class="trip-type-icon">🧑</div>
                                <div class="trip-type-label">Solo</div>
                            </label>
                        </div>
                        
                        <div class="trip-type-card">
                            <input type="radio" name="trip_type" id="trip_couple" value="En couple" required>
                            <label for="trip_couple">
                                <div class="trip-type-icon">💑</div>
                                <div class="trip-type-label">En couple</div>
                            </label>
                        </div>
                        
                        <div class="trip-type-card">
                            <input type="radio" name="trip_type" id="trip_family" value="En famille" required>
                            <label for="trip_family">
                                <div class="trip-type-icon">👨‍👩‍👧‍👦</div>
                                <div class="trip-type-label">En famille</div>
                            </label>
                        </div>
                        
                        <div class="trip-type-card">
                            <input type="radio" name="trip_type" id="trip_friends" value="Entre amis" required>
                            <label for="trip_friends">
                                <div class="trip-type-icon">👥</div>
                                <div class="trip-type-label">Entre amis</div>
                            </label>
                        </div>
                        
                        <div class="trip-type-card">
                            <input type="radio" name="trip_type" id="trip_business" value="Professionnel" required>
                            <label for="trip_business">
                                <div class="trip-type-icon">💼</div>
                                <div class="trip-type-label">Professionnel</div>
                            </label>
                        </div>
                    </div>
                </div>
                
<!-- NOTE GLOBALE (AUTO-CALCULÉE) -->
<div class="form-section">
    <h2><span class="section-icon">⭐</span> Note globale</h2>
    <div class="rating-group" style="flex-direction: column; align-items: flex-start; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 16px; width: 100%;">
            <span class="rating-label" style="min-width: auto;">Calculée automatiquement :</span>
            <div class="rating-stars" data-rating-target="note_globale" style="pointer-events: none; opacity: 0.7;">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star star" data-value="<?= $i ?>"></i>
                <?php endfor; ?>
            </div>
            <span class="rating-value" id="note_globale_value" style="font-size: 28px; font-weight: 700; color: #34e0a1;">0.0/5</span>
        </div>
        <p style="font-size: 13px; color: #666; margin: 0; padding-left: 4px;">
            <i class="fas fa-info-circle"></i> Moyenne de vos 4 notes détaillées ci-dessous
        </p>
        <input type="hidden" name="note_globale" id="note_globale" value="0" required>
    </div>
</div>
                
                <!-- NOTES DÉTAILLÉES -->
                <div class="form-section">
                    <h2><span class="section-icon">📊</span> Notes détaillées (optionnel)</h2>
                    
                    <div class="rating-group">
                        <span class="rating-label">🍽️ Nourriture :</span>
                        <div class="rating-stars" data-rating-target="note_nourriture">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star star" data-value="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value" id="note_nourriture_value">-</span>
                        <input type="hidden" name="note_nourriture" id="note_nourriture">
                    </div>
                    
                    <div class="rating-group">
                        <span class="rating-label">🤝 Service :</span>
                        <div class="rating-stars" data-rating-target="note_service">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star star" data-value="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value" id="note_service_value">-</span>
                        <input type="hidden" name="note_service" id="note_service">
                    </div>
                    
                    <div class="rating-group">
                        <span class="rating-label">🏠 Ambiance :</span>
                        <div class="rating-stars" data-rating-target="note_ambiance">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star star" data-value="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value" id="note_ambiance_value">-</span>
                        <input type="hidden" name="note_ambiance" id="note_ambiance">
                    </div>
                    
                    <div class="rating-group">
                        <span class="rating-label">💰 Prix :</span>
                        <div class="rating-stars" data-rating-target="note_prix">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star star" data-value="<?= $i ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value" id="note_prix_value">-</span>
                        <input type="hidden" name="note_prix" id="note_prix">
                    </div>
                </div>
                <!-- PHOTOS -->
<div class="form-section">
    <h2><span class="section-icon">📸</span> Photos (optionnel)</h2>
    <p style="font-size: 14px; color: #666; margin-bottom: 16px;">
        Ajoutez jusqu'à 5 photos de votre expérience (JPG, PNG, max 5 MB par photo)
    </p>
    
    <div class="photo-upload-zone" id="photoUploadZone">
        <div class="upload-placeholder">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Glissez vos photos ici ou cliquez pour parcourir</p>
            <span class="upload-formats">JPG, PNG (max 5 MB par photo)</span>
        </div>
        <input type="file" id="photoInput" name="photos[]" multiple accept="image/jpeg,image/png,image/jpg" style="display: none;">
    </div>
    
    <div class="photo-count" id="photoCount">0 / 5 photos</div>
    <div class="photo-size-error" id="photoSizeError"></div>
    <div class="photo-preview-grid" id="photoPreviewGrid"></div>
</div>
                <!-- TAGS D'EXPERIENCE -->
                <div class="form-section">
                    <h2><span class="section-icon"><i class="fas fa-tags"></i></span> Tags d'experience (optionnel)</h2>
                    <p style="font-size: 14px; color: #666; margin-bottom: 16px;">
                        Selectionnez les tags qui correspondent a votre experience
                    </p>
                    <div class="tag-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
                        <?php
                        $availableTags = [
                            'romantique' => ['icon' => 'fa-heart', 'label' => 'Romantique'],
                            'familial' => ['icon' => 'fa-child', 'label' => 'Familial'],
                            'business' => ['icon' => 'fa-briefcase', 'label' => 'Business lunch'],
                            'terrasse' => ['icon' => 'fa-umbrella-beach', 'label' => 'Belle terrasse'],
                            'vue' => ['icon' => 'fa-mountain-sun', 'label' => 'Belle vue'],
                            'calme' => ['icon' => 'fa-volume-low', 'label' => 'Calme'],
                            'anime' => ['icon' => 'fa-music', 'label' => 'Anime'],
                            'bon_rapport' => ['icon' => 'fa-coins', 'label' => 'Bon rapport qualite/prix'],
                            'grandes_portions' => ['icon' => 'fa-utensils', 'label' => 'Grandes portions'],
                            'service_rapide' => ['icon' => 'fa-bolt', 'label' => 'Service rapide'],
                            'livraison' => ['icon' => 'fa-motorcycle', 'label' => 'Bonne livraison'],
                        ];
                        foreach ($availableTags as $tagKey => $tagInfo): ?>
                            <label class="tag-option" for="tag_<?= $tagKey ?>">
                                <input type="checkbox" name="tags[]" value="<?= $tagKey ?>" id="tag_<?= $tagKey ?>">
                                <i class="fas <?= $tagInfo['icon'] ?>"></i>
                                <?= $tagInfo['label'] ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- TITRE ET MESSAGE -->
                <div class="form-section">
                    <h2><span class="section-icon">📝</span> Votre avis</h2>
                    
                    <div class="form-group">
                        <label for="title">Titre (optionnel)</label>
                        <input type="text" id="title" name="title" placeholder="Ex: Excellent restaurant, je recommande !">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Votre commentaire <span class="required">*</span></label>
                        <textarea id="message" name="message" required placeholder="Parlez-nous de votre expérience... (minimum 10 caractères)"></textarea>
                        <div class="char-counter">
                            <span id="charCount">0</span> caractères
                        </div>
                    </div>
                </div>

                <!-- POINTS FORTS / POINTS FAIBLES -->
                <div class="form-section">
                    <h2><span class="section-icon"><i class="fas fa-balance-scale"></i></span> Points forts & faibles (optionnel)</h2>

                    <div class="form-group">
                        <label for="pros"><i class="fas fa-check-circle" style="color:#16a34a"></i> Points forts</label>
                        <textarea id="pros" name="pros" placeholder="Ce que vous avez aimé..." maxlength="500" style="min-height:80px"></textarea>
                        <div class="char-counter"><span id="prosCharCount">0</span> / 500</div>
                    </div>

                    <div class="form-group">
                        <label for="cons"><i class="fas fa-exclamation-circle" style="color:#d97706"></i> Points faibles</label>
                        <textarea id="cons" name="cons" placeholder="Ce qui pourrait être amélioré..." maxlength="500" style="min-height:80px"></textarea>
                        <div class="char-counter"><span id="consCharCount">0</span> / 500</div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="form-actions">
                    <a href="/restaurant/<?= $restaurant['id'] ?>" class="btn btn-cancel">Annuler</a>
                    <button type="submit" class="btn btn-submit" id="submitBtn" disabled>Publier l'avis</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Gestion des cards type de voyage
        document.querySelectorAll('.trip-type-card').forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            const label = card.querySelector('label');
            
            label.addEventListener('click', function(e) {
                e.preventDefault();
                radio.checked = true;
                
                // Retirer la classe selected de toutes les cards
                document.querySelectorAll('.trip-type-card').forEach(c => c.classList.remove('selected'));
                
                // Ajouter la classe selected à la card cliquée
                card.classList.add('selected');
                
                checkFormValidity();
            });
        });
        
        // Gestion des étoiles
        document.querySelectorAll('.rating-stars').forEach(container => {
            const target = container.dataset.ratingTarget;
            const stars = container.querySelectorAll('.star');
            const valueDisplay = document.getElementById(target + '_value');
            const input = document.getElementById(target);
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.dataset.value;
                    input.value = value;
                    valueDisplay.textContent = value + '/5';
                    
                    // Mettre à jour l'affichage des étoiles
                    stars.forEach((s, index) => {
                        if (index < value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                    
                    checkFormValidity();
                });
                
                // Hover effect
                star.addEventListener('mouseenter', function() {
                    const value = this.dataset.value;
                    stars.forEach((s, index) => {
                        if (index < value) {
                            s.style.color = '#34e0a1';
                        } else {
                            s.style.color = '#ddd';
                        }
                    });
                });
            });
            
            container.addEventListener('mouseleave', function() {
                const currentValue = input.value;
                stars.forEach((s, index) => {
                    if (index < currentValue) {
                        s.style.color = '#34e0a1';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });
        
        // Compteur de caractères
        const messageInput = document.getElementById('message');
        const charCount = document.getElementById('charCount');
        
        messageInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
            checkFormValidity();
        });

        // Compteurs de caractères pros/cons
        const prosInput = document.getElementById('pros');
        const prosCharCount = document.getElementById('prosCharCount');
        prosInput.addEventListener('input', function() {
            prosCharCount.textContent = this.value.length;
        });

        const consInput = document.getElementById('cons');
        const consCharCount = document.getElementById('consCharCount');
        consInput.addEventListener('input', function() {
            consCharCount.textContent = this.value.length;
        });
        
        // Validation des champs requis
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            field.addEventListener('change', checkFormValidity);
        });
        
        // Validation du formulaire
        function checkFormValidity() {
            const noteGlobale = document.getElementById('note_globale').value;
            const message = document.getElementById('message').value;
            const visitMonth = document.querySelector('input[name="visit_month"]:checked');
            const visitYear = document.getElementById('visit_year').value;
            const tripType = document.querySelector('input[name="trip_type"]:checked');
            const submitBtn = document.getElementById('submitBtn');
            
            if (noteGlobale && message.trim().length >= 10 && visitMonth && visitYear && tripType) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
        
        // ========================================
// GESTION UPLOAD PHOTOS
// ========================================
const photoInput = document.getElementById('photoInput');
const photoUploadZone = document.getElementById('photoUploadZone');
const photoPreviewGrid = document.getElementById('photoPreviewGrid');
const photoCount = document.getElementById('photoCount');
const photoSizeError = document.getElementById('photoSizeError');
const MAX_PHOTOS = 5;
const MAX_SIZE = 5 * 1024 * 1024; // 5 MB
let selectedFiles = [];

// Click sur zone = ouvrir sélecteur
photoUploadZone.addEventListener('click', () => {
    if (selectedFiles.length < MAX_PHOTOS) {
        photoInput.click();
    }
});

// Sélection fichiers
photoInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
    photoInput.value = ''; // Reset input
});

// Drag & drop
photoUploadZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    photoUploadZone.classList.add('drag-over');
});

photoUploadZone.addEventListener('dragleave', () => {
    photoUploadZone.classList.remove('drag-over');
});

photoUploadZone.addEventListener('drop', (e) => {
    e.preventDefault();
    photoUploadZone.classList.remove('drag-over');
    handleFiles(e.dataTransfer.files);
});

function handleFiles(files) {
    photoSizeError.classList.remove('show');
    let errorMessages = [];
        files = Array.from(files).filter(file => file && file.size > 0);

    Array.from(files).forEach(file => {
        // Vérifier limite nombre
        if (selectedFiles.length >= MAX_PHOTOS) {
            errorMessages.push(`Maximum ${MAX_PHOTOS} photos atteint`);
            return;
        }
        
        // Vérifier type
        if (!file.type.match('image/(jpeg|jpg|png)')) {
            errorMessages.push(`${file.name} : Format non supporté (JPG/PNG uniquement)`);
            return;
        }
        
        // Vérifier taille
        if (file.size > MAX_SIZE) {
            const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
            errorMessages.push(`${file.name} : Trop volumineux (${sizeMB} MB > 5 MB)`);
            return;
        }
        
        // Ajouter fichier
        selectedFiles.push(file);
        addPhotoPreview(file, selectedFiles.length - 1);
    });
    
    // Afficher erreurs
    if (errorMessages.length > 0) {
        photoSizeError.innerHTML = errorMessages.join('<br>');
        photoSizeError.classList.add('show');
        setTimeout(() => photoSizeError.classList.remove('show'), 5000);
    }
    
    updatePhotoCount();
}

function addPhotoPreview(file, index) {
    const reader = new FileReader();
    
    reader.onload = (e) => {
        const div = document.createElement('div');
        div.className = 'photo-preview-item';
        div.dataset.index = index;
        
        div.innerHTML = `
            <img loading="lazy" src="${e.target.result}" alt="Preview">
            <button type="button" class="photo-remove-btn" onclick="removePhoto(${index})">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        photoPreviewGrid.appendChild(div);
    };
    
    reader.readAsDataURL(file);
}

function removePhoto(index) {
    // Retirer du tableau
    selectedFiles.splice(index, 1);
    
    // Recharger les previews
    photoPreviewGrid.innerHTML = '';
    selectedFiles.forEach((file, idx) => {
        addPhotoPreview(file, idx);
    });
    
    updatePhotoCount();
}

function updatePhotoCount() {
    photoCount.textContent = `${selectedFiles.length} / ${MAX_PHOTOS} photos`;
    photoCount.classList.toggle('max-reached', selectedFiles.length >= MAX_PHOTOS);
    
    // Désactiver zone si max atteint
    if (selectedFiles.length >= MAX_PHOTOS) {
        photoUploadZone.style.opacity = '0.5';
        photoUploadZone.style.cursor = 'not-allowed';
    } else {
        photoUploadZone.style.opacity = '1';
        photoUploadZone.style.cursor = 'pointer';
    }
}
// Soumission AJAX
document.getElementById('reviewForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Publication en cours...';
    
    try {
        const formData = new FormData(this);
        
// ✅ APRÈS : Filtrer fichiers vides avant envoi
selectedFiles.filter(file => file && file.size > 0).forEach((file, index) => {
    formData.append('photos[]', file);
});
        






        // ✅ Envoyer au serveur
        const response = await fetch(this.action, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Redirection vers le restaurant
            window.location.href = '/restaurant/<?= $restaurant['id'] ?>';
        } else {
            alert(data.message || 'Erreur lors de la soumission');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Publier l\'avis';
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'envoi. Veuillez réessayer.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Publier l\'avis';
    }
});
            // ========================================
// AUTO-CALCUL NOTE GLOBALE
// ========================================
function updateNoteGlobale() {
    const notes = [
        parseFloat(document.getElementById('note_nourriture').value) || 0,
        parseFloat(document.getElementById('note_service').value) || 0,
        parseFloat(document.getElementById('note_ambiance').value) || 0,
        parseFloat(document.getElementById('note_prix').value) || 0
    ];
    
    // Calculer moyenne (ignorer les 0)
    const validNotes = notes.filter(n => n > 0);
    const moyenne = validNotes.length > 0 
        ? (validNotes.reduce((a, b) => a + b, 0) / validNotes.length)
        : 0;
    
    // Arrondir à 0.5 près
    const moyenneRounded = Math.round(moyenne * 2) / 2;
    
    // Update input et affichage
    document.getElementById('note_globale').value = moyenneRounded;
    document.getElementById('note_globale_value').textContent = moyenneRounded.toFixed(1) + '/5';
    
    // Update stars visuels
    const noteGlobaleStars = document.querySelectorAll('[data-rating-target="note_globale"] .star');
    noteGlobaleStars.forEach((s, index) => {
        if (index < moyenneRounded) {
            s.classList.add('active');
            s.style.color = '#34e0a1';
        } else {
            s.classList.remove('active');
            s.style.color = '#ddd';
        }
    });
    
    checkFormValidity();
}

// Attacher événement sur CHAQUE note détaillée
document.querySelectorAll('.rating-stars').forEach(container => {
    const target = container.dataset.ratingTarget;
    if (target !== 'note_globale') {
        container.addEventListener('click', () => {
            setTimeout(updateNoteGlobale, 50);
        });
    }
});
       
    </script>
</body>
</html>
