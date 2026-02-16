<?php
$restoName = htmlspecialchars($restaurant['nom'] ?? 'Restaurant');
$reservationId = (int)($reservation['id'] ?? 0);
$restoId = (int)($restaurant['id'] ?? 0);
?>
<style>
    .srv-wrap {
        max-width: 640px;
        margin: 0 auto;
        padding: 32px 20px 60px;
    }

    /* Header card */
    .srv-header {
        background: linear-gradient(135deg, #00635a, #004d40);
        border-radius: 16px;
        padding: 32px 28px;
        color: #fff;
        text-align: center;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .srv-header::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -15%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.08), transparent 70%);
        border-radius: 50%;
    }
    .srv-header-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin: 0 auto 16px;
    }
    .srv-header h1 {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 8px;
    }
    .srv-header p {
        font-size: 15px;
        opacity: 0.85;
        margin: 0;
    }
    .srv-header .srv-resto {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.15);
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 12px;
    }

    /* Form card */
    .srv-form {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        overflow: hidden;
    }

    /* Rating section */
    .srv-section {
        padding: 24px 28px;
        border-bottom: 1px solid #f3f4f6;
    }
    .srv-section:last-child { border-bottom: none; }
    .srv-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .srv-section-title i { color: #00635a; font-size: 15px; }

    /* Star rating row */
    .srv-rating-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f9fafb;
    }
    .srv-rating-row:last-child { border-bottom: none; }
    .srv-rating-label {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .srv-rating-label i {
        font-size: 14px;
        color: #6b7280;
        width: 20px;
        text-align: center;
    }

    /* Stars */
    .srv-stars {
        display: flex;
        gap: 4px;
        direction: rtl;
    }
    .srv-stars input { display: none; }
    .srv-stars label {
        font-size: 26px;
        color: #e5e7eb;
        cursor: pointer;
        transition: color 0.15s, transform 0.15s;
        padding: 0 2px;
    }
    .srv-stars label:hover,
    .srv-stars label:hover ~ label,
    .srv-stars input:checked ~ label {
        color: #f59e0b;
    }
    .srv-stars label:hover { transform: scale(1.2); }

    .srv-rating-value {
        font-size: 12px;
        color: #9ca3af;
        margin-top: 2px;
        min-height: 16px;
        text-align: right;
    }

    /* Recommend toggle */
    .srv-recommend {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .srv-recommend-btn {
        flex: 1;
        padding: 14px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
        color: #6b7280;
        font-family: inherit;
    }
    .srv-recommend-btn i { margin-right: 6px; }
    .srv-recommend-btn:hover { border-color: #00635a; color: #00635a; }
    .srv-recommend-btn.active-yes {
        border-color: #10b981;
        background: #ecfdf5;
        color: #059669;
    }
    .srv-recommend-btn.active-no {
        border-color: #ef4444;
        background: #fef2f2;
        color: #dc2626;
    }

    /* Textarea */
    .srv-textarea {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        min-height: 100px;
        transition: border-color 0.2s;
        box-sizing: border-box;
        line-height: 1.5;
    }
    .srv-textarea:focus { outline: none; border-color: #00635a; }
    .srv-textarea::placeholder { color: #9ca3af; }
    .srv-char-count {
        text-align: right;
        font-size: 12px;
        color: #9ca3af;
        margin-top: 6px;
    }

    /* Submit */
    .srv-submit-wrap {
        padding: 20px 28px 28px;
    }
    .srv-submit {
        width: 100%;
        padding: 16px;
        background: #00635a;
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: inherit;
    }
    .srv-submit:hover { background: #004d40; }
    .srv-submit:disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* Feedback message */
    .srv-feedback {
        text-align: center;
        padding: 16px;
        margin: 16px 28px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        display: none;
    }
    .srv-feedback.success {
        display: block;
        background: #ecfdf5;
        color: #059669;
    }
    .srv-feedback.error {
        display: block;
        background: #fef2f2;
        color: #dc2626;
    }

    /* Success state */
    .srv-success-state {
        display: none;
        text-align: center;
        padding: 48px 28px;
    }
    .srv-success-state.visible { display: block; }
    .srv-success-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #ecfdf5;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 32px;
        color: #10b981;
    }
    .srv-success-state h2 {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 8px;
    }
    .srv-success-state p {
        font-size: 15px;
        color: #6b7280;
        margin: 0 0 24px;
        line-height: 1.5;
    }
    .srv-success-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 12px 24px;
        background: #00635a;
        color: #fff;
        border-radius: 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .srv-success-link:hover { background: #004d40; }

    @media (max-width: 640px) {
        .srv-wrap { padding: 20px 12px 40px; }
        .srv-section { padding: 20px 18px; }
        .srv-header { padding: 24px 18px; }
        .srv-header h1 { font-size: 20px; }
        .srv-stars label { font-size: 22px; }
        .srv-rating-row { flex-direction: column; align-items: flex-start; gap: 8px; }
        .srv-submit-wrap { padding: 16px 18px 24px; }
    }
</style>

<div class="srv-wrap">
    <!-- Header -->
    <div class="srv-header">
        <div class="srv-header-icon"><i class="fas fa-clipboard-check"></i></div>
        <h1>Votre avis compte !</h1>
        <p>Aidez-nous a ameliorer votre experience en partageant votre ressenti.</p>
        <div class="srv-resto"><i class="fas fa-store"></i> <?= $restoName ?></div>
    </div>

    <!-- Form -->
    <div class="srv-form" id="srvForm">
        <!-- Star ratings -->
        <div class="srv-section">
            <h3 class="srv-section-title"><i class="fas fa-star"></i> Notez votre experience</h3>

            <!-- Nourriture -->
            <div class="srv-rating-row">
                <div>
                    <div class="srv-rating-label"><i class="fas fa-drumstick-bite"></i> Nourriture</div>
                    <div class="srv-rating-value" id="srvFoodVal"></div>
                </div>
                <div class="srv-stars" data-category="food">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                        <input type="radio" name="rating_food" id="food_<?= $s ?>" value="<?= $s ?>">
                        <label for="food_<?= $s ?>" title="<?= $s ?> etoile<?= $s > 1 ? 's' : '' ?>" onclick="updateRatingLabel('food', <?= $s ?>)"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Service -->
            <div class="srv-rating-row">
                <div>
                    <div class="srv-rating-label"><i class="fas fa-bell-concierge"></i> Service</div>
                    <div class="srv-rating-value" id="srvServiceVal"></div>
                </div>
                <div class="srv-stars" data-category="service">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                        <input type="radio" name="rating_service" id="service_<?= $s ?>" value="<?= $s ?>">
                        <label for="service_<?= $s ?>" title="<?= $s ?> etoile<?= $s > 1 ? 's' : '' ?>" onclick="updateRatingLabel('service', <?= $s ?>)"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Ambiance -->
            <div class="srv-rating-row">
                <div>
                    <div class="srv-rating-label"><i class="fas fa-couch"></i> Ambiance</div>
                    <div class="srv-rating-value" id="srvAmbianceVal"></div>
                </div>
                <div class="srv-stars" data-category="ambiance">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                        <input type="radio" name="rating_ambiance" id="ambiance_<?= $s ?>" value="<?= $s ?>">
                        <label for="ambiance_<?= $s ?>" title="<?= $s ?> etoile<?= $s > 1 ? 's' : '' ?>" onclick="updateRatingLabel('ambiance', <?= $s ?>)"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Rapport qualite/prix -->
            <div class="srv-rating-row">
                <div>
                    <div class="srv-rating-label"><i class="fas fa-coins"></i> Rapport qualite/prix</div>
                    <div class="srv-rating-value" id="srvValueVal"></div>
                </div>
                <div class="srv-stars" data-category="value">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                        <input type="radio" name="rating_value" id="value_<?= $s ?>" value="<?= $s ?>">
                        <label for="value_<?= $s ?>" title="<?= $s ?> etoile<?= $s > 1 ? 's' : '' ?>" onclick="updateRatingLabel('value', <?= $s ?>)"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Recommend -->
        <div class="srv-section">
            <h3 class="srv-section-title"><i class="fas fa-heart"></i> Recommanderiez-vous ce restaurant ?</h3>
            <div class="srv-recommend">
                <button type="button" class="srv-recommend-btn" id="srvRecYes" onclick="setRecommend(true)">
                    <i class="fas fa-thumbs-up"></i> Oui
                </button>
                <button type="button" class="srv-recommend-btn" id="srvRecNo" onclick="setRecommend(false)">
                    <i class="fas fa-thumbs-down"></i> Non
                </button>
            </div>
        </div>

        <!-- Feedback -->
        <div class="srv-section">
            <h3 class="srv-section-title"><i class="fas fa-comment-dots"></i> Un commentaire ? (optionnel)</h3>
            <textarea
                class="srv-textarea"
                id="srvFeedback"
                placeholder="Partagez votre experience, ce qui vous a plu ou ce qui pourrait etre ameliore..."
                maxlength="1000"
                oninput="updateCharCount()"
            ></textarea>
            <div class="srv-char-count"><span id="srvCharCount">0</span> / 1000</div>
        </div>

        <!-- Feedback message -->
        <div class="srv-feedback" id="srvFeedbackMsg"></div>

        <!-- Submit -->
        <div class="srv-submit-wrap">
            <button class="srv-submit" id="srvSubmitBtn" onclick="submitSurvey()">
                <i class="fas fa-paper-plane"></i> Envoyer mon avis
            </button>
        </div>

        <!-- Success state (hidden) -->
        <div class="srv-success-state" id="srvSuccess">
            <div class="srv-success-icon"><i class="fas fa-check"></i></div>
            <h2>Merci pour votre avis !</h2>
            <p>Votre retour est precieux et nous aide a ameliorer l'experience pour tous les gourmets.</p>
            <a href="/" class="srv-success-link"><i class="fas fa-home"></i> Retour a l'accueil</a>
        </div>
    </div>
</div>

<script>
var srvData = {
    food: 0,
    service: 0,
    ambiance: 0,
    value: 0,
    recommend: null
};

var ratingLabels = {
    1: 'Mauvais',
    2: 'Mediocre',
    3: 'Correct',
    4: 'Bien',
    5: 'Excellent'
};

function updateRatingLabel(category, score) {
    srvData[category] = score;
    var el = document.getElementById('srv' + capitalize(category) + 'Val');
    if (el) {
        el.textContent = ratingLabels[score] || '';
    }
}

function capitalize(str) {
    var map = { food: 'Food', service: 'Service', ambiance: 'Ambiance', value: 'Value' };
    return map[str] || str.charAt(0).toUpperCase() + str.slice(1);
}

function setRecommend(val) {
    srvData.recommend = val;
    var yesBtn = document.getElementById('srvRecYes');
    var noBtn = document.getElementById('srvRecNo');

    yesBtn.className = 'srv-recommend-btn' + (val === true ? ' active-yes' : '');
    noBtn.className = 'srv-recommend-btn' + (val === false ? ' active-no' : '');
}

function updateCharCount() {
    var textarea = document.getElementById('srvFeedback');
    var counter = document.getElementById('srvCharCount');
    if (textarea && counter) {
        counter.textContent = textarea.value.length;
    }
}

async function submitSurvey() {
    var btn = document.getElementById('srvSubmitBtn');
    var feedbackMsg = document.getElementById('srvFeedbackMsg');

    // Validation
    if (!srvData.food || !srvData.service || !srvData.ambiance || !srvData.value) {
        feedbackMsg.className = 'srv-feedback error';
        feedbackMsg.textContent = 'Veuillez noter toutes les categories (cliquez sur les etoiles).';
        feedbackMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    if (srvData.recommend === null) {
        feedbackMsg.className = 'srv-feedback error';
        feedbackMsg.textContent = 'Veuillez indiquer si vous recommanderiez ce restaurant.';
        feedbackMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    feedbackMsg.className = 'srv-feedback';
    feedbackMsg.style.display = 'none';

    var payload = {
        reservation_id: <?= $reservationId ?>,
        restaurant_id: <?= $restoId ?>,
        rating_food: srvData.food,
        rating_service: srvData.service,
        rating_ambiance: srvData.ambiance,
        rating_value: srvData.value,
        recommend: srvData.recommend,
        feedback: document.getElementById('srvFeedback').value.trim()
    };

    try {
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var headers = { 'Content-Type': 'application/json' };
        if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content;

        var res = await fetch('/api/surveys', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        });
        var data = await res.json();

        if (data.success) {
            // Hide form sections, show success
            var sections = document.querySelectorAll('.srv-section, .srv-submit-wrap, .srv-feedback');
            sections.forEach(function(s) { s.style.display = 'none'; });
            document.getElementById('srvSuccess').classList.add('visible');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer mon avis';
            feedbackMsg.className = 'srv-feedback error';
            feedbackMsg.textContent = data.error || 'Une erreur est survenue. Veuillez reessayer.';
            feedbackMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer mon avis';
        feedbackMsg.className = 'srv-feedback error';
        feedbackMsg.textContent = 'Erreur de connexion. Verifiez votre connexion internet.';
        feedbackMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
