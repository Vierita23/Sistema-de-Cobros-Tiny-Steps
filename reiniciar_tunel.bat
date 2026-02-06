@echo off
echo ========================================
echo   Reiniciar Túnel LocalTunnel
echo ========================================
echo.
echo Este script reiniciara el túnel público
echo.
echo Verificando puerto de Apache...
echo.
echo Si Apache usa puerto 80, presiona 1
echo Si Apache usa puerto 8080, presiona 2
echo.
set /p opcion="Selecciona opcion (1 o 2): "

if "%opcion%"=="1" (
    echo.
    echo Iniciando túnel en puerto 80...
    echo.
    lt --port 80
)

if "%opcion%"=="2" (
    echo.
    echo Iniciando túnel en puerto 8080...
    echo.
    lt --port 8080
)

pause
