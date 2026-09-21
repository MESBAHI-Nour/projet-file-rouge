<?php
/**
 * ESPACE ADMIN - Modification d'une image (operation UPDATE).
 *
 * Le formulaire est pre-rempli avec les informations actuelles.
 * Si aucun nouveau fichier n'est choisi, l'image existante est conservee.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// --- Identifiant de l'image a modifier -----------------------------------
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    flash('erreur', 'Identifiant d image invalide.');
    rediriger('index.php');
}

$imageActuelle = imageTrouver($pdo, $id);

if ($imageActuelle === null) {
    flash('erreur', 'Cette image n existe pas ou a deja ete supprimee.');
    rediriger('index.php');
}

// --- Traitement de l'envoi du formulaire ---------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrfValide($_POST['csrf_token'] ?? null)) {
        flash('erreur', 'Session expiree ou formulaire invalide. Merci de reessayer.');
        rediriger('image-edit.php?id=' . $id);
    }

    $resultat = imageModifier($pdo, $id, $_POST, $_FILES['fichier_image'] ?? null);

    if ($resultat['succes']) {
        flash('succes', 'L image "' . $_POST['titre_image'] . '" a bien ete modifiee.');
        rediriger('index.php');
    }

    memoriserSaisie($_POST, $resultat['erreurs']);
    flash('erreur', 'Le formulaire contient des erreurs. Merci de les corriger.');
    rediriger('image-edit.php?id=' . $id);
}

// --- Affichage du formulaire (GET) ---------------------------------------
// Priorite a la saisie refusee ; sinon, les valeurs actuelles de la base.
$saisie  = lireSaisie();
$erreurs = lireErreurs();

if ($saisie === []) {
    $saisie = [
        'titre_image'       => $imageActuelle['titre_image'],
        'description_image' => $imageActuelle['description_image'],
        'prix_image'        => $imageActuelle['prix_image'],
        'date_publication'  => $imageActuelle['date_publication'],
        'id_photographe'    => $imageActuelle['id_photographe'],
        'id_style'          => $imageActuelle['id_style'],
        'id_licence'        => $imageActuelle['id_licence'],
    ];
}

$photographes = listerPhotographes($pdo);
$styles       = listerStyles($pdo);
$licences     = listerLicences($pdo);

$action     = 'modifier';
$titrePage  = 'Espace admin - Modifier une image';
$feuillesStyle = ['admin.css'];
$pageActive    = 'admin';
$scripts       = ['validation.js', 'effets.js'];
$cheminBase = '..';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-titre reveler">
    <div>
        <h1>Modifier une image</h1>
        <p class="page-titre__info">
            Image n&deg;<?= (int)$imageActuelle['id_image'] ?> &mdash;
            <?= e($imageActuelle['titre_image']) ?>
        </p>
    </div>
    <a class="bouton bouton--neutre" href="index.php">Retour a la liste</a>
</div>

<?php require __DIR__ . '/_formulaire-image.php'; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
