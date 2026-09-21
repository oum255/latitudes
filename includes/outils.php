<?php
// Fonctions utilitaires partagées par les pages rendues côté serveur.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/referentiel.php';

/* Échappe une valeur avant de l'afficher dans du HTML. */
function e($valeur): string
{
    return htmlspecialchars((string)$valeur, ENT_QUOTES, 'UTF-8');
}

/* Décode la colonne `image` (tableau JSON de chemins) en liste de chemins. */
function decoderImages(?string $json): array
{
    if (!$json) {
        return [];
    }
    $liste = json_decode($json, true);
    if (is_array($liste)) {
        return array_values(array_filter($liste, 'is_string'));
    }
    return [$json];
}

/* Construit l'URL publique d'une image stockée sous la forme « uploads/fichier.jpg ». */
function urlImage(string $chemin): string
{
    return '/' . implode('/', array_map('rawurlencode', explode('/', $chemin)));
}

/* Vrai si la date de départ est aujourd'hui ou dans le futur. */
function departAVenir(?string $dateIso): bool
{
    return $dateIso !== null && $dateIso >= date('Y-m-d');
}

/* Crédits des photos libres de droits : chemin => [auteur, licence, licence_url, source_url]. */
function creditsPhotos(): array
{
    static $credits = null;
    if ($credits === null) {
        $fichier = __DIR__ . '/../data/credits_photos.json';
        $credits = is_file($fichier) ? (json_decode(file_get_contents($fichier), true) ?: []) : [];
    }
    return $credits;
}

/* Convertit un horodatage de la base (UTC) en heure locale du site (fuseau défini dans config.php). */
function heureLocale(string $horodatage): DateTimeImmutable
{
    return (new DateTimeImmutable($horodatage, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone(date_default_timezone_get()));
}

/* « 5 juillet 2027 » ou « July 5, 2027 » d'après un horodatage de la base. */
function dateHeureLocale(string $horodatage, ?string $langue = null): string
{
    return formaterDate(heureLocale($horodatage)->format('Y-m-d'), $langue);
}

/* Note formatée : « 4,7 » en français, « 4.7 » en anglais. */
function formaterNote($note, ?string $langue = null): string
{
    $langue ??= langueCourante();
    return number_format((float)$note, 1, $langue === 'en' ? '.' : ',', '');
}

/* Étoiles (HTML) pour une note de 0 à 5, avec un texte alternatif pour les lecteurs d'écran. */
function htmlEtoiles(float $note, ?string $langue = null): string
{
    $pleines = (int)round($note);
    $html = '<span class="etoiles-affichage" role="img" aria-label="'
        . e(t('avis.stars', ['n' => formaterNote($note, $langue)], $langue)) . '">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span aria-hidden="true" class="' . ($i <= $pleines ? 'pleine' : 'vide') . '">★</span>';
    }
    return $html . '</span>';
}
