/**
 * ═══════════════════════════════════════════════════════════════════════════
 * HOME MODERNE - JAVASCRIPT
 * Fichier : /public/assets/js/home-modern.js
 * ═══════════════════════════════════════════════════════════════════════════
 */

// ═══ DOM READY ═══
document.addEventListener('DOMContentLoaded', function() {

    try {
        initStatsAnimation();
    } catch (e) {
        console.error('Erreur stats:', e);
    }

    try {
        initSearchAutocomplete();
    } catch (e) {
        console.error('Erreur search:', e);
    }

    try {
        initSearchTags();
    } catch (e) {
        console.error('Erreur tags:', e);
    }

    try {
        initScrollAnimations();
    } catch (e) {
        console.error('Erreur scroll:', e);
    }

    try {
        initWishlist();
    } catch (e) {
        console.error('Erreur wishlist:', e);
    }

    try {
        initNewsletter();
    } catch (e) {
        console.error('Erreur newsletter:', e);
    }

    try {
        initCuisinesSlider();
    } catch (e) {
        console.error('Erreur slider:', e);
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// STATS ANIMATION - Compteur animé
// ═══════════════════════════════════════════════════════════════════════════
function initStatsAnimation() {
    const stats = document.querySelectorAll('.hero-stat');

    if (stats.length === 0) {
        return;
    }

    const animateValue = (element, start, end, duration) => {
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (end - start) * easeOut);

            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                element.textContent = end.toLocaleString();
            }
        };

        requestAnimationFrame(animate);
    };

    // Observer pour démarrer l'animation quand visible
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const numberElement = entry.target.querySelector('.hero-stat-number');
                const targetValue = parseInt(entry.target.dataset.count);

                if (numberElement && !isNaN(targetValue)) {
                    animateValue(numberElement, 0, targetValue, 2000);
                    observer.unobserve(entry.target);
                }
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => observer.observe(stat));
}

