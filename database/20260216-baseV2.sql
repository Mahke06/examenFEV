CREATE TABLE bngrc_achat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    besoin_id INT NOT NULL,
    ville_id INT NOT NULL,
    type_besoin_id INT NOT NULL,
    quantite DECIMAL(10,2) NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    frais_pourcent DECIMAL(5,2) NOT NULL,
    montant_total DECIMAL(12,2) NOT NULL,
    don_argent_id INT NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (besoin_id) REFERENCES bngrc_besoin(id),
    FOREIGN KEY (ville_id) REFERENCES bngrc_ville(id),
    FOREIGN KEY (type_besoin_id) REFERENCES bngrc_type_besoin(id),
    FOREIGN KEY (don_argent_id) REFERENCES bngrc_don(id)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC;