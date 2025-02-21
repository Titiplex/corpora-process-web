<?php

require_once __DIR__ . '/../config/database.php';   // Suppose qu'on y déclare $pdo
require_once __DIR__ . '/../models/Corpus.php';     // Ex. pour la recherche
require_once __DIR__ . '/../models/User.php';       // Ex. pour la recherche

class DiversController
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
     * search - Exemple de fonction de recherche
     * @param string $query   Le terme saisi par l'utilisateur
     * @param string $scope   Le domaine (ex. 'all', 'corpus', 'user')
     */
    public function search(string $query, string $scope = 'all'): array
    {
        $results = [];

        // Normaliser la requête
        $query = trim($query);

        if (empty($query)) {
            return $results; // rien à chercher
        }

        // Recherche dans les corpus ?
        if ($scope === 'all' || $scope === 'corpus') {
            // Ex. : SELECT * FROM corpora WHERE title LIKE ...
            $sql = "SELECT * FROM corpora 
                    WHERE title LIKE :q OR description LIKE :q
                    ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':q', '%'.$query.'%');
            $stmt->execute();

            $corporaFound = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($corporaFound)) {
                $results['corpora'] = $corporaFound;
            }
        }

        // Recherche dans les utilisateurs ?
        if ($scope === 'all' || $scope === 'user') {
            $sql = "SELECT * FROM users 
                    WHERE username LIKE :q OR email LIKE :q
                    ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':q', '%'.$query.'%');
            $stmt->execute();

            $usersFound = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($usersFound)) {
                $results['users'] = $usersFound;
            }
        }

        return $results;
    }

    /**
     * handleContactForm - Exemple de traitement d'un formulaire de contact
     * @param array $formData  Contient name, email, message
     */
    public function handleContactForm(array $formData): array
    {
        $errors = [];
        $successMessage = "";

        // Validations
        $name = trim($formData['name'] ?? '');
        $email = trim($formData['email'] ?? '');
        $message = trim($formData['message'] ?? '');

        if (empty($name)) {
            $errors[] = "Name is required.";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email is required.";
        }
        if (empty($message)) {
            $errors[] = "Message cannot be empty.";
        }

        if (!empty($errors)) {
            return ['error' => $errors];
        }

        // Simulation : tu peux envoyer un email, ou stocker en base.
        // Ex. : stocker en base :
        // $stmt = $this->pdo->prepare("
        //     INSERT INTO contacts (name, email, message, created_at)
        //     VALUES (?, ?, ?, ?)
        // ");
        // $stmt->execute([$name, $email, $message, date('Y-m-d H:i:s')]);

        $successMessage = "Thank you for contacting us. We will reply soon!";
        return ['success' => $successMessage];
    }

    /**
     * Ex. dummyFunction - Une fonction "diverse"
     * Tu peux y mettre n'importe quelle logique supplémentaire
     */
    public function dummyFunction($someParam): string
    {
        // Faire un traitement…
        return "Dummy result for $someParam";
    }
}
