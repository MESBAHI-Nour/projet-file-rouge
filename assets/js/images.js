const API_IMAGES = "http://localhost:8000/backend/api/api_image.php";
const API_STYLES_FOR_IMAGES = "http://localhost:8000/backend/api/api_style.php";
const API_PHOTOGRAPHES = "http://localhost:8000/backend/api/api_photographe.php";
const API_LICENCES = "http://localhost:8000/backend/api/api_licence.php";

let stylesDisponibles = [];
let photographesDisponibles = [];
let licencesDisponibles = [];

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

function chargerReferentiels() {
    fetch(API_STYLES_FOR_IMAGES)
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Erreur de chargement des styles.");
            }
            return response.json();
        })
        .then(function(styles) {
            stylesDisponibles = styles;
            remplirSelectStyles(styles);
            return fetch(API_PHOTOGRAPHES);
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Erreur de chargement des photographes.");
            }
            return response.json();
        })
        .then(function(photographes) {
            photographesDisponibles = photographes;
            remplirSelectPhotographes(photographes);
            return fetch(API_LICENCES);
        })
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Erreur de chargement des licences.");
            }
            return response.json();
        })
        .then(function(licences) {
            licencesDisponibles = licences;
            remplirSelectLicences(licences);
            chargerImages();
        })
        .catch(function(error) {
            afficherMessage(error.message, "erreur");
        });
}

function remplirSelectStyles(styles) {
    const select = document.getElementById("image-style");
    if (!select) {
        return;
    }
    select.textContent = "";

    const optionVide = document.createElement("option");
    optionVide.value = "";
    optionVide.textContent = "-- Choisir un style --";
    select.appendChild(optionVide);

    for (let i = 0; i < styles.length; i++) {
        const option = document.createElement("option");
        option.value = styles[i].id_style;
        option.textContent = styles[i].nom_style;
        select.appendChild(option);
    }
}

function remplirSelectPhotographes(photographes) {
    const select = document.getElementById("image-photographe");
    if (!select) {
        return;
    }
    select.textContent = "";

    const optionVide = document.createElement("option");
    optionVide.value = "";
    optionVide.textContent = "-- Choisir un photographe --";
    select.appendChild(optionVide);

    for (let i = 0; i < photographes.length; i++) {
        const option = document.createElement("option");
        option.value = photographes[i].id_photographe;
        option.textContent = photographes[i].prenom + " " + photographes[i].nom;
        select.appendChild(option);
    }
}

function remplirSelectLicences(licences) {
    const select = document.getElementById("image-licence");
    if (!select) {
        return;
    }
    select.textContent = "";

    const optionVide = document.createElement("option");
    optionVide.value = "";
    optionVide.textContent = "-- Choisir une licence --";
    select.appendChild(optionVide);

    for (let i = 0; i < licences.length; i++) {
        const option = document.createElement("option");
        option.value = licences[i].id_licence;
        option.textContent = licences[i].nom_licence;
        select.appendChild(option);
    }
}

function trouverNomStyle(idStyle) {
    for (let i = 0; i < stylesDisponibles.length; i++) {
        if (stylesDisponibles[i].id_style === idStyle) {
            return stylesDisponibles[i].nom_style;
        }
    }
    return "Inconnu (#" + idStyle + ")";
}

function trouverNomPhotographe(idPhotographe) {
    for (let i = 0; i < photographesDisponibles.length; i++) {
        if (photographesDisponibles[i].id_photographe === idPhotographe) {
            return photographesDisponibles[i].prenom + " " + photographesDisponibles[i].nom;
        }
    }
    return "Inconnu (#" + idPhotographe + ")";
}

function trouverNomLicence(idLicence) {
    for (let i = 0; i < licencesDisponibles.length; i++) {
        if (licencesDisponibles[i].id_licence === idLicence) {
            return licencesDisponibles[i].nom_licence;
        }
    }
    return "Inconnu (#" + idLicence + ")";
}

