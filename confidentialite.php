<?php
require_once __DIR__ . '/includes/gabarit.php';
$langue = langueCourante();
$en = $langue === 'en';

// Personne responsable : celle qui est configurée, sinon le courriel de contact
$responsableFr = RESPONSABLE_VIE_PRIVEE !== ''
    ? 'La personne responsable de la protection des renseignements personnels est : ' . RESPONSABLE_VIE_PRIVEE . '. Vous pouvez la joindre à {courriel}.'
    : 'Pour toute question au sujet de vos renseignements personnels, écrivez-nous à {courriel}.';
$responsableEn = RESPONSABLE_VIE_PRIVEE !== ''
    ? 'The person in charge of protecting personal information is: ' . RESPONSABLE_VIE_PRIVEE . '. You can reach them at {courriel}.'
    : 'For any question about your personal information, write to us at {courriel}.';

$partageFr = HEBERGEUR !== ''
    ? 'Nous ne vendons ni ne louons vos renseignements. Ils ne sont accessibles qu’aux prestataires nécessaires au fonctionnement du site, notamment notre hébergeur (' . HEBERGEUR . ').'
    : 'Nous ne vendons ni ne louons vos renseignements. Ils ne sont accessibles qu’aux prestataires nécessaires au fonctionnement du site, notamment l’hébergeur du site.';
$partageEn = HEBERGEUR !== ''
    ? 'We do not sell or rent your information. It is only accessible to the providers needed to run the site, notably our host (' . HEBERGEUR . ').'
    : 'We do not sell or rent your information. It is only accessible to the providers needed to run the site, notably the site’s host.';

