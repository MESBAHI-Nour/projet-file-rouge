const LIEN_ACTIF = ["bg-indigo-600", "text-white"];
const LIEN_INACTIF = ["text-slate-300", "hover:bg-slate-800", "hover:text-white"];

function naviguer() {
  const hash = window.location.hash || "#styles";
  const sectionStyles = document.getElementById("section-styles");
  const sectionImages = document.getElementById("section-images");
  const linkStyles = document.getElementById("nav-styles");
  const linkImages = document.getElementById("nav-images");

  if (!sectionStyles || !sectionImages || !linkStyles || !linkImages) {
    return;
  }

  if (hash === "#images") {
    sectionStyles.classList.add("hidden");
    sectionImages.classList.remove("hidden");

    linkStyles.classList.remove(...LIEN_ACTIF);
    linkStyles.classList.add(...LIEN_INACTIF);
    linkStyles.removeAttribute("aria-current");

    linkImages.classList.remove(...LIEN_INACTIF);
    linkImages.classList.add(...LIEN_ACTIF);
    linkImages.setAttribute("aria-current", "page");
  } else {
    sectionStyles.classList.remove("hidden");
    sectionImages.classList.add("hidden");

    linkStyles.classList.remove(...LIEN_INACTIF);
    linkStyles.classList.add(...LIEN_ACTIF);
    linkStyles.setAttribute("aria-current", "page");

    linkImages.classList.remove(...LIEN_ACTIF);
    linkImages.classList.add(...LIEN_INACTIF);
    linkImages.removeAttribute("aria-current");
  }
}

window.addEventListener("hashchange", naviguer);
window.addEventListener("DOMContentLoaded", naviguer);