function chargerImages() {
    fetch(API_IMAGES)
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Erreur lors de la récupération des images.");
            }
            return response.json();
        })
        .then(function(images) {
            afficherImages(images);
        })
        .catch(function(error) {
            afficherMessage(error.message, "erreur");
        });
}

function afficherImages(images) {
    const tbody = document.getElementById("tbody-images");
    if (!tbody) {
        return;
    }
    tbody.textContent = "";

    if (!images || images.length === 0) {
        const trVide = document.createElement("tr");
        const tdVide = document.createElement("td");
        tdVide.colSpan = 9;
        tdVide.className = "px-4 py-6 text-center text-sm text-slate-500";
        tdVide.textContent = "Aucune image pour le moment.";
        trVide.appendChild(tdVide);
        tbody.appendChild(trVide);
        return;
    }

    for (let i = 0; i < images.length; i++) {
        const image = images[i];
        const tr = document.createElement("tr");
        tr.className = "hover:bg-slate-50 transition-colors";

        // ID
        const tdId = document.createElement("td");
        tdId.className = "px-4 py-3 text-sm text-slate-700 font-medium";
        tdId.textContent = image.id_image;
        tr.appendChild(tdId);

        // Miniature
        const tdMiniature = document.createElement("td");
        tdMiniature.className = "px-4 py-3 text-sm text-slate-700";
        const img = document.createElement("img");
        img.src = "../backend/" + image.url_image;
        img.alt = image.titre;
        img.className = "h-14 w-14 rounded-lg object-cover ring-1 ring-slate-200 transition-transform duration-300 ease-out hover:[transform:perspective(600px)_rotateY(-14deg)_rotateX(6deg)_scale(1.15)] hover:shadow-xl hover:relative hover:z-10 motion-reduce:transition-none motion-reduce:hover:[transform:none]";
        tdMiniature.appendChild(img);
        tr.appendChild(tdMiniature);

        // Titre
        const tdTitre = document.createElement("td");
        tdTitre.className = "px-4 py-3 text-sm text-slate-900 font-semibold";
        tdTitre.textContent = image.titre;
        tr.appendChild(tdTitre);

        // Style
        const tdStyle = document.createElement("td");
        tdStyle.className = "px-4 py-3 text-sm text-slate-700";
        tdStyle.textContent = trouverNomStyle(image.id_style);
        tr.appendChild(tdStyle);

        // Photographe
        const tdPhotographe = document.createElement("td");
        tdPhotographe.className = "px-4 py-3 text-sm text-slate-700";
        tdPhotographe.textContent = trouverNomPhotographe(image.id_photographe);
        tr.appendChild(tdPhotographe);

        // Licence
        const tdLicence = document.createElement("td");
        tdLicence.className = "px-4 py-3 text-sm text-slate-700";
        tdLicence.textContent = trouverNomLicence(image.id_licence);
        tr.appendChild(tdLicence);

        // Statut
        const tdStatut = document.createElement("td");
        tdStatut.className = "px-4 py-3 text-sm text-slate-700";
        const badge = document.createElement("span");
        if (image.statut === "valide") {
            badge.className = "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20";
            badge.textContent = "Validée";
        } else {
            badge.className = "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20";
            badge.textContent = "En attente";
        }
        tdStatut.appendChild(badge);
        tr.appendChild(tdStatut);

        // Date d'ajout
        const tdDate = document.createElement("td");
        tdDate.className = "px-4 py-3 text-sm text-slate-600";
        tdDate.textContent = image.date_ajout;
        tr.appendChild(tdDate);

        // Actions
        const tdActions = document.createElement("td");
        tdActions.className = "px-4 py-3 text-sm text-slate-700";
        const divActions = document.createElement("div");
        divActions.className = "flex items-center gap-2";

        // Bouton Modifier
        const btnModif = document.createElement("button");
        btnModif.type = "button";
        btnModif.className = "bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium";
        btnModif.textContent = "Modifier";
        btnModif.addEventListener("click", function() {
            preparerModificationImage(image);
        });
        divActions.appendChild(btnModif);

        // Bouton Valider (uniquement si le statut est "en_attente")
        if (image.statut === "en_attente") {
            const btnValider = document.createElement("button");
            btnValider.type = "button";
            btnValider.className = "bg-green-600 text-white hover:bg-green-700 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium";
            btnValider.textContent = "Valider";
            btnValider.addEventListener("click", function() {
                validerImage(image.id_image);
            });
            divActions.appendChild(btnValider);
        }

        // Bouton Supprimer
        const btnSuppr = document.createElement("button");
        btnSuppr.type = "button";
        btnSuppr.className = "bg-white text-red-600 ring-1 ring-red-200 hover:bg-red-50 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 rounded-lg px-3 py-1.5 text-xs font-medium";
        btnSuppr.textContent = "Supprimer";
        btnSuppr.addEventListener("click", function() {
            supprimerImage(image.id_image);
        });
        divActions.appendChild(btnSuppr);

        tdActions.appendChild(divActions);
        tr.appendChild(tdActions);

        tbody.appendChild(tr);
    }
}

