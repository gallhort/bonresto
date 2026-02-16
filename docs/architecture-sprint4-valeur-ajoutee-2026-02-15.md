# Architecture - Sprint 4 : Valeur Ajoutee

> Date: 2026-02-15
> Features: F9 Classement popularite, F19 Comparateur, F24 Stats publiques

## Architectural Drivers

- **Performance** : Pages publiques a fort trafic — cache obligatoire (CacheService 30min-1h)
- **SEO** : Pages /classement-restaurants, /comparateur, /stats doivent etre indexables
- **Reutilisation** : Maximiser les queries et composants existants (card, analytics, leaderboard)
- **Zero migration complexe** : Utiliser les tables existantes, une seule colonne ajoutee

---

## F9 — Classement Popularite Restaurant

### Concept
Score de popularite composite calcule a partir de donnees existantes. Nouveau sort option dans la recherche + page dediee `/classement-restaurants`.

### Score de popularite (formule)
```
popularity_score = (note_moyenne * 20) + (nb_avis * 5) + (vues_total * 0.01) + (orders_count * 10) + (has_award * 15)
```
- `note_moyenne` : 0-5 → poids x20 = max 100 pts
- `nb_avis` : poids x5 (10 avis = 50 pts)
- `vues_total` : poids x0.01 (1000 vues = 10 pts)
- `orders_count` : poids x10 (commandes recues)
- `has_award` : bonus 15 pts si restaurant a un award

### Migration SQL
```sql
ALTER TABLE restaurants ADD COLUMN popularity_score FLOAT DEFAULT 0 AFTER vues_total;
CREATE INDEX idx_restaurants_popularity ON restaurants(popularity_score DESC);
```

### Recalcul du score
- **Cron/trigger** : Recalcul via une methode `Restaurant::recalculatePopularity()` appelee :
  - Apres chaque nouvel avis approuve
  - Apres chaque commande livree
  - Via CacheService (recalcul batch toutes les heures)
- **Query de recalcul batch** :
```sql
UPDATE restaurants r SET popularity_score = (
  COALESCE(r.note_moyenne, 0) * 20 +
  COALESCE(r.nb_avis, 0) * 5 +
  COALESCE(r.vues_total, 0) * 0.01 +
  (SELECT COUNT(*) FROM orders o WHERE o.restaurant_id = r.id AND o.status = 'delivered') * 10 +
  (SELECT IF(COUNT(*) > 0, 15, 0) FROM restaurant_awards ra WHERE ra.restaurant_id = r.id)
) WHERE r.status = 'validated'
```

### Routes
- `GET /classement-restaurants` → `RestaurantController@ranking`
- `GET /classement-restaurants/{ville}` → `RestaurantController@ranking` (filtre ville)
- `GET /search?sort=popularity` → Sort existant enrichi

### Controller : RestaurantController
- Ajouter methode `ranking()` : top 50 restaurants par popularite, filtrable par ville
- Ajouter `sort=popularity` dans `getRestaurants()` existant
- Cache 30 minutes

### Vue : `app/Views/restaurants/ranking.php`
- Podium top 3 (medailles or/argent/bronze, style leaderboard users)
- Liste 4-50 avec rang, card compacte (photo, nom, ville, note, nb_avis, score)
- Filtre par ville (dropdown)
- Responsive

---

## F19 — Comparateur de Restaurants

### Concept
Comparer 2 a 4 restaurants cote a cote. Selection via bouton "Comparer" sur les cards + page dediee.

### Pas de migration SQL
Tout cote client (localStorage) + API existante.

### Routes
- `GET /comparateur` → `ComparatorController@index`
- `GET /api/comparateur?ids=1,40,374` → `ComparatorController@apiCompare`

### Controller : ComparatorController (nouveau)
```php
class ComparatorController extends Controller {
    public function index()    // Page comparateur (vue avec JS)
    public function apiCompare() // API JSON avec donnees detaillees
}
```

### API Response `/api/comparateur?ids=1,40,374`
```json
{
  "restaurants": [
    {
      "id": 1,
      "nom": "...",
      "photo": "...",
      "ville": "...",
      "type_cuisine": "...",
      "price_range": "$$",
      "note_moyenne": 4.2,
      "nb_avis": 15,
      "vues_total": 1200,
      "popularity_score": 85.5,
      "horaires": {...},
      "options": {"wifi": 1, "terrasse": 1, "parking": 0, ...},
      "orders_enabled": true,
      "awards": ["travelers_choice"],
      "avg_notes": {"nourriture": 4.5, "service": 3.8, "ambiance": 4.0, "prix": 3.5},
      "recent_reviews_count_30d": 5,
      "pros_top3": ["Bonne cuisine", "Service rapide", "Belle terrasse"],
      "cons_top3": ["Bruyant", "Parking difficile"]
    }
  ]
}
```

