@echo off
echo ========================================
echo Iniciando Sistema Tiny Steps en Localhost
echo ========================================
echo.

REM Verificar si XAMPP esta en la ubicacion predeterminada
set XAMPP_PATH=C:\xampp
if not exist "%XAMPP_PATH%" (
    echo [ERROR] No se encontro XAMPP en la ubicacion predeterminada
    echo Por favor, inicia Apache y MySQL manualmente desde el Panel de Control de XAMPP
    pause
    exit /b
)

echo Verificando servicios de XAMPP...
echo.

REM Verificar Apache
netstat -an | findstr ":80" >nul
if %errorlevel% == 0 (
    echo [OK] Apache esta corriendo
) else (
    echo [ADVERTENCIA] Apache no esta corriendo
    echo Intentando iniciar Apache...
    start "" "%XAMPP_PATH%\apache_start.bat"
    timeout /t 3 /nobreak >nul
)

REM Verificar MySQL
netstat -an | findstr ":3306" >nul
if %errorlevel% == 0 (
    echo [OK] MySQL esta corriendo
) else (
    echo [ADVERTENCIA] MySQL no esta corriendo
    echo Intentando iniciar MySQL...
    start "" "%XAMPP_PATH%\mysql_start.bat"
    timeout /t 3 /nobreak >nul
)

echo.
echo ========================================
echo Abriendo el sistema en el navegador...
echo ========================================
echo.

REM Esperar un momento para que los servicios inicien
timeout /t 2 /nobreak >nul

REM Abrir el navegador con la URL del sistema
start http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/

echo.
echo El sistema deberia abrirse en tu navegador.
echo Si no se abre automaticamente, copia y pega esta URL:
echo http://localhost/Sistema de Cobros Tiny Steps/
echo.
echo Si es la primera vez, ve a: http://localhost/Sistema de Cobros Tiny Steps/install.php
echo.
pause
