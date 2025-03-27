<?php
require_once __DIR__ . '/../../controllers/CorrectionController.php';
require_once __DIR__ . '/../../models/Corpus.php';
require_once __DIR__ . '/../../config/Db.php';

use \model\Corpus;
session_start();
if (!isset($_SESSION['user_id']) || Corpus::findById(Db::getConn(), $_GET['corpus'])->getCreatedBy() != $_SESSION['user_id'])
        header('Location: ../../index.php');

$pendingCorrections = CorrectionController::managePending($_GET['id']);

ob_start();
?>
  <div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Gérer les corrections en attente</h1>
    <table class="min-w-full bg-white">
      <thead>
        <tr>
          <th class="py-2">ID</th>
          <th class="py-2">Fichier</th>
          <th class="py-2">Proposé par</th>
          <th class="py-2">Correction</th>
          <th class="py-2">Commentaire</th>
          <th class="py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($pendingCorrections)): ?>
          <?php foreach ($pendingCorrections as $correction): ?>
            <?php if (!$correction->isAccepted()): ?>
            <tr class="border-t">
              <td class="py-2 text-center"><?php echo $correction->getId(); ?></td>
              <td class="py-2 text-center"><?php echo $correction->getImageId(); ?></td>
              <td class="py-2 text-center"><?php echo $correction->getProposedBy(); ?></td>
              <td class="py-2"><?php echo htmlspecialchars($correction->getCorrection()); ?></td>
              <td class="py-2"><?php echo htmlspecialchars($correction->getComment()); ?></td>
              <td class="py-2 text-center">
                <a href="../corrections/accept.php?id=<?php echo $correction->getId()."&corpus=".$_GET['corpus']; ?>" class="bg-green-500 text-white px-2 py-1 rounded">Accepter</a>
                <a href="../corrections/refuse.php?id=<?php echo $correction->getId()."&corpus=".$_GET['corpus']; ?>" class="bg-red-500 text-white px-2 py-1 rounded">Refuser</a>
              </td>
            </tr>
        <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="py-2 text-center">Aucune correction en attente.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php
$content = ob_get_clean();
include("../layout.php");