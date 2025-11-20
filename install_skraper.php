<?php
// Active les erreurs pour le debogage
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0); 
ignore_user_abort(true); 

// ===================================
// CONFIGURATION (SKRAPER avec un "K" - v1.1.1)
// ===================================
// Le lien que vous avez trouve
define('SKRAPER_DOWNLOAD_URL', 'https://www.skraper.net/download/beta/Skraper-1.1.1.7z');

// On l'installe dans son propre dossier
define('SKRAPER_INSTALL_DIR', __DIR__ . '/includes/SkraperUI/');
define('SKRAPER_EXE_PATH', SKRAPER_INSTALL_DIR . 'SkraperUI.exe');

define('TEMP_DOWNLOAD_DIR', __DIR__ . '/temp_downloads');
define('LOG_FILE_PATH', __DIR__ . '/skraper_install_log.txt');

// ===================================

function write_to_log($message) {
    file_put_contents(LOG_FILE_PATH, $message . PHP_EOL, FILE_APPEND);
}

$console_output = "";
$is_installing = false; 

// Logique d'installation
if (isset($_POST['action']) && $_POST['action'] === 'install') {
    $is_installing = true; 

    if (file_exists(LOG_FILE_PATH)) unlink(LOG_FILE_PATH);
    write_to_log("--- Debut de l'installation de Skraper (UI) ---");

    if (!is_dir(TEMP_DOWNLOAD_DIR)) mkdir(TEMP_DOWNLOAD_DIR, 0777, true);
    
    $source_file_name = basename(SKRAPER_DOWNLOAD_URL);
    $downloaded_file_path = TEMP_DOWNLOAD_DIR . '/' . $source_file_name;
    
    // Etape 2 : Telechargement
    write_to_log("--- Debut du Telechargement ---");
    $script_dl = escapeshellcmd(__DIR__ . "/scripts/download.bat");
    $arg_url = escapeshellarg(SKRAPER_DOWNLOAD_URL);
    $arg_dest_folder = escapeshellarg(TEMP_DOWNLOAD_DIR);
    $command_dl = $script_dl . " " . $arg_url . " " . $arg_dest_folder;
    shell_exec($command_dl . " >> " . LOG_FILE_PATH . " 2>&1");
    write_to_log("--- Telechargement Termine ---");

    if (file_exists($downloaded_file_path)) {
        write_to_log("Fichier telecharge trouve.");
        
        // Etape 4 : Decompression (utilise le script simple)
        write_to_log("--- Debut de la Decompression ---");
        if (!is_dir(SKRAPER_INSTALL_DIR)) mkdir(SKRAPER_INSTALL_DIR, 0777, true);
        
        // On utilise decompress_simple.bat
        $script_unzip = escapeshellcmd(__DIR__ . "/scripts/decompress_simple.bat");
        $arg_archive = escapeshellarg($downloaded_file_path);
        $arg_extract_dest = escapeshellarg(SKRAPER_INSTALL_DIR); // On decompresse DANS /includes/SkraperUI/
        $command_unzip = $script_unzip . " " . $arg_archive . " " . $arg_extract_dest;
        shell_exec($command_unzip . " >> " . LOG_FILE_PATH . " 2>&1");
        write_to_log("--- Decompression Terminee ---");

        // Etape 5 : Nettoyage du .7z
        write_to_log("--- Debut du Nettoyage ---");
        unlink($downloaded_file_path);
        write_to_log("Nettoyage reussi.");

    } else {
        write_to_log("ERREUR : Le fichier telecharge n'a pas ete trouve.");
    }
    write_to_log("--- Installation Terminee ---");
    if(file_exists(LOG_FILE_PATH)) $console_output = file_get_contents(LOG_FILE_PATH);
}

$is_installed = file_exists(SKRAPER_EXE_PATH); 

$currentPage = 'scraper';
include 'header.php';
?>
        
<h1>🛰️ Installation de Skraper (avec "K")</h1>

<?php if ($is_installed): ?>
    
    <h2>✅ Skraper est installe !</h2>
    <p>L'exécutable <code>SkraperUI.exe</code> a été trouvé dans <code>/includes/SkraperUI/</code>.</p>
    <p>C'est un outil avec une interface visuelle, mais nous pouvons le lancer en ligne de commande.</p>
    <br>
    <a href="scraper.php" class="btn-save">Aller au Scraper</a>
    
<?php else: ?>

    <h2>❌ Skraper n'est pas detecte</h2>
    <p>L'exécutable <code><?php echo htmlspecialchars(SKRAPER_EXE_PATH); ?></code> est introuvable.</p>
    <p>Voulez-vous telecharger et installer la derniere version (1.1.1) ?</p>
    <br>
    
    <form id="installForm" method="POST" action="install_skraper.php">
        <input type="hidden" name="action" value="install">
        <button id="installButton" class="btn-install" type="submit">
            Télécharger et Installer Skraper
        </button>
    </form>

    <div id="loadingMessage" class="loading-message">
        <span class="spinner"></span> Installation de Skraper en cours...
    </div>

<?php endif; ?>

<?php if (!empty($console_output)): ?>
    <h2>Log de l'installation :</h2>
    <pre><?php echo htmlspecialchars($console_output); ?></pre>
<?php endif; ?>
<?php include 'footer.php'; ?>