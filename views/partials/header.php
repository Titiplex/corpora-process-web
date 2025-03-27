<nav class="bg-white shadow-md p-4">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <a href="/site_web_corpus/index.php" class="text-2xl font-bold text-gray-800">
            Philomathos
        </a>
        <div class="space-x-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/site_web_corpus/views/auth/logout.php" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Logout</a>
            <?php else: ?>
                <a href="/site_web_corpus/views/auth/login.php" class="text-gray-600 hover:text-gray-800">Login</a>
                <a href="/site_web_corpus/views/auth/signup.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