function preparerAjoutImage() {
    const cardForm = document.getElementById("card-form-image");
    const titreForm = document.getElementById("form-image-titre");
    const inputId = document.getElementById("image-id");
    const form = document.getElementById("form-image");
    const labelFichier = document.getElementById("label-image-fichier");
    const inputFichier = document.getElementById("image-fichier");
    const apercuContainer = document.getElementById("apercu-image-container");

    if (!cardForm || !titreForm || !inputId || !form || !labelFichier || !inputFichier || !apercuContainer) {
        return;
    }

    cardForm.classList.remove("hidden");
    titreForm.textContent = "Nouvelle image";
    inputId.value = "";
    form.reset();

    labelFichier.textContent = "Photo (obligatoire)";
    inputFichier.required = true;
    apercuContainer.classList.add("hidden");
}

function preparerModificationImage(image) {
    const cardForm = document.getElementById("card-form-image");
    const titreForm = document.getElementById("form-image-titre");
    const inputId = document.getElementById("image-id");
    const inputTitre = document.getElementById("image-titre");
    const selectStyle = document.getElementById("image-style");
    const selectPhotographe = document.getElementById("image-photographe");
    const selectLicence = document.getElementById("image-licence");
    const inputDescription = document.getElementById("image-description");
    const labelFichier = document.getElementById("label-image-fichier");
    const inputFichier = document.getElementById("image-fichier");
    const apercuContainer = document.getElementById("apercu-image-container");
    const apercuImage = document.getElementById("apercu-image");

    if (!cardForm || !titreForm || !inputId || !inputTitre || !selectStyle || !selectPhotographe || !selectLicence || !inputDescription || !labelFichier || !inputFichier || !apercuContainer || !apercuImage) {
        return;
    }

    cardForm.classList.remove("hidden");
    titreForm.textContent = "Modifier l'image";
    inputId.value = image.id_image;
    inputTitre.value = image.titre;
    selectStyle.value = image.id_style;
    selectPhotographe.value = image.id_photographe;
    selectLicence.value = image.id_licence;
    inputDescription.value = image.description;

    labelFichier.textContent = "Remplacer la photo (facultatif)";
    inputFichier.required = false;
    inputFichier.value = "";

    apercuContainer.classList.remove("hidden");
    apercuImage.src = "../backend/" + image.url_image;
}

function annulerImage() {
    const cardForm = document.getElementById("card-form-image");
    const inputId = document.getElementById("image-id");
    const form = document.getElementById("form-image");
    const inputFichier = document.getElementById("image-fichier");
    const apercuContainer = document.getElementById("apercu-image-container");

    if (!cardForm || !inputId || !form || !inputFichier || !apercuContainer) {
        return;
    }

    cardForm.classList.add("hidden");
    inputId.value = "";
    inputFichier.value = "";
    apercuContainer.classList.add("hidden");
    form.reset();
}

