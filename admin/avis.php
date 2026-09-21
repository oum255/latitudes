<?php
session_start();
// L'espace admin est en français, quelle que soit la langue choisie sur le site public.
define('LANGUE_FORCEE', 'fr');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/admin.php';
exigerAdmin();

// Modération : publier, refuser, remettre en attente ou supprimer un avis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigerCsrf();
    $id = (int)($_POST['id'] ?? 0);
    $statuts = ['publier' => 'publie', 'refuser' => 'refuse', 'attente' => 'en_attente'];
    $action = $_POST['action'] ?? '';

    if (isset($statuts[$action])) {
        $pdo->prepare('UPDATE avis SET statut = ? WHERE id = ?')->execute([$statuts[$action], $id]);
    } elseif ($action === 'supprimer') {
        $pdo->prepare('DELETE FROM avis WHERE id = ?')->execute([$id]);
    }
    header('Location: avis.php');
    exit();
}

$avis = $pdo->query(
    "SELECT a.*, v.titre AS titre_voyage
       FROM avis a JOIN voyages v ON v.id = a.id_voyage
      ORDER BY (a.statut = 'en_attente') DESC, a.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$etiquettes = [
    'en_attente' => ['En attente', 'bg-warning text-dark'],
    'publie' => ['Publié', 'bg-success'],
    'refuse' => ['Refusé', 'bg-secondary'],
];

enteteAdmin('Avis des voyageurs', 'avis', compteursAdmin($pdo));
?>
    <h1 class="h3 mb-1">Avis des voyageurs</h1>
    <p class="texte-doux mb-4">
      <?= count($avis) ?> avis au total. Un avis n'apparaît sur le site qu'une fois <strong>publié</strong> ;
      les avis en attente sont listés en premier.
    </p>

<?php if (!$avis): ?>
    <div class="alert alert-secondary">Aucun avis pour le moment. Les visiteurs peuvent en laisser depuis la fiche d'un voyage ; ils arriveront ici pour modération.</div>
<?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th>Reçu le</th>
            <th>Voyage</th>
            <th>Auteur</th>
            <th>Note</th>
            <th>Avis</th>
            <th>Statut</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($avis as $a): ?>
          <tr>
            <td class="text-nowrap"><?= e(dateHeureLocale($a['created_at'])) ?><br><small class="texte-doux"><?= e(heureLocale($a['created_at'])->format('H:i')) ?></small></td>
            <td><a href="/voyage.php?id=<?= (int)$a['id_voyage'] ?>#avis" target="_blank" rel="noopener"><?= e($a['titre_voyage']) ?></a></td>
            <td>
              <?= e($a['nom']) ?> <span class="badge bg-secondary"><?= e(strtoupper($a['langue'])) ?></span><br>
              <a href="mailto:<?= e($a['email']) ?>"><?= e($a['email']) ?></a>
            </td>
            <td class="text-nowrap"><?= htmlEtoiles((float)$a['note'], 'fr') ?></td>
            <td style="max-width: 360px; white-space: pre-line;"><?= e($a['commentaire']) ?></td>
            <td><span class="badge <?= $etiquettes[$a['statut']][1] ?>"><?= e($etiquettes[$a['statut']][0]) ?></span></td>
            <td class="text-nowrap text-end">
              <form method="post" class="d-inline">
                <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
<?php if ($a['statut'] !== 'publie'): ?>
                <button name="action" value="publier" class="btn btn-sm btn-success">Publier</button>
<?php endif; ?>
<?php if ($a['statut'] === 'en_attente'): ?>
                <button name="action" value="refuser" class="btn btn-sm btn-outline-light">Refuser</button>
<?php elseif ($a['statut'] === 'publie'): ?>
                <button name="action" value="attente" class="btn btn-sm btn-outline-light">Retirer</button>
<?php endif; ?>
              </form>
              <form method="post" class="d-inline" onsubmit="return confirm('Supprimer définitivement cet avis ?');">
                <input type="hidden" name="csrf" value="<?= e(jetonCsrf()) ?>">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
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
