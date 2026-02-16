-- =====================================================
-- LeBonResto - Seed Real Algerian Restaurants
-- 14 cities: Constantine, Annaba, Sétif, Béjaïa, Tlemcen,
--            Batna, Blida, Biskra, Tizi Ouzou, Ghardaia,
--            Djelfa, Mostaganem, Jijel, Tipaza
-- =====================================================
-- Sources: TripAdvisor, Petit Futé, lacarte.menu, GoAlger,
--          OpenAlfa, restoalgerie.com, Google Maps data
-- =====================================================

-- Format: INSERT INTO vendeur (Nom, Type, adresse, codePostal, ville, owner, gps, pricerange, note, descriptif, mea, phone, web)

-- =====================================================
-- CONSTANTINE (Wilaya 25) - Code postal 25000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Tiddis', 'Restaurant', 'Centre-ville, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3650,6.6147', '$$', 3.8, 'Restaurant traditionnel algérien à Constantine, cuisine locale et plats du terroir dans un cadre authentique. Spécialités constantinoises.', 0, 'N/A', 'N/A'),
('Dar Es-Soltane', 'Restaurant', 'Vieux Constantine, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3658,6.6126', '$$', 3.5, 'Restaurant de cuisine traditionnelle algérienne dans le vieux Constantine. Ambiance historique, plats fait maison, couscous et tajines.', 0, 'N/A', 'N/A'),
('Restaurant La Concorde', 'Restaurant', 'Centre-ville, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3651,6.6150', '$$', 3.9, 'Restaurant offrant une authentique cuisine algérienne. Cadre soigné et service attentionné au coeur de Constantine.', 0, 'N/A', 'N/A'),
('Qasar Restaurant', 'Restaurant', 'Constantine Marriott Hotel, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3480,6.6396', '$$$', 4.1, 'Restaurant méditerranéen et algérien au sein du Marriott Constantine. Buffet varié, cadre élégant et vue panoramique.', 0, 'N/A', 'N/A'),
('Siniet El Bey', 'Restaurant', 'Route de Hamma Bouziane, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.4050,6.5961', '$$', 4.0, 'Restaurant proposant une cuisine algérienne authentique avec vue imprenable. Spécialités de Constantine et grillades.', 0, 'N/A', 'N/A'),
('Titta', 'Restaurant', 'Rue Bouhali Laid, El Khroub 25100, Algérie', 25100, 'Constantine', 'N/A', '36.2636,6.6978', '$$', 4.0, 'Restaurant populaire à El Khroub spécialisé dans les viandes et salades. Très apprécié localement avec plus de 300 avis positifs.', 0, '+213552030603', 'N/A'),
('Le Frensh Pizza Tacos', 'Pizzeria', 'Rue Zouieche Amar, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3612,6.6208', '$', 4.6, 'Pizzeria et fast-food populaire à Constantine. Pizza au feu de bois, tacos et sandwiches. Excellent rapport qualité-prix.', 0, '+213540774442', 'N/A'),
('Casa Mia', 'Pizzeria', 'Belle Vue, 11 Rue Barkat Lakhdar, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3627,6.6289', '$$', 3.4, 'Pizzeria et café au coeur de Constantine. Pizzas variées, pâtes et boissons chaudes dans un cadre convivial.', 0, '+213558203083', 'N/A'),
('Mega Pizza St Jean', 'Pizzeria', 'Quartier St Jean, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3565,6.6322', '$', 4.5, 'Pizzeria réputée pour ses pizzas généreuses et savoureuses. Cuisson au feu de bois, livraison disponible.', 0, '+213540739453', 'N/A'),
('Pizzeria Napel Feu De Bois', 'Pizzeria', 'Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3590,6.6175', '$', 4.5, 'Pizzeria artisanale avec cuisson au feu de bois. Pâte maison, ingrédients frais et saveurs authentiques italiennes.', 0, 'N/A', 'N/A'),
('La Baguette', 'Fast food', 'Rue Dr. Lavran, Centre-ville, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3648,6.6143', '$', 3.2, 'Sandwicherie et pizzeria rapide au centre de Constantine. Idéal pour un repas sur le pouce.', 0, '+213555713127', 'N/A'),
('Patisserie Castello', 'Pâtisserie', 'Centre-ville, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3645,6.6155', '$$', 3.8, 'Pâtisserie orientale et occidentale. Gâteaux traditionnels constantinois, pièces montées et viennoiseries.', 0, '+213772902473', 'N/A'),
('Cafe Des Amis', 'Café-Restaurant', 'Hamma Bouziane, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.4105,6.5897', '$', 4.8, 'Café-restaurant convivial à Hamma Bouziane. Petit-déjeuner, déjeuner et boissons chaudes dans une ambiance chaleureuse.', 0, 'N/A', 'N/A'),
('Restaurant Le Vieux Roche', 'Restaurant', 'Vieille ville, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3661,6.6130', '$$', 3.7, 'Restaurant traditionnel dans le vieux Constantine. Plats du terroir, rechta, couscous et chakhchoukha dans un cadre historique.', 0, 'N/A', 'N/A'),
('Parc Du Bardo', 'Restaurant', 'Parc du Bardo, Constantine 25000, Algérie', 25000, 'Constantine', 'N/A', '36.3544,6.5980', '$$', 4.1, 'Restaurant situé dans le magnifique parc du Bardo. Cuisine algérienne et grillades en plein air avec vue sur la nature.', 0, 'N/A', 'N/A');

