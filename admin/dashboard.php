<?php
session_start();
// L'espace admin est en français, quelle que soit la langue choisie sur le site public.
define('LANGUE_FORCEE', 'fr');

// Seul un compte avec le rôle admin accède à l'espace de gestion
if (!isset($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/admin.php';
$compteurs = compteursAdmin($pdo);
$configJs = ['lang' => 'fr', 'textes' => textesPourJs(['card', 'duration', 'results'], 'fr')];
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Espace admin – <?= e(NOM_SITE) ?></title>
  <meta name="robots" content="noindex">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/css/dashboard.css" />
  <link rel="stylesheet" href="/css/public.css" />
  <script>window.LATITUDES = <?= json_encode($configJs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
  <script src="/js/commun.js" defer></script>
  <script src="/js/main.js" defer></script>
  <script src="/js/apercu.js" defer></script>
</head>
<body>

<!-- Barre de navigation -->
<header class="header-gradient">
  <nav class="navbar navbar-dark shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
      <a href="/index.php" class="navbar-brand fw-bold"><?= e(NOM_SITE) ?> <span class="badge bg-primary">Admin</span></a>
      <div class="d-flex align-items-center">
        <img src="https://cdn-icons-png.flaticon.com/512/1077/1077012.png" alt="avatar" class="rounded-circle me-2" style="width: 32px; height: 32px;">
        <span class="text-white me-3 d-none d-sm-inline"><?= e($_SESSION['nom']) ?></span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuCollapse" aria-label="Menu">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </div>

    <div class="collapse" id="menuCollapse">
      <div class="container py-4 text-white">
        <div class="row">

          <!--  Colonne GAUCHE : À propos -->
          <div class="col-md-7 border-end pe-4">
            <h6 class="text-uppercase text-white-50 mb-2">À propos</h6>
            <p class="small mb-0">
              Espace de gestion du catalogue : ajoutez, modifiez ou retirez des destinations. Les visiteurs voient ces changements immédiatement sur le catalogue public, en français et en anglais.
            </p>
          </div>

          <!--  Colonne DROITE : liens -->
          <div class="col-md-5 ps-md-4">
            <ul class="navbar-nav mb-3">
              <li class="nav-item mb-2">
                <a href="/admin/demandes.php" class="nav-link text-white"> Demandes de devis
                  <?php if ($compteurs['demandes'] > 0): ?><span class="badge bg-danger"><?= $compteurs['demandes'] ?></span><?php endif; ?>
                </a>
              </li>
              <li class="nav-item mb-2">
                <a href="/admin/avis.php" class="nav-link text-white"> Avis des voyageurs
                  <?php if ($compteurs['avis'] > 0): ?><span class="badge bg-danger"><?= $compteurs['avis'] ?></span><?php endif; ?>
                </a>
              </li>
              <li class="nav-item mb-2">
                <a href="/admin/abonnes.php" class="nav-link text-white"> Infolettre
                  <?php if ($compteurs['abonnes'] > 0): ?><span class="badge bg-secondary"><?= $compteurs['abonnes'] ?></span><?php endif; ?>
                </a>
              </li>
              <li class="nav-item mb-2"><a href="/index.php" class="nav-link text-white"> Voir le catalogue public</a></li>
              <li class="nav-item mb-2"><a href="/php/logout.php" class="nav-link text-white"> Déconnexion</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</header>

<!-- Formulaire d'ajout -->
<section class="py-4 bg-light">
  <div class="container">
    <div class="row">

      <!-- Formulaire à gauche -->
      <div class="col-md-6">
        <h2 class="mb-4">Ajouter une destination</h2>
        <form id="form-ajout" action="#" enctype="multipart/form-data" class="row g-3">
          <div class="col-12">
            <input type="text" name="titre" class="form-control" placeholder="Titre (français)" maxlength="255" required>
          </div>
          <div class="col-12">
            <input type="text" name="titre_en" class="form-control" placeholder="Titre en anglais (facultatif)" maxlength="255">
          </div>
          <div class="col-12">
            <input type="date" name="date_depart" class="form-control" aria-label="Date de départ" required>
          </div>
          <div class="col-12">
            <select name="pays" id="pays" class="form-select" aria-label="Pays" required>
              <?= optionsPays('fr', '-- Choisir un pays --') ?>
            </select>
          </div>
          <div class="col-12">
            <select name="categorie" class="form-select" aria-label="Catégorie" required>
              <?= optionsCategories('fr', '-- Choisir une catégorie --') ?>
            </select>
          </div>
          <div class="col-4">
            <input type="number" name="prix" class="form-control" placeholder="Prix ($ / pers.)" min="0" max="99999999" step="0.01" aria-label="Prix par personne en dollars canadiens">
          </div>
          <div class="col-4">
            <input type="number" name="duree_jours" class="form-control" placeholder="Durée (jours)" min="1" max="365" step="1" aria-label="Durée en jours">
          </div>
          <div class="col-4">
            <input type="number" name="places_disponibles" class="form-control" placeholder="Places" min="0" max="9999" step="1" aria-label="Places disponibles">
          </div>
          <div class="col-12">
            <input type="file" name="images[]" class="form-control" accept="image/*" multiple aria-label="Photos">
          </div>
          <div class="col-12">
            <textarea name="description" class="form-control" placeholder="Description (français)..." maxlength="5000" required></textarea>
          </div>
          <div class="col-12">
            <textarea name="description_en" class="form-control" placeholder="Description en anglais (facultatif)..." maxlength="5000"></textarea>
          </div>
          <div class="col-12">
            <p class="small texte-doux mb-2">Sans version anglaise, le site affiche le texte français aux visiteurs anglophones.</p>
            <button type="submit" class="btn btn-black">Ajouter</button>
          </div>
        </form>
      </div>

      <!-- Aperçu dynamique à droite -->
      <div class="col-md-6 d-none d-md-block" id="apercu-container">
        <div class="card shadow fade-in tuile-apercu" id="apercu">
          <img id="apercu-image" src="/img/apercu.jpg" class="card-img-top" alt="Aperçu">
          <div class="card-body">
            <h5 class="card-title" id="apercu-titre">Titre de la destination</h5>
            <p class="card-text">
              <strong id="apercu-categorie">Catégorie</strong><br>
              <em><span id="apercu-continent">Continent</span><span id="apercu-pays"> – Pays</span></em><br>
              <span id="apercu-description">Description...</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Zone de filtres et cartes -->
<main>
  <section class="album py-5 bg-body-tertiary">
    <div class="container">
      <?php include __DIR__ . '/../includes/filtres.php'; ?>

      <!-- Tuiles -->
      <div id="zone-tuiles"></div>
    </div>
  </section>
</main>

<!-- Modale Bootstrap pour modification -->
<div class="modal fade" id="modalModification" tabindex="-1" aria-labelledby="modalModificationLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="form-modification" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalModificationLabel">Modifier la destination</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modif-id">
        <div class="mb-3">
          <label for="modif-titre" class="form-label">Titre (français)</label>
          <input type="text" class="form-control" id="modif-titre" name="titre" maxlength="255" required>
        </div>
        <div class="mb-3">
          <label for="modif-titre-en" class="form-label">Titre en anglais <span class="texte-doux">(facultatif)</span></label>
          <input type="text" class="form-control" id="modif-titre-en" name="titre_en" maxlength="255">
        </div>
        <div class="mb-3">
          <label for="modif-date" class="form-label">Date de départ</label>
          <input type="date" class="form-control" id="modif-date" name="date_depart" required>
        </div>
        <div class="mb-3">
          <label for="modif-pays" class="form-label">Pays</label>
          <select id="modif-pays" class="form-select" name="pays" required>
            <?= optionsPays('fr', '-- Choisir un pays --') ?>
          </select>
        </div>
        <div class="mb-3">
          <label for="modif-categorie" class="form-label">Catégorie</label>
          <select id="modif-categorie" class="form-select" name="categorie" required>
            <?= optionsCategories('fr', '-- Choisir une catégorie --') ?>
          </select>
        </div>
        <div class="row g-3 mb-3">
          <div class="col-4">
            <label for="modif-prix" class="form-label">Prix ($ / pers.)</label>
            <input type="number" class="form-control" id="modif-prix" name="prix" min="0" max="99999999" step="0.01">
          </div>
          <div class="col-4">
            <label for="modif-duree" class="form-label">Durée (jours)</label>
            <input type="number" class="form-control" id="modif-duree" name="duree_jours" min="1" max="365" step="1">
          </div>
          <div class="col-4">
            <label for="modif-places" class="form-label">Places</label>
            <input type="number" class="form-control" id="modif-places" name="places_disponibles" min="0" max="9999" step="1">
          </div>
        </div>
        <div class="mb-3">
          <label for="modif-description" class="form-label">Description (français)</label>
          <textarea class="form-control" id="modif-description" name="description" rows="3" maxlength="5000" required></textarea>
        </div>
        <div class="mb-3">
          <label for="modif-description-en" class="form-label">Description en anglais <span class="texte-doux">(facultatif)</span></label>
          <textarea class="form-control" id="modif-description-en" name="description_en" rows="3" maxlength="5000"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Images existantes</label>
          <div id="modif-images-preview" class="d-flex flex-wrap gap-2"></div>
        </div>
        <div class="mb-3">
          <label for="modif-new-images" class="form-label">Ajouter d'autres images</label>
          <input type="file" class="form-control" id="modif-new-images" name="new_images[]" accept="image/*" multiple>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Valider les modifications</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>
