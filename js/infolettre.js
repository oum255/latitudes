// Formulaire d'inscription à l'infolettre (pied de page). Les textes viennent d'attributs data-* du formulaire.

document.addEventListener("DOMContentLoaded", () => {
  const formulaire = document.getElementById("form-infolettre");
  if (!formulaire) return;

  const bouton = document.getElementById("infolettre-envoyer");
  const message = document.getElementById("infolettre-message");
  const champEmail = document.getElementById("infolettre-email");
  const caseConsentement = document.getElementById("infolettre-consentement");

  function afficher(texte, erreur) {
    message.textContent = texte;
    message.className = "small mt-2 " + (erreur ? "text-danger-emphasis" : "text-success-emphasis");
  }

  formulaire.addEventListener("submit", async (e) => {
    e.preventDefault();
    message.textContent = "";

    if (!caseConsentement.checked) {
      afficher(formulaire.dataset.consentement, true);
      caseConsentement.focus();
      return;
    }

    bouton.disabled = true;
    bouton.textContent = formulaire.dataset.envoi;
    try {
      const reponse = await fetch(formulaire.action, { method: "POST", body: new FormData(formulaire) });
      const donnees = await reponse.json().catch(() => ({}));
      if (reponse.ok && donnees.ok) {
        formulaire.reset();
        afficher(formulaire.dataset.succes, false);
      } else {
        afficher(donnees.erreur || formulaire.dataset.reseau, true);
      }
    } catch {
      afficher(formulaire.dataset.reseau, true);
    }
    bouton.disabled = false;
    bouton.textContent = formulaire.dataset.libelle;
  });
});
