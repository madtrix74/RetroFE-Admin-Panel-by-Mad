<?php
// Active les erreurs pour le debogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================================
// CONFIGURATION (CHEMIN LOCAL)
// ===================================
// Definit le chemin vers le config.ini LOCAL de Skyscraper
define('SKYSCRAPER_CONFIG_PATH', __DIR__ . '/includes/Skyscraper/config.ini');

$save_message = "";

// ===================================
// FONCTION POUR ECRIRE LE .INI
// ===================================
function write_ini_file($file, $array) {
    $content = "";
    foreach ($array as $section => $values) {
        $content .= "[" . $section . "]\n";
        foreach ($values as $key => $value) {
            $content .= $key . "=\"" . $value . "\"\n";
        }
        $content .= "\n";
    }
    return file_put_contents($file, $content);
}

// ===================================
// LOGIQUE DE SAUVEGARDE (Manuelle - Plus robuste)
// ===================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifie si le config.ini existe, sinon le cree
    if (!file_exists(SKYSCRAPER_CONFIG_PATH)) {
        file_put_contents(SKYSCRAPER_CONFIG_PATH, "[main]\n");
    }

    if (is_readable(SKYSCRAPER_CONFIG_PATH) && is_writable(SKYSCRAPER_CONFIG_PATH)) {
        
        $lines = file(SKYSCRAPER_CONFIG_PATH);
        $config_data = [];
        $current_section = "";
        foreach ($lines as $line) {
            if (preg_match('/^\[(.*)\]$/', $line, $matches)) {
                $current_section = $matches[1];
            } else if (preg_match('/^(.*?)\s*=\s*"(.*)"/', $line, $matches)) {
                $key = $matches[1];
                $value = $matches[2];
                if ($current_section) {
                    $config_data[$current_section][$key] = $value;
                }
            }
        }
        
        $new_user = $_POST['ss_user'] ?? '';
        $new_pass = $_POST['ss_pass'] ?? '';

        if (!isset($config_data['screenscraper'])) {
            $config_data['screenscraper'] = [];
        }
        
        $config_data['screenscraper']['userCreds'] = $new_user . ':' . $new_pass;
        
        if (write_ini_file(SKYSCRAPER_CONFIG_PATH, $config_data)) {
            $save_message = "Sauvegarde réussie !";
        } else {
            $save_message = "ERREUR: Impossible d'écrire dans le fichier config.ini.";
        }
        
    } else {
        $save_message = "ERREUR : Le fichier config.ini est introuvable ou protégé en écriture. (" . SKYSCRAPER_CONFIG_PATH . ")";
    }
}

// ===================================
// LOGIQUE DE LECTURE (Manuelle - Plus robuste)
// ===================================
$ss_user = "";
$ss_pass = "";

if (is_readable(SKYSCRAPER_CONFIG_PATH)) {
    $lines = file(SKYSCRAPER_CONFIG_PATH);
    foreach ($lines as $line) {
        if (preg_match('/^userCreds\s*=\s*"(.*):(.*)"/', $line, $matches)) {
            $ss_user = $matches[1];
            $ss_pass = $matches[2];
            break; 
        }
    }
} else {
    if (empty($save_message)) { 
        $save_message = "AVERTISSEMENT : Le fichier config.ini (" . SKYSCRAPER_CONFIG_PATH . ") est introuvable. Il sera créé lors de la sauvegarde.";
    }
}

// Definit la page active pour le header
$currentPage = 'scraper';
include 'header.php';
?>
        
<h1>🔧 Configuration de Skyscraper</h1>
<p style="margin-top: -15px; margin-bottom: 25px; font-style: italic;">Modification du fichier <code>/includes/Skyscraper/config.ini</code></p>

<?php if ($save_message): ?>
    <p class="save-message <?php echo (strpos($save_message, 'ERREUR') !== false ? 'error' : (strpos($save_message, 'AVERTISSEMENT') !== false ? 'intro-box' : 'success')); ?>">
        <?php echo htmlspecialchars($save_message); ?>
    </p>
<?php endif; ?>


<div class="helper-box intro-box" style="max-width: 600px; margin: auto;">
    
    <form method="POST" action="scraper_config.php">
        
        <h2 class="form-section">Identifiants ScreenScraper</h2>

        <div class="form-group">
            <label for="ss_user">Utilisateur (user)</label>
            <input type="text" id="ss_user" name="ss_user" value="<?php echo htmlspecialchars($ss_user); ?>">
        </div>
        
        <div class="form-group">
            <label for="ss_pass">Mot de passe (pass)</label>
            <input type="password" id="ss_pass" name="ss_pass" value="<?php echo htmlspecialchars($ss_pass); ?>">
        </div>

        <button type="submit" class="btn-save" style="width: 100%;">💾 Sauvegarder les identifiants</button>
    </form>
    
</div>

<?php
include 'footer.php';
?>