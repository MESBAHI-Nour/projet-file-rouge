<?php
class GestionLicence {
    private string $fichierData = __DIR__ . "/../data/gestionLicence.json";

    public function getAll(): array {
        if (!file_exists($this->fichierData)) {
            return [];
        }
        $json = file_get_contents($this->fichierData);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }
        return $data;
    }

    public function getById(int $id): ?array {
        $licences = $this->getAll();
        foreach ($licences as $l) {
            if (isset($l["id_licence"]) && (int)$l["id_licence"] === $id) {
                return $l;
            }
        }
        return null;
    }

    public function existe(int $id): bool {
        return ($this->getById($id) !== null);
    }
}

