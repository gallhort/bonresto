# STORY-003 : Soumission de commande (checkout)

**Epic:** F33 - Commande en ligne
**Priorite:** Must Have
**Story Points:** 8
**Status:** Not Started
**Sprint:** 1
**Depends on:** STORY-001, STORY-002

---

## User Story

En tant que **client connecte**,
je veux pouvoir **valider ma commande en choisissant retrait ou livraison**,
afin de **envoyer ma commande au restaurant**.

---

## Description

Formulaire de validation apres le panier. Le client choisit entre retrait sur place et livraison (si active). Il renseigne son telephone, et son adresse de livraison si applicable. La commande est envoyee en AJAX, creee en DB avec transaction, et les notifications/points sont declenches.

## Scope

**In scope :**
- Formulaire checkout (type, telephone, adresse, instructions)
- POST `/api/order/{slug}` dans OrderController::store()
- Validation serveur (items existent, sont disponibles, prix corrects)
- Transaction DB (orders + order_items)
- Verification prix serveur vs client (snapshot)
- Rate limiting (3 commandes pending max par client)
- NotificationService : notifier le owner (order_placed)
- LoyaltyService : +10pts
- ActivityFeedService : log
- Confirmation visuelle + vidage panier localStorage

**Out of scope :**
- Gestion des commandes par le owner (STORY-004)
- Livraison specifique (STORY-005)

## User Flow

1. Client clique "Commander" dans le panier
2. Formulaire de checkout s'affiche :
   - Type : Retrait sur place / Livraison (si delivery_enabled)
   - Telephone (pre-rempli si disponible dans le profil)
   - Adresse de livraison (si delivery)
   - Instructions speciales (textarea optionnel)
3. Client valide → AJAX POST
4. Serveur verifie : auth, CSRF, rate limit, items disponibles, prix
5. Serveur cree la commande en transaction (orders + order_items)
6. Notifications + points + feed
7. Client voit confirmation : "Commande envoyee ! Le restaurant va confirmer."
8. Panier localStorage vide

---

## Criteres d'acceptation

- [ ] Le formulaire affiche les options retrait/livraison (livraison uniquement si delivery_enabled)
- [ ] Le telephone est obligatoire et valide (regex)
- [ ] L'adresse est obligatoire si type = delivery
- [ ] Le serveur re-verifie que chaque item existe et est is_available = 1
- [ ] Le serveur utilise le prix DB (pas le prix envoye par le client) pour le calcul total
- [ ] Si un item est devenu indisponible, erreur 409 avec message clair
- [ ] La commande est creee en transaction (rollback si erreur)
- [ ] Les frais de livraison sont ajoutes si type = delivery
- [ ] Le montant minimum de livraison est verifie si delivery_min_order est defini
- [ ] Rate limit : max 3 commandes pending par client par heure
- [ ] Le owner recoit une notification in-app (order_placed)
- [ ] Le client recoit +10 points fidelite
- [ ] L'activite est loguee dans le feed
- [ ] Le panier localStorage est vide apres confirmation
- [ ] Le client ne peut pas commander dans son propre restaurant
- [ ] verify_csrf() est appele avec support X-CSRF-TOKEN

---

## Notes techniques

### Fichiers a creer/modifier
- `app/Controllers/OrderController.php` : methode store()
- `app/Services/NotificationService.php` : ajouter notifyOrderPlaced()
- `public/assets/js/order.js` : formulaire checkout + submit AJAX
- `config/routes.php` : POST `/api/order/{slug}`

### Logique store()
```
1. requireAuth()
2. verify_csrf()
3. RateLimiter::attempt("order_$userId", 3, 3600)
4. SELECT restaurant WHERE slug = :slug AND orders_enabled = 1 AND owner_id IS NOT NULL
5. Verifier que user_id != owner_id
6. Parse JSON input (items, order_type, phone, address, instructions)
7. Valider chaque item : SELECT id, name, price, is_available FROM restaurant_menu_items WHERE id = :id AND restaurant_id = :rid
8. Si un item is_available = 0 → erreur 409
9. Calculer items_total (somme qty * price serveur)
10. Si delivery : ajouter delivery_fee, verifier delivery_min_order
11. BEGIN TRANSACTION
12.   INSERT INTO orders (...)
13.   Pour chaque item : INSERT INTO order_items (snapshot name, price, qty, notes)
14.   NotificationService::notifyOrderPlaced(owner_id, ...)
15.   LoyaltyService::addPoints(user_id, 'order_placed', order_id, 'order')
16.   ActivityFeedService::log(user_id, 'order', 'restaurant', restaurant_id, ...)
17. COMMIT
18. Retourner JSON { success: true, order_id, message }
```

---

**Cree avec BMAD Method v6**
