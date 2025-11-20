<?php
// ===================================
// DEBUT DU BLOC DE DEBOGAGE
// ===================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ===================================

// Augmente le temps d'execution (0 = illimite)
set_time_limit(0); 
// Continue le script meme si l'utilisateur ferme la page
ignore_user_abort(true); 

// --- CONFIGURATION ---
define('RETROFE_PATH', __DIR__ . '/retrofe');
define('DOWNLOAD_PAGE_URL', 'http://retrofe.nl/Download/Release/');
// Lien de secours (le dernier de votre screenshot)
define('FALLBACK_DOWNLOAD_URL', 'http://retrofe.nl/Download/Release/RetroFE_full_0.10.31.zip'); 

define('TEMP_DOWNLOAD_DIR', __DIR__ . '/temp_downloads');
define('LOG_FILE_PATH', __DIR__ . '/install_log.txt');
// --- FIN DE LA CONFIGURATION ---

// Fonction pour ecrire dans le log
function write_to_log($message) {
    file_put_contents(LOG_FILE_PATH, $message . PHP_EOL, FILE_APPEND);
}

$console_output = "";
$is_installing = false; 

if (isset($_POST['action']) && $_POST['action'] === 'install') {
    $is_installing = true; 
    if (file_exists(LOG_FILE_PATH)) {
        unlink(LOG_FILE_PATH);
    }
    write_to_log("--- Debut de l'installation ---");
    write_to_log("Heure : " . date('Y-m-d H:i:s'));
    if (!is_dir(TEMP_DOWNLOAD_DIR)) {
        mkdir(TEMP_DOWNLOAD_DIR, 0777, true);
        write_to_log("Dossier " . TEMP_DOWNLOAD_DIR . " cree.");
    }
    
    // ===================================
    // RECHERCHE DU LIEN DE TELECHARGEMENT (CORRIGEE)
    // ===================================
    write_to_log("--- Recherche du dernier lien de téléchargement ---");
    write_to_log("Lecture de la page: " . DOWNLOAD_PAGE_URL);
    $final_download_url = FALLBACK_DOWNLOAD_URL; 
    
    $page_html = @file_get_contents(DOWNLOAD_PAGE_URL);
    
    if ($page_html === false) {
        write_to_log("AVERTISSEMENT: Impossible de lire la page. Utilisation du lien de secours.");
    } else {
        $regex = '/href="(RetroFE_full_(.*?)\.zip)"/i';
        if (preg_match_all($regex, $page_html, $matches)) {
            $all_links = $matches[1]; 
            $latest_link_filename = end($all_links); 
            $final_download_url = DOWNLOAD_PAGE_URL . $latest_link_filename;
            write_to_log("SUCCES: Lien dynamique trouve (le plus recent) : " . $final_download_url);
        } else {
            write_to_log("AVERTISSEMENT: Impossible de trouver un lien correspondant sur la page. Utilisation du lien de secours.");
        }
    }
    // ===================================
    // FIN DE LA RECHERCHE
    // ===================================

    $source_file_name = basename($final_download_url); 
    $downloaded_file_path = TEMP_DOWNLOAD_DIR . '/' . $source_file_name;
    
    // ===================================
    // CORRECTION DU NOM DE DOSSIER EXTRAIT
    // ===================================
    // D'apres votre screenshot, le dossier s'appelle "RetroFE"
    $extracted_folder_name = 'RetroFE'; 
    // ===================================

    write_to_log("--- Debut du Telechargement de RetroFe ---");
    $script_dl = escapeshellcmd(__DIR__ . "/scripts/download.bat");
    $arg_url = escapeshellarg($final_download_url); 
    $arg_dest_folder = escapeshellarg(TEMP_DOWNLOAD_DIR);
    $command_dl = $script_dl . " " . $arg_url . " " . $arg_dest_folder;
    write_to_log("Commande executee : " . $command_dl);
    shell_exec($command_dl . " >> " . LOG_FILE_PATH . " 2>&1");
    write_to_log("--- Telechargement Termine ---");

    if (file_exists($downloaded_file_path)) {
        
        write_to_log("Fichier telecharge trouve : " . $downloaded_file_path);
        
        // Etape 4 : Decompression dans temp_downloads
        write_to_log("--- Debut de la Decompression (dans temp_downloads) ---");
        $script_unzip = escapeshellcmd(__DIR__ . "/scripts/decompress_simple.bat");
        $arg_archive = escapeshellarg($downloaded_file_path);
        $arg_extract_dest = escapeshellarg(TEMP_DOWNLOAD_DIR); 
        $command_unzip = $script_unzip . " " . $arg_archive . " " . $arg_extract_dest;
        write_to_log("Commande executee : " . $command_unzip);
        shell_exec($command_unzip . " >> " . LOG_FILE_PATH . " 2>&1");
        write_to_log("--- Decompression Terminee ---");

        // Etape 5 : Deplacement du dossier
        $source_folder = TEMP_DOWNLOAD_DIR . '/' . $extracted_folder_name;
        
        if (is_dir($source_folder)) {
            write_to_log("--- Debut du Deplacement du dossier ---");
            write_to_log("Dossier source : " . $source_folder);
            $script_move = escapeshellcmd(__DIR__ . "/scripts/move_folder.bat");
            $arg_source = escapeshellarg($source_folder);
            $arg_dest = escapeshellarg(RETROFE_PATH); 
            $command_move = $script_move . " " . $arg_source . " " . $arg_dest;
            write_to_log("Commande executee : " . $command_move);
            shell_exec($command_move . " >> " . LOG_FILE_PATH . " 2>&1");
            write_to_log("--- Deplacement Termine ---");
        } else {
            write_to_log("ERREUR: Le dossier '" . $extracted_folder_name . "' n'a pas ete trouve dans " . TEMP_DOWNLOAD_DIR);
        }
        
        // Etape 6 : Nettoyage du .zip
        write_to_log("--- Debut du Nettoyage ---");
        if (unlink($downloaded_file_path)) {
            write_to_log("Nettoyage reussi. Le fichier .zip a ete supprime.");
        } else {
            write_to_log("ERREUR : Impossible de supprimer le fichier .zip.");
        }
        write_to_log("--- Nettoyage Termine ---");

    } else {
        write_to_log("ERREUR : Le fichier telecharge (" . $downloaded_file_path . ") n'a pas ete trouve.");
    }

    write_to_log("--- Installation Terminee ---");
    if(file_exists(LOG_FILE_PATH)) {
        $console_output = file_get_contents(LOG_FILE_PATH);
    } else {
        $console_output = "ERREUR : Le fichier log n'a pas pu etre cree ou lu.";
    }
}

