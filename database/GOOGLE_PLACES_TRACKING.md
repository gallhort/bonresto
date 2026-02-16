# Google Places API - Suivi Mensuel

## Quotas gratuits (depuis mars 2025)
| SKU | Quota gratuit/mois | Prix au-dela |
|-----|-------------------|-------------|
| Nearby Search Enterprise | 1 000 | $0.040/req |
| Place Details Enterprise | 1 000 | $0.035/req |
| Place Photos Enterprise | 1 000 | $0.007/req |

Reset : **1er de chaque mois, minuit Pacific Time (UTC-8)**

---

## Fevrier 2026 (Mois 1)

**Date execution** : 2026-02-10
**Cap configure** : 900 appels Nearby Search

### Appels realises (verifies via Google Cloud Console)
| SKU | Script populate | Script fix photos (bugge) | **Total Google** | Gratuit | Depassement | Cout |
|-----|----------------|--------------------------|-----------------|---------|-------------|------|
| SearchNearby | 58 | 5 (tests) | **63** | 1 000 | 0 | $0.00 |
| GetPlace (Details) | 0 | 1 333 | **1 333** | 1 000 | 333 | $11.66 |
| GetPhotoMedia | 796 | 1 226 | **2 022** | 1 000 | 1 022 | $7.15 |
| **Total** | **854** | **2 564** | **3 418** | 3 000 | 1 355 | **$18.81** |

> **Note** : Le script de fix photos (correction du bug `photo_url` → `filename/path`)
> a consomme ~2 564 appels supplementaires (GetPlace + GetPhotoMedia) pour rien car
> l'INSERT en BDD echouait. Les photos ont ete re-inserees en local sans appel API.

### Zones traitees (57 zones sur 65)

**Alger — 21 communes (488 restaurants total)**
| Zone | Coord. | Rayon | Trouves | Inseres |
|------|--------|-------|---------|---------|
| Cheraga | 36.77, 2.95 | 3km | 20 | 19 |
| Draria | 36.72, 2.95 | 3km | 20 | 10 (+re-run) |
| Ain Benian | 36.80, 2.92 | 3km | 20 | 18 |
| Zeralda | 36.71, 2.85 | 3km | 20 | 18 |
| Ouled Fayet | 36.73, 2.93 | 3km | 20 | 5 |
| El Achour | 36.73, 2.97 | 3km | 20 | 14 |
| Staoueli | 36.76, 2.88 | 3km | 20 | 18 |
| Baba Hassen | 36.69, 2.98 | 3km | 20 | 8 |
| Douera | 36.67, 2.93 | 3km | 20 | 16 |
| Khraicia | 36.68, 3.00 | 3km | 20 | 13 |
| Mohammadia | 36.73, 3.15 | 3km | 20 | 20 |
| El Harrach | 36.72, 3.13 | 3km | 20 | 9 |
| Oued Smar | 36.71, 3.17 | 3km | 20 | 11 |
| Rouiba | 36.73, 3.28 | 3km | 20 | 19 |
| Reghaia | 36.74, 3.34 | 3km | 20 | 15 |
| Bordj El Kiffan | 36.75, 3.19 | 3km | 20 | 7 |
| Dar El Beida | 36.71, 3.21 | 3km | 20 | 5 |
| Baraki | 36.67, 3.08 | 3km | 20 | 13 |
| Birtouta | 36.63, 3.05 | 3km | 17 | 14 |
| Eucalyptus | 36.68, 3.11 | 3km | 20 | 3 |
| Sidi Moussa | 36.62, 3.10 | 3km | 20 | 15 |

**Oran — 10 communes (298 restaurants total)**
| Zone | Coord. | Rayon | Trouves | Inseres |
|------|--------|-------|---------|---------|
| Es-Senia | 35.64, -0.62 | 3km | 20 | 19 |
| El Kerma | 35.62, -0.59 | 3km | 20 | 10 |
| Ain El Turk | 35.73, -0.71 | 3km | 12 | 10 |
| Arzew | 35.82, -0.32 | 4km | 20 | 16 |
| Bethioua | 35.78, -0.26 | 3km | 17 | 15 |
| Gdyel | 35.78, -0.47 | 3km | 0 | 0 |
| Hassi Bounif | 35.66, -0.53 | 3km | 13 | 7 |
| Misserghine | 35.63, -0.68 | 3km | 20 | 15 |
| Boutlelis | 35.63, -0.73 | 3km | 20 | 14 |
| Ain El Turk (2) | 35.74, -0.77 | 3km | 20 | 18 |
| Sidi Chahmi | 35.69, -0.56 | 3km | 20 | 13 |

