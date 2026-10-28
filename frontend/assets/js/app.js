const CLASSE_ACTIVE = ["bg-indigo-600", "text-white"];
const CLASSE_INACTIVE = ["text-slate-300", "hover:bg-slate-800", "hover:text-white"];

function naviguer() {
    const navStyles = document.getElementById("nav-styles");
    const navImages = document.getElementById("nav-images");
    const sectionStyles = document.getElementById("section-styles");
    const sectionImages = document.getElementById("section-images");

    if (!navStyles || !navImages || !sectionStyles || !sectionImages) {
        return;
    }

    const hash = window.location.hash || "#styles";

    if (hash === "#images") {
        sectionStyles.classList.add("hidden");
        sectionImages.classList.remove("hidden");

        // Lien Styles inactif
        for (let i = 0; i < CLASSE_ACTIVE.length; i++) {
            navStyles.classList.remove(CLASSE_ACTIVE[i]);
        }
        for (let i = 0; i < CLASSE_INACTIVE.length; i++) {
            navStyles.classList.add(CLASSE_INACTIVE[i]);
        }
        navStyles.removeAttribute("aria-current");

        // Lien Images actif
        for (let i = 0; i < CLASSE_INACTIVE.length; i++) {
            navImages.classList.remove(CLASSE_INACTIVE[i]);
        }
        for (let i = 0; i < CLASSE_ACTIVE.length; i++) {
            navImages.classList.add(CLASSE_ACTIVE[i]);
        }
        navImages.setAttribute("aria-current", "page");
    } else {
        sectionStyles.classList.remove("hidden");
        sectionImages.classList.add("hidden");

        // Lien Styles actif
        for (let i = 0; i < CLASSE_INACTIVE.length; i++) {
            navStyles.classList.remove(CLASSE_INACTIVE[i]);
        }
        for (let i = 0; i < CLASSE_ACTIVE.length; i++) {
            navStyles.classList.add(CLASSE_ACTIVE[i]);
        }
        navStyles.setAttribute("aria-current", "page");

        // Lien Images inactif
        for (let i = 0; i < CLASSE_ACTIVE.length; i++) {
            navImages.classList.remove(CLASSE_ACTIVE[i]);
        }
        for (let i = 0; i < CLASSE_INACTIVE.length; i++) {
            navImages.classList.add(CLASSE_INACTIVE[i]);
        }
        navImages.removeAttribute("aria-current");
    }
}

document.addEventListener("DOMContentLoaded", function() {
    naviguer();
});

window.addEventListener("hashchange", function() {
    naviguer();
});
