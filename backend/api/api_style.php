<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../controllers/StyleController.php";

$controller = new StyleController();
$methode = $_SERVER["REQUEST_METHOD"];
$id = isset($_GET["id"]) ? (int)$_GET["id"] : null;

if ($methode === "GET") {
    echo json_encode($controller->listerStyles($id), JSON_UNESCAPED_UNICODE);
} else if ($methode === "POST") {
    echo json_encode($controller->ajouterStyle(), JSON_UNESCAPED_UNICODE);
} else if ($methode === "PUT") {
    if ($id === null) {
        http_response_code(422);
        echo json_encode(["erreur" => "Identifiant du style manquant."], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($controller->modifierStyle($id), JSON_UNESCAPED_UNICODE);
    }
} else if ($methode === "DELETE") {
    if ($id === null) {
        http_response_code(422);
        echo json_encode(["erreur" => "Identifiant du style manquant."], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode($controller->supprimerStyle($id), JSON_UNESCAPED_UNICODE);
    }
} else {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée."], JSON_UNESCAPED_UNICODE);
}

