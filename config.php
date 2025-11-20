<?php
// Active les erreurs pour le debogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================================
// CONFIGURATION
// ===================================
$config_dir = __DIR__ . '/retrofe/';
$allowed_files = [
    'settings' => ['file' => 'settings.conf', 'name' => 'Réglages Principaux'],
    'controls' => ['file' => 'controls.conf', 'name' => 'Contrôles']
];
$translations = [
    'Display settings' => 'Réglages de l\'affichage',
    'Full screen SDL applications often clash with the focus of other SDL applications' => 'Le mode plein écran peut parfois entrer en conflit avec d\'autres applications.',
    'or enter in the screen pixel width (i.e 1920)' => 'Largeur de l\'écran en pixels (ex: 1920).',
    'or enter in the screen pixel width (i.e 1080)' => 'Hauteur de l\'écran en pixels (ex: 1080).',
    'Do not show the mouse' => 'Masquer le curseur de la souris.',
    'Do not show the text between () in a game\'s description' => 'Masquer les textes entre parenthèses () dans les descriptions.',
    'Do not show the text between [] in a game\'s description' => 'Masquer les textes entre crochets [] dans les descriptions.',
    'Video playback can be turned off for very weak systems' => 'La lecture vidéo peut être désactivée pour les petits PC.',
    'Number of times a video should be played; 0 is forever' => 'Nombre de lectures de la vidéo; 0 = en boucle.',
    'Do not unload the SDL library when starting a game' => 'Ne pas décharger la bibliothèque SDL au lancement d\'un jeu.',
    'Do not minimize RetroFE when it loses focuse' => 'Ne pas minimiser RetroFE s\'il perd le focus (ALT+TAB).',
    'Collection & playlist selection' => 'Sélection des Collections & Playlists',
    'Loaded playlist on starting RetroFE' => 'Playlist à charger au démarrage.',
    'Select this playlist when entering a collection, if available' => 'Sélectionner cette playlist en entrant dans une collection (si elle existe).',
    'Names of the playlists the cycle playlist key cycles over' => 'Noms des playlists à parcourir avec la touche de cycle (ex: all,favorites).',
    'Size of the lastplayed playlist for games; 0 is disabled' => 'Taille de la playlist "derniers jeux joués"; 0 = désactivé.',
    'Size of the lastplayed playlist for collections; 0 is disabled' => 'Taille de la playlist "dernières collections vues"; 0 = désactivé.',
    'Loaded collection on starting RetroFE' => 'Collection à charger au démarrage.',
    'Do not auto-enter the first menu item on starting RetroFE' => 'Ne pas entrer automatiquement dans le premier menu au démarrage.',
    'Do not auto-enter a collection when using the collectionUp/Down/Left/Right buttons' => 'Ne pas entrer automatiquement dans une collection via les flèches.',
    'Move to the next/previous collection when using the collectionUp/Down/Left/Right buttons' => 'Passer à la collection suivante/précédente avec les flèches.',
    'Do not exit RetroFE when pressing the back key in the main menu' => 'Ne pas quitter RetroFE en appuyant sur "Retour" dans le menu principal.',
    'Clear the input queue when entering a collection' => 'Vider la file d\'attente des touches en entrant dans une collection.',
    'Merge all .sub files into a single list when sorting' => 'Fusionner tous les fichiers .sub en une seule liste lors du tri.',
    'When pressing previous letter, switch to the first game of the previous letter' => 'En appuyant sur "lettre précédente", aller au premier jeu de cette lettre.',
    'Remember the position in a menu when exiting a collection' => 'Mémoriser la position dans un menu en quittant une collection.',
    'Automatically back out of empty collection' => 'Quitter automatiquement une collection si elle est vide.',
    'Attract mode settings' => 'Réglages du mode Veille (Attract Mode)',
    'Start attract mode after 120 seconds' => 'Démarrer le mode veille après X secondes d\'inactivité.',
    'Continue attract mode after 30 seconds' => 'Passer au jeu/média suivant en mode veille après X secondes.',
    'Disable playlist switching in attract mode' => 'Désactiver le changement de playlist en mode veille (0 = désactivé).',
    'Disable collection switching in attract mode' => 'Désactiver le changement de collection en mode veille (0 = désactivé).',
    'Playlist not used in attract mode' => 'Playlist à ne pas utiliser en mode veille.',
    'Collection not used in attract mode' => 'Collection à ne pas utiliser en mode veille.',
    'Base folders of media and ROM files' => 'Dossiers de base pour les Médias et les ROMs',
    'Override if you choose to have your media stored outside of RetroFE.' => 'À utiliser si vos médias sont stockés en dehors de RetroFE.',
    'If this is commented out your artwork will be searched in collections/<collectionname>/<imagetype>' => 'Si commenté (#), les médias sont cherchés dans collections/[nom]/[type].',
    'Override if you choose to have your ROMs stored outside of RetroFE.' => 'À utiliser si vos ROMs sont stockées en dehors de RetroFE.',
    'If this is commented out your roms will be searched in collections/<collectionname>/roms' => 'Si commenté (#), les ROMs sont cherchées dans collections/[nom]/roms.',
];
$controls_helper = [
    'Clavier (Directions)' => ['Up', 'Down', 'Left', 'Right'],
    'Clavier (Actions)' => ['Return', 'Escape', 'Space', 'Tab', 'Left Shift', 'Left Ctrl'],
    'Clavier (Lettres)' => ['A', 'B', 'C', 'D', 'W', 'S', 'X', 'Y', 'Z', 'Q'],
    'Manette (Boutons XInput)' => ['joyButton0 (A)', 'joyButton1 (B)', 'joyButton2 (X)', 'joyButton3 (Y)'],
    'Manette (Boutons Special)' => ['joyButton4 (LB)', 'joyButton5 (RB)', 'joyButton6 (Back)', 'joyButton7 (Start)'],
    'Manette (Directions)' => ['joyHat0Up', 'joyHat0Down', 'joyHat0Left', 'joyHat0Right'],
    'Manette (Axes)' => ['joyAxis0Pos', 'joyAxis0Neg', 'joyAxis1Pos', 'joyAxis1Neg']
];

