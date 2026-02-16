# Product Brief: LeBonResto - Commande en Ligne

**Date:** 2026-02-15
**Author:** Billy
**Version:** 1.0
**Project Type:** web-app
**Project Level:** 3
**Feature ID:** F33

---

## Executive Summary

LeBonResto ajoute un systeme de commande en ligne permettant aux clients de commander directement depuis la plateforme (retrait sur place ou livraison) pour les restaurants dont le proprietaire a claim sa page. Le restaurateur configure sa carte complete dans son dashboard, genere un QR code physique pointant vers une page de commande dediee, et recoit/gere les commandes en temps reel avec notifications. Le paiement se fait a la livraison ou au retrait (pas de paiement en ligne). C'est la feature la plus strategique du projet : elle transforme LeBonResto d'une simple plateforme d'avis en un outil operationnel indispensable pour les restaurateurs algeriens.

---

## Problem Statement

### The Problem

Les restaurants algeriens n'ont pas d'outil simple et accessible pour recevoir des commandes en ligne. Les solutions existantes (Uber Eats, Glovo) sont soit absentes, soit tres couteuses en commission (25-30%). Les restaurateurs recoivent les commandes par telephone, ce qui genere des erreurs, des files d'attente, et une impossibilite de gerer les pics d'activite. Les clients, eux, n'ont pas de moyen pratique de consulter la carte, commander et suivre leur commande sans appeler.

### Why Now?

- La base LeBonResto a deja des restaurateurs qui ont claim leur page et gerent leur dashboard (menu, horaires, reservations)
- Le systeme de menu avec prix (restaurant_menu_items) est deja en place depuis Phase 12
- Le systeme de reservations prouve que le workflow owner-client fonctionne
- La demande de commande en ligne en Algerie est forte mais les solutions locales sont quasi inexistantes
- Le QR code en restaurant est devenu un reflexe post-COVID

### Impact if Unsolved

- LeBonResto reste une plateforme passive (avis seulement) sans utilite operationnelle quotidienne
- Les restaurateurs n'ont pas de raison de revenir sur la plateforme chaque jour
- Aucune possibilite de monetisation (pas de transaction = pas de revenue model)
- Les concurrents qui proposent la commande en ligne captureront ce marche

---

## Target Audience

### Primary Users

**Clients (consommateurs)**
- Age : 18-45 ans, urbains, Algerie (Alger, Oran, Constantine, Annaba, Setif)
- Tech-savvy : utilisent smartphone et web quotidiennement
- Comportement : veulent commander rapidement, sans appeler, depuis leur telephone ou bureau
- Pain point : pas de visibilite sur la carte, temps d'attente imprevisible au telephone
- Doivent avoir un compte LeBonResto pour commander

**Restaurateurs (proprietaires)**
- Restaurants qui ont claim leur page sur LeBonResto
- Gerent deja leur dashboard (menu, horaires, reservations)
- Veulent augmenter leur chiffre d'affaires sans commission excessive
- Pain point : gestion telephonique des commandes, erreurs, pas de tracabilite

### Secondary Users

- **Personnel de cuisine** : voit les commandes arriver en temps reel sur l'ecran du dashboard
- **Livreurs du restaurant** : recoivent les details de livraison (adresse, telephone client)
- **Admin LeBonResto** : supervise les commandes, gere les litiges eventuels

### User Needs

1. **Clients** : consulter la carte avec prix, choisir des plats, passer commande simplement, etre notifie du statut
2. **Restaurateurs** : recevoir les commandes en temps reel, valider/refuser, donner un temps estime, gerer livraison ou retrait
3. **Les deux** : communication claire sur le statut de la commande (en attente -> acceptee -> en preparation -> prete -> livree)

---

## Solution Overview

### Proposed Solution

Un systeme de commande en ligne integre a LeBonResto avec 4 composants principaux :

1. **Page de commande dediee** (`/commander/{restaurant_slug}`) : interface client pour parcourir la carte, ajouter au panier, et passer commande. Accessible via QR code ou lien direct.

2. **Dashboard proprietaire enrichi** : nouvel onglet "Commandes" dans le owner dashboard avec vue temps reel des commandes entrantes, actions (accepter/refuser/en preparation/pret), et estimation du temps de traitement.

