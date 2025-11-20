@echo off
:: Definit le chemin du dossier RetroFE (remonte de "scripts" puis va dans "retrofe")
SET RETROFE_DIR=%~dp0..\retrofe

:: Change le repertoire courant pour celui de RetroFE
:: (Necessaire pour que RetroFE trouve ses fichiers de config)
:: /D est necessaire pour changer de lecteur (si jamais)
cd /D "%RETROFE_DIR%"

:: ===================================
:: CORRECTION DU CHEMIN
:: ===================================
:: Lance RetroFE.exe depuis le sous-dossier "core"
START "RetroFE" "core\RetroFE.exe"
:: ===================================

echo "Commande de lancement envoyee."