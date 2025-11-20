<?php
// Si la variable $currentPage n'est pas definie, on met une valeur par defaut
if (!isset($currentPage)) {
    $currentPage = '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin RetroFe</title>
    <link rel="stylesheet" href="style.css">

    <script>
    function startRetroFE() {
        fetch('ajax_start_retrofe.php')
            .then(response => response.json())
            .then(data => {
                alert('RetroFE est en cours de lancement !');
            })
            .catch(error => {
                alert('Erreur lors du lancement de RetroFE.');
            });
    }

    function launchSkraperUI() {
        fetch('ajax_launch_skraper.php')
            .then(response => response.json())
            .then(data => {
                alert('SkraperUI est en cours de lancement. Veuillez entrer vos identifiants.');
            })
            .catch(error => {
                alert('Erreur lors du lancement de SkraperUI.');
            });
    }
    </script>
</head>
<body>
    <div class="container">
    
    <div class="header-container">
        <div class="tabs">
            <a href="index.php" class="<?php echo ($currentPage === 'index') ? 'active' : ''; ?>">Installation</a>
            <a href="config.php" class="<?php echo ($currentPage === 'config') ? 'active' : ''; ?>">Configuration</a>
            <a href="collections.php" class="<?php echo ($currentPage === 'collections') ? 'active' : ''; ?>">Collections</a>
            <a href="install_skraper.php" class="<?php echo ($currentPage === 'scraper') ? 'active' : ''; ?>">Scraper</a>
        </div>

        <a href="#" onclick="startRetroFE(); return false;" class="btn-launch">
            ► Lancer RetroFe
        </a>
    </div>
