<?php
/**
 * PAGE DE DIAGNOSTIC - a ouvrir en premier sur une nouvelle machine.
 *
 * Elle verifie que tout ce dont le projet a besoin est bien en place :
 * version de PHP, extensions, dossier uploads, connexion a la base.
 *
 * A SUPPRIMER avant de rendre le projet (elle affiche des infos techniques).
 */

declare(strict_types=1);

$resultats = [];

/** Enregistre le resultat d'une verification. */
function verif(string $categorie, string $libelle, bool $ok, string $detail, bool $bloquant = true): void
{
    global $resultats;
    $resultats[$categorie][] = [
        'libelle'  => $libelle,
        'ok'       => $ok,
        'detail'   => $detail,
        'bloquant' => $bloquant,
    ];
}

// =====================================================================
// 1. SYSTEME ET PHP
// =====================================================================
verif('Systeme', 'Systeme d exploitation', true, PHP_OS_FAMILY . ' (' . php_uname('s') . ')', false);
verif('Systeme', 'Separateur de dossiers', true, 'DIRECTORY_SEPARATOR = "' . DIRECTORY_SEPARATOR . '"', false);

$versionOk = version_compare(PHP_VERSION, '8.0.0', '>=');
verif('Systeme', 'Version de PHP', $versionOk, PHP_VERSION . ($versionOk ? '' : ' (8.0 minimum requis)'));

// =====================================================================
// 2. EXTENSIONS PHP
// =====================================================================
$extensions = [
    'pdo_mysql' => 'Indispensable : connexion a MySQL.',
    'fileinfo'  => 'Indispensable : verifie le vrai type des fichiers envoyes.',
    'mbstring'  => 'Indispensable : gestion des accents et de la longueur des textes.',
    'gd'        => 'Facultatif : sert uniquement a generer des images de test.',
];

foreach ($extensions as $ext => $role) {
    $charge = extension_loaded($ext);
    verif(
        'Extensions PHP',
        'extension ' . $ext,
        $charge,
        $charge ? 'chargee' : 'MANQUANTE - ' . $role,
        $ext !== 'gd'
    );
}

// =====================================================================
// 3. CONFIGURATION DES ENVOIS DE FICHIERS
// =====================================================================
$uploadsActifs = (bool)ini_get('file_uploads');
verif('Envoi de fichiers', 'file_uploads', $uploadsActifs, $uploadsActifs ? 'active' : 'DESACTIVE dans php.ini');

/** Convertit "8M" en nombre d'octets. */
function enOctets(string $valeur): int
{
    $valeur = trim($valeur);
    if ($valeur === '') {
        return 0;
    }
    $nombre = (int)$valeur;
    return match (strtolower($valeur[strlen($valeur) - 1])) {
        'g'     => $nombre * 1024 * 1024 * 1024,
        'm'     => $nombre * 1024 * 1024,
        'k'     => $nombre * 1024,
        default => $nombre,
    };
}

$limiteProjet = 2 * 1024 * 1024; // 2 Mo, la limite fixee dans functions.php

foreach (['upload_max_filesize', 'post_max_size'] as $cle) {
    $brut    = (string)ini_get($cle);
    $octets  = enOctets($brut);
    $suffit  = $octets >= $limiteProjet;
    verif(
        'Envoi de fichiers',
        $cle,
        $suffit,
        $brut . ($suffit ? '' : ' - trop bas, il faut au moins 2M (8M conseille)')
    );
}

// =====================================================================
// 4. DOSSIER UPLOADS
// =====================================================================
$dossierUploads = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
$existe    = is_dir($dossierUploads);
$accessible = $existe && is_writable($dossierUploads);

verif('Dossier uploads', 'Le dossier existe', $existe, $existe ? $dossierUploads : 'INTROUVABLE : ' . $dossierUploads);
verif('Dossier uploads', 'Accessible en ecriture', $accessible, $accessible ? 'oui' : 'NON - les envois de fichiers echoueront');

if ($accessible) {
    $test = $dossierUploads . DIRECTORY_SEPARATOR . 'test_ecriture.tmp';
    $ecrit = @file_put_contents($test, 'test') !== false;
    if ($ecrit) {
        @unlink($test);
    }
    verif('Dossier uploads', 'Test reel d ecriture', $ecrit, $ecrit ? 'fichier cree puis supprime' : 'ECHEC');
}

// =====================================================================
// 5. BASE DE DONNEES
// =====================================================================
$connexionOk = false;
$pdo = null;
$erreurConnexion = '';

// Demande a connexion.php de ne PAS arreter le script en cas d'echec :
// cette page doit pouvoir afficher son rapport meme sans base de donnees.
define('CONNEXION_SILENCIEUSE', true);

try {
    require_once __DIR__ . '/config/connexion.php';
    $connexionOk = isset($pdo) && $pdo instanceof PDO;
} catch (Throwable $e) {
    $connexionOk = false;
    $erreurConnexion = $e->getMessage();
}

