<?php
// Pied de page du site public : infolettre, liens légaux et mentions. Requiert includes/outils.php.
$languePied = langueCourante();
$liens = [
    '/a-propos.php' => t('footer.about'),
    '/mentions-legales.php' => t('footer.legal'),
    '/confidentialite.php' => t('footer.privacy'),
];
?>
<footer class="pied-public py-4 mt-4">
  <div class="container">
    <div class="row g-4">

      <!-- Infolettre -->
      <div class="col-md-7 col-lg-6">
        <h2 class="h6"><?= e(t('newsletter.title')) ?></h2>
        <p class="small texte-doux mb-2"><?= e(t('newsletter.text')) ?></p>
        <form id="form-infolettre" action="/php/infolettre.php" method="post" novalidate
              data-envoi="<?= e(t('newsletter.sending')) ?>" data-libelle="<?= e(t('newsletter.submit')) ?>"
              data-succes="<?= e(t('newsletter.success')) ?>" data-reseau="<?= e(t('newsletter.err_network')) ?>"
              data-consentement="<?= e(t('newsletter.err_consent')) ?>">
          <input type="hidden" name="lang" value="<?= $languePied ?>">
          <!-- Champ piège : invisible pour les humains, rempli par les robots -->
          <div class="champ-piege" aria-hidden="true">
            <label>Do not fill in this field <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label>
          </div>
          <div class="input-group input-group-sm">
            <label class="visually-hidden" for="infolettre-email"><?= e(t('newsletter.email')) ?></label>
            <input type="email" class="form-control" id="infolettre-email" name="email" maxlength="255"
                   placeholder="<?= e(t('newsletter.email')) ?>" autocomplete="email" required>
            <button type="submit" class="btn btn-cta btn-sm" id="infolettre-envoyer"><?= e(t('newsletter.submit')) ?></button>
          </div>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="infolettre-consentement" name="consentement" value="1" required>
            <label class="form-check-label small texte-doux" for="infolettre-consentement"><?= e(t('newsletter.consent')) ?></label>
          </div>
          <div id="infolettre-message" class="small mt-2" role="status" aria-live="polite"></div>
        </form>
      </div>

      <!-- Liens -->
      <div class="col-md-5 col-lg-6">
        <ul class="list-unstyled small mb-0 liens-pied">
<?php foreach ($liens as $url => $libelle): ?>
          <li><a href="<?= $url ?>?lang=<?= $languePied ?>"><?= e($libelle) ?></a></li>
<?php endforeach; ?>
          <li><a href="mailto:<?= e(EMAIL_CONTACT) ?>"><?= e(EMAIL_CONTACT) ?></a></li>
        </ul>
      </div>
    </div>

    <hr class="my-3">
    <div class="small texte-doux d-flex flex-column flex-md-row justify-content-between gap-2">
      <span><?= e(t('footer.rights', ['year' => date('Y')])) ?></span>
      <span><?= e(t('footer.photos')) ?></span>
    </div>
<?php if (SITE_DEMO): ?>
    <p class="small texte-doux mb-0 mt-2"><?= e(t('footer.demo')) ?></p>
<?php endif; ?>
  </div>
</footer>
<script src="/js/infolettre.js" defer></script>
