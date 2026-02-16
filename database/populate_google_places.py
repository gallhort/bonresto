"""
LeBonResto - Peuplement de restaurants via Google Places API (New)
================================================================
Stratégie : cibler uniquement les zones sous-couvertes pour économiser des requêtes.
- Cible par commune (Alger: 21 zones, Oran: 11 zones) + 34 autres villes
- Skip automatique des zones ayant déjà 10+ restaurants dans un rayon de 2km
- Déduplique par google_place_id ET par nom+ville (fuzzy)
- Télécharge 1 photo par restaurant (max 1000 = quota gratuit)
- Insère jusqu'à 5 avis Google par restaurant
- Log tout dans un fichier CSV

Quotas gratuits (depuis mars 2025):
- Nearby Search Enterprise: 1 000/mois (déterminé par photos+reviews dans le Field Mask)
- Place Photos Enterprise: 1 000/mois

Usage:
    python populate_google_places.py --api-key YOUR_KEY [--dry-run] [--max-per-zone 20] [--only-city Constantine]
"""

import argparse
import csv
import json
import os
import re
import sys
import time
import unicodedata
from datetime import datetime

import pymysql
import requests

# Fix Windows console encoding (cp1252 can't handle Arabic/special chars)
if sys.stdout.encoding != "utf-8":
    sys.stdout.reconfigure(encoding="utf-8", errors="replace")
if sys.stderr.encoding != "utf-8":
    sys.stderr.reconfigure(encoding="utf-8", errors="replace")

# ============================================================
# CONFIGURATION
# ============================================================

DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",
    "database": "lebonresto",
    "charset": "utf8mb4",
}

