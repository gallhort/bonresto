# Architecture : LeBonResto - Commande en Ligne (F33)

**Date:** 2026-02-15
**Architecte:** Billy
**Version:** 1.0
**Project Level:** 3
**Status:** Draft

---

## Documents lies

- Product Brief : `docs/product-brief-lebonresto-commande-en-ligne-2026-02-15.md`

---

## Resume Executif

Le systeme de commande en ligne s'integre dans l'architecture MVC existante de LeBonResto en ajoutant un nouveau controller (`OrderController`), 2 tables MySQL, et 3 nouvelles vues. Il reutilise les services existants (NotificationService, LoyaltyService, ActivityFeedService, RateLimiter) et suit les memes patterns que le systeme de reservations. Le panier est gere cote client (localStorage) pour eviter toute table de panier cote serveur. Le polling AJAX (30s) remplace les WebSockets pour la mise a jour en temps reel du dashboard proprietaire.

---

## Drivers Architecturaux

1. **Temps reel sans WebSockets** : Pas de WebSocket disponible sur XAMPP. Solution : polling AJAX 30s + notification sonore.
2. **Zero paiement en ligne** : Pas de Stripe/CIB. Paiement a la livraison/retrait. Simplifie enormement l'architecture (pas de webhooks, pas de gestion d'echec de paiement).
3. **Integration dans le MVC existant** : Pas de nouveau framework. Meme stack, memes patterns, memes contraintes (PDO named params, verify_csrf, session auth).
4. **Performance panier** : Panier en localStorage (pas de table `cart` serveur). Reduit les requetes DB et la complexite.
5. **Multi-statuts commande** : 7 statuts avec transitions definies. Necessite une state machine claire.

---

## Architecture Globale

### Pattern

**Monolithe MVC existant etendu** - pas de microservice, pas d'API separee. Un nouveau controller + vues s'integrent dans le meme dossier que le reste du code.

**Justification** : Le projet entier est un monolithe PHP custom. Ajouter un service separe pour les commandes serait de l'over-engineering. Le volume prevu (<100 commandes/jour au debut) ne justifie pas une architecture distribuee.

### Diagramme

```
CLIENT (Navigateur)
├── /commander/{slug}          → Page commande dediee (menu + panier localStorage)
├── /mes-commandes             → Historique commandes client
└── /owner/restaurant/{id}/edit → Dashboard proprio (onglet Commandes)

    │ AJAX (JSON + X-CSRF-TOKEN)
    ▼

SERVEUR PHP (MVC Custom)
├── OrderController            → store(), ownerRespond(), updateStatus(), history()
├── OwnerController            → edit() etendu (charge commandes pending)
├── NotificationService        → order_placed, order_confirmed, order_ready, ...
├── LoyaltyService             → +10pts par commande
├── ActivityFeedService        → log commande dans le feed
└── RateLimiter                → max 3 commandes pending par client

    │ PDO (named params only)
    ▼

MYSQL
├── orders                     → commandes (status, totals, type, timestamps)
├── order_items                → items de commande (qty, price snapshot, notes)
├── restaurant_menu_items      → carte existante (lecture seule)
├── restaurants                → +orders_enabled, +delivery_enabled
└── notifications              → notifications existantes
```

---

## Stack Technique

### Frontend
- **HTML/CSS/JS vanilla** (comme le reste du projet)
- **localStorage** pour le panier (pas de session serveur)
- **Fetch API** pour les appels AJAX avec header `X-CSRF-TOKEN`
- **QR Code** : librairie CDN `qrcodejs` (deja utilisee pour les QR avis, Phase 13)
- **Audio API** : `new Audio()` pour le son de notification (dashboard proprio)

### Backend
- **PHP 8.x** avec le framework MVC custom
- **OrderController** : nouveau controller (~300 lignes estime)
- **Services reutilises** : NotificationService, LoyaltyService, ActivityFeedService, RateLimiter

### Base de donnees
- **MySQL** via PDO (ATTR_EMULATE_PREPARES=false, named params uniquement)
- **2 nouvelles tables** : `orders`, `order_items`
- **2 colonnes ajoutees** : `restaurants.orders_enabled`, `restaurants.delivery_enabled`
- **Charset** : utf8mb4

