<?php
// Active les erreurs pour le debogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================================
// CONFIGURATION
// ===================================
$collections_dir = __DIR__ . '/retrofe/collections/';
$collection_to_scrape = $_POST['collection'] ?? ''; 
$platform_id_selected = $_POST['platform_id'] ?? ''; // L'ID qui a ete soumis

// SESSION : VERIFICATION DE L'INSTALLATION
define('SKRAPER_EXE_PATH', __DIR__ . '/includes/SkraperUI/SkraperUI.exe');
$is_installed = file_exists(SKRAPER_EXE_PATH);

// ===================================
// NOUVEAU : LISTE DES PLATEFORMES (ID => Nom)
// ===================================
$platform_list = [
    "3" => "NES",
    "4" => "SNES",
    "7" => "Megadrive / Genesis",
    "11" => "Sega CD",
    "12" => "Sega 32X",
    "13" => "Playstation (PS1/psx)",
    "14" => "Nintendo 64",
    "15" => "PC Engine (pce)",
    "16" => "PC Engine CD",
    "21" => "Master System",
    "22" => "Game Boy",
    "24" => "Game Boy Color",
    "26" => "Game Boy Advance",
    "29" => "Saturn",
    "32" => "Dreamcast",
    "35" => "Game Gear",
    "75" => "MAME",
    "78" => "Neo Geo CD",
    "82" => "Atari 2600",
    "87" => "PC (DOS)",
    "135" => "Neo Geo Pocket Color"
];
// ===================================

// LECTURE DES COLLECTIONS EXISTANTES
$collections = [];
if (is_dir($collections_dir)) {
    $items = scandir($collections_dir);
    foreach ($items as $item) {
        if ($item[0] !== '.' && is_dir($collections_dir . $item)) {
            $collections[] = $item;
        }
    }
}

// Fonction pour nettoyer le log (enleve les codes couleurs)
function ansi_to_html($string) {
    $search = ['/\[0;1;31m/','/\[0;1;32m/','/\[0;1;33m/','/\[0;1;34m/','/\[0;1;36m/','/\[0m/'];
    $replace = ['<span class="ansi-bold-red">','<span class="ansi-bold-green">','<span class="ansi-bold-yellow">','<span class="ansi-bold-blue">','<span class="ansi-bold-cyan">','</span>'];
    $html_line = preg_replace($search, $replace, htmlspecialchars($string));
    $open_tags = substr_count($html_line ?? '', '<span');
    $close_tags = substr_count($html_line ?? '', '</span>');
    if ($open_tags > $close_tags) { $html_line .= str_repeat('</span>', $open_tags - $close_tags); }
    return $html_line;
}

$currentPage = 'scraper';
include 'header.php';
?>
        
<h1>🛰️ Scraper de Médias (SkraperUI)</h1>

