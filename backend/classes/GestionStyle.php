<?php
require_once __DIR__ . "/StylePhotographique.php";

class GestionStyle {
    private string $fichierData = __DIR__ . "/../data/gestionStyle.json";
    private string $fichierImages = __DIR__ . "/../data/gestionImage.json";

    public function getAll(): array {
        if (!file_exists($this->fichierData)) {
            return [];
        }
        $json = file_get_contents($this->fichierData);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        $styles = [];
        foreach ($data as $ligne) {
            $style = new StylePhotographique(
                (int)$ligne["id_style"],
                (string)$ligne["nom_style"],
                (string)$ligne["description"]
            );
            $styles[] = $style;
        }
        return $styles;
    }

    public function getById(int $id): ?StylePhotographique {
        $styles = $this->getAll();
        foreach ($styles as $style) {
            if ($style->getIdStyle() === $id) {
                return $style;
            }
        }
        return null;
    }

    public function nomExiste(string $nom, int $idExclu = 0): bool {
        $styles = $this->getAll();
        $nomNettoye = mb_strtolower(trim($nom));
        foreach ($styles as $style) {
            if ($style->getIdStyle() !== $idExclu) {
                if (mb_strtolower(trim($style->getNomStyle())) === $nomNettoye) {
                    return true;
                }
            }
        }
        return false;
    }

    public function styleUtiliseParImage(int $idStyle): bool {
        if (!file_exists($this->fichierImages)) {
            return false;
        }
        $json = file_get_contents($this->fichierImages);
        $images = json_decode($json, true);
        if (!is_array($images)) {
            return false;
        }
        foreach ($images as $img) {
            if (isset($img["id_style"]) && (int)$img["id_style"] === $idStyle) {
                return true;
            }
        }
        return false;
    }

    private function calculerNouvelId(array $styles): int {
        if (count($styles) === 0) {
            return 1;
        }
        $maxId = 0;
        foreach ($styles as $s) {
            if ($s->getIdStyle() > $maxId) {
                $maxId = $s->getIdStyle();
            }
        }
        return $maxId + 1;
    }

    private function sauvegarder(array $styles): void {
        $data = [];
        foreach ($styles as $s) {
            $data[] = $s->toArray();
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($this->fichierData, $json, LOCK_EX);
    }

    public function ajouter(string $nom, string $description): StylePhotographique {
        if ($this->nomExiste($nom)) {
            throw new Exception("Un style avec ce nom existe déjà.");
        }
        $styles = $this->getAll();
        $id = $this->calculerNouvelId($styles);
        $nouveauStyle = new StylePhotographique($id, $nom, $description);
        $styles[] = $nouveauStyle;
        $this->sauvegarder($styles);
        return $nouveauStyle;
    }

    public function modifier(int $id, string $nom, string $description): StylePhotographique {
        $styles = $this->getAll();
        $styleAModifier = null;
        foreach ($styles as $s) {
            if ($s->getIdStyle() === $id) {
                $styleAModifier = $s;
                break;
            }
        }
        if ($styleAModifier === null) {
            throw new Exception("Style introuvable.");
        }
        if ($this->nomExiste($nom, $id)) {
            throw new Exception("Un style avec ce nom existe déjà.");
        }
        $styleAModifier->setNomStyle($nom);
        $styleAModifier->setDescription($description);
        $this->sauvegarder($styles);
        return $styleAModifier;
    }

    public function supprimer(int $id): void {
        $styles = $this->getAll();
        $styleTrouve = false;
        $nouveauxStyles = [];
        foreach ($styles as $s) {
            if ($s->getIdStyle() === $id) {
                $styleTrouve = true;
            } else {
                $nouveauxStyles[] = $s;
            }
        }
        if (!$styleTrouve) {
            throw new Exception("Style introuvable.");
        }
        if ($this->styleUtiliseParImage($id)) {
            throw new Exception("Ce style est utilisé par des images");
        }
        $this->sauvegarder($nouveauxStyles);
    }
}

