<?php

/**
 * Application Routes
 * Définition de toutes les routes de l'application
 */

// Page d'accueil
$router->get('/', 'HomeController@index');

// SEO
$router->get('/sitemap.xml', 'SitemapController@index');

// Pages publiques - Restaurants
$router->get('/search', 'RestaurantController@search');
$router->post('/search', 'RestaurantController@search');
$router->get('/restaurant/{id}', 'RestaurantController@show');
$router->get('/restaurants', 'RestaurantController@index');

// ============================================
// REVIEWS - AJOUTER UN AVIS
// ============================================
$router->get('/restaurant/{id}/review', 'ReviewController@create');
$router->post('/restaurant/{id}/review', 'ReviewController@store');
$router->get('/api/reviews/{id}', 'ReviewController@apiLoadReviews');
$router->post('/api/reviews/{id}/vote', 'ReviewController@apiVoteHelpful');

// Authentification
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@doLogin');
$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@doRegister');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPassword');
$router->post('/forgot-password', 'AuthController@doForgotPassword');
$router->get('/reset-password', 'AuthController@resetPassword');
$router->post('/reset-password', 'AuthController@doResetPassword');

// Email verification
$router->get('/verify-email', 'AuthController@verifyEmail');
$router->post('/resend-verification', 'AuthController@resendVerification');

// Profil utilisateur
$router->get('/profil', 'UserController@profile');
$router->post('/profil', 'UserController@updateProfile');
$router->post('/profil/password', 'UserController@updatePassword');
$router->get('/user/{id}', 'UserController@publicProfile');
$router->get('/dashboard', 'UserController@dashboard');

// Revendiquer un restaurant
$router->get('/restaurant/{id}/claim', 'UserController@claimRestaurant');
$router->post('/restaurant/{id}/claim', 'UserController@doClaimRestaurant');

// API Notifications & dashboard proprio
$router->get('/api/my-reviews', 'UserController@apiMyRestaurantReviews');
$router->get('/api/notifications', 'UserController@apiNotifications');
$router->post('/api/notifications/read', 'UserController@apiMarkNotificationsRead');

// Wishlist
$router->get('/wishlist', 'WishlistController@index');
$router->post('/wishlist/add', 'WishlistController@add');
$router->post('/wishlist/remove', 'WishlistController@remove');

// Inscription restaurant
$router->get('/add-restaurant', 'RestaurantController@create');
$router->post('/add-restaurant', 'RestaurantController@store');

// Admin - Dashboard
$router->get('/admin', 'Admin\DashboardController@index');
$router->get('/admin/dashboard', 'Admin\DashboardController@index');
$router->get('/admin/export-stats', 'Admin\DashboardController@exportStats');
$router->get('/admin/api/chart-data', 'Admin\DashboardController@getChartDataApi');
$router->get('/dashboard/restaurant/{id}', 'UserController@restaurantStats');

$router->get('/admin/restaurant/{id}/stats', 'Admin\AnalyticsController@restaurantStats');
// Admin - Restaurants
$router->get('/admin/restaurants', 'Admin\DashboardController@restaurants');
$router->get('/admin/analytics/search', 'Admin\AnalyticsController@searchRestaurant');
// Admin - Reviews
$router->get('/admin/reviews', 'Admin\DashboardController@reviews');
$router->get('/admin/reviews/ai-stats', 'Admin\DashboardController@aiStatsPage');
$router->post('/admin/review/approve', 'Admin\DashboardController@approveReview');
$router->post('/admin/review/reject', 'Admin\DashboardController@rejectReview');
$router->post('/admin/review/override-ai', 'Admin\DashboardController@overrideAiDecision');
$router->get('/admin/api/review-details', 'Admin\DashboardController@getReviewDetails');
$router->get('/admin/api/ai-stats', 'Admin\DashboardController@getAiStats');
$router->post('/admin/reviews/bulk-approve', 'Admin\DashboardController@bulkApproveReviews');
$router->post('/admin/reviews/bulk-reject', 'Admin\DashboardController@bulkRejectReviews');
$router->post('/admin/reviews/bulk-delete', 'Admin\DashboardController@bulkDeleteReviews');
$router->get('/admin/reviews/export', 'Admin\DashboardController@exportReviews');

