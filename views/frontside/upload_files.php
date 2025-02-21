<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Upload Files - Philomathos";

$errors = [];
$successMessage = "";

// Simuler une liste de corpus existants (remplacer par une requête SQL)
$mockCorpora = [
    1 => "Old English Letters",
    2 => "Latin Classical Documents",
    3 => "French Renaissance Manuscripts"
];

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérifier que l’utilisateur a sélectionné un corpus
    $corpusId = $_POST['corpus'] ?? '';
    if (!isset($mockCorpora[$corpusId])) {
        $errors[] = "Please select a valid corpus.";
    }

    // Vérifier si un fichier a été uploadé
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = $_FILES['file']['type'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Extensions autorisées
        $allowedExtensions = ['txt', 'pdf', 'docx', 'csv'];
        if (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = "Invalid file format. Allowed formats: " . implode(", ", $allowedExtensions);
        }

        // Taille maximale : 5 Mo
        if ($fileSize > 5 * 1024 * 1024) {
            $errors[] = "File size exceeds the 5MB limit.";
        }

        // Si pas d'erreurs, sauvegarde du fichier
        if (empty($errors)) {
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Générer un nom unique pour éviter les conflits
            $newFileName = uniqid() . "-" . basename($fileName);
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destination)) {
                // TODO: Enregistrer dans la base de données
                // Exemple : $stmt = $db->prepare("INSERT INTO files (corpus_id, filename, uploaded_by) VALUES (?, ?, ?)");
                // $stmt->execute([$corpusId, $newFileName, $_SESSION['user_id']]);

                $successMessage = "File uploaded successfully!";
            } else {
                $errors[] = "Failed to move the uploaded file.";
            }
        }
    } else {
        $errors[] = "No file uploaded or an error occurred.";
    }
}

// Capturer le contenu HTML
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-lg mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Upload a File</h2>

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

        <!-- Formulaire d'upload -->
        <form action="upload_files.php" method="POST" enctype="multipart/form-data" class="space-y-4">

            <!-- Sélection du corpus -->
            <div>
                <label for="corpus" class="block text-gray-700 font-medium">Select Corpus</label>
                <select name="corpus" id="corpus" class="mt-1 block w-full border border-gray-300 rounded-md p-2" required>
                    <option value="">-- Choose a Corpus --</option>
                    <?php foreach ($mockCorpora as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Champ fichier -->
            <div>
                <label for="file" class="block text-gray-700 font-medium">Upload File</label>
                <input
                    type="file"
                    id="file"
                    name="file"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    required
                >
            </div>

            <div class="pt-4 text-center">
                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Upload File
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
