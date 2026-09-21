-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 30, 2025 at 01:06 AM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

USE latitudes;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `latitudes`
--

-- --------------------------------------------------------

--
-- Table structure for table `voyages`
--

CREATE TABLE `voyages` (
  `id` int NOT NULL,
  `titre` varchar(255) NOT NULL,
  `continent` varchar(100) NOT NULL,
  `pays` varchar(100) NOT NULL,
  `date_depart` date NOT NULL,
  `categorie` varchar(100) DEFAULT NULL,
  `description` text,
  `id_utilisateur` int NOT NULL,
  `image` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `voyages`
--

INSERT INTO `voyages` (`id`, `titre`, `continent`, `pays`, `date_depart`, `categorie`, `description`, `id_utilisateur`, `image`) VALUES
(16, 'Plages d’Ibiza', 'Europe', 'Espagne', '2025-08-10', 'Plage', 'Vivez l’alliance parfaite entre farniente, eaux turquoise et ambiance festive dans l’une des destinations balnéaires les plus emblématiques de la Méditerranée.', 1, '[\"uploads\\/68616dec9b1b9_espagne1.jpg\",\"uploads\\/68616dec9b29a_espagne2.jpg\",\"uploads\\/68616dec9b36c_espagne3.jpg\"]'),
(17, 'Gorges du Verdon', 'Europe', 'France', '2025-09-18', 'Nature', 'Plongez au cœur du plus grand canyon d’Europe, entre falaises calcaires vertigineuses et eaux émeraude. Les Gorges du Verdon offrent un décor spectaculaire pour randonner, pagayer ou simplement admirer la puissance paisible de la nature provençale.', 1, '[\"uploads\\/68616dd780b5c_france2.jpg\",\"uploads\\/68616dd780cef_france3.jpg\"]'),
(18, 'Safari au Serengeti', 'Afrique', 'Tanzanie', '2025-07-05', 'Safari', 'Partez à la rencontre des plus grands animaux sauvages au cœur d’une nature préservée, là où le rugissement des lions résonne dans l’immensité des plaines dorées.', 1, '[\"uploads\\/68616dc4399e1_tanzanie1.jpg\",\"uploads\\/68616dc439a3d_tanzanie2.jpg\",\"uploads\\/68616dc439a87_tanzanie3.jpg\",\"uploads\\/68616dc439aca_tanzanie4.jpg\"]'),
(19, 'Tokyo by night', 'Asie', 'Japon', '2025-10-01', 'Nature', 'Découvrez une mégalopole où traditions millénaires et technologie de pointe cohabitent dans un tourbillon de lumières, de temples, de ruelles et de grattes-ciel.', 1, '[\"uploads\\/68616d9f64f09_japon1.jpg\",\"uploads\\/68616d9f64f5c_japon2.jpg\",\"uploads\\/68616d9f64f9e_japon3.jpg\"]'),
(20, 'Désert du Sahara', 'Afrique', 'Maroc', '2025-12-02', 'Désert', 'Vivez l’expérience unique d’un bivouac sous les étoiles, d’une balade en dromadaire au milieu des dunes, et d’un silence doré qui semble suspendre le temps.', 1, '[\"uploads\\/68616d876a0a9_maroc1.jpg\",\"uploads\\/68616d876a0f5_maroc2.jpg\",\"uploads\\/68616d876a139_maroc3.jpg\"]'),
(21, 'Îles Phi Phi', 'Asie', 'Thaïlande', '2025-11-20', 'Île tropicale', 'Entre falaises de calcaire plongeant dans une mer cristalline et plages secrètes bordées de cocotiers, ces îles sont une ode à l’évasion et à la beauté naturelle.', 1, '[\"uploads\\/68616d4947476_thailande1.jpg\",\"uploads\\/68616d49474d8_thailande2.jpg\",\"uploads\\/68616d494752c_thailande3.jpg\"]'),
(22, 'Rizières de Bali', 'Asie', 'Indonésie', '2025-09-01', 'Nature', 'Un paysage vivant d’harmonie verte sculptée par l’homme : les rizières balinaises forment une mosaïque paisible, reflet d’une culture profondément spirituelle.', 1, '[\"uploads\\/68616cf9e38d3_bali1.jpg\",\"uploads\\/68616cf9e3915_bali2.jpg\",\"uploads\\/68616cf9e395a_bali3.jpg\"]'),
(23, 'Zermatt et le Cervin', 'Europe', 'Suisse', '2025-12-20', 'Montagne', 'Au pied du légendaire Cervin, Zermatt vous accueille dans un paysage de carte postale où chalets en bois, pistes enneigées et ciel cristallin composent un décor alpin inoubliable. Skiez, flânez, ou admirez la montagne la plus photogénique d’Europe dans toute sa majesté hivernale.', 1, '[\"uploads\\/68616ce0608ba_suisse1.jpg\",\"uploads\\/68616ce060933_suisse2.jpg\",\"uploads\\/68616ce060990_suisse3.jpg\"]'),
(24, 'Road trip en Californie', 'Amérique', 'États-Unis', '2025-08-22', 'Montagne', 'Explorez la route côtière la plus célèbre du monde, entre plages de surf, séquoias géants, déserts californiens et villes mythiques comme San Francisco et L.A.', 1, '[\"uploads\\/68616cce791d0_\\u00c9tats-Unis1.avif\",\"uploads\\/68616cce79217_\\u00c9tats-Unis2.jpg\",\"uploads\\/68616cce79257_\\u00c9tats-Unis3.jpg\"]'),
(25, 'Chutes d’Iguazú', 'Amérique', 'Brésil', '2025-11-10', 'Nature', 'Une force brute de la nature où 275 cascades se déversent dans une jungle luxuriante : un choc visuel et sonore qui émerveille chaque voyageur.', 1, '[\"uploads\\/68616cbcd372e_Br\\u00e9sil1.jpg\",\"uploads\\/68616cbcd377c_Br\\u00e9sil2.jpg\",\"uploads\\/68616cbcd37bf_Br\\u00e9sil3.jpg\"]'),
(26, 'Coucher de soleil à Santorin', 'Europe', 'Grèce', '2025-09-10', 'Île tropicale', 'Perchée au sommet de falaises volcaniques, l’île de Santorin vous offre des couchers de soleil légendaires sur la mer Égée, entre dômes bleus, maisons blanches éclatantes et ruelles fleuries. Une expérience romantique et lumineuse, suspendue entre ciel et mer.', 1, '[\"uploads\\/68616ca02ca7b_Gr\\u00e8ce1.jpg\",\"uploads\\/68616ca02cb4d_Gr\\u00e8ce2.jpg\",\"uploads\\/68616ca02cb97_Gr\\u00e8ce3.jpg\"]'),
(27, 'Machu Picchu', 'Amérique', 'Argentine', '2025-09-30', 'Montagne', 'Ancienne cité inca perchée dans les nuages, le Machu Picchu vous invite à un pèlerinage entre mystère, nature et splendeur historique.', 1, '[\"uploads\\/68616c9020701_Argentine1.jpg\",\"uploads\\/68616c9020767_Argentine2.jpg\",\"uploads\\/68616c90207c1_Argentine3.jpg\"]'),
(28, 'Réserve de Kruger', 'Afrique', 'Afrique du Sud', '2025-07-15', 'Safari', 'Le royaume de la vie sauvage africaine : partez en 4x4 sur les pistes de cette immense réserve à la recherche d’éléphants, lions, rhinocéros et bien plus.', 1, '[\"uploads\\/68616c8224d02_Afrique du Sud1.jpg\",\"uploads\\/68616c8224d81_Afrique du Sud2.jpg\",\"uploads\\/68616c8224df0_Afrique du Sud3.jpg\"]'),
(29, 'Dunes de Dubaï', 'Asie', 'Émirats arabes unis', '2025-12-05', 'Désert', 'Embarquez pour un safari dans les dunes, admirez le coucher du soleil orangé, puis savourez un dîner oriental dans un camp bédouin au clair de lune.', 1, '[\"uploads\\/68616c567784e_\\u00c9mirats arabes unis1.jpg\",\"uploads\\/68616c5677909_\\u00c9mirats arabes unis2.jpg\",\"uploads\\/68616c56779a0_\\u00c9mirats arabes unis3.jpg\"]'),
(30, 'Grande Barrière de Corail', 'Océanie', 'Australie', '2025-10-12', 'Île tropicale', 'Plongez dans le plus vaste récif corallien au monde : un monde sous-marin coloré, vibrant de vie, à préserver absolument pour les générations futures.', 1, '[\"uploads\\/68616c42bbdf3_Australie1.jpg\",\"uploads\\/68616c42bbec5_Australie2.jpg\",\"uploads\\/68616c42bbf96_Australie3.jpg\"]'),
(52, 'Aventure au cœur des Alpes du Sud, Queenstown', 'Océanie', 'Nouvelle-Zélande', '2025-12-10', 'Montagne', 'Entourée de sommets enneigés et bordée par le lac Wakatipu aux eaux cristallines, Queenstown est le paradis des amateurs de nature et de sensations fortes. C’est ici que le saut à l’élastique est né, et l’on peut y pratiquer ski, VTT, kayak ou randonnées panoramiques. L’ambiance y est chaleureuse, entre cafés branchés, marchés locaux et vues spectaculaires à couper le souffle.', 1, '[\"uploads\\/6861d8f7a041c_Nouvelle-Z\\u00e9lande1.jpg\",\"uploads\\/6861d8f7a0dbe_Nouvelle-Z\\u00e9lande2.jpg\",\"uploads\\/6861d8f7a0e56_Nouvelle-Z\\u00e9lande3.jpg\"]'),
(53, 'Sur les traces de la Renaissance, Florence', 'Europe', 'Italie', '2025-09-20', 'Nature', 'Berceau de la Renaissance, Florence regorge de trésors artistiques et architecturaux. La cathédrale Santa Maria del Fiore domine une ville où chaque rue raconte une histoire. Musées, palais, galeries d’art et marchés en plein air se côtoient dans une atmosphère à la fois élégante et vivante. Un incontournable pour les passionnés d’histoire, de gastronomie et de culture italienne.', 1, '[\"uploads\\/6861d97cdff7a_Italie1.jpg\",\"uploads\\/6861d97ce0195_Italie2.jpg\",\"uploads\\/6861d97ce02db_Italie3.jpg\"]'),
(54, 'Mystères et merveilles des pyramides, Le Caire', 'Afrique', 'Égypte', '2025-11-12', 'Désert', 'Ville millénaire posée sur les rives du Nil, Le Caire abrite l’un des trésors les plus fascinants de l’humanité : les pyramides de Gizeh. Face au désert, les imposants monuments de Khéops, Khéphren et Mykérinos racontent les récits de l’Égypte antique. Non loin de là, le Sphinx veille en silence depuis des millénaires. Entre musées, souks animés et balades sur le Nil, Le Caire est une immersion vivante dans l’histoire et la grandeur des civilisations anciennes.', 1, '[\"uploads\\/6861d9b4479c6_\\u00c9gypte1.jpg\",\"uploads\\/6861d9b447be0_\\u00c9gypte2.jpg\",\"uploads\\/6861d9b447d15_\\u00c9gypte3.jpg\",\"uploads\\/6861d9b447e72_\\u00c9gypte4.jpg\"]');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `voyages`
--
ALTER TABLE `voyages`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `voyages`
--
ALTER TABLE `voyages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
