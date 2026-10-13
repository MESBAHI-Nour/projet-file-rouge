<?php

class style_photographique
{
    private ?int $id_style;
    private string $nom_style;
    private string $description;

    public function __construct(?int $id_style, string $nom_style, string $description)
    {
        $this->id_style = $id_style;
        $this->setNomStyle($nom_style);
        $this->setDescription($description);
    }

    public function getIdStyle(): ?int
    {
        return $this->id_style;
    }

    public function getNomStyle(): string
    {
        return $this->nom_style;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setNomStyle(string $nom_style): void
    {
        $nom_style = trim($nom_style);
        if (mb_strlen($nom_style) < 2) {
            throw new InvalidArgumentException("Le nom du style doit contenir au moins 2 caractères.");
        }
        $this->nom_style = $nom_style;
    }

    public function setDescription(string $description): void
    {
        $description = trim($description);
        if (mb_strlen($description) < 10) {
            throw new InvalidArgumentException("La description doit contenir au moins 10 caractères.");
        }
        $this->description = $description;
    }

    public function toArray(): array
    {
        return [
            'id_style' => $this->id_style,
            'nom_style' => $this->nom_style,
            'description' => $this->description
        ];
    }
}
