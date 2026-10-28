<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../controllers/LicenceController.php";

$controller = new LicenceController();
$methode = $_SERVER["REQUEST_METHOD"];

if ($methode === "GET") {
    echo json_encode($controller->listerLicences(), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(405);
    echo json_encode(["erreur" => "Méthode non autorisée."], JSON_UNESCAPED_UNICODE);
}

