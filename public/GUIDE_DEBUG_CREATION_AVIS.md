# 🔍 DIAGNOSTIC : Erreur création avis réel

## ❌ PROBLÈME

**Symptôme :** "Erreur lors de l'envoi. Veuillez réessayer."

**Contexte :**
- ✅ Script `test-ai-diagnostic.php` → Tout fonctionne
- ❌ Création avis réel via formulaire → Erreur

**Causes possibles :**
1. Colonne BDD manquante (pas détectée par script test)
2. Erreur PHP silencieuse
3. Données formulaire mal formatées
4. Problème ReviewModel

---

## 🔧 DIAGNOSTIC EN 3 ÉTAPES

### **ÉTAPE 1 : Test insertion BDD directe**

**But :** Vérifier que la BDD accepte bien les insertions

**1. Place** `test-insert-bdd.php` dans `/public/`

**2. Visite** `http://tonsite.com/test-insert-bdd.php`

**3. Lis les résultats :**

#### ✅ **Cas 1 : Tout vert**
```
✅ Toutes les colonnes IA présentes
✅ Insertion minimale réussie ! ID = 123
✅ Insertion IA réussie ! ID = 124
```
**→ La BDD est OK, le problème est dans le code**

**Passe à ÉTAPE 2**

#### ❌ **Cas 2 : Colonnes manquantes**
```
❌ COLONNES MANQUANTES :
spam_score, spam_details, ...
```

**→ Migration pas complète**

**SOLUTION :**
Le script affiche la migration SQL à copier/coller.
Exécute-la dans phpMyAdmin, puis recommence ÉTAPE 1.

#### ❌ **Cas 3 : Erreur insertion**
```
❌ Erreur insertion
Array ( [0] => HY000 [1] => 1364 [2] => Field 'visit_month' doesn't have a default value )
```

**→ Colonne requise manquante**

**SOLUTION :**
Regarde l'erreur exacte. Si c'est `visit_month`, `visit_year`, etc. :
```sql
ALTER TABLE reviews MODIFY COLUMN visit_month VARCHAR(20) NULL;
ALTER TABLE reviews MODIFY COLUMN visit_year INT NULL;
ALTER TABLE reviews MODIFY COLUMN trip_type VARCHAR(50) NULL;
```

---

### **ÉTAPE 2 : Activer les logs détaillés**

**But :** Voir EXACTEMENT où ça bloque

**1. Ouvre** `public/index.php`

**2. Ajoute EN TOUT PREMIER** (ligne 2) :
```php
<?php
// DEBUG MODE - À SUPPRIMER APRÈS
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/debug.log');

// Créer dossier logs
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Le reste du fichier...
require_once __DIR__ . '/../vendor/autoload.php';
...
```

**3. Donne les droits** au dossier logs :
```bash
mkdir logs
chmod 777 logs
```

**4. Soumets un avis**

**5. DEUX endroits où regarder :**

#### A) **Erreur affichée dans le navigateur**

Si erreur PHP fatale, elle s'affichera directement.

**Erreurs courantes :**

**Erreur :** `Call to undefined method Review::createReview()`
```
→ ReviewModel pas mis à jour
→ Solution : Vérifie que Review.php contient bien createReview() avec colonnes IA
```

**Erreur :** `Unknown column 'spam_score'`
```
→ Migration BDD pas faite
→ Solution : Exécute migration_ai_moderation.sql
```

**Erreur :** `Class 'App\Helpers\ReviewModerationHelper' not found`
```
→ Fichier Helper mal placé
→ Solution : Vérifie app/Helpers/ReviewModerationHelper.php existe
```

#### B) **Fichier logs/debug.log**

Ouvre `logs/debug.log` :
```bash
tail -f logs/debug.log
```

**Lis les dernières lignes.** Tu verras quelque chose comme :
```
[24-Dec-2025 18:00:00] === DEBUT store() ===
[24-Dec-2025 18:00:00] Restaurant ID: 1
[24-Dec-2025 18:00:00] User authentifié: 1
[24-Dec-2025 18:00:00] Données reçues - Note: 5, Message length: 42
[24-Dec-2025 18:00:00] Validations passées
[24-Dec-2025 18:00:00] ERROR: createReview() a retourné FALSE
[24-Dec-2025 18:00:00] PDO errorInfo: Array ( [0] => HY000 [1] => 1364 [2] => Field 'visit_month' doesn't have a default value )
```

**→ Là tu sauras EXACTEMENT où ça bloque !**

---

### **ÉTAPE 3 : Version debug du Controller**

**Si les logs ne donnent rien de clair :**

**1. Ouvre** `app/Controllers/ReviewController.php`

