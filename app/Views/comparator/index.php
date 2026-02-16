<?php
$optionLabels = [
    'wifi' => ['label' => 'WiFi', 'icon' => 'fa-wifi'],
    'terrasse' => ['label' => 'Terrasse', 'icon' => 'fa-umbrella-beach'],
    'parking' => ['label' => 'Parking', 'icon' => 'fa-parking'],
    'climatisation' => ['label' => 'Clim.', 'icon' => 'fa-snowflake'],
    'livraison' => ['label' => 'Livraison', 'icon' => 'fa-motorcycle'],
    'accessible_pmr' => ['label' => 'PMR', 'icon' => 'fa-wheelchair'],
    'jeux_enfants' => ['label' => 'Jeux enfants', 'icon' => 'fa-child'],
    'emporter' => ['label' => 'A emporter', 'icon' => 'fa-bag-shopping'],
    'salle_privee' => ['label' => 'Salle privee', 'icon' => 'fa-door-closed'],
    'salle_priere' => ['label' => 'Salle priere', 'icon' => 'fa-mosque'],
    'voiturier' => ['label' => 'Voiturier', 'icon' => 'fa-car'],
    'animaux' => ['label' => 'Animaux', 'icon' => 'fa-paw'],
];
?>

<style>
.cmp-page { max-width: 1100px; margin: 0 auto; padding: 24px 20px 60px; }
.cmp-hero { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); border-radius: 20px; padding: 32px 28px; color: #fff; margin-bottom: 24px; position: relative; overflow: hidden; }
.cmp-hero::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.04); border-radius: 50%; }
.cmp-hero h1 { font-size: 24px; font-weight: 800; margin: 0 0 6px; }
.cmp-hero p { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; }

