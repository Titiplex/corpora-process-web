<?php
/**
 * Config/database.php
 *
 * Centralise la connexion à la base de données via PDO.
 *
 * Usage :
 * require_once __DIR__ . '/config/database.php';
 * // ensuite $pdo est disponible pour exécuter des requêtes
 */

// Configuration de la base de données
$dbConfig = [
    'host'     => 'localhost',
    'dbname'   => 'cmi7',         // Remplace par le nom de ta base
    'user'     => 'root',         // Remplace si nécessaire
    'password' => '',             // Remplace si nécessaire
    'charset'  => 'utf8mb4'
];

// Création de la connexion PDO
try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);

    // Configuration des attributs PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Gère l'exception en cas d'erreur de connexion
    die("Database connection failed: " . $e->getMessage());
}