-- =====================================================
-- ANNABA (Wilaya 23) - Code postal 23000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('La Renaissance', 'Restaurant', 'Centre-ville, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9000,7.7667', '$$$', 4.3, 'Restaurant numéro 1 à Annaba selon TripAdvisor. Salle à manger élégante avec tableaux décoratifs et musique live. Cuisine raffinée.', 0, 'N/A', 'N/A'),
('Le Pecheur', 'Restaurant de fruits de mer', '1 Rue De L\'Avant Port, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9042,7.7619', '$$', 4.5, 'Restaurant de fruits de mer frais au port d\'Annaba. Poisson grillé, crevettes et calamars avec vue sur le port.', 0, '+21338454975', 'N/A'),
('La Caravelle', 'Restaurant de fruits de mer', 'Rue Frères Saadane, Corniche, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9120,7.7510', '$$', 3.8, 'Restaurant de fruits de mer et poissons sur la corniche d\'Annaba. Spécialités méditerranéennes avec vue sur mer.', 0, '+21338454986', 'N/A'),
('Restaurant Tabarka', 'Restaurant de fruits de mer', 'Bd Bouzerad Hocine, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.8997,7.7672', '$$', 3.7, 'Restaurant de fruits de mer et soupes de poisson. Spécialités tunisiennes et algériennes dans un cadre authentique.', 0, '+213541240779', 'N/A'),
('Pavillon 23', 'Restaurant', 'Centre-ville, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9005,7.7660', '$$$', 4.4, 'Restaurant gastronomique d\'Annaba. Cuisine raffinée, service impeccable et ambiance élégante. Réservation recommandée.', 0, 'N/A', 'N/A'),
('La Cerise', 'Restaurant', '35 Rue Ben Ouhiba Mohamed, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.8989,7.7655', '$$$', 5.0, 'Restaurant hautement noté à Annaba. Cuisine créative et service excellent. Cadre moderne et raffiné.', 0, 'N/A', 'N/A'),
('Friends Food', 'Fast food', 'Rue Ghodban Messaoud, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9010,7.7640', '$', 5.0, 'Fast-food populaire proposant burgers, sandwiches et plats rapides. Service rapide et portions généreuses.', 0, '+21338436093', 'N/A'),
('Diamantina Annaba', 'Restaurant', 'Centre-ville, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9002,7.7670', '$$', 4.1, 'Restaurant et salon de thé à Annaba. Pâtisseries, plats du jour et boissons. Ouvert de 8h à 22h.', 0, '+213665487392', 'N/A'),
('Minos', 'Pizzeria', 'Boulevard Fellah Rachid, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9015,7.7635', '$', 4.1, 'Pizzeria et sandwicherie au boulevard principal d\'Annaba. Pizzas variées, sandwiches et tacos.', 0, '+213556394064', 'N/A'),
('El-Mouna Cafe Gourmand', 'Café-Restaurant', 'Boulevard Fellah Rachid, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.9018,7.7630', '$$', 4.2, 'Café gourmand proposant petit-déjeuner, chocolaterie et boissons premium. Ambiance cosy et moderne.', 0, 'N/A', 'N/A'),
('Pizza Pizza', 'Pizzeria', 'Centre-ville, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.8995,7.7665', '$', 3.9, 'Pizzeria populaire à Annaba. Pizzas et tacos à prix abordables. Livraison disponible.', 0, '+213555131443', 'N/A'),
('Big Baba', 'Fast food', 'Zaafraniya, Annaba 23000, Algérie', 23000, 'Annaba', 'N/A', '36.8980,7.7690', '$', 3.4, 'Fast-food spécialisé dans les sandwiches et le poulet grillé. Service rapide et prix attractifs.', 0, '+213542113988', 'N/A');

-- =====================================================
-- SÉTIF (Wilaya 19) - Code postal 19000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Pizza Pino Setif', 'Pizzeria', 'Cité Dalas, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1898,5.4108', '$', 4.2, 'Pizzeria italienne populaire à Sétif avec plus de 29 avis. Pizzas au feu de bois, pâtes et cuisine italienne authentique.', 0, '+213557902433', 'N/A'),
('Brezza', 'Restaurant italien', 'Park Mall Hotel, 17ème étage, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1910,5.4130', '$$$', 5.0, 'Restaurant italien haut de gamme au 17ème étage du Park Mall Hotel. Vue panoramique sur Sétif, cuisine italienne et algérienne raffinée.', 0, 'N/A', 'N/A'),
('Restaurant Bab El Hara', 'Restaurant syrien', 'Jardin 1er Novembre 1954, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1905,5.4078', '$$', 3.5, 'Restaurant syrien au coeur de Sétif. Shawarma, falafel, houmous et spécialités du Moyen-Orient. Ambiance chaleureuse.', 0, 'N/A', 'N/A'),
('Maharaja', 'Restaurant indien', 'Cité des 750 logements N25/146, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1920,5.4050', '$$', 3.9, 'Restaurant indien authentique géré par un chef venu d\'Inde. Curry, tandoori, naan et spécialités indiennes. Unique à Sétif.', 0, '+213550437594', 'N/A'),
('Khaima Ham Ham', 'Restaurant', 'Entre l\'hôpital et Cité Gasria, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1870,5.4140', '$$', 4.5, 'Restaurant traditionnel spécialisé dans la Chakhchoukha, plat emblématique de Sétif. Cadre sous la tente, cuisine du terroir.', 0, 'N/A', 'N/A'),
('L\'Hacienda Setif', 'Restaurant turc', 'Avenue de l\'ALN, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1885,5.4095', '$$', 4.0, 'Restaurant turc proposant kebabs, grillades et plats copieux. Ambiance orientale et service convivial.', 0, 'N/A', 'N/A'),
('L\'Asiatico Setif', 'Restaurant asiatique', 'Centre-ville, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1895,5.4100', '$$', 4.0, 'Restaurant japonais et asiatique à Sétif. Sushis, nouilles sautées et plats asiatiques variés. Décor moderne.', 0, 'N/A', 'N/A'),
('Chicha Khayma Khano', 'Restaurant', 'Centre-ville, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1900,5.4110', '$$', 3.8, 'Restaurant et salon de thé avec narguilé. Cuisine algérienne traditionnelle et grillades. Ambiance décontractée.', 0, 'N/A', 'N/A'),
('Restaurant Le Palmier', 'Restaurant', 'Boulevard 8 Mai 1945, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1915,5.4065', '$$', 4.0, 'Restaurant familial au coeur de Sétif. Cuisine algérienne traditionnelle, grillades et couscous. Terrasse agréable.', 0, 'N/A', 'N/A'),
('Resto Pizza Sétif', 'Pizzeria', 'Cité El Hidhab, Sétif 19000, Algérie', 19000, 'Sétif', 'N/A', '36.1850,5.4200', '$', 3.8, 'Pizzeria de quartier proposant pizzas, paninis et sandwiches. Bon rapport qualité-prix et livraison rapide.', 0, 'N/A', 'N/A');

