<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../controllers/ProcessController.php';

$lockFile = BASE_PATH . '/queue/worker.lock';
$queueFile = BASE_PATH . '/queue/queue.json';

App::getLogger()->debug("Dans worker.php");

// 1. Vérifier si un lock existe déjà
if (file_exists($lockFile)) {
    // On quitte : un worker est déjà en cours
    exit("Un worker tourne déjà.\n");
}

// 2. Créer le lock
file_put_contents($lockFile, getmypid()); // on écrit le PID du process

App::getLogger()->info(BASE_PATH.'/queue/trace.log : ' . "Worker démarré");
App::loadEnv();

try {
    // 3. Boucle tant qu'il y a des tâches
    while (true) {
        if (!file_exists($queueFile)) {
            App::getLogger()->info("Aucune queue.json - on sort");
            break;
        }

        $tasks = json_decode(file_get_contents($queueFile), true) ?: [];
        App::getLogger()->debug("Tâches à traiter : " . json_encode($tasks));

        if (empty($tasks)) {
            // Si la queue est vide, on sort
            App::getLogger()->info("File d'attente vide, on sort.");
            break;
        }

        // On traite
        $newTasks = ProcessController::workerJob($tasks);

        // Mise à jour de la file d’attente
        file_put_contents($queueFile, json_encode($newTasks));

        // Si on veut boucler tant qu'il y a des tâches, on peut re-vérifier
        // ou faire un sleep(1) pour éviter de monopoliser le CPU
        if (empty($newTasks)) {
            // plus de tâches en attente
            App::getLogger()->info("Toutes les tâches ont été traitées, on sort.");
            break;
        }

        // Sinon, on peut faire un petit sleep pour éviter de tourner trop vite
        sleep(1);
    }
} finally {
    // 4. Supprimer le lock
    unlink($lockFile);
    App::getLogger()->info("Worker terminé.\n");
}
