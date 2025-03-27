<?php

use model\User;

require_once __DIR__ . '/../models/User.php';      // Le modèle User
require_once __DIR__ . '/../config/Db.php';  // Suppose qu'on y déclare $pdo

class UserController
{
    private mixed $pdo;

    /**
     * Constructeur : injecte la connexion PDO
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Index - Récupère et renvoie tous les utilisateurs
     */
    public function index(): array
    {
        // Récupérer la liste complète des utilisateurs
        return User::findAll($this->pdo); // Tu peux retourner ça à ta vue
    }

    /**
     * Store - Crée un nouvel utilisateur en base
     * @param array $formData : $_POST contenant => username, email, password, role
     */
    public function store(array $formData): array
    {
        $errors = [];
        $username = trim($formData['username'] ?? '');
        $email = trim($formData['email'] ?? '');
        $password = $formData['password'] ?? '';
        $role = $formData['role'] ?? 'user';

        // Validations
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }
        if (empty($password) || strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }

        // Vérifier si l'email n’existe pas déjà
        $existingUser = User::findByEmail($this->pdo, $email);
        if ($existingUser) {
            $errors[] = "Email is already taken.";
        }

        if (!empty($errors)) {
            return ['errors' => $errors];
        }

        // Hacher le mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Créer un nouvel utilisateur
        $user = new User($username, $email, $role, $hashedPassword);
        $user->create($this->pdo);

        return ['success' => true, 'userId' => $user->getId()];
    }

    /**
     * Edit - Récupère un utilisateur par ID (pour l’afficher dans un formulaire)
     * @param int $id
     * @return User|null
     */
    public function edit(int $id): ?User
    {
        // On cherche l'utilisateur
        $user = User::findById($this->pdo, $id);
        if (!$user) {
            return null; // ou lancer une exception / renvoyer une erreur
        }
        return $user;
    }

    /**
     * Update - Met à jour un utilisateur existant
     * @param int $id
     * @param array $formData
     * @return array
     * @throws Exception
     */
    public function update(int $id, array $formData): array
    {
        $user = User::findById($this->pdo, $id);
        if (!$user) {
            return ['error' => "User not found."];
        }

        $errors = [];

        // Récupération des nouvelles valeurs
        $username = trim($formData['username'] ?? $user->getUsername());
        $email = trim($formData['email'] ?? $user->getEmail());
        $role = $formData['role'] ?? $user->getRole();

        // Optionnel : On ne force pas un nouveau password si ce n'est pas fourni
        $password = $formData['password'] ?? '';

        // Validations
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }
        // Vérifier si le mot de passe doit être re-haché
        $hashedPassword = $user->getPasswordHash();
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = "Password must be at least 6 characters long.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            }
        }

        if (!empty($errors)) {
            return ['error' => $errors];
        }

        // Vérifier si l'email n'est pas utilisé par un autre utilisateur
        $existing = User::findByEmail($this->pdo, $email);
        if ($existing && $existing->getId() !== $id) {
            $errors[] = "Email is already taken by another user.";
        }

        if (!empty($errors)) {
            return ['error' => $errors];
        }

        // Mettre à jour les champs dans l'objet
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRole($role);
        $user->setPasswordHash($hashedPassword);

        // Sauvegarde
        $user->update($this->pdo);

        return ['success' => true];
    }

    /**
     * Destroy - Supprime un utilisateur
     * @param int $id
     * @return array
     * @throws Exception
     */
    public function destroy(int $id): array
    {
        $user = User::findById($this->pdo, $id);
        if (!$user) {
            return ['error' => "User not found."];
        }

        // Ex : empêcher la suppression d'un admin ou du super-admin
        if ($user->getRole() === 'admin') {
            return ['error' => "Cannot delete an admin user."];
        }

        $user->delete($this->pdo);
        return ['success' => true];
    }
}