// Admin - Other Pages
$router->get('/admin/users', 'Admin\AdminPagesController@users');
$router->get('/admin/analytics', 'Admin\AnalyticsController@index');
$router->get('/admin/settings', 'Admin\AdminPagesController@settings');
$router->get('/admin/contacts', 'Admin\AdminPagesController@contacts');
$router->post('/admin/contacts/reply', 'Admin\AdminPagesController@replyContact');
$router->get('/admin/moderation-log', 'Admin\AdminPagesController@moderationLog');

// API endpoints
$router->get('/api/villes', 'RestaurantController@autocompleteVilles');
$router->get('/api/wilayas', 'RestaurantController@autocompleteWilayas');
$router->get('/api/types-cuisine', 'RestaurantController@typesCuisine');

$router->get('/admin/restaurants/pending', 'Admin\RestaurantController@pending');
$router->get('/admin/restaurants/validated', 'Admin\RestaurantController@validated');
$router->get('/admin/restaurants/{id}/view', 'Admin\RestaurantController@view');
$router->post('/admin/restaurants/{id}/validate', 'Admin\RestaurantController@validate');
$router->post('/admin/restaurants/{id}/reject', 'Admin\RestaurantController@reject');
$router->get('/admin/restaurants/{id}/edit', 'Admin\RestaurantController@edit');
$router->post('/admin/restaurants/{id}/update', 'Admin\RestaurantController@update');
$router->post('/admin/restaurants/{id}/toggle-featured', 'Admin\RestaurantController@toggleFeatured');

// ============================================
// API - RESTAURANTS
// ============================================
$router->get('/api/restaurants', 'Api\RestaurantController@index');
// Routes spécifiques AVANT le pattern {id}
$router->get('/api/restaurants/filter', 'RestaurantController@apiFilter');
$router->get('/api/restaurants/map-bounds', 'RestaurantController@apiMapBounds');
$router->get('/api/restaurants/{id}', 'Api\RestaurantController@show');

// API Cities
$router->get('/api/cities/search', 'Api\CitiesApiController@search');
$router->get('/api/cities/get', 'Api\CitiesApiController@get');

// API Search Autocomplete (villes + restaurants)
$router->get('/api/search/autocomplete', 'Api\SearchApiController@autocomplete');

 // Routes Wishlist API
$router->post('/api/wishlist/toggle', 'WishlistController@toggle');
$router->get('/api/wishlist/check/{id}', 'WishlistController@check');
$router->post('/api/wishlist/check-multiple', 'WishlistController@checkMultiple');
$router->get('/api/wishlist', 'WishlistController@list');
$router->post('/api/wishlist/remove', 'WishlistController@remove');  // POST au lieu de DELETE
$router->get('/api/wishlist/count', 'WishlistController@count');

// Review edit
$router->get('/review/{id}/edit', 'ReviewController@edit');
$router->post('/api/reviews/{id}/update', 'ReviewController@apiUpdateReview');

// Q&A - Questions & Réponses
$router->post('/api/restaurant/{id}/question', 'RestaurantController@apiAskQuestion');
$router->post('/api/question/{id}/answer', 'RestaurantController@apiAnswerQuestion');

// API - Répondre à un avis (propriétaire uniquement)
$router->post('/api/reviews/{id}/respond', 'ReviewController@apiRespondToReview');
$router->post('/api/reviews/{id}/report', 'ReviewController@apiReportReview');

// ============================================
// API - ANALYTICS (Tracking des événements)
// ============================================
$router->post('/api/analytics/track', 'Api\AnalyticsController@track');
$router->get('/api/analytics/restaurant/{id}', 'Api\AnalyticsController@getRestaurantStats');

$router->get('/admin/analytics/export', 'Admin\\AnalyticsController@exportCsv');

$router->get('/api/check-email', 'AuthController@checkEmail');

// ═══════════════════════════════════════════════════════════════════════════
// ROUTES FIDÉLITÉ (à ajouter dans config/routes.php)
// ═══════════════════════════════════════════════════════════════════════════

// Pages
$router->get('/fidelite', 'LoyaltyController@index');
$router->get('/loyalty', 'LoyaltyController@index');  // Alias anglais
$router->get('/fidelite/coupons', 'LoyaltyController@coupons');

