const API_STYLES = "http://localhost:8000/backend/api/api_style.php";

function extraireJson(texte) {
    if (!texte) {
        return null;
    }
    const debut = texte.indexOf("{");
    const fin = texte.lastIndexOf("}");
    if (debut !== -1 && fin !== -1 && fin >= debut) {
        try {
            return JSON.parse(texte.substring(debut, fin + 1));
        } catch (e) {
            return null;
        }
    }
    return null;
}

function afficherMessage(message, type) {
    const container = document.getElementById("message-container");
    if (!container) {
        return;
    }
    container.textContent = "";

    const div = document.createElement("div");
    div.setAttribute("role", "alert");
    if (type === "succes") {
        div.className = "rounded-lg px-4 py-3 text-sm bg-green-50 text-green-800 ring-1 ring-green-200";
    } else {
        div.className = "rounded-lg px-4 py-3 text-sm bg-red-50 text-red-800 ring-1 ring-red-200";
    }
    div.textContent = message;
    container.appendChild(div);
}

function chargerStyles() {
    fetch(API_STYLES)
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Erreur lors de la récupération des styles.");
            }
            return response.json();
        })
        .then(function(styles) {
            afficherStyles(styles);
        })
        .catch(function(error) {
            afficherMessage(error.message, "erreur");
        });
}

function afficherStyles(styles) {
    const tbody = document.getElementById("tbody-styles");
    if (!tbody) {
        return;
    }
    tbody.textContent = "";

    if (!styles || styles.length === 0) {
        const trVide = document.createElement("tr");
        const tdVide = document.createElement("td");
        tdVide.colSpan = 4;
        tdVide.className = "px-4 py-6 text-center text-sm text-slate-500";
        tdVide.textContent = "Aucun style photographique pour le moment.";
        trVide.appendChild(tdVide);
        tbody.appendChild(trVide);
        return;
    }

    for (let i = 0; i < styles.length; i++) {
        const style = styles[i];
        const tr = document.createElement("tr");
        tr.className = "hover:bg-slate-50 transition-colors";

        const tdId = document.createElement("td");
        tdId.className = "px-4 py-3 text-sm text-slate-700 font-medium";
        tdId.textContent = style.id_style;
        tr.appendChild(tdId);

        const tdNom = document.createElement("td");
        tdNom.className = "px-4 py-3 text-sm text-slate-900 font-semibold";
        tdNom.textContent = style.nom_style;
        tr.appendChild(tdNom);

        const tdDesc = document.createElement("td");
        tdDesc.className = "px-4 py-3 text-sm text-slate-600";
        tdDesc.textContent = style.description;
        tr.appendChild(tdDesc);

        const tdActions = document.createElement("td");
        tdActions.className = "px-4 py-3 text-sm text-slate-700";

        const divActions = document.createElement("div");
        divActions.className = "flex items-center gap-2";

        const btnModif = document.createElement("button");
        btnModif.type = "button";
        btnModif.className = "bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium";
        btnModif.textContent = "Modifier";
        btnModif.addEventListener("click", function() {
            preparerModificationStyle(style);
        });
        divActions.appendChild(btnModif);

        const btnSuppr = document.createElement("button");
        btnSuppr.type = "button";
        btnSuppr.className = "bg-white text-red-600 ring-1 ring-red-200 hover:bg-red-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium";
        btnSuppr.textContent = "Supprimer";
        btnSuppr.addEventListener("click", function() {
            supprimerStyle(style.id_style);
        });
        divActions.appendChild(btnSuppr);

        tdActions.appendChild(divActions);
        tr.appendChild(tdActions);

        tbody.appendChild(tr);
    }
}

function preparerAjoutStyle() {
    const cardForm = document.getElementById("card-form-style");
    const titreForm = document.getElementById("form-style-titre");
    const inputId = document.getElementById("style-id");
    const form = document.getElementById("form-style");

    if (!cardForm || !titreForm || !inputId || !form) {
        return;
    }

    cardForm.classList.remove("hidden");
    titreForm.textContent = "Nouveau style";
    inputId.value = "";
    form.reset();
}

