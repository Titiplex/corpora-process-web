<?php
session_start();

$pageTitle = "Sign Up - Philomathos";

// Si le formulaire est soumis
$errors = [];
$successMessage = "";

// On vérifie si le formulaire a été posté
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des champs
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validations basiques
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // Si pas d'erreurs, on simule l'enregistrement
    if (empty($errors)) {
        // TODO : Insert dans ta base de données
        // e.g. $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        // $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);

        $successMessage = "Your account has been created successfully! You can now log in.";
    }
}

// ------------------------
// On stocke le HTML dans un buffer
ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-md mx-auto mt-12">

        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Create an Account</h2>

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

        <!-- Affichage du message de succès -->
        <?php if (!empty($successMessage)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form action="signup.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-gray-700 font-medium">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    value="<?php echo htmlspecialchars($username ?? ''); ?>"
                    required
                >
            </div>

            <div>
                <label for="email" class="block text-gray-700 font-medium">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    required
                >
            </div>

            <div>
                <label for="password" class="block text-gray-700 font-medium">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="mt-1 block w-full border border-gray-300 rounded-md p-2"
                    required
                >
            </div>

            <div class="pt-4 text-center">
                <button
                    type="submit"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Sign Up
                </button>
            </div>
        </form>

        <!-- Lien vers page de login -->
        <p class="text-gray-600 text-center mt-4">
            Already have an account?
            <a href="login.php" class="text-blue-600 hover:underline">Log In</a>
        </p>
    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
