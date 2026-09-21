<?php
// Référentiel du catalogue : continents, catégories et pays, avec leurs traductions.
// En base, on stocke toujours le nom FRANÇAIS ; l'anglais n'est qu'un affichage.
require_once __DIR__ . '/i18n.php';

const CONTINENTS = [
    'Europe' => 'Europe',
    'Amérique' => 'Americas',
    'Asie' => 'Asia',
    'Afrique' => 'Africa',
    'Océanie' => 'Oceania',
];

const CATEGORIES = [
    'Nature' => 'Nature',
    'Plage' => 'Beach',
    'Montagne' => 'Mountains',
    'Safari' => 'Safari',
    'Île tropicale' => 'Tropical island',
    'Désert' => 'Desert',
    'Culture' => 'Culture',
];

/* Tous les pays : nom français => [code ISO, continent, nom anglais] */
function listePays(): array
{
    static $liste = null;
    return $liste ??= require __DIR__ . '/pays.php';
}

function paysValide(string $nomFrancais): bool
{
    return isset(listePays()[$nomFrancais]);
}

function continentDuPays(string $nomFrancais): ?string
{
    return listePays()[$nomFrancais][1] ?? null;
}

function categorieValide(string $categorie): bool
{
    return isset(CATEGORIES[$categorie]);
}

function nomPays(?string $nomFrancais, ?string $langue = null): string
{
    $langue ??= langueCourante();
    $nomFrancais = (string)$nomFrancais;
    return $langue === 'en' ? (listePays()[$nomFrancais][2] ?? $nomFrancais) : $nomFrancais;
}

function nomContinent(?string $nomFrancais, ?string $langue = null): string
{
    $langue ??= langueCourante();
    $nomFrancais = (string)$nomFrancais;
    return $langue === 'en' ? (CONTINENTS[$nomFrancais] ?? $nomFrancais) : $nomFrancais;
}

function nomCategorie(?string $nomFrancais, ?string $langue = null): string
{
    $langue ??= langueCourante();
    $nomFrancais = (string)$nomFrancais;
    return $langue === 'en' ? (CATEGORIES[$nomFrancais] ?? $nomFrancais) : $nomFrancais;
}

/* Titre ou description dans la langue demandée (repli sur le français si la traduction est vide). */
function champLocalise(array $voyage, string $champ, ?string $langue = null): string
{
    $langue ??= langueCourante();
    if ($langue === 'en' && trim((string)($voyage[$champ . '_en'] ?? '')) !== '') {
        return (string)$voyage[$champ . '_en'];
    }
    return (string)($voyage[$champ] ?? '');
}

/* Ajoute à une ligne de la table `voyages` les valeurs prêtes à afficher dans la langue demandée. */
function voyageAffichage(array $voyage, string $langue): array
{
    $voyage['titre_affiche'] = champLocalise($voyage, 'titre', $langue);
    $voyage['description_affiche'] = champLocalise($voyage, 'description', $langue);
    $voyage['pays_affiche'] = nomPays($voyage['pays'] ?? '', $langue);
    $voyage['continent_affiche'] = nomContinent($voyage['continent'] ?? '', $langue);
    $voyage['categorie_affiche'] = nomCategorie($voyage['categorie'] ?? '', $langue);
    return $voyage;
}

/* <option> de tous les pays, groupés par continent et triés selon la langue.
   Chaque option porte data-continent (utilisé par l'aperçu du formulaire admin). */
function optionsPays(?string $langue = null, string $libelleVide = ''): string
{
    $langue ??= langueCourante();
    $parContinent = [];
    foreach (listePays() as $nomFr => [$code, $continent, $nomEn]) {
        $parContinent[$continent][] = [$nomFr, $langue === 'en' ? $nomEn : $nomFr];
    }
    $collator = class_exists('Collator') ? new Collator($langue) : null;

    $html = '<option value="">' . htmlspecialchars($libelleVide, ENT_QUOTES, 'UTF-8') . '</option>';
    foreach (array_keys(CONTINENTS) as $continent) {
        $liste = $parContinent[$continent] ?? [];
        usort($liste, fn($a, $b) => $collator ? $collator->compare($a[1], $b[1]) : strcmp($a[1], $b[1]));
        $html .= '<optgroup label="' . htmlspecialchars(nomContinent($continent, $langue), ENT_QUOTES, 'UTF-8') . '">';
        foreach ($liste as [$nomFr, $libelle]) {
            $html .= '<option value="' . htmlspecialchars($nomFr, ENT_QUOTES, 'UTF-8') . '" data-continent="'
                . htmlspecialchars($continent, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars($libelle, ENT_QUOTES, 'UTF-8') . '</option>';
        }
        $html .= '</optgroup>';
    }
    return $html;
}

/* <option> des catégories dans la langue demandée. */
function optionsCategories(?string $langue = null, string $libelleVide = ''): string
{
    $langue ??= langueCourante();
    $html = '<option value="">' . htmlspecialchars($libelleVide, ENT_QUOTES, 'UTF-8') . '</option>';
    foreach (array_keys(CATEGORIES) as $categorie) {
        $html .= '<option value="' . htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars(nomCategorie($categorie, $langue), ENT_QUOTES, 'UTF-8') . '</option>';
    }
    return $html;
}