function enregistrerImage(event) {
    event.preventDefault();

    const inputId = document.getElementById("image-id");
    const inputTitre = document.getElementById("image-titre");
    const selectStyle = document.getElementById("image-style");
    const selectPhotographe = document.getElementById("image-photographe");
    const selectLicence = document.getElementById("image-licence");
    const inputDescription = document.getElementById("image-description");
    const inputFichier = document.getElementById("image-fichier");
    const btnSubmit = document.getElementById("btn-submit-image");

    if (!inputId || !inputTitre || !selectStyle || !selectPhotographe || !selectLicence || !inputDescription || !inputFichier || !btnSubmit) {
        return;
    }

    const id = inputId.value;
    const formData = new FormData();
    formData.append("titre", inputTitre.value);
    formData.append("id_style", selectStyle.value);
    formData.append("id_photographe", selectPhotographe.value);
    formData.append("id_licence", selectLicence.value);
    formData.append("description", inputDescription.value);

    if (inputFichier.files.length > 0) {
        formData.append("fichier", inputFichier.files[0]);
    }

    btnSubmit.disabled = true;

    let url = API_IMAGES;
    if (id !== "") {
        url = API_IMAGES + "?id=" + id + "&action=modifier";
    }

    fetch(url, {
        method: "POST",
        body: formData
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
            annulerImage();
            chargerImages();
            const messageSucces = (id === "") ? "Image ajoutée avec succès." : "Image modifiée avec succès.";
            afficherMessage(messageSucces, "succes");
        } else {
            const messageErreur = (resultat.data && resultat.data.erreur) ? resultat.data.erreur : "Erreur lors de l'enregistrement de l'image.";
            afficherMessage(messageErreur, "erreur");
        }
    })
    .catch(function(error) {
        btnSubmit.disabled = false;
        afficherMessage(error.message || "Erreur de connexion avec le serveur.", "erreur");
    });
}

function validerImage(id) {
    fetch(API_IMAGES + "?id=" + id + "&action=valider", {
        method: "PUT"
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
            chargerImages();
            afficherMessage("Image validée avec succès.", "succes");
        } else {
            const messageErreur = (resultat.data && resultat.data.erreur) ? resultat.data.erreur : "Erreur lors de la validation de l'image.";
            afficherMessage(messageErreur, "erreur");
        }
    })
    .catch(function(error) {
        afficherMessage(error.message || "Erreur de connexion avec le serveur.", "erreur");
    });
}

function supprimerImage(id) {
    if (!window.confirm("Êtes-vous sûr de vouloir supprimer cette image ?")) {
        return;
    }

    fetch(API_IMAGES + "?id=" + id, {
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
            chargerImages();
            afficherMessage("Image supprimée avec succès.", "succes");
        } else {
            const messageErreur = (resultat.data && resultat.data.erreur) ? resultat.data.erreur : "Erreur lors de la suppression de l'image.";
            afficherMessage(messageErreur, "erreur");
        }
    })
    .catch(function(error) {
        afficherMessage(error.message || "Erreur de connexion avec le serveur.", "erreur");
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const btnNouveau = document.getElementById("btn-nouvelle-image");
    const btnAnnuler = document.getElementById("btn-annuler-image");
    const form = document.getElementById("form-image");

    if (btnNouveau) {
        btnNouveau.addEventListener("click", preparerAjoutImage);
    }
    if (btnAnnuler) {
        btnAnnuler.addEventListener("click", annulerImage);
    }
    if (form) {
        form.addEventListener("submit", enregistrerImage);
    }

    chargerReferentiels();
});

window.addEventListener("hashchange", function() {
    const hash = window.location.hash || "#styles";
    if (hash === "#images") {
        if (stylesDisponibles.length === 0 || photographesDisponibles.length === 0 || licencesDisponibles.length === 0) {
            chargerReferentiels();
        } else {
            chargerImages();
        }
    }
});
