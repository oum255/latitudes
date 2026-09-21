// Aperçu dynamique de la destination en cours de saisie (formulaire d'ajout de l'espace admin)

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("form-ajout");
  if (!form) return;

  // Champs du formulaire
  const champTitre = form.querySelector('input[name="titre"]');
  const champDescription = form.querySelector('textarea[name="description"]');
  const champCategorie = form.querySelector('select[name="categorie"]');
  const champPays = form.querySelector('select[name="pays"]');
  const champImage = form.querySelector('input[name="images[]"]');

  // Élément HTML d'aperçu
  const apercuContainer = document.getElementById("apercu-container");
  const apercuTitre = document.getElementById("apercu-titre");
  const apercuCategorie = document.getElementById("apercu-categorie");
  const apercuContinent = document.getElementById("apercu-continent");
  const apercuPays = document.getElementById("apercu-pays");
  const apercuDescription = document.getElementById("apercu-description");
  const apercuImage = document.getElementById("apercu-image");

  // Mise à jour en direct du contenu textuel
  champTitre?.addEventListener("input", () => {
    apercuTitre.textContent = champTitre.value || "Titre de la destination";
  });

  champDescription?.addEventListener("input", () => {
    apercuDescription.textContent = champDescription.value || "Description...";
  });

  champCategorie?.addEventListener("change", () => {
    apercuCategorie.textContent = champCategorie.value || "Catégorie";
  });

  // Le continent se déduit du pays choisi (attribut data-continent de l'option)
  champPays?.addEventListener("change", () => {
    const option = champPays.selectedOptions[0];
    apercuContinent.textContent = option?.dataset.continent || "Continent";
    apercuPays.textContent = champPays.value ? " – " + champPays.value : " – Pays";
  });

  // Prévisualisation de la première image sélectionnée
  champImage?.addEventListener("change", () => {
    const file = champImage.files[0];
    if (file && file.type.startsWith("image/")) {
      const reader = new FileReader();
      reader.onload = () => apercuImage.src = reader.result;
      reader.readAsDataURL(file);
    } else {
      apercuImage.src = "/img/apercu.jpg";
    }
  });

  // Réinitialisation après soumission
  form.addEventListener("submit", () => {
    apercuContainer.style.opacity = 0;
    setTimeout(() => {
      apercuTitre.textContent = "Titre de la destination";
      apercuCategorie.textContent = "Catégorie";
      apercuContinent.textContent = "Continent";
      apercuPays.textContent = " – Pays";
      apercuDescription.textContent = "Description...";
      apercuImage.src = "/img/apercu.jpg";
      apercuContainer.style.opacity = 1;
    }, 700);
  });
});
