<?php
/**
 * Fichier d'amorcage : a inclure EN PREMIER dans chaque page.
 *
 * Il demarre la session, ouvre la connexion a la base et charge les
 * fonctions du projet. Il ne doit rien afficher : aucun espace ni retour
 * a la ligne avant <?php ni apres ?>, sinon les redirections echouent.
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,   // le cookie n'est pas lisible en JavaScript
        'cookie_samesite' => 'Lax',  // limite les envois depuis un autre site
    ]);
}

require_once __DIR__ . '/../config/connexion.php';
require_once __DIR__ . '/image-crud.php';

// =========================================================================
// MESSAGES FLASH
//
// Un message flash est stocke en session, affiche une seule fois sur la
// page suivante, puis efface. C'est ce qui permet d'afficher "Image ajoutee"
// apres une redirection.
// =========================================================================

/**
 * Enregistre un message a afficher sur la page suivante.
 *
 * @param string $type 'succes' (vert) ou 'erreur' (rouge)
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Recupere tous les messages en attente et les efface de la session.
 */
function lireFlash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);

    return $messages;
}

/**
 * Redirige vers une autre page puis arrete le script.
 *
 * exit est indispensable : sans lui, le code continue de s'executer
 * apres l'envoi de l'en-tete Location.
 */
function rediriger(string $url): never
{
    header('Location: ' . $url);
    exit;
}

// =========================================================================
// MEMORISATION DES CHAMPS D'UN FORMULAIRE
//
// Quand un formulaire est refuse, on remet les valeurs saisies en session
// pour ne pas obliger l'utilisateur a tout retaper.
// =========================================================================

function memoriserSaisie(array $donnees, array $erreurs): void
{
    unset($donnees['csrf_token']);
    $_SESSION['ancienne_saisie'] = $donnees;
    $_SESSION['erreurs']         = $erreurs;
}

function lireSaisie(): array
{
    $saisie = $_SESSION['ancienne_saisie'] ?? [];
    unset($_SESSION['ancienne_saisie']);

    return $saisie;
}

function lireErreurs(): array
{
    $erreurs = $_SESSION['erreurs'] ?? [];
    unset($_SESSION['erreurs']);

    return $erreurs;
}

/**
 * Renvoie la valeur a reafficher dans un champ du formulaire :
 * la valeur refusee si elle existe, sinon la valeur par defaut.
 */
function ancienneValeur(array $saisie, string $champ, mixed $defaut = ''): string
{
    return (string)($saisie[$champ] ?? $defaut ?? '');
}

// =========================================================================
// PROTECTION CSRF
//
// Empeche qu'un autre site fasse soumettre un de nos formulaires a l'insu
// de l'utilisateur. Chaque formulaire contient un jeton secret que le
// serveur verifie avant de traiter la requete.
// =========================================================================

function jetonCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Renvoie directement le champ cache a placer dans un formulaire.
 */
function champCsrf(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(jetonCsrf()) . '">';
}

function csrfValide(?string $jetonRecu): bool
{
    // hash_equals compare en temps constant (protection contre les attaques
    // par mesure du temps de reponse)
    return is_string($jetonRecu)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $jetonRecu);
}

/**
 * Verifie que la requete est bien un POST avec un jeton CSRF valide.
 * Utilise en tete de chaque page qui modifie des donnees.
 */
function exigerPostValide(string $urlRetour): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        flash('erreur', 'Action non autorisee : cette page attend un envoi de formulaire.');
        rediriger($urlRetour);
    }

    if (!csrfValide($_POST['csrf_token'] ?? null)) {
        flash('erreur', 'Session expiree ou formulaire invalide. Merci de reessayer.');
        rediriger($urlRetour);
    }
}