3. **Systeme de notifications** : notifications push dans l'appli (NotificationService existant) + email pour informer le client du statut de sa commande a chaque etape.

4. **QR Code restaurant** : generation d'un QR code unique par restaurant pointant vers la page de commande dediee. Telechargeable/imprimable depuis le dashboard.

### Key Features

- Carte complete avec categories, prix en DZD, disponibilite en temps reel
- Panier avec quantites, notes speciales par plat, sous-total
- Choix retrait sur place / livraison (si active par le restaurateur)
- Adresse de livraison (si livraison activee)
- Estimation du temps de traitement donnee par le restaurateur
- Suivi de commande en temps reel (statuts : en_attente -> acceptee -> en_preparation -> prete -> en_livraison -> livree / refusee / annulee)
- Historique des commandes (client + restaurateur)
- QR code unique par restaurant
- Notifications multi-canal (in-app + email)
- Points de fidelite pour les commandes

### Value Proposition

**Pour les restaurateurs** : un outil gratuit de commande en ligne sans commission sur les commandes (modele freemium), qui remplace la prise de commande telephonique et augmente le volume de commandes.

**Pour les clients** : commander facilement depuis n'importe ou, voir la carte avec les prix, suivre sa commande en temps reel, sans avoir a appeler.

**Pour LeBonResto** : transformation d'une plateforme d'avis passive en outil operationnel quotidien, avec possibilite de monetisation future (features premium).

---

## Business Objectives

### Goals

- Transformer LeBonResto en outil operationnel quotidien pour les restaurateurs
- Augmenter le nombre de restaurants qui claim leur page (incentive : commande en ligne gratuite)
- Creer un flux transactionnel qui permettra la monetisation future
- Augmenter l'engagement utilisateur (les clients reviennent pour commander, pas seulement pour les avis)
- Generer des donnees de commande utiles pour les restaurateurs (plats populaires, heures de pointe)

### Success Metrics

- Nombre de restaurants activant la commande en ligne
- Nombre de commandes passees par semaine/mois
- Taux de conversion (visite page commande -> commande passee)
- Taux d'acceptation des commandes par les restaurateurs
- Temps moyen de traitement d'une commande
- Retention des restaurateurs (reviennent-ils chaque jour sur le dashboard ?)

### Business Value

- **Court terme** : valeur ajoutee gratuite qui attire les restaurateurs, augmente les claims, et fidélise les utilisateurs
- **Moyen terme** : modele freemium (features premium : statistiques avancees, commandes prioritaires, branding personnalise)
- **Long terme** : commission sur la livraison, integration paiement en ligne, marketplace

---

## Scope

### In Scope (MVP)

