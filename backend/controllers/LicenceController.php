<?php
require_once __DIR__ . "/../classes/GestionLicence.php";

class LicenceController {
    private GestionLicence $gestion;

    public function __construct() {
        $this->gestion = new GestionLicence();
    }

    public function listerLicences(): array {
        http_response_code(200);
        return $this->gestion->getAll();
    }
}