$file_key = 'settings'; 
if (isset($_GET['file']) && isset($allowed_files[$_GET['file']])) {
    $file_key = $_GET['file'];
}
$config_file = $config_dir . $allowed_files[$file_key]['file'];
$save_message = "";

// LOGIQUE DE SAUVEGARDE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings'])) {
    $new_settings = $_POST['settings'];
    if (is_readable($config_file) && is_writable($config_file)) {
        copy($config_file, $config_file . '.bak');
        $lines = file($config_file);
        $new_content = "";
        foreach ($lines as $line) {
            if (preg_match('/^\s*([a-zA-Z0-9_]+)\s*=\s*([^#]*)(\s*#\s*(.*))?/', $line, $matches)) {
                $key = $matches[1];
                $original_comment = $matches[3] ?? ''; 
                if (isset($new_settings[$key])) {
                    $new_value = trim($new_settings[$key]);
                    $new_content .= $key . " = " . $new_value . $original_comment . PHP_EOL;
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

// LOGIQUE DE LECTURE
$config_data = [];
if (is_readable($config_file)) {
    $lines = file($config_file);
    foreach ($lines as $line) {
        $trimmed_line = trim($line);
        if (preg_match('/^\s*([a-zA-Z0-9_]+)\s*=\s*([^#]*)(\s*#\s*(.*))?/', $trimmed_line, $matches)) {
            $key = $matches[1]; 
            $value = trim($matches[2]); 
            $comment_text = $matches[4] ?? ''; 
            if (!empty($comment_text)) {
                $comment_text = (isset($translations[$comment_text])) ? $translations[$comment_text] : htmlspecialchars($comment_text);
            }
            $config_data[] = ['type' => 'setting', 'key' => $key, 'value' => $value, 'comment' => $comment_text];
        }
        else if (preg_match('/^#\s*([a-zA-Z].*)/', $trimmed_line, $matches) && strpos($trimmed_line, '=') === false) {
            $section_text = trim($matches[1], " #"); 
            if (isset($translations[$section_text])) {
                $config_data[] = [
                    'type' => 'section',
                    'text' => $translations[$section_text] 
                ];
            }
        }
    }
} else {
    $save_message = "ERREUR : Le fichier de configuration est introuvable ou n'est pas accessible en lecture.";
}

// Definit la page active pour le header
$currentPage = 'config';
include 'header.php';
?>

<h1>🎨 Éditeur de Configuration RetroFe</h1>

<?php if ($save_message): ?>
    <p class="save-message <?php echo (strpos($save_message, 'ERREUR') !== false) ? 'error' : 'success'; ?>">
        <?php echo htmlspecialchars($save_message); ?>
    </p>
<?php endif; ?>

<div class="grid-container">
    
    <div class="form-column">
        <form method="POST" action="?file=<?php echo $file_key; ?>">
            <?php
            foreach ($config_data as $data) {
                if ($data['type'] === 'section') {
                    echo "<h2 class='form-section'>⚙️ {$data['text']}</h2>";
                }
                else if ($data['type'] === 'setting') {
                    $key = $data['key'];
                    $value = trim($data['value']); 
                    $comment = $data['comment'];

                    echo "<div class='form-group'>";
                    echo "<label>" . htmlspecialchars($key) . "</label>";
                    if (!empty($comment)) {
                        echo "<div class='comment'>$comment</div>"; 
                    }
                    
                    if ($file_key === 'controls') {
                        echo "<input type='text' name='settings[$key]' value='" . htmlspecialchars($value) . "'>";
                    }
                    else if (strtolower($value) === 'yes' || strtolower($value) === 'no') {
                        $selected_yes = (strtolower($value) === 'yes') ? 'selected' : '';
                        $selected_no = (strtolower($value) === 'no') ? 'selected' : '';
                        echo "<select name='settings[$key]'>";
                        echo "<option value='yes' $selected_yes>yes</option>";
                        echo "<option value='no' $selected_no>no</option>";
                        echo "</select>";
                    }
                    else if (is_numeric($value)) {
                        echo "<input type='number' name='settings[$key]' value='" . htmlspecialchars($value) . "'>";
                    }
                    else {
                        echo "<input type='text' name='settings[$key]' value='" . htmlspecialchars($value) . "'>";
                    }
                    
                    echo "</div>";
                }
            }
            ?>
            
            <button type="submit" class="btn-save">💾 Sauvegarder les modifications</button>
        </form>
    </div>
    
    <div class="helper-column">

        <?php if ($file_key === 'settings'): ?>
            <div class="helper-box intro-box">
                <h3>👋 Bienvenue !</h3>
                <p class="note">Modifiez ici les configurations de base de RetroFE. Les modifications seront appliquées au fichier <code>settings.conf</code>.</p>
                <p class="note">N'oubliez pas de cliquer sur "💾 Sauvegarder" en bas de la page.</p>
            </div>

        <?php elseif ($file_key === 'controls'): ?>
            <div class="helper-box">
                <h3>💡 Aide-mémoire des contrôles</h3>
                <p class="note">Utilisez ces codes dans les champs à gauche. Vous pouvez en combiner plusieurs avec une virgule.</p>
                
                <?php foreach ($controls_helper as $title => $keys): ?>
                    <h4><?php echo $title; ?></h4>
                    <?php foreach ($keys as $key): ?>
                        <code><?php echo $key; ?></code>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <p class="note">Pour une liste complète, regardez le bas du fichier `controls.conf` d'origine.</p>
            </div>
        <?php endif; ?>

    </div> </div> <?php
include 'footer.php';
?>