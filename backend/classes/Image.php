<?php
class Image {
    private int $id_image;
    private string $titre;
    private string $description;
    private string $url_image;
    private string $date_ajout;
    private string $statut;
    private int $id_style;
    private int $id_photographe;
    private int $id_licence;

    public function __construct(
        int $id_image,
        string $titre,
        string $description,
        string $url_image,
        string $date_ajout,
        string $statut,
        int $id_style,
        int $id_photographe,
        int $id_licence
    ) {
        $this->setIdImage($id_image);
        $this->setTitre($titre);
        $this->setDescription($description);
        $this->setUrlImage($url_image);
        $this->setDateAjout($date_ajout);
        $this->setStatut($statut);
        $this->setIdStyle($id_style);
        $this->setIdPhotographe($id_photographe);
        $this->setIdLicence($id_licence);
    }

    public function getIdImage(): int {
        return $this->id_image;
    }

    public function getTitre(): string {
        return $this->titre;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getUrlImage(): string {
        return $this->url_image;
    }

    public function getDateAjout(): string {
        return $this->date_ajout;
    }

    public function getStatut(): string {
        return $this->statut;
    }

    public function getIdStyle(): int {
        return $this->id_style;
    }

    public function getIdPhotographe(): int {
        return $this->id_photographe;
    }

    public function getIdLicence(): int {
        return $this->id_licence;
    }

    public function setIdImage(int $id_image): void {
        if ($id_image <= 0) {
            throw new InvalidArgumentException("L'identifiant de l'image doit être un entier positif.");
        }
        $this->id_image = $id_image;
    }

    public function setTitre(string $titre): void {
        $valeur = trim($titre);
        if (mb_strlen($valeur) < 2) {
            throw new InvalidArgumentException("Le titre doit contenir au moins 2 caractères.");
        }
        $this->titre = $valeur;
    }

    public function setDescription(string $description): void {
        $valeur = trim($description);
        if (mb_strlen($valeur) < 10) {
            throw new InvalidArgumentException("La description doit contenir au moins 10 caractères.");
        }
        $this->description = $valeur;
    }

    public function setUrlImage(string $url_image): void {
        $valeur = trim($url_image);
        if ($valeur === "") {
            throw new InvalidArgumentException("L'URL de l'image est obligatoire.");
        }
        $this->url_image = $valeur;
    }

    public function setDateAjout(string $date_ajout): void {
        $valeur = trim($date_ajout);
        if ($valeur === "") {
            throw new InvalidArgumentException("La date d'ajout est obligatoire.");
        }
        $this->date_ajout = $valeur;
    }

    public function setStatut(string $statut): void {
        if ($statut !== "en_attente" && $statut !== "valide") {
            throw new InvalidArgumentException("Le statut doit être 'en_attente' ou 'valide'.");
        }
        $this->statut = $statut;
    }

    public function setIdStyle(int $id_style): void {
        if ($id_style <= 0) {
            throw new InvalidArgumentException("L'identifiant du style doit être un entier positif.");
        }
        $this->id_style = $id_style;
    }

    public function setIdPhotographe(int $id_photographe): void {
        if ($id_photographe <= 0) {
            throw new InvalidArgumentException("L'identifiant du photographe doit être un entier positif.");
        }
        $this->id_photographe = $id_photographe;
    }

    public function setIdLicence(int $id_licence): void {
        if ($id_licence <= 0) {
            throw new InvalidArgumentException("L'identifiant de la licence doit être un entier positif.");
        }
        $this->id_licence = $id_licence;
    }

    public function valider(): void {
        if ($this->statut === "valide") {
            throw new Exception("Cette image est déjà validée.");
        }
        $this->statut = "valide";
    }

    public function toArray(): array {
        return [
            "id_image" => $this->id_image,
            "titre" => $this->titre,
            "description" => $this->description,
            "url_image" => $this->url_image,
            "date_ajout" => $this->date_ajout,
            "statut" => $this->statut,
            "id_style" => $this->id_style,
            "id_photographe" => $this->id_photographe,
            "id_licence" => $this->id_licence
        ];
    }
}

