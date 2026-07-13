@echo off

for %%I in ("%~dp0..") do set "PROJECT_DIR=%%~fI"
set "SCRIPT_DIR=%~dp0"

echo %%%%%%%%%%%%%%%%%%%%%%%%%%%%%
echo APLICACION UMBRALES DOCKER
echo %%%%%%%%%%%%%%%%%%%%%%%%%%%%%

REM Comprobar que Docker Desktop está iniciado
docker info >nul 2>nul

if errorlevel 1 (
    echo ERROR: Docker Desktop no esta iniciado.
    echo.
    echo Inicia Docker Desktop y vuelve a ejecutar este archivo.
    echo.
    pause
    exit
)

echo Construyendo una imagen nueva desde el Dockerfile local
docker build -t umbrales_app:1.1 -f "%PROJECT_DIR%\Dockerfile" "%PROJECT_DIR%"

if errorlevel 1 (
    echo.
    echo ERROR: No se pudo construir la imagen.
    pause
    exit
)


echo Se esta el eliminando el contenedor web_umbrales anterior
docker rm -f web_umbrales >nul 2>nul


echo Creando nuevo contenedor

docker run -d ^
  --name web_umbrales ^
  -p 8080:80 ^
  --env-file "%SCRIPT_DIR%.env" ^
  umbrales_app:1.1

if errorlevel 1 (
    echo.
    echo ERROR: No se pudo crear el contenedor.
    pause
    exit
)


echo ===============================
echo Aplicación cpmpletada
echo ===============================
start http://localhost:8080/login

echo Si no se ha abierto automáticamente, abrir: http://localhost:8080/login
pause
