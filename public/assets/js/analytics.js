/**
 * ═══════════════════════════════════════════════════════════════
 * ANALYTICS TRACKER - LEBONRESTO
 * Tracking automatique de toutes les interactions utilisateurs
 * ═══════════════════════════════════════════════════════════════
 */

class AnalyticsTracker {
    constructor() {
        this.restaurantId = null;
        this.sessionId = this.getOrCreateSessionId();
        this.apiEndpoint = '/api/analytics/track';
        this.isEnabled = true;
        this.queue = [];
        this.isSending = false;
    }
    
    /**
     * Initialiser le tracker pour un restaurant
     */
    init(restaurantId) {
        if (!restaurantId) {
            console.warn('⚠️ Analytics: Restaurant ID manquant');
            return;
        }
        
        this.restaurantId = restaurantId;

        // Track la vue de page immédiatement
        this.trackPageView();

        // Bind tous les événements
        this.bindEvents();
    }
    
    /**
     * Track la vue de page
     */
    trackPageView() {
        this.track('view', {
            title: document.title,
            url: window.location.href
        });
    }
    
    /**
     * Bind tous les événements de tracking
     */
    bindEvents() {
        // 1. CLICS TÉLÉPHONE
        document.querySelectorAll('[data-track="phone"], a[href^="tel:"]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.track('click_phone');
            });
        });
        
        // 2. CLICS ITINÉRAIRE
        document.querySelectorAll('[data-track="directions"]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.track('click_directions');
            });
        });
        
        // 3. CLICS SITE WEB
        document.querySelectorAll('[data-track="website"]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.track('click_website');
            });
        });
        
        // 4. CLICS MENU
        document.querySelectorAll('[data-track="menu"]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.track('click_menu');
            });
        });
        
        // 5. CLICS RÉSERVATION
        document.querySelectorAll('[data-track="booking"]').forEach(btn => {
            btn.addEventListener('click', () => {
                this.track('click_booking');
            });
        });
        
        // 6. PARTAGE (wrapper la fonction globale si existe)
        if (typeof window.shareRestaurant === 'function') {
            const originalShare = window.shareRestaurant;
            window.shareRestaurant = async () => {
                this.track('share');
                return originalShare();
            };
        }
        
        // 7. GALERIE PHOTOS (wrapper la fonction globale)
        if (typeof window.openGallery === 'function') {
            const originalOpenGallery = window.openGallery;
            window.openGallery = (index) => {
                this.track('gallery_open', { photo_index: index });
                return originalOpenGallery(index);
            };
        }
        
        // 8. WISHLIST (écouter l'événement custom)
        window.addEventListener('wishlist:changed', (e) => {
            const eventType = e.detail.isFavorite ? 'wishlist_add' : 'wishlist_remove';
            this.track(eventType);
        });
        
        // 9. FORMULAIRE AVIS (si existe)
        const reviewForm = document.querySelector('form[action*="review"]');
        if (reviewForm) {
            // Track ouverture formulaire (au focus du premier input)
            let formOpened = false;
            reviewForm.querySelectorAll('input, textarea').forEach(input => {
                input.addEventListener('focus', () => {
                    if (!formOpened) {
                        this.track('review_form_open');
                        formOpened = true;
                    }
                }, { once: true });
            });
            
            // Track soumission
            reviewForm.addEventListener('submit', () => {
                this.track('review_submitted');
            });
        }
        
    }
    
    /**
     * Track un événement (méthode principale)
     */
    async track(eventType, metadata = {}) {
        if (!this.isEnabled || !this.restaurantId) return;
        
        const payload = {
            restaurant_id: this.restaurantId,
            event_type: eventType,
            session_id: this.sessionId,
            metadata: metadata,
            referer: document.referrer || null,
            page_url: window.location.href,
            user_agent: navigator.userAgent,
            device_type: this.getDeviceType()
        };
        
        // Ajouter à la queue
        this.queue.push(payload);
        
        // Envoyer immédiatement (debounced)
        this.sendQueue();
    }
    
    /**
     * Envoyer la queue d'événements
     */
    async sendQueue() {
        if (this.isSending || this.queue.length === 0) return;
        
        this.isSending = true;
        const event = this.queue.shift();
        
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(event),
                // Important : keepalive pour envoyer même si page se ferme
                keepalive: true
            });
            
            if (!response.ok) {
                console.warn('Analytics tracking failed:', response.status);
            }
            
        } catch (error) {
            console.warn('Analytics error:', error);
        } finally {
            this.isSending = false;
            
            // Continuer à vider la queue
            if (this.queue.length > 0) {
                setTimeout(() => this.sendQueue(), 100);
            }
        }
    }
    
    /**
     * Générer ou récupérer le session ID (cookie 30min)
     */
    getOrCreateSessionId() {
        const cookieName = 'analytics_sid';
        let sessionId = this.getCookie(cookieName);
        
        if (!sessionId) {
            sessionId = this.generateUUID();
            this.setCookie(cookieName, sessionId, 0.5); // 30 minutes
        }
        
        return sessionId;
    }
    
    /**
     * Générer un UUID v4
     */
    generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    
    /**
     * Détecter le type d'appareil
     */
    getDeviceType() {
        const ua = navigator.userAgent.toLowerCase();
        const isTouchDevice = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        // Détection mobile (smartphones)
        if (/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i.test(ua)) {
            return 'mobile';
        }
        
        // Détection tablet (iPad, Android tablets)
        if (/tablet|ipad|playbook|silk/i.test(ua) || (isTouchDevice && window.innerWidth >= 768 && window.innerWidth <= 1024)) {
            return 'tablet';
        }
        
        // Desktop par défaut
        return 'desktop';
    }
    
    /**
     * Récupérer un cookie
     */
    getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        return match ? match[2] : null;
    }
    
    /**
     * Définir un cookie
     */
    setCookie(name, value, hours) {
        const d = new Date();
        d.setTime(d.getTime() + (hours * 60 * 60 * 1000));
        const expires = 'expires=' + d.toUTCString();
        document.cookie = name + '=' + value + ';' + expires + ';path=/;SameSite=Lax';
    }
    
    /**
     * Désactiver le tracking (RGPD)
     */
    disable() {
        this.isEnabled = false;
    }
    
    /**
     * Réactiver le tracking
     */
    enable() {
        this.isEnabled = true;
    }
}

