@echo off
:: Definit le chemin du dossier SkraperUI
SET SKRAPER_DIR=%~dp0..\includes\SkraperUI

:: Change le repertoire courant pour celui de Skraper
cd /D "%SKRAPER_DIR%"

:: Lance SkraperUI.exe et ne pas attendre
START "SkraperUI" "SkraperUI.exe"

echo "Commande de lancement de l'UI envoyee."