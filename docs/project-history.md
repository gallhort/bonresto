# LeBonResto - Historique Complet du Projet

> Reference long terme pour le workflow BMAD. Chaque phase documente ce qui a ete fait, quand, et les decisions techniques prises.

---

## Phase 1 - Securite & Fondations
**Status**: Complete

- SQL injection fixes (GPS coords parameterized, open_now parameterized)
- MD5 supprime, uploads securises (getimagesize)
- Debug leaks supprimes, session hardening
- Admin strict comparison, router debug supprime
- Password min 8 chars, RateLimiter cree
- Wishlist exploit fixe, duplicate code supprime
- DB indexes script

## Phase 2 - Recherche & Profils
**Status**: Complete

- Text search (parametre `q`)
- Profils publics utilisateurs
- Badge sur les avis
- SEO meta tags

## Phase 3 - Gamification
**Status**: Complete

- Achievement badges
- Points rebalances
- checkAchievements() method

## Phase 4 - Anti-Spam
**Status**: Complete

- SpamDetector false positives corriges
- Duplicate detection
- Fake review detection

## Phase 5 - Performance
**Status**: Complete

- CacheService serialize -> json
- N+1 fixes (reviews photos batch, chart GROUP BY DATE)
- SitemapController
- Slug support dans show()

## Phase 6 - Ownership
**Status**: Complete

- Claim restaurant feature
- Notifications bell dans header
- API endpoints

## Phase 7 - Legal RGPD
**Status**: Complete

- Pages legales (CGU, confidentialite)
- Cookie consent banner
- Suppression de compte (RGPD)

## Phase 8 - Nettoyage
**Status**: Complete

- Debug files archives
- Logger::debug nettoye
- Duplicate routes supprimes
- Session hardening sur root index.php
- **Phase 8.5**: Double navbar fixe, notes>5 capped, search q param, hero search, broken links, carousel arrows, ville filter, list view redesign, Homepage redesign, Search page redesign

## Phase 9 - TripAdvisor-Level
**Status**: Complete

- Error pages (404/500) brandees
- Contact page (controller + view + route + honeypot + rate limiting + DB)
- Restaurant award badges (travelers_choice, top_city, best_cuisine, trending, newcomer)
- Awards display sur show.php et _card.php
- Admin moderation audit log (moderation_log table)
- Admin contacts page (/admin/contacts)
- Admin moderation log page (/admin/moderation-log)
- Owner stats depuis donnees reelles
- **DB migration**: `database/phase9_improvements.sql`

## Phase 10 - Engagement Utilisateur
**Status**: Complete

- Horaires system fix (jour_semaine 1-7 -> 0-6)
- Email verification flow (token, 24h expiry, resend)
- Review edit/update (max 3 edits, re-moderation)
- Amenities filter dans search (INNER JOIN restaurant_options)
- Q&A section sur restaurant pages
- NotificationService centralise (8 event types)
- RGPD: cleanup Q&A et email_verifications on account deletion
- **DB migration**: `database/phase10_improvements.sql`

## Phase 11 - Fidelite & Titres
**Status**: Complete

- Double navbar fix sur /fidelite
- Badge system: 6 tiers (0/150/500/1200/2500/5000)
- 10 achievements
- Perks reels par niveau
- Systeme de titres personnalises (user_titles table)
- Titres competitifs et achievement
- **DB migration**: `database/phase11_titles.sql`

## Phase 12 - Features Majeures
**Status**: Complete

- Photo AI (Google Vision API, 1000/mois)
- Check-in geo (GPS Haversine, 200m max, 4h cooldown)
- Collections publiques (CRUD, browse, Web Share API)
- Reservation en ligne (claimed restaurants, owner accept/refuse)
- Owner dashboard complet
- Menu avec prix (restaurant_menu_items)
- Suggestions personnalisees (RecommendationService)
- Fil d'actualite social (ActivityFeedService, infinite scroll)
- PWA (manifest.json + sw.js)
- API publique documentee
- **DB migration**: `database/phase12_features.sql`

## Phase 13 - Nice-to-Have
**Status**: Complete

- Review tags (12 experience tags)
- Context tags display
- Follow users (FollowController, user_follows)
- Following feed tab
- Review search (FULLTEXT)
- Popular dishes (cross-reference menu vs review text)
- Referral program (200pts/500pts)
- QR Code pour avis (owner dashboard)
- Quick Tips (restaurant_tips, 5-200 chars)
- **DB migration**: `database/phase13_features.sql`

