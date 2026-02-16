<style>
    .mo-page { max-width: 800px; margin: 0 auto; padding: 24px 20px; }
    .mo-page h1 { font-size: 22px; margin: 0 0 24px; display: flex; align-items: center; gap: 10px; }
    .mo-page h1 i { color: #00635a; }

    .mo-section-title { font-size: 14px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin: 24px 0 12px; }

    .mo-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); padding: 16px; margin-bottom: 12px; cursor: pointer; transition: 0.2s; }
    .mo-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .mo-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .mo-card-resto { display: flex; align-items: center; gap: 10px; }
    .mo-card-photo { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; background: #f3f4f6; }
    .mo-card-name { font-size: 15px; font-weight: 600; }
    .mo-card-date { font-size: 12px; color: #6b7280; }

    .mo-badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .mo-badge-pending { background: #fef3c7; color: #b45309; }
    .mo-badge-confirmed { background: #dbeafe; color: #1d4ed8; }
    .mo-badge-preparing { background: #e0e7ff; color: #4338ca; }
    .mo-badge-ready { background: #d1fae5; color: #065f46; }
    .mo-badge-delivering { background: #cffafe; color: #0e7490; }
    .mo-badge-delivered { background: #f3f4f6; color: #374151; }
    .mo-badge-cancelled { background: #fee2e2; color: #dc2626; }
    .mo-badge-refused { background: #fee2e2; color: #dc2626; }

    .mo-card-items { font-size: 13px; color: #6b7280; margin-bottom: 6px; }
    .mo-card-footer { display: flex; justify-content: space-between; align-items: center; }
    .mo-card-total { font-size: 15px; font-weight: 700; color: #00635a; }
    .mo-card-type { font-size: 12px; color: #6b7280; }

    .mo-est { font-size: 12px; color: #059669; font-weight: 600; margin-top: 4px; }
    .mo-cancel-btn { background: #fee2e2; color: #dc2626; border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; margin-top: 8px; }

    /* Detail modal */
    .mo-detail-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1001; }
    .mo-detail-overlay.open { display: block; }
    .mo-detail { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-radius: 20px 20px 0 0; max-height: 80vh; overflow-y: auto; z-index: 1002; padding: 24px; }
    .mo-detail.open { display: block; }
    .mo-detail h2 { font-size: 18px; margin: 0 0 16px; display: flex; justify-content: space-between; align-items: center; }
    .mo-detail-close { background: none; border: none; font-size: 24px; cursor: pointer; color: #6b7280; }
    .mo-detail-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
    .mo-detail-item:last-child { border-bottom: none; }
    .mo-detail-notes { font-size: 12px; color: #6b7280; font-style: italic; }

    .mo-empty { text-align: center; padding: 60px 20px; }
    .mo-empty i { font-size: 48px; color: #d1d5db; margin-bottom: 16px; }
    .mo-empty h2 { font-size: 18px; color: #374151; margin: 0 0 8px; }
    .mo-empty p { color: #6b7280; font-size: 14px; }
    .mo-empty a { color: #00635a; font-weight: 600; }

    .mo-loading { text-align: center; padding: 40px; color: #6b7280; }
    .mo-more-btn { display: block; width: 100%; padding: 12px; background: #f3f4f6; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; color: #374151; margin-top: 12px; }
</style>

<div class="mo-page">
    <h1><i class="fas fa-receipt"></i> Mes commandes</h1>

    <div id="activeOrders"></div>
    <div id="pastOrders"></div>
    <div id="ordersLoading" class="mo-loading"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>
</div>

<!-- Detail modal -->
<div class="mo-detail-overlay" id="detailOverlay" onclick="closeDetail()"></div>
<div class="mo-detail" id="detailPanel">
    <h2>
        <span id="detailTitle">Detail commande</span>
        <button class="mo-detail-close" onclick="closeDetail()" aria-label="Fermer">&times;</button>
    </h2>
    <div id="detailContent"></div>
</div>

<script>
const STATUS_LABELS = {
    pending: 'En attente', confirmed: 'Confirmee', preparing: 'En preparation',
    ready: 'Prete', delivering: 'En livraison', delivered: 'Livree',
    cancelled: 'Annulee', refused: 'Refusee'
};
const ACTIVE_STATUSES = ['pending', 'confirmed', 'preparing', 'ready', 'delivering'];
let allOrders = [];
let currentOffset = 0;
let hasMore = false;
let pollTimer = null;

async function loadOrders(append = false) {
    try {
        const res = await fetch('/api/my-orders?limit=20&offset=' + currentOffset);
        const data = await res.json();
        document.getElementById('ordersLoading').style.display = 'none';

        if (!data.success) return;

        if (append) {
            allOrders = allOrders.concat(data.orders);
        } else {
            allOrders = data.orders;
        }
        hasMore = data.has_more;
        renderOrders();
        startPolling();
    } catch (err) {
        document.getElementById('ordersLoading').innerHTML = '<p style="color:#ef4444">Erreur de chargement</p>';
    }
}

function renderOrders() {
    const active = allOrders.filter(o => ACTIVE_STATUSES.includes(o.status));
    const past = allOrders.filter(o => !ACTIVE_STATUSES.includes(o.status));

    const activeEl = document.getElementById('activeOrders');
    const pastEl = document.getElementById('pastOrders');

    if (allOrders.length === 0) {
        activeEl.innerHTML = `
            <div class="mo-empty">
                <i class="fas fa-shopping-bag"></i>
                <h2>Aucune commande</h2>
                <p>Vous n'avez pas encore passe de commande.<br><a href="/restaurants">Decouvrir les restaurants</a></p>
            </div>`;
        pastEl.innerHTML = '';
        return;
    }

    let html = '';
    if (active.length > 0) {
        html += '<div class="mo-section-title">En cours</div>';
        active.forEach(o => html += renderOrderCard(o, true));
    }
    activeEl.innerHTML = html;

    let pastHtml = '';
    if (past.length > 0) {
        pastHtml += '<div class="mo-section-title">Historique</div>';
        past.forEach(o => pastHtml += renderOrderCard(o, false));
        if (hasMore) {
            pastHtml += '<button class="mo-more-btn" onclick="loadMore()">Voir plus</button>';
        }
    }
    pastEl.innerHTML = pastHtml;
}

function renderOrderCard(o, isActive) {
    const date = new Date(o.created_at);
    const dateStr = date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    const itemCount = (o.items || []).reduce((s, i) => s + parseInt(i.quantity), 0);
    const itemNames = (o.items || []).slice(0, 3).map(i => i.item_name).join(', ');
    const photo = o.restaurant_photo ? '/uploads/restaurants/' + o.restaurant_photo : '';
    const typeIcon = o.order_type === 'delivery' ? '<i class="fas fa-motorcycle"></i> Livraison' : '<i class="fas fa-store"></i> Retrait';

    let extra = '';
    if (isActive && o.estimated_minutes && o.status === 'confirmed') {
        extra = `<div class="mo-est"><i class="fas fa-clock"></i> Pret dans ~${o.estimated_minutes} min</div>`;
    }
    if (isActive && ['pending', 'confirmed'].includes(o.status)) {
        extra += `<button class="mo-cancel-btn" onclick="event.stopPropagation(); cancelOrder(${o.id})"><i class="fas fa-times"></i> Annuler</button>`;
    }

    return `
    <div class="mo-card" onclick="showDetail(${o.id})">
        <div class="mo-card-header">
            <div class="mo-card-resto">
                ${photo ? `<img class="mo-card-photo" src="${escHtml(photo)}" alt="">` : '<div class="mo-card-photo"></div>'}
                <div>
                    <div class="mo-card-name">${escHtml(o.restaurant_nom)}</div>
                    <div class="mo-card-date">${dateStr}</div>
                </div>
            </div>
            <span class="mo-badge mo-badge-${o.status}">${STATUS_LABELS[o.status] || o.status}</span>
        </div>
        <div class="mo-card-items">${itemCount} article(s) : ${escHtml(itemNames)}${(o.items || []).length > 3 ? '...' : ''}</div>
        <div class="mo-card-footer">
            <span class="mo-card-total">${parseFloat(o.grand_total).toLocaleString('fr-DZ')} DA</span>
            <span class="mo-card-type">${typeIcon}</span>
        </div>
        ${extra}
    </div>`;
}

async function showDetail(orderId) {
    const o = allOrders.find(o => parseInt(o.id) === orderId);
    if (!o) return;

    let html = '';
    html += `<p style="font-size:13px;color:#6b7280;margin:0 0 12px">#${o.id} &middot; ${o.order_type === 'delivery' ? 'Livraison' : 'Retrait'}</p>`;

    if (o.items && o.items.length > 0) {
        o.items.forEach(item => {
            html += `<div class="mo-detail-item">
                <div>
                    <strong>${escHtml(item.item_name)}</strong> x${item.quantity}
                    ${item.special_requests ? `<div class="mo-detail-notes">"${escHtml(item.special_requests)}"</div>` : ''}
                </div>
                <span>${(parseFloat(item.item_price) * parseInt(item.quantity)).toLocaleString('fr-DZ')} DA</span>
            </div>`;
        });
    }

    html += `<div style="background:#f9fafb;border-radius:8px;padding:12px;margin-top:12px">`;
    html += `<div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:4px"><span>Sous-total</span><span>${parseFloat(o.items_total).toLocaleString('fr-DZ')} DA</span></div>`;
    if (parseFloat(o.delivery_fee) > 0) {
        html += `<div style="display:flex;justify-content:space-between;font-size:14px;margin-bottom:4px"><span>Livraison</span><span>${parseFloat(o.delivery_fee).toLocaleString('fr-DZ')} DA</span></div>`;
    }
    html += `<div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;border-top:1px solid #e5e7eb;padding-top:8px;margin-top:8px"><span>Total</span><span style="color:#00635a">${parseFloat(o.grand_total).toLocaleString('fr-DZ')} DA</span></div>`;
    html += `</div>`;

    if (o.special_instructions) {
        html += `<p style="font-size:13px;color:#6b7280;margin:12px 0 0"><i class="fas fa-sticky-note"></i> ${escHtml(o.special_instructions)}</p>`;
    }

    if (o.cancel_reason) {
        html += `<p style="font-size:13px;color:#dc2626;margin:12px 0 0"><i class="fas fa-exclamation-circle"></i> ${escHtml(o.cancel_reason)}</p>`;
    }

    document.getElementById('detailTitle').textContent = o.restaurant_nom;
    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailOverlay').classList.add('open');
    document.getElementById('detailPanel').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeDetail() {
    document.getElementById('detailOverlay').classList.remove('open');
    document.getElementById('detailPanel').classList.remove('open');
    document.body.style.overflow = '';
}

async function cancelOrder(orderId) {
    if (!confirm('Annuler cette commande ?')) return;

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Content-Type': 'application/json' };
    if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content;

    try {
        const res = await fetch('/api/orders/' + orderId + '/cancel', {
            method: 'POST',
            headers
        });
        const data = await res.json();
        if (data.success) {
            const o = allOrders.find(o => parseInt(o.id) === orderId);
            if (o) o.status = 'cancelled';
            renderOrders();
        } else {
            alert(data.error || 'Erreur');
        }
    } catch { alert('Erreur de connexion'); }
}

function loadMore() {
    currentOffset += 20;
    loadOrders(true);
}

// Polling for active orders
function startPolling() {
    if (pollTimer) clearInterval(pollTimer);
    const hasActive = allOrders.some(o => ACTIVE_STATUSES.includes(o.status));
    if (hasActive) {
        pollTimer = setInterval(async () => {
            try {
                const res = await fetch('/api/my-orders?limit=10&offset=0');
                const data = await res.json();
                if (data.success) {
                    // Update active orders
                    data.orders.forEach(fresh => {
                        const idx = allOrders.findIndex(o => parseInt(o.id) === parseInt(fresh.id));
                        if (idx >= 0) allOrders[idx] = fresh;
                    });
                    renderOrders();
                }
            } catch {}
        }, 30000);
    }
}

function escHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

loadOrders();
</script>
