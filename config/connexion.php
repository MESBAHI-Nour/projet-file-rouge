<?php

const DB_HOST = 'localhost';
const DB_PORT = 3306;        
const DB_NAME = 'vente_photos';
const DB_USER = 'root';
const DB_PASS = 'root';       

const DB_SOCKET = '';

const MODE_DEV = true;

$dsn = DB_SOCKET !== ''
    ? sprintf('mysql:unix_socket=%s;dbname=%s;charset=utf8mb4', DB_SOCKET, DB_NAME)
    : sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

$options = [
    // Toute erreur SQL leve une exception au lieu d'etre ignoree silencieusement
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Les resultats sont recuperes sous forme de tableaux associatifs
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Vraies requetes preparees cote MySQL, et non une simulation par PDO
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {

    // La page de diagnostic (verif.php) definit CONNEXION_SILENCIEUSE
    // avant d'inclure ce fichier : elle veut pouvoir afficher son rapport
    // meme quand la base est injoignable. On lui rend la main au lieu
    // d'arreter le script.
    if (defined('CONNEXION_SILENCIEUSE') && CONNEXION_SILENCIEUSE) {
        $pdo = null;
        $erreurConnexion = $e->getMessage();
    } else {
        http_response_code(500);

        if (MODE_DEV) {
            exit('Erreur de connexion a la base de donnees : ' . $e->getMessage());
        }

        exit('Le service est momentanement indisponible. Merci de reessayer plus tard.');
    }
}