<?php if ($is_installed): ?>

    <div class="grid-container">
        <div class="form-column">
            <h2 class="form-section">1. Lancer le Scraper (en ligne de commande)</h2>
            
            <form method="POST" action="">
                
                <div class="helper-box intro-box" style="margin-bottom: 25px;">
                    <div class="form-group">
                        <label for="collection">Collection à Analyser :</label>
                        <select name="collection" id="collection" onchange="guessPlatformId()">
                            <option value="">-- Sélectionnez une collection --</option>
                            <?php foreach ($collections as $collection): ?>
                                <option value="<?php echo htmlspecialchars($collection); ?>" <?php echo ($collection === $collection_to_scrape) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($collection); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="platform_id">ID de la Plateforme (ScreenScraper)</label>
                        <select name="platform_id" id="platform_id">
                            <option value="">-- Sélectionnez l'ID de la plateforme --</option>
                            <?php
                            foreach ($platform_list as $id => $name) {
                                // Verifie si cet ID etait celui soumis
                                $selected = ($platform_id_selected === $id) ? 'selected' : '';
                                echo "<option value=\"$id\" $selected>" . htmlspecialchars($name) . " (ID: $id)</option>";
                            }
                            ?>
                        </select>
                        <div class="comment">Auto-rempli en choisissant une collection.</div>
                    </div>
                    <input type="hidden" name="action" value="scrape">
                    <button type="submit" class="btn-launch" style="width: 100%;">
                        🚀 Lancer le Scraper (Console)
                    </button>
                    
                </div> </form>
            
        </div>
        
        <div class="helper-column">
            <h2 class="form-section">2. Configuration (1ère Fois)</h2>
            <div class="helper-box intro-box">
                <h3>⚠️ ACTION REQUISE</h3>
                <p class="note">Pour que le scraping fonctionne, vous devez configurer vos identifiants **une seule fois**.</p>
                <p class="note">1. Cliquez sur le bouton ci-dessous pour lancer l'interface de Skraper.</p>
                
                <a href="#" onclick="launchSkraperUI(); return false;" class="btn-edit" style="width: 100%; text-align: center; margin-top: 15px;">
                    Configurer (1ère Fois)
                </a>
                
                <p class="note" style="margin-top: 15px;">2. Allez dans l'onglet "Compte" et entrez vos identifiants ScreenScraper.</p>
                <p class="note">3. Fermez le programme. Vos identifiants sont sauvegardés.</p>
                <p class="note">4. Vous pouvez maintenant utiliser le bouton "Lancer le Scraper (Console)" à gauche.</p>
            </div>

            <div class="helper-box" style="margin-top: 25px;">
                <h3>💡 IDs des Plateformes</h3>
                <p class="note">Si votre collection n'est pas dans la liste, vous pouvez trouver l'ID complet sur le site de ScreenScraper et le taper dans la case de l'URL (ex: `...scraper.php?platform_id=VOTRE_ID`)</p>
            </div>

        </div>
    </div> <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'scrape') {
        // On utilise $platform_id_selected (qui vient du formulaire)
        if (empty($collection_to_scrape) || empty($platform_id_selected)) {
            echo "<p class='save-message error'>Veuillez sélectionner une collection ET une plateforme.</p>";
        } else {
            echo '<h2 class="form-section">Sortie du Scraper (en direct)</h2>';
            echo '<pre class="live-log">'; 
            
            ob_end_flush(); @ob_flush(); flush();
            
            $rom_path_str = $collections_dir . $collection_to_scrape . '/roms';
            $media_path_str = $collections_dir . $collection_to_scrape . '/media';
            $gamelist_path_str = $rom_path_str . '/gamelist.xml'; 

            if (!is_dir($media_path_str)) {
                mkdir($media_path_str, 0777, true);
                echo "Dossier 'media' créé pour " . htmlspecialchars($collection_to_scrape) . "...\n";
            }
            flush();

            $rom_path = realpath($rom_path_str);
            $media_path = realpath($media_path_str);
            $gamelist_path = str_replace('/', DIRECTORY_SEPARATOR, $gamelist_path_str); 
            
            if ($rom_path === false) {
                echo "ERREUR FATALE : Impossible de trouver le chemin des ROMs.\n";
            } else {
                $script_path = escapeshellcmd(__DIR__ . "/scripts/run_skraper.bat");
                
                $arg_platform = escapeshellarg($platform_id_selected); // On utilise l'ID du formulaire
                $arg_rom_path = escapeshellarg($rom_path);
                $arg_media_path = escapeshellarg($media_path);
                $arg_gamelist = escapeshellarg($gamelist_path);

                $command = $script_path . " " . $arg_rom_path . " " . $arg_media_path . " " . $arg_gamelist . " " . $arg_platform;
                
                $descriptorspec = [ 0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"] ];
                $process = proc_open($command, $descriptorspec, $pipes);
                
                if (is_resource($process)) {
                    fclose($pipes[0]);
                    while ($line = fgets($pipes[1])) {
                        echo ansi_to_html($line);
                        @ob_flush(); flush();
                    }
                    fclose($pipes[1]);
                    $errors = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    if (!empty($errors)) {
                        echo "<span class=\"ansi-bold-red\">ERREURS DU SCRIPT :\n" . ansi_to_html($errors) . "</span>";
                    }
                    $return_code = proc_close($process);
                    echo "\n--- Opération terminée avec le code $return_code ---";
                }
                echo '</pre>';
            }
        }
    }
    ?>

<?php else: ?>
    <h2>❌ Skraper n'est pas detecte</h2>
    <p>L'exécutable <code><?php echo htmlspecialchars(SKRAPER_EXE_PATH); ?></code> est introuvable.</p>
    <p>Vous devez l'installer avant de pouvoir utiliser cette page.</p>
    <br>
    <a href="install_skraper.php" class="btn-install">
        Aller à la page d'installation
    </a>
<?php endif; ?>
        
<?php
// On ajoute le bloc JavaScript a la fin, juste avant le footer
?>
<script>
    function guessPlatformId() {
        // Dictionnaire de "guesses" (Nom de dossier -> ID de plateforme)
        const platformGuesses = {
            'snes': '4',
            'super nintendo': '4',
            'nes': '3',
            'nintendo': '3',
            'sega gensis': '7', // Votre nom de dossier
            'genesis': '7',
            'megadrive': '7',
            'ps1': '13',
            'psx': '13',
            'playstation': '13',
            'n64': '14',
            'gb': '22',
            'game boy': '22',
            'gbc': '24',
            'game boy color': '24',
            'gba': '26',
            'game boy advance': '26',
            'mame': '75',
            'sega cd': '11',
            '32x': '12',
            'master system': '21',
            'game gear': '35',
            'saturn': '29',
            'dreamcast': '32',
            'neogeocd': '78',
            'atari 2600': '82',
            'dos': '87'
        };

        const collectionSelect = document.getElementById('collection');
        const platformIdSelect = document.getElementById('platform_id'); // C'est maintenant le <select>
        
        // Prend le nom de la collection (ex: "Sega Gensis") et le met en minuscule
        let collectionName = collectionSelect.value.toLowerCase();
        
        // Cherche dans notre dictionnaire
        if (platformGuesses[collectionName]) {
            // Si on trouve, on change la valeur selectionnee du dropdown !
            platformIdSelect.value = platformGuesses[collectionName];
        } else {
            // Si on ne trouve pas, on remet sur "Selectionnez"
            platformIdSelect.value = "";
        }
    }
    
    // Lance la fonction une fois au chargement de la page
    document.addEventListener('DOMContentLoaded', guessPlatformId);
</script>
<?php
include 'footer.php';
?>