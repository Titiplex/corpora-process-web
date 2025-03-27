<?php

require_once __DIR__. "/App.php";

/**
 * Config/Db.php
 *
 * Centralise la connexion à la base de données via PDO.
 *
 * Usage :
 * require_once __DIR__ . '/config/Db.php' ;
 */
class Db
{
    private static ?PDO $conn = null;

    /**
     * @return ?PDO
     */
    public static function getConn(): ?PDO
    {
        // Création de la connexion PDO
        if (self::$conn == null) {
            try {
                App::loadEnv();
                $dbConfig = [
                    'host' => getenv("DB_HOST") ?: die("Error : DB_HOST undefined in .env"),
                    'dbname' => getenv("DB_NAME") ?: die("Error : DB_NAME undefined in .env"),
                    'user' => getenv("DB_USER") ?: die("Error : DB_USER undefined in .env"),
                    'password' => '',
                    'charset' => getenv("DB_CHARSET") ?: 'utf8',
                ];
                $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
                $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password']);

                // Configuration des attributs PDO
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                self::$conn = $pdo;
            } catch (PDOException $e) {
                // Gère l'exception en cas d'erreur de connexion
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}
