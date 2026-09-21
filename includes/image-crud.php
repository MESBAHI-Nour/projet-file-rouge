<?php
/**
 * Les 4 operations CRUD sur la table image.
 *
 * Regle appliquee partout : requetes preparees uniquement.
 * Aucune valeur venant de l'utilisateur n'est concatenee dans le SQL.
 */

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Bloc SELECT commun a la lecture : une seule requete avec 3 jointures,
 * pour ne jamais faire de requete a l'interieur d'une boucle d'affichage.
 */
function selectImageBase(): string
{
    return "
        SELECT
            i.id_image,
            i.titre_image,
            i.description_image,
            i.fichier_image,
            i.prix_image,
            i.date_publication,
            i.id_photographe,
            i.id_style,
            i.id_licence,
            p.nom_photographe,
            p.prenom_photographe,
            s.nom_style,
            l.nom_licence,
            l.prix_licence
        FROM image i
        INNER JOIN photographe p ON p.id_photographe = i.id_photographe
        INNER JOIN style        s ON s.id_style       = i.id_style
        INNER JOIN licence      l ON l.id_licence     = i.id_licence
    ";
}

/**
 * READ - Liste les images, avec filtres facultatifs.
 *
 * @param array $filtres ['recherche' => string, 'id_style' => int, 'id_licence' => int]
 * @return array Tableau des images (vide si aucun resultat)
 */
function imageLister(PDO $pdo, array $filtres = []): array
{
    $sql        = selectImageBase();
    $conditions = [];
    $parametres = [];

    // Recherche par titre
    $recherche = trim((string)($filtres['recherche'] ?? ''));
    if ($recherche !== '') {
        $conditions[] = 'i.titre_image LIKE :recherche';
        // Les caracteres speciaux de LIKE sont echappes pour qu'une saisie
        // comme "100%" cherche bien le texte "100%" et non "100" + joker.
        $parametres[':recherche'] = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $recherche) . '%';
    }

    // Filtre par style
    if (!empty($filtres['id_style']) && ctype_digit((string)$filtres['id_style'])) {
        $conditions[] = 'i.id_style = :id_style';
        $parametres[':id_style'] = (int)$filtres['id_style'];
    }

    // Filtre par licence
    if (!empty($filtres['id_licence']) && ctype_digit((string)$filtres['id_licence'])) {
        $conditions[] = 'i.id_licence = :id_licence';
        $parametres[':id_licence'] = (int)$filtres['id_licence'];
    }

    if ($conditions !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY i.date_publication DESC, i.id_image DESC';

    $requete = $pdo->prepare($sql);
    $requete->execute($parametres);

    return $requete->fetchAll();
}

/**
 * READ - Recupere une seule image par son identifiant.
 *
 * @return array|null null si l'image n'existe pas
 */
function imageTrouver(PDO $pdo, int $id): ?array
{
    $requete = $pdo->prepare(selectImageBase() . ' WHERE i.id_image = :id LIMIT 1');
    $requete->execute([':id' => $id]);

    $image = $requete->fetch();

    return $image === false ? null : $image;
}

/**
 * CREATE - Ajoute une image.
 *
 * L'upload est traite AVANT l'insertion. Si l'insertion echoue ensuite,
 * le fichier deja deplace est supprime pour ne pas laisser de fichier orphelin.
 *
 * @param array      $donnees Le tableau $_POST
 * @param array|null $fichier L'entree de $_FILES correspondant au champ fichier
 * @return array ['succes' => bool, 'id' => int|null, 'erreurs' => array]
 */
function imageAjouter(PDO $pdo, array $donnees, ?array $fichier): array
{
    // 1. Validation des champs texte
    $erreurs = validerImage($donnees);

    // 2. Traitement du fichier (obligatoire a l'ajout)
    $upload = traiterUpload($fichier, true);
    if ($upload['erreur'] !== null) {
        $erreurs['fichier_image'] = $upload['erreur'];
    }

    // 3. Si quoi que ce soit est invalide, on s'arrete la
    if ($erreurs !== []) {
        // Un fichier a pu etre deplace alors qu'un autre champ est invalide
        if ($upload['chemin'] !== null) {
            supprimerFichierUpload($upload['chemin']);
        }

        return ['succes' => false, 'id' => null, 'erreurs' => $erreurs];
    }

    // 4. Insertion
    $sql = '
        INSERT INTO image
            (titre_image, description_image, fichier_image, prix_image,
             date_publication, id_photographe, id_style, id_licence)
        VALUES
            (:titre, :description, :fichier, :prix,
             :date, :photographe, :style, :licence)
    ';

    $description = trim((string)($donnees['description_image'] ?? ''));

    try {
        $requete = $pdo->prepare($sql);
        $requete->execute([
            ':titre'       => trim((string)$donnees['titre_image']),
            ':description' => $description === '' ? null : $description,
            ':fichier'     => $upload['chemin'],
            ':prix'        => (float)str_replace(',', '.', (string)$donnees['prix_image']),
            ':date'        => $donnees['date_publication'],
            ':photographe' => (int)$donnees['id_photographe'],
            ':style'       => (int)$donnees['id_style'],
            ':licence'     => (int)$donnees['id_licence'],
        ]);
    } catch (PDOException $e) {
        // L'insertion a echoue : on ne garde pas le fichier deja envoye
        supprimerFichierUpload($upload['chemin']);

        return [
            'succes'  => false,
            'id'      => null,
            'erreurs' => ['global' => messageErreurSql($e)],
        ];
    }

    return ['succes' => true, 'id' => (int)$pdo->lastInsertId(), 'erreurs' => []];
}

