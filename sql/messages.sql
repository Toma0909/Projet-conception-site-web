CREATE TABLE IF NOT EXISTS message (
  id INT(11) NOT NULL AUTO_INCREMENT,
  demenagement_id INT(11) NOT NULL,
  expediteur_id INT(11) NOT NULL,
  destinataire_id INT(11) NOT NULL,
  contenu TEXT NOT NULL,
  date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  lu TINYINT(1) DEFAULT 0,
  PRIMARY KEY (id),
  KEY demenagement_id (demenagement_id),
  KEY expediteur_id (expediteur_id),
  KEY destinataire_id (destinataire_id),
  CONSTRAINT fk_message_demenagement FOREIGN KEY (demenagement_id) REFERENCES demenagement(id) ON DELETE CASCADE,
  CONSTRAINT fk_message_expediteur FOREIGN KEY (expediteur_id) REFERENCES compte(id) ON DELETE CASCADE,
  CONSTRAINT fk_message_destinataire FOREIGN KEY (destinataire_id) REFERENCES compte(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
