<?php

class User
{
    public mixed $id {
        get {
            return $this->id;
        }
    }
    public mixed $username {
        get {
            return $this->username;
        }
        set {
            $this->username = $value;
        }
    }
    public mixed $email {
        get {
            return $this->email;
        }
        set {
            $this->email = $value;
        }
    }
    private mixed $role;           // e.g. 'admin', 'user'
    private mixed $passwordHash;   // stocke le mot de passe haché
    private mixed $createdAt;      // datetime

    /**
     * Constructeur
     */
    public function __construct(
        $username,
        $email,
        $role = 'user',
        $passwordHash = null,
        $id = null,
        $createdAt = null
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->role = $role;
        $this->passwordHash = $passwordHash;
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    // --------------------
    // Getters & Setters
    // --------------------

    public function getRole()
    {
        return $this->role;
    }
    public function setRole($role): void
    {
        $this->role = $role;
    }

    public function getPasswordHash()
    {
        return $this->passwordHash;
    }
    public function setPasswordHash($hash): void
    {
        $this->passwordHash = $hash;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    public function setCreatedAt($datetime): void
    {
        $this->createdAt = $datetime;
    }

    // --------------------
    // Méthodes CRUD
    // --------------------

    /**
     * Create - Insère un nouvel utilisateur en base
     */
    public function create($pdo): void
    {
        $sql = "INSERT INTO users (username, email, role, password, created_at)
                VALUES (:username, :email, :role, :password, :created_at)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':username', $this->username);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':role', $this->role);
        $stmt->bindValue(':password', $this->passwordHash);

        $this->createdAt = date('Y-m-d H:i:s'); // date/heure actuelle
        $stmt->bindValue(':created_at', $this->createdAt);

        $stmt->execute();

        // Récupérer l'ID généré
        $this->id = $pdo->lastInsertId();
    }

    /**
     * Update - Met à jour un utilisateur existant
     * @throws Exception
     */
    public function update($pdo): void
    {
        if (!$this->id) {
            throw new \Exception("Cannot update user without an ID.");
        }

        $sql = "UPDATE users
                SET username = :username,
                    email = :email,
                    role = :role,
                    password = :password
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':username', $this->username);
        $stmt->bindValue(':email', $this->email);
        $stmt->bindValue(':role', $this->role);
        $stmt->bindValue(':password', $this->passwordHash);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * Delete - Supprime un utilisateur de la base
     * @throws Exception
     */
    public function delete($pdo): void
    {
        if (!$this->id) {
            throw new \Exception("Cannot delete user without an ID.");
        }

        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $this->id, \PDO::PARAM_INT);
        $stmt->execute();
    }

    // --------------------
    // Méthodes statiques
    // --------------------

    /**
     * findById - Récupère un utilisateur par son ID
     */
    public static function findById($pdo, $id): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            return new User(
                $row['username'],
                $row['email'],
                $row['role'],
                $row['password'],
                $row['id'],
                $row['created_at']
            );
        }
        return null;
    }

    /**
     * findByEmail - Récupère un utilisateur par email
     */
    public static function findByEmail($pdo, $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            return new User(
                $row['username'],
                $row['email'],
                $row['role'],
                $row['password'],
                $row['id'],
                $row['created_at']
            );
        }
        return null;
    }

    /**
     * findAll - Récupère tous les utilisateurs
     */
    public static function findAll($pdo): array
    {
        $sql = "SELECT * FROM users ORDER BY created_at DESC";
        $stmt = $pdo->query($sql);

        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = new User(
                $row['username'],
                $row['email'],
                $row['role'],
                $row['password'],
                $row['id'],
                $row['created_at']
            );
        }
        return $results;
    }
}
