# Projet Lebonresto (dev)

Ce dépôt est une application PHP monolithique (XAMPP) utilisée pour la liste / recherche / avis de restaurants.

## Configuration locale
- Copiez `.env.example` en `.env` et adaptez les valeurs :

```
DB_HOST=localhost
DB_NAME=lebonresto
DB_USER=your_user
DB_PASS=your_password
DB_CHARSET=utf8mb4
```

- Ne commitez pas `.env`. `.gitignore` contient déjà une règle pour ignorer `.env`.

## Tests rapides
- Linter PHP (sintaxe): `php -l <file>` ou utiliser `dev-tools/php-lint-report.ps1` (PowerShell).
- Smoke tests: recherche, page détail, laisser un avis, wishlist, admin validate/reject.
- Test minimal du wrapper PDO (local): `php tests/test_database.php` (renvoie `OK` si réussi).

## Contact
- Pour des questions, signalez les erreurs ici et je corrigerai rapidement.
