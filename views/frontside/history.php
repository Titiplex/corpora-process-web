<?php

require_once __DIR__ . '/../../controllers/CorrectionController.php';

session_start();
if (!isset($_SESSION['user_id'])) header('Location: ../../index.php');

$corrections = CorrectionController::history($_GET['id']);

ob_start();
?>
<div class="max-w-4xl mx-auto mt-10 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Historique des corrections</h1>
    <table class="min-w-full bg-white">
        <thead>
        <tr>
            <th class="py-2">ID</th>
            <th class="py-2">Fichier</th>
            <th class="py-2">Proposé par</th>
            <th class="py-2">Correction</th>
            <th class="py-2">Statut</th>
            <th class="py-2">Comment</th>
            <th class="py-2">Date traitée</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($corrections)): ?>
            <?php foreach ($corrections as $correction): ?>
                <tr class="border-t">
                    <td class="py-2 text-center"><?php echo $correction->getId(); ?></td>
                    <td class="py-2 text-center"><?php echo $correction->getImageId(); ?></td>
                    <td class="py-2 text-center"><?php echo $correction->getProposedBy(); ?></td>
                    <td class="py-2"><?php echo htmlspecialchars($correction->getCorrection()); ?></td>
                    <td class="py-2 text-center">
                        <?php
                        if ($correction->isAccepted() === true) {
                            echo "Acceptée";
                        } elseif ($correction->isAccepted() === false && $correction->getAcceptedAt()) {
                            echo "Refusée";
                        } else {
                            echo "En attente";
                        }
                        ?>
                    </td>
                    <td class="py-2 text-center"><?php echo $correction->getComment() ?: '-'; ?></td>
                    <td class="py-2 text-center"><?php echo htmlspecialchars($correction->getAcceptedat())?: '-'; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="py-2 text-center">Aucune correction trouvée.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
include("../layout.php");