**2. Remplace la méthode `store()`** par le contenu de `store_method_DEBUG.php`

**3. Soumets un avis**

**4. Lis** `logs/debug.log`

**Tu verras chaque étape :**
```
=== DEBUT store() ===
Restaurant ID: 1
User authentifié: 1
Données reçues - Note: 5, Message length: 50
Validations passées
reviewData construit
IA: Désactivée (mode debug)
Valeurs IA définies - spam_score: 100
Tentative insertion BDD...
reviewData complet: {"restaurant_id":1,"user_id":1,...}
createReview() retourné: FALSE
ERROR: createReview() a retourné FALSE
PDO errorInfo: Array(...)
=== FIN store() ===
```

**→ Tu sauras exactement à quelle ligne ça plante !**

---

## 🎯 SOLUTIONS RAPIDES PAR TYPE D'ERREUR

### **Erreur A : Colonnes BDD manquantes**

```sql
-- Exécute TOUT ça
ALTER TABLE reviews 
ADD COLUMN spam_score INT DEFAULT 100,
ADD COLUMN spam_details TEXT DEFAULT NULL,
ADD COLUMN moderated_by ENUM('manual', 'ai') DEFAULT 'manual',
ADD COLUMN moderated_at TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN ai_rejected TINYINT(1) DEFAULT 0;
```

**Vérification :**
```sql
SHOW COLUMNS FROM reviews LIKE 'spam%';
```

---

### **Erreur B : Colonnes NULL non autorisées**

Si erreur `Field 'X' doesn't have a default value` :

```sql
-- Rendre colonnes optionnelles
ALTER TABLE reviews MODIFY COLUMN visit_month VARCHAR(20) NULL;
ALTER TABLE reviews MODIFY COLUMN visit_year INT NULL;
ALTER TABLE reviews MODIFY COLUMN trip_type VARCHAR(50) NULL;
ALTER TABLE reviews MODIFY COLUMN title VARCHAR(255) NULL;
```

---

### **Erreur C : ReviewModel pas à jour**

**Vérifie** que `app/Models/Review.php` contient :

```php
public function createReview(array $data): int|false
{
    $sql = "INSERT INTO reviews 
            (restaurant_id, user_id, author_name, title, message, 
             note_globale, note_nourriture, note_service, note_ambiance, note_prix,
             status, source,
             spam_score, spam_details, moderated_by, moderated_at, ai_rejected,
             created_at)
            VALUES 
            (:restaurant_id, :user_id, :author_name, :title, :message,
             :note_globale, :note_nourriture, :note_service, :note_ambiance, :note_prix,
             :status, :source,
             :spam_score, :spam_details, :moderated_by, :moderated_at, :ai_rejected,
             NOW())";
    
    // Valeurs par défaut
    $data['spam_score'] = $data['spam_score'] ?? 100;
    $data['spam_details'] = $data['spam_details'] ?? null;
    $data['moderated_by'] = $data['moderated_by'] ?? 'manual';
    $data['moderated_at'] = $data['moderated_at'] ?? null;
    $data['ai_rejected'] = $data['ai_rejected'] ?? 0;
    $data['source'] = $data['source'] ?? 'web';
    
    $this->query($sql, $data);
    return $this->db->lastInsertId();
}
```

**Si pas ça → Remplace par le fichier `Review.php` que je t'ai donné**

---

### **Erreur D : Fichiers IA manquants**

**Vérification rapide :**
```bash
ls -la app/Services/SpamDetector.php
ls -la app/Helpers/ReviewModerationHelper.php
```

**Les 2 doivent exister !**

Si manquant → Réinstalle-les.

---

## 📋 CHECKLIST COMPLÈTE

Coche au fur et à mesure :

- [ ] `test-insert-bdd.php` → TOUS les tests ✅
- [ ] Logs activés (`display_errors = 1`)
- [ ] Dossier `logs/` créé avec droits 777
- [ ] `Review.php` contient `createReview()` avec colonnes IA
- [ ] Migration SQL complète (toutes colonnes IA présentes)
- [ ] Colonnes `visit_*` et `trip_type` acceptent NULL
- [ ] Fichiers `SpamDetector.php` et `ReviewModerationHelper.php` présents

---

## 🆘 SI TOUJOURS BLOQUÉ

**Envoie-moi :**

1. **Résultat** de `test-insert-bdd.php` (copie/colle la page)
2. **Contenu** de `logs/debug.log` après tentative
3. **Résultat** de cette requête SQL :
```sql
SHOW CREATE TABLE reviews;
```

**Avec ça je pourrai identifier le problème exact ! 🔍**