if ($connexionOk) {
    verif('Base de donnees', 'Connexion', true, 'reussie sur ' . DB_HOST . ':' . DB_PORT . ' (base ' . DB_NAME . ')');

    $attendues = ['photographe' => 3, 'style' => 4, 'licence' => 3, 'image' => 6];

    foreach ($attendues as $table => $miniAttendu) {
        try {
            $n = (int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            verif('Base de donnees', 'Table ' . $table, true, $n . ' ligne(s)', false);
        } catch (PDOException $e) {
            verif('Base de donnees', 'Table ' . $table, false, 'INTROUVABLE - importez sql/schema.sql');
        }
    }
} else {
    verif('Base de donnees', 'Connexion', false,
        'ECHEC sur ' . DB_HOST . ':' . DB_PORT . ' - ' . $erreurConnexion);

    verif('Base de donnees', 'Que faire ?', false,
        'Verifiez DB_PORT et DB_PASS dans config/connexion.php '
        . '(XAMPP/WAMP : port 3306 + mot de passe vide, MAMP : port 8889 + mot de passe root), '
        . 'et que le serveur MySQL est bien demarre.');
}

// =====================================================================
// 6. SESSIONS
// =====================================================================
$sessionOk = @session_start();
verif('Sessions', 'Demarrage de session', $sessionOk, $sessionOk ? 'session_id = ' . session_id() : 'ECHEC');

// =====================================================================
// BILAN
// =====================================================================
$bloquantsKo = 0;
$avertissements = 0;

foreach ($resultats as $liste) {
    foreach ($liste as $r) {
        if (!$r['ok']) {
            $r['bloquant'] ? $bloquantsKo++ : $avertissements++;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic du projet</title>
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; background: #f4f6f8;
               color: #1f2933; margin: 0; padding: 32px 16px; line-height: 1.5; }
        .boite { max-width: 880px; margin: 0 auto; }
        h1 { margin: 0 0 4px; font-size: 1.5rem; }
        .sous-titre { color: #647382; margin: 0 0 24px; font-size: .9rem; }
        .bilan { padding: 16px 20px; border-radius: 8px; margin-bottom: 28px; font-weight: 600; }
        .bilan--ok { background: #e4f5ec; color: #1b7f4d; border: 1px solid #bfe5d0; }
        .bilan--ko { background: #fdecea; color: #b3261e; border: 1px solid #f5c6c2; }
        .bloc { background: #fff; border: 1px solid #dde3e9; border-radius: 8px; margin-bottom: 20px; overflow: hidden; }
        .bloc h2 { margin: 0; padding: 12px 20px; font-size: .8rem; text-transform: uppercase;
                   letter-spacing: .05em; color: #647382; background: #eef2f6; border-bottom: 1px solid #dde3e9; }
        .ligne { display: flex; gap: 14px; padding: 12px 20px; border-bottom: 1px solid #f0f3f6; align-items: baseline; }
        .ligne:last-child { border-bottom: none; }
        .pastille { flex: 0 0 auto; font-weight: 700; width: 22px; }
        .pastille--ok { color: #1b7f4d; }
        .pastille--ko { color: #b3261e; }
        .pastille--warn { color: #b06a00; }
        .libelle { flex: 0 0 210px; font-weight: 600; font-size: .92rem; }
        .detail { color: #647382; font-size: .9rem; word-break: break-word; }
        .detail--ko { color: #b3261e; }
        .note { background: #fff; border: 1px dashed #dde3e9; border-radius: 8px; padding: 16px 20px;
                color: #647382; font-size: .88rem; }
        code { background: #eef2f6; padding: 1px 5px; border-radius: 4px; font-size: .88em; }
        a { color: #2f6fd0; }
    </style>
</head>
<body>
<div class="boite">

    <h1>Diagnostic du projet</h1>
    <p class="sous-titre">Plateforme de vente de photos &mdash; verification de l environnement</p>

    <?php if ($bloquantsKo === 0): ?>
        <div class="bilan bilan--ok">
            Tout est pret. Le projet peut fonctionner sur cette machine.
            <?= $avertissements > 0 ? "($avertissements avertissement(s) sans gravite)" : '' ?>
            <br><a href="admin/index.php">Ouvrir l espace admin</a>
        </div>
    <?php else: ?>
        <div class="bilan bilan--ko">
            <?= $bloquantsKo ?> probleme(s) bloquant(s) a corriger avant de lancer le projet.
        </div>
    <?php endif; ?>

    <?php foreach ($resultats as $categorie => $liste): ?>
        <div class="bloc">
            <h2><?= htmlspecialchars($categorie, ENT_QUOTES, 'UTF-8') ?></h2>
            <?php foreach ($liste as $r): ?>
                <div class="ligne">
                    <?php if ($r['ok']): ?>
                        <span class="pastille pastille--ok">OK</span>
                    <?php elseif ($r['bloquant']): ?>
                        <span class="pastille pastille--ko">KO</span>
                    <?php else: ?>
                        <span class="pastille pastille--warn">!</span>
                    <?php endif; ?>
                    <span class="libelle"><?= htmlspecialchars($r['libelle'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="detail <?= $r['ok'] ? '' : 'detail--ko' ?>">
                        <?= htmlspecialchars($r['detail'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <div class="note">
        <strong>En cas de probleme :</strong><br>
        &bull; Extension manquante : ouvrir <code>php.ini</code> et retirer le <code>;</code> devant
        <code>extension=pdo_mysql</code>, <code>extension=fileinfo</code> ou <code>extension=mbstring</code>, puis redemarrer le serveur.<br>
        &bull; Connexion refusee : verifier le port et le mot de passe dans <code>config/connexion.php</code>
        (XAMPP et WAMP : port 3306, mot de passe vide &mdash; MAMP : port 8889, mot de passe root).<br>
        &bull; Table introuvable : importer <code>sql/schema.sql</code> dans phpMyAdmin.<br>
        &bull; Pensez a supprimer ce fichier <code>verif.php</code> avant de rendre le projet.
    </div>

</div>
</body>
</html>
