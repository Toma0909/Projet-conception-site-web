-- ============================================================================
-- INSTALLATION COMPLÈTE DE LA PLATEFORME DE DÉMÉNAGEMENT
-- ============================================================================
-- Ce script crée toutes les tables et insère les données d'exemple
-- À copier/coller dans phpMyAdmin ou à exécuter via MySQL
-- Base de données : bdd
-- ============================================================================

-- 1. CRÉATION DES TABLES
-- ============================================================================

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

-- ============================================================================
-- 2. INSERTION DES DONNÉES D'EXEMPLE
-- ============================================================================

-- Ajouter des comptes de test
-- Mot de passe pour tous : "password123"
INSERT IGNORE INTO `compte` (`nom`, `prenom`, `email`, `password`, `role`) VALUES
('Dupont', 'Marie', 'marie.dupont@email.com', '$2y$10$bMPARLNlV0TSlcASM/M5QO2lvBYcYqXzPUPJOjGfR0S/K9wBzRSJ6', 1),
('Martin', 'Pierre', 'pierre.martin@email.com', '$2y$10$bMPARLNlV0TSlcASM/M5QO2lvBYcYqXzPUPJOjGfR0S/K9wBzRSJ6', 1),
('Moreau', 'Luc', 'luc.moreau@email.com', '$2y$10$bMPARLNlV0TSlcASM/M5QO2lvBYcYqXzPUPJOjGfR0S/K9wBzRSJ6', 2),
('Dubois', 'Jean', 'jean.dubois@email.com', '$2y$10$bMPARLNlV0TSlcASM/M5QO2lvBYcYqXzPUPJOjGfR0S/K9wBzRSJ6', 2);

-- Ajouter des déménagements d'exemple
INSERT INTO `demenagement` (
  `client_id`, `titre`, `description`, `date_demenagement`, `heure_debut`,
  `ville_depart`, `ville_arrivee`, `depart_type`, `depart_etage`, `depart_ascenseur`,
  `arrivee_type`, `arrivee_etage`, `arrivee_ascenseur`, `volume`, `poids`, `nombre_demenageurs`, `statut`
) VALUES
((SELECT id FROM compte WHERE email = 'marie.dupont@email.com'), 'Déménagement appartement 3 pièces Paris-Lyon', 
 'Déménagement d''un appartement T3 situé au 4ème étage avec ascenseur. Mobilier standard : canapé, lit double, table à manger, électroménager.',
 '2025-11-20', '09:00:00', 'Paris 15ème', 'Lyon 6ème', 
 'appartement', 4, 1, 'appartement', 2, 1, 35.00, 800.00, 2, 'en_attente'),

((SELECT id FROM compte WHERE email = 'pierre.martin@email.com'), 'Déménagement maison familiale Marseille-Bordeaux',
 'Maison de 120m² avec jardin. Beaucoup de meubles lourds (armoires, bibliothèques). Environ 50 cartons.',
 '2025-11-25', '08:00:00', 'Marseille', 'Bordeaux',
 'maison', NULL, 0, 'maison', NULL, 0, 60.00, 1500.00, 3, 'en_attente');

-- Ajouter des propositions des déménageurs
-- Variables pour les IDs
SET @dem1 = (SELECT id FROM demenagement WHERE titre LIKE '%Paris-Lyon%' LIMIT 1);
SET @dem2 = (SELECT id FROM demenagement WHERE titre LIKE '%Marseille-Bordeaux%' LIMIT 1);
SET @luc = (SELECT id FROM compte WHERE email = 'luc.moreau@email.com');
SET @jean = (SELECT id FROM compte WHERE email = 'jean.dubois@email.com');

INSERT INTO `proposition` (`demenagement_id`, `demenageur_id`, `prix`, `commentaire`, `statut`) VALUES
-- Pour le déménagement 1 (Paris-Lyon)
(@dem1, @luc, 850.00, 'Je dispose d''un camion 20m³ et de tout le matériel nécessaire. Expérience de 10 ans.', 'en_attente'),
(@dem1, @jean, 780.00, 'Prix compétitif, équipe professionnelle, assurance tous risques incluse.', 'en_attente'),

-- Pour le déménagement 2 (Marseille-Bordeaux)
(@dem2, @luc, 1500.00, 'Spécialiste du transport de meubles lourds. Matériel adapté disponible.', 'en_attente'),
(@dem2, @jean, 1650.00, 'Déménagement complet avec équipe de 3 personnes expérimentées.', 'en_attente');

-- ============================================================================
-- 3. VÉRIFICATION DE L'INSTALLATION
-- ============================================================================

SELECT '✅ Installation terminée avec succès !' as Message;
SELECT '' as '';
SELECT '📊 RÉSUMÉ DES DONNÉES :' as '';
SELECT CONCAT('   - ', COUNT(*), ' comptes créés (2 clients + 2 déménageurs)') as Info FROM compte WHERE id > 1;
SELECT CONCAT('   - ', COUNT(*), ' déménagements créés') as Info FROM demenagement;
SELECT CONCAT('   - ', COUNT(*), ' propositions créées') as Info FROM proposition;
SELECT '' as '';
SELECT '🔑 COMPTES DE TEST :' as '';
SELECT 'Mot de passe pour tous : password123' as '';
SELECT '' as '';
SELECT '👥 CLIENTS (rôle 1) :' as '';
SELECT '   - marie.dupont@email.com' as '';
SELECT '   - pierre.martin@email.com' as '';
SELECT '' as '';
SELECT '🚚 DÉMÉNAGEURS (rôle 2) :' as '';
SELECT '   - luc.moreau@email.com' as '';
SELECT '   - jean.dubois@email.com' as '';
SELECT '' as '';
SELECT '🌐 Accédez au site : http://localhost/projet site web/Projet-conception-site-web/' as '';
