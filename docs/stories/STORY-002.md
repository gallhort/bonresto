# STORY-002 : Page commande client (menu + panier)

**Epic:** F33 - Commande en ligne
**Priorite:** Must Have
**Story Points:** 8
**Status:** Not Started
**Sprint:** 1
**Depends on:** STORY-001

---

## User Story

En tant que **client connecte**,
je veux pouvoir **consulter la carte d'un restaurant et ajouter des plats a mon panier**,
afin de **preparer ma commande avant de la valider**.

---

## Description

Page dediee `/commander/{slug}` accessible via QR code ou lien direct. Affiche la carte du restaurant par categories avec prix, photos et disponibilite. Le panier est gere en localStorage (zero requete serveur). Le client peut ajuster les quantites, ajouter des notes par plat, et voir le sous-total en temps reel.

## Scope

**In scope :**
- Route GET `/commander/{slug}` dans OrderController::showMenu()
- Vue `app/Views/order/menu.php` avec carte par categories
- Panier localStorage avec ajout/suppression/modification quantites
- Notes speciales par plat (textarea)
- Calcul sous-total en temps reel (JS)
- Affichage "Commandes indisponibles" si orders_enabled = 0
- Design responsive (mobile-first, les QR codes seront scannes au tel)

**Out of scope :**
- Le formulaire de validation de commande (STORY-003)
- La soumission AJAX (STORY-003)

## User Flow

1. Client scanne le QR code ou clique sur un lien
2. La page `/commander/{slug}` s'ouvre
3. Le menu est affiche par categories (Entrees, Plats, Desserts, Boissons...)
4. Chaque item montre : nom, description, prix, photo (si disponible)
5. Les items `is_available = 0` sont grises avec mention "Indisponible"
6. Le client clique "+" pour ajouter un item au panier
7. Le panier flottant en bas montre : nombre d'items, total, bouton "Commander"
8. Le client peut ouvrir le detail du panier pour modifier quantites ou ajouter des notes
9. Le panier persiste en localStorage (si le client ferme et rouvre la page)

---

## Criteres d'acceptation

- [ ] La page `/commander/{slug}` affiche la carte du restaurant par categories
- [ ] Les plats avec `is_available = 0` sont visibles mais non ajoutables (grises)
- [ ] Le client peut ajouter un plat au panier en un clic
- [ ] Le client peut modifier la quantite d'un plat (+ / -)
- [ ] Le client peut supprimer un plat du panier
- [ ] Le client peut ajouter une note speciale par plat
- [ ] Le sous-total se met a jour en temps reel
- [ ] Le panier persiste en localStorage (rechargement de page = panier conserve)
- [ ] Le panier est specifique par restaurant (cart_{slug})
- [ ] Si `orders_enabled = 0`, la page affiche un message "Commandes indisponibles"
- [ ] Si le restaurant n'a pas de owner_id, la page affiche "Restaurant non configure"
- [ ] La page est responsive (mobile-first)
- [ ] Le nom du restaurant, l'adresse et le telephone sont affiches en haut

---

## Notes techniques

### Fichiers a creer
- `app/Controllers/OrderController.php` : methode showMenu()
- `app/Views/order/menu.php` : vue page commande
- `public/assets/js/order.js` : gestion panier localStorage
- `public/assets/css/order.css` : styles (ou inline dans la vue)

### Fichiers a modifier
- `config/routes.php` : ajouter GET `/commander/{slug}`

### Query principale
```sql
SELECT r.id, r.nom, r.slug, r.adresse, r.ville, r.telephone, r.orders_enabled, r.delivery_enabled, r.delivery_fee, r.delivery_min_order, r.owner_id
FROM restaurants r
WHERE r.slug = :slug AND r.status = 'validated' AND r.owner_id IS NOT NULL

SELECT * FROM restaurant_menu_items
WHERE restaurant_id = :rid
ORDER BY category, position
```

### Structure localStorage
```javascript
// cart_{slug}
{
  items: [
    { menu_item_id: 42, name: "Couscous Royal", price: 1200, quantity: 2, notes: "Sans piment" },
    { menu_item_id: 15, name: "Limonade", price: 150, quantity: 3, notes: null }
  ],
  updated_at: "2026-02-15T12:00:00"
}
```

---

**Cree avec BMAD Method v6**