/* Search bar */
.cmp-search-wrap { position: relative; margin-bottom: 16px; }
.cmp-search-input { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 14px; font-family: inherit; box-sizing: border-box; }
.cmp-search-input:focus { border-color: #7c3aed; outline: none; }
.cmp-search-results { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); z-index: 100; max-height: 300px; overflow-y: auto; display: none; }
.cmp-search-item { padding: 10px 14px; cursor: pointer; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
.cmp-search-item:hover { background: #f9fafb; }
.cmp-search-item img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; }

/* Selected chips */
.cmp-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.cmp-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 20px; font-size: 13px; font-weight: 600; color: #7c3aed; }
.cmp-chip-remove { cursor: pointer; color: #6b7280; font-size: 14px; margin-left: 2px; }
.cmp-chip-remove:hover { color: #ef4444; }

/* Compare button */
.cmp-btn { padding: 12px 24px; background: #7c3aed; color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s; font-family: inherit; }
.cmp-btn:hover { background: #6d28d9; }
.cmp-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* ═══════════════════════════════════════
   COMPARISON TABLE — Desktop
   ═══════════════════════════════════════ */
.cmp-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.cmp-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.cmp-table th { padding: 16px 14px; text-align: center; font-size: 12px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; background: #f9fafb; border-bottom: 2px solid #f3f4f6; }
.cmp-table th:first-child { text-align: left; }
.cmp-table td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.cmp-table tr:hover td { background: #fafafa; }
.cmp-table .cmp-label { font-weight: 600; color: #374151; width: 140px; background: #fafafa; white-space: nowrap; }
.cmp-table .cmp-section-row td { background: #f3f0ff; font-weight: 700; color: #7c3aed; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 14px; }

/* Best value highlight */
.cmp-best { background: #f0fdf4 !important; position: relative; }
.cmp-best::after { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: #10b981; }

/* Restaurant header */
.cmp-resto-header { text-align: center; padding: 20px 14px !important; }
.cmp-resto-photo { width: 72px; height: 72px; border-radius: 14px; object-fit: cover; margin: 0 auto 8px; display: block; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.cmp-resto-name { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 2px; }
.cmp-resto-ville { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
.cmp-resto-link { font-size: 12px; color: #7c3aed; text-decoration: none; font-weight: 600; }

/* Note bars */
.cmp-note-bar { display: flex; align-items: center; gap: 6px; }
.cmp-note-val { font-weight: 700; font-size: 14px; min-width: 28px; }
.cmp-note-track { flex: 1; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; min-width: 40px; }
.cmp-note-fill { height: 100%; border-radius: 3px; }

/* Amenity icons */
.cmp-yes { color: #10b981; font-weight: 600; }
.cmp-no { color: #d1d5db; }

.cmp-empty { text-align: center; padding: 60px 20px; }
.cmp-empty i { font-size: 48px; color: #d1d5db; margin-bottom: 12px; display: block; }

/* ═══════════════════════════════════════
   MOBILE — Cartes verticales empilees
   ═══════════════════════════════════════ */
@media (max-width: 768px) {
    .cmp-hero { padding: 24px 20px; }
    .cmp-hero h1 { font-size: 20px; }

    .cmp-table-wrap { overflow-x: visible; }
    .cmp-table { display: none; }

    .cmp-mobile-cards { display: flex; flex-direction: column; gap: 16px; }
    .cmp-mobile-card { background: #fff; border-radius: 16px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); overflow: hidden; }
    .cmp-mobile-card-header { display: flex; align-items: center; gap: 12px; padding: 16px; background: linear-gradient(135deg, #faf5ff, #f5f3ff); border-bottom: 2px solid #ede9fe; }
    .cmp-mobile-card-photo { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; flex-shrink: 0; }
    .cmp-mobile-card-info { flex: 1; min-width: 0; }
    .cmp-mobile-card-name { font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cmp-mobile-card-meta { font-size: 12px; color: #6b7280; }
    .cmp-mobile-card-note { font-size: 22px; font-weight: 800; color: #7c3aed; }

    .cmp-mobile-rows { padding: 0; }
    .cmp-mobile-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
    .cmp-mobile-row:last-child { border-bottom: none; }
    .cmp-mobile-row-label { color: #6b7280; font-weight: 500; flex-shrink: 0; }
    .cmp-mobile-row-val { font-weight: 600; color: #111827; text-align: right; }
    .cmp-mobile-section { padding: 6px 16px; background: #f3f0ff; font-size: 11px; font-weight: 700; color: #7c3aed; text-transform: uppercase; letter-spacing: 0.5px; }
    .cmp-mobile-amenities { display: flex; flex-wrap: wrap; gap: 6px; padding: 10px 16px; }
    .cmp-mobile-amenity { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .cmp-mobile-amenity.yes { background: #ecfdf5; color: #059669; }
    .cmp-mobile-amenity.no { background: #f3f4f6; color: #9ca3af; text-decoration: line-through; }
    .cmp-mobile-actions { display: flex; gap: 8px; padding: 12px 16px; border-top: 1px solid #f3f4f6; }
    .cmp-mobile-actions a { flex: 1; text-align: center; padding: 10px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; }
}
@media (min-width: 769px) {
    .cmp-mobile-cards { display: none; }
}
</style>

<div class="cmp-page">

<div class="cmp-hero">
    <h1><i class="fas fa-balance-scale"></i> Comparateur de restaurants</h1>
    <p>Comparez jusqu'a 3 restaurants cote a cote</p>
</div>

<!-- Search + Selection -->
<div class="cmp-search-wrap">
    <input type="text" id="cmpSearchInput" class="cmp-search-input" placeholder="Rechercher un restaurant a ajouter..." autocomplete="off">
    <div id="cmpSearchResults" class="cmp-search-results"></div>
</div>

<div class="cmp-chips" id="cmpChips"></div>

<button class="cmp-btn" id="cmpCompareBtn" disabled onclick="doCompare()">
    <i class="fas fa-balance-scale"></i> Comparer
</button>

<!-- Results -->
<div id="cmpResults" style="margin-top:24px;display:none;">
    <div class="cmp-table-wrap">
        <table class="cmp-table" id="cmpTable"></table>
    </div>
    <div class="cmp-mobile-cards" id="cmpMobile"></div>
</div>

<div class="cmp-empty" id="cmpEmpty">
    <i class="fas fa-balance-scale"></i>
    <h2 style="font-size:20px;color:#374151;margin-bottom:8px;">Selectionnez 2 ou 3 restaurants</h2>
    <p style="font-size:14px;color:#6b7280;">Utilisez la barre de recherche ci-dessus pour ajouter des restaurants a comparer</p>
</div>

</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const optionLabels = <?= json_encode($optionLabels) ?>;
let selectedRestos = JSON.parse(sessionStorage.getItem('compare_list') || '[]').slice(0, 3);
let searchTimeout = null;

function renderChips() {
    const el = document.getElementById('cmpChips');
    el.innerHTML = selectedRestos.map(r =>
        `<span class="cmp-chip">${r.nom} <span class="cmp-chip-remove" onclick="removeResto(${r.id})">&times;</span></span>`
    ).join('');
    document.getElementById('cmpCompareBtn').disabled = selectedRestos.length < 2;
    sessionStorage.setItem('compare_list', JSON.stringify(selectedRestos));
}

function removeResto(id) {
    selectedRestos = selectedRestos.filter(r => r.id !== id);
    renderChips();
    if (selectedRestos.length < 2) {
        document.getElementById('cmpResults').style.display = 'none';
        document.getElementById('cmpEmpty').style.display = 'block';
    }
}

function addResto(r) {
    if (selectedRestos.length >= 3) return alert('Maximum 3 restaurants');
    if (selectedRestos.find(x => x.id === r.id)) return;
    selectedRestos.push({ id: r.id, nom: r.nom });
    renderChips();
    document.getElementById('cmpSearchInput').value = '';
    document.getElementById('cmpSearchResults').style.display = 'none';
}

// Autocomplete search
document.getElementById('cmpSearchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('cmpSearchResults').style.display = 'none';
        return;
    }
    searchTimeout = setTimeout(() => {
        fetch('/api/search/autocomplete?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('cmpSearchResults');
                const restos = (data.restaurants || []).concat(data.results?.filter(r => r.type === 'restaurant') || []);
                if (!restos.length) {
                    el.innerHTML = '<div style="padding:14px;color:#6b7280;font-size:13px;">Aucun restaurant trouve</div>';
                } else {
                    el.innerHTML = restos.slice(0, 6).map(r => {
                        const photo = r.photo ? '/' + r.photo.replace(/^\//, '') : '';
                        return `<div class="cmp-search-item" onclick='addResto(${JSON.stringify({id:r.id,nom:r.nom||r.name})})'>
                            ${photo ? `<img src="${photo}" alt="">` : '<div style="width:36px;height:36px;border-radius:8px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#9ca3af"><i class="fas fa-store"></i></div>'}
                            <div><strong>${r.nom || r.name}</strong><br><small style="color:#6b7280">${r.ville || ''} · ${r.type_cuisine || ''}</small></div>
                        </div>`;
                    }).join('');
                }
                el.style.display = 'block';
            });
    }, 300);
});

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.cmp-search-wrap')) {
        document.getElementById('cmpSearchResults').style.display = 'none';
    }
});

function doCompare() {
    const ids = selectedRestos.map(r => r.id).join(',');
    document.getElementById('cmpCompareBtn').disabled = true;
    document.getElementById('cmpCompareBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';

    fetch('/api/comparateur?ids=' + ids)
        .then(r => r.json())
        .then(data => {
            document.getElementById('cmpCompareBtn').disabled = false;
            document.getElementById('cmpCompareBtn').innerHTML = '<i class="fas fa-balance-scale"></i> Comparer';

            if (!data.success || !data.restaurants?.length) {
                alert(data.error || 'Erreur');
                return;
            }

            renderTable(data.restaurants);
            renderMobile(data.restaurants);
            document.getElementById('cmpResults').style.display = 'block';
            document.getElementById('cmpEmpty').style.display = 'none';
        })
        .catch(() => {
            document.getElementById('cmpCompareBtn').disabled = false;
            document.getElementById('cmpCompareBtn').innerHTML = '<i class="fas fa-balance-scale"></i> Comparer';
        });
}

function noteColor(val) {
    if (val >= 4) return '#10b981';
    if (val >= 3) return '#f59e0b';
    return '#ef4444';
}

function noteBar(val, max) {
    max = max || 5;
    val = parseFloat(val) || 0;
    const pct = Math.round((val / max) * 100);
    return `<div class="cmp-note-bar">
        <span class="cmp-note-val" style="color:${noteColor(val)}">${val.toFixed(1)}</span>
        <div class="cmp-note-track"><div class="cmp-note-fill" style="width:${pct}%;background:${noteColor(val)}"></div></div>
    </div>`;
}

// Find best (highest) value index among restos
function bestIdx(restos, getter) {
    let best = -1, bestVal = -Infinity;
    restos.forEach((r, i) => { const v = getter(r); if (v > bestVal) { bestVal = v; best = i; } });
    return bestVal > 0 ? best : -1;
}

function renderTable(restos) {
    const allOpts = Object.keys(optionLabels);
    let html = '<thead><tr><th></th>';
    restos.forEach(r => {
        const photo = r.main_photo ? '/' + r.main_photo.replace(/^\//, '') : '';
        html += `<th class="cmp-resto-header">
            ${photo ? `<img class="cmp-resto-photo" src="${photo}" alt="">` : ''}
            <div class="cmp-resto-name">${r.nom}</div>
            <div class="cmp-resto-ville"><i class="fas fa-map-marker-alt"></i> ${r.ville || ''} ${r.wilaya ? '(' + r.wilaya + ')' : ''}</div>
            <a href="/restaurant/${r.slug || r.id}" class="cmp-resto-link">Voir <i class="fas fa-external-link-alt"></i></a>
        </th>`;
    });
    html += '</tr></thead><tbody>';

    // Section: Notes
    html += sectionRow('Notes & Avis', restos.length);

    // Note globale
    const bestNote = bestIdx(restos, r => parseFloat(r.note_moyenne) || 0);
    html += '<tr><td class="cmp-label">Note globale</td>';
    restos.forEach((r, i) => { html += `<td${i === bestNote ? ' class="cmp-best"' : ''}>${noteBar(Math.min(5, r.note_moyenne || 0))}</td>`; });
    html += '</tr>';

    // Sub-notes
    const subLabels = { nourriture: 'Nourriture', service: 'Service', ambiance: 'Ambiance', prix: 'Rapport qualite/prix' };
    Object.entries(subLabels).forEach(([key, label]) => {
        const bi = bestIdx(restos, r => parseFloat(r.avg_notes?.[key]) || 0);
        html += `<tr><td class="cmp-label">${label}</td>`;
        restos.forEach((r, i) => { html += `<td${i === bi ? ' class="cmp-best"' : ''}>${noteBar(r.avg_notes?.[key] || 0)}</td>`; });
        html += '</tr>';
    });

    // Nombre d'avis
    const bestAvis = bestIdx(restos, r => parseInt(r.platform_reviews) || 0);
    html += '<tr><td class="cmp-label">Nombre d\'avis</td>';
    restos.forEach((r, i) => { html += `<td${i === bestAvis ? ' class="cmp-best"' : ''} style="font-weight:700">${Number(r.platform_reviews || 0).toLocaleString()}</td>`; });
    html += '</tr>';

    // Section: Infos
    html += sectionRow('Informations', restos.length);

    // Ville/Wilaya
    html += '<tr><td class="cmp-label">Ville</td>';
    restos.forEach(r => { html += `<td>${r.ville || '-'} ${r.wilaya ? '<small style="color:#9ca3af">(' + r.wilaya + ')</small>' : ''}</td>`; });
    html += '</tr>';

    // Cuisine
    html += '<tr><td class="cmp-label">Cuisine</td>';
    restos.forEach(r => { html += `<td>${r.type_cuisine || '-'}</td>`; });
    html += '</tr>';

    // Prix
    html += '<tr><td class="cmp-label">Gamme de prix</td>';
    restos.forEach(r => { html += `<td style="font-weight:700;font-size:16px;">${r.price_range || '-'}</td>`; });
    html += '</tr>';

    // Popularite
    const bestPop = bestIdx(restos, r => parseFloat(r.popularity_score) || 0);
    html += '<tr><td class="cmp-label">Popularite</td>';
    restos.forEach((r, i) => { html += `<td${i === bestPop ? ' class="cmp-best"' : ''} style="font-weight:700;color:#7c3aed">${Math.round(r.popularity_score || 0).toLocaleString()}</td>`; });
    html += '</tr>';

    // Horaires aujourd'hui
    html += '<tr><td class="cmp-label">Aujourd\'hui</td>';
    restos.forEach(r => {
        const h = r.horaires_today;
        if (!h || h.ferme == 1) {
            html += '<td style="color:#ef4444;font-weight:600"><i class="fas fa-times-circle"></i> Ferme</td>';
        } else if (h.service_continu == 1) {
            html += `<td style="color:#10b981;font-weight:600"><i class="fas fa-clock"></i> ${h.ouverture_matin || '?'} - ${h.fermeture_soir || '?'}</td>`;
        } else {
            html += `<td><i class="fas fa-clock" style="color:#6b7280"></i> ${h.ouverture_matin || '?'}-${h.fermeture_matin || '?'}<br><span style="margin-left:18px">${h.ouverture_soir || '?'}-${h.fermeture_soir || '?'}</span></td>`;
        }
    });
    html += '</tr>';

    // Section: Services
    html += sectionRow('Services & Equipements', restos.length);

    // Commande en ligne
    html += '<tr><td class="cmp-label"><i class="fas fa-shopping-bag"></i> Commande</td>';
    restos.forEach(r => {
        html += r.orders_enabled ? '<td class="cmp-yes"><i class="fas fa-check-circle"></i> Oui</td>' : '<td class="cmp-no"><i class="fas fa-minus-circle"></i></td>';
    });
    html += '</tr>';

    // Reservations
    html += '<tr><td class="cmp-label"><i class="fas fa-calendar-check"></i> Reservation</td>';
    restos.forEach(r => {
        html += r.reservations_enabled ? '<td class="cmp-yes"><i class="fas fa-check-circle"></i> Oui</td>' : '<td class="cmp-no"><i class="fas fa-minus-circle"></i></td>';
    });
    html += '</tr>';

    // Amenites
    allOpts.forEach(opt => {
        const info = optionLabels[opt];
        html += `<tr><td class="cmp-label"><i class="fas ${info.icon}"></i> ${info.label}</td>`;
        restos.forEach(r => {
            const has = r.options?.[opt] == 1 || r.options?.[opt] == '1';
            html += has ? '<td class="cmp-yes"><i class="fas fa-check-circle"></i></td>' : '<td class="cmp-no"><i class="fas fa-minus-circle"></i></td>';
        });
        html += '</tr>';
    });

    // Awards
    html += '<tr><td class="cmp-label"><i class="fas fa-trophy"></i> Awards</td>';
    restos.forEach(r => {
        html += '<td>' + (r.awards?.length ? r.awards.map(a => `<span style="font-size:11px;background:#fef3c7;color:#d97706;padding:2px 8px;border-radius:6px;font-weight:600;display:inline-block;margin:1px 0">${a}</span>`).join(' ') : '<span class="cmp-no">-</span>') + '</td>';
    });
    html += '</tr>';

    // Actions
    html += '<tr><td class="cmp-label">Actions</td>';
    restos.forEach(r => {
        html += `<td>
            <a href="/restaurant/${r.slug || r.id}" style="display:inline-block;padding:6px 14px;background:#7c3aed;color:#fff;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;margin:2px">Voir</a>
            ${r.orders_enabled ? `<a href="/commander/${r.slug || r.id}" style="display:inline-block;padding:6px 14px;background:#f59e0b;color:#fff;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;margin:2px">Commander</a>` : ''}
        </td>`;
    });
    html += '</tr>';

    html += '</tbody>';
    document.getElementById('cmpTable').innerHTML = html;
}

function sectionRow(label, cols) {
    return `<tr><td class="cmp-section-row" colspan="${cols + 1}">${label}</td></tr>`;
}

// ═══════════════════════════════════════
// MOBILE: Card layout
// ═══════════════════════════════════════
function renderMobile(restos) {
    const allOpts = Object.keys(optionLabels);
    let html = '';
    restos.forEach(r => {
        const photo = r.main_photo ? '/' + r.main_photo.replace(/^\//, '') : '';
        const note = Math.min(5, parseFloat(r.note_moyenne) || 0);
        const h = r.horaires_today;
        let horaire = '<span style="color:#ef4444">Ferme</span>';
        if (h && h.ferme != 1) {
            horaire = h.service_continu == 1
                ? `${h.ouverture_matin || '?'} - ${h.fermeture_soir || '?'}`
                : `${h.ouverture_matin || '?'}-${h.fermeture_matin || '?'} / ${h.ouverture_soir || '?'}-${h.fermeture_soir || '?'}`;
        }

        html += `<div class="cmp-mobile-card">
            <div class="cmp-mobile-card-header">
                ${photo ? `<img class="cmp-mobile-card-photo" src="${photo}" alt="">` : '<div class="cmp-mobile-card-photo" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#9ca3af"><i class="fas fa-store" style="font-size:20px"></i></div>'}
                <div class="cmp-mobile-card-info">
                    <h3 class="cmp-mobile-card-name">${r.nom}</h3>
                    <div class="cmp-mobile-card-meta"><i class="fas fa-map-marker-alt"></i> ${r.ville || ''} ${r.wilaya ? '(' + r.wilaya + ')' : ''} · ${r.type_cuisine || ''}</div>
                </div>
                <div class="cmp-mobile-card-note">${note.toFixed(1)}</div>
            </div>
            <div class="cmp-mobile-rows">
                <div class="cmp-mobile-section">Notes</div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Nourriture</span><span class="cmp-mobile-row-val" style="color:${noteColor(r.avg_notes?.nourriture || 0)}">${(parseFloat(r.avg_notes?.nourriture) || 0).toFixed(1)}/5</span></div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Service</span><span class="cmp-mobile-row-val" style="color:${noteColor(r.avg_notes?.service || 0)}">${(parseFloat(r.avg_notes?.service) || 0).toFixed(1)}/5</span></div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Ambiance</span><span class="cmp-mobile-row-val" style="color:${noteColor(r.avg_notes?.ambiance || 0)}">${(parseFloat(r.avg_notes?.ambiance) || 0).toFixed(1)}/5</span></div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Qualite/prix</span><span class="cmp-mobile-row-val" style="color:${noteColor(r.avg_notes?.prix || 0)}">${(parseFloat(r.avg_notes?.prix) || 0).toFixed(1)}/5</span></div>

                <div class="cmp-mobile-section">Infos</div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Avis</span><span class="cmp-mobile-row-val">${Number(r.platform_reviews || 0).toLocaleString()}</span></div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Prix</span><span class="cmp-mobile-row-val" style="font-size:16px">${r.price_range || '-'}</span></div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Popularite</span><span class="cmp-mobile-row-val" style="color:#7c3aed">${Math.round(r.popularity_score || 0).toLocaleString()}</span></div>
                <div class="cmp-mobile-row"><span class="cmp-mobile-row-label">Horaires</span><span class="cmp-mobile-row-val">${horaire}</span></div>

                <div class="cmp-mobile-section">Services</div>
                <div class="cmp-mobile-amenities">
                    ${r.orders_enabled ? '<span class="cmp-mobile-amenity yes"><i class="fas fa-shopping-bag"></i> Commande</span>' : ''}
                    ${r.reservations_enabled ? '<span class="cmp-mobile-amenity yes"><i class="fas fa-calendar-check"></i> Reservation</span>' : ''}
                    ${allOpts.map(opt => {
                        const has = r.options?.[opt] == 1 || r.options?.[opt] == '1';
                        const info = optionLabels[opt];
                        return has ? `<span class="cmp-mobile-amenity yes"><i class="fas ${info.icon}"></i> ${info.label}</span>` : '';
                    }).filter(Boolean).join('')}
                </div>
            </div>
            <div class="cmp-mobile-actions">
                <a href="/restaurant/${r.slug || r.id}" style="background:#7c3aed;color:#fff;">Voir le resto</a>
                ${r.orders_enabled ? `<a href="/commander/${r.slug || r.id}" style="background:#f59e0b;color:#fff;">Commander</a>` : ''}
            </div>
        </div>`;
    });
    document.getElementById('cmpMobile').innerHTML = html;
}

// Init from localStorage
renderChips();
if (selectedRestos.length >= 2) doCompare();
</script>