-- =====================================================
-- BÉJAÏA (Wilaya 06) - Code postal 06000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Restaurant La Citadelle', 'Restaurant de fruits de mer', 'Cité Tobbal, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7509,5.0564', '$$$', 4.8, 'Restaurant de fruits de mer et cuisine française à Béjaïa. Accueil chaleureux, très bon poisson frais. Vue imprenable sur la mer.', 0, 'N/A', 'N/A'),
('Dar Adel Restaurant', 'Restaurant de fruits de mer', 'Rue de La Liberté, El Khmis, Derrière La Banque BDL, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7520,5.0580', '$$', 4.3, 'Restaurant steakhouse et fruits de mer. Bon rapport qualité-prix, service agréable. Viandes grillées et poisson frais.', 0, 'N/A', 'N/A'),
('L\'Étoile De Mer', 'Restaurant de fruits de mer', 'Tichy, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.6283,5.3186', '$$', 4.2, 'Restaurant de fruits de mer et cuisine méditerranéenne à Tichy. Poisson grillé, calamars et crevettes face à la mer.', 0, 'N/A', 'N/A'),
('Tacos De Lyon Béjaïa', 'Fast food', 'Naciria, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7450,5.0620', '$', 4.0, 'Fast-food spécialisé dans les tacos à la lyonnaise. Sandwiches, burgers et tacos généreux à prix doux.', 0, '+213561400269', 'N/A'),
('Restaurant Le Cap', 'Restaurant', 'Cap Carbon, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7700,5.0800', '$$', 4.0, 'Restaurant avec vue spectaculaire sur le Cap Carbon. Cuisine algérienne et fruits de mer. Cadre naturel exceptionnel.', 0, 'N/A', 'N/A'),
('InnovaFood', 'Fast food', 'Route des Aurès, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7480,5.0600', '$', 3.8, 'Fast-food moderne proposant burgers, tacos et sandwiches créatifs. Ambiance jeune et dynamique.', 0, 'N/A', 'N/A'),
('Rôtisserie L\'Excellence', 'Restaurant', 'Centre-ville, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7505,5.0570', '$', 4.0, 'Rôtisserie proposant poulet rôti, grillades et plats à emporter. Qualité constante et prix abordables.', 0, 'N/A', 'N/A'),
('Resto Sohaib', 'Restaurant', 'Centre-ville, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7510,5.0555', '$', 3.9, 'Restaurant populaire proposant cuisine algérienne quotidienne. Plats du jour, soupes et grillades à prix accessibles.', 0, 'N/A', 'N/A'),
('Damas Lunch', 'Restaurant syrien', 'CHU Targa Ouzemour, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7350,5.0750', '$', 4.0, 'Restaurant syrien proche de l\'université. Shawarma, falafel et plats syriens à emporter ou sur place.', 0, 'N/A', 'N/A'),
('Bylka Café', 'Café-Restaurant', 'Rue Boudechicha Tahar, Béjaïa 06000, Algérie', 6000, 'Béjaïa', 'N/A', '36.7515,5.0560', '$$', 4.1, 'Café-restaurant moderne au coeur de Béjaïa. Petit-déjeuner, déjeuner et pâtisseries. Terrasse agréable.', 0, 'N/A', 'N/A');

-- =====================================================
-- TLEMCEN (Wilaya 13) - Code postal 13000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Restaurant Equinoxe', 'Restaurant', 'En face de l\'hôpital, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8828,-1.3148', '$$$', 4.5, 'Un des joyaux de Tlemcen. Cuisine gastronomique moderne avec touches de recettes traditionnelles maghrébines. Décor innovant, ambiance raffinée.', 0, '043 41 73 60', 'https://restaurant-equinoxe.com/'),
('Restaurant L\'Impériale', 'Restaurant', '749 Cité Des Amandiers Kiffane, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8790,-1.3180', '$$', 4.2, 'Restaurant marocain et méditerranéen. Tajines, couscous et grillades dans un cadre élégant. Très bonne adresse à Tlemcen.', 0, 'N/A', 'N/A'),
('Restaurant Marrakech', 'Restaurant marocain', '78 Lotissement Korso Boulevard Imama, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8835,-1.3155', '$$', 4.0, 'Restaurant marocain authentique au centre de Tlemcen. Tajines, pastilla et couscous royal. Équipe accueillante.', 0, 'N/A', 'N/A'),
('Restaurant Marrakech Mansoura', 'Restaurant marocain', 'Rue Ain Nedjar, Mansoura, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8600,-1.3350', '$$', 4.1, 'Restaurant marocain à Mansoura près des ruines historiques. Tajines et couscous traditionnels dans un cadre pittoresque.', 0, 'N/A', 'N/A'),
('Pizzeria Oscar', 'Pizzeria', 'Centre-ville, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8830,-1.3150', '$', 3.8, 'Pizzeria proposant des pizzas délicieuses et saveurs locales authentiques. Bon rapport qualité-prix.', 0, 'N/A', 'N/A'),
('Pizzeria Felicita', 'Pizzeria', 'Centre-ville, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8825,-1.3145', '$', 4.0, 'Pizzeria aux saveurs italiennes authentiques. Pâte fine, garnitures généreuses et cuisson parfaite.', 0, 'N/A', 'N/A'),
('Pizzeria La Crouste', 'Pizzeria', 'Centre-ville, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8820,-1.3160', '$', 3.7, 'Pizzeria populaire offrant des saveurs italiennes délicieuses. Ambiance décontractée et prix accessibles.', 0, 'N/A', 'N/A'),
('Check\'it Tlemcen', 'Fast food', 'Centre-ville, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8832,-1.3142', '$', 3.9, 'Fast-food moderne servant pizzas et sandwiches. Accueil chaleureux et service rapide. Idéal pour les jeunes.', 0, 'N/A', 'N/A'),
('Restaurant El Nasr', 'Restaurant', 'Centre-ville, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8827,-1.3153', '$$', 4.0, 'Restaurant spécialisé dans la cuisine algérienne authentique. Plats traditionnels tlemcéniens, rechta et trida.', 0, 'N/A', 'N/A'),
('Restaurant Karam', 'Restaurant', 'Centre-ville, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8833,-1.3147', '$$', 4.1, 'Restaurant méditerranéen proposant grillades, salades et plats algériens. Cadre agréable et service soigné.', 0, 'N/A', 'N/A'),
('Le Géant Tlemcen', 'Restaurant', 'Centre-ville, Tlemcen 13000, Algérie', 13000, 'Tlemcen', 'N/A', '34.8826,-1.3158', '$$', 3.8, 'Grand restaurant proposant cuisine algérienne alliant tradition et modernité. Capacité d\'accueil importante, idéal pour les groupes.', 0, 'N/A', 'N/A');

