/**
 * Confirmation de suppression et validation cote client.
 *
 * ATTENTION : ce fichier n'est qu'une aide pour l'utilisateur.
 * La vraie validation est faite en PHP, cote serveur, dans functions.php.
 * Un visiteur peut desactiver JavaScript : le serveur doit donc toujours
 * revalider les donnees recues.
 */

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ------------------------------------------------------------------
    // 1. Confirmation avant suppression
    // ------------------------------------------------------------------
    document.querySelectorAll('.form-suppression').forEach(function (formulaire) {
        formulaire.addEventListener('submit', function (evenement) {
            var titre = formulaire.dataset.titre || 'cette image';
            var message = 'Voulez-vous vraiment supprimer "' + titre + '" ?\n\n'
                        + 'Le fichier sera definitivement efface.';

            if (!window.confirm(message)) {
                evenement.preventDefault();
            }
        });
    });

    // ------------------------------------------------------------------
    // 2. Validation du formulaire d'image
    // ------------------------------------------------------------------
    var formulaire = document.querySelector('.formulaire');
    if (!formulaire) {
        return;
    }

    var TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo
    var TYPES_OK   = ['image/jpeg', 'image/png', 'image/webp'];

    /** Affiche un message d'erreur sous un champ. */
    function afficherErreur(champ, message) {
        nettoyerErreur(champ);
        champ.classList.add('champ--erreur');

        var paragraphe = document.createElement('p');
        paragraphe.className = 'champ__erreur champ__erreur--js';
        paragraphe.textContent = message;
        champ.parentNode.appendChild(paragraphe);
    }

    /** Retire le message d'erreur genere par JavaScript. */
    function nettoyerErreur(champ) {
        champ.classList.remove('champ--erreur');
        var ancien = champ.parentNode.querySelector('.champ__erreur--js');
        if (ancien) {
            ancien.remove();
        }
    }

    // --- Verification du fichier des sa selection ---------------------
    var champFichier = formulaire.querySelector('#fichier_image');
    if (champFichier) {
        champFichier.addEventListener('change', function () {
            nettoyerErreur(champFichier);

            var fichier = champFichier.files[0];
            if (!fichier) {
                return;
            }

            if (TYPES_OK.indexOf(fichier.type) === -1) {
                afficherErreur(champFichier, 'Format non autorise. Choisissez un fichier JPG, PNG ou WEBP.');
                champFichier.value = '';
                return;
            }

            if (fichier.size > TAILLE_MAX) {
                var taille = (fichier.size / 1024 / 1024).toFixed(2);
                afficherErreur(champFichier, 'Le fichier fait ' + taille + ' Mo. La taille maximale est de 2 Mo.');
                champFichier.value = '';
            }
        });
    }

    // --- Verification globale a l'envoi -------------------------------
    formulaire.addEventListener('submit', function (evenement) {
        var valide = true;

        // Titre non vide
        var titre = formulaire.querySelector('#titre_image');
        if (titre) {
            nettoyerErreur(titre);
            if (titre.value.trim() === '') {
                afficherErreur(titre, 'Le titre est obligatoire.');
                valide = false;
            }
        }

        // Prix positif
        var prix = formulaire.querySelector('#prix_image');
        if (prix) {
            nettoyerErreur(prix);
            if (prix.value.trim() === '') {
                afficherErreur(prix, 'Le prix est obligatoire.');
                valide = false;
            } else if (parseFloat(prix.value) < 0) {
                afficherErreur(prix, 'Le prix ne peut pas etre negatif.');
                valide = false;
            }
        }

        // Date renseignee
        var date = formulaire.querySelector('#date_publication');
        if (date) {
            nettoyerErreur(date);
            if (date.value.trim() === '') {
                afficherErreur(date, 'La date de publication est obligatoire.');
                valide = false;
            }
        }

        // Les trois listes deroulantes (cles etrangeres obligatoires)
        ['#id_photographe', '#id_style', '#id_licence'].forEach(function (selecteur) {
            var liste = formulaire.querySelector(selecteur);
            if (!liste) {
                return;
            }
            nettoyerErreur(liste);
            if (liste.value === '') {
                afficherErreur(liste, 'Ce choix est obligatoire.');
                valide = false;
            }
        });

        if (!valide) {
            evenement.preventDefault();
            var premiereErreur = formulaire.querySelector('.champ--erreur');
            if (premiereErreur) {
                premiereErreur.scrollIntoView({ behavior: 'smooth', block: 'center' });
                premiereErreur.focus();
            }
        }
    });
});
