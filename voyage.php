<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/outils.php';

$langue = langueCourante();
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM voyages WHERE id = ?');
$stmt->execute([$id]);
$voyage = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voyage) {
    http_response_code(404);
}

$images = $voyage ? decoderImages($voyage['image']) : [];
$complet = $voyage && $voyage['places_disponibles'] !== null && (int)$voyage['places_disponibles'] === 0;

// Valeurs affichées dans la langue du visiteur
$titre = $voyage ? champLocalise($voyage, 'titre', $langue) : '';
$description = $voyage ? champLocalise($voyage, 'description', $langue) : '';
$pays = $voyage ? nomPays($voyage['pays'], $langue) : '';
$continent = $voyage ? nomContinent($voyage['continent'], $langue) : '';
$categorie = $voyage ? nomCategorie($voyage['categorie'], $langue) : '';

// Crédits des photos de ce voyage (photos libres de droits)
$credits = [];
foreach ($images as $chemin) {
    if (isset(creditsPhotos()[$chemin])) {
        $credits[] = creditsPhotos()[$chemin];
    }
}

// Métadonnées pour les moteurs de recherche et l'aperçu lors d'un partage
$titrePage = $voyage ? $titre . ' — ' . NOM_SITE : t('fiche.not_found_title') . ' — ' . NOM_SITE;
$descriptionMeta = $voyage ? mb_substr(trim(preg_replace('/\s+/', ' ', $description)), 0, 160) : '';
$urlImageAbsolue = $images ? urlAbsolue(urlImage($images[0])) : '';