-- =====================================================
-- BATNA (Wilaya 05) - Code postal 05000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Beity', 'Restaurant', 'Centre-ville, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5567,6.1739', '$$', 4.5, 'Restaurant familial très populaire à Batna avec plus de 90 avis. Cuisine algérienne traditionnelle, grillades et plats copieux. Réservation possible.', 0, '+213666959595', 'N/A'),
('Beity Tazoult', 'Restaurant', '45 Route De Lambèze, Tazoult-Lambese, Batna, Algérie', 5000, 'Batna', 'N/A', '35.4830,6.2660', '$$', 4.4, 'Deuxième adresse du célèbre Beity à Tazoult. Cuisine traditionnelle dans un cadre familial près des ruines romaines.', 0, '+213666959595', 'N/A'),
('Calija Fast Food', 'Fast food', 'Avenue De L\'Indépendance, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5560,6.1745', '$', 4.7, 'Fast-food populaire proposant burgers, frites, sandwiches et pizzas. Terrasse extérieure et service rapide.', 0, '+213674513515', 'N/A'),
('Le Grec', 'Fast food', 'Centre-ville, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5555,6.1730', '$', 4.0, 'Fast-food spécialisé dans les burgers et tacos grecs. Ouvert jusqu\'à minuit. Portions généreuses.', 0, '+213773821902', 'N/A'),
('Petit Prince', 'Restaurant', 'Rue Mustapha Kaouda, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5570,6.1750', '$$', 3.9, 'Restaurant proposant cuisine variée et plats mexicains. Cadre original et ambiance conviviale. Service de 10h30 à 22h.', 0, '+213557252933', 'N/A'),
('Le Carré Caffè Terrasse', 'Café-Restaurant', 'Rue Larbi Tebessi, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5562,6.1735', '$$', 3.3, 'Café-restaurant avec terrasse proposant pizzas et boissons. Cadre agréable pour se détendre.', 0, '+213550238767', 'N/A'),
('Creperie Kinder 2', 'Fast food', 'Avenue De l\'ANP, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5575,6.1755', '$', 3.4, 'Crêperie et fast-food proposant pizzas, tacos et crêpes sucrées et salées. Idéal pour les familles.', 0, 'N/A', 'N/A'),
('Chicken Grill', 'Fast food', 'Lotissement Des Moudjahidines, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5550,6.1760', '$', 3.1, 'Restaurant spécialisé dans le poulet grillé et rôti. Menu simple et prix attractifs. Livraison disponible.', 0, '+213550086714', 'N/A'),
('Poissonnerie Roukhou', 'Restaurant de fruits de mer', 'Route Nationale 31, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5545,6.1720', '$$', 3.9, 'Poissonnerie et restaurant de poisson frais. Poisson grillé, frit ou en sauce. Ouvert de 8h à 23h.', 0, '+213772034982', 'N/A'),
('La Petite Maison', 'Restaurant de fruits de mer', 'Centre-ville, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5558,6.1742', '$$', 4.0, 'Restaurant de poissons et fruits de mer avec pizzas. Poisson grillé, farci et frit, pâtes et soupes. Cadre intime.', 0, 'N/A', 'N/A'),
('La Corniola', 'Restaurant', 'Centre-ville, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5565,6.1738', '$$', 3.8, 'Restaurant oriental proposant shawarma, pizzas, burgers et poulet grillé. Service rapide et cuisine variée.', 0, 'N/A', 'N/A'),
('Odeon & Babylon', 'Café-Restaurant', 'Centre-ville, Batna 05000, Algérie', 5000, 'Batna', 'N/A', '35.5563,6.1740', '$$', 4.2, 'Café-restaurant européen moderne. Cuisine italienne et française, desserts raffinés. Décor moderne et ambiance calme.', 0, 'N/A', 'N/A');

