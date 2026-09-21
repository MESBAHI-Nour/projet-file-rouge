<?php
/**
 * En-tete commun a toutes les pages.
 *
 * Avant de l'inclure, la page peut definir :
 *   $titrePage      : titre affiche dans l'onglet du navigateur
 *   $cheminBase     : '..' depuis le dossier admin, '.' depuis la racine
 *   $feuillesStyle  : feuilles CSS a charger EN PLUS de base.css
 *   $pageActive     : 'galerie' ou 'admin', pour surligner le bon lien
 */

declare(strict_types=1);

$titrePage     = $titrePage     ?? 'Plateforme de vente de photos';
$cheminBase    = $cheminBase    ?? '.';
$feuillesStyle = $feuillesStyle ?? [];
$pageActive    = $pageActive    ?? '';

// TODO : ajouter l'authentification ici.
// C'est a cet endroit qu'il faudra verifier que l'admin est connecte,
// par exemple :
//   if (empty($_SESSION['id_admin'])) { rediriger($cheminBase . '/login.php'); }
// Pour l'instant l'espace admin est volontairement accessible sans login.

$messagesFlash = lireFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <!-- La classe .js est posee tout de suite : le CSS sait que le
         JavaScript est actif et peut masquer les elements a animer.
         Si ce script ne s'execute pas, la classe reste absente et tout
         le contenu s'affiche normalement, sans animation. -->
    <script>document.documentElement.className += ' js';</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titrePage) ?></title>

    <!-- base.css contient les variables de couleur et les styles communs -->
    <link rel="stylesheet" href="<?= e($cheminBase) ?>/assets/css/base.css">
    <?php foreach ($feuillesStyle as $feuille): ?>
        <link rel="stylesheet" href="<?= e($cheminBase . '/assets/css/' . $feuille) ?>">
    <?php endforeach; ?>
</head>
<body>

<header class="entete">
    <div class="conteneur entete__contenu">
        <a class="entete__logo" href="<?= e($cheminBase) ?>/index.php">
            Galerie<span>Photo</span>
        </a>
        <nav class="entete__nav">
            <a href="<?= e($cheminBase) ?>/index.php"
               <?= $pageActive === 'galerie' ? 'aria-current="page"' : '' ?>>Galerie</a>
            <a href="<?= e($cheminBase) ?>/admin/index.php"
               <?= $pageActive === 'admin' ? 'aria-current="page"' : '' ?>>Espace admin</a>
        </nav>
    </div>
</header>

<main class="conteneur">

<?php foreach ($messagesFlash as $flash): ?>
    <div class="alerte alerte--<?= e($flash['type']) ?>" role="alert">
        <span><?= e($flash['message']) ?></span>
    </div>
<?php endforeach; ?>
