<?php
class StylePhotographique {
    private int $id_style;
    private string $nom_style;
    private string $description;

    public function __construct(int $id_style, string $nom_style, string $description) {
        $this->setIdStyle($id_style);
        $this->setNomStyle($nom_style);
        $this->setDescription($description);
    }

    public function getIdStyle(): int {
        return $this->id_style;
    }

    public function getNomStyle(): string {
        return $this->nom_style;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setIdStyle(int $id_style): void {
        if ($id_style <= 0) {
            throw new InvalidArgumentException("L'identifiant du style doit être un entier positif.");
        }
        $this->id_style = $id_style;
    }

    public function setNomStyle(string $nom_style): void {
        $valeur = trim($nom_style);
        if (mb_strlen($valeur) < 2) {
            throw new InvalidArgumentException("Le nom du style doit contenir au moins 2 caractères.");
        }
        $this->nom_style = $valeur;
    }

    public function setDescription(string $description): void {
        $valeur = trim($description);
        if (mb_strlen($valeur) < 10) {
            throw new InvalidArgumentException("La description du style doit contenir au moins 10 caractères.");
        }
        $this->description = $valeur;
    }

    public function toArray(): array {
        return [
            "id_style" => $this->id_style,
            "nom_style" => $this->nom_style,
            "description" => $this->description
        ];
    }
}

