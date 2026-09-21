// Fiche d'un voyage : galerie photo avec visionneuse plein écran, formulaire de devis et formulaire d'avis.
// Requiert js/commun.js (textes traduits).

document.addEventListener("DOMContentLoaded", () => {
  const { texte } = window.Latitudes;

  /* ----- Galerie ----- */
  const imagePrincipale = document.getElementById("galerie-principale");
  const boutonOuvrir = document.getElementById("galerie-ouvrir");
  const miniatures = document.querySelectorAll("#galerie-miniatures button");
  const lightboxEl = document.getElementById("lightbox");
  let indexCourant = 0;

  miniatures.forEach((bouton) => {
    bouton.addEventListener("click", () => {
      indexCourant = Number(bouton.dataset.index);
      imagePrincipale.src = bouton.querySelector("img").src;
      miniatures.forEach((b) => b.classList.toggle("active", b === bouton));
    });
  });

  if (boutonOuvrir && lightboxEl) {
    boutonOuvrir.addEventListener("click", () => {
      const carousel = bootstrap.Carousel.getOrCreateInstance("#lightbox-carousel", { interval: false });
      carousel.to(indexCourant);
      bootstrap.Modal.getOrCreateInstance(lightboxEl).show();
    });
  }

  /* ----- Formulaires envoyés en AJAX (devis et avis) ----- */

  /* Envoie le formulaire, affiche l'erreur du serveur ou le message de confirmation.
     `prefixe` : « devis » ou « avis » (identifiants des éléments et clés de texte). */
  function brancherFormulaire(prefixe) {
    const formulaire = document.getElementById(`form-${prefixe}`);
    if (!formulaire) return;

    const zoneErreur = document.getElementById(`${prefixe}-erreur`);
    const zoneSucces = document.getElementById(`${prefixe}-succes`);
    const bouton = document.getElementById(`${prefixe}-envoyer`);

    const afficherErreur = (message) => {
      zoneErreur.textContent = message;
      zoneErreur.classList.remove("d-none");
    };

    formulaire.addEventListener("submit", async (e) => {
      e.preventDefault();
      zoneErreur.classList.add("d-none");
      bouton.disabled = true;
      bouton.textContent = texte(`${prefixe}.sending`);

      try {
        const reponse = await fetch(formulaire.action, { method: "POST", body: new FormData(formulaire) });
        const donnees = await reponse.json().catch(() => ({}));

        if (reponse.ok && donnees.ok) {
          formulaire.classList.add("d-none");
          zoneSucces.classList.remove("d-none");
          return;
        }
        afficherErreur(donnees.erreur || texte(`${prefixe}.err_generic`));
      } catch {
        afficherErreur(texte(`${prefixe}.err_network`));
      }

      bouton.disabled = false;
      bouton.textContent = texte(`${prefixe}.send`);
    });
  }

  brancherFormulaire("devis");
  brancherFormulaire("avis");
});
