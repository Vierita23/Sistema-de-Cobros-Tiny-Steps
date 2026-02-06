@echo off
echo ========================================
echo   Buscar Ubicacion de Node.js
echo ========================================
echo.
echo Buscando node.exe...
echo.
where node
echo.
echo Si no aparece nada arriba, Node.js puede estar en:
echo.
echo 1. C:\Program Files\nodejs\node.exe
echo 2. C:\Program Files (x86)\nodejs\node.exe
echo 3. C:\Users\%USERNAME%\AppData\Roaming\npm\node.exe
echo 4. C:\Users\%USERNAME%\AppData\Local\Programs\nodejs\node.exe
echo.
echo Verificando ubicaciones comunes...
echo.

if exist "C:\Program Files\nodejs\node.exe" (
    echo [ENCONTRADO] C:\Program Files\nodejs\node.exe
) else (
    echo [NO ENCONTRADO] C:\Program Files\nodejs\node.exe
)

if exist "C:\Program Files (x86)\nodejs\node.exe" (
    echo [ENCONTRADO] C:\Program Files (x86)\nodejs\node.exe
) else (
    echo [NO ENCONTRADO] C:\Program Files (x86)\nodejs\node.exe
)

if exist "%USERPROFILE%\AppData\Roaming\npm\node.exe" (
    echo [ENCONTRADO] %USERPROFILE%\AppData\Roaming\npm\node.exe
) else (
    echo [NO ENCONTRADO] %USERPROFILE%\AppData\Roaming\npm\node.exe
)

if exist "%USERPROFILE%\AppData\Local\Programs\nodejs\node.exe" (
    echo [ENCONTRADO] %USERPROFILE%\AppData\Local\Programs\nodejs\node.exe
) else (
    echo [NO ENCONTRADO] %USERPROFILE%\AppData\Local\Programs\nodejs\node.exe
)

echo.
echo Presiona cualquier tecla para cerrar...
pause >nul
