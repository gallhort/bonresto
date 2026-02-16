# STORY-007 : Integration header + routes + polish

**Epic:** F33 - Commande en ligne
**Priorite:** Should Have
**Story Points:** 3
**Status:** Not Started
**Sprint:** 2
**Depends on:** STORY-002, STORY-004, STORY-005

---

## User Story

En tant que **utilisateur de LeBonResto**,
je veux que **la commande en ligne soit integree dans la navigation et les notifications**,
afin de **acceder facilement a mes commandes et etre notifie des mises a jour**.

---

## Description

Integration des icones de notification commande dans le header, ajout des liens "Mes commandes" dans la navigation, lien "Commander" sur la page restaurant show.php, et bouton commande sur les cartes restaurant si orders_enabled.

## Scope

**In scope :**
- Icons notifications commande dans header.php (order_placed, order_confirmed, order_ready, etc.)
- Lien "Mes commandes" dans le dropdown utilisateur (header)
- Bouton "Commander en ligne" sur show.php (si orders_enabled et owner_id)
- Lien vers `/commander/{slug}` depuis la carte restaurant (_card.php) si orders_enabled
- Ajout 'order' dans ActivityFeedService action_types ENUM
- Son notification (fichier audio libre de droits)
- Badge compteur commandes pending dans le header owner

**Out of scope :**
- Refactoring du header
- Nouvelles pages

---

## Criteres d'acceptation

- [ ] Les notifications de commande ont les bons icones dans le header (📦✅🍽️🚚❌)
- [ ] Le lien "Mes commandes" est dans le dropdown utilisateur
- [ ] La page show.php affiche un bouton "Commander en ligne" si le restaurant a orders_enabled
- [ ] Le bouton ne s'affiche pas si orders_enabled = 0 ou owner_id = NULL
- [ ] Les cartes restaurant montrent un badge "Commande en ligne" si orders_enabled
- [ ] Le feed d'activite affiche les commandes passees
- [ ] Le son de notification fonctionne sur le dashboard owner

---

## Notes techniques

### Fichiers a modifier
- `app/Views/partials/header.php` : icons notifications + lien "Mes commandes" + badge owner
- `app/Views/restaurants/show.php` : bouton "Commander en ligne"
- `app/Views/restaurants/_card.php` : badge "Commande en ligne"
- `app/Services/ActivityFeedService.php` : ajouter 'order' dans les types

### Fichier a ajouter
- `public/assets/sounds/order-bell.mp3` : son notification (libre de droits, court ~1s)

---

**Cree avec BMAD Method v6**
