// Catalogue public en lecture seule : filtres, tri et pagination, sans les actions de gestion
// (pas de Modifier/Supprimer, pas de formulaire d'ajout). Requiert js/commun.js.

const {
  texte, pluriel, echapper, urlImage, champ, lireImages,
  formaterPrix, formaterDate, formaterDuree, formaterNote, aujourdhui, creerFiltres
} = window.Latitudes;

let tousLesVoyages = [];
let pageActuelle = 1;
const voyagesParPage = 15;

const zoneTuiles = document.getElementById("zone-tuiles");
const compteur = document.getElementById("nb-resultats");

// Conteneur de pagination : celui de la page (avant le pied de page), sinon un conteneur créé à la volée
const paginationContainer = document.getElementById("pagination") ?? document.body.appendChild(Object.assign(document.createElement("div"), { id: "pagination" }));

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

function chargerTuiles() {
  fetch(`/php/voyages.php?lang=${encodeURIComponent(window.LATITUDES.lang)}`)
    .then((res) => res.json())
    .then((data) => {
      tousLesVoyages = data;
      filtres.rafraichirOptions();
      pageActuelle = 1;
      afficherPage();
    })
    .catch((err) => console.error("Erreur lors du chargement :", err));
}

/* Bloc prix / durée / disponibilité affiché en bas de carte */
function htmlPrix(voyage) {
  const prix = voyage.prix !== null && voyage.prix !== undefined
    ? `<div class="prix-carte">${echapper(formaterPrix(voyage.prix))} <small>${echapper(texte("card.per_person"))}</small></div>`
    : `<div class="prix-carte"><small>${echapper(texte("card.on_request"))}</small></div>`;

  const infos = [];
  if (voyage.duree_jours) infos.push(echapper(formaterDuree(voyage.duree_jours)));
  if (Number(voyage.nb_avis) > 0) {
    const detail = pluriel("avis.count", Number(voyage.nb_avis));
    infos.push(`<span class="note-carte" title="${echapper(detail)}">★ ${echapper(formaterNote(voyage.note_moyenne))} <span class="texte-doux">(${Number(voyage.nb_avis)})</span></span>`);
  }
  if (voyage.places_disponibles !== null && voyage.places_disponibles !== undefined && Number(voyage.places_disponibles) === 0) {
    infos.push(`<span class="badge badge-complet">${echapper(texte("card.full"))}</span>`);
  }
  return prix + (infos.length ? `<div class="infos-carte">${infos.join(" · ")}</div>` : "");
}

function htmlImages(voyage, images, titre) {
  const carouselId = `carousel-${voyage.id}`;
  const alt = (i) => echapper(texte("card.photo_alt", { title: titre, n: i + 1 }));

  if (images.length > 1) {
    return `
      <div id="${carouselId}" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
          ${images.map((src, i) => `
            <div class="carousel-item ${i === 0 ? "active" : ""}">
              <img src="${echapper(urlImage(src))}" class="d-block w-100" alt="${alt(i)}" loading="lazy">
            </div>`).join("")}
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#${carouselId}" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
          <span class="visually-hidden">${echapper(texte("lightbox.prev"))}</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#${carouselId}" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
          <span class="visually-hidden">${echapper(texte("lightbox.next"))}</span>
        </button>
      </div>`;
  }
  if (images.length === 1) {
    return `<img src="${echapper(urlImage(images[0]))}" class="card-img-top" alt="${echapper(titre)}" loading="lazy">`;
  }
  return "";
}

function afficherPage() {
  const voyagesFiltres = filtres.liste();
  compteur.textContent = pluriel("results", voyagesFiltres.length);

  const totalPages = Math.ceil(voyagesFiltres.length / voyagesParPage);
  const debut = (pageActuelle - 1) * voyagesParPage;
  const voyagesPage = voyagesFiltres.slice(debut, debut + voyagesParPage);

  if (voyagesFiltres.length === 0) {
    zoneTuiles.innerHTML = `<p class="text-center texte-doux py-5">${echapper(texte("results.none"))}</p>`;
    afficherPagination(0);
    return;
  }

  zoneTuiles.innerHTML = '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3"></div>';
  const row = zoneTuiles.querySelector(".row");
  const langue = encodeURIComponent(window.LATITUDES.lang);

  voyagesPage.forEach((voyage) => {
    const col = document.createElement("div");
    col.className = "col";

    const titre = champ(voyage, "titre");
    const lienFiche = `/voyage.php?id=${encodeURIComponent(voyage.id)}&lang=${langue}`;
    const departAVenir = voyage.date_depart && voyage.date_depart >= aujourdhui();
    const ligneDepart = departAVenir
      ? `<br><span class="infos-carte">${echapper(texte("card.departs", { date: formaterDate(voyage.date_depart) }))}</span>`
      : "";

    col.innerHTML = `
      <div class="card carte-catalogue shadow-sm">
        ${htmlImages(voyage, lireImages(voyage), titre)}
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><a href="${lienFiche}">${echapper(titre)}</a></h5>
          <p class="mb-1">
            <strong>${echapper(champ(voyage, "categorie") || texte("card.no_category"))}</strong><br>
            <em>${echapper(champ(voyage, "continent"))}${voyage.pays ? " – " + echapper(champ(voyage, "pays")) : ""}</em>${ligneDepart}
          </p>
          <div class="card-description-wrapper mb-2 flex-grow-1">
            <div class="description-courte">${echapper(champ(voyage, "description"))}</div>
          </div>
          <button type="button" class="btn-voir-plus mb-2">${echapper(texte("card.more"))}</button>
          <div class="d-flex justify-content-between align-items-end mt-auto gap-2">
            <div>${htmlPrix(voyage)}</div>
            <a href="${lienFiche}" class="btn btn-cta btn-sm">${echapper(texte("card.discover"))}</a>
          </div>
        </div>
      </div>`;
    row.appendChild(col);
  });

  afficherPagination(totalPages);
  activerBoutonsVoirPlus();
}

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
      zoneTuiles.scrollIntoView({ behavior: "smooth", block: "start" });
    });
    paginationContainer.appendChild(bouton);
  }
}

function activerBoutonsVoirPlus() {
  document.querySelectorAll(".btn-voir-plus").forEach((btn) => {
    const wrapper = btn.previousElementSibling;
    const content = wrapper.querySelector(".description-courte");

    // Texte plus long que les 3 lignes affichées : proposer « Voir plus »
    if (wrapper.classList.contains("expanded") || content.scrollHeight > content.clientHeight + 1) {
      btn.style.display = "inline-block";
      btn.addEventListener("click", () => {
        wrapper.classList.toggle("expanded");
        btn.textContent = texte(wrapper.classList.contains("expanded") ? "card.less" : "card.more");
      });
    } else {
      btn.style.display = "none";
    }
  });
}

chargerTuiles();
