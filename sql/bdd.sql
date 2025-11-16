-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : dim. 16 nov. 2025 à 23:29
-- Version du serveur : 10.4.28-MariaDB
-- Version de PHP : 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bdd`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id` int(11) NOT NULL,
  `demenagement_id` int(11) NOT NULL,
  `demenageur_id` int(6) NOT NULL,
  `client_id` int(6) NOT NULL,
  `note` int(1) NOT NULL CHECK (`note` >= 1 and `note` <= 5),
  `commentaire` text DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `compte`
--

CREATE TABLE `compte` (
  `id` int(6) NOT NULL,
  `nom` varchar(60) NOT NULL,
  `prenom` varchar(60) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(120) NOT NULL,
  `role` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `contre_offre`
--

CREATE TABLE `contre_offre` (
  `id` int(11) NOT NULL,
  `proposition_id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `prix_propose` decimal(10,2) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `statut` enum('en_attente','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demenagement`
--

CREATE TABLE `demenagement` (
  `id` int(11) NOT NULL,
  `client_id` int(6) NOT NULL,
  `titre` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `date_demenagement` date NOT NULL,
  `heure_debut` time NOT NULL,
  `ville_depart` varchar(100) NOT NULL,
  `ville_arrivee` varchar(100) NOT NULL,
  `depart_type` enum('maison','appartement') NOT NULL,
  `depart_etage` int(2) DEFAULT NULL,
  `depart_ascenseur` tinyint(1) DEFAULT 0,
  `arrivee_type` enum('maison','appartement') NOT NULL,
  `arrivee_etage` int(2) DEFAULT NULL,
  `arrivee_ascenseur` tinyint(1) DEFAULT 0,
  `volume` decimal(10,2) DEFAULT NULL,
  `poids` decimal(10,2) DEFAULT NULL,
  `nombre_demenageurs` int(2) NOT NULL DEFAULT 2,
  `statut` enum('en_attente','en_cours','termine','annule') NOT NULL DEFAULT 'en_attente',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demenagement_demenageur`
--

CREATE TABLE `demenagement_demenageur` (
  `id` int(11) NOT NULL,
  `demenagement_id` int(11) NOT NULL,
  `demenageur_id` int(6) NOT NULL,
  `proposition_id` int(11) NOT NULL,
  `date_selection` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demenagement_image`
--

CREATE TABLE `demenagement_image` (
  `id` int(11) NOT NULL,
  `demenagement_id` int(11) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin` varchar(500) NOT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `demenagement_id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp(),
  `lu` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `proposition`
--

CREATE TABLE `proposition` (
  `id` int(11) NOT NULL,
  `demenagement_id` int(11) NOT NULL,
  `demenageur_id` int(6) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `statut` enum('en_attente','accepte','refuse') NOT NULL DEFAULT 'en_attente',
  `date_proposition` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_avis` (`demenagement_id`,`demenageur_id`),
  ADD KEY `demenagement_id` (`demenagement_id`),
  ADD KEY `demenageur_id` (`demenageur_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Index pour la table `compte`
--
ALTER TABLE `compte`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `contre_offre`
--
ALTER TABLE `contre_offre`
  ADD PRIMARY KEY (`id`),
  ADD KEY `proposition_id` (`proposition_id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- Index pour la table `demenagement`
--
ALTER TABLE `demenagement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Index pour la table `demenagement_demenageur`
--
ALTER TABLE `demenagement_demenageur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_selection` (`demenagement_id`,`demenageur_id`),
  ADD KEY `demenagement_id` (`demenagement_id`),
  ADD KEY `demenageur_id` (`demenageur_id`),
  ADD KEY `proposition_id` (`proposition_id`);

--
-- Index pour la table `demenagement_image`
--
ALTER TABLE `demenagement_image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demenagement_id` (`demenagement_id`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `demenagement_id` (`demenagement_id`),
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `destinataire_id` (`destinataire_id`);

--
-- Index pour la table `proposition`
--
ALTER TABLE `proposition`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_proposition` (`demenagement_id`,`demenageur_id`),
  ADD KEY `demenagement_id` (`demenagement_id`),
  ADD KEY `demenageur_id` (`demenageur_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `compte`
--
ALTER TABLE `compte`
  MODIFY `id` int(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `contre_offre`
--
ALTER TABLE `contre_offre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `demenagement`
--
ALTER TABLE `demenagement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demenagement_demenageur`
--
ALTER TABLE `demenagement_demenageur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `demenagement_image`
--
ALTER TABLE `demenagement_image`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `proposition`
--
ALTER TABLE `proposition`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`demenageur_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contre_offre`
--
ALTER TABLE `contre_offre`
  ADD CONSTRAINT `fk_contre_offre_auteur` FOREIGN KEY (`auteur_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_contre_offre_proposition` FOREIGN KEY (`proposition_id`) REFERENCES `proposition` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demenagement`
--
ALTER TABLE `demenagement`
  ADD CONSTRAINT `demenagement_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demenagement_demenageur`
--
ALTER TABLE `demenagement_demenageur`
  ADD CONSTRAINT `demenagement_demenageur_ibfk_1` FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `demenagement_demenageur_ibfk_2` FOREIGN KEY (`demenageur_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `demenagement_demenageur_ibfk_3` FOREIGN KEY (`proposition_id`) REFERENCES `proposition` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demenagement_image`
--
ALTER TABLE `demenagement_image`
  ADD CONSTRAINT `demenagement_image_ibfk_1` FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `fk_message_demenagement` FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_destinataire` FOREIGN KEY (`destinataire_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_message_expediteur` FOREIGN KEY (`expediteur_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `proposition`
--
ALTER TABLE `proposition`
  ADD CONSTRAINT `proposition_ibfk_1` FOREIGN KEY (`demenagement_id`) REFERENCES `demenagement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposition_ibfk_2` FOREIGN KEY (`demenageur_id`) REFERENCES `compte` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
