<?php

class image
{
    private ?int $id_image;
    private string $titre;
    private string $description;
    private string $url_image;
    private string $date_ajout;
    private string $statut;
    private int $id_style;
    private int $id_photographe;
    private int $id_licence;

    public function __construct(
        ?int $id_image,
        string $titre,
        string $description,
        string $url_image,
        string $date_ajout,
        string $statut,
        int $id_style,
        int $id_photographe,
        int $id_licence
    ) {
        $this->id_image = $id_image;
        $this->setTitre($titre);
        $this->setDescription($description);
        $this->setUrlImage($url_image);
        $this->setDateAjout($date_ajout);
        $this->setStatut($statut);
        $this->setIdStyle($id_style);
        $this->setIdPhotographe($id_photographe);
        $this->setIdLicence($id_licence);
    }

    public function getIdImage(): ?int
    {
        return $this->id_image;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUrlImage(): string
    {
        return $this->url_image;
    }

    public function getDateAjout(): string
    {
        return $this->date_ajout;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function getIdStyle(): int
    {
        return $this->id_style;
    }

    public function getIdPhotographe(): int
    {
        return $this->id_photographe;
    }

    public function getIdLicence(): int
    {
        return $this->id_licence;
    }

    public function setTitre(string $titre): void
    {
        $titre = trim($titre);
        if (mb_strlen($titre) < 2) {
            throw new InvalidArgumentException("Le titre de l'image doit contenir au moins 2 caractères.");
        }
        $this->titre = $titre;
    }

    public function setDescription(string $description): void
    {
        $description = trim($description);
        if (mb_strlen($description) < 10) {
            throw new InvalidArgumentException("La description de l'image doit contenir au moins 10 caractères.");
        }
        $this->description = $description;
    }

    public function setUrlImage(string $url_image): void
    {
        $url_image = trim($url_image);
        if (empty($url_image)) {
            throw new InvalidArgumentException("L'URL de l'image ne peut pas être vide.");
        }
        $this->url_image = $url_image;
    }

    public function setDateAjout(string $date_ajout): void
    {
        $date_ajout = trim($date_ajout);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_ajout)) {
            throw new InvalidArgumentException("La date d'ajout doit être au format AAAA-MM-JJ.");
        }
        $this->date_ajout = $date_ajout;
    }

    public function setStatut(string $statut): void
    {
        $statut = trim($statut);
        if ($statut !== 'en_attente' && $statut !== 'valide') {
            throw new InvalidArgumentException("Le statut doit être 'en_attente' ou 'valide'.");
        }
        $this->statut = $statut;
    }

    public function setIdStyle(int $id_style): void
    {
        if ($id_style <= 0) {
            throw new InvalidArgumentException("L'identifiant du style doit être un entier positif.");
        }
        $this->id_style = $id_style;
    }

    public function setIdPhotographe(int $id_photographe): void
    {
        if ($id_photographe <= 0) {
            throw new InvalidArgumentException("L'identifiant du photographe doit être un entier positif.");
        }
        $this->id_photographe = $id_photographe;
    }

    public function setIdLicence(int $id_licence): void
    {
        if ($id_licence <= 0) {
            throw new InvalidArgumentException("L'identifiant de la licence doit être un entier positif.");
        }
        $this->id_licence = $id_licence;
    }

    public function modifier(
        string $titre,
        string $description,
        int $id_style,
        int $id_photographe,
        int $id_licence,
        ?string $url_image = null
    ): void {
        $this->setTitre($titre);
        $this->setDescription($description);
        $this->setIdStyle($id_style);
        $this->setIdPhotographe($id_photographe);
        $this->setIdLicence($id_licence);
        if ($url_image !== null) {
            $this->setUrlImage($url_image);
        }
    }

    public function supprimer(): void
    {
        // Méthode prévue dans le diagramme de classe pour la suppression de l'entité
    }

    public function valider(): void
    {
        if ($this->statut === 'valide') {
            throw new DomainException("Cette image est déjà validée.");
        }
        $this->statut = 'valide';
    }

    public function toArray(): array
    {
        return [
            'id_image' => $this->id_image,
            'titre' => $this->titre,
            'description' => $this->description,
            'url_image' => $this->url_image,
            'date_ajout' => $this->date_ajout,
            'statut' => $this->statut,
            'id_style' => $this->id_style,
            'id_photographe' => $this->id_photographe,
            'id_licence' => $this->id_licence
        ];
    }
}
