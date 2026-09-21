<?php
require_once __DIR__ . '/includes/gabarit.php';
$langue = langueCourante();

$c = $langue === 'en' ? [
    'titre' => 'About',
    'lead' => NOM_SITE . ' is a travel catalogue designed to help you find, compare and request a quote for your next getaway, in French or in English.',
    'approche' => 'Our approach',
    'piliers' => [
        ['Clear information', 'For every trip: duration, price per person, departure dates and available seats, with photos and a detailed description. You know what to expect before you write to us.'],
        ['A free quote', 'Requesting a quote is free and carries no obligation. Tell us your dates, the number of travellers and your questions; we reply to the address you give us.'],
        ['Reviewed feedback', 'Travellers can share their opinion on every destination. Each review is read through before it is published.'],
    ],
    'comment' => 'How it works',
    'etapes' => [
        ['Explore.', 'Browse the catalogue and filter by continent, country or theme, or search for a destination directly.'],
        ['Request a quote.', 'From a trip page, send us your request with just a few fields.'],
        ['We reply.', 'We get back to you by email with a suitable proposal.'],
        ['You decide.', 'You confirm your trip, or not, directly with us.'],
    ],
    'joindre' => 'Get in touch',
    'courriel' => 'Email',
    'telephone' => 'Phone',
    'adresse' => 'Address',
    'voir' => 'See the destinations',
] : [
    'titre' => 'À propos',
    'lead' => NOM_SITE . ' est un catalogue de voyages conçu pour vous aider à trouver, comparer et demander un devis pour votre prochain départ, en français comme en anglais.',
    'approche' => 'Notre approche',
    'piliers' => [
        ['Des informations claires', 'Pour chaque voyage : durée, prix par personne, dates de départ et places disponibles, avec des photos et une description détaillée. Vous savez à quoi vous attendre avant de nous écrire.'],
        ['Un devis gratuit', 'Demander un devis est gratuit et sans engagement de votre part. Indiquez vos dates, le nombre de voyageurs et vos questions ; nous répondons à l’adresse que vous nous donnez.'],
        ['Des avis relus', 'Les voyageurs peuvent partager leur opinion sur chaque destination. Chaque avis est relu avant d’être publié.'],
    ],
    'comment' => 'Comment ça marche',
    'etapes' => [
        ['Explorez.', 'Parcourez le catalogue et filtrez par continent, pays ou thème, ou cherchez directement une destination.'],
        ['Demandez un devis.', 'Depuis la fiche d’un voyage, envoyez-nous votre demande en quelques champs.'],
        ['Nous vous répondons.', 'Nous revenons vers vous par courriel avec une proposition adaptée.'],
        ['Vous décidez.', 'Vous confirmez, ou non, votre voyage directement avec nous.'],
    ],
    'joindre' => 'Nous joindre',
    'courriel' => 'Courriel',
    'telephone' => 'Téléphone',
    'adresse' => 'Adresse',
    'voir' => 'Voir les destinations',
];

$deuxPoints = $langue === 'en' ? ': ' : ' : ';
debutPagePublique($c['titre'], $c['lead']);
?>
    <h1><?= e($c['titre']) ?></h1>
    <p class="lead"><?= e($c['lead']) ?></p>
    <?php avisDemonstration($langue); ?>

    <h2 class="h4 mt-4"><?= e($c['approche']) ?></h2>
    <div class="row g-3 mt-1">
<?php foreach ($c['piliers'] as [$titre, $texte]): ?>
      <div class="col-md-4">
        <div class="pilier h-100">
          <h3 class="h6"><?= e($titre) ?></h3>
          <p class="mb-0"><?= e($texte) ?></p>
        </div>
      </div>
<?php endforeach; ?>
    </div>

    <h2 class="h4 mt-5"><?= e($c['comment']) ?></h2>
    <ol class="etapes">
<?php foreach ($c['etapes'] as [$titre, $texte]): ?>
      <li><strong><?= e($titre) ?></strong> <?= e($texte) ?></li>
<?php endforeach; ?>
    </ol>

    <h2 class="h4 mt-5"><?= e($c['joindre']) ?></h2>
    <ul class="list-unstyled">
      <li><?= e($c['courriel']) . $deuxPoints ?><?= lienCourriel() ?></li>
<?php if (TELEPHONE !== ''): ?>
      <li><?= e($c['telephone']) . $deuxPoints ?><?= e(TELEPHONE) ?></li>
<?php endif; ?>
<?php if (ADRESSE_POSTALE !== ''): ?>
      <li><?= e($c['adresse']) . $deuxPoints ?><?= e(ADRESSE_POSTALE) ?></li>
<?php endif; ?>
    </ul>
    <a href="/index.php?lang=<?= $langue ?>" class="btn btn-cta"><?= e($c['voir']) ?></a>
<?php finPagePublique(); ?>
