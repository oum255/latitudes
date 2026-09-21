// Espace admin : ajout, modification et suppression des voyages, avec les mêmes filtres que le catalogue public.
// Requiert js/commun.js.

const {
  echapper, urlImage, lireImages, champ, formaterPrix, pluriel, creerFiltres
} = window.Latitudes;

let voyageActuel = null;
let pageActuelle = 1;
let tousLesVoyages = [];
let ecouteursModaleInstalles = false;
const voyagesParPage = 15;

const form = document.getElementById("form-ajout");
const zoneTuiles = document.getElementById("zone-tuiles");
const compteur = document.getElementById("nb-resultats");

const paginationContainer = document.createElement("div");
paginationContainer.id = "pagination";
document.body.appendChild(paginationContainer);

const filtres = creerFiltres({
  champs: {
    recherche: document.getElementById("filtre"),
    continent: document.getElementById("filtre-continent"),
    pays: document.getElementById("filtre-pays"),
    categorie: document.getElementById("filtre-categorie"),
    tri: document.getElementById("filtre-tri"),
    reinit: document.getElementById("filtres-reinit")
  },
  obtenirVoyages: () => tousLesVoyages,
  surChangement: () => { pageActuelle = 1; afficherPage(); }
});

/* Affiche l'erreur renvoyée par le serveur (JSON { erreur }) et indique si la requête a réussi */
async function reponseOk(res) {
  if (res.ok) return true;
  const donnees = await res.json().catch(() => ({}));
  alert(donnees.erreur || "Une erreur est survenue. Veuillez réessayer.");
  return false;
}

/* Résumé « Prix · durée · places » affiché sur la carte admin */
function resumeCommercial(voyage) {
  const morceaux = [];
  if (voyage.prix !== null && voyage.prix !== undefined) morceaux.push(formaterPrix(voyage.prix));
  if (voyage.duree_jours) morceaux.push(`${voyage.duree_jours} j`);
  if (voyage.places_disponibles !== null && voyage.places_disponibles !== undefined) morceaux.push(`${voyage.places_disponibles} pl.`);
  return morceaux.length ? " · " + echapper(morceaux.join(" · ")) : "";
}

/* Soumission du formulaire d'ajout de destination */
if (form) {
  form.addEventListener("submit", (e) => {
    e.preventDefault();
    fetch("/php/voyages.php", { method: "POST", body: new FormData(form) })
      .then(async (res) => {
        if (await reponseOk(res)) {
          form.reset();
          chargerTuiles();
        }
      })
      .catch((err) => alert(err.message));
  });
}

/* Charge tous les voyages depuis le serveur */
function chargerTuiles() {
  fetch("/php/voyages.php")
    .then((res) => res.json())
    .then((data) => {
      tousLesVoyages = data;
      filtres.rafraichirOptions();
      pageActuelle = 1;
      afficherPage();
    })
    .catch((err) => console.error("Erreur lors du chargement :", err));
}

function htmlImages(voyage, images) {
  const carouselId = `carousel-${voyage.id}`;
  if (images.length > 1) {
    return `
      <div id="${carouselId}" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          ${images.map((src, i) => `
            <div class="carousel-item ${i === 0 ? "active" : ""}">
              <img src="${echapper(urlImage(src))}" class="d-block w-100" alt="Image ${i + 1}" loading="lazy">
            </div>`).join("")}
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
          <span class="visually-hidden">Précédent</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
          <span class="visually-hidden">Suivant</span>
        </button>
      </div>`;
  }
  if (images.length === 1) {
    return `<img src="${echapper(urlImage(images[0]))}" class="card-img-top" alt="Image" loading="lazy">`;
  }
  return "";
}

/* Affiche la page actuelle de tuiles filtrées */
function afficherPage() {
  const voyagesFiltres = filtres.liste();
  compteur.textContent = pluriel("results", voyagesFiltres.length);

  const totalPages = Math.ceil(voyagesFiltres.length / voyagesParPage);
  const debut = (pageActuelle - 1) * voyagesParPage;
  const voyagesPage = voyagesFiltres.slice(debut, debut + voyagesParPage);

  zoneTuiles.innerHTML = '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3"></div>';
  const row = zoneTuiles.querySelector(".row");

  voyagesPage.forEach((voyage) => {
    const col = document.createElement("div");
    col.className = "col";

    col.innerHTML = `
      <div class="card shadow-sm">
        ${htmlImages(voyage, lireImages(voyage))}
        <div class="card-body d-flex flex-column">
          <h5 class="card-title">${echapper(champ(voyage, "titre"))}${voyage.titre_en ? ' <span class="badge bg-secondary align-middle" title="Version anglaise renseignée">EN</span>' : ""}</h5>
          <p class="mb-1">
            <strong>${echapper(champ(voyage, "categorie") || "Sans catégorie")}</strong>${resumeCommercial(voyage)}<br>
            <em>${echapper(champ(voyage, "continent"))}${voyage.pays ? " – " + echapper(champ(voyage, "pays")) : ""}</em>
          </p>
          <div class="card-description-wrapper mb-2 flex-grow-1">
            <div class="description-courte">${echapper(champ(voyage, "description"))}</div>
          </div>
          <button type="button" class="btn-voir-plus mb-2">Voir plus</button>
          <div class="d-flex justify-content-between align-items-center mt-auto">
            <div class="btn-group">
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-modifier modifier" data-id="${echapper(voyage.id)}">Modifier</button>
                <button type="button" class="btn btn-sm btn-supprimer supprimer" data-id="${echapper(voyage.id)}">Supprimer</button>
              </div>
            </div>
            <small class="text-muted">${echapper(voyage.date_depart)}</small>
          </div>
        </div>
      </div>`;
    row.appendChild(col);
  });

  afficherPagination(totalPages);
  activerBoutons();
}

