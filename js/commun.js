// Fonctions partagées par le catalogue public et l'espace admin :
// textes traduits, formats, échappement HTML et filtres (recherche, continent, pays, catégorie, tri).
// La configuration (langue + textes) est fournie par la page dans window.LATITUDES.

(function () {
  const ORDRE_CONTINENTS = ["Europe", "Amérique", "Asie", "Afrique", "Océanie"];
  const ORDRE_CATEGORIES = ["Nature", "Plage", "Montagne", "Safari", "Île tropicale", "Désert", "Culture"];

  const configuration = () => window.LATITUDES || { lang: "fr", textes: {} };

  /* ---------- Textes ---------- */

  function texte(cle, variables = {}) {
    const brut = configuration().textes[cle] ?? cle;
    return brut.replace(/\{(\w+)\}/g, (_, nom) => (nom in variables ? variables[nom] : `{${nom}}`));
  }

  /* « base.one » ou « base.other » selon le nombre (règle du pluriel de la langue) */
  function pluriel(base, n) {
    const singulier = configuration().lang === "fr" ? n < 2 : n === 1;
    return texte(base + (singulier ? ".one" : ".other"), { n });
  }

  /* ---------- Sécurité et formats ---------- */

  /* Échappe une valeur avant de l'insérer dans du HTML (les données viennent de la base) */
  function echapper(valeur) {
    const remplacements = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
    return String(valeur ?? "").replace(/[&<>"']/g, (c) => remplacements[c]);
  }

  /* Chemin « uploads/fichier.jpg » -> URL sûre « /uploads/fichier.jpg » */
  function urlImage(chemin) {
    return "/" + String(chemin).split("/").map(encodeURIComponent).join("/");
  }

  const localeIntl = () => (configuration().lang === "en" ? "en-CA" : "fr-CA");

  function formaterPrix(prix) {
    const nombre = parseFloat(prix);
    return new Intl.NumberFormat(localeIntl(), {
      style: "currency",
      currency: "CAD",
      minimumFractionDigits: Number.isInteger(nombre) ? 0 : 2,
      maximumFractionDigits: 2
    }).format(nombre);
  }

  function formaterDate(dateIso) {
    const [annee, mois, jour] = dateIso.split("-").map(Number);
    return new Date(annee, mois - 1, jour).toLocaleDateString(localeIntl(), {
      day: "numeric", month: "long", year: "numeric"
    });
  }

  const formaterDuree = (jours) => pluriel("duration", Number(jours));

  /* « 4,7 » en français, « 4.7 » en anglais */
  function formaterNote(note) {
    const texteNote = Number(note).toFixed(1);
    return configuration().lang === "en" ? texteNote : texteNote.replace(".", ",");
  }

  /* Date du jour au format AAAA-MM-JJ (heure locale), pour ne pas afficher de départs passés */
  const aujourdhui = () => new Date().toLocaleDateString("sv-SE");

  /* ---------- Données d'un voyage ---------- */

  /* Valeur affichable d'un champ : la version traduite fournie par le serveur, sinon la valeur brute */
  const champ = (voyage, nom) => voyage[nom + "_affiche"] ?? voyage[nom] ?? "";

  function lireImages(voyage) {
    try {
      const liste = JSON.parse(voyage.image);
      return Array.isArray(liste) ? liste : [voyage.image];
    } catch {
      return voyage.image ? [voyage.image] : [];
    }
  }

  /* ---------- Recherche ---------- */

  /* Minuscules et sans accents : « Désert » et « desert » se valent */
  function normaliser(valeur) {
    return String(valeur ?? "").normalize("NFD").replace(/[̀-ͯ]/g, "").toLowerCase();
  }

  /* Texte de recherche d'un voyage (titre, pays, continent, catégorie, description, dans les deux langues) */
  function indexRecherche(voyage) {
    if (voyage._index === undefined) {
      voyage._index = normaliser([
        voyage.titre, voyage.titre_en, voyage.description, voyage.description_en,
        voyage.pays, voyage.pays_affiche, voyage.continent, voyage.continent_affiche,
        voyage.categorie, voyage.categorie_affiche
      ].filter(Boolean).join(" "));
    }
    return voyage._index;
  }

  /* ---------- Tri ---------- */

  function trier(liste, tri) {
    const criteres = {
      "prix-asc": [(v) => v.prix, 1],
      "prix-desc": [(v) => v.prix, -1],
      "duree-asc": [(v) => v.duree_jours, 1]
    };
    if (!criteres[tri]) return liste; // « recent » : ordre du serveur (le plus récent d'abord)
    const [valeur, sens] = criteres[tri];
    return [...liste].sort((a, b) => {
      const x = valeur(a), y = valeur(b);
      const xVide = x === null || x === undefined, yVide = y === null || y === undefined;
      if (xVide && yVide) return 0;
      if (xVide) return 1; // les voyages sans prix / sans durée passent en dernier
      if (yVide) return -1;
      return (parseFloat(x) - parseFloat(y)) * sens;
    });
  }

  /* ---------- Filtres ---------- */

  /* Branche les champs de filtre. Les listes de continents, pays et catégories sont construites
     à partir des voyages réellement présents (donc jamais limitées à une liste fixe). */
  function creerFiltres({ champs, obtenirVoyages, surChangement }) {
    const { recherche, continent, pays, categorie, tri, reinit } = champs;

    /* Remplace les options d'un <select> (la première, « Tous… », est conservée) */
    function remplir(select, entrees) {
      const valeurActuelle = select.value;
      while (select.options.length > 1) select.remove(1);
      for (const [valeur, libelle] of entrees) select.add(new Option(libelle, valeur));
      select.value = entrees.some(([valeur]) => valeur === valeurActuelle) ? valeurActuelle : "";
    }

    /* Valeurs distinctes d'un champ, avec leur libellé traduit */
    function distincts(voyages, nom) {
      const valeurs = new Map();
      for (const v of voyages) {
        if (v[nom] && !valeurs.has(v[nom])) valeurs.set(v[nom], champ(v, nom));
      }
      return valeurs;
    }

    function selonOrdre(valeurs, ordre) {
      const rang = (cle) => (ordre.includes(cle) ? ordre.indexOf(cle) : ordre.length);
      return [...valeurs].sort((a, b) => rang(a[0]) - rang(b[0]) || a[1].localeCompare(b[1], configuration().lang));
    }

    function rafraichirOptions() {
      const voyages = obtenirVoyages();
      remplir(continent, selonOrdre(distincts(voyages, "continent"), ORDRE_CONTINENTS));
      const dansLeContinent = continent.value ? voyages.filter((v) => v.continent === continent.value) : voyages;
      remplir(pays, [...distincts(dansLeContinent, "pays")].sort((a, b) => a[1].localeCompare(b[1], configuration().lang)));
      remplir(categorie, selonOrdre(distincts(voyages, "categorie"), ORDRE_CATEGORIES));
    }

    /* Voyages qui respectent tous les filtres, dans l'ordre de tri choisi */
    function liste() {
      const mots = normaliser(recherche.value).split(/\s+/).filter(Boolean);
      const retenus = obtenirVoyages().filter((v) =>
        (!continent.value || v.continent === continent.value) &&
        (!pays.value || v.pays === pays.value) &&
        (!categorie.value || v.categorie === categorie.value) &&
        mots.every((mot) => indexRecherche(v).includes(mot))
      );
      return trier(retenus, tri.value);
    }

    let minuterie;
    recherche.addEventListener("input", () => {
      clearTimeout(minuterie);
      minuterie = setTimeout(surChangement, 120);
    });
    continent.addEventListener("change", () => { rafraichirOptions(); surChangement(); });
    pays.addEventListener("change", surChangement);
    categorie.addEventListener("change", surChangement);
    tri.addEventListener("change", surChangement);
    reinit.addEventListener("click", () => {
      recherche.value = "";
      continent.value = "";
      rafraichirOptions();
      pays.value = "";
      categorie.value = "";
      tri.value = "recent";
      surChangement();
    });

    return { rafraichirOptions, liste };
  }

  window.Latitudes = {
    texte, pluriel, echapper, urlImage, normaliser, champ, lireImages,
    formaterPrix, formaterDate, formaterDuree, formaterNote, aujourdhui, trier, creerFiltres
  };
})();
