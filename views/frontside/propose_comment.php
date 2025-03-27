<?php

use model\File;

require_once __DIR__."/../../controllers/CorrectionController.php";
require_once __DIR__."/../../models/File.php";
require_once __DIR__."/../../config/Db.php";

require_once __DIR__ . '/../../config/bootstrap.php';

session_start();
if (!isset($_SESSION['user_id'])) header('Location: ../../index.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "in";
    $fileId = $_POST['file_id'];
    $comment = $_POST['comment'];
    $correction = $_POST['correction'];
    echo "else";
    CorrectionController::propose($fileId, $correction, $comment);
    echo "sait on jamais\n";
}

$file = File::selectById(Db::getConn(), $_GET['id']);
$idImage = $file->getId();
$filename = basename($file->getOriginalPath());
$rawed = BASE_URL . "/images/raw/{$_GET['corpus']}/$filename";

ob_start();
?>
<div class="max-w-lg mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Proposer une correction</h1>
    <img src="<?php echo $rawed; ?>" alt="">
    <form action="" method="post">
        <input type="hidden" name="file_id" value="<?php echo $_GET['id'] ?>" required>
        <div class="mb-4">
            <label class="block text-gray-700">Correction
                <textarea name="correction" class="w-full px-3 py-2 border rounded" required><?php echo $file->getText(); ?></textarea>
            </label>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700">Commentaire
                <textarea name="comment" class="w-full px-3 py-2 border rounded"></textarea>
            </label>
        </div>
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Envoyer</button>
    </form>
</div>

<?php
$content = ob_get_clean();
include '../layout.php';