## Phase 14 - Social Utilitaire
**Status**: Complete

- Photo-centric feed redesign
- Friend recommendations ("Vos abonnements connaissent ce restaurant")
- City leaderboard (podium + ranking)
- **Routes**: /classement, /classement/{ville}

## Phase 15 - Proposer un Restaurant
**Status**: Complete

- Suggestion form (/proposer-restaurant)
- Points: +15 submission, +100 validation
- Eclaireur badge (3 validated suggestions)
- Admin moderation (/admin/suggestions)
- Rate limiting (5/h)
- **DB migration**: `database/phase15_suggestions.sql`

## Phase 16 - Activites Verticale
**Status**: Complete

- Multi-vertical architecture (Restaurants | Activites tabs)
- ActivityController complet (browse, detail, review, wishlist, tip, checkin)
- 92 seed places, 5 villes, 12 categories
- Google Places API script ready
- **DB migration**: `database/phase16_activities.sql`

## Phase 17 - Audit Complet
**Status**: Complete

- 10 bugs critiques corriges
- 7 failles securite corrigees
- 8 optimisations performance
- Offres speciales (OfferController, TheFork-style)
- Resume IA avis (ReviewSummaryService)
- Restaurants recemment vus
- Dark mode (CSS variables, localStorage)
- Breadcrumbs + JSON-LD enrichi
- **DB migration**: `database/phase17_audit_fixes.sql`

## Sprint 1 - Bug Fixes (Post Phase 17)
**Status**: Complete (2026-02-15)

22 bugs corriges :
- B1-B2: SQL injections DashboardController -> prepared statements
- B3-B5: LIMIT injection RecommendationService -> `(int)$limit`
- B6-B7: LoyaltyService `note` -> `note_globale`
- B8: OwnerController horaires schema mismatch -> colonnes correctes
- B9: XSS schema_json -> JSON_HEX_TAG | JSON_HEX_AMP
- B10-B14: CSRF verify_csrf() sur 5 POST routes + X-CSRF-TOKEN header
- B15-B16: N+1 UserController -> GROUP BY DATE / GROUP BY restaurant_id
- B17: computeUserTitles() -> session throttle 1h
- B18: LeaderboardController photo_count -> LEFT JOIN + CacheService
- B19: 50 console.log supprimes (5 fichiers JS)
- B20: Fichiers orphelins supprimes (home.css, home.js vides)
- B21: Code mort supprime (User::getStats, Review::getStats)
- B22: CacheService ajoute a LeaderboardController
- saveOptions() fix: mapping form fields -> option_name

## Sprint 2 - Quick Wins
**Status**: Complete (2026-02-15)

8 features implementees :
- F1: Prix DZD prep (colonnes prix_min/prix_max + champs owner dashboard)
- F3: Reactions multiples (Utile/Drole/J'adore) avec toggle AJAX
- F5: PMR filter fix + saveOptions() reecrit avec mapping
- F6: Pros/Cons sur les avis (champs, formulaire, affichage)
- F7: Badge "Client fidele" (3+ avis meme restaurant)
- F8: Double points temporaires (table points_multipliers)
- F15: Galerie photos par categorie (onglets filtrants)
- Halal retire de l'UI (tout est halal en Algerie)

**Migration SQL**: `database/sprint2_quick_wins.sql`

---

## Decisions Techniques Cles

| Decision | Raison |
|----------|--------|
| PDO named params uniquement | EMULATE_PREPARES=false interdit le mix `?` et `:named` |
| verify_csrf() + X-CSRF-TOKEN | Support forms classiques + AJAX |
| CacheService file-based | Pas de Redis en local XAMPP |
| Session throttle pour titres | Eviter recalcul couteux a chaque page |
| Pas d'IA payante | Templates + NLP basique (sauf Google Vision optionnel) |
| Halal retire de l'UI | Tout est halal en Algerie, info inutile |
| Points multiplier global | Applique a toutes les actions via addPoints() central |

---

## Prochaines Etapes (BMAD Required)

Voir `FEATURES_ROADMAP.md` pour la liste detaillee.

**Priorite 1**: F33 - Commande en ligne -> `/bmad:product-brief`
**Sprint 3**: F25 Messagerie + F13 Posts restaurateur + F26 Reponse IA
**Sprint 4-7**: 22 features restantes (voir roadmap)