/**
 * UPDATE - Modifie une image existante.
 *
 * Si aucun nouveau fichier n'est envoye, l'ancien est conserve.
 * Si un nouveau fichier est envoye, l'ancien est supprime du disque
 * seulement APRES que la mise a jour en base a reussi.
 *
 * @return array ['succes' => bool, 'erreurs' => array]
 */
function imageModifier(PDO $pdo, int $id, array $donnees, ?array $fichier): array
{
    // 1. L'image existe-t-elle ?
    $existante = imageTrouver($pdo, $id);
    if ($existante === null) {
        return ['succes' => false, 'erreurs' => ['global' => 'Cette image n existe pas ou a deja ete supprimee.']];
    }

    // 2. Validation des champs texte
    $erreurs = validerImage($donnees);

    // 3. Fichier facultatif en modification
    $upload = traiterUpload($fichier, false);
    if ($upload['erreur'] !== null) {
        $erreurs['fichier_image'] = $upload['erreur'];
    }

    if ($erreurs !== []) {
        if ($upload['chemin'] !== null) {
            supprimerFichierUpload($upload['chemin']);
        }

        return ['succes' => false, 'erreurs' => $erreurs];
    }

    // 4. Nouveau fichier fourni ? sinon on garde l'ancien chemin
    $nouveauFichier = $upload['chemin'] !== null;
    $cheminFinal    = $nouveauFichier ? $upload['chemin'] : $existante['fichier_image'];

    $sql = '
        UPDATE image SET
            titre_image       = :titre,
            description_image = :description,
            fichier_image     = :fichier,
            prix_image        = :prix,
            date_publication  = :date,
            id_photographe    = :photographe,
            id_style          = :style,
            id_licence        = :licence
        WHERE id_image = :id
    ';

    $description = trim((string)($donnees['description_image'] ?? ''));

    try {
        $requete = $pdo->prepare($sql);
        $requete->execute([
            ':titre'       => trim((string)$donnees['titre_image']),
            ':description' => $description === '' ? null : $description,
            ':fichier'     => $cheminFinal,
            ':prix'        => (float)str_replace(',', '.', (string)$donnees['prix_image']),
            ':date'        => $donnees['date_publication'],
            ':photographe' => (int)$donnees['id_photographe'],
            ':style'       => (int)$donnees['id_style'],
            ':licence'     => (int)$donnees['id_licence'],
            ':id'          => $id,
        ]);
    } catch (PDOException $e) {
        if ($nouveauFichier) {
            supprimerFichierUpload($upload['chemin']);
        }

        return ['succes' => false, 'erreurs' => ['global' => messageErreurSql($e)]];
    }

    // 5. La base est a jour : on peut supprimer l'ancien fichier
    if ($nouveauFichier) {
        supprimerFichierUpload($existante['fichier_image']);
    }

    return ['succes' => true, 'erreurs' => []];
}

/**
 * DELETE - Supprime une image et son fichier.
 *
 * On supprime d'abord la ligne en base : si cela echoue, le fichier reste
 * en place et l'image est toujours affichable.
 *
 * @return array ['succes' => bool, 'erreur' => string|null]
 */
function imageSupprimer(PDO $pdo, int $id): array
{
    $existante = imageTrouver($pdo, $id);
    if ($existante === null) {
        return ['succes' => false, 'erreur' => 'Cette image n existe pas ou a deja ete supprimee.'];
    }

    try {
        $requete = $pdo->prepare('DELETE FROM image WHERE id_image = :id');
        $requete->execute([':id' => $id]);
    } catch (PDOException $e) {
        return ['succes' => false, 'erreur' => messageErreurSql($e)];
    }

    if ($requete->rowCount() === 0) {
        return ['succes' => false, 'erreur' => 'Aucune image n a ete supprimee.'];
    }

    supprimerFichierUpload($existante['fichier_image']);

    return ['succes' => true, 'erreur' => null];
}

// =========================================================================
// Listes utilisees pour remplir les menus deroulants des formulaires
// =========================================================================

function listerPhotographes(PDO $pdo): array
{
    return $pdo->query(
        'SELECT id_photographe, nom_photographe, prenom_photographe
         FROM photographe
         ORDER BY nom_photographe, prenom_photographe'
    )->fetchAll();
}

function listerStyles(PDO $pdo): array
{
    return $pdo->query(
        'SELECT id_style, nom_style FROM style ORDER BY nom_style'
    )->fetchAll();
}

function listerLicences(PDO $pdo): array
{
    return $pdo->query(
        'SELECT id_licence, nom_licence, prix_licence FROM licence ORDER BY prix_licence'
    )->fetchAll();
}

/**
 * Transforme une erreur SQL technique en message comprehensible en francais.
 */
function messageErreurSql(PDOException $e): string
{
    // 23000 = violation de contrainte (cle etrangere, unicite, CHECK)
    if ($e->getCode() === '23000') {
        return 'Le photographe, le style ou la licence choisi n existe pas.';
    }

    return MODE_DEV
        ? 'Erreur SQL : ' . $e->getMessage()
        : 'Une erreur est survenue lors de l enregistrement. Merci de reessayer.';
}
