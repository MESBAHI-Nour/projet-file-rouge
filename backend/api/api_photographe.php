<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../controllers/PhotographeController.php";

$controller = new PhotographeController();
$methode = $_SERVER["REQUEST_METHOD"];

if ($methode === "GET") {
    echo json_encode($controller->listerPhotographes(), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée."], JSON_UNESCAPED_UNICODE);
}

