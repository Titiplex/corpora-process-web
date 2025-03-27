<?php
session_start();
$corpus_id = $_GET['id'];

require_once '../../models/Corpus.php';
require_once '../../models/File.php';
require_once '../../config/Db.php';
require_once __DIR__ . '/../../config/bootstrap.php';

if (!\model\Corpus::findById(Db::getConn(), $corpus_id) || !isset($_SESSION['user_id'])) {
    header('Location: /site_web_corpus/views/frontside/home.php');
}

require_once '../../controllers/ProcessController.php';
require_once '../../controllers/CorpusController.php';
require_once '../../controllers/CorrectionController.php';

$rawDir = BASE_PATH . "/images/raw/$corpus_id/";
$processedDir = BASE_PATH . "/images/processed/$corpus_id/";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $uploadedPaths = [];

    foreach ([$rawDir, $processedDir] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
    foreach ($_FILES['documents']['tmp_name'] as $key => $tmpFile) {
        $originalName = $_FILES['documents']['name'][$key];
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $newName = uniqid() . "." . $ext;
        $destination = $rawDir . $newName;

        if (move_uploaded_file($tmpFile, $destination)) {
            if (!file_exists($destination)) {
                error_log("Le fichier n'existe pas après move_uploaded_file : " . $destination);
            }
            $uploadedPaths[] = $destination;
        } else {
            echo "<p class='text-red-500'>Échec du transfert de $originalName</p>";
        }
    }

// Envoi au contrôleur
    try {
        $description = $_POST['description'];
        $processedData = ProcessController::handle($uploadedPaths, $corpus_id, $description);
        // var_dump($processedData);
        // echo json_encode($processedData, JSON_PRETTY_PRINT);
        foreach ($processedData as $data) {
            $result = (new CorpusController(Db::getConn()))->createFile($corpus_id, $data, $_SESSION['user_id']);
            if ($result->success) CorrectionController::propose($result->fileId, $data->text, "Initialisation");
        }
        // Ici, tu peux faire ce que tu veux avec $processedData (affichage, base de données, etc.)
        // header('Content-Type: application/json');
        //echo json_encode($processedData);
    } catch (ImagickException|Exception $e) {
        echo $e->getMessage();
    }
    // header('Location: /site_web_corpus/views/frontside/view_corpus.php?id=' . $corpus_id);
}

$corpusImages = \model\File::findByCorpus(Db::getConn(), $corpus_id);
$corpus = \model\Corpus::findById(Db::getConn(), $corpus_id);

ob_start();
?>

    <h1 class="text-3xl font-bold mb-6 text-center">Corpus : <?= htmlspecialchars($corpus->getTitle()) ?></h1>

    <div class="mt-10 text-center bg-white p-6 rounded shadow-md max-w-xl mx-auto">
        <h2 class="text-l font-bold">Description :</h2>
        <p><?php echo $corpus->getDescription();?></p>
    </div>

    <!-- Formulaire d'ajout -->

    <?php if (isset($_SESSION) && $corpus->getCreatedBy() == $_SESSION['user_id']): ?>
    <div class="mt-10">
        <form action="" method="POST" enctype="multipart/form-data"
              class="bg-white p-6 rounded shadow-md max-w-xl mx-auto">
            <input type="hidden" name="id_corpus" value="<?= htmlspecialchars($corpus_id) ?>">
            <div>
                <label for="description" class="block text-gray-700 font-medium">Description</label>
                <input
                        type="text"
                        id="description"
                        name="description"
                        class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                        required
                        placeholder="Accurate and precise description of the images"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ajouter des fichiers (PDF, JPG, PNG)</label>
                <input type="file" name="documents[]" multiple accept=".pdf,.jpg,.jpeg,.png"
                       class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full
                   file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                   hover:file:bg-blue-100 mb-4"/>
            </div>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                Ajouter au corpus
            </button>
        </form>
    </div>
    <?php endif; ?>

    <!-- Section affichage -->
    <!-- Conteneur global qui gère l’espacement vertical entre les paires -->
    <div class="space-y-4 mt-10">
        <?php foreach ($corpusImages as $raw) {
            $idImage = $raw->getId();
            $filename = basename($raw->getOriginalPath());
            $rawed = BASE_URL . "/images/raw/$corpus_id/$filename";
            $processed = BASE_URL . "/images/processed/$corpus_id/$filename";
            ?>

            <!-- Pour chaque itération, on crée une "ligne" de 2 colonnes -->
            <div class="grid grid-cols-1 md:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Colonne 1 : Image brute -->
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Image brute</h2>
                    <img src="<?= $rawed ?>" alt="Image brute" class="mb-4 max-w-full rounded border">
                </div>

                <!--  Colonne 2 : -->
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Texte Tiré</h2>
                    <p><?php echo $raw->getText(); ?></p>
                </div>

                <!-- Colonne 3 : Image traitée -->
                <div class="bg-white p-4 rounded shadow">
                    <h2 class="text-lg font-semibold mb-2">Image traitée</h2>
                    <img src="<?= $processed ?>" alt="Image traitée" class="max-w-full rounded border">
                </div>
                <div class="text-center mb-8">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                        <a href="history.php?id=<?php echo $idImage."&corpus=".$corpus_id ?>">History</a>
                    </button>
                </div>
                <div class="text-center mb-8">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                    <a href="propose_comment.php?id=<?php echo $idImage."&corpus=".$corpus_id ?>">Propose a modification</a>
                    </button>
                </div>
                <?php if (isset($_SESSION) && $corpus->getCreatedBy() == $_SESSION['user_id']): ?>
                <div class="text-center mb-8">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition">
                        <a href="view_comments.php?id=<?php echo $idImage."&corpus=".$corpus_id ?>">View suggestions</a>
                    </button>
                </div>
            <?php endif; ?>
            </div>

        <?php }?>
    </div>


<?php
$content = ob_get_clean();
include '../layout.php';