<?php
require_once __DIR__ . "/Image.php";
require_once __DIR__ . "/GestionStyle.php";
require_once __DIR__ . "/GestionPhotographe.php";
require_once __DIR__ . "/GestionLicence.php";

class GestionImage {
    private string $fichierData = __DIR__ . "/../data/gestionImage.json";
    private string $dossierImages = __DIR__ . "/../images/";

    private function enregistrerFichier(array $fichier): string {
        if (!isset($fichier["error"]) || $fichier["error"] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException("Erreur lors du téléchargement de la photo.");
        }

        // Limite de taille à 5 Mo (5 * 1024 * 1024 octets)
        if ($fichier["size"] > 5242880) {
            throw new InvalidArgumentException("La photo dépasse la taille maximale autorisée (5 Mo).");
        }

        if (!isset($fichier["tmp_name"]) || !is_uploaded_file($fichier["tmp_name"])) {
            throw new InvalidArgumentException("Fichier téléchargé invalide.");
        }

        // Détection sécurisée du type MIME réel
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fichier["tmp_name"]);
        
        $extensionsValides = [
            "image/jpeg" => ".jpg",
            "image/png"  => ".png",
            "image/webp" => ".webp"
        ];

        if (!isset($extensionsValides[$mime])) {
            throw new InvalidArgumentException("Format d'image non autorisé (seuls jpg, jpeg, png, webp sont acceptés).");
        }

        $extension = $extensionsValides[$mime];
        $nomAleatoire = bin2hex(random_bytes(16)) . $extension;
        $destination = $this->dossierImages . $nomAleatoire;

        if (!move_uploaded_file($fichier["tmp_name"], $destination)) {
            throw new Exception("Échec de l'enregistrement physique du fichier.");
        }

