@echo off
chcp 65001 >nul
color 0B
echo ========================================
echo   Iniciando Sistema Tiny Steps
echo   en Localhost
echo ========================================
echo.

REM Verificar si XAMPP esta en la ubicacion predeterminada
set XAMPP_PATH=C:\xampp
if not exist "%XAMPP_PATH%" (
    echo [ERROR] No se encontro XAMPP en la ubicacion predeterminada
    echo Por favor, inicia Apache y MySQL manualmente desde el Panel de Control de XAMPP
    echo.
    echo O ejecuta: ayuda_localhost.bat para obtener ayuda
    echo.
    pause
    exit /b
)

echo [INFO] Verificando servicios de XAMPP...
echo.

REM Verificar Apache
netstat -an | findstr ":80" >nul
if %errorlevel% == 0 (
    echo [✓] Apache esta corriendo
    set APACHE_OK=1
) else (
    echo [✗] Apache no esta corriendo
    echo [INFO] Intentando iniciar Apache...
    start "" "%XAMPP_PATH%\apache_start.bat"
    timeout /t 5 /nobreak >nul
    netstat -an | findstr ":80" >nul
    if %errorlevel% == 0 (
        echo [✓] Apache iniciado correctamente
        set APACHE_OK=1
    ) else (
        echo [✗] No se pudo iniciar Apache automaticamente
        echo [INFO] Por favor inicia Apache desde el Panel de Control de XAMPP
        set APACHE_OK=0
    )
)

REM Verificar MySQL
netstat -an | findstr ":3306" >nul
if %errorlevel% == 0 (
    echo [✓] MySQL esta corriendo
    set MYSQL_OK=1
) else (
    echo [✗] MySQL no esta corriendo
    echo [INFO] Intentando iniciar MySQL...
    start "" "%XAMPP_PATH%\mysql_start.bat"
    timeout /t 5 /nobreak >nul
    netstat -an | findstr ":3306" >nul
    if %errorlevel% == 0 (
        echo [✓] MySQL iniciado correctamente
        set MYSQL_OK=1
    ) else (
        echo [✗] No se pudo iniciar MySQL automaticamente
        echo [INFO] Por favor inicia MySQL desde el Panel de Control de XAMPP
        set MYSQL_OK=0
    )
)

echo.

REM Verificar si todo esta OK
if %APACHE_OK%==1 if %MYSQL_OK%==1 (
    echo ========================================
    echo [✓] Todos los servicios estan listos
    echo ========================================
    echo.
    echo Abriendo el sistema en el navegador...
    echo.
    
    REM Esperar un momento para que los servicios esten completamente listos
    timeout /t 2 /nobreak >nul
    
    REM Abrir el navegador con la URL del sistema
    start http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/
    
    echo.
    echo El sistema deberia abrirse en tu navegador.
    echo.
    echo URLs importantes:
    echo - Login: http://localhost/Sistema de Cobros Tiny Steps/
    echo - Verificar: http://localhost/Sistema de Cobros Tiny Steps/verificar_localhost.php
    echo - Instalar BD: http://localhost/Sistema de Cobros Tiny Steps/install.php
    echo.
) else (
    echo ========================================
    echo [✗] Hay problemas con los servicios
    echo ========================================
    echo.
    echo Por favor, ejecuta: ayuda_localhost.bat
    echo para obtener ayuda detallada.
    echo.
)

pause
