<?php

// Exemple de namespace si souhaité :
// namespace App\Models;

class Corpus
{
    // Propriétés privées
    private mixed $id {
        get {
            return $this->id;
        }
    }
    private mixed $title {
        get {
            return $this->title;
        }
        set {
            $this->title = $value;
        }
    }
    private mixed $description {
        get {
            return $this->description;
        }
        set {
            $this->description = $value;
        }
    }
    private mixed $language {
        get {
            return $this->language;
        }
        set {
            $this->language = $value;
        }
    }
    private mixed $created_by;  // id de l'utilisateur / auteur
    private mixed $created_at;  // datetime

    // Constructeur
    public function __construct($title, $description, $language, $created_by = null, $id = null, $created_at = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->language = $language;
        $this->created_by = $created_by;
        $this->id = $id;
        $this->created_at = $created_at;
    }

    // --------------------
    // Getters & Setters
    // --------------------

    public function getCreatedBy()
    {
        return $this->created_by;
    }
    public function setCreatedBy($userId): void
    {
        $this->created_by = $userId;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }
    public function setCreatedAt($datetime): void
    {
        $this->created_at = $datetime;
    }

    // --------------------
    // Méthodes CRUD
    // --------------------

    /**
     * Create - Insère un nouveau corpus dans la base de données
     */
    public function create($pdo): void
    {
        // $pdo est une instance de PDO, fournie par ex. depuis config/database.php
        $sql = "INSERT INTO corpora (title, description, language, created_by, created_at) 
                VALUES (:title, :description, :language, :created_by, :created_at)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title', $this->title);
        $stmt->bindValue(':description', $this->description);
        $stmt->bindValue(':language', $this->language);
        $stmt->bindValue(':created_by', $this->created_by, \PDO::PARAM_INT);
        // On peut stocker la date de création "maintenant" ou depuis la propriété $created_at
        $this->created_at = date('Y-m-d H:i:s');
        $stmt->bindValue(':created_at', $this->created_at);

        $stmt->execute();

        // Récupérer l'ID généré
        $this->id = $pdo->lastInsertId();
    }

    /**
     * Update - Met à jour un corpus existant
     * @throws Exception
     */
    public function update($pdo): void
    {
        if (!$this->id) {
            throw new \Exception("Cannot update corpus without ID.");
        }

        $sql = "UPDATE corpora 
                SET title = :title, description = :description, language = :language
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':title', $this->title);
        $stmt->bindValue(':description', $this->description);
        $stmt->bindValue(':language', $this->language);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * delete - Supprime le corpus de la base
     * @throws Exception
     */
    public function delete($pdo): void
    {
        if (!$this->id) {
            throw new \Exception("Cannot delete corpus without ID.");
        }

        $sql = "DELETE FROM corpora WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);

        $stmt->execute();
    }

    // --------------------
    // Méthodes statiques
    // --------------------

    /**
     * findById - Récupère un corpus par son ID
     */
    public static function findById($pdo, $id): ?Corpus
    {
        $sql = "SELECT * FROM corpora WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            // Créer une instance de Corpus
            return new Corpus(
                $row['title'],
                $row['description'],
                $row['language'],
                $row['created_by'],
                $row['id'],
                $row['created_at']
            );
        }

        return null; // Pas trouvé
    }

    /**
     * findAll - Récupère tous les corpus
     */
    public static function findAll($pdo): array
    {
        $sql = "SELECT * FROM corpora ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = new Corpus(
                $row['title'],
                $row['description'],
                $row['language'],
                $row['created_by'],
                $row['id'],
                $row['created_at']
            );
        }
        return $results;
    }
}
