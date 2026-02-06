@echo off
echo ========================================
echo Verificando servicios de XAMPP
echo ========================================
echo.

REM Verificar Apache
echo Verificando Apache...
netstat -an | findstr ":80" >nul
if %errorlevel% == 0 (
    echo [OK] Apache esta corriendo en el puerto 80
) else (
    echo [ERROR] Apache NO esta corriendo
    echo Por favor inicia Apache desde el Panel de Control de XAMPP
)
echo.

REM Verificar MySQL
echo Verificando MySQL...
netstat -an | findstr ":3306" >nul
if %errorlevel% == 0 (
    echo [OK] MySQL esta corriendo en el puerto 3306
) else (
    echo [ERROR] MySQL NO esta corriendo
    echo Por favor inicia MySQL desde el Panel de Control de XAMPP
)
echo.

REM Verificar PHP
echo Verificando PHP...
php -v >nul 2>&1
if %errorlevel% == 0 (
    echo [OK] PHP esta instalado
    php -v
) else (
    echo [ADVERTENCIA] PHP no se encuentra en el PATH
    echo Esto es normal si usas XAMPP, PHP funciona a traves de Apache
)
echo.

echo ========================================
echo Resumen:
echo ========================================
echo.
echo Si Apache y MySQL estan corriendo:
echo 1. Abre tu navegador
echo 2. Ve a: http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/
echo 3. O mejor: http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/install.php
echo.
echo Si hay errores, inicia los servicios desde el Panel de Control de XAMPP
echo.
pause