function preparerModificationStyle(style) {
    const cardForm = document.getElementById("card-form-style");
    const titreForm = document.getElementById("form-style-titre");
    const inputId = document.getElementById("style-id");
    const inputNom = document.getElementById("style-nom");
    const inputDescription = document.getElementById("style-description");

    if (!cardForm || !titreForm || !inputId || !inputNom || !inputDescription) {
        return;
    }

    cardForm.classList.remove("hidden");
    titreForm.textContent = "Modifier le style";
    inputId.value = style.id_style;
    inputNom.value = style.nom_style;
    inputDescription.value = style.description;
}

function annulerStyle() {
    const cardForm = document.getElementById("card-form-style");
    const inputId = document.getElementById("style-id");
    const form = document.getElementById("form-style");

    if (!cardForm || !inputId || !form) {
        return;
    }

    cardForm.classList.add("hidden");
    inputId.value = "";
    form.reset();
}

function enregistrerStyle(event) {
    event.preventDefault();

    const inputId = document.getElementById("style-id");
    const inputNom = document.getElementById("style-nom");
    const inputDescription = document.getElementById("style-description");
    const btnSubmit = document.getElementById("btn-submit-style");

    if (!inputId || !inputNom || !inputDescription || !btnSubmit) {
        return;
    }

    const id = inputId.value;
    const donnees = {
        nom_style: inputNom.value,
        description: inputDescription.value
    };

    btnSubmit.disabled = true;

    let url = API_STYLES;
    let methode = "POST";

    if (id !== "") {
        url = API_STYLES + "?id=" + id;
        methode = "PUT";
    }

    fetch(url, {
        method: methode,
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(donnees)
    })
    .then(function(response) {
        return response.text().then(function(texte) {
            let data = extraireJson(texte);
            if (!data) {
                try {
                    data = JSON.parse(texte);
                } catch (e) {
                    data = { erreur: texte };
                }
            }
            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    })
    .then(function(resultat) {
        btnSubmit.disabled = false;
        if (resultat.ok) {
            annulerStyle();
            chargerStyles();
            const messageSucces = (id === "") ? "Style ajouté avec succès." : "Style modifié avec succès.";
            afficherMessage(messageSucces, "succes");
        } else {
            const messageErreur = (resultat.data && resultat.data.erreur) ? resultat.data.erreur : "Erreur lors de l'enregistrement du style.";
            afficherMessage(messageErreur, "erreur");
        }
    })
    .catch(function(error) {
        btnSubmit.disabled = false;
        afficherMessage(error.message || "Erreur de connexion avec le serveur.", "erreur");
    });
}

function supprimerStyle(id) {
    if (!window.confirm("Êtes-vous sûr de vouloir supprimer ce style ?")) {
        return;
    }

    fetch(API_STYLES + "?id=" + id, {
        method: "DELETE"
    })
    .then(function(response) {
        return response.text().then(function(texte) {
            let data = extraireJson(texte);
            if (!data) {
                try {
                    data = JSON.parse(texte);
                } catch (e) {
                    data = { erreur: texte };
                }
            }
            return {
                ok: response.ok,
                status: response.status,
                data: data
            };
        });
    })
    .then(function(resultat) {
        if (resultat.ok) {
            chargerStyles();
            afficherMessage("Style supprimé avec succès.", "succes");
        } else {
            const messageErreur = (resultat.data && resultat.data.erreur) ? resultat.data.erreur : "Erreur lors de la suppression du style.";
            afficherMessage(messageErreur, "erreur");
        }
    })
    .catch(function(error) {
        afficherMessage(error.message || "Erreur de connexion avec le serveur.", "erreur");
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const btnNouveau = document.getElementById("btn-nouveau-style");
    const btnAnnuler = document.getElementById("btn-annuler-style");
    const form = document.getElementById("form-style");

    if (btnNouveau) {
        btnNouveau.addEventListener("click", preparerAjoutStyle);
    }
    if (btnAnnuler) {
        btnAnnuler.addEventListener("click", annulerStyle);
    }
    if (form) {
        form.addEventListener("submit", enregistrerStyle);
    }

    chargerStyles();
});

window.addEventListener("hashchange", function() {
    const hash = window.location.hash || "#styles";
    if (hash === "#styles") {
        chargerStyles();
    }
});
