-- À exécuter après create_db.sql, voyages.sql, catalogue_enrichi.sql et destinations_bilingues.sql.
-- Ajoute les avis des voyageurs (avec modération) et les abonnés à l'infolettre.
SET NAMES utf8mb4;
USE latitudes;

-- Avis des voyageurs. Un avis n'est visible sur le site qu'une fois publié par un admin (statut « publie »).
-- Le courriel n'est jamais affiché : il sert à limiter les abus.
CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_voyage INT NOT NULL,
    nom VARCHAR(60) NOT NULL,
    email VARCHAR(255) NOT NULL,
    note TINYINT UNSIGNED NOT NULL,
    commentaire TEXT NOT NULL,
    langue CHAR(2) NOT NULL DEFAULT 'fr',
    statut ENUM('en_attente', 'publie', 'refuse') NOT NULL DEFAULT 'en_attente',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5),
    INDEX idx_avis_voyage (id_voyage, statut, created_at),
    INDEX idx_avis_statut (statut, created_at),
    INDEX idx_avis_email (email, created_at),
    CONSTRAINT fk_avis_voyage FOREIGN KEY (id_voyage) REFERENCES voyages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Abonnés à l'infolettre. On conserve la date du consentement ; le jeton sert au lien de désabonnement.
CREATE TABLE abonnes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    langue CHAR(2) NOT NULL DEFAULT 'fr',
    token CHAR(32) NOT NULL UNIQUE,
    consenti_le TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    desabonne_le TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
