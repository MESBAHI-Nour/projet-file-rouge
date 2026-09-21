<?php
/**
 * ESPACE ADMIN - Liste des images (operation READ).
 *
 * Affiche toutes les images dans un tableau, avec une barre de recherche
 * sur le titre et deux filtres (style et licence).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// --- Filtres lus dans l'URL (methode GET : ils restent dans l'adresse) ----
$filtres = [
    'recherche'  => trim((string)($_GET['recherche'] ?? '')),
    'id_style'   => $_GET['id_style']   ?? '',
    'id_licence' => $_GET['id_licence'] ?? '',
];

$images   = imageLister($pdo, $filtres);
$styles   = listerStyles($pdo);
$licences = listerLicences($pdo);

// Un filtre est-il actif ? (sert a proposer "Reinitialiser")
$filtreActif = $filtres['recherche'] !== ''
    || $filtres['id_style'] !== ''
    || $filtres['id_licence'] !== '';

$titrePage  = 'Espace admin - Gestion des images';
$feuillesStyle = ['admin.css'];
$pageActive    = 'admin';
$scripts       = ['validation.js', 'effets.js'];
$cheminBase = '..';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-titre reveler">
    <div>
        <h1>Gestion des images</h1>
        <p class="page-titre__info">
            <?= count($images) ?> image<?= count($images) > 1 ? 's' : '' ?>
            <?= $filtreActif ? 'correspondant a la recherche' : 'au total' ?>
        </p>
    </div>
    <a class="bouton bouton--ajouter" href="image-create.php">+ Ajouter une image</a>
</div>

<!-- ------------------------------------------------------------------ -->
<!-- Barre de recherche et filtres (methode GET)                        -->
<!-- ------------------------------------------------------------------ -->
<form class="filtres reveler" method="get" action="index.php">
    <div class="filtres__champ">
        <label for="recherche">Rechercher un titre</label>
        <input type="search" id="recherche" name="recherche"
               value="<?= e($filtres['recherche']) ?>"
               placeholder="Ex : Dunes">
    </div>

    <div class="filtres__champ">
        <label for="id_style">Style</label>
        <select id="id_style" name="id_style">
            <option value="">Tous les styles</option>
            <?php foreach ($styles as $style): ?>
                <option value="<?= (int)$style['id_style'] ?>"
                    <?= (string)$filtres['id_style'] === (string)$style['id_style'] ? 'selected' : '' ?>>
                    <?= e($style['nom_style']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filtres__champ">
        <label for="id_licence">Licence</label>
        <select id="id_licence" name="id_licence">
            <option value="">Toutes les licences</option>
            <?php foreach ($licences as $licence): ?>
                <option value="<?= (int)$licence['id_licence'] ?>"
                    <?= (string)$filtres['id_licence'] === (string)$licence['id_licence'] ? 'selected' : '' ?>>
                    <?= e($licence['nom_licence']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filtres__actions">
        <button type="submit" class="bouton">Filtrer</button>
        <?php if ($filtreActif): ?>
            <a class="bouton bouton--neutre" href="index.php">Reinitialiser</a>
        <?php endif; ?>
    </div>
</form>

<!-- ------------------------------------------------------------------ -->
<!-- Tableau des images                                                 -->
<!-- ------------------------------------------------------------------ -->
<?php if ($images === []): ?>

    <div class="vide">
        <p class="vide__titre">Aucune image trouvee</p>
        <p>
            <?php if ($filtreActif): ?>
                Aucune image ne correspond a votre recherche.
                <a href="index.php">Afficher toutes les images</a>
            <?php else: ?>
                La galerie est vide pour le moment.
                <a href="image-create.php">Ajouter la premiere image</a>
            <?php endif; ?>
        </p>
    </div>

<?php else: ?>

    <div class="tableau-zone reveler">
        <table class="tableau">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Titre</th>
                    <th>Prix</th>
                    <th>Publication</th>
                    <th>Photographe</th>
                    <th>Style</th>
                    <th>Licence</th>
                    <th class="tableau__actions-entete">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($images as $image): ?>
                <tr>
                    <td data-libelle="Image">
                        <img class="miniature"
                             src="<?= e($cheminBase . '/' . cheminAffichage($image['fichier_image'])) ?>"
                             alt="<?= e($image['titre_image']) ?>"
                             loading="lazy">
                    </td>
                    <td data-libelle="Titre">
                        <a class="lien-titre" href="image-show.php?id=<?= (int)$image['id_image'] ?>">
                            <?= e($image['titre_image']) ?>
                        </a>
                    </td>
                    <td data-libelle="Prix">
                        <span class="prix"><?= e(formaterPrix($image['prix_image'])) ?></span>
                    </td>
                    <td data-libelle="Publication">
                        <span class="date"><?= e(date('d/m/Y', strtotime($image['date_publication']))) ?></span>
                    </td>
                    <td data-libelle="Photographe">
                        <?= e($image['prenom_photographe'] . ' ' . $image['nom_photographe']) ?>
                    </td>
                    <td data-libelle="Style">
                        <span class="etiquette"><?= e($image['nom_style']) ?></span>
                    </td>
                    <td data-libelle="Licence"><?= e($image['nom_licence']) ?></td>
                    <td data-libelle="Actions">
                        <div class="tableau__actions">
                            <a class="bouton bouton--voir"
                               href="image-show.php?id=<?= (int)$image['id_image'] ?>">Afficher</a>

                            <a class="bouton bouton--modifier"
                               href="image-edit.php?id=<?= (int)$image['id_image'] ?>">Modifier</a>

                            <!-- Suppression en POST, jamais en GET -->
                            <form method="post" action="image-delete.php"
                                  class="form-suppression"
                                  data-titre="<?= e($image['titre_image']) ?>">
                                <?= champCsrf() ?>
                                <input type="hidden" name="id_image" value="<?= (int)$image['id_image'] ?>">
                                <button type="submit" class="bouton bouton--supprimer">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
