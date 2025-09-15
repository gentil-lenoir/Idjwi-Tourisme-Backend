CREATE DATABASE IF NOT EXISTS idjwi_tourisme CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE idjwi_tourisme;

-- Réservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    visit_date DATE NOT NULL,
    people INT UNSIGNED NOT NULL DEFAULT 1,
    type ENUM('classique', 'privee', 'familiale') NOT NULL DEFAULT 'classique',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Témoignages
CREATE TABLE temoignages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    contenu TEXT NOT NULL,
    note TINYINT CHECK (note BETWEEN 1 AND 5),
    statut ENUM('en_attente','publie','rejete') DEFAULT 'en_attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Galerie
CREATE TABLE galerie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150),
    description TEXT,
    image_url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog
CREATE TABLE blog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    image_url VARCHAR(255),
    auteur VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tarifs
CREATE TABLE tarifs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    unite VARCHAR(50) DEFAULT 'USD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enregistres
CREATE TABLE enregistres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    nom VARCHAR(100),
    enregistre_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);