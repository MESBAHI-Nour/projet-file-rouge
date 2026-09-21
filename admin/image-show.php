<?php
/**
 * ESPACE ADMIN - Afficher le detail d'une image (operation READ).
 *
 * C'est la vue "Afficher" du CRUD : elle montre TOUTES les informations
 * d'une seule image, la ou la page index.php n'affiche qu'un resume
 * de chaque ligne.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    flash('erreur', 'Identifiant d image invalide.');
    rediriger('index.php');
}

$image = imageTrouver($pdo, $id);

if ($image === null) {
    flash('erreur', 'Cette image n existe pas ou a deja ete supprimee.');
    rediriger('index.php');
}

// Taille du fichier sur le disque, si on le trouve
$cheminDisque = racineProjet() . DIRECTORY_SEPARATOR . $image['fichier_image'];
$poids        = is_file($cheminDisque) ? filesize($cheminDisque) : null;
$dimensions   = is_file($cheminDisque) ? @getimagesize($cheminDisque) : false;

$titrePage     = 'Espace admin - ' . $image['titre_image'];
$cheminBase    = '..';
$feuillesStyle = ['admin.css'];
$pageActive    = 'admin';
$scripts       = ['effets.js'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-titre reveler">
    <div>
        <h1><?= e($image['titre_image']) ?></h1>
        <p class="page-titre__info">Fiche complete de l image n&deg;<?= (int)$image['id_image'] ?></p>
    </div>
    <a class="bouton bouton--neutre" href="index.php">Retour a la liste</a>
</div>

<div class="fiche reveler">

    <!-- Colonne de gauche : l image -->
    <div class="fiche__media">
        <img src="<?= e($cheminBase . '/' . cheminAffichage($image['fichier_image'])) ?>"
             alt="<?= e($image['titre_image']) ?>">
    </div>

    <!-- Colonne de droite : toutes les donnees -->
    <div class="fiche__infos">

        <p class="fiche__prix"><?= e(formaterPrix($image['prix_image'])) ?></p>

        <?php if (!empty($image['description_image'])): ?>
            <p class="fiche__description"><?= e($image['description_image']) ?></p>
        <?php else: ?>
            <p class="fiche__description fiche__description--vide">Aucune description renseignee.</p>
        <?php endif; ?>

        <dl class="fiche__liste">
            <div>
                <dt>Photographe</dt>
                <dd><?= e($image['prenom_photographe'] . ' ' . $image['nom_photographe']) ?></dd>
            </div>
            <div>
                <dt>Style</dt>
                <dd><span class="etiquette"><?= e($image['nom_style']) ?></span></dd>
            </div>
            <div>
                <dt>Licence</dt>
                <dd>
                    <?= e($image['nom_licence']) ?>
                    <span class="fiche__secondaire">(<?= e(formaterPrix($image['prix_licence'])) ?>)</span>
                </dd>
            </div>
            <div>
                <dt>Date de publication</dt>
                <dd><?= e(date('d/m/Y', strtotime($image['date_publication']))) ?></dd>
            </div>
            <div>
                <dt>Fichier</dt>
                <dd class="fiche__fichier"><?= e(basename($image['fichier_image'])) ?></dd>
            </div>
            <?php if ($poids !== null): ?>
                <div>
                    <dt>Poids</dt>
                    <dd><?= e(number_format($poids / 1024, 0, ',', ' ')) ?> Ko</dd>
                </div>
            <?php endif; ?>
            <?php if ($dimensions !== false): ?>
                <div>
                    <dt>Dimensions</dt>
                    <dd><?= (int)$dimensions[0] ?> &times; <?= (int)$dimensions[1] ?> px</dd>
                </div>
            <?php endif; ?>
        </dl>

        <div class="fiche__actions">
            <a class="bouton bouton--modifier" href="image-edit.php?id=<?= (int)$image['id_image'] ?>">Modifier</a>

            <form method="post" action="image-delete.php" class="form-suppression"
                  data-titre="<?= e($image['titre_image']) ?>">
                <?= champCsrf() ?>
                <input type="hidden" name="id_image" value="<?= (int)$image['id_image'] ?>">
                <button type="submit" class="bouton bouton--supprimer">Supprimer</button>
            </form>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
