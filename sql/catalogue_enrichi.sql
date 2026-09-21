-- À exécuter après create_db.sql et voyages.sql.
-- Ajoute les informations commerciales des voyages et la table des demandes de devis.
SET NAMES utf8mb4;
USE latitudes;

-- Prix par personne (en CAD), durée du séjour et places restantes.
-- Toutes les colonnes sont facultatives : un voyage sans prix s'affiche « Prix sur demande ».
ALTER TABLE voyages
    ADD COLUMN prix DECIMAL(10, 2) NULL AFTER description,
    ADD COLUMN duree_jours SMALLINT UNSIGNED NULL AFTER prix,
    ADD COLUMN places_disponibles SMALLINT UNSIGNED NULL AFTER duree_jours;

-- Demandes de devis envoyées par les visiteurs depuis la fiche d'un voyage.
-- Si le voyage est supprimé, la demande est conservée (id_voyage passe à NULL, titre_voyage garde le nom).
CREATE TABLE demandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_voyage INT NULL,
    titre_voyage VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(30) NULL,
    nb_voyageurs TINYINT UNSIGNED NOT NULL DEFAULT 1,
    message TEXT NULL,
    statut ENUM('nouvelle', 'traitee') NOT NULL DEFAULT 'nouvelle',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_demandes_statut (statut, created_at),
    INDEX idx_demandes_email (email, created_at),
    CONSTRAINT fk_demandes_voyage FOREIGN KEY (id_voyage) REFERENCES voyages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données de démonstration (prix par personne en CAD) pour les voyages d'exemple.
UPDATE voyages SET prix = 1890, duree_jours = 7,  places_disponibles = 14 WHERE id = 16;
UPDATE voyages SET prix = 1290, duree_jours = 5,  places_disponibles = 10 WHERE id = 17;
UPDATE voyages SET prix = 4590, duree_jours = 8,  places_disponibles = 8  WHERE id = 18;
UPDATE voyages SET prix = 3490, duree_jours = 9,  places_disponibles = 12 WHERE id = 19;
UPDATE voyages SET prix = 1990, duree_jours = 6,  places_disponibles = 16 WHERE id = 20;
UPDATE voyages SET prix = 2790, duree_jours = 10, places_disponibles = 6  WHERE id = 21;
UPDATE voyages SET prix = 2490, duree_jours = 10, places_disponibles = 12 WHERE id = 22;
UPDATE voyages SET prix = 2890, duree_jours = 7,  places_disponibles = 4  WHERE id = 23;
UPDATE voyages SET prix = 3190, duree_jours = 12, places_disponibles = 9  WHERE id = 24;
UPDATE voyages SET prix = 2990, duree_jours = 8,  places_disponibles = 10 WHERE id = 25;
UPDATE voyages SET prix = 2390, duree_jours = 7,  places_disponibles = 0  WHERE id = 26;
UPDATE voyages SET prix = 3290, duree_jours = 9,  places_disponibles = 8  WHERE id = 27;
UPDATE voyages SET prix = 3990, duree_jours = 8,  places_disponibles = 6  WHERE id = 28;
UPDATE voyages SET prix = 2590, duree_jours = 6,  places_disponibles = 15 WHERE id = 29;
UPDATE voyages SET prix = 4290, duree_jours = 10, places_disponibles = 5  WHERE id = 30;
UPDATE voyages SET prix = 4190, duree_jours = 10, places_disponibles = 7  WHERE id = 52;
UPDATE voyages SET prix = 2190, duree_jours = 6,  places_disponibles = 12 WHERE id = 53;
UPDATE voyages SET prix = 2690, duree_jours = 8,  places_disponibles = 10 WHERE id = 54;
