<?php
session_start();
// L'espace admin est en français, quelle que soit la langue choisie sur le site public.
define('LANGUE_FORCEE', 'fr');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/admin.php';
exigerAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigerCsrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'traitee') {
        $pdo->prepare("UPDATE demandes SET statut = 'traitee' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'nouvelle') {
        $pdo->prepare("UPDATE demandes SET statut = 'nouvelle' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'supprimer') {
        $pdo->prepare("DELETE FROM demandes WHERE id = ?")->execute([$id]);
    }

    header("Location: demandes.php");
    exit();
}

$demandes = $pdo->query(
    "SELECT * FROM demandes ORDER BY (statut = 'nouvelle') DESC, created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);
$nbNouvelles = count(array_filter($demandes, fn($d) => $d['statut'] === 'nouvelle'));

enteteAdmin('Demandes de devis', 'demandes', compteursAdmin($pdo));
?>
    <h1 class="h3 mb-1">Demandes de devis</h1>
    <p class="texte-doux mb-4">
      <?= count($demandes) ?> demande<?= count($demandes) > 1 ? 's' : '' ?> au total,
      <?= $nbNouvelles ?> à traiter.
    </p>

<?php if (!$demandes): ?>
    <div class="alert alert-secondary">Aucune demande pour le moment. Elles apparaîtront ici dès qu'un visiteur en enverra une depuis la fiche d'un voyage.</div>
<?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Reçue le</th>
            <th>Voyage</th>
            <th>Contact</th>
            <th>Langue</th>
            <th class="text-center">Pers.</th>
            <th>Message</th>
            <th>Statut</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($demandes as $d): ?>
          <tr>
            <td class="text-nowrap"><?= e(dateHeureLocale($d['created_at'])) ?><br><small class="texte-doux"><?= e(heureLocale($d['created_at'])->format('H:i')) ?></small></td>
            <td>
<?php if ($d['id_voyage']): ?>
              <a href="/voyage.php?id=<?= (int)$d['id_voyage'] ?>" target="_blank" rel="noopener"><?= e($d['titre_voyage']) ?></a>
<?php else: ?>
              <?= e($d['titre_voyage']) ?> <small class="texte-doux">(voyage retiré)</small>
<?php endif; ?>
            </td>
            <td>
              <?= e($d['nom']) ?><br>
              <a href="mailto:<?= e($d['email']) ?>"><?= e($d['email']) ?></a>
<?php if ($d['telephone']): ?>
              <br><small><?= e($d['telephone']) ?></small>
<?php endif; ?>
            </td>
            <td><span class="badge bg-secondary"><?= e(strtoupper($d['langue'] ?? 'fr')) ?></span></td>
            <td class="text-center"><?= (int)$d['nb_voyageurs'] ?></td>
            <td style="max-width: 320px; white-space: pre-line;"><?= e($d['message'] ?? '') ?></td>
            <td>
              <?php if ($d['statut'] === 'nouvelle'): ?>
                <span class="badge bg-danger">Nouvelle</span>
              <?php else: ?>
                <span class="badge bg-secondary">Traitée</span>
              <?php endif; ?>
            </td>
            <td class="text-nowrap text-end">
              <form method="post" class="d-inline">
                <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                <?php if ($d['statut'] === 'nouvelle'): ?>
                  <button name="action" value="traitee" class="btn btn-sm btn-outline-light">Marquer traitée</button>
                <?php else: ?>
                  <button name="action" value="nouvelle" class="btn btn-sm btn-outline-secondary">Rouvrir</button>
                <?php endif; ?>
              </form>
              <form method="post" class="d-inline" onsubmit="return confirm('Supprimer définitivement cette demande ?');">
                <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                <button name="action" value="supprimer" class="btn btn-sm btn-outline-danger">Supprimer</button>
              </form>
            </td>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php endif; ?>
<?php piedAdmin(); ?>
