@echo off
REM Start All Services for MQTT Scanner

echo ============================================
echo  Starting MQTT Scanner Services
echo ============================================
echo.

REM Check Docker
echo [1/4] Checking Docker containers...
docker ps | findstr "mosq_"
if errorlevel 1 (
    echo ERROR: Docker containers not running!
    echo Please start Docker Desktop and run: cd mqtt-brokers ^&^& docker-compose up -d
    pause
    exit /b 1
)
echo OK: Docker containers running
echo.

REM Start Flask in new window
echo [2/4] Starting Flask MQTT Scanner...
start "Flask Scanner" cmd /k "cd mqtt-scanner && python app.py"
timeout /t 3 /nobreak >nul
echo OK: Flask started on http://127.0.0.1:5000
echo.

REM Start Laravel in new window
echo [3/4] Starting Laravel Dashboard...
start "Laravel Dashboard" cmd /k "php artisan serve"
timeout /t 3 /nobreak >nul
echo OK: Laravel started on http://127.0.0.1:8000
echo.

REM Summary
echo [4/4] All services started!
echo.
echo ============================================
echo  Service URLs:
echo ============================================
echo  Flask API:      http://127.0.0.1:5000
echo  Laravel Dashboard: http://127.0.0.1:8000
echo.
echo  MQTT Brokers:
echo   - Insecure:    192.168.100.56:1883
echo   - Secure TLS:  192.168.100.56:8883
echo ============================================
echo.
echo Press any key to open dashboard in browser...
pause >nul

REM Open dashboard in default browser
start http://127.0.0.1:8000/dashboard

echo.
echo Dashboard opened! Check the browser window.
echo.
echo IMPORTANT: To stop services, close the Flask and Laravel windows.
echo.
pause
