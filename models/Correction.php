<?php

namespace model;

use Exception;
use PDOException;
use App;

require_once __DIR__."/../config/App.php";
require_once "Model.php";

class Correction extends Model
{
    private int $image_id;
    private int $proposed_by;
    private string $correction;
    private string $comment;
    private string $created_at;
    private bool $accepted;
    private int $accepted_by;
    private string $accepted_at;

    /**
     * @param int $id
     * @param int $image_id
     * @param int $proposed_by
     * @param string $correction
     * @param string $comment
     * @param string $created_at
     * @param bool $accepted
     * @param int $accepted_by
     * @param string $accepted_at
     */
    public function __construct(int $id, int $image_id, int $proposed_by, string $correction, string $comment, string $created_at, bool $accepted, int $accepted_by, string $accepted_at)
    {
        parent::__construct($id);
        $this->image_id = $image_id;
        $this->proposed_by = $proposed_by;
        $this->correction = $correction;
        $this->comment = $comment;
        $this->created_at = $created_at;
        $this->accepted = $accepted;
        $this->accepted_by = $accepted_by;
        $this->accepted_at = $accepted_at;
    }


    public static function createFromStmt($stmt, string $sql): array
    {
        $stmt->execute();

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = new Correction(
                $row['id'],
                $row['image_id'],
                $row['proposed_by'],
                $row['correction'],
                $row['comment'],
                $row['created_at'],
                $row['accepted'],
                $row['accepted_by'],
                $row['treated_at']
            );
        }
        return $results;
    }

    public function getImageId(): int
    {
        return $this->image_id;
    }

    public function setImageId(int $image_id): void
    {
        $this->image_id = $image_id;
    }

    public function getProposedBy(): int
    {
        return $this->proposed_by;
    }

    public function setProposedBy(int $proposed_by): void
    {
        $this->proposed_by = $proposed_by;
    }

    public function getCorrection(): string
    {
        return $this->correction;
    }

    public function setCorrection(string $correction): void
    {
        $this->correction = $correction;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): void
    {
        $this->accepted = $accepted;
    }

    public function getAcceptedBy(): int
    {
        return $this->accepted_by;
    }

    public function setAcceptedBy(int $accepted_by): void
    {
        $this->accepted_by = $accepted_by;
    }

    public function getAcceptedAt(): string
    {
        return $this->accepted_at;
    }

    public function setAcceptedAt(string $accepted_at): void
    {
        $this->accepted_at = $accepted_at;
    }

    /**
     * Create - Insère un nouveau corpus dans la base de données
     */
    public function create($pdo): bool
    {

        // $pdo est une instance de PDO, fournie par ex. depuis config/Db.php
        $sql = "INSERT INTO corrections (image_id, proposed_by, correction, comment, created_at, accepted, accepted_by, treated_at) 
                VALUES (:image_id, :proposed_by, :correction, :comment, :created_at, :accepted, :accepted_by, :treated_at)";

        $stmt = $pdo->prepare($sql);
        echo "avant try";
        try {
            $pdo->beginTransaction();
            $this->extracted($stmt);
            echo "après extr";
            // On peut stocker la date de création "maintenant" ou depuis la propriété $created_at
            $this->created_at = date('Y-m-d H:i:s');
            $stmt->bindValue(":image_id", $this->image_id);
            $stmt->bindValue(":proposed_by", $this->proposed_by);
            $stmt->bindValue(":correction", $this->correction);
            $stmt->bindValue(":comment", $this->comment);
            $stmt->bindValue(':created_at', $this->created_at);
            $stmt->bindValue(':accepted', $this->accepted, \PDO::PARAM_INT);
            $stmt->bindValue(':accepted_by', $this->accepted_by);
            $stmt->bindValue(':treated_at', $this->accepted_at);
            echo "bindé";

            $stmt->execute();
            echo "exec";

            // Récupérer l'ID généré
            $this->id = $pdo->lastInsertId();

            $pdo->commit();
            echo "commité";
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo $e->getMessage();
            \App::getLogger()->error($e->getMessage());
        }
        return false;
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

        $sql = "UPDATE corrections
                SET image_id = :image_id, proposed_by = :proposed_by, correction = :correction,
                comment = :comment, created_at = :created_at, accepted = :accepted, accepted_by = :accepted_by, treated_at = :treated_at
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        try {
            $pdo->beginTransaction();
            $this->extracted($stmt);
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);

            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            App::getLogger()->error($e->getMessage());
        }
    }

    /**
     * delete - Supprime la correction de la base
     * @throws Exception
     */
    public function delete($pdo): void
    {
        if (!$this->id) {
            throw new \Exception("Cannot delete corpus without ID.");
        }

        $sql = "DELETE FROM corrections WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        try {
            $pdo->beginTransaction();
            $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);

            $stmt->execute();
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            App::getLogger()->error($e->getMessage());
        }
    }

    // --------------------
    // Méthodes statiques
    // --------------------

    /**
     * findById - Récupère un corpus par son ID
     */
    public static function findById($pdo, $id): ?Correction
    {
        $sql = "SELECT * FROM corrections WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row) {
            // Créer une instance de Corpus
            return new Correction(
                $row['id'],
                $row['image_id'],
                $row['proposed_by'],
                $row['correction'],
                $row['comment'],
                $row['created_at'],
                $row['accepted'],
                $row['accepted_by'],
                $row['treated_at']
            );
        }

        return null; // Pas trouvé
    }

    /**
     * findAll - Récupère tous les corpus
     */
    public static function findAll($pdo): array
    {
        $sql = "SELECT * FROM corrections ORDER BY created_at DESC";
        return self::createFromStmt($pdo, $sql);
    }

    public static function findAllByImageId($pdo, int $image_id): array
    {
        $sql = "SELECT * FROM corrections WHERE image_id = :image_id ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':image_id', $image_id, \PDO::PARAM_INT);
        return self::createFromStmt($stmt, $sql);
    }

    /**
     * @param $stmt
     * @return void
     */
    public function extracted($stmt): void
    {
        $stmt->bindValue(':image_id', $this->image_id);
        $stmt->bindValue(':proposed_by', $this->proposed_by);
        $stmt->bindValue(':correction', $this->correction);
        $stmt->bindValue(':comment', $this->comment);
        $stmt->bindValue(':created_at', $this->created_at);
        $stmt->bindValue(':accepted', $this->accepted);
        $stmt->bindValue(':accepted_by', $this->accepted_by);
        $stmt->bindValue(':treated_at', $this->accepted_at);
    }
}