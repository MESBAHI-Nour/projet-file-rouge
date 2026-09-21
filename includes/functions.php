<?php
/**
 * Fonctions utilitaires : validation des donnees et gestion de l'upload.
 */

declare(strict_types=1);

// --- Parametres de l'upload ----------------------------------------------
const UPLOAD_DOSSIER   = 'uploads';
const UPLOAD_TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo

/**
 * Types MIME reellement autorises, avec l'extension imposee pour chacun.
 * On se base sur le type MIME detecte dans le fichier, pas sur l'extension
 * envoyee par le navigateur (qui peut etre falsifiee).
 */
const UPLOAD_TYPES_AUTORISES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

/**
 * Chemin absolu vers la racine du projet (le dossier file-rouge).
 */
function racineProjet(): string
{
    return dirname(__DIR__);
}

/**
 * Echappe une valeur avant de l'afficher dans du HTML (protection XSS).
 * A utiliser sur TOUTE donnee qui vient de la base ou de l'utilisateur.
 */
function e(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Valide les champs texte du formulaire image.
 *
 * @param array $donnees Le tableau $_POST
 * @return array Tableau des erreurs : ['nom_du_champ' => 'message en francais']
 *               Vide si tout est valide.
 */
function validerImage(array $donnees): array
{
    $erreurs = [];

    // --- Titre ---
    $titre = trim((string)($donnees['titre_image'] ?? ''));
    if ($titre === '') {
        $erreurs['titre_image'] = 'Le titre est obligatoire.';
    } elseif (mb_strlen($titre) > 150) {
        $erreurs['titre_image'] = 'Le titre ne doit pas depasser 150 caracteres.';
    }

    // --- Description (facultative) ---
    $description = trim((string)($donnees['description_image'] ?? ''));
    if (mb_strlen($description) > 2000) {
        $erreurs['description_image'] = 'La description ne doit pas depasser 2000 caracteres.';
    }

    // --- Prix ---
    $prixBrut = str_replace(',', '.', trim((string)($donnees['prix_image'] ?? '')));
    if ($prixBrut === '') {
        $erreurs['prix_image'] = 'Le prix est obligatoire.';
    } elseif (!is_numeric($prixBrut)) {
        $erreurs['prix_image'] = 'Le prix doit etre un nombre.';
    } elseif ((float)$prixBrut < 0) {
        $erreurs['prix_image'] = 'Le prix ne peut pas etre negatif.';
    } elseif ((float)$prixBrut > 99999999.99) {
        $erreurs['prix_image'] = 'Le prix saisi est trop eleve.';
    }

    // --- Date de publication ---
    $date = trim((string)($donnees['date_publication'] ?? ''));
    if ($date === '') {
        $erreurs['date_publication'] = 'La date de publication est obligatoire.';
    } elseif (!estDateValide($date)) {
        $erreurs['date_publication'] = 'La date doit etre au format AAAA-MM-JJ.';
    }

    // --- Les 3 cles etrangeres (obligatoires : cardinalite 1,1 dans le MCD) ---
    $liens = [
        'id_photographe' => 'Vous devez choisir un photographe.',
        'id_style'       => 'Vous devez choisir un style photographique.',
        'id_licence'     => 'Vous devez choisir une licence d utilisation.',
    ];

    foreach ($liens as $champ => $message) {
        $valeur = $donnees[$champ] ?? '';
        if ($valeur === '' || !ctype_digit((string)$valeur) || (int)$valeur < 1) {
            $erreurs[$champ] = $message;
        }
    }

    return $erreurs;
}

/**
 * Verifie qu'une chaine est bien une date reelle au format AAAA-MM-JJ.
 * (checkdate refuse par exemple le 2025-02-30)
 */
function estDateValide(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);

    return $d !== false && $d->format('Y-m-d') === $date;
}

/**
 * Traite le fichier envoye par le formulaire.
 *
 * Verifie l'erreur d'upload, la taille, puis le VRAI type MIME du fichier
 * avec finfo. Si tout est bon, deplace le fichier dans uploads/ sous un nom
 * unique et renvoie le chemin relatif a stocker en base.
 *
 * @param array|null $fichier Une entree de $_FILES, ou null
 * @param bool $obligatoire true en ajout, false en modification
 * @return array ['chemin' => string|null, 'erreur' => string|null]
 */
