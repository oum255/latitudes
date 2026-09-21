<?php
session_start();
require_once __DIR__ . '/includes/outils.php';
$langue = langueCourante();
$erreur = $_GET['erreur'] ?? '';
?>
<!DOCTYPE html>
<html lang="<?= $langue ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(t('register.title')) ?> – <?= e(NOM_SITE) ?></title>
  <meta name="robots" content="noindex">
  <link rel="stylesheet" href="/css/login_register.css" />
</head>
<body>

  <!-- Conteneur principal du formulaire -->
  <div class="login-container">
    <form method="POST" action="php/auth.php" class="login-form" data-mismatch="<?= e(t('register.mismatch')) ?>">
      <p class="auth-outils">
        <a href="index.php"><?= e(t('auth.back')) ?></a>
        <span class="lang-switch">
<?php foreach (LANGUES as $l): ?>
          <a href="<?= e(urlLangue($l)) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>" class="<?= $l === $langue ? 'active' : '' ?>"><?= strtoupper($l) ?></a>
<?php endforeach; ?>
        </span>
      </p>
      <h1><?= e(t('register.heading')) ?></h1>

      <!-- Messages d'erreur transmis par l'URL (php/auth.php renvoie « existe ») -->
      <?php if ($erreur === 'existe' || $erreur === 'email') : ?>
        <div class="error"><?= e(t('register.err_exists')) ?></div>
      <?php elseif ($erreur === 'serveur') : ?>
        <div class="error"><?= e(t('register.err_server')) ?></div>
      <?php endif; ?>

      <label for="nom"><?= e(t('register.name')) ?></label>
      <input type="text" id="nom" name="nom" placeholder="<?= e(t('register.name')) ?>" autocomplete="name" required />

      <label for="email"><?= e(t('login.email')) ?></label>
      <input type="email" id="email" name="email" placeholder="<?= e(t('login.email')) ?>" autocomplete="email" required />

      <label for="mot_de_passe"><?= e(t('login.password')) ?></label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="<?= e(t('login.password')) ?>" autocomplete="new-password" required />

      <label for="confirmation"><?= e(t('register.confirm')) ?></label>
      <input type="password" id="confirmation" name="confirmation" placeholder="<?= e(t('register.confirm')) ?>" autocomplete="new-password" required />

      <p class="note-vie-privee"><?= e(t('register.privacy')) ?>
        <a href="confidentialite.php?lang=<?= $langue ?>"><?= e(t('devis.privacy_link')) ?></a></p>

      <button type="submit" name="register"><?= e(t('register.submit')) ?></button>

      <p style="margin-top: 1rem;"><a href="login.php"><?= e(t('register.have_account')) ?></a></p>
    </form>
  </div>

  <!-- Vérification JS avant soumission : mot de passe et confirmation doivent correspondre -->
  <script>
    document.querySelector("form").addEventListener("submit", function (e) {
      const mdp = document.getElementById("mot_de_passe").value;
      const confirmation = document.getElementById("confirmation").value;
      if (mdp !== confirmation) {
        e.preventDefault();
        alert(this.dataset.mismatch);
      }
    });
  </script>

</body>
</html>
