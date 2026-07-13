@echo off

echo %%%%%%%%%%%%%%%%%%%%%%%%%%%%%
echo   PARAR APLICACIÓN UMRALES
echo %%%%%%%%%%%%%%%%%%%%%%%%%%%%%
echo.

echo Deteniendo contenedor...

docker stop web_umbrales

REM echo Eliminando contenedor...

REM docker rm web_umbrales

echo.
echo Aplicacion detenida correctamente.
echo.

pause
