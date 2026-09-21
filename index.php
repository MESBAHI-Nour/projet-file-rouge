<?php
/**
 * PAGE PUBLIQUE - Galerie des photos en vente.
 *
 * Page en lecture seule : aucun bouton de modification ni de suppression.
 * Elle reutilise les memes fonctions que l'espace admin (imageLister),
 * ce qui evite de reecrire les requetes.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// --- Filtre facultatif par style, passe dans l'URL -----------------------
$styleChoisi = $_GET['style'] ?? '';

$images = imageLister($pdo, ['id_style' => $styleChoisi]);
$styles = listerStyles($pdo);

// Nom du style actuellement selectionne (pour le texte du compteur)
$nomStyleChoisi = '';
foreach ($styles as $s) {
    if ((string)$s['id_style'] === (string)$styleChoisi) {
        $nomStyleChoisi = $s['nom_style'];
    }
}

/**
 * Construit les initiales d'un photographe : "Nour Mesbahi" donne "NM".
 */
function initiales(string $prenom, string $nom): string
{
    return mb_strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
}

$titrePage     = 'Galerie - Plateforme de vente de photos';
$cheminBase    = '.';
$feuillesStyle = ['galerie.css'];
$pageActive    = 'galerie';
$scripts       = ['effets.js'];

require_once __DIR__ . '/includes/header.php';
?>

<section class="banniere reveler">
    <h1>Photographies <span class="accent">a vendre</span></h1>
    <p class="banniere__intro">
        Decouvrez les images publiees par nos photographes.
        Chaque photo est proposee avec sa licence d utilisation.
    </p>
</section>

<!-- Filtres par style : de simples liens, donc partageables par URL -->
<nav class="filtres-galerie reveler" aria-label="Filtrer par style">
    <a href="index.php" class="<?= $styleChoisi === '' ? 'actif' : '' ?>">Toutes</a>
    <?php foreach ($styles as $style): ?>
        <a href="index.php?style=<?= (int)$style['id_style'] ?>"
           class="<?= (string)$styleChoisi === (string)$style['id_style'] ? 'actif' : '' ?>">
            <?= e($style['nom_style']) ?>
        </a>
    <?php endforeach; ?>
</nav>

<?php if ($images === []): ?>

    <div class="vide">
        <p class="vide__titre">Aucune photo disponible</p>
        <p>
            <?php if ($styleChoisi !== ''): ?>
                Aucune photo dans ce style pour le moment.
                <a href="index.php">Voir toutes les photos</a>
            <?php else: ?>
                La galerie est vide pour le moment.
            <?php endif; ?>
        </p>
    </div>

<?php else: ?>

    <p class="compteur reveler">
        <?= count($images) ?> photo<?= count($images) > 1 ? 's' : '' ?>
        <?= $nomStyleChoisi !== '' ? 'dans le style ' . e($nomStyleChoisi) : 'disponibles' ?>
    </p>

    <div class="grille">
        <?php foreach ($images as $image): ?>
            <a class="carte reveler" href="photo.php?id=<?= (int)$image['id_image'] ?>">

                <div class="carte__media">
                    <img class="carte__image"
                         src="<?= e(cheminAffichage($image['fichier_image'])) ?>"
                         alt="<?= e($image['titre_image']) ?>"
                         loading="lazy">
                    <span class="carte__style"><?= e($image['nom_style']) ?></span>
                    <span class="carte__apercu">Voir la photo &rarr;</span>
                </div>

                <div class="carte__corps">
                    <h2 class="carte__titre"><?= e($image['titre_image']) ?></h2>

                    <?php if (!empty($image['description_image'])): ?>
                        <p class="carte__description"><?= e($image['description_image']) ?></p>
                    <?php endif; ?>

                    <p class="carte__auteur">
                        <span class="carte__initiales" aria-hidden="true"><?=
                            e(initiales($image['prenom_photographe'], $image['nom_photographe']))
                        ?></span>
                        <?= e($image['prenom_photographe'] . ' ' . $image['nom_photographe']) ?>
                    </p>

                    <div class="carte__pied">
                        <span class="carte__prix"><?= e(formaterPrix($image['prix_image'])) ?></span>
                        <span class="carte__licence"><?= e($image['nom_licence']) ?></span>
                    </div>
                </div>

            </a>
        <?php endforeach; ?>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
