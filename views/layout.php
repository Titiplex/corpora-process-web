<?php
// layout.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?php echo $pageTitle ?? 'Philomathos'; ?></title>
    <!-- Si tu utilises Tailwind via CDN : -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Si tu as un output.css local :
    <link rel="stylesheet" href="/assets/css/output.css" />
    -->
</head>
<body class="bg-gray-100 text-gray-900">

<!-- Inclure l'en-tête -->
<?php include __DIR__ . '/partials/header.php'; ?>

<!-- Contenu principal -->
<main class="max-w-6xl mx-auto p-6 mt-8">
    <!-- La variable $content contiendra le HTML spécifique à chaque page -->
    <?php echo $content ?? ''; ?>
</main>

<!-- Inclure le pied de page -->
<?php include __DIR__ . '/partials/footer.php'; ?>

</body>
</html>