### Infrastructure
- **XAMPP** (Apache + MySQL + PHP)
- **Pas de cron job** : tout est declenche par les actions utilisateur
- **Email** : fonction `mail()` PHP ou SMTP local (Mailtrap en dev)

---

## Composants Systeme

### Composant 1 : OrderController

**Objectif** : Gerer tout le cycle de vie d'une commande

**Responsabilites :**
- Valider et creer une commande (items, totaux, adresse livraison)
- Permettre au proprietaire d'accepter/refuser avec temps estime
- Mettre a jour les statuts (en_preparation, pret, en_livraison, livre)
- Fournir l'historique des commandes (client + proprio)
- Fournir les commandes pending en polling AJAX
- Permettre l'annulation par le client (si status = pending)

**Interfaces :**
- POST `/api/order/{slug}` → Creer commande
- POST `/api/orders/{id}/respond` → Proprietaire accepte/refuse
- POST `/api/orders/{id}/status` → Proprietaire met a jour statut
- POST `/api/orders/{id}/cancel` → Client annule
- GET `/api/my-orders` → Historique client
- GET `/api/owner/orders` → Commandes proprietaire (polling)
- GET `/api/owner/orders/count` → Compteur commandes pending (polling rapide)
- GET `/commander/{slug}` → Page commande client (rendu HTML)
- GET `/mes-commandes` → Page historique client (rendu HTML)

**Dependances :** NotificationService, LoyaltyService, ActivityFeedService, RateLimiter

### Composant 2 : Page de commande client

**Objectif** : Interface de commande accessible via QR code ou lien direct

**Responsabilites :**
- Afficher la carte du restaurant par categories
- Gerer le panier (localStorage)
- Formulaire de commande (type, adresse si livraison, telephone, notes)
- Afficher le suivi de commande apres validation

**Vue** : `app/Views/order/menu.php`

**JS** : `public/assets/js/order.js` (panier localStorage + AJAX submit)

### Composant 3 : Dashboard commandes proprietaire

**Objectif** : Onglet dans le dashboard owner pour gerer les commandes

**Responsabilites :**
- Liste des commandes en attente avec detail des items
- Boutons accepter/refuser avec champ temps estime
- Boutons transitions de statut (en preparation → pret → en livraison → livre)
- Notification sonore quand nouvelle commande
- Polling AJAX toutes les 30 secondes

**Vue** : Integre dans `app/Views/owner/edit.php` (nouvel onglet)

**JS** : `public/assets/js/owner-orders.js` (polling + actions + son)

### Composant 4 : QR Code commande

**Objectif** : QR code imprimable pointant vers `/commander/{slug}`

**Responsabilites :**
- Generer le QR code dans le dashboard proprietaire
- Telecharger en PNG
- Imprimer avec mise en page

**Note** : Reutilise le meme composant QR que Phase 13 (deja un onglet QR dans le owner dashboard pour les avis). On ajoute un deuxieme QR pour les commandes.

---

## Architecture de Donnees

### Modele de donnees

```
restaurants (existant)
├── +orders_enabled TINYINT(1) DEFAULT 0
├── +delivery_enabled TINYINT(1) DEFAULT 0
├── +delivery_fee DECIMAL(8,2) DEFAULT NULL
├── +delivery_min_order DECIMAL(8,2) DEFAULT NULL
│
├── 1:N → restaurant_menu_items (existant, lecture seule)
├── 1:N → orders (NOUVEAU)
│         ├── id, restaurant_id, user_id
│         ├── order_type ENUM('pickup','delivery')
│         ├── status ENUM('pending','confirmed','preparing','ready','delivering','delivered','cancelled','refused')
│         ├── items_total, delivery_fee, grand_total (DECIMAL)
│         ├── client_name, client_phone, delivery_address, delivery_city
│         ├── special_instructions TEXT
│         ├── estimated_minutes INT (rempli par le proprio a l'acceptation)
│         ├── cancel_reason TEXT
│         ├── created_at, confirmed_at, ready_at, delivered_at, updated_at
│         │
│         └── 1:N → order_items (NOUVEAU)
│                   ├── id, order_id, menu_item_id
│                   ├── item_name, item_price (snapshot au moment de la commande)
│                   ├── quantity INT
│                   └── special_requests TEXT
│
users (existant)
└── 1:N → orders (via user_id)
```

