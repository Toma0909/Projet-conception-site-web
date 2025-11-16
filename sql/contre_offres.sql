CREATE TABLE IF NOT EXISTS contre_offre (
  id INT(11) NOT NULL AUTO_INCREMENT,
  proposition_id INT(11) NOT NULL,
  auteur_id INT(11) NOT NULL,
  prix_propose DECIMAL(10,2) NOT NULL,
  commentaire TEXT,
  statut ENUM('en_attente', 'accepte', 'refuse') NOT NULL DEFAULT 'en_attente',
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY proposition_id (proposition_id),
  KEY auteur_id (auteur_id),
  CONSTRAINT fk_contre_offre_proposition FOREIGN KEY (proposition_id) REFERENCES proposition(id) ON DELETE CASCADE,
  CONSTRAINT fk_contre_offre_auteur FOREIGN KEY (auteur_id) REFERENCES compte(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
