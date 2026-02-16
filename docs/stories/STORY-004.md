# STORY-004 : Dashboard commandes proprietaire

**Epic:** F33 - Commande en ligne
**Priorite:** Must Have
**Story Points:** 8
**Status:** Not Started
**Sprint:** 2
**Depends on:** STORY-001, STORY-003

---

## User Story

En tant que **proprietaire de restaurant**,
je veux pouvoir **voir et gerer les commandes recues en temps reel depuis mon dashboard**,
afin de **accepter, preparer et servir les commandes efficacement**.

---

## Description

Nouvel onglet "Commandes" dans le dashboard owner (`/owner/restaurant/{id}/edit`). Affiche les commandes par statut avec polling AJAX toutes les 30 secondes. Le proprietaire peut accepter/refuser une commande avec un temps estime, et faire progresser le statut (en preparation → pret → en livraison → livre). Notification sonore quand une nouvelle commande arrive.

## Scope

**In scope :**
- Onglet "Commandes" dans owner/edit.php
- Liste commandes par statut (pending, confirmed, preparing, ready, delivering)
- Detail de chaque commande (items, quantites, notes, total, infos client)
- Boutons accepter/refuser avec champ temps estime (minutes)
- Boutons transition statut (preparing → ready → delivering → delivered)
- Polling AJAX 30s sur `/api/owner/orders/count`
- Notification sonore nouvelle commande
- Historique commandes livrees/refusees/annulees

**Out of scope :**
- L'interface client (STORY-002/003)
- Le QR code (STORY-006)

## User Flow

1. Proprio ouvre son dashboard, onglet "Commandes"
2. Section "En attente" montre les commandes pending
3. Chaque commande affiche : heure, client, type (retrait/livraison), items, total
4. Proprio clique "Accepter" → saisit temps estime (ex: 30 min) → confirme
5. La commande passe en "Confirmee" → le client est notifie
6. Proprio clique "En preparation" quand la cuisine commence
7. Proprio clique "Pret" quand c'est pret
8. Si livraison : proprio clique "En livraison" puis "Livre"
9. Si retrait : proprio clique "Livre" directement quand le client recupere
10. Toutes les 30s : le JS poll pour nouvelles commandes + son "ding"

---

## Criteres d'acceptation

- [ ] L'onglet "Commandes" est visible dans le dashboard owner
- [ ] Les commandes pending sont affichees avec tous les details (items, client, total)
- [ ] Le proprietaire peut accepter une commande avec un temps estime en minutes
- [ ] Le proprietaire peut refuser une commande avec une raison optionnelle
- [ ] Le proprietaire peut faire progresser le statut etape par etape
- [ ] Seules les transitions valides sont possibles (cf. state machine dans l'architecture)
- [ ] Le polling AJAX se declenche toutes les 30 secondes
- [ ] Une notification sonore se joue quand une nouvelle commande arrive
- [ ] Le client est notifie a chaque changement de statut (NotificationService)
- [ ] L'historique des commandes passees est accessible (filtre par statut)
- [ ] verify_csrf() sur tous les POST

---

## Notes techniques

### Fichiers a creer
- `public/assets/js/owner-orders.js` : polling + actions + son
- `public/assets/sounds/order-bell.mp3` : son notification (libre de droits)

### Fichiers a modifier
- `app/Controllers/OrderController.php` : ownerRespond(), updateStatus(), ownerOrders(), ownerOrdersCount()
- `app/Views/owner/edit.php` : onglet Commandes
- `config/routes.php` : routes owner orders

### Endpoints
- GET `/api/owner/orders?status=pending` → liste commandes
- GET `/api/owner/orders/count` → `{ pending_count: N }` (polling leger)
- POST `/api/orders/{id}/respond` → `{ action: 'confirm'/'refuse', estimated_minutes, note }`
- POST `/api/orders/{id}/status` → `{ status: 'preparing'/'ready'/'delivering'/'delivered' }`

### Validation transitions
```php
$validTransitions = [
    'pending' => ['confirmed', 'refused'],
    'confirmed' => ['preparing'],
    'preparing' => ['ready'],
    'ready' => ['delivering', 'delivered'],
    'delivering' => ['delivered'],
];
```

---

**Cree avec BMAD Method v6**
