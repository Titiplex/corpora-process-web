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

$pageTitle = "Settings - Philomathos";

$errors = [];
$successMessage = "";

// Simulation des paramètres existants (remplacer par une requête SQL)
$settings = [
    'site_name' => 'Linguistic Corpus Hub',
    'default_language' => 'English',
    'max_upload_size' => 5, // En Mo
];

// Liste des langues disponibles
$availableLanguages = ['English', 'French', 'Spanish', 'German'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des valeurs soumises
    $settings['site_name'] = trim($_POST['site_name'] ?? '');
    $settings['default_language'] = trim($_POST['default_language'] ?? '');
    $settings['max_upload_size'] = intval($_POST['max_upload_size'] ?? 5);

    // Validation des champs
    if (empty($settings['site_name'])) {
        $errors[] = "Site name is required.";
    }
    if (!in_array($settings['default_language'], $availableLanguages)) {
        $errors[] = "Invalid language selected.";
    }
    if ($settings['max_upload_size'] < 1 || $settings['max_upload_size'] > 100) {
        $errors[] = "Upload size must be between 1MB and 100MB.";
    }

    // Sauvegarde simulée
    if (empty($errors)) {
        // TODO: Sauvegarder en base de données
        // Exemple :
        // $stmt = $db->prepare("UPDATE settings SET site_name=?, default_language=?, max_upload_size=? WHERE id=1");
        // $stmt->execute([$settings['site_name'], $settings['default_language'], $settings['max_upload_size']]);

        $successMessage = "Settings updated successfully!";
    }
}

// Capturer le contenu HTML
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-lg mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Site Settings</h2>

        <!-- Affichage des erreurs -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc ml-6">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Message de succès -->
        <?php if (!empty($successMessage)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form action="settings.php" method="POST" class="space-y-4">

            <div>
                <label for="site_name" class="block text-gray-700 font-medium">Site Name</label>
                <input
                    type="text"
                    id="site_name"
                    name="site_name"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    value="<?php echo htmlspecialchars($settings['site_name']); ?>"
                    required
                >
            </div>

            <div>
                <label for="default_language" class="block text-gray-700 font-medium">Default Language</label>
                <select name="default_language" id="default_language" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                    <?php foreach ($availableLanguages as $language): ?>
                        <option value="<?php echo $language; ?>" <?php echo ($settings['default_language'] === $language) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($language); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="max_upload_size" class="block text-gray-700 font-medium">Max Upload Size (MB)</label>
                <input
                    type="number"
                    id="max_upload_size"
                    name="max_upload_size"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    min="1" max="100"
                    value="<?php echo htmlspecialchars($settings['max_upload_size']); ?>"
                    required
                >
            </div>

            <div class="pt-4 text-center">
                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Save Settings
                </button>
            </div>
        </form>

    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
