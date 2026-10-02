const API_IMAGES = "../../backend/admin/api/GestionImage.php";
const API_STYLES_FOR_IMAGES = "../../backend/admin/api/GestionStyle.php";
const API_PHOTOGRAPHES = "../../backend/admin/api/GestionPhotographe.php";
const API_LICENCES = "../../backend/admin/api/GestionLicence.php";

let cacheStyles = [];
let cachePhotographes = [];
let cacheLicences = [];

function chargerImages() {
  Promise.all([
    fetch(API_IMAGES),
    fetch(API_STYLES_FOR_IMAGES),
    fetch(API_PHOTOGRAPHES),
    fetch(API_LICENCES)
  ])
    .then(async ([resImages, resStyles, resPhotographes, resLicences]) => {
      if (!resImages.ok) {
        const err = await resImages.json().catch(() => ({}));
        throw new Error(err.erreur || "Erreur lors du chargement des images");
      }
      if (!resStyles.ok) {
        throw new Error("Erreur lors du chargement des styles");
      }
      if (!resPhotographes.ok) {
        throw new Error("Erreur lors du chargement des photographes");
      }
      if (!resLicences.ok) {
        throw new Error("Erreur lors du chargement des licences");
      }

      const images = await resImages.json();
      const styles = await resStyles.json();
      const photographes = await resPhotographes.json();
      const licences = await resLicences.json();

      return { images, styles, photographes, licences };
    })
    .then(({ images, styles, photographes, licences }) => {
      cacheStyles = Array.isArray(styles) ? styles : [];
      cachePhotographes = Array.isArray(photographes) ? photographes : [];
      cacheLicences = Array.isArray(licences) ? licences : [];

      remplirSelectsImage();

      const stylesMap = {};
      cacheStyles.forEach((s) => {
        stylesMap[s.id_style] = s.nom_style;
      });

      const photographesMap = {};
      cachePhotographes.forEach((p) => {
        photographesMap[p.id_photographe] = `${p.prenom} ${p.nom}`;
      });

      const licencesMap = {};
      cacheLicences.forEach((l) => {
        licencesMap[l.id_licence] = l.nom_licence;
      });

      afficherTableauImages(images, stylesMap, photographesMap, licencesMap);
    })
    .catch((error) => {
      if (typeof afficherMessage === "function") {
        afficherMessage(error.message, false);
      }
    });
}

function remplirSelectsImage() {
  const selectStyle = document.getElementById("image-style");
  const selectPhotographe = document.getElementById("image-photographe");
  const selectLicence = document.getElementById("image-licence");

  if (selectStyle) {
    while (selectStyle.firstChild) {
      selectStyle.removeChild(selectStyle.firstChild);
    }
    const optDefaut = document.createElement("option");
    optDefaut.value = "";
    optDefaut.textContent = "Sélectionnez un style...";
    selectStyle.appendChild(optDefaut);

    cacheStyles.forEach((s) => {
      const opt = document.createElement("option");
      opt.value = s.id_style;
      opt.textContent = s.nom_style;
      selectStyle.appendChild(opt);
    });
  }

  if (selectPhotographe) {
    while (selectPhotographe.firstChild) {
      selectPhotographe.removeChild(selectPhotographe.firstChild);
    }
    const optDefaut = document.createElement("option");
    optDefaut.value = "";
    optDefaut.textContent = "Sélectionnez un photographe...";
    selectPhotographe.appendChild(optDefaut);

    cachePhotographes.forEach((p) => {
      const opt = document.createElement("option");
      opt.value = p.id_photographe;
      opt.textContent = `${p.prenom} ${p.nom}`;
      selectPhotographe.appendChild(opt);
    });
  }

  if (selectLicence) {
    while (selectLicence.firstChild) {
      selectLicence.removeChild(selectLicence.firstChild);
    }
    const optDefaut = document.createElement("option");
    optDefaut.value = "";
    optDefaut.textContent = "Sélectionnez une licence...";
    selectLicence.appendChild(optDefaut);

    cacheLicences.forEach((l) => {
      const opt = document.createElement("option");
      opt.value = l.id_licence;
      opt.textContent = l.nom_licence;
      selectLicence.appendChild(opt);
    });
  }
}

