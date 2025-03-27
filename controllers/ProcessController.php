<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/App.php';

class ProcessController
{
    /**
     * Traitement de tous les fichiers (images ou PDF) et conversion asynchrone pour les PDF volumineux.
     *
     * @param array $filePaths
     * @param string $idCorpus
     * @param string $description
     * @return array
     * @throws ImagickException
     * @throws Exception
     */
    public static function handle(array $filePaths, string $idCorpus, string $description): array
    {
        $rawDir = BASE_PATH."/images/raw/$idCorpus/";
        $processedDir = BASE_PATH."/images/processed/$idCorpus/";

        foreach ([$rawDir, $processedDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        $allImages = [];

        foreach ($filePaths as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if ($ext === 'pdf') {
                // Traitement asynchrone si le PDF est volumineux
                $pages = self::getPdfPageCount($file);
                App::getLogger()->info("Nb pages: $pages");
                if ($pages > 10) {
                    // Enregistrer chaque page comme tâche asynchrone
                    for ($i = 0; $i < $pages; $i++) {
                        self::enqueuePdfPageTask($file, $idCorpus, $i, $description);
                    }
                    self::maybeLaunchWorker();
                } else {
                    // Si le PDF a peu de pages, on le traite immédiatement
                    $images = self::convertPdfToImages($file);
                    $allImages = array_merge($allImages, $images);
                }
            } else {
                // Convertir en JPG localement si nécessaire
                $fileJpg = self::ensureJpg($file);
                $allImages[] = $fileJpg;
            }
        }

        // Traitement de toutes les images
        $results = [];
        foreach ($allImages as $rawImage) {
            $response = self::processImage($rawImage, $description);
            var_dump($response);
            if (isset($response['processedImage'])) {
                // On enlève le préfixe éventuel (ex : "data:image/png;base64,")
                $processedImageBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $response['processedImage']);
                // echo '<img src="'. $response['processedImage']. '" alt ="">';
                //var_dump($processedImageData);
                // $filename = basename($rawImage);
                $filename = pathinfo($rawImage, PATHINFO_FILENAME) . '.jpg';
                $processedPath = $processedDir . $filename;
                // file_put_contents($processedPath, $processedImageData);

                // Sauvegarde en JPG
                self::saveProcessedAsJpg($processedImageBase64, $processedPath);

                $results[] = [
                    'raw' => $rawImage,
                    'processed' => $processedPath,
                    'text' => $response['text'] ?? null,
                    'error' => UPLOAD_ERR_OK
                ];
            }
        }

        return $results;
    }

    /**
     *  Convertit un PDF en images.
     *  Si le PDF a plus de 10 pages, ce traitement est délégué à un système de file d'attente.
     *
     * @param string $pdfPath
     * @return array
     * @throws ImagickException
     */
    private static function convertPdfToImages(string $pdfPath): array
    {
        $imagick = new Imagick();
        $imagick->setResolution(200, 200);
        $imagick->readImage($pdfPath);

        $pageCount = $imagick->getNumberImages();
        // Si le PDF a plus de 10 pages, on ne traite pas ici
        if ($pageCount > 10) {
            // On peut choisir de retourner un message ou simplement un tableau vide
            return [];
        }

        $outputImages = [];
        foreach ($imagick as $i => $page) {
            $page->setImageFormat('jpg');
            $filename = pathinfo($pdfPath, PATHINFO_FILENAME) . "_page_$i.jpg";
            $fullPath = dirname($pdfPath) . '/' . $filename;
            $page->writeImage($fullPath);
            $outputImages[] = $fullPath;
        }

        return $outputImages;
    }

    /**
     * @param string $imagePath
     * @param $description
     * @return array
     * @throws Exception
     */
    private static function processImage(string $imagePath, $description): array
    {
        $endpoint = getenv("ENDPOINT_URL") ?: die("Error : ENDPOINT URL not defined");
        echo $endpoint;
        $cfile = curl_file_create($imagePath, mime_content_type($imagePath), basename($imagePath));

        $postFields = [
            'image' => $cfile,
            'details' => $description,
            'returnProcessedImage' => 'true',
            'skipMistral' => 'false'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Erreur cURL : ' . curl_error($ch));
        }

        curl_close($ch);

        App::getLogger()->task("Processed image \{$imagePath\} with description \{$description\}");

        return json_decode($response, true);
    }

    /**
     * Enfile une tâche pour le traitement asynchrone d'une page de PDF.
     * La tâche est ajoutée dans un fichier JSON dans le dossier "queue".
     *
     * @param string $pdfPath
     * @param string $idCorpus
     * @param int $pageNumber
     * @param string $description
     */
    private static function enqueuePdfPageTask(string $pdfPath, string $idCorpus, int $pageNumber, string $description): void
    {
        $queueDir = BASE_PATH . "/queue/";
        if (!is_dir($queueDir)) {
            mkdir($queueDir, 0777, true);
        }
        $task = [
            'pdfPath'     => $pdfPath,
            'idCorpus'    => $idCorpus,
            'pageNumber'  => $pageNumber,
            'description' => $description,
            'timestamp'   => time()
        ];
        $queueFile = $queueDir . "queue.json";
        $tasks = [];
        if (file_exists($queueFile)) {
            $content = file_get_contents($queueFile);
            $tasks = json_decode($content, true) ?: [];
        }
        $tasks[] = $task;
        file_put_contents($queueFile, json_encode($tasks));
    }

    /**
     * Méthode utilitaire pour obtenir le nombre de pages d'un PDF.
     *
     * @param string $pdfPath
     * @return int
     * @throws ImagickException
     */
    private static function getPdfPageCount(string $pdfPath): int
    {
        $imagick = new Imagick();
        $imagick->pingImage($pdfPath); // pingImage est plus léger que readImage
        return $imagick->getNumberImages();
    }

    /**
     * @param mixed $tasks
     * @param array $newTasks
     * @return array
     */
    public static function workerJob(array $tasks, array $newTasks = []): array
    {
        foreach ($tasks as $task) {
            // Construction des dossiers à partir du corpus de la tâche
            $rawDir = BASE_PATH . "/images/raw/" . $task['idCorpus'] . "/";
            $processedDir = BASE_PATH . "/images/processed/" . $task['idCorpus'] . "/";

            if (!is_dir($rawDir)) {
                mkdir($rawDir, 0777, true);
            }
            if (!is_dir($processedDir)) {
                mkdir($processedDir, 0777, true);
            }

            App::getLogger()->task("Traitement de la page " . $task['pageNumber'] . " du PDF " . basename($task['pdfPath']) . "\n");

            try {
                // Ouvre le PDF et lit uniquement la page demandée
                $pdf = new Imagick();
                $pdf->setResolution(200, 200);
                // Lire uniquement la page spécifique (en utilisant la syntaxe "[pageNumber]")
                $pdf->readImage($task['pdfPath'] . "[" . $task['pageNumber'] . "]");
                $pdf->setImageFormat('jpg');

                // Construire le nom de fichier pour la page convertie
                $filename = pathinfo($task['pdfPath'], PATHINFO_FILENAME) . "_page_" . $task['pageNumber'] . ".jpg";
                $rawImagePath = $rawDir . $filename;
                $pdf->writeImage($rawImagePath);
                $pdf->clear();

                // S'assurer que le fichier est bien en JPEG (la fonction convertit si nécessaire et supprime l'original)
                $rawImagePath = self::ensureJpg($rawImagePath);

                // Envoi de l'image à l'API de traitement
                $response = self::processImage($rawImagePath, $task['description']);

                if (isset($response['processedImage'])) {
                    // Retirer le préfixe éventuel (ex : "data:image/png;base64,")
                    $processedImageBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $response['processedImage']);
                    // Définir le chemin de sauvegarde dans le dossier processed, en gardant le même nom
                    $processedPath = $processedDir . $filename;

                    // Sauvegarde en JPG (la fonction interne décodera la chaîne base64 et enregistrera en JPEG)
                    self::saveProcessedAsJpg($processedImageBase64, $processedPath);

                    App::getLogger()->task("Page traitée et enregistrée: $processedPath \n");
                } else {
                    App::getLogger()->task("Aucune image traitée pour la page " . $task['pageNumber'] . "\n");
                    $newTasks[] = $task;
                }
            } catch (Exception $e) {
                App::getLogger()->logError(1, "Erreur lors du traitement de la page " . $task['pageNumber'] . " : " . $e->getMessage() . "\n");
                $newTasks[] = $task; // Réinsérer la tâche pour réessayer plus tard
            }
        }
        return $newTasks;
    }

