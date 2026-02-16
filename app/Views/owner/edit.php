<style>
    .oe-container { max-width: 1000px; margin: 0 auto; padding: 32px 20px; }
    .oe-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
    .oe-header h1 { font-size: 22px; margin: 0; }
    .oe-back { text-decoration: none; color: #00635a; font-size: 14px; display: flex; align-items: center; gap: 6px; }

    /* Tabs */
    .oe-tabs { display: flex; gap: 4px; margin-bottom: 24px; border-bottom: 2px solid #e5e7eb; flex-wrap: wrap; }
    .oe-tab { padding: 10px 18px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; background: none; color: #6b7280; border-bottom: 2px solid transparent; margin-bottom: -2px; }
    .oe-tab.active { color: #00635a; border-bottom-color: #00635a; }

    /* Tab content */
    .oe-panel { display: none; }
    .oe-panel.active { display: block; }

    /* Cards */
    .oe-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 16px; }
    .oe-card h3 { font-size: 16px; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }
    .oe-card h3 i { color: #00635a; }

    /* Form elements */
    .oe-field { margin-bottom: 14px; }
    .oe-label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #374151; }
    .oe-input { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
    .oe-textarea { height: 100px; resize: vertical; }
    .oe-btn { padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; border: none; }
    .oe-btn-primary { background: #00635a; color: #fff; }
    .oe-btn-primary:hover { background: #004d40; }
    .oe-btn-danger { background: #fee2e2; color: #dc2626; }
    .oe-btn-sm { padding: 6px 14px; font-size: 13px; }
    .oe-success { color: #059669; font-size: 13px; display: none; margin-left: 10px; }

    /* Toggle switch */
    .oe-toggle { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .oe-switch { position: relative; width: 44px; height: 24px; background: #d1d5db; border-radius: 12px; cursor: pointer; transition: 0.3s; flex-shrink: 0; }
    .oe-switch.on { background: #00635a; }
    .oe-switch::after { content: ''; position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background: #fff; border-radius: 50%; transition: 0.3s; }
    .oe-switch.on::after { left: 22px; }
    .oe-toggle-label { font-size: 14px; font-weight: 600; color: #374151; }

    /* ── HOURS TABLE ── */
    .oe-hours-grid { display: flex; flex-direction: column; gap: 12px; }
    .oe-hours-row { background: #f9fafb; border-radius: 10px; padding: 14px 16px; }
    .oe-hours-row.closed { opacity: 0.5; }
    .oe-hours-row-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .oe-hours-day { font-weight: 700; font-size: 14px; color: #1f2937; }
    .oe-hours-controls { display: flex; gap: 8px; align-items: center; }
    .oe-hours-times { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
    .oe-hours-slot { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #6b7280; }
    .oe-hours-slot label { font-weight: 600; font-size: 12px; color: #374151; white-space: nowrap; }
    .oe-hours-slot input[type="time"] { padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; }
    .oe-hours-separator { color: #d1d5db; font-weight: 700; font-size: 16px; }
    .oe-hours-continu-label { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 4px; }

    /* ── MENU REDESIGN ── */
    .oe-menu-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px; }
    .oe-menu-header h3 { margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px; }
    .oe-btn-add-top { background: #00635a; color: #fff; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s; box-shadow: 0 2px 8px rgba(0,99,90,0.2); }
    .oe-btn-add-top:hover { background: #004d40; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,99,90,0.3); }

    .oe-menu-cats { display: flex; gap: 6px; margin-bottom: 20px; flex-wrap: wrap; }
    .oe-menu-cat-btn { padding: 8px 16px; border-radius: 20px; border: 2px solid #e5e7eb; background: #fff; font-size: 13px; font-weight: 600; cursor: pointer; color: #6b7280; transition: 0.2s; }
    .oe-menu-cat-btn.active { background: #00635a; color: #fff; border-color: #00635a; }
    .oe-menu-cat-btn .cat-count { background: rgba(0,0,0,0.1); padding: 1px 7px; border-radius: 10px; font-size: 11px; margin-left: 4px; }
    .oe-menu-cat-btn.active .cat-count { background: rgba(255,255,255,0.3); }

    .oe-menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }

    .oe-menu-card { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; transition: 0.2s; position: relative; }
    .oe-menu-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .oe-menu-card.unavailable { opacity: 0.5; }

    .oe-menu-card-photo { width: 100%; height: 160px; object-fit: contain; background: #f9fafb; display: block; cursor: pointer; padding: 4px; box-sizing: border-box; }
    .oe-menu-card-photo-empty { width: 100%; height: 100px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
    .oe-menu-card-photo-empty:hover { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
    .oe-menu-card-photo-empty i { font-size: 24px; color: #9ca3af; }
    .oe-menu-card-photo-empty:hover i { color: #00635a; }

    .oe-menu-card-body { padding: 14px; }
    .oe-menu-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 6px; }
    .oe-menu-card-name { font-size: 15px; font-weight: 700; color: #1f2937; flex: 1; }
    .oe-menu-card-price { font-size: 16px; font-weight: 800; color: #00635a; white-space: nowrap; }
    .oe-menu-card-desc { font-size: 12px; color: #6b7280; line-height: 1.4; margin-bottom: 10px; }

    .oe-menu-card-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 10px; border-top: 1px solid #f3f4f6; }
    .oe-menu-card-actions { display: flex; gap: 6px; }
    .oe-menu-card-actions button { background: #f3f4f6; border: none; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; color: #6b7280; transition: 0.2s; }
    .oe-menu-card-actions button:hover { background: #e5e7eb; color: #1f2937; }
    .oe-menu-card-actions .btn-delete:hover { background: #fee2e2; color: #dc2626; }

    .oe-avail-toggle { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #6b7280; }
    .oe-avail-dot { width: 8px; height: 8px; border-radius: 50%; background: #d1d5db; }
    .oe-avail-dot.on { background: #059669; }

    .oe-menu-card-unavail { position: absolute; top: 8px; right: 8px; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; }

    /* Photo lightbox */
    .oe-lightbox { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 3000; align-items: center; justify-content: center; cursor: pointer; }
    .oe-lightbox.open { display: flex; }
    .oe-lightbox img { max-width: 90%; max-height: 85vh; border-radius: 12px; object-fit: contain; box-shadow: 0 8px 40px rgba(0,0,0,0.5); }
    .oe-lightbox-close { position: absolute; top: 20px; right: 24px; color: #fff; font-size: 32px; cursor: pointer; background: rgba(0,0,0,0.4); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; }
    .oe-lightbox-actions { position: absolute; bottom: 24px; display: flex; gap: 10px; }
    .oe-lightbox-actions button { background: rgba(255,255,255,0.15); color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; backdrop-filter: blur(4px); }
    .oe-lightbox-actions button:hover { background: rgba(255,255,255,0.25); }

    /* Edit/Add modal */
    .oe-modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .oe-modal-overlay.open { display: flex; }
    .oe-modal { background: #fff; border-radius: 16px; padding: 28px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; }
    .oe-modal h3 { margin: 0 0 20px; font-size: 18px; }
    .oe-modal-actions { display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end; }

    /* Stats cards */
    .oe-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px; }
    .oe-stat-card { background: #f9fafb; border-radius: 10px; padding: 16px; text-align: center; }
    .oe-stat-value { font-size: 24px; font-weight: 800; color: #00635a; }
    .oe-stat-label { font-size: 12px; color: #6b7280; margin-top: 4px; }

    /* Reservations */
    .oe-res-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f9fafb; border-radius: 8px; margin-bottom: 8px; }
    .oe-res-info { font-size: 13px; }
    .oe-res-info strong { display: block; margin-bottom: 2px; }
    .oe-res-actions { display: flex; gap: 6px; }
    .oe-res-accept { background: #d1fae5; color: #065f46; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }
    .oe-res-refuse { background: #fee2e2; color: #dc2626; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600; }

    @media (max-width: 600px) {
        .oe-menu-grid { grid-template-columns: 1fr; }
        .oe-add-grid { grid-template-columns: 1fr; }
        .oe-hours-times { flex-direction: column; gap: 8px; }
    }
</style>

<div class="oe-container">
    <div class="oe-header">
        <div>
            <a href="/dashboard" class="oe-back"><i class="fas fa-arrow-left"></i> Retour au dashboard</a>
            <h1><i class="fas fa-store"></i> <?= htmlspecialchars($restaurant['nom']) ?></h1>
        </div>
        <a href="/restaurant/<?= $restaurant['slug'] ?? $restaurant['id'] ?>" class="oe-back">
            <i class="fas fa-external-link-alt"></i> Voir la page publique
        </a>
    </div>

    <div class="oe-tabs">
        <button class="oe-tab active" data-tab="info">Informations</button>
        <button class="oe-tab" data-tab="hours">Horaires</button>
        <button class="oe-tab" data-tab="menu">Menu & Prix</button>
        <button class="oe-tab" data-tab="orders">Commandes <span id="ordersBadge" style="background:#ef4444;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;margin-left:4px;display:none"></span></button>
        <button class="oe-tab" data-tab="reservations">Reservations</button>
        <button class="oe-tab" data-tab="settings">Parametres</button>
        <button class="oe-tab" data-tab="qrcode">QR Code</button>
    </div>

    <!-- TAB: Informations -->
    <div class="oe-panel active" id="tab-info">
        <div class="oe-card">
            <h3><i class="fas fa-info-circle"></i> Informations generales</h3>
            <div class="oe-field">
                <label class="oe-label">Description</label>
                <textarea class="oe-input oe-textarea" id="editDesc"><?= htmlspecialchars($restaurant['description'] ?? '') ?></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                <div class="oe-field">
                    <label class="oe-label">Telephone</label>
                    <input type="text" class="oe-input" id="editTel" value="<?= htmlspecialchars($restaurant['phone'] ?? '') ?>">
                </div>
                <div class="oe-field">
                    <label class="oe-label">Email</label>
                    <input type="email" class="oe-input" id="editEmail" value="<?= htmlspecialchars($restaurant['email'] ?? '') ?>">
                </div>
                <div class="oe-field">
                    <label class="oe-label">Site web</label>
                    <input type="url" class="oe-input" id="editWeb" value="<?= htmlspecialchars($restaurant['website'] ?? '') ?>">
                </div>
                <div class="oe-field">
                    <label class="oe-label">Gamme de prix</label>
                    <select class="oe-input" id="editPrice">
                        <option value="$" <?= ($restaurant['price_range'] ?? '') === '$' ? 'selected' : '' ?>>$ - Economique</option>
                        <option value="$$" <?= ($restaurant['price_range'] ?? '') === '$$' ? 'selected' : '' ?>>$$ - Moyen</option>
                        <option value="$$$" <?= ($restaurant['price_range'] ?? '') === '$$$' ? 'selected' : '' ?>>$$$ - Haut de gamme</option>
                        <option value="$$$$" <?= ($restaurant['price_range'] ?? '') === '$$$$' ? 'selected' : '' ?>>$$$$ - Luxe</option>
                    </select>
                </div>
                <div class="oe-field">
                    <label class="oe-label">Fourchette de prix en DZD (optionnel)</label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <input type="number" class="oe-input" id="editPrixMin" placeholder="Min (ex: 800)" value="<?= htmlspecialchars($restaurant['prix_min'] ?? '') ?>" min="0" style="width:120px">
                        <span>-</span>
                        <input type="number" class="oe-input" id="editPrixMax" placeholder="Max (ex: 2500)" value="<?= htmlspecialchars($restaurant['prix_max'] ?? '') ?>" min="0" style="width:120px">
                        <span style="font-size:13px;color:#6b7280">DA</span>
                    </div>
                </div>
            </div>
            <button class="oe-btn oe-btn-primary" onclick="saveInfo()">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <span class="oe-success" id="infoSuccess"><i class="fas fa-check"></i> Sauvegarde !</span>
        </div>
    </div>

    <!-- TAB: Horaires -->
    <div class="oe-panel" id="tab-hours">
        <div class="oe-card">
            <h3><i class="fas fa-clock"></i> Horaires d'ouverture</h3>
            <p style="font-size:13px;color:#6b7280;margin:0 0 16px">Definissez vos horaires avec service continu ou coupure midi. Cochez "Ferme" si vous etes ferme ce jour-la.</p>
            <div class="oe-hours-grid">
                <?php
                $jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
                $horairesByDay = [];
                foreach ($horaires as $h) $horairesByDay[(int)$h['jour_semaine']] = $h;
                for ($d = 0; $d < 7; $d++):
                    $h = $horairesByDay[$d] ?? null;
                    $isClosed = $h ? (int)($h['ferme'] ?? 0) : 0;
                    $isContinu = $h ? (int)($h['service_continu'] ?? 1) : 1;
                    $ouv_m = $h['ouverture_matin'] ?? '08:00';
                    $ferm_m = $h['fermeture_matin'] ?? '14:00';
                    $ouv_s = $h['ouverture_soir'] ?? '18:00';
                    $ferm_s = $h['fermeture_soir'] ?? '22:00';
                ?>
                <div class="oe-hours-row <?= $isClosed ? 'closed' : '' ?>" id="hoursRow_<?= $d ?>">
                    <div class="oe-hours-row-header">
                        <span class="oe-hours-day"><?= $jours[$d] ?></span>
                        <div class="oe-hours-controls">
                            <label class="oe-hours-continu-label">
                                <input type="checkbox" id="continu_<?= $d ?>" <?= $isContinu ? 'checked' : '' ?> <?= $isClosed ? 'disabled' : '' ?>
                                       onchange="toggleContinu(<?= $d ?>)"> Service continu
                            </label>
                            <label class="oe-hours-continu-label" style="margin-left:8px">
                                <input type="checkbox" id="ferme_<?= $d ?>" <?= $isClosed ? 'checked' : '' ?>
                                       onchange="toggleDay(<?= $d ?>)"> Ferme
                            </label>
                        </div>
                    </div>
                    <div class="oe-hours-times" id="hoursFields_<?= $d ?>" style="<?= $isClosed ? 'display:none' : '' ?>">
                        <!-- Matin / Service continu -->
                        <div class="oe-hours-slot">
                            <label id="labelMatin_<?= $d ?>"><?= $isContinu ? 'Ouverture' : 'Matin' ?></label>
                            <input type="time" id="ouv_m_<?= $d ?>" value="<?= htmlspecialchars($ouv_m) ?>">
                            <span>-</span>
                            <input type="time" id="ferm_m_<?= $d ?>" value="<?= htmlspecialchars($isContinu ? $ferm_s : $ferm_m) ?>" <?= $isContinu ? '' : '' ?>>
                        </div>
                        <!-- Soir (hidden if service continu) -->
                        <div class="oe-hours-slot" id="soirSlot_<?= $d ?>" style="<?= $isContinu ? 'display:none' : '' ?>">
                            <span class="oe-hours-separator">|</span>
                            <label>Soir</label>
                            <input type="time" id="ouv_s_<?= $d ?>" value="<?= htmlspecialchars($ouv_s) ?>">
                            <span>-</span>
                            <input type="time" id="ferm_s_<?= $d ?>" value="<?= htmlspecialchars($ferm_s) ?>">
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            <button class="oe-btn oe-btn-primary" onclick="saveHours()" style="margin-top:16px">
                <i class="fas fa-save"></i> Enregistrer les horaires
            </button>
            <span class="oe-success" id="hoursSuccess"><i class="fas fa-check"></i> Sauvegarde !</span>
        </div>
    </div>

    <!-- TAB: Menu (REDESIGNED) -->
    <div class="oe-panel" id="tab-menu">
        <?php
        $cats = [];
        foreach ($menuItems as $item) {
            $cats[$item['category']][] = $item;
        }
        $allCategories = array_keys($cats);
        $defaultCategories = ['Entrees', 'Plats', 'Desserts', 'Boissons', 'Accompagnements'];
        $mergedCategories = array_unique(array_merge($allCategories, $defaultCategories));
        ?>

        <!-- Header with add button -->
        <div class="oe-menu-header">
            <h3><i class="fas fa-utensils" style="color:#00635a"></i> Carte du restaurant</h3>
            <button class="oe-btn-add-top" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Ajouter un plat
            </button>
        </div>

        <!-- Category sub-tabs -->
        <div class="oe-menu-cats">
            <button class="oe-menu-cat-btn active" data-cat="all" onclick="filterMenuCat('all', this)">
                Tout <span class="cat-count"><?= count($menuItems) ?></span>
            </button>
            <?php foreach ($allCategories as $cat): ?>
            <button class="oe-menu-cat-btn" data-cat="<?= htmlspecialchars($cat) ?>" onclick="filterMenuCat('<?= addslashes(htmlspecialchars($cat)) ?>', this)">
                <?= htmlspecialchars($cat) ?> <span class="cat-count"><?= count($cats[$cat]) ?></span>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Menu items grid -->
        <div class="oe-menu-grid" id="menuGrid">
            <?php if (empty($menuItems)): ?>
                <div style="grid-column:1/-1;text-align:center;padding:40px;color:#9ca3af">
                    <i class="fas fa-utensils" style="font-size:36px;margin-bottom:12px;display:block"></i>
                    <p style="font-size:14px;margin:0">Votre menu est vide. Ajoutez vos premiers plats ci-dessous.</p>
                </div>
            <?php else: ?>
                <?php foreach ($menuItems as $item): ?>
                <div class="oe-menu-card <?= !(int)$item['is_available'] ? 'unavailable' : '' ?>" data-id="<?= $item['id'] ?>" data-category="<?= htmlspecialchars($item['category']) ?>">
                    <?php if (!(int)$item['is_available']): ?>
                        <span class="oe-menu-card-unavail">Indisponible</span>
                    <?php endif; ?>

                    <?php if (!empty($item['photo_path'])): ?>
                        <img class="oe-menu-card-photo" src="/uploads/menu/<?= htmlspecialchars($item['photo_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onclick="openLightbox(this.src, <?= $item['id'] ?>)" title="Cliquez pour voir en grand">
                    <?php else: ?>
                        <div class="oe-menu-card-photo-empty" onclick="triggerPhotoUpload(<?= $item['id'] ?>)">
                            <i class="fas fa-camera-retro"></i>
                        </div>
                    <?php endif; ?>

                    <div class="oe-menu-card-body">
                        <div class="oe-menu-card-top">
                            <div class="oe-menu-card-name"><?= htmlspecialchars($item['name']) ?></div>
                            <?php if ($item['price']): ?>
                                <div class="oe-menu-card-price"><?= number_format((float)$item['price'], 0) ?> DA</div>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['description'])): ?>
                            <div class="oe-menu-card-desc"><?= htmlspecialchars($item['description']) ?></div>
                        <?php endif; ?>

                        <div class="oe-menu-card-footer">
                            <div class="oe-avail-toggle" onclick="toggleMenuAvail(<?= $item['id'] ?>, this)" style="cursor:pointer" title="Cliquez pour changer la disponibilite">
                                <span class="oe-avail-dot <?= (int)$item['is_available'] ? 'on' : '' ?>"></span>
                                <span><?= (int)$item['is_available'] ? 'Disponible' : 'Indisponible' ?></span>
                            </div>
                            <div class="oe-menu-card-actions">
                                <button onclick="openEditModal(<?= $item['id'] ?>, <?= htmlspecialchars(json_encode($item['name'])) ?>, <?= (float)($item['price'] ?? 0) ?>, <?= htmlspecialchars(json_encode($item['description'] ?? '')) ?>, <?= htmlspecialchars(json_encode($item['category'] ?? 'Plats')) ?>)" title="Modifier"><i class="fas fa-pen"></i></button>
                                <button onclick="changeMenuPhoto(<?= $item['id'] ?>)" title="Changer la photo"><i class="fas fa-image"></i></button>
                                <button class="btn-delete" onclick="deleteMenuItem(<?= $item['id'] ?>, this)" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <!-- Edit Modal -->
    <div class="oe-modal-overlay" id="editModal">
        <div class="oe-modal">
            <h3><i class="fas fa-pen" style="color:#00635a;margin-right:8px"></i> Modifier le plat</h3>
            <input type="hidden" id="editItemId">
            <div class="oe-field">
                <label class="oe-label">Nom</label>
                <input type="text" class="oe-input" id="editItemName">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="oe-field">
                    <label class="oe-label">Prix (DA)</label>
                    <input type="number" class="oe-input" id="editItemPrice" min="0">
                </div>
                <div class="oe-field">
                    <label class="oe-label">Categorie</label>
                    <select class="oe-input" id="editItemCat">
                        <?php foreach ($mergedCategories as $cat): ?>
                            <option><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="oe-field">
                <label class="oe-label">Description</label>
                <textarea class="oe-input" id="editItemDesc" rows="3" style="resize:vertical"></textarea>
            </div>
            <div class="oe-modal-actions">
                <button class="oe-btn" style="background:#f3f4f6;color:#374151" onclick="closeEditModal()">Annuler</button>
                <button class="oe-btn oe-btn-primary" onclick="saveEditedItem()"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="oe-modal-overlay" id="addModal">
        <div class="oe-modal">
            <h3><i class="fas fa-plus-circle" style="color:#00635a;margin-right:8px"></i> Ajouter un plat</h3>
            <div class="oe-field">
                <label class="oe-label">Nom du plat *</label>
                <input type="text" class="oe-input" id="newMenuName" placeholder="Ex: Couscous royal">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="oe-field">
                    <label class="oe-label">Prix (DA)</label>
                    <input type="number" class="oe-input" id="newMenuPrice" placeholder="1200" min="0">
                </div>
                <div class="oe-field">
                    <label class="oe-label">Categorie</label>
                    <select class="oe-input" id="newMenuCat">
                        <?php foreach ($mergedCategories as $cat): ?>
                            <option <?= $cat === 'Plats' ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                        <option value="__new__">+ Nouvelle categorie...</option>
                    </select>
                </div>
            </div>
            <div class="oe-field">
                <label class="oe-label">Description (optionnel)</label>
                <input type="text" class="oe-input" id="newMenuDesc" placeholder="Ingredients, details...">
            </div>
            <div class="oe-field">
                <label class="oe-label">Photo (optionnel)</label>
                <input type="file" accept="image/jpeg,image/png,image/webp" id="newMenuPhoto" class="oe-input" style="padding:6px">
            </div>
            <div class="oe-modal-actions">
                <button class="oe-btn" style="background:#f3f4f6;color:#374151" onclick="closeAddModal()">Annuler</button>
                <button class="oe-btn oe-btn-primary" onclick="addMenuItem()"><i class="fas fa-plus"></i> Ajouter au menu</button>
            </div>
        </div>
    </div>

    <!-- Photo Lightbox -->
    <div class="oe-lightbox" id="photoLightbox" onclick="closeLightbox(event)">
        <button class="oe-lightbox-close" onclick="closeLightbox(event)">&times;</button>
        <img id="lightboxImg" src="" alt="Photo du plat">
        <div class="oe-lightbox-actions">
            <button onclick="event.stopPropagation(); changeLightboxPhoto()"><i class="fas fa-image"></i> Changer la photo</button>
        </div>
    </div>

    <!-- Hidden file input for photo uploads -->
    <input type="file" id="hiddenPhotoInput" accept="image/jpeg,image/png,image/webp" style="display:none">

    <!-- TAB: Commandes -->
    <div class="oe-panel" id="tab-orders">
        <?php if (!($restaurant['orders_enabled'] ?? 0)): ?>
            <div class="oe-card" style="text-align:center;padding:40px">
                <i class="fas fa-shopping-bag" style="font-size:36px;color:#d1d5db;margin-bottom:12px"></i>
                <h3 style="margin:0 0 8px">Commande en ligne desactivee</h3>
                <p style="font-size:13px;color:#6b7280;margin:0 0 16px">Activez la commande en ligne dans l'onglet "Parametres" pour recevoir des commandes.</p>
            </div>
        <?php else: ?>
            <div class="oe-card">
                <h3><i class="fas fa-chart-bar"></i> Statistiques</h3>
                <div class="oe-stats-grid" id="orderStats">
                    <div class="oe-stat-card"><div class="oe-stat-value" id="statTotal">-</div><div class="oe-stat-label">Commandes</div></div>
                    <div class="oe-stat-card"><div class="oe-stat-value" id="statRevenue">-</div><div class="oe-stat-label">Chiffre d'affaires</div></div>
                    <div class="oe-stat-card"><div class="oe-stat-value" id="statAvg">-</div><div class="oe-stat-label">Panier moyen</div></div>
                    <div class="oe-stat-card"><div class="oe-stat-value" id="statToday">-</div><div class="oe-stat-label">Aujourd'hui</div></div>
                </div>
            </div>
            <div class="oe-card">
                <h3><i class="fas fa-shopping-bag"></i> Commandes en attente</h3>
                <div id="pendingOrders"><p style="color:#9ca3af;text-align:center;padding:20px;font-size:13px"><i class="fas fa-spinner fa-spin"></i> Chargement...</p></div>
            </div>
            <div class="oe-card">
                <h3><i class="fas fa-fire"></i> Commandes actives</h3>
                <div id="activeOrders"><p style="color:#9ca3af;text-align:center;padding:20px;font-size:13px"><i class="fas fa-spinner fa-spin"></i> Chargement...</p></div>
            </div>
            <div class="oe-card">
                <h3><i class="fas fa-history"></i> Historique
                    <select id="historyFilter" onchange="loadOwnerOrders()" style="margin-left:auto;padding:4px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:12px">
                        <option value="delivered">Livrees</option>
                        <option value="cancelled">Annulees</option>
                        <option value="refused">Refusees</option>
                    </select>
                </h3>
                <div id="historyOrders"><p style="color:#9ca3af;text-align:center;padding:20px;font-size:13px">Selectionnez un filtre</p></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- TAB: Reservations -->
    <div class="oe-panel" id="tab-reservations">
        <div class="oe-card">
            <h3><i class="fas fa-calendar-check"></i> Demandes de reservation</h3>
            <?php if (!empty($pendingReservations)): ?>
                <?php foreach ($pendingReservations as $res): ?>
                <div class="oe-res-item" id="res-<?= $res['id'] ?>">
                    <div class="oe-res-info">
                        <strong><?= htmlspecialchars($res['prenom'] . ' ' . $res['client_nom']) ?></strong>
                        <span><?= date('d/m/Y', strtotime($res['date_souhaitee'])) ?> a <?= htmlspecialchars($res['heure']) ?> &middot; <?= (int)$res['nb_personnes'] ?> pers.</span>
                        <?php if ($res['telephone']): ?><br><small><i class="fas fa-phone"></i> <?= htmlspecialchars($res['telephone']) ?></small><?php endif; ?>
                        <?php if ($res['message']): ?><br><small style="color:#6b7280">"<?= htmlspecialchars(mb_substr($res['message'], 0, 100)) ?>"</small><?php endif; ?>
                    </div>
                    <div class="oe-res-actions">
                        <button class="oe-res-accept" onclick="respondReservation(<?= $res['id'] ?>, 'accept')"><i class="fas fa-check"></i> Accepter</button>
                        <button class="oe-res-refuse" onclick="respondReservation(<?= $res['id'] ?>, 'refuse')"><i class="fas fa-times"></i> Refuser</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color:#9ca3af;text-align:center;padding:20px;font-size:13px">Aucune demande en attente</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- TAB: Parametres -->
    <div class="oe-panel" id="tab-settings">
        <div class="oe-card">
            <h3><i class="fas fa-cog"></i> Parametres du restaurant</h3>
            <div class="oe-toggle">
                <div class="oe-switch <?= $restaurant['reservations_enabled'] ? 'on' : '' ?>" id="toggleRes"
                     onclick="toggleReservations()"></div>
                <span class="oe-toggle-label">Accepter les reservations en ligne</span>
            </div>
            <p style="font-size:12px;color:#6b7280;margin:0 0 20px">Quand active, les utilisateurs peuvent envoyer des demandes de reservation depuis la page de votre restaurant.</p>

            <div class="oe-toggle">
                <div class="oe-switch <?= ($restaurant['menu_enabled'] ?? 0) ? 'on' : '' ?>" id="toggleMenu"
                     onclick="toggleMenuSetting()"></div>
                <span class="oe-toggle-label">Afficher le menu avec prix</span>
            </div>
            <p style="font-size:12px;color:#6b7280;margin:0 0 20px">Quand active, le menu et les prix sont visibles sur la page publique du restaurant.</p>

            <hr style="margin:20px 0;border:none;border-top:1px solid #e5e7eb">
            <h4 style="font-size:14px;margin:0 0 16px"><i class="fas fa-shopping-bag" style="color:#00635a;margin-right:6px"></i> Commande en ligne</h4>

            <div class="oe-toggle">
                <div class="oe-switch <?= ($restaurant['orders_enabled'] ?? 0) ? 'on' : '' ?>" id="toggleOrders"
                     onclick="toggleOrders()"></div>
                <span class="oe-toggle-label">Activer la commande en ligne</span>
            </div>
            <p style="font-size:12px;color:#6b7280;margin:0 0 20px">Quand active, les clients peuvent passer des commandes depuis votre menu.</p>

            <div class="oe-toggle">
                <div class="oe-switch <?= ($restaurant['delivery_enabled'] ?? 0) ? 'on' : '' ?>" id="toggleDelivery"
                     onclick="toggleDelivery()"></div>
                <span class="oe-toggle-label">Proposer la livraison</span>
            </div>
            <p style="font-size:12px;color:#6b7280;margin:0 0 16px">Quand active, les clients peuvent choisir entre retrait sur place et livraison.</p>

            <div id="deliverySettings" style="display:<?= ($restaurant['delivery_enabled'] ?? 0) ? 'block' : 'none' ?>;padding-left:54px">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;max-width:500px">
                    <div class="oe-field">
                        <label class="oe-label">Frais de livraison (DA)</label>
                        <input type="number" class="oe-input" id="editDeliveryFee" placeholder="200" min="0"
                               value="<?= htmlspecialchars($restaurant['delivery_fee'] ?? '') ?>">
                    </div>
                    <div class="oe-field">
                        <label class="oe-label">Commande minimum (DA)</label>
                        <input type="number" class="oe-input" id="editDeliveryMin" placeholder="500" min="0"
                               value="<?= htmlspecialchars($restaurant['delivery_min_order'] ?? '') ?>">
                    </div>
                    <div class="oe-field">
                        <label class="oe-label">Distance max (km)</label>
                        <input type="number" class="oe-input" id="editDeliveryMaxKm" placeholder="10" min="1" max="100" step="0.5"
                               value="<?= htmlspecialchars($restaurant['delivery_max_km'] ?? '') ?>">
                    </div>
                </div>
                <button class="oe-btn oe-btn-primary oe-btn-sm" onclick="saveDeliverySettings()" style="margin-top:8px">
                    <i class="fas fa-save"></i> Enregistrer livraison
                </button>
                <span class="oe-success" id="deliverySuccess"><i class="fas fa-check"></i> Sauvegarde !</span>
            </div>
        </div>
    </div>

    <!-- TAB: QR Code -->
    <div class="oe-panel" id="tab-qrcode">
        <div class="oe-card">
            <h3><i class="fas fa-qrcode"></i> QR Code pour les avis</h3>
            <p style="font-size:13px;color:#6b7280;margin:0 0 20px">Imprimez ce QR code et placez-le dans votre restaurant. Vos clients pourront scanner pour laisser un avis directement.</p>
            <div style="text-align:center;padding:24px;background:#f9fafb;border-radius:12px;border:2px dashed #d1d5db">
                <div id="qrCodeCanvas" style="display:inline-block;background:#fff;padding:16px;border-radius:8px"></div>
                <p style="margin:14px 0 0;font-size:14px;font-weight:600;color:#374151"><?= htmlspecialchars($restaurant['nom']) ?></p>
                <p style="margin:4px 0 0;font-size:12px;color:#6b7280">Scannez pour laisser un avis</p>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;justify-content:center">
                <button class="oe-btn oe-btn-primary" onclick="downloadQR()"><i class="fas fa-download"></i> Telecharger PNG</button>
                <button class="oe-btn" style="background:#f3f4f6;color:#374151" onclick="printQR()"><i class="fas fa-print"></i> Imprimer</button>
            </div>
        </div>

        <?php if ($restaurant['orders_enabled'] ?? 0): ?>
        <div class="oe-card" style="margin-top:16px">
            <h3><i class="fas fa-shopping-bag" style="color:#00635a"></i> QR Code pour commander</h3>
            <p style="font-size:13px;color:#6b7280;margin:0 0 20px">Vos clients scannent ce QR code pour commander directement depuis leur telephone.</p>
            <div style="text-align:center;padding:24px;background:#ecfdf5;border-radius:12px;border:2px dashed #00635a">
                <div id="qrCodeOrder" style="display:inline-block;background:#fff;padding:16px;border-radius:8px"></div>
                <p style="margin:14px 0 0;font-size:14px;font-weight:600;color:#374151"><?= htmlspecialchars($restaurant['nom']) ?></p>
                <p style="margin:4px 0 0;font-size:12px;color:#00635a;font-weight:600">Scannez pour commander</p>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;justify-content:center">
                <button class="oe-btn oe-btn-primary" onclick="downloadOrderQR()"><i class="fas fa-download"></i> Telecharger PNG</button>
                <button class="oe-btn" style="background:#f3f4f6;color:#374151" onclick="printOrderQR()"><i class="fas fa-print"></i> Imprimer</button>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const RESTO_ID = <?= (int)$restaurant['id'] ?>;

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

function apiHeaders(json = true) {
    const h = { 'X-CSRF-TOKEN': getCsrfToken() };
    if (json) h['Content-Type'] = 'application/json';
    return h;
}

// ══════════════════════════════════
// TAB SYSTEM with URL hash persistence
// ══════════════════════════════════
function switchTab(tabName) {
    document.querySelectorAll('.oe-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.oe-panel').forEach(p => p.classList.remove('active'));
    const tab = document.querySelector('.oe-tab[data-tab="' + tabName + '"]');
    const panel = document.getElementById('tab-' + tabName);
    if (tab && panel) {
        tab.classList.add('active');
        panel.classList.add('active');
        window.location.hash = tabName;
    }
}

document.querySelectorAll('.oe-tab').forEach(tab => {
    tab.addEventListener('click', () => switchTab(tab.dataset.tab));
});

// Restore tab from URL hash on load
(function() {
    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById('tab-' + hash)) {
        switchTab(hash);
    }
})();

function reloadToTab(tabName) {
    window.location.hash = tabName;
    window.location.reload();
}

function showSuccess(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'inline';
    setTimeout(() => el.style.display = 'none', 2500);
}

// ══════════════════════════════════
// INFO TAB
// ══════════════════════════════════
async function saveInfo() {
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/update', {
            method: 'POST',
            headers: apiHeaders(),
            body: JSON.stringify({
                description: document.getElementById('editDesc').value,
                phone: document.getElementById('editTel').value,
                email: document.getElementById('editEmail').value,
                website: document.getElementById('editWeb').value,
                price_range: document.getElementById('editPrice').value,
                prix_min: document.getElementById('editPrixMin').value || null,
                prix_max: document.getElementById('editPrixMax').value || null,
            })
        });
        const data = await res.json();
        if (data.success) showSuccess('infoSuccess');
        else alert(data.error || 'Erreur');
    } catch { alert('Erreur de connexion'); }
}

// ══════════════════════════════════
// HOURS TAB (with plages matin/soir)
// ══════════════════════════════════
function toggleDay(day) {
    const closed = document.getElementById('ferme_' + day).checked;
    const row = document.getElementById('hoursRow_' + day);
    const fields = document.getElementById('hoursFields_' + day);
    const continuCb = document.getElementById('continu_' + day);

    if (closed) {
        row.classList.add('closed');
        fields.style.display = 'none';
        continuCb.disabled = true;
    } else {
        row.classList.remove('closed');
        fields.style.display = 'flex';
        continuCb.disabled = false;
    }
}

function toggleContinu(day) {
    const continu = document.getElementById('continu_' + day).checked;
    const soirSlot = document.getElementById('soirSlot_' + day);
    const label = document.getElementById('labelMatin_' + day);

    if (continu) {
        soirSlot.style.display = 'none';
        label.textContent = 'Ouverture';
    } else {
        soirSlot.style.display = 'flex';
        label.textContent = 'Matin';
    }
}

async function saveHours() {
    const horaires = [];
    for (let d = 0; d < 7; d++) {
        const isClosed = document.getElementById('ferme_' + d).checked;
        const isContinu = document.getElementById('continu_' + d).checked;

        horaires.push({
            jour: d,
            est_ferme: isClosed ? 1 : 0,
            service_continu: isContinu ? 1 : 0,
            ouverture_matin: document.getElementById('ouv_m_' + d).value || null,
            fermeture_matin: isContinu ? null : (document.getElementById('ferm_m_' + d).value || null),
            ouverture_soir: (isClosed || isContinu) ? null : (document.getElementById('ouv_s_' + d).value || null),
            fermeture_soir: isContinu
                ? (document.getElementById('ferm_m_' + d).value || null)
                : ((isClosed) ? null : (document.getElementById('ferm_s_' + d).value || null)),
        });
    }
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/hours', {
            method: 'POST',
            headers: apiHeaders(),
            body: JSON.stringify({ horaires })
        });
        const data = await res.json();
        if (data.success) showSuccess('hoursSuccess');
        else alert(data.error || 'Erreur');
    } catch { alert('Erreur de connexion'); }
}

// ══════════════════════════════════
// MENU TAB
// ══════════════════════════════════

function openAddModal() {
    document.getElementById('newMenuName').value = '';
    document.getElementById('newMenuPrice').value = '';
    document.getElementById('newMenuDesc').value = '';
    document.getElementById('newMenuPhoto').value = '';
    document.getElementById('newMenuCat').value = 'Plats';
    document.getElementById('addModal').classList.add('open');
    setTimeout(() => document.getElementById('newMenuName')?.focus(), 100);
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('open');
}

document.getElementById('addModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddModal();
});

// Category filter
function filterMenuCat(cat, btn) {
    document.querySelectorAll('.oe-menu-cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.oe-menu-card').forEach(card => {
        card.style.display = (cat === 'all' || card.dataset.category === cat) ? '' : 'none';
    });
}

// New category prompt
document.getElementById('newMenuCat')?.addEventListener('change', function() {
    if (this.value === '__new__') {
        const newCat = prompt('Nom de la nouvelle categorie:');
        if (newCat && newCat.trim()) {
            const opt = document.createElement('option');
            opt.value = newCat.trim();
            opt.textContent = newCat.trim();
            opt.selected = true;
            this.insertBefore(opt, this.querySelector('[value="__new__"]'));
        } else {
            this.value = 'Plats';
        }
    }
});

async function addMenuItem() {
    const name = document.getElementById('newMenuName').value.trim();
    const price = parseFloat(document.getElementById('newMenuPrice').value) || null;
    const category = document.getElementById('newMenuCat').value;
    const description = document.getElementById('newMenuDesc').value.trim();
    const photoFile = document.getElementById('newMenuPhoto').files[0];

    if (!name) { alert('Nom requis'); return; }

    const fd = new FormData();
    fd.append('_token', getCsrfToken());
    fd.append('action', 'add');
    fd.append('name', name);
    fd.append('price', price || '');
    fd.append('category', category);
    fd.append('description', description);
    if (photoFile) fd.append('photo', photoFile);

    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/menu', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            reloadToTab('menu');
        } else {
            alert(data.error || 'Erreur');
        }
    } catch { alert('Erreur de connexion'); }
}

async function deleteMenuItem(itemId, btn) {
    if (!confirm('Supprimer ce plat ?')) return;
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/menu', {
            method: 'POST',
            headers: apiHeaders(),
            body: JSON.stringify({ action: 'delete', item_id: itemId })
        });
        const data = await res.json();
        if (data.success) btn.closest('.oe-menu-card').remove();
    } catch { alert('Erreur de connexion'); }
}

async function toggleMenuAvail(itemId, el) {
    const dot = el.querySelector('.oe-avail-dot');
    const label = el.querySelector('span:last-child');
    const card = el.closest('.oe-menu-card');
    const newVal = dot.classList.contains('on') ? 0 : 1;

    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/menu', {
            method: 'POST',
            headers: apiHeaders(),
            body: JSON.stringify({ action: 'toggle_available', item_id: itemId, is_available: newVal })
        });
        const data = await res.json();
        if (data.success) {
            dot.classList.toggle('on');
            card.classList.toggle('unavailable');
            label.textContent = newVal ? 'Disponible' : 'Indisponible';
            const badge = card.querySelector('.oe-menu-card-unavail');
            if (newVal && badge) badge.remove();
            if (!newVal && !badge) card.insertAdjacentHTML('afterbegin', '<span class="oe-menu-card-unavail">Indisponible</span>');
        }
    } catch { alert('Erreur de connexion'); }
}

// Edit modal
function openEditModal(id, name, price, desc, category) {
    document.getElementById('editItemId').value = id;
    document.getElementById('editItemName').value = name;
    document.getElementById('editItemPrice').value = price || '';
    document.getElementById('editItemDesc').value = desc || '';
    document.getElementById('editItemCat').value = category || 'Plats';
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}

document.getElementById('editModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

async function saveEditedItem() {
    const itemId = parseInt(document.getElementById('editItemId').value);
    const name = document.getElementById('editItemName').value.trim();
    const price = parseFloat(document.getElementById('editItemPrice').value) || null;
    const desc = document.getElementById('editItemDesc').value.trim();

    if (!name) { alert('Nom requis'); return; }

    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/menu', {
            method: 'POST',
            headers: apiHeaders(),
            body: JSON.stringify({ action: 'update', item_id: itemId, name, price, description: desc })
        });
        const data = await res.json();
        if (data.success) reloadToTab('menu');
        else alert(data.error || 'Erreur');
    } catch { alert('Erreur de connexion'); }
}

// ── Photo upload ──
async function uploadMenuPhoto(itemId, file) {
    const fd = new FormData();
    fd.append('_token', getCsrfToken());
    fd.append('action', 'upload_photo');
    fd.append('item_id', itemId);
    fd.append('photo', file);

    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/menu', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            reloadToTab('menu');
        } else {
            alert(data.error || 'Erreur photo');
        }
    } catch { alert('Erreur de connexion'); }
}

function triggerPhotoUpload(itemId) {
    const input = document.getElementById('hiddenPhotoInput');
    input.value = '';
    input.onchange = function() {
        if (this.files[0]) uploadMenuPhoto(itemId, this.files[0]);
    };
    input.click();
}

function changeMenuPhoto(itemId) {
    triggerPhotoUpload(itemId);
}

// ── Lightbox ──
let lightboxItemId = null;

function openLightbox(src, itemId) {
    lightboxItemId = itemId;
    document.getElementById('lightboxImg').src = src;
    document.getElementById('photoLightbox').classList.add('open');
}

function closeLightbox(e) {
    // Only close when clicking the backdrop or close button, not the image or action buttons
    if (e && (e.target.tagName === 'IMG' || e.target.closest('.oe-lightbox-actions'))) return;
    document.getElementById('photoLightbox').classList.remove('open');
    lightboxItemId = null;
}

function changeLightboxPhoto() {
    if (lightboxItemId) {
        closeLightbox({target:{}});
        triggerPhotoUpload(lightboxItemId);
    }
}

// ══════════════════════════════════
// RESERVATIONS TAB
// ══════════════════════════════════
async function respondReservation(resId, action) {
    const note = action === 'refuse' ? prompt('Raison du refus (optionnel):') : '';
    try {
        const res = await fetch('/api/reservations/' + resId + '/respond', {
            method: 'POST',
            headers: apiHeaders(),
            body: JSON.stringify({ action, note: note || '' })
        });
        const data = await res.json();
        if (data.success) {
            const el = document.getElementById('res-' + resId);
            el.style.opacity = '0.4';
            el.innerHTML = '<p style="text-align:center;width:100%;font-size:13px">' +
                (action === 'accept' ? 'Acceptee' : 'Refusee') + '</p>';
        }
    } catch { alert('Erreur de connexion'); }
}

// ══════════════════════════════════
// SETTINGS TAB
// ══════════════════════════════════
async function toggleReservations() {
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/toggle-reservations', {
            method: 'POST', headers: apiHeaders()
        });
        const data = await res.json();
        if (data.success) document.getElementById('toggleRes').classList.toggle('on');
    } catch { alert('Erreur de connexion'); }
}

async function toggleMenuSetting() {
    const el = document.getElementById('toggleMenu');
    const newVal = el.classList.contains('on') ? 0 : 1;
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/update', {
            method: 'POST', headers: apiHeaders(),
            body: JSON.stringify({ menu_enabled: newVal })
        });
        const data = await res.json();
        if (data.success) el.classList.toggle('on');
    } catch { alert('Erreur de connexion'); }
}

async function toggleOrders() {
    const el = document.getElementById('toggleOrders');
    const newVal = el.classList.contains('on') ? 0 : 1;
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/update', {
            method: 'POST', headers: apiHeaders(),
            body: JSON.stringify({ orders_enabled: newVal })
        });
        const data = await res.json();
        if (data.success) el.classList.toggle('on');
    } catch { alert('Erreur de connexion'); }
}

async function toggleDelivery() {
    const el = document.getElementById('toggleDelivery');
    const newVal = el.classList.contains('on') ? 0 : 1;
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/update', {
            method: 'POST', headers: apiHeaders(),
            body: JSON.stringify({ delivery_enabled: newVal })
        });
        const data = await res.json();
        if (data.success) {
            el.classList.toggle('on');
            document.getElementById('deliverySettings').style.display = newVal ? 'block' : 'none';
        }
    } catch { alert('Erreur de connexion'); }
}

async function saveDeliverySettings() {
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/update', {
            method: 'POST', headers: apiHeaders(),
            body: JSON.stringify({
                delivery_fee: document.getElementById('editDeliveryFee').value || null,
                delivery_min_order: document.getElementById('editDeliveryMin').value || null,
                delivery_max_km: document.getElementById('editDeliveryMaxKm').value || null,
            })
        });
        const data = await res.json();
        if (data.success) showSuccess('deliverySuccess');
        else alert(data.error || 'Erreur');
    } catch { alert('Erreur de connexion'); }
}

// ══════════════════════════════════
// QR CODE TAB
// ══════════════════════════════════
function generateQR() {
    const url = window.location.origin + '/restaurant/' + RESTO_ID + '/review';
    const container = document.getElementById('qrCodeCanvas');
    if (typeof QRCode !== 'undefined') {
        new QRCode(container, { text: url, width: 200, height: 200, colorDark: '#00635a', colorLight: '#ffffff' });
    } else {
        container.innerHTML = '<p style="font-size:12px;color:#6b7280;max-width:200px;word-break:break-all">'+url+'</p>';
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
        script.onload = function() {
            container.innerHTML = '';
            new QRCode(container, { text: url, width: 200, height: 200, colorDark: '#00635a', colorLight: '#ffffff' });
        };
        document.head.appendChild(script);
    }
}

function downloadQR() {
    const canvas = document.querySelector('#qrCodeCanvas canvas');
    if (canvas) { const a = document.createElement('a'); a.download = 'qr-avis-'+RESTO_ID+'.png'; a.href = canvas.toDataURL('image/png'); a.click(); }
}

function printQR() {
    const canvas = document.querySelector('#qrCodeCanvas canvas');
    if (canvas) {
        const w = window.open('','_blank');
        w.document.write('<html><body style="text-align:center;padding:40px"><h2><?= addslashes(htmlspecialchars($restaurant["nom"])) ?></h2><img src="'+canvas.toDataURL('image/png')+'"><p>Scannez pour laisser un avis</p></body></html>');
        w.document.close(); w.print();
    }
}

function generateOrderQR() {
    const url = window.location.origin + '/commander/<?= addslashes($restaurant['slug'] ?? '') ?>';
    const container = document.getElementById('qrCodeOrder');
    if (!container || container.querySelector('canvas') || container.querySelector('img')) return;
    if (typeof QRCode !== 'undefined') {
        new QRCode(container, { text: url, width: 200, height: 200, colorDark: '#00635a', colorLight: '#ffffff' });
    } else {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
        script.onload = function() { new QRCode(container, { text: url, width: 200, height: 200, colorDark: '#00635a', colorLight: '#ffffff' }); };
        document.head.appendChild(script);
    }
}

function downloadOrderQR() {
    const canvas = document.querySelector('#qrCodeOrder canvas');
    if (canvas) { const a = document.createElement('a'); a.download = 'qr-commande-'+RESTO_ID+'.png'; a.href = canvas.toDataURL('image/png'); a.click(); }
}

function printOrderQR() {
    const canvas = document.querySelector('#qrCodeOrder canvas');
    if (canvas) {
        const w = window.open('','_blank');
        w.document.write('<html><body style="text-align:center;padding:40px"><h2><?= addslashes(htmlspecialchars($restaurant["nom"])) ?></h2><img src="'+canvas.toDataURL('image/png')+'"><p style="font-size:18px;font-weight:bold;color:#00635a">Scannez pour commander</p></body></html>');
        w.document.close(); w.print();
    }
}

document.querySelector('[data-tab="qrcode"]')?.addEventListener('click', () => {
    if (!document.querySelector('#qrCodeCanvas canvas') && !document.querySelector('#qrCodeCanvas img')) generateQR();
    generateOrderQR();
});

// ══════════════════════════════════
// COMMANDES EN LIGNE - Owner Dashboard
// ══════════════════════════════════
<?php if ($restaurant['orders_enabled'] ?? 0): ?>

const STATUS_LABELS = {
    pending: 'En attente', confirmed: 'Confirmee', preparing: 'En preparation',
    ready: 'Prete', delivering: 'En livraison', delivered: 'Livree',
    cancelled: 'Annulee', refused: 'Refusee'
};
const STATUS_COLORS = {
    pending: '#f59e0b', confirmed: '#3b82f6', preparing: '#6366f1',
    ready: '#059669', delivering: '#0ea5e9', delivered: '#6b7280',
    cancelled: '#dc2626', refused: '#dc2626'
};

let ownerLastCount = 0;
let orderBellAudio = null;

function playOrderBell() {
    try {
        if (!orderBellAudio) orderBellAudio = new AudioContext();
        const osc = orderBellAudio.createOscillator();
        const gain = orderBellAudio.createGain();
        osc.connect(gain); gain.connect(orderBellAudio.destination);
        osc.frequency.value = 800; osc.type = 'sine';
        gain.gain.setValueAtTime(0.3, orderBellAudio.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, orderBellAudio.currentTime + 0.5);
        osc.start(); osc.stop(orderBellAudio.currentTime + 0.5);
    } catch {}
}

async function loadOrderStats() {
    try {
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/orders?status=delivered');
        const data = await res.json();
        if (!data.success) return;
        const all = data.orders || [];
        const totalRevenue = all.reduce((s, o) => s + parseFloat(o.grand_total || 0), 0);
        const today = new Date().toISOString().slice(0, 10);
        const todayCount = all.filter(o => (o.delivered_at || o.created_at || '').slice(0, 10) === today).length;
        document.getElementById('statTotal').textContent = all.length;
        document.getElementById('statRevenue').textContent = totalRevenue > 0 ? Math.round(totalRevenue).toLocaleString('fr-DZ') + ' DA' : '0 DA';
        document.getElementById('statAvg').textContent = all.length > 0 ? Math.round(totalRevenue / all.length).toLocaleString('fr-DZ') + ' DA' : '-';
        document.getElementById('statToday').textContent = todayCount;
    } catch {}
}

async function loadOwnerOrders() {
    try {
        loadOrderStats();
        const res = await fetch('/api/owner/restaurant/' + RESTO_ID + '/orders');
        const data = await res.json();
        if (!data.success) return;
        if (data.pending_count > ownerLastCount && ownerLastCount > 0) playOrderBell();
        ownerLastCount = data.pending_count;
        const badge = document.getElementById('ordersBadge');
        if (data.pending_count > 0) { badge.textContent = data.pending_count; badge.style.display = 'inline'; }
        else { badge.style.display = 'none'; }
        const pending = data.orders.filter(o => o.status === 'pending');
        const active = data.orders.filter(o => ['confirmed', 'preparing', 'ready', 'delivering'].includes(o.status));
        renderOwnerOrderList('pendingOrders', pending, true);
        renderOwnerOrderList('activeOrders', active, false);
        const filter = document.getElementById('historyFilter')?.value || 'delivered';
        const hRes = await fetch('/api/owner/restaurant/' + RESTO_ID + '/orders?status=' + filter);
        const hData = await hRes.json();
        if (hData.success) renderOwnerOrderList('historyOrders', hData.orders, false, true);
    } catch {}
}

function renderOwnerOrderList(containerId, orders, showRespond, isHistory = false) {
    const container = document.getElementById(containerId);
    if (!container) return;
    if (orders.length === 0) { container.innerHTML = '<p style="color:#9ca3af;text-align:center;padding:20px;font-size:13px">Aucune commande</p>'; return; }
    let html = '';
    orders.forEach(o => {
        const date = new Date(o.created_at);
        const dateStr = date.toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'}) + ' - ' + date.toLocaleDateString('fr-FR', {day:'numeric',month:'short'});
        const typeLabel = o.order_type === 'delivery' ? '<i class="fas fa-motorcycle"></i> Livraison' : '<i class="fas fa-store"></i> Retrait';
        const sc = STATUS_COLORS[o.status] || '#6b7280';
        html += '<div class="oe-res-item" style="display:block;margin-bottom:12px" id="order-' + o.id + '">';
        html += '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">';
        html += '<div><strong style="font-size:14px">#' + o.id + ' - ' + esc(o.client_name) + '</strong><span style="font-size:12px;color:#6b7280;margin-left:8px">' + dateStr + '</span></div>';
        html += '<span style="background:' + sc + '20;color:' + sc + ';padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700">' + STATUS_LABELS[o.status] + '</span>';
        html += '</div>';
        html += '<div style="font-size:12px;color:#6b7280;margin-bottom:6px">' + typeLabel + ' &middot; <i class="fas fa-phone"></i> ' + esc(o.client_phone) + '</div>';
        if (o.delivery_address) html += '<div style="font-size:12px;color:#6b7280;margin-bottom:6px"><i class="fas fa-map-marker-alt"></i> ' + esc(o.delivery_address) + ', ' + esc(o.delivery_city||'') + '</div>';
        html += '<div style="margin:8px 0;padding:8px;background:#fff;border-radius:6px">';
        (o.items || []).forEach(item => {
            html += '<div style="display:flex;justify-content:space-between;font-size:13px;padding:2px 0"><span>' + esc(item.item_name) + ' x' + item.quantity + '</span><span style="font-weight:600">' + (parseFloat(item.item_price)*parseInt(item.quantity)).toLocaleString('fr-DZ') + ' DA</span></div>';
            if (item.special_requests) html += '<div style="font-size:11px;color:#6b7280;font-style:italic;padding-left:8px">"' + esc(item.special_requests) + '"</div>';
        });
        html += '</div>';
        html += '<div style="font-size:14px;font-weight:700;color:#00635a">Total: ' + parseFloat(o.grand_total).toLocaleString('fr-DZ') + ' DA</div>';
        if (o.special_instructions) html += '<div style="font-size:12px;color:#6b7280;margin-top:4px"><i class="fas fa-sticky-note"></i> ' + esc(o.special_instructions) + '</div>';
        if (showRespond && o.status === 'pending') {
            html += '<div style="display:flex;gap:8px;margin-top:10px;align-items:center">';
            html += '<input type="number" id="est-' + o.id + '" value="30" min="5" max="180" style="width:60px;padding:6px;border:1px solid #d1d5db;border-radius:6px;font-size:12px"><span style="font-size:11px;color:#6b7280">min</span>';
            html += '<button class="oe-res-accept" onclick="respondOrder(' + o.id + ',\'confirm\')"><i class="fas fa-check"></i> Accepter</button>';
            html += '<button class="oe-res-refuse" onclick="respondOrder(' + o.id + ',\'refuse\')"><i class="fas fa-times"></i> Refuser</button></div>';
        }
        if (!showRespond && !isHistory) {
            const nextMap = {confirmed:'preparing',preparing:'ready',ready:o.order_type==='delivery'?'delivering':'delivered',delivering:'delivered'};
            const next = nextMap[o.status];
            if (next) html += '<div style="margin-top:10px"><button class="oe-res-accept" onclick="advanceOrder(' + o.id + ',\'' + next + '\')"><i class="fas fa-arrow-right"></i> ' + STATUS_LABELS[next] + '</button></div>';
        }
        html += '</div>';
    });
    container.innerHTML = html;
}

async function respondOrder(orderId, action) {
    const est = parseInt(document.getElementById('est-'+orderId)?.value) || 30;
    let reason = '';
    if (action === 'refuse') reason = prompt('Raison du refus (optionnel):') || '';
    try {
        const res = await fetch('/api/owner/orders/'+orderId+'/respond', { method:'POST', headers: apiHeaders(), body: JSON.stringify({action, estimated_minutes:est, reason}) });
        const data = await res.json();
        if (data.success) loadOwnerOrders(); else alert(data.error||'Erreur');
    } catch { alert('Erreur de connexion'); }
}

async function advanceOrder(orderId, newStatus) {
    try {
        const res = await fetch('/api/owner/orders/'+orderId+'/status', { method:'POST', headers: apiHeaders(), body: JSON.stringify({status:newStatus}) });
        const data = await res.json();
        if (data.success) loadOwnerOrders(); else alert(data.error||'Erreur');
    } catch { alert('Erreur de connexion'); }
}

function esc(str) { if (!str) return ''; const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }

document.querySelector('[data-tab="orders"]')?.addEventListener('click', () => { loadOwnerOrders(); });
setInterval(() => { if (document.getElementById('tab-orders')?.classList.contains('active')) loadOwnerOrders(); }, 30000);

<?php endif; ?>
</script>
