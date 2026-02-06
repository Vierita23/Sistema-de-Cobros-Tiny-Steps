@echo off
chcp 65001 >nul
color 0E
echo ========================================
echo   Verificando Servicios de XAMPP
echo ========================================
echo.

set XAMPP_PATH=C:\xampp
set ALL_OK=1

REM Verificar si XAMPP existe
if not exist "%XAMPP_PATH%" (
    echo [✗] XAMPP no encontrado en: %XAMPP_PATH%
    echo.
    echo Por favor, verifica la instalación de XAMPP
    set ALL_OK=0
    goto :end
) else (
    echo [✓] XAMPP encontrado en: %XAMPP_PATH%
    echo.
)

REM Verificar Apache
echo Verificando Apache (puerto 80)...
netstat -an | findstr ":80" >nul
if %errorlevel% == 0 (
    echo [✓] Apache esta corriendo en el puerto 80
) else (
    echo [✗] Apache NO esta corriendo
    echo    Por favor inicia Apache desde el Panel de Control de XAMPP
    echo    O ejecuta: %XAMPP_PATH%\apache_start.bat
    set ALL_OK=0
)
echo.

REM Verificar MySQL
echo Verificando MySQL (puerto 3306)...
netstat -an | findstr ":3306" >nul
if %errorlevel% == 0 (
    echo [✓] MySQL esta corriendo en el puerto 3306
) else (
    echo [✗] MySQL NO esta corriendo
    echo    Por favor inicia MySQL desde el Panel de Control de XAMPP
    echo    O ejecuta: %XAMPP_PATH%\mysql_start.bat
    set ALL_OK=0
)
echo.

REM Verificar PHP
echo Verificando PHP...
if exist "%XAMPP_PATH%\php\php.exe" (
    echo [✓] PHP encontrado en XAMPP
    "%XAMPP_PATH%\php\php.exe" -v | findstr "PHP"
) else (
    echo [⚠] PHP no se encuentra en el PATH del sistema
    echo    Esto es normal si usas XAMPP, PHP funciona a traves de Apache
    if exist "%XAMPP_PATH%\php\php.exe" (
        echo    PHP esta disponible en: %XAMPP_PATH%\php\php.exe
    )
)
echo.

REM Resumen
echo ========================================
echo RESUMEN
echo ========================================
echo.

if %ALL_OK%==1 (
    echo [✓] Todos los servicios estan corriendo correctamente
    echo.
    echo PRÓXIMOS PASOS:
    echo 1. Abre tu navegador
    echo 2. Ve a: http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/
    echo 3. O verifica: http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/verificar_localhost.php
    echo 4. Si es primera vez: http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/install.php
    echo.
    echo ¿Deseas abrir el navegador ahora? (S/N)
    set /p abrir="> "
    if /i "%abrir%"=="S" (
        start http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/verificar_localhost.php
    )
) else (
    echo [✗] Hay servicios que no estan corriendo
    echo.
    echo SOLUCIÓN:
    echo 1. Abre el Panel de Control de XAMPP: %XAMPP_PATH%\xampp-control.exe
    echo 2. Inicia los servicios que falten (Apache y/o MySQL)
    echo 3. Ejecuta este script nuevamente
    echo.
    echo O ejecuta: ayuda_localhost.bat para obtener ayuda detallada
)

:end
echo.
pause
