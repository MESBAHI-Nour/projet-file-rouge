<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../controllers/ImageController.php";

$controller = new ImageController();
$methode = $_SERVER["REQUEST_METHOD"];
$id = isset($_GET["id"]) ? (int)$_GET["id"] : null;
$action = isset($_GET["action"]) ? $_GET["action"] : null;

if ($methode === "GET") {
    echo json_encode($controller->listerImages($id), JSON_UNESCAPED_UNICODE);
} else if ($methode === "POST") {
    if ($action === null && $id === null) {
        echo json_encode($controller->ajouterImage(), JSON_UNESCAPED_UNICODE);
    } else if ($action === "modifier" && $id !== null) {
        echo json_encode($controller->modifierImage($id), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(405);
        echo json_encode(["erreur" => "Action POST invalide."], JSON_UNESCAPED_UNICODE);
    }
} else if ($methode === "PUT") {
    if ($action === "valider" && $id !== null) {
        echo json_encode($controller->validerImage($id), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(405);
        echo json_encode(["erreur" => "Action PUT invalide."], JSON_UNESCAPED_UNICODE);
    }
} else if ($methode === "DELETE") {
    if ($id === null) {
        http_response_code(422);
        echo json_encode(["erreur" => "Identifiant de l'image manquant."], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($controller->supprimerImage($id), JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée."], JSON_UNESCAPED_UNICODE);
}

