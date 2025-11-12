-- Structure complète de la base de données pour la plateforme de déménagement

-- Table des déménagements (annonces des clients)
CREATE TABLE IF NOT EXISTS `demenagement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(6) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `date_demenagement` date NOT NULL,
  `heure_debut` time NOT NULL,
  `ville_depart` varchar(100) NOT NULL,
  `ville_arrivee` varchar(100) NOT NULL,
  
  -- Détails du lieu de départ
  `depart_type` enum('maison','appartement') NOT NULL,
  `depart_etage` int(2) DEFAULT NULL,
  `depart_ascenseur` tinyint(1) DEFAULT 0,
  
  -- Détails du lieu d'arrivée
  `arrivee_type` enum('maison','appartement') NOT NULL,
  `arrivee_etage` int(2) DEFAULT NULL,
  `arrivee_ascenseur` tinyint(1) DEFAULT 0,
  
  -- Informations sur le volume
  `volume` decimal(10,2) DEFAULT NULL,
  `poids` decimal(10,2) DEFAULT NULL,
  `nombre_demenageurs` int(2) NOT NULL DEFAULT 2,
  
  -- Statut de l'annonce
  `statut` enum('en_attente','en_cours','termine','annule') NOT NULL DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  FOREIGN KEY (`client_id`) REFERENCES `compte`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des images des déménagements
CREATE TABLE IF NOT EXISTS `demenagement_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `demenagement_id` int(11) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin` varchar(500) NOT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `demenagement_id` (`demenagement_id`),
  FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des propositions des déménageurs
CREATE TABLE IF NOT EXISTS `proposition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `demenagement_id` int(11) NOT NULL,
  `demenageur_id` int(6) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `commentaire` text,
  `statut` enum('en_attente','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `date_proposition` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_proposition` (`demenagement_id`, `demenageur_id`),
  KEY `demenagement_id` (`demenagement_id`),
  KEY `demenageur_id` (`demenageur_id`),
  FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`demenageur_id`) REFERENCES `compte`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table de liaison entre déménagements et déménageurs sélectionnés
CREATE TABLE IF NOT EXISTS `demenagement_demenageur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `demenagement_id` int(11) NOT NULL,
  `demenageur_id` int(6) NOT NULL,
  `proposition_id` int(11) NOT NULL,
  `date_selection` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_selection` (`demenagement_id`, `demenageur_id`),
  KEY `demenagement_id` (`demenagement_id`),
  KEY `demenageur_id` (`demenageur_id`),
  KEY `proposition_id` (`proposition_id`),
  FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`demenageur_id`) REFERENCES `compte`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`proposition_id`) REFERENCES `proposition`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