function traiterUpload(?array $fichier, bool $obligatoire): array
{
    $aucunFichier = $fichier === null
        || !isset($fichier['error'])
        || $fichier['error'] === UPLOAD_ERR_NO_FILE;

    if ($aucunFichier) {
        if ($obligatoire) {
            return ['chemin' => null, 'erreur' => 'Vous devez choisir un fichier image.'];
        }
        // En modification, ne pas envoyer de fichier signifie "garder l'ancien"
        return ['chemin' => null, 'erreur' => null];
    }

    // --- Erreurs renvoyees par PHP lui-meme ---
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'Le fichier depasse la taille autorisee par le serveur.',
            UPLOAD_ERR_FORM_SIZE  => 'Le fichier depasse la taille autorisee par le formulaire.',
            UPLOAD_ERR_PARTIAL    => 'Le fichier n a ete envoye que partiellement.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur.',
            UPLOAD_ERR_CANT_WRITE => 'Impossible d ecrire le fichier sur le disque.',
            UPLOAD_ERR_EXTENSION  => 'Une extension PHP a bloque l envoi du fichier.',
        ];

        return [
            'chemin' => null,
            'erreur' => $messages[$fichier['error']] ?? 'Erreur inconnue lors de l envoi du fichier.',
        ];
    }

    // --- Securite : le fichier doit vraiment venir d'un upload HTTP ---
    if (!is_uploaded_file($fichier['tmp_name'])) {
        return ['chemin' => null, 'erreur' => 'Envoi de fichier invalide.'];
    }

    // --- Taille ---
    if ($fichier['size'] > UPLOAD_TAILLE_MAX) {
        return [
            'chemin' => null,
            'erreur' => 'Le fichier est trop volumineux (2 Mo maximum).',
        ];
    }

    if ($fichier['size'] === 0) {
        return ['chemin' => null, 'erreur' => 'Le fichier envoye est vide.'];
    }

    // --- Vrai type MIME, lu dans le contenu du fichier ---
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($fichier['tmp_name']);

    if (!is_string($mime) || !isset(UPLOAD_TYPES_AUTORISES[$mime])) {
        return [
            'chemin' => null,
            'erreur' => 'Format non autorise. Seuls les fichiers JPG, PNG et WEBP sont acceptes.',
        ];
    }

    // --- Double verification : le fichier est-il une vraie image ? ---
    if (@getimagesize($fichier['tmp_name']) === false) {
        return ['chemin' => null, 'erreur' => 'Le fichier envoye n est pas une image valide.'];
    }

    // --- Nom unique : impossible d ecraser un fichier existant ---
    $extension = UPLOAD_TYPES_AUTORISES[$mime];
    $nomFichier = sprintf('img_%s_%s.%s', date('Ymd'), bin2hex(random_bytes(8)), $extension);

    $dossierAbsolu = racineProjet() . DIRECTORY_SEPARATOR . UPLOAD_DOSSIER;

    if (!is_dir($dossierAbsolu) && !mkdir($dossierAbsolu, 0755, true) && !is_dir($dossierAbsolu)) {
        return ['chemin' => null, 'erreur' => 'Le dossier uploads est introuvable sur le serveur.'];
    }

    if (!is_writable($dossierAbsolu)) {
        return ['chemin' => null, 'erreur' => 'Le dossier uploads n est pas accessible en ecriture.'];
    }

    $destination = $dossierAbsolu . DIRECTORY_SEPARATOR . $nomFichier;

    if (!move_uploaded_file($fichier['tmp_name'], $destination)) {
        return ['chemin' => null, 'erreur' => 'Impossible d enregistrer le fichier sur le serveur.'];
    }

    // On ne stocke en base QUE le chemin relatif, jamais le chemin absolu
    return ['chemin' => UPLOAD_DOSSIER . '/' . $nomFichier, 'erreur' => null];
}

/**
 * Supprime un fichier du dossier uploads.
 *
 * Verifie que le chemin reste bien a l'interieur de uploads/ pour eviter
 * qu'une valeur trafiquee en base fasse supprimer un fichier du systeme.
 */
function supprimerFichierUpload(?string $cheminRelatif): bool
{
    if ($cheminRelatif === null || trim($cheminRelatif) === '') {
        return false;
    }

    $dossierAbsolu = realpath(racineProjet() . DIRECTORY_SEPARATOR . UPLOAD_DOSSIER);
    $cible         = realpath(racineProjet() . DIRECTORY_SEPARATOR . $cheminRelatif);

    if ($dossierAbsolu === false || $cible === false) {
        return false;
    }

    // Le fichier doit se trouver DANS le dossier uploads
    if (!str_starts_with($cible, $dossierAbsolu . DIRECTORY_SEPARATOR)) {
        return false;
    }

    return is_file($cible) && unlink($cible);
}

/**
 * Renvoie le chemin d'affichage d'une image.
 * Si le fichier n'existe pas sur le disque (cas des donnees de test),
 * renvoie une image de remplacement pour ne pas casser la page.
 */
function cheminAffichage(?string $cheminRelatif): string
{
    if ($cheminRelatif !== null && $cheminRelatif !== '') {
        $absolu = racineProjet() . DIRECTORY_SEPARATOR . $cheminRelatif;
        if (is_file($absolu)) {
            return $cheminRelatif;
        }
    }

    return 'assets/img/placeholder.svg';
}

/**
 * Formate un prix pour l'affichage : 120.5 devient "120,50 MAD".
 */
function formaterPrix(float|string|null $prix): string
{
    return number_format((float)$prix, 2, ',', ' ') . ' MAD';
}
