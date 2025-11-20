@echo off
:: Force l'encodage en UTF-8 pour que PHP comprenne la sortie
chcp 65001 > NUL

:: %1 est le dossier source (ex: D:\retrofe-admin\temp_downloads\RetroFE)
:: %2 est le dossier destination (ex: D:\retrofe-admin\retrofe)
SET SOURCE_FOLDER=%1
SET DEST_FOLDER=%2

:: Nettoyage des chemins
SET SOURCE_FOLDER=%SOURCE_FOLDER:/=\%
SET DEST_FOLDER=%DEST_FOLDER:/=\%

echo ===================================
echo = Script de Deplacement de Dossier
echo ===================================

echo Deplacement de: %SOURCE_FOLDER%
echo Vers: %DEST_FOLDER%

:: Verifie si la source existe
IF EXIST "%SOURCE_FOLDER%" (
    :: Deplace le dossier. C'est atomique, ca le renomme.
    move "%SOURCE_FOLDER%" "%DEST_FOLDER%"
    
    IF EXIST "%DEST_FOLDER%" (
        echo Deplacement reussi.
    ) ELSE (
        echo ERREUR: Le deplacement a echoue.
    )
) ELSE (
    echo ERREUR: Le dossier source n'existe pas.
)

echo ===================================