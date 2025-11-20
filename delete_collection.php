<?php
// Active les erreurs pour le debogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================================
// CONFIGURATION
// ===================================
$collections_dir = __DIR__ . '/retrofe/collections/';
$main_menu_txt = $collections_dir . 'Main' . DIRECTORY_SEPARATOR . 'menu.txt';

// ===================================
// GESTION DE LA SUPPRESSION
// ===================================

$delete_name = $_POST['collection_name'] ?? '';

if (empty($delete_name) || strpos($delete_name, '..') !== false) {
    // Si le nom est invalide, on affiche une erreur propre
    $currentPage = 'collections';
    include 'header.php';
    echo '<h1>❌ Erreur</h1>';
    echo "<p class='save-message error'>Nom de collection invalide.</p>";
    include 'footer.php';
    exit;
}

$delete_path = str_replace('/', DIRECTORY_SEPARATOR, $collections_dir . $delete_name);

if (!is_dir($delete_path)) {
    // Si le dossier n'existe pas, on affiche une erreur propre
    $currentPage = 'collections';
    include 'header.php';
    echo '<h1>❌ Erreur</h1>';
    echo "<p class='save-message error'>Le dossier n'existe pas (il a peut-être déjà été supprimé).</p>";
    include 'footer.php';
    exit;
}

// 1. On execute le script .bat (il est plus puissant que PHP)
$script_path = escapeshellcmd(__DIR__ . "/scripts/delete_folder.bat");
$arg_path = escapeshellarg($delete_path); // On envoie le chemin brut
$command = $script_path . " " . $arg_path . " 2>&1"; 
$script_output = shell_exec($command); // On capture la sortie

// 2. On verifie si ca a marche
if (is_dir($delete_path)) {
    
    // ===================================
    // ECHEC : ON AFFICHE LA PAGE D'ERREUR DETAILLEE
    // ===================================
    $currentPage = 'collections';
    include 'header.php';
    
    echo '<h1>❌ Erreur de Suppression</h1>';
    echo "<p class='save-message error'>La suppression de <strong>" . htmlspecialchars($delete_name) . "</strong> a échoué.</p>";
    
    // Voici la nouvelle boite d'explication
    echo '
    <div class="helper-box intro-box" style="border-top-color: #f44336; margin-top: 25px;">
        <h3 style="color: #f44336;">💡 Solution la plus probable : "Accès contrôlé" de Windows</h3>
        <p class="note">Cette erreur est presque toujours causée par la <strong>Protection contre les ransomwares</strong> de Windows, qui empêche PHP de supprimer des dossiers.</p>
        <p class="note"><strong>Pour corriger (Solution A - Simple) :</strong></p>
        <ol style="color: #CCC; font-size: 0.9em; line-height: 1.5;">
            <li>Ouvrez le menu Démarrer et tapez <strong>"Sécurité Windows"</strong>.</li>
            <li>Allez dans "Protection contre les virus et menaces".</li>
            <li>Cliquez sur "Gérer la protection contre les ransomwares".</li>
            <li>Désactivez <strong>"Accès contrôlé aux dossiers"</strong>.</li>
            <li><strong>Redémarrez votre <code>serveur.bat</code></strong>.</li>
        </ol>
        
        <p class="note" style="margin-top: 15px;"><strong>Pour corriger (Solution B - Propre) :</strong></p>
         <ol style="color: #CCC; font-size: 0.9em; line-height: 1.5;">
            <li>Laissez "Accès contrôlé" activé.</li>
            <li>Sur le même écran, cliquez sur "Autoriser une application via l\'accès contrôlé aux dossiers".</li>
            <li>Cliquez sur "Ajouter une application autorisée" -> "Parcourir toutes les applications".</li>
            <li>Autorisez le fichier <code><strong>D:\retrofe-admin\php\php.exe</strong></code>.</li>
            <li><strong>Redémarrez votre <code>serveur.bat</code></strong>.</li>
        </ol>

        <p class="note" style="margin-top: 15px;">Assurez-vous aussi que le dossier n\'est pas ouvert dans l\'Explorateur Windows et que Google Drive est en pause.</p>
    </div>
    ';
    
    // Affiche le log brut du .bat
    echo '<h2 style="margin-top: 25px;">Log du script Batch :</h2>';
    echo '<pre>' . htmlspecialchars($script_output) . '</pre>';
    
    // Inclut le footer
    include 'footer.php';
    exit; // On s'arrete ici.
}

// 3. Ca a marche ! On met a jour le menu.txt
if (is_readable($main_menu_txt) && is_writable($main_menu_txt)) {
    $lines = file($main_menu_txt); 
    $new_content = "";
    foreach ($lines as $line) {
        if (trim($line) !== $delete_name) {
            $new_content .= $line;
        }
    }
    file_put_contents($main_menu_txt, $new_content); 
}

// 4. On redirige l'utilisateur vers la page des collections
header('Location: collections.php?deleted=' . urlencode($delete_name));
exit;
?>