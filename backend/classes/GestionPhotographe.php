<?php
class GestionPhotographe {
    private string $fichierData = __DIR__ . "/../data/gestionPhotographe.json";

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
        $photographes = $this->getAll();
        foreach ($photographes as $p) {
            if (isset($p["id_photographe"]) && (int)$p["id_photographe"] === $id) {
                return $p;
            }
        }
        return null;
    }

    public function existe(int $id): bool {
        return ($this->getById($id) !== null);
    }
}