# Zones cibles : (ville, lat, lng, rayon_m)
# Stratégie par commune : cibler les zones sous-couvertes uniquement
TARGET_ZONES = [
    # ══════════════════════════════════════════════════════════
    # ALGER — Communes vides (❌) et faibles (⚠️)
    # Centre/Ouest déjà bien couvert, on cible la périphérie
    # ══════════════════════════════════════════════════════════
    # Ouest d'Alger
    ("Alger", 36.7667, 2.9500, 3000),   # Cheraga ❌
    ("Alger", 36.7167, 2.9500, 3000),   # Draria ❌
    ("Alger", 36.8025, 2.9200, 3000),   # Ain Benian ❌
    ("Alger", 36.7117, 2.8533, 3000),   # Zeralda ❌
    ("Alger", 36.7333, 2.9333, 3000),   # Ouled Fayet ⚠️
    ("Alger", 36.7267, 2.9667, 3000),   # El Achour ⚠️
    ("Alger", 36.7583, 2.8833, 3000),   # Staoueli ⚠️
    ("Alger", 36.6917, 2.9750, 3000),   # Baba Hassen ⚠️
    ("Alger", 36.6667, 2.9333, 3000),   # Douera ❌
    ("Alger", 36.6750, 3.0000, 3000),   # Khraicia ❌
    # Est d'Alger
    ("Alger", 36.7333, 3.1467, 3000),   # Mohammadia ❌
    ("Alger", 36.7200, 3.1333, 3000),   # El Harrach ❌
    ("Alger", 36.7067, 3.1667, 3000),   # Oued Smar ❌
    ("Alger", 36.7333, 3.2833, 3000),   # Rouiba ❌
    ("Alger", 36.7367, 3.3417, 3000),   # Reghaia ❌
    ("Alger", 36.7467, 3.1933, 3000),   # Bordj El Kiffan ⚠️
    ("Alger", 36.7133, 3.2133, 3000),   # Dar El Beida ⚠️
    # Sud d'Alger
    ("Alger", 36.6667, 3.0833, 3000),   # Baraki ❌
    ("Alger", 36.6333, 3.0500, 3000),   # Birtouta ❌
    ("Alger", 36.6800, 3.1100, 3000),   # Eucalyptus ❌
    ("Alger", 36.6167, 3.1000, 3000),   # Sidi Moussa ❌

    # ══════════════════════════════════════════════════════════
    # ORAN — Communes vides (❌) et faibles (⚠️)
    # ══════════════════════════════════════════════════════════
    ("Oran", 35.6350, -0.6217, 3000),   # Es-Senia ❌
    ("Oran", 35.6217, -0.5933, 3000),   # El Kerma ❌
    ("Oran", 35.7267, -0.7100, 3000),   # Mers El Kebir ❌
    ("Oran", 35.8228, -0.3203, 4000),   # Arzew ❌
    ("Oran", 35.7833, -0.2583, 3000),   # Bethioua ❌
    ("Oran", 35.7767, -0.4667, 3000),   # Gdyel ❌
    ("Oran", 35.6583, -0.5250, 3000),   # Hassi Bounif ❌
    ("Oran", 35.6333, -0.6833, 3000),   # Misserghine ❌
    ("Oran", 35.6300, -0.7267, 3000),   # Boutlelis ❌
    ("Oran", 35.7400, -0.7700, 3000),   # Ain El Turk ⚠️
    ("Oran", 35.6867, -0.5617, 3000),   # Sidi Chahmi ⚠️

    # ══════════════════════════════════════════════════════════
    # AUTRES VILLES — peu de restaurants ou absentes
    # ══════════════════════════════════════════════════════════
    ("Constantine", 36.3650, 6.6147, 8000),
    ("Annaba", 36.9000, 7.7667, 8000),
    ("Sétif", 36.1910, 5.4078, 8000),
    ("Béjaïa", 36.7509, 5.0567, 8000),
    ("Tlemcen", 34.8828, -1.3167, 8000),
    ("Tizi Ouzou", 36.7169, 4.0497, 8000),
    ("Biskra", 34.8484, 5.7286, 8000),
    ("Tipaza", 36.5894, 2.4486, 6000),
    ("Ghardaïa", 32.4900, 3.6700, 6000),
    ("Batna", 35.5567, 6.1742, 8000),
    ("Blida", 36.4700, 2.8300, 8000),
    ("Djelfa", 34.6700, 3.2500, 6000),
    ("Mostaganem", 35.9333, 0.0833, 6000),
    ("Jijel", 36.8200, 5.7667, 6000),
    # Grandes villes manquantes (0 restaurants)
    ("Médéa", 36.2675, 2.7500, 6000),
    ("M'sila", 35.7050, 4.5422, 6000),
    ("Chlef", 36.1650, 1.3317, 6000),
    ("Bouira", 36.3833, 3.9000, 6000),
    ("Skikda", 36.8764, 6.9061, 6000),
    ("Sidi Bel Abbès", 35.1897, -0.6308, 6000),
    ("El Oued", 33.3564, 6.8633, 6000),
    ("Ouargla", 31.9500, 5.3167, 6000),
    ("Béchar", 31.6167, -2.2167, 5000),
    ("Relizane", 35.7372, 0.5567, 5000),
    ("Saïda", 34.8303, 0.1517, 5000),
    ("Tiaret", 35.3711, 1.3178, 6000),
    ("Bordj Bou Arréridj", 36.0667, 4.7500, 5000),
    ("Souk Ahras", 36.2864, 7.9511, 5000),
    ("Mascara", 35.3964, 0.1403, 5000),
    ("Guelma", 36.4622, 7.4247, 5000),
    ("Khenchela", 35.4353, 7.1411, 5000),
    ("Aïn Temouchent", 35.2972, -1.1403, 5000),
    ("Tébessa", 35.4042, 8.1242, 5000),
]

# Google Places API (New) endpoints
PLACES_NEARBY_URL = "https://places.googleapis.com/v1/places:searchNearby"
PLACE_PHOTO_URL = "https://places.googleapis.com/v1/{}/media"

# Field mask - uniquement les champs mappés à notre BDD
# Tier le plus élevé : Enterprise (photos + reviews) → 1000 appels gratuits/mois
FIELD_MASK = ",".join([
    # ── Essentials (10 000 gratuits/mois) ──
    "places.id",                    # → restaurants.google_place_id
    "places.displayName",           # → restaurants.nom
    "places.formattedAddress",      # → restaurants.adresse
    "places.location",              # → restaurants.gps_latitude/longitude
    "places.types",                 # → restaurants.type_cuisine (via CUISINE_MAP)
    # ── Pro (5 000 gratuits/mois) ──
    "places.nationalPhoneNumber",   # → restaurants.phone
    "places.websiteUri",            # → restaurants.website
    "places.regularOpeningHours",   # → horaires table
    "places.priceLevel",            # → restaurants.price_range
    "places.rating",                # → restaurants.note_moyenne
    "places.userRatingCount",       # → restaurants.nb_avis
    # ── Enterprise (1 000 gratuits/mois) ──
    "places.photos",                # → restaurant_photos (1 photo/resto)
    "places.reviews",               # → reviews table (max 5/resto)
    "places.editorialSummary",      # → restaurants.descriptif
    "places.reservable",            # → restaurants.reservations_enabled
    "places.delivery",              # → restaurant_options.delivery
    "places.takeout",               # → restaurant_options.takeaway
    "places.outdoorSeating",        # → restaurant_options.terrace
    "places.allowsDogs",            # → restaurant_options.pets_allowed
    "places.goodForChildren",       # → restaurant_options.baby_chair
    "places.parkingOptions",        # → restaurant_options.parking
    "places.accessibilityOptions",  # → restaurant_options.handicap_access
    "places.servesBeer",            # → restaurants.verified_halal (inverse)
    "places.servesWine",            # → restaurants.verified_halal (inverse)
    "places.servesCocktails",       # → restaurants.verified_halal (inverse)
])

