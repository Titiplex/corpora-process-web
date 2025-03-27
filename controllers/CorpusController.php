<?php

use \model\Corpus;
use \model\File;

require_once __DIR__ . '/../models/Corpus.php';
require_once __DIR__ . '/../models/File.php';     // Modèle pour gérer les uploads, par ex.
require_once __DIR__ . '/../config/Db.php';

class CorpusController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Index - Liste tous les corpus
     */
    public function index(): array
    {
        return Corpus::findAll($this->pdo);
    }

    /**
     * Store - Crée un nouveau corpus
     * * @param array $formData (title, description, language, created_by)
     * @return array|array[]
     */
    public function store(array $formData): array
    {
        $errors = [];

        // Vérifications
        $title = trim($formData['title'] ?? '');
        $description = trim($formData['description'] ?? '');
        $language = trim($formData['language'] ?? '');
        $createdBy = $formData['created_by'] ?? null; // ID user

        if (empty($title)) {
            $errors[] = "Title is required.";
        }
        if (empty($language)) {
            $errors[] = "Language is required.";
        }
        if (!$createdBy) {
            $errors[] = "Created_by (user ID) is required.";
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // Création d'un nouvel objet Corpus
        $corpus = new Corpus($title, $description, $language, $createdBy);
        $corpus->create($this->pdo); // Insères-en DB

        return ['success' => true, 'corpusId' => $corpus->getId()];
    }

    /**
     * Edit - Récupère un corpus par ID pour affichage dans un formulaire d'édition
     */
    public function edit(int $id): ?Corpus
    {
        $corpus = Corpus::findById($this->pdo, $id);
        if (!$corpus) {
            return null; // ou lever une exception
        }
        return $corpus;
    }

    /**
     * Update - Met à jour un corpus
     * @param int $id
     * @param array $formData
     * @return array
     * @throws Exception
     */
    public function update(int $id, array $formData): array
    {
        $corpus = Corpus::findById($this->pdo, $id);
        if (!$corpus) {
            return ['error' => "Corpus not found."];
        }

        $errors = [];

        // Récupération des nouvelles valeurs
        $title = trim($formData['title'] ?? $corpus->getTitle());
        $description = trim($formData['description'] ?? $corpus->getDescription());
        $language = trim($formData['language'] ?? $corpus->getLanguage());

        if (empty($title)) {
            $errors[] = "Title is required.";
        }
        if (empty($language)) {
            $errors[] = "Language is required.";
        }

        if (!empty($errors)) {
            return ['error' => $errors];
        }

        // Mettre à jour l'objet
        $corpus->setTitle($title);
        $corpus->setDescription($description);
        $corpus->setLanguage($language);

        // Sauvegarde en DB
        $corpus->update($this->pdo);

        return ['success' => true];
    }

    /**
     * destroy - Supprime un corpus
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function destroy(int $id): array
    {
        $corpus = Corpus::findById($this->pdo, $id);
        if (!$corpus) {
            return ['error' => "Corpus not found."];
        }

        $corpus->delete($this->pdo);
        return ['success' => true];
    }

    /**
     * uploadFile - Associe un fichier / image à un corpus
     * @param int $corpusId - L'ID du corpus
     * @param array $fileData - $_FILES['file'] par exemple
     * @param int $uploaderId - L'ID de l'utilisateur qui upload
     */


    public function createFile(int $corpusId, array $fileData, int $uploaderId): array
    {
        // Vérifier si le corpus existe
        $corpus = Corpus::findById($this->pdo, $corpusId);
        if (!$corpus) {
            return ['error' => "Corpus not found for ID $corpusId"];
        }

        // Vérifications sur le fichier (ex : taille, extension)
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            return ['error' => "File upload error code: " . $fileData['error']];
        }
        // Simples vérifs
        $allowedExt = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower(pathinfo($fileData['raw'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExt)) {
            return ['error' => "Invalid file extension."];
        }
        /*
         * if ($fileData['size'] > 5 * 1024 * 1024) {
            return ['error' => "File exceeds 5MB limit."];
        }
         */

        // Enregistrer en base (table files) via le modèle File
        $file = new File(0, $corpusId, $fileData['raw'], $fileData['processed'], $fileData['text'] , $uploaderId, date('Y-m-d H:i:s'));
        $file->create($this->pdo);

        return ['success' => true, 'fileId' => $file->getId()];
    }

    /**
     * listFiles - Liste les fichiers associés à un corpus
     * @param int $corpusId
     * @return array
     */
    public function listFiles(int $corpusId): array
    {
        return File::findByCorpus($this->pdo, $corpusId);
    }
}
