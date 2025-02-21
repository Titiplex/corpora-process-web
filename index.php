<!-- index.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Accueil - Projet Linguistique</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">

</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<header class="bg-white shadow">
    <div class="container mx-auto p-4">
        <h1 class="text-xl font-bold">Projet de Linguistique Historique</h1>
    </div>
</header>

<main class="container mx-auto p-4 flex-grow">
    <p>Bienvenue ! Ce site permet aux linguistes de créer, gérer et annoter des corpus.</p>
    <div class="mt-4">
        <a href="/views/auth/login.php"
           class="inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            Se connecter
        </a>
        <a href="/views/auth/signup.php"
           class="inline-block bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 ml-2">
            S’inscrire
        </a>
    </div>
</main>

<footer class="bg-gray-200 p-4 mt-4 text-center text-sm">
    &copy; 2025. Projet CMI7 - Tout droit réservé.
</footer>
</body>
</html>
