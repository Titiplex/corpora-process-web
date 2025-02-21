<?php

// Exemple d'un contrôleur d'authentification
// Gère la logique de login, logout, register

require_once __DIR__ . '/../models/User.php'; // Le modèle User
require_once __DIR__ . '/../config/database.php'; // Suppose qu'on y déclare $pdo

class AuthController
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
     * Login - Gère la logique de connexion
     * @param array $postData : tableau $_POST contenant 'email' et 'password'
     */
    public function login(array $postData): array
    {
        $email = trim($postData['email'] ?? '');
        $password = $postData['password'] ?? '';

        // Validation basique
        if (empty($email) || empty($password)) {
            return ['error' => "Email and password are required."];
        }

        // On cherche l'utilisateur par email
        $user = User::findByEmail($this->pdo, $email);
        if (!$user) {
            return ['error' => "No account found for this email."];
        }

        // Vérifier le mot de passe
        if (!password_verify($password, $user->getPasswordHash())) {
            return ['error' => "Incorrect password."];
        }

        // Si OK, on stocke les informations de session
        session_start();
        $_SESSION['user_id'] = $user->id;
        $user->
        $_SESSION['username'] = $user->username;
        $_SESSION['user_role'] = $user->getRole();

        // Retourne success → redirection possible
        return ['success' => true];
    }

    /**
     * Logout - Gère la déconnexion
     */
    public function logout(): void
    {
        session_start();
        session_unset();
        session_destroy();

        // Pas de return particulier, on peut simplement rediriger ensuite
    }

    /**
     * Register - Gère la logique d'inscription
     * @param array $postData : tableau $_POST (username, email, password)
     */
    public function register(array $postData): array
    {
        $username = trim($postData['username'] ?? '');
        $email    = trim($postData['email'] ?? '');
        $password = $postData['password'] ?? '';

        // Validation simple
        $errors = [];
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }
        if (empty($password)) {
            $errors[] = "Password is required.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        }

        if (!empty($errors)) {
            return ['error' => $errors];
        }

        // Vérifier si l'email existe déjà
        $existingUser = User::findByEmail($this->pdo, $email);
        if ($existingUser) {
            return ['error' => ["Email is already taken."]];
        }

        // Création d'un nouvel utilisateur
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $newUser = new User($username, $email, 'user', $hashedPassword);
        $newUser->create($this->pdo);

        // Inscription réussie
        return ['success' => true];
    }

    /**
     * isLoggedIn - Vérifier si un utilisateur est connecté
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        session_start();
        return isset($_SESSION['user_id']);
    }

    /**
     * isAdmin - Vérifier si l'utilisateur connecté est admin
     * @return bool
     */
    public static function isAdmin(): bool
    {
        session_start();
        return (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
    }
}
