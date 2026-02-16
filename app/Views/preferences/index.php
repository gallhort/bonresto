<style>
.pref-wrap { max-width: 700px; margin: 0 auto; padding: 30px 20px; }
.pref-title { font-size: 24px; font-weight: 800; color: #111827; margin-bottom: 8px; }
.pref-subtitle { font-size: 14px; color: #6b7280; margin-bottom: 30px; }

.pref-section { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.pref-section-title { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
.pref-section-title i { color: #00635a; font-size: 14px; }
.pref-section-desc { font-size: 13px; color: #6b7280; margin-bottom: 16px; }

.pref-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.pref-chip { padding: 8px 14px; border: 2px solid #e5e7eb; border-radius: 20px; font-size: 13px; cursor: pointer; transition: all 0.2s; background: #fff; color: #374151; font-family: inherit; }
.pref-chip:hover { border-color: #00635a; }
.pref-chip.active { background: #00635a; color: #fff; border-color: #00635a; }

.pref-toggle-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
.pref-toggle-row:last-child { border-bottom: none; }
.pref-toggle-label { font-size: 14px; color: #374151; }
.pref-toggle { position: relative; width: 44px; height: 24px; }
.pref-toggle input { opacity: 0; width: 0; height: 0; }
.pref-toggle-slider { position: absolute; inset: 0; background: #d1d5db; border-radius: 12px; cursor: pointer; transition: background 0.2s; }
.pref-toggle-slider::before { content: ''; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: transform 0.2s; }
.pref-toggle input:checked + .pref-toggle-slider { background: #00635a; }
.pref-toggle input:checked + .pref-toggle-slider::before { transform: translateX(20px); }

.pref-save { display: flex; justify-content: flex-end; margin-top: 10px; }
.pref-save-btn { padding: 12px 28px; background: #00635a; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; font-family: inherit; }
.pref-save-btn:hover { background: #004d40; }
.pref-save-btn:disabled { background: #d1d5db; cursor: not-allowed; }

.pref-msg { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; display: none; }
.pref-msg.success { background: #dcfce7; color: #166534; display: block; }
.pref-msg.error { background: #fef2f2; color: #dc2626; display: block; }

@media (max-width: 640px) {
    .pref-wrap { padding: 16px 12px; }
    .pref-section { padding: 16px; }
}
</style>

<div class="pref-wrap">
    <h1 class="pref-title"><i class="fas fa-sliders-h" style="color:#00635a"></i> Mes preferences</h1>
    <p class="pref-subtitle">Personnalisez votre experience LeBonResto</p>

    <div id="prefMsg" class="pref-msg"></div>

    <!-- Cuisines favorites -->
    <div class="pref-section">
        <div class="pref-section-title"><i class="fas fa-utensils"></i> Cuisines favorites</div>
        <p class="pref-section-desc">Selectionnez vos types de cuisine preferes</p>
        <div class="pref-chips" id="cuisineChips">
            <?php
            $cuisines = [
                'algerien'=>'Algerien','tunisien'=>'Tunisien','marocain'=>'Marocain','libanais'=>'Libanais',
                'turc'=>'Turc','italien'=>'Italien','francais'=>'Francais','japonais'=>'Japonais',
                'chinois'=>'Chinois','indien'=>'Indien','mexicain'=>'Mexicain','americain'=>'Americain',
                'thai'=>'Thai','pizza'=>'Pizza','burger'=>'Burger','sushi'=>'Sushi',
                'grillades'=>'Grillades','fruits_de_mer'=>'Fruits de mer','fast-food'=>'Fast-food',
                'patisserie'=>'Patisserie','cafe'=>'Cafe',
            ];
            foreach ($cuisines as $key => $label): ?>
                <button class="pref-chip" data-group="cuisines" data-value="<?= $key ?>" onclick="toggleChip(this)"><?= $label ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Regimes alimentaires -->
    <div class="pref-section">
        <div class="pref-section-title"><i class="fas fa-leaf"></i> Regime alimentaire</div>
        <p class="pref-section-desc">Indiquez vos restrictions alimentaires</p>
        <div class="pref-chips" id="dietChips">
            <?php
            $diets = ['halal'=>'Halal','vegetarien'=>'Vegetarien','vegan'=>'Vegan','sans_gluten'=>'Sans gluten','sans_lactose'=>'Sans lactose','pescetarien'=>'Pescetarien','keto'=>'Keto','casher'=>'Casher'];
            foreach ($diets as $key => $label): ?>
                <button class="pref-chip" data-group="diet" data-value="<?= $key ?>" onclick="toggleChip(this)"><?= $label ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Allergies -->
    <div class="pref-section">
        <div class="pref-section-title"><i class="fas fa-exclamation-triangle"></i> Allergies</div>
        <p class="pref-section-desc">Selectionnez vos allergies pour etre prevenu(e)</p>
        <div class="pref-chips" id="allergyChips">
            <?php
            $allergies = ['gluten'=>'Gluten','dairy'=>'Produits laitiers','eggs'=>'Oeufs','fish'=>'Poisson','shellfish'=>'Crustaces','nuts'=>'Fruits a coque','peanuts'=>'Arachides','soy'=>'Soja','celery'=>'Celeri','mustard'=>'Moutarde','sesame'=>'Sesame','sulfites'=>'Sulfites','lupin'=>'Lupin','mollusks'=>'Mollusques'];
            foreach ($allergies as $key => $label): ?>
                <button class="pref-chip" data-group="allergies" data-value="<?= $key ?>" onclick="toggleChip(this)"><?= $label ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Gamme de prix -->
    <div class="pref-section">
        <div class="pref-section-title"><i class="fas fa-tag"></i> Budget prefere</div>
        <p class="pref-section-desc">Votre gamme de prix habituelle</p>
        <div class="pref-chips" id="priceChips">
            <button class="pref-chip" data-group="price_range" data-value="$" onclick="togglePriceChip(this)">$ Economique</button>
            <button class="pref-chip" data-group="price_range" data-value="$$" onclick="togglePriceChip(this)">$$ Modere</button>
            <button class="pref-chip" data-group="price_range" data-value="$$$" onclick="togglePriceChip(this)">$$$ Premium</button>
            <button class="pref-chip" data-group="price_range" data-value="$$$$" onclick="togglePriceChip(this)">$$$$ Gastronomique</button>
        </div>
    </div>

    <!-- Notifications -->
    <div class="pref-section">
        <div class="pref-section-title"><i class="fas fa-bell"></i> Notifications</div>
        <p class="pref-section-desc">Choisissez comment recevoir vos notifications</p>
        <div class="pref-toggle-row">
            <span class="pref-toggle-label">Notifications par email</span>
            <label class="pref-toggle"><input type="checkbox" id="notifEmail" checked><span class="pref-toggle-slider"></span></label>
        </div>
        <div class="pref-toggle-row">
            <span class="pref-toggle-label">Notifications push</span>
            <label class="pref-toggle"><input type="checkbox" id="notifPush" checked><span class="pref-toggle-slider"></span></label>
        </div>
        <div class="pref-toggle-row">
            <span class="pref-toggle-label">Newsletter</span>
            <label class="pref-toggle"><input type="checkbox" id="notifNewsletter" checked><span class="pref-toggle-slider"></span></label>
        </div>
    </div>

    <div class="pref-save">
        <button class="pref-save-btn" id="prefSaveBtn" onclick="savePreferences()">
            <i class="fas fa-check"></i> Enregistrer
        </button>
    </div>
</div>

<script>
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    // Load current preferences
    fetch('/api/preferences')
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;
        var p = data.preferences;

        // Activate cuisine chips
        (p.cuisines || []).forEach(function(c) {
            var chip = document.querySelector('[data-group="cuisines"][data-value="' + c + '"]');
            if (chip) chip.classList.add('active');
        });

        // Activate diet chips
        (p.diet || []).forEach(function(d) {
            var chip = document.querySelector('[data-group="diet"][data-value="' + d + '"]');
            if (chip) chip.classList.add('active');
        });

        // Activate allergy chips
        (p.allergies || []).forEach(function(a) {
            var chip = document.querySelector('[data-group="allergies"][data-value="' + a + '"]');
            if (chip) chip.classList.add('active');
        });

        // Price range
        if (p.price_range) {
            var chip = document.querySelector('[data-group="price_range"][data-value="' + p.price_range + '"]');
            if (chip) chip.classList.add('active');
        }

        // Notifications
        if (p.notifications) {
            document.getElementById('notifEmail').checked = p.notifications.email !== false;
            document.getElementById('notifPush').checked = p.notifications.push !== false;
            document.getElementById('notifNewsletter').checked = p.notifications.newsletter !== false;
        }
    })
    .catch(function() {});

    window.toggleChip = function(el) {
        el.classList.toggle('active');
    };

    window.togglePriceChip = function(el) {
        // Single select for price
        document.querySelectorAll('[data-group="price_range"]').forEach(function(c) { c.classList.remove('active'); });
        el.classList.add('active');
    };

    window.savePreferences = function() {
        var btn = document.getElementById('prefSaveBtn');
        var msg = document.getElementById('prefMsg');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';

        var cuisines = [];
        document.querySelectorAll('[data-group="cuisines"].active').forEach(function(c) { cuisines.push(c.dataset.value); });

        var diet = [];
        document.querySelectorAll('[data-group="diet"].active').forEach(function(c) { diet.push(c.dataset.value); });

        var allergies = [];
        document.querySelectorAll('[data-group="allergies"].active').forEach(function(c) { allergies.push(c.dataset.value); });

        var priceChip = document.querySelector('[data-group="price_range"].active');
        var priceRange = priceChip ? priceChip.dataset.value : '';

        var payload = {
            cuisines: cuisines,
            diet: diet,
            allergies: allergies,
            price_range: priceRange,
            notifications: {
                email: document.getElementById('notifEmail').checked,
                push: document.getElementById('notifPush').checked,
                newsletter: document.getElementById('notifNewsletter').checked
            }
        };

        fetch('/api/preferences', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(payload)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Enregistrer';
            if (data.success) {
                msg.className = 'pref-msg success';
                msg.textContent = 'Preferences enregistrees avec succes !';
            } else {
                msg.className = 'pref-msg error';
                msg.textContent = data.errors ? data.errors.join(', ') : (data.error || 'Erreur');
            }
            msg.style.display = 'block';
            setTimeout(function() { msg.style.display = 'none'; }, 4000);
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Enregistrer';
            msg.className = 'pref-msg error';
            msg.textContent = 'Erreur de connexion';
            msg.style.display = 'block';
        });
    };
})();
</script>