-- =====================================================
-- BLIDA (Wilaya 09) - Code postal 09000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Restaurant Dar El Nnouar', 'Restaurant', '79 Avenue Mustapha Benboulaid, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4714,2.8283', '$$', 4.2, 'Restaurant traditionnel algérien à Blida. Rechta, couscous et plats du terroir. Options végétariennes disponibles. Cadre familial.', 0, 'N/A', 'N/A'),
('Café-Restaurant Le Ciné', 'Café-Restaurant', 'Centre-ville, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4700,2.8275', '$$', 4.3, 'Café-restaurant moderne et propre au centre de Blida. Décoration moderne, personnel serviable et prix moyens.', 0, 'N/A', 'N/A'),
('Le Grand Boulevard', 'Restaurant', 'Boulevard principal, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4710,2.8280', '$$', 4.0, 'Restaurant français, américain et algérien. Cuisine variée et généreuse dans un cadre élégant sur le grand boulevard.', 0, 'N/A', 'N/A'),
('Le 84', 'Restaurant', 'Centre-ville, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4705,2.8278', '$$', 4.1, 'Restaurant moderne proposant cuisine algérienne et internationale. Ambiance branchée et plats créatifs.', 0, 'N/A', 'N/A'),
('Restaurant Bab El Ward', 'Restaurant', 'Centre-ville, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4708,2.8270', '$$', 4.0, 'Restaurant familial avec menu varié entre spécialités algériennes et orientales. Portions généreuses et prix raisonnables.', 0, 'N/A', 'N/A'),
('Fissa Food', 'Fast food', 'Boulevard Mohamed Boudiaf, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4720,2.8290', '$', 3.8, 'Fast-food et pizzeria à Blida. Pizzas, sandwiches et plats rapides. Bon rapport qualité-prix.', 0, '+213554206967', 'N/A'),
('Pyramid Pizzeria', 'Pizzeria', 'Centre-ville, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4712,2.8282', '$', 3.9, 'Pizzeria populaire au centre de Blida. Pizzas variées, cuisson au feu de bois et ambiance conviviale.', 0, 'N/A', 'N/A'),
('Savannah Pizzeria', 'Pizzeria', 'Centre-ville, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4715,2.8285', '$', 3.7, 'Pizzeria proposant un large choix de pizzas et de garnitures. Livraison disponible dans tout Blida.', 0, 'N/A', 'N/A'),
('Venezia Pizza & Doner', 'Pizzeria', 'Centre-ville, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4718,2.8288', '$', 3.8, 'Pizzeria et doner kebab. Pizzas, tacos et sandwiches doner. Service rapide et prix attractifs.', 0, 'N/A', 'N/A'),
('Patron Pizzeria', 'Pizzeria', 'Centre-ville, Blida 09000, Algérie', 9000, 'Blida', 'N/A', '36.4702,2.8272', '$', 4.0, 'Pizzeria artisanale proposant des pizzas généreuses. Cadre simple et efficace, idéal pour un repas entre amis.', 0, '+213796958599', 'N/A');

-- =====================================================
-- BISKRA (Wilaya 07) - Code postal 07000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Mr. BBQ', 'Barbecue restaurant', 'Centre-ville, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8484,5.7276', '$$', 4.3, 'Meilleur restaurant de grillades de Biskra. Décor rustique et magnifique, accueil chaleureux. Viandes grillées de qualité supérieure.', 0, 'N/A', 'N/A'),
('Restaurant Janitou', 'Restaurant', 'Centre-ville, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8480,5.7280', '$$', 4.3, 'Restaurant proposant bonne cuisine algérienne dans un cadre propre. Grillades, soupes et plats du jour. Service agréable.', 0, 'N/A', 'N/A'),
('Tanjra Restaurant', 'Restaurant', 'Route vers Ouargla, Biskra, Algérie', 7000, 'Biskra', 'N/A', '34.8200,5.7400', '$$', 4.7, 'Restaurant unique au milieu du désert entre Biskra et Ouargla. Architecture magnifique entourée d\'une grande ferme. Cuisine traditionnelle.', 0, 'N/A', 'N/A'),
('Damasco Food', 'Restaurant syrien', 'Rue Mohammed Khider, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8490,5.7270', '$', 3.0, 'Restaurant syrien proposant shawarma, falafel et grillades moyen-orientales. Service rapide et prix abordables.', 0, '+213541937190', 'N/A'),
('Happy Burger', 'Fast food', 'Centre-ville, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8485,5.7275', '$', 5.0, 'Fast-food proposant burgers gourmets et sandwiches. Ingrédients frais et recettes originales.', 0, 'N/A', 'N/A'),
('Gama Food', 'Fast food', 'Centre-ville, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8488,5.7278', '$', 4.6, 'Fast-food proposant une variété de plats rapides. Service efficace et cadre moderne.', 0, '+213770257660', 'N/A'),
('Living Pool', 'Café-Restaurant', 'Centre-ville, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8482,5.7272', '$$', 4.0, 'Café-restaurant moderne proposant repas et boissons. Ambiance détendue, idéal pour se retrouver entre amis.', 0, '+213773732119', 'N/A'),
('Restaurant El Baraka', 'Restaurant', 'Centre-ville, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8486,5.7274', '$', 3.8, 'Restaurant traditionnel proposant couscous, chorba et grillades. Cuisine familiale à prix doux.', 0, 'N/A', 'N/A'),
('Pizzeria Biskra', 'Pizzeria', 'Centre-ville, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8483,5.7277', '$', 3.5, 'Pizzeria de quartier proposant pizzas, sandwiches et boissons. Cuisson au feu de bois.', 0, 'N/A', 'N/A'),
('Restaurant Sidi Yahia', 'Restaurant', 'Sidi Yahia, Biskra 07000, Algérie', 7000, 'Biskra', 'N/A', '34.8100,5.7350', '$$', 3.9, 'Restaurant traditionnel près du lieu saint de Sidi Yahia. Cuisine du sud algérien, spécialités locales et dattes.', 0, 'N/A', 'N/A');