# Mapping type cuisine Google -> notre système
CUISINE_MAP = {
    "italian_restaurant": "Italien",
    "pizza_restaurant": "Pizzeria",
    "french_restaurant": "Français",
    "chinese_restaurant": "Chinois",
    "japanese_restaurant": "Japonais",
    "indian_restaurant": "Indien",
    "mexican_restaurant": "Mexicain",
    "turkish_restaurant": "Turc",
    "lebanese_restaurant": "Libanais",
    "seafood_restaurant": "Poissons/Fruits de mer",
    "steak_house": "Grillades",
    "hamburger_restaurant": "Fast food",
    "fast_food_restaurant": "Fast food",
    "sandwich_shop": "Fast food",
    "cafe": "Café-Restaurant",
    "coffee_shop": "Café-Restaurant",
    "bakery": "Boulangerie-Pâtisserie",
    "ice_cream_shop": "Glacier",
    "bar": "Bar-Restaurant",
    "brunch_restaurant": "Brunch",
    "breakfast_restaurant": "Brunch",
    "mediterranean_restaurant": "Méditerranéen",
    "asian_restaurant": "Asiatique",
    "middle_eastern_restaurant": "Oriental",
    "vegetarian_restaurant": "Végétarien",
    "vegan_restaurant": "Végétarien",
    "barbecue_restaurant": "Grillades",
}

PRICE_MAP = {
    "PRICE_LEVEL_FREE": "€",
    "PRICE_LEVEL_INEXPENSIVE": "€",
    "PRICE_LEVEL_MODERATE": "€€",
    "PRICE_LEVEL_EXPENSIVE": "€€€",
    "PRICE_LEVEL_VERY_EXPENSIVE": "€€€€",
}


# ============================================================
# HELPERS
# ============================================================

def make_slug(name, existing_slugs):
    """Génère un slug unique à partir du nom."""
    slug = unicodedata.normalize("NFKD", name.lower())
    slug = slug.encode("ascii", "ignore").decode("ascii")
    slug = re.sub(r"[^a-z0-9]+", "-", slug).strip("-")
    slug = re.sub(r"-+", "-", slug)
    if not slug:
        slug = "restaurant"

    base = slug
    counter = 1
    while slug in existing_slugs:
        slug = f"{base}-{counter}"
        counter += 1
    existing_slugs.add(slug)
    return slug


def normalize_name(name):
    """Normalise un nom pour la comparaison anti-doublon."""
    name = unicodedata.normalize("NFKD", name.lower())
    name = name.encode("ascii", "ignore").decode("ascii")
    name = re.sub(r"[^a-z0-9]", "", name)
    return name


def extract_cuisine(types):
    """Extrait le type de cuisine depuis les types Google Places."""
    for t in types:
        if t in CUISINE_MAP:
            return CUISINE_MAP[t]
    # Si aucun type spécifique, vérifier si c'est un restaurant
    if "restaurant" in types:
        return "Algérien traditionnel"
    return "classique"


def extract_ville_from_address(address, target_ville):
    """Extrait la ville depuis l'adresse formatée, ou utilise la ville cible."""
    return target_ville


def download_photo(api_key, photo_name, dest_path, max_width=800):
    """Télécharge une photo Google Places."""
    url = PLACE_PHOTO_URL.format(photo_name)
    params = {
        "maxWidthPx": max_width,
        "key": api_key,
    }
    headers = {"Referer": "http://localhost"}
    try:
        resp = requests.get(url, params=params, headers=headers, timeout=15)
        if resp.status_code == 200 and resp.headers.get("content-type", "").startswith("image"):
            with open(dest_path, "wb") as f:
                f.write(resp.content)
            return True
    except Exception as e:
        print(f"    [WARN] Photo download failed: {e}")
    return False


