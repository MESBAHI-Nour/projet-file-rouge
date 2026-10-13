# RÔLE
Tu es un développeur senior PHP / JavaScript, expert Tailwind CSS. Génère de zéro une
petite application d'administration (SPA) pour une "Plateforme Vente de Photos".
Reproduis EXACTEMENT les technologies, l'organisation et le style de code de mon
prototype de référence (PHP + fichiers JSON + JavaScript vanilla : GestionGenre.php,
genre.php, genres.js, admin-genres.html).

# RÈGLES STRICTES
- Utilise UNIQUEMENT : PHP natif (pas de framework, pas de Composer), des fichiers JSON
  comme base de données, JavaScript vanilla avec fetch (aucune bibliothèque, pas de
  modules ES, balises <script> classiques, style fetch(...).then(...) comme genres.js),
  HTML, et Tailwind CSS v3 via le CDN <script src="https://cdn.tailwindcss.com"></script>.
- N'ajoute AUCUN outil, bibliothèque, fichier, dossier, fonctionnalité ou concept qui
  n'est pas décrit ici (pas de CSS personnalisé, pas de <style>, pas de tailwind.config,
  pas d'icônes, pas de polices personnalisées, pas de mode sombre, pas de modales, pas de
  classe JS "Api"). Si tu penses qu'il manque quelque chose, ARRÊTE-TOI et demande-moi.
- Pas d'authentification, pas de sessions, pas de pages publiques (visiteur), pas de
  pages Photographe. Partie administrateur uniquement.
- Interface en français (<html lang="fr">). Identifiants du code en français.
- Les "classes API" sont les classes PHP du backend (GestionX), comme GestionGenre.

# CONTEXTE : DIAGRAMMES (référence seulement, ne pas les régénérer)
[classDiagram

class Photographe {
    -int id_photographe
    -string nom
    -string prenom
    -string email
    -string mot_de_passe
    -string photo_profil

    +getIdPhotographe()
    +getNom()
    +getPrenom()
    +getEmail()
    +setNom()
    +setPrenom()
    +setEmail()
    +setMotDePasse()
    +ajouterImage()
    +modifierImage()
    +supprimerImage()
    +changerProfil()
}

class Image {
    -int id_image
    -string titre
    -string description
    -string url_image
    -date date_ajout
    -string statut

    +getIdImage()
    +getTitre()
    +getDescription()
    +getUrlImage()
    +setTitre()
    +setDescription()
    +setUrlImage()
    +modifier()
    +supprimer()
}

class StylePhotographique {
    -int id_style
    -string nom_style
    -string description

    +getIdStyle()
    +getNomStyle()
    +getDescription()
    +setNomStyle()
    +setDescription()
}

class LicenceUtilisation {
    -int id_licence
    -string nom_licence
    -string description
    -string conditions

    +getIdLicence()
    +getNomLicence()
    +getDescription()
    +getConditions()
    +setNomLicence()
    +setDescription()
    +setConditions()
}

Photographe "1" --> "0..*" Image : ajoute
StylePhotographique "1" --> "0..*" Image : classifie
LicenceUtilisation "1" --> "0..*" Image : définit]
[usecase-beta

direction LR

actor Administrateur
actor Photographe
actor Visiteur

Administrateur --> "consulter les images"
Administrateur --> "ajouter un photographe"
Administrateur --> "modifier un photographe"
Administrateur --> "supprimer un photographe"
Administrateur --> "ajouter un style photographique"
Administrateur --> "modifier un style photographique"
Administrateur --> "supprimer un style photographique"
Administrateur --> "ajouter une licence"
Administrateur --> "modifier une licence"
Administrateur --> "supprimer une licence"
Administrateur --> "valider une image"
Administrateur --> "supprimer une image"

Photographe --> "ajouter une image"
Photographe --> "modifier une image non validée"
Photographe --> "supprimer une image non validée"
Photographe --> "changer son profil"
Photographe --> "réinitialiser son mot de passe"

Visiteur --> "consulter les images"
Visiteur --> "consulter les styles photographiques"
Visiteur --> "consulter les licences"]
Utilise les noms d'attributs du diagramme de classes EXACTEMENT comme clés JSON et
propriétés PHP.

# PÉRIMÈTRE (côté administrateur)
1. Style photographique : consulter, ajouter, modifier, supprimer.
2. Image : consulter, ajouter (avec upload de la photo), modifier (avec remplacement
   facultatif de la photo), valider, supprimer.
Photographe et Licence : AUCUNE gestion. Seulement deux fichiers JSON en lecture seule
et deux API en GET uniquement, pour remplir les listes déroulantes et afficher leurs noms.

