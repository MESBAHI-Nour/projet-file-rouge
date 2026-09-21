
DROP DATABASE IF EXISTS vente_photos;
CREATE DATABASE vente_photos
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE vente_photos;


CREATE TABLE photographe (
    id_photographe     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom_photographe    VARCHAR(80)  NOT NULL,
    prenom_photographe VARCHAR(80)  NOT NULL,
    email_photographe  VARCHAR(160) NOT NULL,
    mot_de_passe       VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_photographe),
    UNIQUE KEY uq_photographe_email (email_photographe)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE style (
    id_style          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom_style         VARCHAR(80)  NOT NULL,
    description_style TEXT         NULL,
    PRIMARY KEY (id_style),
    UNIQUE KEY uq_style_nom (nom_style)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE licence (
    id_licence          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nom_licence         VARCHAR(80)    NOT NULL,
    description_licence TEXT           NULL,
    prix_licence        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id_licence),
    UNIQUE KEY uq_licence_nom (nom_licence),
    CONSTRAINT ck_licence_prix CHECK (prix_licence >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE image (
    id_image          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    titre_image       VARCHAR(150)  NOT NULL,
    description_image TEXT          NULL,
    fichier_image     VARCHAR(255)  NOT NULL,
    prix_image        DECIMAL(10,2) NOT NULL,
    date_publication  DATE          NOT NULL,
    id_photographe    INT UNSIGNED  NOT NULL,
    id_style          INT UNSIGNED  NOT NULL,
    id_licence        INT UNSIGNED  NOT NULL,
    PRIMARY KEY (id_image),
    KEY idx_image_photographe (id_photographe),
    KEY idx_image_style (id_style),
    KEY idx_image_licence (id_licence),
    KEY idx_image_titre (titre_image),
    CONSTRAINT fk_image_photographe
        FOREIGN KEY (id_photographe) REFERENCES photographe (id_photographe)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_image_style
        FOREIGN KEY (id_style) REFERENCES style (id_style)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_image_licence
        FOREIGN KEY (id_licence) REFERENCES licence (id_licence)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_image_prix CHECK (prix_image >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO photographe (nom_photographe, prenom_photographe, email_photographe, mot_de_passe) VALUES
('Bennani',  'Yassine', 'yassine.bennani@example.com', '$2y$12$vTQJEeArNY/t317awg3heeZVEN4gF6fw.wggmDbgeJSXrbbWXmNLG'),
('Meroune',  'Salwa',    'nour.mesbahi@example.com',    '$2y$12$vTQJEeArNY/t317awg3heeZVEN4gF6fw.wggmDbgeJSXrbbWXmNLG'),
('Lahlou',   'Salma',   'salma.lahlou@example.com',    '$2y$12$vTQJEeArNY/t317awg3heeZVEN4gF6fw.wggmDbgeJSXrbbWXmNLG');

INSERT INTO style (nom_style, description_style) VALUES
('Portrait',    'Photographie centree sur une personne ou un visage.'),
('Paysage',     'Vues naturelles, montagnes, deserts, littoral.'),
('Architecture','Batiments, structures urbaines et details geometriques.'),
('Noir et blanc', 'Photographie monochrome jouant sur les contrastes.');

INSERT INTO licence (nom_licence, description_licence, prix_licence) VALUES
('Usage personnel',  'Utilisation privee uniquement, aucune diffusion commerciale.',        0.00),
('Usage commercial', 'Utilisation autorisee sur supports commerciaux et publicitaires.',  150.00),
('Licence etendue',  'Utilisation illimitee, revente et modification autorisees.',        400.00);

INSERT INTO image (titre_image, description_image, fichier_image, prix_image, date_publication, id_photographe, id_style, id_licence) VALUES
('Regard du Sud',        'Portrait realise en lumiere naturelle a Marrakech.',        'uploads/demo-portrait-1.jpg',    120.00, '2025-01-14', 1, 1, 2),
('Dunes de Merzouga',    'Lever de soleil sur les dunes du Sahara.',                  'uploads/demo-paysage-1.jpg',     180.50, '2025-02-03', 1, 2, 3),
('Bleu de Chefchaouen',  'Ruelle traditionnelle de la ville bleue.',                  'uploads/demo-archi-1.jpg',        95.00, '2025-02-21', 2, 3, 1),
('Silence',              NULL,                                                        'uploads/demo-nb-1.jpg',          140.00, '2025-03-10', 2, 4, 2),
('Atlas en hiver',       'Sommets enneiges du Haut Atlas.',                           'uploads/demo-paysage-2.jpg',     210.00, '2025-03-28', 3, 2, 3),
('Mains d artisan',      'Detail des mains d un artisan de Fes au travail.',          'uploads/demo-portrait-2.jpg',     85.00, '2025-04-05', 3, 1, 1);
