<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/../class/image.php';

class GestionImage
{
    private string $path_file;
    private string $dir_images;

    public function __construct()
    {
        $this->path_file = __DIR__ . '/../../database/gestionImage.json';
        $this->dir_images = __DIR__ . '/../../images';
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

    private function enregistrerDonnees(array $donnees): bool
    {
        $result = file_put_contents(
            $this->path_file,
            json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            LOCK_EX
        );
        return $result !== false;
    }

    private function enregistrerFichier(bool $obligatoire): ?string
    {
        $contentLength = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if (empty($_FILES) && $contentLength > 0) {
            $this->repondre(422, ['erreur' => 'Fichier trop volumineux (limite du serveur)']);
        }

        if (!isset($_FILES['fichier'])) {
            if ($obligatoire) {
                $this->repondre(422, ['erreur' => 'Photo obligatoire']);
            }
            return null;
        }

        $erreur = $_FILES['fichier']['error'];

        if ($erreur === UPLOAD_ERR_NO_FILE) {
            if ($obligatoire) {
                $this->repondre(422, ['erreur' => 'Photo obligatoire']);
            }
            return null;
        }

        if ($erreur !== UPLOAD_ERR_OK) {
            $messages = [
                UPLOAD_ERR_INI_SIZE   => 'Fichier trop volumineux (limite du serveur)',
                UPLOAD_ERR_FORM_SIZE  => 'Fichier trop volumineux (limite du formulaire)',
                UPLOAD_ERR_PARTIAL    => "Le fichier n'a été que partiellement téléversé",
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur',
                UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le serveur",
                UPLOAD_ERR_EXTENSION  => 'Téléversement bloqué par une extension PHP',
            ];
            $msg = $messages[$erreur] ?? "Erreur de téléversement (code $erreur)";
            $this->repondre(422, ['erreur' => $msg]);
        }

        if ($_FILES['fichier']['size'] > 5 * 1024 * 1024) {
            $this->repondre(422, ['erreur' => 'Fichier trop volumineux (maximum 5 Mo)']);
        }

        $tmpPath = $_FILES['fichier']['tmp_name'];
        if (!is_uploaded_file($tmpPath)) {
            $this->repondre(422, ['erreur' => 'Fichier téléversé invalide']);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpPath);

        $extensionsAutorisees = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensionsAutorisees[$mime])) {
            $this->repondre(422, ['erreur' => 'Format non autorisé. Seuls JPG, PNG et WebP sont acceptés.']);
        }

        $extension = $extensionsAutorisees[$mime];
        $nomFichier = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $this->dir_images . DIRECTORY_SEPARATOR . $nomFichier;

        if (!move_uploaded_file($tmpPath, $destination)) {
            $this->repondre(500, ['erreur' => 'Impossible de déplacer le fichier téléversé']);
        }

