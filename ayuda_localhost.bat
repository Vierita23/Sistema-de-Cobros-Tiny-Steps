@echo off
chcp 65001 >nul
color 0A
echo ========================================
echo   AYUDA LOCALHOST - Sistema Tiny Steps
echo ========================================
echo.

REM Verificar si XAMPP está instalado
set XAMPP_PATH=C:\xampp
if not exist "%XAMPP_PATH%" (
    echo [ERROR] No se encontró XAMPP en la ubicación predeterminada
    echo.
    echo Por favor, asegúrate de que XAMPP esté instalado en: C:\xampp
    echo O modifica la variable XAMPP_PATH en este script.
    echo.
    pause
    exit /b
)

echo [INFO] XAMPP encontrado en: %XAMPP_PATH%
echo.

REM Verificar Apache
echo ========================================
echo Verificando Apache...
echo ========================================
netstat -an | findstr ":80" >nul
if %errorlevel% == 0 (
    echo [✓] Apache está corriendo en el puerto 80
    set APACHE_OK=1
) else (
    echo [✗] Apache NO está corriendo
    set APACHE_OK=0
    echo.
    echo ¿Deseas iniciar Apache ahora? (S/N)
    set /p iniciar_apache="> "
    if /i "%iniciar_apache%"=="S" (
        echo Iniciando Apache...
        start "" "%XAMPP_PATH%\apache_start.bat"
        timeout /t 5 /nobreak >nul
        netstat -an | findstr ":80" >nul
        if %errorlevel% == 0 (
            echo [✓] Apache iniciado correctamente
            set APACHE_OK=1
        ) else (
            echo [✗] No se pudo iniciar Apache. Por favor inícialo manualmente desde el Panel de Control de XAMPP
            set APACHE_OK=0
        )
    )
)
echo.

REM Verificar MySQL
echo ========================================
echo Verificando MySQL...
echo ========================================
netstat -an | findstr ":3306" >nul
if %errorlevel% == 0 (
    echo [✓] MySQL está corriendo en el puerto 3306
    set MYSQL_OK=1
) else (
    echo [✗] MySQL NO está corriendo
    set MYSQL_OK=0
    echo.
    echo ¿Deseas iniciar MySQL ahora? (S/N)
    set /p iniciar_mysql="> "
    if /i "%iniciar_mysql%"=="S" (
        echo Iniciando MySQL...
        start "" "%XAMPP_PATH%\mysql_start.bat"
        timeout /t 5 /nobreak >nul
        netstat -an | findstr ":3306" >nul
        if %errorlevel% == 0 (
            echo [✓] MySQL iniciado correctamente
            set MYSQL_OK=1
        ) else (
            echo [✗] No se pudo iniciar MySQL. Por favor inícialo manualmente desde el Panel de Control de XAMPP
            set MYSQL_OK=0
        )
    )
)
echo.

REM Verificar PHP
echo ========================================
echo Verificando PHP...
echo ========================================
if exist "%XAMPP_PATH%\php\php.exe" (
    echo [✓] PHP encontrado
    "%XAMPP_PATH%\php\php.exe" -v | findstr "PHP"
    set PHP_OK=1
) else (
    echo [✗] PHP no encontrado en XAMPP
    set PHP_OK=0
)
echo.

REM Resumen
echo ========================================
echo RESUMEN
echo ========================================
echo.

if %APACHE_OK%==1 if %MYSQL_OK%==1 if %PHP_OK%==1 (
    echo [✓] Todos los servicios están funcionando correctamente
    echo.
    echo ========================================
    echo PRÓXIMOS PASOS
    echo ========================================
    echo.
    echo 1. Verificar la instalación de la base de datos:
    echo    http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/verificar_localhost.php
    echo.
    echo 2. Si es la primera vez, instalar la base de datos:
    echo    http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/install.php
    echo.
    echo 3. Acceder al sistema:
    echo    http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/
    echo.
    echo ¿Deseas abrir el navegador ahora? (S/N)
    set /p abrir_navegador="> "
    if /i "%abrir_navegador%"=="S" (
        start http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/verificar_localhost.php
    )
) else (
    echo [✗] Hay problemas con algunos servicios
    echo.
    echo SOLUCIONES:
    echo.
    if %APACHE_OK%==0 (
        echo - Apache: Abre el Panel de Control de XAMPP e inicia Apache manualmente
        echo   O ejecuta: %XAMPP_PATH%\apache_start.bat
        echo.
    )
    if %MYSQL_OK%==0 (
        echo - MySQL: Abre el Panel de Control de XAMPP e inicia MySQL manualmente
        echo   O ejecuta: %XAMPP_PATH%\mysql_start.bat
        echo.
    )
    if %PHP_OK%==0 (
        echo - PHP: Verifica que XAMPP esté instalado correctamente
        echo.
    )
    echo Después de solucionar los problemas, ejecuta este script nuevamente.
)

echo.
echo ========================================
echo OPCIONES ADICIONALES
echo ========================================
echo.
echo 1. Abrir Panel de Control de XAMPP
echo 2. Abrir phpMyAdmin
echo 3. Verificar localhost en el navegador
echo 4. Salir
echo.
set /p opcion="Selecciona una opción (1-4): "

if "%opcion%"=="1" (
    start "" "%XAMPP_PATH%\xampp-control.exe"
)
if "%opcion%"=="2" (
    start http://localhost/phpmyadmin/
)
if "%opcion%"=="3" (
    start http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/verificar_localhost.php
)

echo.
pause