- Page de commande dediee par restaurant (`/commander/{slug}`)
- Panier (ajout, suppression, modification quantites, notes par plat)
- Mode retrait sur place ("des que possible")
- Mode livraison (si active par le restaurateur) avec adresse client
- Toggle activation commande en ligne dans le owner dashboard
- Toggle activation livraison dans le owner dashboard
- Dashboard commandes pour le proprietaire (liste temps reel, actions)
- Statuts de commande : en_attente -> acceptee -> en_preparation -> prete -> en_livraison -> livree / refusee / annulee
- Notifications in-app (NotificationService) a chaque changement de statut
- Notification email au client (acceptation + commande prete)
- Estimation du temps de traitement (saisie par le restaurateur a l'acceptation)
- QR code unique par restaurant pointant vers `/commander/{slug}`
- Historique des commandes (cote client + cote restaurateur)
- Points de fidelite : +10pts par commande passee
- Utilisation du systeme de menu existant (restaurant_menu_items)
- Compte client obligatoire pour commander

### Out of Scope

- Paiement en ligne (Stripe, CIB, etc.) - paiement a la livraison/retrait uniquement
- Systeme de livreurs independants (type Uber Eats) - livreurs du restaurant uniquement pour le MVP
- Suivi GPS en temps reel du livreur
- Chat en direct client-restaurant
- Systeme de pourboire en ligne
- Multi-langue (la commande est en francais uniquement)
- Application mobile native
- Systeme de promotions/codes promo sur les commandes
- Integration avec des caisses enregistreuses / POS

### Future Considerations

- Paiement en ligne (CIB Dahabia, carte bancaire)
- Livreurs independants (marketplace de livraison)
- Suivi GPS livreur en temps reel
- Codes promo et offres speciales sur les commandes
- Statistiques avancees de commande (premium)
- Commande programmee (choisir un creneau futur)
- Commande de groupe (plusieurs personnes ajoutent au meme panier)
- Integration SMS pour les notifications

---

## Key Stakeholders

- **Billy (Product Owner)** - Influence haute. Vision produit, decisions finales, priorites.
- **Restaurateurs partenaires** - Influence haute. Utilisateurs primaires cote business, leurs besoins dictent les fonctionnalites.
- **Clients finaux** - Influence moyenne. Experience utilisateur, adoption, feedback.
- **Admin LeBonResto** - Influence moyenne. Supervision, moderation, support.

---

## Constraints and Assumptions

### Constraints

- **Pas de paiement en ligne** : paiement a la livraison/retrait uniquement (pas de Stripe/CIB pour le MVP)
- **Stack existante** : PHP custom MVC, MySQL, PDO named params, pas de WebSockets
- **Pas d'IA payante** : pas de systeme intelligent de routing/estimation
- **XAMPP local** : pas de serveur de production avec cron jobs sophistiques
- **Notifications** : pas de push notifications navigateur (pas encore implemente), donc in-app + email
- **Budget zero** : pas de services tiers payants
- **CSRF obligatoire** : verify_csrf() sur tous les POST, X-CSRF-TOKEN pour AJAX

### Assumptions

- Les restaurateurs ont deja saisi leur carte (restaurant_menu_items) ou le feront
- Les restaurateurs consultent leur dashboard regulierement (au moins pendant les heures d'ouverture)
- Les clients ont un compte LeBonResto ou sont prets a en creer un
- Le volume de commandes sera gerahle sans WebSockets (polling AJAX ou refresh periodique)
- Le systeme de livraison est gere entierement par le restaurant (leurs propres livreurs)
- L'email fonctionne (SMTP configure ou simule en local)
- Le menu existant (restaurant_menu_items) a les colonnes necessaires (name, price, category, is_available)

---

## Success Criteria

- Au moins 5 restaurants activent la commande en ligne dans le premier mois
- Au moins 50 commandes passees dans le premier mois
- Taux d'acceptation des commandes > 80%
- Temps moyen entre commande et acceptation < 10 minutes
- Aucun bug critique bloquant une commande
- Le restaurateur peut gerer ses commandes sans formation (UX intuitive)
- Le client peut passer commande en moins de 3 minutes (de l'ouverture de la page a la validation)

---

## Timeline and Milestones

### Target Launch

Le plus vite possible - implementation directe apres validation du product brief.

### Key Milestones

- **M1 - Architecture** : Schema DB, routes, controllers, design des vues (1-2 jours)
- **M2 - Backend commande** : Table orders, OrderController, logique de statuts, NotificationService enrichi (2-3 jours)
- **M3 - Page commande client** : UI carte + panier + formulaire commande + responsive (2-3 jours)
- **M4 - Dashboard proprietaire** : Onglet commandes, actions accepter/refuser, temps estime, historique (2-3 jours)
- **M5 - Livraison** : Toggle livraison, adresse client, statut en_livraison, zone de livraison (1-2 jours)
- **M6 - QR Code + Notifications** : Generation QR, emails de statut, points fidelite (1 jour)
- **M7 - Tests & Polish** : Tests manuels, edge cases, UX polish, migration SQL finale (1-2 jours)

**Total estime : 10-16 jours**

---

## Risks and Mitigation

- **Risk:** Les restaurateurs ne saisissent pas leur carte complete
  - **Likelihood:** Medium
  - **Mitigation:** Menu deja existant (restaurant_menu_items), inciter via le dashboard avec un indicateur de completion "Votre carte est complete a X%"

- **Risk:** Pas de WebSockets = le restaurateur ne voit pas les nouvelles commandes en temps reel
  - **Likelihood:** High
  - **Mitigation:** Polling AJAX toutes les 30 secondes sur le dashboard commandes + notification sonore + badge compteur dans le header

- **Risk:** Volume de commandes trop eleve pour un seul restaurateur sans systeme de gestion
  - **Likelihood:** Low (au debut)
  - **Mitigation:** Possibilite de desactiver temporairement les commandes + indicateur "occupe" sur la page commande

- **Risk:** Abus / fausses commandes
  - **Likelihood:** Medium
  - **Mitigation:** Compte obligatoire + rate limiting (max 3 commandes en attente par client) + historique pour identifier les abus

- **Risk:** Livraison non tracable (livreurs du restaurant)
  - **Likelihood:** Medium
  - **Mitigation:** Statut simple (en_livraison -> livree) avec telephone du livreur visible par le client. Pas de tracking GPS pour le MVP.

- **Risk:** Email non delivre (SMTP local)
  - **Likelihood:** High (en dev local)
  - **Mitigation:** Notification in-app toujours presente comme fallback, email en complement. Tester avec Mailtrap en dev.

---

## Technical Context (for architecture agent)

### Existing Infrastructure to Reuse

**Tables existantes pertinentes :**
- `restaurant_menu_items` : id, restaurant_id, category VARCHAR(80), name VARCHAR(150), description TEXT, price DECIMAL(8,2), photo_path, is_available TINYINT(1), position INT
- `restaurants` : id, nom, slug, owner_id, reservations_enabled, menu_enabled, telephone, adresse, ville, latitude, longitude
- `users` : id, email, prenom, nom, points, badge
- `notifications` : user_id, type, title, message, data JSON, read_at, created_at
- `reservations` : modele de reference pour le workflow owner-client (status ENUM pending/accepted/refused/cancelled)

**Controllers existants a etendre :**
- `OwnerController` (`app/Controllers/OwnerController.php`) : getOwnedRestaurant(), edit(), apiUpdate(), apiUpdateMenu(), apiToggleReservations(). Ajouter `orders_enabled` a allowedFields et un onglet Commandes dans la vue.
- `ReservationController` : pattern de reference (rate limiting, ownership check, duplicate check, notification, status flow)

**Services existants :**
- `NotificationService` (`app/Services/NotificationService.php`) : create(userId, type, title, message, data[]). Types existants : new_review, review_approved, reservation_request, reservation_response, etc. Ajouter : order_placed, order_confirmed, order_ready, order_delivering, order_delivered, order_cancelled.
- `LoyaltyService` (`app/Services/LoyaltyService.php`) : addPoints(userId, action, referenceId, referenceType). Ajouter action 'order_placed' => 10 dans pointsConfig.
- `CacheService` : remember(key, callback, ttl) pour cache file-based.
- `RateLimiter` : attempt(action, maxAttempts, windowSeconds).

**Routes pattern** (`config/routes.php`) :
- GET pages : `$router->get('/path', 'Controller@method')`
- POST API : `$router->post('/api/path', 'Controller@method')`
- Params : `{id}`, `{slug}`

**Notification bell** (`app/Views/partials/header.php`) :
- Icons mapping JS : ajouter order_placed='📦', order_confirmed='✅', order_ready='🍽️', order_cancelled='❌', order_delivering='🚚'
- API : GET /api/notifications, POST /api/notifications/read

**Contraintes techniques critiques :**
- PDO ONLY named params (`:param`), NEVER positional `?` - ATTR_EMULATE_PREPARES=false
- verify_csrf() sur TOUS les POST + X-CSRF-TOKEN header pour AJAX
- DB transactions (beginTransaction/commit/rollBack) pour les operations multi-tables
- utf8mb4 charset

### Fichiers a lire par l'agent architecture

```
config/routes.php                          - Routes existantes
app/Controllers/OwnerController.php        - Dashboard proprio (a etendre)
app/Controllers/ReservationController.php  - Pattern de reference workflow
app/Services/NotificationService.php       - Service notifications
app/Services/LoyaltyService.php            - Points fidelite
app/Views/owner/edit.php                   - Vue dashboard proprio
database/phase12_features.sql              - Schema tables menu/reservations
```

---

## Next Steps

1. Creer l'Architecture technique - `/bmad:architecture`
2. Creer les User Stories - `/bmad:create-story`
3. Implementer sprint par sprint - `/bmad:dev-story`

---

**This document was created using BMAD Method v6 - Phase 1 (Analysis)**

*To continue: Run `/bmad:architecture` to design the system architecture.*