// Avis publiés (modérés) et note moyenne
$avisPublies = [];
$nbAvis = 0;
$noteMoyenne = null;
if ($voyage) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS n, AVG(note) AS moyenne FROM avis WHERE id_voyage = ? AND statut = 'publie'");
    $stmt->execute([$id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    $nbAvis = (int)$resume['n'];
    $noteMoyenne = $nbAvis > 0 ? (float)$resume['moyenne'] : null;

    $stmt = $pdo->prepare("SELECT nom, note, commentaire, langue, created_at FROM avis WHERE id_voyage = ? AND statut = 'publie' ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$id]);
    $avisPublies = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$configJs = ['lang' => $langue, 'textes' => textesPourJs(['devis', 'avis'], $langue)];
?>
<!DOCTYPE html>
<html lang="<?= $langue ?>" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titrePage) ?></title>
<?php if ($voyage): ?>
  <meta name="description" content="<?= e($descriptionMeta) ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= e($titre) ?>">
  <meta property="og:description" content="<?= e($descriptionMeta) ?>">
  <meta property="og:locale" content="<?= $langue === 'en' ? 'en_CA' : 'fr_CA' ?>">
<?php if ($urlImageAbsolue): ?>
  <meta property="og:image" content="<?= e($urlImageAbsolue) ?>">
<?php endif; ?>
<?= balisesLangues() ?>
<?php else: ?>
  <meta name="robots" content="noindex">
<?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css" />
  <link rel="stylesheet" href="/css/public.css" />
  <script>window.LATITUDES = <?= json_encode($configJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="/js/commun.js" defer></script>
  <script src="/js/voyage.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/includes/nav_publique.php'; ?>

<main class="fiche py-4">
  <div class="container">
    <p class="mb-3"><a href="/index.php?lang=<?= $langue ?>" class="text-white"><?= e(t('fiche.back')) ?></a></p>

<?php if (!$voyage): ?>
    <div class="text-center py-5">
      <h1><?= e(t('fiche.not_found_title')) ?></h1>
      <p class="texte-doux"><?= e(t('fiche.not_found_text')) ?></p>
      <a href="/index.php?lang=<?= $langue ?>" class="btn btn-cta"><?= e(t('fiche.see_all')) ?></a>
    </div>
<?php else: ?>
    <div class="row g-4">

      <!-- Colonne gauche : galerie + description -->
      <div class="col-lg-7">
<?php if ($images): ?>
        <button type="button" class="p-0 border-0 bg-transparent w-100 d-block" id="galerie-ouvrir" aria-label="<?= e(t('fiche.enlarge')) ?>">
          <img id="galerie-principale" class="fiche-galerie-principale" src="<?= e(urlImage($images[0])) ?>" alt="<?= e($titre) ?>">
        </button>
<?php if (count($images) > 1): ?>
        <div class="fiche-miniatures" id="galerie-miniatures">
<?php foreach ($images as $i => $chemin): ?>
          <button type="button" data-index="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="<?= e(t('fiche.view_photo', ['n' => $i + 1])) ?>">
            <img src="<?= e(urlImage($chemin)) ?>" alt="" loading="lazy">
          </button>
<?php endforeach; ?>
        </div>
<?php endif; ?>
<?php else: ?>
        <img class="fiche-galerie-principale" style="cursor: default;" src="/img/apercu.jpg" alt="<?= e(t('fiche.no_photo')) ?>">
<?php endif; ?>

<?php if ($credits): ?>
        <details class="credits-photos mt-2">
          <summary><?= e(t('fiche.photo_credits')) ?></summary>
          <ul>
<?php foreach ($credits as $c): ?>
            <li>
              <?= e(t('fiche.photo_by', ['author' => $c['auteur'] ?: 'Wikimedia Commons'])) ?> —
              <a href="<?= e($c['licence_url']) ?>" target="_blank" rel="noopener"><?= e($c['licence']) ?></a>
              (<a href="<?= e($c['source_url']) ?>" target="_blank" rel="noopener"><?= e(t('fiche.source')) ?></a>)
            </li>
<?php endforeach; ?>
          </ul>
          <p class="mb-0"><?= e(t('fiche.photo_resized')) ?></p>
        </details>
<?php endif; ?>

        <h2 class="h4 mt-4"><?= e(t('fiche.about')) ?></h2>
        <p class="fiche-description"><?= e($description) ?></p>

        <!-- Avis des voyageurs -->
        <section id="avis" class="avis mt-5">
          <h2 class="h4"><?= e(t('avis.title')) ?></h2>
<?php if ($noteMoyenne !== null): ?>
          <p class="mb-3">
            <?= htmlEtoiles($noteMoyenne) ?>
            <strong><?= e(t('avis.average', ['note' => formaterNote($noteMoyenne)])) ?></strong>
            <span class="texte-doux">· <?= e(tn('avis.count', $nbAvis)) ?></span>
          </p>
          <ul class="liste-avis list-unstyled">
<?php foreach ($avisPublies as $a): ?>
            <li>
              <div class="d-flex flex-wrap align-items-center gap-2">
                <?= htmlEtoiles((float)$a['note']) ?>
                <strong><?= e($a['nom']) ?></strong>
                <span class="texte-doux small"><?= e(dateHeureLocale($a['created_at'])) ?></span>
              </div>
              <p class="mb-0 mt-1" lang="<?= e($a['langue']) ?>"><?= e($a['commentaire']) ?></p>
            </li>
<?php endforeach; ?>
          </ul>
<?php else: ?>
          <p class="texte-doux"><?= e(t('avis.none')) ?></p>
<?php endif; ?>

          <div class="card carte-devis p-3 mt-3">
            <h3 class="h5 mb-3"><?= e(t('avis.leave')) ?></h3>
            <form id="form-avis" action="/php/avis.php" method="post">
              <input type="hidden" name="id_voyage" value="<?= (int)$voyage['id'] ?>">
              <input type="hidden" name="lang" value="<?= $langue ?>">
              <div class="champ-piege" aria-hidden="true">
                <label>Do not fill in this field <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label>
              </div>

              <fieldset class="mb-3">
                <legend class="etiquette-legende"><?= e(t('avis.rating')) ?></legend>
                <div class="etoiles">
<?php for ($i = 5; $i >= 1; $i--): ?>
                  <input type="radio" name="note" id="avis-note-<?= $i ?>" value="<?= $i ?>" required>
                  <label for="avis-note-<?= $i ?>" title="<?= e(t('avis.stars', ['n' => $i])) ?>"><span class="visually-hidden"><?= e(t('avis.stars', ['n' => $i])) ?></span>★</label>
<?php endfor; ?>
                </div>
              </fieldset>

              <div class="row g-3 mb-3">
                <div class="col-sm-6">
                  <label for="avis-nom"><?= e(t('avis.name')) ?></label>
                  <input type="text" class="form-control" id="avis-nom" name="nom" maxlength="60" autocomplete="nickname" required>
                </div>
                <div class="col-sm-6">
                  <label for="avis-email"><?= e(t('avis.email')) ?></label>
                  <input type="email" class="form-control" id="avis-email" name="email" maxlength="255" autocomplete="email" required aria-describedby="avis-email-aide">
                  <div id="avis-email-aide" class="form-text"><?= e(t('avis.email_hint')) ?></div>
                </div>
              </div>
              <div class="mb-3">
                <label for="avis-commentaire"><?= e(t('avis.comment')) ?></label>
                <textarea class="form-control" id="avis-commentaire" name="commentaire" rows="4" minlength="10" maxlength="1000" placeholder="<?= e(t('avis.comment_placeholder')) ?>" required></textarea>
              </div>

              <div id="avis-erreur" class="alert alert-danger d-none" role="alert"></div>
              <button type="submit" class="btn btn-cta" id="avis-envoyer"><?= e(t('avis.send')) ?></button>
              <p class="mention mt-2 mb-0"><?= e(t('avis.moderation')) ?>
                <a href="/confidentialite.php?lang=<?= $langue ?>"><?= e(t('devis.privacy_link')) ?></a></p>
            </form>
            <div id="avis-succes" class="alert alert-success d-none mb-0" role="status"><?= e(t('avis.success')) ?></div>
          </div>
        </section>
      </div>

      <!-- Colonne droite : résumé + demande de devis -->
      <div class="col-lg-5">
        <h1><?= e($titre) ?></h1>
        <p class="texte-doux mb-3">
          <?= e($categorie ?: t('card.no_category')) ?> ·
          <?= e($continent) ?><?= $pays ? ' – ' . e($pays) : '' ?>
        </p>

<?php if ($voyage['prix'] !== null): ?>
        <div class="fiche-prix"><?= e(formaterPrix($voyage['prix'])) ?> <small><?= e(t('card.per_person')) ?></small></div>
<?php else: ?>
        <div class="fiche-prix"><small><?= e(t('card.on_request')) ?></small></div>
<?php endif; ?>

<?php if ($noteMoyenne !== null): ?>
        <p class="mt-2 mb-0">
          <a href="#avis" class="lien-note"><?= htmlEtoiles($noteMoyenne) ?>
            <strong><?= e(formaterNote($noteMoyenne)) ?></strong>
            <span class="texte-doux">(<?= e(tn('avis.count', $nbAvis)) ?>)</span></a>
        </p>
<?php endif; ?>

        <ul class="fiche-infos">
<?php if ($voyage['duree_jours'] !== null): ?>
          <li><span><?= e(t('fiche.duration')) ?></span><span><?= e(formaterDuree((int)$voyage['duree_jours'])) ?></span></li>
<?php endif; ?>
          <li><span><?= e(t('fiche.departure')) ?></span><span><?= departAVenir($voyage['date_depart']) ? e(formaterDate($voyage['date_depart'])) : e(t('fiche.dates_tbc')) ?></span></li>
<?php if ($voyage['places_disponibles'] !== null): ?>
          <li><span><?= e(t('fiche.availability')) ?></span><span>
<?php if ($complet): ?>
            <span class="badge badge-complet"><?= e(t('card.full')) ?></span> <?= e(t('fiche.waitlist_open')) ?>
<?php elseif ((int)$voyage['places_disponibles'] <= 5): ?>
            <?= e(tn('fiche.only_left', (int)$voyage['places_disponibles'])) ?>
<?php else: ?>
            <?= e(t('fiche.places', ['n' => (int)$voyage['places_disponibles']])) ?>
<?php endif; ?>
          </span></li>
<?php endif; ?>
        </ul>

        <div class="card carte-devis mt-4 p-3">
          <h2 class="h5 mb-3"><?= e($complet ? t('devis.title_waitlist') : t('devis.title')) ?></h2>

          <form id="form-devis" action="/php/demande.php" method="post">
            <input type="hidden" name="id_voyage" value="<?= (int)$voyage['id'] ?>">
            <input type="hidden" name="lang" value="<?= $langue ?>">
            <!-- Champ piège : invisible pour les humains, rempli par les robots -->
            <div class="champ-piege" aria-hidden="true">
              <label>Do not fill in this field <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="mb-3">
              <label for="devis-nom"><?= e(t('devis.name')) ?></label>
              <input type="text" class="form-control" id="devis-nom" name="nom" maxlength="100" autocomplete="name" required>
            </div>
            <div class="mb-3">
              <label for="devis-email"><?= e(t('devis.email')) ?></label>
              <input type="email" class="form-control" id="devis-email" name="email" maxlength="255" autocomplete="email" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-sm-7">
                <label for="devis-tel"><?= e(t('devis.phone')) ?> <span class="texte-doux"><?= e(t('devis.optional')) ?></span></label>
                <input type="tel" class="form-control" id="devis-tel" name="telephone" maxlength="30" autocomplete="tel">
              </div>
              <div class="col-sm-5">
                <label for="devis-nb"><?= e(t('devis.travellers')) ?></label>
                <input type="number" class="form-control" id="devis-nb" name="nb_voyageurs" min="1" max="20" value="2" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="devis-message"><?= e(t('devis.message')) ?> <span class="texte-doux"><?= e(t('devis.optional')) ?></span></label>
              <textarea class="form-control" id="devis-message" name="message" rows="3" maxlength="2000" placeholder="<?= e(t('devis.message_placeholder')) ?>"></textarea>
            </div>

            <div id="devis-erreur" class="alert alert-danger d-none" role="alert"></div>

            <button type="submit" class="btn btn-cta w-100" id="devis-envoyer"><?= e(t('devis.send')) ?></button>
            <p class="mention mt-2 mb-0"><?= e(t('devis.privacy')) ?>
              <a href="/confidentialite.php?lang=<?= $langue ?>"><?= e(t('devis.privacy_link')) ?></a></p>
          </form>

          <div id="devis-succes" class="alert alert-success d-none mb-0" role="status"><?= e(t('devis.success')) ?></div>
        </div>
      </div>
    </div>

<?php if ($images): ?>
    <!-- Visionneuse plein écran -->
    <div class="modal fade" id="lightbox" tabindex="-1" aria-label="<?= e(t('lightbox.label')) ?>" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= e(t('lightbox.close')) ?>"></button>
          <div id="lightbox-carousel" class="carousel slide" data-bs-interval="false">
            <div class="carousel-inner">
<?php foreach ($images as $i => $chemin): ?>
              <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= e(urlImage($chemin)) ?>" alt="<?= e(t('card.photo_alt', ['title' => $titre, 'n' => $i + 1])) ?>" loading="lazy">
              </div>
<?php endforeach; ?>
            </div>
<?php if (count($images) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#lightbox-carousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
              <span class="visually-hidden"><?= e(t('lightbox.prev')) ?></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#lightbox-carousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
              <span class="visually-hidden"><?= e(t('lightbox.next')) ?></span>
            </button>
<?php endif; ?>
          </div>
        </div>
      </div>
    </div>
<?php endif; ?>
<?php endif; ?>
  </div>
</main>

<?php include __DIR__ . '/includes/pied_public.php'; ?>

</body>
</html>
