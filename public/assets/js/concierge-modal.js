/**
 * Concierge Modal v2 — Conversation Logic
 * Handles: open/close, send message, render responses, click tracking, chips
 * Conversation persists across open/close via sessionStorage
 */
(function() {
    'use strict';

    // ── State ──
    let sessionId = null;
    let isOpen = false;
    let isSending = false;
    let hasInitialized = false; // tracks if we already have a conversation

    // ── DOM refs (lazy) ──
    const $ = (sel) => document.querySelector(sel);
    const overlay = () => $('#ccmOverlay');
    const modal   = () => $('#ccmModal');
    const body    = () => $('#ccmBody');
    const input   = () => $('#ccmInput');
    const form    = () => $('#ccmForm');

    // ── CSRF token ──
    function getCsrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    // ── Session ID (persistent per browser session) ──
    function getOrCreateSessionId() {
        let sid = sessionStorage.getItem('ccm_session_id');
        if (!sid) {
            sid = 'ccm_' + Date.now() + '_' + Math.random().toString(36).substring(2, 8);
            sessionStorage.setItem('ccm_session_id', sid);
        }
        return sid;
    }

    // ── Conversation persistence ──
    function saveConversation() {
        const b = body();
        if (!b) return;
        sessionStorage.setItem('ccm_conversation', b.innerHTML);
    }

    function restoreConversation() {
        const saved = sessionStorage.getItem('ccm_conversation');
        const b = body();
        if (!saved || !b) return false;
        b.innerHTML = saved;
        // Re-attach click handlers on restored cards
        b.querySelectorAll('.ccm-card-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                handleCardClick(this);
            });
        });
        b.querySelectorAll('.ccm-card').forEach(function(card) {
            card.addEventListener('click', function() {
                const b = this.querySelector('.ccm-card-btn');
                if (b) handleCardClick(b);
            });
        });
        // Re-attach chip click handlers
        b.querySelectorAll('.ccm-chip').forEach(function(chip) {
            chip.addEventListener('click', function() {
                const wrapper = this.closest('.ccm-chips');
                const query = this.dataset.query || this.textContent.trim();
                if (wrapper) wrapper.remove();
                sendMessage(query);
            });
        });
        return true;
    }

    function resetConversation() {
        sessionStorage.removeItem('ccm_conversation');
        sessionStorage.removeItem('ccm_session_id');
        hasInitialized = false;
        sessionId = getOrCreateSessionId();
        const b = body();
        if (b) b.innerHTML = '';
        renderWelcome();
    }

    // ══════════════════════════════════════════════════
    // OPEN / CLOSE
    // ══════════════════════════════════════════════════

    window.openConciergeModal = function() {
        const o = overlay();
        const m = modal();
        if (!o || !m) return;

        sessionId = getOrCreateSessionId();

        o.classList.add('ccm-open');
        m.classList.add('ccm-open');
        isOpen = true;

        // Restore or initialize conversation
        if (!hasInitialized) {
            const restored = restoreConversation();
            if (!restored) {
                body().innerHTML = '';
                renderWelcome();
            }
            hasInitialized = true;
        }

        // Focus input
        setTimeout(() => { input()?.focus(); }, 200);

        // Scroll to bottom of conversation
        scrollToBottom();

        // Prevent body scroll on mobile
        document.body.style.overflow = 'hidden';
    };

    window.closeConciergeModal = function() {
        const o = overlay();
        const m = modal();
        if (!o || !m) return;

        // Save conversation before closing
        saveConversation();

        o.classList.remove('ccm-open');
        m.classList.remove('ccm-open');
        isOpen = false;

        // Restore body scroll
        document.body.style.overflow = '';
    };

    // Reset conversation (accessible via header button)
    window.resetConciergeConversation = resetConversation;

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            closeConciergeModal();
        }
    });

    // ══════════════════════════════════════════════════
    // WELCOME MESSAGE + CHIPS
    // ══════════════════════════════════════════════════

    function renderWelcome() {
        const b = body();
        if (!b) return;

        // Welcome block
        const welcome = document.createElement('div');
        welcome.className = 'ccm-welcome';

        const greeting = getTimeBasedGreeting();

        welcome.innerHTML =
            '<div class="ccm-welcome-emoji">&#x1F9ED;</div>' +
            '<div class="ccm-welcome-text">' + greeting.text + '</div>';
        b.appendChild(welcome);

        // Welcome chips
        renderChips(greeting.chips);
    }

    // ── Time-based greeting (STORY-019) ──
    function getTimeBasedGreeting() {
        const hour = new Date().getHours();
        const day = new Date().getDay(); // 0=dim, 6=sam
        const isWeekend = (day === 0 || day === 6);

        if (isWeekend) {
            return {
                text: 'Bon weekend ! Envie de sortir manger ?',
                chips: [
                    { icon: '&#x1F468;&#x200D;&#x1F469;&#x200D;&#x1F467;', label: 'Famille', query: 'restaurant familial' },
                    { icon: '&#x2615;', label: 'Brunch', query: 'brunch ou petit dejeuner' },
                    { icon: '&#x1F389;', label: 'Sortie', query: 'restaurant festif' },
                    { icon: '&#x1F3D6;', label: 'Terrasse', query: 'restaurant avec terrasse' }
                ]
            };
        }

        if (hour >= 7 && hour < 11) {
            return {
                text: 'Bonjour ! Comment puis-je vous aider ce matin ?',
                chips: [
                    { icon: '&#x2615;', label: 'Petit-dej', query: 'petit dejeuner ou brunch' },
                    { icon: '&#x1F4BC;', label: 'Business', query: 'restaurant business' },
                    { icon: '&#x26A1;', label: 'Rapide', query: 'restaurant rapide' },
                    { icon: '&#x1F3D6;', label: 'Terrasse', query: 'restaurant avec terrasse' }
                ]
            };
        }

        if (hour >= 11 && hour < 14) {
            return {
                text: "C'est l'heure du dejeuner ! Que recherchez-vous ?",
                chips: [
                    { icon: '&#x26A1;', label: 'Rapide', query: 'restaurant rapide pas cher' },
                    { icon: '&#x1F4B0;', label: 'Pas cher', query: 'restaurant pas cher' },
                    { icon: '&#x1F69A;', label: 'Livraison', query: 'restaurant livraison' },
                    { icon: '&#x1F3D6;', label: 'Terrasse', query: 'restaurant avec terrasse' }
                ]
            };
        }

        if (hour >= 14 && hour < 18) {
            return {
                text: 'Bonjour ! Besoin de trouver un bon restaurant ?',
                chips: [
                    { icon: '&#x1F468;&#x200D;&#x1F469;&#x200D;&#x1F467;', label: 'Famille', query: 'restaurant familial' },
                    { icon: '&#x2764;', label: 'Romantique', query: 'restaurant romantique' },
                    { icon: '&#x1F3D6;', label: 'Terrasse', query: 'restaurant avec terrasse' },
                    { icon: '&#x2B50;', label: 'Top note', query: 'meilleur restaurant' }
                ]
            };
        }

        if (hour >= 18 && hour < 22) {
            return {
                text: "Bonsoir ! Trouvons votre restaurant pour ce soir.",
                chips: [
                    { icon: '&#x2764;', label: 'Romantique', query: 'restaurant romantique' },
                    { icon: '&#x1F468;&#x200D;&#x1F469;&#x200D;&#x1F467;', label: 'Famille', query: 'restaurant familial' },
                    { icon: '&#x1F37D;', label: 'Gastronomie', query: 'restaurant gastronomique' },
                    { icon: '&#x1F389;', label: 'Festif', query: 'restaurant festif' }
                ]
            };
        }

        // Night (22-7)
        return {
            text: 'Bonsoir ! Encore faim ? Je suis la pour vous aider.',
            chips: [
                { icon: '&#x26A1;', label: 'Rapide', query: 'restaurant rapide' },
                { icon: '&#x1F69A;', label: 'Livraison', query: 'restaurant livraison' },
                { icon: '&#x1F389;', label: 'Festif', query: 'restaurant festif' },
                { icon: '&#x2B50;', label: 'Top note', query: 'meilleur restaurant' }
            ]
        };
    }

    // ══════════════════════════════════════════════════
    // SEND MESSAGE
    // ══════════════════════════════════════════════════

    window.sendConciergeMessage = function(e) {
        if (e) e.preventDefault();

        const inp = input();
        if (!inp) return;

        const message = inp.value.trim();
        if (!message || isSending) return;

        inp.value = '';
        sendMessage(message);
    };

    function sendMessage(message) {
        if (isSending) return;
        isSending = true;

        // Render user bubble
        renderUserMessage(message);

        // Show typing indicator
        const typingEl = renderTyping();

        // Scroll to bottom
        scrollToBottom();

        // API call
        fetch('/api/concierge/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrf()
            },
            body: JSON.stringify({
                message: message,
                session_id: sessionId
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            // Remove typing
            if (typingEl && typingEl.parentNode) typingEl.remove();

            if (data.success) {
                renderBotResponse(data);
            } else {
                renderError(data.error || 'Une erreur est survenue');
            }
        })
        .catch(function() {
            if (typingEl && typingEl.parentNode) typingEl.remove();
            renderError('Erreur de connexion. Reessayez.');
        })
        .finally(function() {
            isSending = false;
            scrollToBottom();
            saveConversation();
        });
    }

    // ══════════════════════════════════════════════════
    // RENDER FUNCTIONS
    // ══════════════════════════════════════════════════

    function renderUserMessage(text) {
        const b = body();
        if (!b) return;
        const div = document.createElement('div');
        div.className = 'ccm-msg ccm-msg-user';
        div.textContent = text;
        b.appendChild(div);
    }

    function renderTyping() {
        const b = body();
        if (!b) return null;
        const div = document.createElement('div');
        div.className = 'ccm-typing';
        div.innerHTML = '<div class="ccm-typing-dot"></div><div class="ccm-typing-dot"></div><div class="ccm-typing-dot"></div>';
        b.appendChild(div);
        scrollToBottom();
        return div;
    }

    function renderError(msg) {
        const b = body();
        if (!b) return;
        const div = document.createElement('div');
        div.className = 'ccm-error';
        div.textContent = msg;
        b.appendChild(div);
    }

    function renderBotResponse(data) {
        const b = body();
        if (!b) return;

        // Response header text (contextual)
        if (data.response) {
            const headerText = extractHeader(data.response);
            if (headerText) {
                const hDiv = document.createElement('div');
                hDiv.className = 'ccm-msg ccm-msg-bot';
                const hSpan = document.createElement('div');
                hSpan.className = 'ccm-response-header';
                hSpan.textContent = headerText;
                hDiv.appendChild(hSpan);
                b.appendChild(hDiv);
            }
        }

        // Restaurant cards
        if (data.data && data.data.restaurants && data.data.restaurants.length > 0) {
            renderRecommendations(data.data.restaurants, data.recommendations || []);
        } else if (data.response && !data.data?.restaurants?.length) {
            // Text-only response (hours, booking, general)
            const textDiv = document.createElement('div');
            textDiv.className = 'ccm-msg ccm-msg-bot';
            textDiv.textContent = cleanResponse(data.response);
            b.appendChild(textDiv);
        }

        // Suggestion chips
        if (data.suggestions && data.suggestions.length > 0) {
            renderChips(data.suggestions.map(function(s) {
                if (typeof s === 'string') {
                    return { label: s, query: s };
                }
                return s;
            }));
        }

        scrollToBottom();
    }

    function extractHeader(response) {
        // The response text starts with the contextual header, then \n\n, then restaurant lines
        const parts = response.split('\n\n');
        if (parts.length > 1) {
            return parts[0];
        }
        return null;
    }

    function cleanResponse(response) {
        // Remove markdown bold **text** → text
        return response.replace(/\*\*/g, '').replace(/→/g, '- ');
    }

    function renderRecommendations(restaurants, recommendations) {
        const b = body();
        if (!b) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'ccm-cards';

        restaurants.forEach(function(r, i) {
            const recId = recommendations[i] ? recommendations[i].rec_id : null;
            const card = createCard(r, i + 1, recId);
            wrapper.appendChild(card);
        });

        b.appendChild(wrapper);
    }

    function createCard(r, position, recId) {
        const card = document.createElement('div');
        card.className = 'ccm-card';
        card.setAttribute('role', 'article');

        const rating = parseFloat(r.rating || 0).toFixed(1);
        const photoSrc = r.photo || '';
        const photoHtml = photoSrc
            ? '<img class="ccm-card-photo" src="' + escHtml(photoSrc) + '" alt="' + escHtml(r.name) + '" loading="lazy" onerror="this.style.display=\'none\'">'
            : '';

        card.innerHTML =
            '<div class="ccm-card-position">#' + position + '</div>' +
            photoHtml +
            '<div class="ccm-card-info">' +
                '<div class="ccm-card-name">' + escHtml(r.name) + '</div>' +
                '<div class="ccm-card-meta">' +
                    '<span class="ccm-card-star"><i class="fas fa-star"></i> ' + rating + '</span>' +
                    (r.price ? ' <span>' + escHtml(r.price) + '</span>' : '') +
                    (r.city ? ' <span>' + escHtml(r.city) + '</span>' : '') +
                '</div>' +
                '<div class="ccm-card-explanation">' + escHtml(r.explanation || '') + '</div>' +
            '</div>' +
            '<button class="ccm-card-btn" data-url="' + escHtml(r.url) + '" data-rec-id="' + (recId || '') + '" data-restaurant-id="' + r.id + '">Voir</button>';

        // Click handler on "Voir" button
        const btn = card.querySelector('.ccm-card-btn');
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            handleCardClick(this);
        });

        // Click on card itself
        card.addEventListener('click', function() {
            const b = this.querySelector('.ccm-card-btn');
            if (b) handleCardClick(b);
        });

        return card;
    }

    function handleCardClick(btn) {
        const url = btn.dataset.url;
        const recId = btn.dataset.recId;
        const restaurantId = btn.dataset.restaurantId;

        if (!url) return;

        // Build URL with ref params
        let targetUrl = url;
        const sep = url.includes('?') ? '&' : '?';
        targetUrl += sep + 'ref=concierge';
        if (recId) targetUrl += '&rec_id=' + recId;

        // Track click (non-blocking)
        if (recId) {
            fetch('/api/concierge/click', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                },
                body: JSON.stringify({ rec_id: parseInt(recId, 10), restaurant_id: parseInt(restaurantId, 10) || 0 })
            }).catch(function() { /* silent */ });
        }

        // Open restaurant page
        window.open(targetUrl, '_blank');
    }

    // ══════════════════════════════════════════════════
    // CHIPS (welcome + follow-up)
    // ══════════════════════════════════════════════════

    function renderChips(chips) {
        const b = body();
        if (!b || !chips || !chips.length) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'ccm-chips';

        chips.forEach(function(chip) {
            const btn = document.createElement('button');
            btn.className = 'ccm-chip';
            btn.type = 'button';

            const label = chip.label || chip;
            const query = chip.query || label;
            const icon = chip.icon || '';

            btn.dataset.query = query;
            btn.innerHTML = (icon ? '<span>' + icon + '</span> ' : '') + escHtml(label);

            btn.addEventListener('click', function() {
                // Remove this chips row
                if (wrapper.parentNode) wrapper.remove();
                // Send as message
                sendMessage(query);
            });

            wrapper.appendChild(btn);
        });

        b.appendChild(wrapper);
        scrollToBottom();
    }

    // ══════════════════════════════════════════════════
    // UTILITIES
    // ══════════════════════════════════════════════════

    function scrollToBottom() {
        const b = body();
        if (b) {
            setTimeout(function() {
                b.scrollTop = b.scrollHeight;
            }, 50);
        }
    }

    function escHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

})();
