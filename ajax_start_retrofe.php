<?php
// Ce script ne fait qu'une chose: lancer RetroFE en arriere-plan

// Definit le chemin du script .bat
$script_path = __DIR__ . "/scripts/start_retrofe.bat";

// Nettoie le chemin pour shell_exec
$command = escapeshellcmd($script_path);

// Execute le .bat (qui utilise START, donc ca termine immediatement)
shell_exec($command);

// Renvoie une reponse JSON
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Lancement de RetroFE...']);
?>