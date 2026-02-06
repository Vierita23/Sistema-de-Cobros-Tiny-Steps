@echo off
echo ========================================
echo   Iniciar Túnel Público - Tiny Steps
echo ========================================
echo.
echo Este script iniciara un túnel público para tu sistema
echo.
echo Opciones disponibles:
echo 1. LocalTunnel (Recomendado - Gratis, Rapido)
echo 2. ngrok (Requiere registro)
echo 3. Ver URL local
echo.
set /p opcion="Selecciona una opcion (1-3): "

if "%opcion%"=="1" (
    echo.
    echo Instalando LocalTunnel...
    npm install -g localtunnel
    echo.
    echo Iniciando túnel en puerto 80...
    echo Si XAMPP usa otro puerto, cambia 80 por el puerto correcto
    echo.
    lt --port 80
)

if "%opcion%"=="2" (
    echo.
    echo Asegurate de tener ngrok instalado y configurado
    echo.
    echo Iniciando túnel en puerto 80...
    ngrok http 80
)

if "%opcion%"=="3" (
    echo.
    echo URL Local:
    echo http://localhost/Sistema%%20de%%20Cobros%%20Tiny%%20Steps/
    echo.
    echo Presiona cualquier tecla para cerrar...
    pause >nul
)

pause