### Schema SQL

```sql
-- Colonnes ajoutees a restaurants
ALTER TABLE restaurants
    ADD COLUMN orders_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN delivery_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN delivery_fee DECIMAL(8,2) DEFAULT NULL,
    ADD COLUMN delivery_min_order DECIMAL(8,2) DEFAULT NULL;

-- Table des commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,

    -- Type et statut
    order_type ENUM('pickup','delivery') NOT NULL DEFAULT 'pickup',
    status ENUM('pending','confirmed','preparing','ready','delivering','delivered','cancelled','refused') NOT NULL DEFAULT 'pending',

    -- Montants (snapshot au moment de la commande)
    items_total DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(8,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(10,2) NOT NULL,

    -- Infos client
    client_name VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20) NOT NULL,
    delivery_address VARCHAR(255) DEFAULT NULL,
    delivery_city VARCHAR(80) DEFAULT NULL,
    special_instructions TEXT DEFAULT NULL,

    -- Traitement
    estimated_minutes INT DEFAULT NULL,
    cancel_reason TEXT DEFAULT NULL,

    -- Timestamps
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmed_at DATETIME DEFAULT NULL,
    ready_at DATETIME DEFAULT NULL,
    delivered_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_restaurant_status (restaurant_id, status),
    INDEX idx_user_created (user_id, created_at DESC),
    INDEX idx_status_created (status, created_at DESC),
    INDEX idx_restaurant_created (restaurant_id, created_at DESC),

    -- Foreign keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items de commande
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,

    -- Snapshot au moment de la commande (le prix peut changer apres)
    item_name VARCHAR(150) NOT NULL,
    item_price DECIMAL(8,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    special_requests TEXT DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_order (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES restaurant_menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index supplementaire pour le polling rapide
CREATE INDEX idx_orders_pending ON orders(restaurant_id, status, created_at DESC);
```

### Decisions de design DB

| Decision | Justification |
|----------|---------------|
| Snapshot prix dans order_items | Le prix du plat peut changer, la commande doit garder le prix au moment de la commande |
| Snapshot nom dans order_items | Si le plat est renomme ou supprime, la commande reste lisible |
| ON DELETE RESTRICT pour menu_item_id | Empecher la suppression d'un plat qui a des commandes. Le proprio doit le marquer `is_available=0` plutot |
| Pas de table cart | Panier en localStorage cote client. Evite la complexite serveur et les paniers abandonnes en DB |
| delivery_fee sur restaurants | Un seul tarif de livraison par restaurant (simple). Pas de zones tarifaires pour le MVP |
| ENUM pour status | 8 statuts fixes, pas de statuts custom. Suffisant pour le MVP |

### Flux de donnees

```
1. CLIENT ouvre /commander/{slug}
   → SELECT restaurant + menu_items WHERE is_available=1
   → Rendu HTML avec carte par categories

2. CLIENT ajoute au panier
   → localStorage.setItem('cart_{slug}', JSON.stringify(items))
   → Aucune requete serveur

3. CLIENT valide la commande
   → POST /api/order/{slug} (JSON: items[], type, phone, address, notes)
   → BEGIN TRANSACTION
   →   INSERT INTO orders (...)
   →   INSERT INTO order_items (...) x N
   →   NotificationService::create(owner_id, 'order_placed', ...)
   →   LoyaltyService::addPoints(user_id, 'order_placed', order_id, 'order')
   →   ActivityFeedService::log(user_id, 'order', 'restaurant', restaurant_id, ...)
   → COMMIT
   → localStorage.removeItem('cart_{slug}')

4. PROPRIO voit la commande (polling 30s)
   → GET /api/owner/orders?status=pending
   → Notification sonore si nouvelles commandes

5. PROPRIO accepte
   → POST /api/orders/{id}/respond {action:'confirm', estimated_minutes:30}
   → UPDATE orders SET status='confirmed', estimated_minutes=30, confirmed_at=NOW()
   → NotificationService::create(user_id, 'order_confirmed', ...)
   → Email au client (optionnel)

6. PROPRIO met a jour le statut
   → POST /api/orders/{id}/status {status:'preparing'}
   → UPDATE orders SET status='preparing'
   → (idem pour ready, delivering, delivered)
   → NotificationService a chaque transition
```

