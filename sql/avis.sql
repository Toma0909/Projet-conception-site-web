-- Table pour les avis et notes des déménageurs
CREATE TABLE IF NOT EXISTS `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `demenagement_id` int(11) NOT NULL,
  `demenageur_id` int(6) NOT NULL,
  `client_id` int(6) NOT NULL,
  `note` int(1) NOT NULL CHECK (`note` >= 1 AND `note` <= 5),
  `commentaire` text,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_avis` (`demenagement_id`, `demenageur_id`),
  KEY `demenagement_id` (`demenagement_id`),
  KEY `demenageur_id` (`demenageur_id`),
  KEY `client_id` (`client_id`),
  FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`demenageur_id`) REFERENCES `compte`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`client_id`) REFERENCES `compte`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
