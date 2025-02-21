<?php
session_start(); // Assurez-vous que la session est démarrée

// Set the page title
$pageTitle = "Browse Corpora - Philomathos";

// Example data (replace with actual DB query or function)
$corpora = [
    [
        'title' => 'Old English Letters',
        'description' => 'A collection of early medieval English texts.',
        'created_at' => '2024-05-10',
        'owner' => 'Dr. Brown'
    ],
    [
        'title' => 'Latin Classical Documents',
        'description' => 'Includes various classical Latin prose and poetry.',
        'created_at' => '2024-06-02',
        'owner' => 'Professor Rivera'
    ],
    [
        'title' => 'French Renaissance Manuscripts',
        'description' => '16th-century documents from the French Renaissance era.',
        'created_at' => '2025-01-15',
        'owner' => 'Ms. Bernard'
    ],
];

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
                            <a href="edit_corpus.php?title=<?php echo urlencode($corpus['title']); ?>" class="hover:underline">
                                <?php echo htmlspecialchars($corpus['title']); ?>
                            </a>
                        </td>
                        <td class="px-4 py-2 text-gray-700">
                            <?php echo htmlspecialchars($corpus['description']); ?>
                        </td>
                        <td class="px-4 py-2 text-gray-700">
                            <?php echo date('M j, Y', strtotime($corpus['created_at'])); ?>
                        </td>
                        <td class="px-4 py-2 text-gray-700">
                            <?php echo htmlspecialchars($corpus['owner']); ?>
                        </td>
                        <td class="px-4 py-2">
                            <a href="edit_corpus.php?title=<?php echo urlencode($corpus['title']); ?>"
                               class="text-blue-600 hover:underline mr-2">
                                Edit
                            </a>
                            <a href="delete_corpus.php?title=<?php echo urlencode($corpus['title']); ?>"
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
    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
