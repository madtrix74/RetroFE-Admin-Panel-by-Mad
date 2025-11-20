<?php
// Active les erreurs pour le debogage
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ===================================
// CONFIGURATION
// ===================================
$collections_dir = __DIR__ . '/retrofe/collections/';
$message = ""; 
$main_menu_txt = $collections_dir . 'Main' . DIRECTORY_SEPARATOR . 'menu.txt';

// ===================================
// GESTION DES ACTIONS (POST)
// ===================================

// Gere les messages de retour de la suppression
if (isset($_GET['deleted'])) {
    $deleted_name = htmlspecialchars($_GET['deleted']);
    $message = "<p class='save-message success'>Collection '$deleted_name' supprimée avec succès.</p>";
    $message .= "<p class='save-message success'>...et '$deleted_name' a été retiré de <code>Main/menu.txt</code>.</p>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ACTION : CREER UNE COLLECTION ---
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $new_name = trim($_POST['collection_name'] ?? '');
        if (empty($new_name)) {
            $message = "<p class='save-message error'>Le nom ne peut pas être vide.</p>";
        } else if (!preg_match('/^[a-zA-Z0-9_\- ]+$/', $new_name)) {
            $message = "<p class='save-message error'>Le nom contient des caractères invalides.</p>";
        } else {
            $new_path = str_replace('/', DIRECTORY_SEPARATOR, $collections_dir . $new_name);
            if (is_dir($new_path)) {
                $message = "<p class='save-message error'>Une collection avec ce nom existe déjà.</p>";
            } else {
                
                // 1. Creation du dossier
                if (!mkdir($new_path, 0777, true)) {
                    $message = "<p class='save-message error'>ERREUR: Impossible de créer le dossier. Vérifiez les permissions.</p>";
                } else {
                    
                    // ===================================
                    // DEBUT DE LA CORRECTION
                    // ===================================
                    // 2. Creation du settings.conf (au lieu de collection.conf)
                    $conf_content = "roms = ./roms/\n" . 
                                    "list.include.extensions = zip,7z\n" .
                                    "list.menu.layout = default\n";
                    // Le nom du fichier est settings.conf
                    $conf_file_path = $new_path . DIRECTORY_SEPARATOR . 'settings.conf';
                    
                    if (file_put_contents($conf_file_path, $conf_content) === false) {
                        $message = "<p class='save-message error'>ERREUR: Le dossier <strong>" . htmlspecialchars($new_name) . "</strong> a été créé, mais impossible d'écrire <code>settings.conf</code>.</p>";
                        $message .= "<p class='save-message error'>Vérifiez la 'Protection contre les ransomwares' de Windows.</p>";
                    } else {
                        // SUCCES !
                        // 3. Creation du dossier roms
                        mkdir($new_path . DIRECTORY_SEPARATOR . 'roms', 0777, true);
                        $message = "<p class='save-message success'>Collection '" . htmlspecialchars($new_name) . "' créée !</p>";
                        
                        // 4. Ajout au menu.txt
                        if (is_writable($main_menu_txt)) {
                            $new_line = $new_name . PHP_EOL; 
                            file_put_contents($main_menu_txt, $new_line, FILE_APPEND);
                            $message .= "<p class='save-message success'>...et '" . htmlspecialchars($new_name) . "' a été ajouté à <code>Main/menu.txt</code>.</p>";
                        } else {
                            $message .= "<p class='save-message error'>...MAIS ERREUR : Le fichier <code>Main/menu.txt</code> est introuvable.</p>";
                        }
                    }
                    // ===================================
                    // FIN DE LA CORRECTION
                    // ===================================
                }
            }
        }
    }
}


// LECTURE DES COLLECTIONS EXISTANTES
$collections = [];
if (is_dir($collections_dir)) {
    $items = scandir($collections_dir);
    foreach ($items as $item) {
        if ($item[0] !== '.' && is_dir($collections_dir . $item) && strtolower($item) !== 'main') {
            $collections[] = $item;
        }
    }
}

// Definit la page active pour le header
$currentPage = 'collections';
include 'header.php';
?>
        
<h1>🕹️ Gestion des Collections</h1>

<?php echo $message; ?>

<h2 class="form-section">➕ Créer une nouvelle collection</h2>
<div class="helper-box intro-box" style="margin-bottom: 25px;">
    <form method="POST" action="collections.php" style="display: flex; gap: 15px; align-items: center;">
        <label for="collection_name" style="font-size: 1.1em; font-weight: bold; color: #EEE; margin:0;">Nom de la collection :</label>
        <input type="text" id="collection_name" name="collection_name" style="flex-grow: 1; margin:0;" placeholder="ex: SNES">
        <input type="hidden" name="action" value="create">
        <button type="submit" class="btn-save">Créer</button>
    </form>
</div>

<h2 class="form-section">📚 Collections Existantes</h2>

<div class="collection-list">
    <?php if (empty($collections)): ?>
        <p style="text-align: center; font-style: italic;">Aucune collection trouvée (à part "Main").</p>
    <?php else: ?>
        <?php foreach ($collections as $collection): ?>
            <div class="collection-item">
                <span class="collection-item-name"><?php echo htmlspecialchars($collection); ?></span>
                
                <div class="collection-item-actions">
                    <a href="edit_collection.php?collection=<?php echo htmlspecialchars($collection); ?>" class="btn-edit">Éditer</a>
                    
                    <form method="POST" action="delete_collection.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cette collection ? Tous ses fichiers seront perdus !');">
                        <input type="hidden" name="collection_name" value="<?php echo htmlspecialchars($collection); ?>">
                        <button type="submit" class="btn-delete">Supprimer</button>
                    </form>
                </div>
                
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
include 'footer.php';
?>