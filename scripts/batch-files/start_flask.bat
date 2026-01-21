@echo off
chcp 65001 > nul
echo Starting MQTT Scanner API Service...
cd /d "S:\Unikl\SEM 6\IPB49906 - FINAL YEAR PROJECT 2\Develop\mqtt-scanner-fyp2-main (2)\mqtt-scanner-fyp2-main"
echo Activating virtual environment...
call .venv\Scripts\activate.bat
cd mqtt-scanner
set PYTHONIOENCODING=utf-8
echo Starting Flask API on port 5001...
python app.py
pause
