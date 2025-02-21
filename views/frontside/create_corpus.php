<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Create a New Corpus - Philomathos";

$errors = [];
$successMessage = "";

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des champs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $language = trim($_POST['language'] ?? '');

    // Validation des champs
    if (empty($title)) {
        $errors[] = "Corpus title is required.";
    }
    if (empty($language)) {
        $errors[] = "Please specify a language.";
    }
    if (empty($description)) {
        $errors[] = "A description is required.";
    }

    // Si pas d'erreurs, insertion (simulation)
    if (empty($errors)) {
        // TODO: Insérer dans la base de données
        // Exemple:
        // $stmt = $db->prepare("INSERT INTO corpora (title, description, language, created_by) VALUES (?, ?, ?, ?)");
        // $stmt->execute([$title, $description, $language, $_SESSION['user_id']]);

        $successMessage = "Corpus created successfully!";
    }
}

// Capturer le contenu HTML
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-lg mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Create a New Corpus</h2>

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
        <form action="create_corpus.php" method="POST" class="space-y-4">

            <div>
                <label for="title" class="block text-gray-700 font-medium">Corpus Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    value="<?php echo htmlspecialchars($title ?? ''); ?>"
                    required
                >
            </div>

            <div>
                <label for="language" class="block text-gray-700 font-medium">Language</label>
                <input
                    type="text"
                    id="language"
                    name="language"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    value="<?php echo htmlspecialchars($language ?? ''); ?>"
                    required
                >
            </div>

            <div>
                <label for="description" class="block text-gray-700 font-medium">Description</label>
                <textarea
                    id="description"
                    name="description"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    rows="4"
                    required
                ><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>

            <div class="pt-4 text-center">
                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Create Corpus
                </button>
            </div>
        </form>

        <!-- Lien retour -->
        <p class="text-gray-600 text-center mt-4">
            <a href="list_corpora.php" class="text-blue-600 hover:underline">Back to Corpus List</a>
        </p>

    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
