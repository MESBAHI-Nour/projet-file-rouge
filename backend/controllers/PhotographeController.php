<?php
require_once __DIR__ . "/../classes/GestionPhotographe.php";

class PhotographeController {
    private GestionPhotographe $gestion;

    public function __construct() {
        $this->gestion = new GestionPhotographe();
    }

    public function listerPhotographes(): array {
        http_response_code(200);
        return $this->gestion->getAll();
    }
}

