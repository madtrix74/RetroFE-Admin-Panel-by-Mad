@echo off
:: Force l'encodage en UTF-8 pour que PHP comprenne la sortie
chcp 65001 > NUL

echo ===================================
echo = Script de Decompression RetroFE
echo = (Extraction directe)
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

:: ===================================
:: DEBUT DE LA CORRECTION (Merci 7-Zip!)
:: ===================================

:: 'x' = eXtract with full paths
:: -o"%DEST_FOLDER%" = Dossier de destination
:: "RetroFE\*" = NE PAS extraire l'archive, mais extraire le CONTENU (*)
::                du dossier "RetroFE" qui est DANS l'archive.
:: -aoa = Overwrite All files without prompt (Ecrase tout sans demander)
"%SEVEN_ZIP_EXE%" x "%ARCHIVE_FILE%" -o"%DEST_FOLDER%" "RetroFE\*" -aoa

:: ===================================
:: FIN DE LA CORRECTION
:: ===================================

echo.
echo Decompression et aplatissement termines.
echo Operation terminee.
echo ===================================