$is_installed = is_dir(RETROFE_PATH) && count(scandir(RETROFE_PATH)) > 2; 

// Definit la page active pour le header
$currentPage = 'index';
include 'header.php';
?>
        
<h1>🚀 Panneau d'installation RetroFe</h1>

<?php if ($is_installed): ?>
    
    <h2>✅ RetroFe est installe !</h2>
    <p>Le dossier <code><?php echo htmlspecialchars(RETROFE_PATH); ?></code> a ete trouve.</p>
    <p>Vous pouvez maintenant passer a la gestion de votre frontend.</p>
    
<?php else: ?>

    <h2>❌ RetroFe n'est pas detecte</h2>
    <p>Le dossier <code><?php echo htmlspecialchars(RETROFE_PATH); ?></code> est introuvable.</p>
    <p>Voulez-vous telecharger et installer la derniere version ? (Détection auto)</p>
    <br>
    
    <form id="installForm" method="POST" action="index.php">
        <input type="hidden" name="action" value="install">
        <button id="installButton" class="btn-install" type="submit">
            Télécharger et Installer RetroFe
        </button>
    </form>

    <div id="loadingMessage" class="loading-message">
        <span class="spinner"></span> Installation de RetroFe en cours... Cela peut prendre plusieurs minutes.
    </div>

<?php endif; ?>

<?php if (!empty($console_output)): ?>
    <h2>Log de l'installation :</h2>
    <pre><?php echo htmlspecialchars($console_output); ?></pre>
<?php endif; ?>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const installForm = document.getElementById('installForm');
            const installButton = document.getElementById('installButton');
            const loadingMessage = document.getElementById('loadingMessage');

            <?php if ($is_installing && empty($console_output)): ?>
                if(installButton) installButton.style.display = 'none';
                if(loadingMessage) loadingMessage.style.display = 'block';
            <?php endif; ?>

            if (installButton) { 
                installButton.addEventListener('click', function(event) {
                    event.preventDefault(); 
                    installButton.style.display = 'none';
                    loadingMessage.style.display = 'block';
                    setTimeout(() => {
                        installForm.submit();
                    }, 50); 
                });
            }
        });
    </script>

<?php
include 'footer.php';
?>