<?php

namespace model;

use PDO;

require_once __DIR__ . "/Model.php";
require_once __DIR__ . "/../config/App.php";

class File extends Model
{
    private int $corpusId;
    private string $originalPath;
    private string $processedPath;
    private string $text;
    private mixed $uploadedBy;
    private mixed $createdAt;

    /**
     * @param int $id
     * @param int $corpusId
     * @param string $originalPath
     * @param string $processedPath
     * @param string $text
     * @param mixed $uploadedBy
     * @param mixed $createdAt
     */
    public function __construct(int $id, int $corpusId, string $originalPath, string $processedPath, string $text, mixed $uploadedBy, mixed $createdAt)
    {
        parent::__construct($id);
        $this->corpusId = $corpusId;
        $this->originalPath = $originalPath;
        $this->processedPath = $processedPath;
        $this->text = $text;
        $this->uploadedBy = $uploadedBy;
        $this->createdAt = $createdAt;
    }

    public static function selectById($pdo, int $id) : File
    {
        $sql = "SELECT * FROM files WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return new File(
            $row['id'],
            $row['corpus_id'],
            $row['original'],
            $row['processed'],
            $row['text'],
            $row['uploaded_by'],
            $row['created_at']
        );
    }

    /**
     * @param PDO $pdo
     * @return void
     */
    public function create(PDO $pdo): void
    {
        $sql = "INSERT INTO files (corpus_id, original, processed, text, uploaded_by, created_at)
VALUES (:cid, :or, :pro, :txt, :uby, :ca)";
        $stmt = $pdo->prepare($sql);
        $this->createdAt = date('Y-m-d H:i:s');
        $stmt->execute([
            ':cid' => $this->corpusId,
            ':or' => $this->originalPath,
            ':pro' => $this->processedPath,
            ':txt' => $this->text,
            ':uby' => $this->uploadedBy,
            ':ca' => $this->createdAt
        ]);
        $this->id = $pdo->lastInsertId();
    }

    /**
     * @throws \Exception
     */
    public function update($pdo): bool
    {
        if (!$this->id) {
            throw new \Exception("Cannot update corpus without ID.");
        }

        $sql = "UPDATE files
                SET corpus_id = :cid, original = :or, processed = :pro, text = :txt, uploaded_by = :uby, created_at = :ca
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        try {
            $pdo->beginTransaction();
            $stmt->execute([
                ':cid' => $this->corpusId,
                ':or' => $this->originalPath,
                ':pro' => $this->processedPath,
                ':txt' => $this->text,
                ':uby' => $this->uploadedBy,
                ':ca' => $this->createdAt,
                'id' => $this->id
            ]);
            $pdo->commit();
            return true;
        } catch (\Exception $e) {
            $pdo->rollBack();
            \App::getLogger()->error($e->getMessage());
            echo $e->getMessage();
        }
        return false;
    }

    /**
     * @param PDO $pdo
     * @param int $corpusId
     * @return array
     */
    public static function findByCorpus(PDO $pdo, int $corpusId): array
    {
        $sql = "SELECT * FROM files WHERE corpus_id = :cid ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':cid' => $corpusId]);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new File(
                $row['id'],
                $row['corpus_id'],
                $row['original'],
                $row['processed'],
                $row['text'],
                $row['uploaded_by'],
                $row['created_at']
            );
        }
        return $results;
    }

    public function getCorpusId(): int
    {
        return $this->corpusId;
    }

    public function setCorpusId(int $corpusId): void
    {
        $this->corpusId = $corpusId;
    }

    public function getOriginalPath(): string
    {
        return $this->originalPath;
    }

    public function setOriginalPath(string $originalPath): void
    {
        $this->originalPath = $originalPath;
    }

    public function getProcessedPath(): string
    {
        return $this->processedPath;
    }

    public function setProcessedPath(string $processedPath): void
    {
        $this->processedPath = $processedPath;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getUploadedBy(): mixed
    {
        return $this->uploadedBy;
    }

    public function setUploadedBy(mixed $uploadedBy): void
    {
        $this->uploadedBy = $uploadedBy;
    }

    public function getCreatedAt(): mixed
    {
        return $this->createdAt;
    }

    public function setCreatedAt(mixed $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
