@echo off
echo ===================================
echo = Lancement de Skraper (UI)
echo ===================================

:: %1 est le chemin des ROMs (Absolu)
:: %2 est le chemin des Medias (Absolu)
:: %3 est le chemin du Gamelist (Absolu)
:: %4 est la Plateforme (ex: 7 pour Genesis)
SET ROM_PATH=%~1
SET MEDIA_PATH=%~2
SET GAMELIST_PATH=%~3
SET PLATFORM_ID=%~4

:: Nettoyage des chemins
SET ROM_PATH=%ROM_PATH:/=\%
SET MEDIA_PATH=%MEDIA_PATH:/=\%
SET GAMELIST_PATH=%GAMELIST_PATH:/=\%

:: Chemin vers l'executable
SET SKRAPER_DIR=%~dp0..\includes\SkraperUI
SET SKRAPER_EXE=%SKRAPER_DIR%\SkraperUI.exe

:: Change le repertoire de travail
cd /D "%SKRAPER_DIR%"

echo Lancement de Skraper...
echo Plateforme ID: %PLATFORM_ID%
echo Dossier ROMs: %ROM_PATH%
echo Dossier Medias: %MEDIA_PATH%
echo Fichier Gamelist: %GAMELIST_PATH%
echo.

:: ===================================
:: LIGNE DE COMMANDE (SKRAPER UI)
:: -console : Mode ligne de commande
:: -system [ID] : ID de la plateforme (doit correspondre a l'ID de ScreenScraper)
:: -romfolder [PATH]
:: -mediafolder [PATH]
:: -gamelist [PATH]
:: ===================================
"%SKRAPER_EXE%" -console -system %PLATFORM_ID% -romfolder "%ROM_PATH%" -mediafolder "%MEDIA_PATH%" -gamelist "%GAMELIST_PATH%"

echo.
echo ===================================
echo = Skraper a termine.
echo ===================================