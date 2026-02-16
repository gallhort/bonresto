<style>
    .ord-page { max-width: 800px; margin: 0 auto; padding: 20px; padding-bottom: 100px; }

    /* Header */
    .ord-header { background: linear-gradient(135deg, #00635a, #004d40); color: #fff; border-radius: 16px; padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden; }
    .ord-header-photo { position: absolute; top: 0; right: 0; bottom: 0; width: 160px; opacity: 0.2; object-fit: cover; }
    .ord-header h1 { font-size: 22px; margin: 0 0 8px; position: relative; z-index: 1; }
    .ord-header-info { font-size: 13px; opacity: 0.9; position: relative; z-index: 1; }
    .ord-header-info i { width: 16px; text-align: center; margin-right: 4px; }
    .ord-header-badges { display: flex; gap: 8px; margin-top: 12px; position: relative; z-index: 1; }
    .ord-badge { background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }

    /* Unavailable message */
    .ord-unavail { text-align: center; padding: 60px 20px; }
    .ord-unavail i { font-size: 48px; color: #d1d5db; margin-bottom: 16px; }
    .ord-unavail h2 { font-size: 20px; color: #374151; margin: 0 0 8px; }
    .ord-unavail p { color: #6b7280; font-size: 14px; }

    /* Category */
    .ord-cat { margin-bottom: 24px; }
    .ord-cat-title { font-size: 16px; font-weight: 700; color: #374151; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 2px solid #00635a; display: inline-block; }

    /* Menu item */
    .ord-item { display: flex; align-items: center; padding: 12px 16px; background: #fff; border-radius: 10px; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: 0.2s; gap: 12px; }
    .ord-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .ord-item.unavailable { opacity: 0.5; pointer-events: none; }
    .ord-item-photo { width: 72px; height: 72px; border-radius: 10px; object-fit: contain; background: #f9fafb; flex-shrink: 0; }
    .ord-item-info { flex: 1; min-width: 0; }
    .ord-item-name { font-size: 14px; font-weight: 600; color: #1f2937; }
    .ord-item-desc { font-size: 12px; color: #6b7280; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ord-item-unavail { font-size: 11px; color: #ef4444; font-weight: 600; }
    .ord-item-right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }
    .ord-item-price { font-size: 15px; font-weight: 700; color: #00635a; white-space: nowrap; }
    .ord-item-add { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #00635a; background: #fff; color: #00635a; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; flex-shrink: 0; }
    .ord-item-add:hover { background: #00635a; color: #fff; }

    /* Floating cart bar */
    .ord-cart-bar { position: fixed; bottom: 0; left: 0; right: 0; background: #00635a; color: #fff; padding: 14px 20px; display: none; z-index: 1000; box-shadow: 0 -4px 20px rgba(0,0,0,0.15); }
    .ord-cart-bar.visible { display: flex; align-items: center; justify-content: space-between; }
    .ord-cart-bar-info { font-size: 14px; }
    .ord-cart-bar-info strong { font-size: 16px; }
    .ord-cart-bar-btn { background: #fff; color: #00635a; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }

    /* Cart detail panel */
    .ord-cart-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1001; }
    .ord-cart-overlay.open { display: block; }
    .ord-cart-panel { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-radius: 20px 20px 0 0; max-height: 85vh; overflow-y: auto; z-index: 1002; padding: 24px; display: none; }
    .ord-cart-panel.open { display: block; }
    .ord-cart-panel h2 { font-size: 18px; margin: 0 0 16px; display: flex; align-items: center; justify-content: space-between; }
    .ord-cart-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280; padding: 4px; }

    /* Cart item */
    .ord-ci { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f3f4f6; }
    .ord-ci:last-child { border-bottom: none; }
    .ord-ci-info { flex: 1; }
    .ord-ci-name { font-size: 14px; font-weight: 600; }
    .ord-ci-price { font-size: 13px; color: #00635a; font-weight: 600; }
    .ord-ci-notes { margin-top: 4px; }
    .ord-ci-notes textarea { width: 100%; border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px 8px; font-size: 12px; resize: none; height: 32px; box-sizing: border-box; }
    .ord-ci-qty { display: flex; align-items: center; gap: 8px; }
    .ord-ci-qty button { width: 28px; height: 28px; border-radius: 50%; border: 1px solid #d1d5db; background: #f9fafb; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .ord-ci-qty span { font-weight: 700; min-width: 20px; text-align: center; }
    .ord-ci-del { color: #ef4444; background: none; border: none; font-size: 16px; cursor: pointer; padding: 4px; }

    /* Cart totals */
    .ord-totals { background: #f9fafb; border-radius: 10px; padding: 16px; margin-top: 16px; }
    .ord-total-line { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px; }
    .ord-total-line.grand { font-size: 16px; font-weight: 700; border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 8px; }

    /* Checkout form */
    .ord-checkout { margin-top: 20px; }
    .ord-checkout h3 { font-size: 16px; margin: 0 0 14px; }
    .ord-field { margin-bottom: 12px; }
    .ord-field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #374151; }
    .ord-field input, .ord-field textarea, .ord-field select { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; box-sizing: border-box; }
    .ord-field textarea { height: 60px; resize: vertical; }
    .ord-radio-group { display: flex; gap: 10px; }
    .ord-radio { flex: 1; }
    .ord-radio input[type="radio"] { display: none; }
    .ord-radio label { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; font-size: 14px; font-weight: 600; text-align: center; transition: 0.2s; }
    .ord-radio input:checked + label { border-color: #00635a; background: #ecfdf5; color: #00635a; }
    .ord-submit { width: 100%; padding: 14px; background: #00635a; color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 16px; }
    .ord-submit:hover { background: #004d40; }
    .ord-submit:disabled { background: #9ca3af; cursor: not-allowed; }

    /* Confirmation */
    .ord-confirm { text-align: center; padding: 40px 20px; }
    .ord-confirm i { font-size: 60px; color: #059669; margin-bottom: 16px; }
    .ord-confirm h2 { font-size: 20px; color: #065f46; margin: 0 0 8px; }
    .ord-confirm p { color: #6b7280; font-size: 14px; }
    .ord-confirm-btn { display: inline-block; margin-top: 20px; padding: 10px 24px; background: #00635a; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; }

    /* Lightbox */
    .ord-lightbox { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 3000; align-items: center; justify-content: center; cursor: pointer; }
    .ord-lightbox.open { display: flex; }
    .ord-lightbox img { max-width: 90%; max-height: 85vh; border-radius: 12px; object-fit: contain; box-shadow: 0 8px 40px rgba(0,0,0,0.5); }
    .ord-lightbox-close { position: absolute; top: 20px; right: 24px; color: #fff; font-size: 32px; cursor: pointer; background: rgba(0,0,0,0.4); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; }

    @media (max-width: 600px) {
        .ord-page { padding: 12px; padding-bottom: 90px; }
        .ord-header h1 { font-size: 18px; }
        .ord-header-photo { width: 100px; }
        .ord-item { padding: 10px 12px; }
    }
</style>

<div class="ord-page">
    <!-- Header -->
    <div class="ord-header">
        <?php if ($mainPhoto): ?>
            <img class="ord-header-photo" src="/uploads/restaurants/<?= htmlspecialchars($mainPhoto) ?>" alt="">
        <?php endif; ?>
        <h1><?= htmlspecialchars($restaurant['nom']) ?></h1>
        <div class="ord-header-info">
            <?php if ($restaurant['adresse']): ?>
                <div><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($restaurant['adresse']) ?>, <?= htmlspecialchars($restaurant['ville'] ?? '') ?></div>
            <?php endif; ?>
            <?php if ($restaurant['phone']): ?>
                <div style="margin-top:4px"><i class="fas fa-phone"></i> <?= htmlspecialchars($restaurant['phone']) ?></div>
            <?php endif; ?>
        </div>
        <div class="ord-header-badges">
            <?php if ($restaurant['delivery_enabled']): ?>
                <span class="ord-badge"><i class="fas fa-motorcycle"></i> Livraison</span>
            <?php endif; ?>
            <span class="ord-badge"><i class="fas fa-shopping-bag"></i> Retrait</span>
        </div>
    </div>

    <?php if (!$restaurant['owner_id']): ?>
        <div class="ord-unavail">
            <i class="fas fa-store-slash"></i>
            <h2>Restaurant non configure</h2>
            <p>Ce restaurant n'a pas encore active la commande en ligne.</p>
            <a href="/restaurant/<?= htmlspecialchars($restaurant['slug'] ?? $restaurant['id']) ?>" style="color:#00635a;font-weight:600;margin-top:16px;display:inline-block">Voir la page du restaurant</a>
        </div>
    <?php elseif (!(int)$restaurant['orders_enabled']): ?>
        <div class="ord-unavail">
            <i class="fas fa-pause-circle"></i>
            <h2>Commandes indisponibles</h2>
            <p>Ce restaurant n'accepte pas de commandes en ligne pour le moment.</p>
            <a href="/restaurant/<?= htmlspecialchars($restaurant['slug'] ?? $restaurant['id']) ?>" style="color:#00635a;font-weight:600;margin-top:16px;display:inline-block">Voir la page du restaurant</a>
        </div>
    <?php elseif (empty($menuByCategory)): ?>
        <div class="ord-unavail">
            <i class="fas fa-utensils"></i>
            <h2>Menu en cours de preparation</h2>
            <p>Le restaurant n'a pas encore publie sa carte. Revenez bientot !</p>
        </div>
    <?php else: ?>
        <!-- Menu by category -->
        <div id="menuSection">
            <?php foreach ($menuByCategory as $category => $items): ?>
                <div class="ord-cat">
                    <h3 class="ord-cat-title"><?= htmlspecialchars($category) ?></h3>
                    <?php foreach ($items as $item): ?>
                        <div class="ord-item <?= !(int)$item['is_available'] ? 'unavailable' : '' ?>"
                             data-id="<?= (int)$item['id'] ?>"
                             data-name="<?= htmlspecialchars($item['name']) ?>"
                             data-price="<?= (float)$item['price'] ?>"
                             data-available="<?= (int)$item['is_available'] ?>">
                            <?php if (!empty($item['photo_path'])): ?>
                                <img class="ord-item-photo" src="/uploads/menu/<?= htmlspecialchars($item['photo_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onclick="openOrdLightbox(this.src)" style="cursor:pointer">
                            <?php endif; ?>
                            <div class="ord-item-info">
                                <div class="ord-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <?php if ($item['description']): ?>
                                    <div class="ord-item-desc"><?= htmlspecialchars($item['description']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($item['allergens'])): ?>
                                    <div class="ord-item-allergens" style="display:flex;flex-wrap:wrap;gap:3px;margin-top:4px">
                                        <?php
                                        $algLabels = ['gluten'=>'Gluten','dairy'=>'Lait','eggs'=>'Œufs','fish'=>'Poisson','shellfish'=>'Crustacés','nuts'=>'Noix','peanuts'=>'Arachides','soy'=>'Soja','celery'=>'Céleri','mustard'=>'Moutarde','sesame'=>'Sésame','sulfites'=>'Sulfites','lupin'=>'Lupin','mollusks'=>'Mollusques'];
                                        foreach ($item['allergens'] as $alg): $lbl = $algLabels[$alg] ?? $alg; ?>
                                        <span title="<?= htmlspecialchars($lbl) ?>" style="font-size:10px;padding:1px 5px;background:#fef2f2;color:#dc2626;border-radius:3px;border:1px solid #fecaca"><?= htmlspecialchars($lbl) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!(int)$item['is_available']): ?>
                                    <div class="ord-item-unavail">Indisponible</div>
                                <?php endif; ?>
                            </div>
                            <div class="ord-item-right">
                                <?php if ($item['price']): ?>
                                    <span class="ord-item-price"><?= number_format((float)$item['price'], 0) ?> DA</span>
                                <?php endif; ?>
                                <?php if ((int)$item['is_available']): ?>
                                    <button class="ord-item-add" aria-label="Ajouter <?= htmlspecialchars($item['name']) ?>" onclick="addToCart(this.closest('.ord-item'))">+</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Floating cart bar -->
        <div class="ord-cart-bar" id="cartBar">
            <div class="ord-cart-bar-info">
                <span id="cartCount">0</span> article(s) &middot; <strong id="cartTotal">0 DA</strong>
            </div>
            <button class="ord-cart-bar-btn" onclick="openCart()">Voir le panier</button>
        </div>

        <!-- Cart overlay + panel -->
        <div class="ord-cart-overlay" id="cartOverlay" onclick="closeCart()"></div>
        <div class="ord-cart-panel" id="cartPanel">
            <h2>
                <span><i class="fas fa-shopping-cart" style="color:#00635a;margin-right:8px"></i> Votre panier</span>
                <button class="ord-cart-close" onclick="closeCart()" aria-label="Fermer">&times;</button>
            </h2>

            <div id="cartItems"></div>

            <div class="ord-totals" id="cartTotals" style="display:none">
                <div class="ord-total-line">
                    <span>Sous-total</span>
                    <span id="subtotalDisplay">0 DA</span>
                </div>
                <div class="ord-total-line" id="deliveryFeeLine" style="display:none">
                    <span>Frais de livraison</span>
                    <span id="deliveryFeeDisplay">0 DA</span>
                </div>
                <div class="ord-total-line grand">
                    <span>Total</span>
                    <span id="grandTotalDisplay">0 DA</span>
                </div>
            </div>

            <!-- Checkout form -->
            <div class="ord-checkout" id="checkoutForm" style="display:none">
                <h3>Finaliser la commande</h3>

                <div class="ord-field">
                    <label>Mode de recuperation</label>
                    <div class="ord-radio-group">
                        <div class="ord-radio">
                            <input type="radio" name="orderType" id="typePickup" value="pickup" checked>
                            <label for="typePickup"><i class="fas fa-store"></i> Retrait sur place</label>
                        </div>
                        <?php if ((int)$restaurant['delivery_enabled']): ?>
                        <div class="ord-radio">
                            <input type="radio" name="orderType" id="typeDelivery" value="delivery">
                            <label for="typeDelivery"><i class="fas fa-motorcycle"></i> Livraison</label>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="ord-field">
                    <label for="clientPhone">Telephone *</label>
                    <input type="tel" id="clientPhone" placeholder="0555 123 456" value="<?= htmlspecialchars($_SESSION['user']['telephone'] ?? '') ?>">
                </div>

                <div id="deliveryFields" style="display:none">
                    <div class="ord-field">
                        <label for="deliveryAddress">Adresse de livraison *</label>
                        <input type="text" id="deliveryAddress" placeholder="123 Rue Example, Quartier">
                    </div>
                    <div class="ord-field">
                        <label for="deliveryCity">Ville *</label>
                        <input type="text" id="deliveryCity" placeholder="<?= htmlspecialchars($restaurant['ville'] ?? '') ?>" value="<?= htmlspecialchars($restaurant['ville'] ?? '') ?>">
                    </div>
                    <?php if ($restaurant['delivery_min_order']): ?>
                        <p style="font-size:12px;color:#f59e0b;margin:0 0 8px"><i class="fas fa-info-circle"></i> Minimum de commande pour la livraison : <?= number_format((float)$restaurant['delivery_min_order'], 0) ?> DA</p>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['delivery_max_km'])): ?>
                        <p style="font-size:12px;color:#6b7280;margin:0 0 12px"><i class="fas fa-map-marker-alt"></i> Zone de livraison : <?= number_format((float)$restaurant['delivery_max_km'], 1) ?> km max</p>
                    <?php endif; ?>
                </div>

                <div class="ord-field">
                    <label for="specialInstructions">Instructions speciales (optionnel)</label>
                    <textarea id="specialInstructions" placeholder="Ex: Sonnez au 2eme etage, allergie aux arachides..."></textarea>
                </div>

                <button class="ord-submit" id="submitOrder" onclick="submitOrder()">
                    <i class="fas fa-paper-plane"></i> Envoyer la commande
                </button>
                <p style="text-align:center;font-size:12px;color:#6b7280;margin-top:8px">Paiement a la reception (especes ou carte)</p>
            </div>
        </div>

        <!-- Success confirmation (hidden by default) -->
        <div class="ord-confirm" id="orderSuccess" style="display:none">
            <i class="fas fa-check-circle"></i>
            <h2>Commande envoyee !</h2>
            <p>Le restaurant va confirmer votre commande sous quelques minutes.<br>Vous recevrez une notification.</p>
            <a href="/mes-commandes" class="ord-confirm-btn"><i class="fas fa-list"></i> Voir mes commandes</a>
        </div>
    <?php endif; ?>

    <!-- Photo Lightbox -->
    <div class="ord-lightbox" id="ordLightbox" onclick="closeOrdLightbox(event)">
        <button class="ord-lightbox-close" onclick="closeOrdLightbox(event)">&times;</button>
        <img id="ordLightboxImg" src="" alt="Photo du plat">
    </div>
</div>

<?php if (!empty($menuByCategory) && (int)($restaurant['orders_enabled'] ?? 0) && $restaurant['owner_id']): ?>
<script>
const SLUG = '<?= addslashes($restaurant['slug']) ?>';
const CART_KEY = 'cart_' + SLUG;
const DELIVERY_FEE = <?= (float)($restaurant['delivery_fee'] ?? 0) ?>;
const DELIVERY_MIN = <?= (float)($restaurant['delivery_min_order'] ?? 0) ?>;
const DELIVERY_MAX_KM = <?= (float)($restaurant['delivery_max_km'] ?? 0) ?>;
const DELIVERY_ENABLED = <?= (int)$restaurant['delivery_enabled'] ?>;
const IS_AUTHED = <?= isset($_SESSION['user']['id']) ? 'true' : 'false' ?>;
let clientLat = null, clientLng = null;

// ── Cart State ──
function getCart() {
    try {
        const data = JSON.parse(localStorage.getItem(CART_KEY));
        return (data && Array.isArray(data.items)) ? data : { items: [], updated_at: null };
    } catch { return { items: [], updated_at: null }; }
}

function saveCart(cart) {
    cart.updated_at = new Date().toISOString();
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    renderCartBar();
}

function addToCart(el) {
    const id = parseInt(el.dataset.id);
    const name = el.dataset.name;
    const price = parseFloat(el.dataset.price);

    const cart = getCart();
    const existing = cart.items.find(i => i.menu_item_id === id);

    if (existing) {
        existing.quantity++;
    } else {
        cart.items.push({ menu_item_id: id, name, price, quantity: 1, notes: '' });
    }
    saveCart(cart);

    // Visual feedback
    el.style.transform = 'scale(0.97)';
    setTimeout(() => el.style.transform = '', 150);
}

function updateQuantity(menuItemId, delta) {
    const cart = getCart();
    const item = cart.items.find(i => i.menu_item_id === menuItemId);
    if (!item) return;

    item.quantity += delta;
    if (item.quantity <= 0) {
        cart.items = cart.items.filter(i => i.menu_item_id !== menuItemId);
    }
    saveCart(cart);
    renderCartDetail();
}

function removeFromCart(menuItemId) {
    const cart = getCart();
    cart.items = cart.items.filter(i => i.menu_item_id !== menuItemId);
    saveCart(cart);
    renderCartDetail();
}

function updateNotes(menuItemId, notes) {
    const cart = getCart();
    const item = cart.items.find(i => i.menu_item_id === menuItemId);
    if (item) item.notes = notes;
    saveCart(cart);
}

// ── Render ──
function renderCartBar() {
    const cart = getCart();
    const bar = document.getElementById('cartBar');
    const count = cart.items.reduce((s, i) => s + i.quantity, 0);
    const total = cart.items.reduce((s, i) => s + i.price * i.quantity, 0);

    if (count > 0) {
        bar.classList.add('visible');
        document.getElementById('cartCount').textContent = count;
        document.getElementById('cartTotal').textContent = total.toLocaleString('fr-DZ') + ' DA';
    } else {
        bar.classList.remove('visible');
    }
}

function renderCartDetail() {
    const cart = getCart();
    const container = document.getElementById('cartItems');
    const totals = document.getElementById('cartTotals');
    const checkout = document.getElementById('checkoutForm');

    if (cart.items.length === 0) {
        container.innerHTML = '<p style="text-align:center;color:#9ca3af;padding:30px;font-size:14px">Votre panier est vide</p>';
        totals.style.display = 'none';
        checkout.style.display = 'none';
        return;
    }

    let html = '';
    let subtotal = 0;

    cart.items.forEach(item => {
        const lineTotal = item.price * item.quantity;
        subtotal += lineTotal;

        html += `
        <div class="ord-ci">
            <div class="ord-ci-info">
                <div class="ord-ci-name">${escHtml(item.name)}</div>
                <div class="ord-ci-price">${item.price.toLocaleString('fr-DZ')} DA x ${item.quantity} = ${lineTotal.toLocaleString('fr-DZ')} DA</div>
                <div class="ord-ci-notes">
                    <textarea placeholder="Note pour ce plat..." oninput="updateNotes(${item.menu_item_id}, this.value)">${escHtml(item.notes || '')}</textarea>
                </div>
            </div>
            <div class="ord-ci-qty">
                <button onclick="updateQuantity(${item.menu_item_id}, -1)">-</button>
                <span>${item.quantity}</span>
                <button onclick="updateQuantity(${item.menu_item_id}, 1)">+</button>
            </div>
            <button class="ord-ci-del" onclick="removeFromCart(${item.menu_item_id})" aria-label="Supprimer"><i class="fas fa-trash"></i></button>
        </div>`;
    });

    container.innerHTML = html;

    // Totals
    const isDelivery = document.getElementById('typeDelivery')?.checked || false;
    const fee = isDelivery ? DELIVERY_FEE : 0;
    const grandTotal = subtotal + fee;

    document.getElementById('subtotalDisplay').textContent = subtotal.toLocaleString('fr-DZ') + ' DA';
    document.getElementById('deliveryFeeDisplay').textContent = fee.toLocaleString('fr-DZ') + ' DA';
    document.getElementById('deliveryFeeLine').style.display = isDelivery ? 'flex' : 'none';
    document.getElementById('grandTotalDisplay').textContent = grandTotal.toLocaleString('fr-DZ') + ' DA';

    totals.style.display = 'block';
    checkout.style.display = IS_AUTHED ? 'block' : 'none';

    if (!IS_AUTHED) {
        container.insertAdjacentHTML('afterend',
            '<p style="text-align:center;background:#fef3c7;padding:12px;border-radius:8px;font-size:13px;margin-top:12px">' +
            '<i class="fas fa-lock"></i> <a href="/login" style="color:#00635a;font-weight:600">Connectez-vous</a> pour passer commande</p>');
    }
}

function openCart() {
    document.getElementById('cartOverlay').classList.add('open');
    document.getElementById('cartPanel').classList.add('open');
    document.body.style.overflow = 'hidden';
    renderCartDetail();
}

function closeCart() {
    document.getElementById('cartOverlay').classList.remove('open');
    document.getElementById('cartPanel').classList.remove('open');
    document.body.style.overflow = '';
}

// ── Order type toggle ──
document.querySelectorAll('input[name="orderType"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const isDelivery = radio.value === 'delivery' && radio.checked;
        document.getElementById('deliveryFields').style.display = isDelivery ? 'block' : 'none';
        renderCartDetail();
        // Request GPS for distance check
        if (isDelivery && DELIVERY_MAX_KM > 0 && !clientLat && navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                pos => { clientLat = pos.coords.latitude; clientLng = pos.coords.longitude; },
                () => {},
                { enableHighAccuracy: false, timeout: 5000 }
            );
        }
    });
});

// ── Submit order ──
async function submitOrder() {
    const btn = document.getElementById('submitOrder');
    const cart = getCart();

    if (cart.items.length === 0) { alert('Le panier est vide'); return; }

    const orderType = document.querySelector('input[name="orderType"]:checked')?.value || 'pickup';
    const phone = document.getElementById('clientPhone').value.trim();
    const address = document.getElementById('deliveryAddress')?.value.trim() || '';
    const city = document.getElementById('deliveryCity')?.value.trim() || '';
    const instructions = document.getElementById('specialInstructions').value.trim();

    if (!/^[0-9+\s\-]{8,20}$/.test(phone)) {
        alert('Numero de telephone invalide');
        return;
    }

    if (orderType === 'delivery' && address.length < 5) {
        alert('Adresse de livraison requise');
        return;
    }

    // Check delivery minimum
    if (orderType === 'delivery' && DELIVERY_MIN > 0) {
        const subtotal = cart.items.reduce((s, i) => s + i.price * i.quantity, 0);
        if (subtotal < DELIVERY_MIN) {
            alert('Minimum de commande pour la livraison : ' + DELIVERY_MIN + ' DA');
            return;
        }
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';

    try {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const headers = { 'Content-Type': 'application/json' };
        if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content;

        const res = await fetch('/api/orders', {
            method: 'POST',
            headers,
            body: JSON.stringify({
                slug: SLUG,
                items: cart.items,
                order_type: orderType,
                client_phone: phone,
                delivery_address: address,
                delivery_city: city,
                special_instructions: instructions,
                client_lat: clientLat,
                client_lng: clientLng,
            })
        });

        const data = await res.json();

        if (data.success) {
            // Clear cart
            localStorage.removeItem(CART_KEY);
            renderCartBar();
            closeCart();

            // Show success
            document.getElementById('menuSection').style.display = 'none';
            document.getElementById('cartBar').style.display = 'none';
            document.getElementById('orderSuccess').style.display = 'block';
        } else {
            alert(data.error || 'Erreur lors de la commande');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer la commande';
        }
    } catch (err) {
        alert('Erreur de connexion. Verifiez votre connexion internet.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer la commande';
    }
}

function escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ── Photo Lightbox ──
function openOrdLightbox(src) {
    document.getElementById('ordLightboxImg').src = src;
    document.getElementById('ordLightbox').classList.add('open');
}

function closeOrdLightbox(e) {
    if (e && e.target.tagName === 'IMG') return;
    document.getElementById('ordLightbox').classList.remove('open');
}

// Init
renderCartBar();
</script>
<?php endif; ?>