---

## Design API

### Architecture API

- **REST JSON** sur les routes existantes du framework MVC
- **Auth** : session PHP (cookie PHPSESSID) - meme que le reste de l'app
- **CSRF** : `verify_csrf()` sur tous les POST + header `X-CSRF-TOKEN` pour AJAX
- **Pas de versioning** : API interne, pas publique

### Endpoints

#### Client - Commande

| Methode | Route | Description | Auth |
|---------|-------|-------------|------|
| GET | `/commander/{slug}` | Page commande (HTML) | Non |
| POST | `/api/order/{slug}` | Creer commande | Oui |
| POST | `/api/orders/{id}/cancel` | Annuler commande (si pending) | Oui |
| GET | `/mes-commandes` | Page historique (HTML) | Oui |
| GET | `/api/my-orders` | Historique JSON | Oui |
| GET | `/api/orders/{id}` | Detail commande JSON | Oui |

#### Proprietaire - Gestion

| Methode | Route | Description | Auth |
|---------|-------|-------------|------|
| GET | `/api/owner/orders` | Commandes recues (filtrable par status) | Owner |
| GET | `/api/owner/orders/count` | Compteur pending (polling leger) | Owner |
| POST | `/api/orders/{id}/respond` | Accepter/refuser + temps estime | Owner |
| POST | `/api/orders/{id}/status` | Mettre a jour statut | Owner |
| POST | `/api/owner/restaurant/{id}/toggle-orders` | Toggle commandes | Owner |
| POST | `/api/owner/restaurant/{id}/toggle-delivery` | Toggle livraison | Owner |

### Payload - Creer commande

```json
// POST /api/order/{slug}
{
    "items": [
        {"menu_item_id": 42, "quantity": 2, "special_requests": "Sans oignons"},
        {"menu_item_id": 15, "quantity": 1, "special_requests": null}
    ],
    "order_type": "delivery",
    "client_phone": "0555123456",
    "delivery_address": "12 Rue Didouche Mourad, Alger Centre",
    "delivery_city": "Alger",
    "special_instructions": "Sonner 2 fois"
}
```

### Payload - Reponse proprietaire

```json
// POST /api/orders/{id}/respond
{
    "action": "confirm",
    "estimated_minutes": 30,
    "note": "Merci pour votre commande !"
}
```

### Payload - Mise a jour statut

```json
// POST /api/orders/{id}/status
{
    "status": "preparing"
}
```

### Machine a etats - Statuts

```
                ┌──────────┐
                │ pending   │ (client vient de commander)
                └─────┬─────┘
                      │
          ┌───────────┼───────────┐
          ▼                       ▼
    ┌──────────┐           ┌──────────┐
    │ confirmed │           │ refused  │ (fin)
    └─────┬─────┘           └──────────┘
          │
          ▼
    ┌──────────┐
    │ preparing │ (en cuisine)
    └─────┬─────┘
          │
          ▼
    ┌──────────┐
    │  ready    │ (pret a recuperer/livrer)
    └─────┬─────┘
          │
    ┌─────┴─────────┐
    │               │
    ▼               ▼ (si delivery)
┌──────────┐  ┌──────────┐
│ delivered │  │delivering│
│  (fin)    │  └─────┬────┘
└──────────┘        │
                     ▼
               ┌──────────┐
               │ delivered │ (fin)
               └──────────┘

  * cancelled : possible depuis pending ou confirmed uniquement (par le client)
```