def parse_opening_hours(hours_data):
    """Parse les horaires Google vers notre format restaurant_horaires.

    Google: 0=Sunday, 1=Monday...6=Saturday
    Notre format: 0=Lundi, 1=Mardi...6=Dimanche
    Conversion: (google_day + 6) % 7
    """
    if not hours_data or "periods" not in hours_data:
        return []

    # Regrouper par jour (notre format)
    jours = {}
    for period in hours_data["periods"]:
        open_info = period.get("open", {})
        close_info = period.get("close", {})

        google_day = open_info.get("day")
        if google_day is None:
            continue

        our_day = (google_day + 6) % 7  # Google 0=Sunday → our 6=Dimanche

        open_time = f"{open_info.get('hour', 0):02d}:{open_info.get('minute', 0):02d}:00"

        if close_info:
            close_time = f"{close_info.get('hour', 0):02d}:{close_info.get('minute', 0):02d}:00"
        else:
            close_time = "23:59:00"

        if our_day not in jours:
            jours[our_day] = []
        jours[our_day].append((open_time, close_time))

    # Construire les entrées pour restaurant_horaires
    horaires = []
    for jour, periodes in jours.items():
        if len(periodes) == 1:
            # Service continu
            horaires.append({
                "jour_semaine": jour,
                "ferme": 0,
                "service_continu": 1,
                "ouverture_matin": periodes[0][0],
                "fermeture_matin": None,
                "ouverture_soir": None,
                "fermeture_soir": periodes[0][1],
            })
        elif len(periodes) >= 2:
            # Deux services (midi + soir)
            periodes.sort()
            horaires.append({
                "jour_semaine": jour,
                "ferme": 0,
                "service_continu": 0,
                "ouverture_matin": periodes[0][0],
                "fermeture_matin": periodes[0][1],
                "ouverture_soir": periodes[1][0],
                "fermeture_soir": periodes[1][1],
            })

    return horaires


# ============================================================
# MAIN LOGIC
# ============================================================