        return 'images/' . $nomFichier;
    }

    private function verifierClesEtrangeres(int $id_style, int $id_photographe, int $id_licence): void
    {
        $pathStyle = __DIR__ . '/../../database/gestionStyle.json';
        if (!file_exists($pathStyle)) {
            throw new InvalidArgumentException("Le style sélectionné n'existe pas.");
        }
        $styles = json_decode(file_get_contents($pathStyle), true);
        $idsStyles = is_array($styles) ? array_map('intval', array_column($styles, 'id_style')) : [];
        if (!in_array($id_style, $idsStyles, true)) {
            throw new InvalidArgumentException("Le style sélectionné n'existe pas.");
        }

        $pathPhotographe = __DIR__ . '/../../database/gestionPhotographe.json';
        if (!file_exists($pathPhotographe)) {
            throw new InvalidArgumentException("Le photographe sélectionné n'existe pas.");
        }
        $photographes = json_decode(file_get_contents($pathPhotographe), true);
        $idsPhotographes = is_array($photographes) ? array_map('intval', array_column($photographes, 'id_photographe')) : [];
        if (!in_array($id_photographe, $idsPhotographes, true)) {
            throw new InvalidArgumentException("Le photographe sélectionné n'existe pas.");
        }

        $pathLicence = __DIR__ . '/../../database/gestionLicence.json';
        if (!file_exists($pathLicence)) {
            throw new InvalidArgumentException("La licence sélectionnée n'existe pas.");
        }
        $licences = json_decode(file_get_contents($pathLicence), true);
        $idsLicences = is_array($licences) ? array_map('intval', array_column($licences, 'id_licence')) : [];
        if (!in_array($id_licence, $idsLicences, true)) {
            throw new InvalidArgumentException("La licence sélectionnée n'existe pas.");
        }
    }

    public function traiterRequete(): void
    {
        $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $action = $_GET['action'] ?? '';
        $idParam = isset($_GET['id']) ? (int)$_GET['id'] : null;

        switch ($methode) {
            case 'GET':
                $this->consulter();
                break;

            case 'POST':
                if ($idParam !== null && $action === 'modifier') {
                    $this->modifier($idParam);
                } elseif ($idParam === null && $action === '') {
                    $this->creer();
                } else {
                    $this->repondre(405, ['erreur' => 'Méthode non autorisée']);
                }
                break;

            case 'PUT':
                $this->valider();
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
        $images = $this->lireDonnees();

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            foreach ($images as $img) {
                if ((int)$img['id_image'] === $id) {
                    $this->repondre(200, $img);
                }
            }
            $this->repondre(404, ['erreur' => 'Image non trouvée']);
        }

        $this->repondre(200, $images);
    }

    public function creer(): void
    {
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $id_style = (int)($_POST['id_style'] ?? 0);
        $id_photographe = (int)($_POST['id_photographe'] ?? 0);
        $id_licence = (int)($_POST['id_licence'] ?? 0);

        try {
            $this->verifierClesEtrangeres($id_style, $id_photographe, $id_licence);
        } catch (InvalidArgumentException $e) {
            $this->repondre(422, ['erreur' => $e->getMessage()]);
        }

        $url_image = $this->enregistrerFichier(true);

        try {
            $images = $this->lireDonnees();
            $ids = array_column($images, 'id_image');
            $nouvelId = empty($ids) ? 1 : (max($ids) + 1);

            $imageObj = new image(
                $nouvelId,
                $titre,
                $description,
                $url_image,
                date('Y-m-d'),
                'en_attente',
                $id_style,
                $id_photographe,
                $id_licence
            );

            $images[] = $imageObj->toArray();

            if (!$this->enregistrerDonnees($images)) {
                $fichier = $this->dir_images . DIRECTORY_SEPARATOR . basename($url_image);
                if (is_file($fichier)) {
                    unlink($fichier);
                }
                $this->repondre(500, ['erreur' => "Erreur lors de l'écriture des données"]);
            }

            $this->repondre(201, $imageObj->toArray());
        } catch (InvalidArgumentException $e) {
            if ($url_image !== null) {
                $fichier = $this->dir_images . DIRECTORY_SEPARATOR . basename($url_image);
                if (is_file($fichier)) {
                    unlink($fichier);
                }
            }
            $this->repondre(422, ['erreur' => $e->getMessage()]);
        }
    }

    public function modifier(int $id): void
    {
        $images = $this->lireDonnees();
        $index = -1;

        foreach ($images as $i => $img) {
            if ((int)$img['id_image'] === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === -1) {
            $this->repondre(404, ['erreur' => 'Image non trouvée']);
        }

        $ancienneData = $images[$index];
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $id_style = (int)($_POST['id_style'] ?? 0);
        $id_photographe = (int)($_POST['id_photographe'] ?? 0);
        $id_licence = (int)($_POST['id_licence'] ?? 0);

        try {
            $this->verifierClesEtrangeres($id_style, $id_photographe, $id_licence);
        } catch (InvalidArgumentException $e) {
            $this->repondre(422, ['erreur' => $e->getMessage()]);
        }

        $nouvelleUrl = $this->enregistrerFichier(false);

        try {
            $imageObj = new image(
                (int)$ancienneData['id_image'],
                (string)$ancienneData['titre'],
                (string)$ancienneData['description'],
                (string)$ancienneData['url_image'],
                (string)$ancienneData['date_ajout'],
                (string)$ancienneData['statut'],
                (int)$ancienneData['id_style'],
                (int)$ancienneData['id_photographe'],
                (int)$ancienneData['id_licence']
            );

            $imageObj->modifier($titre, $description, $id_style, $id_photographe, $id_licence, $nouvelleUrl);

            $images[$index] = $imageObj->toArray();

            if (!$this->enregistrerDonnees($images)) {
                if ($nouvelleUrl !== null) {
                    $fichier = $this->dir_images . DIRECTORY_SEPARATOR . basename($nouvelleUrl);
                    if (is_file($fichier)) {
                        unlink($fichier);
                    }
                }
                $this->repondre(500, ['erreur' => "Erreur lors de l'écriture des données"]);
            }

            if ($nouvelleUrl !== null && !empty($ancienneData['url_image'])) {
                $ancienNom = basename($ancienneData['url_image']);
                $realImagesDir = realpath($this->dir_images);
                $ancienChemin = realpath($this->dir_images . DIRECTORY_SEPARATOR . $ancienNom);

                if (
                    $realImagesDir && $ancienChemin &&
                    str_starts_with($ancienChemin, $realImagesDir) &&
                    is_file($ancienChemin)
                ) {
                    unlink($ancienChemin);
                }
            }

            $this->repondre(200, $imageObj->toArray());
        } catch (InvalidArgumentException $e) {
            if ($nouvelleUrl !== null) {
                $fichier = $this->dir_images . DIRECTORY_SEPARATOR . basename($nouvelleUrl);
                if (is_file($fichier)) {
                    unlink($fichier);
                }
            }
            $this->repondre(422, ['erreur' => $e->getMessage()]);
        }
    }

    public function valider(): void
    {
        if (!isset($_GET['id'])) {
            $this->repondre(404, ['erreur' => "Identifiant d'image manquant"]);
        }

        if (!isset($_GET['action']) || $_GET['action'] !== 'valider') {
            $this->repondre(405, ['erreur' => 'Action non autorisée']);
        }

        $id = (int)$_GET['id'];
        $images = $this->lireDonnees();
        $index = -1;

        foreach ($images as $i => $img) {
            if ((int)$img['id_image'] === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === -1) {
            $this->repondre(404, ['erreur' => 'Image non trouvée']);
        }

        $data = $images[$index];

        try {
            $imageObj = new image(
                (int)$data['id_image'],
                (string)$data['titre'],
                (string)$data['description'],
                (string)$data['url_image'],
                (string)$data['date_ajout'],
                (string)$data['statut'],
                (int)$data['id_style'],
                (int)$data['id_photographe'],
                (int)$data['id_licence']
            );
            $imageObj->valider();
        } catch (DomainException $e) {
            $this->repondre(409, ['erreur' => $e->getMessage()]);
        } catch (InvalidArgumentException $e) {
            $this->repondre(422, ['erreur' => $e->getMessage()]);
        }

        $images[$index] = $imageObj->toArray();
        $this->enregistrerDonnees($images);

        $this->repondre(200, $imageObj->toArray());
    }

    public function supprimer(): void
    {
        if (!isset($_GET['id'])) {
            $this->repondre(404, ['erreur' => "Identifiant d'image manquant"]);
        }

        $id = (int)$_GET['id'];
        $images = $this->lireDonnees();
        $index = -1;

        foreach ($images as $i => $img) {
            if ((int)$img['id_image'] === $id) {
                $index = $i;
                break;
            }
        }

        if ($index === -1) {
            $this->repondre(404, ['erreur' => 'Image non trouvée']);
        }

        $imgData = $images[$index];

        if (!empty($imgData['url_image'])) {
            $filename = basename($imgData['url_image']);
            $realImagesDir = realpath($this->dir_images);
            $targetPath = realpath($this->dir_images . DIRECTORY_SEPARATOR . $filename);

            if ($realImagesDir && $targetPath && str_starts_with($targetPath, $realImagesDir) && is_file($targetPath)) {
                unlink($targetPath);
            }
        }

        array_splice($images, $index, 1);
        $this->enregistrerDonnees($images);

        $this->repondre(200, ['message' => 'Image supprimée avec succès']);
    }
}

$api = new GestionImage();
$api->traiterRequete();