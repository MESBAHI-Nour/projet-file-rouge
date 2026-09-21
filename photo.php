<?php
/**
 * PAGE PUBLIQUE - Detail d'une photo.
 *
 * Accessible en cliquant sur une carte de la galerie.
 * Page en lecture seule : aucun bouton de modification.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    rediriger('index.php');
}

$photo = imageTrouver($pdo, $id);

if ($photo === null) {
    flash('erreur', 'Cette photo n est plus disponible.');
    rediriger('index.php');
}

// Quelques autres photos du meme style, pour proposer une suite
$similaires = array_values(array_filter(
    imageLister($pdo, ['id_style' => $photo['id_style']]),
    static fn (array $i): bool => (int)$i['id_image'] !== $id
));
$similaires = array_slice($similaires, 0, 3);

/** Initiales du photographe : "Nour Mesbahi" donne "NM". */
function initiales(string $prenom, string $nom): string
{
    return mb_strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
}

$titrePage     = $photo['titre_image'] . ' - Galerie';
$cheminBase    = '.';
$feuillesStyle = ['galerie.css'];
$pageActive    = 'galerie';
$scripts       = ['effets.js'];

require_once __DIR__ . '/includes/header.php';
?>

<nav class="fil-ariane reveler" aria-label="Fil d Ariane">
    <a href="index.php">Galerie</a>
    <span aria-hidden="true">/</span>
    <a href="index.php?style=<?= (int)$photo['id_style'] ?>"><?= e($photo['nom_style']) ?></a>
    <span aria-hidden="true">/</span>
    <span><?= e($photo['titre_image']) ?></span>
</nav>

<article class="detail reveler">

    <div class="detail__media">
        <img src="<?= e(cheminAffichage($photo['fichier_image'])) ?>"
             alt="<?= e($photo['titre_image']) ?>">
    </div>

    <div class="detail__infos">
        <span class="etiquette"><?= e($photo['nom_style']) ?></span>

        <h1 class="detail__titre"><?= e($photo['titre_image']) ?></h1>

        <p class="detail__auteur">
            <span class="carte__initiales" aria-hidden="true"><?=
                e(initiales($photo['prenom_photographe'], $photo['nom_photographe']))
            ?></span>
            <?= e($photo['prenom_photographe'] . ' ' . $photo['nom_photographe']) ?>
        </p>

        <?php if (!empty($photo['description_image'])): ?>
            <p class="detail__description"><?= e($photo['description_image']) ?></p>
        <?php endif; ?>

        <div class="detail__achat">
            <div>
                <p class="detail__prix-libelle">Prix de la photo</p>
                <p class="detail__prix"><?= e(formaterPrix($photo['prix_image'])) ?></p>
            </div>
            <div class="detail__licence">
                <p class="detail__prix-libelle">Licence</p>
                <p class="detail__licence-nom"><?= e($photo['nom_licence']) ?></p>
                <p class="detail__licence-prix">+ <?= e(formaterPrix($photo['prix_licence'])) ?></p>
            </div>
        </div>

        <?php if (!empty($photo['description_licence'])): ?>
            <p class="detail__licence-texte"><?= e($photo['description_licence']) ?></p>
        <?php endif; ?>

        <p class="detail__date">
            Publiee le <?= e(date('d/m/Y', strtotime($photo['date_publication']))) ?>
        </p>
    </div>

</article>

<?php if ($similaires !== []): ?>
    <section class="similaires">
        <h2 class="similaires__titre reveler">Dans le meme style</h2>
        <div class="grille">
            <?php foreach ($similaires as $s): ?>
                <a class="carte reveler" href="photo.php?id=<?= (int)$s['id_image'] ?>">
                    <div class="carte__media">
                        <img class="carte__image"
                             src="<?= e(cheminAffichage($s['fichier_image'])) ?>"
                             alt="<?= e($s['titre_image']) ?>" loading="lazy">
                    </div>
                    <div class="carte__corps">
                        <h3 class="carte__titre"><?= e($s['titre_image']) ?></h3>
                        <div class="carte__pied">
                            <span class="carte__prix"><?= e(formaterPrix($s['prix_image'])) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
