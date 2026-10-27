const API_STYLES = "../../backend/admin/api/GestionStyle.php";

function afficherMessage(message, estSucces) {
  const container = document.getElementById("message-container");
  if (!container) return;

  container.textContent = message;
  container.setAttribute("role", "alert");
  if (estSucces) {
    container.className = "rounded-lg px-4 py-3 text-sm bg-green-50 text-green-800 ring-1 ring-green-200";
  } else {
    container.className = "rounded-lg px-4 py-3 text-sm bg-red-50 text-red-800 ring-1 ring-red-200";
  }
  container.classList.remove("hidden");
}

function chargerStyles() {
  fetch(API_STYLES)
    .then((response) => {
      if (!response.ok) {
        return response.json().then((err) => {
          throw new Error(err.erreur || "Erreur lors du chargement des styles");
        });
      }
      return response.json();
    })
    .then((styles) => {
      afficherTableauStyles(styles);
    })
    .catch((error) => {
      afficherMessage(error.message, false);
    });
}

function afficherTableauStyles(styles) {
  const tbody = document.getElementById("styles-table-body");
  if (!tbody) return;

  while (tbody.firstChild) {
    tbody.removeChild(tbody.firstChild);
  }

  if (!Array.isArray(styles) || styles.length === 0) {
    const trEmpty = document.createElement("tr");
    const tdEmpty = document.createElement("td");
    tdEmpty.colSpan = 4;
    tdEmpty.className = "text-center text-sm text-slate-500 py-10";
    tdEmpty.textContent = "Aucun style pour le moment.";
    trEmpty.appendChild(tdEmpty);
    tbody.appendChild(trEmpty);
    return;
  }

  styles.forEach((style) => {
    const tr = document.createElement("tr");
    tr.className = "hover:bg-slate-50 transition-colors";

    const tdId = document.createElement("td");
    tdId.className = "px-4 py-3 text-sm font-mono text-slate-400";
    tdId.textContent = style.id_style;
    tr.appendChild(tdId);

    const tdNom = document.createElement("td");
    tdNom.className = "px-4 py-3 text-sm font-medium text-slate-900";
    tdNom.textContent = style.nom_style;
    tr.appendChild(tdNom);

    const tdDesc = document.createElement("td");
    tdDesc.className = "px-4 py-3 text-sm text-slate-600";
    tdDesc.textContent = style.description;
    tr.appendChild(tdDesc);

    const tdActions = document.createElement("td");
    tdActions.className = "px-4 py-3 text-sm whitespace-nowrap text-right";

    const actionsWrapper = document.createElement("div");
    actionsWrapper.className = "flex gap-2 justify-end";

    const btnModifier = document.createElement("button");
    btnModifier.type = "button";
    btnModifier.textContent = "Modifier";
    btnModifier.className = "transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 focus-visible:ring-slate-400";
    btnModifier.addEventListener("click", () => {
      ouvrirFormulaireModifierStyle(style);
    });
    actionsWrapper.appendChild(btnModifier);

    const btnSupprimer = document.createElement("button");
    btnSupprimer.type = "button";
    btnSupprimer.textContent = "Supprimer";
    btnSupprimer.className = "transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium bg-white text-red-600 ring-1 ring-red-200 hover:bg-red-50 focus-visible:ring-red-500";
    btnSupprimer.addEventListener("click", () => {
      supprimerStyle(style.id_style);
    });
    actionsWrapper.appendChild(btnSupprimer);

    tdActions.appendChild(actionsWrapper);
    tr.appendChild(tdActions);
    tbody.appendChild(tr);
  });
}

function ouvrirFormulaireNouveauStyle() {
  const formContainer = document.getElementById("style-form-container");
  const formTitle = document.getElementById("style-form-title");
  const inputId = document.getElementById("style-id");
  const inputNom = document.getElementById("style-nom");
  const inputDesc = document.getElementById("style-description");

  if (!formContainer || !formTitle || !inputId || !inputNom || !inputDesc) return;

  formTitle.textContent = "Nouveau style photographique";
  inputId.value = "";
  inputNom.value = "";
  inputDesc.value = "";

  formContainer.classList.remove("hidden");
  inputNom.focus();
}

function ouvrirFormulaireModifierStyle(style) {
  const formContainer = document.getElementById("style-form-container");
  const formTitle = document.getElementById("style-form-title");
  const inputId = document.getElementById("style-id");
  const inputNom = document.getElementById("style-nom");
  const inputDesc = document.getElementById("style-description");

  if (!formContainer || !formTitle || !inputId || !inputNom || !inputDesc) return;

  formTitle.textContent = "Modifier le style photographique";
  inputId.value = style.id_style;
  inputNom.value = style.nom_style;
  inputDesc.value = style.description;

  formContainer.classList.remove("hidden");
  inputNom.focus();
}

function fermerFormulaireStyle() {
  const formContainer = document.getElementById("style-form-container");
  const form = document.getElementById("form-style");
  if (form) form.reset();
  if (formContainer) formContainer.classList.add("hidden");
}

function enregistrerStyle(e) {
  e.preventDefault();

  const inputId = document.getElementById("style-id");
  const inputNom = document.getElementById("style-nom");
  const inputDesc = document.getElementById("style-description");

  if (!inputNom || !inputDesc) return;

  const id = inputId ? inputId.value.trim() : "";
  const nom_style = inputNom.value.trim();
  const description = inputDesc.value.trim();

  const estModification = id !== "";
  const url = estModification ? `${API_STYLES}?id=${encodeURIComponent(id)}` : API_STYLES;
  const methode = estModification ? "PUT" : "POST";

  fetch(url, {
    method: methode,
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      nom_style: nom_style,
      description: description
    })
  })
    .then((response) => {
      return response.json().then((data) => {
        if (!response.ok) {
          throw new Error(data.erreur || "Erreur lors de l'enregistrement du style");
        }
        return data;
      });
    })
    .then(() => {
      afficherMessage(
        estModification ? "Style modifié avec succès." : "Style créé avec succès.",
        true
      );
      fermerFormulaireStyle();
      chargerStyles();
    })
    .catch((error) => {
      afficherMessage(error.message, false);
    });
}

function supprimerStyle(id) {
  if (!window.confirm("Êtes-vous sûr de vouloir supprimer ce style photographique ?")) {
    return;
  }

  fetch(`${API_STYLES}?id=${encodeURIComponent(id)}`, {
    method: "DELETE"
  })
    .then((response) => {
      return response.json().then((data) => {
        if (!response.ok) {
          throw new Error(data.erreur || "Erreur lors de la suppression du style");
        }
        return data;
      });
    })
    .then((data) => {
      afficherMessage(data.message || "Style supprimé avec succès.", true);
      chargerStyles();
    })
    .catch((error) => {
      afficherMessage(error.message, false);
    });
}

document.addEventListener("DOMContentLoaded", () => {
  const btnNouveau = document.getElementById("btn-nouveau-style");
  if (btnNouveau) {
    btnNouveau.addEventListener("click", ouvrirFormulaireNouveauStyle);
  }

  const btnAnnuler = document.getElementById("btn-annuler-style");
  if (btnAnnuler) {
    btnAnnuler.addEventListener("click", fermerFormulaireStyle);
  }

  const form = document.getElementById("form-style");
  if (form) {
    form.addEventListener("submit", enregistrerStyle);
  }

  chargerStyles();
});

window.addEventListener("hashchange", () => {
  if (window.location.hash === "#styles" || !window.location.hash) {
    chargerStyles();
  }
});
