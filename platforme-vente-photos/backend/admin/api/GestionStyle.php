<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../class/style_photographique.php';

class GestionStyle
{
    private string $path_file;
    private string $path_images;

    public function __construct()
    {
        $this->path_file = __DIR__ . '/../../database/gestionStyle.json';
        $this->path_images = __DIR__ . '/../../database/gestionImage.json';
    }

    private function repondre(int $code, array $donnees): void
    {
        http_response_code($code);
        echo json_encode($donnees, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function lireDonnees(): array
    {
        if (!file_exists($this->path_file)) {
            return [];
        }
        $contenu = file_get_contents($this->path_file);
        $donnees = json_decode($contenu, true);
        return is_array($donnees) ? $donnees : [];
    }

    private function enregistrerDonnees(array $donnees): void
    {
        file_put_contents(
            $this->path_file,
            json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            LOCK_EX
        );
    }

    public function traiterRequete(): void
    {
        $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        switch ($methode) {
            case 'GET':
                $this->consulter();
                break;
            case 'POST':
                $this->ajouter();
                break;
            case 'PUT':
                $this->modifier();
                break;
            case 'DELETE':
                $this->supprimer();
                break;
            default:
                $this->repondre(405, ['erreur' => 'Méthode non autorisée']);
        }
    }

    public function consulter(): void
    {
        $styles = $this->lireDonnees();

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            foreach ($styles as $s) {
                if ((int)$s['id_style'] === $id) {
                    $this->repondre(200, $s);
                }
            }
            $this->repondre(404, ['erreur' => 'Style non trouvé']);
        }

        $this->repondre(200, $styles);
    }

    public function ajouter(): void
    {
        $corps = file_get_contents('php://input');
        $donnees = json_decode($corps, true);

        if (!is_array($donnees) || !isset($donnees['nom_style']) || !isset($donnees['description'])) {
            $this->repondre(422, ['erreur' => 'Données incomplètes ou invalides']);
        }

        $styles = $this->lireDonnees();
        $nomStyle = trim((string)$donnees['nom_style']);

        foreach ($styles as $s) {
            if (mb_strtolower($s['nom_style']) === mb_strtolower($nomStyle)) {
                $this->repondre(409, ['erreur' => 'Un style avec ce nom existe déjà']);
            }
        }

        $maxId = 0;
        foreach ($styles as $s) {
            if (isset($s['id_style']) && (int)$s['id_style'] > $maxId) {
                $maxId = (int)$s['id_style'];
            }
        }
        $nouveauId = $maxId + 1;

        try {
            $style = new style_photographique(
                $nouveauId,
                $nomStyle,
                (string)$donnees['description']
            );
        } catch (InvalidArgumentException $e) {
            $this->repondre(422, ['erreur' => $e->getMessage()]);
        }

        $styles[] = $style->toArray();
        $this->enregistrerDonnees($styles);

        $this->repondre(201, $style->toArray());
    }

    public function modifier(): void
    {
        if (!isset($_GET['id'])) {
            $this->repondre(404, ['erreur' => 'Identifiant du style manquant']);
        }

        $id = (int)$_GET['id'];
        $styles = $this->lireDonnees();
        $index = -1;

        foreach ($styles as $i => $s) {
            if ((int)$s['id_style'] === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === -1) {
            $this->repondre(404, ['erreur' => 'Style non trouvé']);
        }

        $corps = file_get_contents('php://input');
        $donnees = json_decode($corps, true);

        if (!is_array($donnees) || !isset($donnees['nom_style']) || !isset($donnees['description'])) {
            $this->repondre(422, ['erreur' => 'Données incomplètes ou invalides']);
        }

        $nomStyle = trim((string)$donnees['nom_style']);

        foreach ($styles as $i => $s) {
            if ($i !== $index && mb_strtolower($s['nom_style']) === mb_strtolower($nomStyle)) {
                $this->repondre(409, ['erreur' => 'Un style avec ce nom existe déjà']);
            }
        }

        try {
            $style = new style_photographique(
                $id,
                $nomStyle,
                (string)$donnees['description']
            );
        } catch (InvalidArgumentException $e) {
            $this->repondre(422, ['erreur' => $e->getMessage()]);
        }

        $styles[$index] = $style->toArray();
        $this->enregistrerDonnees($styles);

        $this->repondre(200, $style->toArray());
    }

    public function supprimer(): void
    {
        if (!isset($_GET['id'])) {
            $this->repondre(404, ['erreur' => 'Identifiant du style manquant']);
        }

        $id = (int)$_GET['id'];
        $styles = $this->lireDonnees();
        $index = -1;

        foreach ($styles as $i => $s) {
            if ((int)$s['id_style'] === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === -1) {
            $this->repondre(404, ['erreur' => 'Style non trouvé']);
        }

        if (file_exists($this->path_images)) {
            $imagesJson = file_get_contents($this->path_images);
            $images = json_decode($imagesJson, true);
            if (is_array($images)) {
                foreach ($images as $img) {
                    if (isset($img['id_style']) && (int)$img['id_style'] === $id) {
                        $this->repondre(409, ['erreur' => 'Ce style est utilisé par des images']);
                    }
                }
            }
        }

        array_splice($styles, $index, 1);
        $this->enregistrerDonnees($styles);

        $this->repondre(200, ['message' => 'Style supprimé avec succès']);
    }
}

$api = new GestionStyle();
$api->traiterRequete();
