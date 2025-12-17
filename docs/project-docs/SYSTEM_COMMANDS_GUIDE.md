# MQTT Security Scanner - Terminal Commands Guide

This guide lists all the terminal commands required to operate, manage, and test the MQTT Security Scanner system.

---

## 1. System Startup (The "Run" Commands)

To start the entire system, you need to run three separate components in three different terminals.

### Terminal 1: Infrastructure (MQTT Brokers)

This starts the Docker containers for the secure and insecure brokers.

```powershell
cd "mqtt-brokers"
docker-compose up -d
```

_Check status:_ `docker-compose ps`

### Terminal 2: Backend Engine (Python Scanner)

This starts the Flask API that performs the scanning.

```powershell
cd "mqtt-scanner"
python app.py
```

_Expected Output:_ `Running on http://127.0.0.1:5000`

### Terminal 3: Frontend Dashboard (Laravel)

This starts the web interface.

```powershell
php artisan serve
```

_Expected Output:_ `Server running on http://127.0.0.1:8000`

---

## 2. System Management & Configuration

### Managing Secure Broker Users

If you need to add or change the user for the secure broker (Port 8883):

**Add a new user (or update existing):**

```powershell
cd "mqtt-brokers"
docker exec mosq_secure mosquitto_passwd -b /mosquitto/config/passwordfile faris@gmail.com faris123
docker restart mosq_secure
```

**Check if the user exists:**

```powershell
docker exec mosq_secure cat /mosquitto/config/passwordfile
```

---

## 3. Testing & Verification Tools

These commands help you verify that the system is working correctly.

### A. Simulate IoT Traffic (Test Publisher)

If you don't have the physical ESP32 connected, use this script to send fake sensor data to the brokers.

```powershell
cd "mqtt-scanner"
python test_publisher.py
```

_Action:_ Sends temperature, humidity, and motion data to both brokers.
_Result:_ You should see this data appear on the Dashboard immediately.

### B. Clear "Stale" Data (Retained Messages)

If the dashboard shows data even when devices are off, use this to wipe the broker's memory.

```powershell
cd "mqtt-scanner"
python clear_retained.py
```

_Action:_ Deletes all retained messages from the brokers.
_Result:_ The dashboard should show no sensor data on the next scan.

### C. Verify Backend Connection

Check if the Laravel frontend can talk to the Python backend.

```powershell
cd "scripts"
php test_flask_connection.php
```

---

## 4. Troubleshooting Commands

**Stop all Brokers:**

```powershell
cd "mqtt-brokers"
docker-compose down
```

**View Broker Logs:**

```powershell
cd "mqtt-brokers"
docker-compose logs -f
```

**Install Python Dependencies (if missing):**

```powershell
cd "mqtt-scanner"
pip install -r requirements.txt
```
