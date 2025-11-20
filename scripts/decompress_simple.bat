@echo off
echo ===================================
echo = Script de Decompression Simple
echo = (Sans aplatissement)
echo ===================================

:: %1 est l'archive a decompresser
:: %2 est le dossier de destination
SET ARCHIVE_FILE=%1
SET DEST_FOLDER=%2

:: Nettoyage des chemins (remplace / par \)
SET ARCHIVE_FILE=%ARCHIVE_FILE:/=\%
SET DEST_FOLDER=%DEST_FOLDER:/=\%

:: On definit le chemin vers nos outils
SET SEVEN_ZIP_EXE=%~dp0..\includes\7z.exe

echo.
echo Lancement de 7zip...
echo Archive: %ARCHIVE_FILE%
echo Destination: %DEST_FOLDER%
echo.

:: 'x' signifie 'eXtract with full paths'
:: -o (output directory) - Note: il ne faut PAS d'espace apres -o
:: -y (yes on all prompts) - repond "oui" a tout
"%SEVEN_ZIP_EXE%" x "%ARCHIVE_FILE%" -o"%DEST_FOLDER%" -y

echo.
echo Decompression terminee.
echo ===================================