**Transitions autorisees :**

| Depuis | Vers | Par |
|--------|------|-----|
| pending | confirmed | Owner |
| pending | refused | Owner |
| pending | cancelled | Client |
| confirmed | preparing | Owner |
| confirmed | cancelled | Client |
| preparing | ready | Owner |
| ready | delivering | Owner (si delivery) |
| ready | delivered | Owner (si pickup) |
| delivering | delivered | Owner |

---

## Couverture Non-Fonctionnelle

### NFR-1 : Temps reel (quasi)

**Requis :** Le proprietaire doit voir les nouvelles commandes rapidement

**Solution :**
- Polling AJAX toutes les 30 secondes sur `/api/owner/orders/count`
- Si nouvelles commandes : recharger la liste complete
- Notification sonore (`new Audio('/assets/sounds/order-bell.mp3').play()`)
- Badge compteur dans le header (meme pattern que la notification bell)
- L'endpoint `/api/owner/orders/count` est ultra-leger : `SELECT COUNT(*) FROM orders WHERE restaurant_id=:rid AND status='pending'`

**Validation :** Le proprietaire voit une nouvelle commande en <30 secondes

### NFR-2 : Performance panier

**Requis :** Le panier doit etre instantane

**Solution :**
- Panier en localStorage (zero requete serveur)
- Calcul totaux en JS cote client
- Validation serveur uniquement au submit final
- La page commande fait 1 seul SELECT (restaurant + menu_items JOIN)

**Validation :** Ajout/suppression au panier = 0ms latence

### NFR-3 : Integrite des donnees

**Requis :** Les prix commandes doivent etre corrects meme si la carte change

**Solution :**
- Snapshot du prix et du nom dans `order_items` au moment de la commande
- Verification serveur : re-SELECT le prix du menu_item et compare avec le prix envoye par le client
- Si ecart : utiliser le prix serveur (le client est informe)
- Transaction DB pour la creation commande (BEGIN/COMMIT)
- `ON DELETE RESTRICT` sur menu_item_id (impossible de supprimer un plat commande)

**Validation :** Le montant total est toujours correct

### NFR-4 : Protection anti-abus

**Requis :** Empecher les fausses commandes et le spam

**Solution :**
- Compte obligatoire (auth session)
- `RateLimiter::attempt("order_$userId", 3, 3600)` : max 3 commandes pending/heure
- Verification que le client n'a pas deja 3 commandes en status pending
- verify_csrf() sur le POST
- Validation serveur de tous les champs (telephone, items, prix)

**Validation :** Un utilisateur malveillant ne peut pas spammer de commandes

### NFR-5 : Disponibilite menu

**Requis :** Les plats indisponibles ne doivent pas etre commandables

**Solution :**
- La page commande filtre `WHERE is_available = 1`
- Au moment du submit, re-verification serveur : si un item est devenu indisponible, erreur 409
- Le proprietaire peut toggle `is_available` depuis le dashboard menu existant

**Validation :** Impossible de commander un plat marque indisponible

---

## Securite

### Authentification
- Session PHP existante (cookie `PHPSESSID`, bcrypt passwords)
- `$this->requireAuth()` pour les actions client
- `$this->getOwnedRestaurant($id)` pour les actions proprietaire (verifie owner_id)

### Autorisation

| Action | Verification |
|--------|-------------|
| Voir la carte | Aucune (page publique) |
| Passer commande | `isAuthenticated()` + pas proprietaire du restaurant |
| Annuler commande | `isAuthenticated()` + `orders.user_id = session.user.id` + status pending/confirmed |
| Accepter/refuser | `getOwnedRestaurant()` verifie que le user est owner |
| Mettre a jour statut | `getOwnedRestaurant()` + transition valide |
| Voir historique | `isAuthenticated()` + filtre par user_id ou owner_id |

### Securite CSRF
- `verify_csrf()` sur tous les POST
- Header `X-CSRF-TOKEN` envoye par JS (meme pattern que reviews, reservations, etc.)

