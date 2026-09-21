<?php
// Barre de filtres partagée par le catalogue public et l'espace admin. Requiert includes/outils.php.
// Les listes de continents, pays et catégories sont remplies en JavaScript à partir des voyages réellement présents.
?>
<div class="bg-white p-3 rounded shadow-sm mb-4" id="barre-filtres">
  <h6 class="fw-bold mb-3"><?= e(t('filters.heading')) ?></h6>
  <div class="row g-2 align-items-center">
    <div class="col-12 col-lg-4">
      <label class="visually-hidden" for="filtre"><?= e(t('filters.search_label')) ?></label>
      <input type="search" id="filtre" class="form-control form-control-sm" placeholder="<?= e(t('filters.search_placeholder')) ?>" autocomplete="off">
    </div>
    <div class="col-6 col-lg-2">
      <label class="visually-hidden" for="filtre-continent"><?= e(t('filters.continent_all')) ?></label>
      <select id="filtre-continent" class="form-select form-select-sm">
        <option value=""><?= e(t('filters.continent_all')) ?></option>
      </select>
    </div>
    <div class="col-6 col-lg-2">
      <label class="visually-hidden" for="filtre-pays"><?= e(t('filters.country_all')) ?></label>
      <select id="filtre-pays" class="form-select form-select-sm">
        <option value=""><?= e(t('filters.country_all')) ?></option>
      </select>
    </div>
    <div class="col-6 col-lg-2">
      <label class="visually-hidden" for="filtre-categorie"><?= e(t('filters.category_all')) ?></label>
      <select id="filtre-categorie" class="form-select form-select-sm">
        <option value=""><?= e(t('filters.category_all')) ?></option>
      </select>
    </div>
    <div class="col-6 col-lg-2">
      <label class="visually-hidden" for="filtre-tri"><?= e(t('filters.sort_label')) ?></label>
      <select id="filtre-tri" class="form-select form-select-sm">
        <option value="recent"><?= e(t('sort.recent')) ?></option>
        <option value="prix-asc"><?= e(t('sort.price_asc')) ?></option>
        <option value="prix-desc"><?= e(t('sort.price_desc')) ?></option>
        <option value="duree-asc"><?= e(t('sort.duration_asc')) ?></option>
      </select>
    </div>
  </div>
  <div class="d-flex justify-content-between align-items-center mt-3">
    <span id="nb-resultats" class="texte-doux small" aria-live="polite"></span>
    <button type="button" id="filtres-reinit" class="btn btn-link btn-sm p-0 lien-reinit"><?= e(t('filters.reset')) ?></button>
  </div>
</div>
