<?php
require_once __DIR__ . '/includes/outils.php';
$langue = langueCourante();
$configJs = ['lang' => $langue, 'textes' => textesPourJs(['card', 'duration', 'results', 'lightbox', 'avis'], $langue)];
?>
<!DOCTYPE html>
<html lang="<?= $langue ?>" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(t('home.title')) ?></title>
  <meta name="description" content="<?= e(t('home.description')) ?>">
  <meta property="og:title" content="<?= e(t('home.title')) ?>">
  <meta property="og:description" content="<?= e(t('home.description')) ?>">
  <meta property="og:locale" content="<?= $langue === 'en' ? 'en_CA' : 'fr_CA' ?>">
<?= balisesLangues() ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css" />
  <link rel="stylesheet" href="/css/public.css" />
  <script>window.LATITUDES = <?= json_encode($configJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="/js/commun.js" defer></script>
  <script src="/js/catalogue.js" defer></script>
</head>
<body>

<?php include __DIR__ . '/includes/nav_publique.php'; ?>

<!-- Bannière -->
<section class="hero-banner position-relative mb-4">
  <video autoplay muted loop playsinline aria-hidden="true">
    <source src="/videos/mon_bandeau.mp4" type="video/mp4">
  </video>

  <div class="position-absolute top-50 start-50 translate-middle text-white text-center w-100 px-3">
    <h1 class="fw-bold display-6 text-shadow"><?= e(t('home.hero_title')) ?></h1>
    <p class="lead text-shadow-sm"><?= e(t('home.hero_text')) ?></p>
  </div>
</section>

<!-- Filtres et voyages -->
<main>
  <section class="album py-5 bg-body-tertiary">
    <div class="container">
      <?php include __DIR__ . '/includes/filtres.php'; ?>

      <div id="zone-tuiles"></div>
      <div id="pagination"></div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/includes/pied_public.php'; ?>

</body>
</html>
