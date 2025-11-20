@echo off
echo ===================================
echo = Script de Telechargement Wget
echo ===================================

:: %1 est le premier argument (l'URL)
:: %2 est le deuxieme argument (la destination)
SET URL_A_TELECHARGER=%1
SET DOSSIER_DESTINATION=%2

:: %~dp0 est le chemin de ce script (ex: D:\retrofe-admin\scripts\)
:: On remonte d'un niveau (..) pour aller dans le dossier 'includes'
SET WGET_EXE=%~dp0..\includes\wget.exe

echo.
echo Lancement de Wget...
echo Executable Wget utilise: %WGET_EXE%
echo URL: %URL_A_TELECHARGER%
echo Destination: %DOSSIER_DESTINATION%
echo.

:: La ligne modifiee est ci-dessous (ajout de -nv)
"%WGET_EXE%" -nv -P "%DOSSIER_DESTINATION%" "%URL_A_TELECHARGER%"

echo.
echo Telechargement termine.
echo ===================================