<?php
/**
 * Formulaire d'image, partage entre l'ajout et la modification.
 *
 * Variables attendues :
 *   $action        : 'creer' ou 'modifier'
 *   $saisie        : valeurs a reafficher dans les champs
 *   $erreurs       : messages d'erreur par champ
 *   $photographes, $styles, $licences : listes pour les menus deroulants
 *   $imageActuelle : l'image en cours de modification (null si ajout)
 */

declare(strict_types=1);

$estModification = ($action === 'modifier');
?>

<form method="post" enctype="multipart/form-data" class="formulaire" novalidate
      action="<?= $estModification
          ? 'image-edit.php?id=' . (int)$imageActuelle['id_image']
          : 'image-create.php' ?>">

    <?= champCsrf() ?>

    <?php if (!empty($erreurs['global'])): ?>
        <div class="alerte alerte--erreur"><?= e($erreurs['global']) ?></div>
    <?php endif; ?>

    <!-- Titre -->
    <div class="champ">
        <label for="titre_image">Titre <span class="obligatoire">*</span></label>
        <input type="text" id="titre_image" name="titre_image" maxlength="150" required
               value="<?= e(ancienneValeur($saisie, 'titre_image')) ?>"
               class="<?= isset($erreurs['titre_image']) ? 'champ--erreur' : '' ?>">
        <?php if (isset($erreurs['titre_image'])): ?>
            <p class="champ__erreur"><?= e($erreurs['titre_image']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Description -->
    <div class="champ">
        <label for="description_image">Description</label>
        <textarea id="description_image" name="description_image" rows="4" maxlength="2000"
                  class="<?= isset($erreurs['description_image']) ? 'champ--erreur' : '' ?>"
        ><?= e(ancienneValeur($saisie, 'description_image')) ?></textarea>
        <p class="champ__aide">Facultatif, 2000 caracteres maximum.</p>
        <?php if (isset($erreurs['description_image'])): ?>
            <p class="champ__erreur"><?= e($erreurs['description_image']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Fichier image -->
    <div class="champ">
        <label for="fichier_image">
            Fichier image
            <?php if (!$estModification): ?><span class="obligatoire">*</span><?php endif; ?>
        </label>

        <?php if ($estModification): ?>
            <div class="apercu">
                <img class="apercu__image"
                     src="<?= e($cheminBase . '/' . cheminAffichage($imageActuelle['fichier_image'])) ?>"
                     alt="<?= e($imageActuelle['titre_image']) ?>">
                <p class="champ__aide">
                    Image actuelle.<br>
                    Laissez ce champ vide pour la conserver.
                </p>
            </div>
        <?php endif; ?>

        <input type="file" id="fichier_image" name="fichier_image"
               accept="image/jpeg,image/png,image/webp"
               data-taille-max="2097152"
               <?= $estModification ? '' : 'required' ?>
               class="<?= isset($erreurs['fichier_image']) ? 'champ--erreur' : '' ?>">
        <p class="champ__aide">Formats acceptes : JPG, PNG, WEBP. Taille maximale : 2 Mo.</p>
        <?php if (isset($erreurs['fichier_image'])): ?>
            <p class="champ__erreur"><?= e($erreurs['fichier_image']) ?></p>
        <?php endif; ?>
    </div>

    <div class="champ-groupe">
        <!-- Prix -->
        <div class="champ">
            <label for="prix_image">Prix (MAD) <span class="obligatoire">*</span></label>
            <input type="number" id="prix_image" name="prix_image" step="0.01" min="0" required
                   value="<?= e(ancienneValeur($saisie, 'prix_image')) ?>"
                   class="<?= isset($erreurs['prix_image']) ? 'champ--erreur' : '' ?>">
            <?php if (isset($erreurs['prix_image'])): ?>
                <p class="champ__erreur"><?= e($erreurs['prix_image']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Date de publication -->
        <div class="champ">
            <label for="date_publication">Date de publication <span class="obligatoire">*</span></label>
            <input type="date" id="date_publication" name="date_publication" required
                   value="<?= e(ancienneValeur($saisie, 'date_publication', date('Y-m-d'))) ?>"
                   class="<?= isset($erreurs['date_publication']) ? 'champ--erreur' : '' ?>">
            <?php if (isset($erreurs['date_publication'])): ?>
                <p class="champ__erreur"><?= e($erreurs['date_publication']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="champ-groupe">
        <!-- Photographe (cle etrangere) -->
        <div class="champ">
            <label for="id_photographe">Photographe <span class="obligatoire">*</span></label>
            <select id="id_photographe" name="id_photographe" required
                    class="<?= isset($erreurs['id_photographe']) ? 'champ--erreur' : '' ?>">
                <option value="">-- Choisir --</option>
                <?php foreach ($photographes as $p): ?>
                    <option value="<?= (int)$p['id_photographe'] ?>"
                        <?= ancienneValeur($saisie, 'id_photographe') === (string)$p['id_photographe'] ? 'selected' : '' ?>>
                        <?= e($p['prenom_photographe'] . ' ' . $p['nom_photographe']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erreurs['id_photographe'])): ?>
                <p class="champ__erreur"><?= e($erreurs['id_photographe']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Style (cle etrangere) -->
        <div class="champ">
            <label for="id_style">Style <span class="obligatoire">*</span></label>
            <select id="id_style" name="id_style" required
                    class="<?= isset($erreurs['id_style']) ? 'champ--erreur' : '' ?>">
                <option value="">-- Choisir --</option>
                <?php foreach ($styles as $s): ?>
                    <option value="<?= (int)$s['id_style'] ?>"
                        <?= ancienneValeur($saisie, 'id_style') === (string)$s['id_style'] ? 'selected' : '' ?>>
                        <?= e($s['nom_style']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erreurs['id_style'])): ?>
                <p class="champ__erreur"><?= e($erreurs['id_style']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Licence (cle etrangere) -->
        <div class="champ">
            <label for="id_licence">Licence d utilisation <span class="obligatoire">*</span></label>
            <select id="id_licence" name="id_licence" required
                    class="<?= isset($erreurs['id_licence']) ? 'champ--erreur' : '' ?>">
                <option value="">-- Choisir --</option>
                <?php foreach ($licences as $l): ?>
                    <option value="<?= (int)$l['id_licence'] ?>"
                        <?= ancienneValeur($saisie, 'id_licence') === (string)$l['id_licence'] ? 'selected' : '' ?>>
                        <?= e($l['nom_licence']) ?> (<?= e(formaterPrix($l['prix_licence'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erreurs['id_licence'])): ?>
                <p class="champ__erreur"><?= e($erreurs['id_licence']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="formulaire__actions">
        <button type="submit" class="bouton bouton--ajouter">
            <?= $estModification ? 'Enregistrer les modifications' : 'Ajouter l image' ?>
        </button>
        <a class="bouton bouton--neutre" href="index.php">Annuler</a>
    </div>
</form>
