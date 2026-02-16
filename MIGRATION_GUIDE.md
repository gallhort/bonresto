# 📋 Guide de Migration - Ancien Code → MVC

## 🎯 Vue d'ensemble

Ce guide explique comment le code a été refactorisé de l'ancienne structure vers l'architecture MVC.

## 📊 Comparaison des structures

### AVANT (Ancien code)
```
bonresto_for_claude/
├── index.php (5058 lignes !)
├── result.php (1762 lignes)
├── detail-restaurant-2.php
├── header.php
├── admin-*.php (multiples fichiers)
├── connect.php
├── classes/
│   ├── connect.php
│   └── DatabasePDO.php
└── ... (50+ fichiers mélangés)
```

### APRÈS (MVC)
```
bonresto_mvc/
├── app/
│   ├── Controllers/ (logique)
│   ├── Models/ (données)
│   └── Views/ (affichage)
├── public/
│   └── index.php (point d'entrée unique)
├── config/
│   └── routes.php
└── .env (configuration)
```

## 🔄 Migrations effectuées

### 1. Page d'accueil (index.php)

**AVANT** : `index.php` - 5058 lignes
**APRÈS** : 
- Controller: `app/Controllers/HomeController.php` (35 lignes)
- Model: `app/Models/Restaurant.php` (métier + requêtes)
- View: `app/Views/home/index.php` (HTML propre)

**Mapping des fonctionnalités** :
```php
// AVANT
<?php
include 'connect.php';
$sql = "SELECT * FROM addresto WHERE...";
$result = mysqli_query($conn, $sql);
?>
<html>...</html>

// APRÈS
// Controller
public function index(Request $request) {
    $restaurants = $this->restaurantModel->getFeatured();
    $this->render('home.index', ['restaurants' => $restaurants]);
}

// Model
public function getFeatured() {
    return $this->query("SELECT * FROM {$this->table} WHERE...");
}

// View (HTML pur)
<?php foreach($restaurants as $resto): ?>
    ...
<?php endforeach; ?>
```

### 2. Recherche (result.php)

**À MIGRER** : `result.php` - 1762 lignes

**Plan de migration** :
```
1. Controller: app/Controllers/RestaurantController.php
   - Méthode: search()
   - Récupère les paramètres
   - Appelle le modèle
   - Rend la vue

2. Model: app/Models/Restaurant.php
   - Méthode: searchNearby()
   - Formule Haversine
   - Filtres (prix, type, options)

3. View: app/Views/restaurants/search.php
   - Affichage des résultats
   - Filtres
   - Carte
```

### 3. Détail Restaurant (detail-restaurant-2.php)

**À MIGRER** : 115KB de code

**Plan** :
```
Controller: RestaurantController@show
Model: Restaurant::find($id)
View: restaurants/detail.php
```

### 4. Admin

**AVANT** : 
- admin-dashboard.php
- admin-liste-attente.php
- admin-liste-valides.php
- admin-modifier-resto.php
- etc.

**APRÈS** :
```
app/Controllers/Admin/
├── DashboardController.php
├── RestaurantController.php
└── AuthController.php
```

## 🛠️ Comment migrer une page

### Étape 1 : Identifier la logique

Dans l'ancien fichier, identifiez :
- Les requêtes SQL
- La logique métier
- L'affichage HTML

### Étape 2 : Créer le Controller

```php
<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Restaurant;

class RestaurantController extends Controller
{
    public function search(Request $request)
    {
        // 1. Récupérer les paramètres
        $address = $request->post('adresse');
        $type = $request->post('type_list')[0] ?? null;
        
        // 2. Appeler le modèle
        $restaurantModel = new Restaurant();
        $results = $restaurantModel->searchNearby(...);
        
        // 3. Rendre la vue
        $this->render('restaurants.search', [
            'restaurants' => $results,
            'address' => $address
        ]);
    }
}
```

### Étape 3 : Créer/Compléter le Model

```php
<?php
namespace App\Models;

use App\Core\Model;

class Restaurant extends Model
{
    protected string $table = 'addresto';
    
    public function searchNearby($lat, $lon, $radius, $type = null)
    {
        // Requêtes SQL avec PDO
        $sql = "SELECT *, {$this->getDistanceFormula($lat, $lon)} as distance 
                FROM {$this->table} 
                WHERE status = 'validated'";
        
        // Etc.
        return $this->query($sql, $params);
    }
}
```

### Étape 4 : Créer la Vue

```php
<!-- app/Views/restaurants/search.php -->
<div class="search-results">
    <h1>Résultats pour "<?= e($address) ?>"</h1>
    
    <?php foreach($restaurants as $resto): ?>
        <div class="restaurant-card">
            <h3><?= e($resto['nom']) ?></h3>
            <p><?= e($resto['adresse']) ?></p>
        </div>
    <?php endforeach; ?>
</div>
```

### Étape 5 : Ajouter la route

```php
// config/routes.php
$router->get('/search', 'RestaurantController@search');
$router->post('/search', 'RestaurantController@search');
```

## 📝 Checklist de migration

Pour chaque page à migrer :

- [ ] Identifier la fonctionnalité
- [ ] Créer le controller
- [ ] Extraire la logique SQL dans le model
- [ ] Créer la vue (HTML propre)
- [ ] Ajouter la route
- [ ] Tester
- [ ] Déplacer CSS/JS spécifiques
- [ ] Documenter

## 🔍 Pages à migrer

### Priorité 1 (Core fonctionnel)
- [ ] Search / Results (result.php)
- [ ] Restaurant Detail (detail-restaurant-2.php)
- [ ] Reviews (leave-review.php)

### Priorité 2 (User)
- [ ] Login/Register (auth/)
- [ ] Profile (profil.php)
- [ ] Wishlist (viewwish.php)

### Priorité 3 (Admin)
- [ ] Admin Dashboard
- [ ] Pending Restaurants
- [ ] Validated Restaurants
- [ ] Edit Restaurant

### Priorité 4 (Features)
- [ ] Add Restaurant (inscription-restaurant.php)
- [ ] API Endpoints
- [ ] Ajax calls

## 💡 Best Practices

### Sécurité
✅ Toujours utiliser PDO avec requêtes préparées
✅ Échapper les sorties avec `e()` ou `htmlspecialchars()`
✅ Valider les entrées
✅ Utiliser CSRF tokens pour les formulaires

### Code Quality
✅ Une responsabilité par classe
✅ Controllers légers (orchestration)
✅ Models pour la logique métier
✅ Vues sans logique complexe
✅ Réutiliser les composants

### Performance
✅ Indexer les colonnes fréquemment recherchées
✅ Limiter les requêtes en boucle
✅ Utiliser le cache quand possible
✅ Optimiser les images

## 🎨 Organisation CSS/JS

### Avant
Chaque page a son CSS/JS intégré dans le fichier PHP.

### Après
```
public/assets/
├── css/
│   ├── core/ (styles globaux)
│   └── pages/ (styles par page)
└── js/
    ├── core/ (scripts globaux)
    └── pages/ (scripts par page)
```

Les vues incluent leurs assets via :
```php
$this->render('page', [
    'pageCSS' => ['search', 'map'],
    'pageJS' => ['search']
]);
```

## 🚀 Prochaines étapes

1. Migrer les pages priorité 1
2. Tester chaque fonctionnalité
3. Optimiser les requêtes
4. Documenter l'API
5. Ajouter des tests unitaires

## 📞 Questions ?

Consultez le README.md ou examinez le code existant de HomeController comme exemple.
