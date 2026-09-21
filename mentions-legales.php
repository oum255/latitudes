<?php
require_once __DIR__ . '/includes/gabarit.php';
$langue = langueCourante();
$en = $langue === 'en';

// ---- Identité de l'exploitant : uniquement ce qui est renseigné dans includes/config.php ----
$editeur = [];
if (RAISON_SOCIALE !== '') {
    $editeur[] = ($en ? 'Publisher: ' : 'Éditeur : ') . RAISON_SOCIALE;
}
if (ADRESSE_POSTALE !== '') {
    $editeur[] = ($en ? 'Address: ' : 'Adresse : ') . ADRESSE_POSTALE;
}
if (TELEPHONE !== '') {
    $editeur[] = ($en ? 'Phone: ' : 'Téléphone : ') . TELEPHONE;
}
$editeur[] = ($en ? 'Email: ' : 'Courriel : ') . '{courriel}';
if (NUMERO_PERMIS !== '') {
    $editeur[] = $en
        ? 'Travel agent permit issued by the Office de la protection du consommateur (Québec): no. ' . NUMERO_PERMIS
        : 'Titulaire d’un permis d’agent de voyages délivré par l’Office de la protection du consommateur (Québec) : no ' . NUMERO_PERMIS;
}
if (HEBERGEUR !== '') {
    $editeur[] = ($en ? 'Hosting: ' : 'Hébergement : ') . HEBERGEUR;
}

$blocsEditeur = [['ul' => $editeur]];
if (RAISON_SOCIALE === '' && SITE_DEMO) {
    array_unshift($blocsEditeur, ['p' => $en
        ? 'This site is a demonstration project created as a personal project. It is not operated by a travel agency.'
        : 'Ce site est un projet de démonstration réalisé à titre personnel. Il n’est exploité par aucune agence de voyages.']);
} elseif (RAISON_SOCIALE === '') {
    array_unshift($blocsEditeur, ['p' => $en
        ? 'The operator’s details have not been configured yet (see includes/config.php).'
        : 'Les renseignements de l’exploitant ne sont pas encore configurés (voir includes/config.php).']);
}

$sections = $en ? [
    ['titre' => 'Publisher of the site', 'blocs' => $blocsEditeur],
    ['titre' => 'Intellectual property', 'blocs' => [
        ['p' => 'Unless stated otherwise, the texts and layout of this site belong to its operator.'],
        ['p' => 'The photographs of the destinations are published under free licences (Creative Commons or public domain). Each photo is credited on the page of the trip it illustrates. Any reuse must comply with the terms of these licences.'],
    ]],
    ['titre' => 'Accuracy of the information', 'blocs' => [
        ['p' => 'The information presented (prices, durations, availability, departure dates) is provided for guidance and may change. Only the quote we send you is binding.'],
    ]],
    ['titre' => 'Liability and links', 'blocs' => [
        ['p' => 'We do our best to keep the site accurate and available, but we cannot guarantee it will be free of errors or interruptions. We are not responsible for the content of external sites this site may link to.'],
    ]],
    ['titre' => 'Personal data', 'blocs' => [
        ['p' => 'The way we handle your personal information is described in our {confidentialite}.'],
    ]],
    ['titre' => 'Contact', 'blocs' => [
        ['p' => 'For any question about this site, write to us at {courriel}.'],
    ]],
] : [
    ['titre' => 'Éditeur du site', 'blocs' => $blocsEditeur],
    ['titre' => 'Propriété intellectuelle', 'blocs' => [
        ['p' => 'Sauf mention contraire, les textes et la mise en page de ce site appartiennent à son exploitant.'],
        ['p' => 'Les photographies des destinations sont publiées sous licences libres (Creative Commons ou domaine public). Chaque photo est créditée sur la fiche du voyage qu’elle illustre. Toute réutilisation doit respecter les conditions de ces licences.'],
    ]],
    ['titre' => 'Exactitude des informations', 'blocs' => [
        ['p' => 'Les informations présentées (prix, durées, disponibilités, dates de départ) sont fournies à titre indicatif et peuvent changer. Seul le devis que nous vous adressons fait foi.'],
    ]],
    ['titre' => 'Responsabilité et liens', 'blocs' => [
        ['p' => 'Nous faisons de notre mieux pour que le site soit exact et disponible, mais nous ne pouvons garantir qu’il soit exempt d’erreurs ou d’interruptions. Nous ne sommes pas responsables du contenu des sites externes vers lesquels ce site pourrait renvoyer.'],
    ]],
    ['titre' => 'Renseignements personnels', 'blocs' => [
        ['p' => 'La façon dont nous traitons vos renseignements personnels est décrite dans notre {confidentialite}.'],
    ]],
    ['titre' => 'Nous joindre', 'blocs' => [
        ['p' => 'Pour toute question au sujet de ce site, écrivez-nous à {courriel}.'],
    ]],
];

$titre = $en ? 'Legal notice' : 'Mentions légales';
$jetons = [
    'courriel' => lienCourriel(),
    'confidentialite' => '<a href="/confidentialite.php?lang=' . $langue . '">' . e($en ? 'privacy policy' : 'politique de confidentialité') . '</a>',
];

debutPagePublique($titre);
?>
    <h1><?= e($titre) ?></h1>
    <?php avisDemonstration($langue); ?>
    <?php rendreSections($sections, $jetons); ?>
    <p class="small texte-doux mt-4"><?= e(($en ? 'Last updated: ' : 'Dernière mise à jour : ') . formaterDate(DATE_MAJ_POLITIQUES, $langue)) ?></p>
<?php finPagePublique(); ?>
