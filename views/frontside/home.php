<?php
// home.php
session_start();
$pageTitle = "Home - Philomathos";

// Crée du contenu HTML (ou includes, etc.)
ob_start();  // on démarre un buffer
?>
    <!-- Hero Section -->
    <section class="max-w-6xl mx-auto my-12 p-6 bg-white shadow-lg rounded-lg text-center">
        <h2 class="text-3xl font-semibold text-gray-800">Explore & Manage Linguistic Corpora</h2>
        <p class="text-gray-600 mt-2">
            Upload, edit, and analyze linguistic corpora in a collaborative environment. Built for linguists, by linguists.
        </p>
        <div class="mt-6">
            <a href="list_corpora.php"
               class="bg-blue-600 text-white px-6 py-3 rounded-md text-lg hover:bg-blue-700">
                Browse Corpora
            </a>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="max-w-6xl mx-auto my-12 grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Feature Card -->
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <h3 class="text-xl font-semibold text-gray-800">Corpus Management</h3>
            <p class="text-gray-600 mt-2">Create and organize linguistic datasets effortlessly.</p>
        </div>

        <!-- Feature Card -->
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <h3 class="text-xl font-semibold text-gray-800">Collaboration Tools</h3>
            <p class="text-gray-600 mt-2">Work with other linguists to annotate and correct texts.</p>
        </div>

        <!-- Feature Card -->
        <div class="bg-white p-6 rounded-lg shadow-md text-center">
            <h3 class="text-xl font-semibold text-gray-800">Smart Analysis</h3>
            <p class="text-gray-600 mt-2">Use AI tools to process and enhance linguistic data.</p>
        </div>

    </section>

    <?php if (!isset($_SESSION['user_id'])):?>
    <!-- Call to Action -->
    <section class="max-w-6xl mx-auto my-12 p-6 bg-blue-600 text-white rounded-lg text-center">
        <h2 class="text-2xl font-semibold">Get Started Today</h2>
        <p class="text-white mt-2">Join a growing community of linguists and researchers.</p>
        <div class="mt-4">
            <a href="../auth/signup.php" class="bg-white text-blue-600 px-6 py-3 rounded-md text-lg hover:bg-gray-200">
                Create an Account
            </a>
        </div>
    </section>

<?php
    endif;
$content = ob_get_clean(); // récupère tout le HTML généré

// On inclut le layout principal
include '../layout.php';
