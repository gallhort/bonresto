# 🚀 Installation de BonResto MVC

Guide d'installation pas à pas pour mettre en place le projet.

## ⚙️ Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7+ ou MariaDB 10.3+
- Apache 2.4+ avec mod_rewrite activé
- (Optionnel) Composer pour les dépendances futures

## 📦 Installation sur XAMPP (Windows)

### 1. Télécharger et installer XAMPP
- Télécharger XAMPP : https://www.apachefriends.org/
- Installer dans `C:\xampp`
- Démarrer Apache et MySQL

### 2. Placer le projet
```bash
Copier le dossier bonresto_mvc dans :
C:\xampp\htdocs\bonresto_mvc
```

### 3. Créer la base de données
1. Ouvrir http://localhost/phpmyadmin
2. Créer une nouvelle base : `lebonresto`
3. Importer le fichier `lebonresto.sql`

### 4. Configuration
Éditer le fichier `.env` à la racine :
```env
DB_HOST=localhost
DB_NAME=lebonresto
DB_USER=sam
DB_PASS=123
DB_CHARSET=utf8mb4

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/bonresto_mvc/public
```

### 5. Configuration Apache

#### Option A : Utiliser un sous-dossier
Accéder via : `http://localhost/bonresto_mvc/public`

Pas de configuration supplémentaire nécessaire.

#### Option B : Créer un Virtual Host (Recommandé)

Éditer `C:\xampp\apache\conf\extra\httpd-vhosts.conf` :

```apache
<VirtualHost *:80>
    ServerName bonresto.local
    DocumentRoot "C:/xampp/htdocs/bonresto_mvc/public"
    
    <Directory "C:/xampp/htdocs/bonresto_mvc/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog "logs/bonresto-error.log"
    CustomLog "logs/bonresto-access.log" common
</VirtualHost>
```

Éditer `C:\Windows\System32\drivers\etc\hosts` (en admin) :
```
127.0.0.1 bonresto.local
```

Redémarrer Apache.

Accéder via : `http://bonresto.local`

### 6. Permissions
Sur Windows, généralement pas de problème de permissions.
Si nécessaire, donner les droits d'écriture sur :
- `storage/logs/`
- `public/uploads/`

### 7. Tester
Ouvrir votre navigateur :
- http://localhost/bonresto_mvc/public (Option A)
- http://bonresto.local (Option B)

Vous devriez voir la page d'accueil !

## 📦 Installation sur Linux/Mac

### 1. Placer le projet
```bash
cd /var/www/html  # ou ~/Sites sur Mac
git clone [votre-repo] bonresto_mvc
# ou décompresser l'archive
```

### 2. Installer les dépendances (optionnel)
```bash
cd bonresto_mvc
composer install  # si vous avez Composer
# Sinon, l'autoloader manuel fonctionne déjà
```

### 3. Configuration de la base de données
```bash
mysql -u root -p
CREATE DATABASE lebonresto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sam'@'localhost' IDENTIFIED BY '123';
GRANT ALL PRIVILEGES ON lebonresto.* TO 'sam'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importer le SQL
mysql -u sam -p123 lebonresto < lebonresto.sql
```

### 4. Configuration du fichier .env
```bash
cp .env.example .env
nano .env  # ou vim, ou votre éditeur préféré
```

Éditer :
```env
DB_HOST=localhost
DB_NAME=lebonresto
DB_USER=sam
DB_PASS=123
DB_CHARSET=utf8mb4

APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost  # ou votre domaine
```

### 5. Permissions
```bash
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data public/uploads/
sudo chmod -R 755 storage/
sudo chmod -R 755 public/uploads/
```

### 6. Configuration Apache

#### Créer un Virtual Host
```bash
sudo nano /etc/apache2/sites-available/bonresto.conf
```

Ajouter :
```apache
<VirtualHost *:80>
    ServerName bonresto.local
    ServerAdmin admin@bonresto.local
    DocumentRoot /var/www/html/bonresto_mvc/public
    
    <Directory /var/www/html/bonresto_mvc/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/bonresto-error.log
    CustomLog ${APACHE_LOG_DIR}/bonresto-access.log combined
</VirtualHost>
```

Activer le site :
```bash
sudo a2ensite bonresto.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Ajouter au fichier hosts :
```bash
sudo nano /etc/hosts
# Ajouter :
127.0.0.1 bonresto.local
```

### 7. Tester
```bash
curl http://bonresto.local
# ou ouvrir dans le navigateur
```

## 🧪 Vérification de l'installation

### Checklist
- [ ] La page d'accueil s'affiche correctement
- [ ] Le CSS est chargé (header coloré, design moderne)
- [ ] Les images/icônes s'affichent
- [ ] Pas d'erreur PHP dans les logs
- [ ] La base de données est accessible

### Tests rapides
1. **Page d'accueil** : http://bonresto.local
2. **Test de recherche** : Entrer une ville et chercher
3. **Admin** : http://bonresto.local/admin/login
   - User: sam
   - Pass: 123 (à changer en production !)

### Vérifier les logs
```bash
# Apache errors
tail -f /var/log/apache2/bonresto-error.log

# PHP errors (si APP_DEBUG=true)
tail -f storage/logs/error.log
```

## 🔧 Dépannage

### Problème : Page blanche
**Solution** :
```bash
# Vérifier les logs Apache
# Vérifier que mod_rewrite est activé
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Problème : Erreur 500
**Solution** :
```bash
# Activer le debug
# Dans .env :
APP_DEBUG=true

# Vérifier les permissions
chmod -R 755 storage/
```

### Problème : Base de données inaccessible
**Solution** :
```bash
# Vérifier les credentials dans .env
# Tester la connexion :
mysql -u sam -p123 lebonresto
```

### Problème : CSS/JS ne chargent pas
**Solution** :
```bash
# Vérifier le chemin dans .env :
APP_URL=http://bonresto.local  # sans /public

# Vérifier les permissions
chmod -R 755 public/assets/
```

### Problème : Erreur "Class not found"
**Solution** :
```bash
# Vérifier que vendor/autoload.php existe
# Vérifier les namespaces dans les fichiers
# Nettoyer le cache (si applicable)
```

## 🔒 Sécurité pour la production

Avant de mettre en production :

1. **Modifier .env** :
```env
APP_ENV=production
APP_DEBUG=false
```

2. **Changer les mots de passe** :
```sql
-- Changer le mot de passe admin
UPDATE users SET password = PASSWORD('nouveau_mdp_fort') WHERE email = 'admin@bonresto.fr';
```

3. **Permissions strictes** :
```bash
chmod 640 .env
chmod -R 755 public/
chmod -R 750 storage/
```

4. **HTTPS** :
```bash
sudo certbot --apache -d votredomaine.fr
```

5. **Désactiver les fonctions PHP dangereuses** :
Dans php.ini :
```ini
disable_functions = exec,passthru,shell_exec,system
```

## 📝 Prochaines étapes

1. [ ] Tester toutes les fonctionnalités
2. [ ] Personnaliser le design
3. [ ] Ajouter du contenu
4. [ ] Configurer les emails
5. [ ] Optimiser les performances
6. [ ] Mettre en place les backups

## 🆘 Support

En cas de problème :
1. Vérifier la section Dépannage ci-dessus
2. Consulter les logs : `storage/logs/`
3. Activer le mode debug : `APP_DEBUG=true`
4. Contacter : support@bonresto.fr

---

**Bon développement ! 🚀**
