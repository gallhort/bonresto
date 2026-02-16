# STORY-006 : QR Code commande + notifications email

**Epic:** F33 - Commande en ligne
**Priorite:** Should Have
**Story Points:** 3
**Status:** Not Started
**Sprint:** 2
**Depends on:** STORY-003, STORY-004

---

## User Story

En tant que **proprietaire de restaurant**,
je veux pouvoir **generer un QR code pointant vers ma page de commande et que mes clients recoivent des emails de suivi**,
afin de **faciliter l'acces a la commande en ligne et tenir les clients informes**.

---

## Description

QR code dans le dashboard owner pointant vers `/commander/{slug}`. Telecharger en PNG + imprimer. Emails envoyes au client a chaque changement de statut important (confirmation, pret/en livraison).

## Scope

**In scope :**
- QR code dans l'onglet Commandes du dashboard (ou onglet QR existant)
- URL du QR : `/commander/{slug}`
- Telechargement PNG + bouton imprimer
- Email au client : commande confirmee (avec temps estime)
- Email au client : commande prete (retrait) ou en livraison (delivery)
- 7 nouvelles methodes dans NotificationService (notifyOrder*)

**Out of scope :**
- Push notifications navigateur
- SMS

---

## Criteres d'acceptation

- [ ] Le QR code pointe vers `/commander/{slug}` (URL complete)
- [ ] Le QR code est affichable dans le dashboard owner
- [ ] Le QR code peut etre telecharge en PNG
- [ ] Le QR code peut etre imprime (print-friendly)
- [ ] Un email est envoye au client quand la commande est confirmee
- [ ] Un email est envoye au client quand la commande est prete/en livraison
- [ ] Les emails contiennent : nom restaurant, detail commande, temps estime
- [ ] Si l'email echoue, pas d'erreur visible (fallback notification in-app)
- [ ] Les 7 methodes notifyOrder* sont ajoutees a NotificationService

---

## Notes techniques

### QR Code
Reutiliser `qrcodejs` CDN deja utilise dans le dashboard (onglet QR pour les avis).

### Email
```php
// Template simple HTML inline
function sendOrderEmail(string $to, string $subject, string $body): bool {
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: noreply@lebonresto.dz";
    return @mail($to, $subject, $body, $headers);
}
```

### Fichiers a modifier
- `app/Views/owner/edit.php` : QR code dans onglet Commandes
- `app/Services/NotificationService.php` : 7 methodes notifyOrder*
- `app/Controllers/OrderController.php` : appels email dans respond() et updateStatus()

---

**Cree avec BMAD Method v6**
