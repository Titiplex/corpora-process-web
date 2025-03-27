<?php
session_start(); // Assurez-vous que la session est démarrée
require_once "../../config/Db.php";
require_once "../../models/Corpus.php";
require_once "../../models/User.php";
require_once "../../controllers/CorpusController.php";

use \model\Corpus;
use \model\User;

// Set the page title
$pageTitle = "Browse Corpora - Philomathos";


$corpora = Corpus::findAll(Db::getConn());

// Start output buffering to capture the page’s unique content
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-gray-800">All Corpora</h2>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="create_corpus.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    + New Corpus
                </a>
            <?php endif; ?>
        </div>

        <p class="text-gray-600 mb-6">
            Browse through the available corpora or create a new one. Click a corpus to view or edit its contents.
        </p>

        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border border-gray-200">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-600">Title</th>
                    <th class="px-4 py-2 text-left text-gray-600">Description</th>
                    <th class="px-4 py-2 text-left text-gray-600">Created</th>
                    <th class="px-4 py-2 text-left text-gray-600">Owner</th>
                    <th class="px-4 py-2 text-left text-gray-600">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($corpora as $corpus): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2 text-gray-800 font-medium">
                            <a href="view_corpus.php?id=<?php echo urlencode($corpus->getId()); ?>"
                               class="hover:underline">
                                <?php echo htmlspecialchars($corpus->getTitle()); ?>
                            </a>
                        </td>
                        <td class="px-4 py-2 text-gray-700">
                            <?php echo htmlspecialchars($corpus->getDescription()); ?>
                        </td>
                        <td class="px-4 py-2 text-gray-700">
                            <?php echo date('M j, Y', strtotime($corpus->getCreatedAt())); ?>
                        </td>
                        <td class="px-4 py-2 text-gray-700">
                            <?php echo htmlspecialchars(User::findById(Db::getConn(), $corpus->getCreatedBy())->getUsername()); ?>
                        </td>
                        <td class="px-4 py-2">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $corpus->getCreatedBy()): ?>
                                <a href="edit_corpus.php?id=<?php echo urlencode($corpus->getId()); ?>"
                                   class="text-blue-600 hover:underline mr-2">
                                    Edit
                                </a>
                                <a href="delete_corpus.php?id=<?php echo urlencode($corpus->getId()); ?>"
                                   class="text-red-600 hover:underline"
                                   onclick="return confirm('Are you sure you want to delete this corpus?');">
                                    Delete
                                </a>
                            <?php endif; ?>
                            <a href="view_corpus.php?id=<?php echo urlencode($corpus->getId()); ?>"
                               class="text-blue-600 hover:underline mr-2">
                                View
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
