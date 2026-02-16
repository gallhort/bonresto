/**
 * ═══════════════════════════════════════════════════════════════════════════
 * SYSTÈME D'AVIS - LEBONRESTO
 * Features: Load More, Tri, Filtre, Vote, Photos, Réponses propriétaire
 * ═══════════════════════════════════════════════════════════════════════════
 */

class ReviewsManager {
    constructor(restaurantId) {
        this.restaurantId = restaurantId;
        this.currentOffset = 3; // On a déjà 3 avis chargés au départ
        this.limit = 5;
        this.currentSort = 'recent';
        this.currentRatingFilter = null;
        this.currentSearchQuery = '';
        this.isLoading = false;
        this.hasMore = true;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.checkInitialCount();
    }
    
    /**
     * Vérifier si on doit afficher le bouton "Voir plus"
     */
    checkInitialCount() {
        const reviewCards = document.querySelectorAll('.review-card');
        const totalReviews = parseInt(document.querySelector('[data-total-reviews]')?.dataset.totalReviews || '0');
        const loadMoreBtn = document.getElementById('loadMoreReviews');
        
        if (reviewCards.length >= totalReviews) {
            this.hasMore = false;
            if (loadMoreBtn) loadMoreBtn.style.display = 'none';
        }
    }
    
    /**
     * Attacher les événements (UNE SEULE FOIS)
     */
    bindEvents() {
        // Bouton "Voir plus"
        const loadMoreBtn = document.getElementById('loadMoreReviews');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => this.loadMore());
        }
        
        // Tri des avis
        const sortSelect = document.getElementById('reviewSort');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => this.changeSort(e.target.value));
        }
        
        // Filtre par note (clic sur les barres)
        document.querySelectorAll('[data-rating-filter]').forEach(bar => {
            bar.addEventListener('click', (e) => {
                const rating = parseInt(e.currentTarget.dataset.ratingFilter);
                this.filterByRating(rating);
            });
        });
        
        // Reset filtre
        const resetBtn = document.getElementById('resetFilter');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => this.resetFilter());
        }
        
        // ═══════════════════════════════════════════════════════════
        // EVENT DELEGATION GLOBAL (évite les doublons)
        // ═══════════════════════════════════════════════════════════
        document.addEventListener('click', (e) => {
            // Reactions (utile / drole / j'adore)
            if (e.target.closest('.reaction-btn')) {
                const btn = e.target.closest('.reaction-btn');
                const reviewId = btn.dataset.reviewId;
                const reaction = btn.dataset.reaction || 'useful';
                this.toggleReaction(reviewId, reaction, btn);
                return;
            }
            // Legacy: helpful-btn (backward compat)
            if (e.target.closest('.helpful-btn')) {
                const btn = e.target.closest('.helpful-btn');
                if (btn.disabled) return;
                const reviewId = btn.dataset.reviewId;
                this.toggleReaction(reviewId, 'useful', btn);
                return;
            }
            
            // Répondre à un avis (propriétaire)
            if (e.target.closest('.btn-respond')) {
                const btn = e.target.closest('.btn-respond');
                const reviewId = btn.dataset.reviewId;
                this.showResponseForm(reviewId);
                return;
            }
            
            // Annuler réponse
            if (e.target.closest('.btn-cancel-response')) {
                const btn = e.target.closest('.btn-cancel-response');
                this.hideResponseForm(btn);
                return;
            }
            
            // Publier réponse
            if (e.target.closest('.btn-submit-response')) {
                const btn = e.target.closest('.btn-submit-response');
                const reviewId = btn.dataset.reviewId;
                this.submitResponse(reviewId);
                return;
            }
            
            // Signaler un avis
            if (e.target.closest('.btn-report-review')) {
                const btn = e.target.closest('.btn-report-review');
                const reviewId = btn.dataset.reviewId;
                this.showReportModal(reviewId);
                return;
            }
        });
        
    }
    
    /**
     * Charger plus d'avis
     */
    async loadMore() {
        if (this.isLoading || !this.hasMore) return;
        
        this.isLoading = true;
        const btn = document.getElementById('loadMoreReviews');
        const originalText = btn.innerHTML;
        
        // Loader
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
        btn.disabled = true;
        
        try {
            const params = new URLSearchParams({
                offset: this.currentOffset,
                limit: this.limit,
                sort: this.currentSort
            });
            
            if (this.currentRatingFilter !== null) {
                params.append('rating', this.currentRatingFilter);
            }
            if (this.currentSearchQuery) {
                params.append('q', this.currentSearchQuery);
            }

            const response = await fetch(`/api/reviews/${this.restaurantId}?${params}`);
            const data = await response.json();

            if (data.success && data.reviews.length > 0) {
                this.appendReviews(data.reviews);
                this.currentOffset += data.reviews.length;
                this.hasMore = data.hasMore;
                
                if (!this.hasMore) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Tous les avis affichés';
                    setTimeout(() => btn.style.display = 'none', 2000);
                } else {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } else {
                this.hasMore = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Tous les avis affichés';
                setTimeout(() => btn.style.display = 'none', 2000);
            }
            
        } catch (error) {
            console.error('Erreur chargement avis:', error);
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erreur de chargement';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 3000);
        }
        
        this.isLoading = false;
    }
    
    /**
     * Changer le tri
     */
    async changeSort(sort) {
        this.currentSort = sort;
        this.currentOffset = 0;
        await this.reloadReviews();
    }
    
    /**
     * Filtrer par note
     */
    async filterByRating(rating) {
        // Toggle le filtre
        if (this.currentRatingFilter === rating) {
            this.currentRatingFilter = null;
        } else {
            this.currentRatingFilter = rating;
        }
        
        this.currentOffset = 0;
        
        // Mettre à jour l'UI du filtre
        this.updateFilterUI();
        
        await this.reloadReviews();
    }
    
    /**
     * Reset le filtre
     */
    async resetFilter() {
        this.currentRatingFilter = null;
        this.currentOffset = 0;
        this.updateFilterUI();
        await this.reloadReviews();
    }
    
    /**
     * Mettre à jour l'UI du filtre
     */
    updateFilterUI() {
        // Barres de stats
        document.querySelectorAll('[data-rating-filter]').forEach(bar => {
            const rating = parseInt(bar.dataset.ratingFilter);
            if (rating === this.currentRatingFilter) {
                bar.classList.add('active');
            } else {
                bar.classList.remove('active');
            }
        });
        
        // Badge filtre actif
        const filterBadge = document.getElementById('activeFilterBadge');
        if (filterBadge) {
            if (this.currentRatingFilter !== null) {
                filterBadge.innerHTML = `
                    <span class="filter-badge">
                        <i class="fas fa-filter"></i> 
                        Filtré par : ${this.currentRatingFilter} étoile${this.currentRatingFilter > 1 ? 's' : ''}
                        <button onclick="reviewsManager.resetFilter()" class="filter-badge-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </span>
                `;
                filterBadge.style.display = 'block';
            } else {
                filterBadge.style.display = 'none';
            }
        }
    }
    
    /**
     * Recharger tous les avis (après tri/filtre)
     */
    async reloadReviews() {
        const container = document.getElementById('reviewsList');
        if (!container) return;
        
        // Skeleton loader
        container.innerHTML = this.getSkeletonHTML(3);
        
        try {
            const params = new URLSearchParams({
                offset: 0,
                limit: 3,
                sort: this.currentSort
            });
            
            if (this.currentRatingFilter !== null) {
                params.append('rating', this.currentRatingFilter);
            }
            if (this.currentSearchQuery) {
                params.append('q', this.currentSearchQuery);
            }

            const response = await fetch(`/api/reviews/${this.restaurantId}?${params}`);
            const data = await response.json();
            
            if (data.success) {
                container.innerHTML = '';
                if (data.reviews.length > 0) {
                    this.appendReviews(data.reviews);
                    this.currentOffset = data.reviews.length;
                    this.hasMore = data.hasMore;
                    
                    // Afficher/cacher le bouton "Voir plus"
                    const loadMoreBtn = document.getElementById('loadMoreReviews');
                    if (loadMoreBtn) {
                        loadMoreBtn.style.display = this.hasMore ? 'block' : 'none';
                        loadMoreBtn.disabled = false;
                        loadMoreBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Voir plus d\'avis';
                    }
                } else {
                    container.innerHTML = `
                        <div class="no-reviews-message">
                            <i class="fas fa-search"></i>
                            <h4>Aucun avis trouvé</h4>
                            <p>Aucun avis ne correspond à ce filtre.</p>
                        </div>
                    `;
                    const loadMoreBtn = document.getElementById('loadMoreReviews');
                    if (loadMoreBtn) loadMoreBtn.style.display = 'none';
                }
            }
            
        } catch (error) {
            console.error('Erreur rechargement avis:', error);
            container.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Erreur lors du chargement des avis</p>
                </div>
            `;
        }
    }
    
    /**
     * Ajouter les avis au DOM
     */
    appendReviews(reviews) {
        const container = document.getElementById('reviewsList');
        if (!container) return;
        
        reviews.forEach(review => {
            const reviewCard = this.createReviewCard(review);
            container.insertAdjacentHTML('beforeend', reviewCard);
        });
        
        // Animation d'entrée
        setTimeout(() => {
            const newCards = container.querySelectorAll('.review-card:not(.animated)');
            newCards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('animated');
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }, 50);
    }
    
    /**
     * Créer le HTML d'une carte d'avis
     */
   createReviewCard(review) {
    const stars = this.getStarsHTML(review.note_globale);
    const userInitial = (review.user_prenom || 'A').charAt(0).toUpperCase();
    const userName = review.user_prenom ? 
        `${review.user_prenom} ${(review.user_nom || '').charAt(0)}.` : 
        (review.author_name || 'Anonyme');
    
    // Réponse du propriétaire
    let ownerResponseHTML = '';
    if (review.owner_response) {
        ownerResponseHTML = `
            <div class="owner-response">
                <div class="owner-response-header">
                    <div class="owner-badge">
                        <i class="fas fa-store"></i>
                        <span>Réponse du propriétaire</span>
                    </div>
                    <span class="owner-response-date">${review.date_relative || 'Récemment'}</span>
                </div>
                <p class="owner-response-text">${this.escapeHtml(review.owner_response)}</p>
            </div>
        `;
    }
    
    // Badge "Visite confirmée" (check-in GPS)
    const checkinBadge = review.has_checkin ?
        '<span class="review-checkin-badge"><i class="fas fa-location-dot"></i> Visite confirmée</span>' : '';

    // Badge "Avec photos"
    const photoBadge = (review.photos && review.photos.length > 0) ?
        '<span class="review-photo-badge"><i class="fas fa-camera"></i> Avec photos</span>' : '';
    
    // ⭐ NOUVEAU : Section photos
    let photosHTML = '';
    if (review.photos && Array.isArray(review.photos) && review.photos.length > 0) {
        photosHTML = '<div class="review-photos">';
        review.photos.forEach((photo, index) => {
            if (photo.photo_path) {
                photosHTML += `
                    <div class="review-photo-item" onclick="openLightbox(${review.id}, ${index})" data-review-id="${review.id}" data-photo-index="${index}">
                        <img loading="lazy" src="/${this.escapeHtml(photo.photo_path)}" alt="Photo avis ${index + 1}" loading="lazy">
                        ${index === 0 && review.photos.length > 1 ? 
                            `<span class="review-photo-count">+${review.photos.length - 1}</span>` : 
                            ''
                        }
                    </div>
                `;
            }
        });
        photosHTML += '</div>';
    }
    
    // Bouton "Répondre" pour le propriétaire
    const isOwner = document.querySelector('.owner-banner') !== null;
    let respondButtonHTML = '';
    if (isOwner && !review.owner_response) {
        respondButtonHTML = `
            <button class="btn-respond" data-review-id="${review.id}">
                <i class="fas fa-reply"></i> Répondre à cet avis
            </button>
        `;
    }
    
    return `
        <article class="review-card" data-review-id="${review.id}" style="opacity: 0; transform: translateY(20px); transition: all 0.4s ease;">
            <div class="review-header">
                <div class="review-avatar">
                    ${review.user_photo ? 
                        `<img loading="lazy" src="/${review.user_photo}" alt="${userName}">` : 
                        userInitial
                    }
                </div>
                <div class="review-author">
                    <div class="review-author-name">${this.escapeHtml(userName)}</div>
                    <div class="review-author-meta">
                        ${review.user_ville ? `<span><i class="fas fa-map-marker-alt"></i> ${this.escapeHtml(review.user_ville)}</span>` : ''}
                        ${review.user_total_reviews ? `<span>${review.user_total_reviews} avis</span>` : ''}
                        ${checkinBadge}
                        ${photoBadge}
                    </div>
                </div>
                <div class="review-rating">
                    <div class="stars">${stars}</div>
                </div>
            </div>
            
            ${review.title ? `<h4 class="review-title">${this.escapeHtml(review.title)}</h4>` : ''}
            ${review.message ? `<p class="review-content">${this.escapeHtml(review.message).replace(/\n/g, '<br>')}</p>` : ''}

            ${review.pros ? `<div style="margin-top:10px;padding:8px 12px;background:#f0fdf4;border-radius:8px;font-size:14px"><strong style="color:#16a34a"><i class="fas fa-check-circle"></i> Points forts :</strong> <span>${this.escapeHtml(review.pros).replace(/\n/g, '<br>')}</span></div>` : ''}
            ${review.cons ? `<div style="margin-top:6px;padding:8px 12px;background:#fef3c7;border-radius:8px;font-size:14px"><strong style="color:#d97706"><i class="fas fa-exclamation-circle"></i> Points faibles :</strong> <span>${this.escapeHtml(review.cons).replace(/\n/g, '<br>')}</span></div>` : ''}

            ${photosHTML}
            
            <div class="review-visit">
                <span><i class="far fa-calendar"></i> ${review.date_relative || 'Récemment'}</span>
                ${review.trip_type ? `<span><i class="fas fa-users"></i> ${this.escapeHtml(review.trip_type)}</span>` : ''}
                ${review.source !== 'site' ? `<span><i class="fas fa-external-link-alt"></i> ${this.capitalize(review.source)}</span>` : ''}
            </div>
            
            ${ownerResponseHTML}
            ${respondButtonHTML}
            
            <div class="review-helpful">
                <span class="reactions-label">Reactions :</span>
                <button class="reaction-btn" data-review-id="${review.id}" data-reaction="useful">
                    <i class="far fa-thumbs-up"></i> Utile <span class="vote-count">${review.votes_utiles || 0}</span>
                </button>
                <button class="reaction-btn" data-review-id="${review.id}" data-reaction="funny">
                    <i class="far fa-face-laugh"></i> Drole <span class="vote-count">${review.votes_funny || 0}</span>
                </button>
                <button class="reaction-btn" data-review-id="${review.id}" data-reaction="love">
                    <i class="far fa-heart"></i> J'adore <span class="vote-count">${review.votes_love || 0}</span>
                </button>
                <button class="btn-report-review" data-review-id="${review.id}" style="margin-left:auto">
                    <i class="fas fa-flag"></i> Signaler
                </button>
            </div>
        </article>
    `;
}
    
    /**
     * Toggle a reaction (useful/funny/love) on a review
     */
    async toggleReaction(reviewId, reaction, btn) {
        try {
            const response = await fetch(`/api/reviews/${reviewId}/vote`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: JSON.stringify({ reaction })
            });

            const data = await response.json();

            if (data.success) {
                btn.classList.toggle('active', data.toggled);
                const countSpan = btn.querySelector('.vote-count');
                if (countSpan && data.counts) {
                    countSpan.textContent = data.counts[reaction] || 0;
                }
                btn.style.transform = 'scale(1.15)';
                setTimeout(() => btn.style.transform = 'scale(1)', 200);
            } else {
                this.showNotification(data.error || 'Erreur lors du vote', 'error');
            }
        } catch (error) {
            this.showNotification('Erreur lors du vote', 'error');
        }
    }
    
    /**
     * Afficher le formulaire de réponse propriétaire
     */
    showResponseForm(reviewId) {
        const reviewCard = document.querySelector(`.review-card[data-review-id="${reviewId}"]`);
        if (!reviewCard) {
            console.error('❌ Review card introuvable');
            return;
        }
        
        // Vérifier si un formulaire n'existe pas déjà
        if (reviewCard.querySelector('.response-form')) {
            return;
        }
        
        // Cacher le bouton "Répondre"
        const respondBtn = reviewCard.querySelector('.btn-respond');
        if (respondBtn) respondBtn.style.display = 'none';
        
        // Get review rating to suggest appropriate templates
        const rating = parseInt(reviewCard.dataset.rating) || 3;
        const templateCategory = rating >= 4 ? 'positive' : (rating === 3 ? 'neutral' : 'negative');

        // Créer le formulaire avec suggestions IA
        const formHTML = `
            <div class="response-form" data-review-id="${reviewId}">
                <div class="response-form-header">
                    <h5><i class="fas fa-reply"></i> Répondre à cet avis</h5>
                </div>
                <div class="response-templates" data-category="${templateCategory}">
                    <div class="response-templates-header">
                        <i class="fas fa-magic" style="color:#8b5cf6"></i>
                        <span>Suggestions de réponse</span>
                        <button class="response-templates-toggle" onclick="this.closest('.response-templates').classList.toggle('collapsed')">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                    </div>
                    <div class="response-templates-list" id="templatesList_${reviewId}">
                        <p style="color:#9ca3af;font-size:13px;padding:8px"><i class="fas fa-spinner fa-spin"></i> Chargement...</p>
                    </div>
                </div>
                <textarea
                    class="response-textarea"
                    placeholder="Rédigez votre réponse... (min. 20 caractères)"
                    rows="4"
                ></textarea>
                <div class="response-form-actions">
                    <button class="btn-cancel-response">
                        <i class="fas fa-times"></i> Annuler
                    </button>
                    <button class="btn-submit-response" data-review-id="${reviewId}">
                        <i class="fas fa-paper-plane"></i> Publier la réponse
                    </button>
                </div>
            </div>
        `;

        // Insérer le formulaire AVANT de charger les templates
        const helpfulSection = reviewCard.querySelector('.review-helpful');
        if (helpfulSection) {
            helpfulSection.insertAdjacentHTML('beforebegin', formHTML);

            // Load templates via API (after DOM insertion so container exists)
            this.loadResponseTemplates(reviewId, templateCategory);
            
            // Focus sur le textarea
            setTimeout(() => {
                const textarea = reviewCard.querySelector('.response-textarea');
                if (textarea) {
                    textarea.focus();
                }
            }, 100);
        }
    }
    
    /**
     * Cacher le formulaire de réponse
     */
    hideResponseForm(btn) {
        const form = btn.closest('.response-form');
        if (!form) return;
        
        const reviewId = form.dataset.reviewId;
        const reviewCard = document.querySelector(`.review-card[data-review-id="${reviewId}"]`);
        
        // Réafficher le bouton "Répondre"
        const respondBtn = reviewCard?.querySelector('.btn-respond');
        if (respondBtn) respondBtn.style.display = 'inline-flex';
        
        // Supprimer le formulaire avec animation
        form.style.opacity = '0';
        form.style.transform = 'translateY(-10px)';
        setTimeout(() => form.remove(), 300);
    }
    
    /**
     * Soumettre une réponse propriétaire
     */
    async submitResponse(reviewId) {
        const form = document.querySelector(`.response-form[data-review-id="${reviewId}"]`);
        if (!form) return;
        
        const textarea = form.querySelector('.response-textarea');
        const submitBtn = form.querySelector('.btn-submit-response');
        const response = textarea.value.trim();
        
        // Validation
        if (response.length < 20) {
            this.showNotification('Votre réponse doit contenir au moins 20 caractères', 'error');
            textarea.focus();
            return;
        }
        
        // Loader
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication...';
        submitBtn.disabled = true;
        
        try {
            const res = await fetch(`/api/reviews/${reviewId}/respond`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ response })
            });
            
            const data = await res.json();
            
            if (data.success) {
                // Supprimer le formulaire
                form.remove();
                
                // Afficher la réponse immédiatement
                const reviewCard = document.querySelector(`.review-card[data-review-id="${reviewId}"]`);
                const helpfulSection = reviewCard?.querySelector('.review-helpful');
                
                if (helpfulSection) {
                    const ownerResponseHTML = `
                        <div class="owner-response" style="opacity: 0; transform: translateY(-10px); transition: all 0.3s ease;">
                            <div class="owner-response-header">
                                <div class="owner-badge">
                                    <i class="fas fa-store"></i>
                                    <span>Réponse du propriétaire</span>
                                </div>
                                <span class="owner-response-date">À l'instant</span>
                            </div>
                            <p class="owner-response-text">${this.escapeHtml(response)}</p>
                        </div>
                    `;
                    
                    helpfulSection.insertAdjacentHTML('beforebegin', ownerResponseHTML);
                    
                    // Animation d'entrée
                    setTimeout(() => {
                        const newResponse = reviewCard.querySelector('.owner-response');
                        if (newResponse) {
                            newResponse.style.opacity = '1';
                            newResponse.style.transform = 'translateY(0)';
                        }
                    }, 50);
                }
                
                // Notification de succès
                this.showNotification('Réponse publiée avec succès ! 🎉', 'success');
                
            } else {
                this.showNotification(data.error || 'Erreur lors de la publication', 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
            
        } catch (error) {
            console.error('Erreur soumission réponse:', error);
            this.showNotification('Erreur lors de la publication', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
    
    /**
     * Afficher modal signalement
     */
    showReportModal(reviewId) {
        const modalHTML = `
            <div class="report-modal-overlay active" id="reportModal">
                <div class="report-modal">
                    <button class="report-modal-close" onclick="document.getElementById('reportModal').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                    
                    <div class="report-modal-header">
                        <i class="fas fa-flag"></i>
                        <h3>Signaler cet avis</h3>
                        <p>Aidez-nous à maintenir un environnement sûr et respectueux</p>
                    </div>
                    
                    <div class="report-modal-body">
                        <input type="hidden" id="reportReviewId" value="${reviewId}">
                        
                        <div class="form-group">
                            <label>Raison du signalement *</label>
                            <select id="reportReason" class="form-control" required>
                                <option value="">-- Sélectionnez une raison --</option>
                                <option value="spam">Spam ou publicité</option>
                                <option value="offensive">Contenu offensant ou inapproprié</option>
                                <option value="fake">Avis manifestement faux</option>
                                <option value="harassment">Harcèlement</option>
                                <option value="personal">Informations personnelles</option>
                                <option value="copyright">Violation de droits d'auteur</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Détails supplémentaires (optionnel)</label>
                            <textarea 
                                id="reportDetails" 
                                class="form-control" 
                                rows="4" 
                                placeholder="Donnez plus de contexte sur ce signalement..."
                            ></textarea>
                        </div>
                        
                        <div class="report-warning">
                            <i class="fas fa-info-circle"></i>
                            <span>Les signalements abusifs peuvent entraîner des sanctions sur votre compte.</span>
                        </div>
                    </div>
                    
                    <div class="report-modal-footer">
                        <button class="btn btn-outline" onclick="document.getElementById('reportModal').remove()">Annuler</button>
                        <button class="btn btn-danger" onclick="reviewsManager.submitReport()">
                            <i class="fas fa-flag"></i> Envoyer le signalement
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    /**
     * Soumettre un signalement
     */
    async submitReport() {
        const reviewId = document.getElementById('reportReviewId').value;
        const reason = document.getElementById('reportReason').value;
        const details = document.getElementById('reportDetails').value.trim();
        
        if (!reason) {
            this.showNotification('Veuillez sélectionner une raison', 'error');
            return;
        }
        
        try {
            const response = await fetch(`/api/reviews/${reviewId}/report`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason, details })
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('reportModal').remove();
                this.showNotification('Signalement envoyé avec succès', 'success');
                
                const reportBtn = document.querySelector(`[data-review-id="${reviewId}"].btn-report-review`);
                if (reportBtn) {
                    reportBtn.classList.add('reported');
                    reportBtn.innerHTML = '<i class="fas fa-check"></i> Signalé';
                    reportBtn.disabled = true;
                }
            } else {
                this.showNotification(data.error || 'Erreur lors du signalement', 'error');
            }
        } catch (error) {
            console.error('Erreur signalement:', error);
            this.showNotification('Erreur lors de l\'envoi du signalement', 'error');
        }
    }
    
    /**
     * Afficher une notification toast
     */
    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `toast-notification toast-${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Retrait après 4s
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
    
    /**
     * Générer HTML des étoiles
     */
    getStarsHTML(rating) {
        const fullStars = Math.floor(rating);
        let html = '';
        for (let i = 0; i < 5; i++) {
            html += i < fullStars ? 
                '<i class="fas fa-star"></i>' : 
                '<i class="fas fa-star empty"></i>';
        }
        return html;
    }
    
    /**
     * Skeleton loader
     */
    getSkeletonHTML(count = 3) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="review-card skeleton">
                    <div class="skeleton-header">
                        <div class="skeleton-avatar"></div>
                        <div class="skeleton-author">
                            <div class="skeleton-line"></div>
                            <div class="skeleton-line short"></div>
                        </div>
                    </div>
                    <div class="skeleton-content">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            `;
        }
        return html;
    }
    
    /**
     * Charger les templates de réponse IA
     */
    async loadResponseTemplates(reviewId, category) {
        try {
            const res = await fetch(`/api/response-templates?category=${category}`);
            const data = await res.json();
            const container = document.getElementById(`templatesList_${reviewId}`);
            if (!container || !data.success) return;

            const reviewCard = document.querySelector(`.review-card[data-review-id="${reviewId}"]`);
            const textarea = reviewCard?.querySelector('.response-textarea');

            let html = '';
            (data.templates || []).forEach((t, i) => {
                html += `<button class="response-template-btn" data-index="${i}" title="Cliquez pour utiliser ce modele">${this.escapeHtml(t.template_fr)}</button>`;
            });
            container.innerHTML = html;

            container.querySelectorAll('.response-template-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (textarea) {
                        textarea.value = btn.textContent;
                        textarea.focus();
                        textarea.style.borderColor = '#8b5cf6';
                        setTimeout(() => textarea.style.borderColor = '', 1000);
                    }
                });
            });
        } catch (e) {
            const container = document.getElementById(`templatesList_${reviewId}`);
            if (container) container.innerHTML = '';
        }
    }

    /**
     * Utilitaires
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    capitalize(text) {
        return text.charAt(0).toUpperCase() + text.slice(1);
    }
}

// ═══════════════════════════════════════════════════════════════
// INITIALISATION GLOBALE
// ═══════════════════════════════════════════════════════════════
let reviewsManager;
document.addEventListener('DOMContentLoaded', () => {
    const section = document.querySelector('[data-restaurant-id]');
    if (section) {
        const restaurantId = section.dataset.restaurantId;
        reviewsManager = new ReviewsManager(restaurantId);
    } else {
        console.warn('⚠️ Section avis non trouvée (normal si pas d\'avis)');
    }
});