### Validation des entrees
- `(int)` cast sur tous les IDs
- `trim()` + `mb_substr()` sur les champs texte
- Regex validation sur le telephone
- Whitelist sur status et order_type
- Prix recalcule cote serveur (jamais confiance au client)

---

## Performance et Cache

### Strategie de cache
- **Page commande** : pas de cache (le menu doit etre a jour en temps reel)
- **Historique commandes** : pas de cache (donnees temps reel)
- **Compteur pending** : pas de cache (polling toutes les 30s, la requete est un simple COUNT(*))
- **Menu items** : pourrait etre cache avec `CacheService::remember()` mais le TTL devrait etre tres court (30s). A evaluer selon la charge. Pour le MVP, pas de cache.

### Optimisation requetes
- Index `idx_restaurant_status` pour le filtre commandes par restaurant + status
- Index `idx_orders_pending` specifique pour le polling
- `LIMIT 50` sur les listes (historique, commandes owner)
- Join `order_items` charge en une seule requete avec la commande

---

## Organisation du code

### Nouveaux fichiers

```
app/Controllers/OrderController.php         → Controller principal (~300 lignes)
app/Views/order/menu.php                    → Page commande client
app/Views/order/my-orders.php               → Historique commandes client
public/assets/js/order.js                   → Panier localStorage + submit AJAX
public/assets/js/owner-orders.js            → Polling + actions owner
public/assets/css/order.css                 → Styles page commande
public/assets/sounds/order-bell.mp3         → Son notification nouvelle commande
database/phase_orders.sql                   → Migration SQL
```

### Fichiers modifies

```
config/routes.php                           → +12 routes commande
app/Controllers/OwnerController.php         → +orders_enabled, +delivery_enabled dans allowedFields
                                              → edit() charge les commandes pending
app/Views/owner/edit.php                    → Nouvel onglet "Commandes" + toggles
app/Views/partials/header.php               → Icons notifications commande
app/Services/NotificationService.php        → 5 nouvelles methodes notify commande
app/Services/LoyaltyService.php             → +order_placed => 10 dans pointsConfig
```

### Conventions a suivre
- Controller : memes patterns que ReservationController (auth check, rate limit, validation, notification)
- Vues : meme layout que le reste (`$this->render()`)
- JS : vanilla, `fetch()` avec `X-CSRF-TOKEN`, pas de jQuery
- CSS : inline dans la vue (comme le reste du projet) ou fichier separe
- Nommage routes : `/api/...` pour JSON, `/...` pour HTML

---

## Notifications

### Nouveaux types

| Type | Destinataire | Quand | Icon | Titre |
|------|-------------|-------|------|-------|
| `order_placed` | Owner | Nouvelle commande | 📦 | "Nouvelle commande !" |
| `order_confirmed` | Client | Commande acceptee | ✅ | "Commande confirmee !" |
| `order_ready` | Client | Commande prete | 🍽️ | "Commande prete !" |
| `order_delivering` | Client | En cours de livraison | 🚚 | "En cours de livraison" |
| `order_delivered` | Client | Livree | ✅ | "Commande livree !" |
| `order_refused` | Client | Commande refusee | ❌ | "Commande refusee" |
| `order_cancelled` | Owner | Client a annule | ❌ | "Commande annulee" |

### Nouvelles methodes NotificationService

```php
notifyOrderPlaced(int $ownerId, int $orderId, string $clientName, float $total): void
notifyOrderConfirmed(int $userId, int $orderId, string $restaurantName, int $estimatedMinutes): void
notifyOrderReady(int $userId, int $orderId, string $restaurantName): void
notifyOrderDelivering(int $userId, int $orderId, string $restaurantName): void
notifyOrderDelivered(int $userId, int $orderId, string $restaurantName): void
notifyOrderRefused(int $userId, int $orderId, string $restaurantName, ?string $reason): void
notifyOrderCancelled(int $ownerId, int $orderId, string $clientName): void
```

### Email

Envoi email au client pour :
1. Commande confirmee (avec temps estime)
2. Commande prete (retrait) ou en livraison (delivery)

Template email simple en HTML inline (pas de template engine).

---

## Tracabilite des Requirements