function afficherTableauImages(images, stylesMap, photographesMap, licencesMap) {
  const tbody = document.getElementById("images-table-body");
  if (!tbody) return;

  while (tbody.firstChild) {
    tbody.removeChild(tbody.firstChild);
  }

  if (!Array.isArray(images) || images.length === 0) {
    const trEmpty = document.createElement("tr");
    const tdEmpty = document.createElement("td");
    tdEmpty.colSpan = 9;
    tdEmpty.className = "text-center text-sm text-slate-500 py-10";
    tdEmpty.textContent = "Aucune image pour le moment.";
    trEmpty.appendChild(tdEmpty);
    tbody.appendChild(trEmpty);
    return;
  }

  images.forEach((image) => {
    const tr = document.createElement("tr");
    tr.className = "hover:bg-slate-50 transition-colors";

    // ID
    const tdId = document.createElement("td");
    tdId.className = "px-4 py-3 text-sm font-mono text-slate-400";
    tdId.textContent = image.id_image;
    tr.appendChild(tdId);

    // Miniature avec effet 3D
    const tdImg = document.createElement("td");
    tdImg.className = "px-4 py-3 text-sm";
    const imgTag = document.createElement("img");
    imgTag.src = "../../backend/" + image.url_image;
    imgTag.alt = image.titre;
    imgTag.className = "h-14 w-14 rounded-lg object-cover ring-1 ring-slate-200 transition-transform duration-300 ease-out hover:[transform:perspective(600px)_rotateY(-14deg)_rotateX(6deg)_scale(1.15)] hover:shadow-xl hover:relative hover:z-10 motion-reduce:transition-none motion-reduce:hover:[transform:none]";
    tdImg.appendChild(imgTag);
    tr.appendChild(tdImg);

    // Titre
    const tdTitre = document.createElement("td");
    tdTitre.className = "px-4 py-3 text-sm font-medium text-slate-900";
    tdTitre.textContent = image.titre;
    tr.appendChild(tdTitre);

    // Style
    const tdStyle = document.createElement("td");
    tdStyle.className = "px-4 py-3 text-sm text-slate-700";
    tdStyle.textContent = stylesMap[image.id_style] || ("Style #" + image.id_style);
    tr.appendChild(tdStyle);

    // Photographe
    const tdPhotographe = document.createElement("td");
    tdPhotographe.className = "px-4 py-3 text-sm text-slate-700";
    tdPhotographe.textContent = photographesMap[image.id_photographe] || ("Photographe #" + image.id_photographe);
    tr.appendChild(tdPhotographe);

    // Licence
    const tdLicence = document.createElement("td");
    tdLicence.className = "px-4 py-3 text-sm text-slate-700";
    tdLicence.textContent = licencesMap[image.id_licence] || ("Licence #" + image.id_licence);
    tr.appendChild(tdLicence);

    // Statut
    const tdStatut = document.createElement("td");
    tdStatut.className = "px-4 py-3 text-sm";
    const badge = document.createElement("span");
    const baseBadgeClasses = "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium";
    if (image.statut === "valide") {
      badge.textContent = "Validée";
      badge.className = baseBadgeClasses + " bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20";
    } else {
      badge.textContent = "En attente";
      badge.className = baseBadgeClasses + " bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20";
    }
    tdStatut.appendChild(badge);
    tr.appendChild(tdStatut);

    // Date d'ajout
    const tdDate = document.createElement("td");
    tdDate.className = "px-4 py-3 text-sm text-slate-500";
    tdDate.textContent = image.date_ajout;
    tr.appendChild(tdDate);

    // Actions
    const tdActions = document.createElement("td");
    tdActions.className = "px-4 py-3 text-sm whitespace-nowrap text-right";

    const actionsWrapper = document.createElement("div");
    actionsWrapper.className = "flex gap-2 justify-end";

    // Bouton Modifier
    const btnModifier = document.createElement("button");
    btnModifier.type = "button";
    btnModifier.textContent = "Modifier";
    btnModifier.className = "transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 focus-visible:ring-slate-400";
    btnModifier.addEventListener("click", function() {
      ouvrirFormulaireModifierImage(image);
    });
    actionsWrapper.appendChild(btnModifier);

    // Bouton Valider (seulement si en_attente)
    if (image.statut === "en_attente") {
      const btnValider = document.createElement("button");
      btnValider.type = "button";
      btnValider.textContent = "Valider";
      btnValider.className = "transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium bg-green-600 text-white hover:bg-green-700 focus-visible:ring-green-500";
      btnValider.addEventListener("click", function() {
        validerImage(image.id_image);
      });
      actionsWrapper.appendChild(btnValider);
    }

    // Bouton Supprimer
    const btnSupprimer = document.createElement("button");
    btnSupprimer.type = "button";
    btnSupprimer.textContent = "Supprimer";
    btnSupprimer.className = "transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium bg-white text-red-600 ring-1 ring-red-200 hover:bg-red-50 focus-visible:ring-red-500";
    btnSupprimer.addEventListener("click", function() {
      supprimerImage(image.id_image);
    });
    actionsWrapper.appendChild(btnSupprimer);

    tdActions.appendChild(actionsWrapper);
    tr.appendChild(tdActions);
    tbody.appendChild(tr);
  });
}

