@echo off
:: Force l'encodage en UTF-8 pour que PHP comprenne la sortie
chcp 65001 > NUL

:: %1 est le chemin complet du dossier a supprimer
SET FOLDER_TO_DELETE=%1
SET FOLDER_TO_DELETE=%FOLDER_TO_DELETE:/=\%

echo ===================================
echo = Tentative de suppression FORCEE
echo = %FOLDER_TO_DELETE%
echo ===================================

:: Verifie s'il existe
IF NOT EXIST "%FOLDER_TO_DELETE%" (
    echo INFO: Le dossier n'existe deja plus.
    exit /b
)

echo Etape 1: Nettoyage des attributs (Lecture Seule, Cache, Systeme)...
:: (Votre suggestion)
attrib -h -r -s /s /d "%FOLDER_TO_DELETE%" > NUL

echo Etape 2: Tentative de suppression finale...
:: /S = Sous-dossiers, /Q = Silencieux
rd /S /Q "%FOLDER_TO_DELETE%"

:: Verification finale
IF EXIST "%FOLDER_TO_DELETE%" (
    echo ERREUR: La suppression a echoue malgre le nettoyage des attributs.
    echo Le dossier est verrouille par un autre programme (Google Drive? Antivirus?).
) ELSE (
    echo SUCCES: Le dossier a ete supprime.
)

echo ===================================
echo = Commande terminee.
echo ===================================