// API Fidélité
$router->get('/api/loyalty/stats', 'LoyaltyController@getStats');
$router->get('/api/loyalty/leaderboard', 'LoyaltyController@getLeaderboard');
$router->get('/api/loyalty/history', 'LoyaltyController@getHistory');
$router->get('/api/loyalty/widget', 'LoyaltyController@widget');
$router->post('/api/loyalty/use-coupon', 'LoyaltyController@useCoupon');
$router->post('/api/loyalty/claim-coupon', 'LoyaltyController@claimCoupon');

// Contact
$router->get('/contact', 'ContactController@index');
$router->post('/contact', 'ContactController@send');

// Pages légales
$router->get('/cgu', 'LegalController@cgu');
$router->get('/confidentialite', 'LegalController@confidentialite');

// Suppression de compte (RGPD)
$router->post('/profil/delete-account', 'UserController@deleteAccount');

// ═══════════════════════════════════════════════════════════════════════════
// PHASE 12 - NOUVELLES FEATURES
// ═══════════════════════════════════════════════════════════════════════════

// Check-in géo
$router->post('/api/restaurants/{id}/checkin', 'CheckinController@store');
$router->get('/api/restaurants/{id}/checkin-status', 'CheckinController@status');
$router->get('/api/checkins', 'CheckinController@history');

// Collections publiques
$router->get('/collections', 'CollectionController@index');
$router->get('/collections/{id}', 'CollectionController@show');
$router->get('/mes-collections', 'CollectionController@myCollections');
$router->post('/api/collections', 'CollectionController@apiCreate');
$router->get('/api/my-collections', 'CollectionController@apiMyCollections');
$router->post('/api/collections/{id}/add', 'CollectionController@apiAddRestaurant');
$router->post('/api/collections/{id}/remove', 'CollectionController@apiRemoveRestaurant');
$router->post('/api/collections/{id}/delete', 'CollectionController@apiDelete');

// Réservations en ligne
$router->post('/api/restaurants/{id}/reservation', 'ReservationController@store');
$router->post('/api/reservations/{id}/respond', 'ReservationController@respond');
$router->post('/api/reservations/{id}/cancel', 'ReservationController@cancel');
$router->get('/api/my-reservations', 'ReservationController@myReservations');
$router->get('/api/owner/reservations', 'ReservationController@ownerReservations');

// Owner - Gestion restaurant
$router->get('/owner/restaurant/{id}/edit', 'OwnerController@edit');
$router->post('/api/owner/restaurant/{id}/update', 'OwnerController@apiUpdate');
$router->post('/api/owner/restaurant/{id}/hours', 'OwnerController@apiUpdateHours');
$router->post('/api/owner/restaurant/{id}/menu', 'OwnerController@apiUpdateMenu');
$router->post('/api/owner/restaurant/{id}/toggle-reservations', 'OwnerController@apiToggleReservations');

// Fil d'actualité & Suggestions
$router->get('/feed', 'FeedController@index');
$router->get('/api/feed', 'FeedController@apiFeed');
$router->get('/api/suggestions', 'FeedController@apiSuggestions');

// API publique documentée
$router->get('/api/docs', 'ApiDocsController@index');

// ═══════════════════════════════════════════════════════════════════════════
// PHASE 13 - FEATURES NICE-TO-HAVE
// ═══════════════════════════════════════════════════════════════════════════

// Quick Tips
$router->get('/api/restaurants/{id}/tips', 'TipController@list');
$router->post('/api/restaurants/{id}/tip', 'TipController@store');
$router->post('/api/tips/{id}/vote', 'TipController@vote');

// Follow utilisateurs
$router->post('/api/users/{id}/follow', 'FollowController@toggle');
$router->get('/api/users/{id}/follow-status', 'FollowController@status');
$router->get('/api/users/{id}/followers', 'FollowController@followers');

// Programme parrainage
$router->get('/parrainage', 'ReferralController@index');

// Classement contributeurs
$router->get('/classement', 'LeaderboardController@index');
$router->get('/classement/{ville}', 'LeaderboardController@index');

// Proposer un restaurant (suggestions communautaires)
$router->get('/proposer-restaurant', 'SuggestionController@index');
$router->post('/proposer-restaurant', 'SuggestionController@store');

// Admin - Suggestions
$router->get('/admin/suggestions', 'SuggestionController@adminList');
$router->post('/admin/suggestions/{id}/approve', 'SuggestionController@adminApprove');
$router->post('/admin/suggestions/{id}/reject', 'SuggestionController@adminReject');

// ═══════════════════════════════════════════════════════════════════════════
// PHASE 16 - ACTIVITES & SORTIES
// ═══════════════════════════════════════════════════════════════════════════