/* Boutons de pagination */
function afficherPagination(totalPages) {
  paginationContainer.innerHTML = "";
  if (totalPages <= 1) return;
  for (let i = 1; i <= totalPages; i++) {
    const bouton = document.createElement("button");
    bouton.textContent = i;
    bouton.className = i === pageActuelle ? "actif" : "";
    bouton.addEventListener("click", () => {
      pageActuelle = i;
      afficherPage();
    });
    paginationContainer.appendChild(bouton);
  }
}

/* Remplit et ouvre la modale de modification */
function ouvrirModale(voyage) {
  document.getElementById("modif-id").value = voyage.id;
  document.getElementById("modif-titre").value = voyage.titre;
  document.getElementById("modif-titre-en").value = voyage.titre_en ?? "";
  document.getElementById("modif-date").value = voyage.date_depart;
  document.getElementById("modif-pays").value = voyage.pays || "";
  document.getElementById("modif-categorie").value = voyage.categorie || "";
  document.getElementById("modif-prix").value = voyage.prix ?? "";
  document.getElementById("modif-duree").value = voyage.duree_jours ?? "";
  document.getElementById("modif-places").value = voyage.places_disponibles ?? "";
  document.getElementById("modif-description").value = voyage.description || "";
  document.getElementById("modif-description-en").value = voyage.description_en ?? "";

  /* Réinitialisation des images de la modale */
  document.getElementById("modif-new-images").value = "";
  const preview = document.getElementById("modif-images-preview");
  preview.innerHTML = "";

  voyageActuel = voyage;
  voyageActuel.imagesListe = lireImages(voyage);

  voyageActuel.imagesListe.forEach((src, i) => {
    const div = document.createElement("div");
    div.className = "position-relative";
    div.innerHTML = `
      <img src="${echapper(urlImage(src))}" class="rounded border" style="width: 80px; height: 80px; object-fit: cover;" alt="">
      <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 btn-supprimer-image" data-index="${i}" style="transform: translate(50%,-50%)" aria-label="Retirer cette image">&times;</button>
    `;
    preview.appendChild(div);
  });

  new bootstrap.Modal(document.getElementById("modalModification")).show();
}

/* Activation des boutons d'action sur chaque tuile */
function activerBoutons() {
  document.querySelectorAll(".supprimer").forEach((btn) => {
    btn.addEventListener("click", () => {
      if (confirm("Confirmer la suppression ?")) {
        fetch("/php/voyages.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `supprimer=${btn.dataset.id}`,
        }).then(() => chargerTuiles());
      }
    });
  });

  document.querySelectorAll(".modifier").forEach((btn) => {
    btn.addEventListener("click", () => {
      const voyage = tousLesVoyages.find((v) => v.id == btn.dataset.id);
      if (voyage) ouvrirModale(voyage);
    });
  });

  /* Les écouteurs de la modale ne doivent être installés qu'une seule fois :
     activerBoutons() est rappelée à chaque affichage de page. */
  if (!ecouteursModaleInstalles) {
    ecouteursModaleInstalles = true;

    /* Suppression d'une image existante dans la modale */
    document.addEventListener("click", (e) => {
      if (e.target.classList.contains("btn-supprimer-image")) {
        e.preventDefault();
        voyageActuel.imagesListe.splice(e.target.dataset.index, 1);
        e.target.closest("div").remove();
      }
    });

    /* Soumission du formulaire de modification */
    document.getElementById("form-modification").addEventListener("submit", (e) => {
      e.preventDefault();
      const champs = {
        modifier_complet: "modif-id", titre: "modif-titre", titre_en: "modif-titre-en", date_depart: "modif-date",
        pays: "modif-pays", categorie: "modif-categorie", prix: "modif-prix", duree_jours: "modif-duree",
        places_disponibles: "modif-places", description: "modif-description", description_en: "modif-description-en"
      };
      const formData = new FormData();
      for (const [nom, id] of Object.entries(champs)) {
        formData.append(nom, document.getElementById(id).value);
      }
      formData.append("images_restantes", JSON.stringify(voyageActuel.imagesListe));

      const nouvellesImages = document.getElementById("modif-new-images").files;
      for (let i = 0; i < nouvellesImages.length; i++) {
        formData.append("new_images[]", nouvellesImages[i]);
      }

      fetch("/php/voyages.php", { method: "POST", body: formData })
        .then(async (res) => {
          if (await reponseOk(res)) {
            bootstrap.Modal.getInstance(document.getElementById("modalModification")).hide();
            chargerTuiles();
          }
        });
    });
  }

  /* Gestion du bouton "voir plus" pour les longues descriptions */
  document.querySelectorAll(".btn-voir-plus").forEach((btn) => {
    const wrapper = btn.previousElementSibling;
    const content = wrapper.querySelector(".description-courte");

    if (content.scrollHeight > wrapper.clientHeight + 1) {
      btn.style.display = "inline-block";
      btn.addEventListener("click", () => {
        wrapper.classList.toggle("expanded");
        btn.textContent = wrapper.classList.contains("expanded") ? "Réduire" : "Voir plus";
      });
    } else {
      btn.style.display = "none";
    }
  });
}

/* Note : la prévisualisation en direct du formulaire d'ajout est gérée par js/apercu.js */

chargerTuiles();