### Exigences fonctionnelles

| Requirement | Composant | Notes |
|-------------|-----------|-------|
| Page commande dediee | OrderController::showMenu() + order/menu.php | Via QR ou lien |
| Panier | localStorage + order.js | Zero requete serveur |
| Mode retrait | orders.order_type = 'pickup' | "Des que possible" |
| Mode livraison | orders.order_type = 'delivery' + delivery_address | Si delivery_enabled |
| Toggle commandes | OwnerController.allowedFields + restaurants.orders_enabled | Dashboard |
| Toggle livraison | OwnerController.allowedFields + restaurants.delivery_enabled | Dashboard |
| Dashboard commandes | owner/edit.php onglet + owner-orders.js | Polling 30s |
| Statuts commande | ENUM 8 valeurs + state machine | Transitions definies |
| Notifications in-app | NotificationService + 7 nouveaux types | Existant etendu |
| Notification email | mail() PHP | Confirmation + pret |
| Temps estime | orders.estimated_minutes | Saisi par le proprio |
| QR code commande | qrcodejs CDN | /commander/{slug} |
| Historique commandes | order/my-orders.php + /api/my-orders | Client + proprio |
| Points fidelite | LoyaltyService.order_placed => 10 | Existant etendu |
| Compte obligatoire | requireAuth() | Session PHP |

---

## Trade-offs et Decisions

| Decision | Gain | Perte | Justification |
|----------|------|-------|---------------|
| Panier localStorage | Zero complexite serveur, instant | Perdu si clear browser | Le panier est ephemere, pas critique |
| Polling 30s vs WebSocket | Zero config, fonctionne sur XAMPP | Delai max 30s | Volume faible, polling suffit |
| Snapshot prix dans order_items | Historique fiable | Duplication donnees | Necessaire pour l'integrite |
| ON DELETE RESTRICT sur menu_items | Pas de commande orpheline | Le proprio ne peut pas supprimer un plat commande | Il peut le marquer is_available=0 |
| Pas de table cart | Simplicite | Pas de panier persistant cross-device | Pas un besoin MVP |
| Email via mail() | Zero dependance | Deliverabilite limitee | Notification in-app en fallback |
| Pas de paiement en ligne | Simplicite massive | Pas de garantie paiement | Paiement a la livraison = norme en Algerie |

---

## Contraintes et Hypotheses

### Contraintes
- PDO avec UNIQUEMENT des named params (`:param`)
- verify_csrf() sur tous les POST
- Pas de WebSockets (XAMPP)
- Pas de service email externe (budget zero)
- Pas de paiement en ligne (MVP)

### Hypotheses
- Volume < 100 commandes/jour par restaurant (polling 30s suffit)
- Les restaurateurs consultent le dashboard pendant les heures d'ouverture
- Le panier en localStorage est suffisant (pas de besoin cross-device)
- `mail()` PHP fonctionne ou SMTP est configure
- Le restaurant gere ses propres livreurs

---

## Considerations Futures

1. **Paiement en ligne** : Ajouter `payment_status` ENUM et `payment_method` a la table orders. Integrer CIB Dahabia.
2. **Livreurs independants** : Table `delivery_drivers` + assignation par zone geographique.
3. **Tracking GPS** : Ajouter `driver_latitude/longitude` + endpoint position + map temps reel.
4. **Codes promo** : Table `promo_codes` + champ `discount_code` sur orders + validation au checkout.
5. **Commande programmee** : Ajouter `scheduled_for` DATETIME a la table orders.
6. **Push notifications** : Integrer web push API quand disponible (Service Worker deja en place via PWA).

---

## Prochaines Etapes

1. **Creer les User Stories** → `/bmad:create-story`
2. **Implementer sprint par sprint** → `/bmad:dev-story`
3. **Executer la migration SQL** → `database/phase_orders.sql`

---

**Document cree avec BMAD Method v6 - Phase 3 (Solutioning)**

| Version | Date | Auteur | Changements |
|---------|------|--------|-------------|
| 1.0 | 2026-02-15 | Billy | Architecture initiale |