**Villes a 0 restaurant (19 villes)**
| Ville | Rayon | Trouves | Inseres |
|-------|-------|---------|---------|
| Medea | 6km | 20 | 17 |
| M'sila | 6km | 20 | 14 |
| Chlef | 6km | 20 | 19 |
| Bouira | 6km | 20 | 20 |
| Skikda | 6km | 20 | 19 |
| Sidi Bel Abbes | 6km | 20 | 20 |
| El Oued | 6km | 20 | 9 |
| Ouargla | 6km | 20 | 16 |
| Bechar | 5km | 20 | 16 |
| Relizane | 5km | 20 | 20 |
| Saida | 5km | 20 | 17 |
| Tiaret | 6km | 20 | 17 |
| Bordj Bou Arreridj | 5km | 20 | 19 |
| Souk Ahras | 5km | 20 | 17 |
| Mascara | 5km | 20 | 19 |
| Guelma | 5km | 20 | 15 |
| Khenchela | 5km | 20 | 20 |
| Ain Temouchent | 5km | 20 | 20 |
| Tebessa | 5km | 20 | 16 |

**Villes avec restaurants existants (enrichissement)**
| Ville | Avant | Apres | Nouveaux |
|-------|-------|-------|----------|
| Jijel | 3 | 23 | +20 |
| Blida | 5 | 24 | +19 |
| Batna | 6 | 23 | +17 |
| Bejaia | 9 | 37 | +19 (dont 9 existants) |
| Djelfa | 4 | 19 | +15 |
| Mostaganem | 4 | 24 | +20 |

**Zones SKIP (deja bien couvertes, >10 dans rayon 2km)**
Tipaza (11), Biskra (14), Ghardaia (14), Constantine (15), Annaba (15), Tizi Ouzou (16), Tlemcen (17), Setif (18)

### Resultats finaux
| Metrique | Valeur |
|----------|--------|
| Appels Nearby Search | 58 /1000 |
| Appels Place Details | 854 /1000 |
| Appels Place Photos | 796 /1000 |
| Restaurants trouves | 1 099 |
| Doublons evites | 269 (24%) |
| **Nouveaux inseres** | **830** |
| **Avis Google inseres** | **3 874** |
| **Photos telechargees** | **796** |
| Sans photo Google | 58 |
| Erreurs | 0 |
| **Cout (script populate)** | **$0.00** |
| **Cout total (avec fix bugge)** | **~$18.81** |

### Distribution finale par ville (top 20)
| Ville | Restaurants |
|-------|------------|
| Alger | 488 |
| Oran | 298 |
| Bejaia | 37 |
| Blida | 24 |
| Mostaganem | 24 |
| Jijel | 23 |
| Batna | 23 |
| Constantine | 21 |
| Ain Temouchent | 20 |
| Annaba | 20 |
| Khenchela | 20 |
| Sidi Bel Abbes | 20 |
| Bouira | 20 |
| Relizane | 20 |
| BBA | 19 |
| Djelfa | 19 |
| Skikda | 19 |
| Chlef | 19 |
| Mascara | 19 |
| Setif | 18 |

### Commandes executees
```bash
# 1. Migration SQL
mysql -u root lebonresto < database/phase16_google_places_prep.sql

# 2. Peuplement (58 appels Nearby Search)
python database/populate_google_places.py --api-key AIza... --max-per-zone 20 --max-total-calls 899

# 3. Photos (854 Place Details + 796 Place Photos)
# Script inline pour telecharger 1 photo/restaurant via Place Details + Photo API
```

---

## Mars 2026 (Mois 2) — A PLANIFIER

**Quota reset** : 1er mars 2026
**Objectif** : Enrichir les zones non couvertes + centres Alger/Oran

### Quotas restants fevrier (pas reportes)
| SKU | Utilises (Google) | Restants gratuits (perdus 1er mars) |
|-----|-------------------|-------------------------------------|
| Nearby Search | 63 | 937 |
| Place Details | 1 333 | 0 (depasse de 333) |
| Place Photos | 2 022 | 0 (depasse de 1 022) |

### Zones prioritaires mars
1. **Zones SKIP fevrier** (deja couvertes mais enrichissables) :
   - Tipaza, Biskra, Ghardaia, Constantine, Annaba, Tizi Ouzou, Tlemcen, Setif
2. **Alger centre** (nouvelles communes) :
   - Hydra, El Biar, Ben Aknoun, Dely Ibrahim, Bouzareah, Kouba, Hussein Dey
3. **Oran centre** :
   - Sidi El Houari, Canastel, USTO, Bir El Djir
4. **Nouvelles villes** :
   - Mila, Ain Defla, Tissemsilt, Naama, El Bayadh

### Resultats
| Metrique | Valeur |
|----------|--------|
| Appels Nearby Search | ___ /1000 |
| Appels Place Details | ___ /1000 |
| Appels Place Photos | ___ /1000 |
| Nouveaux inseres | ___ |
| Cout | $___ |

---

## Avril 2026 (Mois 3) — A PLANIFIER

**Objectif** : Derniere passe, zones touristiques, nettoyage doublons

### Actions
- [ ] Recherche zones touristiques (Timimoun, Djanet, etc.)
- [ ] Nettoyage doublons GPS (requete SQL)
- [ ] Mise a jour des restaurants existants (re-fetch rating/reviews)

---

## Historique cumule

| Mois | Restaurants ajoutes | Total BDD | Cout |
|------|--------------------:|----------:|-----:|
| Avant | — | 552 | $0 |
| Fev 2026 | +830 | 1 406 | ~$18.81 |
| Mars 2026 | ___ | ___ | $___ |
| Avr 2026 | ___ | ___ | $___ |