// ═══════════════════════════════════════════════════════════════
// INSTANCE GLOBALE
// ═══════════════════════════════════════════════════════════════
window.AnalyticsTracker = new AnalyticsTracker();

// ═══════════════════════════════════════════════════════════════
// AUTO-INIT (si window.restaurant existe)
// ═══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    // Essayer de récupérer l'ID restaurant depuis plusieurs sources
    let restaurantId = null;
    
    // 1. Variable globale window.restaurant
    if (window.restaurant && window.restaurant.id) {
        restaurantId = window.restaurant.id;
    }
    // 2. Data attribute sur un élément
    else if (document.querySelector('[data-restaurant-id]')) {
        const section = document.querySelector('[data-restaurant-id]');
        restaurantId = section.dataset.restaurantId;
    }
    // 3. Meta tag
    else if (document.querySelector('meta[name="restaurant-id"]')) {
        const meta = document.querySelector('meta[name="restaurant-id"]');
        restaurantId = meta.content;
    }
    
    if (restaurantId) {
        window.AnalyticsTracker.init(restaurantId);
    }
});

// ═══════════════════════════════════════════════════════════════
// HELPER : Track manuel depuis n'importe où
// ═══════════════════════════════════════════════════════════════
window.trackEvent = (eventType, metadata = {}) => {
    window.AnalyticsTracker.track(eventType, metadata);
};