-- =====================================================
-- TIZI OUZOU (Wilaya 15) - Code postal 15000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('L\'Ambassade', 'Restaurant', 'Centre-ville, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7169,4.0456', '$$$', 4.7, 'Restaurant méditerranéen classé numéro 1 à Tizi Ouzou. Cuisine raffinée, service impeccable. 24 avis élogieux sur TripAdvisor.', 0, 'N/A', 'N/A'),
('Le Bagdad', 'Restaurant de fruits de mer', 'Centre-ville, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7165,4.0460', '$$', 4.5, 'Restaurant de fruits de mer réputé à Tizi Ouzou. Poisson frais, crevettes et calamars. 15 avis positifs.', 0, 'N/A', 'N/A'),
('Va Bene', 'Fast food', 'Centre-ville, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7172,4.0452', '$', 4.3, 'Fast-food et street food très populaire à Tizi Ouzou avec 40 avis. Burgers, sandwiches et plats rapides de qualité.', 0, 'N/A', 'N/A'),
('Le Mystic', 'Restaurant', 'Centre-ville, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7170,4.0455', '$$', 4.3, 'Restaurant healthy et algérien. Plats équilibrés et cuisine traditionnelle revisitée. Cadre moderne.', 0, 'N/A', 'N/A'),
('Restaurant Le Romain', 'Restaurant', 'Centre-ville, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7168,4.0458', '$$', 4.0, 'Restaurant proposant des saveurs exquises où tradition locale et excellence culinaire se rencontrent.', 0, 'N/A', 'N/A'),
('Restaurant Pikanya', 'Restaurant', 'Centre-ville, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7166,4.0462', '$$', 4.7, 'Restaurant très bien noté offrant cuisine algérienne et méditerranéenne. Ambiance chaleureuse et plats savoureux.', 0, 'N/A', 'N/A'),
('El Mamounia', 'Restaurant', 'Centre-ville, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7174,4.0450', '$$', 3.8, 'Restaurant proposant cuisine orientale et algérienne. Couscous, tajines et grillades dans un cadre traditionnel.', 0, 'N/A', 'N/A'),
('Madiba Restaurant', 'Restaurant', 'Boulevard Khaled Khoudja, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7175,4.0448', '$$', 4.0, 'Restaurant sur le boulevard principal de Tizi Ouzou. Cuisine variée, grillades et plats traditionnels kabyles.', 0, '+213540743890', 'N/A'),
('Restaurant Cafétéria Taksebt', 'Restaurant', 'Barrage Taksebt, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.6400,4.0800', '$$', 5.0, 'Restaurant algérien avec vue magnifique sur le barrage Taksebt. Barbecue en plein air et cadre naturel exceptionnel.', 0, 'N/A', 'N/A'),
('Restaurant Le Majestic', 'Restaurant', 'Azazga, Tizi Ouzou 15000, Algérie', 15000, 'Tizi Ouzou', 'N/A', '36.7450,4.3700', '$$$', 5.0, 'Restaurant français et américain à Azazga. Cuisine gastronomique dans un cadre majestueux. Service haut de gamme.', 0, 'N/A', 'N/A');

-- =====================================================
-- GHARDAIA (Wilaya 47) - Code postal 47000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Khaima Hamma', 'Restaurant', 'Centre-ville, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4903,3.6733', '$$', 5.0, 'Restaurant traditionnel sous la tente (khaïma). Classé numéro 2 à Ghardaïa. Cuisine mozabite authentique, couscous et spécialités locales.', 0, 'N/A', 'N/A'),
('Restaurant Djurdjura', 'Restaurant', 'Sidi Abaz N1, Bounoura, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.5100,3.6550', '$$', 4.0, 'Restaurant spécialisé dans les plats locaux mozabites. Repas sur place uniquement. Cuisine authentique du M\'zab.', 0, 'N/A', 'N/A'),
('M\'zab Restaurant', 'Restaurant', 'Centre-ville, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4905,3.6735', '$$', 3.5, 'Restaurant proposant cuisine algérienne et spécialités du M\'zab. Cadre simple et repas traditionnels.', 0, 'N/A', 'N/A'),
('Al Nawras', 'Pâtisserie', 'Centre-ville, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4900,3.6730', '$', 5.0, 'Salon de thé et pâtisserie traditionnelle sous la tente. Sucreries, gâteaux mozabites et thé à la menthe.', 0, 'N/A', 'N/A'),
('Eldjawhara Hotel Restaurant', 'Restaurant', 'Hôtel Eldjawhara, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4910,3.6740', '$$$', 3.8, 'Restaurant d\'hôtel proposant cuisine algérienne et internationale. Cadre confortable, service soigné.', 0, 'N/A', 'N/A'),
('L\'Escale Street Food', 'Fast food', 'Centre-ville, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4902,3.6732', '$', 3.5, 'Fast-food et pizzeria ouvrant vers 17h30. Pizzas et sandwiches à emporter ou sur place.', 0, 'N/A', 'N/A'),
('Road Restaurant', 'Restaurant', 'Route principale, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4908,3.6738', '$', 3.3, 'Restaurant routier proposant repas sur place, plats à emporter et livraison. Ouvert le soir à partir de 18h.', 0, 'N/A', 'N/A'),
('MC Daddy', 'Fast food', 'Centre-ville, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4898,3.6728', '$', 3.5, 'Fast-food chaleureux proposant burgers, sandwiches et frites. Ouvert jusqu\'à 22h30. Ambiance jeune.', 0, 'N/A', 'N/A'),
('Marina Burger', 'Fast food', 'Centre-ville, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4895,3.6725', '$', 3.6, 'Fast-food spécialisé dans les burgers. Ouvert à partir de 18h. Burgers gourmets et frites maison.', 0, 'N/A', 'N/A'),
('Restaurant El Waha', 'Restaurant', 'Centre-ville, Ghardaïa 47000, Algérie', 47000, 'Ghardaïa', 'N/A', '32.4906,3.6736', '$$', 3.7, 'Restaurant oasis proposant cuisine traditionnelle du sud. Couscous aux légumes du désert et spécialités sahariennes.', 0, 'N/A', 'N/A');

