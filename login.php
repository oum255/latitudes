<?php
session_start();
require_once __DIR__ . '/includes/outils.php';
$langue = langueCourante();
?>
<!DOCTYPE html>
<html lang="<?= $langue ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(t('login.title')) ?> – <?= e(NOM_SITE) ?></title>
  <meta name="robots" content="noindex">
  <link rel="stylesheet" href="/css/login_register.css" />
</head>
<body>
  <div class="login-container">
    <form method="POST" action="php/auth.php" class="login-form">
      <p class="auth-outils">
        <a href="index.php"><?= e(t('auth.back')) ?></a>
        <span class="lang-switch">
<?php foreach (LANGUES as $l): ?>
          <a href="<?= e(urlLangue($l)) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>" class="<?= $l === $langue ? 'active' : '' ?>"><?= strtoupper($l) ?></a>
<?php endforeach; ?>
        </span>
      </p>
      <h1><?= e(t('login.heading')) ?></h1>

      <?php if (isset($_GET['success']) && $_GET['success'] === 'inscription') : ?>
        <div class="error" style="color: green;"><?= e(t('login.registered')) ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['erreur']) && $_GET['erreur'] === 'identifiants') : ?>
        <div class="error"><?= e(t('login.bad_credentials')) ?></div>
      <?php endif; ?>

      <label for="email"><?= e(t('login.email')) ?></label>
      <input type="email" id="email" name="email" placeholder="<?= e(t('login.email')) ?>" autocomplete="email" required />

      <label for="mot_de_passe"><?= e(t('login.password')) ?></label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="<?= e(t('login.password')) ?>" autocomplete="current-password" required />

      <button type="submit" name="login"><?= e(t('login.submit')) ?></button>

      <p style="margin-top: 1rem;"><?= e(t('login.no_account')) ?> <a href="register.php"><?= e(t('login.create_account')) ?></a></p>
    </form>
  </div>
</body>
</html>
