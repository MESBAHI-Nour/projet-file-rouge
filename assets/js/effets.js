/**
 * EFFETS.JS
 * Animations de l interface : inclinaison 3D des cartes, apparition au
 * defilement, ombre de la barre de navigation.
 *
 * Tout est facultatif : si le JavaScript ne se charge pas, ou si
 * l utilisateur a demande de reduire les animations, le site reste
 * entierement utilisable. Aucune donnee ne depend de ce fichier.
 */

'use strict';

(function () {

    // La classe .js sur <html> permet au CSS de savoir que le JavaScript
    // est actif. Sans elle, les elements .reveler restent visibles.
    document.documentElement.classList.add('js');

    // Reglage systeme : l utilisateur prefere-t-il eviter les animations ?
    var animationsReduites = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Appareil tactile : pas de souris, donc pas d inclinaison 3D.
    var appareilTactile = window.matchMedia('(hover: none)').matches;

    document.addEventListener('DOMContentLoaded', function () {

        /* -------------------------------------------------------------
           1. APPARITION AU DEFILEMENT
           IntersectionObserver previent quand un element entre dans
           l ecran. Plus economique qu ecouter l evenement scroll.
           ------------------------------------------------------------- */
        var aReveler = document.querySelectorAll('.reveler');

        if (animationsReduites || !('IntersectionObserver' in window)) {
            // Pas d animation : on affiche tout immediatement
            aReveler.forEach(function (el) { el.classList.add('visible'); });
        } else {
            var observateur = new IntersectionObserver(function (entrees) {
                entrees.forEach(function (entree) {
                    if (entree.isIntersecting) {
                        entree.target.classList.add('visible');
                        observateur.unobserve(entree.target);   // une seule fois
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

            aReveler.forEach(function (el, index) {
                // Decalage progressif : les elements apparaissent en cascade
                el.style.setProperty('--retard', Math.min(index * 70, 420) + 'ms');
                observateur.observe(el);
            });

            // FILET DE SECURITE
            // Si pour une raison quelconque l observateur ne se declenche
            // pas (onglet en arriere-plan, navigateur exotique, erreur),
            // on affiche tout au bout de 2,5 secondes.
            // Sans cela, un contenu masque au depart resterait invisible.
            window.setTimeout(function () {
                aReveler.forEach(function (el) { el.classList.add('visible'); });
            }, 2500);
        }

        /* -------------------------------------------------------------
           2. INCLINAISON 3D DES CARTES
           On calcule la position du curseur a l interieur de la carte,
           puis on ecrit deux variables CSS que la feuille de style
           utilise pour la rotation.
           ------------------------------------------------------------- */
        if (!animationsReduites && !appareilTactile) {

            var INCLINAISON_MAX = 7;   // en degres : au-dela, l effet devient lourd
            var cartes = document.querySelectorAll('.carte');

            cartes.forEach(function (carte) {
                var cadre = null;   // dimensions de la carte, mises en cache

                carte.addEventListener('mouseenter', function () {
                    // On mesure une seule fois a l entree, pas a chaque
                    // deplacement : getBoundingClientRect force un recalcul
                    // de la mise en page, c est l operation couteuse ici.
                    cadre = carte.getBoundingClientRect();
                    carte.classList.add('incline');
                });

                carte.addEventListener('mousemove', function (evenement) {
                    if (!cadre) { return; }

                    // Position du curseur dans la carte, de 0 a 1
                    var x = (evenement.clientX - cadre.left) / cadre.width;
                    var y = (evenement.clientY - cadre.top) / cadre.height;

                    // Recentre autour de zero : -0.5 a gauche, +0.5 a droite
                    var rotationY = (x - 0.5) * 2 * INCLINAISON_MAX;
                    var rotationX = (0.5 - y) * 2 * INCLINAISON_MAX;

                    // Ecrire une variable CSS ne declenche qu un recalcul
                    // de style, pas de mise en page : c est assez leger
                    // pour etre fait directement, sans requestAnimationFrame.
                    carte.style.setProperty('--ry', rotationY.toFixed(2) + 'deg');
                    carte.style.setProperty('--rx', rotationX.toFixed(2) + 'deg');
                    carte.style.setProperty('--lx', (x * 100).toFixed(1) + '%');
                    carte.style.setProperty('--ly', (y * 100).toFixed(1) + '%');
                });

                carte.addEventListener('mouseleave', function () {
                    carte.classList.remove('incline');   // retour en douceur
                    carte.style.setProperty('--rx', '0deg');
                    carte.style.setProperty('--ry', '0deg');
                    cadre = null;
                });
            });
        }

        /* -------------------------------------------------------------
           3. OMBRE DE LA BARRE DE NAVIGATION
           Elle apparait seulement quand la page est defilee.
           ------------------------------------------------------------- */
        var entete = document.querySelector('.entete');

        if (entete) {
            var majEntete = function () {
                entete.classList.toggle('defile', window.scrollY > 8);
            };
            majEntete();
            window.addEventListener('scroll', majEntete, { passive: true });
        }

        /* -------------------------------------------------------------
           4. DISPARITION AUTOMATIQUE DES MESSAGES
           Les messages de confirmation s effacent au bout de 6 secondes.
           Les messages d erreur restent : l utilisateur doit les lire.
           ------------------------------------------------------------- */
        document.querySelectorAll('.alerte--succes').forEach(function (alerte) {
            window.setTimeout(function () {
                alerte.style.transition = 'opacity 420ms ease, transform 420ms ease';
                alerte.style.opacity = '0';
                alerte.style.transform = 'translateY(-10px)';
                window.setTimeout(function () { alerte.remove(); }, 440);
            }, 6000);
        });
    });
})();
