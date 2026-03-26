@echo off
title Sistema Predictivo - Iniciando...
echo.
echo  ===============================================
echo   ITSC - Sistema Predictivo de Reprobacion
echo  ===============================================
echo.

REM Verificar que Laragon este corriendo (MySQL)
echo [1/3] Verificando MySQL...
mysql -u root -e "SELECT 1;" >nul 2>&1
if errorlevel 1 (
    echo  ADVERTENCIA: MySQL no responde. Asegurate de que Laragon este corriendo.
    echo  Presiona cualquier tecla para continuar de todas formas...
    pause >nul
) else (
    echo  MySQL OK
)

REM Iniciar microservicio Python en nueva ventana
echo.
echo [2/3] Iniciando microservicio ML (Python)...
start "ML Service - Puerto 5000" cmd /k "cd /d "%~dp0ml-service" && echo Iniciando Flask... && C:\Users\itzfa\AppData\Local\Programs\Python\Python312\python.exe app.py"

REM Esperar 2 segundos para que Flask arranque
timeout /t 2 /nobreak >nul

REM Iniciar Laravel en nueva ventana
echo [3/3] Iniciando servidor Laravel...
start "Laravel - Puerto 8000" cmd /k "cd /d "%~dp0backend" && echo Iniciando Laravel... && php artisan route:clear && php artisan serve"

echo.
echo  ===============================================
echo   Servidores iniciados:
echo   - Laravel:  http://127.0.0.1:8000
echo   - ML API:   http://127.0.0.1:5000/health
echo  ===============================================
echo.
echo  Cierra esta ventana cuando quieras. Los
echo  servidores siguen corriendo en sus propias
echo  ventanas.
echo.
pause
