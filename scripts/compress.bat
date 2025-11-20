@echo off
echo ===================================
echo = Script de Compression 7zip
echo ===================================

:: %1 est le nom de l'archive a creer (ex: "backup.7z")
:: %2 est le dossier/fichier a compresser
SET ARCHIVE_NAME=%1
SET FOLDER_TO_ZIP=%2

:: On definit le chemin vers nos outils
SET SEVEN_ZIP_EXE=%~dp0..\includes\7z.exe

echo.
echo Lancement de 7zip...
echo Archive: %ARCHIVE_NAME%
echo Dossier a compresser: %FOLDER_TO_ZIP%
echo.

:: 'a' signifie 'Add to archive' (Ajouter a l'archive)
:: -t7z force le format 7z
"%SEVEN_ZIP_EXE%" a -t7z "%ARCHIVE_NAME%" "%FOLDER_TO_ZIP%"

echo.
echo Compression terminee.
echo ===================================