function ouvrirFormulaireNouvelleImage() {
  const formContainer = document.getElementById("image-form-container");
  const formTitle = document.getElementById("image-form-title");
  const inputId = document.getElementById("image-id");
  const inputTitre = document.getElementById("image-titre");
  const inputDescription = document.getElementById("image-description");
  const selectStyle = document.getElementById("image-style");
  const selectPhotographe = document.getElementById("image-photographe");
  const selectLicence = document.getElementById("image-licence");
  const inputFichier = document.getElementById("image-fichier");
  const labelFichier = document.getElementById("image-fichier-label");
  const apercuContainer = document.getElementById("image-apercu-container");
  const imgApercu = document.getElementById("image-apercu");

  if (!formContainer || !formTitle || !inputId || !inputTitre || !inputDescription || !inputFichier) return;

  const form = document.getElementById("form-image");
  if (form) form.reset();

  formTitle.textContent = "Nouvelle image";
  inputId.value = "";
  inputTitre.value = "";
  inputDescription.value = "";
  if (selectStyle) selectStyle.value = "";
  if (selectPhotographe) selectPhotographe.value = "";
  if (selectLicence) selectLicence.value = "";
  inputFichier.value = "";
  inputFichier.required = true;

  if (labelFichier) {
    labelFichier.textContent = "Photo";
  }

  if (apercuContainer) {
    apercuContainer.classList.add("hidden");
  }
  if (imgApercu) {
    imgApercu.src = "";
  }

  formContainer.classList.remove("hidden");
  inputTitre.focus();
}

function ouvrirFormulaireModifierImage(image) {
  const formContainer = document.getElementById("image-form-container");
  const formTitle = document.getElementById("image-form-title");
  const inputId = document.getElementById("image-id");
  const inputTitre = document.getElementById("image-titre");
  const inputDescription = document.getElementById("image-description");
  const selectStyle = document.getElementById("image-style");
  const selectPhotographe = document.getElementById("image-photographe");
  const selectLicence = document.getElementById("image-licence");
  const inputFichier = document.getElementById("image-fichier");
  const labelFichier = document.getElementById("image-fichier-label");
  const apercuContainer = document.getElementById("image-apercu-container");
  const imgApercu = document.getElementById("image-apercu");

  if (!formContainer || !formTitle || !inputId || !inputTitre || !inputDescription || !inputFichier) return;

  formTitle.textContent = "Modifier l'image";
  inputId.value = image.id_image;
  inputTitre.value = image.titre;
  inputDescription.value = image.description;
  if (selectStyle) selectStyle.value = image.id_style;
  if (selectPhotographe) selectPhotographe.value = image.id_photographe;
  if (selectLicence) selectLicence.value = image.id_licence;

  inputFichier.value = "";
  inputFichier.required = false;

  if (labelFichier) {
    labelFichier.textContent = "Remplacer la photo (facultatif)";
  }

  if (apercuContainer && imgApercu) {
    imgApercu.src = "../../backend/" + image.url_image;
    imgApercu.alt = image.titre;
    apercuContainer.classList.remove("hidden");
  }

  formContainer.classList.remove("hidden");
  inputTitre.focus();
}

function fermerFormulaireImage() {
  const formContainer = document.getElementById("image-form-container");
  const form = document.getElementById("form-image");
  const apercuContainer = document.getElementById("image-apercu-container");
  const inputFichier = document.getElementById("image-fichier");

  if (form) form.reset();
  if (inputFichier) inputFichier.value = "";
  if (apercuContainer) apercuContainer.classList.add("hidden");
  if (formContainer) formContainer.classList.add("hidden");
}

