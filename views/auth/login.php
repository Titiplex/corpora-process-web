<?php
session_start();
require_once '../../models/User.php';
require_once '../../config/Db.php';

use \model\User;

$pageTitle = "Login - Philomathos";

$errors = [];
$successMessage = "";

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des champs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validations basiques
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $dbUser = User::findByEmail(Db::getConn(), $email);

        // Vérifier si l'utilisateur existe (email) et le mot de passe
        if ($dbUser && $email === $dbUser->getEmail()) {
            // Vérifier le mot de passe
            if (password_verify($password, $dbUser->getPasswordHash())) {
                // Connexion réussie → stocker en session
                $_SESSION['user_id'] = $dbUser->getId();
                $_SESSION['username'] = $dbUser->getUsername();

                // Optionnel: redirection
                header('Location: ../../index.php');
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found for this email.";
        }
    }
}

ob_start();
?>

    <div class="bg-white p-6 shadow-lg rounded-lg max-w-md mx-auto mt-12">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4 text-center">Log In</h2>

        <!-- Afficher erreurs -->
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
                <ul class="list-disc ml-6">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulaire de login -->
        <form action="login.php" method="POST" class="space-y-4">

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
                    Login
                </button>
            </div>
        </form>

        <!-- Lien vers signup -->
        <p class="text-gray-600 text-center mt-4">
            Don’t have an account?
            <a href="signup.php" class="text-blue-600 hover:underline">Sign Up</a>
        </p>
    </div>

<?php
$content = ob_get_clean();
include '../layout.php';
