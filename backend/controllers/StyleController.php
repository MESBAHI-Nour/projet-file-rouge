<?php
require_once __DIR__ . "/../classes/GestionStyle.php";

class StyleController {
    private GestionStyle $gestion;

    public function __construct() {
        $this->gestion = new GestionStyle();
    }

    public function listerStyles(?int $id = null): array {
        if ($id !== null) {
            $style = $this->gestion->getById($id);
            if ($style === null) {
                http_response_code(404);
                return ["erreur" => "Style introuvable."];
            }
            http_response_code(200);
            return [
                "id_style" => $style->getIdStyle(),
                "nom_style" => $style->getNomStyle(),
                "description" => $style->getDescription()
            ];
        }

        $styles = $this->gestion->getAll();
        $data = [];
        foreach ($styles as $s) {
            $data[] = [
                "id_style" => $s->getIdStyle(),
                "nom_style" => $s->getNomStyle(),
                "description" => $s->getDescription()
            ];
        }
        http_response_code(200);
        return $data;
    }

    public function ajouterStyle(): array {
        $contenu = file_get_contents("php://input");
        $input = json_decode($contenu, true);

        if (!is_array($input) || !isset($input["nom_style"]) || !isset($input["description"])) {
            http_response_code(422);
            return ["erreur" => "Données incomplètes ou invalides."];
        }

        try {
            $nom = (string)$input["nom_style"];
            $description = (string)$input["description"];
            $nouveau = $this->gestion->ajouter($nom, $description);
            http_response_code(201);
            return [
                "id_style" => $nouveau->getIdStyle(),
                "nom_style" => $nouveau->getNomStyle(),
                "description" => $nouveau->getDescription()
            ];
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            return ["erreur" => $e->getMessage()];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "existe déjà") !== false) {
                http_response_code(409);
            } else {
                http_response_code(422);
            }
            return ["erreur" => $e->getMessage()];
        }
    }

    public function modifierStyle(int $id): array {
        $contenu = file_get_contents("php://input");
        $input = json_decode($contenu, true);

        if (!is_array($input) || !isset($input["nom_style"]) || !isset($input["description"])) {
            http_response_code(422);
            return ["erreur" => "Données incomplètes ou invalides."];
        }

        try {
            $nom = (string)$input["nom_style"];
            $description = (string)$input["description"];
            $modifie = $this->gestion->modifier($id, $nom, $description);
            http_response_code(200);
            return [
                "id_style" => $modifie->getIdStyle(),
                "nom_style" => $modifie->getNomStyle(),
                "description" => $modifie->getDescription()
            ];
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            return ["erreur" => $e->getMessage()];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "introuvable") !== false) {
                http_response_code(404);
            } else if (strpos($e->getMessage(), "existe déjà") !== false) {
                http_response_code(409);
            } else {
                http_response_code(422);
            }
            return ["erreur" => $e->getMessage()];
        }
    }

    public function supprimerStyle(int $id): array {
        try {
            $this->gestion->supprimer($id);
            http_response_code(200);
            return ["message" => "Style supprimé avec succès."];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), "introuvable") !== false) {
                http_response_code(404);
            } else if (strpos($e->getMessage(), "utilisé") !== false) {
                http_response_code(409);
            } else {
                http_response_code(422);
            }
            return ["erreur" => $e->getMessage()];
        }
    }
}