# STRUCTURE DES DOSSIERS (à la racine du projet, servie par php -S depuis cette racine)
backend/
  admin/
    api/    GestionStyle.php, GestionImage.php,
            GestionPhotographe.php (GET seul), GestionLicence.php (GET seul)
    class/  style_photographique.php (classe style_photographique),
            image.php (classe image)
  database/ gestionStyle.json, gestionImage.json,
            gestionPhotographe.json, gestionLicence.json
  images/   (fichiers des photos, créé avec un fichier .gitkeep)
frontend/
  pages/    admin.html   (la seule page de la SPA)
  js/       app.js (navigation SPA uniquement), styles.js, images.js
            (chacun avec ses propres appels fetch)
conception/ (mes deux diagrammes, ne rien générer)

# MODÈLE DE DONNÉES
style_photographique : id_style (int), nom_style (string), description (string)
image : id_image (int), titre (string), description (string), url_image (string),
        date_ajout (date Y-m-d), statut (string : "en_attente" ou "valide"),
        id_style (int), id_photographe (int), id_licence (int)
Relations : un style classifie 0..* images ; une image a exactement un style, un
photographe et une licence.
gestionPhotographe.json : id_photographe, nom, prenom (SANS email, SANS mot de passe)
gestionLicence.json : id_licence, nom_licence, description, conditions
url_image = chemin relatif au dossier backend, ex. "images/ab12cd.jpg".

# BACKEND (suis le modèle de GestionGenre.php)
- Chaque fichier API contient une classe (GestionStyle, GestionImage, GestionPhotographe,
  GestionLicence) avec un constructeur qui définit $this->path_file, une méthode par
  action, et traiterRequete() qui aiguille selon $_SERVER["REQUEST_METHOD"] puis selon le
  paramètre d'URL "action". Le fichier se termine par $api = new GestionX();
  $api->traiterRequete();
- Toujours : header("Content-Type: application/json; charset=utf-8"),
  JSON_UNESCAPED_UNICODE, JSON_PRETTY_PRINT pour écrire les JSON, chemins construits avec
  __DIR__, écritures avec LOCK_EX, aucun warning PHP si une clé manque ou si le corps est
  invalide (réponse 422).
- Codes HTTP : 200 lecture/modif/suppression, 201 création (renvoyer l'élément créé, pas
  toute la liste), 404 id inconnu, 405 méthode non autorisée, 409 conflit, 422 données
  invalides.
- Les classes du domaine (style_photographique, image) sont UTILISÉES par les API. Leurs
  setters LÈVENT InvalidArgumentException si la valeur est invalide (jamais d'ignorance
  silencieuse) ; l'API attrape l'exception et répond 422 {"erreur": "..."}. Chaque classe
  a un toArray(). La classe image a une méthode valider() qui lève une exception si
  l'image est déjà "valide".
- IDs : max(id existants) + 1 (1 si vide), jamais count()+1.

GestionStyle :
- GET (liste, ou un seul avec ?id=), POST (création, corps JSON), PUT ?id= (modification,
  corps JSON), DELETE ?id=.
- Validation : nom_style >= 2 caractères, description >= 10 (trim, mb_strlen).
- Nom de style unique (insensible à la casse) -> 409 si doublon.
- DELETE d'un style utilisé par au moins une image -> 409
  {"erreur": "Ce style est utilisé par des images"}.

GestionPhotographe / GestionLicence : GET (liste) seulement ; autre méthode -> 405.

GestionImage :
- GET (liste, ou une seule avec ?id=).
- POST sans action : créer une image.
- POST ?id=X&action=modifier : modifier une image (multipart, car PHP ne remplit pas
  $_FILES pour PUT).
