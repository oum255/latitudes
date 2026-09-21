<?php
// Barre de navigation du site public (catalogue, fiches voyage, connexion). Requiert includes/outils.php.
$langueNav = langueCourante();
?>
<header class="header-gradient">
  <nav class="navbar navbar-dark shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
      <a href="/index.php" class="navbar-brand fw-bold"><?= e(NOM_SITE) ?></a>
      <div class="d-flex align-items-center gap-3">
        <div class="lang-switch" role="group" aria-label="<?= e(t('nav.language')) ?>">
<?php foreach (LANGUES as $l): ?>
          <a href="<?= e(urlLangue($l)) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>"
             class="<?= $l === $langueNav ? 'active' : '' ?>"<?= $l === $langueNav ? ' aria-current="true"' : '' ?>><?= strtoupper($l) ?></a>
<?php endforeach; ?>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuCollapse" aria-label="<?= e(t('nav.menu')) ?>">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>

    <div class="collapse" id="menuCollapse">
      <div class="container py-4 text-white">
        <div class="row">

          <!--  Colonne GAUCHE : À propos -->
          <div class="col-md-7 border-end pe-4">
            <h6 class="text-uppercase text-white-50 mb-2"><?= e(t('nav.about')) ?></h6>
            <p class="small mb-2"><?= e(t('nav.about_text')) ?></p>
            <a href="/a-propos.php?lang=<?= $langueNav ?>" class="text-white small"><?= e(t('nav.learn_more')) ?> →</a>
          </div>

          <!--  Colonne DROITE : Connexion + Contact -->
          <div class="col-md-5 ps-md-4">
            <ul class="navbar-nav mb-3">
              <li class="nav-item mb-2"><a href="/login.php" class="nav-link text-white"><?= e(t('nav.agency')) ?></a></li>
            </ul>
            <h6 class="text-uppercase text-white-50 mb-2"><?= e(t('nav.contact')) ?></h6>
            <ul class="list-unstyled small">
              <li><a href="mailto:<?= e(EMAIL_CONTACT) ?>" class="text-white"><?= e(EMAIL_CONTACT) ?></a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>