### Vue : `app/Views/comparator/index.php`
- Barre de recherche pour ajouter des restaurants (autocomplete existant)
- Tableau de comparaison responsive :
  - Photo + nom + ville
  - Note globale + detail (nourriture/service/ambiance/prix)
  - Prix range
  - Nombre d'avis
  - Score popularite
  - Amenites (icones vert/rouge)
  - Horaires aujourd'hui
  - Awards
  - Pros/Cons les plus cites
  - Boutons : "Voir" / "Commander"
- localStorage pour persister la selection
- Max 4 restaurants
- Mobile : scroll horizontal ou accordeon

### Bouton "Comparer" sur les cards
- Ajouter un bouton discret sur `_card.php` : icone balance
- Click → ajoute l'id au localStorage `compare_list`
- Badge compteur dans le header "Comparer (3)"
- Lien vers `/comparateur`

---

## F24 — Statistiques Publiques /stats

### Concept
Page publique montrant les statistiques agregees de la plateforme. Vitrine pour les investisseurs, les medias, et les utilisateurs curieux.

### Pas de migration SQL
Tout calcule a partir des tables existantes.

### Routes
- `GET /stats` → `StatsController@index`
- `GET /stats/{ville}` → `StatsController@cityStats`

### Controller : StatsController (nouveau)
```php
class StatsController extends Controller {
    public function index()     // Page stats globales
    public function cityStats($ville)  // Stats par ville
}
```

### Donnees a afficher (toutes cachees 1h)

**KPIs Hero** :
- Total restaurants (COUNT restaurants WHERE status='validated')
- Total avis (COUNT reviews WHERE status='approved')
- Total utilisateurs (COUNT users)
- Total commandes (COUNT orders)
- Villes couvertes (COUNT DISTINCT ville FROM restaurants)
- Note moyenne plateforme (AVG note_moyenne)

**Graphiques** :
1. **Top 10 villes** par nombre de restaurants (bar chart horizontal)
2. **Top 10 cuisines** par nombre de restaurants (bar chart)
3. **Distribution des notes** (1-5 etoiles, bar chart)
4. **Restaurants ajoutes par mois** (line chart, 12 derniers mois)
5. **Top 10 restaurants** par popularite (table avec medailles)

**Stats par ville** (page /stats/{ville}) :
- Memes KPIs filtrés par ville
- Top restaurants de la ville
- Cuisines populaires dans la ville
- Distribution des notes dans la ville

### Vue : `app/Views/stats/index.php`
- Hero avec 6 KPI cards animees (compteur AOS)
- Section graphiques (Chart.js)
- Section "Top restaurants" avec podium
- Section villes (cards cliquables → /stats/{ville})
- Dark mode compatible
- Full responsive

---

## Fichiers a creer/modifier

### Nouveaux fichiers
1. `app/Controllers/ComparatorController.php`
2. `app/Controllers/StatsController.php`
3. `app/Views/restaurants/ranking.php`
4. `app/Views/comparator/index.php`
5. `app/Views/stats/index.php`
6. `app/Views/stats/city.php`
7. `database/sprint4_valeur_ajoutee.sql`

### Fichiers a modifier
1. `app/Controllers/RestaurantController.php` — ajouter sort=popularity + methode ranking()
2. `app/Models/Restaurant.php` — ajouter recalculatePopularity(), getTopByPopularity()
3. `app/Views/restaurants/_card.php` — bouton "Comparer"
4. `app/Views/partials/header.php` — lien Comparateur (badge count)
5. `config/routes.php` — 5 nouvelles routes

### Migration SQL
```sql
-- Sprint 4: Valeur Ajoutee
ALTER TABLE restaurants ADD COLUMN popularity_score FLOAT DEFAULT 0 AFTER vues_total;
CREATE INDEX idx_restaurants_popularity ON restaurants(popularity_score DESC);

-- Calcul initial du score
UPDATE restaurants r SET popularity_score = (
  COALESCE(r.note_moyenne, 0) * 20 +
  COALESCE(r.nb_avis, 0) * 5 +
  COALESCE(r.vues_total, 0) * 0.01 +
  (SELECT COUNT(*) FROM orders o WHERE o.restaurant_id = r.id AND o.status = 'delivered') * 10 +
  (SELECT IF(COUNT(*) > 0, 15, 0) FROM restaurant_awards ra WHERE ra.restaurant_id = r.id)
) WHERE r.status = 'validated';
```