        return "images/" . $nomAleatoire;
    }

    private function supprimerFichierPhysique(string $urlImage): void {
        $dossierImagesReel = realpath($this->dossierImages);
        $cheminComplet = __DIR__ . "/../" . $urlImage;
        $cheminReel = realpath($cheminComplet);

        if ($cheminReel !== false && $dossierImagesReel !== false) {
            if (strpos($cheminReel, $dossierImagesReel) === 0 && file_exists($cheminReel) && basename($cheminReel) !== ".gitkeep") {
                @unlink($cheminReel);
            }
        }
    }

    private function verifierRelations(int $idStyle, int $idPhotographe, int $idLicence): void {
        $gestionStyle = new GestionStyle();
        if ($gestionStyle->getById($idStyle) === null) {
            throw new InvalidArgumentException("Le style sélectionné n'existe pas.");
        }

        $gestionPhotographe = new GestionPhotographe();
        if (!$gestionPhotographe->existe($idPhotographe)) {
            throw new InvalidArgumentException("Le photographe sélectionné n'existe pas.");
        }

        $gestionLicence = new GestionLicence();
        if (!$gestionLicence->existe($idLicence)) {
            throw new InvalidArgumentException("La licence sélectionnée n'existe pas.");
        }
    }

    private function calculerNouvelId(array $images): int {
        if (count($images) === 0) {
            return 1;
        }
        $maxId = 0;
        foreach ($images as $img) {
            if ($img->getIdImage() > $maxId) {
                $maxId = $img->getIdImage();
            }
        }
        return $maxId + 1;
    }

    private function sauvegarder(array $images): bool {
        $data = [];
        foreach ($images as $img) {
            $data[] = $img->toArray();
        }
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $resultat = file_put_contents($this->fichierData, $json, LOCK_EX);
        return ($resultat !== false);
    }

    public function getAll(): array {
        if (!file_exists($this->fichierData)) {
            return [];
        }
        $json = file_get_contents($this->fichierData);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        $images = [];
        foreach ($data as $ligne) {
            $img = new Image(
                (int)$ligne["id_image"],
                (string)$ligne["titre"],
                (string)$ligne["description"],
                (string)$ligne["url_image"],
                (string)$ligne["date_ajout"],
                (string)$ligne["statut"],
                (int)$ligne["id_style"],
                (int)$ligne["id_photographe"],
                (int)$ligne["id_licence"]
            );
            $images[] = $img;
        }
        return $images;
    }

    public function getById(int $id): ?Image {
        $images = $this->getAll();
        foreach ($images as $img) {
            if ($img->getIdImage() === $id) {
                return $img;
            }
        }
        return null;
    }

    public function ajouter(
        string $titre,
        string $description,
        int $idStyle,
        int $idPhotographe,
        int $idLicence,
        array $fichier
    ): Image {
        $this->verifierRelations($idStyle, $idPhotographe, $idLicence);

        if (mb_strlen(trim($titre)) < 2) {
            throw new InvalidArgumentException("Le titre doit contenir au moins 2 caractères.");
        }
        if (mb_strlen(trim($description)) < 10) {
            throw new InvalidArgumentException("La description doit contenir au moins 10 caractères.");
        }

        $urlImage = $this->enregistrerFichier($fichier);

        try {
            $images = $this->getAll();
            $nouvelId = $this->calculerNouvelId($images);
            $dateAjout = date("Y-m-d");
            $statut = "en_attente";

            $nouvelleImage = new Image(
                $nouvelId,
                $titre,
                $description,
                $urlImage,
                $dateAjout,
                $statut,
                $idStyle,
                $idPhotographe,
                $idLicence
            );

            $images[] = $nouvelleImage;
            $succes = $this->sauvegarder($images);

            if (!$succes) {
                throw new Exception("Erreur lors de l'enregistrement de l'image.");
            }

            return $nouvelleImage;
        } catch (Throwable $e) {
            $this->supprimerFichierPhysique($urlImage);
            throw $e;
        }
    }

    public function modifier(
        int $id,
        string $titre,
        string $description,
        int $idStyle,
        int $idPhotographe,
        int $idLicence,
        ?array $fichier
    ): Image {
        $images = $this->getAll();
        $imageAModifier = null;
        foreach ($images as $img) {
            if ($img->getIdImage() === $id) {
                $imageAModifier = $img;
                break;
            }
        }

        if ($imageAModifier === null) {
            throw new Exception("Image introuvable.");
        }

        $this->verifierRelations($idStyle, $idPhotographe, $idLicence);

        // Validation des champs via les setters
        $imageAModifier->setTitre($titre);
        $imageAModifier->setDescription($description);
        $imageAModifier->setIdStyle($idStyle);
        $imageAModifier->setIdPhotographe($idPhotographe);
        $imageAModifier->setIdLicence($idLicence);

        $ancienneUrl = $imageAModifier->getUrlImage();
        $nouvelleUrl = $ancienneUrl;
        $nouveauFichierEnregistre = false;

        if ($fichier !== null && isset($fichier["error"]) && $fichier["error"] !== UPLOAD_ERR_NO_FILE) {
            $nouvelleUrl = $this->enregistrerFichier($fichier);
            $nouveauFichierEnregistre = true;
        }

        $imageAModifier->setUrlImage($nouvelleUrl);

        $succes = $this->sauvegarder($images);

        if (!$succes) {
            if ($nouveauFichierEnregistre) {
                $this->supprimerFichierPhysique($nouvelleUrl);
            }
            throw new Exception("Erreur lors de la modification de l'image.");
        }

        if ($nouveauFichierEnregistre) {
            $this->supprimerFichierPhysique($ancienneUrl);
        }

        return $imageAModifier;
    }

    public function valider(int $id): Image {
        $images = $this->getAll();
        $imageAValider = null;
        foreach ($images as $img) {
            if ($img->getIdImage() === $id) {
                $imageAValider = $img;
                break;
            }
        }

        if ($imageAValider === null) {
            throw new Exception("Image introuvable.");
        }

        $imageAValider->valider();

        $succes = $this->sauvegarder($images);
        if (!$succes) {
            throw new Exception("Erreur lors de la validation de l'image.");
        }

        return $imageAValider;
    }

    public function supprimer(int $id): void {
        $images = $this->getAll();
        $imageASupprimer = null;
        $nouvellesImages = [];
        foreach ($images as $img) {
            if ($img->getIdImage() === $id) {
                $imageASupprimer = $img;
            } else {
                $nouvellesImages[] = $img;
            }
        }

        if ($imageASupprimer === null) {
            throw new Exception("Image introuvable.");
        }

        $succes = $this->sauvegarder($nouvellesImages);
        if (!$succes) {
            throw new Exception("Erreur lors de la suppression de l'image.");
        }

        $this->supprimerFichierPhysique($imageASupprimer->getUrlImage());
    }
}