// Browse / Search
$router->get('/activites', 'ActivityController@index');
$router->get('/api/activities/filter', 'ActivityController@apiFilter');

// Detail page (slug or id)
$router->get('/activite/{id}', 'ActivityController@show');

// Reviews
$router->post('/activite/{id}/review', 'ActivityController@storeReview');

// Tips
$router->post('/api/activite/{id}/tip', 'ActivityController@storeTip');

// Wishlist
$router->post('/api/activity-wishlist/toggle', 'ActivityController@toggleWishlist');

// Check-in
$router->post('/api/activite/{id}/checkin', 'ActivityController@checkin');

// ═══════════════════════════════════════════════════════════════════════════
// PHASE 17 - AUDIT FIXES + NEW FEATURES
// ═══════════════════════════════════════════════════════════════════════════

// Restaurant Offers (TheFork-style deals)
$router->get('/api/restaurant/{id}/offers', 'OfferController@apiList');
$router->post('/api/owner/restaurant/{id}/offer', 'OfferController@apiStore');
$router->post('/api/owner/restaurant/{id}/offer/delete', 'OfferController@apiDelete');

// Review Summaries API
$router->get('/api/restaurant/{id}/summary', 'RestaurantController@apiReviewSummary');

// ═══════════════════════════════════════════════════════════════════════════
// F33 - COMMANDE EN LIGNE
// ═══════════════════════════════════════════════════════════════════════════

// Page commande client
$router->get('/commander/{slug}', 'OrderController@orderPage');

// API - Soumission de commande
$router->post('/api/orders', 'OrderController@store');

// API - Dashboard owner (commandes entrantes)
$router->get('/api/owner/restaurant/{id}/orders', 'OrderController@ownerOrders');
$router->post('/api/owner/orders/{id}/respond', 'OrderController@respond');
$router->post('/api/owner/orders/{id}/status', 'OrderController@updateStatus');

// API - Historique client
$router->get('/mes-commandes', 'OrderController@myOrders');
$router->get('/api/my-orders', 'OrderController@myOrdersApi');
$router->get('/api/orders/{id}', 'OrderController@orderDetail');
$router->post('/api/orders/{id}/cancel', 'OrderController@cancelOrder');

// ═══════════════════════════════════════════════════════════════════════════
// SPRINT 3 - INTERACTION & ENGAGEMENT
// ═══════════════════════════════════════════════════════════════════════════

// F26 - Response templates (AI-assisted)
$router->get('/api/response-templates', 'ReviewController@apiResponseTemplates');

// F13 - Restaurant posts / feed
$router->get('/restaurant/{id}/posts', 'PostController@feed');
$router->get('/api/restaurant/{id}/posts', 'PostController@apiList');
$router->post('/api/restaurant/{id}/posts', 'PostController@store');
$router->post('/api/restaurant/posts/{id}/delete', 'PostController@delete');
$router->post('/api/restaurant/posts/{id}/like', 'PostController@toggleLike');
$router->post('/api/restaurant/posts/{id}/pin', 'PostController@togglePin');

// ═══════════════════════════════════════════════════════════════════════════
// SPRINT 4 - VALEUR AJOUTEE
// ═══════════════════════════════════════════════════════════════════════════

// F9 - Classement popularite restaurants
$router->get('/classement-restaurants', 'RestaurantController@ranking');

// F19 - Comparateur de restaurants
$router->get('/comparateur', 'ComparatorController@index');
$router->get('/api/comparateur', 'ComparatorController@apiCompare');

// F24 - Statistiques publiques
$router->get('/stats', 'StatsController@index');
$router->get('/stats/{ville}', 'StatsController@cityStats');

// F25 - Internal messaging
$router->get('/messages', 'MessageController@inbox');
$router->get('/messages/sent', 'MessageController@sent');
$router->get('/messages/conversation/{id}', 'MessageController@conversation');
$router->post('/api/messages/send', 'MessageController@send');
$router->get('/api/messages/unread-count', 'MessageController@apiUnreadCount');
$router->post('/api/messages/{id}/delete', 'MessageController@delete');

// ═══════════════════════════════════════════
// SPRINT 5: Social & Engagement
// ═══════════════════════════════════════════

// F22 - Partage social
$router->get('/api/share/card', 'ShareController@shareCard');
$router->post('/api/share/log', 'ShareController@logShare');

