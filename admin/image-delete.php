<?php
/**
 * ESPACE ADMIN - Suppression d'une image (operation DELETE).
 *
 * Cette page ne s'affiche jamais : elle traite la requete puis redirige.
 * Elle refuse les requetes GET (une suppression ne doit jamais etre
 * declenchee par un simple clic sur un lien ou par un robot).
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

// Refuse tout ce qui n'est pas un POST avec un jeton CSRF valide
exigerPostValide('index.php');

$id = filter_input(INPUT_POST, 'id_image', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    flash('erreur', 'Identifiant d image invalide.');
    rediriger('index.php');
}

// On lit le titre avant la suppression, pour pouvoir le citer dans le message
$image = imageTrouver($pdo, $id);
$titre = $image['titre_image'] ?? '';

$resultat = imageSupprimer($pdo, $id);

if ($resultat['succes']) {
    flash('succes', 'L image "' . $titre . '" a bien ete supprimee.');
} else {
    flash('erreur', $resultat['erreur'] ?? 'La suppression a echoue.');
}

rediriger('index.php');
