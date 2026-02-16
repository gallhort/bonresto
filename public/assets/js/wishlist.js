/**
 * ═══════════════════════════════════════════════════════════════════════════
 * WISHLIST.JS - Système de favoris réutilisable
 * ═══════════════════════════════════════════════════════════════════════════
 * 
 * Usage:
 * 1. Inclure ce fichier: <script src="/assets/js/wishlist.js"></script>
 * 2. Ajouter data-wishlist="RESTAURANT_ID" sur les boutons
 * 3. C'est tout ! Le script s'initialise automatiquement
 * 
 * HTML Examples:
 * <button class="wishlist-btn" data-wishlist="123"><i class="far fa-heart"></i></button>
 * <button data-wishlist="456" data-wishlist-text>♡ Favoris</button>
 */

(function() {
    'use strict';

    // ═══════════════════════════════════════════════════════════════════════
    // CONFIGURATION
    // ═══════════════════════════════════════════════════════════════════════
    const CONFIG = {
        apiBase: '/api/wishlist',
        selectors: {
            button: '[data-wishlist]',
            icon: '.fa-heart',
        },
        classes: {
            active: 'active',
            loading: 'loading',
            error: 'error'
        },
        text: {
            add: 'Ajouter aux favoris',
            remove: 'Retirer des favoris',
            added: 'Ajouté !',
            removed: 'Retiré',
            error: 'Erreur',
            login: 'Connectez-vous'
        },
        toast: {
            enabled: true,
            duration: 3000
        }
    };

    // ═══════════════════════════════════════════════════════════════════════
    // STATE
    // ═══════════════════════════════════════════════════════════════════════
    const state = {
        favorites: new Set(),
        isLoggedIn: null,
        initialized: false
    };

    // ═══════════════════════════════════════════════════════════════════════
    // API CALLS
    // ═══════════════════════════════════════════════════════════════════════
    
    async function apiToggle(restaurantId) {
        const response = await fetch(`${CONFIG.apiBase}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ restaurant_id: restaurantId })
        });
        return response.json();
    }

    async function apiCheckMultiple(restaurantIds) {
        const response = await fetch(`${CONFIG.apiBase}/check-multiple`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ restaurant_ids: restaurantIds })
        });
        return response.json();
    }

    async function apiCheck(restaurantId) {
        const response = await fetch(`${CONFIG.apiBase}/check/${restaurantId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        return response.json();
    }

    // ═══════════════════════════════════════════════════════════════════════
    // UI UPDATES
    // ═══════════════════════════════════════════════════════════════════════

    function updateButtonState(button, isFavorite, isLoading = false) {
        const icon = button.querySelector(CONFIG.selectors.icon);
        const restaurantId = button.dataset.wishlist;

        // Toggle classes
        button.classList.toggle(CONFIG.classes.active, isFavorite);
        button.classList.toggle(CONFIG.classes.loading, isLoading);

        // Update icon
        if (icon) {
            // Remove all heart classes
            icon.classList.remove('far', 'fas', 'fa-heart', 'fa-spinner', 'fa-spin');
            
            if (isLoading) {
                icon.classList.add('fas', 'fa-spinner', 'fa-spin');
            } else if (isFavorite) {
                icon.classList.add('fas', 'fa-heart');
            } else {
                icon.classList.add('far', 'fa-heart');
            }
        }

        // Update title/aria
        button.title = isFavorite ? CONFIG.text.remove : CONFIG.text.add;
        button.setAttribute('aria-pressed', isFavorite);

        // Update state
        if (isFavorite) {
            state.favorites.add(parseInt(restaurantId));
        } else {
            state.favorites.delete(parseInt(restaurantId));
        }
    }

    function updateAllButtons(restaurantId, isFavorite) {
        document.querySelectorAll(`[data-wishlist="${restaurantId}"]`).forEach(btn => {
            updateButtonState(btn, isFavorite);
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // TOAST NOTIFICATIONS
    // ═══════════════════════════════════════════════════════════════════════

    function showToast(message, type = 'success') {
        if (!CONFIG.toast.enabled) return;

        // Supprimer les anciens toasts
        document.querySelectorAll('.wishlist-toast').forEach(t => t.remove());

        const toast = document.createElement('div');
        toast.className = `wishlist-toast wishlist-toast-${type}`;
        toast.innerHTML = `
            <div class="wishlist-toast-content">
                <i class="${type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;

        // Styles inline pour être indépendant
        Object.assign(toast.style, {
            position: 'fixed',
            bottom: '100px',
            left: '50%',
            transform: 'translateX(-50%) translateY(20px)',
            padding: '14px 24px',
            borderRadius: '12px',
            background: type === 'success' ? '#00875A' : type === 'error' ? '#ef4444' : '#1a1a1a',
            color: 'white',
            fontSize: '14px',
            fontWeight: '500',
            fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            boxShadow: '0 8px 32px rgba(0,0,0,0.2)',
            zIndex: '10000',
            opacity: '0',
            transition: 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)'
        });

        const content = toast.querySelector('.wishlist-toast-content');
        Object.assign(content.style, {
            display: 'flex',
            alignItems: 'center',
            gap: '10px'
        });

        document.body.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(-50%) translateY(0)';
        });

        // Auto remove
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(() => toast.remove(), 300);
        }, CONFIG.toast.duration);
    }

    // ═══════════════════════════════════════════════════════════════════════
    // LOGIN MODAL
    // ═══════════════════════════════════════════════════════════════════════

    function showLoginPrompt() {
        // Option 1: Redirect
        // window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);

        // Option 2: Toast avec lien
        showToast('Connectez-vous pour ajouter aux favoris', 'info');

        // Option 3: Trigger custom event (pour modal externe)
        window.dispatchEvent(new CustomEvent('wishlist:login-required'));
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MAIN TOGGLE FUNCTION
    // ═══════════════════════════════════════════════════════════════════════

    async function toggleWishlist(button) {
        const restaurantId = parseInt(button.dataset.wishlist);
        if (!restaurantId || button.classList.contains(CONFIG.classes.loading)) return;

        const wasFavorite = state.favorites.has(restaurantId);

        // Optimistic UI update
        updateButtonState(button, !wasFavorite, true);

        try {
            const result = await apiToggle(restaurantId);

            if (result.success) {
                const isFavorite = result.is_favorite;
                updateAllButtons(restaurantId, isFavorite);

                // Toast
                const message = isFavorite 
                    ? `${result.restaurant_name || 'Restaurant'} ajouté aux favoris`
                    : `${result.restaurant_name || 'Restaurant'} retiré des favoris`;
                showToast(message, 'success');

                // Dispatch event
                window.dispatchEvent(new CustomEvent('wishlist:changed', {
                    detail: { restaurantId, isFavorite, action: result.action }
                }));

            } else if (result.require_login) {
                // Revert
                updateButtonState(button, wasFavorite, false);
                showLoginPrompt();
            } else {
                throw new Error(result.error || 'Erreur inconnue');
            }

        } catch (error) {
            console.error('Wishlist error:', error);
            // Revert
            updateButtonState(button, wasFavorite, false);
            showToast('Une erreur est survenue', 'error');
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // INITIALIZATION
    // ═══════════════════════════════════════════════════════════════════════

    async function initializeButtons() {
        const buttons = document.querySelectorAll(CONFIG.selectors.button);
        if (buttons.length === 0) return;

        // Collecter tous les IDs
        const restaurantIds = [...new Set(
            Array.from(buttons).map(btn => parseInt(btn.dataset.wishlist)).filter(id => id > 0)
        )];

        if (restaurantIds.length === 0) return;

        try {
            // Vérifier en batch
            const result = await apiCheckMultiple(restaurantIds);

            if (result.success) {
                state.isLoggedIn = result.logged_in !== false;
                state.favorites = new Set(result.favorites || []);

                // Update all buttons
                buttons.forEach(btn => {
                    const id = parseInt(btn.dataset.wishlist);
                    const isFavorite = state.favorites.has(id);
                    updateButtonState(btn, isFavorite, false);
                });
            }
        } catch (error) {
            console.error('Failed to initialize wishlist:', error);
        }

        state.initialized = true;
    }

    function attachEventListeners() {
        // Event delegation
        document.addEventListener('click', (e) => {
            const button = e.target.closest(CONFIG.selectors.button);
            if (button) {
                e.preventDefault();
                e.stopPropagation();
                toggleWishlist(button);
            }
        });

        // Re-initialize on dynamic content
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                        const newButtons = node.querySelectorAll 
                            ? node.querySelectorAll(CONFIG.selectors.button)
                            : [];
                        if (newButtons.length > 0 || node.matches?.(CONFIG.selectors.button)) {
                            initializeButtons();
                        }
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    // ═══════════════════════════════════════════════════════════════════════
    // PUBLIC API
    // ═══════════════════════════════════════════════════════════════════════

    window.Wishlist = {
        toggle: async (restaurantId) => {
            const btn = document.querySelector(`[data-wishlist="${restaurantId}"]`);
            if (btn) {
                await toggleWishlist(btn);
            } else {
                // Toggle sans bouton
                const result = await apiToggle(restaurantId);
                if (result.success) {
                    if (result.is_favorite) {
                        state.favorites.add(restaurantId);
                    } else {
                        state.favorites.delete(restaurantId);
                    }
                }
                return result;
            }
        },
        isFavorite: (restaurantId) => state.favorites.has(parseInt(restaurantId)),
        getFavorites: () => [...state.favorites],
        refresh: initializeButtons,
        isLoggedIn: () => state.isLoggedIn
    };

    // ═══════════════════════════════════════════════════════════════════════
    // AUTO INIT
    // ═══════════════════════════════════════════════════════════════════════

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            attachEventListeners();
            initializeButtons();
        });
    } else {
        attachEventListeners();
        initializeButtons();
    }

})();