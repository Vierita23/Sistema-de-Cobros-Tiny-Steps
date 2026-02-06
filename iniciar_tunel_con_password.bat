@echo off
echo ========================================
echo   Iniciar Túnel con Contraseña
echo ========================================
echo.
echo Este script iniciara un túnel con contraseña personalizada
echo.
set /p password="Ingresa la contraseña que quieres usar: "
echo.
echo Iniciando túnel con contraseña...
echo.
lt --port 80 --subdomain gold-facts-feel
echo.
echo Si te pide contraseña, usa: %password%
pause
