<?php
require_once __DIR__ . "/../classes/GestionImage.php";

class ImageController {
    private GestionImage $gestion;

    public function __construct() {
        $this->gestion = new GestionImage();
    }

    public function listerImages(?int $id = null): array {
        if ($id !== null) {
            $image = $this->gestion->getById($id);
            if ($image === null) {
                http_response_code(404);
                return ["erreur" => "Image introuvable."];
            }
            http_response_code(200);
            return [
                "id_image" => $image->getIdImage(),
                "titre" => $image->getTitre(),
                "description" => $image->getDescription(),
                "url_image" => $image->getUrlImage(),
                "date_ajout" => $image->getDateAjout(),
                "statut" => $image->getStatut(),
                "id_style" => $image->getIdStyle(),
                "id_photographe" => $image->getIdPhotographe(),
                "id_licence" => $image->getIdLicence()
            ];
        }

        $images = $this->gestion->getAll();
        $data = [];
        foreach ($images as $img) {
            $data[] = [
                "id_image" => $img->getIdImage(),
                "titre" => $img->getTitre(),
                "description" => $img->getDescription(),
                "url_image" => $img->getUrlImage(),
                "date_ajout" => $img->getDateAjout(),
                "statut" => $img->getStatut(),
                "id_style" => $img->getIdStyle(),
                "id_photographe" => $img->getIdPhotographe(),
                "id_licence" => $img->getIdLicence()
            ];
        }
        http_response_code(200);
        return $data;
    }

    public function ajouterImage(): array {
        if (empty($_FILES) && isset($_SERVER["CONTENT_LENGTH"]) && (int)$_SERVER["CONTENT_LENGTH"] > 0) {
            http_response_code(422);
            return ["erreur" => "Fichier trop volumineux (limite du serveur)"];
        }

        if (!isset($_FILES["fichier"]) || $_FILES["fichier"]["error"] === UPLOAD_ERR_NO_FILE) {
            http_response_code(422);
            return ["erreur" => "Photo obligatoire"];
        }

        if ($_FILES["fichier"]["error"] !== UPLOAD_ERR_OK) {
            http_response_code(422);
            return ["erreur" => "Erreur lors du téléchargement du fichier"];
        }

        $titre = isset($_POST["titre"]) ? (string)$_POST["titre"] : "";
        $description = isset($_POST["description"]) ? (string)$_POST["description"] : "";
        $idStyle = isset($_POST["id_style"]) ? (int)$_POST["id_style"] : 0;
        $idPhotographe = isset($_POST["id_photographe"]) ? (int)$_POST["id_photographe"] : 0;
        $idLicence = isset($_POST["id_licence"]) ? (int)$_POST["id_licence"] : 0;

        try {
            $nouvelleImage = $this->gestion->ajouter(
                $titre,
                $description,
                $idStyle,
                $idPhotographe,
                $idLicence,
                $_FILES["fichier"]
            );
            http_response_code(201);
            return [
                "id_image" => $nouvelleImage->getIdImage(),
                "titre" => $nouvelleImage->getTitre(),
                "description" => $nouvelleImage->getDescription(),
                "url_image" => $nouvelleImage->getUrlImage(),
                "date_ajout" => $nouvelleImage->getDateAjout(),
                "statut" => $nouvelleImage->getStatut(),
                "id_style" => $nouvelleImage->getIdStyle(),
                "id_photographe" => $nouvelleImage->getIdPhotographe(),
                "id_licence" => $nouvelleImage->getIdLicence()
            ];
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            return ["erreur" => $e->getMessage()];
        } catch (Exception $e) {
            http_response_code(422);
            return ["erreur" => $e->getMessage()];
        }
    }

    public function modifierImage(int $id): array {
        $imageExistante = $this->gestion->getById($id);
        if ($imageExistante === null) {
            http_response_code(404);
            return ["erreur" => "Image introuvable."];
        }

        if (empty($_FILES) && isset($_SERVER["CONTENT_LENGTH"]) && (int)$_SERVER["CONTENT_LENGTH"] > 0) {
            http_response_code(422);
            return ["erreur" => "Fichier trop volumineux (limite du serveur)"];
        }

        $fichier = null;
        if (isset($_FILES["fichier"])) {
            if ($_FILES["fichier"]["error"] === UPLOAD_ERR_NO_FILE) {
                $fichier = null;
            } else if ($_FILES["fichier"]["error"] !== UPLOAD_ERR_OK) {
                http_response_code(422);
                return ["erreur" => "Erreur lors du téléchargement du fichier"];
            } else {
                $fichier = $_FILES["fichier"];
            }
        }

        $titre = isset($_POST["titre"]) ? (string)$_POST["titre"] : "";
        $description = isset($_POST["description"]) ? (string)$_POST["description"] : "";
        $idStyle = isset($_POST["id_style"]) ? (int)$_POST["id_style"] : 0;
        $idPhotographe = isset($_POST["id_photographe"]) ? (int)$_POST["id_photographe"] : 0;
        $idLicence = isset($_POST["id_licence"]) ? (int)$_POST["id_licence"] : 0;

        try {
            $imageModifiee = $this->gestion->modifier(
                $id,
                $titre,
                $description,
                $idStyle,
                $idPhotographe,
                $idLicence,
                $fichier
            );
            http_response_code(200);
            return [
                "id_image" => $imageModifiee->getIdImage(),
                "titre" => $imageModifiee->getTitre(),
                "description" => $imageModifiee->getDescription(),
                "url_image" => $imageModifiee->getUrlImage(),
                "date_ajout" => $imageModifiee->getDateAjout(),
                "statut" => $imageModifiee->getStatut(),
                "id_style" => $imageModifiee->getIdStyle(),
                "id_photographe" => $imageModifiee->getIdPhotographe(),
                "id_licence" => $imageModifiee->getIdLicence()
            ];
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            return ["erreur" => $e->getMessage()];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "introuvable") !== false) {
                http_response_code(404);
            } else {
                http_response_code(422);
            }
            return ["erreur" => $e->getMessage()];
        }
    }

    public function validerImage(int $id): array {
        try {
            $imageValidee = $this->gestion->valider($id);
            http_response_code(200);
            return [
                "id_image" => $imageValidee->getIdImage(),
                "titre" => $imageValidee->getTitre(),
                "description" => $imageValidee->getDescription(),
                "url_image" => $imageValidee->getUrlImage(),
                "date_ajout" => $imageValidee->getDateAjout(),
                "statut" => $imageValidee->getStatut(),
                "id_style" => $imageValidee->getIdStyle(),
                "id_photographe" => $imageValidee->getIdPhotographe(),
                "id_licence" => $imageValidee->getIdLicence()
            ];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "introuvable") !== false) {
                http_response_code(404);
            } else if (strpos($e->getMessage(), "déjà validée") !== false) {
                http_response_code(409);
            } else {
                http_response_code(422);
            }
            return ["erreur" => $e->getMessage()];
        }
    }

    public function supprimerImage(int $id): array {
        try {
            $this->gestion->supprimer($id);
            http_response_code(200);
            return ["message" => "Image supprimée avec succès."];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "introuvable") !== false) {
                http_response_code(404);
            } else {
                http_response_code(422);
            }
            return ["erreur" => $e->getMessage()];
        }
    }
}

