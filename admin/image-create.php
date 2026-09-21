<?php
/**
 * ESPACE ADMIN - Ajout d'une image (operation CREATE).
 *
 * La page affiche le formulaire (GET) et traite son envoi (POST).
 * Apres un ajout reussi : redirection vers la liste (principe POST/Redirect/GET),
 * pour qu'un rafraichissement de page ne renvoie pas le formulaire.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// --- Traitement de l'envoi du formulaire ---------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrfValide($_POST['csrf_token'] ?? null)) {
        flash('erreur', 'Session expiree ou formulaire invalide. Merci de reessayer.');
        rediriger('image-create.php');
    }

    $resultat = imageAjouter($pdo, $_POST, $_FILES['fichier_image'] ?? null);

    if ($resultat['succes']) {
        flash('succes', 'L image "' . $_POST['titre_image'] . '" a bien ete ajoutee.');
        rediriger('index.php');
    }

    // Echec : on garde la saisie et les erreurs, puis on revient au formulaire
    memoriserSaisie($_POST, $resultat['erreurs']);
    flash('erreur', 'Le formulaire contient des erreurs. Merci de les corriger.');
    rediriger('image-create.php');
}

// --- Affichage du formulaire (GET) ---------------------------------------
$saisie  = lireSaisie();
$erreurs = lireErreurs();

$photographes = listerPhotographes($pdo);
$styles       = listerStyles($pdo);
$licences     = listerLicences($pdo);

// Message d'aide si aucune donnee de reference n'existe encore
$referencesManquantes = ($photographes === [] || $styles === [] || $licences === []);

$action        = 'creer';
$imageActuelle = null;
$titrePage     = 'Espace admin - Ajouter une image';
$feuillesStyle = ['admin.css'];
$pageActive    = 'admin';
$scripts       = ['validation.js', 'effets.js'];
$cheminBase    = '..';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-titre reveler">
    <div>
        <h1>Ajouter une image</h1>
        <p class="page-titre__info">Les champs marques d un asterisque sont obligatoires.</p>
    </div>
    <a class="bouton bouton--neutre" href="index.php">Retour a la liste</a>
</div>

<?php if ($referencesManquantes): ?>
    <div class="alerte alerte--erreur">
        Impossible d ajouter une image : il faut au moins un photographe, un style
        et une licence enregistres en base. Importez le fichier sql/schema.sql.
    </div>
<?php else: ?>
    <?php require __DIR__ . '/_formulaire-image.php'; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
