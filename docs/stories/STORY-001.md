# STORY-001 : Migration SQL + Configuration Restaurant

**Epic:** F33 - Commande en ligne
**Priorite:** Must Have
**Story Points:** 3
**Status:** Not Started
**Sprint:** 1

---

## User Story

En tant que **proprietaire de restaurant**,
je veux pouvoir **activer/desactiver la commande en ligne et la livraison** depuis mon dashboard,
afin de **controler si les clients peuvent commander chez moi**.

---

## Description

Creer les tables `orders` et `order_items`, ajouter les colonnes `orders_enabled`, `delivery_enabled`, `delivery_fee`, `delivery_min_order` a la table `restaurants`, et integrer les toggles dans le dashboard owner.

## Scope

**In scope :**
- Migration SQL complete (2 tables + 4 colonnes)
- Toggle orders_enabled dans OwnerController.allowedFields
- Toggle delivery_enabled dans OwnerController.allowedFields
- Champs delivery_fee et delivery_min_order dans le dashboard
- Ajout `order_placed => 10` dans LoyaltyService.pointsConfig

**Out of scope :**
- L'interface commande client (STORY-002)
- Le dashboard commandes owner (STORY-004)

---

## Criteres d'acceptation

- [ ] Les tables `orders` et `order_items` sont creees avec tous les indexes
- [ ] Les colonnes `orders_enabled`, `delivery_enabled`, `delivery_fee`, `delivery_min_order` existent dans restaurants
- [ ] Le proprietaire peut activer/desactiver les commandes via le dashboard
- [ ] Le proprietaire peut activer/desactiver la livraison via le dashboard
- [ ] Le proprietaire peut saisir le tarif de livraison et le montant minimum
- [ ] `orders_enabled` et `delivery_enabled` sont dans OwnerController.allowedFields
- [ ] `order_placed => 10` est ajoute dans LoyaltyService.pointsConfig
- [ ] La migration SQL s'execute sans erreur sur une base existante

---

## Notes techniques

### Fichiers a creer
- `database/phase_orders.sql`

### Fichiers a modifier
- `app/Controllers/OwnerController.php` : ajouter 4 champs dans allowedFields
- `app/Views/owner/edit.php` : toggles + champs delivery_fee/delivery_min_order
- `app/Services/LoyaltyService.php` : ajouter order_placed dans pointsConfig

### SQL
Voir schema complet dans `docs/architecture-lebonresto-commande-en-ligne-2026-02-15.md` section "Schema SQL"

---

**Cree avec BMAD Method v6**
