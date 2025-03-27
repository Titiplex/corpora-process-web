<?php

require_once __DIR__.'/App.php';

// Définit la racine absolue du projet (ex: C:/xampp/htdocs/site_web_corpus)
define('BASE_PATH', realpath(__DIR__ . '/../'));

// URL de base pour les balises HTML (à adapter si tu es dans un sous-dossier)
const BASE_URL = '/site_web_corpus';

// Tu peux aussi inclure ici d'autres configs globales si besoin

putenv("MAGICK_GHOSTSCRIPT_PATH=C:\\Program Files\\gs\\gs10.04.0\\bin\\gswin64c.exe");

App::setLogger(new Logger(BASE_PATH . '/logs/app.log'));