<?php
// Réglages généraux du site.
//
// Pour un déploiement réel, ne modifiez pas ce fichier : créez includes/config.local.php (ignoré par git)
// et définissez-y les constantes voulues avec define('NOM', valeur). Le fichier local est chargé en premier.
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

/* Définit une constante seulement si elle n'a pas déjà été définie (par config.local.php). */
function reglage(string $nom, $valeur): void
{
    if (!defined($nom)) {
        define($nom, $valeur);
    }
}

reglage('NOM_SITE', 'Latitudes');
reglage('EMAIL_CONTACT', 'contact@latitudes.example');

// Langues disponibles ; la première est la langue par défaut.
reglage('LANGUES', ['fr', 'en']);
reglage('LANGUE_PAR_DEFAUT', 'fr');

// Les dates de départ sont comparées à la date du jour au Québec.
date_default_timezone_set('America/Toronto');

/* ---------- Renseignements sur l'exploitant du site ----------
   Ils alimentent les pages « À propos », « Mentions légales » et « Confidentialité ».
   Laisser une valeur vide pour ne rien afficher. À renseigner AVANT toute mise en ligne réelle. */

// Tant que le site est une démonstration (prix, disponibilités et dates fictifs), un avis le précise.
reglage('SITE_DEMO', true);
reglage('RAISON_SOCIALE', '');
reglage('ADRESSE_POSTALE', '');
reglage('TELEPHONE', '');
// Numéro du permis d'agent de voyages (Office de la protection du consommateur, au Québec)
reglage('NUMERO_PERMIS', '');
// Personne responsable de la protection des renseignements personnels (à défaut : le courriel de contact)
reglage('RESPONSABLE_VIE_PRIVEE', '');
reglage('HEBERGEUR', '');
// Date de dernière mise à jour des mentions légales et de la politique de confidentialité
reglage('DATE_MAJ_POLITIQUES', '2026-09-21');