class PlacesPopulator:
    def __init__(self, api_key, dry_run=False, max_per_zone=50, only_city=None, skip_photos=False, max_total_calls=0):
        self.api_key = api_key
        self.dry_run = dry_run
        self.max_per_zone = max_per_zone
        self.only_city = only_city
        self.skip_photos = skip_photos
        self.max_total_calls = max_total_calls  # 0 = illimité

        self.conn = pymysql.connect(**DB_CONFIG)
        self.cursor = self.conn.cursor(pymysql.cursors.DictCursor)

        # Charger les restaurants existants pour déduplication
        self.existing_names = {}  # {normalized_name+ville: id}
        self.existing_place_ids = set()
        self.existing_slugs = set()
        self._load_existing()

        # Stats
        self.stats = {
            "api_calls": 0,
            "places_found": 0,
            "duplicates_skipped": 0,
            "inserted": 0,
            "photos_downloaded": 0,
            "reviews_inserted": 0,
            "errors": 0,
        }

        # Log CSV
        self.log_file = open(
            os.path.join(os.path.dirname(__file__), "populate_log.csv"),
            "w", newline="", encoding="utf-8"
        )
        self.log_writer = csv.writer(self.log_file)
        self.log_writer.writerow([
            "timestamp", "ville", "google_place_id", "nom", "action", "reason"
        ])

        # Photos dir
        self.photos_dir = os.path.join(
            os.path.dirname(__file__), "..", "public", "uploads", "restaurants"
        )
        os.makedirs(self.photos_dir, exist_ok=True)

    def _load_existing(self):
        """Charge tous les restaurants existants pour comparaison."""
        self.cursor.execute(
            "SELECT id, nom, ville, slug, google_place_id FROM restaurants WHERE status='validated'"
        )
        for row in self.cursor.fetchall():
            key = normalize_name(row["nom"]) + "_" + normalize_name(row["ville"] or "")
            self.existing_names[key] = row["id"]
            if row["google_place_id"]:
                self.existing_place_ids.add(row["google_place_id"])
            if row["slug"]:
                self.existing_slugs.add(row["slug"])

        print(f"[INFO] {len(self.existing_names)} restaurants existants chargés")
        print(f"[INFO] {len(self.existing_place_ids)} google_place_ids connus")

    def _is_duplicate(self, place_id, name, ville):
        """Vérifie si un restaurant est un doublon."""
        # Check exact google_place_id
        if place_id in self.existing_place_ids:
            return True, "google_place_id déjà en BDD"

        # Check normalized name + city
        key = normalize_name(name) + "_" + normalize_name(ville)
        if key in self.existing_names:
            return True, f"nom+ville match (id={self.existing_names[key]})"

        return False, ""

    def search_nearby(self, ville, lat, lng, radius):
        """Recherche les restaurants autour d'un point via Nearby Search."""
        headers = {
            "Content-Type": "application/json",
            "X-Goog-Api-Key": self.api_key,
            "X-Goog-FieldMask": FIELD_MASK,
            "Referer": "http://localhost",
        }

        body = {
            "includedTypes": ["restaurant"],
            "maxResultCount": min(self.max_per_zone, 20),  # API max = 20 par requête
            "locationRestriction": {
                "circle": {
                    "center": {"latitude": lat, "longitude": lng},
                    "radius": float(radius),
                }
            },
            "languageCode": "fr",
        }

        all_places = []

        # Première requête
        self.stats["api_calls"] += 1
        try:
            resp = requests.post(PLACES_NEARBY_URL, json=body, headers=headers, timeout=30)
            if resp.status_code != 200:
                print(f"    [ERROR] API {resp.status_code}: {resp.text[:200]}")
                self.stats["errors"] += 1
                return []

            data = resp.json()
            places = data.get("places", [])
            all_places.extend(places)

        except Exception as e:
            print(f"    [ERROR] Request failed: {e}")
            self.stats["errors"] += 1
            return []

        # Si on a eu 20 résultats et qu'on en veut plus, faire des sous-recherches
        # en divisant la zone en quadrants
        if len(places) >= 20 and self.max_per_zone > 20:
            print(f"    Zone saturée ({len(places)} résultats), subdivision en 4 quadrants...")
            offset = radius / 111000 * 0.5  # ~moitié du rayon en degrés
            sub_radius = radius * 0.6

            quadrants = [
                (lat + offset, lng + offset),  # NE
                (lat + offset, lng - offset),  # NW
                (lat - offset, lng + offset),  # SE
                (lat - offset, lng - offset),  # SW
            ]

            for qlat, qlng in quadrants:
                if len(all_places) >= self.max_per_zone:
                    break

                time.sleep(0.3)  # Rate limiting
                body["locationRestriction"]["circle"]["center"] = {
                    "latitude": qlat, "longitude": qlng
                }
                body["locationRestriction"]["circle"]["radius"] = sub_radius

                self.stats["api_calls"] += 1
                try:
                    resp = requests.post(PLACES_NEARBY_URL, json=body, headers=headers, timeout=30)
                    if resp.status_code == 200:
                        sub_places = resp.json().get("places", [])
                        all_places.extend(sub_places)
                except Exception as e:
                    print(f"    [WARN] Sub-query failed: {e}")

        # Dédupliquer par place_id (les quadrants peuvent chevaucher)
        seen_ids = set()
        unique_places = []
        for p in all_places:
            pid = p.get("id")
            if pid and pid not in seen_ids:
                seen_ids.add(pid)
                unique_places.append(p)

        self.stats["places_found"] += len(unique_places)
        return unique_places[:self.max_per_zone]

    def insert_restaurant(self, place, ville):
        """Insère un restaurant en BDD."""
        place_id = place.get("id", "")
        name = place.get("displayName", {}).get("text", "Restaurant")
        address = place.get("formattedAddress", "")
        location = place.get("location", {})
        lat = location.get("latitude")
        lng = location.get("longitude")
        types = place.get("types", [])
        phone = place.get("nationalPhoneNumber") or ""
        website = place.get("websiteUri") or ""
        price_level = place.get("priceLevel")
        rating = place.get("rating")
        rating_count = place.get("userRatingCount", 0)
        photos = place.get("photos", [])
        hours = place.get("regularOpeningHours")
        reviews = place.get("reviews", [])

        # Déduplication
        is_dup, reason = self._is_duplicate(place_id, name, ville)
        if is_dup:
            self.stats["duplicates_skipped"] += 1
            self.log_writer.writerow([
                datetime.now().isoformat(), ville, place_id, name, "SKIP", reason
            ])
            return False

        # Préparer les données
        slug = make_slug(name, self.existing_slugs)
        cuisine = extract_cuisine(types)
        price_range = PRICE_MAP.get(price_level, "€€") if price_level else "€€"

        # Nettoyer l'adresse (retirer "Algérie" et le code postal)
        clean_address = address
        clean_address = re.sub(r",?\s*Alg[eé]ri[ae]?\s*$", "", clean_address, flags=re.IGNORECASE)
        clean_address = re.sub(r",?\s*\d{5}\s*", " ", clean_address).strip().strip(",").strip()

        # Descriptif : préférer editorialSummary de Google si disponible
        editorial = place.get("editorialSummary", {}).get("text", "")
        if editorial:
            descriptif = editorial
        else:
            descriptif = f"Restaurant {cuisine.lower()} à {ville}."
            if rating and rating_count > 5:
                descriptif += f" Noté {rating}/5 par {rating_count} avis Google."

        # Halal : si sert bière/vin/cocktails → probablement pas halal
        serves_alcohol = place.get("servesBeer") or place.get("servesWine") or place.get("servesCocktails")
        verified_halal = 0 if serves_alcohol else 1

        # Réservation
        reservable = 1 if place.get("reservable") else 0

        if self.dry_run:
            print(f"    [DRY-RUN] {name} ({cuisine}) - {clean_address}")
            self.log_writer.writerow([
                datetime.now().isoformat(), ville, place_id, name, "DRY-RUN", ""
            ])
            self.stats["inserted"] += 1
            return True

        # INSERT
        try:
            sql = """
                INSERT INTO restaurants
                (nom, slug, type_cuisine, pays, ville, adresse, gps_latitude, gps_longitude,
                 phone, website, descriptif, price_range, note_moyenne, nb_avis, status,
                 verified_halal, reservations_enabled, google_place_id, created_at, updated_at)
                VALUES
                (%s, %s, %s, 'Algérie', %s, %s, %s, %s,
                 %s, %s, %s, %s, %s, %s, 'validated',
                 %s, %s, %s, NOW(), NOW())
            """
            self.cursor.execute(sql, (
                name, slug, cuisine, ville, clean_address, lat, lng,
                phone, website, descriptif, price_range,
                min(rating, 5.0) if rating else None,
                rating_count if rating_count else 0,
                verified_halal, reservable, place_id,
            ))
            restaurant_id = self.cursor.lastrowid
            self.conn.commit()

            # Enregistrer pour déduplications futures dans cette session
            self.existing_place_ids.add(place_id)
            key = normalize_name(name) + "_" + normalize_name(ville)
            self.existing_names[key] = restaurant_id

            # Télécharger 1 photo (respecte le cap total d'appels)
            cap_ok = not self.max_total_calls or self.stats["api_calls"] < self.max_total_calls
            if photos and not self.skip_photos and self.stats["photos_downloaded"] < 1000 and cap_ok:
                photo_name = photos[0].get("name")
                if photo_name:
                    ext = "jpg"
                    filename = f"restaurant_{restaurant_id}_google.{ext}"
                    filepath = os.path.join(self.photos_dir, filename)
                    if download_photo(self.api_key, photo_name, filepath):
                        # Insérer dans restaurant_photos
                        self.cursor.execute(
                            """INSERT INTO restaurant_photos
                               (restaurant_id, filename, path, type, created_at)
                               VALUES (%s, %s, %s, 'main', NOW())""",
                            (restaurant_id, filename, f"uploads/restaurants/{filename}")
                        )
                        self.conn.commit()
                        self.stats["photos_downloaded"] += 1
                        self.stats["api_calls"] += 1  # Photo = 1 API call

            # Insérer les horaires dans restaurant_horaires
            if hours:
                horaires = parse_opening_hours(hours)
                for h in horaires:
                    try:
                        self.cursor.execute(
                            """INSERT INTO restaurant_horaires
                               (restaurant_id, jour_semaine, ferme, service_continu,
                                ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir)
                               VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""",
                            (restaurant_id, h["jour_semaine"],
                             h["ferme"], h["service_continu"],
                             h["ouverture_matin"], h["fermeture_matin"],
                             h["ouverture_soir"], h["fermeture_soir"])
                        )
                    except Exception:
                        pass  # Skip duplicate hours
                self.conn.commit()

            # Insérer les amenities dans restaurant_options
            has_parking = place.get("parkingOptions") is not None and bool(place.get("parkingOptions"))
            has_accessibility = place.get("accessibilityOptions") is not None and bool(place.get("accessibilityOptions"))
            try:
                self.cursor.execute(
                    """INSERT INTO restaurant_options
                       (restaurant_id, delivery, takeaway, terrace, pets_allowed,
                        baby_chair, handicap_access, parking, air_conditioning)
                       VALUES (%s, %s, %s, %s, %s, %s, %s, %s, 0)""",
                    (restaurant_id,
                     1 if place.get("delivery") else 0,
                     1 if place.get("takeout") else 0,
                     1 if place.get("outdoorSeating") else 0,
                     1 if place.get("allowsDogs") else 0,
                     1 if place.get("goodForChildren") else 0,
                     1 if has_accessibility else 0,
                     1 if has_parking else 0)
                )
                self.conn.commit()
            except Exception:
                pass  # Skip if options already exist

            # Insérer les avis Google (jusqu'à 5)
            reviews_inserted = 0
            for rev in reviews[:5]:
                try:
                    rev_author = rev.get("authorAttribution", {}).get("displayName", "Utilisateur Google")
                    rev_rating = rev.get("rating")
                    rev_text = rev.get("text", {}).get("text") or rev.get("originalText", {}).get("text") or ""
                    rev_time = rev.get("publishTime")  # RFC 3339
                    # Titre : premiers mots du texte (max 60 chars)
                    rev_title = (rev_text[:57] + "...") if len(rev_text) > 60 else rev_text
                    rev_title = rev_title.split("\n")[0]  # Première ligne seulement

                    if not rev_rating:
                        continue

                    self.cursor.execute(
                        """INSERT INTO reviews
                           (restaurant_id, user_id, author_name, title, message, note_globale,
                            status, source, spam_score, created_at, updated_at)
                           VALUES (%s, NULL, %s, %s, %s, %s,
                            'approved', 'google', 100, %s, NOW())""",
                        (restaurant_id, rev_author, rev_title, rev_text,
                         min(float(rev_rating), 5.0),
                         rev_time if rev_time else datetime.now().isoformat())
                    )
                    reviews_inserted += 1
                except Exception:
                    pass
            if reviews_inserted:
                self.conn.commit()
                self.stats["reviews_inserted"] += reviews_inserted

            self.stats["inserted"] += 1
            self.log_writer.writerow([
                datetime.now().isoformat(), ville, place_id, name, "INSERT",
                f"id={restaurant_id} reviews={reviews_inserted}"
            ])
            print(f"    [OK] #{restaurant_id} {name} ({cuisine}) +{reviews_inserted} avis")
            return True

        except Exception as e:
            self.stats["errors"] += 1
            print(f"    [ERROR] Insert {name}: {e}")
            self.conn.rollback()
            return False

    def _count_nearby(self, lat, lng, radius_deg=0.018):
        """Compte les restaurants existants dans un rayon autour d'un point."""
        self.cursor.execute(
            """SELECT COUNT(*) as cnt FROM restaurants
               WHERE status='validated'
               AND gps_latitude BETWEEN %s AND %s
               AND gps_longitude BETWEEN %s AND %s""",
            (lat - radius_deg, lat + radius_deg,
             lng - radius_deg, lng + radius_deg)
        )
        return self.cursor.fetchone()["cnt"]

    def run(self):
        """Lance le peuplement pour toutes les zones cibles."""
        zones = TARGET_ZONES

        if self.only_city:
            zones = [z for z in zones if z[0].lower() == self.only_city.lower()]
            if not zones:
                print(f"[ERROR] Ville '{self.only_city}' non trouvée dans les zones cibles")
                return

        # Compter la densité locale pour chaque zone et trier par densité croissante
        zones_with_density = []
        for z in zones:
            ville, lat, lng, radius = z
            local_count = self._count_nearby(lat, lng)
            zones_with_density.append((ville, lat, lng, radius, local_count))
        zones_with_density.sort(key=lambda z: z[4])  # Zones les plus vides en premier

        print(f"\n{'='*60}")
        print(f"  LeBonResto - Peuplement Google Places")
        print(f"  Mode: {'DRY-RUN' if self.dry_run else 'PRODUCTION'}")
        print(f"  Zones: {len(zones_with_density)} zones ciblées")
        print(f"  Max par zone: {self.max_per_zone}")
        if self.max_total_calls:
            print(f"  Cap total appels API: {self.max_total_calls}")
        print(f"{'='*60}\n")

        for ville, lat, lng, radius, local_count in zones_with_density:
            # Vérifier le cap total d'appels API
            if self.max_total_calls and self.stats["api_calls"] >= self.max_total_calls:
                print(f"\n[CAP] Limite de {self.max_total_calls} appels API atteinte — arrêt")
                break

            # Skip zones locales déjà bien couvertes (> 10 dans le rayon 2km)
            if local_count >= 10 and not self.only_city:
                print(f"[SKIP] {ville} @ ({lat},{lng}) — {local_count} restos dans un rayon de 2km")
                continue

            print(f"\n[ZONE] {ville} @ ({lat}, {lng}) — {local_count} restos locaux")
            print(f"  Recherche rayon {radius}m...")

            places = self.search_nearby(ville, lat, lng, radius)
            print(f"  {len(places)} restaurants trouvés via API")

            inserted = 0
            for place in places:
                if inserted >= self.max_per_zone:
                    break
                # Vérifier le cap avant chaque insertion (qui peut déclencher un appel photo)
                if self.max_total_calls and self.stats["api_calls"] >= self.max_total_calls:
                    print(f"    [CAP] Limite atteinte — pas de photo/insertion supplémentaire")
                    break
                if self.insert_restaurant(place, ville):
                    inserted += 1
                time.sleep(0.1)  # Petit délai entre insertions

            print(f"  => {inserted} nouveaux restaurants ajoutés pour cette zone")
            time.sleep(0.5)  # Pause entre zones

        # Résumé final
        print(f"\n{'='*60}")
        print(f"  RÉSUMÉ FINAL")
        print(f"{'='*60}")
        print(f"  Appels API:          {self.stats['api_calls']}")
        print(f"  Restaurants trouvés: {self.stats['places_found']}")
        print(f"  Doublons évités:     {self.stats['duplicates_skipped']}")
        print(f"  Nouveaux insérés:    {self.stats['inserted']}")
        print(f"  Avis Google insérés: {self.stats['reviews_inserted']}")
        print(f"  Photos téléchargées: {self.stats['photos_downloaded']}")
        print(f"  Erreurs:             {self.stats['errors']}")

        # Quotas gratuits (depuis mars 2025)
        # Nearby Search Enterprise : 1 000 gratuits/mois
        # Place Photos Enterprise  : 1 000 gratuits/mois
        search_calls = self.stats["api_calls"] - self.stats["photos_downloaded"]
        photo_calls = self.stats["photos_downloaded"]

        search_over = max(0, search_calls - 1000)
        photo_over = max(0, photo_calls - 1000)

        # Prix au-delà du quota : Nearby Search Enterprise ~$0.040, Photos ~$0.007
        search_cost = search_over * 0.040
        photo_cost = photo_over * 0.007

        print(f"\n  Quotas utilisés:")
        print(f"    Nearby Search Enterprise: {search_calls}/1000 gratuits", end="")
        print(f" (dépassement: {search_over})" if search_over else " OK")
        print(f"    Place Photos Enterprise:  {photo_calls}/1000 gratuits", end="")
        print(f" (dépassement: {photo_over})" if photo_over else " OK")
        if search_cost + photo_cost > 0:
            print(f"\n  Coût dépassement:")
            if search_cost > 0:
                print(f"    Nearby Search: ${search_cost:.2f}")
            if photo_cost > 0:
                print(f"    Photos:        ${photo_cost:.2f}")
            print(f"    TOTAL:         ${search_cost + photo_cost:.2f}")
        else:
            print(f"\n  Coût total: $0.00 (dans le quota gratuit)")
        print(f"{'='*60}")

        # Distribution finale
        self.cursor.execute(
            """SELECT ville, COUNT(*) as cnt FROM restaurants
               WHERE status='validated' GROUP BY ville ORDER BY cnt DESC"""
        )
        print(f"\n  Distribution finale:")
        for row in self.cursor.fetchall():
            print(f"    {row['ville']}: {row['cnt']}")

        self.log_file.close()
        self.conn.close()
        print(f"\n  Log CSV: database/populate_log.csv")


# ============================================================
# ENTRY POINT
# ============================================================

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Peupler LeBonResto via Google Places API")
    parser.add_argument("--api-key", required=True, help="Google Places API key")
    parser.add_argument("--dry-run", action="store_true", help="Simuler sans insérer en BDD")
    parser.add_argument("--max-per-zone", type=int, default=20, help="Max restaurants par zone (default: 20)")
    parser.add_argument("--only-city", type=str, help="Ne traiter qu'une seule ville")
    parser.add_argument("--skip-photos", action="store_true", help="Ne pas télécharger les photos")
    parser.add_argument("--max-total-calls", type=int, default=0, help="Limite totale d'appels API (0=illimité)")

    args = parser.parse_args()

    populator = PlacesPopulator(
        api_key=args.api_key,
        dry_run=args.dry_run,
        max_per_zone=args.max_per_zone,
        only_city=args.only_city,
        skip_photos=args.skip_photos,
        max_total_calls=args.max_total_calls,
    )
    populator.run()
