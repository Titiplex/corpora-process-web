<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'utilisateur est admin (TODO : Modifier en fonction de la BDD)
$isAdmin = ($_SESSION['user_role'] ?? '') === 'admin';

if (!$isAdmin) {
    die("Access denied. Admins only.");
}

$pageTitle = "Analytics Dashboard - Philomathos";

// Simulations de données (remplacer par des requêtes SQL)
$totalUsers = 120;
$totalCorpora = 45;
$totalFiles = 320;
$recentUploads = [
    ['filename' => 'old_english.txt', 'corpus' => 'Old English Letters', 'uploaded_by' => 'User1', 'date' => '2025-02-20'],
    ['filename' => 'latin_prose.pdf', 'corpus' => 'Latin Classical Documents', 'uploaded_by' => 'User2', 'date' => '2025-02-19'],
    ['filename' => 'renaissance_manuscript.docx', 'corpus' => 'French Renaissance Manuscripts', 'uploaded_by' => 'User3', 'date' => '2025-02-18'],
];

// Capturer le contenu HTML
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-6xl mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Analytics Dashboard</h2>

        <!-- Statistiques clés -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="bg-blue-100 p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Total Users</h3>
                <p class="text-3xl font-bold"><?php echo $totalUsers; ?></p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Total Corpora</h3>
                <p class="text-3xl font-bold"><?php echo $totalCorpora; ?></p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg shadow">
                <h3 class="text-lg font-semibold">Total Files</h3>
                <p class="text-3xl font-bold"><?php echo $totalFiles; ?></p>
            </div>
        </div>

        <!-- Graphique avec Chart.js -->
        <div class="mt-8">
            <canvas id="corpusChart"></canvas>
        </div>

        <!-- Derniers fichiers uploadés -->
        <div class="mt-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Uploads</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border border-gray-200">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">File</th>
                        <th class="px-4 py-2 text-left text-gray-600">Corpus</th>
                        <th class="px-4 py-2 text-left text-gray-600">Uploaded By</th>
                        <th class="px-4 py-2 text-left text-gray-600">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentUploads as $upload): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2 text-gray-800"><?php echo htmlspecialchars($upload['filename']); ?></td>
                            <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($upload['corpus']); ?></td>
                            <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($upload['uploaded_by']); ?></td>
                            <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($upload['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Script pour afficher un graphique avec Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('corpusChart').getContext('2d');
        const corpusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Old English Letters', 'Latin Classical Documents', 'French Renaissance Manuscripts'],
                datasets: [{
                    label: 'Number of Files',
                    data: [12, 18, 20], // Simulated data (remplacer avec une requête SQL)
                    backgroundColor: ['#3B82F6', '#10B981', '#FBBF24'],
                }]
            }
        });
    </script>

<?php
$content = ob_get_clean();
include '../layout.php';
