@echo off
echo ========================================
echo   Verificar Estado de Apache
echo ========================================
echo.
echo Verificando si Apache esta corriendo...
echo.

netstat -ano | findstr :80 >nul
if %errorlevel% == 0 (
    echo [OK] Puerto 80 esta en uso - Apache probablemente esta corriendo
    echo.
    echo Verifica en XAMPP Control Panel que Apache este en VERDE
) else (
    echo [ERROR] Puerto 80 NO esta en uso
    echo.
    echo Necesitas iniciar Apache en XAMPP Control Panel
)

echo.
echo Presiona cualquier tecla para cerrar...
pause >nul
