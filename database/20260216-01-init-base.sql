CREATE DATABASE bngrc;
USE bngrc;

CREATE TABLE bngrc_region (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

CREATE TABLE bngrc_ville (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    region_id INT NOT NULL,
    nombre_sinistres INT NOT NULL DEFAULT 0,
    FOREIGN KEY (region_id) REFERENCES bngrc_region(id) ON DELETE CASCADE 
);

CREATE TABLE bngrc_categorie_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

CREATE TABLE bngrc_type_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    categorie_id INT NOT NULL,
    prix_unitaire INT NOT NULL,
    unite VARCHAR(50) NOT NULL,
    FOREIGN KEY (categorie_id) REFERENCES bngrc_categorie_besoin(id) ON DELETE CASCADE 
);

CREATE TABLE bngrc_besoin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ville_id INT NOT NULL,
    type_besoin_id INT NOT NULL,
    quantite_demandee INT NOT NULL,
    quantite_satisfaite INT NOT NULL DEFAULT 0,
    date_saisie TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ville_id) REFERENCES bngrc_ville(id) ON DELETE CASCADE,
    FOREIGN KEY (type_besoin_id) REFERENCES bngrc_type_besoin(id) ON DELETE CASCADE
);

CREATE TABLE bngrc_don (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_besoin_id INT NOT NULL,
    quantite INT NOT NULL,
    quantite_restante INT NOT NULL,
    date_saisie TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(20) DEFAULT 'disponible',
    FOREIGN KEY (type_besoin_id) REFERENCES bngrc_type_besoin(id) ON DELETE CASCADE
);

CREATE TABLE bngrc_attribution (
    id INT AUTO_INCREMENT PRIMARY KEY,
    don_id INT NOT NULL,
    besoin_id INT NOT NULL,
    ville_id INT NOT NULL,
    quantite_attribuee INT NOT NULL,
    date_attribution TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (don_id) REFERENCES bngrc_don(id) ON DELETE CASCADE,
    FOREIGN KEY (besoin_id) REFERENCES bngrc_besoin(id) ON DELETE CASCADE,
    FOREIGN KEY (ville_id) REFERENCES bngrc_ville(id) ON DELETE CASCADE
);

INSERT INTO bngrc_region (nom) VALUES
('Analamanga'),
('Atsinanana'),
('Sud'),
('Est'),
('Sud-Est');

INSERT INTO bngrc_ville (nom, region_id) VALUES
('Antananarivo', 1),
('Toamasina', 4),
('Morondava', 3),
('Mananjary', 4),
('Farafangana', 5),
('Nosy Be', 2);

INSERT INTO bngrc_categorie_besoin (nom) VALUES
('en Nature'),
('en Materiaux'),
('en Argent');

INSERT INTO bngrc_type_besoin (nom, categorie_id, prix_unitaire, unite) VALUES
('riz', 1, 3000, 'kg'),
('huile', 1, 6000, 'litre'),
('sucre', 1, 1500, 'kg'),
('sel', 1, 500, 'kg'),
('matelas', 2, 30000, 'unité'),
('couverture', 2, 10000, 'unité'),
('tente', 2, 25000, 'unité'),
('Vêtements', 2, 5000, 'unité'),
('Abri temporaire', 2, 20000, 'm²'),
('Soins médicaux', 3, 1, 'Ar'),
('Eau potable', 1, 500, 'litre'),
('Matériel scolaire', 2, 3000, 'unité'),
('Espèces', 3, 1, 'Ar');

INSERT INTO bngrc_besoin (ville_id, type_besoin_id, quantite_demandee, date_saisie) VALUES
(1, 1, 1000, '2026-02-10 08:00:00'),
(1, 2, 200, '2026-02-10 08:15:00'),
(1, 11, 500, '2026-02-10 08:30:00'),
(2, 1, 500, '2026-02-10 10:00:00'),
(2, 5, 20, '2026-02-10 10:30:00'),
(2, 6, 30, '2026-02-10 11:00:00'),
(3, 1, 800, '2026-02-11 09:00:00'),
(3, 3, 300, '2026-02-11 09:30:00'),
(3, 7, 15, '2026-02-11 10:00:00'),
(4, 1, 600, '2026-02-11 14:00:00'),
(4, 4, 100, '2026-02-11 14:30:00'),
(5, 10, 50, '2026-02-12 08:00:00'),
(5, 1, 400, '2026-02-12 08:30:00'),
(6, 11, 1000, '2026-02-12 10:00:00'),
(6, 1, 700, '2026-02-12 10:30:00'),
(7, 12, 40, '2026-02-13 09:00:00'),
(7, 5, 25, '2026-02-13 09:30:00'),
(8, 1, 900, '2026-02-13 11:00:00'),
(8, 8, 50, '2026-02-13 11:30:00'),
(9, 1, 350, '2026-02-14 08:00:00'),
(9, 2, 150, '2026-02-14 08:30:00'),
(10, 1, 1200, '2026-02-14 10:00:00'),
(10, 3, 400, '2026-02-14 10:30:00'),
(10, 6, 50, '2026-02-14 11:00:00'),
(1, 5, 30, '2026-02-15 08:00:00'),
(2, 7, 10, '2026-02-15 09:00:00'),
(3, 6, 40, '2026-02-15 10:00:00'),
(4, 8, 60, '2026-02-15 11:00:00'),
(5, 1, 250, '2026-02-15 13:00:00'),
(6, 2, 180, '2026-02-15 14:00:00');

ALTER TABLE bngrc_don MODIFY statut VARCHAR(50) DEFAULT 'disponible';