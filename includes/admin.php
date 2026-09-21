<?php
// Aides communes aux pages de l'espace admin : accès, jeton CSRF, compteurs et navigation.
// L'espace admin est en français : les pages doivent faire define('LANGUE_FORCEE', 'fr') AVANT d'inclure ce fichier.
require_once __DIR__ . '/outils.php';

/* Réserve la page aux comptes admin (les autres sont renvoyés vers la page de connexion). */
function exigerAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: /login.php');
        exit();
    }
}

/* Jeton CSRF de la session : empêche un site tiers de déclencher une action à l'insu de l'admin connecté. */
function jetonCsrf(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

/* Refuse la requête si le jeton CSRF envoyé en POST est absent ou faux. */
function exigerCsrf(): void
{
    if (!hash_equals(jetonCsrf(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Requête refusée (jeton de sécurité invalide).');
    }
}

/* Nombre d'éléments qui attendent une action de l'admin. */
function compteursAdmin(PDO $pdo): array
{
    return [
        'demandes' => (int)$pdo->query("SELECT COUNT(*) FROM demandes WHERE statut = 'nouvelle'")->fetchColumn(),
        'avis' => (int)$pdo->query("SELECT COUNT(*) FROM avis WHERE statut = 'en_attente'")->fetchColumn(),
        'abonnes' => (int)$pdo->query("SELECT COUNT(*) FROM abonnes WHERE desabonne_le IS NULL")->fetchColumn(),
    ];
}

/* Début d'une page admin « liste » (thème sombre) avec la barre de navigation. $actif : demandes | avis | abonnes */
function enteteAdmin(string $titre, string $actif, array $compteurs): void
{
    $liens = [
        'demandes' => ['/admin/demandes.php', 'Demandes de devis', $compteurs['demandes'], 'bg-danger'],
        'avis' => ['/admin/avis.php', 'Avis', $compteurs['avis'], 'bg-danger'],
        'abonnes' => ['/admin/abonnes.php', 'Infolettre', $compteurs['abonnes'], 'bg-secondary'],
    ];
    ?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titre) ?> – <?= e(NOM_SITE) ?></title>
  <meta name="robots" content="noindex">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css" />
  <link rel="stylesheet" href="/css/public.css" />
</head>
<body>

<header class="header-gradient">
  <nav class="navbar navbar-dark shadow-sm">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
      <a href="/admin/dashboard.php" class="navbar-brand fw-bold"><?= e(NOM_SITE) ?> <span class="badge bg-primary">Admin</span></a>
      <div class="d-flex flex-wrap align-items-center gap-3">
        <a href="/admin/dashboard.php" class="text-white">← Catalogue</a>
<?php foreach ($liens as $cle => [$url, $libelle, $nombre, $classe]): ?>
        <a href="<?= $url ?>" class="text-white<?= $cle === $actif ? ' fw-bold text-decoration-underline' : '' ?>"><?= e($libelle) ?>
          <?php if ($nombre > 0): ?><span class="badge <?= $classe ?>"><?= (int)$nombre ?></span><?php endif; ?></a>
<?php endforeach; ?>
        <a href="/php/logout.php" class="text-white">Déconnexion</a>
      </div>
    </div>
  </nav>
</header>

<main class="py-4">
  <div class="container">
<?php
}

function piedAdmin(): void
{
    ?>
  </div>
</main>

</body>
</html>
<?php
}
