# Sécurisation & roadmap de refactor (résumé)

## ✅ Changements appliqués (résumé)
- Remplacement des identifiants BDD en dur par une logique d'environnement (`connect.php` / `classes/connect.php`).
- Ajout d'un loader `.env` local et d'un fallback de développement (root / mot de passe vide pour XAMPP) dans `connect.php` pour éviter les erreurs de connexion sur les environnements locaux.
- Remplacement de requêtes SQL vulnérables par des requêtes préparées dans les endpoints critiques (batch initial).
- Échappement des sorties utilisateur (htmlspecialchars) et validation stricte des entrées (casts numériques, whitelist des noms de colonnes pour options).
- Ajout d'un script PowerShell `dev-tools/php-lint-report.ps1` pour exécuter `php -l` sur fichiers importants.

## 🧪 Tests effectués
- Linting PHP (`php -l`) sur les fichiers modifiés: tous passés.
- Smoke tests réalisés: recherche, page détail, soumission d'avis (incl. upload), tests manuels de quelques endpoints — OK (rapport manuel fourni par l'utilisateur).

## 🔜 Prochaines étapes recommandées
Priorité haute (sécurité & stabilité):
1. Centraliser complètement l'accès à la base de données via un seul wrapper PDO (réduire usage de mysqli réparti).  
2. Éviter `unserialize()` sur des données non fiabilisées — remplacer par JSON pour les options encodées ou ajouter schéma/whitelists.
3. Ajouter règles stricte pour les uploads: type MIME, max size, et nom sécurisé.

Priorité moyenne (maintenabilité):
1. Introduire Composer et un autoloader minimal, extraire utilitaires (DB, Auth) en classes.  
2. Introduire quelques tests d'intégration basiques (utiliser sqlite en mémoire pour tests rapides).
3. Documenter endpoints principaux (README ou OpenAPI minimal).

Priorité basse (long terme):
1. Refaire la structure en MVC ou micro-framework (Slim/Laravel) selon disponibilité et budget.

## 🔧 Proposition de découpages de PRs (petits changements, faciles à reviewer)
- PR 1 (small): Ajouter tests unitaires de base + wrapper PDO minimal (non-invasive) et modifier 5-10 fichiers pour utiliser le wrapper.
- PR 2 (medium): Remplacer les usages restants de `unserialize(base64)` par JSON; ajouter validations whitelists.
- PR 3 (medium): Durcir la validation des uploads et centraliser la logique d'uploads.

## 📋 Checklist pour merger
- Exécuter `php -l` sur tout le repo
- Effectuer tests manuels sur les flux critiques (search, detail, review, admin)
- Factoriser progressivement en PRs petites et testées

---
_Note_: Je peux préparer la PR 1 (wrapper PDO + quelques remplacements) dès que vous me donnez le feu vert. Je peux aussi ajouter une courte doc sur comment exécuter les tests et déploiement.
