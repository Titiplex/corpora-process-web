<?php

require_once __DIR__."/Logger.php";
class App {
    private static $logger;

    public static function setLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    public static function getLogger(): Logger {
        return self::$logger;
    }

    static function loadEnv(): void
    {
        $filePath = dirname(__DIR__) . '/.env';
        if (!file_exists($filePath)) {
            die("Erreur : Fichier .env introuvable !");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // if (str_starts_with(trim($line), '#')) continue;

            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if (!array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
