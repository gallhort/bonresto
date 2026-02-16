# STORY-005 : Historique commandes client + suivi

**Epic:** F33 - Commande en ligne
**Priorite:** Must Have
**Story Points:** 5
**Status:** Not Started
**Sprint:** 2
**Depends on:** STORY-003

---

## User Story

En tant que **client**,
je veux pouvoir **voir l'historique de mes commandes et suivre le statut de ma commande en cours**,
afin de **savoir ou en est ma commande et revoir mes commandes passees**.

---

## Description

Page `/mes-commandes` avec liste des commandes passees et en cours. Pour les commandes actives (pending → delivering), affichage du statut en temps reel avec polling. Detail de chaque commande accessible avec la liste des items.

## Scope

**In scope :**
- Route GET `/mes-commandes` → OrderController::myOrders()
- Vue `app/Views/order/my-orders.php`
- API GET `/api/my-orders` pour chargement AJAX
- API GET `/api/orders/{id}` pour le detail d'une commande
- Suivi statut en temps reel (polling 30s pour commandes actives)
- Annulation possible si statut = pending ou confirmed
- Indicateur de temps estime (minutes restantes)
- POST `/api/orders/{id}/cancel` pour annuler

**Out of scope :**
- Notation du restaurant post-commande (existant via /restaurant/{id}/review)

## User Flow

1. Client clique "Mes commandes" dans le menu
2. Page affiche les commandes en cours en haut, historique en bas
3. Chaque commande montre : restaurant, date, total, statut (badge colore)
4. Les commandes actives ont un polling 30s pour mettre a jour le statut
5. Le client peut cliquer sur une commande pour voir le detail (items, notes, prix)
6. Si statut pending/confirmed : bouton "Annuler" disponible
7. Si commande confirmee : temps estime affiche ("Pret dans ~20 min")

---

## Criteres d'acceptation

- [ ] La page `/mes-commandes` affiche la liste des commandes de l'utilisateur
- [ ] Les commandes actives sont en haut avec badge statut colore
- [ ] Le detail d'une commande affiche tous les items avec quantites et prix
- [ ] Le client peut annuler une commande si statut = pending ou confirmed
- [ ] Le temps estime est affiche pour les commandes confirmees
- [ ] Le polling met a jour le statut des commandes actives toutes les 30s
- [ ] Le owner est notifie si le client annule (order_cancelled)
- [ ] L'historique est pagine (LIMIT 20, bouton "Voir plus")

---

## Notes techniques

### Fichiers a creer
- `app/Views/order/my-orders.php`

### Fichiers a modifier
- `app/Controllers/OrderController.php` : myOrders(), myOrdersApi(), orderDetail(), cancelOrder()
- `config/routes.php` : GET /mes-commandes, GET /api/my-orders, GET /api/orders/{id}, POST /api/orders/{id}/cancel

### Query historique
```sql
SELECT o.*, r.nom as restaurant_nom, r.slug as restaurant_slug,
       (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as restaurant_photo
FROM orders o
INNER JOIN restaurants r ON r.id = o.restaurant_id
WHERE o.user_id = :uid
ORDER BY o.created_at DESC
LIMIT :limit OFFSET :offset
```

---

**Cree avec BMAD Method v6**
