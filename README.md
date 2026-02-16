# 🍽️ Le Bon Resto - MVC Edition

Annuaire des restaurants Halal en France - Version MVC refactorisée

## 🚀 Installation

### Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache/Nginx) avec mod_rewrite

### Étapes d'installation

1. **Cloner le projet**
```bash
cd /votre/repertoire/web
```

2. **Configuration de la base de données**
- Importer le fichier SQL : `lebonresto.sql` (fourni séparément)
- Créer un fichier `.env` à la racine :

```env
DB_HOST=localhost
DB_NAME=lebonresto
DB_USER=sam
DB_PASS=123
DB_CHARSET=utf8mb4

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
```

3. **Configuration Apache**

Pointer le DocumentRoot vers le dossier `/public`

Exemple de vhost:
```apache
<VirtualHost *:80>
    ServerName bonresto.local
    DocumentRoot "/path/to/bonresto_mvc/public"
    
    <Directory "/path/to/bonresto_mvc/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

4. **Permissions**
```bash
chmod -R 755 storage/
chmod -R 755 public/uploads/
```

5. **Accéder au site**
```
http://localhost  (ou votre domaine configuré)
```

## 📁 Structure du projet

```
bonresto_mvc/
├── app/                    # Code de l'application
│   ├── Controllers/        # Contrôleurs
│   ├── Models/            # Modèles
│   ├── Views/             # Vues
│   ├── Core/              # Classes Core (Router, Database, etc.)
│   ├── Services/          # Services métier
│   └── Helpers/           # Fonctions utilitaires
│
├── config/                # Configuration
│   └── routes.php         # Définition des routes
│
├── public/                # Point d'entrée web
│   ├── index.php          # Front controller
│   ├── assets/            # CSS, JS, images
│   └── uploads/           # Fichiers uploadés
│
├── storage/               # Logs et fichiers temporaires
├── tests/                 # Tests unitaires
├── vendor/                # Dépendances (autoloader)
├── .env                   # Configuration environnement
└── README.md
```

## 🎯 Fonctionnalités

### Pages publiques
- ✅ Page d'accueil avec recherche
- ✅ Recherche de restaurants par géolocalisation
- ✅ Détail d'un restaurant
- ✅ Système d'avis
- ✅ Wishlist (favoris)
- ✅ Profil utilisateur

### Administration
- ✅ Dashboard admin
- ✅ Validation/Rejet de restaurants
- ✅ Gestion des restaurants
- ✅ Mise en avant de restaurants

### API
- ✅ API REST pour récupérer les restaurants
- ✅ Endpoint pour les données carte

## 🔒 Sécurité

- ✅ PDO avec requêtes préparées (protection SQL injection)
- ✅ CSRF tokens
- ✅ Échappement des sorties (protection XSS)
- ✅ Validation des entrées
- ✅ Sessions sécurisées
- ✅ Authentification admin

## 🛠️ Technologies

- **Backend**: PHP 7.4+ (Architecture MVC)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **CSS Framework**: Custom (pas de Bootstrap)
- **Icons**: Font Awesome 6

## 📝 Routes principales

```
GET  /                              Page d'accueil
GET  /search                        Recherche de restaurants
GET  /restaurant/{id}               Détail restaurant
GET  /login                         Connexion
GET  /register                      Inscription
GET  /profil                        Profil utilisateur
GET  /wishlist                      Favoris

GET  /admin                         Dashboard admin
GET  /admin/restaurants/pending     Restaurants en attente
GET  /admin/restaurants/validated   Restaurants validés
```

## 🎨 Personnalisation

### Modifier les couleurs
Éditez `/public/assets/css/core/components.css` et changez les variables CSS

### Ajouter une nouvelle page
1. Créer un contrôleur dans `app/Controllers/`
2. Créer une vue dans `app/Views/`
3. Ajouter la route dans `config/routes.php`

## 🐛 Debug

En mode développement (`APP_DEBUG=true`), les erreurs sont affichées.

Logs disponibles dans `storage/logs/`

## 📧 Support

Pour toute question: contact@lebonresto.fr

## 📄 Licence

Propriétaire - Tous droits réservés

---

**Version**: 2.0.0 MVC  
**Date**: Décembre 2024