$sections = $en ? [
    ['titre' => 'Who is responsible', 'blocs' => [['p' => $responsableEn]]],
    ['titre' => 'What we collect and why', 'blocs' => [
        ['p' => 'We only collect the information you give us by filling in one of our forms.'],
        ['table' => [
            ['Where', 'Information', 'What we use it for'],
            ['Quote request', 'Name, email, phone (optional), number of travellers, message (optional), language', 'Answering your request'],
            ['Review', 'Display name, email (never published), rating, comment, language', 'Publishing your review after it has been checked, and preventing abuse'],
            ['Newsletter', 'Email, language, date of your consent', 'Sending you the newsletter you asked for'],
            ['Account', 'Name, email, password (stored in an irreversible, hashed form)', 'Managing your account'],
        ]],
    ]],
    ['titre' => 'Your consent', 'blocs' => [
        ['p' => 'By submitting a form you agree that we use the information for the purpose shown above. For the newsletter, you must tick a box (unticked by default). You can withdraw your consent at any time.'],
    ]],
    ['titre' => 'Cookies and tracking', 'blocs' => [
        ['p' => 'This site uses no advertising and no audience-measurement tool, and does not follow you from one site to another. It only sets two technical cookies:'],
        ['ul' => [
            'lang: remembers your language choice (one year);',
            'PHPSESSID: keeps you signed in when you log in (deleted when you close your browser).',
        ]],
        ['p' => 'These cookies are necessary for the site to work, so no additional consent is required.'],
        ['p' => 'The Bootstrap style sheets and scripts are loaded from the jsDelivr network. As a result, your IP address and browser details are sent to that service when the page loads.'],
    ]],
    ['titre' => 'Who we share it with', 'blocs' => [['p' => $partageEn]]],
    ['titre' => 'How long we keep it', 'blocs' => [
        ['p' => 'For as long as needed for the purposes above. You can ask us to delete your information at any time (see your rights below).'],
    ]],
    ['titre' => 'Your rights', 'blocs' => [
        ['p' => 'You can ask us to:'],
        ['ul' => [
            'tell you what information we hold about you;',
            'correct it if it is inaccurate;',
            'delete it;',
            'stop sending you the newsletter (every newsletter subscription can also be cancelled through its unsubscribe link).',
        ]],
        ['p' => 'Write to us at {courriel}. We reply within 30 days.'],
    ]],
    ['titre' => 'Security', 'blocs' => [
        ['p' => 'We take reasonable measures to protect your information: passwords are stored hashed, and access to the management area is restricted to authorised accounts.'],
    ]],
    ['titre' => 'Complaints', 'blocs' => [
        ['p' => 'If you believe your rights are not being respected, write to us. You can also contact the personal-data protection authority of your territory (in Québec: the Commission d’accès à l’information).'],
    ]],
] : [
    ['titre' => 'Qui est responsable', 'blocs' => [['p' => $responsableFr]]],
    ['titre' => 'Ce que nous recueillons et pourquoi', 'blocs' => [
        ['p' => 'Nous ne recueillons que les renseignements que vous nous transmettez en remplissant l’un de nos formulaires.'],
        ['table' => [
            ['Contexte', 'Renseignements', 'Utilisation'],
            ['Demande de devis', 'Nom, courriel, téléphone (facultatif), nombre de voyageurs, message (facultatif), langue', 'Répondre à votre demande'],
            ['Avis', 'Nom affiché, courriel (jamais publié), note, commentaire, langue', 'Publier votre avis après relecture et limiter les abus'],
            ['Infolettre', 'Courriel, langue, date de votre consentement', 'Vous envoyer l’infolettre que vous avez demandée'],
            ['Compte', 'Nom, courriel, mot de passe (conservé sous une forme hachée, irréversible)', 'Gérer votre compte'],
        ]],
    ]],
    ['titre' => 'Votre consentement', 'blocs' => [
        ['p' => 'En envoyant un formulaire, vous acceptez que nous utilisions les renseignements aux fins indiquées ci-dessus. Pour l’infolettre, vous devez cocher une case (décochée par défaut). Vous pouvez retirer votre consentement en tout temps.'],
    ]],
    ['titre' => 'Témoins (cookies) et suivi', 'blocs' => [
        ['p' => 'Ce site n’utilise ni publicité ni outil de mesure d’audience et ne vous suit pas d’un site à l’autre. Il ne dépose que deux témoins techniques :'],
        ['ul' => [
            'lang : mémorise votre choix de langue (un an) ;',
            'PHPSESSID : garde votre session ouverte lorsque vous vous connectez (supprimé à la fermeture du navigateur).',
        ]],
        ['p' => 'Ces témoins sont nécessaires au fonctionnement du site : aucun consentement supplémentaire n’est requis.'],
        ['p' => 'Les feuilles de style et scripts Bootstrap sont chargés depuis le réseau jsDelivr. Votre adresse IP et les informations de votre navigateur sont donc transmises à ce service lors du chargement de la page.'],
    ]],
    ['titre' => 'Avec qui nous les partageons', 'blocs' => [['p' => $partageFr]]],
    ['titre' => 'Combien de temps nous les conservons', 'blocs' => [
        ['p' => 'Le temps nécessaire aux fins décrites ci-dessus. Vous pouvez demander la suppression de vos renseignements en tout temps (voir vos droits ci-dessous).'],
    ]],
    ['titre' => 'Vos droits', 'blocs' => [
        ['p' => 'Vous pouvez nous demander :'],
        ['ul' => [
            'de savoir quels renseignements nous détenons à votre sujet ;',
            'de les corriger s’ils sont inexacts ;',
            'de les supprimer ;',
            'de cesser de vous envoyer l’infolettre (chaque abonnement peut aussi être annulé grâce à son lien de désabonnement).',
        ]],
        ['p' => 'Écrivez-nous à {courriel}. Nous répondons dans un délai maximal de 30 jours.'],
    ]],
    ['titre' => 'Sécurité', 'blocs' => [
        ['p' => 'Nous prenons des mesures raisonnables pour protéger vos renseignements : les mots de passe sont conservés sous forme hachée et l’accès à l’espace de gestion est réservé aux comptes autorisés.'],
    ]],
    ['titre' => 'Plaintes', 'blocs' => [
        ['p' => 'Si vous estimez que vos droits ne sont pas respectés, écrivez-nous. Vous pouvez aussi vous adresser à l’autorité de protection des renseignements personnels de votre territoire (au Québec : la Commission d’accès à l’information).'],
    ]],
];

$titre = $en ? 'Privacy policy' : 'Politique de confidentialité';
debutPagePublique($titre);
?>
    <h1><?= e($titre) ?></h1>
    <?php avisDemonstration($langue); ?>
    <?php rendreSections($sections, ['courriel' => lienCourriel()]); ?>
    <p class="small texte-doux mt-4"><?= e(($en ? 'Last updated: ' : 'Dernière mise à jour : ') . formaterDate(DATE_MAJ_POLITIQUES, $langue)) ?></p>
<?php finPagePublique(); ?>