-- =====================================================
-- DJELFA (Wilaya 17) - Code postal 17000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('El Khaima', 'Restaurant', 'Boulevard Elmoudjahidin, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6704,3.2504', '$$', 4.0, 'Restaurant de haut niveau face au lycée. Cuisine algérienne traditionnelle, spécialités des Hauts Plateaux. Agneau et couscous.', 0, 'N/A', 'N/A'),
('Pizzéria Mahdi', 'Pizzeria', 'Boulevard du 1er Novembre, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6710,3.2500', '$', 3.8, 'Pizzeria populaire sur le boulevard principal de Djelfa. Pizzas variées et sandwiches à prix attractifs.', 0, '0550034897', 'N/A'),
('Pizzeria Milano', 'Pizzeria', 'Rue piétonne, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6708,3.2502', '$', 3.5, 'Pizzeria située sur la rue piétonne de Djelfa. Pizzas italiennes et plats rapides. Ambiance décontractée.', 0, 'N/A', 'N/A'),
('Restaurant Fares', 'Restaurant', 'Boulevard Sidi Nail, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6700,3.2510', '$$', 3.7, 'Restaurant sur le boulevard Sidi Nail. Cuisine algérienne traditionnelle, grillades d\'agneau et méchoui.', 0, 'N/A', 'N/A'),
('Restaurant Salah Eddine', 'Restaurant', 'Boulevard Sidi Nail, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6702,3.2508', '$$', 3.8, 'Restaurant oriental proposant grillades, brochettes et plats traditionnels. Service convivial.', 0, 'N/A', 'N/A'),
('Chez Bahi', 'Restaurant', 'Boulevard du 1er Novembre, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6706,3.2506', '$', 3.5, 'Restaurant simple et populaire sur le boulevard principal. Plats du jour, soupes et grillades à petit prix.', 0, 'N/A', 'N/A'),
('Lookers Restaurant', 'Restaurant', 'Avenue Emir Abdelkader 205, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6698,3.2512', '$$', 3.6, 'Restaurant proposant cuisine variée. Cadre moderne et service agréable sur l\'avenue principale.', 0, 'N/A', 'N/A'),
('Cherif & Djelloul', 'Fast food', 'Rue Myriam Makéba, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6712,3.2498', '$', 3.5, 'Cafétéria et fast-food ouvert de 6h à 20h. Petit-déjeuner, sandwiches et boissons chaudes.', 0, '0782923494', 'N/A'),
('Restaurant El Khalil', 'Restaurant', 'Centre-ville, Djelfa 17000, Algérie', 17000, 'Djelfa', 'N/A', '34.6705,3.2505', '$$', 3.6, 'Restaurant proposant cuisine traditionnelle. Ouvert de 11h à 22h. Plats copieux et prix raisonnables.', 0, 'N/A', 'N/A'),
('Pizza Chez Toufik', 'Pizzeria', 'Rouini, Djelfa 17001, Algérie', 17001, 'Djelfa', 'N/A', '34.6715,3.2495', '$', 3.4, 'Pizzeria de quartier ouverte de 10h à 22h. Pizzas et garnitures variées. Service de livraison.', 0, 'N/A', 'N/A');

-- =====================================================
-- MOSTAGANEM (Wilaya 27) - Code postal 27000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Pecherie L\'Aquarium', 'Restaurant de fruits de mer', 'Front De Mer, Salamandre, Mostaganem, Algérie', 27000, 'Mostaganem', 'N/A', '35.9314,-0.0891', '$$$', 4.8, 'Restaurant de fruits de mer en front de mer à Salamandre. Soupe de poisson, poisson grillé et viandes. 64 avis élogieux.', 0, '+213780144736', 'N/A'),
('Le Chez Nous', 'Restaurant de fruits de mer', 'Centre-ville, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9310,-0.0895', '$$', 3.5, 'Restaurant de poisson et soupe de mer. Cuisine traditionnelle maritime mostaganémoise. Ouvert midi et soir.', 0, '+21345398182', 'N/A'),
('Pizzeria Quick Mostaganem', 'Pizzeria', 'Rue Tahlaiti Othmane, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9305,-0.0900', '$', 3.5, 'Pizzeria populaire proposant pizzas variées à prix doux. Service rapide et livraison disponible.', 0, '+213555949440', 'N/A'),
('Paloma Nadir', 'Pizzeria', 'Centre-ville, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9308,-0.0898', '$', 4.7, 'Pizzeria très bien notée avec 9 avis positifs. Pizzas artisanales servies de 11h à 23h tous les jours.', 0, '+213776801628', 'N/A'),
('Pizzeria Le Château', 'Pizzeria', 'Centre-ville, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9312,-0.0893', '$', 3.8, 'Pizzeria et restaurant de poulet grillé. Pizzas variées et plats rapides dans un cadre agréable.', 0, '+213555825847', 'N/A'),
('Lavita', 'Pizzeria', 'Rue Lahoual Cheref, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9306,-0.0896', '$', 3.6, 'Pizzeria proposant pizzas et plats italiens. Ouvert tous les jours de 10h à 23h.', 0, '+213558061614', 'N/A'),
('Bab El Marsa Chez Rachid', 'Restaurant', 'Front de mer, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9316,-0.0889', '$$', 3.8, 'Restaurant de soupe et viandes en bord de mer. Cuisine traditionnelle et grillades. Ouvert midi et soir jusqu\'à minuit.', 0, '+213778307755', 'N/A'),
('Grillades Jijli', 'Barbecue restaurant', 'Centre-ville, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9302,-0.0902', '$', 3.3, 'Restaurant de grillades et poulet rôti style Jijel. Viandes grillées et brochettes. Ouvert jusqu\'à minuit.', 0, 'N/A', 'N/A'),
('Quiky Fastfood', 'Fast food', 'Avenue Ould Aissa Belkacem, Mostaganem 27000, Algérie', 27000, 'Mostaganem', 'N/A', '35.9300,-0.0905', '$', 3.8, 'Fast-food proposant sandwiches variés. Ouvert du samedi au jeudi de 9h à 22h. Service rapide.', 0, 'N/A', 'N/A'),
('Restaurant Algérie', 'Restaurant', 'Rue Djaloul Makhlouf, Mostaganem 27012, Algérie', 27012, 'Mostaganem', 'N/A', '35.9318,-0.0887', '$$', 3.9, 'Restaurant traditionnel algérien à Mostaganem. Cuisine familiale, couscous et plats du jour.', 0, '+213554372537', 'N/A');

