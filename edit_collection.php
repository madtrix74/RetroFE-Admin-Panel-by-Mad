<?php
// Active les erreurs pour le debogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================================
// CORRECTION DU BUG DE CACHE
// ===================================
// Force PHP a re-verifier l'existence des fichiers
clearstatcache();
// ===================================

// ===================================
// SECURITE : Valider la collection
// ===================================
$collection_name = $_GET['collection'] ?? '';
$config_file = __DIR__ . '/retrofe/collections/' . $collection_name . '/settings.conf';

// Empeche les attaques (ex: ?collection=../scripts)
if (empty($collection_name) || !preg_match('/^[a-zA-Z0-9_\- ]+$/', $collection_name) || !file_exists($config_file)) {
    // C'est cette ligne qui causait le "flash"
    header('Location: collections.php');
    exit;
}

// ===================================
// DICTIONNAIRE DE TRADUCTION
// ===================================
$translations = [
    'roms' => 'Chemin vers les ROMs',
    'list.include.extensions' => 'Extensions de fichiers à inclure',
    'list.menu.layout' => 'Nom du Layout (Thème) du menu',
    'emulator' => 'Nom de l\'émulateur à utiliser',
];

$save_message = "";

// ===================================
// LOGIQUE DE SAUVEGARDE
// ===================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    $new_settings = $_POST['settings'];
    if (is_readable($config_file) && is_writable($config_file)) {
        copy($config_file, $config_file . '.bak');
        $lines = file($config_file);
        $new_content = "";
        foreach ($lines as $line) {
            if (preg_match('/^\s*([a-zA-Z0-9\._]+)\s*=\s*(.*)/', $line, $matches)) {
                $key = $matches[1];
                if (isset($new_settings[$key])) {
                    $new_value = trim($new_settings[$key]);
                    $new_content .= $key . " = " . $new_value . PHP_EOL;
                } else {
                    $new_content .= $line;
                }
            } else {
                $new_content .= $line;
            }
        }
        file_put_contents($config_file, $new_content);
        $save_message = "Sauvegarde réussie !";
    } else {
        $save_message = "ERREUR : Le fichier de configuration est introuvable ou n'est pas accessible en écriture.";
    }
}

// ===================================
// LOGIQUE DE LECTURE
// ===================================
$config_data = [];
if (is_readable($config_file)) {
    $lines = file($config_file);
    foreach ($lines as $line) {
        $trimmed_line = trim($line);
        if (preg_match('/^\s*([a-zA-Z0-9\._]+)\s*=\s*(.*)/', $trimmed_line, $matches)) {
            $key = $matches[1]; 
            $value = trim($matches[2]); 
            $label = (isset($translations[$key])) ? $translations[$key] : $key;
            $config_data[] = [
                'type' => 'setting',
                'key' => $key,
                'label' => $label,
                'value' => $value,
            ];
        }
    }
} else {
    $save_message = "ERREUR : Le fichier de configuration est introuvable ou n'est pas accessible en lecture.";
}

// Definit la page active pour le header
$currentPage = 'collections';
include 'header.php';
?>
        
<h1>✏️ Éditeur de Collection : <?php echo htmlspecialchars($collection_name); ?></h1>

<?php if ($save_message): ?>
    <p class="save-message <?php echo (strpos($save_message, 'ERREUR') !== false) ? 'error' : 'success'; ?>">
        <?php echo htmlspecialchars($save_message); ?>
    </p>
<?php endif; ?>

<div class="grid-container">
    
    <div class="form-column">
        <form method="POST" action="?collection=<?php echo htmlspecialchars($collection_name); ?>">
            <?php
            foreach ($config_data as $data) {
                if ($data['type'] === 'setting') {
                    $key = $data['key'];
                    $value = $data['value'];
                    $label = $data['label'];

                    echo "<div class='form-group'>";
                    echo "<label>" . htmlspecialchars($label) . "</label>";
                    echo "<div class='comment'>Clé : <code>" . htmlspecialchars($key) . "</code></div>"; 
                    echo "<input type='text' name='settings[$key]' value='" . htmlspecialchars($value) . "'>";
                    echo "</div>";
                }
            }
            ?>
            <button type="submit" class="btn-save">💾 Sauvegarder les modifications</button>
        </form>
    </div>
    
    <div class="helper-column">
        <div class="helper-box intro-box">
            <h3>⚙️ Édition de la collection</h3>
            <p class="note">Vous modifiez le fichier <code>settings.conf</code> pour <strong><?php echo htmlspecialchars($collection_name); ?></strong>.</p>
            <p class="note">Les réglages courants sont :<br>
                - <strong>roms</strong>: Le chemin vers vos jeux (ex: ./roms/).<br>
                - <strong>list.include.extensions</strong>: Les extensions de fichiers (ex: zip,sfc,smc).<br>
                - <strong>emulator</strong>: L'émulateur à utiliser.
            </p>
            <br>
            <a href="collections.php" class="btn-edit" style="background: #555; width: 100%; text-align: center;">Retour à la liste</a>
        </div>
    </div>
    
</div> <?php
include 'footer.php';
?>