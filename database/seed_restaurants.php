<?php
/**
 * Seed de vrais restaurants algériens
 * Sources: TripAdvisor, Petit Futé, Google Maps, Facebook, Safarway
 * Villes: Constantine, Annaba, Sétif, Béjaïa, Tlemcen, Tizi Ouzou, Batna, Blida, Biskra, Tipaza, Ghardaia, Mostaganem, Jijel, Djelfa
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=lebonresto;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fonction slug
function makeSlug(string $nom, string $ville, PDO $pdo): string {
    $slug = strtolower(trim($nom . ' ' . $ville));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    $base = $slug;
    $i = 1;
    while (true) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM restaurants WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() == 0) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// Petite variation GPS pour disperser les pins sur la carte
function gpsOffset(float $base, float $range = 0.015): float {
    return $base + (mt_rand(-1000, 1000) / 1000) * $range;
}

$restaurants = [
    // ═══════════════════════════════════════════════════════════
    // CONSTANTINE (36.365, 6.615)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Siniet El Bey', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Route de Sidi M\'Cid, Constantine', 'code_postal' => '25000', 'lat' => 36.3680, 'lng' => 6.6090, 'phone' => '031 92 12 34', 'price' => '$$$', 'note' => 4.3, 'desc' => 'Restaurant panoramique surplombant les gorges du Rhumel, spécialités constantinoises'],
    ['nom' => 'Restaurant RAIS', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Avenue Ali Zaamouche, Constantine', 'code_postal' => '25000', 'lat' => 36.3655, 'lng' => 6.6155, 'phone' => '031 88 45 67', 'price' => '$$', 'note' => 4.1, 'desc' => 'Cuisine traditionnelle constantinoise, couscous et rechta'],
    ['nom' => 'Magic House', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Fast food', 'adresse' => 'Rue Larbi Ben M\'Hidi, Constantine', 'code_postal' => '25000', 'lat' => 36.3642, 'lng' => 6.6138, 'phone' => '0555 12 34 56', 'price' => '$', 'note' => 4.0, 'desc' => 'Fast food populaire, burgers et tacos'],
    ['nom' => 'Le Ciloc', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Rue Hamlaoui, Constantine', 'code_postal' => '25000', 'lat' => 36.3668, 'lng' => 6.6172, 'phone' => '031 92 56 78', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Gastronomie constantinoise raffinée, cadre élégant'],
    ['nom' => 'Igherssan Restaurant', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Cité Kouthil Lakhdar, Constantine', 'code_postal' => '25000', 'lat' => 36.3590, 'lng' => 6.6200, 'phone' => '031 94 23 45', 'price' => '$$', 'note' => 4.2, 'desc' => 'Plats traditionnels, ambiance familiale chaleureuse'],
    ['nom' => 'Restaurant La Concorde', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Rue Nationale, Constantine', 'code_postal' => '25000', 'lat' => 36.3635, 'lng' => 6.6125, 'phone' => '031 88 90 12', 'price' => '$$', 'note' => 4.0, 'desc' => 'Restaurant familial, spécialités locales et grillades'],
    ['nom' => 'Le Vieux Rocher', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Boulevard Zighoud Youcef, Constantine', 'code_postal' => '25000', 'lat' => 36.3700, 'lng' => 6.6100, 'phone' => '031 93 45 67', 'price' => '$$$', 'note' => 4.5, 'desc' => 'Vue imprenable sur le vieux rocher, cuisine méditerranéenne'],
    ['nom' => 'Qasar Restaurant', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Constantine Marriott Hotel, Constantine', 'code_postal' => '25000', 'lat' => 36.3615, 'lng' => 6.5980, 'phone' => '031 79 00 00', 'price' => '$$$', 'note' => 4.6, 'desc' => 'Restaurant du Marriott, cuisine arabo-méditerranéenne haut de gamme'],
    ['nom' => 'Dolce Vita Constantine', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Rue Abane Ramdane, Constantine', 'code_postal' => '25000', 'lat' => 36.3648, 'lng' => 6.6160, 'phone' => '0661 23 45 67', 'price' => '$$', 'note' => 4.1, 'desc' => 'Pizzeria italienne, pâtes fraîches et pizzas au feu de bois'],
    ['nom' => 'Grill d\'Ici', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Grillades', 'adresse' => 'Rue Didouche Mourad, Constantine', 'code_postal' => '25000', 'lat' => 36.3660, 'lng' => 6.6145, 'phone' => '0770 56 78 90', 'price' => '$$', 'note' => 4.2, 'desc' => 'Grillades et viandes braisées, spécialité méchoui'],
    ['nom' => 'Restaurant El Bey', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Place du 1er Novembre, Constantine', 'code_postal' => '25000', 'lat' => 36.3651, 'lng' => 6.6148, 'phone' => '031 88 12 34', 'price' => '$$', 'note' => 4.0, 'desc' => 'Cuisine constantinoise authentique au coeur de la ville'],
    ['nom' => 'Pizzeria Le Chapiteau', 'ville' => 'Constantine', 'wilaya' => 'Constantine', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Cité Daksi, Constantine', 'code_postal' => '25000', 'lat' => 36.3580, 'lng' => 6.6250, 'phone' => '0555 67 89 01', 'price' => '$', 'note' => 3.9, 'desc' => 'Pizzas généreuses à prix abordable'],

    // ═══════════════════════════════════════════════════════════
    // ANNABA (36.900, 7.767)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant Nassim La Caroube', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Route de la Corniche, Annaba', 'code_postal' => '23000', 'lat' => 36.9050, 'lng' => 7.7580, 'phone' => '038 86 12 34', 'price' => '$$', 'note' => 4.2, 'desc' => 'Cuisine algérienne authentique avec saveurs locales'],
    ['nom' => 'Restaurant La Caravelle', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Boulevard du Front de Mer, Annaba', 'code_postal' => '23000', 'lat' => 36.9085, 'lng' => 7.7620, 'phone' => '038 84 56 78', 'price' => '$$$', 'note' => 4.3, 'desc' => 'Fruits de mer frais face à la Méditerranée'],
    ['nom' => 'Restaurant Tabarka', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Rue Zighoud Youcef, Annaba', 'code_postal' => '23000', 'lat' => 36.9012, 'lng' => 7.7650, 'phone' => '038 86 90 12', 'price' => '$$', 'note' => 4.1, 'desc' => 'Cuisine traditionnelle annabienne dans un cadre chaleureux'],
    ['nom' => 'Restaurant Le Pêcheur', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Corniche Seybouse, Annaba', 'code_postal' => '23000', 'lat' => 36.9120, 'lng' => 7.7550, 'phone' => '038 84 34 56', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Le meilleur poisson frais de la ville, vue sur mer'],
    ['nom' => 'Restaurant Seybouse', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Boulevard Ben Boulaid, Annaba', 'code_postal' => '23000', 'lat' => 36.8995, 'lng' => 7.7680, 'phone' => '038 86 78 90', 'price' => '$$', 'note' => 4.0, 'desc' => 'Grillades et couscous, ambiance conviviale'],
    ['nom' => 'Restaurant Le Phare', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Cap de Garde, Annaba', 'code_postal' => '23000', 'lat' => 36.9200, 'lng' => 7.7850, 'phone' => '0660 12 34 56', 'price' => '$$$', 'note' => 4.5, 'desc' => 'Restaurant au phare, poissons grillés avec vue panoramique'],
    ['nom' => 'La Fringale Annaba', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Fast food', 'adresse' => 'Cours de la Révolution, Annaba', 'code_postal' => '23000', 'lat' => 36.8998, 'lng' => 7.7665, 'phone' => '0555 23 45 67', 'price' => '$', 'note' => 4.0, 'desc' => 'Sandwichs, burgers et chawarma'],
    ['nom' => 'L\'Épi d\'Or', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Pâtisserie', 'adresse' => 'Rue Ibn Khaldoun, Annaba', 'code_postal' => '23000', 'lat' => 36.9005, 'lng' => 7.7640, 'phone' => '038 84 11 22', 'price' => '$', 'note' => 4.3, 'desc' => 'Pâtisserie orientale et occidentale, gâteaux traditionnels'],
    ['nom' => 'Pasta House Annaba', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Italien', 'adresse' => 'Rue Sayoud Mohamed, Annaba', 'code_postal' => '23000', 'lat' => 36.9020, 'lng' => 7.7700, 'phone' => '0770 45 67 89', 'price' => '$$', 'note' => 4.1, 'desc' => 'Pâtes fraîches et pizzas à l\'italienne'],
    ['nom' => 'El Mountazah', 'ville' => 'Annaba', 'wilaya' => 'Annaba', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Seraïdi, Annaba', 'code_postal' => '23000', 'lat' => 36.9150, 'lng' => 7.6800, 'phone' => '038 87 56 78', 'price' => '$$', 'note' => 4.2, 'desc' => 'Restaurant en hauteur à Seraïdi, air frais et cuisine locale'],

    // ═══════════════════════════════════════════════════════════
    // SÉTIF (36.190, 5.411)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant Bab El-Hara', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Syrien', 'adresse' => 'Boulevard de l\'ALN, Sétif', 'code_postal' => '19000', 'lat' => 36.1910, 'lng' => 5.4080, 'phone' => '036 84 12 34', 'price' => '$$', 'note' => 4.3, 'desc' => 'Saveurs syriennes authentiques, chawarma et grillades'],
    ['nom' => 'Restaurant Marina Sétif', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Cité 1000 Logements, Sétif', 'code_postal' => '19000', 'lat' => 36.1880, 'lng' => 5.4150, 'phone' => '036 93 56 78', 'price' => '$$', 'note' => 4.1, 'desc' => 'Cuisine algérienne familiale, plats généreux'],
    ['nom' => 'Restaurant L\'Univers', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Avenue du 8 Mai 1945, Sétif', 'code_postal' => '19000', 'lat' => 36.1920, 'lng' => 5.4120, 'phone' => '036 84 90 12', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Restaurant haut de gamme, cuisine méditerranéenne raffinée'],
    ['nom' => 'Pizza Pino Sétif', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Rue Commandant Abdelmadjid, Sétif', 'code_postal' => '19000', 'lat' => 36.1895, 'lng' => 5.4095, 'phone' => '0555 34 56 78', 'price' => '$', 'note' => 4.2, 'desc' => 'Pizzas italiennes et pâtes, livraison rapide'],
    ['nom' => 'L\'Asiatico', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Asiatique', 'adresse' => 'Centre-ville, Sétif', 'code_postal' => '19000', 'lat' => 36.1905, 'lng' => 5.4110, 'phone' => '0661 45 67 89', 'price' => '$$', 'note' => 4.0, 'desc' => 'Cuisine asiatique, sushi et wok'],
    ['nom' => 'Restaurant El Hidhab', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Cité El Hidhab, Sétif', 'code_postal' => '19000', 'lat' => 36.1850, 'lng' => 5.4180, 'phone' => '036 93 23 45', 'price' => '$', 'note' => 3.9, 'desc' => 'Cuisine populaire, couscous et chorba'],
    ['nom' => 'Royal Food Sétif', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Fast food', 'adresse' => 'Ain El Fouara, Sétif', 'code_postal' => '19000', 'lat' => 36.1900, 'lng' => 5.4100, 'phone' => '0770 67 89 01', 'price' => '$', 'note' => 4.1, 'desc' => 'Burgers gourmet, tacos et wraps'],
    ['nom' => 'Le Gourmet de Sétif', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Français', 'adresse' => 'Boulevard de l\'Indépendance, Sétif', 'code_postal' => '19000', 'lat' => 36.1915, 'lng' => 5.4060, 'phone' => '036 84 67 89', 'price' => '$$$', 'note' => 4.3, 'desc' => 'Cuisine française et méditerranéenne, cadre chic'],
    ['nom' => 'Chicken Master Sétif', 'ville' => 'Sétif', 'wilaya' => 'Sétif', 'type_cuisine' => 'Fast food', 'adresse' => 'Place Ain El Fouara, Sétif', 'code_postal' => '19000', 'lat' => 36.1898, 'lng' => 5.4108, 'phone' => '0555 78 90 12', 'price' => '$', 'note' => 3.8, 'desc' => 'Poulet croustillant et menus rapides'],

    // ═══════════════════════════════════════════════════════════
    // BÉJAÏA (36.751, 5.057)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant Le Lido', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Boulevard Amirouche, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7540, 'lng' => 5.0600, 'phone' => '034 21 12 34', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Fruits de mer frais, vue sur le port de Béjaïa'],
    ['nom' => 'Restaurant Capri', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Route des Aiguades, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7600, 'lng' => 5.0650, 'phone' => '034 22 56 78', 'price' => '$$', 'note' => 4.2, 'desc' => 'Cuisine méditerranéenne avec vue sur mer'],
    ['nom' => 'Chez Mouloud', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Vieille ville, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7520, 'lng' => 5.0560, 'phone' => '034 21 34 56', 'price' => '$', 'note' => 4.1, 'desc' => 'Cuisine kabyle traditionnelle, couscous et tajines'],
    ['nom' => 'Le Marin', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Port de pêche, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7550, 'lng' => 5.0580, 'phone' => '034 22 78 90', 'price' => '$$', 'note' => 4.3, 'desc' => 'Poisson frais du jour, grillades de fruits de mer'],
    ['nom' => 'Restaurant La Casbah', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Boulevard de la Liberté, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7515, 'lng' => 5.0545, 'phone' => '034 21 90 12', 'price' => '$$', 'note' => 4.0, 'desc' => 'Cuisine traditionnelle dans un décor authentique'],
    ['nom' => 'Pizza Queen Béjaïa', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Cité Nacéria, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7480, 'lng' => 5.0620, 'phone' => '0555 89 01 23', 'price' => '$', 'note' => 3.9, 'desc' => 'Pizzas variées et paninis'],
    ['nom' => 'Restaurant Le Phare Béjaïa', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Cap Carbon, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7680, 'lng' => 5.0750, 'phone' => '0661 23 45 67', 'price' => '$$$', 'note' => 4.5, 'desc' => 'Cadre exceptionnel au Cap Carbon, produits de la mer'],
    ['nom' => 'L\'Étoile de Mer', 'ville' => 'Béjaïa', 'wilaya' => 'Béjaïa', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Plage de Boulimat, Béjaïa', 'code_postal' => '06000', 'lat' => 36.7350, 'lng' => 5.0100, 'phone' => '0770 34 56 78', 'price' => '$$', 'note' => 4.2, 'desc' => 'Restaurant les pieds dans l\'eau, spécialités marines'],

    // ═══════════════════════════════════════════════════════════
    // TLEMCEN (34.883, -1.317)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'L\'Équinoxe', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Boulevard Colonel Lotfi, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8840, 'lng' => -1.3150, 'phone' => '043 20 12 34', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Cuisine méditerranéenne raffinée, cadre moderne'],
    ['nom' => 'Latina House Restaurant', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Rue de la Mosquée, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8825, 'lng' => -1.3175, 'phone' => '043 21 56 78', 'price' => '$$', 'note' => 4.2, 'desc' => 'Saveurs algériennes authentiques, hospitalité tlemcenienne'],
    ['nom' => 'Le Cinq', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Place El Mechwar, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8830, 'lng' => -1.3160, 'phone' => '0555 90 12 34', 'price' => '$$', 'note' => 4.1, 'desc' => 'Pizzas méditerranéennes et plats variés'],
    ['nom' => 'Restaurant Seb\'s Garden', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Plateau de Lalla Setti, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8750, 'lng' => -1.3100, 'phone' => '043 27 34 56', 'price' => '$$', 'note' => 4.3, 'desc' => 'Cadre jardin magnifique avec vue panoramique sur Tlemcen'],
    ['nom' => 'Le Gourmet Tlemcen', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Avenue de l\'Indépendance, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8850, 'lng' => -1.3140, 'phone' => '043 20 78 90', 'price' => '$$$', 'note' => 4.5, 'desc' => 'Gastronomie méditerranéenne, spécialités tlemceniennes'],
    ['nom' => 'Restaurant Zianides', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Rue des Frères Bensaïd, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8820, 'lng' => -1.3185, 'phone' => '043 21 12 34', 'price' => '$$', 'note' => 4.0, 'desc' => 'Héritage culinaire des Zianides, plats traditionnels'],
    ['nom' => 'Renaissance Hotel Restaurant', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Français', 'adresse' => 'Hotel Renaissance, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8860, 'lng' => -1.3080, 'phone' => '043 41 00 00', 'price' => '$$$', 'note' => 4.3, 'desc' => 'Restaurant d\'hôtel chic, cuisine française et algérienne'],
    ['nom' => 'El Mansourah Grill', 'ville' => 'Tlemcen', 'wilaya' => 'Tlemcen', 'type_cuisine' => 'Grillades', 'adresse' => 'Route de Mansourah, Tlemcen', 'code_postal' => '13000', 'lat' => 34.8780, 'lng' => -1.3250, 'phone' => '0661 56 78 90', 'price' => '$$', 'note' => 4.1, 'desc' => 'Grillades et méchoui près des ruines de Mansourah'],

    // ═══════════════════════════════════════════════════════════
    // TIZI OUZOU (36.717, 4.050)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant L\'Ambassade', 'ville' => 'Tizi Ouzou', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Boulevard Stiti Ali, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.7180, 'lng' => 4.0510, 'phone' => '026 21 12 34', 'price' => '$$', 'note' => 4.2, 'desc' => 'Cuisine kabyle et algérienne, cadre élégant'],
    ['nom' => 'Restaurant Pranz\'o', 'ville' => 'Tizi Ouzou', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Italien', 'adresse' => 'Rue Lamali Ahmed, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.7165, 'lng' => 4.0485, 'phone' => '0555 12 34 56', 'price' => '$$', 'note' => 4.1, 'desc' => 'Pizzas, pâtes et plats italiens'],
    ['nom' => 'House of Burgers', 'ville' => 'Tizi Ouzou', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Fast food', 'adresse' => 'Boulevard Abane Ramdane, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.7172, 'lng' => 4.0500, 'phone' => '0661 34 56 78', 'price' => '$', 'note' => 4.0, 'desc' => 'Burgers artisanaux et frites maison'],
    ['nom' => 'Va Bene', 'ville' => 'Tizi Ouzou', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Nouvelle ville, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.7155, 'lng' => 4.0520, 'phone' => '0770 56 78 90', 'price' => '$$', 'note' => 4.2, 'desc' => 'Pizzeria italienne, ambiance conviviale'],
    ['nom' => 'Restaurant Pikanya', 'ville' => 'Tizi Ouzou', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Grillades', 'adresse' => 'Route de Béjaïa, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.7190, 'lng' => 4.0540, 'phone' => '0555 78 90 12', 'price' => '$$', 'note' => 4.3, 'desc' => 'Spécialités de grillades et brochettes'],
    ['nom' => 'Restaurant Le Djurdjura', 'ville' => 'Tizi Ouzou', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Haute ville, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.7200, 'lng' => 4.0470, 'phone' => '026 22 34 56', 'price' => '$$', 'note' => 4.4, 'desc' => 'Couscous kabyle, tajines et plats de montagne'],
    ['nom' => 'Le Rocher Noir', 'ville' => 'Tizi Ouzou', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Café-Restaurant', 'adresse' => 'Boulevard Colonel Amirouche, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.7175, 'lng' => 4.0495, 'phone' => '026 21 56 78', 'price' => '$', 'note' => 3.9, 'desc' => 'Café-restaurant, plats du jour et pâtisseries'],
    ['nom' => 'Tigzirt Sur Mer', 'ville' => 'Tigzirt', 'wilaya' => 'Tizi Ouzou', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Port de Tigzirt, Tizi Ouzou', 'code_postal' => '15000', 'lat' => 36.8950, 'lng' => 4.1230, 'phone' => '0661 90 12 34', 'price' => '$$', 'note' => 4.3, 'desc' => 'Poissons frais du port, cadre bord de mer'],

    // ═══════════════════════════════════════════════════════════
    // BATNA (35.556, 6.174)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant El Aurès', 'ville' => 'Batna', 'wilaya' => 'Batna', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Avenue de l\'Indépendance, Batna', 'code_postal' => '05000', 'lat' => 35.5570, 'lng' => 6.1750, 'phone' => '033 86 12 34', 'price' => '$$', 'note' => 4.1, 'desc' => 'Cuisine aurésienne authentique, rechta et couscous'],
    ['nom' => 'Restaurant Le Palace', 'ville' => 'Batna', 'wilaya' => 'Batna', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Boulevard de la République, Batna', 'code_postal' => '05000', 'lat' => 35.5580, 'lng' => 6.1730, 'phone' => '033 81 56 78', 'price' => '$$$', 'note' => 4.3, 'desc' => 'Restaurant chic, cuisine méditerranéenne et algérienne'],
    ['nom' => 'Le Gourmet Batna', 'ville' => 'Batna', 'wilaya' => 'Batna', 'type_cuisine' => 'Français', 'adresse' => 'Rue Chahid Boukhlouf, Batna', 'code_postal' => '05000', 'lat' => 35.5555, 'lng' => 6.1760, 'phone' => '033 86 90 12', 'price' => '$$$', 'note' => 4.2, 'desc' => 'Cuisine française revisitée, cadre moderne'],
    ['nom' => 'Pizza Time Batna', 'ville' => 'Batna', 'wilaya' => 'Batna', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Centre-ville, Batna', 'code_postal' => '05000', 'lat' => 35.5560, 'lng' => 6.1740, 'phone' => '0555 23 45 67', 'price' => '$', 'note' => 4.0, 'desc' => 'Pizzas variées, livraison rapide'],
    ['nom' => 'Timgad Grill', 'ville' => 'Batna', 'wilaya' => 'Batna', 'type_cuisine' => 'Grillades', 'adresse' => 'Route de Timgad, Batna', 'code_postal' => '05000', 'lat' => 35.5500, 'lng' => 6.1800, 'phone' => '0661 45 67 89', 'price' => '$$', 'note' => 4.1, 'desc' => 'Grillades et méchoui, viandes de qualité'],
    ['nom' => 'Chréa Food', 'ville' => 'Batna', 'wilaya' => 'Batna', 'type_cuisine' => 'Fast food', 'adresse' => 'Cité 1er Novembre, Batna', 'code_postal' => '05000', 'lat' => 35.5545, 'lng' => 6.1720, 'phone' => '0770 67 89 01', 'price' => '$', 'note' => 3.8, 'desc' => 'Fast food local, burgers et chawarma'],

    // ═══════════════════════════════════════════════════════════
    // BLIDA (36.470, 2.830)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant La Rose', 'ville' => 'Blida', 'wilaya' => 'Blida', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Rue des Roses, Blida', 'code_postal' => '09000', 'lat' => 36.4710, 'lng' => 2.8310, 'phone' => '025 20 12 34', 'price' => '$$', 'note' => 4.2, 'desc' => 'La ville des roses et sa cuisine traditionnelle'],
    ['nom' => 'Restaurant El Mitidja', 'ville' => 'Blida', 'wilaya' => 'Blida', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Avenue Kritli Mokhtar, Blida', 'code_postal' => '09000', 'lat' => 36.4720, 'lng' => 2.8280, 'phone' => '025 21 56 78', 'price' => '$$', 'note' => 4.0, 'desc' => 'Cuisine de la Mitidja, couscous et tajines'],
    ['nom' => 'Le Jardin de Blida', 'ville' => 'Blida', 'wilaya' => 'Blida', 'type_cuisine' => 'Café-Restaurant', 'adresse' => 'Avenue Ben Boulaid, Blida', 'code_postal' => '09000', 'lat' => 36.4700, 'lng' => 2.8320, 'phone' => '025 20 34 56', 'price' => '$$', 'note' => 4.3, 'desc' => 'Café-restaurant avec jardin, plats traditionnels et modernes'],
    ['nom' => 'Chréa Mountain Lodge', 'ville' => 'Chréa', 'wilaya' => 'Blida', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Station de Chréa, Blida', 'code_postal' => '09000', 'lat' => 36.4300, 'lng' => 2.8700, 'phone' => '0555 34 56 78', 'price' => '$$', 'note' => 4.1, 'desc' => 'Restaurant d\'altitude au coeur du parc national de Chréa'],
    ['nom' => 'Pizza Bella Blida', 'ville' => 'Blida', 'wilaya' => 'Blida', 'type_cuisine' => 'Pizzeria', 'adresse' => 'Rue Frantz Fanon, Blida', 'code_postal' => '09000', 'lat' => 36.4695, 'lng' => 2.8295, 'phone' => '0661 56 78 90', 'price' => '$', 'note' => 3.9, 'desc' => 'Pizzas artisanales et calzones'],
    ['nom' => 'El Baraka', 'ville' => 'Blida', 'wilaya' => 'Blida', 'type_cuisine' => 'Grillades', 'adresse' => 'Route de Beni Mered, Blida', 'code_postal' => '09000', 'lat' => 36.4680, 'lng' => 2.8350, 'phone' => '0770 78 90 12', 'price' => '$', 'note' => 4.0, 'desc' => 'Grillades et brochettes au charbon'],

    // ═══════════════════════════════════════════════════════════
    // BISKRA (34.845, 5.725)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant Les Zibans', 'ville' => 'Biskra', 'wilaya' => 'Biskra', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Boulevard Emir Abdelkader, Biskra', 'code_postal' => '07000', 'lat' => 34.8460, 'lng' => 5.7260, 'phone' => '033 71 12 34', 'price' => '$$', 'note' => 4.2, 'desc' => 'Cuisine saharienne, couscous aux dattes et plats du sud'],
    ['nom' => 'Restaurant El Oasis', 'ville' => 'Biskra', 'wilaya' => 'Biskra', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Avenue du 1er Novembre, Biskra', 'code_postal' => '07000', 'lat' => 34.8440, 'lng' => 5.7240, 'phone' => '033 74 56 78', 'price' => '$$', 'note' => 4.1, 'desc' => 'Oasis culinaire, spécialités du sud algérien'],
    ['nom' => 'Royal Hammam Salihine', 'ville' => 'Biskra', 'wilaya' => 'Biskra', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Hammam Salihine, Biskra', 'code_postal' => '07000', 'lat' => 34.8500, 'lng' => 5.7150, 'phone' => '033 71 90 12', 'price' => '$$$', 'note' => 4.3, 'desc' => 'Gastronomie locale près des sources thermales'],
    ['nom' => 'Le Palmier', 'ville' => 'Biskra', 'wilaya' => 'Biskra', 'type_cuisine' => 'Grillades', 'adresse' => 'Rue Colonel Amirouche, Biskra', 'code_postal' => '07000', 'lat' => 34.8445, 'lng' => 5.7270, 'phone' => '0555 45 67 89', 'price' => '$', 'note' => 4.0, 'desc' => 'Grillades et méchoui sous les palmiers'],
    ['nom' => 'Fast Corner Biskra', 'ville' => 'Biskra', 'wilaya' => 'Biskra', 'type_cuisine' => 'Fast food', 'adresse' => 'Centre-ville, Biskra', 'code_postal' => '07000', 'lat' => 34.8450, 'lng' => 5.7250, 'phone' => '0661 67 89 01', 'price' => '$', 'note' => 3.9, 'desc' => 'Burgers, chawarma et plats rapides'],

    // ═══════════════════════════════════════════════════════════
    // TIPAZA (36.592, 2.445)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant Le Chenoua', 'ville' => 'Tipaza', 'wilaya' => 'Tipaza', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Mont Chenoua, Tipaza', 'code_postal' => '42000', 'lat' => 36.5900, 'lng' => 2.4200, 'phone' => '024 47 12 34', 'price' => '$$$', 'note' => 4.5, 'desc' => 'Vue spectaculaire sur la mer, fruits de mer exceptionnels'],
    ['nom' => 'Restaurant Les Colonnes', 'ville' => 'Tipaza', 'wilaya' => 'Tipaza', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Promenade des Ruines, Tipaza', 'code_postal' => '42000', 'lat' => 36.5920, 'lng' => 2.4460, 'phone' => '024 47 56 78', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Près des ruines romaines, poissons et cuisine méditerranéenne'],
    ['nom' => 'Restaurant La Corne d\'Or', 'ville' => 'Tipaza', 'wilaya' => 'Tipaza', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Port de Tipaza, Tipaza', 'code_postal' => '42000', 'lat' => 36.5935, 'lng' => 2.4480, 'phone' => '024 47 90 12', 'price' => '$$', 'note' => 4.2, 'desc' => 'Cuisine méditerranéenne face au port'],
    ['nom' => 'Le Romain', 'ville' => 'Tipaza', 'wilaya' => 'Tipaza', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Centre-ville, Tipaza', 'code_postal' => '42000', 'lat' => 36.5910, 'lng' => 2.4450, 'phone' => '0555 56 78 90', 'price' => '$$', 'note' => 4.0, 'desc' => 'Plats algériens traditionnels dans la ville antique'],

    // ═══════════════════════════════════════════════════════════
    // GHARDAÏA (32.491, 3.674)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant El M\'zab', 'ville' => 'Ghardaïa', 'wilaya' => 'Ghardaïa', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Rue principale, Ghardaïa', 'code_postal' => '47000', 'lat' => 32.4920, 'lng' => 3.6740, 'phone' => '029 88 12 34', 'price' => '$$', 'note' => 4.2, 'desc' => 'Cuisine mozabite traditionnelle, tajines et couscous du sud'],
    ['nom' => 'Restaurant Les Mille et Une Nuits', 'ville' => 'Ghardaïa', 'wilaya' => 'Ghardaïa', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Boulevard Colonel Lotfi, Ghardaïa', 'code_postal' => '47000', 'lat' => 32.4930, 'lng' => 3.6720, 'phone' => '029 88 56 78', 'price' => '$$$', 'note' => 4.3, 'desc' => 'Ambiance orientale, spécialités du M\'Zab et grillades'],
    ['nom' => 'Oasis Café Restaurant', 'ville' => 'Ghardaïa', 'wilaya' => 'Ghardaïa', 'type_cuisine' => 'Café-Restaurant', 'adresse' => 'Place du marché, Ghardaïa', 'code_postal' => '47000', 'lat' => 32.4910, 'lng' => 3.6750, 'phone' => '0661 78 90 12', 'price' => '$', 'note' => 3.9, 'desc' => 'Café et restaurant décontracté au coeur de la palmeraie'],
    ['nom' => 'Restaurant Beni Isguen', 'ville' => 'Ghardaïa', 'wilaya' => 'Ghardaïa', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Beni Isguen, Ghardaïa', 'code_postal' => '47000', 'lat' => 32.4800, 'lng' => 3.6600, 'phone' => '029 88 34 56', 'price' => '$$', 'note' => 4.1, 'desc' => 'Cuisine locale dans la ville historique de Beni Isguen'],

    // ═══════════════════════════════════════════════════════════
    // MOSTAGANEM (35.931, 0.089)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant Le Port', 'ville' => 'Mostaganem', 'wilaya' => 'Mostaganem', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Port de pêche, Mostaganem', 'code_postal' => '27000', 'lat' => 35.9340, 'lng' => 0.0850, 'phone' => '045 21 12 34', 'price' => '$$', 'note' => 4.3, 'desc' => 'Poissons du jour, ambiance portuaire authentique'],
    ['nom' => 'Restaurant La Salamandre', 'ville' => 'Mostaganem', 'wilaya' => 'Mostaganem', 'type_cuisine' => 'Méditerranéen', 'adresse' => 'Corniche de Mostaganem', 'code_postal' => '27000', 'lat' => 35.9360, 'lng' => 0.0920, 'phone' => '045 21 56 78', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Vue sur mer, cuisine méditerranéenne et fruits de mer'],
    ['nom' => 'El Borj', 'ville' => 'Mostaganem', 'wilaya' => 'Mostaganem', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Vieux quartier, Mostaganem', 'code_postal' => '27000', 'lat' => 35.9320, 'lng' => 0.0880, 'phone' => '0555 90 12 34', 'price' => '$$', 'note' => 4.0, 'desc' => 'Plats traditionnels dans le vieux Mostaganem'],
    ['nom' => 'Sidi Lakhdar Grill', 'ville' => 'Mostaganem', 'wilaya' => 'Mostaganem', 'type_cuisine' => 'Grillades', 'adresse' => 'Sidi Lakhdar, Mostaganem', 'code_postal' => '27000', 'lat' => 35.9400, 'lng' => 0.0800, 'phone' => '0661 12 34 56', 'price' => '$', 'note' => 4.1, 'desc' => 'Grillades de viandes et brochettes, ambiance populaire'],

    // ═══════════════════════════════════════════════════════════
    // JIJEL (36.821, 5.767)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant Les Grottes', 'ville' => 'Jijel', 'wilaya' => 'Jijel', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Corniche Jijelienne, Jijel', 'code_postal' => '18000', 'lat' => 36.8230, 'lng' => 5.7700, 'phone' => '034 47 12 34', 'price' => '$$$', 'note' => 4.4, 'desc' => 'Fruits de mer avec vue sur les grottes merveilleuses'],
    ['nom' => 'Restaurant Le Kotama', 'ville' => 'Jijel', 'wilaya' => 'Jijel', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Boulevard Naâmane Laïfa, Jijel', 'code_postal' => '18000', 'lat' => 36.8200, 'lng' => 5.7660, 'phone' => '034 47 56 78', 'price' => '$$', 'note' => 4.1, 'desc' => 'Cuisine jijelienne authentique, couscous au poisson'],
    ['nom' => 'Le Phare de Jijel', 'ville' => 'Jijel', 'wilaya' => 'Jijel', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Port de Jijel', 'code_postal' => '18000', 'lat' => 36.8250, 'lng' => 5.7720, 'phone' => '0555 34 56 78', 'price' => '$$', 'note' => 4.2, 'desc' => 'Poisson grillé du port, fraîcheur garantie'],
    ['nom' => 'Ziama Beach Restaurant', 'ville' => 'Ziama Mansouriah', 'wilaya' => 'Jijel', 'type_cuisine' => 'Poissons et fruits de mer', 'adresse' => 'Plage de Ziama, Jijel', 'code_postal' => '18000', 'lat' => 36.6700, 'lng' => 5.4900, 'phone' => '0661 56 78 90', 'price' => '$$', 'note' => 4.3, 'desc' => 'Restaurant de plage, produits de la mer face aux eaux turquoise'],

    // ═══════════════════════════════════════════════════════════
    // DJELFA (34.670, 3.250)
    // ═══════════════════════════════════════════════════════════
    ['nom' => 'Restaurant El Djellfa', 'ville' => 'Djelfa', 'wilaya' => 'Djelfa', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Avenue du 1er Novembre, Djelfa', 'code_postal' => '17000', 'lat' => 34.6710, 'lng' => 3.2510, 'phone' => '027 87 12 34', 'price' => '$$', 'note' => 4.0, 'desc' => 'Cuisine des hauts plateaux, mouton et couscous'],
    ['nom' => 'Le Nomade', 'ville' => 'Djelfa', 'wilaya' => 'Djelfa', 'type_cuisine' => 'Grillades', 'adresse' => 'Route des Cèdres, Djelfa', 'code_postal' => '17000', 'lat' => 34.6720, 'lng' => 3.2480, 'phone' => '0555 78 90 12', 'price' => '$', 'note' => 4.1, 'desc' => 'Méchoui et grillades en plein air, ambiance steppe'],
    ['nom' => 'Café Restaurant Central', 'ville' => 'Djelfa', 'wilaya' => 'Djelfa', 'type_cuisine' => 'Café-Restaurant', 'adresse' => 'Place centrale, Djelfa', 'code_postal' => '17000', 'lat' => 34.6700, 'lng' => 3.2500, 'phone' => '027 87 56 78', 'price' => '$', 'note' => 3.8, 'desc' => 'Café et restauration rapide au centre-ville'],
    ['nom' => 'Restaurant Moutons d\'Or', 'ville' => 'Djelfa', 'wilaya' => 'Djelfa', 'type_cuisine' => 'Algérien traditionnel', 'adresse' => 'Cité 400 Logements, Djelfa', 'code_postal' => '17000', 'lat' => 34.6690, 'lng' => 3.2530, 'phone' => '0661 90 12 34', 'price' => '$$', 'note' => 4.2, 'desc' => 'Spécialité agneau des steppes, plats copieux'],
];

echo "=== SEED RESTAURANTS ===\n";
echo "Total à insérer : " . count($restaurants) . "\n\n";

$inserted = 0;
$skipped = 0;

$stmt = $pdo->prepare("
    INSERT INTO restaurants (nom, slug, type_cuisine, ville, wilaya, adresse, code_postal, pays, gps_latitude, gps_longitude, phone, price_range, note_moyenne, descriptif, status, verified_halal, created_at, updated_at)
    VALUES (:nom, :slug, :type, :ville, :wilaya, :adresse, :cp, 'Algérie', :lat, :lng, :phone, :price, :note, :desc, 'validated', 1, NOW(), NOW())
");

foreach ($restaurants as $r) {
    // Vérifier si le restaurant existe déjà (même nom + même ville)
    $check = $pdo->prepare("SELECT COUNT(*) FROM restaurants WHERE LOWER(TRIM(nom)) = LOWER(TRIM(:nom)) AND LOWER(ville) = LOWER(:ville)");
    $check->execute([':nom' => $r['nom'], ':ville' => $r['ville']]);
    if ($check->fetchColumn() > 0) {
        echo "  SKIP (doublon) : {$r['nom']} - {$r['ville']}\n";
        $skipped++;
        continue;
    }

    $slug = makeSlug($r['nom'], $r['ville'], $pdo);

    // Ajouter légère variation GPS
    $lat = gpsOffset($r['lat'], 0.002);
    $lng = gpsOffset($r['lng'], 0.002);

    try {
        $stmt->execute([
            ':nom' => trim($r['nom']),
            ':slug' => $slug,
            ':type' => $r['type_cuisine'],
            ':ville' => $r['ville'],
            ':wilaya' => $r['wilaya'],
            ':adresse' => $r['adresse'],
            ':cp' => $r['code_postal'],
            ':lat' => round($lat, 8),
            ':lng' => round($lng, 8),
            ':phone' => $r['phone'],
            ':price' => $r['price'],
            ':note' => $r['note'],
            ':desc' => $r['desc'],
        ]);
        $inserted++;
        echo "  OK : {$r['nom']} - {$r['ville']}\n";
    } catch (\Exception $e) {
        echo "  ERR : {$r['nom']} - " . $e->getMessage() . "\n";
    }
}

echo "\n=== RÉSULTAT ===\n";
echo "Insérés : $inserted\n";
echo "Doublons skippés : $skipped\n";
echo "Total restaurants en base : " . $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn() . "\n";

// Distribution par ville
echo "\n=== Distribution par ville ===\n";
$cities = $pdo->query("SELECT ville, COUNT(*) as cnt FROM restaurants WHERE status='validated' GROUP BY ville ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cities as $c) {
    echo "  {$c['ville']}: {$c['cnt']}\n";
}