// ═══════════════════════════════════════════════════════════════════════════
// SEARCH AUTOCOMPLETE
// ═══════════════════════════════════════════════════════════════════════════
function initSearchAutocomplete() {
    const searchInput = document.getElementById('searchInput');
    const autocompleteResults = document.getElementById('autocompleteResults');

    if (!searchInput || !autocompleteResults) {
        return;
    }

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);

        const query = this.value.trim();

        if (query.length < 2) {
            autocompleteResults.classList.remove('show');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });

    // Fermer au clic extérieur
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
            autocompleteResults.classList.remove('show');
        }
    });

    async function fetchSuggestions(query) {
        try {
            const response = await fetch(`/api/search/autocomplete?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            displaySuggestions(data.results || []);
        } catch (error) {
            console.error('Autocomplete error:', error);
        }
    }

    function displaySuggestions(results) {
        if (results.length === 0) {
            autocompleteResults.innerHTML = '<div class="autocomplete-item">Aucun résultat trouvé</div>';
            autocompleteResults.classList.add('show');
            return;
        }

        const html = results.map(result => `
            <a href="/restaurant/${result.id}" class="autocomplete-item">
                <i class="fas fa-utensils" style="color: var(--primary);"></i>
                <span style="flex:1; font-weight:500;">${result.nom}</span>
                <span style="font-size:13px; color:#999;">${result.ville || ''}</span>
            </a>
        `).join('');

        autocompleteResults.innerHTML = html;
        autocompleteResults.classList.add('show');
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// SEARCH TAGS - Tags de recherche rapide
// ═══════════════════════════════════════════════════════════════════════════
function initSearchTags() {
    const searchTags = document.querySelectorAll('.search-tag');
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.querySelector('.search-form');

    if (searchTags.length === 0) {
        return;
    }

    searchTags.forEach(tag => {
        tag.addEventListener('click', function() {
            const searchTerm = this.dataset.search;
            if (searchInput) {
                searchInput.value = searchTerm;
                if (searchForm) searchForm.submit();
            }
        });
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// SCROLL ANIMATIONS - Animations au défilement
// ═══════════════════════════════════════════════════════════════════════════
function initScrollAnimations() {
    const animateElements = document.querySelectorAll('.restaurant-card, .city-card, .cuisine-card, .review-card-home');

    if (animateElements.length === 0) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    animateElements.forEach(element => {
        observer.observe(element);
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// WISHLIST - Gestion des favoris
// ═══════════════════════════════════════════════════════════════════════════
function initWishlist() {
    const wishlistButtons = document.querySelectorAll('.card-wishlist');

    if (wishlistButtons.length === 0) {
        return;
    }

    wishlistButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            const restaurantId = this.dataset.id;
            const icon = this.querySelector('i');

            try {
                const response = await fetch('/api/wishlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ restaurant_id: restaurantId })
                });

                const data = await response.json();

                if (data.success) {
                    // Toggle icon
                    if (icon.classList.contains('far')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');

                        // Animation
                        this.style.transform = 'scale(1.3)';
                        setTimeout(() => {
                            this.style.transform = 'scale(1)';
                        }, 200);

                        showToast('Ajouté aux favoris ❤️');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        showToast('Retiré des favoris');
                    }
                } else if (data.redirect) {
                    // Utilisateur non connecté
                    window.location.href = '/login';
                }
            } catch (error) {
                console.error('Wishlist error:', error);
                showToast('Une erreur est survenue', 'error');
            }
        });
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// NEWSLETTER - Formulaire d'inscription
// ═══════════════════════════════════════════════════════════════════════════
function initNewsletter() {
    const form = document.getElementById('newsletterForm');

    if (!form) {
        return;
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const input = this.querySelector('.newsletter-input');
        const button = this.querySelector('.newsletter-button');
        const email = input.value.trim();

        if (!isValidEmail(email)) {
            showToast('Adresse email invalide', 'error');
            return;
        }

        // Désactiver le bouton
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription...';

        try {
            const response = await fetch('/api/newsletter/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json();

            if (data.success) {
                showToast('Inscription confirmée !', 'success');
                input.value = '';
            } else {
                showToast(data.message || 'Une erreur est survenue', 'error');
            }
        } catch (error) {
            console.error('Newsletter error:', error);
            showToast('Une erreur est survenue', 'error');
        } finally {
            button.disabled = false;
            button.innerHTML = 'S\'inscrire <i class="fas fa-arrow-right"></i>';
        }
    });
}

// ═══════════════════════════════════════════════════════════════════════════
// CUISINES SLIDER
// ═══════════════════════════════════════════════════════════════════════════
/**
 * SOLUTION FINALE - Calcul RÉEL du nombre de slides visibles
 * Remplace la fonction initCuisinesSlider() complète
 */



function initCuisinesSlider() {
    const slider = document.getElementById('cuisinesSlider');
    const prevBtn = document.getElementById('cuisinesPrev');
    const nextBtn = document.getElementById('cuisinesNext');
    const dotsContainer = document.getElementById('sliderDots');

    if (!slider) {
        return;
    }

    const slides = slider.querySelectorAll('.cuisine-slide');

    if (slides.length === 0) {
        return;
    }

    let currentIndex = 0;

    // CALCUL RÉEL du nombre de slides visibles
    const getVisibleSlides = () => {
        const sliderWrapper = slider.parentElement;
        const wrapperWidth = sliderWrapper.offsetWidth;
        const slideWidth = slides[0].offsetWidth;
        const gap = parseInt(window.getComputedStyle(slider).gap) || 24;

        // Nombre de slides qui rentrent dans la largeur visible
        const visibleCount = wrapperWidth / (slideWidth + gap);

        // Arrondir à l'entier inférieur (slides COMPLETS visibles)
        return Math.floor(visibleCount);
    };

    // Calculer slideWidth
    const getSlideWidth = () => {
        const slide = slides[0];
        const width = slide.offsetWidth;
        const gap = parseInt(window.getComputedStyle(slider).gap) || 24;
        return width + gap;
    };

    let slideWidth = getSlideWidth();
    let visibleSlides = getVisibleSlides();

    // CALCUL CORRECT du maxIndex
    const getMaxIndex = () => {
        const max = slides.length - visibleSlides;
        return Math.max(0, max);
    };

    let maxIndex = getMaxIndex();

    // Créer SEULEMENT les dots pour les positions valides
    const createDots = () => {
        if (!dotsContainer) return;

        dotsContainer.innerHTML = '';

        // Créer maxIndex + 1 dots (positions 0 à maxIndex)
        for (let i = 0; i <= maxIndex; i++) {
            const dot = document.createElement('button');
            dot.className = 'slider-dot';

            if (i === currentIndex) {
                dot.classList.add('active');
            }

            dot.addEventListener('click', () => {
                goToSlide(i);
            });

            dotsContainer.appendChild(dot);
        }
    };

    // Navigation avec loop
    const goToSlide = (index) => {
        // Loop strict
        if (index > maxIndex) {
            currentIndex = 0;
        } else if (index < 0) {
            currentIndex = maxIndex;
        } else {
            currentIndex = index;
        }

        updateSlider();
    };

    // Mise à jour
    const updateSlider = () => {
        const offset = -(currentIndex * slideWidth);
        slider.style.transform = `translateX(${offset}px)`;

        // Mettre à jour les dots
        if (dotsContainer) {
            const dots = dotsContainer.querySelectorAll('.slider-dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }

        // Boutons toujours actifs (loop)
        if (prevBtn) prevBtn.disabled = false;
        if (nextBtn) nextBtn.disabled = false;
    };

    // Bouton PREV
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                goToSlide(currentIndex - 1);
            } else {
                goToSlide(maxIndex); // Loop
            }
        });
    }

    // Bouton NEXT
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (currentIndex < maxIndex) {
                goToSlide(currentIndex + 1);
            } else {
                goToSlide(0); // Loop
            }
        });
    }

    // Swipe
    let touchStartX = 0;
    let touchEndX = 0;

    slider.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });

    slider.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;

        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                nextBtn?.click();
            } else {
                prevBtn?.click();
            }
        }
    });

    // Resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            slideWidth = getSlideWidth();
            visibleSlides = getVisibleSlides();
            const newMaxIndex = getMaxIndex();

            if (newMaxIndex !== maxIndex) {
                maxIndex = newMaxIndex;

                if (currentIndex > maxIndex) {
                    currentIndex = maxIndex;
                }

                createDots();
            }

            updateSlider();
        }, 250);
    });

    // Initialisation
    createDots();
    updateSlider();
}

// ═══════════════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════════════

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.style.cssText = `
            position: fixed;
            bottom: 32px;
            right: 32px;
            padding: 16px 24px;
            background: ${type === 'error' ? '#ef4444' : '#10b981'};
            color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            font-weight: 500;
            z-index: 10000;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s;
        `;
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.style.background = type === 'error' ? '#ef4444' : '#10b981';

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
    }, 3000);
}

// ═══════════════════════════════════════════════════════════════════════════
// SMOOTH SCROLL
// ═══════════════════════════════════════════════════════════════════════════
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        const href = this.getAttribute('href');
        if (href === '#') return;

        e.preventDefault();
        const target = document.querySelector(href);

        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// SCROLL INDICATOR
// ═══════════════════════════════════════════════════════════════════════════
const scrollIndicator = document.querySelector('.scroll-indicator');
if (scrollIndicator) {
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            scrollIndicator.style.opacity = '0';
        } else {
            scrollIndicator.style.opacity = '1';
        }
    });

    scrollIndicator.addEventListener('click', function() {
        window.scrollTo({
            top: window.innerHeight,
            behavior: 'smooth'
        });
    });
}
