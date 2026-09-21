<?php
// Gestion des langues (français / anglais) : détection, traductions et formats.
require_once __DIR__ . '/config.php';

/* Langue de la page en cours. Ordre de priorité :
   1. LANGUE_FORCEE (définie par les pages qui ne sont pas traduites, ex. l'espace admin)
   2. paramètre ?lang=fr|en (mémorisé dans un cookie)
   3. cookie « lang »
   4. en-tête Accept-Language du navigateur
   5. langue par défaut */
function langueCourante(bool $memoriser = true): string
{
    static $langue = null;
    if ($langue !== null) {
        return $langue;
    }

    if (defined('LANGUE_FORCEE') && in_array(LANGUE_FORCEE, LANGUES, true)) {
        return $langue = LANGUE_FORCEE;
    }

    $demandee = $_GET['lang'] ?? $_POST['lang'] ?? null;
    if (is_string($demandee) && in_array($demandee, LANGUES, true)) {
        if ($memoriser && !headers_sent()) {
            setcookie('lang', $demandee, ['expires' => time() + 365 * 86400, 'path' => '/', 'samesite' => 'Lax']);
        }
        return $langue = $demandee;
    }

    $cookie = $_COOKIE['lang'] ?? null;
    if (is_string($cookie) && in_array($cookie, LANGUES, true)) {
        return $langue = $cookie;
    }

    return $langue = langueDuNavigateur();
}

/* Choisit la langue préférée du navigateur parmi celles du site (en-tête Accept-Language). */
function langueDuNavigateur(): string
{
    $preferences = [];
    foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '') as $rang => $morceau) {
        $parties = explode(';', trim($morceau));
        $code = strtolower(substr(trim($parties[0]), 0, 2));
        $poids = 1.0;
        if (isset($parties[1]) && preg_match('/q=([0-9.]+)/', $parties[1], $m)) {
            $poids = (float)$m[1];
        }
        if (in_array($code, LANGUES, true)) {
            $preferences[] = [$poids, -$rang, $code];
        }
    }
    if (!$preferences) {
        return LANGUE_PAR_DEFAUT;
    }
    rsort($preferences);
    return $preferences[0][2];
}

function dictionnaire(string $langue): array
{
    static $cache = [];
    return $cache[$langue] ??= require __DIR__ . '/lang/' . $langue . '.php';
}

/* Traduit une clé. Les variables s'écrivent {nom} dans le texte ({site} est remplacé par le nom du site). Repli : français, puis la clé elle-même. */
function t(string $cle, array $variables = [], ?string $langue = null): string
{
    $langue ??= langueCourante();
    $texte = dictionnaire($langue)[$cle] ?? dictionnaire(LANGUE_PAR_DEFAUT)[$cle] ?? $cle;
    $texte = str_replace('{site}', NOM_SITE, $texte);
    foreach ($variables as $nom => $valeur) {
        $texte = str_replace('{' . $nom . '}', (string)$valeur, $texte);
    }
    return $texte;
}

/* Choisit entre « cle.one » et « cle.other » selon le nombre (règle du pluriel de la langue). */
function tn(string $cle, int $n, ?string $langue = null): string
{
    $langue ??= langueCourante();
    $singulier = $langue === 'fr' ? $n < 2 : $n === 1;
    return t($cle . ($singulier ? '.one' : '.other'), ['n' => $n], $langue);
}

/* Textes à transmettre au JavaScript : toutes les clés commençant par l'un des préfixes donnés. */
function textesPourJs(array $prefixes, ?string $langue = null): array
{
    $langue ??= langueCourante();
    $tous = array_merge(dictionnaire(LANGUE_PAR_DEFAUT), dictionnaire($langue));
    $sortie = [];
    foreach ($tous as $cle => $texte) {
        foreach ($prefixes as $prefixe) {
            if (str_starts_with($cle, $prefixe . '.')) {
                $sortie[$cle] = str_replace('{site}', NOM_SITE, $texte);
                break;
            }
        }
    }
    return $sortie;
}

/* ---------- Formats selon la langue ---------- */

/* « 1 890 $ » en français, « $1,890 » en anglais (les centimes ne s'affichent que s'il y en a). */
function formaterPrix($prix, ?string $langue = null): string
{
    $langue ??= langueCourante();
    $decimales = fmod((float)$prix, 1) === 0.0 ? 0 : 2;
    if ($langue === 'en') {
        return '$' . number_format((float)$prix, $decimales, '.', ',');
    }
    return number_format((float)$prix, $decimales, ',', "\u{00A0}") . "\u{00A0}$";
}

function formaterDuree(int $jours, ?string $langue = null): string
{
    return tn('duration', $jours, $langue);
}

/* « 5 juillet 2027 » ou « July 5, 2027 » */
function formaterDate(string $dateIso, ?string $langue = null): string
{
    static $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
                    'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $langue ??= langueCourante();
    $t = strtotime($dateIso);
    if ($t === false) {
        return $dateIso;
    }
    if ($langue === 'en') {
        return date('F j, Y', $t);
    }
    return (int)date('j', $t) . ' ' . $mois[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
}

/* ---------- Liens entre les versions linguistiques ---------- */

/* URL de la page en cours dans une autre langue (les autres paramètres sont conservés). */
function urlLangue(string $langue): string
{
    $parametres = $_GET;
    $parametres['lang'] = $langue;
    $chemin = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    return $chemin . '?' . http_build_query($parametres);
}

function urlAbsolue(string $cheminEtRequete): string
{
    $schema = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $schema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $cheminEtRequete;
}

/* Balises <link rel="alternate"> pour les moteurs de recherche (une par langue). */
function balisesLangues(): string
{
    $html = '';
    foreach (LANGUES as $l) {
        $html .= '  <link rel="alternate" hreflang="' . $l . '" href="' . htmlspecialchars(urlAbsolue(urlLangue($l)), ENT_QUOTES, 'UTF-8') . '">' . "\n";
    }
    $html .= '  <link rel="alternate" hreflang="x-default" href="' . htmlspecialchars(urlAbsolue(urlLangue(LANGUE_PAR_DEFAUT)), ENT_QUOTES, 'UTF-8') . '">' . "\n";
    return $html;
}