function enregistrerImage(e) {
  e.preventDefault();

  const inputId = document.getElementById("image-id");
  const inputTitre = document.getElementById("image-titre");
  const inputDescription = document.getElementById("image-description");
  const selectStyle = document.getElementById("image-style");
  const selectPhotographe = document.getElementById("image-photographe");
  const selectLicence = document.getElementById("image-licence");
  const inputFichier = document.getElementById("image-fichier");
  const btnSubmit = document.getElementById("btn-valider-image");

  if (!inputTitre || !inputDescription || !selectStyle || !selectPhotographe || !selectLicence || !inputFichier) {
    return;
  }

  const id = inputId ? inputId.value.trim() : "";
  const estModification = id !== "";

  const formData = new FormData();
  formData.append("titre", inputTitre.value.trim());
  formData.append("description", inputDescription.value.trim());
  formData.append("id_style", selectStyle.value);
  formData.append("id_photographe", selectPhotographe.value);
  formData.append("id_licence", selectLicence.value);

  if (inputFichier.files && inputFichier.files.length > 0) {
    formData.append("fichier", inputFichier.files[0]);
  }

  const url = estModification
    ? API_IMAGES + "?id=" + encodeURIComponent(id) + "&action=modifier"
    : API_IMAGES;

  if (btnSubmit) {
    btnSubmit.disabled = true;
  }

  fetch(url, {
    method: "POST",
    body: formData
  })
    .then(function(response) {
      return response.json().then(function(data) {
        if (!response.ok) {
          throw new Error(data.erreur || "Erreur lors de l'enregistrement de l'image");
        }
        return data;
      });
    })
    .then(function() {
      if (typeof afficherMessage === "function") {
        afficherMessage(
          estModification ? "Image modifiée avec succès." : "Image ajoutée avec succès.",
          true
        );
      }
      fermerFormulaireImage();
      chargerImages();
    })
    .catch(function(error) {
      if (typeof afficherMessage === "function") {
        afficherMessage(error.message, false);
      }
    })
    .finally(function() {
      if (btnSubmit) {
        btnSubmit.disabled = false;
      }
    });
}

function validerImage(id) {
  fetch(API_IMAGES + "?id=" + encodeURIComponent(id) + "&action=valider", {
    method: "PUT"
  })
    .then(function(response) {
      return response.json().then(function(data) {
        if (!response.ok) {
          throw new Error(data.erreur || "Erreur lors de la validation de l'image");
        }
        return data;
      });
    })
    .then(function() {
      if (typeof afficherMessage === "function") {
        afficherMessage("Image validée avec succès.", true);
      }
      chargerImages();
    })
    .catch(function(error) {
      if (typeof afficherMessage === "function") {
        afficherMessage(error.message, false);
      }
    });
}

function supprimerImage(id) {
  if (!window.confirm("Êtes-vous sûr de vouloir supprimer cette image ?")) {
    return;
  }

  fetch(API_IMAGES + "?id=" + encodeURIComponent(id), {
    method: "DELETE"
  })
    .then(function(response) {
      return response.json().then(function(data) {
        if (!response.ok) {
          throw new Error(data.erreur || "Erreur lors de la suppression de l'image");
        }
        return data;
      });
    })
    .then(function(data) {
      if (typeof afficherMessage === "function") {
        afficherMessage(data.message || "Image supprimée avec succès.", true);
      }
      chargerImages();
    })
    .catch(function(error) {
      if (typeof afficherMessage === "function") {
        afficherMessage(error.message, false);
      }
    });
}

document.addEventListener("DOMContentLoaded", function() {
  const btnNouvelleImage = document.getElementById("btn-nouvelle-image");
  if (btnNouvelleImage) {
    btnNouvelleImage.addEventListener("click", ouvrirFormulaireNouvelleImage);
  }

  const btnAnnulerImage = document.getElementById("btn-annuler-image");
  if (btnAnnulerImage) {
    btnAnnulerImage.addEventListener("click", fermerFormulaireImage);
  }

  const formImage = document.getElementById("form-image");
  if (formImage) {
    formImage.addEventListener("submit", enregistrerImage);
  }

  chargerImages();
});

window.addEventListener("hashchange", function() {
  if (window.location.hash === "#images") {
    chargerImages();
  }
});