-- =====================================================
-- JIJEL (Wilaya 18) - Code postal 18000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Restaurant A La Cabane', 'Barbecue restaurant', 'Rue Zaimen Athmane, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8209,5.7663', '$$', 5.0, 'Classé numéro 1 à Jijel. Restaurant barbecue avec excellente cuisine et rapport qualité-prix parfait. Incontournable.', 0, 'N/A', 'N/A'),
('Café Hafid', 'Café-Restaurant', '50 Rue Colonel Lotfi, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8205,5.7660', '$', 4.5, 'Café-restaurant au centre de Jijel. Très bon café, pizzas et accueil chaleureux. Prix abordables et emplacement central.', 0, 'N/A', 'N/A'),
('Atlanta Food', 'Fast food', 'Rue Abdelhamid Ben Badis, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8200,5.7665', '$', 3.8, 'Fast-food proposant burgers, sandwiches et plats rapides. Service rapide et cadre moderne.', 0, 'N/A', 'N/A'),
('Le Lavandou', 'Restaurant', 'Corniche, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8220,5.7670', '$$', 4.0, 'Restaurant sur la corniche de Jijel avec vue sur mer. Poissons frais, grillades et cuisine méditerranéenne.', 0, 'N/A', 'N/A'),
('Restaurant TOP CHEF', 'Restaurant', 'Centre-ville, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8210,5.7658', '$$', 3.9, 'Restaurant proposant cuisine algérienne variée. Plats du jour, grillades et couscous dans un cadre soigné.', 0, 'N/A', 'N/A'),
('Pizzeria L\'Escale', 'Pizzeria', 'Centre-ville, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8208,5.7662', '$', 3.7, 'Pizzeria populaire au centre de Jijel. Pizzas variées, cuisson au feu de bois et livraison disponible.', 0, 'N/A', 'N/A'),
('Glacier Les Platanes', 'Café-Restaurant', 'Centre-ville, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8212,5.7655', '$', 4.2, 'Glacier et café proposant glaces artisanales, crêpes et boissons fraîches. Terrasse ombragée sous les platanes.', 0, 'N/A', 'N/A'),
('Magie Glaces', 'Café-Restaurant', 'Centre-ville, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8215,5.7652', '$', 4.0, 'Salon de glaces et pâtisseries. Glaces variées, gaufres et crêpes. Cadre familial et ambiance estivale.', 0, 'N/A', 'N/A'),
('Empire Of Donuts Jijel', 'Fast food', 'Centre-ville, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8202,5.7668', '$', 3.8, 'Spécialiste du donut et des pâtisseries américaines. Donuts gourmets, milkshakes et boissons.', 0, 'N/A', 'N/A'),
('Restaurant La Corniche', 'Restaurant de fruits de mer', 'Corniche Jijelienne, Jijel 18000, Algérie', 18000, 'Jijel', 'N/A', '36.8225,5.7675', '$$', 4.1, 'Restaurant de fruits de mer sur la corniche. Poisson frais du jour, crevettes et calamars avec vue panoramique sur la mer.', 0, 'N/A', 'N/A');

-- =====================================================
-- TIPAZA (Wilaya 42) - Code postal 42000
-- =====================================================
INSERT INTO `vendeur` (`Nom`, `Type`, `adresse`, `codePostal`, `ville`, `owner`, `gps`, `pricerange`, `note`, `descriptif`, `mea`, `phone`, `web`) VALUES
('Fontaine d\'Or', 'Restaurant de fruits de mer', 'Entrée Est de Tipaza, près du Parc de Loisirs, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5882,2.4470', '$$$', 4.4, 'Classé numéro 1 à Tipaza. Restaurant de poisson face à la mer avec grand jardin. Soupe de poisson, calamar farci, espadon grillé. Entrées offertes.', 0, 'N/A', 'N/A'),
('Le Dauphin - Chez Sid Ali', 'Restaurant de fruits de mer', 'Rue du Port, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5900,2.4485', '$$', 3.9, 'Restaurant de fruits de mer historique au port de Tipaza. Poisson grillé, homard et plateaux de fruits de mer. Institution locale.', 0, 'N/A', 'N/A'),
('Restaurant Les Oursins', 'Restaurant de fruits de mer', 'Ain Tagourait, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5750,2.4200', '$$', 4.2, 'Restaurant méditerranéen spécialisé dans les fruits de mer. Oursins frais, poisson grillé et spécialités de la mer. Prix moyen 2000 DA.', 0, 'N/A', 'N/A'),
('Albert Camus', 'Restaurant', 'Centre-ville, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5890,2.4475', '$$$', 4.0, 'Restaurant gastronomique de Tipaza. Cuisine raffinée dans un cadre élégant. Prix moyen 4000 DA.', 0, 'N/A', 'N/A'),
('Restaurant Liberté', 'Restaurant de fruits de mer', 'Douaouda Marine, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.6800,2.7900', '$$', 3.8, 'Restaurant de poisson à Douaouda Marine. Poisson frais du jour, grillades et cuisine de la mer. Prix moyen 1800 DA.', 0, 'N/A', 'N/A'),
('La Brise Marine', 'Restaurant de fruits de mer', 'Corniche, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5895,2.4480', '$$$', 4.3, 'Institution combinant cuisine raffinée et vue panoramique sur la Méditerranée. Calamars frits, plateaux de fruits de mer et poisson grillé.', 0, 'N/A', 'N/A'),
('L\'Oasis des Pêcheurs', 'Restaurant de fruits de mer', 'Port de Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5905,2.4490', '$$', 4.0, 'Restaurant familial misant sur l\'authenticité. Sardines grillées, filets de rouget et soupe de poisson traditionnelle. Prix accessibles.', 0, 'N/A', 'N/A'),
('Le Pêcheur Gourmet', 'Restaurant de fruits de mer', 'Centre-ville, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5888,2.4472', '$$$', 4.5, 'Restaurant gastronomique avec cuisine de la mer créative. Coquilles Saint-Jacques, crevettes flambées au cognac. Idéal pour les occasions spéciales.', 0, 'N/A', 'N/A'),
('Cafétéria Le Petit Pêcheur', 'Café-Restaurant', 'Port, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5902,2.4488', '$', 3.5, 'Petit café-restaurant au port de Tipaza. Sandwiches au poisson, café et boissons fraîches. Vue sur les bateaux.', 0, 'N/A', 'N/A'),
('La Baie des Sirènes', 'Restaurant de fruits de mer', 'Corniche, Tipaza, Algérie', 42000, 'Tipaza', 'N/A', '36.5892,2.4478', '$$', 4.1, 'Restaurant méditerranéen réputé pour le loup de mer rôti et les moules marinières. Recettes authentiques et portions généreuses.', 0, 'N/A', 'N/A');

-- =====================================================
-- Summary: Total ~152 restaurants across 14 cities
-- Constantine: 15 | Annaba: 12 | Sétif: 10 | Béjaïa: 10
-- Tlemcen: 11 | Batna: 12 | Blida: 10 | Biskra: 10
-- Tizi Ouzou: 10 | Ghardaïa: 10 | Djelfa: 10
-- Mostaganem: 10 | Jijel: 10 | Tipaza: 10
-- =====================================================