// F23 - Widget embeddable
$router->get('/widget/{token}', 'WidgetController@embed');
$router->post('/api/widget/create', 'WidgetController@create');
$router->get('/api/widget/{restaurantId}/code', 'WidgetController@apiGetCode');

// F20 - Newsletter
$router->post('/api/newsletter/subscribe', 'NewsletterController@subscribe');
$router->get('/newsletter/unsubscribe/{token}', 'NewsletterController@unsubscribe');
$router->post('/api/newsletter/preferences', 'NewsletterController@preferences');

// F21 - Push notifications
$router->post('/api/push/subscribe', 'PushController@subscribe');
$router->post('/api/push/unsubscribe', 'PushController@unsubscribe');
$router->get('/api/push/vapid-key', 'PushController@getVapidKey');

// ═══════════════════════════════════════════
// SPRINT 6: Enrichissement
// ═══════════════════════════════════════════

// F14 - Allergènes
$router->get('/api/allergens', 'AllergenController@list');
$router->get('/api/menu-item/{id}/allergens', 'AllergenController@getForItem');
$router->post('/api/menu-item/{id}/allergens', 'AllergenController@update');

// F27 - Préférences utilisateur
$router->get('/preferences', 'PreferencesController@page');
$router->get('/api/preferences', 'PreferencesController@get');
$router->post('/api/preferences', 'PreferencesController@update');

// F10/F11 - Rappels réservation + No-show
$router->get('/api/cron/reservation-reminders', 'ReservationExtController@sendReminders');
$router->post('/api/reservations/{id}/no-show', 'ReservationExtController@markNoShow');
$router->get('/api/users/{id}/reliability', 'ReservationExtController@getUserReliability');

// ═══════════════════════════════════════════
// SPRINT 7: Gros chantiers
// ═══════════════════════════════════════════

// F34 - Événements culinaires
$router->get('/evenements', 'EventController@index');
$router->get('/evenement/{id}', 'EventController@show');
$router->post('/api/events', 'EventController@store');
$router->post('/api/events/{id}/register', 'EventController@register');
$router->post('/api/events/{id}/cancel-registration', 'EventController@cancelRegistration');
$router->get('/api/owner/events', 'EventController@ownerEvents');

// F29 - Liste d'attente
$router->post('/api/restaurants/{id}/waitlist', 'WaitlistController@join');
$router->get('/api/waitlist/{id}/status', 'WaitlistController@status');
$router->post('/api/waitlist/{id}/notify', 'WaitlistController@notify');
$router->post('/api/waitlist/{id}/seat', 'WaitlistController@seat');
$router->post('/api/waitlist/{id}/cancel', 'WaitlistController@cancel');
$router->get('/api/owner/restaurant/{id}/waitlist', 'WaitlistController@ownerList');

// F17 - Questionnaire post-visite
$router->get('/survey/{reservationId}', 'SurveyController@show');
$router->post('/api/surveys', 'SurveyController@submit');
$router->get('/api/restaurants/{id}/survey-results', 'SurveyController@results');

// F18 - Alerte disponibilité
$router->post('/api/availability-alerts', 'AlertController@create');
$router->get('/api/my-alerts', 'AlertController@myAlerts');
$router->post('/api/availability-alerts/{id}/delete', 'AlertController@delete');
$router->get('/api/cron/check-availability', 'AlertController@checkAndNotify');

// F35 - Premium
$router->get('/premium', 'PremiumController@plans');
$router->post('/api/premium/subscribe', 'PremiumController@subscribe');
$router->post('/api/premium/cancel', 'PremiumController@cancel');
$router->get('/api/premium/my-subscription', 'PremiumController@mySubscription');

// F30 - Heures de pointe
$router->get('/api/restaurants/{id}/peak-hours', 'PeakHoursController@getForRestaurant');

// F32 - AI Concierge
$router->get('/concierge', 'ConciergeController@chat');
$router->post('/api/concierge/ask', 'ConciergeController@ask');

// F31 - Avis vidéo/audio
$router->post('/api/reviews/{id}/media', 'MediaReviewController@upload');
$router->post('/api/reviews/{id}/media/delete', 'MediaReviewController@delete');

// F28 - i18n
$router->post('/api/locale', 'TranslationController@setLocale');
$router->get('/api/translations/{locale}', 'TranslationController@getTranslations');