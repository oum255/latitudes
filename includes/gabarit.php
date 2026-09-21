<?php
// Gabarit des pages publiques « de texte » (à propos, mentions légales, confidentialité, désabonnement).
// Usage : debutPagePublique($titre, $description); … contenu … finPagePublique();
require_once __DIR__ . '/outils.php';

function debutPagePublique(string $titre, string $description = '', bool $indexable = true): void
{
    $langue = langueCourante();
    ?>
<!DOCTYPE html>
<html lang="<?= $langue ?>" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titre) ?> — <?= e(NOM_SITE) ?></title>
<?php if ($description !== ''): ?>
  <meta name="description" content="<?= e($description) ?>">
<?php endif; ?>
<?php if ($indexable): ?>
<?= balisesLangues() ?>
<?php else: ?>
  <meta name="robots" content="noindex">
<?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css" />
  <link rel="stylesheet" href="/css/public.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/nav_publique.php'; ?>

<main class="page-texte py-4">
  <div class="container">
    <p class="mb-3"><a href="/index.php?lang=<?= $langue ?>" class="text-white"><?= e(t('fiche.back')) ?></a></p>
<?php
}

function finPagePublique(): void
{
    ?>
  </div>
</main>

<?php include __DIR__ . '/pied_public.php'; ?>

</body>
</html>
<?php
}

/* Encadré affiché tant que le site est une démonstration (voir SITE_DEMO dans includes/config.php). */
function avisDemonstration(string $langue): void
{
    if (!SITE_DEMO) {
        return;
    }
    $texte = $langue === 'en'
        ? NOM_SITE . ' is a demonstration project: the trips, prices, availability and dates shown are fictitious, and nothing can be booked. Photos come from Wikimedia Commons (see the credits on each trip page).'
        : NOM_SITE . ' est un projet de démonstration : les voyages, prix, disponibilités et dates présentés sont fictifs et aucune réservation n’est possible. Les photos proviennent de Wikimedia Commons (crédits sur la fiche de chaque voyage).';
    echo '<div class="encadre-demo" role="note">' . e($texte) . '</div>';
}

/* Échappe un texte puis remplace des jetons {cle} par du HTML de confiance (ex. un lien). */
function texteRiche(string $texte, array $jetons = []): string
{
    $html = e($texte);
    foreach ($jetons as $cle => $extrait) {
        $html = str_replace('{' . $cle . '}', $extrait, $html);
    }
    return $html;
}

/* Affiche des sections de texte. Chaque section : ['titre' => …, 'blocs' => [ ['p' => …] | ['ul' => […]] | ['table' => [[en-têtes], [ligne], …]] ]] */
function rendreSections(array $sections, array $jetons = []): void
{
    foreach ($sections as $section) {
        echo '<section class="section-texte"><h2 class="h5">' . e($section['titre']) . '</h2>';
        foreach ($section['blocs'] as $bloc) {
            if (isset($bloc['p'])) {
                echo '<p>' . texteRiche($bloc['p'], $jetons) . '</p>';
            } elseif (isset($bloc['ul'])) {
                echo '<ul>';
                foreach ($bloc['ul'] as $item) {
                    echo '<li>' . texteRiche($item, $jetons) . '</li>';
                }
                echo '</ul>';
            } elseif (isset($bloc['table'])) {
                echo '<div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead><tr>';
                foreach (array_shift($bloc['table']) as $entete) {
                    echo '<th scope="col">' . e($entete) . '</th>';
                }
                echo '</tr></thead><tbody>';
                foreach ($bloc['table'] as $ligne) {
                    echo '<tr>';
                    foreach ($ligne as $cellule) {
                        echo '<td>' . texteRiche($cellule, $jetons) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody></table></div>';
            }
        }
        echo '</section>';
    }
}

/* Lien mailto: vers l'adresse de contact. */
function lienCourriel(): string
{
    return '<a href="mailto:' . e(EMAIL_CONTACT) . '">' . e(EMAIL_CONTACT) . '</a>';
}
