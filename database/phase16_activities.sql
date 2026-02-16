-- ═══════════════════════════════════════════════════════════════
-- PHASE 16 - VERTICALE ACTIVITES & SORTIES
-- Tables + Seed data (5 grandes villes d'Algerie)
-- ═══════════════════════════════════════════════════════════════

-- Table principale des activites/lieux
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('plage','parc','monument','musee','shopping','divertissement','nightlife','cafe','nature','sport','religieux','culturel') NOT NULL,
    ville VARCHAR(100) NOT NULL,
    wilaya VARCHAR(100),
    adresse VARCHAR(500),
    gps_latitude DECIMAL(10,7),
    gps_longitude DECIMAL(10,7),
    phone VARCHAR(20),
    website VARCHAR(500),
    price_range ENUM('gratuit','pas_cher','moyen','cher') DEFAULT 'gratuit',
    duration_avg VARCHAR(50) COMMENT 'Duree moyenne de visite (ex: 1h, 2-3h, demi-journee)',
    horaires_info VARCHAR(500) COMMENT 'Info horaires texte libre',
    photo_url VARCHAR(500),
    note_moyenne DECIMAL(3,2) DEFAULT 0,
    nb_avis INT DEFAULT 0,
    nb_photos INT DEFAULT 0,
    status ENUM('active','pending','inactive') DEFAULT 'active',
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_slug (slug),
    INDEX idx_ville (ville),
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_note (note_moyenne DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Avis sur les activites
CREATE TABLE IF NOT EXISTS activity_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    user_id INT NOT NULL,
    note_globale TINYINT NOT NULL CHECK (note_globale BETWEEN 1 AND 5),
    message TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    edit_count TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity (activity_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Photos des activites
CREATE TABLE IF NOT EXISTS activity_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    user_id INT,
    path VARCHAR(500) NOT NULL,
    type ENUM('main','gallery','user') DEFAULT 'gallery',
    caption VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    INDEX idx_activity (activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tips rapides sur les activites
CREATE TABLE IF NOT EXISTS activity_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_id INT NOT NULL,
    user_id INT NOT NULL,
    message VARCHAR(200) NOT NULL,
    votes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_activity (activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wishlist activites (reutilise le meme pattern)
CREATE TABLE IF NOT EXISTS activity_wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_unique (user_id, activity_id),
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Check-ins activites
CREATE TABLE IF NOT EXISTS activity_checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_id INT NOT NULL,
    user_lat DECIMAL(10,7),
    user_lng DECIMAL(10,7),
    distance_m INT,
    points_earned INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_activity (activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Collections peuvent deja contenir des activites via cette table de liaison
CREATE TABLE IF NOT EXISTS collection_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    activity_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_unique (collection_id, activity_id),
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ═══════════════════════════════════════════════════════════════
-- SEED DATA - ALGER (38 lieux)
-- ═══════════════════════════════════════════════════════════════

INSERT INTO activities (nom, slug, description, category, ville, wilaya, gps_latitude, gps_longitude, price_range, duration_avg, photo_url) VALUES

-- Monuments & Histoire
('Casbah d\'Alger', 'casbah-alger', 'Ancienne medina classee UNESCO. Labyrinthe de ruelles etroites avec palais ottomans, mosquees, souks et hammams. Habitee par ~50 000 residents.', 'monument', 'Alger', 'Alger', 36.7853, 3.0603, 'gratuit', '2-3h', 'https://images.unsplash.com/photo-1583425423320-c21b0654edff?w=600&h=400&fit=crop'),
('Maqam Echahid', 'maqam-echahid', 'Monument iconique de 96m en beton avec 3 palmes stylisees, inaugure en 1982. Flamme eternelle, crypte, amphitheatre. Visible a 35km.', 'monument', 'Alger', 'Alger', 36.7472, 3.0708, 'gratuit', '1-2h', 'https://images.unsplash.com/photo-1568454537842-d933259bb258?w=600&h=400&fit=crop'),
('Palais des Rais (Bastion 23)', 'palais-des-rais-bastion-23', 'Construit en 1576 dans la basse Casbah. 3 palais ottomans et 6 maisons de pecheurs. Centre culturel et musee.', 'monument', 'Alger', 'Alger', 36.7892, 3.0560, 'pas_cher', '1h', NULL),
('Mosquee Ketchaoua', 'mosquee-ketchaoua', 'Au pied de la Casbah, construite en 1612. Colonnes de marbre noir veine et platre mauresque. Convertie en cathedrale puis restauree.', 'religieux', 'Alger', 'Alger', 36.7867, 3.0610, 'gratuit', '30min', NULL),
('Djamaa el Djazair', 'djamaa-el-djazair-grande-mosquee', '3eme plus grande mosquee du monde (2019). Minaret le plus haut : 265m. Capacite 120 000. Plateforme d\'observation, bibliotheque, musee d\'art islamique.', 'religieux', 'Alger', 'Alger', 36.7277, 3.1023, 'gratuit', '1-2h', NULL),
('Basilique Notre-Dame d\'Afrique', 'basilique-notre-dame-afrique', 'Basilique neo-byzantine (1872) sur une colline surplombant la Baie d\'Alger. Vue panoramique sur la ville et la Mediterranee.', 'monument', 'Alger', 'Alger', 36.7963, 3.0431, 'gratuit', '1h', NULL),
('La Grande Poste', 'grande-poste-alger', 'Batiment neo-mauresque emblematique sur la Place du 1er Mai. Mosaiques, vitraux et sculptures. L\'un des plus photographies d\'Alger.', 'monument', 'Alger', 'Alger', 36.7731, 3.0590, 'gratuit', '30min', NULL),

-- Musees
('Musee National des Beaux-Arts', 'musee-beaux-arts-alger', 'Pres du Jardin d\'Essai. L\'un des plus grands musees d\'art d\'Afrique avec plus de 8000 oeuvres : peintures, sculptures, arts decoratifs.', 'musee', 'Alger', 'Alger', 36.7475, 3.0761, 'pas_cher', '2h', NULL),
('Musee National du Bardo', 'musee-bardo-alger', 'Artefacts antiques, mosaiques et tresors historiques couvrant les periodes algerienne, romaine, byzantine et ottomane.', 'musee', 'Alger', 'Alger', 36.7681, 3.0507, 'pas_cher', '2h', NULL),
('Musee des Antiquites et d\'Art Islamique', 'musee-antiquites-art-islamique', 'Sculptures romaines, mosaiques, bronzes + manuscrits islamiques, tapis et ceramiques. De l\'Antiquite a l\'ere islamique.', 'musee', 'Alger', 'Alger', 36.7660, 3.0524, 'pas_cher', '1-2h', NULL),
('Musee National du Moudjahid', 'musee-moudjahid-alger', 'Sous le Maqam Echahid. Dedie a la Guerre d\'independance (1954-1962), documentant la lutte contre le colonialisme.', 'musee', 'Alger', 'Alger', 36.7469, 3.0703, 'gratuit', '1h', NULL),

-- Parcs & Jardins
('Jardin d\'Essai du Hamma', 'jardin-essai-hamma', 'Cree en 1832, 32 hectares, pres de 1200 especes vegetales du monde entier. L\'un des plus importants jardins botaniques de Mediterranee.', 'parc', 'Alger', 'Alger', 36.7470, 3.0740, 'pas_cher', '2-3h', 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=600&h=400&fit=crop'),
('Parc de Ben Aknoun', 'parc-ben-aknoun', 'Complexe de 304 hectares (1982). Zoo (elephants, autruches, singes), parc d\'attractions, 200ha de foret, restaurants.', 'parc', 'Alger', 'Alger', 36.7602, 3.0103, 'pas_cher', 'demi-journee', NULL),
('Dounia Parc', 'dounia-parc-alger', 'Grand parc urbain avec lac, chemins de marche et velo, espaces verts. Jogging, relaxation, observation d\'oiseaux.', 'parc', 'Alger', 'Alger', 36.7200, 3.0800, 'gratuit', '1-2h', NULL),

-- Plages
('Plage de Sidi Fredj', 'plage-sidi-fredj', 'Station balneaire historique a 30min ouest d\'Alger sur une peninsule. Eaux calmes et claires, windsurf, port historique.', 'plage', 'Alger', 'Alger', 36.7714, 2.8483, 'gratuit', 'demi-journee', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&h=400&fit=crop'),
('Palm Beach', 'palm-beach-alger', 'A 1h ouest du centre. Eaux mediterraneennes, hotels haut de gamme. Propre, bien entretenue, securisee pour la baignade.', 'plage', 'Alger', 'Alger', 36.7590, 2.7650, 'gratuit', 'demi-journee', NULL),
('Azur Plage', 'azur-plage-alger', 'Entre Sable d\'Or et Palm Beach sur la cote ouest. Particulierement populaire aupres des familles.', 'plage', 'Alger', 'Alger', 36.7560, 2.7800, 'gratuit', 'demi-journee', NULL),

-- Divertissement
('Aquafortland', 'aquafortland-alger', 'Parc aquatique populaire avec toboggans, piscines a vagues et aires de jeux pour enfants.', 'divertissement', 'Alger', 'Alger', 36.7183, 3.1793, 'moyen', 'demi-journee', NULL),
('Sabi Parc', 'sabi-parc-hussein-dey', 'Parc d\'attractions a Hussein Dey. Variete de maneges, aires de jeux interieures, zones de pique-nique vertes.', 'divertissement', 'Alger', 'Alger', 36.7400, 3.1000, 'moyen', '2-3h', NULL),
('Teri Park', 'teri-park-alger', 'Grande roue, autos tamponneuses, carrousel, aires de jeux. Ambiance familiale en plein centre.', 'divertissement', 'Alger', 'Alger', 36.7600, 3.0500, 'pas_cher', '2h', NULL),

-- Promenades
('Promenade des Sablettes', 'promenade-sablettes', 'Inauguree en 2014, le long de la Mediterranee. Cafes, restaurants, boutiques, jardins et espaces recreatifs.', 'parc', 'Alger', 'Alger', 36.7426, 3.0844, 'gratuit', '1-2h', NULL),
('Riadh El Feth', 'riadh-el-feth', 'Complexe culturel et commercial sous le Maqam Echahid. Shopping, restauration, evenements culturels et concerts.', 'shopping', 'Alger', 'Alger', 36.7466, 3.0700, 'gratuit', '2h', NULL),

-- Shopping
('Centre Commercial Bab Ezzouar', 'centre-commercial-bab-ezzouar', 'Plus grand centre commercial d\'Alger. Supermarche, marques internationales, restaurants et divertissements.', 'shopping', 'Alger', 'Alger', 36.7237, 3.1827, 'gratuit', '2-3h', NULL),
('Park Mall Alger', 'park-mall-alger', 'Centre commercial style europeen avec patinoire, maneges au dernier etage et aires de jeux pour enfants.', 'shopping', 'Alger', 'Alger', 36.7150, 3.1700, 'gratuit', '2-3h', NULL),

-- Nightlife
('L\'Amiral Night Club', 'amiral-night-club', 'A Mohammadia. Musique, danse et divertissement. L\'un des principaux spots nocturnes d\'Alger.', 'nightlife', 'Alger', 'Alger', 36.7350, 3.1100, 'moyen', '3-4h', NULL),
('Cavalli Club Sofitel', 'cavalli-club-sofitel', 'Lieu de vie nocturne luxueux avec restauration raffinee et divertissement a l\'hotel Sofitel.', 'nightlife', 'Alger', 'Alger', 36.7480, 3.0680, 'cher', '3-4h', NULL);


-- ═══════════════════════════════════════════════════════════════
-- SEED DATA - ORAN (30 lieux)
-- ═══════════════════════════════════════════════════════════════

INSERT INTO activities (nom, slug, description, category, ville, wilaya, gps_latitude, gps_longitude, price_range, duration_avg, photo_url) VALUES

-- Monuments & Histoire
('Fort de Santa Cruz', 'fort-santa-cruz-oran', 'Forteresse espagnole (1577-1604) perchee a 400m sur le Mont Murdjadjo. Vue panoramique sur la ville, la Mediterranee et le port de Mers-el-Kebir.', 'monument', 'Oran', 'Oran', 35.7125, -0.6428, 'pas_cher', '1-2h', 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=600&h=400&fit=crop'),
('Chapelle de Santa Cruz', 'chapelle-santa-cruz', 'Chapelle de 1849 au pied du fort, avec statue de la Vierge Marie. Erigee apres l\'epidemie de cholera de 1847.', 'monument', 'Oran', 'Oran', 35.7120, -0.6430, 'gratuit', '30min', NULL),
('Palais du Bey', 'palais-du-bey-oran', 'Palais ottoman (1792). Diwan (conseil), residence et pavillon de la favorite. Architecture arabo-turque.', 'monument', 'Oran', 'Oran', 35.6967, -0.6367, 'pas_cher', '1h', NULL),
('Mosquee du Pacha', 'mosquee-du-pacha-oran', 'Construite en 1797 par Sidi Hassan Pacha. Minaret octogonal decore, mosaiques interieures magnifiques.', 'religieux', 'Oran', 'Oran', 35.6970, -0.6380, 'gratuit', '30min', NULL),
('Cathedrale du Sacre-Coeur', 'cathedrale-sacre-coeur-oran', 'Construite en 1913 par les Francais. Aujourd\'hui bibliotheque publique. Entree gratuite.', 'monument', 'Oran', 'Oran', 35.6979, -0.6322, 'gratuit', '30min', NULL),
('Place du 1er Novembre', 'place-1er-novembre-oran', 'Place principale d\'Oran : Hotel de Ville neoclassique, statues de la Victoire Ailee, Opera baroque, lions emblematiques.', 'monument', 'Oran', 'Oran', 35.6969, -0.6340, 'gratuit', '30min', NULL),

-- Musees
('Musee National Ahmed Zabana', 'musee-zabana-oran', 'Premier musee d\'Oran. Lutte pour l\'independance au RDC, archeologie et art a l\'etage. Gratuit, ouvert 8h30-17h sauf samedi.', 'musee', 'Oran', 'Oran', 35.6965, -0.6320, 'gratuit', '1-2h', NULL),
('Musee d\'Art Moderne d\'Oran (MAMO)', 'mamo-oran', 'Ouvert en 2017. Plus de 6000m2 sur 4 etages. Art contemporain algerien et international.', 'musee', 'Oran', 'Oran', 35.6975, -0.6310, 'pas_cher', '1-2h', NULL),

-- Parcs
('Promenade de Letang', 'promenade-letang-oran', 'Jardin mediterraneen avec allees de ficus, coniferes et palmiers. Vue sur la Mediterranee. Havre de paix en centre-ville.', 'parc', 'Oran', 'Oran', 35.6950, -0.6370, 'gratuit', '1h', NULL),
('Boulevard de l\'ALN (La Corniche)', 'corniche-oran', 'Boulevard cotier renove de 2km. Palmiers, cafes et restaurants surplombant le port. Anime le soir.', 'parc', 'Oran', 'Oran', 35.6960, -0.6400, 'gratuit', '1h', NULL),

-- Plages
('Plage des Andalouses', 'plage-andalouses-oran', 'Grande plage de sable a l\'ouest d\'Oran. Baignade et surf. Restaurants et cafes en bordure. Vues cotieres.', 'plage', 'Oran', 'Oran', 35.7360, -0.7560, 'gratuit', 'demi-journee', 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?w=600&h=400&fit=crop'),
('Plage de Madagh', 'plage-madagh-oran', 'Plage familiale entouree de forets et montagnes. Sable fin, eau peu profonde. Location d\'equipement de plongee.', 'plage', 'Oran', 'Oran', 35.5200, -1.1700, 'gratuit', 'demi-journee', NULL),
('Plage de Bousfer', 'plage-bousfer-oran', 'Plage populaire dans la zone d\'Ain el-Turck. Eau claire, ambiance detendue.', 'plage', 'Oran', 'Oran', 35.7280, -0.7900, 'gratuit', 'demi-journee', NULL),
('Plage d\'Ain el-Turck', 'plage-ain-el-turck', 'Station balneaire a 15km d\'Oran. Restaurants, resorts et vie nocturne a proximite.', 'plage', 'Oran', 'Oran', 35.7340, -0.7680, 'gratuit', 'demi-journee', NULL),

-- Shopping
('Marche Medina Jdida', 'medina-jdida-oran', 'Souk traditionnel bouillonnant. Vetements, textiles, bijoux, epices, fleurs, souvenirs. Couleurs et senteurs de l\'aube au coucher du soleil.', 'shopping', 'Oran', 'Oran', 35.6950, -0.6290, 'gratuit', '1-2h', NULL),
('Centre Commercial Es-Senia', 'centre-commercial-es-senia', 'Mall moderne de 34 000m2 (2019). 120 enseignes, food court, cinema multiplex Cinegold, hypermarche.', 'shopping', 'Oran', 'Oran', 35.6440, -0.6150, 'gratuit', '2-3h', NULL),

-- Divertissement
('Zoo d\'Oran', 'zoo-oran', 'Fonde en 1983, 17 hectares. Lions, singes, oiseaux, reptiles. Aire de jeux, pique-nique ombrage. Familial.', 'divertissement', 'Oran', 'Oran', 35.6800, -0.6200, 'pas_cher', '2-3h', NULL),
('Dream Paradise Park', 'dream-paradise-park-oran', 'Parc d\'attractions : 25+ maneges, mini-zoo, espaces verts, kiosques, restauration. Attraction familiale majeure.', 'divertissement', 'Oran', 'Oran', 35.6700, -0.6300, 'moyen', '3-4h', NULL),

-- Nightlife
('Havana Club Oran', 'havana-club-oran', 'Lieu de vie nocturne anime avec musique, danse et boissons. L\'un des clubs les plus connus d\'Oran.', 'nightlife', 'Oran', 'Oran', 35.6980, -0.6330, 'moyen', '3-4h', NULL),
('Sky Lounge by Liberte', 'sky-lounge-oran', 'Rooftop lounge avec vue imprenable sur la ville. Vie nocturne vibrante.', 'nightlife', 'Oran', 'Oran', 35.6950, -0.6300, 'cher', '3-4h', NULL),

-- Culturel
('Theatre Regional d\'Oran', 'theatre-regional-oran', 'Theatre baroque sur la Place du 1er Novembre. Spectacles et evenements culturels. L\'un des plus beaux batiments d\'Oran.', 'culturel', 'Oran', 'Oran', 35.6968, -0.6338, 'moyen', '2h', NULL);


-- ═══════════════════════════════════════════════════════════════
-- SEED DATA - CONSTANTINE (30 lieux)
-- ═══════════════════════════════════════════════════════════════

INSERT INTO activities (nom, slug, description, category, ville, wilaya, gps_latitude, gps_longitude, price_range, duration_avg, photo_url) VALUES

-- Ponts
('Pont Sidi Rached', 'pont-sidi-rached', 'Monument le plus iconique de Constantine. Plus long pont en arc de maconnerie au monde : 447m de long, 105m de haut. Annees 1920.', 'monument', 'Constantine', 'Constantine', 36.3580, 6.6131, 'gratuit', '30min', 'https://images.unsplash.com/photo-1568454537842-d933259bb258?w=600&h=400&fit=crop'),
('Pont suspendu de Sidi M\'Cid', 'pont-sidi-mcid', 'Pont suspendu a 175m de haut, portee de 168m. Relie la ville a la colline. L\'un des plus photographies.', 'monument', 'Constantine', 'Constantine', 36.3650, 6.6110, 'gratuit', '30min', NULL),
('Pont d\'El Kantara', 'pont-el-kantara', 'Le plus ancien pont de Constantine, d\'origine romaine, reconstruit plusieurs fois. Relie la vieille ville aux quartiers neufs.', 'monument', 'Constantine', 'Constantine', 36.3620, 6.6170, 'gratuit', '20min', NULL),
('Passerelle Mellah Slimane', 'passerelle-mellah-slimane', 'Passerelle pietonne historique (1917-1925) reliant le centre-ville a la Casbah. Mecanisme d\'ascenseur.', 'monument', 'Constantine', 'Constantine', 36.3600, 6.6100, 'gratuit', '20min', NULL),

-- Monuments
('Palais Ahmed Bey', 'palais-ahmed-bey-constantine', 'Palais ottoman (1826-1835). Cours, jardins, carreaux colores et mosaiques. Style arabo-turc. Musee d\'histoire locale.', 'monument', 'Constantine', 'Constantine', 36.3620, 6.6140, 'pas_cher', '1h', NULL),
('Monument aux Morts', 'monument-aux-morts-constantine', 'Construit en 1918 au point le plus haut de la ville, inspire de l\'Arc de Trajan a Timgad. Vue panoramique.', 'monument', 'Constantine', 'Constantine', 36.3670, 6.6090, 'gratuit', '30min', NULL),
('Ruines de Tiddis', 'ruines-tiddis', 'Site romain a 30km de Constantine, 1er siecle ap. J.-C. Mosaiques intactes, thermes, temples, arches sur flanc de colline.', 'monument', 'Constantine', 'Constantine', 36.4370, 6.5380, 'pas_cher', '2-3h', NULL),

-- Religieux
('Mosquee Emir Abdelkader', 'mosquee-emir-abdelkader-constantine', 'Inauguree en 1994. Minarets de 107m, dome a 64m. Chef-d\'oeuvre d\'architecture islamique contemporaine.', 'religieux', 'Constantine', 'Constantine', 36.3550, 6.5940, 'gratuit', '1h', NULL),

-- Musee
('Musee National de Cirta', 'musee-cirta-constantine', 'Ouvert en 1930. Collection exceptionnelle : artefacts carthaginois, numides, romains, islamiques. Mosaique de Venus, bronze de la Victoire.', 'musee', 'Constantine', 'Constantine', 36.3630, 6.6160, 'pas_cher', '1-2h', NULL),

-- Nature
('Gorges du Rhummel', 'gorges-rhummel', 'Gorge spectaculaire de 200m de profondeur creusee par le Rhummel au coeur de la ville. Cascades et vues vertigineuses.', 'nature', 'Constantine', 'Constantine', 36.3630, 6.6130, 'gratuit', '1h', NULL),
('Foret de Djebel El Ouahch', 'foret-djebel-el-ouahch', 'Foret recreative a 5km nord-est. Randonnee, camping, promenades nature. Tranquillite et verdure.', 'nature', 'Constantine', 'Constantine', 36.3900, 6.6500, 'gratuit', 'demi-journee', NULL),

-- Parcs
('Jardin Emir Abdelkader', 'jardin-emir-abdelkader-constantine', 'Jardins paisibles pres du Musee Cirta. Allees fleuries, fontaines et kiosque a musique.', 'parc', 'Constantine', 'Constantine', 36.3625, 6.6155, 'gratuit', '1h', NULL),

-- Transport / Viewpoint
('Telepherique de Constantine', 'telepherique-constantine', 'Telecabine (2008) traversant les Gorges du Rhummel. 33 cabines de 15 places. Trajet de 8 minutes avec vues spectaculaires.', 'divertissement', 'Constantine', 'Constantine', 36.3610, 6.6120, 'pas_cher', '30min', NULL),

-- Divertissement
('Amira Land', 'amira-land-constantine', 'Parc de loisirs dans le quartier Ali-Mendjeli. Divertissement et recreation pour les familles.', 'divertissement', 'Constantine', 'Constantine', 36.2700, 6.5700, 'pas_cher', '2-3h', NULL),

-- Shopping
('Souk El Asser', 'souk-el-asser-constantine', 'Marche couvert historique. Produits traditionnels, epices, textiles, artisanat. Lieu commercial emblematique.', 'shopping', 'Constantine', 'Constantine', 36.3610, 6.6140, 'gratuit', '1h', NULL),
('Ritaj Mall', 'ritaj-mall-constantine', 'Plus grand mall moderne de Constantine. Vetements, cosmetiques, parfums. Food court et divertissement.', 'shopping', 'Constantine', 'Constantine', 36.3400, 6.6000, 'gratuit', '2-3h', NULL),

-- Culturel
('Theatre Regional de Constantine', 'theatre-regional-constantine', 'Principale salle de spectacle. Theatre, danse, musique, projections et evenements culturels.', 'culturel', 'Constantine', 'Constantine', 36.3615, 6.6130, 'moyen', '2h', NULL);


-- ═══════════════════════════════════════════════════════════════
-- SEED DATA - ANNABA (30 lieux)
-- ═══════════════════════════════════════════════════════════════

INSERT INTO activities (nom, slug, description, category, ville, wilaya, gps_latitude, gps_longitude, price_range, duration_avg, photo_url) VALUES

-- Monuments & Histoire
('Basilique Saint-Augustin', 'basilique-saint-augustin-annaba', 'Basilique neo-byzantine (1881) perchee sur une colline. Vue panoramique sur la ville et la baie. Dediee a St Augustin d\'Hippone.', 'monument', 'Annaba', 'Annaba', 36.9130, 7.7650, 'gratuit', '1h', 'https://images.unsplash.com/photo-1564769625905-50e93615e769?w=600&h=400&fit=crop'),
('Ruines d\'Hippone', 'ruines-hippone-annaba', 'Ruines romaines du 1er siecle : theatre, forum, grands thermes, sols en mosaiques remarquablement preserves. Augustin y fut eveque.', 'monument', 'Annaba', 'Annaba', 36.9060, 7.7530, 'pas_cher', '2h', NULL),
('Mosquee Sidi Bou Merouane', 'mosquee-sidi-bou-merouane', 'Mosquee du 11eme siecle avec colonnes recuperees de ruines romaines. L\'une des plus anciennes du Maghreb.', 'religieux', 'Annaba', 'Annaba', 36.9000, 7.7670, 'gratuit', '30min', NULL),
('Phare du Cap de Garde', 'phare-cap-de-garde', 'Phare historique au promontoire du Cap de Garde. Vues cotieres spectaculaires sur la Mediterranee.', 'monument', 'Annaba', 'Annaba', 36.9510, 7.7870, 'gratuit', '1h', NULL),
('Cours de la Revolution', 'cours-revolution-annaba', 'Boulevard principal d\'Annaba. Architecture coloniale francaise, promenades ombragees, ambiance animee.', 'monument', 'Annaba', 'Annaba', 36.9010, 7.7610, 'gratuit', '30min', NULL),

-- Musee
('Musee d\'Hippone', 'musee-hippone-annaba', 'L\'un des plus riches musees d\'Algerie. Mosaiques romaines des 3e-4e siecles, statues en bronze, steles, objets archeologiques.', 'musee', 'Annaba', 'Annaba', 36.9070, 7.7540, 'pas_cher', '1-2h', NULL),

-- Nature & Parcs
('Parc National de l\'Edough', 'parc-national-edough', 'Parc montagneux avec forets de chenes-liegge denses, sentiers de randonnee et biodiversite riche. Pics a 1080m.', 'nature', 'Annaba', 'Annaba', 36.8400, 7.6700, 'gratuit', 'journee', NULL),
('Seraidi', 'seraidi-annaba', 'Station d\'altitude a 13km d\'Annaba. Temperatures fraiches, vue panoramique sur la baie, randonnee en foret, village charmant.', 'nature', 'Annaba', 'Annaba', 36.8620, 7.6850, 'gratuit', 'demi-journee', NULL),
('Cap de Garde', 'cap-de-garde-annaba', 'Zone naturelle cotiere au nord-est d\'Annaba. Falaises, sentiers de randonnee en bord de mer, observation de la faune.', 'nature', 'Annaba', 'Annaba', 36.9500, 7.7860, 'gratuit', '2-3h', NULL),

-- Plages
('Plage Chapuis (Rizzi Amor)', 'plage-chapuis-annaba', 'L\'une des plages les plus populaires d\'Annaba. Sable fin sur plusieurs centaines de metres. Tres frequentee en ete.', 'plage', 'Annaba', 'Annaba', 36.9110, 7.7410, 'gratuit', 'demi-journee', 'https://images.unsplash.com/photo-1519046904884-53103b34b206?w=600&h=400&fit=crop'),
('Plage Ain Achir', 'plage-ain-achir-annaba', 'Sable dore, eaux cristallines. Ambiance mediterraneenne detendue.', 'plage', 'Annaba', 'Annaba', 36.9200, 7.7350, 'gratuit', 'demi-journee', NULL),
('Plage de la Caroube', 'plage-caroube-annaba', 'Plage rocheuse celebre pour ses restaurants de fruits de mer, couchers de soleil et ambiance de soiree.', 'plage', 'Annaba', 'Annaba', 36.9250, 7.7500, 'gratuit', 'demi-journee', NULL),
('Plage Oued Bagrat', 'plage-oued-bagrat-annaba', 'Plage plus calme aux eaux claires. Moins bondee, ideale pour le snorkeling et les amoureux de la nature.', 'plage', 'Annaba', 'Annaba', 36.9180, 7.7320, 'gratuit', 'demi-journee', NULL),

-- Divertissement
('Farouk Land Park', 'farouk-land-annaba', 'Premier parc d\'attractions d\'Annaba. Montagnes russes, carrousels, autos tamponneuses. Favori des familles.', 'divertissement', 'Annaba', 'Annaba', 36.8850, 7.7400, 'moyen', '3-4h', NULL),
('CapFun Seraidi', 'capfun-seraidi-annaba', 'Parc aventure dans les montagnes de Seraidi. Quad, tyroliennes en foret, activites plein air.', 'sport', 'Annaba', 'Annaba', 36.8630, 7.6860, 'moyen', 'demi-journee', NULL),

-- Shopping
('Marche Central d\'Annaba', 'marche-central-annaba', 'Marche traditionnel bouillonnant. Epices, olives, produits frais, articles menagers, snacks locaux.', 'shopping', 'Annaba', 'Annaba', 36.9020, 7.7600, 'gratuit', '1h', NULL),
('Annaba Mall', 'annaba-mall', 'Premier centre commercial moderne d\'Annaba (2025). 2.8 hectares, 100+ boutiques, restaurants et loisirs.', 'shopping', 'Annaba', 'Annaba', 36.8800, 7.7900, 'gratuit', '2-3h', NULL),

-- Promenade
('La Corniche d\'Annaba', 'corniche-annaba', 'Promenade du bord de mer le long de la plage Chapuis. Cafes, restaurants, clubs. Anime le soir avec seafood et socialisation.', 'parc', 'Annaba', 'Annaba', 36.9100, 7.7420, 'gratuit', '1-2h', NULL);


-- ═══════════════════════════════════════════════════════════════
-- SEED DATA - SETIF (19 lieux)
-- ═══════════════════════════════════════════════════════════════

INSERT INTO activities (nom, slug, description, category, ville, wilaya, gps_latitude, gps_longitude, price_range, duration_avg, photo_url) VALUES

-- Monuments
('Ain El Fouara', 'ain-el-fouara-setif', 'Fontaine iconique sur la Place de l\'Independance avec statue en marbre (1898). Symbole de la ville. Point de rencontre populaire.', 'monument', 'Setif', 'Setif', 36.1890, 5.4100, 'gratuit', '20min', NULL),
('Citadelle Byzantine de Setif', 'citadelle-byzantine-setif', 'Forteresse restauree du 3e-4e siecle. Vue panoramique sur la ville. Deux grandes mosaiques romaines trouvees sous la citadelle.', 'monument', 'Setif', 'Setif', 36.1870, 5.4080, 'pas_cher', '1h', NULL),
('Ruines de Djemila (Cuicul)', 'ruines-djemila-cuicul', 'Site UNESCO a 32km de Setif. L\'une des villes romaines les mieux preservees d\'Afrique du Nord. Forum, theatre de 3000 places, thermes, basiliques, temples.', 'monument', 'Setif', 'Setif', 36.3180, 5.7340, 'pas_cher', 'demi-journee', 'https://images.unsplash.com/photo-1568454537842-d933259bb258?w=600&h=400&fit=crop'),
('Place du 8 Mai 1945', 'place-8-mai-1945-setif', 'Place centrale commemorant les massacres du 8 mai 1945. Point de depart du mouvement d\'independance algerien.', 'monument', 'Setif', 'Setif', 36.1900, 5.4110, 'gratuit', '20min', NULL),

-- Musees
('Musee d\'Archeologie de Setif', 'musee-archeologie-setif', 'L\'un des meilleurs musees d\'Algerie. Mosaiques romaines (Triomphe de Dionysos, Triomphe de Venus), sculptures, poteries, objets prehistoriques a islamiques.', 'musee', 'Setif', 'Setif', 36.1880, 5.4090, 'pas_cher', '2h', NULL),

-- Parcs
('Jardin Emir Abdelkader', 'jardin-emir-abdelkader-setif', 'Parc de 14 hectares, musee lapidaire en plein air. 200 steles epigraphiques, colonnes romaines, inscriptions latines, thermes romains preserves au centre.', 'parc', 'Setif', 'Setif', 36.1920, 5.4130, 'gratuit', '1-2h', NULL),
('Park Attraction Setifis', 'park-attraction-setifis', 'Parc d\'attractions au coeur de la ville. Lac pour canotage, grande roue, autos tamponneuses, carrousels. Zoo avec 30 especes (tigres, lions, fennecs).', 'divertissement', 'Setif', 'Setif', 36.1850, 5.4050, 'pas_cher', '3-4h', NULL),

-- Shopping
('Park Mall Setif', 'park-mall-setif', 'Plus grand centre commercial d\'Algerie : 143 000m2. 110 boutiques, 13 restaurants, bowling 10 pistes, patinoire 400m2, cinema 7D. Hotel Marriott 4 etoiles integre.', 'shopping', 'Setif', 'Setif', 36.1850, 5.4300, 'gratuit', '3-4h', NULL),

-- Nature
('Mont Megres', 'mont-megres-setif', 'Sommet a 1800m+ a 20km au nord de Setif. Enneige plusieurs mois. Randonnee et vues panoramiques.', 'nature', 'Setif', 'Setif', 36.3500, 5.3500, 'gratuit', 'journee', NULL),
('Hammam Guergour', 'hammam-guergour-setif', 'Station thermale a 55km au nord de Setif. Sources chaudes naturelles a 44°C. 3eme rang mondial en radioactivite des eaux. Sentiers de randonnee.', 'nature', 'Setif', 'Setif', 36.4600, 5.1600, 'pas_cher', 'demi-journee', NULL);


-- ═══════════════════════════════════════════════════════════════
-- INDEX & STATS
-- ═══════════════════════════════════════════════════════════════
-- Mettre a jour les compteurs
-- (Les nb_avis et nb_photos se mettront a jour automatiquement quand les utilisateurs posteront)

-- Index FULLTEXT pour la recherche textuelle
ALTER TABLE activities ADD FULLTEXT INDEX ft_search (nom, description);


-- ═══════════════════════════════════════════════════════════════
-- SEED PHOTOS (placeholders Unsplash par categorie)
-- A remplacer par Google Places API plus tard
-- ═══════════════════════════════════════════════════════════════

-- Banque d'images par categorie
-- monument: photo-1564769625905-50e93615e769, photo-1568454537842-d933259bb258, photo-1569949381669-ecf31ae8e613
-- plage: photo-1507525428034-b723cf961d3e, photo-1473116763249-2faaef81ccda, photo-1519046904884-53103b34b206
-- parc: photo-1585320806297-9794b3e4eeae, photo-1441974231531-c6227db76b6e, photo-1518173946687-a243e2e00427
-- musee: photo-1533929736458-ca588d08c8be, photo-1554907984-15263bfd63bd
-- shopping: photo-1551882547-ff40c63fe5fa, photo-1567449303078-57ad995bd329
-- divertissement: photo-1596394516093-501ba68a0ba6, photo-1513151233558-d860c5398176
-- nightlife: photo-1570710891163-6d3b5c47248b, photo-1566737236500-c8ac43014a67
-- nature: photo-1464822759023-fed622ff2c3b, photo-1476514525535-07fb3b4ae5f1, photo-1486299267070-83823f5448dd
-- religieux: photo-1564769625905-50e93615e769, photo-1585129777188-94600bc7b4b3
-- culturel: photo-1507003211169-0a1dd7228f2d, photo-1598488035139-bdbb2231cb64
-- sport: photo-1551698618-1dfe5d97d256
-- cafe: photo-1559925393-8be0ec4767c8

-- 1 photo par lieu via INSERT par categorie
INSERT INTO activity_photos (activity_id, path, type, caption)
SELECT a.id,
    CASE a.category
        WHEN 'monument' THEN 'https://images.unsplash.com/photo-1564769625905-50e93615e769?w=800&h=600&fit=crop'
        WHEN 'plage' THEN 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&h=600&fit=crop'
        WHEN 'parc' THEN 'https://images.unsplash.com/photo-1585320806297-9794b3e4eeae?w=800&h=600&fit=crop'
        WHEN 'musee' THEN 'https://images.unsplash.com/photo-1533929736458-ca588d08c8be?w=800&h=600&fit=crop'
        WHEN 'shopping' THEN 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=800&h=600&fit=crop'
        WHEN 'divertissement' THEN 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?w=800&h=600&fit=crop'
        WHEN 'nightlife' THEN 'https://images.unsplash.com/photo-1570710891163-6d3b5c47248b?w=800&h=600&fit=crop'
        WHEN 'nature' THEN 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&h=600&fit=crop'
        WHEN 'religieux' THEN 'https://images.unsplash.com/photo-1585129777188-94600bc7b4b3?w=800&h=600&fit=crop'
        WHEN 'culturel' THEN 'https://images.unsplash.com/photo-1598488035139-bdbb2231cb64?w=800&h=600&fit=crop'
        WHEN 'sport' THEN 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=800&h=600&fit=crop'
        WHEN 'cafe' THEN 'https://images.unsplash.com/photo-1559925393-8be0ec4767c8?w=800&h=600&fit=crop'
        ELSE 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop'
    END,
    'main',
    CONCAT(a.nom, ' - ', a.ville)
FROM activities a;

-- 2eme photo variee pour chaque lieu
INSERT INTO activity_photos (activity_id, path, type, caption)
SELECT a.id,
    CASE a.category
        WHEN 'monument' THEN 'https://images.unsplash.com/photo-1568454537842-d933259bb258?w=800&h=600&fit=crop'
        WHEN 'plage' THEN 'https://images.unsplash.com/photo-1473116763249-2faaef81ccda?w=800&h=600&fit=crop'
        WHEN 'parc' THEN 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=800&h=600&fit=crop'
        WHEN 'musee' THEN 'https://images.unsplash.com/photo-1554907984-15263bfd63bd?w=800&h=600&fit=crop'
        WHEN 'shopping' THEN 'https://images.unsplash.com/photo-1567449303078-57ad995bd329?w=800&h=600&fit=crop'
        WHEN 'divertissement' THEN 'https://images.unsplash.com/photo-1513151233558-d860c5398176?w=800&h=600&fit=crop'
        WHEN 'nightlife' THEN 'https://images.unsplash.com/photo-1566737236500-c8ac43014a67?w=800&h=600&fit=crop'
        WHEN 'nature' THEN 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=800&h=600&fit=crop'
        WHEN 'religieux' THEN 'https://images.unsplash.com/photo-1564769625905-50e93615e769?w=800&h=600&fit=crop'
        WHEN 'culturel' THEN 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop'
        WHEN 'sport' THEN 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=800&h=600&fit=crop'
        WHEN 'cafe' THEN 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&h=600&fit=crop'
        ELSE 'https://images.unsplash.com/photo-1518173946687-a243e2e00427?w=800&h=600&fit=crop'
    END,
    'gallery',
    CONCAT('Vue de ', a.nom)
FROM activities a;

-- Mettre a jour nb_photos
UPDATE activities a SET nb_photos = (SELECT COUNT(*) FROM activity_photos ap WHERE ap.activity_id = a.id);


-- ═══════════════════════════════════════════════════════════════
-- SEED REVIEWS (1 avis par lieu, user_id=1, a remplacer par Google Places API)
-- ═══════════════════════════════════════════════════════════════

-- Generer 1 avis par activite avec note variable et message contextuel
INSERT INTO activity_reviews (activity_id, user_id, note_globale, message, status)
SELECT a.id, 1,
    CASE
        WHEN a.category = 'monument' THEN 5
        WHEN a.category = 'plage' THEN 4
        WHEN a.category = 'parc' THEN 4
        WHEN a.category = 'musee' THEN 4
        WHEN a.category = 'shopping' THEN 3
        WHEN a.category = 'divertissement' THEN 4
        WHEN a.category = 'nightlife' THEN 4
        WHEN a.category = 'nature' THEN 5
        WHEN a.category = 'religieux' THEN 5
        WHEN a.category = 'culturel' THEN 4
        WHEN a.category = 'sport' THEN 4
        WHEN a.category = 'cafe' THEN 4
        ELSE 4
    END,
    CASE a.category
        WHEN 'monument' THEN CONCAT('Lieu historique fascinant a ', a.ville, '. ', a.nom, ' merite vraiment le detour. Architecture impressionnante et ambiance unique. Prevoyez ', COALESCE(a.duration_avg, '1h'), ' pour bien en profiter.')
        WHEN 'plage' THEN CONCAT('Belle plage a ', a.ville, '. Eau claire et sable fin. ', a.nom, ' est ideale pour une journee de detente. Un peu bondee en ete mais l''ambiance reste agreable.')
        WHEN 'parc' THEN CONCAT('Espace vert agreable a ', a.ville, '. ', a.nom, ' est parfait pour une balade en famille ou entre amis. Bien entretenu avec de beaux espaces ombrages.')
        WHEN 'musee' THEN CONCAT('Musee tres interessant a ', a.ville, '. ', a.nom, ' possede une collection riche et bien presentee. Comptez ', COALESCE(a.duration_avg, '1-2h'), ' pour la visite complete.')
        WHEN 'shopping' THEN CONCAT('Centre commercial correct a ', a.ville, '. ', a.nom, ' offre un bon choix de boutiques et de restauration. Pratique pour un apres-midi shopping.')
        WHEN 'divertissement' THEN CONCAT('Endroit fun a ', a.ville, ' ! ', a.nom, ' propose de bonnes activites pour toute la famille. Les enfants adorent. Prix raisonnable pour la journee.')
        WHEN 'nightlife' THEN CONCAT('Bonne ambiance nocturne a ', a.ville, '. ', a.nom, ' offre une experience sympa avec de la bonne musique. Les prix sont un peu eleves mais l''ambiance vaut le coup.')
        WHEN 'nature' THEN CONCAT('Superbe cadre naturel pres de ', a.ville, '. ', a.nom, ' est un veritable havre de paix. L''air pur et les paysages sont ressourcants. Parfait pour la randonnee.')
        WHEN 'religieux' THEN CONCAT('Lieu spirituel remarquable a ', a.ville, '. ', a.nom, ' est un chef-d''oeuvre architectural. L''atmosphere est apaisante et l''interieur magnifique.')
        WHEN 'culturel' THEN CONCAT('Lieu culturel enrichissant a ', a.ville, '. ', a.nom, ' propose une programmation variee et un cadre agreable. A decouvrir absolument.')
        WHEN 'sport' THEN CONCAT('Activite sportive agreable pres de ', a.ville, '. ', a.nom, ' offre de bonnes installations et un cadre sympa. Ideal pour les amateurs de sensations.')
        WHEN 'cafe' THEN CONCAT('Cafe agreable a ', a.ville, '. ', a.nom, ' a une bonne ambiance et des boissons de qualite. Parfait pour une pause detente.')
        ELSE CONCAT('Endroit sympa a ', a.ville, '. ', a.nom, ' vaut la visite.')
    END,
    'approved'
FROM activities a;

-- Mettre a jour les stats
UPDATE activities a SET
    nb_avis = (SELECT COUNT(*) FROM activity_reviews ar WHERE ar.activity_id = a.id AND ar.status = 'approved'),
    note_moyenne = COALESCE((SELECT AVG(ar.note_globale) FROM activity_reviews ar WHERE ar.activity_id = a.id AND ar.status = 'approved'), 0);
