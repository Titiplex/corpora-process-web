<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'utilisateur est admin
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

if (!$isAdmin) {
    die("Access denied. Admins only.");
}

$pageTitle = "Manage Corpora - Philomathos";

// Simulation de données (remplacer avec des requêtes SQL)
$corpora = [
    ['id' => 1, 'title' => 'Old English Letters', 'language' => 'Old English', 'created_by' => 'Dr. Smith', 'date' => '2025-02-10'],
    ['id' => 2, 'title' => 'Latin Classical Documents', 'language' => 'Latin', 'created_by' => 'Professor Rivera', 'date' => '2025-02-08'],
    ['id' => 3, 'title' => 'French Renaissance Manuscripts', 'language' => 'French', 'created_by' => 'Ms. Bernard', 'date' => '2025-02-05'],
];

// Capturer le contenu HTML
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-6xl mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Manage Corpora</h2>

        <p class="text-gray-600 text-center mb-6">View, edit, or delete linguistic corpora.</p>

        <!-- Tableau des corpus -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border border-gray-200">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-600">Title</th>
                    <th class="px-4 py-2 text-left text-gray-600">Language</th>
                    <th class="px-4 py-2 text-left text-gray-600">Created By</th>
                    <th class="px-4 py-2 text-left text-gray-600">Date</th>
                    <th class="px-4 py-2 text-left text-gray-600">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($corpora as $corpus): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2 text-gray-800 font-medium">
                            <a href="view_corpus.php?id=<?php echo $corpus['id']; ?>" class="hover:underline">
                                <?php echo htmlspecialchars($corpus['title']); ?>
                            </a>
                        </td>
                        <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($corpus['language']); ?></td>
                        <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($corpus['created_by']); ?></td>
                        <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($corpus['date']); ?></td>
                        <td class="px-4 py-2">
                            <a href="edit_corpus.php?id=<?php echo $corpus['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                            <a href="delete_corpus.php?id=<?php echo $corpus['id']; ?>"
                               class="text-red-600 hover:underline"
                               onclick="return confirm('Are you sure you want to delete this corpus?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bouton d'ajout -->
        <div class="text-center mt-6">
            <a href="create_corpus.php" class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700">
                + New Corpus
            </a>
        </div>

    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
