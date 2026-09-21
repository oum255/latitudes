<?php
session_start();
// L'espace admin est en français, quelle que soit la langue choisie sur le site public.
define('LANGUE_FORCEE', 'fr');
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/admin.php';
exigerAdmin();

/* Neutralise les cellules CSV qu'un tableur interpréterait comme une formule (=, +, -, @). */
function cellulePure(string $valeur): string
{
    return preg_match('/^[=+\-@\t\r]/', $valeur) ? "'" . $valeur : $valeur;
}

// Export CSV des abonnés actifs (lecture seule, réservé à l'admin connecté)
if (($_GET['export'] ?? '') === 'csv') {
    $lignes = $pdo->query('SELECT email, langue, consenti_le FROM abonnes WHERE desabonne_le IS NULL ORDER BY consenti_le')->fetchAll(PDO::FETCH_NUM);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="abonnes-' . date('Y-m-d') . '.csv"');
    $sortie = fopen('php://output', 'w');
    fwrite($sortie, "\xEF\xBB\xBF"); // BOM : Excel reconnaît l'UTF-8
    fputcsv($sortie, ['courriel', 'langue', 'consentement'], ',', '"', '');
    foreach ($lignes as $ligne) {
        fputcsv($sortie, array_map(fn($v) => cellulePure((string)$v), $ligne), ',', '"', '');
    }
    exit();
}

// Suppression définitive d'un abonné
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigerCsrf();
    if (($_POST['action'] ?? '') === 'supprimer') {
        $pdo->prepare('DELETE FROM abonnes WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
    }
    header('Location: abonnes.php');
    exit();
}

$abonnes = $pdo->query('SELECT * FROM abonnes ORDER BY (desabonne_le IS NULL) DESC, consenti_le DESC')->fetchAll(PDO::FETCH_ASSOC);
$nbActifs = count(array_filter($abonnes, fn($a) => $a['desabonne_le'] === null));

enteteAdmin('Infolettre', 'abonnes', compteursAdmin($pdo));
?>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
      <div>
        <h1 class="h3 mb-1">Infolettre</h1>
        <p class="texte-doux mb-0"><?= $nbActifs ?> abonné<?= $nbActifs > 1 ? 's' : '' ?> actif<?= $nbActifs > 1 ? 's' : '' ?>, <?= count($abonnes) - $nbActifs ?> désabonné<?= count($abonnes) - $nbActifs > 1 ? 's' : '' ?>.</p>
      </div>
      <a href="abonnes.php?export=csv" class="btn btn-outline-light btn-sm<?= $nbActifs === 0 ? ' disabled' : '' ?>">Exporter les abonnés actifs (CSV)</a>
    </div>

    <div class="alert alert-secondary small">
      Cette page ne fait que <strong>collecter</strong> les inscriptions (avec la date du consentement). L'envoi de l'infolettre
      se fait avec un service extérieur (à brancher), en y important l'export CSV et en incluant le lien de désabonnement
      <code>/desabonnement.php?t=<em>jeton</em></code> propre à chaque abonné.
    </div>

<?php if (!$abonnes): ?>
    <div class="alert alert-secondary">Aucun abonné pour le moment. Le formulaire d'inscription se trouve dans le pied de page du site.</div>
<?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>Courriel</th><th>Langue</th><th>Consentement</th><th>Statut</th><th></th></tr>
        </thead>
        <tbody>
<?php foreach ($abonnes as $a): ?>
          <tr>
            <td><?= e($a['email']) ?></td>
            <td><span class="badge bg-secondary"><?= e(strtoupper($a['langue'])) ?></span></td>
            <td class="text-nowrap"><?= e(dateHeureLocale($a['consenti_le'])) ?></td>
            <td>
<?php if ($a['desabonne_le'] === null): ?>
              <span class="badge bg-success">Actif</span>
<?php else: ?>
              <span class="badge bg-secondary">Désabonné le <?= e(dateHeureLocale($a['desabonne_le'])) ?></span>
<?php endif; ?>
            </td>
            <td class="text-end">
              <form method="post" class="d-inline" onsubmit="return confirm('Supprimer définitivement cette adresse ?');">
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
