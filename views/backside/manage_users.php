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

$pageTitle = "Manage Users - Philomathos";

// Simulation de données (remplacer avec des requêtes SQL)
$users = [
    ['id' => 1, 'username' => 'AdminUser', 'email' => 'admin@example.com', 'role' => 'admin', 'joined' => '2025-02-01'],
    ['id' => 2, 'username' => 'JohnDoe', 'email' => 'john@example.com', 'role' => 'user', 'joined' => '2025-02-10'],
    ['id' => 3, 'username' => 'JaneSmith', 'email' => 'jane@example.com', 'role' => 'user', 'joined' => '2025-02-15'],
];

// Capturer le contenu HTML
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-6xl mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Manage Users</h2>

        <p class="text-gray-600 text-center mb-6">Modify user roles or remove accounts.</p>

        <!-- Tableau des utilisateurs -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border border-gray-200">
                <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-gray-600">Username</th>
                    <th class="px-4 py-2 text-left text-gray-600">Email</th>
                    <th class="px-4 py-2 text-left text-gray-600">Role</th>
                    <th class="px-4 py-2 text-left text-gray-600">Joined</th>
                    <th class="px-4 py-2 text-left text-gray-600">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="border-b">
                        <td class="px-4 py-2 text-gray-800 font-medium"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($user['joined']); ?></td>
                        <td class="px-4 py-2">
                            <?php if ($user['role'] !== 'admin'): ?> <!-- Empêcher la suppression d'admins -->
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-600 hover:underline mr-2">Edit</a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>"
                                   class="text-red-600 hover:underline"
                                   onclick="return confirm('Are you sure you want to delete this user?');">
                                    Delete
                                </a>
                            <?php else: ?>
                                <span class="text-gray-400">Admin</span>
                            <?php endif; ?>
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
