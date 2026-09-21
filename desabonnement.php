<?php
// Désabonnement de l'infolettre. Le lien contient le jeton de l'abonné ; le désabonnement n'est effectif
// qu'après avoir cliqué sur le bouton (un simple aperçu du lien par une messagerie ne désabonne personne).
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/gabarit.php';

$jeton = (string)($_GET['t'] ?? $_POST['t'] ?? '');
$valide = (bool)preg_match('/^[a-f0-9]{32}$/', $jeton);
$abonne = null;
if ($valide) {
    $stmt = $pdo->prepare('SELECT id, desabonne_le FROM abonnes WHERE token = ?');
    $stmt->execute([$jeton]);
    $abonne = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$termine = false;
if ($abonne && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare('UPDATE abonnes SET desabonne_le = COALESCE(desabonne_le, CURRENT_TIMESTAMP) WHERE id = ?')->execute([$abonne['id']]);
    $termine = true;
}

$langue = langueCourante();
debutPagePublique(t('unsub.title'), '', false);
?>
    <div class="texte-etroit text-center py-4">
      <h1><?= e(t('unsub.title')) ?></h1>
<?php if ($termine || ($abonne && $abonne['desabonne_le'] !== null)): ?>
      <p class="alert alert-success" role="status"><?= e(t('unsub.done')) ?></p>
<?php elseif ($abonne): ?>
      <p><?= e(t('unsub.confirm_text')) ?></p>
      <form method="post">
        <input type="hidden" name="t" value="<?= e($jeton) ?>">
        <button type="submit" class="btn btn-cta"><?= e(t('unsub.button')) ?></button>
      </form>
<?php else: ?>
      <p class="alert alert-secondary" role="status"><?= e(t('unsub.invalid')) ?></p>
<?php endif; ?>
    </div>
<?php finPagePublique(); ?>
