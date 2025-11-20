@echo off

REM ===================================================================
REM == 1. VERIFICATION ET ELEVATION DES DROITS ADMINISTRATEUR
REM ===================================================================
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Demande des privileges administrateur pour Nginx/PHP...
    powershell.exe -Command "Start-Process -FilePath '%~f0' -ArgumentList '%*' -Verb RunAs"
    exit
)

REM ===================================================================
REM == 2. SCRIPT PRINCIPAL (en tant qu'admin)
REM ===================================================================
chcp 65001 >nul
cd /d "%~dp0"


REM --- Boucle du Menu ---
:menu
cls
echo =================================================
echo   Gestionnaire de Serveur Portable - RetroFE
echo =================================================
echo.
echo   [1] DEMARRER les serveurs (Nginx + PHP)
echo   [2] ARRETER les serveurs
echo   [3] QUITTER
echo.
echo =================================================
CHOICE /C 123 /M "Votre choix: "

REM Gérer le choix de l'utilisateur
if ERRORLEVEL 3 goto :quit
if ERRORLEVEL 2 goto :stop_servers
if ERRORLEVEL 1 goto :start_servers

goto :menu


REM ===================================================================
REM == 3. LOGIQUE DE DEMARRAGE
REM ===================================================================
:start_servers
cls
echo [INFO] Demarrage de Nginx + PHP-CGI...
echo.

REM --- NOUVEAU: CORRECTION DES PERMISSIONS (v7 - SIDs) ---
REM %~dp0 se termine par \, ce qui casse les guillemets.
REM On le met dans une variable et on supprime le dernier caractère.
set "APP_PATH=%~dp0"
set "APP_PATH=%APP_PATH:~0,-1%"
echo [INFO] Application des permissions d'écriture pour PHP sur "%APP_PATH%"...
REM Donne les droits (F) au SID *S-1-5-20 (SERVICE RÉSEAU)
icacls "%APP_PATH%" /grant *S-1-5-20:(OI)(CI)F /T >nul
REM Donne les droits (F) au SID *S-1-1-0 (TOUT LE MONDE)
icacls "%APP_PATH%" /grant *S-1-1-0:(OI)(CI)F /T >nul
echo [INFO] Permissions appliquées.

set "PROJECT_ROOT=%CD:\=/%"
set "NGINX_CONF=%CD%\nginx\conf\nginx.conf"

if not exist "nginx\conf" mkdir "nginx\conf"

REM Generation du nginx.conf a la volee
(
    echo worker_processes 1;
    echo.
    echo events {
    echo     worker_connections  1024^;
    echo }
    echo.
    echo http {
    echo     include       mime.types^;
    echo     default_type  application/octet-stream^;
    echo     sendfile        on^;
    echo     keepalive_timeout  65^;
    echo.
    echo     server {
    echo         listen       8080^;
    echo         server_name  localhost^;
    echo         root   "%PROJECT_ROOT:^=/%"^;
    echo         index  index.php index.html^;
    echo.
    echo         charset utf-8^;
    echo.
    echo         location / {
    echo             try_files $uri $uri/ /index.php?$args^;
    echo         }
    echo.
    echo         location ~ \.php$ {
    echo             fastcgi_pass   127.0.0.1:9000^;
    echo             fastcgi_index  index.php^;
    echo.
    REM --- Configuration FastCGI autonome ---
    echo             fastcgi_param  GATEWAY_INTERFACE  CGI/1.1^;
    echo             fastcgi_param  SERVER_SOFTWARE    nginx^;
    echo             fastcgi_param  QUERY_STRING       $query_string^;
    echo             fastcgi_param  REQUEST_METHOD     $request_method^;
    echo             fastcgi_param  CONTENT_TYPE       $content_type^;
    echo             fastcgi_param  CONTENT_LENGTH     $content_length^;
    echo             fastcgi_param  REQUEST_URI        $request_uri^;
    echo             fastcgi_param  DOCUMENT_URI       $document_uri^;
    echo             fastcgi_param  DOCUMENT_ROOT      $document_root^;
    echo             fastcgi_param  SERVER_PROTOCOL    $server_protocol^;
    echo             fastcgi_param  REMOTE_ADDR        $remote_addr^;
    echo             fastcgi_param  REMOTE_PORT        $remote_port^;
    echo             fastcgi_param  SERVER_ADDR        $server_addr^;
    echo             fastcgi_param  SERVER_PORT        $server_port^;
    echo             fastcgi_param  SERVER_NAME        $server_name^;
    echo             fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name^;
    echo.
    echo             fastcgi_param  HTTP_ACCEPT_CHARSET "utf-8"^;
    echo             fastcgi_param  HTTP_ACCEPT_LANGUAGE "fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4"^;
    echo.
    echo    		 proxy_read_timeout 600s;
    echo    		 proxy_send_timeout 600s;
    echo    		 fastcgi_read_timeout 600s;
    echo    		 fastcgi_send_timeout 600s;
    echo         }
    echo     }
    echo }
) > "%NGINX_CONF%"

echo [INFO] nginx.conf genere avec root : %PROJECT_ROOT%

if not exist "nginx\logs" mkdir nginx\logs

REM Demarrer PHP-CGI (en arriere-plan)
start "PHP-CGI" "php\php-cgi.exe" -b 127.0.0.1:9000

REM Demarrer Nginx
cd nginx
start "Nginx" nginx.exe
cd ..

REM Ouvrir le navigateur
start "" http://localhost:8080

echo.
echo =============================================
echo PHP FastCGI et Nginx sont DEMARRES.
echo URL : http://localhost:8080
echo =============================================
echo.
echo Appuyez sur une touche pour retourner au menu...
pause >nul
goto :menu


REM ===================================================================
REM == 4. LOGIQUE D'ARRET
REM ===================================================================
:stop_servers
cls
echo [INFO] Arret de Nginx + PHP-CGI...
echo.

REM Arrêter Nginx proprement
if exist "nginx\nginx.exe" (
  cd nginx
  nginx.exe -s quit
  cd ..
)

REM Tuer les processus par force pour nettoyer
taskkill /IM nginx.exe /F >nul 2>&1
taskkill /IM php-cgi.exe /F >nul 2>&1

echo.
echo =========================
echo Tous les serveurs sont ARRETES.
echo =========================
echo.
echo Appuyez sur une touche pour retourner au menu...
pause >nul
goto :menu


REM ===================================================================
REM == 5. QUITTER LE SCRIPT
REM ===================================================================
:quit
cls
echo Au revoir!
timeout /t 1 /nobreak >nul
exit