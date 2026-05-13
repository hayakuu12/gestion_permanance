CREATE DATABASE IF NOT EXISTS gestion_validation
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE gestion_validation;

CREATE TABLE listes (
    id_liste INT AUTO_INCREMENT PRIMARY KEY,
    type_liste ENUM('permanence', 'heures_supp') NOT NULL,
    trimestre INT NOT NULL,
    annee INT NOT NULL,
    fichier_original VARCHAR(255),
    date_import DATETIME DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'valide', 'refuse', 'a_corriger') DEFAULT 'en_attente'
);

CREATE TABLE elements_liste (
    id_element INT AUTO_INCREMENT PRIMARY KEY,
    id_liste INT NOT NULL,
    nom_complet VARCHAR(150) NOT NULL,
    numero_tajir VARCHAR(50) NOT NULL,
    mois INT,
    nombre_jours INT DEFAULT 0,
    nombre_heures DECIMAL(6,2) DEFAULT 0,
    remarque_manuelle TEXT,

    FOREIGN KEY (id_liste) 
    REFERENCES listes(id_liste) 
    ON DELETE CASCADE
);

CREATE TABLE controles (
    id_controle INT AUTO_INCREMENT PRIMARY KEY,
    id_element INT NOT NULL,
    type_controle VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    niveau ENUM('info', 'attention', 'grave') DEFAULT 'attention',
    date_controle DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_element) 
    REFERENCES elements_liste(id_element) 
    ON DELETE CASCADE
);

CREATE TABLE validations (
    id_validation INT AUTO_INCREMENT PRIMARY KEY,
    id_liste INT NOT NULL,
    decision ENUM('valide', 'refuse', 'a_corriger') NOT NULL,
    commentaire TEXT,
    date_validation DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_liste) 
    REFERENCES listes(id_liste) 
    ON DELETE CASCADE
);