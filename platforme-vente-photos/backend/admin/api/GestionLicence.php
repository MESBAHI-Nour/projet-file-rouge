<?php
header("Content-Type: application/json; charset=utf-8");

class GestionLicence
{
    private string $path_file;

    public function __construct()
    {
        $this->path_file = __DIR__ . '/../../database/gestionLicence.json';
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

    public function traiterRequete(): void
    {
        $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($methode !== 'GET') {
            $this->repondre(405, ['erreur' => 'Méthode non autorisée']);
        }

        $this->consulter();
    }

    public function consulter(): void
    {
        $licences = $this->lireDonnees();
        $this->repondre(200, $licences);
    }
}

$api = new GestionLicence();
$api->traiterRequete();