- PUT ?id=X&action=valider : passe statut à "valide" (404 si inconnue, 409 si déjà valide).
- DELETE ?id=X : supprime l'entrée JSON ET le fichier physique dans backend/images/
  (unlink seulement s'il existe et si le chemin reste dans backend/images/).
- Autre -> 405.
- Les deux POST reçoivent du multipart/form-data : champs titre, description, id_style,
  id_photographe, id_licence et fichier dans le champ "fichier". Lire les champs texte
  dans $_POST et le fichier dans $_FILES (jamais php://input). La gestion du fichier est
  dans UNE méthode privée de la classe (ex. enregistrerFichier()) utilisée par les deux.
- Validation : titre >= 2, description >= 10 (trim, mb_strlen) ; id_style doit exister dans
  gestionStyle.json ; id_photographe dans gestionPhotographe.json ; id_licence dans
  gestionLicence.json.
- Fichier : si $_FILES est vide alors que CONTENT_LENGTH > 0, le fichier dépasse
  post_max_size -> 422 "Fichier trop volumineux (limite du serveur)". Vérifier
  $_FILES["fichier"]["error"] : à la CRÉATION, UPLOAD_ERR_NO_FILE -> 422 "Photo
  obligatoire" ; à la MODIFICATION, UPLOAD_ERR_NO_FILE signifie "garder la photo
  actuelle" ; toute autre erreur -> 422 avec un message français clair.
  Formats autorisés : jpg, jpeg, png, webp. Vérifier le VRAI type MIME côté serveur
  (finfo_file ou getimagesize), jamais l'extension ni le type envoyé par le client.
  Taille max 5 Mo. Ne JAMAIS utiliser le nom du fichier du client : nom aléatoire
  bin2hex(random_bytes(16)) + extension correspondant au MIME vérifié. Enregistrer avec
  move_uploaded_file dans backend/images/.
- CRÉATION : date_ajout = date("Y-m-d") côté serveur ; statut = "en_attente". Si l'écriture
  du JSON échoue après le déplacement du fichier, supprimer le fichier déplacé.
- MODIFICATION : vérifier que l'id existe AVANT de toucher un fichier (sinon 404). Met à
  jour titre, description, id_style, id_photographe, id_licence ; ne change jamais
  id, date_ajout ni statut. Si un nouveau fichier est envoyé : le valider et l'enregistrer,
  écrire le JSON avec le nouvel url_image, et SEULEMENT APRÈS écriture réussie supprimer
  l'ancien fichier. Si l'écriture échoue, supprimer le nouveau fichier et garder l'ancien.
  Sans fichier : garder url_image actuel.
- Données d'exemple : gestionStyle.json (3 styles valides), gestionPhotographe.json (2),
  gestionLicence.json (2), gestionImage.json VIDE ([]).

# FRONTEND
- admin.html : page unique. Barre latérale sombre avec titre "Dashboard Admin" et deux
  liens (#nav-styles -> "#styles", #nav-images -> "#images"), zone principale avec une
  zone de messages (#message-container) et deux sections (#section-styles,
  #section-images). Une seule section visible à la fois (classe Tailwind "hidden").
  Balises <script> toujours fermées explicitement (jamais <script ... />), placées en
  fin de body dans l'ordre : styles.js, images.js, app.js.
- app.js : navigation par hash (#styles par défaut, #images), exécutée AU CHARGEMENT
  (DOMContentLoaded) et à chaque "hashchange" ; met en évidence le lien actif et pose
  aria-current="page". Définit les classes actives/inactives dans deux constantes.
  Rien d'autre.
- Chaque fichier JS : vérifie que les éléments du DOM existent (jamais de code de
  premier niveau qui plante si un élément manque), vérifie response.ok sur chaque
  fetch, affiche les messages (succès/erreur) dans #message-container, jamais
  seulement console.error. Chaque tranche charge ses données au DOMContentLoaded et
  quand sa section est ouverte (hashchange).
- Sécurité : JAMAIS insertAdjacentHTML / innerHTML avec des données du serveur ;
  construire les lignes avec createElement et textContent.
- styles.js : tableau (ID, Nom, Description, Actions : Modifier / Supprimer), bouton
  "+ Nouveau style" qui affiche le formulaire masqué (même formulaire pour créer et
  modifier), bouton "Annuler", window.confirm avant suppression.
- images.js : tableau (ID, miniature, Titre, Style, Photographe "prenom nom", Licence,
  Statut, Date d'ajout, Actions : Modifier / Valider (seulement si "en_attente") /
  Supprimer). Les noms sont résolus en chargeant GestionStyle.php, GestionPhotographe.php
  et GestionLicence.php. Miniature : <img> avec src "../../backend/" + url_image et
  alt = titre. Tableau vide : une ligne "Aucune image pour le moment.".
- Formulaire image (masqué par défaut, bouton "+ Nouvelle image") : titre, description
  (textarea), style (<select>), photographe (<select>, texte "prenom nom"), licence
  (<select>), photo (<input type="file" accept="image/jpeg,image/png,image/webp">).
  Chaque champ a un <label for>.
  * AJOUT : photo obligatoire ; envoi avec fetch(API, {method:"POST", body: formData})
    où formData est un FormData. Ne JAMAIS définir le header Content-Type à la main.
  * MODIFICATION : même formulaire pré-rempli, miniature de la photo actuelle, libellé
    "Remplacer la photo (facultatif)", input fichier NON obligatoire ; envoi avec
    fetch(API + "?id=" + id + "&action=modifier", {method:"POST", body: formData}).
  * Après succès : masquer et réinitialiser le formulaire, recharger la liste, message
    de succès. Réinitialiser formulaire, input fichier et état d'édition à chaque clic
    sur "+ Nouvelle image" ou "Annuler". Désactiver le bouton d'envoi pendant la requête.

# STYLE (Tailwind v3 uniquement, ces jetons partout)
- Palette : slate (neutres), indigo (accent), vert (succès), ambre (attente), rouge
  (danger). Body : "min-h-screen bg-slate-100 text-slate-800 antialiased".
- Barre latérale : "bg-slate-900", titre "text-white font-semibold tracking-tight".
  Liens : "rounded-lg px-3 py-2 text-sm font-medium transition-colors" ; inactif
  "text-slate-300 hover:bg-slate-800 hover:text-white" ; actif "bg-indigo-600 text-white".
- Cartes : "bg-white rounded-xl shadow-sm ring-1 ring-slate-200". Titre de page :
  "text-2xl font-semibold tracking-tight text-slate-900", sous-titre
  "text-sm text-slate-500", bouton principal aligné à droite.
- Boutons (tous avec "transition-colors focus:outline-none focus-visible:ring-2
  focus-visible:ring-offset-2 rounded-lg px-4 py-2 text-sm font-medium") : principal
  "bg-indigo-600 text-white hover:bg-indigo-700 focus-visible:ring-indigo-500" ;
  secondaire "bg-white text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50" ;
  succès (Valider) "bg-green-600 text-white hover:bg-green-700" ; danger (Supprimer)
  "bg-white text-red-600 ring-1 ring-red-200 hover:bg-red-50" ; variante petite
  "px-3 py-1.5 text-xs".
- Formulaires : label "block text-sm font-medium text-slate-700 mb-1" ; champs
  "w-full rounded-lg border-0 ring-1 ring-slate-300 px-3 py-2 text-sm
  placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500" ;
  grille "grid gap-4 md:grid-cols-2" ; boutons alignés à droite "flex justify-end gap-3".
- Tableaux : conteneur "overflow-x-auto" dans une carte ; "min-w-full divide-y
  divide-slate-200" ; thead "bg-slate-50", th "px-4 py-3 text-left text-xs
  font-semibold uppercase tracking-wider text-slate-500" (scope="col") ; lignes
  "hover:bg-slate-50 transition-colors" ; td "px-4 py-3 text-sm text-slate-700".
- Badge de statut : base "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs
  font-medium" ; valide "bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20" ;
  en attente "bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-600/20".
- Miniature : "h-14 w-14 rounded-lg object-cover ring-1 ring-slate-200".
- Messages : "rounded-lg px-4 py-3 text-sm" ; succès "bg-green-50 text-green-800
  ring-1 ring-green-200" ; erreur "bg-red-50 text-red-800 ring-1 ring-red-200" ;
  role="alert".
- Responsive (préfixes Tailwind seulement) : desktop "md:flex-row", barre latérale
  "md:w-64 md:sticky md:top-0 md:h-screen" ; mobile : barre latérale en haut avec liens
  en ligne défilante, tableaux défilants horizontalement, grilles sur une colonne.
- Accessibilité : <label for> partout, focus visible, boutons <button>.

EFFETS 3D (arbitrary values Tailwind v3, pas de classes v4 comme rotate-x-* ou
perspective-*)
- Miniatures : "transition-transform duration-300 ease-out
  hover:[transform:perspective(600px)_rotateY(-14deg)_rotateX(6deg)_scale(1.15)]
  hover:shadow-xl hover:relative hover:z-10".
- Cartes de formulaire seulement : "transition-all duration-300 ease-out
  hover:[transform:perspective(1000px)_rotateX(2deg)_rotateY(-2deg)] hover:shadow-2xl".
- Boutons principaux : "shadow-[0_5px_0_0_#3730a3] transition-all duration-100
  active:translate-y-1 active:shadow-[0_1px_0_0_#3730a3]".
- Jamais de 3D sur les lignes de tableau, les tableaux, le texte, les champs, la barre
  latérale ou la zone de messages. Chaque élément animé a aussi
  "motion-reduce:transition-none motion-reduce:hover:[transform:none]". Les conteneurs
  de miniatures ne doivent pas couper l'effet (utiliser overflow-x-auto, pas
  overflow-hidden, sur le conteneur du tableau des images).

# FORMAT DE SORTIE
1. Affiche d'abord l'arborescence finale des dossiers.
2. Puis chaque fichier COMPLET, un bloc de code par fichier avec son chemin en titre,
   dans cet ordre : backend (classes, API, JSON), puis frontend (admin.html, app.js,
   styles.js, images.js). Ne coupe aucun fichier.
3. Termine par : (a) la commande pour lancer le projet (php -S localhost:8000 depuis la
   racine) et l'URL de la page, (b) en 3 à 5 lignes, tout ce que tu as supposé ou tout
   point où tu as été tenté d'ajouter quelque chose hors des règles ci-dessus (ne
   l'implémente PAS).