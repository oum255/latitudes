CREATE DATABASE IF NOT EXISTS latitudes;
USE latitudes;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL, -- Doit être suffisamment long pour contenir les hash
    role ENUM('admin', 'utilisateur') NOT NULL DEFAULT 'utilisateur' -- 'admin' gère le catalogue, 'utilisateur' est un compte sans droit d'édition
);

-- Compte admin de démonstration (mot de passe : Demo-Latitudes-2026)
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
    ('Administrateur', 'admin@voyages.com', '$2y$12$wDGbh46yGPuke.wrxu.Pgu7j2tl6Ko9za0Fwzx0305pJHTGjJ2UWO', 'admin');

-- La table `voyages` est créée par l'import de sql/voyages.sql
