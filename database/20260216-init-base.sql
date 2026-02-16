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
('Boeny'),
('Bongolava'),
('Diana'),
('Haute Matsiatra'),
('Itasy'),
('Menabe'),
('Sava'),
('Vakinankaratra');

INSERT INTO bngrc_ville (nom, region_id) VALUES
('Antananarivo', 1),
('Avarandrano', 1),
('Toamasina', 2),
('Mahajanga', 3),
('Miarinarivo', 4),
('Antsiranana', 5),
('Fianarantsoa', 6),
('Miarinarivo', 7),
('Morondava', 8),
('Antalaha', 9),
('Antsirabe', 10);

INSERT INTO bngrc_categorie_besoin (nom) VALUES
('en Nature'),
('en Materiaux'),
('en Argent');

INSERT INTO bngrc_type_besoin (nom, categorie_id, prix_unitaire, unite) VALUES
('riz', 1, 1000, 'kg'),
('huile', 1, 2000, 'litre'),
('sucre', 1, 1500, 'kg'),
('sel', 1, 500, 'kg'),
('matelas', 2, 30000, 'unité'),
('couverture', 2, 10000, 'unité'),
('tente', 2, 25000, 'unité'),
('Vêtements', 2, 5000, 'unité'),
('Abri temporaire', 2, 20000, 'm²'),
('Soins médicaux', 3, 15000, 'unité'),
('Eau potable', 1, 500, 'litre'),
('Matériel scolaire', 2, 3000, 'unité');