    /**
     * @throws ImagickException
     */
    private static function ensureJpg(string $imagePath): string
    {
        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        // Si c'est déjà du .jpg, on ne fait rien
        if ($ext == 'jpg') {
            return $imagePath;
        }

        // Conversion en JPG via Imagick
        $img = new Imagick($imagePath);
        // On enlève le canal alpha si besoin
        $img->setImageBackgroundColor(new ImagickPixel('white'));
        $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $img->setImageFormat('jpg');

        // On construit un nouveau chemin .jpg
        $jpgPath = pathinfo($imagePath, PATHINFO_DIRNAME)
            . '/' . pathinfo($imagePath, PATHINFO_FILENAME)
            . '.jpg';

        $img->writeImage($jpgPath);
        $img->clear();

        // Suppression du fichier original
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        return $jpgPath;
    }

    /**
     * @throws ImagickException
     */
    private static function saveProcessedAsJpg(string $base64, string $destination): void
    {
        $blob = base64_decode($base64);

        $img = new Imagick();
        $img->readImageBlob($blob);
        // On enlève le canal alpha si présent
        $img->setImageBackgroundColor(new ImagickPixel('white'));
        $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        $img->setImageFormat('jpg');

        $img->writeImage($destination);

        $img->clear();
    }

    private static function maybeLaunchWorker(): void
    {
        $workerLock = BASE_PATH . '/queue/worker.lock';
        if (file_exists($workerLock)) {
            // Un worker est déjà en cours
            return;
        }

        // On lance le worker en tâche de fond
        $workerPhp = BASE_PATH . '/queue/worker.php';

        // Sous Windows + XAMPP, on peut faire :
        // start /B php "C:\xampp\htdocs\site_web_corpus\queue\worker.php"
        // Sous Linux/mac, on ferait un & pour le background
        // ex: shell_exec('php /var/www/site_web_corpus/queue/worker.php > /dev/null 2>&1 &');
        // Adapte selon ton OS
        $cmd = 'start /B C:\xampp\php\php.exe -c "C:\xampp\php\php.ini "' . $workerPhp . '"';
        $output = shell_exec($cmd . ' 2>&1');
        App::getLogger()->info("Lancement worker : " . $cmd);
        App::getLogger()->info("Résultat shell_exec : " . $output);
    }
}
