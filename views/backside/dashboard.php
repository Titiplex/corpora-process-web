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

$pageTitle = "Admin Dashboard - Philomathos";

// Simulations de données (remplacer par des requêtes SQL)
$totalUsers = 120;
$totalCorpora = 45;
$totalFiles = 320;
$recentActivities = [
    ['action' => 'New corpus created', 'details' => 'Old English Letters', 'date' => '2025-02-21'],
    ['action' => 'User registered', 'details' => 'JohnDoe', 'date' => '2025-02-20'],
    ['action' => 'File uploaded', 'details' => 'latin_text.pdf', 'date' => '2025-02-19'],
];

// Capturer le contenu HTML
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-6xl mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Admin Dashboard</h2>

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

        <!-- Liens rapides -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <a href="list_corpora.php" class="bg-blue-600 text-white p-4 rounded-lg shadow hover:bg-blue-700">
                📜 Manage Corpora
            </a>
            <a href="analytics.php" class="bg-green-600 text-white p-4 rounded-lg shadow hover:bg-green-700">
                📊 View Analytics
            </a>
            <a href="manage_users.php" class="bg-yellow-600 text-white p-4 rounded-lg shadow hover:bg-yellow-700">
                👤 Manage Users
            </a>
        </div>

        <!-- Activités récentes -->
        <div class="mt-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Activities</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto border border-gray-200">
                    <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-600">Action</th>
                        <th class="px-4 py-2 text-left text-gray-600">Details</th>
                        <th class="px-4 py-2 text-left text-gray-600">Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recentActivities as $activity): ?>
                        <tr class="border-b">
                            <td class="px-4 py-2 text-gray-800"><?php echo htmlspecialchars($activity['action']); ?></td>
                            <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($activity['details']); ?></td>
                            <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($activity['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
