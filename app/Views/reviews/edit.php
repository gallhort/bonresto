<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Modifier mon avis' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8f9fa; }
        .review-container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .review-header { background: white; padding: 24px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .review-header h1 { font-size: 1.75rem; margin-bottom: 8px; color: #1a1a1a; }
        .review-resto-name { color: #666; font-size: 1.1rem; }
        .edit-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #fef3c7; color: #92400e; border-radius: 8px; font-size: 13px; font-weight: 600; margin-top: 12px; }
        .review-form { background: white; padding: 32px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid #e0e0e0; }
        .form-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .form-section h2 { font-size: 1.25rem; margin-bottom: 16px; color: #1a1a1a; display: flex; align-items: center; gap: 8px; }
        .section-icon { color: #34e0a1; }
        .rating-group { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
        .rating-label { min-width: 120px; font-weight: 500; color: #333; }
        .rating-stars { display: flex; gap: 8px; }
        .rating-stars .star { font-size: 28px; cursor: pointer; color: #ddd; transition: all 0.2s; }
        .rating-stars .star:hover { transform: scale(1.2); }
        .rating-stars .star.active { color: #34e0a1; }
        .rating-value { font-weight: 600; min-width: 40px; color: #333; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: 500; margin-bottom: 8px; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 15px; font-family: inherit; transition: border-color 0.2s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #34e0a1; }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .required { color: red; }
        .char-counter { font-size: 12px; color: #999; margin-top: 4px; text-align: right; }
        .form-actions { display: flex; gap: 16px; justify-content: flex-end; padding-top: 24px; }
        .btn { padding: 12px 28px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
        .btn-cancel { background: #f0f0f0; color: #666; }
        .btn-cancel:hover { background: #e0e0e0; }
        .btn-submit { background: #34e0a1; color: white; }
        .btn-submit:hover { background: #2bc48a; }
        .btn-submit:disabled { background: #ccc; cursor: not-allowed; }
        .alert { padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    </style>
</head>
<body>
    <div class="review-container">
        <div class="review-header">
            <h1><i class="fas fa-edit"></i> Modifier mon avis</h1>
            <div class="review-resto-name"><?= htmlspecialchars($restaurant['nom'] ?? '') ?></div>
            <div class="edit-badge">
                <i class="fas fa-info-circle"></i>
                Modification <?= ($review['edit_count'] ?? 0) + 1 ?>/3 - L'avis sera re-modere apres modification
            </div>
        </div>

        <div class="review-form">
            <div class="alert alert-warning">
                <strong><i class="fas fa-exclamation-triangle"></i> Attention :</strong> Apres modification, votre avis sera re-verifie par notre equipe avant publication.
            </div>

            <form id="editReviewForm">
                <!-- NOTE GLOBALE (AUTO-CALCULÉE) -->
                <div class="form-section">
                    <h2><span class="section-icon">*</span> Note globale</h2>
                    <div class="rating-group" style="flex-direction: column; align-items: flex-start; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 16px; width: 100%;">
                            <span class="rating-label" style="min-width: auto;">Calculee automatiquement :</span>
                            <div class="rating-stars" data-rating-target="note_globale" style="pointer-events: none; opacity: 0.7;">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star star <?= $i <= floor($review['note_globale']) ? 'active' : '' ?>" data-value="<?= $i ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-value" id="note_globale_value" style="font-size: 28px; font-weight: 700; color: #34e0a1;"><?= number_format($review['note_globale'], 1) ?>/5</span>
                        </div>
                        <input type="hidden" name="note_globale" id="note_globale" value="<?= $review['note_globale'] ?>">
                    </div>
                </div>

                <!-- NOTES DÉTAILLÉES -->
                <div class="form-section">
                    <h2><span class="section-icon"><i class="fas fa-chart-bar"></i></span> Notes detaillees</h2>

                    <?php
                    $noteFields = [
                        'note_nourriture' => ['label' => 'Nourriture', 'icon' => '<i class="fas fa-utensils"></i>'],
                        'note_service' => ['label' => 'Service', 'icon' => '<i class="fas fa-handshake"></i>'],
                        'note_ambiance' => ['label' => 'Ambiance', 'icon' => '<i class="fas fa-home"></i>'],
                        'note_prix' => ['label' => 'Prix', 'icon' => '<i class="fas fa-wallet"></i>'],
                    ];
                    foreach ($noteFields as $field => $info):
                        $currentVal = (float)($review[$field] ?? 0);
                    ?>
                    <div class="rating-group">
                        <span class="rating-label"><?= $info['icon'] ?> <?= $info['label'] ?> :</span>
                        <div class="rating-stars" data-rating-target="<?= $field ?>">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star star <?= $i <= floor($currentVal) ? 'active' : '' ?>" data-value="<?= $i ?>" style="color: <?= $i <= floor($currentVal) ? '#34e0a1' : '#ddd' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-value" id="<?= $field ?>_value"><?= $currentVal > 0 ? $currentVal . '/5' : '-' ?></span>
                        <input type="hidden" name="<?= $field ?>" id="<?= $field ?>" value="<?= $currentVal ?>">
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- TITRE ET MESSAGE -->
                <div class="form-section">
                    <h2><span class="section-icon"><i class="fas fa-pen"></i></span> Votre avis</h2>

                    <div class="form-group">
                        <label for="title">Titre (optionnel)</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($review['title'] ?? '') ?>" placeholder="Ex: Excellent restaurant, je recommande !">
                    </div>

                    <div class="form-group">
                        <label for="message">Votre commentaire <span class="required">*</span></label>
                        <textarea id="message" name="message" required placeholder="Parlez-nous de votre experience... (minimum 10 caracteres)"><?= htmlspecialchars($review['message'] ?? '') ?></textarea>
                        <div class="char-counter">
                            <span id="charCount"><?= strlen($review['message'] ?? '') ?></span> caracteres
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="form-actions">
                    <a href="/restaurant/<?= $restaurant['id'] ?>" class="btn btn-cancel">Annuler</a>
                    <button type="submit" class="btn btn-submit" id="submitBtn">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gestion des etoiles
        document.querySelectorAll('.rating-stars').forEach(container => {
            const target = container.dataset.ratingTarget;
            if (target === 'note_globale') return;
            const stars = container.querySelectorAll('.star');
            const valueDisplay = document.getElementById(target + '_value');
            const input = document.getElementById(target);

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.dataset.value;
                    input.value = value;
                    valueDisplay.textContent = value + '/5';
                    stars.forEach((s, index) => {
                        s.classList.toggle('active', index < value);
                        s.style.color = index < value ? '#34e0a1' : '#ddd';
                    });
                    updateNoteGlobale();
                });
                star.addEventListener('mouseenter', function() {
                    const value = this.dataset.value;
                    stars.forEach((s, index) => {
                        s.style.color = index < value ? '#34e0a1' : '#ddd';
                    });
                });
            });
            container.addEventListener('mouseleave', function() {
                const currentValue = input.value;
                stars.forEach((s, index) => {
                    s.style.color = index < currentValue ? '#34e0a1' : '#ddd';
                });
            });
        });

        function updateNoteGlobale() {
            const notes = [
                parseFloat(document.getElementById('note_nourriture').value) || 0,
                parseFloat(document.getElementById('note_service').value) || 0,
                parseFloat(document.getElementById('note_ambiance').value) || 0,
                parseFloat(document.getElementById('note_prix').value) || 0
            ];
            const validNotes = notes.filter(n => n > 0);
            const moyenne = validNotes.length > 0 ? validNotes.reduce((a, b) => a + b, 0) / validNotes.length : 0;
            const moyenneRounded = Math.round(moyenne * 2) / 2;
            document.getElementById('note_globale').value = moyenneRounded;
            document.getElementById('note_globale_value').textContent = moyenneRounded.toFixed(1) + '/5';
            const noteGlobaleStars = document.querySelectorAll('[data-rating-target="note_globale"] .star');
            noteGlobaleStars.forEach((s, index) => {
                s.classList.toggle('active', index < moyenneRounded);
                s.style.color = index < moyenneRounded ? '#34e0a1' : '#ddd';
            });
        }

        // Compteur caracteres
        const messageInput = document.getElementById('message');
        const charCount = document.getElementById('charCount');
        messageInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Soumission AJAX
        document.getElementById('editReviewForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mise a jour...';

            try {
                const formData = {
                    title: document.getElementById('title').value,
                    message: document.getElementById('message').value,
                    note_globale: parseFloat(document.getElementById('note_globale').value),
                    note_nourriture: parseFloat(document.getElementById('note_nourriture').value) || null,
                    note_service: parseFloat(document.getElementById('note_service').value) || null,
                    note_ambiance: parseFloat(document.getElementById('note_ambiance').value) || null,
                    note_prix: parseFloat(document.getElementById('note_prix').value) || null
                };

                const response = await fetch('/api/reviews/<?= $review['id'] ?>/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if (data.success) {
                    window.location.href = data.redirect || '/restaurant/<?= $restaurant['id'] ?>';
                } else {
                    alert(data.error || 'Erreur lors de la mise a jour');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enregistrer les modifications';
                }
            } catch (error) {
                alert('Erreur lors de l\'envoi. Veuillez reessayer.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enregistrer les modifications';
            }
        });
    </script>
</body>
</html>
